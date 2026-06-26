<?php
// init_db.php

require_once __DIR__ . '/database.php';

echo "Initializing database...\n";

$schema = "
CREATE TABLE IF NOT EXISTS PRODUCT (
    PRODUCT_ID INTEGER PRIMARY KEY AUTOINCREMENT,
    PRODUCT_NAME VARCHAR(50) NOT NULL,
    PRODUCT_DESC VARCHAR(200) NOT NULL,
    FLAVOUR_TOPPING VARCHAR(100),
    PRICE DECIMAL(8,2) NOT NULL
);

CREATE TABLE IF NOT EXISTS CUSTOMER (
    CUST_ID INTEGER PRIMARY KEY AUTOINCREMENT,
    CUST_NAME VARCHAR(50) NOT NULL,
    CUST_NOPHONE VARCHAR(15) NOT NULL,
    CUST_EMAIL VARCHAR(50) NOT NULL
);

CREATE TABLE IF NOT EXISTS WORKER (
    WORKER_ID INTEGER PRIMARY KEY AUTOINCREMENT,
    WORKER_NAME VARCHAR(50) NOT NULL,
    WORKER_NOPHONE VARCHAR(15) NOT NULL
);

CREATE TABLE IF NOT EXISTS ORDERS (
    ORDER_ID INTEGER PRIMARY KEY AUTOINCREMENT,
    ORDER_DATE DATE NOT NULL,
    ORDER_STATUS VARCHAR(256) NOT NULL,
    ORDER_TYPE VARCHAR(256) NOT NULL,
    CUST_ID INTEGER NOT NULL,
    WORKER_ID INTEGER NOT NULL,
    FOREIGN KEY(CUST_ID) REFERENCES CUSTOMER(CUST_ID),
    FOREIGN KEY(WORKER_ID) REFERENCES WORKER(WORKER_ID)
);

CREATE TABLE IF NOT EXISTS ORDER_DETAILS (
    ID INTEGER PRIMARY KEY AUTOINCREMENT,
    ORDER_ID INTEGER NOT NULL,
    PRODUCT_ID INTEGER NOT NULL,
    QUANTITY INTEGER NOT NULL,
    SUBTOTAL DECIMAL(8,2) NOT NULL,
    FOREIGN KEY(ORDER_ID) REFERENCES ORDERS(ORDER_ID),
    FOREIGN KEY(PRODUCT_ID) REFERENCES PRODUCT(PRODUCT_ID)
);

CREATE TABLE IF NOT EXISTS DELIVERY (
    ID INTEGER PRIMARY KEY AUTOINCREMENT,
    ORDER_ID INTEGER NOT NULL,
    TRACKING_NUMBER VARCHAR(255),
    DATE_TIME DATETIME,
    ADDRESS VARCHAR(255),
    FOREIGN KEY(ORDER_ID) REFERENCES ORDERS(ORDER_ID)
);

CREATE TABLE IF NOT EXISTS PICKUP (
    ID INTEGER PRIMARY KEY AUTOINCREMENT,
    ORDER_ID INTEGER NOT NULL,
    DATE_TIME DATETIME,
    FOREIGN KEY(ORDER_ID) REFERENCES ORDERS(ORDER_ID)
);
";

try {
    $pdo->exec($schema);
    echo "Schema created successfully.\n";
    
    // Check if we have products
    $stmt = $pdo->query("SELECT COUNT(*) FROM PRODUCT");
    $count = $stmt->fetchColumn();
    
    if ($count == 0) {
        echo "Inserting sample products...\n";
        $products = [
            ['Fudgy Brownies', 'Rich, dense, and fudgy chocolate brownies.', 'Chocolate', 45.00],
            ['Banana Muffins', 'Moist banana muffins with a hint of cinnamon.', 'Banana', 30.00],
            ['Orange Buttercake', 'Classic buttery cake with fresh orange zest.', 'Orange', 55.00],
            ['Chocolate Cupcakes', 'Decadent chocolate cupcakes with frosting.', 'Chocolate', 40.00],
            ['Classic Cheesecake', 'Creamy New York style cheesecake.', 'Cheese', 85.00],
            ['Carrot Cake', 'Moist spiced carrot cake with cream cheese frosting.', 'Carrot', 75.00]
        ];
        
        $stmt = $pdo->prepare("INSERT INTO PRODUCT (PRODUCT_NAME, PRODUCT_DESC, FLAVOUR_TOPPING, PRICE) VALUES (?, ?, ?, ?)");
        foreach ($products as $p) {
            $stmt->execute($p);
        }
        echo "Sample products inserted.\n";
    }
    
    // Check if we have a default worker
    $stmt = $pdo->query("SELECT COUNT(*) FROM WORKER");
    $count = $stmt->fetchColumn();
    
    if ($count == 0) {
        $pdo->exec("INSERT INTO WORKER (WORKER_NAME, WORKER_NOPHONE) VALUES ('Default Worker', '0000000000')");
        echo "Default worker inserted.\n";
    }
    
    echo "Database initialization complete.\n";
    
} catch (PDOException $e) {
    echo "Error initializing database: " . $e->getMessage() . "\n";
}
?>
