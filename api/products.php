<?php
/* Author: Dayana Qistina Binti Mat Zake */
// api/products.php

header('Content-Type: application/json');

require_once __DIR__ . '/../database.php';

try {
    $stmt = oci_parse($conn, "SELECT * FROM PRODUCT");
    oci_execute($stmt);
    
    $result = [];
    while ($p = oci_fetch_assoc($stmt)) {
        $result[] = [
            'id' => $p['PRODUCT_ID'],
            'name' => $p['PRODUCT_NAME'],
            'description' => $p['PRODUCT_DESC'],
            'flavourTopping' => $p['FLAVOUR_TOPPING'],
            'stockQuantity' => null, // Not used in original, or nullable
            'price' => (float) $p['PRICE']
        ];
    }
    
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to fetch products: ' . $e->getMessage()]);
}
?>
