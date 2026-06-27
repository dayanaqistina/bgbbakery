<?php
/* Author: Dayana Qistina Binti Mat Zake */
require_once 'database.php';
try {
    $tables = ['ORDERS', 'ORDERDETAILS', 'PICKUP', 'DELIVERY'];
    foreach ($tables as $t) {
        $stmt = oci_parse($conn, "SELECT COUNT(*) FROM $t");
        oci_execute($stmt);
        $row = oci_fetch_array($stmt, OCI_NUM);
        echo "$t count: " . $row[0] . "\n";
        
        $stmt2 = oci_parse($conn, "SELECT * FROM $t FETCH FIRST 2 ROWS ONLY");
        oci_execute($stmt2);
        while ($r = oci_fetch_assoc($stmt2)) {
            print_r($r);
        }
        echo "----------------------\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
