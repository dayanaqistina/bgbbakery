<?php
// api/upload-worker-profile.php
session_start();
require_once '../database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['worker_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_FILES['profile_image'])) {
    http_response_code(400);
    echo json_encode(['error' => 'No image uploaded']);
    exit;
}

$file = $_FILES['profile_image'];
$worker_id = $_SESSION['worker_id'];

// Check for upload errors
if ($file['error'] !== UPLOAD_ERR_OK) {
    http_response_code(500);
    echo json_encode(['error' => 'File upload error code: ' . $file['error']]);
    exit;
}

// Check file type
$allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
if (!in_array($file['type'], $allowedTypes)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid file type. Only JPG, PNG, GIF, WEBP are allowed.']);
    exit;
}

// Create unique filename
$ext = pathinfo($file['name'], PATHINFO_EXTENSION);
$filename = 'worker_' . $worker_id . '_' . time() . '.' . $ext;
$uploadDir = '../images/worker_profiles/';
$uploadPath = $uploadDir . $filename;
$dbPath = 'images/worker_profiles/' . $filename; // Relative path for DB

if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to save uploaded file']);
    exit;
}

// Update database
try {
    $stmt = oci_parse($conn, "UPDATE WORKER SET PROFILE_IMAGE = :img WHERE WORKER_ID = :id");
    oci_bind_by_name($stmt, ':img', $dbPath);
    oci_bind_by_name($stmt, ':id', $worker_id);
    
    if (oci_execute($stmt, OCI_COMMIT_ON_SUCCESS)) {
        // Update session
        $_SESSION['worker_profile_image'] = $dbPath;
        
        echo json_encode([
            'success' => true, 
            'message' => 'Profile image updated',
            'image_url' => $dbPath
        ]);
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
