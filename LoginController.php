<?php
session_start();
include 'db.php';

if (isset($_POST['login'])) {
    $phone = $conn->real_escape_string($_POST['phone']); 
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE phone = '$phone' LIMIT 1";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['fullname'] = $user['fullname']; 

            header("Location: main_page.php");
            exit();
        } else {
            header("Location: login.php?error=password");
            exit();
        }
    } else {
        header("Location: login.php?error=notfound");
        exit();
    }
}
?>