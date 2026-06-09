<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action_type']; 
    $biz_id = $_POST['business_id'];
    $product_id = $_POST['product_id'] ?? null;
    
    $name = $_POST['name'];
    $category = $_POST['category'];
    $buy_price = $_POST['purchase_price']; 
    $sell_price = $_POST['price'];        
    $stock = $_POST['stock'];

    $image_path = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $target_dir = "uploads/products/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $ext = pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION);
        $file_name = time() . '_' . uniqid() . '.' . $ext;
        $target_file = $target_dir . $file_name;

        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            $image_path = $target_file;
        }
    }

    if ($action == 'add') {
        $sql = "INSERT INTO products (business_id, name, category, buy_price, sell_price, stock, image_path) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("issddis", $biz_id, $name, $category, $buy_price, $sell_price, $stock, $image_path);
    } else {
        if ($image_path) {
            // Jika ganti foto
            $sql = "UPDATE products SET name=?, category=?, buy_price=?, sell_price=?, stock=?, image_path=? WHERE id=? AND business_id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssddisii", $name, $category, $buy_price, $sell_price, $stock, $image_path, $product_id, $biz_id);
        } else {
            $sql = "UPDATE products SET name=?, category=?, buy_price=?, sell_price=?, stock=? WHERE id=? AND business_id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssddiii", $name, $category, $buy_price, $sell_price, $stock, $product_id, $biz_id);
        }
    }

    if ($stmt->execute()) {
        header("Location: stok.php?status=success");
    } else {
        echo "Error: " . $stmt->error;
    }
    exit;
}