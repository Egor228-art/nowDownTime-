<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

CModule::IncludeModule("sale");

$response = ['success' => false, 'orders' => []];

global $USER;

if ($USER->IsAuthorized()) {
    $userId = $USER->GetID();
    
    // Получаем последние 3 заказа пользователя
    $dbOrders = CSaleOrder::GetList(
        ["DATE_INSERT" => "DESC"],
        ["USER_ID" => $userId, "CANCELED" => "N"],
        false,
        ["nTopCount" => 3],
        ["ID", "ORDER_NUMBER", "DATE_INSERT", "PRICE", "CURRENCY", "STATUS_ID", "PAYED"]
    );
    
    while ($arOrder = $dbOrders->Fetch()) {
        // Получаем статус заказа
        $statusName = '';
        $dbStatus = CSaleStatus::GetList([], ["ID" => $arOrder['STATUS_ID']]);
        if ($arStatus = $dbStatus->Fetch()) {
            $statusName = $arStatus['NAME'];
        }
        
        $response['orders'][] = [
            'ID' => $arOrder['ID'],
            'ORDER_NUMBER' => $arOrder['ORDER_NUMBER'],
            'CREATED_AT' => $arOrder['DATE_INSERT'],
            'TOTAL_PRICE' => $arOrder['PRICE'],
            'CURRENCY' => $arOrder['CURRENCY'],
            'STATUS' => $arOrder['STATUS_ID'],
            'STATUS_NAME' => $statusName,
            'PAYED' => $arOrder['PAYED']
        ];
    }
    
    $response['success'] = true;
} else {
    // Для неавторизованных пользователей - показываем заказы из сессии
    $sessionOrders = $_SESSION['RECENT_ORDERS'] ?? [];
    if (!empty($sessionOrders)) {
        $response['success'] = true;
        $response['orders'] = array_slice($sessionOrders, 0, 3);
        $response['is_guest'] = true;
    } else {
        $response['success'] = true;
        $response['orders'] = [];
        $response['message'] = 'Нет заказов';
    }
}

header('Content-Type: application/json');
echo json_encode($response, JSON_UNESCAPED_UNICODE);
?>