<?php
/* Author: Dayana Qistina Binti Mat Zake */
// api/shop_orders.php

header('Content-Type: application/json');

require_once __DIR__ . '/../database.php';

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON input']);
    exit;
}

if (empty($data['items'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Order must contain at least one product.']);
    exit;
}

try {
    // 1. Create or Get Customer
    $email = empty($data['email']) ? 'not-provided@bgb.local' : $data['email'];
    $name = empty($data['name']) ? 'Unknown' : $data['name'];
    $phone = empty($data['phoneNumber']) ? 'Unknown' : $data['phoneNumber'];
    
    $stmt = oci_parse($conn, "INSERT INTO CUSTOMER (CUST_NAME, CUST_NOPHONE, CUST_EMAIL) VALUES (:name, :phone, :email) RETURNING CUST_ID INTO :cust_id");
    oci_bind_by_name($stmt, ':name', $name);
    oci_bind_by_name($stmt, ':phone', $phone);
    oci_bind_by_name($stmt, ':email', $email);
    oci_bind_by_name($stmt, ':cust_id', $custId, -1, SQLT_INT);
    oci_execute($stmt, OCI_NO_AUTO_COMMIT);
    
    // 2. Get Worker
    $stmt = oci_parse($conn, "SELECT WORKER_ID FROM WORKER FETCH FIRST 1 ROWS ONLY");
    oci_execute($stmt, OCI_NO_AUTO_COMMIT);
    
    $workerRow = oci_fetch_assoc($stmt);
    if ($workerRow) {
        $workerId = $workerRow['WORKER_ID'];
    } else {
        $stmt = oci_parse($conn, "INSERT INTO WORKER (WORKER_NAME, WORKER_NOPHONE) VALUES ('Default Worker', '0000000000') RETURNING WORKER_ID INTO :worker_id");
        oci_bind_by_name($stmt, ':worker_id', $workerId, -1, SQLT_INT);
        oci_execute($stmt, OCI_NO_AUTO_COMMIT);
    }
    
    // 3. Create Order
    $orderDate = date('Y-m-d');
    $status = 'Pending';
    $orderType = (strcasecmp($data['fulfilment'] ?? '', 'Delivery') === 0) ? 'Delivery' : 'Pickup';
    
    $stmt = oci_parse($conn, "INSERT INTO ORDERS (ORDER_DATE, ORDER_STATUS, ORDER_TYPE, CUST_ID, WORKER_ID) VALUES (TO_DATE(:orderDate, 'YYYY-MM-DD'), :status, :orderType, :custId, :workerId) RETURNING ORDER_ID INTO :order_id");
    oci_bind_by_name($stmt, ':orderDate', $orderDate);
    oci_bind_by_name($stmt, ':status', $status);
    oci_bind_by_name($stmt, ':orderType', $orderType);
    oci_bind_by_name($stmt, ':custId', $custId);
    oci_bind_by_name($stmt, ':workerId', $workerId);
    oci_bind_by_name($stmt, ':order_id', $orderId, -1, SQLT_INT);
    oci_execute($stmt, OCI_NO_AUTO_COMMIT);
    
    // 4. Create Order Details
    $total = 0.0;
    foreach ($data['items'] as $item) {
        $productId = $item['productId'];
        $quantity = (!isset($item['quantity']) || $item['quantity'] < 1) ? 1 : $item['quantity'];
        
        $stmt = oci_parse($conn, "SELECT PRICE FROM PRODUCT WHERE PRODUCT_ID = :productId");
        oci_bind_by_name($stmt, ':productId', $productId);
        oci_execute($stmt, OCI_NO_AUTO_COMMIT);
        
        $priceRow = oci_fetch_assoc($stmt);
        if (!$priceRow) {
            throw new Exception("Product not found: " . $productId);
        }
        $price = $priceRow['PRICE'];
        
        $subtotal = $price * $quantity;
        
        $stmt = oci_parse($conn, "INSERT INTO ORDERDETAILS (ORDER_ID, PRODUCT_ID, QUANTITY, SUBTOTAL) VALUES (:orderId, :productId, :quantity, :subtotal)");
        oci_bind_by_name($stmt, ':orderId', $orderId);
        oci_bind_by_name($stmt, ':productId', $productId);
        oci_bind_by_name($stmt, ':quantity', $quantity);
        oci_bind_by_name($stmt, ':subtotal', $subtotal);
        oci_execute($stmt, OCI_NO_AUTO_COMMIT);
        
        $total += $subtotal;
    }
    
    // 5. Create Delivery or Pickup
    $preferredDateStr = empty($data['preferredDate']) ? date('Y-m-d') : $data['preferredDate'];
    // We will just use TO_DATE in Oracle
    
    if ($orderType === 'Delivery') {
        $trackingNumber = 'TRK' . time();
        $address = empty($data['notes']) ? 'To be confirmed' : $data['notes'];
        
        $stmt = oci_parse($conn, "INSERT INTO DELIVERY (ORDER_ID, TRACKING_NO, DATE_TIME, ADDRESS) VALUES (:orderId, :trackingNumber, TO_DATE(:prefDate, 'YYYY-MM-DD'), :address)");
        oci_bind_by_name($stmt, ':orderId', $orderId);
        oci_bind_by_name($stmt, ':trackingNumber', $trackingNumber);
        oci_bind_by_name($stmt, ':prefDate', $preferredDateStr);
        oci_bind_by_name($stmt, ':address', $address);
        oci_execute($stmt, OCI_NO_AUTO_COMMIT);
    } else {
        $stmt = oci_parse($conn, "INSERT INTO PICKUP (ORDER_ID, DATE_TIME) VALUES (:orderId, TO_DATE(:prefDate, 'YYYY-MM-DD'))");
        oci_bind_by_name($stmt, ':orderId', $orderId);
        oci_bind_by_name($stmt, ':prefDate', $preferredDateStr);
        oci_execute($stmt, OCI_NO_AUTO_COMMIT);
    }
    
    oci_commit($conn);
    
    echo json_encode([
        'orderId' => $orderId,
        'status' => $status,
        'totalAmount' => $total
    ]);
    
} catch (Exception $e) {
    oci_rollback($conn);
    http_response_code(500);
    echo json_encode(['error' => 'Failed to create order: ' . $e->getMessage()]);
}
?>
