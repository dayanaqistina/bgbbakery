<?php
/* Author: Dayana Qistina Binti Mat Zake */
// api/check_auth.php
session_start();

header('Content-Type: application/json');

if (isset($_SESSION['cust_id'])) {
    echo json_encode([
        'authenticated' => true,
        'user' => [
            'id' => $_SESSION['cust_id'],
            'email' => $_SESSION['cust_email'],
            'name' => $_SESSION['cust_name']
        ]
    ]);
} else {
    echo json_encode([
        'authenticated' => false
    ]);
}
?>
