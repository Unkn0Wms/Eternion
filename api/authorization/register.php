<?php
require '../db.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $username = trim($_POST['login']);
    $password = trim($_POST['pw_hash']);
    $role = 'Пользователь';
    $hwid = null;

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode("Неверный формат e-mail", JSON_UNESCAPED_UNICODE);
        exit;
    }
    if (strlen($username) < 3 || strlen($username) > 20) {
        echo json_encode("Логин должен содержать от 3 до 20 символов", JSON_UNESCAPED_UNICODE);
        exit;
    }
    if (strlen($password) < 6) {
        echo json_encode("Пароль должен содержать не менее 6 символов", JSON_UNESCAPED_UNICODE);
        exit;
    }

    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->rowCount() > 0) {
        echo json_encode("Этот e-mail уже зарегистрирован", JSON_UNESCAPED_UNICODE);
        exit;
    }

    $stmt = $pdo->prepare("INSERT INTO users (email, username, password, role, hwid) VALUES (?, ?, ?, ?, ?)");
    if ($stmt->execute([$email, $username, $hashed_password, $role, $hwid])) {

    header('Location: ../../dashboard/dashboard.php');
    } else {
        echo json_encode("Ошибка регистрации, попробуйте позже", JSON_UNESCAPED_UNICODE);
    }
} else {
    echo json_encode("Неверный метод запроса", JSON_UNESCAPED_UNICODE);
}
?>
