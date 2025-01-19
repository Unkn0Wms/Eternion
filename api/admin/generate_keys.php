<?php
require '../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $count = intval($_POST['count']);
    $subscription_name = trim($_POST['subscription_name']);
    $duration_days = intval($_POST['duration_days']);

    if ($count <= 0 || $duration_days <= 0 || empty($subscription_name)) {
        echo "Некорректные данные для генерации ключей.";
        exit;
    }

    $keys = [];
    for ($i = 0; $i < $count; $i++) {
        $key_value = bin2hex(random_bytes(16));
        $keys[] = $key_value;

        $stmt = $pdo->prepare("INSERT INTO activation_keys (key_value, subscription_name, duration_days) VALUES (?, ?, ?)");
        $stmt->execute([$key_value, $subscription_name, $duration_days]);
    }

    echo "<h3>Сгенерированные ключи:</h3><ul>";
    foreach ($keys as $key) {
        echo "<li>$key</li>";
    }
    echo "</ul>";
}
?>