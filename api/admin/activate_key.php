<?php
session_start();
require '../db.php';

if (!isset($_SESSION['user_id'])) {
    echo "Вы не авторизованы.";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $activation_key = trim($_POST['key']);

    $stmt = $pdo->prepare("SELECT id, subscription_name, duration_days, is_used FROM activation_keys WHERE key_value = ?");
    $stmt->execute([$activation_key]);
    $key = $stmt->fetch();

    if (!$key) {
        echo "Ключ не найден.";
        exit;
    }

    if ($key['is_used']) {
        echo "Ключ уже был использован.";
        exit;
    }

    $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
    $stmt->execute([$key['subscription_name'], $user_id]);

    $current_date = new DateTime();
    $stmt = $pdo->prepare("SELECT expires_at FROM subscriptions WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $subscription = $stmt->fetch();

    if ($subscription && new DateTime($subscription['expires_at']) > $current_date) {
        $new_expires_at = (new DateTime($subscription['expires_at']))->modify("+{$key['duration_days']} days");
    } else {
        $new_expires_at = $current_date->modify("+{$key['duration_days']} days");
    }

    $stmt = $pdo->prepare("
        INSERT INTO subscriptions (user_id, subscription_name, expires_at, status)
        VALUES (?, ?, ?, 'active')
        ON DUPLICATE KEY UPDATE subscription_name = VALUES(subscription_name), expires_at = VALUES(expires_at), status = 'active'
    ");
    $stmt->execute([$user_id, $key['subscription_name'], $new_expires_at->format('Y-m-d H:i:s')]);

    $stmt = $pdo->prepare("UPDATE activation_keys SET is_used = TRUE WHERE id = ?");
    $stmt->execute([$key['id']]);

    echo "Ключ активирован до {$new_expires_at->format('d.m.Y H:i')}.";
}
?>
