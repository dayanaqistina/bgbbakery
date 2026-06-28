<?php
/* Author: Dayana Qistina Binti Mat Zake */
// api/logout.php
session_start();
session_unset();
session_destroy();

header('Content-Type: application/json');
echo json_encode(['success' => true, 'message' => 'Logged out successfully.']);
?>
