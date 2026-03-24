<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

CModule::IncludeModule("sale");
CModule::IncludeModule("catalog");

$response = ['success' => false, 'message' => ''];

$orderId = intval($_POST['order_id'] ?? 0);

if (!$orderId) {
    $response['message'] = 'Не указан ID заказа';
    echo json_encode($response);
    exit;
}

global $USER;

// Проверяем, что заказ принадлежит пользователю
$dbOrder = CSaleOrder::GetList(
    [],
    ["ID" => $orderId, "USER_ID" => $USER->GetID()],
    false,
    false,
    ["ID"]
);

if (!$dbOrder->Fetch()) {
    $response['message'] = 'Заказ не найден';
    echo json_encode($response);
    exit;
}

// Получаем товары из заказа
$dbBasket = CSaleBasket::GetList(
    [],
    ["ORDER_ID" => $orderId],
    false,
    false,
    ["PRODUCT_ID", "QUANTITY"]
);

$added = 0;
while ($arItem = $dbBasket->Fetch()) {
    // Добавляем товар в корзину
    $result = Add2BasketByProductID($arItem['PRODUCT_ID'], $arItem['QUANTITY']);
    if ($result) {
        $added++;
    }
}

if ($added > 0) {
    $response['success'] = true;
    $response['message'] = "Добавлено товаров: $added";
    $response['added'] = $added;
} else {
    $response['message'] = 'Не удалось добавить товары';
}

header('Content-Type: application/json');
echo json_encode($response);
?>