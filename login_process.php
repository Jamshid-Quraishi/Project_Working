<?php
require 'auth.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        header("Location: index.php?error=1");
        exit;
    }
    
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    if (empty($username) || empty($password)) {
        header("Location: index.php?error=1");
        exit;
    }
    
    $login_result = login($username, $password);
    
    if ($login_result === 'success') {
        header("Location: " . (is_admin() ? "admin_dashboard.php" : "home.php"));
    } else if ($login_result === 'disabled') {
        header("Location: index.php?error=disabled");
    } else {
        header("Location: index.php?error=1");
    }
    exit;
}

header("Location: index.php");
?>
