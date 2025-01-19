<?php
session_start();
require '../api/db.php'; 

if (!isset($_SESSION['user_id'])) {
    header('Location: /login.html');
    exit;
}

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare('SELECT role FROM users WHERE id = ?');
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user || $user['role'] !== 'Администратор') {
    header('Location: /index.html'); 
    exit;
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Админ-панель</title>
    <link href="../vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
<div class="container mt-5">
    <h1>Админ-панель</h1>
    <div class="mb-4">
        <button class="btn btn-primary" onclick="showSection('users')">Управление пользователями</button>
        <button class="btn btn-primary" onclick="showSection('subscriptions')">Управление подписками</button>
        <button class="btn btn-primary" onclick="showSection('keys')">Генерация ключей</button>
                <button class="btn btn-primary" onclick="showSection('role-set')">Установка ролей</button>
        <button class="btn btn-primary" onclick="showSection('get-info-key')">Информация о ключе</button>
        <button class="btn btn-primary" onclick="showSection('reset-hwid')">Сброс HWID</button>
        <button class="btn btn-primary" onclick="showSection('ban-user')">Бан/Разбан</button>
    </div>

    <!-- Раздел управления пользователями -->
    <div id="users" class="admin-section" style="display: none;">
        <h2>Управление пользователями</h2>
        <form id="deleteUserForm">
            <label for="userId">ID пользователя:</label>
            <input type="number" name="user_id" id="userId" required>
            <button class="btn btn-danger" type="submit">Удалить пользователя</button>
        </form>
        <div id="deleteUserResult" class="mt-2"></div>
    </div>

    <!-- Раздел управления подписками -->
    <div id="subscriptions" class="admin-section" style="display: none;">
        <h2>Управление подписками</h2>
        <form id="giveSubscriptionForm">
            <label for="userIdSub">ID пользователя:</label>
            <input type="number" name="user_id" id="userIdSub" required>
            <label for="subName">Название подписки:</label>
            <input type="text" name="subscription_name" id="subName" required>
            <label for="expiresAt">Дата окончания:</label>
            <input type="datetime-local" name="expires_at" id="expiresAt" required>
            <button class="btn btn-success" type="submit">Выдать подписку</button>
        </form>
        <div id="giveSubscriptionResult" class="mt-2"></div>
    </div>

    <!-- Раздел генерации ключей -->
    <div id="keys" class="admin-section" style="display: none;">
        <h2>Генерация ключей</h2>
        <form id="generateKeysForm">
            <label for="keyCount">Количество ключей:</label>
            <input type="number" name="count" id="keyCount" required>
            <label for="subscriptionName">Название подписки:</label>
            <input type="text" name="subscription_name" id="subscriptionName" required>
            <label for="durationDays">Длительность подписки (в днях):</label>
            <input type="number" name="duration_days" id="durationDays" required>
            <button class="btn btn-success" type="submit">Сгенерировать ключи</button>
        </form>
        <div id="generateKeysResult" class="mt-2"></div>
    </div>
</div>
    <div id="role-set" class="admin-section" style="display: none;">
        <h3>Установка роли</h3>
<form id="roleSet" method="POST" action="/api/admin/roleSet.php">
    <label for="userId">ID Пользователя</label>
    <input type="text" id="userId" name="user_id" required>
    
    <label for="newRole">Новая Роль</label>
    <input type="text" id="newRole" name="role" required>
    
    <button type="submit">Установить роль</button>
</form>

    </div>

    <div id="get-info-key" class="admin-section" style="display: none;">
        <h3>Информация о ключе</h3>
        <form id="getInfoKey">
            <div class="mb-3">
                <label for="key" class="form-label">Ключ</label>
                <input type="text" id="key" name="key" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Получить информацию</button>
        </form>
        <div id="getInfoKeyResult" class="mt-3"></div>
    </div>

    <div id="reset-hwid" class="admin-section" style="display: none;">
        <h3>Сброс HWID</h3>
<form id="resetHWID" method="POST" action="/api/admin/reset_hwid.php">
    <div class="mb-3">
        <label for="userIdHwid" class="form-label">ID Пользователя</label>
        <input type="text" id="userIdHwid" name="user_id" class="form-control" required>
    </div>
    <button type="submit" class="btn btn-primary">Сбросить HWID</button>
</form>

    </div>
<div id="ban-user" class="admin-section" style="display: none;">
    <h3>Система бана</h3>
    <form id="banUserForm">
        <div class="mb-3">
            <label for="userIdBan" class="form-label">ID Пользователя</label>
            <input type="text" id="userIdBan" name="user_id" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="banStatus" class="form-label">Статус</label>
            <select id="banStatus" name="is_banned" class="form-control" required>
                <option value="1">Забанить</option>
                <option value="0">Разбанить</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="banReason" class="form-label">Причина бана (если есть)</label>
            <textarea id="banReason" name="ban_reason" class="form-control"></textarea>
        </div>
        <button type="submit" class="btn btn-danger">Применить</button>
    </form>
</div>
</div>
<script>
    function showSection(sectionId) {
        const sections = document.querySelectorAll('.admin-section');
        sections.forEach(section => section.style.display = 'none');

        document.getElementById(sectionId).style.display = 'block';
    }

    $('#deleteUserForm').submit(function (event) {
        event.preventDefault();
        $.post('../api/admin/delete_user.php', $(this).serialize(), function (response) {
            $('#deleteUserResult').text(response);
        });
    });

    $('#giveSubscriptionForm').submit(function (event) {
        event.preventDefault();
        $.post('../api/admin/give_subscription.php', $(this).serialize(), function (response) {
            $('#giveSubscriptionResult').text(response);
        });
    });

    $('#generateKeysForm').submit(function (event) {
        event.preventDefault();
        $.post('../api/admin/generate_keys.php', $(this).serialize(), function (response) {
            $('#generateKeysResult').html(response);
        });
    });
    $(document).ready(function () {
    $("#roleSet").submit(function (event) {
        event.preventDefault();
        $.ajax({
            type: "POST",
            url: "../api/admin/roleSet.php",
            data: $(this).serialize(),
            success: function (response) {
                alert(response);
            }
        });
    });

    $("#getInfoKey").submit(function (event) {
        event.preventDefault();
        $.ajax({
            type: "POST",
            url: "../api/admin/infoKey.php",
            data: $(this).serialize(),
            success: function (response) {
                $("#getInfoKeyResult").html(response);
            }
        });
    });

    $("#resetHWID").submit(function (event) {
        event.preventDefault();
        $.ajax({
            type: "POST",
            url: "../api/admin/resetHWID.php",
            data: $(this).serialize(),
            success: function (response) {
                alert(response);
            }
        });
    });
});
    $(document).ready(function () {
    $("#banUserForm").submit(function (event) {
        event.preventDefault();

        $.ajax({
            type: "POST",
            url: "../api/admin/ban_user.php",
            data: $(this).serialize(),
            success: function (response) {
                alert(response);
            },
            error: function () {
                alert("Ошибка при выполнении запроса.");
            }
        });
    });
});

</script>
</body>
</html>
