<?php
session_start();
include 'db.php';

$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    header("Location: login.php");
    exit;
}

$old_password = $_POST['old_password'];
$new_password = $_POST['new_password'];
$confirm_password = $_POST['confirm_password'];

if ($new_password !== $confirm_password) {
    header("Location: change_password.php?error=confirm");
    exit;
}

$stmt = $conn->prepare("
SELECT password
FROM users
WHERE id = ?
");

$stmt->bind_param("i", $user_id);
$stmt->execute();

$user = $stmt->get_result()->fetch_assoc();

if (!password_verify($old_password, $user['password'])) {
    header("Location: change_password.php?error=old");
    exit;
}

$new_hash = password_hash($new_password, PASSWORD_DEFAULT);

$update = $conn->prepare("
UPDATE users
SET password = ?
WHERE id = ?
");

$update->bind_param(
    "si",
    $new_hash,
    $user_id
);

$update->execute();

header("Location: profile.php?password=success");
exit;