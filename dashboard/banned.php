<?php
session_start();
require '../api/db.php';

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare('SELECT id, username, email, role, hwid, created_at, is_banned, ban_reason FROM users WHERE id = ?');
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    session_destroy();
    header('Location: /login.php');
    exit;
}

if ($user['is_banned'] == 0) {
    $_SESSION['ban_reason'] = $user['ban_reason'];
    header('Location: dashboard.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вы забанены</title>
    <style>
        body {
            background: #1e1e1e;
            color: #f0f0f0;
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .ban-container {
            text-align: center;
            padding: 20px;
            border: 1px solid #444;
            border-radius: 8px;
            background: #2b2b2b;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.5);
        }

        .ban-container h1 {
            font-size: 3rem;
            color: #ff4d4d;
            margin-bottom: 20px;
        }

        .ban-container p {
            font-size: 1.2rem;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div class="ban-container">
        <h1>ВЫ ЗАБАНЕНЫ!</h1>
        <p>Причина: <?php echo isset($_SESSION['ban_reason']) ? htmlspecialchars($_SESSION['ban_reason']) : 'Не указана'; ?></p>
    </div>
</body>
</html>
