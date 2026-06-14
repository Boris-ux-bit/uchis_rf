-- =====================================================
-- БАЗА ДАННЫХ ДЛЯ ПОРТАЛА «Учусь.РФ»
-- Вариант №5 (онлайн курсы повышения квалификации)
-- =====================================================

-- Создание базы данных
CREATE DATABASE IF NOT EXISTS uchis_rf;
USE uchis_rf;

-- =====================================================
-- 1. ТАБЛИЦА users (пользователи)
-- =====================================================
DROP TABLE IF EXISTS users;
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    login VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(100) NOT NULL,
    role ENUM('user', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =====================================================
-- 2. ТАБЛИЦА courses (курсы)
-- =====================================================
DROP TABLE IF EXISTS courses;
CREATE TABLE courses (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    type VARCHAR(50) NOT NULL,
    duration_hours INT,
    price DECIMAL(10, 2)
);

-- =====================================================
-- 3. ТАБЛИЦА applications (заявки на обучение)
-- =====================================================
DROP TABLE IF EXISTS applications;
CREATE TABLE applications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    course_id INT NOT NULL,
    start_date DATE NOT NULL,
    payment_method VARCHAR(50) NOT NULL,
    status ENUM('Новая', 'Идет обучение', 'Обучение завершено') DEFAULT 'Новая',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id)
);

-- =====================================================
-- 4. ТАБЛИЦА reviews (отзывы)
-- =====================================================
DROP TABLE IF EXISTS reviews;
CREATE TABLE reviews (
    id INT PRIMARY KEY AUTO_INCREMENT,
    application_id INT NOT NULL,
    user_id INT NOT NULL,
    rating INT CHECK (rating BETWEEN 1 AND 5),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (application_id) REFERENCES applications(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- =====================================================
-- ТЕСТОВЫЕ ДАННЫЕ
-- =====================================================

-- Добавление курсов
INSERT INTO courses (id, name, type, duration_hours, price) VALUES
(1, 'Повышение квалификации: Педагогика', 'повышение квалификации', 72, 5000),
(2, 'Профессиональная переподготовка', 'переподготовка', 256, 25000),
(3, 'Охрана труда для руководителей', 'охрана труда', 40, 4500),
(4, 'Повышение квалификации: ИТ-специалист', 'повышение квалификации', 80, 8000),
(5, 'Пожарно-технический минимум', 'охрана труда', 24, 3000),
(6, 'Переподготовка: Кадровое делопроизводство', 'переподготовка', 180, 18000),
(7, 'Повышение квалификации: Менеджмент', 'повышение квалификации', 72, 5500),
(8, 'Охрана труда: Специальная оценка условий', 'охрана труда', 56, 6500);

-- Добавление администратора
-- Логин: Admin26
-- Пароль: Demo20
INSERT INTO users (id, login, password, full_name, phone, email, role) 
VALUES (1, 'Admin26', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 
        'Администратор', '+7 (000) 000-00-00', 'admin@uchis.ru', 'admin');

-- =====================================================
-- КОНЕЦ ФАЙЛА
-- =====================================================
