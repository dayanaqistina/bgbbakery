<?php
/* Author: Dayana Qistina Binti Mat Zake */
require_once '../database.php';

session_start();
if (!isset($_SESSION['worker_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$globalDate = $_GET['global_date'] ?? null;
$globalMonth = $_GET['global_month'] ?? null;
$searchTerm = $_GET['search'] ?? null;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10; // 10 orders per page
$offset = ($page - 1) * $limit;

try {
    $whereClauses = [];
    $params = [];

    if ($globalDate) {
        $whereClauses[] = "TRUNC(o.ORDER_DATE) = TO_DATE(:gdate, 'YYYY-MM-DD')";
        $params[':gdate'] = $globalDate;
    } else if ($globalMonth) {
        $whereClauses[] = "TO_CHAR(o.ORDER_DATE, 'YYYY-MM') = :gmonth";
        $params[':gmonth'] = $globalMonth;
    }

    if ($searchTerm) {
        $cleanNum = preg_replace('/[^0-9]/', '', $searchTerm);
        if (!empty($cleanNum)) {
            $whereClauses[] = "(UPPER('ORD' || LPAD(TO_CHAR(o.ORDER_ID), 4, '0')) LIKE UPPER(:search) OR TO_CHAR(o.ORDER_ID) LIKE :raw_search OR UPPER(c.CUST_ID) LIKE UPPER(:search) OR LOWER(c.CUST_NAME) LIKE LOWER(:search))";
            $params[':search'] = '%' . trim($searchTerm) . '%';
            $params[':raw_search'] = '%' . $cleanNum . '%';
        } else {
            $whereClauses[] = "(UPPER('ORD' || LPAD(TO_CHAR(o.ORDER_ID), 4, '0')) LIKE UPPER(:search) OR UPPER(c.CUST_ID) LIKE UPPER(:search) OR LOWER(c.CUST_NAME) LIKE LOWER(:search))";
            $params[':search'] = '%' . trim($searchTerm) . '%';
        }
    }

    $whereSql = count($whereClauses) > 0 ? "WHERE " . implode(" AND ", $whereClauses) : "";

    // Get Total Count
    $countQuery = "SELECT COUNT(*) FROM ORDERS o LEFT JOIN CUSTOMER c ON o.CUST_ID = c.CUST_ID $whereSql";
    $countStmt = oci_parse($conn, $countQuery);
    foreach ($params as $key => $val) {
        oci_bind_by_name($countStmt, $key, $params[$key]);
    }
    oci_execute($countStmt);
    $countRow = oci_fetch_array($countStmt, OCI_NUM);
    $totalRecords = $countRow ? (int)$countRow[0] : 0;
    $totalPages = ceil($totalRecords / $limit);
    if ($totalPages == 0) $totalPages = 1;

    // Get Paginated Data
    $ordersQuery = "
        SELECT o.ORDER_ID, c.CUST_NAME, o.ORDER_TYPE, 
               (SELECT COUNT(PRODUCT_ID) FROM ORDERDETAILS od WHERE od.ORDER_ID = o.ORDER_ID) as ITEMS, 
               o.ORDER_STATUS, 
               (SELECT NVL(SUM(SUBTOTAL), 0) FROM ORDERDETAILS od WHERE od.ORDER_ID = o.ORDER_ID) as TOTAL_AMOUNT,
               TO_CHAR(o.ORDER_DATE, 'HH:MI AM') as ORDER_TIME
        FROM ORDERS o 
        LEFT JOIN CUSTOMER c ON o.CUST_ID = c.CUST_ID
        $whereSql
        ORDER BY o.ORDER_DATE DESC, o.ORDER_ID DESC
        OFFSET :offset ROWS FETCH NEXT :limit ROWS ONLY
    ";
    
    $stmt = oci_parse($conn, $ordersQuery);
    foreach ($params as $key => $val) {
        oci_bind_by_name($stmt, $key, $params[$key]);
    }
    oci_bind_by_name($stmt, ':offset', $offset);
    oci_bind_by_name($stmt, ':limit', $limit);
    oci_execute($stmt);

    $orders = [];
    while ($row = oci_fetch_assoc($stmt)) {
        $orders[] = [
            'order_id' => 'ORD' . str_pad($row['ORDER_ID'], 4, '0', STR_PAD_LEFT),
            'customer_name' => $row['CUST_NAME'] ?? 'Guest',
            'type' => $row['ORDER_TYPE'],
            'items' => $row['ITEMS'],
            'status' => $row['ORDER_STATUS'],
            'total' => isset($row['TOTAL_AMOUNT']) ? (float)$row['TOTAL_AMOUNT'] : 0,
            'time' => $row['ORDER_TIME']
        ];
    }

    echo json_encode([
        'success' => true,
        'orders' => $orders,
        'current_page' => $page,
        'total_pages' => $totalPages,
        'total_records' => $totalRecords
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to load orders: ' . $e->getMessage()]);
}
?>
