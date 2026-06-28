<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../database.php';

$action = $_GET['action'] ?? 'get';

try {

    // =========================
    // GET ALL CUSTOMERS
    // =========================
    if ($action == 'get') {

        $stmt = oci_parse($conn, "SELECT * FROM CUSTOMER ORDER BY CUST_ID");
        oci_execute($stmt);

        $result = [];

        while ($row = oci_fetch_assoc($stmt)) {
            $result[] = [
                'id' => $row['CUST_ID'],
                'name' => $row['CUST_NAME'],
                'phoneNumber' => $row['CUST_NOPHONE'],
                'email' => $row['CUST_EMAIL'],
                'address1' => $row['CUST_ADDRESS1'],
                'address2' => $row['CUST_ADDRESS2'],
                'postcode' => $row['CUST_POSTCODE'],
                'state' => $row['CUST_STATE']
            ];
        }

        echo json_encode($result);
        exit;
    }


    // =========================
    // ADD CUSTOMER
    // =========================
    if ($action == 'add') {

        $data = json_decode(file_get_contents("php://input"), true);

        $sql = "INSERT INTO CUSTOMER 
                (CUST_ID, CUST_NAME, CUST_NOPHONE, CUST_EMAIL, CUST_PASSWORD, CUST_ADDRESS1, CUST_ADDRESS2, CUST_POSTCODE, CUST_STATE)
                VALUES 
                (:id, :name, :phone, :email, :password, :addr1, :addr2, :postcode, :state)";

        $stmt = oci_parse($conn, $sql);

        oci_bind_by_name($stmt, ":id", $data['id']);
        oci_bind_by_name($stmt, ":name", $data['name']);
        oci_bind_by_name($stmt, ":phone", $data['phone']);
        oci_bind_by_name($stmt, ":email", $data['email']);
        oci_bind_by_name($stmt, ":password", $data['password']);
        oci_bind_by_name($stmt, ":addr1", $data['address1']);
        oci_bind_by_name($stmt, ":addr2", $data['address2']);
        oci_bind_by_name($stmt, ":postcode", $data['postcode']);
        oci_bind_by_name($stmt, ":state", $data['state']);

        oci_execute($stmt);

        echo json_encode(["message" => "Customer added successfully"]);
        exit;
    }


    // =========================
    // UPDATE CUSTOMER
    // =========================
    if ($action == 'update') {

        $data = json_decode(file_get_contents("php://input"), true);

        $sql = "UPDATE CUSTOMER SET
                    CUST_NAME = :name,
                    CUST_NOPHONE = :phone,
                    CUST_EMAIL = :email,
                    CUST_ADDRESS1 = :addr1,
                    CUST_ADDRESS2 = :addr2,
                    CUST_POSTCODE = :postcode,
                    CUST_STATE = :state
                WHERE CUST_ID = :id";

        $stmt = oci_parse($conn, $sql);

        oci_bind_by_name($stmt, ":id", $data['id']);
        oci_bind_by_name($stmt, ":name", $data['name']);
        oci_bind_by_name($stmt, ":phone", $data['phone']);
        oci_bind_by_name($stmt, ":email", $data['email']);
        oci_bind_by_name($stmt, ":addr1", $data['address1']);
        oci_bind_by_name($stmt, ":addr2", $data['address2']);
        oci_bind_by_name($stmt, ":postcode", $data['postcode']);
        oci_bind_by_name($stmt, ":state", $data['state']);

        oci_execute($stmt);

        echo json_encode(["message" => "Customer updated successfully"]);
        exit;
    }


    // =========================
    // DELETE CUSTOMER
    // =========================
    if ($action == 'delete') {

        $data = json_decode(file_get_contents("php://input"), true);

        $stmt = oci_parse($conn, "DELETE FROM CUSTOMER WHERE CUST_ID = :id");
        oci_bind_by_name($stmt, ":id", $data['id']);

        oci_execute($stmt);

        echo json_encode(["message" => "Customer deleted successfully"]);
        exit;
    }


    // =========================
    // INVALID ACTION
    // =========================
    echo json_encode(["error" => "Invalid action"]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}
?>