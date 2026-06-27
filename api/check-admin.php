<?php
/* Author: Dayana Qistina Binti Mat Zake */
// api/check-admin.php
session_start();

header('Content-Type: application/json');

if (isset($_SESSION['worker_id'])) {
    echo json_encode([
        'is_admin' => true, 
        'worker_id' => $_SESSION['worker_id'],
        'worker_name' => $_SESSION['worker_name'] ?? 'Admin',
        'worker_profile_image' => $_SESSION['worker_profile_image'] ?? null
    ]);
} else {
    echo json_encode(['is_admin' => false]);
}
?>
