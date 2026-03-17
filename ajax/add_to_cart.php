<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');

// Заголовок для JSON ответа
header('Content-Type: application/json');

// Подключаем модуль инфоблоков (на всякий случай)
if(!CModule::IncludeModule('iblock')) {
    echo json_encode(['success' => false, 'error' => 'Модуль инфоблоков не найден']);
    return;
}

// Функция для получения ID текущей сессии
function getSessionId() {
    if (!session_id()) {
        session_start();
    }
    
    if (empty($_SESSION['CART_SESSION_ID'])) {
        $_SESSION['CART_SESSION_ID'] = session_id() . '_' . uniqid();
    }
    
    return $_SESSION['CART_SESSION_ID'];
}

// Функция для получения ID пользователя (если авторизован)
function getUserId() {
    global $USER;
    return $USER->IsAuthorized() ? $USER->GetID() : null;
}

// Получаем действие
$action = $_REQUEST['action'] ?? '';

switch ($action) {
    case 'add':
        // Добавление товара в корзину
        $productId = intval($_POST['product_id'] ?? 0);
        $quantity = intval($_POST['quantity'] ?? 1);
        
        if ($productId <= 0) {
            echo json_encode(['success' => false, 'error' => 'Неверный ID товара']);
            return;
        }
        
        if ($quantity <= 0) {
            $quantity = 1;
        }
        
        $sessionId = getSessionId();
        $userId = getUserId();
        
        global $DB;
        
        // Проверяем, есть ли уже такой товар в корзине
        $sql = "SELECT ID, QUANTITY FROM user_cart 
                WHERE SESSION_ID = '".$DB->ForSql($sessionId)."' 
                AND PRODUCT_ID = ".$productId;
        
        if ($userId) {
            $sql .= " AND (USER_ID = ".$userId." OR USER_ID IS NULL)";
        }
        
        $result = $DB->Query($sql);
        
        if ($item = $result->Fetch()) {
            // Обновляем количество
            $newQuantity = $item['QUANTITY'] + $quantity;
            $DB->Update('user_cart', [
                'QUANTITY' => $newQuantity,
                'USER_ID' => $userId ?: 'NULL'
            ], "WHERE ID = ".$item['ID']);
        } else {
            // Добавляем новый товар
            $DB->Insert('user_cart', [
                'USER_ID' => $userId ?: 'NULL',
                'SESSION_ID' => "'".$DB->ForSql($sessionId)."'",
                'PRODUCT_ID' => $productId,
                'QUANTITY' => $quantity,
                'DATE_ADDED' => "'".date('Y-m-d H:i:s')."'"
            ]);
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Товар добавлен в корзину',
            'cart_count' => getCartCount()
        ]);
        break;
        
    case 'update':
        // Обновление количества товара
        $productId = intval($_POST['product_id'] ?? 0);
        $quantity = intval($_POST['quantity'] ?? 1);
        
        if ($productId <= 0) {
            echo json_encode(['success' => false, 'error' => 'Неверный ID товара']);
            return;
        }
        
        $sessionId = getSessionId();
        $userId = getUserId();
        
        global $DB;
        
        $sql = "UPDATE user_cart SET QUANTITY = ".$quantity;
        
        if ($userId) {
            $sql .= ", USER_ID = ".$userId;
        }
        
        $sql .= " WHERE SESSION_ID = '".$DB->ForSql($sessionId)."' 
                  AND PRODUCT_ID = ".$productId;
        
        $DB->Query($sql);
        
        echo json_encode([
            'success' => true,
            'cart_count' => getCartCount()
        ]);
        break;
        
    case 'remove':
        // Удаление товара из корзины
        $productId = intval($_POST['product_id'] ?? 0);
        
        if ($productId <= 0) {
            echo json_encode(['success' => false, 'error' => 'Неверный ID товара']);
            return;
        }
        
        $sessionId = getSessionId();
        $userId = getUserId();
        
        global $DB;
        
        $sql = "DELETE FROM user_cart 
                WHERE SESSION_ID = '".$DB->ForSql($sessionId)."' 
                AND PRODUCT_ID = ".$productId;
        
        $DB->Query($sql);
        
        echo json_encode([
            'success' => true,
            'cart_count' => getCartCount()
        ]);
        break;
        
    case 'clear':
        // Очистка корзины
        $sessionId = getSessionId();
        $userId = getUserId();
        
        global $DB;
        
        $sql = "DELETE FROM user_cart WHERE SESSION_ID = '".$DB->ForSql($sessionId)."'";
        $DB->Query($sql);
        
        echo json_encode([
            'success' => true,
            'cart_count' => 0
        ]);
        break;
        
    case 'get':
        // Получение количества товаров в корзине
        echo json_encode([
            'success' => true,
            'cart_count' => getCartCount()
        ]);
        break;
        
    case 'get_full':
        // Получение полной информации о корзине
        $items = getCartItems();
        $total = 0;
        
        // Получаем цены товаров
        foreach ($items as &$item) {
            $res = CIBlockElement::GetList(
                [],
                ['IBLOCK_CODE' => 'products', 'ID' => $item['PRODUCT_ID']],
                false,
                false,
                ['ID', 'NAME', 'PROPERTY_PRICE', 'DETAIL_PICTURE']
            );
            
            if ($product = $res->GetNext()) {
                $item['NAME'] = $product['NAME'];
                $item['PRICE'] = $product['PROPERTY_PRICE_VALUE'] ? explode('|', $product['PROPERTY_PRICE_VALUE'])[0] : 0;
                $item['SUM'] = $item['PRICE'] * $item['QUANTITY'];
                $item['IMAGE'] = $product['DETAIL_PICTURE'] ? CFile::GetPath($product['DETAIL_PICTURE']) : '';
                
                if ($item['PRICE']) {
                    $total += $item['SUM'];
                }
            }
        }
        
        echo json_encode([
            'success' => true,
            'items' => $items,
            'total' => $total,
            'count' => count($items)
        ]);
        break;
        
    default:
        echo json_encode(['success' => false, 'error' => 'Неизвестное действие']);
}

// Функция для получения количества товаров в корзине
function getCartCount() {
    $sessionId = getSessionId();
    $userId = getUserId();
    
    global $DB;
    
    $sql = "SELECT SUM(QUANTITY) as CNT FROM user_cart 
            WHERE SESSION_ID = '".$DB->ForSql($sessionId)."'";
    
    $result = $DB->Query($sql);
    $row = $result->Fetch();
    
    return intval($row['CNT']);
}

// Функция для получения товаров в корзине
function getCartItems() {
    $sessionId = getSessionId();
    $userId = getUserId();
    
    global $DB;
    
    $sql = "SELECT * FROM user_cart 
            WHERE SESSION_ID = '".$DB->ForSql($sessionId)."'
            ORDER BY DATE_ADDED DESC";
    
    $result = $DB->Query($sql);
    $items = [];
    
    while ($row = $result->Fetch()) {
        $items[] = $row;
    }
    
    return $items;
}