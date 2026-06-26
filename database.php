<?php
// database.php

// Connect to Oracle using OCI8
// The Service Name is FREEPDB1 based on the provided connection details
$dsn = "localhost:1521/FREEPDB1";
$username = "test";
$password = "system";

// Set Oracle Instant Client paths for macOS
// Using project-local copy so Apache's daemon user can access it
$oracle_ic = __DIR__ . '/oracle/instantclient_19_8';
putenv("TNS_ADMIN=" . $oracle_ic . "/network/admin");

$conn = @oci_connect($username, $password, $dsn, 'AL32UTF8');

if (!$conn) {
    $e = oci_error();
    $errorMessage = $e ? $e['message'] : 'Oracle environment initialization failed (ORA-01804). Please check Oracle Instant Client paths.';
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed: ' . $errorMessage]);
    exit;
}
?>

