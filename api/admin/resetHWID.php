<?php
require '../db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['user_id'])) {
        echo "Ошибка: не передан ID пользователя.";
        exit;
    }

    $userId = intval($_POST['user_id']);

    $checkStmt = $pdo->prepare("SELECT id FROM users WHERE id = ?");
    $checkStmt->execute([$userId]);
    $userExists = $checkStmt->fetch();

    if (!$userExists) {
        echo "Ошибка: пользователь не найден.";
        exit;
    }

    $stmt = $pdo->prepare("UPDATE users SET hwid = NULL WHERE id = ?");
    $stmt->execute([$userId]);

    if ($stmt->rowCount() > 0) {
        echo "HWID успешно сброшен.";
    } else {
        echo "Ошибка: не удалось сбросить HWID.";
    }
}
