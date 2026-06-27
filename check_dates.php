<?php
require_once 'database.php';
$stmt = oci_parse($conn, "SELECT TO_CHAR(ORDER_DATE, 'YYYY-MM-DD') as D, COUNT(*) FROM ORDERS GROUP BY TO_CHAR(ORDER_DATE, 'YYYY-MM-DD') ORDER BY D DESC");
oci_execute($stmt);
while ($row = oci_fetch_assoc($stmt)) {
    echo $row['D'] . " : " . $row['COUNT(*)'] . "\n";
}
?>
