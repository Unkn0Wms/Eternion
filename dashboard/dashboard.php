<?php
session_start();
require '../api/db.php'; 

if (!isset($_SESSION['user_id'])) {
    header('Location: /login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare('SELECT id, username, email, role, hwid, created_at, is_banned, ban_reason FROM users WHERE id = ?');
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    session_destroy();
    header('Location: /login.php');
    exit;
}

if ($user['is_banned'] == 1) {
    $_SESSION['ban_reason'] = $user['ban_reason'];
    header('Location: banned.php');
    exit;
}

$stmt = $pdo->prepare('SELECT subscription_name, status, expires_at FROM subscriptions WHERE user_id = ?');
$stmt->execute([$user_id]);
$subscription = $stmt->fetch(PDO::FETCH_ASSOC);

$isAdmin = ($user['role'] === 'Администратор');

?>


<!DOCTYPE html>
<html>
<head>
    <title>Eternion » Личный кабинет</title>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link href="../vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="/img/logos.png">

    <!-- Custom CSS -->
    <link href="../css/main.css" rel="stylesheet">
    <link href="../vendor/aos/aos.css" rel="stylesheet">
    <link href="../vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>

    <style>
        .container {
            max-width: 1200px;
            margin: 10% auto;
        }

        .profile-header {
            background: linear-gradient(135deg, #5A5AFF 0%, rgb(254 80 44 / 50%) 100%);
            color: white;
            padding: 40px;
            border-radius: 20px;
            text-align: center;
            box-shadow: 0 4px 20px rgb(254 80 44 / 30%);
            position: relative;
        }

        .profile-header img {
            border-radius: 50%;
            border: 5px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 15px;
            transition: transform 0.3s ease-in-out;
        }

        .profile-header img:hover {
            transform: scale(1.1);
        }

        .profile-header h1 {
            font-size: 28px;
            margin-bottom: 5px;
        }

        .profile-header p {
            margin: 0;
            font-size: 16px;
            color: #cfcfcf;
        }

        .nav-tabs {
            margin-bottom: 20px;
            border-bottom: none;
            margin-top: 20px;
        }

        .nav-tabs .nav-link {
            color: #999;
            border: none;
            padding: 12px 20px;
            margin: 10px 5px;
            background-color: #252627;
            border-radius: 12px;
            transition: background-color 0.3s ease-in-out, color 0.3s ease-in-out;
        }

        .nav-tabs .nav-link.active {
            background-color: #5A5AFF;
            color: white;
        }

        .nav-link:hover {
            color: white;
            background-color: #5A5AFF;
        }

        .tab-content {
            border-radius: 20px;
        }

        .tab-content h3 {
            color: #a6a6a6;
        }

        .tab-content p, .tab-content ul, .tab-content li, .tab-content form {
            color: #d1d1d1;
        }

        .form-label {
            color: #bbb;
        }

        .form-control {
            background-color: #333;
            color: #e0e0e0;
            border: 1px solid #444;
        }

        .form-control:focus {
            background-color: #444;
            color: #e0e0e0;
            border-color: #5A5AFF;
            box-shadow: none;
        }

        .data-card {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            transition: transform 0.3s ease-in-out, box-shadow 0.3s ease-in-out;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .data-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
        }

        .data-card h4 {
            font-size: 20px;
            margin-bottom: 10px;
            color: #c9d1d9;
        }

        .data-card p {
            font-size: 15px;
            color: #8b949e;
            margin-bottom: 5px;
        }

        .data-card i {
            font-size: 24px;
            margin-right: 10px;
            color: #5A5AFF;
        }

        .btn-container {
            display: flex;
            justify-content: space-between;
        }

        .btn-download {
            padding: 5px 15px;
            font-size: 14px;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        .activation-key-form, .change-password-form, .fromStyle {
            margin-top: 20px;
            padding: 25px;
            border-radius: 15px;
            background: rgba(255, 255, 255, 0.05);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .activation-key-form h4, .change-password-form h4 .fromStyle h4 {
            font-size: 22px;
            color: #ffffff;
        }

        .form-control {
            margin-bottom: 15px;
            color: #ffffff;
            outline: none;
            border: none;
            background: rgba(0, 0, 0, 0.2);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            border-radius: 8px;
            padding: 10px;
            font-size: 16px;

        }

        .form-control:focus {
            background: rgba(0, 0, 0, 0.2);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            color: #ffffff;
            border-radius: 8px;
            padding: 10px;
            font-size: 16px;
        }


        .activation-key-form input::placeholder, .change-password-form input::placeholder .fromStyle::placeholder {
            color: #aaa;
        }

        .activation-key-form button, .change-password-form button, .fromStyle button {
            width: 200px;
            padding: 4px;
            font-size: 16px;
            background-color: #5A5AFF;
            border: none;
            border-radius: 8px;
            color: #ffffff;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .activation-key-form button:hover, .change-password-form button:hover, .fromStyle button:hover {
            background-color: #4040c1;
        }

        .activation-key-form .form-group, .change-password-form .form-group, .fromStyle .form-group {
            margin-bottom: 20px;
        }


        input[type=file]::file-selector-button {
            margin-left: 1px;
            border-radius: 6px;
        }


    </style>
</head>
<body class="index-page">
<header><div>

<header id="header" class="header d-flex align-items-center fixed-top ">
    <div class="container-fluid container-xl position-relative d-flex align-items-center">

        <a href="/" class="logo d-flex align-items-center me-auto">
            <img src="img/logos.png" alt="">
            <h1>Eternion</h1>
        </a>

        <nav id="navmenu" class="navmenu">
            <ul>
                <li><a href="/#hero">Главная<br></a></li>
                <li><a href="#footer">Документы</a></li>
            </ul>
            <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
        </nav>


        <a class="btn-logout flex-md-shrink-0" style="margin-left: 30px;" href="../api/logout.php">Выйти</a>

        

    </div>
</header>
</div></header>
<div class="container" data-aos="fade-out">
    <div class="profile-header mt-5">
        <img src="img/head.png" data-aos="zoom-in">
        <h1 data-aos="zoom-out"><?php echo htmlspecialchars($user['username']); ?></h1>
        <p data-aos="zoom-out">Email: <?php echo htmlspecialchars($user['email']); ?></p>
        <p data-aos="zoom-out">UID: <?php echo htmlspecialchars($user['id']); ?></p>
    </div>

    <ul class="nav nav-tabs" id="myTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="overview-tab" data-bs-toggle="tab" data-bs-target="#overview-content"
                    type="button" role="tab" aria-controls="overview-content" aria-selected="true">Аккаунт
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="purchase-tab" data-bs-toggle="tab" data-bs-target="#purchase-content"
                    type="button" role="tab" aria-controls="purchase-content" aria-selected="false">Покупка клиента
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="settings-tab" data-bs-toggle="tab" data-bs-target="#settings-content"
                    type="button" role="tab" aria-controls="settings-content" aria-selected="false">Дополнительно
            </button>
        </li>
    <?php if ($isAdmin): ?>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="admin-tab" data-bs-toggle="tab" data-bs-target="#admin-content"
                type="button" role="tab" aria-controls="admin-content" aria-selected="false"><a style="color:gray;" >Админ-Панель</a>
        </button>
    </li>
        <li class="nav-item" role="presentation">
        <a href="path/to/your/file.zip" class="nav-link" id="dw-tab" download>
            Скачать клиент
        </a>
    </li>
    <?php endif; ?>
        
    </ul>

    <div class="tab-content" id="myTabContent">
        <div class="tab-pane fade show active" id="overview-content" role="tabpanel" aria-labelledby="overview-tab">
            <h3>Обзор аккаунта</h3>

            <div class="data-card">
                <h4>
                    <i class="fas fa-check-circle"></i>
                    Статус подписки
                </h4>
    <?php if ($subscription): ?>
        <p>Действует до: <span style="color: green;"><?php echo htmlspecialchars(date('d.m.Y H:i', strtotime($subscription['expires_at']))); ?></span></p>
    <?php else: ?>
        <p style="color: red;">Подписка отсутствует</p>
    <?php endif; ?>
            </div>

            <div class="data-card">
                <h4>
                    <i class="fas fa-calendar-alt"></i>
                    Дата регистрации
                </h4>
                <p><?php echo htmlspecialchars(date('d.m.Y H:i', strtotime($user['created_at']))); ?></p>
            </div>


            <div class="data-card">
                <h4>
                    <i class="fas fa-user"></i>
                    Логин
                </h4>
                <p><?php echo htmlspecialchars($user['username']); ?></p>
            </div>

            <div class="data-card">
                <h4>
                    <i class="fas fa-user-tag"></i>
                    Роль
                </h4>
                <p><?php echo htmlspecialchars($user['role']); ?></p>
            </div>

            <div class="data-card">
                <h4>
                    <i class="fas fa-desktop"></i>
                    HWID
                </h4>
                <p><?php echo htmlspecialchars($user['hwid'] ?? 'Вы еще не запускали клиент'); ?></p>
            </div>
        </div>

        <div class="tab-pane fade" id="purchase-content" role="tabpanel" aria-labelledby="purchase-tab">
            <h3>Наши продукты</h3>
            <div class="row row-cols-1 row-cols-xl-4 px-5 mb-4 mt-5" data-aos="zoom-out">
                <tbody>

                <tr>

                    <div class="p-2">
                        <div class="card rounded-4" style="background-color: #1E1E20;">
                            <img class=" p-3 rounded-5" src="img/betatester.png" alt="...">
                            <div class="p-3" style="margin-top: -20px">
                                <p class="fw-bolder" style="height: 5px">Eternion Бета навсегда</p>

                                <!--                                        <text>Описание-->
                                <!--                                            <p class="fw-bolder" th:text="${product.desc}"> </p>-->
                                <!--                                        </text>-->


                                <p>
                                    <span>Цена: </span>
                                    <span class="fw-bolder">799</span>
                                    <i class="fw-bolder bx bx-ruble text-success"></i>
                                </p>
                                <div class="text-center">
                                    <a class="btn btn-getstarted w-100 bi-bag-fill"
                                       href="/checkout?id=1">
                                        Купить
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                </tr>

                <tr>

                    <div class="p-2">
                        <div class="card rounded-4" style="background-color: #1E1E20;">
                            <img class=" p-3 rounded-5" src="img/lifetime.png" alt="...">
                            <div class="p-3" style="margin-top: -20px">
                                <p class="fw-bolder" style="height: 5px">Eternion Навсегда</p>

                                <!--                                        <text>Описание-->
                                <!--                                            <p class="fw-bolder" th:text="${product.desc}"> </p>-->
                                <!--                                        </text>-->


                                <p>
                                    <span>Цена: </span>
                                    <span class="fw-bolder">549</span>
                                    <i class="fw-bolder bx bx-ruble text-success"></i>
                                </p>
                                <div class="text-center">
                                    <a class="btn btn-getstarted w-100 bi-bag-fill"
                                       href="/checkout?id=2">
                                        Купить
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                </tr>

                <tr>

                    <div class="p-2">
                        <div class="card rounded-4" style="background-color: #1E1E20;">
                            <img class=" p-3 rounded-5" src="img/1year.png" alt="...">
                            <div class="p-3" style="margin-top: -20px">
                                <p class="fw-bolder" style="height: 5px">Eternion Год</p>

                                <!--                                        <text>Описание-->
                                <!--                                            <p class="fw-bolder" th:text="${product.desc}"> </p>-->
                                <!--                                        </text>-->


                                <p>
                                    <span>Цена: </span>
                                    <span class="fw-bolder">499</span>
                                    <i class="fw-bolder bx bx-ruble text-success"></i>
                                </p>
                                <div class="text-center">
                                    <a class="btn btn-getstarted w-100 bi-bag-fill"
                                       href="/checkout?id=3">
                                        Купить
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                </tr>

                <tr>

                    <div class="p-2">
                        <div class="card rounded-4" style="background-color: #1E1E20;">
                            <img class=" p-3 rounded-5" src="img/6month.png" alt="...">
                            <div class="p-3" style="margin-top: -20px">
                                <p class="fw-bolder" style="height: 5px">Eternion пол года</p>

                                <!--                                        <text>Описание-->
                                <!--                                            <p class="fw-bolder" th:text="${product.desc}"> </p>-->
                                <!--                                        </text>-->


                                <p>
                                    <span>Цена: </span>
                                    <span class="fw-bolder">439</span>
                                    <i class="fw-bolder bx bx-ruble text-success"></i>
                                </p>
                                <div class="text-center">
                                    <a class="btn btn-getstarted w-100 bi-bag-fill"
                                       href="/checkout?id=4">
                                        Купить
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                </tr>

                <tr>

                    <div class="p-2">
                        <div class="card rounded-4" style="background-color: #1E1E20;">
                            <img class=" p-3 rounded-5" src="img/3month.png" alt="...">
                            <div class="p-3" style="margin-top: -20px">
                                <p class="fw-bolder" style="height: 5px">Eternion 3 Месяца</p>

                                <!--                                        <text>Описание-->
                                <!--                                            <p class="fw-bolder" th:text="${product.desc}"> </p>-->
                                <!--                                        </text>-->


                                <p>
                                    <span>Цена: </span>
                                    <span class="fw-bolder">349</span>
                                    <i class="fw-bolder bx bx-ruble text-success"></i>
                                </p>
                                <div class="text-center">
                                    <a class="btn btn-getstarted w-100 bi-bag-fill"
                                       href="/checkout?id=5">
                                        Купить
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                </tr>

                <tr>

                    <div class="p-2">
                        <div class="card rounded-4" style="background-color: #1E1E20;">
                            <img class=" p-3 rounded-5" src="img/month.png" alt="...">
                            <div class="p-3" style="margin-top: -20px">
                                <p class="fw-bolder" style="height: 5px">Eternion Месяц</p>

                                <!--                                        <text>Описание-->
                                <!--                                            <p class="fw-bolder" th:text="${product.desc}"> </p>-->
                                <!--                                        </text>-->


                                <p>
                                    <span>Цена: </span>
                                    <span class="fw-bolder">260</span>
                                    <i class="fw-bolder bx bx-ruble text-success"></i>
                                </p>
                                <div class="text-center">
                                    <a class="btn btn-getstarted w-100 bi-bag-fill"
                                       href="/checkout?id=6">
                                        Купить
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                </tr>

                <tr>

                    <div class="p-2">
                        <div class="card rounded-4" style="background-color: #1E1E20;">
                            <img class=" p-3 rounded-5" src="img/hwid.png" alt="...">
                            <div class="p-3" style="margin-top: -20px">
                                <p class="fw-bolder" style="height: 5px">Сброс HWID</p>

                                <!--                                        <text>Описание-->
                                <!--                                            <p class="fw-bolder" th:text="${product.desc}"> </p>-->
                                <!--                                        </text>-->


                                <p>
                                    <span>Цена: </span>
                                    <span class="fw-bolder">252</span>
                                    <i class="fw-bolder bx bx-ruble text-success"></i>
                                </p>
                                <div class="text-center">
                                    <a class="btn btn-getstarted w-100 bi-bag-fill"
                                       href="/checkout?id=7">
                                        Купить
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                </tr>
                </tbody>
            </div>
        </div>

        <div class="tab-pane fade" id="admin-content" role="tabpanel" aria-labelledby="admin-tab">
            <h3>Админ панель</h3>

            <div class="fromStyle">
                <h4>Управление пользователями</h4>
                <form id="deleteUserForm">
                    <div class="form-group">
                        <label for="userId" class="form-label">ID пользователя:</label>
                        <input type="number" class="form-control" name="user_id" id="userId" required
                               placeholder="ID пользователя">
                        <div id="deleteUserResult" class="mt-2"></div>
                    </div>
                    <button type="submit" class="btn btn-primary">Удалить</button>
                </form>
            </div>

            <div class="fromStyle">
                <h4>Управление подписками</h4>
                <form id="giveSubscriptionForm">  
                    <div class="form-group">
                        <label for="userIdSub" class="form-label">ID пользователя:</label>
                        <input type="number" class="form-control" name="user_id" id="userIdSub" required
                               placeholder="ID пользователя">
                        <label for="subName" class="form-label">Название подписки:</label>
                        <input type="text" class="form-control" name="subscription_name" id="subName" required
                               placeholder="Название подписки">
                        <label for="expiresAt" class="form-label">Дата окончания:</label>
                        <input type="datetime-local" class="form-control" name="expires_at" id="expiresAt" required
                               placeholder="Дата окончания">
                        <div id="giveSubscriptionResult" class="mt-2"></div>
                    </div>
                    <button type="submit" class="btn btn-primary">Выдать подписку</button>
                </form>
            </div>

            <div class="fromStyle">
                <h4>Выдать роль</h4>
                <form id="roleSet">  
                    <div class="form-group">
                        <label for="userId" method="POST" action="/api/admin/roleSet.php" class="form-label">ID пользователя:</label>
                        <input type="number" class="form-control" name="userId" id="user_id" required
                               placeholder="ID пользователя">
                        <label for="newRole" class="form-label">Роль:</label>
                        <input type="text" class="form-control" name="role" id="newRole" required
                               placeholder="Роль">
                    </div>
                    <button type="submit" class="btn btn-primary">Установить роль</button>
                </form>
            </div>

            <div class="fromStyle">
                <h4>Сброс HWID</h4>
                <form id="resetHWID">  
                    <div class="form-group">
                        <label for="reset-hwid" class="form-label">Сброс HWID</label>
                        <input type="text" class="form-control" name="userId" id="userIdHwid" required
                               placeholder="ID пользователя">
                    </div>
                    <button type="submit" class="btn btn-primary">Сбросить HWID</button>
                </form>
            </div>

            <div class="fromStyle">
                <h4>Система бана</h4>
                <form id="banUserForm">  
                    <div class="form-group">
                        <label for="userIdBan" class="form-label">ID пользователя:</label>
                        <input type="number" class="form-control" name="user_id" id="userIdBan" required
                               placeholder="ID пользователя">
                        <label for="banStatus" class="form-label">Статус:</label>
                        <select id="banStatus" name="is_banned" class="form-control" required>
                        <option value="1">Забанить</option>
                        <option value="0">Разбанить</option>
                    </select>
                        <label for="banReason" class="form-label">Причина бана (если есть):</label>
                        <input type="text" class="form-control" name="ban_reason" id="banReason" required
                               placeholder="Причина бана">
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

        <div class="tab-pane fade" id="settings-content" role="tabpanel" aria-labelledby="settings-tab">
            <h3>Настройки</h3>

            <div class="fromStyle">
                <h4>Активация ключа</h4>
                <form id="activateKeyForm">
                    <div class="form-group">
                        <label for="activationKey" class="form-label">Введите ключ активации</label>
                        <input type="text" class="form-control" name="key" id="activationKey"
                               placeholder="Ключ активации">
                        <div id="activateResult" class="mt-2"></div>
                    </div>
                    <button type="submit" class="btn btn-primary">Активация</button>
                </form>
            </div>


            <div class="change-password-form mt-4">
                <h4>Сменить пароль</h4>
<div class="change-password-form mt-4">
    <h4>Сменить пароль</h4>
    <form id="change_password_form">
        <div class="form-group">
            <label for="current-password" class="form-label">Текущий пароль</label>
            <input type="password" class="form-control" name="old_password" id="current-password" placeholder="Текущий пароль">
        </div>
        <div class="form-group">
            <label for="new-password" class="form-label">Новый пароль</label>
            <input type="password" class="form-control" name="new_password" id="new-password" placeholder="Новый пароль">
        </div>
        <div class="form-group">
            <label for="confirm-password" class="form-label">Подтвердите новый пароль</label>
            <input type="password" class="form-control" name="reapeat_password" id="confirm-password" placeholder="Подтвердите новый пароль">
        </div>
        <p id="result" class="text-danger"></p>
        <button type="submit" class="btn btn-primary">Сохранить изменения</button>
    </form>
</div>
            </div>
        </div>
    </div>


    

</div>
<a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center"><i
        class="bi bi-arrow-up-short"></i></a>

<script data-cfasync="false" src="../cdn-cgi/scripts/5c5dd728/cloudflare-static/email-decode.min.js"></script><script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../js/main.js"></script>
<script src="../vendor/aos/aos.js"></script>
<script>
    const selectTexwork = document.getElementById('selectTexwork');
    if (selectTexwork) {
        selectTexwork.addEventListener('change', function () {
            var selectedValue = this.value;
            const texWork = document.getElementById('texWork');
            if (texWork) {
                texWork.value = selectedValue;
            }
        });
    }

    $('#activateKeyForm').submit(function (event) {
        event.preventDefault();
        $.post('../api/admin/activate_key.php', $(this).serialize(), function (response) {
            $('#activateResult').html(response);
        });
    });

        const promoCreateForm = $("#promoCreate");
        if (promoCreateForm.length) {
            promoCreateForm.submit(function (event) {
                event.preventDefault();
                $.ajax({
                    type: "POST",
                    url: "/createPromo",
                    data: $(this).serialize(),
                    success: function (response) {
                        $("#promoResult").html(response);
                    }
                });
            });
        }

$(document).ready(function () {
    const changePasswordForm = $("#change_password_form");

    if (changePasswordForm.length) {
        changePasswordForm.submit(function (event) {
            event.preventDefault();
            $.ajax({
                type: "POST",
                url: "../api/admin/change_password.php",
                data: $(this).serialize(),
                success: function (response) {
                    $("#result").html(response);
                },
                error: function () {
                    $("#result").html('<p class="text-danger">Произошла ошибка. Попробуйте еще раз.</p>');
                }
            });
        });
    }
});
</script>
<footer><div>

<footer id="footer" class="footer">
    <div class="container footer-top">
        <div class="row gy-4">
            <div class="col-lg-4 col-md-6 footer-about">
                <a href="/" class="d-flex align-items-center">

                    <!--<img src="img/logos.png"class="px-2" alt="" width="50px;"!-->

                    <span>Eternion</span>
                </a>
                <div class="footer-contact pt-3">
                    <p>Наш продукт не связан с MojangAB</p>
                    <p>все средства идут на развитие проекта.</p>
                    <p class="mt-3"><strong>Email: </strong><span><a href="/cdn-cgi/l/email-protection" class="__cf_email__" data-cfemail="dfbaa7bcbab3b3bab1abbcb3b6bab1abf1b1baaba8b0adb49fb8b2beb6b3f1bcb0b2">[email&#160;protected]</a></span></p>
                    <p>Тарасов Сергей Петрович | ИНН 713602155690</p>
                </div>
            </div>

            <div class="col-lg-2 col-md-3 footer-links">
                <h4>Навигация</h4>
                <ul>
                    <li><i class="bi bi-chevron-right"></i> <a href="/">Главная</a></li>
                     <li><i class="bi bi-chevron-right"></i> <a href="/#faq">Вопросы</a></li>
                     <li><i class="bi bi-chevron-right"></i> <a href="/#alt-features">Наши качества</a></li>
                </ul>
            </div>

            <div class="col-lg-2 col-md-3 footer-links">
                <h4>Документы</h4>
                <ul>
                    <li><i class="bi bi-chevron-right"></i> <a href="../eula">Пользовательское соглашение</a></li>
                    <li><i class="bi bi-chevron-right"></i> <a href="../privacy_policy">Обработка данных</a></li>
                  </ul>
            </div>

            <div class="col-lg-4 col-md-12">
                <h4>Наши социальные сети</h4>
                <p>Здесь мы собрали все наши официальные социальные сети, мы будем очень рады вашей подписке.</p>
                <div class="social-links d-flex">
                    <a href="https://www.youtube.com/@luvbeeq" target="_blank"><i class="bi bi-youtube"></i></a>
                    <a href="https://t.me/excellent_recode" target="_blank"><i class="bi bi-telegram"></i></a>
                    <a href="https://discord.gg/excellentrecode" target="_blank"><i class="bi bi-discord"></i></a>
                </div>
            </div>

        </div>
    </div>


    <div class="container copyright text-center mt-4">
        <p>© <span>Copyright</span> <strong class="px-1 sitename">Eternion</strong><span>          -> All Rights Reserved</span></p>
    </div>

</footer>
</div></footer>
<script data-cfasync="false" src="../cdn-cgi/scripts/5c5dd728/cloudflare-static/email-decode.min.js"></script></body>
</html>
