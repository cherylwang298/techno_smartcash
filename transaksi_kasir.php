<?php
session_start();
include 'db.php';

header('Content-Type: application/json');

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Ambil business_id
$sql = "SELECT id FROM businesses WHERE user_id = ? LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$biz = $stmt->get_result()->fetch_assoc();

if (!$biz) {
    echo json_encode(['success' => false, 'message' => 'Business tidak ditemukan']);
    exit;
}

$business_id = $biz['id'];

// Ambil data JSON dari JS
$data = json_decode(file_get_contents("php://input"), true);
$cart = $data['cart'] ?? [];

if (empty($cart)) {
    echo json_encode(['success' => false, 'message' => 'Cart kosong']);
    exit;
}

$conn->begin_transaction();

try {
    $total = 0;

    foreach ($cart as $item) {
        $product_id = $item['id'];
        $price = $item['price'];

        // Ambil stock sekarang
        $check = $conn->prepare("SELECT stock FROM products WHERE id = ? AND business_id = ?");
        $check->bind_param("ii", $product_id, $business_id);
        $check->execute();
        $result = $check->get_result()->fetch_assoc();

        if (!$result || $result['stock'] <= 0) {
            throw new Exception("Stock produk tidak cukup");
        }

        // Kurangi stock & tambah sold_count
        $update = $conn->prepare("
            UPDATE products 
            SET stock = stock - 1,
                sold_count = sold_count + 1
            WHERE id = ? AND business_id = ?
        ");
        $update->bind_param("ii", $product_id, $business_id);
        $update->execute();

        $total += $price;
    }

    // Insert ke transactions
    $desc = "Penjualan kasir (" . count($cart) . " item)";
    $insert = $conn->prepare("
        INSERT INTO transactions (business_id, type, nominal, description)
        VALUES (?, 'Pemasukan', ?, ?)
    ");
    $insert->bind_param("ids", $business_id, $total, $desc);
    $insert->execute();

    $conn->commit();

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}