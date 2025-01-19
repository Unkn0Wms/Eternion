<?php
require '../db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = intval($_POST['user_id']);
    $isBanned = intval($_POST['is_banned']); // 1 - бан, 0 - разбан
    $banReason = isset($_POST['ban_reason']) ? trim($_POST['ban_reason']) : null;

    try {
        if ($isBanned === 1) {
            $stmt = $pdo->prepare("UPDATE users SET is_banned = ?, ban_reason = ? WHERE id = ?");
            $stmt->execute([$isBanned, $banReason, $userId]);
        } else { // Логика для разбана
            $stmt = $pdo->prepare("UPDATE users SET is_banned = ?, ban_reason = NULL WHERE id = ?");
            $stmt->execute([$isBanned, $userId]);
        }

        if ($stmt->rowCount() > 0) {
            echo $isBanned === 1 
                ? "Пользователь забанен. Причина: " . ($banReason ?: "Не указана.")
                : "Пользователь разбанен.";
        } else {
            echo "Ошибка: пользователь не найден.";
        }
    } catch (PDOException $e) {
        echo "Ошибка базы данных: " . $e->getMessage();
    } catch (Exception $e) {
        echo "Ошибка: " . $e->getMessage();
    }
}
