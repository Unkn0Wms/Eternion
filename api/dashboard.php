<?php
session_start();
require 'db.php'; 

if (!isset($_SESSION['user_id'])) {
    header('Location: /login.php'); 
    exit;
}

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare('SELECT id, username, email, role, hwid, created_at FROM users WHERE id = ?');
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    session_destroy();
    header('Location: /login.php');
    exit;
}
?>
