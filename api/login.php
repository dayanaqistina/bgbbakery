<?php
// api/login.php
session_start();
require_once '../database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
    exit;
}

$email = $_POST['CUST_EMAIL'] ?? '';
$password = $_POST['CUST_PASSWORD'] ?? '';

if (empty($email) || empty($password)) {
    http_response_code(400);
    echo json_encode(['error' => 'Email and password are required.']);
    exit;
}

try {
    $stmt = oci_parse($conn, "SELECT * FROM CUSTOMER WHERE CUST_EMAIL = :email");
    oci_bind_by_name($stmt, ':email', $email);
    oci_execute($stmt);
    
    $user = oci_fetch_assoc($stmt);

    if ($user && password_verify($password, $user['CUST_PASSWORD'])) {
        // Success
        $_SESSION['cust_id'] = $user['CUST_ID'];
        $_SESSION['cust_email'] = $user['CUST_EMAIL'];
        $_SESSION['cust_name'] = $user['CUST_NAME'];
        
        echo json_encode(['success' => true, 'message' => 'Logged in successfully.']);
    } else {
        http_response_code(401);
        echo json_encode(['error' => 'Invalid email or password.']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>
