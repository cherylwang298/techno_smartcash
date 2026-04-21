<?php
include 'db.php'; // Mengambil file koneksi yang kamu buat tadi

$sql = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fullname VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    subscription ENUM('free', 'premium') DEFAULT 'free',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";



if ($conn->query($sql) === TRUE) {
    echo "Tabel 'users' berhasil dibuat atau sudah ada.";
} else {
    echo "Gagal membuat tabel: " . $conn->error;
}

$conn->close();
?>