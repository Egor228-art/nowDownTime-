<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');

header('Content-Type: application/json');

global $USER, $DB;

$orderId = intval($_GET['id'] ?? 0);

if (!$orderId) {
    echo json_encode(['success' => false]);
    return;
}

if (!$USER->IsAuthorized()) {
    echo json_encode(['success' => false]);
    return;
}

$userId = $USER->GetID();

// Получаем заказ
$sql = "SELECT * FROM orders WHERE ID = " . $orderId . " AND USER_ID = " . $userId;
$result = $DB->Query($sql);

if (!$order = $result->Fetch()) {
    echo json_encode(['success' => false]);
    return;
}

// Получаем товары заказа
$sql = "SELECT * FROM order_items WHERE ORDER_ID = " . $orderId;
$result = $DB->Query($sql);

$items = [];
while ($row = $result->Fetch()) {
    $items[] = $row;
}

echo json_encode([
    'success' => true,
    'order' => $order,
    'items' => $items
]);