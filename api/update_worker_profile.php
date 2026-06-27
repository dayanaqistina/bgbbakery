<?php
// api/update_worker_profile.php
session_start();
require_once '../database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['worker_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
    exit;
}

$worker_id = $_SESSION['worker_id'];
$name = $_POST['WORKER_NAME'] ?? '';
$email = $_POST['WORKER_EMAIL'] ?? '';
$phone = $_POST['WORKER_NOPHONE'] ?? '';
$password = $_POST['WORKER_PASSWORD'] ?? '';

if (empty($name) || empty($email) || empty($phone)) {
    http_response_code(400);
    echo json_encode(['error' => 'Name, email, and phone are required.']);
    exit;
}

$dbPath = null;

// Handle optional image upload
if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
    $file = $_FILES['profile_image'];
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($file['type'], $allowedTypes)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid file type. Only JPG, PNG, GIF, WEBP are allowed.']);
        exit;
    }

    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'worker_' . $worker_id . '_' . time() . '.' . $ext;
    $uploadDir = '../images/worker_profiles/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    $uploadPath = $uploadDir . $filename;
    $dbPath = 'images/worker_profiles/' . $filename;

    if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to save uploaded image.']);
        exit;
    }
}

try {
    // Build update query dynamically
    $query = "UPDATE WORKER SET WORKER_NAME = :name, WORKER_EMAIL = :email, WORKER_NOPHONE = :phone";
    if (!empty($password)) {
        $query .= ", WORKER_PASSWORD = :password";
    }
    if ($dbPath) {
        $query .= ", PROFILE_IMAGE = :img";
    }
    $query .= " WHERE WORKER_ID = :id";
    
    $stmt = oci_parse($conn, $query);
    oci_bind_by_name($stmt, ':name', $name);
    oci_bind_by_name($stmt, ':email', $email);
    oci_bind_by_name($stmt, ':phone', $phone);
    oci_bind_by_name($stmt, ':id', $worker_id);
    
    if (!empty($password)) {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        oci_bind_by_name($stmt, ':password', $hashed);
    }
    if ($dbPath) {
        oci_bind_by_name($stmt, ':img', $dbPath);
    }

    if (oci_execute($stmt, OCI_COMMIT_ON_SUCCESS)) {
        $_SESSION['worker_name'] = $name;
        $_SESSION['worker_email'] = $email;
        if ($dbPath) {
            $_SESSION['worker_profile_image'] = $dbPath;
        }
        echo json_encode(['success' => true, 'message' => 'Profile updated successfully']);
    } else {
        $e = oci_error($stmt);
        http_response_code(500);
        echo json_encode(['error' => 'Database error: ' . $e['message']]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}
?>
