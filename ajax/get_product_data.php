<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

CModule::IncludeModule("iblock");

$productId = intval($_GET['id'] ?? 0);
$response = ['id' => 0];

if ($productId > 0) {
    $dbRes = CIBlockElement::GetList(
        [],
        ['ID' => $productId, 'ACTIVE' => 'Y'],
        false,
        false,
        ['ID', 'NAME', 'IBLOCK_SECTION_ID', 'PREVIEW_PICTURE', 'DETAIL_PICTURE', 'PROPERTY_PRICE', 'PROPERTY_PLAYERS_COUNT', 'PROPERTY_GAME_TIME']
    );
    
    if ($arItem = $dbRes->Fetch()) {
        // Получаем категорию
        $categoryName = '';
        if ($arItem['IBLOCK_SECTION_ID'] > 0) {
            $section = CIBlockSection::GetByID($arItem['IBLOCK_SECTION_ID'])->Fetch();
            $categoryName = $section['NAME'];
        }
        
        // Получаем цену
        $price = 0;
        if ($arItem['PROPERTY_PRICE_VALUE']) {
            $price = floatval($arItem['PROPERTY_PRICE_VALUE']);
        }
        
        // Получаем изображение
        $image = '';
        $pictureId = $arItem['DETAIL_PICTURE'] ?: $arItem['PREVIEW_PICTURE'];
        if ($pictureId) {
            $image = CFile::GetPath($pictureId);
        }
        
        $response = [
            'id' => $arItem['ID'],
            'name' => $arItem['NAME'],
            'category' => $categoryName,
            'price' => $price,
            'image' => $image,
            'players' => $arItem['PROPERTY_PLAYERS_COUNT_VALUE'] ?: '2-4',
            'time' => $arItem['PROPERTY_GAME_TIME_VALUE'] ?: '30-60'
        ];
    }
}

header('Content-Type: application/json');
echo json_encode($response);
?>