<?php
/* Author: Dayana Qistina Binti Mat Zake */
// api/profile_details.php
session_start();
require_once '../database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['cust_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Not authenticated.']);
    exit;
}

try {
    $stmt = oci_parse($conn, "SELECT CUST_NAME, CUST_NOPHONE, CUST_EMAIL, CUST_ADDRESS1, CUST_ADDRESS2, CUST_POSTCODE, CUST_STATE FROM CUSTOMER WHERE CUST_ID = :id");
    oci_bind_by_name($stmt, ':id', $_SESSION['cust_id']);
    oci_execute($stmt);
    
    $user = oci_fetch_assoc($stmt);
    
    if ($user) {
        echo json_encode([
            'success' => true,
            'user' => [
                'name' => $user['CUST_NAME'],
                'phone' => $user['CUST_NOPHONE'],
                'email' => $user['CUST_EMAIL'],
                'address1' => $user['CUST_ADDRESS1'],
                'address2' => $user['CUST_ADDRESS2'],
                'postcode' => $user['CUST_POSTCODE'],
                'state' => $user['CUST_STATE']
            ]
        ]);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'User not found.']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>
