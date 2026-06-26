<?php
// api/orders.php

header('Content-Type: application/json');

require_once __DIR__ . '/../database.php';

try {
    $stmt = oci_parse($conn, "
        SELECT o.ORDER_ID, o.ORDER_DATE, o.ORDER_STATUS, o.ORDER_TYPE, 
               c.CUST_NAME, c.CUST_NOPHONE, c.CUST_EMAIL
        FROM ORDERS o
        JOIN CUSTOMER c ON o.CUST_ID = c.CUST_ID
    ");
    oci_execute($stmt);
    
    $result = [];
    while ($o = oci_fetch_assoc($stmt)) {
        $result[] = [
            'id' => $o['ORDER_ID'],
            'orderDate' => $o['ORDER_DATE'],
            'status' => $o['ORDER_STATUS'],
            'orderType' => $o['ORDER_TYPE'],
            'customer' => [
                'name' => $o['CUST_NAME'],
                'phoneNumber' => $o['CUST_NOPHONE'],
                'email' => $o['CUST_EMAIL']
            ]
        ];
    }
    
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to fetch orders: ' . $e->getMessage()]);
}
?>
