<?php
// ============ ОБРАБОТКА AJAX ЗАПРОСОВ ============
// Это должно быть ПЕРВЫМ, до любого другого кода
if (isset($_POST['ajax_action']) || isset($_GET['ajax_action'])) {
    header('Content-Type: application/json');
    
    // Подключаем ядро Битрикс
    require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
    
    global $DB;
    $action = $_POST['ajax_action'] ?? $_GET['ajax_action'] ?? '';
    
    if ($action === 'get_viewers') {
        $productId = intval($_POST['product_id'] ?? $_GET['product_id'] ?? 0);
        
        $viewers = [];
        $res = $DB->Query("SELECT user_id, user_name, user_avatar FROM active_viewers 
                           WHERE product_id = $productId AND last_active > DATE_SUB(NOW(), INTERVAL 30 SECOND)
                           ORDER BY created_at DESC LIMIT 9");
        
        while ($row = $res->Fetch()) {
            $viewers[] = [
                'id' => $row['user_id'],
                'name' => $row['user_name'],
                'avatar' => $row['user_avatar']
            ];
        }
        
        echo json_encode(['success' => true, 'viewers' => $viewers]);
        exit;
    }
    
    if ($action === 'update_viewer') {
        $userId = intval($_POST['user_id']);
        $userName = $DB->ForSql($_POST['user_name']);
        $userAvatar = $DB->ForSql($_POST['user_avatar']);
        $productId = intval($_POST['product_id']);
        $sessionId = $DB->ForSql(md5($_SERVER['HTTP_USER_AGENT'] . $_SERVER['REMOTE_ADDR']));
        
        if ($userId > 0) {
            // Удаляем неактивных
            $DB->Query("DELETE FROM active_viewers WHERE last_active < DATE_SUB(NOW(), INTERVAL 30 SECOND)");
            
            // Проверяем существует ли запись
            $exists = $DB->Query("SELECT id FROM active_viewers 
                                  WHERE user_id = $userId AND product_id = $productId AND session_id = '$sessionId'");
            
            if ($exists->Fetch()) {
                $DB->Query("UPDATE active_viewers 
                            SET last_active = NOW(), user_name = '$userName', user_avatar = '$userAvatar'
                            WHERE user_id = $userId AND product_id = $productId AND session_id = '$sessionId'");
            } else {
                $DB->Query("INSERT INTO active_viewers (user_id, user_name, user_avatar, product_id, session_id, last_active, created_at) 
                            VALUES ($userId, '$userName', '$userAvatar', $productId, '$sessionId', NOW(), NOW())");
            }
            
            // Получаем всех активных зрителей
            $viewers = [];
            $res = $DB->Query("SELECT user_id, user_name, user_avatar FROM active_viewers 
                               WHERE product_id = $productId AND last_active > DATE_SUB(NOW(), INTERVAL 30 SECOND)
                               ORDER BY created_at DESC LIMIT 9");
            
            while ($row = $res->Fetch()) {
                $viewers[] = [
                    'id' => $row['user_id'],
                    'name' => $row['user_name'],
                    'avatar' => $row['user_avatar']
                ];
            }
            
            echo json_encode(['success' => true, 'viewers' => $viewers]);
            exit;
        }
        
        echo json_encode(['success' => false]);
        exit;
    }
    
    echo json_encode(['success' => false, 'error' => 'Unknown action']);
    exit;
}

// ============ ДАЛЬШЕ ИДЕТ ВЕСЬ ОСТАЛЬНОЙ КОД ============

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

// Подключаем модули
if(!CModule::IncludeModule('iblock')) {
    die('Ошибка: модуль инфоблоков не установлен');
}

// Получаем ID товара из URL
$elementId = intval($_GET['ID']);
if (!$elementId) {
    LocalRedirect('/catalog/');
    return;
}

// Получаем ID инфоблока "Товары"
$iblockId = 0;
$res = CIBlock::GetList(array(), array('CODE' => 'products'));
if ($arRes = $res->Fetch()) {
    $iblockId = $arRes['ID'];
}

if (!$iblockId) {
    echo '<div class="container"><div class="alert alert-danger">Инфоблок не найден</div></div>';
    require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
    return;
}

// Получаем данные товара
$arSelect = array(
    "ID",
    "NAME",
    "IBLOCK_SECTION_ID",
    "DETAIL_PICTURE",
    "PREVIEW_PICTURE",
    "DETAIL_TEXT",
    "PREVIEW_TEXT",
    "PROPERTY_PRICE",
    "PROPERTY_COLOR",
    "PROPERTY_MATERIAL",
    "PROPERTY_PLAYERS",
    "PROPERTY_GAME_TIME",
    "PROPERTY_AGE",
    "PROPERTY_PUBLISHER",
    "PROPERTY_GALLERY",
    "PROPERTY_RULES",
    "PROPERTY_VIDEO_URL"
);

$arFilter = array(
    "IBLOCK_ID" => $iblockId,
    "ID" => $elementId,
    "ACTIVE" => "Y"
);

$res = CIBlockElement::GetList(array(), $arFilter, false, false, $arSelect);
$product = $res->GetNext();

if (!$product) {
    LocalRedirect('/catalog/');
    return;
}

// Получаем раздел
$sectionName = '';
$sectionId = '';
if ($product['IBLOCK_SECTION_ID']) {
    $secRes = CIBlockSection::GetById($product['IBLOCK_SECTION_ID']);
    if ($sec = $secRes->Fetch()) {
        $sectionName = $sec['NAME'];
        $sectionId = $sec['ID'];
    }
}

// Главное изображение
$mainImage = '';
if ($product['DETAIL_PICTURE']) {
    $mainImage = CFile::GetPath($product['DETAIL_PICTURE']);
} elseif ($product['PREVIEW_PICTURE']) {
    $mainImage = CFile::GetPath($product['PREVIEW_PICTURE']);
} else {
    $mainImage = SITE_TEMPLATE_PATH . '/images/no-image.jpg';
}

// Галерея
$galleryImages = array();
if ($product['PROPERTY_GALLERY_VALUE']) {
    if (is_array($product['PROPERTY_GALLERY_VALUE'])) {
        foreach ($product['PROPERTY_GALLERY_VALUE'] as $fileId) {
            $galleryImages[] = CFile::GetPath($fileId);
        }
    } else {
        $galleryImages[] = CFile::GetPath($product['PROPERTY_GALLERY_VALUE']);
    }
}
if (empty($galleryImages) && $mainImage) {
    $galleryImages[] = $mainImage;
}

// Цена
$price = 'Цена по запросу';
$priceValue = 0;
if ($product['PROPERTY_PRICE_VALUE']) {
    $priceValue = (float)$product['PROPERTY_PRICE_VALUE'];
    $price = number_format($priceValue, 0, '', ' ') . ' ₽';
}

// Характеристики
$props = array();
if ($product['PROPERTY_PLAYERS_VALUE']) $props['Игроков'] = $product['PROPERTY_PLAYERS_VALUE'];
if ($product['PROPERTY_GAME_TIME_VALUE']) $props['Время партии'] = $product['PROPERTY_GAME_TIME_VALUE'];
if ($product['PROPERTY_AGE_VALUE']) $props['Возраст'] = $product['PROPERTY_AGE_VALUE'] . '+';
if ($product['PROPERTY_PUBLISHER_VALUE']) $props['Издатель'] = $product['PROPERTY_PUBLISHER_VALUE'];
if ($product['PROPERTY_COLOR_VALUE']) $props['Цвет'] = $product['PROPERTY_COLOR_VALUE'];
if ($product['PROPERTY_MATERIAL_VALUE']) $props['Материал'] = $product['PROPERTY_MATERIAL_VALUE'];

$APPLICATION->SetTitle($product['NAME']);

// Проверка авторизации
$userId = 0;
$userName = '';
$userAvatar = '';
$isAuthorized = false;
global $USER;
if ($USER->IsAuthorized()) {
    $isAuthorized = true;
    $userId = $USER->GetID();
    $userName = $USER->GetFullName() ?: $USER->GetLogin();
    $userAvatar = $USER->GetParam("PERSONAL_PHOTO");
    if ($userAvatar) {
        $userAvatar = CFile::GetPath($userAvatar);
    }
}

// Проверка покупки
$hasPurchased = false;
if ($isAuthorized && CModule::IncludeModule('sale')) {
    $dbOrders = CSaleOrder::GetList(
        array('DATE_INSERT' => 'DESC'),
        array('USER_ID' => $userId, 'LID' => SITE_ID, 'STATUS_ID' => array('F', 'P')),
        false, false, array('ID')
    );
    $orderIds = array();
    while ($arOrder = $dbOrders->Fetch()) $orderIds[] = $arOrder['ID'];
    
    if (!empty($orderIds)) {
        $dbBasket = CSaleBasket::GetList(
            array(),
            array('ORDER_ID' => $orderIds, 'PRODUCT_ID' => $elementId),
            false, false, array('ID')
        );
        if ($dbBasket->Fetch()) $hasPurchased = true;
    }
}

// Получаем отзывы
$reviews = array();
$topReviews = array('gold' => null, 'silver' => null, 'bronze' => null);

if (CModule::IncludeModule('iblock')) {
    $reviewsIblockId = 0;
    $res = CIBlock::GetList(array(), array('CODE' => 'reviews'));
    if ($arRes = $res->Fetch()) $reviewsIblockId = $arRes['ID'];
    
    if ($reviewsIblockId) {
        $arReviewSelect = array("ID", "NAME", "PREVIEW_TEXT", "PROPERTY_RATING", "PROPERTY_USER_NAME", "PROPERTY_USER_ID", "PROPERTY_PRO", "PROPERTY_CONTRA", "PROPERTY_LIKES", "PROPERTY_DISLIKES", "DATE_CREATE");
        $arReviewFilter = array("IBLOCK_ID" => $reviewsIblockId, "PROPERTY_PRODUCT_ID" => $elementId, "ACTIVE" => "Y");
        $resReviews = CIBlockElement::GetList(array("PROPERTY_LIKES" => "DESC"), $arReviewFilter, false, false, $arReviewSelect);
        
        while ($arReview = $resReviews->GetNext()) {
            $review = array(
                'id' => $arReview['ID'], 'user_name' => $arReview['PROPERTY_USER_NAME_VALUE'], 'user_id' => $arReview['PROPERTY_USER_ID_VALUE'],
                'text' => $arReview['PREVIEW_TEXT'], 'rating' => intval($arReview['PROPERTY_RATING_VALUE']),
                'pros' => $arReview['PROPERTY_PRO_VALUE'], 'cons' => $arReview['PROPERTY_CONTRA_VALUE'],
                'likes' => intval($arReview['PROPERTY_LIKES_VALUE']), 'dislikes' => intval($arReview['PROPERTY_DISLIKES_VALUE']),
                'date' => $arReview['DATE_CREATE'], 'has_purchased' => ($arReview['user_id'] && $arReview['user_id'] % 3 == 0)
            );
            $reviews[] = $review;
        }
        
        usort($reviews, function($a, $b) { return $b['likes'] - $a['likes']; });
        if (isset($reviews[0])) $topReviews['gold'] = $reviews[0];
        if (isset($reviews[1])) $topReviews['silver'] = $reviews[1];
        if (isset($reviews[2])) $topReviews['bronze'] = $reviews[2];
    }
}

// Избранное
$favorites = array();
if (isset($_COOKIE['favorites'])) {
    $favorites = json_decode($_COOKIE['favorites'], true);
    if (!is_array($favorites)) $favorites = array();
}
$isFavorite = in_array($elementId, $favorites);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <title><?= htmlspecialchars($product['NAME']) ?> — Мир Настольных Игр</title>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Шрифты -->
    <link href="https://fonts.googleapis.com/css2?family=MedievalSharp&family=Open+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        /* ===== ОСНОВНЫЕ ПЕРЕМЕННЫЕ ===== */
        :root {
            --silver: #c0c0c0;
            --bronze: #cd7f32;
            --text-dark: #2c3e50;
            --text-light: #7f8c8d;
            --bg-light: #f8f5f0;
            --font-headers: 'MedievalSharp', cursive;
            --font-main: 'Open Sans', sans-serif;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: var(--font-main);
            background: #0a0a0a;
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* ===== ПАРЯЩИЕ КУБИКИ ===== */
        .floating-dice {
            position: fixed;
            pointer-events: none;
            z-index: 0;
            font-size: 40px;
            opacity: 0.12;
            animation: floatAround 20s infinite ease-in-out;
        }
        .dice-1 { top: 10%; left: 2%; animation-delay: 0s; }
        .dice-2 { top: 20%; right: 3%; animation-delay: 2s; font-size: 60px; }
        .dice-3 { bottom: 15%; left: 5%; animation-delay: 5s; font-size: 50px; }
        .dice-4 { bottom: 30%; right: 8%; animation-delay: 1s; }
        .dice-5 { top: 50%; left: 10%; animation-delay: 7s; font-size: 70px; }
        .dice-6 { bottom: 40%; right: 15%; animation-delay: 3s; font-size: 45px; }

        @keyframes floatAround {
            0%, 100% { transform: translate(0, 0) rotate(0deg); }
            25% { transform: translate(-20px, -30px) rotate(90deg); }
            50% { transform: translate(20px, -50px) rotate(180deg); }
            75% { transform: translate(30px, 20px) rotate(270deg); }
        }

        /* ===== ДРАКОН ===== */
        .dragon {
            position: fixed;
            bottom: 20px;
            left: 20px;
            width: 100px;
            height: 100px;
            z-index: 1000;
            cursor: pointer;
            transition: transform 0.3s;
            filter: drop-shadow(0 5px 15px rgba(0,0,0,0.2));
        }
        .dragon:hover { transform: scale(1.1); }
        .dragon-body { position: relative; width: 100%; height: 100%; }
        .dragon-head {
            position: absolute;
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #2ecc71, #27ae60);
            border-radius: 50% 50% 40% 40%;
            top: 10px;
            left: 20px;
            box-shadow: 0 5px 0 #1e8449;
        }
        .dragon-eye-left, .dragon-eye-right {
            position: absolute;
            width: 12px;
            height: 12px;
            background: white;
            border-radius: 50%;
            top: 20px;
        }
        .dragon-eye-left { left: 15px; }
        .dragon-eye-right { right: 15px; }
        .dragon-pupil {
            width: 6px;
            height: 6px;
            background: #2c3e50;
            border-radius: 50%;
            position: absolute;
            top: 3px;
            left: 3px;
            transition: all 0.1s;
        }
        .dragon-mouth {
            position: absolute;
            width: 30px;
            height: 10px;
            background: #c0392b;
            border-radius: 0 0 10px 10px;
            bottom: 10px;
            left: 15px;
            transition: all 0.3s;
        }
        .dragon.mouth-open .dragon-mouth { height: 20px; border-radius: 0 0 20px 20px; }
        .dragon-horn {
            position: absolute;
            width: 0;
            height: 0;
            border-left: 10px solid transparent;
            border-right: 10px solid transparent;
            border-bottom: 20px solid #e67e22;
            top: -5px;
        }
        .dragon-horn-left { left: 10px; transform: rotate(-20deg); }
        .dragon-horn-right { right: 10px; transform: rotate(20deg); }
        .dragon-wing {
            position: absolute;
            width: 30px;
            height: 50px;
            background: linear-gradient(135deg, #2ecc71, #229954);
            border-radius: 50% 50% 0 0;
            top: 30px;
            animation: flap 2s infinite ease-in-out;
        }
        .dragon-wing-left { left: -15px; transform: rotate(-10deg); }
        .dragon-wing-right { right: -15px; transform: rotate(10deg); animation-delay: 0.5s; }
        @keyframes flap {
            0%, 100% { transform: rotate(-5deg) scaleY(1); }
            50% { transform: rotate(15deg) scaleY(0.8); }
        }
        .dragon-wing-right { animation-name: flap-right; }
        @keyframes flap-right {
            0%, 100% { transform: rotate(5deg) scaleY(1); }
            50% { transform: rotate(-15deg) scaleY(0.8); }
        }

        /* ===== КОНТЕЙНЕР ===== */
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 20px;
            position: relative;
            z-index: 10;
        }

        /* ===== ГАЛЕРЕЯ ===== */
        .gallery-carousel {
            position: relative;
            width: 100vw;
            border-top-left-radius: 30px;
            border-top-right-radius: 30px;
            margin-left: calc(-50vw + 50%);
            margin-right: calc(-50vw + 50%);
            height: 80vh;
            min-height: 550px;
            background: #000;
            overflow: hidden;
            z-index: 5;
        }

        .carousel-wrapper {
            position: relative;
            width: 100%;
            height: 100%;
            overflow: hidden;
        }

        .carousel-track {
            position: relative;
            width: 100%;
            height: 100%;
        }

        .carousel-item {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            overflow: hidden;
            border-radius: 20px;
            transition: none;
        }

        .carousel-item img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            background: transparent;
        }

        .carousel-item.center {
            width: 70%;
            height: 85%;
            left: 15%;
            z-index: 20;
            opacity: 1;
            filter: blur(0);
            cursor: default;
        }

        .carousel-btn {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: rgba(0,0,0,0.5);
            backdrop-filter: blur(5px);
            border: 1px solid rgba(255,255,255,0.2);
            color: white;
            font-size: 24px;
            cursor: pointer;
            transition: all 0.3s;
            z-index: 30;
        }

        .carousel-btn:hover {
            background: var(--DragonLightActiv);
            transform: translateY(-50%) scale(1.1);
        }

        .carousel-btn.prev { left: 30px; }
        .carousel-btn.next { right: 30px; }

        .carousel-dots {
            position: absolute;
            bottom: 20px;
            left: 0;
            right: 0;
            display: flex;
            justify-content: center;
            gap: 10px;
            z-index: 30;
        }

        .dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: rgba(255,255,255,0.05);
            cursor: pointer;
            transition: all 0.3s;
        }

        .dot.active {
            background: var(--GoldFake);
            width: 24px;
            border-radius: 4px;
        }

        @media (max-width: 768px) {
            .gallery-carousel {
                height: 60vh;
                min-height: 400px;
            }
            .carousel-btn {
                width: 40px;
                height: 40px;
                font-size: 18px;
            }
            .carousel-btn.prev { left: 15px; }
            .carousel-btn.next { right: 15px; }
        }

/* ===== ИНТЕРАКТИВНЫЕ КУБИКИ ===== */
.dice-interactive {
    position: fixed;
    z-index: 10000;
    pointer-events: auto;
    cursor: pointer;
    user-select: none;
    transition: transform 0.1s ease;
    width: 50px;
    height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    transition: all 0.2s ease;
}

.dice-interactive:hover {
    transform: scale(1.05);
}

/* Эффект отбрасывания */
.dice-flying {
    transition: all 0.5s cubic-bezier(0.25, 0.46, 0.45, 0.94);
    pointer-events: none;
    z-index: 10001;
}

/* Разные размеры кубиков */
.dice-small { font-size: 32px; width: 40px; height: 40px; }
.dice-medium { font-size: 48px; width: 56px; height: 56px; }
.dice-large { font-size: 64px; width: 72px; height: 72px; }

/* Анимация появления */
@keyframes diceAppear {
    from {
        opacity: 0;
        transform: scale(0) rotate(-180deg);
    }
    to {
        opacity: 1;
        transform: scale(1) rotate(0);
    }
}

.dice-interactive {
    animation: diceAppear 0.3s ease-out;
}

/* ===== РАЗВЁРНУТАЯ КНИГА ===== */
.book-wrapper {
    position: relative;
    margin-top: -235px;
    margin-bottom: 50px;
    padding: 40px 0;
    display: flex;
    justify-content: center;
}

.book {
    position: relative;
    width: 100%;
    background: #fff9ef;
    border-radius: 8px 20px 20px 8px;
    box-shadow: 0 20px 40px rgba(0,0,0,0.3);
    z-index: 20; /* Книга выше закладок */
}

/* Единый прямоугольник-корешок (заменяет и ::before и ::after) */
.book::before {
    content: '';
    position: absolute;
    top: -8px;
    left: -25px;
    right: -25px;
    bottom: -8px;
    background: linear-gradient(135deg, #6B3E1A 0%, #8B4513 50%, #6B3E1A 100%);
    border-radius: 12px;
    z-index: -1;
}

.book-pages {
    position: relative;
    background: #fff9ef;
    overflow: visible;
    min-height: 550px;
    z-index: 7;
}

/* Вертикальная полоса слева (имитация переплета) */
.book-pages::before {
    content: '';
    position: absolute;
    top: 0;
    left: -15px;
    width: 15px;
    height: 100%;
    background: linear-gradient(90deg, #DEB887 0%, #F5DEB3 50%, #DEB887 100%);
    border-radius: 3px 0 0 3px;
    z-index: 8;
    pointer-events: none;
}

/* Вертикальная полоса справа */
.book-pages::after {
    content: '';
    position: absolute;
    top: 0;
    right: -15px;
    width: 15px;
    height: 100%;
    background: linear-gradient(90deg, #F5DEB3 0%, #DEB887 50%, #F5DEB3 100%);
    border-radius: 0 3px 3px 0;
    z-index: 8;
    pointer-events: none;
}

/* Внутренний контент */
.book-content-inner {
    padding: 40px 50px;
    position: relative;
    z-index: 9;
    min-height: 500px;
}

/* Закладки - скрыты по умолчанию, показываются при наведении на область книги */
.bookmarks-left {
    position: absolute;
    left: -65px;
    top: 80px;
    display: flex;
    flex-direction: column;
    gap: 15px;
    z-index: 7;
}

.bookmarks-right {
    position: absolute;
    right: -65px;
    top: 80px;
    display: flex;
    flex-direction: column;
    gap: 15px;
    z-index: 7;
}

.bookmark-tab {
    padding: 12px 24px;
    background: linear-gradient(135deg, #8B7355 0%, #6B4E2E 100%);
    border: none;
    border-radius: 30px 8px 8px 30px;
    font-family: var(--font-headers);
    font-size: 15px;
    font-weight: bold;
    color: #D4C4A8;
    cursor: pointer;
    transition: all 0.3s ease;
    white-space: nowrap;
    letter-spacing: 1px;
    box-shadow: 0 3px 8px rgba(0,0,0,0.2);
}


.bookmarks-right .bookmark-tab {
    border-radius: 8px 30px 30px 8px;
}

/* Активная закладка - выдвигается и выше всех */
.bookmark-tab.active {
    color: white;
    transform: translateX(-100px); /* Выпирает вправо для левой части */
    z-index: 20;
}

.bookmarks-right .bookmark-tab.active {
    margin-right: 0;
}

/* Цвета для каждой вкладки (неактивные) */
.bookmark-tab[data-page="main"] {
    background: linear-gradient(135deg, #8B4513 0%, #5D3A1A 100%);
}

.bookmark-tab[data-page="description"] {
    background: linear-gradient(135deg, #2C5F2D 0%, #1E3A1E 100%);
}

.bookmark-tab[data-page="overview"] {
    background: linear-gradient(135deg, #2C3E50 0%, #1A2A36 100%);
}

.bookmark-tab[data-page="reviews"] {
    background: linear-gradient(135deg, #8B5A2B 0%, #5D3A1A 100%);
}

/* Активные вкладки - ярче и выдвигаются */
.bookmark-tab.active[data-page="main"] {
    background: linear-gradient(135deg, #E67E22 0%, #C0392B 100%);
    transform: translateX(-110px);
    box-shadow: 0 8px 20px rgba(230, 126, 34, 0.4);
}

.bookmark-tab.active[data-page="description"] {
    background: linear-gradient(135deg, #4CAF50 0%, #2E7D32 100%);
    transform: translateX(-110px);
    box-shadow: 0 8px 20px rgba(76, 175, 80, 0.4);
}

.bookmark-tab.active[data-page="overview"] {
    background: linear-gradient(135deg, #3498DB 0%, #1F618D 100%);
    transform: translateX(105px);
    box-shadow: 0 8px 20px rgba(52, 152, 219, 0.4);
}

.bookmark-tab.active[data-page="reviews"] {
    background: linear-gradient(135deg, #F39C12 0%, #E67E22 100%);
    transform: translateX(105px);
    box-shadow: 0 8px 20px rgba(243, 156, 18, 0.4);
}

/* Неактивные закладки - спрятаны под книгой */
.bookmark-tab:not(.active) {
    transform: translateX(0px);
    opacity: 0.5;
}

.bookmarks-right .bookmark-tab:not(.active) {
    transform: translateX(0px);
}

/* При наведении на неактивные - чуть выдвигаются */
.bookmark-tab:not(.active):hover {
    transform: translateX(-5px);
    opacity: 0.8;
}

.bookmarks-right .bookmark-tab:not(.active):hover {
    transform: translateX(5px);
}

/* Стили для главной страницы */
.main-page-grid {
    display: grid;
    grid-template-columns: 1fr 0.8fr;
    gap: 40px;
}

.main-info h1 {
    font-family: var(--font-headers);
    font-size: 38px;
    color: var(--text-dark);
    margin: 0 0 15px;
    line-height: 1.2;
    border-left: 4px solid var(--DragonLight);
    padding-left: 20px;
}

.main-meta {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
    margin-bottom: 25px;
}

.main-meta span {
    background: var(--bg-light);
    padding: 6px 14px;
    border-radius: 30px;
    font-size: 13px;
    color: var(--text-dark);
}

.badges-list {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
    margin-bottom: 25px;
}

.badge {
    background: white;
    padding: 10px 18px;
    border-radius: 40px;
    display: flex;
    align-items: center;
    gap: 8px;
    border: 1px solid #eee;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    font-size: 14px;
}

.props-list {
    background: var(--bg-light);
    border-radius: 20px;
    padding: 20px;
    margin-bottom: 25px;
}

.props-list h3 {
    font-family: var(--font-headers);
    margin-bottom: 15px;
    font-size: 18px;
}

.props-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
}

.prop-row {
    display: flex;
    justify-content: space-between;
    padding: 8px 0;
    border-bottom: 1px dashed #ddd;
}

.prop-name {
    color: var(--text-light);
    font-size: 13px;
}

.prop-value {
    font-weight: 600;
    color: var(--text-dark);
}

.action-buttons {
    display: flex;
    gap: 15px;
    margin-top: 25px;
}

.btn-book {
    padding: 14px 28px;
    border: none;
    border-radius: 40px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    gap: 10px;
    flex: 1;
    justify-content: center;
}

.btn-buy-book {
    background: linear-gradient(135deg, #f4c542, #e67e22);
    color: white;
    border: none;
    position: relative;
    overflow: hidden;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 6px 20px rgba(230, 126, 34, 0.4);
}

.btn-buy-book::before {
    content: '📚';
    position: absolute;
    right: -30px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 18px;
    opacity: 0;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

.btn-buy-book:hover::before {
    right: 20px;
    opacity: 1;
}

.btn-buy-book:hover {
    background: linear-gradient(135deg, #e67e22, #d35400);
    transform: translateY(-2px);
    box-shadow: 0 12px 28px rgba(230, 126, 34, 0.5);
}

.btn-buy-book:active {
    transform: translateY(0px);
    transition: transform 0.1s ease;
}

/* Эффект пульсации для кнопок */
.btn-favorite-book:focus-visible,
.btn-buy-book:focus-visible {
    outline: none;
    animation: btnPulse 0.5s ease-out;
}

@keyframes btnPulse {
    0% {
        box-shadow: 0 0 0 0 rgba(230, 126, 34, 0.4);
    }
    70% {
        box-shadow: 0 0 0 10px rgba(230, 126, 34, 0);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(230, 126, 34, 0);
    }
}

/* Адаптивность для мобильных */
@media (max-width: 768px) {
    .btn-favorite-book:hover::before,
    .btn-buy-book:hover::before {
        opacity: 0;
    }
    
    .btn-favorite-book:hover,
    .btn-buy-book:hover {
        transform: translateY(-1px);
    }
}

.btn-favorite-book {
    background: linear-gradient(135deg, #f5e6d3, #e8d5b5);
    color: #5a3e1b;
    border: none;
    position: relative;
    overflow: hidden;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.btn-favorite-book::before {
    content: '❤️';
    position: absolute;
    left: -30px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 18px;
    opacity: 0;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

.btn-favorite-book:hover::before {
    left: 20px;
    opacity: 1;
}

.btn-favorite-book:hover {
    background: linear-gradient(135deg, #e8c99e, #d4b17c);
    color: #fff5e6;
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
}

.btn-favorite-book:active {
    transform: translateY(0px);
    transition: transform 0.1s ease;
}

.btn-favorite-book.active {
    background: #fff0f0;
    color: var(--DragonLight);
    border-color: var(--DragonLight);
}

.price-card {
    background: linear-gradient(135deg, #fff5e6, #ffe6d5);
    padding: 25px;
    border-radius: 25px;
    margin-top: 5px;
    margin-bottom: 25px;
    text-align: center;
    position: relative;
    overflow: hidden;
}

.price-card::after {
    content: '🎲';
    position: absolute;
    bottom: -10px;
    right: -10px;
    font-size: 60px;
    opacity: 0.1;
    transform: rotate(-15deg);
}

.price-value {
    font-size: 48px;
    font-weight: 800;
    color: var(--DragonLight);
}

.price-label {
    font-size: 14px;
    color: var(--text-light);
    margin-top: 5px;
}

.main-description {
    background: var(--bg-light);
    border-radius: 20px;
    padding: 25px;
    margin-top: 20px;
}

.main-description i {
    color: var(--GoldFake);
    font-size: 28px;
    margin-bottom: 15px;
    display: block;
}

/* Анимация перелистывания */
@keyframes pageFlipIn {
    0% {
        opacity: 0;
        transform: perspective(1000px) rotateY(10deg) translateX(30px);
        filter: blur(2px);
    }
    100% {
        opacity: 1;
        transform: perspective(1000px) rotateY(0) translateX(0);
        filter: blur(0);
    }
}

.book-page-content {
    display: none;
}

.book-page-content.active {
    display: block;
    animation: pageFlipIn 0.5s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Адаптивность */
@media (max-width: 1024px) {
    .bookmarks-left, .bookmarks-right {
        position: relative;
        left: 0;
        right: 0;
        top: 0;
        flex-direction: row;
        justify-content: center;
        margin-bottom: 20px;
        z-index: 15;
    }
    
    .bookmark-tab:not(.active) {
        transform: none;
        opacity: 0.6;
    }
    
    .bookmark-tab.active {
        transform: scale(1.05);
    }
    
    .bookmark-tab:not(.active):hover {
        transform: none;
        opacity: 0.9;
    }
}

        /* ===== ОСНОВНАЯ КАРТОЧКА ===== */
        .product-card {
            top: -200px;
            display: grid;
            grid-template-columns: 1fr 0.9fr;
            gap: 50px;
            background: white;
            border-radius: 40px;
            padding: 40px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            position: relative;
            z-index: 15;
        }

        .product-title {
            font-family: var(--font-headers);
            font-size: 42px;
            color: var(--text-dark);
            margin: 0 0 15px;
            line-height: 1.2;
            text-shadow: 3px 3px 0 rgba(234, 187, 102, 0.2);
        }

        .product-meta {
            display: flex;
            gap: 15px;
            margin-bottom: 25px;
            flex-wrap: wrap;
        }

        .product-code, .product-publisher {
            background: var(--bg-light);
            padding: 8px 16px;
            border-radius: 40px;
            font-size: 14px;
            color: var(--text-dark);
        }

        .product-price-block {
            background: linear-gradient(135deg, #fff5e6, #ffe6d5);
            padding: 25px;
            border-radius: 25px;
            margin-bottom: 25px;
            position: relative;
            overflow: hidden;
        }

        .product-price-block::after {
            content: '🎲';
            position: absolute;
            bottom: -10px;
            right: -10px;
            font-size: 80px;
            opacity: 0.1;
            transform: rotate(-15deg);
        }

        .product-price {
            font-size: 52px;
            font-weight: 700;
            color: var(--DragonLight);
            line-height: 1;
        }

        .product-badges {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 25px;
        }

        .badge-item {
            background: white;
            padding: 10px 18px;
            border-radius: 40px;
            display: flex;
            align-items: center;
            gap: 8px;
            border: 2px solid #eee;
            transition: all 0.3s;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }

        .badge-item:hover {
            border-color: var(--DragonLight);
            transform: translateY(-3px);
        }

        .product-props {
            margin-bottom: 25px;
        }

        .props-title {
            font-family: var(--font-headers);
            color: var(--text-dark);
            margin: 0 0 15px;
            font-size: 22px;
        }

        .props-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
        }

        .prop-item {
            background: var(--bg-light);
            padding: 12px 15px;
            border-radius: 15px;
            border: 1px solid #eee;
        }

        .prop-name {
            color: var(--text-light);
            font-size: 13px;
            display: block;
            margin-bottom: 5px;
        }

        .prop-value {
            color: var(--text-dark);
            font-weight: 600;
            font-size: 16px;
        }

        .product-actions {
            display: flex;
            gap: 15px;
            margin-bottom: 25px;
        }

        .btn {
            padding: 18px 28px;
            border: none;
            border-radius: 20px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .btn-buy {
            background: linear-gradient(135deg, var(--GoldFake), var(--DragonLight));
            color: white;
            flex: 2;
            box-shadow: 0 8px 20px rgba(231, 76, 60, 0.3);
        }

        .btn-buy:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(231, 76, 60, 0.4);
        }

        .btn-favorite {
            background: white;
            color: var(--text-dark);
            border: 2px solid #eee;
            flex: 1;
        }

        .btn-favorite:hover {
            border-color: var(--DragonLight);
            color: var(--DragonLight);
        }

        .btn-favorite.active {
            background: #fff0f0;
            color: var(--DragonLight);
            border-color: var(--DragonLight);
        }

        /* ===== КНИЖНЫЕ ЗАКЛАДКИ ===== */
        .book-tabs {
            margin: 50px 0 30px;
        }

        .bookmark-header {
            display: flex;
            gap: 5px;
            padding-left: 30px;
        }

        .bookmark {
            padding: 15px 30px 20px;
            background: linear-gradient(135deg, #f8f5f0, #e8e0d5);
            border: 2px solid #eee;
            border-bottom: none;
            border-radius: 20px 20px 0 0;
            font-family: var(--font-headers);
            font-size: 18px;
            color: var(--text-dark);
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 10px;
            transform: translateY(10px);
        }

        .bookmark:hover {
            transform: translateY(5px);
        }

        .bookmark.active {
            transform: translateY(0);
            background: white;
            color: var(--DragonLight);
            z-index: 10;
        }

        .bookmark.gold { border-top: 3px solid var(--Gold); }
        .bookmark.silver { border-top: 3px solid var(--silver); }
        .bookmark.red { border-top: 3px solid var(--DragonLight); }
        .bookmark.green { border-top: 3px solid #27ae60; }

        .book-content {
            background: white;
            border: 2px solid #eee;
            border-radius: 0 20px 20px 20px;
            padding: 30px;
            margin-top: -2px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            min-height: 400px;
        }

        .book-page {
            display: none;
        }

        .book-page.active {
            display: block;
            animation: fadeIn 0.5s;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateX(20px); }
            to { opacity: 1; transform: translateX(0); }
        }

        /* ===== ПЬЕДЕСТАЛ ===== */
        .podium-section {
            margin: 40px 0;
            padding: 30px;
            background: linear-gradient(135deg, #2c3e50, #1a2632);
            border-radius: 40px;
        }

        .podium-title {
            font-family: var(--font-headers);
            color: var(--GoldFake);
            text-align: center;
            font-size: 32px;
            margin-bottom: 30px;
        }

        .podium {
            display: flex;
            align-items: flex-end;
            justify-content: center;
            gap: 20px;
            flex-wrap: wrap;
        }

        .podium-place {
            flex: 1;
            max-width: 280px;
            text-align: center;
            transition: all 0.3s;
        }

        .podium-place:hover {
            transform: translateY(-10px);
        }

        .podium-avatar {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--GoldFake), var(--DragonLight));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            border: 5px solid;
            font-size: 32px;
            font-weight: bold;
            color: white;
        }

        .podium-place.gold .podium-avatar { border-color: var(--Gold); box-shadow: 0 0 30px gold; }
        .podium-place.silver .podium-avatar { border-color: var(--silver); box-shadow: 0 0 30px silver; }
        .podium-place.bronze .podium-avatar { border-color: var(--bronze); box-shadow: 0 0 30px #cd7f32; }

        .podium-name { color: white; font-size: 18px; font-weight: 600; margin-bottom: 5px; }
        .podium-stats { color: var(--GoldFake); margin-bottom: 10px; }
        .podium-review {
            background: rgba(255,255,255,0.1);
            padding: 15px;
            border-radius: 20px;
            color: white;
            font-size: 14px;
        }

        .podium-base {
            height: 120px;
            background: linear-gradient(135deg, #555, #333);
            border-radius: 20px 20px 0 0;
            margin-top: 20px;
        }
        .podium-place.gold .podium-base { background: linear-gradient(135deg, #f1c40f, #e67e22); }
        .podium-place.silver .podium-base { background: linear-gradient(135deg, #bdc3c7, #95a5a6); }
        .podium-place.bronze .podium-base { background: linear-gradient(135deg, #e67e22, #d35400); }

        /* ===== ЛЕНТЫ С ОТЗЫВАМИ ===== */
        .reviews-streams {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            pointer-events: none;
            z-index: 5;
        }

        .stream-left, .stream-right {
            position: fixed;
            top: 0;
            width: 320px;
            height: 100%;
            pointer-events: none;
        }

        .stream-left { left: 0; }
        .stream-right { right: 0; }

        .stream-container {
            position: relative;
            width: 100%;
            height: 100%;
            overflow: hidden;
        }

        .stream-item {
            position: absolute;
            width: 280px;
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(5px);
            border-radius: 20px;
            padding: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            border: 1px solid rgba(0,0,0,0.05);
            cursor: grab;
            transition: all 0.3s;
            pointer-events: auto;
            animation: floatDown 28s linear infinite;
            left: 20px;
        }

        .stream-left .stream-item { animation: floatDown 28s linear infinite; }
        .stream-right .stream-item { animation: floatUp 28s linear infinite; }

        @keyframes floatDown {
            0% { transform: translateY(-100%); opacity: 0; }
            10% { opacity: 1; }
            90% { opacity: 1; }
            100% { transform: translateY(1000%); opacity: 0; }
        }

        @keyframes floatUp {
            0% { transform: translateY(100%); opacity: 0; }
            10% { opacity: 1; }
            90% { opacity: 1; }
            100% { transform: translateY(-1000%); opacity: 0; }
        }

        .stream-item:hover {
            animation-play-state: paused;
            transform: scale(1.03);
            box-shadow: 0 20px 40px rgba(0,0,0,0.3);
            cursor: grabbing;
        }

        .stream-item.dragging {
            position: fixed;
            z-index: 10000;
            animation: none;
            cursor: grabbing;
            opacity: 0.95;
            transform: scale(1.05);
        }

        .review-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 10px;
        }

        .review-avatar {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, var(--GoldFake), var(--DragonLight));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
        }

        .review-author strong { display: block; color: var(--text-dark); font-size: 14px; }
        .review-badge { font-size: 10px; padding: 2px 6px; border-radius: 20px; background: var(--Gold); color: #000; }
        .review-badge.guest { background: #95a5a6; color: white; }
        .review-rating { color: #ffc107; font-size: 12px; margin-bottom: 8px; }
        .review-text { font-size: 13px; line-height: 1.4; margin-bottom: 8px; color: #333; }
        .review-stats { display: flex; gap: 10px; font-size: 12px; }
        .review-like, .review-dislike {
            display: flex;
            align-items: center;
            gap: 4px;
            cursor: pointer;
            padding: 4px 8px;
            border-radius: 20px;
            background: #f0f0f0;
        }

        .pinned-reviews {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin: 20px 0;
            justify-content: center;
        }

        .pinned-review {
            background: white;
            border-radius: 20px;
            padding: 15px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            border-left: 4px solid var(--DragonLight);
            position: relative;
            width: 280px;
            cursor: pointer;
        }

        .pinned-review .unpin-btn {
            position: absolute;
            top: -10px;
            right: -10px;
            width: 28px;
            height: 28px;
            background: var(--DragonLight);
            color: white;
            border: none;
            border-radius: 50%;
            cursor: pointer;
            display: none;
            align-items: center;
            justify-content: center;
        }

        .pinned-review:hover .unpin-btn { display: flex; }

        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 12px 20px;
            background: #27ae60;
            color: white;
            border-radius: 50px;
            z-index: 10001;
            animation: slideIn 0.3s ease, fadeOut 0.3s ease 2.7s;
            font-size: 14px;
        }

        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }

        /* Модальное окно */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.8);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 10000;
            backdrop-filter: blur(5px);
        }

        .modal {
            background: white;
            border-radius: 30px;
            padding: 35px;
            max-width: 500px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal-close {
            float: right;
            font-size: 28px;
            cursor: pointer;
            background: none;
            border: none;
        }

        .rating-selector { display: flex; gap: 10px; font-size: 40px; cursor: pointer; margin: 15px 0; }
        .rating-selector span { color: #ddd; transition: all 0.2s; }
        .rating-selector span.active { color: #ffc107; }

        textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #eee;
            border-radius: 15px;
            resize: vertical;
            font-family: inherit;
        }

        .form-actions { display: flex; gap: 15px; justify-content: flex-end; margin-top: 20px; }
        .btn-secondary { padding: 10px 20px; background: #f0f0f0; border: none; border-radius: 10px; cursor: pointer; }
        .btn-primary { padding: 10px 20px; background: var(--DragonLight); color: white; border: none; border-radius: 10px; cursor: pointer; }

        /* ===== КИНОЗАЛ С НАЛОЖЕНИЕМ ===== */
        .seats-area {
            background: linear-gradient(180deg, #000000 0%, #ffffff00 100%);
            padding: 40px 20px 200px 20px;rgba(255, 255, 255, 0)
            position: relative;
            margin-left: calc(-50vw + 50%);
            margin-right: calc(-50vw + 50%);
            width: 100vw;
        }

        .seats-container {
            height: 325px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .seats-title {
            text-align: center;
            color: var(--GoldFake);
            font-family: var(--font-headers);
            margin-bottom: 30px;
            font-size: 28px;
            text-shadow: 0 0 10px rgba(234, 187, 102, 0.5);
        }

        .cinema-stage {
            margin-left: 145px !important;
            margin-top: 100px !important;
            height: 131px;
            position: relative;
            width: 100%;
            max-width: 1000px;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .cinema-row {
            display: flex;
            justify-content: center;
            position: relative;
            width: 100%;
            overflow: visible;
        }

        .row-bottom {
            margin-left: -100px;
            position: relative;
            z-index: 1;
        }

        .row-top {
            position: relative;
            transform: translateY(-110px);
            z-index: 2;
        }

        .row-background {
            width: 200%;
            height: auto;
            display: block;
            pointer-events: none;
            border-radius: 20px;
        }

        .viewers-layer {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
        }

        .viewer-spot {
            position: absolute;
            width: 60px;
            height: 60px;
            transform: translate(-50%, -50%);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            pointer-events: auto;
        }

        .viewer-entity {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            opacity: 0;
            transition: all 0.3s ease;
        }

        .viewer-spot.occupied .viewer-entity {
            cursor: auto;
            opacity: 1 !important;
            width: 48px !important;
            height: 52px !important;
            border-radius: 45% 45% 40% 40% !important;
            display: block !important;
            position: relative !important;
            animation: viewerBreathing 3s infinite ease-in-out !important;
        }

        .viewer-spot.occupied .viewer-entity::before {
            content: '' !important;
            position: absolute !important;
            top: -20px !important;
            left: 50% !important;
            transform: translateX(-50%) !important;
            width: 50px !important;
            height: 50px !important;
            background: radial-gradient(circle at 50% 45%,
                rgba(255, 255, 255, 0.95) 0%,
                rgba(255, 255, 255, 0.85) 100%) !important;
            border-radius: 50% !important;
            box-shadow: 0 2px 6px rgba(0,0,0,0.2) !important;
        }

        .viewer-spot.occupied .viewer-entity::after {
            content: '' !important;
            position: absolute !important;
            top: 24px !important;
            left: 50% !important;
            transform: translateX(-50%) !important;
            width: 125px !important;
            height: 60px !important;
            background: radial-gradient(ellipse at 50% 40%,
                rgba(255, 255, 255, 0.9) 0%,
                rgba(255, 255, 255, 0.8) 100%) !important;
            border-radius: 50% !important;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2) !important;
        }

        .seats-avatars-layer {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 10;
        }

        .seat-avatar {
            position: absolute;
            width: 46px;
            height: 46px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            font-weight: bold;
            color: white;
            box-shadow: 0 3px 8px rgba(0,0,0,0.3);
            cursor: pointer;
            transition: transform 0.2s;
            border: 2px solid rgba(255,255,255,0.8);
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            pointer-events: auto;
            z-index: 15;
        }

        .seat-avatar:hover {
            transform: scale(1.15);
            z-index: 20;
        }

        .seat-tooltip {
            position: absolute;
            top: -65px; 
            left: 50%;
            transform: translateX(-50%);
            background: rgba(0,0,0,0.9);
            color: white;
            padding: 4px 10px;
            border-radius: 15px;
            font-size: 11px;
            white-space: nowrap;
            pointer-events: none;
            opacity: 0;
            transition: opacity 0.2s;
        }

        .seat-avatar:hover .seat-tooltip {
            opacity: 1;
        }

        .viewer-spot .viewer-tooltip {
            position: absolute !important;
            bottom: -45px !important;
            left: 50% !important;
            transform: translateX(-50%) !important;
            background: rgba(0,0,0,0.85) !important;
            color: white !important;
            padding: 5px 12px !important;
            border-radius: 20px !important;
            font-size: 11px !important;
            white-space: nowrap !important;
            pointer-events: none !important;
            opacity: 0 !important;
            transition: opacity 0.2s !important;
            z-index: 20 !important;
        }

        .viewer-spot:hover .viewer-tooltip {
            opacity: 1 !important;
        }

        @keyframes viewerBreathing {
            0%, 100% {
                transform: scale(1);
                filter: brightness(1);
            }
            50% {
                transform: scale(1.02);
                filter: brightness(1.08);
            }
        }

        .viewers-stats {
            color: var(--GoldFake);
            font-size: 18px;
            margin-bottom: 10px;
        }

        .viewers-stats .viewers-count {
            font-size: 28px;
            font-weight: bold;
            margin-right: 8px;
        }

        @media (max-width: 768px) {
            .viewer-spot {
                width: 40px;
                height: 40px;
            }
            .viewer-entity {
                width: 30px;
                height: 30px;
            }
            .row-top {
                transform: translateY(-60px);
            }
        }

        @media (max-width: 1200px) {
            .product-card { grid-template-columns: 1fr; gap: 30px; }
            .stream-left, .stream-right { display: none; }
            .gallery-carousel { height: 450px; }
        }

        @media (max-width: 768px) {
            .gallery-carousel { height: 350px; }
            .product-title { font-size: 32px; }
            .props-grid { grid-template-columns: 1fr; }
            .product-actions { flex-direction: column; }
            .bookmark { padding: 12px 20px; font-size: 14px; }
        }
    </style>
</head>
<body>

<audio id="pageFlipSound" preload="auto">
    <source src="https://www.soundjay.com/misc/sounds/page-flip-01.mp3" type="audio/mpeg">
</audio>

<?if (!$USER->IsAuthorized()):?>
<style>
    body {
        margin-top: 0 !important;
        padding-top: 200px !important;
    }
</style>
<?endif?>

<?if ($USER->IsAuthorized()):?>
<style>
    body {
        margin-top: 0 !important;
        padding-top: 160px !important;
    }
</style>
<?endif?>

    <!-- Парящие кубики -->
    <div class="floating-dice dice-1">🎲</div>
    <div class="floating-dice dice-2">🎲</div>
    <div class="floating-dice dice-3">🎲</div>
    <div class="floating-dice dice-4">🎲</div>
    <div class="floating-dice dice-5">🎲</div>
    <div class="floating-dice dice-6">🎲</div>
    
    <!-- Дракон -->
    <div class="dragon" id="dragon">
        <div class="dragon-body">
            <div class="dragon-head">
                <div class="dragon-eye-left"><div class="dragon-pupil"></div></div>
                <div class="dragon-eye-right"><div class="dragon-pupil"></div></div>
                <div class="dragon-mouth"></div>
                <div class="dragon-horn dragon-horn-left"></div>
                <div class="dragon-horn dragon-horn-right"></div>
            </div>
            <div class="dragon-wing dragon-wing-left"></div>
            <div class="dragon-wing dragon-wing-right"></div>
        </div>
    </div>
    
    <div class="container">
        <!-- Галерея-карусель -->
        <div class="gallery-carousel" id="galleryCarousel">
            <div class="carousel-wrapper">
                <div class="carousel-track" id="carouselTrack">
                    <?php foreach ($galleryImages as $index => $img): ?>
                        <div class="carousel-item" data-index="<?= $index ?>" onclick="openFullscreen(<?= $index ?>)">
                            <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($product['NAME']) ?>">
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <button class="carousel-btn prev" id="prevBtn">
                <i class="fas fa-chevron-left"></i>
            </button>
            <button class="carousel-btn next" id="nextBtn">
                <i class="fas fa-chevron-right"></i>
            </button>
            <div class="carousel-dots" id="carouselDots"></div>
        </div>

        <div class="seats-area">
            <div class="seats-container">
                <h3 class="seats-title"><i class="fas fa-users"></i> Зрители в зале <span class="viewers-count" id="viewersCount">0</span></h3>
                
                <div class="cinema-stage">
                    <!-- Верхний ряд (слот 1) -->
                    <div class="cinema-row row-bottom">
                        <img src="/upload/seats/slote1.png" style="width: 90%; margin-bottom: -12px; z-index: 3;" alt="Нижний ряд" class="row-background">
                        <div class="viewers-layer" id="viewers-layer-top">
                            <div class="viewer-spot" style="left: 17%; top: -10%;"><div class="viewer-entity"></div></div>
                            <div class="viewer-spot" style="left: 28%; top: -10%;"><div class="viewer-entity"></div></div>
                            <div class="viewer-spot" style="left: 46%; top: -10%;"><div class="viewer-entity"></div></div>
                            <div class="viewer-spot" style="left: 64%; top: -10%;"><div class="viewer-entity"></div></div>
                        </div>
                        <div class="seats-avatars-layer"></div>
                    </div>
                    
                    <!-- Нижний ряд (слот 2) -->
                    <div class="cinema-row row-top">
                        <img src="/upload/seats/slote2.png" style="width: 200%; margin-left: -80px; margin-top: 26px; z-index: 1;" alt="Верхний ряд" class="row-background">
                        <div class="viewers-layer" id="viewers-layer-bottom">
                            <div class="viewer-spot" style="left: 0%; top: 0%;"><div class="viewer-entity"></div></div>
                            <div class="viewer-spot" style="left: 28%; top: 0%;"><div class="viewer-entity"></div></div>
                            <div class="viewer-spot" style="left: 64%; top: 0%;"><div class="viewer-entity"></div></div>
                            <div class="viewer-spot" style="left: 69%; top: 0%;"><div class="viewer-entity"></div></div>
                            <div class="viewer-spot" style="left: 92%; top: 0%;"><div class="viewer-entity"></div></div>
                        </div>
                        <div class="seats-avatars-layer"></div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Основная карточка товара (остается без изменений) -->
        <div class="book-wrapper">
            <!-- Сама книга -->
            <div class="book">
                    <!-- Закладки СЛЕВА - снаружи книги -->
                <div class="bookmarks-left">
                    <button class="bookmark-tab active" data-page="main">
                        <i class="fas fa-home"></i>ㅤㅤГлавная
                    </button>
                    <button class="bookmark-tab" data-page="description">
                        <i class="fas fa-scroll"></i>ㅤㅤОписание
                    </button>
                </div>
                
                <!-- Закладки СПРАВА - снаружи книги -->
                <div class="bookmarks-right">
                    <button class="bookmark-tab" data-page="overview">
                        Обзор ㅤ ㅤ <i class="fas fa-video"></i>
                    </button>
                    <button class="bookmark-tab" data-page="reviews">
                        Отзывыㅤㅤ<i class="fas fa-star"></i>
                    </button>
                </div>
                <div class="book-pages">
                    <div class="book-content-inner">
                        <!-- Страница Главная -->
                        <div class="book-page-content active" id="page-main">
                            <div class="main-page-grid">
                                <div class="main-info">
                                    <h1><?= htmlspecialchars($product['NAME']) ?></h1>
                                    
                                    <div class="main-meta">
                                        <span><i class="fas fa-barcode"></i> Артикул: #<?= $product['ID'] ?></span>
                                        <?php if ($product['PROPERTY_PUBLISHER_VALUE']): ?>
                                            <span><i class="fas fa-building"></i> <?= htmlspecialchars($product['PROPERTY_PUBLISHER_VALUE']) ?></span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="badges-list">
                                        <?php if ($product['PROPERTY_PLAYERS_VALUE']): ?>
                                            <div class="badge"><i class="fas fa-users"></i> <?= htmlspecialchars($product['PROPERTY_PLAYERS_VALUE']) ?></div>
                                        <?php endif; ?>
                                        <?php if ($product['PROPERTY_GAME_TIME_VALUE']): ?>
                                            <div class="badge"><i class="fas fa-hourglass-half"></i> <?= htmlspecialchars($product['PROPERTY_GAME_TIME_VALUE']) ?></div>
                                        <?php endif; ?>
                                        <?php if ($product['PROPERTY_AGE_VALUE']): ?>
                                            <div class="badge"><i class="fas fa-birthday-cake"></i> <?= htmlspecialchars($product['PROPERTY_AGE_VALUE']) ?>+</div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <?php if (!empty($props)): ?>
                                        <div class="props-list">
                                            <h3><i class="fas fa-list-ul"></i> Характеристики</h3>
                                            <div class="props-grid">
                                                <?php foreach ($props as $name => $value): ?>
                                                    <div class="prop-row">
                                                        <span class="prop-name"><?= htmlspecialchars($name) ?></span>
                                                        <span class="prop-value"><?= htmlspecialchars($value) ?></span>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="action-buttons">
                                        <button class="btn-book btn-buy-book" onclick="addToCart(<?= $product['ID'] ?>)">
                                            <i class="fas fa-shopping-cart"></i> В корзину
                                        </button>
                                        <button class="btn-book btn-favorite-book <?= $isFavorite ? 'active' : '' ?>" id="favoriteBtn" onclick="toggleFavorite(<?= $product['ID'] ?>)">
                                            <?= $isFavorite ? 'В избранном' : 'В избранное' ?> <i class="fas fa-heart"></i>
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="main-sidebar">
                                    <div class="price-card">
                                        <div class="price-value"><?= $price ?></div>
                                        <div class="price-label"><i class="fas fa-gift"></i> Цена за полный комплект</div>
                                    </div>
                                    
                                    <?php if ($product['PREVIEW_TEXT']): ?>
                                        <div class="main-description">
                                            <i class="fas fa-quote-left"></i>
                                            <p><?= nl2br(htmlspecialchars($product['PREVIEW_TEXT'])) ?></p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Страница Описание -->
                        <div class="book-page-content" id="page-description">
                            <div style="line-height: 1.8; font-size: 16px;">
                                <?php if ($product['DETAIL_TEXT']): ?>
                                    <?= $product['DETAIL_TEXT'] ?>
                                <?php else: ?>
                                    <p style="text-align: center; padding: 50px;">
                                        <i class="fas fa-book-open" style="font-size: 48px; opacity: 0.3; display: block; margin-bottom: 15px;"></i>
                                        Описание пока в разработке. Скоро здесь появится подробная информация об игре.
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Страница Обзор -->
                        <div class="book-page-content" id="page-overview">
                            <div class="overview-content">
                                <?php if ($product['PROPERTY_VIDEO_URL_VALUE']): ?>
                                    <iframe src="<?= htmlspecialchars($product['PROPERTY_VIDEO_URL_VALUE']) ?>" frameborder="0" allowfullscreen style="width: 100%; height: 450px; border-radius: 20px;"></iframe>
                                <?php else: ?>
                                    <p style="text-align: center; padding: 80px;">
                                        <i class="fas fa-video-slash" style="font-size: 64px; opacity: 0.3; display: block; margin-bottom: 20px;"></i>
                                        Видеообзор скоро появится
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Страница Отзывы -->
                        <div class="book-page-content" id="page-reviews">
                            <?php if ($topReviews['gold'] || $topReviews['silver'] || $topReviews['bronze']): ?>
                                <div class="podium-section">
                                    <h2 class="podium-title"><i class="fas fa-trophy"></i> Лучшие отзывы</h2>
                                    <div class="podium">
                                        <?php if ($topReviews['silver']): ?>
                                            <div class="podium-place silver">
                                                <div class="podium-avatar"><?= substr($topReviews['silver']['user_name'], 0, 1) ?></div>
                                                <div class="podium-name"><?= htmlspecialchars($topReviews['silver']['user_name']) ?></div>
                                                <div class="podium-stats"><i class="fas fa-thumbs-up"></i> <?= $topReviews['silver']['likes'] ?></div>
                                                <div class="podium-review">"<?= htmlspecialchars(substr($topReviews['silver']['text'], 0, 80)) ?>..."</div>
                                                <div class="podium-base"></div>
                                            </div>
                                        <?php endif; ?>
                                        <?php if ($topReviews['gold']): ?>
                                            <div class="podium-place gold">
                                                <div class="podium-avatar"><?= substr($topReviews['gold']['user_name'], 0, 1) ?></div>
                                                <div class="podium-name"><?= htmlspecialchars($topReviews['gold']['user_name']) ?></div>
                                                <div class="podium-stats"><i class="fas fa-thumbs-up"></i> <?= $topReviews['gold']['likes'] ?></div>
                                                <div class="podium-review">"<?= htmlspecialchars(substr($topReviews['gold']['text'], 0, 80)) ?>..."</div>
                                                <div class="podium-base"></div>
                                            </div>
                                        <?php endif; ?>
                                        <?php if ($topReviews['bronze']): ?>
                                            <div class="podium-place bronze">
                                                <div class="podium-avatar"><?= substr($topReviews['bronze']['user_name'], 0, 1) ?></div>
                                                <div class="podium-name"><?= htmlspecialchars($topReviews['bronze']['user_name']) ?></div>
                                                <div class="podium-stats"><i class="fas fa-thumbs-up"></i> <?= $topReviews['bronze']['likes'] ?></div>
                                                <div class="podium-review">"<?= htmlspecialchars(substr($topReviews['bronze']['text'], 0, 80)) ?>..."</div>
                                                <div class="podium-base"></div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <div class="pinned-reviews" id="pinnedReviews"></div>
                            
                            <div class="reviews-actions" style="text-align: center; margin-top: 20px;">
                                <?php if ($hasPurchased): ?>
                                    <button class="btn btn-primary" onclick="openReviewModal()"><i class="fas fa-pen"></i> Написать отзыв</button>
                                <?php elseif ($isAuthorized): ?>
                                    <p><i class="fas fa-lock"></i> Вы сможете оставить отзыв после покупки</p>
                                <?php else: ?>
                                    <p><i class="fas fa-sign-in-alt"></i> <a href="/login/">Войдите</a>, чтобы оставить отзыв</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Ленты с отзывами (оставляем как есть) -->
    <div class="reviews-streams">
        <div class="stream-left">
            <div class="stream-container" id="streamLeft">
                <?php foreach ($reviews as $review): ?>
                    <div class="stream-item" data-id="<?= $review['id'] ?>" style="top: <?= rand(10, 90) ?>%; animation-delay: -<?= rand(0, 22) ?>s;">
                        <div class="review-header">
                            <div class="review-avatar"><?= substr($review['user_name'], 0, 1) ?></div>
                            <div class="review-author">
                                <strong><?= htmlspecialchars($review['user_name']) ?></strong>
                                <span class="review-badge <?= $review['has_purchased'] ? 'purchased' : 'guest' ?>"><?= $review['has_purchased'] ? '<i class="fas fa-check-circle"></i> Купил' : '<i class="fas fa-user"></i> Гость' ?></span>
                            </div>
                        </div>
                        <div class="review-rating"><?= str_repeat('★', $review['rating']) . str_repeat('☆', 5 - $review['rating']) ?></div>
                        <div class="review-text"><?= htmlspecialchars(substr($review['text'], 0, 100)) ?>...</div>
                        <div class="review-stats">
                            <span class="review-like" onclick="likeReview(<?= $review['id'] ?>, event)"><i class="fas fa-thumbs-up"></i> <span class="likes-count"><?= $review['likes'] ?></span></span>
                            <span class="review-dislike" onclick="dislikeReview(<?= $review['id'] ?>, event)"><i class="fas fa-thumbs-down"></i> <span class="dislikes-count"><?= $review['dislikes'] ?></span></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="stream-right">
            <div class="stream-container" id="streamRight">
                <?php foreach ($reviews as $review): ?>
                    <div class="stream-item" data-id="<?= $review['id'] ?>" style="top: <?= rand(10, 90) ?>%; animation-delay: -<?= rand(0, 22) ?>s;">
                        <div class="review-header">
                            <div class="review-avatar"><?= substr($review['user_name'], 0, 1) ?></div>
                            <div class="review-author">
                                <strong><?= htmlspecialchars($review['user_name']) ?></strong>
                                <span class="review-badge <?= $review['has_purchased'] ? 'purchased' : 'guest' ?>"><?= $review['has_purchased'] ? '<i class="fas fa-check-circle"></i> Купил' : '<i class="fas fa-user"></i> Гость' ?></span>
                            </div>
                        </div>
                        <div class="review-rating"><?= str_repeat('★', $review['rating']) . str_repeat('☆', 5 - $review['rating']) ?></div>
                        <div class="review-text"><?= htmlspecialchars(substr($review['text'], 0, 100)) ?>...</div>
                        <div class="review-stats">
                            <span class="review-like" onclick="likeReview(<?= $review['id'] ?>, event)"><i class="fas fa-thumbs-up"></i> <span class="likes-count"><?= $review['likes'] ?></span></span>
                            <span class="review-dislike" onclick="dislikeReview(<?= $review['id'] ?>, event)"><i class="fas fa-thumbs-down"></i> <span class="dislikes-count"><?= $review['dislikes'] ?></span></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    
    <!-- Модальное окно -->
    <div id="reviewModal" class="modal-overlay" style="display: none;">
        <div class="modal">
            <button class="modal-close" onclick="closeReviewModal()">&times;</button>
            <h2><i class="fas fa-pen"></i> Оставить отзыв</h2>
            <form id="reviewForm" onsubmit="submitReview(event)">
                <input type="hidden" name="product_id" value="<?= $product['ID'] ?>">
                <div class="form-group">
                    <label>Оценка</label>
                    <div class="rating-selector" id="ratingStars">
                        <span onclick="setRating(1)">☆</span><span onclick="setRating(2)">☆</span>
                        <span onclick="setRating(3)">☆</span><span onclick="setRating(4)">☆</span>
                        <span onclick="setRating(5)">☆</span>
                    </div>
                    <input type="hidden" name="rating" id="reviewRating" value="5">
                </div>
                <div class="form-group">
                    <label>Отзыв</label>
                    <textarea name="text" rows="5" required placeholder="Поделитесь впечатлениями об игре..."></textarea>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn-secondary" onclick="closeReviewModal()">Отмена</button>
                    <button type="submit" class="btn-primary">Отправить</button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
    // Передаем данные пользователя из PHP
    const currentUser = <?= json_encode([
        'id' => (int)($userId ?: 0),
        'name' => $userName ?: '',
        'avatar' => $userAvatar ?: null,
        'isAuthorized' => (bool)$isAuthorized,
        'productId' => (int)$elementId
    ]) ?>;
    
    console.log('Current user:', currentUser);
    
    // ============ ГАЛЕРЕЯ-КАРУСЕЛЬ ==========
    let carouselItems = [];
    let currentIndex = 0;
    let isAnimating = false;
    
    function initCarousel() {
        const track = document.getElementById('carouselTrack');
        if (!track) return;
        carouselItems = Array.from(document.querySelectorAll('.carousel-item'));
        if (carouselItems.length === 0) return;
        currentIndex = 0;
        carouselItems.forEach(item => { item.style.transition = 'all 0.5s cubic-bezier(0.4, 0, 0.2, 1)'; });
        createDots();
        updatePositions();
        startAutoScroll();
        const gallery = document.querySelector('.gallery-carousel');
        if (gallery) {
            gallery.addEventListener('mouseenter', stopAutoScroll);
            gallery.addEventListener('mouseleave', startAutoScroll);
        }
        const prevBtn = document.getElementById('prevBtn');
        const nextBtn = document.getElementById('nextBtn');
        if (prevBtn) {
            const newPrev = prevBtn.cloneNode(true);
            prevBtn.parentNode.replaceChild(newPrev, prevBtn);
            newPrev.addEventListener('click', (e) => { e.preventDefault(); if (!isAnimating) prevImage(); });
        }
        if (nextBtn) {
            const newNext = nextBtn.cloneNode(true);
            nextBtn.parentNode.replaceChild(newNext, nextBtn);
            newNext.addEventListener('click', (e) => { e.preventDefault(); if (!isAnimating) nextImage(); });
        }
        track.addEventListener('click', (e) => {
            if (isAnimating) return;
            const item = e.target.closest('.carousel-item');
            if (!item) return;
            if (item.classList.contains('left')) prevImage();
            else if (item.classList.contains('right')) nextImage();
        });
    }
    
    let autoScrollInterval = null;
    let autoScrollDelay = 5000;
    
    function startAutoScroll() {
        if (autoScrollInterval) clearInterval(autoScrollInterval);
        autoScrollInterval = setInterval(() => { if (!isAnimating && carouselItems.length > 1) nextImage(); }, autoScrollDelay);
    }
    
    function stopAutoScroll() {
        if (autoScrollInterval) { clearInterval(autoScrollInterval); autoScrollInterval = null; }
    }
    
    function updatePositions() {
        const total = carouselItems.length;
        const pos2 = (currentIndex - 2 + total) % total;
        const pos1 = (currentIndex - 1 + total) % total;
        const pos0 = currentIndex;
        const pos_1 = (currentIndex + 1) % total;
        const pos_2 = (currentIndex + 2) % total;
        const wrapper = document.querySelector('.carousel-wrapper');
        const containerWidth = wrapper ? wrapper.clientWidth : window.innerWidth;
        const centerWidth = containerWidth * 0.7;
        const sideWidth = containerWidth * 0.25;
        const invisibleWidth = containerWidth * 0.15;
        const centerLeft = (containerWidth - centerWidth) / 2;
        const leftLeft = containerWidth * 0.02;
        const rightLeft = containerWidth - sideWidth - (containerWidth * 0.02);
        const farLeft = -invisibleWidth;
        const farRight = containerWidth;
        const centerHeight = '85%';
        const sideHeight = '55%';
        const invisibleHeight = '40%';
        carouselItems.forEach((item, idx) => {
            if (idx === pos0) {
                item.style.width = `${centerWidth}px`; item.style.height = centerHeight; item.style.left = `${centerLeft}px`;
                item.style.opacity = '1'; item.style.filter = 'blur(0px)'; item.style.zIndex = '20'; item.style.pointerEvents = 'auto';
                item.classList.add('center'); item.classList.remove('left', 'right', 'far-left', 'far-right');
            } else if (idx === pos1) {
                item.style.width = `${sideWidth}px`; item.style.height = sideHeight; item.style.left = `${leftLeft}px`;
                item.style.opacity = '0.6'; item.style.filter = 'blur(2px)'; item.style.zIndex = '10'; item.style.pointerEvents = 'auto';
                item.classList.add('left'); item.classList.remove('center', 'right', 'far-left', 'far-right');
            } else if (idx === pos_1) {
                item.style.width = `${sideWidth}px`; item.style.height = sideHeight; item.style.left = `${rightLeft}px`;
                item.style.opacity = '0.6'; item.style.filter = 'blur(2px)'; item.style.zIndex = '10'; item.style.pointerEvents = 'auto';
                item.classList.add('right'); item.classList.remove('center', 'left', 'far-left', 'far-right');
            } else if (idx === pos2) {
                item.style.width = `${invisibleWidth}px`; item.style.height = invisibleHeight; item.style.left = `${farLeft}px`;
                item.style.opacity = '0'; item.style.filter = 'blur(5px)'; item.style.zIndex = '5'; item.style.pointerEvents = 'none';
                item.classList.add('far-left'); item.classList.remove('center', 'left', 'right', 'far-right');
            } else if (idx === pos_2) {
                item.style.width = `${invisibleWidth}px`; item.style.height = invisibleHeight; item.style.left = `${farRight}px`;
                item.style.opacity = '0'; item.style.filter = 'blur(5px)'; item.style.zIndex = '5'; item.style.pointerEvents = 'none';
                item.classList.add('far-right'); item.classList.remove('center', 'left', 'right', 'far-left');
            } else {
                item.style.width = '0'; item.style.height = '0'; item.style.opacity = '0'; item.style.left = '-200%';
                item.style.pointerEvents = 'none'; item.classList.remove('center', 'left', 'right', 'far-left', 'far-right');
            }
        });
        updateDots();
    }
    
    function nextImage() { if (isAnimating) return; isAnimating = true; currentIndex = (currentIndex + 1) % carouselItems.length; updatePositions(); setTimeout(() => { isAnimating = false; }, 400); }
    function prevImage() { if (isAnimating) return; isAnimating = true; currentIndex = (currentIndex - 1 + carouselItems.length) % carouselItems.length; updatePositions(); setTimeout(() => { isAnimating = false; }, 400); }
    
    function createDots() {
        const dotsContainer = document.getElementById('carouselDots');
        if (!dotsContainer) return;
        dotsContainer.innerHTML = '';
        carouselItems.forEach((_, index) => {
            const dot = document.createElement('div');
            dot.classList.add('dot');
            if (index === currentIndex) dot.classList.add('active');
            dot.addEventListener('click', () => {
                if (isAnimating || index === currentIndex) return;
                if (index > currentIndex) {
                    const steps = index - currentIndex;
                    let step = 0;
                    function doNext() { if (step < steps && !isAnimating) { nextImage(); step++; setTimeout(doNext, 450); } }
                    doNext();
                } else {
                    const steps = currentIndex - index;
                    let step = 0;
                    function doPrev() { if (step < steps && !isAnimating) { prevImage(); step++; setTimeout(doPrev, 450); } }
                    doPrev();
                }
            });
            dotsContainer.appendChild(dot);
        });
    }
    
    function updateDots() {
        const dots = document.querySelectorAll('#carouselDots .dot');
        dots.forEach((dot, index) => { if (index === currentIndex) dot.classList.add('active'); else dot.classList.remove('active'); });
    }
    
    window.openFullscreen = function(clickedIndex) {
        const allImages = [];
        const allItems = document.querySelectorAll('.carousel-item img');
        allItems.forEach(img => { if (img.src) allImages.push({ src: img.src, alt: img.alt || 'Изображение' }); });
        if (allImages.length === 0) return;
        let currentImageIndex = 0;
        const centerImg = document.querySelector('.carousel-item.center img');
        if (centerImg) { currentImageIndex = allImages.findIndex(img => img.src === centerImg.src); if (currentImageIndex === -1) currentImageIndex = 0; }
        if (window.Fancybox) { window.Fancybox.show([allImages[currentImageIndex]], { startIndex: 0, infinite: true }); }
        else { window.open(centerImg.src, '_blank'); }
    };
    
    let resizeTimeout;
    window.addEventListener('resize', () => { clearTimeout(resizeTimeout); resizeTimeout = setTimeout(() => { if (!isAnimating) updatePositions(); }, 200); });
    document.addEventListener('DOMContentLoaded', () => { initCarousel(); });
    
    // ============ ДРАКОН ============
    const dragon = document.getElementById('dragon');
    const pupils = document.querySelectorAll('.dragon-pupil');
    if (dragon && pupils.length) {
        document.addEventListener('mousemove', (e) => {
            const rect = dragon.getBoundingClientRect();
            const centerX = rect.left + rect.width / 2;
            const centerY = rect.top + rect.height / 2;
            const angle = Math.atan2(e.clientY - centerY, e.clientX - centerX);
            const moveX = Math.sin(angle) * 3;
            const moveY = Math.cos(angle) * 3;
            pupils.forEach(pupil => pupil.style.transform = `translate(${moveX}px, ${-moveY}px)`);
        });
        const buyBtn = document.querySelector('.btn-buy');
        if (buyBtn) {
            buyBtn.addEventListener('mouseenter', () => dragon.classList.add('mouth-open'));
            buyBtn.addEventListener('mouseleave', () => dragon.classList.remove('mouth-open'));
            buyBtn.addEventListener('click', () => { dragon.classList.add('mouth-open'); setTimeout(() => dragon.classList.remove('mouth-open'), 500); });
        }
    }
    
    // ============ РЕАЛЬНЫЕ ЗРИТЕЛИ (БД) ============
    class RealViewersManager {
        constructor() {
            console.log('RealViewersManager constructor started');
            this.currentUserId = currentUser.id;
            this.currentUserName = currentUser.name;
            this.currentUserAvatar = currentUser.avatar;
            this.isAuthorized = currentUser.isAuthorized;
            this.productId = currentUser.productId;
            this.viewers = [];
            this.maxSeats = 9;
            this.heartbeatInterval = null;
            this.pollInterval = null;
            this.init();
        }
        
        init() {
            console.log('Initializing RealViewersManager...');
            if (this.isAuthorized && this.currentUserId > 0) {
                this.sendHeartbeat();
                this.heartbeatInterval = setInterval(() => this.sendHeartbeat(), 15000);
                window.addEventListener('beforeunload', () => this.removeViewer());
            }
            this.fetchViewers();
            this.pollInterval = setInterval(() => this.fetchViewers(), 3000);
            this.trackActivity();
        }
        
        sendHeartbeat() {
            if (this.isAuthorized && this.currentUserId > 0) {
                fetch(window.location.pathname, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({
                        ajax_action: 'update_viewer',
                        user_id: this.currentUserId,
                        user_name: this.currentUserName,
                        user_avatar: this.currentUserAvatar || '',
                        product_id: this.productId
                    })
                }).then(r => r.json()).then(data => {
                    if (data.success && data.viewers) { 
                        this.viewers = data.viewers; 
                        this.renderViewers(); 
                    }
                }).catch(err => console.error('Heartbeat error:', err));
            }
        }

        removeViewer() {
            if (this.isAuthorized && this.currentUserId > 0) {
                fetch(window.location.pathname, {  // <-- ИЗМЕНЕНО
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({ 
                        action: 'remove_viewer', 
                        user_id: this.currentUserId, 
                        product_id: this.productId 
                    })
                }).catch(err => console.error('Remove viewer error:', err));
            }
        }

        fetchViewers() {
            fetch(window.location.pathname, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    ajax_action: 'get_viewers',
                    product_id: this.productId
                })
            }).then(r => r.json()).then(data => {
                if (data.success && data.viewers) { 
                    this.viewers = data.viewers; 
                    this.renderViewers(); 
                }
            }).catch(err => console.error('Fetch viewers error:', err));
        }
        
        trackActivity() {
            let lastActivity = Date.now();
            const updateActivity = () => {
                const now = Date.now();
                if (now - lastActivity > 5000) { lastActivity = now; this.sendHeartbeat(); }
            };
            const events = ['mousemove', 'click', 'keydown', 'scroll', 'touchstart'];
            events.forEach(event => { document.addEventListener(event, updateActivity); });
        }
        
        renderViewers() {
            const allSeats = [
                { seatId: 'r21', row: 'top', position: '17%', top: '-10%' },
                { seatId: 'r22', row: 'top', position: '28%', top: '-10%' },
                { seatId: 'r23', row: 'top', position: '46%', top: '-10%' },
                { seatId: 'r24', row: 'top', position: '64%', top: '-10%' },
                { seatId: 'r11', row: 'bottom', position: '0%', top: '0%' },
                { seatId: 'r12', row: 'bottom', position: '28%', top: '0%' },
                { seatId: 'r13', row: 'bottom', position: '64%', top: '0%' },
                { seatId: 'r14', row: 'bottom', position: '69%', top: '0%' },
                { seatId: 'r15', row: 'bottom', position: '92%', top: '0%' }
            ];
            
            // Фильтруем: убираем текущего пользователя из отображения
            const viewersToShow = this.viewers.filter(v => v.id != this.currentUserId);
            
            const viewersCountEl = document.getElementById('viewersCount');
            if (viewersCountEl) {
                viewersCountEl.textContent = this.viewers.length; // Счетчик показывает ВСЕХ (включая себя)
            }
            
            document.querySelectorAll('.seat-avatar').forEach(el => el.remove());
            document.querySelectorAll('.viewer-spot').forEach(spot => {
                spot.classList.remove('occupied');
                const tooltip = spot.querySelector('.viewer-tooltip');
                if (tooltip) tooltip.remove();
            });
            
            // Перемешиваем и рассаживаем ТОЛЬКО других зрителей
            const shuffledViewers = [...viewersToShow];
            for (let i = shuffledViewers.length - 1; i > 0; i--) {
                const j = Math.floor(Math.random() * (i + 1));
                [shuffledViewers[i], shuffledViewers[j]] = [shuffledViewers[j], shuffledViewers[i]];
            }
            
            shuffledViewers.slice(0, this.maxSeats).forEach((viewer, index) => {
                if (index < allSeats.length) {
                    this.renderAvatar(viewer, allSeats[index]);
                    this.showViewerFigure(viewer, allSeats[index]);
                }
            });
        }
        
        showViewerFigure(viewer, seat) {
            if (viewer.id == this.currentUserId) return;
    
            const layerId = seat.row === 'top' ? 'viewers-layer-top' : 'viewers-layer-bottom';
            const layer = document.getElementById(layerId);
            if (!layer) return;
            
            const spots = layer.querySelectorAll('.viewer-spot');
            for (let spot of spots) {
                if (spot.style.left === seat.position && spot.style.top === seat.top) {
                    spot.classList.add('occupied');
                    spot.setAttribute('data-name', viewer.name);
                    let tooltip = spot.querySelector('.viewer-tooltip');
                    if (!tooltip) { 
                        tooltip = document.createElement('div'); 
                        tooltip.className = 'viewer-tooltip'; 
                        spot.appendChild(tooltip); 
                    }
                    tooltip.textContent = viewer.name;
                    break;
                }
            }
        }
        
        renderAvatar(viewer, seat) {
            // Пропускаем текущего пользователя
            if (viewer.id == this.currentUserId) return;
            
            const container = seat.row === 'top' ? document.querySelector('.row-bottom .seats-avatars-layer') : document.querySelector('.row-top .seats-avatars-layer');
            if (!container) return;
            const topOffset = seat.row === 'top' ? 40 : 40;
            const avatarDiv = document.createElement('div');
            avatarDiv.className = 'seat-avatar';
            avatarDiv.style.cssText = `left: calc(${seat.position} - 25px); top: calc(${seat.top} + ${topOffset}px); ${viewer.avatar ? `background-image: url(${viewer.avatar});` : 'background: linear-gradient(135deg, var(--GoldFake), var(--DragonLight));'} background-size: cover; background-position: center;`;
            avatarDiv.innerHTML = `${!viewer.avatar ? (viewer.name ? viewer.name.charAt(0).toUpperCase() : '?') : ''}<div class="seat-tooltip">${this.escapeHtml(viewer.name)}</div>`;
            avatarDiv.onclick = () => { this.showNotification(`${viewer.name} смотрит этот товар! 👀`); };
            container.appendChild(avatarDiv);
        }
        
        escapeHtml(str) { if (!str) return ''; return str.replace(/[&<>]/g, function(m) { if (m === '&') return '&amp;'; if (m === '<') return '&lt;'; if (m === '>') return '&gt;'; return m; }); }
        showNotification(msg) { const n = document.createElement('div'); n.className = 'notification'; n.style.background = '#27ae60'; n.innerHTML = msg; document.body.appendChild(n); setTimeout(() => n.remove(), 3000); }
    }
    
    let realViewersManager = null;
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(function() {
            if (typeof RealViewersManager !== 'undefined') {
                realViewersManager = new RealViewersManager();
                console.log('RealViewersManager initialized');
            } else { console.error('RealViewersManager not found'); }
        }, 500);
    });
    
    // ============ ОСТАЛЬНЫЕ ФУНКЦИИ ==========
    let draggedItem = null;
    function makeDraggable(item) {
        item.addEventListener('mousedown', startDrag);
        item.addEventListener('mousemove', onDrag);
        item.addEventListener('mouseup', endDrag);
    }
    function startDrag(e) { if (e.button !== 0) return; draggedItem = this; draggedItem.classList.add('dragging'); const rect = draggedItem.getBoundingClientRect(); draggedItem.style.left = rect.left + 'px'; draggedItem.style.top = rect.top + 'px'; draggedItem.style.width = rect.width + 'px'; draggedItem.style.position = 'fixed'; draggedItem.style.animation = 'none'; e.preventDefault(); }
    function onDrag(e) { if (!draggedItem) return; draggedItem.style.left = (e.clientX - 100) + 'px'; draggedItem.style.top = (e.clientY - 50) + 'px'; }
    function endDrag(e) { if (!draggedItem) return; const pinnedArea = document.querySelector('.pinned-reviews'); if (pinnedArea) { const rect = pinnedArea.getBoundingClientRect(); if (e.clientX >= rect.left && e.clientX <= rect.right && e.clientY >= rect.top && e.clientY <= rect.bottom) { const clone = draggedItem.cloneNode(true); clone.classList.add('pinned-review'); clone.classList.remove('stream-item', 'dragging'); clone.style.position = ''; clone.style.left = ''; clone.style.top = ''; clone.style.width = ''; clone.style.animation = ''; const unpin = document.createElement('button'); unpin.className = 'unpin-btn'; unpin.innerHTML = '<i class="fas fa-times"></i>'; unpin.onclick = () => clone.remove(); clone.appendChild(unpin); pinnedArea.appendChild(clone); draggedItem.remove(); showNotification('📌 Отзыв закреплен на доске'); } } draggedItem.classList.remove('dragging'); draggedItem.style.position = ''; draggedItem.style.left = ''; draggedItem.style.top = ''; draggedItem.style.width = ''; draggedItem.style.animation = ''; draggedItem = null; }
    document.querySelectorAll('.stream-item').forEach(makeDraggable);
    
    function likeReview(id, event) { if (event) event.stopPropagation(); const btn = event ? event.currentTarget : null; if (!btn) return; const countSpan = btn.querySelector('.likes-count'); fetch('/ajax/review_like.php', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: 'review_id=' + id + '&action=like' }).then(r => r.json()).then(data => { if (data.success && countSpan) countSpan.textContent = data.likes; }).catch(err => console.error('Error:', err)); }
    function dislikeReview(id, event) { if (event) event.stopPropagation(); const btn = event ? event.currentTarget : null; if (!btn) return; const countSpan = btn.querySelector('.dislikes-count'); fetch('/ajax/review_like.php', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: 'review_id=' + id + '&action=dislike' }).then(r => r.json()).then(data => { if (data.success && countSpan) countSpan.textContent = data.dislikes; }).catch(err => console.error('Error:', err)); }
    
    document.querySelectorAll('.bookmark').forEach(bookmark => { bookmark.addEventListener('click', function() { const pageId = this.dataset.page; document.querySelectorAll('.bookmark').forEach(b => b.classList.remove('active')); this.classList.add('active'); document.querySelectorAll('.book-page').forEach(p => p.classList.remove('active')); const targetPage = document.getElementById('page-' + pageId); if (targetPage) targetPage.classList.add('active'); }); });
    
    function addToCart(productId) { fetch('/ajax/add_to_cart.php', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: 'action=add&product_id=' + productId + '&quantity=1' }).then(r => r.json()).then(data => { if (data.success) { showNotification('✅ Товар добавлен в корзину!'); if (window.updateCartCounter) window.updateCartCounter(); } else { showNotification('❌ Ошибка при добавлении в корзину', 'error'); } }).catch(err => { console.error('Error:', err); showNotification('❌ Ошибка при добавлении в корзину', 'error'); }); }
    function toggleFavorite(productId) { const btn = document.getElementById('favoriteBtn'); if (!btn) return; fetch('/ajax/favorite.php', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: 'action=toggle&product_id=' + productId }).then(r => r.json()).then(data => { if (data.success) { btn.classList.toggle('active'); btn.innerHTML = data.action === 'added' ? '<i class="fas fa-heart"></i> В избранном' : '<i class="fas fa-heart"></i> В избранное'; showNotification(data.action === 'added' ? '❤️ Добавлено в избранное' : '💔 Удалено из избранного'); } }).catch(err => console.error('Error:', err)); }
    
    function openReviewModal() { const modal = document.getElementById('reviewModal'); if (modal) { modal.style.display = 'flex'; document.body.style.overflow = 'hidden'; setRating(5); } }
    function closeReviewModal() { const modal = document.getElementById('reviewModal'); if (modal) { modal.style.display = 'none'; document.body.style.overflow = ''; } }
    function setRating(rating) { const stars = document.querySelectorAll('#ratingStars span'); stars.forEach((star, i) => { star.textContent = i < rating ? '★' : '☆'; star.classList.toggle('active', i < rating); }); const ratingInput = document.getElementById('reviewRating'); if (ratingInput) ratingInput.value = rating; }
    function submitReview(event) { event.preventDefault(); const form = document.getElementById('reviewForm'); if (!form) return; fetch('/ajax/add_review.php', { method: 'POST', body: new FormData(form) }).then(r => r.json()).then(data => { if (data.success) { showNotification('✅ Отзыв отправлен на модерацию!'); closeReviewModal(); form.reset(); } else { showNotification('❌ Ошибка: ' + (data.message || 'Неизвестная ошибка'), 'error'); } }).catch(err => { console.error('Error:', err); showNotification('❌ Ошибка при отправке отзыва', 'error'); }); }
    function showNotification(msg, type = 'success') { const n = document.createElement('div'); n.className = 'notification'; n.style.background = type === 'success' ? '#27ae60' : '#e74c3c'; n.innerHTML = msg; document.body.appendChild(n); setTimeout(() => n.remove(), 3000); }
    
    window.updateCartCounter = function() { fetch('/ajax/add_to_cart.php?action=get&t=' + Date.now()).then(r => r.json()).then(data => { if (data.success) { document.querySelectorAll('.cart-counter').forEach(counter => { counter.textContent = data.cart_count; counter.style.display = data.cart_count > 0 ? 'inline' : 'none'; }); } }).catch(err => console.error('Error:', err)); };
    
    document.addEventListener('DOMContentLoaded', () => { setRating(5); if (window.updateCartCounter) window.updateCartCounter(); const productIdElement = document.querySelector('input[name="product_id"]'); if (productIdElement && productIdElement.value) { fetch('/ajax/track_view.php', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: 'product_id=' + productIdElement.value }).catch(err => console.error('Error tracking view:', err)); } });
    
// ============ ИНТЕРАКТИВНЫЕ КУБИКИ С ФИЗИКОЙ ============
class InteractiveDice {
    constructor() {
        this.diceList = [];
        this.isDragging = false;
        this.currentDice = null;
        this.dragStartX = 0;
        this.dragStartY = 0;
        this.diceStartX = 0;
        this.diceStartY = 0;
        this.velocityX = 0;
        this.velocityY = 0;
        this.animationFrame = null;
        
        this.init();
    }
    
    init() {
        this.createDice();
        this.addEventListeners();
    }
    
    createDice() {
        const diceIcons = ['🎲', '🎲', '🎲', '🎲', '🎲'];
        const diceSizes = ['dice-small', 'dice-medium', 'dice-large'];
        const positions = [
            { left: '15%', top: '20%' },
            { left: '85%', top: '15%' },
            { left: '10%', top: '70%' },
            { left: '90%', top: '75%' },
            { left: '50%', top: '85%' },
            { left: '25%', top: '40%' },
            { left: '75%', top: '45%' }
        ];
        
        // Создаем 5-7 кубиков
        const diceCount = Math.floor(Math.random() * 3) + 5; // 5-7 кубиков
        
        for (let i = 0; i < diceCount; i++) {
            const dice = document.createElement('div');
            dice.className = `dice-interactive ${diceSizes[Math.floor(Math.random() * diceSizes.length)]}`;
            dice.innerHTML = diceIcons[Math.floor(Math.random() * diceIcons.length)];
            
            const pos = positions[i % positions.length];
            dice.style.left = pos.left;
            dice.style.top = pos.top;
            
            // Случайное вращение
            dice.style.transform = `rotate(${Math.random() * 360}deg)`;
            
            dice.setAttribute('data-vel-x', '0');
            dice.setAttribute('data-vel-y', '0');
            
            document.body.appendChild(dice);
            this.diceList.push(dice);
        }
    }
    
    addEventListeners() {
        this.diceList.forEach(dice => {
            dice.addEventListener('mousedown', this.onDragStart.bind(this, dice));
            dice.addEventListener('dragstart', (e) => e.preventDefault());
        });
        
        window.addEventListener('mousemove', this.onDragMove.bind(this));
        window.addEventListener('mouseup', this.onDragEnd.bind(this));
    }
    
    onDragStart(dice, e) {
        if (e.button !== 0) return;
        
        this.isDragging = true;
        this.currentDice = dice;
        this.dragStartX = e.clientX;
        this.dragStartY = e.clientY;
        
        const rect = dice.getBoundingClientRect();
        this.diceStartX = rect.left;
        this.diceStartY = rect.top;
        
        dice.style.cursor = 'grabbing';
        dice.style.zIndex = '10002';
        
        e.preventDefault();
    }
    
    onDragMove(e) {
        if (!this.isDragging || !this.currentDice) return;
        
        const deltaX = e.clientX - this.dragStartX;
        const deltaY = e.clientY - this.dragStartY;
        
        this.currentDice.style.left = `${this.diceStartX + deltaX}px`;
        this.currentDice.style.top = `${this.diceStartY + deltaY}px`;
        this.currentDice.style.transform = `rotate(${Math.atan2(deltaY, deltaX) * 20}deg)`;
        
        // Сохраняем скорость для инерции
        this.velocityX = deltaX;
        this.velocityY = deltaY;
    }
    
    onDragEnd(e) {
        if (!this.isDragging || !this.currentDice) return;
        
        this.isDragging = false;
        this.currentDice.style.cursor = 'grab';
        
        // Добавляем эффект отбрасывания с инерцией
        this.throwDice(this.currentDice, this.velocityX, this.velocityY);
        
        this.currentDice = null;
        this.velocityX = 0;
        this.velocityY = 0;
    }
    
    throwDice(dice, velX, velY) {
        // Нормализуем скорость
        const speed = Math.min(Math.sqrt(velX * velX + velY * velY), 400);
        if (speed < 5) return;
        
        const angle = Math.atan2(velY, velX);
        const power = Math.min(speed * 0.5, 400);
        
        const startX = parseFloat(dice.style.left);
        const startY = parseFloat(dice.style.top);
        const targetX = startX + Math.cos(angle) * power;
        const targetY = startY + Math.sin(angle) * power;
        
        // Получаем границы экрана
        const maxX = window.innerWidth - dice.offsetWidth;
        const maxY = window.innerHeight - dice.offsetHeight;
        
        // Ограничиваем полет границами экрана с отскоком
        let finalX = Math.min(Math.max(targetX, 0), maxX);
        let finalY = Math.min(Math.max(targetY, 0), maxY);
        
        // Расчет количества оборотов в зависимости от скорости
        const rotations = Math.min(Math.floor(speed / 50) + 2, 8);
        const rotationAngle = (Math.atan2(velY, velX) * 180 / Math.PI);
        
        // Создаем анимацию вращения
        dice.classList.add('dice-flying');
        dice.style.transition = 'all 0.5s cubic-bezier(0.25, 0.46, 0.45, 0.94)';
        
        // Добавляем множественное вращение
        let currentRotation = 0;
        const rotateStep = 360 * rotations;
        
        // Анимируем вращение через requestAnimationFrame для плавности
        const startTime = performance.now();
        const duration = 500;
        const startLeft = startX;
        const startTop = startY;
        const deltaLeft = finalX - startX;
        const deltaTop = finalY - startY;
        
        const animateThrow = (now) => {
            const elapsed = now - startTime;
            const progress = Math.min(elapsed / duration, 1);
            
            // Плавное движение
            const easeOut = 1 - Math.pow(1 - progress, 3);
            const currentLeft = startLeft + deltaLeft * easeOut;
            const currentTop = startTop + deltaTop * easeOut;
            
            // Вращение: сначала быстро, потом замедляется
            const rotationProgress = Math.sin(progress * Math.PI) * rotations;
            const currentRotate = rotationAngle + (rotationProgress * 120);
            
            // Добавляем подпрыгивание в начале броска
            const bounce = Math.sin(progress * Math.PI) * 20 * (1 - progress);
            
            dice.style.left = `${currentLeft}px`;
            dice.style.top = `${currentTop - bounce}px`;
            dice.style.transform = `rotate(${currentRotate}deg) scale(${1 + Math.sin(progress * Math.PI) * 0.1})`;
            
            if (progress < 1) {
                requestAnimationFrame(animateThrow);
            } else {
                // Завершаем анимацию
                dice.style.left = `${finalX}px`;
                dice.style.top = `${finalY}px`;
                
                // Финальное вращение с отскоком
                const finalRotation = rotationAngle + (rotations * 360);
                dice.style.transform = `rotate(${finalRotation % 360}deg) scale(1)`;
                
                setTimeout(() => {
                    dice.classList.remove('dice-flying');
                    dice.style.transition = '';
                    
                    // Эффект приземления
                    dice.style.transform = `rotate(${finalRotation % 360}deg) scale(0.92)`;
                    setTimeout(() => {
                        dice.style.transform = `rotate(${finalRotation % 360}deg) scale(1)`;
                        
                        // Генерируем случайное число (эффект выпавшего значения)
                        const diceValue = Math.floor(Math.random() * 6) + 1;
                        this.showDiceValue(dice, diceValue);
                    }, 100);
                }, 50);
            }
        };
        
        requestAnimationFrame(animateThrow);
    }

    showDiceValue(dice, value) {
        // Показываем выпавшее значение
        const valueIndicator = document.createElement('div');
        valueIndicator.textContent = value;
        valueIndicator.style.cssText = `
            position: fixed;
            left: ${parseFloat(dice.style.left) + dice.offsetWidth / 2 - 15}px;
            top: ${parseFloat(dice.style.top)}px;
            width: 30px;
            height: 30px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 18px;
            color: black;
            text-shadow: 1px 1px 0 rgba(0,0,0,0.3);
            z-index: 10003;
            pointer-events: none;
            animation: valuePop 0.5s ease-out forwards;
        `;
        document.body.appendChild(valueIndicator);
        
        setTimeout(() => {
            valueIndicator.remove();
        }, 500);
    }
    
    playDiceSound() {
        // Опционально: воспроизвести звук кубика
        try {
            const audio = new Audio('/sounds/dice.mp3');
            audio.volume = 0.2;
            audio.play().catch(e => console.log('Audio not supported'));
        } catch(e) {}
    }
    
    // Добавляем новый кубик при клике на дракона
    addNewDice() {
        const diceIcons = ['🎲', '🎲', '🎲', '🎲'];
        const dice = document.createElement('div');
        dice.className = `dice-interactive dice-medium`;
        dice.innerHTML = diceIcons[Math.floor(Math.random() * diceIcons.length)];
        
        // Появляется из дракона
        const dragon = document.getElementById('dragon');
        if (dragon) {
            const rect = dragon.getBoundingClientRect();
            dice.style.left = `${rect.left + rect.width / 2}px`;
            dice.style.top = `${rect.top + rect.height / 2}px`;
        } else {
            dice.style.left = '50%';
            dice.style.top = '50%';
        }
        
        dice.style.transform = 'scale(0)';
        document.body.appendChild(dice);
        
        // Анимация появления
        setTimeout(() => {
            dice.style.transform = 'scale(1)';
        }, 10);
        
        this.diceList.push(dice);
        this.addEventListenersToNewDice(dice);
        
        // Удаляем через 30 секунд если не трогают
        setTimeout(() => {
            if (dice.parentNode && !dice.isDragging) {
                dice.style.transition = 'all 0.3s';
                dice.style.transform = 'scale(0)';
                setTimeout(() => dice.remove(), 300);
                const index = this.diceList.indexOf(dice);
                if (index > -1) this.diceList.splice(index, 1);
            }
        }, 30000);
    }
    
    addEventListenersToNewDice(dice) {
        dice.addEventListener('mousedown', this.onDragStart.bind(this, dice));
        dice.addEventListener('dragstart', (e) => e.preventDefault());
    }
}

// Инициализация интерактивных кубиков
let interactiveDice = null;
document.addEventListener('DOMContentLoaded', () => {
    setTimeout(() => {
        interactiveDice = new InteractiveDice();
        console.log('Interactive dice initialized');
        
        // По клику на дракона появляется новый кубик
        const dragon = document.getElementById('dragon');
        if (dragon) {
            dragon.addEventListener('click', () => {
                if (interactiveDice) {
                    interactiveDice.addNewDice();
                }
            });
        }
    }, 1000);
});

    // ============ ПЕРЕКЛЮЧЕНИЕ СТРАНИЦ КНИГИ ============
document.querySelectorAll('.bookmark-tab').forEach(tab => {
    tab.addEventListener('click', function() {
        const pageId = this.dataset.page;
        const currentActive = document.querySelector('.bookmark-tab.active');
        
        if (currentActive === this) return;
        
        const currentPage = document.querySelector('.book-page-content.active');
        const newPage = document.getElementById('page-' + pageId);
        
        if (currentPage) {
            currentPage.style.animation = 'pageFlipOut 0.3s ease forwards';
            setTimeout(() => {
                currentPage.classList.remove('active');
                currentPage.style.animation = '';
                newPage.classList.add('active');
                newPage.style.animation = 'pageFlipIn 0.5s cubic-bezier(0.4, 0, 0.2, 1) forwards';
                setTimeout(() => {
                    newPage.style.animation = '';
                }, 500);
            }, 250);
        } else {
            newPage.classList.add('active');
            newPage.style.animation = 'pageFlipIn 0.5s cubic-bezier(0.4, 0, 0.2, 1)';
            setTimeout(() => {
                newPage.style.animation = '';
            }, 500);
        }
        
        document.querySelectorAll('.bookmark-tab').forEach(t => t.classList.remove('active'));
        this.classList.add('active');
    });
});

// Добавляем анимацию вылета
const styleSheet = document.createElement("style");
styleSheet.textContent = `
    @keyframes pageFlipOut {
        0% {
            opacity: 1;
            transform: perspective(1000px) rotateY(0) translateX(0);
        }
        100% {
            opacity: 0;
            transform: perspective(1000px) rotateY(-15deg) translateX(-50px);
            filter: blur(2px);
        }
    }
`;
document.head.appendChild(styleSheet);
    </script>
    <script src="https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0/dist/fancybox/fancybox.umd.js"></script>
</body>
</html>

<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
?>