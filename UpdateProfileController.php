<?php
session_start();
include 'db.php';

$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    header("Location: login.php");
    exit;
}

// $fullname = $_POST['fullname'];
// $phone = $_POST['phone'];

// $business_name = $_POST['business_name'];
// $address = $_POST['address'];
// $city = $_POST['city'];

$fullname = trim($_POST['fullname']);
$phone = trim($_POST['phone']);

$business_name = trim($_POST['business_name']);
$business_type = trim($_POST['business_type']);
$category = trim($_POST['category']);
$address = trim($_POST['address']);
$city = trim($_POST['city']);
$phone_number = trim($_POST['phone_number']);
$capital = (int)$_POST['capital'];
$description = trim($_POST['description']);

$stmt = $conn->prepare("
UPDATE users
SET fullname = ?, phone = ?
WHERE id = ?
");

$stmt->bind_param(
    "ssi",
    $fullname,
    $phone,
    $user_id
);

$stmt->execute();

// $stmt2 = $conn->prepare("
// UPDATE businesses
// SET business_name = ?, address = ?, city = ?
// WHERE user_id = ?
// ");
$stmt2 = $conn->prepare("
UPDATE businesses
SET
    business_name = ?,
    business_type = ?,
    category = ?,
    address = ?,
    city = ?,
    phone_number = ?,
    capital = ?,
    description = ?
WHERE user_id = ?
");

// $stmt2->bind_param(
//     "sssi",
//     $business_name,
//     $address,
//     $city,
//     $user_id
// );

$stmt2->bind_param(
    "ssssssisi",
    $business_name,
    $business_type,
    $category,
    $address,
    $city,
    $phone_number,
    $capital,
    $description,
    $user_id
);

$stmt2->execute();

header("Location: profile.php?success=1");
exit;