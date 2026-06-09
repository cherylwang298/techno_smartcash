<?php
session_start();
include 'db.php';

header('Content-Type: application/json');

$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$conn->begin_transaction();

try {
    // Cek subscription sekarang
    $check = $conn->prepare("SELECT subscription FROM users WHERE id = ?");
    $check->bind_param("i", $user_id);
    $check->execute();
    $user = $check->get_result()->fetch_assoc();

    if (!$user) {
        throw new Exception("User tidak ditemukan");
    }

    if ($user['subscription'] === 'premium') {
        throw new Exception("Kamu sudah Premium");
    }

    // Update ke premium
    $update = $conn->prepare("
        UPDATE users 
        SET subscription = 'premium' 
        WHERE id = ?
    ");
    $update->bind_param("i", $user_id);
    $update->execute();

    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Berhasil upgrade ke Premium!'
    ]);

} catch (Exception $e) {
    $conn->rollback();

    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 