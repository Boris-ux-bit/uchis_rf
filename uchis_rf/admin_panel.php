<?php
session_start();
require_once 'config.php';

// Проверка авторизации администратора
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$success_message = '';

// Обработка смены статуса
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $id = (int)$_GET['id'];
    
    if ($action == 'start') {
        $stmt = $pdo->prepare("UPDATE applications SET status = 'Идет обучение' WHERE id = ?");
        $stmt->execute([$id]);
        $success_message = "Заявка #$id - обучение начато";
    } elseif ($action == 'complete') {
        $stmt = $pdo->prepare("UPDATE applications SET status = 'Обучение завершено' WHERE id = ?");
        $stmt->execute([$id]);
        $success_message = "Заявка #$id - обучение завершено";
    }
}

// Получаем все заявки
$stmt = $pdo->query("
    SELECT a.*, u.login, u.full_name, c.name as course_name, c.type as course_type
    FROM applications a
    JOIN users u ON a.user_id = u.id
    JOIN courses c ON a.course_id = c.id
    ORDER BY a.created_at DESC
");
$applications = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Учусь.РФ - Админ-панель</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        .status-new { background: #ffc107; color: #000; }
        .status-learning { background: #17a2b8; color: #fff; }
        .status-completed { background: #28a745; color: #fff; }
        .admin-btn {
            display: inline-block;
            padding: 5px 10px;
            background: #1a3a5c;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-size: 11px;
            margin: 2px;
        }
        .admin-btn.start { background: #17a2b8; }
        .admin-btn.complete { background: #28a745; }
        .table-wrapper { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #f8f9fa; }
        .toast-notification {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: #28a745;
            color: white;
            padding: 12px 20px;
            border-radius: 10px;
            z-index: 1000;
            animation: slideIn 0.3s ease;
        }
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        .logout-btn {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background: #dc3545;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>👑 Панель администратора</h1>
            <p>Управление заявками на обучение</p>
        </div>
        <div class="content">
            <?php if(count($applications) > 0): ?>
                <div class="table-wrapper">
                    <table class="data-table">
                        <thead>
                            <tr><th>ID</th><th>Пользователь</th><th>Курс</th><th>Дата</th><th>Оплата</th><th>Статус</th><th>Действия</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach($applications as $app): ?>
                                <?php
                                    $status_class = '';
                                    if ($app['status'] == 'Новая') $status_class = 'status-new';
                                    elseif ($app['status'] == 'Идет обучение') $status_class = 'status-learning';
                                    elseif ($app['status'] == 'Обучение завершено') $status_class = 'status-completed';
                                ?>
                                <tr>
                                    <td><?= $app['id'] ?></td>
                                    <td><strong><?= htmlspecialchars($app['login']) ?></strong><br><small><?= htmlspecialchars($app['full_name']) ?></small></td>
                                    <td><?= htmlspecialchars($app['course_name']) ?><br><small><?= $app['course_type'] ?></small></td>
                                    <td><?= date('d.m.Y', strtotime($app['start_date'])) ?></td>
                                    <td><?= htmlspecialchars($app['payment_method']) ?></td>
                                    <td><span class="status-badge <?= $status_class ?>"><?= $app['status'] ?></span></td>
                                    <td>
                                        <?php if($app['status'] == 'Новая'): ?>
                                            <a href="?action=start&id=<?= $app['id'] ?>" class="admin-btn start" onclick="return confirm('Начать обучение?')">📚 Начать</a>
                                        <?php endif; ?>
                                        <?php if($app['status'] == 'Идет обучение'): ?>
                                            <a href="?action=complete&id=<?= $app['id'] ?>" class="admin-btn complete" onclick="return confirm('Завершить обучение?')">✅ Завершить</a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p style="text-align:center; color:#999;">📭 Нет заявок</p>
            <?php endif; ?>
            
            <div style="text-align: center;">
                <a href="logout.php" class="logout-btn">🚪 Выйти</a>
            </div>
        </div>
    </div>
    
    <?php if($success_message): ?>
    <script>
        const toast = document.createElement('div');
        toast.className = 'toast-notification';
        toast.innerHTML = '✅ <?= addslashes($success_message) ?>';
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 3000);
    </script>
    <?php endif; ?>
</body>
</html>