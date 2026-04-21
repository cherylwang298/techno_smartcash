<?php
session_start();

unset($_SESSION['user_id']);
unset($_SESSION['username']);

$_SESSION = [];

session_destroy();

header("Location: login.php");
exit;