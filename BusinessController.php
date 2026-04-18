<?php
session_start();
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $user_id = $_SESSION['user_id'] ?? null;

    if (!$user_id) {
        die("User belum login.");
    }

    // ====== GET FORM DATA ======
    $business_name = $_POST['business_name'] ?? null;
    $business_type = $_POST['business_type'] ?? null;
    $category      = $_POST['category'] ?? null;
    $address       = $_POST['address'] ?? null;
    $city          = $_POST['city'] ?? null;
    $phone_number  = $_POST['phone_number'] ?? null;
    $capital       = $_POST['capital'] ?? 0;
    $description   = $_POST['description'] ?? null;
    $is_pro        = $_POST['is_pro'] ?? 0;

    // ====== HANDLE LOGO UPLOAD ======
    $logoPath = null;

    if (!empty($_FILES['logo']['name'])) {

        $uploadDir = "uploads/logos/";

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileName = time() . "_" . basename($_FILES["logo"]["name"]);
        $targetFile = $uploadDir . $fileName;

        $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

        $allowed = ['jpg', 'jpeg', 'png', 'webp'];

        if (!in_array($imageFileType, $allowed)) {
            die("Format file tidak didukung.");
        }

        if (move_uploaded_file($_FILES["logo"]["tmp_name"], $targetFile)) {
            $logoPath = $targetFile;
        } else {
            die("Upload logo gagal.");
        }
    }

    // ====== MYSQLI INSERT ======
    $sql = "INSERT INTO businesses 
    (user_id, business_name, business_type, category, logo, address, city, phone_number, capital, description, is_pro)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param(
        "isssssssssi",
        $user_id,
        $business_name,
        $business_type,
        $category,
        $logoPath,
        $address,
        $city,
        $phone_number,
        $capital,
        $description,
        $is_pro
    );

    if ($stmt->execute()) {
        header("Location: main_page.php?success=1");
        exit;
    } else {
        die("Insert gagal: " . $stmt->error);
    }
}