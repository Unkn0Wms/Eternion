<?php
session_start();
require '../db.php';

header('Content-Type: application/json; charset=utf-8'); 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login']);
    $password = trim($_POST['password']);

    if (empty($login) || empty($password)) {
        echo json_encode("Пожалуйста, заполните все поля", JSON_UNESCAPED_UNICODE);
        exit;
    }

    $stmt = $pdo->prepare("SELECT id, username, email, password FROM users WHERE email = ? OR username = ?");
    $stmt->execute([$login, $login]);
    $user = $stmt->fetch();

    if (!$user) {
        echo json_encode("Неверный логин или пароль", JSON_UNESCAPED_UNICODE);
        exit;
    }

    if (!password_verify($password, $user['password'])) {
        echo json_encode("Неверный логин или пароль", JSON_UNESCAPED_UNICODE);
        exit;
    }

    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['role'] = $user['role'];
    
    header('Location: ../../dashboard/dashboard.php');
} else {
    echo json_encode("Неверный метод запроса", JSON_UNESCAPED_UNICODE);
}
?>
