<?php
session_start();
include 'db.php';

// Cek apakah data dikirim melalui POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $biz_id = $_POST['business_id'];
    $type = $_POST['type'];
    $nominal = $_POST['nominal'];
    $description = $_POST['description'];
    $date = $_POST['created_at'];

    // Query untuk memasukkan data ke tabel transactions
    $sql = "INSERT INTO transactions (business_id, type, nominal, description, created_at) 
            VALUES (?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isdss", $biz_id, $type, $nominal, $description, $date);

    if ($stmt->execute()) {
        // Langsung arahkan kembali ke dashboard
        header("Location: main_page.php");
        exit();
    } else {
        echo "Error: " . $conn->error;
    }
}
