<?php
session_start();
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullname = $conn->real_escape_string($_POST['fullname']);
    $phone    = $conn->real_escape_string($_POST['phone']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $action   = $_POST['action'];

    // Nama kolom disesuaikan: fullname, phone, password
    $sql = "INSERT INTO users (fullname, phone, password) 
            VALUES ('$fullname', '$phone', '$password')";

    if ($conn->query($sql) === TRUE) {
        // Simpan ID baru ke session
        $_SESSION['user_id'] = $conn->insert_id;
        $_SESSION['username'] = $fullname;

        if ($action == "dashboard") {
            header("Location: register_usaha.php");
        } else {
            header("Location: main_page.php");
        }
        exit(); 
    } else {
        die("Error: " . $conn->error);
    }
}
?>