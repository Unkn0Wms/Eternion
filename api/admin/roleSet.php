<?php
require '../db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['user_id']) || !isset($_POST['role'])) {
        echo "Ошибка: не переданы данные пользователя или роль.";
        exit;
    }

    $userId = intval($_POST['user_id']);
    $newRole = trim($_POST['role']);

    if ($userId <= 0) {
        echo "Ошибка: неверный ID пользователя.";
        exit;
    }

    $checkStmt = $pdo->prepare("SELECT id FROM users WHERE id = ?");
    $checkStmt->execute([$userId]);
    $userExists = $checkStmt->fetch();

    if (!$userExists) {
        echo "Ошибка: пользователь не найден.";
        exit;
    }

    $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
    $stmt->execute([$newRole, $userId]);

    if ($stmt->rowCount() > 0) {
        echo "Роль успешно обновлена.";
    } else {
        echo "Ошибка: не удалось обновить роль.";
    }
}
?>
