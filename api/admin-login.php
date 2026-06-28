<?php
/* Author: Dayana Qistina Binti Mat Zake */
// api/admin-login.php
session_start();
require_once '../database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
    exit;
}

$email = $_POST['WORKER_EMAIL'] ?? '';
$password = $_POST['WORKER_PASSWORD'] ?? '';

if (empty($email) || empty($password)) {
    http_response_code(400);
    echo json_encode(['error' => 'Email and password are required.']);
    exit;
}

try {
    $stmt = oci_parse($conn, "SELECT * FROM WORKER WHERE WORKER_EMAIL = :email");
    oci_bind_by_name($stmt, ':email', $email);
    oci_execute($stmt);
    
    $user = oci_fetch_assoc($stmt);

    // Support both hashed passwords (preferred) and plain text (fallback for dev)
    if ($user && (password_verify($password, $user['WORKER_PASSWORD']) || $password === $user['WORKER_PASSWORD'])) {
        // Success
        $_SESSION['worker_id'] = $user['WORKER_ID'];
        $_SESSION['worker_email'] = $user['WORKER_EMAIL'];
        $_SESSION['worker_name'] = $user['WORKER_NAME'];
        $_SESSION['worker_profile_image'] = $user['PROFILE_IMAGE'] ?? null;
        if (isset($user['OWNER_ID'])) {
            $_SESSION['owner_id'] = $user['OWNER_ID'];
        }
        
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
