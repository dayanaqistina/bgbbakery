<?php
// api/dashboard.php
session_start();
require_once '../database.php';

header('Content-Type: application/json');

// Check admin authentication
if (!isset($_SESSION['worker_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$globalDate = $_GET['global_date'] ?? null;
$globalMonth = $_GET['global_month'] ?? null;
$revenueMonth = $_GET['revenue_month'] ?? null;
$searchTerm = $_GET['search'] ?? null;

try {
    function getSingleValueFiltered($conn, $baseQuery, $dateFilterString, $filterVal) {
        $query = $baseQuery . ($filterVal ? " $dateFilterString " : "");
        $stmt = oci_parse($conn, $query);
        if ($filterVal) oci_bind_by_name($stmt, ':filterval', $filterVal);
        oci_execute($stmt);
        $row = oci_fetch_array($stmt, OCI_NUM);
        return $row ? (float)$row[0] : 0;
    }

    $metrics = [];
    
    $filterSql = "";
    $filterVal = null;
    if ($globalDate) {
        $filterSql = "WHERE TRUNC(ORDER_DATE) = TO_DATE(:filterval, 'YYYY-MM-DD')";
        $filterVal = $globalDate;
    } else if ($globalMonth) {
        $filterSql = "WHERE TO_CHAR(ORDER_DATE, 'YYYY-MM') = :filterval";
        $filterVal = $globalMonth;
    }
    
    $filterSqlWithAnd = "";
    if ($globalDate) {
        $filterSqlWithAnd = "AND TRUNC(ORDER_DATE) = TO_DATE(:filterval, 'YYYY-MM-DD')";
    } else if ($globalMonth) {
        $filterSqlWithAnd = "AND TO_CHAR(ORDER_DATE, 'YYYY-MM') = :filterval";
    }

    // 1. Get Top Metrics (Total, Pending, Completed, Revenue)
    $metrics['total_orders'] = getSingleValueFiltered($conn, 
        "SELECT COUNT(ORDER_ID) FROM ORDERS", 
        $filterSql, 
        $filterVal
    );

    $metrics['pending_orders'] = getSingleValueFiltered($conn, 
        "SELECT COUNT(*) FROM ORDERS WHERE LOWER(ORDER_STATUS) = 'pending'", 
        $filterSqlWithAnd, 
        $filterVal
    );

    $metrics['completed_orders'] = getSingleValueFiltered($conn, 
        "SELECT COUNT(*) FROM ORDERS WHERE LOWER(ORDER_STATUS) = 'completed'", 
        $filterSqlWithAnd, 
        $filterVal
    );

    $metrics['revenue'] = getSingleValueFiltered($conn, 
        "SELECT NVL(SUM(od.SUBTOTAL), 0) FROM ORDERDETAILS od JOIN ORDERS o ON o.ORDER_ID = od.ORDER_ID", 
        $filterSql, 
        $filterVal
    );

    // 2. Orders
    $recentOrdersQuery = "
        SELECT o.ORDER_ID, c.CUST_NAME, o.ORDER_TYPE, 
               (SELECT COUNT(PRODUCT_ID) FROM ORDERDETAILS od WHERE od.ORDER_ID = o.ORDER_ID) as ITEMS, 
               o.ORDER_STATUS, 
               (SELECT NVL(SUM(SUBTOTAL), 0) FROM ORDERDETAILS od WHERE od.ORDER_ID = o.ORDER_ID) as TOTAL_AMOUNT,
               TO_CHAR(o.ORDER_DATE, 'HH:MI AM') as ORDER_TIME
        FROM ORDERS o 
        LEFT JOIN CUSTOMER c ON o.CUST_ID = c.CUST_ID
    ";
    
    $whereClauses = [];
    if ($globalDate) {
        $whereClauses[] = "TRUNC(o.ORDER_DATE) = TO_DATE(:gdate, 'YYYY-MM-DD')";
    } else if ($globalMonth) {
        $whereClauses[] = "TO_CHAR(o.ORDER_DATE, 'YYYY-MM') = :gmonth";
    }
    
    if ($searchTerm) {
        $whereClauses[] = "(TO_CHAR(o.ORDER_ID) LIKE :search OR c.CUST_ID LIKE :search OR LOWER(c.CUST_NAME) LIKE LOWER(:search))";
    }
    
    if (count($whereClauses) > 0) {
        $recentOrdersQuery .= " WHERE " . implode(" AND ", $whereClauses);
    }
    
    $recentOrdersQuery .= " ORDER BY o.ORDER_DATE DESC, o.ORDER_ID DESC FETCH FIRST 7 ROWS ONLY";
    
    $stmt = oci_parse($conn, $recentOrdersQuery);
    if ($globalDate) oci_bind_by_name($stmt, ':gdate', $globalDate);
    if ($globalMonth) oci_bind_by_name($stmt, ':gmonth', $globalMonth);
    if ($searchTerm) {
        $searchParam = '%' . $searchTerm . '%';
        oci_bind_by_name($stmt, ':search', $searchParam);
    }
    oci_execute($stmt);
    
    $recentOrders = [];
    while ($row = oci_fetch_assoc($stmt)) {
        $recentOrders[] = [
            'order_id' => 'ORD' . str_pad($row['ORDER_ID'], 4, '0', STR_PAD_LEFT), // Formatting ID
            'customer_name' => $row['CUST_NAME'],
            'type' => $row['ORDER_TYPE'],
            'items' => $row['ITEMS'],
            'status' => $row['ORDER_STATUS'],
            'total' => $row['TOTAL'] ? (float)$row['TOTAL'] : 0,
            'time' => $row['ORDER_TIME']
        ];
    }

    // 3. Revenue Chart
    $revenueChart = [];
    if ($revenueMonth) {
        $revenueChartQuery = "
            SELECT TO_CHAR(TRUNC(o.ORDER_DATE), 'YYYY-MM-DD') as ORDER_DATE_STR, SUM(od.SUBTOTAL) as REVENUE
            FROM ORDERDETAILS od 
            JOIN ORDERS o ON o.ORDER_ID = od.ORDER_ID 
            WHERE TO_CHAR(o.ORDER_DATE, 'YYYY-MM') = :revMonth
            GROUP BY TRUNC(o.ORDER_DATE) 
            ORDER BY TRUNC(o.ORDER_DATE)
        ";
        $stmt = oci_parse($conn, $revenueChartQuery);
        oci_bind_by_name($stmt, ':revMonth', $revenueMonth);
        oci_execute($stmt);
        
        $revenueDataMap = [];
        while ($row = oci_fetch_assoc($stmt)) {
            $revenueDataMap[$row['ORDER_DATE_STR']] = (float)$row['REVENUE'];
        }

        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, date('m', strtotime($revenueMonth . '-01')), date('Y', strtotime($revenueMonth . '-01')));
        for ($i = 1; $i <= $daysInMonth; $i++) {
            $dateStr = $revenueMonth . '-' . str_pad($i, 2, '0', STR_PAD_LEFT);
            $displayStr = date('M j', strtotime($dateStr));
            $revenueChart[] = [
                'date' => $displayStr,
                'revenue' => isset($revenueDataMap[$dateStr]) ? $revenueDataMap[$dateStr] : 0
            ];
        }
    } else {
        $revenueChartQuery = "
            SELECT TO_CHAR(TRUNC(o.ORDER_DATE, 'MM'), 'YYYY-MM') as ORDER_MONTH_STR, SUM(od.SUBTOTAL) as REVENUE
            FROM ORDERDETAILS od 
            JOIN ORDERS o ON o.ORDER_ID = od.ORDER_ID 
            WHERE o.ORDER_DATE >= TRUNC(ADD_MONTHS(SYSDATE, -5), 'MM')
            GROUP BY TRUNC(o.ORDER_DATE, 'MM')
            ORDER BY TRUNC(o.ORDER_DATE, 'MM')
        ";
        $stmt = oci_parse($conn, $revenueChartQuery);
        oci_execute($stmt);
        
        $revenueDataMap = [];
        while ($row = oci_fetch_assoc($stmt)) {
            $revenueDataMap[$row['ORDER_MONTH_STR']] = (float)$row['REVENUE'];
        }

        for ($i = 5; $i >= 0; $i--) {
            $monthStr = date('Y-m', strtotime("-$i months"));
            $displayStr = date('M Y', strtotime("-$i months"));
            $revenueChart[] = [
                'date' => $displayStr,
                'revenue' => isset($revenueDataMap[$monthStr]) ? $revenueDataMap[$monthStr] : 0
            ];
        }
    }

    // 4. Pickup vs Delivery
    $pickupFilter = "";
    if ($globalDate) {
        $pickupFilter = "WHERE TRUNC(DATE_TIME) = TO_DATE(:filterval, 'YYYY-MM-DD')";
    } else if ($globalMonth) {
        $pickupFilter = "WHERE TO_CHAR(DATE_TIME, 'YYYY-MM') = :filterval";
    }
    
    $pickupCount = getSingleValueFiltered($conn, "SELECT COUNT(*) FROM PICKUP", $pickupFilter, $filterVal);
    $deliveryCount = getSingleValueFiltered($conn, "SELECT COUNT(*) FROM DELIVERY", $pickupFilter, $filterVal);
    
    $pickupVsDelivery = ['Delivery' => $deliveryCount, 'Pickup' => $pickupCount];

    echo json_encode([
        'success' => true,
        'metrics' => $metrics,
        'recent_orders' => $recentOrders,
        'revenue_chart' => $revenueChart,
        'pickup_vs_delivery' => $pickupVsDelivery
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to load dashboard data: ' . $e->getMessage()]);
}
?>
