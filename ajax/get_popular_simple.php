<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

if(!CModule::IncludeModule('iblock')) {
    echo '<div class="alert alert-danger">Ошибка загрузки модуля</div>';
    return;
}

// Получаем ID инфоблока
$iblockId = 0;
$res = CIBlock::GetList(array(), array('CODE' => 'products'));
if ($arRes = $res->Fetch()) {
    $iblockId = $arRes['ID'];
}

if (!$iblockId) {
    echo '<div class="alert alert-danger">Инфоблок не найден</div>';
    return;
}

// Получаем товары
$arSelect = array(
    "ID", "NAME", "IBLOCK_SECTION_ID", "PREVIEW_PICTURE", 
    "DETAIL_PICTURE", "PROPERTY_PRICE", "PROPERTY_PLAYERS_COUNT", 
    "PROPERTY_GAME_TIME", "PROPERTY_VIEWS_COUNT"
);

$arFilter = array(
    "IBLOCK_ID" => $iblockId,
    "ACTIVE" => "Y"
);

// Сортируем по просмотрам, если есть, иначе по дате
$arOrder = array("PROPERTY_VIEWS_COUNT" => "DESC");
$res = CIBlockElement::GetList($arOrder, $arFilter, false, array("nTopCount" => 8), $arSelect);

$products = array();
while($arItem = $res->GetNext()) {
    // Получаем категорию
    $sectionName = '';
    if ($arItem['IBLOCK_SECTION_ID'] > 0) {
        $section = CIBlockSection::GetByID($arItem['IBLOCK_SECTION_ID'])->Fetch();
        $sectionName = $section['NAME'];
    }
    
    // Получаем цену
    $price = 'Цена по запросу';
    $priceValue = 0;
    if ($arItem['PROPERTY_PRICE_VALUE']) {
        $priceValue = floatval($arItem['PROPERTY_PRICE_VALUE']);
        $price = number_format($priceValue, 0, '', ' ') . ' ₽';
    }
    
    // Получаем изображение
    $image = '';
    if ($arItem['PREVIEW_PICTURE']) {
        $image = CFile::GetPath($arItem['PREVIEW_PICTURE']);
    } elseif ($arItem['DETAIL_PICTURE']) {
        $image = CFile::GetPath($arItem['DETAIL_PICTURE']);
    }
    
    $products[] = array(
        'id' => $arItem['ID'],
        'name' => $arItem['NAME'],
        'category' => $sectionName,
        'price' => $price,
        'price_value' => $priceValue,
        'image' => $image,
        'players_count' => $arItem['PROPERTY_PLAYERS_COUNT_VALUE'] ?: '2-6',
        'game_time' => $arItem['PROPERTY_GAME_TIME_VALUE'] ?: '60-120',
        'views' => intval($arItem['PROPERTY_VIEWS_COUNT_VALUE'])
    );
}

// Если нет товаров с просмотрами, показываем последние 8
if (empty($products)) {
    $res = CIBlockElement::GetList(
        array("DATE_CREATE" => "DESC"),
        array("IBLOCK_ID" => $iblockId, "ACTIVE" => "Y"),
        false,
        array("nTopCount" => 8),
        $arSelect
    );
    
    while($arItem = $res->GetNext()) {
        $sectionName = '';
        if ($arItem['IBLOCK_SECTION_ID'] > 0) {
            $section = CIBlockSection::GetByID($arItem['IBLOCK_SECTION_ID'])->Fetch();
            $sectionName = $section['NAME'];
        }
        
        $price = $arItem['PROPERTY_PRICE_VALUE'] ? number_format(floatval($arItem['PROPERTY_PRICE_VALUE']), 0, '', ' ') . ' ₽' : 'Цена по запросу';
        $image = '';
        if ($arItem['PREVIEW_PICTURE']) {
            $image = CFile::GetPath($arItem['PREVIEW_PICTURE']);
        } elseif ($arItem['DETAIL_PICTURE']) {
            $image = CFile::GetPath($arItem['DETAIL_PICTURE']);
        }
        
        $products[] = array(
            'id' => $arItem['ID'],
            'name' => $arItem['NAME'],
            'category' => $sectionName,
            'price' => $price,
            'image' => $image,
            'players_count' => $arItem['PROPERTY_PLAYERS_COUNT_VALUE'] ?: '2-6',
            'game_time' => $arItem['PROPERTY_GAME_TIME_VALUE'] ?: '60-120'
        );
    }
}

// Выводим HTML напрямую
if (empty($products)) {
    echo '<div class="alert alert-info">Товары будут добавлены позже</div>';
} else {
    foreach ($products as $product) {
        ?>
        <div class="game-card" onclick="window.location='/catalog/detail.php?ID=<?= $product['id'] ?>'">
            <div class="game-image">
                <? if ($product['image']): ?>
                    <img src="<?= $product['image'] ?>" alt="<?= htmlspecialchars($product['name']) ?>">
                <? else: ?>
                    <i class="fas fa-dragon"></i>
                <? endif; ?>
                <? if ($product['views'] > 100): ?>
                    <div class="badge">Хит</div>
                <? endif; ?>
            </div>
            <div class="game-info">
                <div class="game-category"><?= htmlspecialchars($product['category'] ?: 'Настольная игра') ?></div>
                <h3><?= htmlspecialchars($product['name']) ?></h3>
                <div class="game-meta">
                    <span><i class="fas fa-user-friends"></i> <?= htmlspecialchars($product['players_count']) ?> игр.</span>
                    <span><i class="fas fa-clock"></i> <?= htmlspecialchars($product['game_time']) ?> мин</span>
                </div>
                <div class="game-price">
                    <span class="price"><?= $product['price'] ?></span>
                    <button class="add-to-cart" onclick="event.stopPropagation(); addToCart(<?= $product['id'] ?>)">
                        <i class="fas fa-shopping-cart"></i>
                    </button>
                </div>
            </div>
        </div>
        <?php
    }
}
?>