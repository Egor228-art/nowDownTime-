<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

if(!CModule::IncludeModule('iblock')) {
    return;
}

$productId = intval($_POST['product_id']);
if ($productId > 0) {
    // Получаем текущее значение счетчика
    $propertyValue = CIBlockElement::GetProperty(
        0, // ID инфоблока будет подставлен автоматически
        $productId,
        array(),
        array("CODE" => "VIEWS_COUNT")
    );
    
    $currentViews = 0;
    if ($arProp = $propertyValue->Fetch()) {
        $currentViews = intval($arProp['VALUE']);
    }
    
    // Увеличиваем счетчик
    CIBlockElement::SetPropertyValuesEx(
        $productId,
        false,
        array("VIEWS_COUNT" => $currentViews + 1)
    );
    
    echo json_encode(['success' => true, 'views' => $currentViews + 1]);
} else {
    echo json_encode(['success' => false]);
}
?>