<?php
// api/customers.php

header('Content-Type: application/json');

require_once __DIR__ . '/../database.php';

try {
    $stmt = oci_parse($conn, "SELECT * FROM CUSTOMER");
    oci_execute($stmt);
    
    $result = [];
    while ($c = oci_fetch_assoc($stmt)) {
        // Find orders for this customer to nest them as the Spring Boot entity might do, 
        // but looking at Customer.java it returns them if fetched, though usually DTOs omit it or break cycles.
        // Let's just return basic info
        $result[] = [
            'id' => $c['CUST_ID'],
            'name' => $c['CUST_NAME'],
            'phoneNumber' => $c['CUST_NOPHONE'],
            'email' => $c['CUST_EMAIL']
        ];
    }
    
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to fetch customers: ' . $e->getMessage()]);
}
?>
