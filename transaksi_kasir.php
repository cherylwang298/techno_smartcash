<?php
ob_start(); // Tahan output biar gak ada spasi/error yang bocor
session_start();
include 'db.php';

ob_clean(); // Bersihkan semua output yang gak sengaja tercetak
header('Content-Type: application/json');

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    echo json_encode(['success' => false, 'message' => 'Sesi login telah habis.']);
    exit;
}

// Ambil business_id
$sql = "SELECT id FROM businesses WHERE user_id = ? LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$biz = $stmt->get_result()->fetch_assoc();

if (!$biz) {
    echo json_encode(['success' => false, 'message' => 'Profil bisnis tidak ditemukan.']);
    exit;
}

$business_id = $biz['id'];

// Ambil data JSON dari Javascript
$data = json_decode(file_get_contents("php://input"), true);
$cart = $data['cart'] ?? [];
$method = $data['method'] ?? 'Tunai';

if (empty($cart)) {
    echo json_encode(['success' => false, 'message' => 'Keranjang masih kosong.']);
    exit;
}

// KELOMPOKKAN BARANG (Biar database gak kerja rodi)
$grouped_cart = [];
foreach ($cart as $item) {
    $id = $item['id'];
    if (!isset($grouped_cart[$id])) {
        $grouped_cart[$id] = [
            'id' => $id,
            'price' => $item['price'],
            'qty' => 0
        ];
    }
    $grouped_cart[$id]['qty'] += 1;
}

// Kunci database untuk transaksi
$conn->begin_transaction();

try {
    $total = 0;
    $total_items = 0;

    foreach ($grouped_cart as $item) {
        $product_id = $item['id'];
        $price = $item['price'];
        $qty = $item['qty'];

        // Cek stok
        $check = $conn->prepare("SELECT stock FROM products WHERE id = ? AND business_id = ? FOR UPDATE");
        $check->bind_param("ii", $product_id, $business_id);
        $check->execute();
        $result = $check->get_result()->fetch_assoc();

        if (!$result || $result['stock'] < $qty) {
            throw new Exception("Stok produk tidak cukup untuk jumlah yang dibeli.");
        }

        // Potong stok dan tambah jumlah terjual
        $update = $conn->prepare("UPDATE products SET stock = stock - ?, sold_count = sold_count + ? WHERE id = ? AND business_id = ?");
        $update->bind_param("iiii", $qty, $qty, $product_id, $business_id);
        $update->execute();

        $total += ($price * $qty);
        $total_items += $qty;
    }

    // Catat ke transaksi dengan deskripsi metode pembayaran
    $desc = "Penjualan kasir ($total_items item) via $method";
    $insert = $conn->prepare("INSERT INTO transactions (business_id, type, nominal, description, created_at) VALUES (?, 'Pemasukan', ?, ?, NOW())");
    $insert->bind_param("ids", $business_id, $total, $desc);
    $insert->execute();

    // Permanenkan simpanan
    $conn->commit();
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    $conn->rollback(); // Batalkan semua kalau ada yang error
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>