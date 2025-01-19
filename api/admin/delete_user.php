<?php
require '../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = intval($_POST['user_id']);

    $stmt = $pdo->prepare('DELETE FROM users WHERE id = ?');
    if ($stmt->execute([$user_id])) {
        echo "Пользователь успешно удалён.";
    } else {
        echo "Ошибка удаления пользователя.";
    }
}
?>