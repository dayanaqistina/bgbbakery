<?php
require_once 'database.php';
try {
    $tables = ['PICKUP', 'DELIVERY'];
    foreach ($tables as $t) {
        $stmt = oci_parse($conn, "SELECT column_name, data_type FROM user_tab_columns WHERE table_name = '$t'");
        oci_execute($stmt);
        echo "$t columns:\n";
        while ($row = oci_fetch_assoc($stmt)) {
            echo $row['COLUMN_NAME'] . " (" . $row['DATA_TYPE'] . ")\n";
        }
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
