<?php
// Cek apakah tombol login sudah diklik
if (isset($_POST['login'])) {
    $phone = $_POST['phone'];
    $password = $_POST['password'];

    // Di sini nanti kamu hubungkan ke database
    // Contoh sederhana:
    if ($phone == "0812345678" && $password == "admin123") {
        echo "Login Berhasil!";
    } else {
        echo "Nomor atau Password Salah.";
    }
}
?>