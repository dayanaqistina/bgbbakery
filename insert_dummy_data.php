<?php
/* Author: Dayana Qistina Binti Mat Zake */
require_once 'database.php';

try {
    $custId = '00001';
    $workerId = '2001'; 
    
    $ordersToInsert = [
        ['date' => date('Y-m-d'), 'status' => 'Pending', 'type' => 'Pickup', 'total' => 45.00],
        ['date' => date('Y-m-d'), 'status' => 'Preparing', 'type' => 'Delivery', 'total' => 120.00],
        ['date' => date('Y-m-d', strtotime('-1 days')), 'status' => 'Completed', 'type' => 'Pickup', 'total' => 75.50],
        ['date' => date('Y-m-d', strtotime('-2 days')), 'status' => 'Completed', 'type' => 'Delivery', 'total' => 200.00],
        ['date' => date('Y-m-d', strtotime('-3 days')), 'status' => 'Completed', 'type' => 'Pickup', 'total' => 30.00],
        ['date' => date('Y-m-d', strtotime('-4 days')), 'status' => 'Completed', 'type' => 'Delivery', 'total' => 95.00],
        ['date' => date('Y-m-d', strtotime('-5 days')), 'status' => 'Cancelled', 'type' => 'Pickup', 'total' => 60.00]
    ];

    foreach ($ordersToInsert as $o) {
        // Insert Order
        $stmt = oci_parse($conn, "INSERT INTO ORDERS (ORDER_DATE, ORDER_STATUS, ORDER_TYPE, CUST_ID, WORKER_ID) 
                                  VALUES (TO_DATE(:odate, 'YYYY-MM-DD'), :status, :otype, :custId, :workerId)");
        oci_bind_by_name($stmt, ':odate', $o['date']);
        oci_bind_by_name($stmt, ':status', $o['status']);
        oci_bind_by_name($stmt, ':otype', $o['type']);
        oci_bind_by_name($stmt, ':custId', $custId);
        oci_bind_by_name($stmt, ':workerId', $workerId);
        oci_execute($stmt);

        // Get the latest inserted ORDER_ID manually since RETURNING INTO might not work
        $getOid = oci_parse($conn, "SELECT MAX(ORDER_ID) FROM ORDERS");
        oci_execute($getOid);
        $row = oci_fetch_array($getOid, OCI_NUM);
        $orderId = $row[0];

        // Insert Order Details (dummy product)
        $productId = 1;
        $qty = 2;
        $det = oci_parse($conn, "INSERT INTO ORDERDETAILS (ORDER_ID, PRODUCT_ID, QUANTITY, SUBTOTAL) VALUES (:oid, :pid, :qty, :sub)");
        oci_bind_by_name($det, ':oid', $orderId);
        oci_bind_by_name($det, ':pid', $productId);
        oci_bind_by_name($det, ':qty', $qty);
        oci_bind_by_name($det, ':sub', $o['total']);
        oci_execute($det);
    }
    
    echo "Dummy data inserted successfully!\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
