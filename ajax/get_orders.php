<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');

CModule::IncludeModule('sale');

header('Content-Type: application/json');

global $USER;

if (!$USER->IsAuthorized()) {
    echo json_encode(['success' => false, 'error' => 'Не авторизован']);
    return;
}

$userId = $USER->GetID();

// Получаем все заказы пользователя
$orders = array();

$dbOrders = CSaleOrder::GetList(
    array('DATE_INSERT' => 'DESC'),
    array('USER_ID' => $userId),
    false,
    false,
    array('ID', 'DATE_INSERT', 'PRICE', 'CURRENCY', 'STATUS_ID', 'USER_DESCRIPTION')
);

while ($order = $dbOrders->Fetch()) {
    // Получаем адрес доставки из свойств
    $deliveryAddress = '';
    $deliveryType = 'pickup';
    $paymentType = 'cash';
    
    $dbProps = CSaleOrderPropsValue::GetList(
        array(),
        array('ORDER_ID' => $order['ID'])
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
    
    $orders[] = array(
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
    );
}

echo json_encode([
    'success' => true,
    'orders' => $orders
]);
?>