<?php
// api/register.php
require_once '../database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
    exit;
}

$name = $_POST['CUST_NAME'] ?? '';
$phone = $_POST['CUST_NOPHONE'] ?? '';
$email = $_POST['CUST_EMAIL'] ?? '';
$password = $_POST['CUST_PASSWORD'] ?? '';
$address1 = $_POST['CUST_ADDRESS1'] ?? '';
$address2 = $_POST['CUST_ADDRESS2'] ?? '';
$postcode = $_POST['CUST_POSTCODE'] ?? '';
$state = $_POST['CUST_STATE'] ?? '';

if (empty($name) || empty($email) || empty($password) || empty($phone)) {
    http_response_code(400);
    echo json_encode(['error' => 'Name, email, password, and phone are required.']);
    exit;
}

// Hash the password securely
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

try {
    // Check if email already exists
    $stmt = oci_parse($conn, "SELECT CUST_ID FROM CUSTOMER WHERE CUST_EMAIL = :email");
    oci_bind_by_name($stmt, ':email', $email);
    oci_execute($stmt);
    
    if (oci_fetch_assoc($stmt)) {
        http_response_code(409);
        echo json_encode(['error' => 'Email already registered.']);
        exit;
    }

    // Generate new CUST_ID formatted as 00001, 00002...
    $id_stmt = oci_parse($conn, "SELECT NVL(MAX(TO_NUMBER(CUST_ID)), 0) + 1 AS NEXT_ID FROM CUSTOMER");
    oci_execute($id_stmt);
    $id_row = oci_fetch_assoc($id_stmt);
    $new_cust_id = str_pad($id_row['NEXT_ID'], 5, '0', STR_PAD_LEFT);

    $sql = "INSERT INTO CUSTOMER (CUST_ID, CUST_NAME, CUST_NOPHONE, CUST_EMAIL, CUST_PASSWORD, CUST_ADDRESS1, CUST_ADDRESS2, CUST_POSTCODE, CUST_STATE) 
            VALUES (:cust_id, :name, :phone, :email, :password, :address1, :address2, :postcode, :state)";
    
    $stmt = oci_parse($conn, $sql);
    oci_bind_by_name($stmt, ':cust_id', $new_cust_id);
    oci_bind_by_name($stmt, ':name', $name);
    oci_bind_by_name($stmt, ':phone', $phone);
    oci_bind_by_name($stmt, ':email', $email);
    oci_bind_by_name($stmt, ':password', $hashedPassword);
    oci_bind_by_name($stmt, ':address1', $address1);
    oci_bind_by_name($stmt, ':address2', $address2);
    oci_bind_by_name($stmt, ':postcode', $postcode);
    oci_bind_by_name($stmt, ':state', $state);
    
    $result = oci_execute($stmt);
    
    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Registration successful.']);
    } else {
        $e = oci_error($stmt);
        throw new Exception($e['message']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>
