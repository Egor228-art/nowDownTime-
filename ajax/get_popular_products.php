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

// Получаем все разделы для категорий
$allSections = array();
$secRes = CIBlockSection::GetList(
    array("LEFT_MARGIN" => "ASC"),
    array("IBLOCK_ID" => $iblockId, "ACTIVE" => "Y"),
    false,
    array("ID", "NAME", "IBLOCK_SECTION_ID")
);
while($arSection = $secRes->Fetch()) {
    $allSections[$arSection['ID']] = array(
        'ID' => $arSection['ID'],
        'NAME' => $arSection['NAME'],
        'IBLOCK_SECTION_ID' => $arSection['IBLOCK_SECTION_ID']
    );
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

// Сортируем по просмотрам или по дате
$arOrder = array("PROPERTY_VIEWS_COUNT" => "DESC");
$res = CIBlockElement::GetList($arOrder, $arFilter, false, array("nTopCount" => 8), $arSelect);

$hasProducts = false;
while($arItem = $res->Fetch()) {
    $hasProducts = true;
    
    // Получаем категорию (самую глубокую)
    $categoryName = '';
    $sectionId = $arItem['IBLOCK_SECTION_ID'];
    $maxDepth = 0;
    
    while ($sectionId > 0 && isset($allSections[$sectionId])) {
        // Простая логика - берем название раздела
        $categoryName = $allSections[$sectionId]['NAME'];
        $sectionId = $allSections[$sectionId]['IBLOCK_SECTION_ID'];
    }
    
    // Получаем цену
    $price = 'Цена по запросу';
    $priceValue = 0;
    if (!empty($arItem['PROPERTY_PRICE_VALUE'])) {
        $priceValue = floatval($arItem['PROPERTY_PRICE_VALUE']);
        $price = number_format($priceValue, 0, '', ' ') . ' ₽';
    }
    
    // Получаем изображение
    $imageHtml = '<i class="fas fa-dragon"></i>';
    if (!empty($arItem['PREVIEW_PICTURE'])) {
        $imagePath = CFile::GetPath($arItem['PREVIEW_PICTURE']);
        if ($imagePath) {
            $imageHtml = '<img src="' . $imagePath . '" alt="' . htmlspecialchars($arItem['NAME']) . '">';
        }
    } elseif (!empty($arItem['DETAIL_PICTURE'])) {
        $imagePath = CFile::GetPath($arItem['DETAIL_PICTURE']);
        if ($imagePath) {
            $imageHtml = '<img src="' . $imagePath . '" alt="' . htmlspecialchars($arItem['NAME']) . '">';
        }
    }
    
    // Определяем бейдж
    $badgeHtml = '';
    $viewsCount = intval($arItem['PROPERTY_VIEWS_COUNT_VALUE']);
    if ($viewsCount > 50) {
        $badgeHtml = '<div class="badge">Хит</div>';
    }
    
    $playersCount = !empty($arItem['PROPERTY_PLAYERS_COUNT_VALUE']) ? $arItem['PROPERTY_PLAYERS_COUNT_VALUE'] : '2-6';
    $gameTime = !empty($arItem['PROPERTY_GAME_TIME_VALUE']) ? $arItem['PROPERTY_GAME_TIME_VALUE'] : '60-120';
    
    ?>
    <div class="game-card" onclick="window.location='/catalog/detail.php?ID=<?= $arItem['ID'] ?>'">
        <div class="game-image">
            <?= $imageHtml ?>
            <?= $badgeHtml ?>
        </div>
        <div class="game-info">
            <div class="game-category"><?= htmlspecialchars($categoryName ?: 'Настольная игра') ?></div>
            <h3><?= htmlspecialchars($arItem['NAME']) ?></h3>
            <div class="game-meta">
                <span><i class="fas fa-user-friends"></i> <?= htmlspecialchars($playersCount) ?> игр.</span>
                <span><i class="fas fa-clock"></i> <?= htmlspecialchars($gameTime) ?> мин</span>
            </div>
            <div class="game-price">
                <span class="price"><?= $price ?></span>
                <button class="add-to-cart" onclick="event.stopPropagation(); addToCart(<?= $arItem['ID'] ?>)">
                    <i class="fas fa-shopping-cart"></i>
                </button>
            </div>
        </div>
    </div>
    <?php
}

// Если нет товаров с просмотрами, показываем последние добавленные
if (!$hasProducts) {
    $res = CIBlockElement::GetList(
        array("DATE_CREATE" => "DESC"),
        array("IBLOCK_ID" => $iblockId, "ACTIVE" => "Y"),
        false,
        array("nTopCount" => 8),
        $arSelect
    );
    
    while($arItem = $res->Fetch()) {
        // Получаем категорию
        $categoryName = '';
        $sectionId = $arItem['IBLOCK_SECTION_ID'];
        while ($sectionId > 0 && isset($allSections[$sectionId])) {
            $categoryName = $allSections[$sectionId]['NAME'];
            $sectionId = $allSections[$sectionId]['IBLOCK_SECTION_ID'];
        }
        
        // Получаем цену
        $price = 'Цена по запросу';
        if (!empty($arItem['PROPERTY_PRICE_VALUE'])) {
            $price = number_format(floatval($arItem['PROPERTY_PRICE_VALUE']), 0, '', ' ') . ' ₽';
        }
        
        // Получаем изображение
        $imageHtml = '<i class="fas fa-dragon"></i>';
        if (!empty($arItem['PREVIEW_PICTURE'])) {
            $imagePath = CFile::GetPath($arItem['PREVIEW_PICTURE']);
            if ($imagePath) {
                $imageHtml = '<img src="' . $imagePath . '" alt="' . htmlspecialchars($arItem['NAME']) . '">';
            }
        }
        
        $playersCount = !empty($arItem['PROPERTY_PLAYERS_COUNT_VALUE']) ? $arItem['PROPERTY_PLAYERS_COUNT_VALUE'] : '2-6';
        $gameTime = !empty($arItem['PROPERTY_GAME_TIME_VALUE']) ? $arItem['PROPERTY_GAME_TIME_VALUE'] : '60-120';
        
        ?>
        <div class="game-card" onclick="window.location='/catalog/detail.php?ID=<?= $arItem['ID'] ?>'">
            <div class="game-image">
                <?= $imageHtml ?>
                <div class="badge">Новинка</div>
            </div>
            <div class="game-info">
                <div class="game-category"><?= htmlspecialchars($categoryName ?: 'Настольная игра') ?></div>
                <h3><?= htmlspecialchars($arItem['NAME']) ?></h3>
                <div class="game-meta">
                    <span><i class="fas fa-user-friends"></i> <?= htmlspecialchars($playersCount) ?> игр.</span>
                    <span><i class="fas fa-clock"></i> <?= htmlspecialchars($gameTime) ?> мин</span>
                </div>
                <div class="game-price">
                    <span class="price"><?= $price ?></span>
                    <button class="add-to-cart" onclick="event.stopPropagation(); addToCart(<?= $arItem['ID'] ?>)">
                        <i class="fas fa-shopping-cart"></i>
                    </button>
                </div>
            </div>
        </div>
        <?php
    }
}

// Если вообще нет товаров
if (!$hasProducts && !isset($res)) {
    echo '<div class="alert alert-info">Товары будут добавлены позже</div>';
}
?>