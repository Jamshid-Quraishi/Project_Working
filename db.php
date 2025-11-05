<?php
$host = '127.0.0.1';
$db   = 'sondaggi_db';
$user = 'sondaggi_user';
$pass = '12345';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
} catch (PDOException $e) {
    error_log("Errore DB: " . $e->getMessage());
    die("Errore di connessione al database.");
}
?>