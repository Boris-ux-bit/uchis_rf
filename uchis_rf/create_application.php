<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$error = '';
$success = false;

$stmt = $pdo->query("SELECT * FROM courses ORDER BY type, name");
$courses = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $course_id = $_POST['course_id'] ?? '';
    $start_date = trim($_POST['start_date'] ?? '');
    $payment_method = $_POST['payment_method'] ?? '';
    
    if (empty($course_id)) {
        $error = 'Выберите курс';
    } elseif (empty($start_date)) {
        $error = 'Укажите дату начала';
    } elseif (empty($payment_method)) {
        $error = 'Выберите способ оплаты';
    } else {
        if (preg_match('/^\d{2}\.\d{2}\.\d{4}$/', $start_date)) {
            $parts = explode('.', $start_date);
            $start_date_db = "$parts[2]-$parts[1]-$parts[0]";
            
            $today = date('Y-m-d');
            if ($start_date_db < $today) {
                $error = 'Дата не может быть в прошлом';
            } else {
                $sql = "INSERT INTO applications (user_id, course_id, start_date, payment_method, status) 
                        VALUES (?, ?, ?, ?, 'Новая')";
                $stmt = $pdo->prepare($sql);
                if ($stmt->execute([$user_id, $course_id, $start_date_db, $payment_method])) {
                    $success = true;
                } else {
                    $error = 'Ошибка при создании заявки';
                }
            }
        } else {
            $error = 'Неверный формат даты. Используйте ДД.ММ.ГГГГ';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Учусь.РФ - Новая заявка</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📚 Учусь.РФ</h1>
            <p>Запись на онлайн курсы</p>
        </div>
        <div class="content">
            <?php if($success): ?>
                <div class="success">
                    <h3>✅ Заявка успешно создана!</h3>
                    <p>Ваша заявка отправлена на согласование администратору.</p>
                    <a href="profile.php" class="btn">В личный кабинет</a>
                </div>
            <?php else: ?>
                <h2>📝 Новая заявка на обучение</h2>
                
                <?php if($error): ?>
                    <div class="error">❌ <?= $error ?></div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="form-group">
                        <label>📚 Выберите курс</label>
                        <select name="course_id" required>
                            <option value="">-- Выберите курс --</option>
                            <?php foreach($courses as $course): ?>
                                <option value="<?= $course['id'] ?>">
                                    <?= htmlspecialchars($course['name']) ?> 
                                    (<?= $course['type'] ?>, 
                                     <?= $course['duration_hours'] ?> ч., 
                                     <?= number_format($course['price'], 0, '', ' ') ?> ₽)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>📅 Желаемая дата начала</label>
                        <input type="text" name="start_date" id="start_date" placeholder="ДД.ММ.ГГГГ" required>
                        <small>Формат: 25.12.2024</small>
                    </div>
                    
                    <div class="form-group">
                        <label>💳 Способ оплаты</label>
                        <select name="payment_method" required>
                            <option value="">-- Выберите способ --</option>
                            <option value="Наличные">💰 Наличные</option>
                            <option value="Карта">💳 Банковская карта</option>
                            <option value="Безналичный расчёт">🏦 Безналичный расчёт</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn">🎓 Отправить заявку</button>
                    <a href="profile.php" class="btn btn-secondary" style="text-align:center;">← Назад</a>
                </form>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        const dateInput = document.getElementById('start_date');
        if (dateInput) {
            dateInput.addEventListener('input', function(e) {
                let value = this.value.replace(/[^\d]/g, '');
                if (value.length >= 2 && value.length < 5) {
                    value = value.slice(0, 2) + '.' + value.slice(2);
                } else if (value.length >= 5 && value.length < 9) {
                    value = value.slice(0, 2) + '.' + value.slice(2, 4) + '.' + value.slice(4, 8);
                } else if (value.length >= 9) {
                    value = value.slice(0, 2) + '.' + value.slice(2, 4) + '.' + value.slice(4, 8);
                }
                this.value = value;
            });
        }
    </script>
</body>
</html>