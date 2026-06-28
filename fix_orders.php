<?php
/* Author: Dayana Qistina Binti Mat Zake */
require_once 'database.php';

try {
    echo "Fixing ORDERS table...\n";
    
    // 1. Get Pickups
    $stmt = oci_parse($conn, "SELECT ORDER_ID, DATE_TIME FROM PICKUP");
    oci_execute($stmt);
    while ($row = oci_fetch_assoc($stmt)) {
        $oid = $row['ORDER_ID'];
        $dt = $row['DATE_TIME'];
        
        $chk = oci_parse($conn, "SELECT COUNT(*) FROM ORDERS WHERE ORDER_ID = :oid");
        oci_bind_by_name($chk, ':oid', $oid);
        oci_execute($chk);
        $r = oci_fetch_array($chk);
        if ($r[0] == 0) {
            $ins = oci_parse($conn, "INSERT INTO ORDERS (ORDER_ID, ORDER_DATE, ORDER_STATUS, ORDER_TYPE, CUST_ID, WORKER_ID) 
                                     VALUES (:oid, TO_TIMESTAMP(:dt, 'DD-MON-RR HH.MI.SSXFF AM'), 'Completed', 'Pickup', '00002', 2001)");
            oci_bind_by_name($ins, ':oid', $oid);
            oci_bind_by_name($ins, ':dt', $dt);
            oci_execute($ins);
            echo "Inserted missing Pickup order $oid\n";
        }
    }
    
    // 2. Get Deliveries
    $stmt = oci_parse($conn, "SELECT ORDER_ID, DATE_TIME FROM DELIVERY");
    oci_execute($stmt);
    while ($row = oci_fetch_assoc($stmt)) {
        $oid = $row['ORDER_ID'];
        $dt = $row['DATE_TIME'];
        
        $chk = oci_parse($conn, "SELECT COUNT(*) FROM ORDERS WHERE ORDER_ID = :oid");
        oci_bind_by_name($chk, ':oid', $oid);
        oci_execute($chk);
        $r = oci_fetch_array($chk);
        if ($r[0] == 0) {
            $ins = oci_parse($conn, "INSERT INTO ORDERS (ORDER_ID, ORDER_DATE, ORDER_STATUS, ORDER_TYPE, CUST_ID, WORKER_ID) 
                                     VALUES (:oid, TO_TIMESTAMP(:dt, 'DD-MON-RR HH.MI.SSXFF AM'), 'Completed', 'Delivery', '00002', 2001)");
            oci_bind_by_name($ins, ':oid', $oid);
            oci_bind_by_name($ins, ':dt', $dt);
            oci_execute($ins);
            echo "Inserted missing Delivery order $oid\n";
        }
    }
    
    echo "Done repairing ORDERS table!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
