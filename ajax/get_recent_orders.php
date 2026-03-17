<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');

header('Content-Type: application/json');

global $USER, $DB;

if (!$USER->IsAuthorized()) {
    echo json_encode(['success' => true, 'orders' => []]);
    return;
}

$userId = $USER->GetID();

// Получаем 3 последних заказа
$sql = "SELECT * FROM orders 
        WHERE USER_ID = " . $userId . " 
        ORDER BY CREATED_AT DESC 
        LIMIT 3";

$result = $DB->Query($sql);

$orders = [];
while ($row = $result->Fetch()) {
    $orders[] = $row;
}

echo json_encode([
    'success' => true,
    'orders' => $orders
]);