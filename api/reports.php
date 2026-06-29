<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../database.php';

$type = $_GET['type'] ?? 'daily';

if ($type === 'monthly') {
    $groupBy = "TO_CHAR(o.ORDER_DATE, 'MM-YYYY')";
} elseif ($type === 'yearly') {
    $groupBy = "TO_CHAR(o.ORDER_DATE, 'YYYY')";
} else {
    $groupBy = "TO_CHAR(o.ORDER_DATE, 'DD-MM-YYYY')";
}

$sql = "
    SELECT
        $groupBy AS REPORT_PERIOD,
        COUNT(DISTINCT o.ORDER_ID) AS TOTAL_ORDERS,
        SUM(od.SUBTOTAL) AS TOTAL_SALES
    FROM ORDERS o
    JOIN ORDERDETAILS od ON o.ORDER_ID = od.ORDER_ID
    GROUP BY $groupBy
    ORDER BY REPORT_PERIOD
";

$stmt = oci_parse($conn, $sql);
oci_execute($stmt);

$sales = [];
while ($row = oci_fetch_assoc($stmt)) {
    $sales[] = [
        'period' => $row['REPORT_PERIOD'],
        'totalOrders' => (int)$row['TOTAL_ORDERS'],
        'totalSales' => (float)$row['TOTAL_SALES']
    ];
}

$bestSql = "
    SELECT *
    FROM (
        SELECT
            p.PRODUCT_NAME,
            SUM(od.QUANTITY) AS TOTAL_SOLD
        FROM ORDERDETAILS od
        JOIN PRODUCT p ON od.PRODUCT_ID = p.PRODUCT_ID
        GROUP BY p.PRODUCT_NAME
        ORDER BY TOTAL_SOLD DESC
    )
    WHERE ROWNUM <= 5
";

$bestStmt = oci_parse($conn, $bestSql);
oci_execute($bestStmt);

$bestSelling = [];
while ($row = oci_fetch_assoc($bestStmt)) {
    $bestSelling[] = [
        'productName' => $row['PRODUCT_NAME'],
        'totalSold' => (int)$row['TOTAL_SOLD']
    ];
}

echo json_encode([
    'sales' => $sales,
    'bestSelling' => $bestSelling
]);
?>