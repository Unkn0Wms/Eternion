<?php
session_start();
require '../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $current_password = $_POST['old_password'];
    $new_password = $_POST['new_password'];
    $repeat_password = $_POST['reapeat_password'];

    if ($new_password !== $repeat_password) {
        echo json_encode("Пароли не совпадают", JSON_UNESCAPED_UNICODE);
        exit;
    }

    $stmt = $pdo->prepare('SELECT password FROM users WHERE id = ?');
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($current_password, $user['password'])) {
        echo json_encode("Неверный текущий пароль", JSON_UNESCAPED_UNICODE);
        exit;
    }

    $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
    $stmt = $pdo->prepare('UPDATE users SET password = ? WHERE id = ?');
    if ($stmt->execute([$hashed_password, $user_id])) {
        echo json_encode("Пароль успешно обновлен", JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode("Ошибка при обновлении пароля", JSON_UNESCAPED_UNICODE);
    }
} else {
    echo json_encode("Неверный метод запроса", JSON_UNESCAPED_UNICODE);
}
?>
