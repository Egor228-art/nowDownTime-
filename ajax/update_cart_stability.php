<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

$response = ['success' => false, 'message' => ''];

$action = $_POST['action'] ?? '';

switch ($action) {
    case 'save':
        // Сохраняем стабильное состояние корзины
        $stableCart = $_POST['cart'] ?? '';
        if ($stableCart) {
            $_SESSION['STABLE_CART'] = $stableCart;
            $response['success'] = true;
            $response['message'] = 'Сохранено';
        }
        break;
        
    case 'load':
        // Загружаем стабильное состояние
        if (isset($_SESSION['STABLE_CART'])) {
            $response['success'] = true;
            $response['cart'] = $_SESSION['STABLE_CART'];
        }
        break;
        
    case 'clear':
        // Очищаем
        unset($_SESSION['STABLE_CART']);
        $response['success'] = true;
        break;
        
    default:
        $response['message'] = 'Неизвестное действие';
        break;
}

header('Content-Type: application/json');
echo json_encode($response);
?>