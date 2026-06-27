<?php
/* Author: Dayana Qistina Binti Mat Zake */
// api/update_profile.php
session_start();
require_once '../database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['cust_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Not authenticated.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
    exit;
}

$name = $_POST['CUST_NAME'] ?? '';
$phone = $_POST['CUST_NOPHONE'] ?? '';
$address1 = $_POST['CUST_ADDRESS1'] ?? '';
$address2 = $_POST['CUST_ADDRESS2'] ?? '';
$postcode = $_POST['CUST_POSTCODE'] ?? '';
$state = $_POST['CUST_STATE'] ?? '';

if (empty($name) || empty($phone)) {
    http_response_code(400);
    echo json_encode(['error' => 'Name and phone are required.']);
    exit;
}

try {
    $sql = "UPDATE CUSTOMER SET 
            CUST_NAME = :name, 
            CUST_NOPHONE = :phone, 
            CUST_ADDRESS1 = :address1, 
            CUST_ADDRESS2 = :address2, 
            CUST_POSTCODE = :postcode, 
            CUST_STATE = :state 
            WHERE CUST_ID = :id";
            
    $stmt = oci_parse($conn, $sql);
    oci_bind_by_name($stmt, ':name', $name);
    oci_bind_by_name($stmt, ':phone', $phone);
    oci_bind_by_name($stmt, ':address1', $address1);
    oci_bind_by_name($stmt, ':address2', $address2);
    oci_bind_by_name($stmt, ':postcode', $postcode);
    oci_bind_by_name($stmt, ':state', $state);
    oci_bind_by_name($stmt, ':id', $_SESSION['cust_id']);
    
    $result = oci_execute($stmt);
    
    if ($result) {
        // Update session name if changed
        $_SESSION['cust_name'] = $name;
        echo json_encode(['success' => true, 'message' => 'Profile updated successfully.']);
    } else {
        $e = oci_error($stmt);
        throw new Exception($e['message']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>
