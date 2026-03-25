<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

CModule::IncludeModule("iblock");

$response = ['success' => false, 'message' => ''];

// Получаем данные
$productId = intval($_POST['product_id'] ?? $_GET['product_id'] ?? 0);
$action = $_POST['action'] ?? $_GET['action'] ?? '';

// Для action=get НЕ нужен productId - пропускаем проверку
if ($action != 'get' && $productId <= 0) {
    $response['message'] = 'Неверный ID товара';
    echo json_encode($response);
    exit;
}

// Проверяем существование товара (только для действий, кроме get)
if ($action != 'get' && $productId > 0) {
    $dbRes = CIBlockElement::GetList([], ['ID' => $productId, 'ACTIVE' => 'Y'], false, false, ['ID']);
    if (!$dbRes->Fetch()) {
        $response['message'] = 'Товар не найден';
        echo json_encode($response);
        exit;
    }
}

// Получаем избранное из сессии
session_start();
$favoritesKey = 'user_favorites_' . ($USER->GetID() ?: session_id());

if (!isset($_SESSION[$favoritesKey])) {
    $_SESSION[$favoritesKey] = [];
}

$favorites = $_SESSION[$favoritesKey];

switch ($action) {
    case 'add':
        if (!in_array($productId, $favorites)) {
            $favorites[] = $productId;
            $response['success'] = true;
            $response['message'] = 'Добавлено в избранное';
            $response['is_favorite'] = true;
        } else {
            $response['success'] = true;
            $response['message'] = 'Уже в избранном';
            $response['is_favorite'] = true;
        }
        break;
        
    case 'remove':
        $favorites = array_filter($favorites, function($id) use ($productId) {
            return $id != $productId;
        });
        $favorites = array_values($favorites);
        $response['success'] = true;
        $response['message'] = 'Удалено из избранного';
        $response['is_favorite'] = false;
        break;
        
    case 'toggle':
        if (in_array($productId, $favorites)) {
            $favorites = array_filter($favorites, function($id) use ($productId) {
                return $id != $productId;
            });
            $response['message'] = 'Удалено из избранного';
            $response['is_favorite'] = false;
        } else {
            $favorites[] = $productId;
            $response['message'] = 'Добавлено в избранное';
            $response['is_favorite'] = true;
        }
        $favorites = array_values($favorites);
        $response['success'] = true;
        break;
        
    case 'get':
        $response['success'] = true;
        $response['favorites'] = $favorites;
        $response['count'] = count($favorites);
        echo json_encode($response);
        exit;
        
    case 'clear':
        $favorites = [];
        $response['success'] = true;
        $response['message'] = 'Избранное очищено';
        break;
        
    default:
        $response['message'] = 'Неизвестное действие';
        break;
}

// Сохраняем обратно в сессию
$_SESSION[$favoritesKey] = $favorites;

// Если пользователь авторизован, сохраняем в базу
if ($USER->IsAuthorized()) {
    $userId = $USER->GetID();
    $favoritesStr = implode(',', $favorites);
    
    $user = new CUser;
    $user->Update($userId, ['UF_FAVORITES' => $favoritesStr]);
}

header('Content-Type: application/json');
echo json_encode($response, JSON_UNESCAPED_UNICODE);
?>