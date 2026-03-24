<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');

CModule::IncludeModule('sale');
CModule::IncludeModule('iblock');

header('Content-Type: application/json');

global $USER;

$orderId = intval($_GET['id'] ?? 0);

if (!$orderId) {
    echo json_encode(['success' => false, 'error' => 'Неверный ID заказа']);
    return;
}

if (!$USER->IsAuthorized()) {
    echo json_encode(['success' => false, 'error' => 'Не авторизован']);
    return;
}

$userId = $USER->GetID();

// Получаем заказ
$dbOrder = CSaleOrder::GetList(
    array(),
    array('ID' => $orderId, 'USER_ID' => $userId)
);

if (!$order = $dbOrder->Fetch()) {
    echo json_encode(['success' => false, 'error' => 'Заказ не найден']);
    return;
}

// Получаем товары заказа
$items = array();
$dbBasket = CSaleBasket::GetList(
    array(),
    array('ORDER_ID' => $orderId),
    false,
    false,
    array('ID', 'PRODUCT_ID', 'NAME', 'PRICE', 'QUANTITY')
);

while ($item = $dbBasket->Fetch()) {
    // Получаем картинку товара
    $image = '';
    $res = CIBlockElement::GetList(
        array(),
        array('ID' => $item['PRODUCT_ID']),
        false,
        false,
        array('DETAIL_PICTURE')
    );
    if ($product = $res->GetNext()) {
        if ($product['DETAIL_PICTURE']) {
            $image = CFile::GetPath($product['DETAIL_PICTURE']);
        }
    }
    
    $items[] = array(
        'ID' => $item['ID'],
        'PRODUCT_ID' => $item['PRODUCT_ID'],
        'PRODUCT_NAME' => $item['NAME'],
        'PRICE' => $item['PRICE'],
        'QUANTITY' => $item['QUANTITY'],
        'IMAGE' => $image
    );
}

// Получаем свойства заказа
$deliveryAddress = '';
$deliveryType = 'pickup';
$paymentType = 'cash';

$dbProps = CSaleOrderPropsValue::GetList(
    array(),
    array('ORDER_ID' => $orderId)
);

while ($prop = $dbProps->Fetch()) {
    if ($prop['NAME'] == 'Адрес доставки' || $prop['CODE'] == 'ADDRESS') {
        $deliveryAddress = $prop['VALUE'];
        $deliveryType = 'delivery';
    }
    if ($prop['NAME'] == 'Способ оплаты' || $prop['CODE'] == 'PAYMENT_METHOD') {
        $paymentType = $prop['VALUE'];
    }
}

$statusText = '';
switch ($order['STATUS_ID']) {
    case 'N':
        $statusText = 'Новый';
        break;
    case 'P':
        $statusText = 'Оплачен';
        break;
    case 'F':
        $statusText = 'Доставлен';
        break;
    case 'C':
        $statusText = 'Отменён';
        break;
    default:
        $statusText = $order['STATUS_ID'];
}

echo json_encode([
    'success' => true,
    'order' => array(
        'ID' => $order['ID'],
        'ORDER_NUMBER' => $order['ID'],
        'CREATED_AT' => $order['DATE_INSERT'],
        'TOTAL_PRICE' => $order['PRICE'],
        'STATUS' => $order['STATUS_ID'],
        'STATUS_TEXT' => $statusText,
        'DELIVERY_TYPE' => $deliveryType,
        'DELIVERY_ADDRESS' => $deliveryAddress,
        'PAYMENT_TYPE' => $paymentType,
        'COMMENT' => $order['USER_DESCRIPTION']
    ),
    'items' => $items
]);
?>