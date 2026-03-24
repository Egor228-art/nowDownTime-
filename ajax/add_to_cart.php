<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

CModule::IncludeModule("sale");
CModule::IncludeModule("iblock");

$response = ['success' => false, 'message' => ''];

$action = $_REQUEST['action'] ?? '';
$productId = intval($_REQUEST['product_id'] ?? 0);
$quantity = intval($_REQUEST['quantity'] ?? 1);
$fuserId = CSaleBasket::GetBasketUserID();

function getProductData($productId) {
    $dbRes = CIBlockElement::GetList(
        [],
        ['ID' => $productId, 'ACTIVE' => 'Y'],
        false,
        false,
        ['ID', 'NAME', 'IBLOCK_ID', 'DETAIL_PICTURE', 'PREVIEW_PICTURE']
    );
    
    if ($arRes = $dbRes->Fetch()) {
        $price = 0;
        $dbProps = CIBlockElement::GetProperty(
            $arRes['IBLOCK_ID'],
            $productId,
            [],
            ['CODE' => 'PRICE']
        );
        if ($arProp = $dbProps->Fetch()) {
            $price = floatval($arProp['VALUE']);
        }
        
        $image = '';
        $pictureId = $arRes['DETAIL_PICTURE'] ?: $arRes['PREVIEW_PICTURE'];
        if ($pictureId) {
            $image = CFile::GetPath($pictureId);
        }
        
        return [
            'NAME' => $arRes['NAME'],
            'PRICE' => $price,
            'IMAGE' => $image
        ];
    }
    
    return null;
}

switch ($action) {
    case 'add':
        if ($productId > 0 && $quantity > 0) {
            $productData = getProductData($productId);
            
            if (!$productData) {
                $response['message'] = 'Товар не найден';
                break;
            }
            
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
                $result = CSaleBasket::Update($arItem["ID"], [
                    "QUANTITY" => $arItem["QUANTITY"] + $quantity,
                    "NAME" => $productData['NAME'],
                    "PRICE" => $productData['PRICE']
                ]);
            } else {
                $arFields = [
                    "PRODUCT_ID" => $productId,
                    "PRICE" => $productData['PRICE'],
                    "CURRENCY" => "RUB",
                    "QUANTITY" => $quantity,
                    "LID" => SITE_ID,
                    "NAME" => $productData['NAME'],
                    "MODULE" => "catalog",
                    "FUSER_ID" => $fuserId
                ];
                $result = CSaleBasket::Add($arFields);
            }
            
            if ($result) {
                $response['success'] = true;
                $response['product_name'] = $productData['NAME'];
                $response['product_price'] = $productData['PRICE'];
                $response['product_image'] = $productData['IMAGE'];
            } else {
                $response['message'] = 'Ошибка добавления';
            }
        }
        break;
        
    case 'remove':
        // УДАЛЕНИЕ ОДНОГО ТОВАРА (уменьшение количества на 1)
        if ($productId > 0) {
            // Находим запись в корзине
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
            
            $deleted = false;
            if ($arItem = $dbBasketItems->Fetch()) {
                $newQuantity = $arItem["QUANTITY"] - 1;
                
                if ($newQuantity > 0) {
                    // Если после удаления остается больше 0 - просто уменьшаем количество
                    $result = CSaleBasket::Update($arItem["ID"], ["QUANTITY" => $newQuantity]);
                    if ($result) $deleted = true;
                } else {
                    // Если количество станет 0 - удаляем запись
                    $result = CSaleBasket::Delete($arItem["ID"]);
                    if ($result) $deleted = true;
                }
            }
            
            if ($deleted) {
                $response['success'] = true;
                $response['message'] = 'Товар удален из корзины';
                $response['product_id'] = $productId;
            } else {
                $response['message'] = 'Товар не найден в корзине';
            }
        } else {
            $response['message'] = 'Неверный ID товара';
        }
        break;
        
    case 'clear':
        $result = CSaleBasket::DeleteAll($fuserId);
        $response['success'] = $result;
        break;
        
    case 'get_full':
        $items = [];
        $dbBasketItems = CSaleBasket::GetList(
            [],
            [
                "FUSER_ID" => $fuserId,
                "LID" => SITE_ID,
                "ORDER_ID" => "NULL"
            ],
            false,
            false,
            ["ID", "PRODUCT_ID", "NAME", "QUANTITY", "PRICE"]
        );
        
        while ($arItem = $dbBasketItems->Fetch()) {
            if (empty($arItem['NAME']) || $arItem['PRICE'] <= 0) {
                $productData = getProductData($arItem['PRODUCT_ID']);
                if ($productData) {
                    if (empty($arItem['NAME'])) {
                        CSaleBasket::Update($arItem['ID'], ['NAME' => $productData['NAME']]);
                        $arItem['NAME'] = $productData['NAME'];
                    }
                    if ($arItem['PRICE'] <= 0) {
                        CSaleBasket::Update($arItem['ID'], ['PRICE' => $productData['PRICE']]);
                        $arItem['PRICE'] = $productData['PRICE'];
                    }
                }
            }
            
            // Получаем картинку
            $image = '';
            $dbRes = CIBlockElement::GetList(
                [],
                ["ID" => $arItem['PRODUCT_ID']],
                false,
                false,
                ["DETAIL_PICTURE", "PREVIEW_PICTURE"]
            );
            if ($arRes = $dbRes->Fetch()) {
                $pictureId = $arRes['DETAIL_PICTURE'] ?: $arRes['PREVIEW_PICTURE'];
                if ($pictureId) {
                    $image = CFile::GetPath($pictureId);
                }
            }
            
            $items[] = [
                'PRODUCT_ID' => $arItem['PRODUCT_ID'],
                'NAME' => $arItem['NAME'],
                'QUANTITY' => intval($arItem['QUANTITY']),
                'PRICE' => floatval($arItem['PRICE']),
                'IMAGE' => $image ?: null
            ];
        }
        
        $response['success'] = true;
        $response['items'] = $items;
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
            ["QUANTITY"]
        );
        while ($arItem = $dbBasketItems->Fetch()) {
            $count += $arItem['QUANTITY'];
        }
        $response['success'] = true;
        $response['count'] = $count;
        break;
        
    default:
        $response['message'] = 'Неизвестное действие';
        $response['available_actions'] = ['add', 'remove', 'clear', 'get_full', 'get_count'];
        break;
}

header('Content-Type: application/json');
echo json_encode($response, JSON_UNESCAPED_UNICODE);
?>