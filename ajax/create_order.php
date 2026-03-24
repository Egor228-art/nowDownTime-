<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

CModule::IncludeModule("sale");
CModule::IncludeModule("catalog");

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

// Добавляем +7 если нужно
if (strlen($phone) == 10) {
    $phone = '7' . $phone;
}

global $USER;

// Создаем пользователя если не авторизован
$userId = $USER->GetID();
if (!$userId) {
    // Проверяем, есть ли пользователь с таким email
    $dbUser = CUser::GetList(
        [],
        [],
        ["EMAIL" => $email]
    );
    
    if ($arUser = $dbUser->Fetch()) {
        $userId = $arUser['ID'];
    } else {
        // Создаем нового пользователя
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
            "GROUP_ID" => [2] // Группа "Все пользователи"
        ];
        
        $userId = $user->Add($arFields);
        
        if (!$userId) {
            $response['message'] = 'Ошибка создания пользователя: ' . $user->LAST_ERROR;
            echo json_encode($response);
            exit;
        }
        
        // Авторизуем пользователя
        $USER->Authorize($userId);
        
        // Отправляем email с паролем
        $arEventFields = [
            "NAME" => $firstName . ' ' . $lastName,
            "EMAIL" => $email,
            "PASSWORD" => $password,
            "LOGIN" => $email
        ];
        CEvent::Send("NEW_USER", SITE_ID, $arEventFields);
    }
}

// Получаем корзину пользователя
$basketItems = [];
$totalPrice = 0;
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

while ($arItem = $dbBasketItems->Fetch()) {
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
    "PERSON_TYPE_ID" => 1, // ID типа плательщика (физическое лицо)
    "PAYED" => "N",
    "CANCELED" => "N",
    "STATUS_ID" => "N", // Новый заказ
    "PRICE" => $totalPrice,
    "CURRENCY" => "RUB",
    "USER_ID" => $userId,
    "USER_DESCRIPTION" => $comment,
    "COMMENTS" => "Доставка: " . ($delivery == 'delivery' ? "Курьером" : "Самовывоз") . "\nАдрес: " . $address . "\nТелефон: " . $phone
];

$orderId = CSaleOrder::Add($arOrderFields);

if (!$orderId) {
    $response['message'] = 'Ошибка создания заказа';
    echo json_encode($response);
    exit;
}

// Добавляем товары в заказ
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
    
    CSaleBasket::Add($arBasketFields);
    
    // Удаляем из корзины
    CSaleBasket::Delete($item['ID']);
}

// Сохраняем данные для последних заказов (для гостей)
if (!$USER->IsAuthorized()) {
    $sessionOrders = $_SESSION['RECENT_ORDERS'] ?? [];
    array_unshift($sessionOrders, [
        'ID' => $orderId,
        'ORDER_NUMBER' => $orderId,
        'CREATED_AT' => date('Y-m-d H:i:s'),
        'TOTAL_PRICE' => $totalPrice,
        'STATUS' => 'new'
    ]);
    $_SESSION['RECENT_ORDERS'] = array_slice($sessionOrders, 0, 10);
}

// Сохраняем игровые очки
if ($score > 0) {
    CUser::SetUserField($userId, 'UF_GAME_SCORE', $score);
}

// Отправляем уведомление администратору
$arEventFields = [
    "ORDER_ID" => $orderId,
    "ORDER_NUMBER" => $orderId,
    "USER_NAME" => $firstName . ' ' . $lastName,
    "PHONE" => $phone,
    "EMAIL" => $email,
    "TOTAL_PRICE" => $totalPrice,
    "DELIVERY" => $delivery == 'delivery' ? 'Курьером' : 'Самовывоз',
    "PAYMENT" => $payment == 'cash' ? 'Наличными' : 'Картой онлайн'
];

CEvent::Send("NEW_ORDER", SITE_ID, $arEventFields);

$response['success'] = true;
$response['order_id'] = $orderId;
$response['message'] = 'Заказ успешно создан';

header('Content-Type: application/json');
echo json_encode($response, JSON_UNESCAPED_UNICODE);
?>