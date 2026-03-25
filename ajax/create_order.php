<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

CModule::IncludeModule("sale");
CModule::IncludeModule("catalog");
CModule::IncludeModule("iblock");

$response = ['success' => false, 'message' => ''];

// Получаем данные из POST
$lastName = trim($_POST['lastName'] ?? '');
$firstName = trim($_POST['firstName'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$email = trim($_POST['email'] ?? '');
$city = trim($_POST['city'] ?? '');
$delivery = $_POST['delivery'] ?? 'pickup';
$address = trim($_POST['address'] ?? '');
$payment = $_POST['payment'] ?? 'cash';
$comment = trim($_POST['comment'] ?? '');
$score = intval($_POST['score'] ?? 0);

// Валидация
if (empty($lastName) || empty($firstName) || empty($phone) || empty($email)) {
    $response['message'] = 'Заполните все обязательные поля';
    echo json_encode($response);
    exit;
}

// Очищаем телефон
$phone = preg_replace('/[^0-9]/', '', $phone);
if (strlen($phone) < 10) {
    $response['message'] = 'Некорректный номер телефона';
    echo json_encode($response);
    exit;
}

if (strlen($phone) == 10) {
    $phone = '7' . $phone;
}

global $USER;

// Создаем пользователя если не авторизован
$userId = $USER->GetID();
if (!$userId) {
    $dbUser = CUser::GetList([], [], ["EMAIL" => $email]);
    if ($arUser = $dbUser->Fetch()) {
        $userId = $arUser['ID'];
    } else {
        $password = rand(100000, 999999);
        $user = new CUser;
        
        $arFields = [
            "NAME" => $firstName,
            "LAST_NAME" => $lastName,
            "EMAIL" => $email,
            "LOGIN" => $email,
            "PASSWORD" => $password,
            "CONFIRM_PASSWORD" => $password,
            "PERSONAL_PHONE" => $phone,
            "PERSONAL_CITY" => $city,
            "PERSONAL_STREET" => $address,
            "ACTIVE" => "Y",
            "GROUP_ID" => [2]
        ];
        
        $userId = $user->Add($arFields);
        
        if (!$userId) {
            $response['message'] = 'Ошибка создания пользователя';
            echo json_encode($response);
            exit;
        }
        
        $USER->Authorize($userId);
    }
}

// Получаем корзину пользователя
$fuserId = CSaleBasket::GetBasketUserID();

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

$basketItems = [];
$totalPrice = 0;
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

// Добавляем стоимость доставки
$deliveryPrice = ($delivery == 'delivery') ? 500 : 0;
$totalPrice += $deliveryPrice;

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
    "USER_DESCRIPTION" => $comment
];

$orderId = CSaleOrder::Add($arOrderFields);

if (!$orderId) {
    $response['message'] = 'Ошибка создания заказа';
    echo json_encode($response);
    exit;
}

// ========== СОХРАНЯЕМ СВОЙСТВА ЗАКАЗА ==========
// Получаем ID свойств заказа (если они есть)
$arDeliveryProps = [
    'DELIVERY_TYPE' => '',
    'DELIVERY_ADDRESS' => '',
    'PAYMENT_TYPE' => ''
];

// Ищем существующие свойства заказа
$dbProps = CSaleOrderProps::GetList(
    [],
    ["PERSON_TYPE_ID" => 1, "ACTIVE" => "Y"],
    false,
    false,
    ["ID", "CODE", "NAME"]
);

while ($prop = $dbProps->Fetch()) {
    if ($prop['CODE'] == 'DELIVERY_TYPE' || strpos($prop['NAME'], 'Способ доставки') !== false) {
        $arDeliveryProps['DELIVERY_TYPE'] = $prop['ID'];
    }
    if ($prop['CODE'] == 'DELIVERY_ADDRESS' || strpos($prop['NAME'], 'Адрес') !== false) {
        $arDeliveryProps['DELIVERY_ADDRESS'] = $prop['ID'];
    }
    if ($prop['CODE'] == 'PAYMENT_TYPE' || strpos($prop['NAME'], 'Способ оплаты') !== false) {
        $arDeliveryProps['PAYMENT_TYPE'] = $prop['ID'];
    }
}

// Добавляем свойства заказа
if ($arDeliveryProps['DELIVERY_TYPE']) {
    $deliveryName = ($delivery == 'delivery') ? 'Доставка курьером' : 'Самовывоз';
    CSaleOrderPropsValue::Add([
        "ORDER_ID" => $orderId,
        "ORDER_PROPS_ID" => $arDeliveryProps['DELIVERY_TYPE'],
        "NAME" => "Способ доставки",
        "VALUE" => $deliveryName
    ]);
}

if ($arDeliveryProps['DELIVERY_ADDRESS'] && $delivery == 'delivery' && !empty($address)) {
    CSaleOrderPropsValue::Add([
        "ORDER_ID" => $orderId,
        "ORDER_PROPS_ID" => $arDeliveryProps['DELIVERY_ADDRESS'],
        "NAME" => "Адрес доставки",
        "VALUE" => $address
    ]);
}

if ($arDeliveryProps['PAYMENT_TYPE']) {
    $paymentName = ($payment == 'cash') ? 'Наличными при получении' : 'Картой онлайн';
    CSaleOrderPropsValue::Add([
        "ORDER_ID" => $orderId,
        "ORDER_PROPS_ID" => $arDeliveryProps['PAYMENT_TYPE'],
        "NAME" => "Способ оплаты",
        "VALUE" => $paymentName
    ]);
}

// Добавляем телефон и email как свойства
$dbProps = CSaleOrderProps::GetList(
    [],
    ["PERSON_TYPE_ID" => 1, "CODE" => "PHONE"],
    false,
    false,
    ["ID"]
);
if ($arProp = $dbProps->Fetch()) {
    CSaleOrderPropsValue::Add([
        "ORDER_ID" => $orderId,
        "ORDER_PROPS_ID" => $arProp['ID'],
        "NAME" => "Телефон",
        "VALUE" => $phone
    ]);
}

$dbProps = CSaleOrderProps::GetList(
    [],
    ["PERSON_TYPE_ID" => 1, "CODE" => "EMAIL"],
    false,
    false,
    ["ID"]
);
if ($arProp = $dbProps->Fetch()) {
    CSaleOrderPropsValue::Add([
        "ORDER_ID" => $orderId,
        "ORDER_PROPS_ID" => $arProp['ID'],
        "NAME" => "Email",
        "VALUE" => $email
    ]);
}

// ========== ПЕРЕНОСИМ КОРЗИНУ В ЗАКАЗ ==========
$result = CSaleBasket::OrderBasket($orderId, $fuserId, SITE_ID);

if (!$result) {
    foreach ($basketItems as $item) {
        $arBasketFields = [
            "ORDER_ID" => $orderId,
            "PRODUCT_ID" => $item['PRODUCT_ID'],
            "PRODUCT_NAME" => $item['NAME'],
            "QUANTITY" => $item['QUANTITY'],
            "PRICE" => $item['PRICE'],
            "CURRENCY" => $item['CURRENCY'],
            "LID" => SITE_ID,
            "FUSER_ID" => $fuserId,
            "MODULE" => "catalog"
        ];
        
        $newId = CSaleBasket::Add($arBasketFields);
        if ($newId) {
            CSaleBasket::Delete($item['ID']);
        }
    }
}

// Сохраняем комментарий в свойство
$dbProps = CSaleOrderProps::GetList(
    [],
    ["PERSON_TYPE_ID" => 1, "CODE" => "COMMENT"],
    false,
    false,
    ["ID"]
);
if ($arProp = $dbProps->Fetch() && !empty($comment)) {
    CSaleOrderPropsValue::Add([
        "ORDER_ID" => $orderId,
        "ORDER_PROPS_ID" => $arProp['ID'],
        "NAME" => "Комментарий",
        "VALUE" => $comment
    ]);
}

// Сохраняем игровые очки
if ($score > 0) {
    $USER->SetUserField($userId, 'UF_GAME_SCORE', $score);
}

$response['success'] = true;
$response['order_id'] = $orderId;
$response['message'] = 'Заказ успешно создан';

header('Content-Type: application/json');
echo json_encode($response, JSON_UNESCAPED_UNICODE);
?>