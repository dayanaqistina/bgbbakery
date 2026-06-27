<?php
/* Author: Dayana Qistina Binti Mat Zake */
// api/admin-logout.php
session_start();

// Unset all worker session variables
unset($_SESSION['worker_id']);
unset($_SESSION['worker_email']);
unset($_SESSION['worker_name']);
unset($_SESSION['worker_profile_image']);
unset($_SESSION['owner_id']);

header('Content-Type: application/json');
echo json_encode(['success' => true]);
?>
