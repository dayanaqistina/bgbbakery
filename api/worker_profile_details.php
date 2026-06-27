<?php
// api/worker_profile_details.php
session_start();
require_once '../database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['worker_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$worker_id = $_SESSION['worker_id'];

try {
    $stmt = oci_parse($conn, "SELECT WORKER_NAME, WORKER_EMAIL, WORKER_NOPHONE, PROFILE_IMAGE FROM WORKER WHERE WORKER_ID = :id");
    oci_bind_by_name($stmt, ':id', $worker_id);
    oci_execute($stmt);
    
    $worker = oci_fetch_assoc($stmt);
    if ($worker) {
        echo json_encode([
            'success' => true,
            'worker' => [
                'name' => $worker['WORKER_NAME'] ?? '',
                'email' => $worker['WORKER_EMAIL'] ?? '',
                'phone' => $worker['WORKER_NOPHONE'] ?? '',
                'profile_image' => $worker['PROFILE_IMAGE'] ?? 'images/IMG_7322-removebg-preview.png'
            ]
        ]);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Worker not found']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>
