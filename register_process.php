<?php
require 'auth.php';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    if (register($username, $password)) {
        login($username, $password);
        header("Location: home.php");
    } else {
        header("Location: register.php?error=1");
    }
    exit;
}
?>