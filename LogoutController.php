<?php
session_start();

// hapus semua session yang kamu pakai
unset($_SESSION['user_id']);
unset($_SESSION['username']);

// optional: bersihin total session
$_SESSION = [];

// destroy session
session_destroy();

// redirect ke login
header("Location: login.php");
exit;