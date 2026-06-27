<?php
require_once 'database.php';
$stmt = oci_parse($conn, "SELECT WORKER_ID FROM WORKER FETCH FIRST 1 ROWS ONLY"); oci_execute($stmt); print_r(oci_fetch_assoc($stmt));
$stmt = oci_parse($conn, "SELECT CUST_ID FROM CUSTOMER FETCH FIRST 1 ROWS ONLY"); oci_execute($stmt); print_r(oci_fetch_assoc($stmt));
$stmt = oci_parse($conn, "SELECT PRODUCT_ID FROM PRODUCT FETCH FIRST 1 ROWS ONLY"); oci_execute($stmt); print_r(oci_fetch_assoc($stmt));
