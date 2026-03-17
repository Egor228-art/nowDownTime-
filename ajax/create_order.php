<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');

// Подключаем модуль sale
CModule::IncludeModule('sale');

header('Content-Type: application/json');

$action = $_POST['action'] ?? '';

if ($action !== 'create') {
    echo json_encode(['success' => false, 'error' => 'Неизвестное действие']);
    return;
}

// Получаем данные из формы
$lastName = trim($_POST['lastName'] ?? '');
$firstName = trim($_POST['firstName'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$email = trim($_POST['email'] ?? '');
$delivery = $_POST['delivery'] ?? 'pickup';
$address = trim($_POST['address'] ?? '');
$payment = $_POST['payment'] ?? 'cash';
$comment = trim($_POST['comment'] ?? '');

// Валидация (как у тебя)
// ...

// Получаем корзину текущего пользователя
$fuser = CSaleBasket::GetBasketUserID();
$basketItems = array();

$dbBasketItems = CSaleBasket::GetList(
    array(),
    array(
        'FUSER_ID' => $fuser,
        'ORDER_ID' => 'NULL',
        'LID' => SITE_ID
    ),
    false,
    false,
    array('ID', 'PRODUCT_ID', 'NAME', 'PRICE', 'QUANTITY')
);

while ($item = $dbBasketItems->Fetch()) {
    $basketItems[] = $item;
}

if (empty($basketItems)) {
    echo json_encode(['success' => false, 'error' => 'Корзина пуста']);
    return;
}

// Создаем заказ
$arOrder = array(
    'LID' => SITE_ID,
    'PERSON_TYPE_ID' => 1, // Тип плательщика (физическое лицо)
    'PAYED' => 'N',
    'CANCELED' => 'N',
    'STATUS_ID' => 'N', // Новый заказ
    'PRICE' => 0, // Посчитаем позже
    'CURRENCY' => 'RUB',
    'USER_ID' => $USER->GetID() ?: 1, // Если не авторизован - ставим 1 (админ)
    'USER_DESCRIPTION' => $comment
);

$orderId = CSaleOrder::Add($arOrder);

if (!$orderId) {
    echo json_encode(['success' => false, 'error' => 'Ошибка создания заказа']);
    return;
}

// Добавляем товары в заказ
$totalPrice = 0;
foreach ($basketItems as $item) {
    $arBasket = array(
        'ORDER_ID' => $orderId,
        'PRODUCT_ID' => $item['PRODUCT_ID'],
        'NAME' => $item['NAME'],
        'PRICE' => $item['PRICE'],
        'QUANTITY' => $item['QUANTITY'],
        'CURRENCY' => 'RUB',
        'LID' => SITE_ID
    );
    
    CSaleBasket::Update($item['ID'], array('ORDER_ID' => $orderId));
    $totalPrice += $item['PRICE'] * $item['QUANTITY'];
}

// Добавляем доставку
$deliveryPrice = 0;
if ($delivery === 'delivery') {
    $deliveryPrice = 500;
    $totalPrice += $deliveryPrice;
}

// Обновляем общую стоимость заказа
CSaleOrder::Update($orderId, array('PRICE' => $totalPrice));

// Добавляем свойства заказа (ФИО, телефон, email, адрес)
$arProps = array(
    array('ORDER_ID' => $orderId, 'ORDER_PROPS_ID' => 1, 'NAME' => 'Фамилия', 'VALUE' => $lastName),
    array('ORDER_ID' => $orderId, 'ORDER_PROPS_ID' => 2, 'NAME' => 'Имя', 'VALUE' => $firstName),
    array('ORDER_ID' => $orderId, 'ORDER_PROPS_ID' => 3, 'NAME' => 'Телефон', 'VALUE' => $phone),
    array('ORDER_ID' => $orderId, 'ORDER_PROPS_ID' => 4, 'NAME' => 'Email', 'VALUE' => $email),
);

if ($delivery === 'delivery' && $address) {
    $arProps[] = array('ORDER_ID' => $orderId, 'ORDER_PROPS_ID' => 5, 'NAME' => 'Адрес доставки', 'VALUE' => $address);
}

foreach ($arProps as $prop) {
    CSaleOrderPropsValue::Add($prop);
}

// Очищаем корзину
CSaleBasket::DeleteAll(CSaleBasket::GetBasketUserID());

echo json_encode([
    'success' => true,
    'order_id' => $orderId,
    'order_number' => $orderId // В Битриксе номер заказа = ID
]);