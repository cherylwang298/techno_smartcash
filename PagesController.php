<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isPremium($conn) {
    $user_id = $_SESSION['user_id'] ?? null;

    if (!$user_id) return false;

    $sql = "SELECT subscription FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    return $user['subscription'] === 'premium';
}