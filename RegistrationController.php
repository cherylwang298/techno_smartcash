<?php
include 'db.php'; // Pastikan sudah buat koneksi database

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullname = $_POST['fullname'];
    $phone    = $_POST['phone'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $action   = $_POST['action']; // Mengambil value dari tombol yang diklik

    // 1. Logika simpan ke database (Simpan data pengguna)
    $sql = "INSERT INTO users (fullname, phone, password) VALUES ('$fullname', '$phone', '$password')";
    
    if ($conn->query($sql) === TRUE) {
        // 2. Cek tombol mana yang diklik untuk redirect
        if ($action == "usaha") {
            // Jika klik "Daftar Usaha", arahkan ke form usaha
            header("Location: register_usaha.php");
        } else {
            // Jika klik "Selesai", arahkan ke dashboard utama
            header("Location: main_page.php");
        }
        exit();
    } else {
        echo "Error: " . $conn->error;
    }
}
?>