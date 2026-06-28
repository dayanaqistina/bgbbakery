<?php
/* Author: Dayana Qistina Binti Mat Zake */
require_once 'database.php';

try {
    // Delete the dummy data we just added
    oci_execute(oci_parse($conn, "DELETE FROM ORDERDETAILS WHERE PRODUCT_ID = 1 AND QUANTITY = 2"));
    oci_execute(oci_parse($conn, "DELETE FROM ORDERS WHERE CUST_ID = '00001' AND WORKER_ID = '2001'"));
    echo "Dummy data removed.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
