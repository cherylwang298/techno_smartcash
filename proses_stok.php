<?php

// echo "MASUK PROSES_STOK";
// echo "<pre>";
// print_r($_POST);
// print_r($_FILES);
// exit;

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
    // MENANGKAP MIN_STOCK DARI FORM
    $min_stock = $_POST['min_stock'] ?? 5; 

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
    // MENAMBAHKAN min_stock KE QUERY INSERT
    $sql = "INSERT INTO products
            (business_id, name, category, buy_price, sell_price, stock, min_stock, image_path)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    // Tambah satu 'i' untuk min_stock -> "issddiis"
    $stmt->bind_param(
        "issddiis",
        $biz_id,
        $name,
        $category,
        $buy_price,
        $sell_price,
        $stock,
        $min_stock,
        $image_path
    );

    if (!$stmt->execute()) {
        die("Error: " . $stmt->error);
    }

    // Catat pengeluaran pembelian stok
    $nominal_pengeluaran = $buy_price * $stock;
    $keterangan = "Pembelian stok awal: " . $name;

    $sql_trans = "
        INSERT INTO transactions
        (business_id, type, nominal, description, created_at)
        VALUES (?, 'Pengeluaran', ?, ?, NOW())
    ";

    $stmt_trans = $conn->prepare($sql_trans);
    $stmt_trans->bind_param(
        "ids",
        $biz_id,
        $nominal_pengeluaran,
        $keterangan
    );

    if (!$stmt_trans->execute()) {
        die("Error transaksi: " . $stmt_trans->error);
    }

    header("Location: stok.php?status=success");
    exit;

    } else {

        $getOld = $conn->prepare("
        SELECT stock
        FROM products
        WHERE id = ? AND business_id = ?
        ");
        $getOld->bind_param("ii", $product_id, $biz_id);
        $getOld->execute();

        $oldData = $getOld->get_result()->fetch_assoc();

        $oldStock = $oldData['stock'];
        $newStock = $stock;

        if ($newStock > $oldStock) {

            $selisih = $newStock - $oldStock;
            $nominal_pengeluaran = $selisih * $buy_price;
            $keterangan = "Restock produk: " . $name;

            $stmt_trans = $conn->prepare("
                INSERT INTO transactions
                (
                    business_id,
                    product_id,
                    qty,
                    type,
                    nominal,
                    description
                )
                VALUES (?, ?, ?, 'Pengeluaran', ?, ?)
            ");

            $stmt_trans->bind_param(
                "iiids",
                $biz_id,
                $product_id,
                $selisih,
                $nominal_pengeluaran,
                $keterangan
            );

            $stmt_trans->execute();
        }

        if ($image_path) {
            // Jika ganti foto, tambahkan min_stock
            $sql = "UPDATE products SET name=?, category=?, buy_price=?, sell_price=?, stock=?, min_stock=?, image_path=? WHERE id=? AND business_id=?";
            $stmt = $conn->prepare($sql);
            // Tambah 'i' untuk min_stock -> "ssddiisii"
            $stmt->bind_param("ssddiisii", $name, $category, $buy_price, $sell_price, $stock, $min_stock, $image_path, $product_id, $biz_id);
        } else {
            // Jika tidak ganti foto, tambahkan min_stock
            $sql = "UPDATE products SET name=?, category=?, buy_price=?, sell_price=?, stock=?, min_stock=? WHERE id=? AND business_id=?";
            $stmt = $conn->prepare($sql);
            // Tambah 'i' untuk min_stock -> "ssddiiii"
            $stmt->bind_param("ssddiiii", $name, $category, $buy_price, $sell_price, $stock, $min_stock, $product_id, $biz_id);
        }
    }

    if ($stmt->execute()) {
        header("Location: stok.php?status=success");
    } else {
        echo "Error: " . $stmt->error;
    }
    exit;
}