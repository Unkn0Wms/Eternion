<?php
session_start();
require '../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = intval($_POST['user_id']);
    $subscription_name = trim($_POST['subscription_name']);
    $expires_at = $_POST['expires_at'];

    $stmt = $pdo->prepare('SELECT id FROM users WHERE id = ?');
    $stmt->execute([$user_id]);
    if ($stmt->rowCount() === 0) {
        echo "Пользователь с таким ID не найден.";
        exit;
    }

    $stmt = $pdo->prepare('SELECT id FROM subscriptions WHERE user_id = ?');
    $stmt->execute([$user_id]);
    if ($stmt->rowCount() > 0) {
        $stmt = $pdo->prepare('UPDATE subscriptions SET subscription_name = ?, status = "active", expires_at = ? WHERE user_id = ?');
        if ($stmt->execute([$subscription_name, $expires_at, $user_id])) {
            echo "Подписка обновлена успешно.";
        } else {
            echo "Ошибка обновления подписки.";
        }
    } else {
        $stmt = $pdo->prepare('INSERT INTO subscriptions (user_id, subscription_name, status, expires_at) VALUES (?, ?, "active", ?)');
        if ($stmt->execute([$user_id, $subscription_name, $expires_at])) {
            echo "Подписка выдана успешно.";
        } else {
            echo "Ошибка выдачи подписки.";
        }
    }
} else {
    echo "Неверный метод запроса.";
}
?>
