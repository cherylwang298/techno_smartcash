<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $business_id = $_POST['business_id'] ?? 0;
    $real_stock_array = $_POST['real_stock'] ?? [];

    if ($business_id > 0 && !empty($real_stock_array)) {
        foreach ($real_stock_array as $product_id => $jumlah) {
            if ($jumlah !== "" && is_numeric($jumlah)) {
                $jumlah_int = (int)$jumlah;
                $sql = "UPDATE products SET real_stock = ? WHERE id = ? AND business_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("iii", $jumlah_int, $product_id, $business_id);
                $stmt->execute();
            }
        }
        echo "success";
    } else {
        echo "empty";
    }
}
?>