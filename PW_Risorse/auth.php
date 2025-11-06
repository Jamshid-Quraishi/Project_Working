<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function is_logged_in() {
    return isset($_SESSION['user_id']) && isset($_SESSION['login_time']);
}

function is_admin() {
    return is_logged_in() && $_SESSION['is_admin'] == 1;
}

function require_login() {
    if (!is_logged_in()) {
        header("Location: index.php");
        exit;
    }
    
    if (time() - $_SESSION['login_time'] > 3600) {
        session_destroy();
        header("Location: index.php?error=session_expired");
        exit;
    }
    
    $_SESSION['login_time'] = time();
}

function require_admin() {
    require_login();
    if (!is_admin()) {
        header("Location: home.php");
        exit;
    }
}

function login($username, $password) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    
    if ($user) {
        if ($user['is_active'] == 0) {
            return 'disabled';
        }
        
        if (password_verify($password, $user['password'])) {
            if (password_needs_rehash($user['password'], PASSWORD_BCRYPT)) {
                $newHash = password_hash($password, PASSWORD_BCRYPT);
                $updateStmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                $updateStmt->execute([$newHash, $user['id']]);
            }
            
            session_regenerate_id(true);
            
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['is_admin'] = $user['is_admin'];
            $_SESSION['login_time'] = time();
            $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
            $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'];
            
            return 'success';
        }
    }
    
    sleep(2);
    return 'invalid';
}

function register($username, $password) {
    global $pdo;
    
    if (strlen($password) < 8) {
        return false;
    }
    
    $hash = password_hash($password, PASSWORD_BCRYPT);
    $username = trim(strip_tags($username));
    
    try {
        $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
        return $stmt->execute([$username, $hash]);
    } catch (PDOException $e) {
        error_log("Registration error: " . $e->getMessage());
        return false;
    }
}

function verify_session() {
    if (!is_logged_in()) return false;
    
    if ($_SESSION['user_agent'] !== $_SERVER['HTTP_USER_AGENT'] || 
        $_SESSION['ip_address'] !== $_SERVER['REMOTE_ADDR']) {
        session_destroy();
        return false;
    }
    
    return true;
}

function log_audit($user_id, $action_type, $details) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO audit_log (user_id, action_type, details, ip_address) 
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([
            $user_id, 
            $action_type, 
            $details, 
            $_SERVER['REMOTE_ADDR']
        ]);
        return true;
    } catch (Exception $e) {
        error_log("Audit log error: " . $e->getMessage());
        return false;
    }
}
?>
