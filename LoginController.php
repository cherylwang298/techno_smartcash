<?php
session_start();
include 'db.php';

if (isset($_POST['login'])) {

    $phone = $_POST['phone'];
    $password = $_POST['password'];

    // Ambil user berdasarkan nomor HP
    $sql = "SELECT * FROM users WHERE phone = '$phone' LIMIT 1";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Cek password (pakai password_verify)
        if (password_verify($password, $user['password'])) {

            // Simpan session login
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];

            // Redirect ke dashboard
            header("Location: main_page.php");
            exit();

        } else {
            echo "Password salah!";
        }

    } else {
        echo "Nomor HP tidak ditemukan!";
    }
}
?>