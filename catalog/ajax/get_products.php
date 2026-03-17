<?php
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

header('Content-Type: application/json');

if(!CModule::IncludeModule('iblock')) {
    echo json_encode(['error' => 'Модуль инфоблоков не найден']);
    return;
}

// Получаем ID инфоблока
$iblockId = 0;
$res = CIBlock::GetList(array(), array('CODE' => 'products'));
if ($arRes = $res->Fetch()) {
    $iblockId = $arRes['ID'];
}

if (!$iblockId) {
    echo json_encode(['error' => 'Инфоблок не найден']);
    return;
}

$sectionId = intval($_GET['section_id']);

// Получаем все разделы для подстановки названий
$arAllSections = array();
$secRes = CIBlockSection::GetList(
    array("LEFT_MARGIN" => "ASC"),
    array("IBLOCK_ID" => $iblockId, "ACTIVE" => "Y"),
    false,
    array("ID", "NAME", "CODE", "IBLOCK_SECTION_ID")
);

while($arSection = $secRes->Fetch()) {
    $arAllSections[$arSection['ID']] = array(
        'ID' => $arSection['ID'],
        'NAME' => $arSection['NAME'],
        'IBLOCK_SECTION_ID' => (int)$arSection['IBLOCK_SECTION_ID']
    );
}

// Получаем товары
$arSelect = array(
    "ID", "NAME", "IBLOCK_SECTION_ID", "PREVIEW_PICTURE", 
    "DETAIL_PICTURE", "PREVIEW_TEXT", "PROPERTY_PRICE",
    "PROPERTY_PLAYERS_COUNT", "PROPERTY_GAME_TIME"
);

$arFilter = array(
    "IBLOCK_ID" => $iblockId,
    "ACTIVE" => "Y"
);

if ($sectionId > 0) {
    $arFilter["SECTION_ID"] = $sectionId;
    $arFilter["INCLUDE_SUBSECTIONS"] = "N";
}

$res = CIBlockElement::GetList(
    array("DATE_CREATE" => "DESC"),
    $arFilter,
    false,
    false,
    $arSelect
);

$products = array();
while($arItem = $res->GetNext()) {
    $price = 'Цена по запросу';
    $priceValue = 0;
    if ($arItem['PROPERTY_PRICE_VALUE']) {
        $priceParts = explode('|', $arItem['PROPERTY_PRICE_VALUE']);
        $priceNumber = $priceParts[0];
        $price = number_format($priceNumber, 0, '', ' ') . ' ₽';
        $priceValue = floatval($priceParts[0]);
    }

    // Получаем название раздела товара
    $sectionName = '';
    if ($arItem['IBLOCK_SECTION_ID'] > 0 && isset($arAllSections[$arItem['IBLOCK_SECTION_ID']])) {
        $sectionName = $arAllSections[$arItem['IBLOCK_SECTION_ID']]['NAME'];
    }
    
    // Получаем ВСЕ разделы товара
    $productSections = array();
    $db_groups = CIBlockElement::GetElementGroups($arItem['ID'], true);
    while($ar_group = $db_groups->Fetch()) {
        $productSections[] = array(
            'id' => $ar_group['ID'],
            'name' => $ar_group['NAME']
        );
    }

    $image = '';
    if ($arItem['PREVIEW_PICTURE']) {
        $image = CFile::GetPath($arItem['PREVIEW_PICTURE']);
    } elseif ($arItem['DETAIL_PICTURE']) {
        $image = CFile::GetPath($arItem['DETAIL_PICTURE']);
    } else {
        $image = SITE_TEMPLATE_PATH . '/images/no-image.jpg';
    }

    $products[] = array(
        'id' => $arItem['ID'],
        'name' => htmlspecialchars($arItem['NAME']),
        'section_id' => $arItem['IBLOCK_SECTION_ID'],
        'section_name' => htmlspecialchars($sectionName),
        'all_sections' => $productSections, // ВСЕ разделы товара
        'price' => $price,
        'price_value' => $priceValue,
        'image' => $image,
        'description' => htmlspecialchars($arItem['PREVIEW_TEXT'] ?: 'Описание отсутствует'),
        'players_count' => htmlspecialchars($arItem['PROPERTY_PLAYERS_COUNT_VALUE']),
        'game_time' => htmlspecialchars($arItem['PROPERTY_GAME_TIME_VALUE']),
        'url' => '/catalog/detail.php?ID=' . $arItem['ID']
    );
}

echo json_encode(['products' => $products]);
?>