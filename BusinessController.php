<?php
session_start();
include 'db.php';

// Proteksi halaman: Jika tidak ada session, tendang balik ke register
if (!isset($_SESSION['user_id'])) {
    header("Location: register.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    $user_id = $_SESSION['user_id'];
    $business_name = $_POST['business_name'];
    $business_type = $_POST['business_type'];
    $category = $_POST['category'];
    $address = $_POST['address'];
    $city = $_POST['city'];
    $phone_number = $_POST['phone_number'];
    $capital = $_POST['capital'] ?? 0;
    $description = $_POST['description'];
    $is_pro = $_POST['is_pro'] ?? 0;

    // Logika Upload Logo
    $logo_path = null;
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] == 0) {
        $target_dir = "uploads/";
        
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $file_extension = pathinfo($_FILES["logo"]["name"], PATHINFO_EXTENSION);
        $new_filename = time() . '_' . uniqid() . '.' . $file_extension;
        $logo_path = $target_dir . $new_filename;

        move_uploaded_file($_FILES["logo"]["tmp_name"], $logo_path);
    }

    // Query INSERT menggunakan Prepared Statement
    $sql = "INSERT INTO businesses (
                user_id, business_name, business_type, category, 
                logo, address, city, phone_number, capital, 
                description, is_pro
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    
    if ($stmt) {
        // i = integer, s = string, d = double
        // Sesuaikan parameter terakhir (is_pro) jika di DB adalah integer gunakan 'i'
        $stmt->bind_param(
            "isssssssdss", 
            $user_id, $business_name, $business_type, $category, 
            $logo_path, $address, $city, $phone_number, $capital, 
            $description, $is_pro
        );

        if ($stmt->execute()) {
            // Jika berhasil, langsung ke main_page.php tanpa pesan echo
            header("Location: login.php");
            exit();
        } else {
            // Ini tetap dibiarkan satu untuk jaga-jaga jika ada error database
            die("Gagal menyimpan data usaha: " . $stmt->error);
        }
    }
}
?>