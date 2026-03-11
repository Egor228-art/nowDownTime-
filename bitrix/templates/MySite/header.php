<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
	<? $APPLICATION->ShowHead(); ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>НастолкиSHOP — магазин настольных игр</title>
    <!-- Font Awesome для иконок -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
        }

        body {
            background-color: #f9fafc;
            color: #1e293b;
            line-height: 1.5;
        }

        /* Контейнер для центрирования */
        .container {
            max-width: 1280px;
            margin: 0 auto;
            padding: 0 30px;
        }

        /* ===== ШАПКА (первая строка) ===== */
        .top-header {
            background-color: white;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.03);
            padding: 15px 0;
            border-bottom: 1px solid #eef2f6;
        }

        .top-header .container {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 20px;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .logo i {
            font-size: 28px;
            color: #6c5ce7;
            background: linear-gradient(135deg, #6c5ce7, #a463f5);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .logo span {
            font-weight: 700;
            font-size: 22px;
            letter-spacing: -0.5px;
            background: linear-gradient(135deg, #1e293b, #334155);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        /* Поиск */
        .search-box {
            flex: 1;
            max-width: 500px;
            display: flex;
            align-items: center;
            background: #f1f5f9;
            border-radius: 40px;
            padding: 5px 5px 5px 18px;
            transition: all 0.2s;
            border: 1px solid #e2e8f0;
        }

        .search-box:focus-within {
            background: white;
            box-shadow: 0 0 0 3px rgba(108, 92, 231, 0.1);
            border-color: #6c5ce7;
        }

        .search-box i {
            color: #94a3b8;
            font-size: 16px;
        }

        .search-box input {
            border: none;
            background: transparent;
            padding: 10px 12px;
            width: 100%;
            font-size: 15px;
            outline: none;
        }

        .search-box button {
            background: linear-gradient(135deg, #6c5ce7, #8b5cf6);
            border: none;
            color: white;
            padding: 8px 22px;
            border-radius: 30px;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            transition: 0.2s;
            white-space: nowrap;
        }

        .search-box button:hover {
            background: linear-gradient(135deg, #5b4bc4, #7c4dff);
            transform: scale(1.02);
        }

        /* Пользователь и корзина */
        .user-actions {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .user-icon {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            padding: 8px 12px;
            border-radius: 30px;
            transition: 0.2s;
        }

        .user-icon:hover {
            background: #f1f5f9;
        }

        .user-icon i {
            font-size: 18px;
            color: #475569;
        }

        .user-icon span {
            font-weight: 500;
            color: #1e293b;
        }

        .cart-btn {
            background: linear-gradient(135deg, #1e293b, #0f172a);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 40px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            transition: 0.2s;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .cart-btn:hover {
            background: linear-gradient(135deg, #2d3a4f, #1e293b);
            transform: translateY(-2px);
        }

        .cart-btn i {
            font-size: 16px;
        }

        .cart-btn span {
            background: #ef4444;
            color: white;
            border-radius: 20px;
            padding: 2px 8px;
            font-size: 12px;
            margin-left: 5px;
        }

        /* ===== ПОДШАПКА (категории) ===== */
        .nav-header {
            background: white;
            border-bottom: 1px solid #eef2f6;
            padding: 0;
        }

        .nav-menu {
            display: flex;
            align-items: center;
            gap: 32px;
            list-style: none;
            padding: 15px 0;
        }

        .nav-menu a {
            text-decoration: none;
            color: #334155;
            font-weight: 600;
            font-size: 16px;
            transition: 0.2s;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .nav-menu a i {
            color: #6c5ce7;
            font-size: 14px;
        }

        .nav-menu a:hover {
            color: #6c5ce7;
        }

        .nav-menu .highlight {
            background: linear-gradient(135deg, #f8f0ff, #f0e6ff);
            padding: 6px 15px;
            border-radius: 30px;
            color: #6c5ce7;
        }

        /* ===== ГЛАВНЫЙ БАННЕР ===== */
        .hero {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 60px 0;
            border-radius: 0 0 30px 30px;
            margin-bottom: 50px;
        }

        .hero .container {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 40px;
        }

        .hero-text h1 {
            font-size: 48px;
            font-weight: 800;
            margin-bottom: 15px;
            line-height: 1.2;
        }

        .hero-text p {
            font-size: 18px;
            opacity: 0.9;
            margin-bottom: 30px;
            max-width: 500px;
        }

        .hero-btn {
            background: white;
            color: #6c5ce7;
            border: none;
            padding: 14px 36px;
            border-radius: 40px;
            font-weight: 700;
            font-size: 16px;
            cursor: pointer;
            transition: 0.2s;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        .hero-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15);
        }

        .hero-image i {
            font-size: 200px;
            color: rgba(255, 255, 255, 0.2);
        }

        /* ===== СЕКЦИЯ ПОПУЛЯРНЫХ ИГР ===== */
        .section-title {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 40px;
            position: relative;
            display: inline-block;
        }

        .section-title:after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 0;
            width: 60px;
            height: 4px;
            background: linear-gradient(135deg, #6c5ce7, #a463f5);
            border-radius: 2px;
        }

        .games-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 30px;
            margin: 50px 0;
        }

        .game-card {
            background: white;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            transition: 0.3s;
            border: 1px solid #f0f0f0;
        }

        .game-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(108, 92, 231, 0.15);
        }

        .game-image {
            height: 200px;
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }

        .game-image i {
            font-size: 80px;
            color: #adb5bd;
            opacity: 0.4;
        }

        .badge {
            position: absolute;
            top: 15px;
            right: 15px;
            background: #ef4444;
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .game-info {
            padding: 20px;
        }

        .game-category {
            color: #6c5ce7;
            font-size: 13px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 5px;
        }

        .game-info h3 {
            font-size: 18px;
            margin-bottom: 10px;
            color: #1e293b;
        }

        .game-meta {
            display: flex;
            gap: 15px;
            margin: 15px 0;
            color: #64748b;
            font-size: 14px;
        }

        .game-meta i {
            color: #fbbf24;
            margin-right: 4px;
        }

        .game-price {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 15px;
        }

        .price {
            font-size: 22px;
            font-weight: 700;
            color: #1e293b;
        }

        .price small {
            font-size: 14px;
            font-weight: 400;
            color: #94a3b8;
            text-decoration: line-through;
            margin-left: 8px;
        }

        .add-to-cart {
            background: #f1f5f9;
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            color: #475569;
            cursor: pointer;
            transition: 0.2s;
            font-size: 16px;
        }

        .add-to-cart:hover {
            background: #6c5ce7;
            color: white;
        }

        /* Секция категорий */
        .categories {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 20px;
            margin: 60px 0;
        }

        .category-item {
            background: white;
            border-radius: 60px;
            padding: 30px 20px;
            text-align: center;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.02);
            border: 1px solid #eef2f6;
            transition: 0.2s;
        }

        .category-item:hover {
            border-color: #6c5ce7;
            transform: scale(1.02);
        }

        .category-item i {
            font-size: 36px;
            color: #6c5ce7;
            margin-bottom: 15px;
        }

        .category-item h4 {
            font-size: 16px;
            font-weight: 600;
        }

        /* ===== ПОДВАЛ ===== */
        footer {
            background: #0f172a;
            color: white;
            margin-top: 80px;
            padding: 60px 0 20px;
            border-radius: 40px 40px 0 0;
        }

        .footer-grid {
            display: grid;
            grid-template-columns: 1.5fr 1fr 1fr 1.2fr;
            gap: 40px;
            margin-bottom: 50px;
        }

        .footer-col .logo {
            margin-bottom: 20px;
        }

        .footer-col .logo i,
        .footer-col .logo span {
            background: white;
            -webkit-text-fill-color: white;
            -webkit-background-clip: unset;
            color: white;
        }

        .footer-col p {
            color: #a0afc0;
            margin-bottom: 25px;
            line-height: 1.7;
        }

        .social-links {
            display: flex;
            gap: 15px;
        }

        .social-links a {
            color: white;
            background: #1e2a3a;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: 0.2s;
            text-decoration: none;
        }

        .social-links a:hover {
            background: #6c5ce7;
            transform: translateY(-3px);
        }

        .footer-col h4 {
            font-size: 18px;
            margin-bottom: 25px;
            position: relative;
        }

        .footer-col h4:after {
            content: '';
            position: absolute;
            bottom: -8px;
            left: 0;
            width: 40px;
            height: 2px;
            background: #6c5ce7;
        }

        .footer-col ul {
            list-style: none;
        }

        .footer-col ul li {
            margin-bottom: 12px;
        }

        .footer-col ul a {
            color: #a0afc0;
            text-decoration: none;
            transition: 0.2s;
        }

        .footer-col ul a:hover {
            color: white;
            padding-left: 5px;
        }

        .footer-col i {
            width: 20px;
            color: #6c5ce7;
            margin-right: 10px;
        }

        .copyright {
            text-align: center;
            padding-top: 30px;
            border-top: 1px solid #1e2a3a;
            color: #64748b;
            font-size: 14px;
        }

        /* Адаптация */
        @media (max-width: 768px) {
            .top-header .container {
                flex-direction: column;
                align-items: stretch;
            }
            .search-box {
                max-width: 100%;
            }
            .footer-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
	<div id="panel"><? $APPLICATION->ShowPanel(); ?></div>

    <!-- ШАПКА (первая линия) -->
    <div class="top-header">
        <div class="container">
            <!-- Логотип -->
            <div class="logo">
                <i class="fas fa-dragon"></i>
                <span>НастолкиSHOP</span>
            </div>

            <!-- Поиск -->
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" placeholder="Поиск игр, аксессуаров...">
                <button>Найти</button>
            </div>

            <!-- Юзер и корзина -->
            <div class="user-actions">
                <div class="user-icon">
                    <i class="far fa-user-circle"></i>
                    <span>Войти</span>
                </div>
                <button class="cart-btn">
                    <i class="fas fa-shopping-cart"></i>
                    Корзина
                    <span>3</span>
                </button>
            </div>
        </div>
    </div>

    <!-- ПОДШАПКА (разделы) -->
    <div class="nav-header">
        <div class="container">
            <ul class="nav-menu">
                <li><a href="#"><i class="fas fa-dice-d20"></i> D&D</a></li>
                <li><a href="#"><i class="fas fa-chess-board"></i> Настольные игры</a></li>
                <li><a href="#"><i class="fas fa-puzzle-piece"></i> Головоломки</a></li>
                <li><a href="#"><i class="fas fa-users"></i> Для компаний</a></li>
                <li><a href="#"><i class="fas fa-child"></i> Детям</a></li>
                <li><a href="#" class="highlight"><i class="fas fa-gift"></i> Акции</a></li>
            </ul>
        </div>
    </div>
<main>
