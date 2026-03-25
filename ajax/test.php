<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

CModule::IncludeModule("sale");
CModule::IncludeModule("catalog");

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

// Тестовые данные
$userId = 1; // ID твоего пользователя
$fuserId = CSaleBasket::GetBasketUserID();

// Получаем корзину
$basketItems = [];
$totalPrice = 0;

$dbBasketItems = CSaleBasket::GetList(
    [],
    [
        "FUSER_ID" => $fuserId,
        "LID" => SITE_ID,
        "ORDER_ID" => "NULL"
    ],
    false,
    false,
    ["ID", "PRODUCT_ID", "NAME", "QUANTITY", "PRICE", "CURRENCY"]
);

while ($arItem = $dbBasketItems->Fetch()) {
    if (empty($arItem['NAME']) || $arItem['PRICE'] <= 0) continue;
    $basketItems[] = $arItem;
    $totalPrice += $arItem['PRICE'] * $arItem['QUANTITY'];
}

if (empty($basketItems)) {
    $response['message'] = 'Корзина пуста';
    echo json_encode($response);
    exit;
}

echo "Корзина найдена, товаров: " . count($basketItems) . ", сумма: $totalPrice<br>";

// Создаем заказ
$arOrderFields = [
    "LID" => SITE_ID,
    "PERSON_TYPE_ID" => 1,
    "PAYED" => "N",
    "CANCELED" => "N",
    "STATUS_ID" => "N",
    "PRICE" => $totalPrice,
    "CURRENCY" => "RUB",
    "USER_ID" => $userId,
    "USER_DESCRIPTION" => "Тестовый заказ",
    "COMMENTS" => "Тестовый комментарий"
];

echo "<pre>Данные заказа: " . print_r($arOrderFields, true) . "</pre>";

$orderId = CSaleOrder::Add($arOrderFields);

if (!$orderId) {
    global $APPLICATION;
    $ex = $APPLICATION->GetException();
    echo "Ошибка создания заказа: " . ($ex ? $ex->GetString() : "Неизвестная ошибка");
} else {
    echo "✅ Заказ создан! ID: $orderId<br>";
    
    // Добавляем товары
    foreach ($basketItems as $item) {
        $arBasketFields = [
            "ORDER_ID" => $orderId,
            "PRODUCT_ID" => $item['PRODUCT_ID'],
            "PRODUCT_NAME" => $item['NAME'],
            "QUANTITY" => $item['QUANTITY'],
            "PRICE" => $item['PRICE'],
            "CURRENCY" => $item['CURRENCY'],
            "LID" => SITE_ID,
            "FUSER_ID" => $fuserId
        ];
        
        $basketId = CSaleBasket::Add($arBasketFields);
        if ($basketId) {
            echo "Товар добавлен: {$item['NAME']}<br>";
            // Удаляем из корзины
            CSaleBasket::Delete($item['ID']);
        } else {
            echo "Ошибка добавления товара: {$item['NAME']}<br>";
        }
    }
    
    echo "✅ Заказ успешно создан!";
}
?>