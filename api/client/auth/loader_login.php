<?php
require '../../db.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = $_POST['username'];
    $password = $_POST['password'];
    $client_hwid = $_POST['hwid'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || !password_verify($password, $user['password'])) {
        echo json_encode(["status" => "error", "message" => "Неверные данные"]);
        exit;
    }

    if ($user['is_banned']) {
        echo json_encode(["status" => "banned", "reason" => $user['ban_reason']]);
        exit;
    }

    if (empty($user['hwid'])) {
        $stmt = $pdo->prepare("UPDATE users SET hwid = ? WHERE id = ?");
        $stmt->execute([$client_hwid, $user['id']]);
    } elseif ($user['hwid'] !== $client_hwid) {
        echo json_encode(["status" => "error", "message" => "HWID Не совпадает"]);
        exit;
    }

    $stmt = $pdo->prepare("SELECT * FROM subscriptions WHERE user_id = ? AND status = 'active'");
    $stmt->execute([$user['id']]);
    $subscription = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$subscription) {
        $subscription = ["subscription_name" => "Нет подписки", "expires_at" => "N/A"];
    }

    echo json_encode([
        "status" => "success",
        "user_id" => $user['id'],
        "hwid" => $client_hwid,
        "subscription_name" => $subscription['subscription_name'],
        "expires_at" => $subscription['expires_at']
    ]);
    exit;
}
