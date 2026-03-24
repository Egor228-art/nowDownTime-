<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

// Подключаем модули
CModule::IncludeModule("sale");
CModule::IncludeModule("catalog");
CModule::IncludeModule("iblock"); // Добавляем модуль инфоблоков

$response = ['success' => false, 'message' => ''];

$action = $_REQUEST['action'] ?? '';
$productId = intval($_REQUEST['product_id'] ?? 0);
$quantity = intval($_REQUEST['quantity'] ?? 1);

// Получаем ID пользователя корзины
$fuserId = CSaleBasket::GetBasketUserID();

switch ($action) {
    case 'add':
        if ($productId > 0 && $quantity > 0) {
            // ПРОВЕРЯЕМ, СУЩЕСТВУЕТ ЛИ ТОВАР
            $dbRes = CIBlockElement::GetList(
                [],
                ['ID' => $productId, 'ACTIVE' => 'Y'],
                false,
                false,
                ['ID', 'NAME', 'IBLOCK_ID']
            );
            
            if (!$dbRes->Fetch()) {
                $response['message'] = 'Товар не найден в каталоге';
                $response['error'] = "Товар с ID {$productId} не существует или неактивен";
                echo json_encode($response);
                exit;
            }
            
            // Проверяем, есть ли уже такой товар в корзине
            $dbBasketItems = CSaleBasket::GetList(
                [],
                [
                    "PRODUCT_ID" => $productId,
                    "FUSER_ID" => $fuserId,
                    "LID" => SITE_ID,
                    "ORDER_ID" => "NULL"
                ],
                false,
                false,
                ["ID", "QUANTITY"]
            );
            
            if ($arItem = $dbBasketItems->Fetch()) {
                $newQuantity = $arItem["QUANTITY"] + $quantity;
                $result = CSaleBasket::Update($arItem["ID"], ["QUANTITY" => $newQuantity]);
            } else {
                // Альтернативный способ
                $arFields = array(
                    "PRODUCT_ID" => $productId,
                    "PRODUCT_PRICE_ID" => 0,
                    "PRICE" => 0, // Будет автоматически подставлена
                    "CURRENCY" => "RUB",
                    "QUANTITY" => $quantity,
                    "LID" => SITE_ID,
                    "DELAY" => "N",
                    "CAN_BUY" => "Y",
                    "NAME" => "", // Будет автоматически подставлена
                    "MODULE" => "catalog",
                    "FUSER_ID" => $fuserId,
                    "NOTES" => "",
                );
                
                $result = CSaleBasket::Add($arFields);
                
                if (!$result) {
                    // Пробуем через стандартную функцию
                    $result = Add2BasketByProductID($productId, $quantity);
                }
            }
            
            if ($result) {
                $response['success'] = true;
                $response['message'] = 'Товар добавлен в корзину';
                $response['product_id'] = $productId;
                $response['quantity'] = $quantity;
            } else {
                $response['message'] = 'Ошибка добавления товара';
                if ($ex = $GLOBALS['APPLICATION']->GetException()) {
                    $response['error'] = $ex->GetString();
                } else {
                    $response['error'] = 'Неизвестная ошибка при добавлении в корзину';
                }
            }
        } else {
            $response['message'] = 'Неверные параметры товара';
        }
        break;
        
    case 'get_count':
        $count = 0;
        $dbBasketItems = CSaleBasket::GetList(
            [],
            [
                "FUSER_ID" => $fuserId,
                "LID" => SITE_ID,
                "ORDER_ID" => "NULL"
            ],
            false,
            false,
            ["ID", "QUANTITY"]
        );
        
        while ($arItem = $dbBasketItems->Fetch()) {
            $count += $arItem['QUANTITY'];
        }
        
        $response['success'] = true;
        $response['count'] = $count;
        break;
        
    default:
        $response['message'] = 'Неизвестное действие';
        break;
}

header('Content-Type: application/json');
echo json_encode($response, JSON_UNESCAPED_UNICODE);
?>