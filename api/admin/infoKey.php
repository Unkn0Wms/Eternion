<?php
require '../db.php';
session_start();


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $key = trim($_POST['key']);

    $stmt = $pdo->prepare("SELECT * FROM `activation_keys` WHERE `key_value` = ?");
    $stmt->execute([$key]);
    $keyInfo = $stmt->fetch();

    if ($keyInfo) {
        echo "Ключ: " . htmlspecialchars($keyInfo['key_value']) . "<br>";
        echo "Активирован пользователем: " . htmlspecialchars($keyInfo['is_used']) . "<br>";
        echo "Сгенерирован: " . htmlspecialchars($keyInfo['created_at']) . "<br>";
        echo "Длительность (дней): " . htmlspecialchars($keyInfo['duration_days']) . "<br>";
        echo "Роль, выдаваемая ключом: " . htmlspecialchars($keyInfo['subscription_name']);
    } else {
        echo "Ключ не найден.";
    }
}
