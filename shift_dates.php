<?php
/* Author: Dayana Qistina Binti Mat Zake */
require_once 'database.php';
// Add 1 year and 25 days to bring dates from 2025-05/06 to 2026-06/07. Actually, just add 1 year (365 days).
$queries = [
    "UPDATE ORDERS SET ORDER_DATE = ORDER_DATE + 390",
    "UPDATE PICKUP SET DATE_TIME = DATE_TIME + 390",
    "UPDATE DELIVERY SET DATE_TIME = DATE_TIME + 390"
];
foreach ($queries as $q) {
    $stmt = oci_parse($conn, $q);
    if (!oci_execute($stmt)) {
        $e = oci_error($stmt);
        echo "Error: " . $e['message'] . "\n";
    }
}
echo "Dates shifted forward by 390 days (roughly 13 months).\n";
?>
