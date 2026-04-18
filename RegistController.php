<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    var_dump($_POST); // cek data masuk atau tidak
    echo "<br><br>";

    $fullname = $_POST['fullname'];
    $phone    = $_POST['phone'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $action   = $_POST['action'];


    $sql = "INSERT INTO users (username, phone_number, password) 
            VALUES ('$fullname', '$phone', '$password')";

    if ($conn->query($sql)) {
        echo "INSERT BERHASIL";
    } else {
        die("ERROR SQL: " . $conn->error);
    }

    exit;
}