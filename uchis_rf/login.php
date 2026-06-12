<?php
session_start();
require_once 'config.php';

$error = '';

// Прямая проверка для администратора (без обращения к БД)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // ========== ОСОБАЯ ПРОВЕРКА ДЛЯ АДМИНА ==========
    if ($login === 'Admin26' && $password === 'Demo20') {
        $_SESSION['user_id'] = 1;
        $_SESSION['user_login'] = 'Admin26';
        $_SESSION['user_role'] = 'admin';
        header('Location: admin_panel.php');
        exit;
    }
    
    // ========== ОБЫЧНАЯ ПРОВЕРКА ДЛЯ ВСЕХ ПОЛЬЗОВАТЕЛЕЙ ==========
    if (empty($login) || empty($password)) {
        $error = 'Заполните все поля';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE login = ?");
        $stmt->execute([$login]);
        $user = $stmt->fetch();
        
        if ($user) {
            // Пробуем password_verify, а если не сработало — сравниваем как обычный текст
            if (password_verify($password, $user['password']) || $password === $user['password']) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_login'] = $user['login'];
                $_SESSION['user_role'] = $user['role'];
                
                if ($user['role'] === 'admin') {
                    header('Location: admin_panel.php');
                } else {
                    header('Location: profile.php');
                }
                exit;
            } else {
                $error = 'Неверный пароль';
            }
        } else {
            $error = 'Пользователь не найден';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Учусь.РФ - Вход</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📚 Учусь.РФ</h1>
            <p>Онлайн курсы повышения квалификации</p>
        </div>
        <div class="content">
            <h2>🔑 Вход в систему</h2>
            
            <?php if($error): ?>
                <div class="error">⚠️ <?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label>👤 Логин</label>
                    <input type="text" name="login" placeholder="Введите логин" required>
                </div>
                
                <div class="form-group">
                    <label>🔒 Пароль</label>
                    <input type="password" name="password" placeholder="Введите пароль" required>
                </div>
                
                <button type="submit" class="btn">Войти</button>
                
                <div class="nav-links">
                    <a href="register.php">📝 Ещё не зарегистрированы? Регистрация</a>
                </div>
            </form>
            
            <hr>
            <div class="admin-hint">
                👑 Администратор: <strong>Admin26</strong> / <strong>Demo20</strong>
            </div>
        </div>
    </div>
</body>
</html>