<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

// Подключаем модуль инфоблоков
if(!CModule::IncludeModule('iblock')) {
    die('Ошибка: модуль инфоблоков не установлен');
}

// Получаем ID инфоблока "Товары"
$iblockId = 0;
$res = CIBlock::GetList(array(), array('CODE' => 'products'));
if ($arRes = $res->Fetch()) {
    $iblockId = $arRes['ID'];
}

if (!$iblockId) {
    echo '<div class="container"><div class="alert alert-danger">Инфоблок "Товары" не найден</div></div>';
    require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
    return;
}

// --- ПОЛУЧАЕМ ВСЕ РАЗДЕЛЫ ---
function buildSectionTree($sections, $parentId = 0) {
    $tree = array();
    foreach ($sections as $id => $section) {
        if ($section['IBLOCK_SECTION_ID'] == $parentId) {
            $section['CHILDREN'] = buildSectionTree($sections, $id);
            $tree[$id] = $section;
        }
    }
    return $tree;
}

$arAllSections = array();
$secRes = CIBlockSection::GetList(
    array("LEFT_MARGIN" => "ASC"),
    array("IBLOCK_ID" => $iblockId, "ACTIVE" => "Y"),
    false,
    array("ID", "NAME", "CODE", "IBLOCK_SECTION_ID", "SECTION_PAGE_URL", "DEPTH_LEVEL", "PICTURE", "ELEMENT_CNT")
);

while($arSection = $secRes->GetNext()) {
    $sectionImage = '';
    if ($arSection['PICTURE']) {
        $arSection['PICTURE'] = CFile::GetFileArray($arSection['PICTURE']);
        $sectionImage = $arSection['PICTURE']['SRC'];
    }
    $arAllSections[$arSection['ID']] = array(
        'ID' => $arSection['ID'],
        'NAME' => $arSection['NAME'],
        'CODE' => $arSection['CODE'],
        'IBLOCK_SECTION_ID' => (int)$arSection['IBLOCK_SECTION_ID'],
        'DEPTH_LEVEL' => $arSection['DEPTH_LEVEL'],
        'IMAGE' => $sectionImage,
        'ELEMENT_CNT' => $arSection['ELEMENT_CNT'],
        'URL' => '/catalog/?SECTION_ID=' . $arSection['ID']
    );
}

$sectionTree = buildSectionTree($arAllSections);

// Получаем ID раздела из URL
$sectionId = intval($_GET['SECTION_ID']);
$parentSectionId = 0;

if ($sectionId > 0 && isset($arAllSections[$sectionId])) {
    $parentSectionId = $arAllSections[$sectionId]['IBLOCK_SECTION_ID'];
}

// Строим путь к текущему разделу
$currentSectionPath = array();
if ($sectionId > 0 && isset($arAllSections[$sectionId])) {
    $tempId = $sectionId;
    while ($tempId > 0 && isset($arAllSections[$tempId])) {
        array_unshift($currentSectionPath, $arAllSections[$tempId]);
        $tempId = $arAllSections[$tempId]['IBLOCK_SECTION_ID'];
    }
}

// --- ПОЛУЧАЕМ ТОВАРЫ ---
$arSelect = array(
    "ID", "NAME", "IBLOCK_SECTION_ID", "PREVIEW_PICTURE", 
    "DETAIL_PICTURE", "PREVIEW_TEXT", "PROPERTY_PRICE",
    "PROPERTY_PLAYERS_COUNT", "PROPERTY_GAME_TIME" // Добавили новые поля
);

$arFilter = array(
    "IBLOCK_ID" => $iblockId,
    "ACTIVE" => "Y"
);

if ($sectionId > 0) {
    $arFilter["SECTION_ID"] = $sectionId;
    $arFilter["INCLUDE_SUBSECTIONS"] = "N";
    $currentSectionName = $arAllSections[$sectionId]['NAME'];
} else {
    $currentSectionName = 'Каталог товаров';
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
    if ($arItem['PROPERTY_PRICE_VALUE']) {
        $priceParts = explode('|', $arItem['PROPERTY_PRICE_VALUE']);
        $priceNumber = $priceParts[0];
        $price = number_format($priceNumber, 0, '', ' ') . ' ₽';
    }

    // Получаем название раздела товара
    $sectionName = '';
    if ($arItem['IBLOCK_SECTION_ID'] > 0 && isset($arAllSections[$arItem['IBLOCK_SECTION_ID']])) {
        $sectionName = $arAllSections[$arItem['IBLOCK_SECTION_ID']]['NAME'];
    }
    
    // ОТЛАДКА: смотрим все разделы товара
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
        'name' => $arItem['NAME'],
        'section_id' => $arItem['IBLOCK_SECTION_ID'],
        'section_name' => $sectionName,
        'all_sections' => $productSections, // ВСЕ разделы товара
        'price' => $price,
        'price_value' => $arItem['PROPERTY_PRICE_VALUE'] ? floatval($priceParts[0]) : 0,
        'image' => $image,
        'description' => htmlspecialchars($arItem['PREVIEW_TEXT'] ?: 'Описание отсутствует'),
        'players_count' => htmlspecialchars($arItem['PROPERTY_PLAYERS_COUNT_VALUE'] ?: '2-4'),
        'game_time' => htmlspecialchars($arItem['PROPERTY_GAME_TIME_VALUE'] ?: '30-60'),
        'url' => '/catalog/detail.php?ID=' . $arItem['ID']
    );
}

$APPLICATION->SetTitle($currentSectionName);
?>

<!-- Подключаем Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<div class="catalog-page">
    <!-- Боковая панель -->
    <div class="catalog-sidebar" id="catalogSidebar">
        <div class="sidebar-content">
            <div class="sidebar-header">
                <button class="mode-toggle" id="modeToggle" title="Развернуть горизонтальное меню">
                    <i class="fas fa-dragon"></i>
                    <i class="fas fa-bars menu-icon"></i>
                </button>
                <span class="sidebar-title">Категории</span>
            </div>

            <div class="tree-container" id="treeContainer">
                <?= renderTree($sectionTree, $sectionId, $currentSectionPath) ?>
            </div>
        </div>
    </div>

    <!-- Основной контент -->
    <div class="catalog-content" id="catalogContent">
        <!-- Горизонтальное меню -->
        <div class="horizontal-nav" id="horizontalNav">
            <div class="horizontal-nav-header">
                <button class="back-horizontal-nav" id="backHorizontalNav" style="<?= $sectionId > 0 ? 'opacity:1; visibility:visible;' : 'opacity:0; visibility:hidden;' ?>">
                    <i class="fas fa-arrow-left"></i>
                </button>
                
                <div class="horizontal-nav-title">
                    <i class="fas fa-dragon"></i>
                    <span id="horizontalNavTitle"><?= $sectionId > 0 ? $arAllSections[$sectionId]['NAME'] : 'Категории' ?></span>
                </div>
                
                <button class="mode-toggle-horizontal" id="modeToggleHorizontal" title="Вернуться к вертикальному меню">
                    <i class="fas fa-bars"></i>
                    <i class="fas fa-dragon dragon-icon"></i>
                </button>
            </div>
            
            <div class="horizontal-nav-content" id="horizontalNavContent">
                <? if ($sectionId > 0): ?>
                    <? 
                    $subsections = array_filter($arAllSections, function($s) use ($sectionId) {
                        return $s['IBLOCK_SECTION_ID'] == $sectionId;
                    });
                    
                    foreach ($subsections as $subsection): 
                    ?>
                        <div class="horizontal-section-card" data-section-id="<?= $subsection['ID'] ?>" data-has-children="<?= hasChildren($subsection['ID'], $arAllSections) ? 'true' : 'false' ?>">
                            <div class="horizontal-section-image">
                                <? if ($subsection['IMAGE']): ?>
                                    <img src="<?= $subsection['IMAGE'] ?>" alt="<?= $subsection['NAME'] ?>">
                                <? else: ?>
                                    <i class="fas fa-folder"></i>
                                <? endif; ?>
                            </div>
                            <div class="horizontal-section-info">
                                <div class="horizontal-section-name"><?= $subsection['NAME'] ?></div>
                                <? if ($subsection['ELEMENT_CNT'] > 0): ?>
                                    <div class="horizontal-section-count"><?= $subsection['ELEMENT_CNT'] ?> товаров</div>
                                <? endif; ?>
                            </div>
                        </div>
                    <? endforeach; ?>
                    
                    <? if (empty($subsections)): ?>
                        <div class="no-subsections">
                            <i class="fas fa-folder-open"></i>
                            <p>В этом разделе нет подкатегорий</p>
                        </div>
                    <? endif; ?>
                <? else: ?>
                    <? 
                    $mainSections = array_filter($arAllSections, function($s) {
                        return $s['IBLOCK_SECTION_ID'] == 0;
                    });
                    
                    foreach ($mainSections as $mainSection): 
                    ?>
                        <div class="horizontal-section-card" data-section-id="<?= $mainSection['ID'] ?>" data-has-children="<?= hasChildren($mainSection['ID'], $arAllSections) ? 'true' : 'false' ?>">
                            <div class="horizontal-section-image">
                                <? if ($mainSection['IMAGE']): ?>
                                    <img src="<?= $mainSection['IMAGE'] ?>" alt="<?= $mainSection['NAME'] ?>">
                                <? else: ?>
                                    <i class="fas fa-dragon"></i>
                                <? endif; ?>
                            </div>
                            <div class="horizontal-section-info">
                                <div class="horizontal-section-name"><?= $mainSection['NAME'] ?></div>
                                <? if ($mainSection['ELEMENT_CNT'] > 0): ?>
                                    <div class="horizontal-section-count"><?= $mainSection['ELEMENT_CNT'] ?> товаров</div>
                                <? endif; ?>
                            </div>
                        </div>
                    <? endforeach; ?>
                <? endif; ?>
            </div>
        </div>

        <div class="container">
            <!-- Хлебные крошки -->
            <div class="breadcrumbs" id="breadcrumbs">
                <a href="/catalog/">Главная</a>
                <? if (!empty($currentSectionPath)): ?>
                    <? foreach ($currentSectionPath as $pathSection): ?>
                        <span class="separator">/</span>
                        <? if ($pathSection['ID'] == $sectionId): ?>
                            <span class="active"><?= htmlspecialchars($pathSection['NAME']) ?></span>
                        <? else: ?>
                            <a href="#" onclick="loadSection(<?= $pathSection['ID'] ?>); return false;"><?= htmlspecialchars($pathSection['NAME']) ?></a>
                        <? endif; ?>
                    <? endforeach; ?>
                <? endif; ?>
            </div>

            <div class="catalog-header">
                <h1 id="catalogTitle"><?= htmlspecialchars($currentSectionName) ?></h1>
            </div>

            <div class="products-grid" id="productsGrid">
                <? if (empty($products)): ?>
                    <div class="alert alert-info">В этом разделе пока нет товаров</div>
                <? else: ?>
                    <? foreach ($products as $product): ?>
                        <div class="product-card" onclick="window.location='<?= $product['url'] ?>'">
                            <div class="product-image">
                                <img src="<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
                            </div>
                            <div class="product-badge"><?= htmlspecialchars($product['section_name']) ?></div>
                            <div class="product-info">
                                <h3 class="product-title"><?= htmlspecialchars($product['name']) ?></h3>
                                
                                <div class="product-details">
                                    <div class="product-detail">
                                        <i class="fas fa-users"></i>
                                        <span><?= htmlspecialchars($product['players_count']) ?> игроков</span>
                                    </div>
                                    <div class="product-detail">
                                        <i class="fas fa-clock"></i>
                                        <span><?= htmlspecialchars($product['game_time']) ?> мин</span>
                                    </div>
                                </div>
                                
                                <div class="product-footer">
                                    <div class="product-price"><?= htmlspecialchars($product['price']) ?></div>
                                    <button class="product-cart" onclick="event.stopPropagation(); addToCart(<?= $product['id'] ?>)">
                                        <i class="fas fa-shopping-cart"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    <? endforeach; ?>
                <? endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
    /* ===== ОСНОВНЫЕ СТИЛИ ===== */
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        margin-top: 120px;
    }

    .catalog-page {
        display: flex;
        min-height: 600px;
        position: relative;
    }

    .page-wrapper {
    background: #8b1a1a;  /* Красный */}

    /* ===== БОКОВАЯ ПАНЕЛЬ ===== */
    .catalog-sidebar {
        border-top-right-radius: 20px;
        border-bottom-right-radius: 20px;
        width: 70px;
        background: linear-gradient(135deg, #2c3e50, #1a2634);
        color: white;
        transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        overflow: hidden;
        position: relative;
        flex-shrink: 0;
        box-shadow: 2px 0 20px rgba(0,0,0,0.15);
        z-index: 100;
    }

    .catalog-sidebar:hover {
        width: 240px;
    }

    .sidebar-header {
        display: flex;
        align-items: center;
        padding: 15px;
        border-bottom: 1px solid rgba(255,255,255,0.1);
        gap: 15px;
    }

    .sidebar-title {
        font-size: 18px;
        font-weight: 600;
        color: #eabb66;
        white-space: nowrap;
        opacity: 0;
        transition: opacity 0.2s;
    }

    .catalog-sidebar:hover .sidebar-title {
        opacity: 1;
    }

    .mode-toggle {
        width: 40px;
        height: 40px;
        border: none;
        background: rgba(255,255,255,0.1);
        border-radius: 10px;
        color: white;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        transition: all 0.3s;
        flex-shrink: 0;

        border-radius: 12px;
        border: 1px solid rgba(255,255,255,0.1);
        position: relative;
    }

    .mode-toggle i {
        transition: all 0.3s;
    }

    .mode-toggle .fa-dragon {
        display: block;
    }

    .mode-toggle .fa-bars {
        display: none;
    }

    .mode-toggle:hover .fa-dragon {
        display: none;
    }

    .mode-toggle:hover .fa-bars {
        display: block;
    }

    .mode-toggle:hover {
        transform: scale(1.05);
    }

    /* Дерево категорий */
    .tree-container {
        display: flex;
        padding: 10px 0;
        flex-direction: column;
        align-items: flex-start;
    }

    .tree-item {
        margin-bottom: 2px;
        position: relative;
    }

    .tree-header {
        display: flex;
        align-items: center;
        padding: 8px 15px;
        border-radius: 8px;
        margin: 0 5px;
        transition: all 0.2s;
        cursor: pointer;
        white-space: nowrap;
        /* ТВОИ ПРАВКИ: */
        background: transparent;
        border-left: 3px solid transparent;
    }

    .tree-header:hover {
        background: rgba(255,255,255,0.15);
        border-left-color: #eabb66;
    }

    .tree-header.active {
        background: #eabc6638;
        border-left-color: #eabb66; /* Тоже золотой для активного */
    }

    .section-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        overflow: hidden;
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 12px;
    }

    .section-icon i {
        font-size: 20px;
        color: white;
    }

    .section-icon img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .section-name {
        flex-grow: 1;
        font-size: 14px;
        font-weight: 500;
        color: white;
        text-decoration: none;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .section-name:hover {
        color: #eabb66;
    }

    .tree-header.active .section-name:hover {
        color: #2c3e50;
    }

    .toggle-children {
        background: none;
        border: none;
        color: white;
        cursor: pointer;
        font-size: 12px;
        width: 24px;
        height: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s;
        border-radius: 8px;
        margin-left: 5px;
    }

    .toggle-children:hover {
        color: var(--Gold);
        background: rgba(255,255,255,0.1);
    }

    .toggle-children.expanded {
        transform: rotate(90deg);
    }

    .tree-children {
        display: none;
        margin-left: 20px;
        /* ТВОИ ПРАВКИ: */
        border-left: 1px dashed #eabb66;
    }

    .tree-children.expanded {
        display: block;
    }

    /* Свернутое состояние */
    .catalog-sidebar:not(:hover) .section-name,
    .catalog-sidebar:not(:hover) .toggle-children,
    .catalog-sidebar:not(:hover) .sidebar-title,
    .catalog-sidebar:not(:hover) .tree-children {
        display: none;
    }

    .catalog-sidebar:not(:hover) .tree-header {
        padding: 8px;
        justify-content: center;
    }

    .catalog-sidebar:not(:hover) .section-icon {
        margin-right: 0;
    }

    /* Показываем подразделы активного раздела при наведении */
    .catalog-sidebar:not(:hover) .tree-item.has-active .tree-children {
        display: block;
        position: absolute;
        left: 70px;
        top: 0;
        background: #2c3e50;
        padding: 10px;
        border-radius: 12px;
        box-shadow: 5px 5px 20px rgba(0,0,0,0.3);
        min-width: 220px;
        z-index: 200;
        margin-left: 0;
    }

    .catalog-sidebar:not(:hover) .tree-item.has-active .tree-children .section-name {
        display: block !important;
        color: white;
        padding: 8px 12px;
    }

    /* ===== ГОРИЗОНТАЛЬНОЕ МЕНЮ ===== */
    .horizontal-nav {
        display: none;
        background: linear-gradient(135deg, #2c3e50, #1a2634);
        color: white;
        border-radius: 24px;
        box-shadow: 0 15px 40px rgba(0,0,0,0.25);
        overflow: hidden;
        border: 1px solid rgba(255,255,255,0.1);
        transition: border-radius 0.3s ease;
    }

    .horizontal-nav.active {
        display: block;
        animation: slideDown 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .horizontal-nav.hiding-complete {
        animation: slideUp 0.4s cubic-bezier(0.4, 0, 0.2, 1) forwards;
    }

    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes slideUp {
        from {
            opacity: 1;
            transform: translateY(0);
        }
        to {
            opacity: 0;
            transform: translateY(-30px);
            display: none;
        }
    }

    .nav-header {
        display: none;
    }

    .horizontal-nav-header {
        display: flex;
        align-items: center;
        padding: 8px 25px;
        border-bottom: 1px solid rgba(255,255,255,0.1);
        gap: 15px;
        cursor: pointer;
        transition: background-color 0.3s;
        position: relative;
        z-index: 2;
    }

    .horizontal-nav-header:hover {
        background: rgba(255,255,255,0.03);
    }

    .horizontal-nav-header button {
        cursor: pointer;
        position: relative;
        z-index: 3;
    }

    .horizontal-nav-content {
        padding: 15px;
        display: flex;
        gap: 20px;
        overflow-x: auto;
        overflow-y: hidden;
        white-space: nowrap;
        scroll-behavior: smooth;
        min-height: 220px;
        max-height: 300px;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        opacity: 1;
        transform-origin: top;
    }

    /* Стили для свернутого состояния */
    .horizontal-nav-content.collapsed {
        min-height: 0;
        max-height: 0;
        padding: 0 15px;
        opacity: 0;
        margin-top: 0;
        margin-bottom: 0;
        overflow: hidden;
        pointer-events: none;
    }

    /* Анимация для иконки сворачивания */
    .collapse-horizontal i {
        transition: transform 0.3s ease;
    }

    /* Стили для кнопок управления */
    .horizontal-nav-controls {
        display: flex;
        gap: 8px;
        align-items: center;
    }

    .collapse-horizontal {
        background: rgba(255,255,255,0.1);
        border: none;
        color: white;
        width: 44px;
        height: 44px;
        border-radius: 12px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        transition: all 0.3s;
        border: 1px solid rgba(255,255,255,0.1);
    }

    .collapse-horizontal:hover {
        background: #eabb66;
        color: #2c3e50;
        transform: translateY(-2px);
    }

    /* Корректировка для мобильных */
    @media (max-width: 768px) {
        .horizontal-nav-content {
            min-height: 180px;
        }
        
        .horizontal-nav-content.collapsed {
            min-height: 0;
        }
    }

    .back-horizontal-nav {
        background: rgba(255,255,255,0.1);
        border: none;
        color: white;
        width: 44px;
        height: 44px;
        border-radius: 12px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        transition: all 0.3s;
        border: 1px solid rgba(255,255,255,0.1);
        text-decoration: none;
    }

    .back-horizontal-nav.visible {
        opacity: 1;
        visibility: visible;
    }

    .back-horizontal-nav:hover {
        background: #eabb66;
        color: #2c3e50;
        transform: translateX(-5px);
        border-color: #eabb66;
    }

    .horizontal-nav-title {
        display: flex;
        align-items: center;
        gap: 12px;
        font-size: 22px;
        font-weight: 700;
        flex-grow: 1;
    }

    .horizontal-nav-title i {
        color: #eabb66;
        font-size: 26px;
        filter: drop-shadow(0 2px 5px rgba(234, 187, 102, 0.3));
    }

    .horizontal-nav-title span {
        background: linear-gradient(135deg, #fff, #eabb66);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .mode-toggle-horizontal {
        background: rgba(255,255,255,0.1);
        border: none;
        color: white;
        width: 44px;
        height: 44px;
        border-radius: 12px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
        transition: all 0.3s;
        border: 1px solid rgba(255,255,255,0.1);
        position: relative;
    }

    .mode-toggle-horizontal i {
        transition: all 0.3s;
    }

    .mode-toggle-horizontal .fa-bars {
        display: block;
    }

    .mode-toggle-horizontal .fa-dragon {
        display: none;
    }

    .mode-toggle-horizontal:hover, .mode-toggle:hover {
        background: #eabb66;
        color: #2c3e50;
        transform: rotate(180deg);
    }

    .mode-toggle-horizontal:hover .fa-bars {
        display: none;
    }

    .mode-toggle-horizontal:hover .fa-dragon {
        display: block;
        transform: rotate(180deg);
    }

    .horizontal-nav-content {
        padding: 15px;
        display: flex;
        gap: 20px;
        overflow-x: auto;
        overflow-y: hidden;
        white-space: nowrap;
        scroll-behavior: smooth;
        min-height: 220px;
    }

    /* Кастомный скроллбар */
    .horizontal-nav-content::-webkit-scrollbar {
        height: 8px;
    }

    .horizontal-nav-content::-webkit-scrollbar-track {
        background: rgba(255,255,255,0.05);
        border-radius: 4px;
    }

    .horizontal-nav-content::-webkit-scrollbar-thumb {
        background: rgba(234, 187, 102, 0.3);
        border-radius: 4px;
    }

    .horizontal-nav-content::-webkit-scrollbar-thumb:hover {
        background: #eabb66;
    }

    /* Карточки разделов */
    .horizontal-section-card {
        display: inline-flex;
        flex-direction: column;
        text-decoration: none;
        color: white;
        background: rgba(255,255,255,0.05);
        border-radius: 16px;
        transition: all 0.3s;
        border: 1px solid rgba(255,255,255,0.1);
        backdrop-filter: blur(10px);
        width: 180px;
        flex-shrink: 0;
        cursor: pointer;
    }

    .horizontal-section-card:hover {
        transform: translateY(-8px);
        background: rgba(255,255,255,0.1);
        border-color: #eabb66;
        box-shadow: 0 15px 30px rgba(234, 187, 102, 0.2);
    }

    .horizontal-section-card.active {
        border: 2px solid #eabb66;
        background: rgba(234, 187, 102, 0.1);
        box-shadow: 0 10px 25px rgba(234, 187, 102, 0.3);
    }

    .horizontal-section-image {
        width: 100%;
        aspect-ratio: 1;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .horizontal-section-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s;
    }

    .horizontal-section-card:hover .horizontal-section-image img {
        transform: scale(1.1);
        z-index: 1;
    }

    .horizontal-section-image i {
        font-size: 50px;
        color: white;
        opacity: 0.8;
        filter: drop-shadow(0 4px 10px rgba(0,0,0,0.2));
        /* ТВОИ ПРАВКИ: */
        transition: all 0.3s;
    }

    .horizontal-section-card:hover .horizontal-section-image i {
        /* ТВОИ ПРАВКИ: */
        color: #eabb66;
        transform: scale(1.1);
    }

    .horizontal-section-info {
        padding: 10px;
        text-align: center;
    }

    .horizontal-section-name {
        font-size: 16px;
        font-weight: 600;
        margin-bottom: 5px;
        color: white;
        transition: color 0.3s;
        white-space: normal;
        word-break: break-word;
    }

    .horizontal-section-card:hover .horizontal-section-name {
        color: #eabb66;
    }

    .horizontal-section-count {
        font-size: 13px;
        color: rgba(255,255,255,0.5);
    }

    .no-subsections {
        width: 100%;
        padding: 40px;
        text-align: center;
        color: rgba(255,255,255,0.5);
    }

    .no-subsections i {
        font-size: 48px;
        margin-bottom: 15px;
    }

    /* ===== ХЛЕБНЫЕ КРОШКИ ===== */
    .breadcrumbs {
        padding: 15px 0;
        font-size: 14px;
        color: #666;
    }

    .breadcrumbs a {
        color: var(--DragonDark);
        text-decoration: none;
        transition: 0.2s;
    }

    .breadcrumbs a:hover {
        color: var(--DragonLightActiv);
    }

    .breadcrumbs .active {
        color: #2c3e50;
        font-weight: 600;
    }

    .breadcrumbs .separator {
        margin: 0 8px;
        color: #999;
    }

    /* ===== ОСНОВНОЙ КОНТЕНТ ===== */
    .catalog-content {
        flex-grow: 1;
        padding: 20px;
        transition: all 0.3s;
    }

    .catalog-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
    }

    .catalog-header h1 {
        font-size: 32px;
        color: #2c3e50;
        margin: 0;
    }

    .products-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 25px;
        margin: 30px 0;
    }

    .product-card {
        background: white;
        border-radius: 24px;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
        border: 1px solid #f0f0f0;
        position: relative;
        cursor: pointer;
        display: flex;
        flex-direction: column;
    }

    .product-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 40px rgba(147, 145, 11, 0.15);
        border-color: var(--GoldFake);
    }

    .product-image {
        height: 350px;
        background: linear-gradient(135deg, #f8f9fa, #e9ecef);
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        overflow: hidden;
    }

    .product-image::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(to bottom, transparent 70%, rgba(0,0,0,0.05));
        pointer-events: none;
    }

    .product-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s ease;
    }

    .product-card:hover .product-image img {
        transform: scale(1.1);
    }

    .product-badge {
        color: var(--DragonLight);
        position: relative;
        top: 10px;
        left: 20px;
        font-size: 13px;
        font-weight: 600;
        z-index: 6;
    }

    .product-info {
        padding: 15px 20px 20px;
        background: white;
        flex-grow: 1;
        display: flex;
        flex-direction: column;
        /* ТВОИ ПРАВКИ: */
        position: relative;
        z-index: 2;
    }

    /* ТВОИ ПРАВКИ: */
    .product-info::before {
        width: 100%;
        content: '';
        position: absolute;
        top: -30px;
        left: 0;
        right: 20px;
        height: 20px;
        background: white;
        border-radius: 50% 50% 0 0;
        box-shadow: 0 -5px 10px rgba(0,0,0,0.05);
    }

    .product-title {
        font-size: 18px;
        font-weight: 700;
        margin: 0 0 12px 0;
        color: #1e293b;
        line-height: 1.4;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        min-height: 50px;
    }

    .product-description {
        color: #64748b;
        font-size: 13px;
        line-height: 1.5;
        margin: 0 0 15px 0;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        padding: 10px 0;
        border-top: 1px dashed #e2e8f0;
        border-bottom: 1px dashed #e2e8f0;
    }

    .product-details {
        display: flex;
        gap: 20px;
        margin-bottom: 15px;
        padding: 5px 0;
    }

    .product-detail {
        display: flex;
        align-items: center;
        gap: 8px;
        color: #475569;
        font-size: 13px;
        font-weight: 500;
    }

    .product-detail i {
        color: var(--GoldFake);
        font-size: 14px;
        width: 18px;
        text-align: center;
    }

    .product-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-top: auto;
        padding-top: 10px;
        border-top: 1px solid #eef2f6;
    }

    .product-price {
        font-size: 24px;
        font-weight: 800;
        color: #1e293b;
        line-height: 1;
        letter-spacing: -0.5px;
        /* ТВОИ ПРАВКИ: */
        position: relative;
        display: inline-block;
    }

    /* ТВОИ ПРАВКИ: */
    .product-price::before {
        content: '◆';
        color: #eabb66;
        font-size: 14px;
        position: relative;
        top: -10px;
        left: -5px;
        opacity: 0.7;
    }

    .product-cart {
        width: 38px;
        height: 38px;
        border: none;
        background: #f9f9f1;
        color: var(--DragonDarkText);
        border-radius: 50%;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        transition: all 0.3s ease;
        box-shadow: 0 8px 15px rgba(147, 127, 11, 0.2);
        border: 1px solid rgba(255, 255, 255, 0.1);
        position: relative;
        overflow: hidden;
    }

    .product-cart::after {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        width: 0;
        height: 0;
        border-radius: 50%;
        background: rgba(234, 187, 102, 0.5);
        transform: translate(-50%, -50%);
        transition: width 0.4s ease, height 0.4s ease;
        z-index: 0;
    }

    .product-cart i {
        position: relative;
        z-index: 1;
    }

    .product-cart:hover {
        background: var(--GoldActiv);
        box-shadow: 0 12px 20px rgba(237, 241, 0, 0.3);
    }

    .product-cart:active {
        transform: scale(0.9);
    }

    .product-cart:active::after {
        width: 100px;
        height: 100px;
        background: rgba(234, 187, 102, 0.3);
    }

    /* Анимация при добавлении в корзину */
    .product-cart.adding {
        animation: cartPulse 0.5s ease;
        background: linear-gradient(135deg, #10b981, #059669);
        color: white;
    }

    .product-cart.adding i {
        animation: cartIconSpin 0.5s ease;
    }

    @keyframes cartPulse {
        0% {
            transform: scale(1);
            box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
        }
        50% {
            transform: scale(1.2);
            box-shadow: 0 0 0 15px rgba(16, 185, 129, 0);
        }
        100% {
            transform: scale(1);
            box-shadow: 0 0 0 0 rgba(16, 185, 129, 0);
        }
    }

    @keyframes cartIconSpin {
        0% {
            transform: rotate(0deg);
        }
        50% {
            transform: rotate(20deg) scale(1.2);
        }
        100% {
            transform: rotate(0deg) scale(1);
        }
    }

    /* Уведомление о добавлении в корзину */
    .cart-notification {
        position: fixed;
        bottom: 30px;
        right: 30px;
        background: linear-gradient(135deg, #10b981, #059669);
        color: white;
        padding: 15px 25px;
        border-radius: 50px;
        box-shadow: 0 10px 30px rgba(16, 185, 129, 0.3);
        font-weight: 600;
        z-index: 9999;
        animation: slideInRight 0.3s ease;
        display: flex;
        align-items: center;
        gap: 10px;
        border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .cart-notification i {
        font-size: 20px;
    }

    .btn-details {
        display: inline-block;
        padding: 10px 25px;
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: white;
        text-decoration: none;
        border-radius: 8px;
        transition: all 0.3s;
        border: none;
        cursor: pointer;
    }

    .btn-details:hover {
        background: linear-gradient(135deg, #5a6fd6, #6842a0);
        transform: scale(1.05);
    }

    .alert {
        padding: 20px;
        border-radius: 12px;
        text-align: center;
    }

    .alert-info {
        background: #d1ecf1;
        color: #0c5460;
        border: 1px solid #bee5eb;
    }

    /* Адаптивность */
    @keyframes slideInRight {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    /* Адаптивность для карточек */
    @media (max-width: 768px) {
        .products-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }
        
        .product-image {
            height: 140px;
        }
        
        .product-badge {
            top: 10px;
            left: 10px;
            padding: 4px 10px;
            font-size: 10px;
        }
        
        .product-info {
            padding: 15px;
        }
        
        .product-title {
            font-size: 15px;
            min-height: 42px;
            margin-bottom: 8px;
        }
        
        .product-description {
            font-size: 12px;
            padding: 8px 0;
            margin-bottom: 10px;
        }
        
        .product-details {
            gap: 12px;
            margin-bottom: 10px;
        }
        
        .product-detail {
            font-size: 11px;
            gap: 5px;
        }
        
        .product-detail i {
            font-size: 12px;
            width: 14px;
        }
        
        .product-price {
            font-size: 18px;
        }
        
        .product-cart {
            width: 38px;
            height: 38px;
            font-size: 16px;
        }
    }

    @media (max-width: 480px) {
        .products-grid {
            grid-template-columns: 1fr;
        }
        
        .product-image {
            height: 180px;
        }
        
        .product-title {
            font-size: 16px;
        }
        
        .product-description {
            font-size: 13px;
        }
        
        .product-detail {
            font-size: 12px;
        }
        
        .product-price {
            font-size: 20px;
        }
        
        .product-cart {
            width: 42px;
            height: 42px;
            font-size: 18px;
        }
    }
</style>

<script>
// Глобальные данные
const sectionsData = <?= json_encode($arAllSections) ?>;
let currentSectionId = <?= $sectionId ?: 0 ?>;
let navigationStack = [<?= $sectionId ?: 0 ?>].filter(id => id > 0); // Стек навигации
let horizontalMode = false;

document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('catalogSidebar');
    const modeToggle = document.getElementById('modeToggle');
    const modeToggleHorizontal = document.getElementById('modeToggleHorizontal');
    const horizontalNav = document.getElementById('horizontalNav');
    const backBtn = document.getElementById('backHorizontalNav');
    
    // ===== РАСКРЫТИЕ ПОДРАЗДЕЛОВ =====
    document.querySelectorAll('.toggle-children').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const treeItem = this.closest('.tree-item');
            const childrenContainer = treeItem.querySelector('.tree-children');
            
            if (childrenContainer) {
                childrenContainer.classList.toggle('expanded');
                this.classList.toggle('expanded');
            }
        });
    });
    
    // ===== ОБРАБОТКА КЛИКОВ ПО РАЗДЕЛАМ В ВЕРТИКАЛЬНОМ МЕНЮ =====
    document.querySelectorAll('.tree-header').forEach(header => {
        header.addEventListener('click', function(e) {
            if (e.target.closest('.toggle-children')) return;
            e.preventDefault();
            
            const treeItem = this.closest('.tree-item');
            if (treeItem) {
                const sectionId = parseInt(treeItem.dataset.sectionId);
                if (sectionId) {
                    // В вертикальном меню начинаем новую навигацию
                    navigationStack = [sectionId];
                    loadSection(sectionId);
                }
            }
        });
    });
    
    // ===== ПЕРЕКЛЮЧЕНИЕ МЕЖДУ МЕНЮ =====
    modeToggle.addEventListener('click', function() {
        sidebar.style.display = 'none';
        horizontalNav.style.display = 'block';
        setTimeout(() => horizontalNav.classList.add('active'), 10);
        horizontalMode = true;
        updateHorizontalNav(currentSectionId);
    });

    modeToggleHorizontal.addEventListener('click', function() {
        // При клике на кнопку возврата в вертикальное меню - полностью скрываем
        hideHorizontalNavComplete();
    });

    // ===== ФУНКЦИЯ ПОЛНОГО СКРЫТИЯ =====
    function hideHorizontalNavComplete() {
        horizontalNav.classList.add('hiding-complete');
        setTimeout(() => {
            horizontalNav.classList.remove('active', 'hiding-complete');
            horizontalNav.style.display = 'none';
            sidebar.style.display = 'block';
            horizontalMode = false;
        }, 400);
    }

    // ===== ФУНКЦИЯ СВОРАЧИВАНИЯ КОНТЕНТА =====
    function toggleHorizontalContent() {
        const content = document.getElementById('horizontalNavContent');
        const header = document.querySelector('.horizontal-nav-header');
        const collapseIcon = document.querySelector('.collapse-horizontal i');
        
        content.classList.toggle('collapsed');
        
        // Меняем иконку
        if (collapseIcon) {
            if (content.classList.contains('collapsed')) {
                collapseIcon.className = 'fas fa-chevron-down';
            } else {
                collapseIcon.className = 'fas fa-chevron-up';
            }
        }
        
        // Меняем скругление низа меню
        if (content.classList.contains('collapsed')) {
            horizontalNav.style.borderRadius = '24px 24px 24px 24px';
        } else {
            horizontalNav.style.borderRadius = '24px 24px 24px 24px';
        }
    }

    // ===== ОБРАБОТКА КЛИКА ПО ЗАГОЛОВКУ =====
    const horizontalHeader = document.querySelector('.horizontal-nav-header');
    if (horizontalHeader) {
        horizontalHeader.addEventListener('click', function(e) {
            // Проверяем, что клик не по кнопкам внутри заголовка
            if (!e.target.closest('button')) {
                toggleHorizontalContent();
            }
        });
    }

    // ===== КНОПКА СВОРАЧИВАНИЯ =====
    const collapseBtn = document.getElementById('collapseHorizontal');
    if (collapseBtn) {
        collapseBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            toggleHorizontalContent();
        });
    }
    
    // ===== КНОПКА НАЗАД =====
    backBtn.addEventListener('click', function() {
        if (currentSectionId === 0) return; // В корне ничего не делаем
        
        // Получаем родительский раздел
        const parentId = sectionsData[currentSectionId]?.IBLOCK_SECTION_ID || 0;
        
        if (parentId > 0) {
            // Если есть родитель, идем к нему
            loadSection(parentId);
            updateHorizontalNav(parentId);
        } else {
            // Если родителя нет (корневой раздел), идем в каталог
            loadSection(0);
            updateHorizontalNav(0);
        }
        
        // Очищаем стек, так как мы используем простую навигацию по родителям
        navigationStack = [];
    });
        
        // ===== ОБРАБОТКА КЛИКОВ ПО КАРТОЧКАМ =====
        attachHorizontalCardHandlers();
        
        // ===== ПОДСВЕТКА АКТИВНОГО РАЗДЕЛА =====
        highlightActiveSection(currentSectionId);
    });

// ===== ФУНКЦИЯ ЗАГРУЗКИ ТОВАРОВ =====
function loadSection(sectionId) {
    if (horizontalMode && sectionId !== currentSectionId && currentSectionId > 0) {
        navigationStack.push(currentSectionId);
    }
    
    currentSectionId = sectionId;
    
    const url = new URL(window.location);
    if (sectionId > 0) {
        url.searchParams.set('SECTION_ID', sectionId);
    } else {
        url.searchParams.delete('SECTION_ID');
    }
    window.history.pushState({}, '', url);
    
    document.getElementById('productsGrid').innerHTML = '<div class="text-center py-5"><i class="fas fa-spinner fa-spin fa-3x"></i></div>';
    
    fetch(`/catalog/ajax/get_products.php?section_id=${sectionId}`)
        .then(response => response.json())
        .then(data => {
            const title = sectionId > 0 ? sectionsData[sectionId]?.NAME : 'Каталог товаров';
            document.getElementById('catalogTitle').textContent = title;
            
            updateBreadcrumbs(sectionId);
            updateProductsGrid(data.products);
            highlightActiveSection(sectionId);
            
            if (horizontalMode) {
                updateHorizontalNav(sectionId);
            }
        })
        .catch(error => {
            console.error('Ошибка:', error);
            document.getElementById('productsGrid').innerHTML = '<div class="alert alert-danger">Ошибка загрузки товаров</div>';
        });
}

// ===== ФУНКЦИЯ ОБНОВЛЕНИЯ ГОРИЗОНТАЛЬНОГО МЕНЮ =====
function updateHorizontalNav(sectionId) {
    const navContent = document.getElementById('horizontalNavContent');
    const navTitle = document.getElementById('horizontalNavTitle');
    const backBtn = document.getElementById('backHorizontalNav');
    
    if (sectionId > 0) {
        navTitle.textContent = sectionsData[sectionId]?.NAME || 'Раздел';
        backBtn.style.opacity = '1';
        backBtn.style.visibility = 'visible';
        
        let html = '';
        const subsections = Object.values(sectionsData).filter(s => s.IBLOCK_SECTION_ID == sectionId);
        
        if (subsections.length > 0) {
            subsections.forEach(subsection => {
                const hasChildren = Object.values(sectionsData).some(s => s.IBLOCK_SECTION_ID == subsection.ID);
                html += `<div class="horizontal-section-card" data-section-id="${subsection.ID}" data-has-children="${hasChildren}">`;
                html += '<div class="horizontal-section-image">';
                html += subsection.IMAGE ? `<img src="${subsection.IMAGE}" alt="${subsection.NAME}">` : '<i class="fas fa-folder"></i>';
                html += '</div>';
                html += '<div class="horizontal-section-info">';
                html += `<div class="horizontal-section-name">${subsection.NAME}</div>`;
                if (subsection.ELEMENT_CNT > 0) {
                    html += `<div class="horizontal-section-count">${subsection.ELEMENT_CNT} товаров</div>`;
                }
                html += '</div></div>';
            });
        } else {
            html = '<div class="no-subsections"><i class="fas fa-folder-open"></i><p>В этом разделе нет подкатегорий</p></div>';
        }
        
        navContent.innerHTML = html;
    } else {
        navTitle.textContent = 'Категории';
        backBtn.style.opacity = '0';
        backBtn.style.visibility = 'hidden';
        
        let html = '';
        const mainSections = Object.values(sectionsData).filter(s => s.IBLOCK_SECTION_ID == 0);
        
        mainSections.forEach(section => {
            const hasChildren = Object.values(sectionsData).some(s => s.IBLOCK_SECTION_ID == section.ID);
            html += `<div class="horizontal-section-card" data-section-id="${section.ID}" data-has-children="${hasChildren}">`;
            html += '<div class="horizontal-section-image">';
            html += section.IMAGE ? `<img src="${section.IMAGE}" alt="${section.NAME}">` : '<i class="fas fa-dragon"></i>';
            html += '</div>';
            html += '<div class="horizontal-section-info">';
            html += `<div class="horizontal-section-name">${section.NAME}</div>`;
            if (section.ELEMENT_CNT > 0) {
                html += `<div class="horizontal-section-count">${section.ELEMENT_CNT} товаров</div>`;
            }
            html += '</div></div>';
        });
        
        navContent.innerHTML = html;
    }
    
    attachHorizontalCardHandlers();
}

// ===== ФУНКЦИЯ ПРИВЯЗКИ ОБРАБОТЧИКОВ =====
function attachHorizontalCardHandlers() {
    document.querySelectorAll('.horizontal-section-card').forEach(card => {
        card.addEventListener('click', function(e) {
            e.preventDefault();
            const sectionId = parseInt(this.dataset.sectionId);
            loadSection(sectionId);
            updateHorizontalNav(sectionId);
        });
    });
}

// ===== ФУНКЦИЯ ОБНОВЛЕНИЯ ХЛЕБНЫХ КРОШЕК =====
function updateBreadcrumbs(sectionId) {
    const breadcrumbs = document.getElementById('breadcrumbs');
    let html = '<a href="/catalog/">Главная</a>';
    
    if (sectionId > 0 && sectionsData[sectionId]) {
        const path = [];
        let tempId = sectionId;
        while (tempId > 0 && sectionsData[tempId]) {
            path.unshift(sectionsData[tempId]);
            tempId = sectionsData[tempId].IBLOCK_SECTION_ID;
        }
        
        path.forEach(section => {
            html += '<span class="separator">/</span>';
            if (section.ID == sectionId) {
                html += `<span class="active">${section.NAME}</span>`;
            } else {
                html += `<a href="#" onclick="loadSection(${section.ID}); return false;">${section.NAME}</a>`;
            }
        });
    }
    
    breadcrumbs.innerHTML = html;
}

// ===== ФУНКЦИЯ ОБНОВЛЕНИЯ ТОВАРОВ =====
function updateProductsGrid(products) {
    console.log('Получены товары:', products);
    
    const grid = document.getElementById('productsGrid');
    
    if (!products || products.length === 0) {
        grid.innerHTML = '<div class="alert alert-info">В этом разделе пока нет товаров</div>';
        return;
    }
    
    let html = '';
    products.forEach((product, index) => {
        // ОПРЕДЕЛЯЕМ САМЫЙ ГЛУБОКИЙ ПОДРАЗДЕЛ ДЛЯ BADGE
        let badgeText = 'Настольные игры';
        
        if (product.all_sections && product.all_sections.length > 0) {
            // Находим самый глубокий подраздел
            // Сортируем разделы по глубине
            const sectionsWithDepth = [];
            
            product.all_sections.forEach(section => {
                let depth = 1;
                let currentId = section.id;
                
                // Считаем глубину, поднимаясь по родителям
                while (true) {
                    const parentId = sectionsData[currentId]?.IBLOCK_SECTION_ID;
                    if (parentId > 0) {
                        depth++;
                        currentId = parentId;
                    } else {
                        break;
                    }
                }
                
                sectionsWithDepth.push({
                    id: section.id,
                    name: section.name,
                    depth: depth
                });
            });
            
            // Сортируем по глубине (от большего к меньшему)
            sectionsWithDepth.sort((a, b) => b.depth - a.depth);
            
            // Берем самый глубокий раздел
            if (sectionsWithDepth.length > 0) {
                badgeText = sectionsWithDepth[0].name;
            }
            
            // Для отладки
            if (product.name.includes('Каркассон')) {
                console.log('🏷️ Все разделы товара с глубиной:', sectionsWithDepth);
                console.log('🏷️ Самый глубокий:', badgeText);
            }
        }
        
        // Форматируем количество игроков
        let playersText = '2-4 игрока';
        if (product.players_count) {
            playersText = product.players_count.includes('-') 
                ? product.players_count + ' игроков'
                : product.players_count + ' игрок';
        }
        
        // Форматируем время игры
        let timeText = '30-60 мин';
        if (product.game_time) {
            timeText = product.game_time.includes('-')
                ? product.game_time + ' мин'
                : product.game_time + ' мин';
        }
        
        html += `
            <div class="product-card" onclick="window.location='${product.url}'">
                <div class="product-image">
                    <img src="${product.image}" alt="${product.name}">
                </div>
                <div class="product-badge">${badgeText}</div>
                <div class="product-info">
                    <h3 class="product-title">${product.name}</h3>
                    
                    <div class="product-details">
                        <div class="product-detail">
                            <i class="fas fa-users"></i>
                            <span>${playersText}</span>
                        </div>
                        <div class="product-detail">
                            <i class="fas fa-clock"></i>
                            <span>${timeText}</span>
                        </div>
                    </div>
                    
                    <div class="product-footer">
                        <div class="product-price">${product.price}</div>
                        <button class="product-cart" onclick="event.stopPropagation(); addToCart(${product.id})">
                            <i class="fas fa-shopping-cart"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;
    });
    
    grid.innerHTML = html;
}

// ===== ФУНКЦИЯ ПОДСВЕТКИ АКТИВНОГО РАЗДЕЛА =====
function highlightActiveSection(sectionId) {
    document.querySelectorAll('.tree-header').forEach(h => h.classList.remove('active'));
    document.querySelectorAll('.tree-item').forEach(i => i.classList.remove('has-active'));
    
    if (!sectionId) return;
    
    const activeItem = document.querySelector(`.tree-item[data-section-id="${sectionId}"]`);
    if (activeItem) {
        const activeHeader = activeItem.querySelector('.tree-header');
        if (activeHeader) activeHeader.classList.add('active');
        
        let parent = activeItem.parentElement?.closest('.tree-item');
        while (parent) {
            parent.classList.add('has-active');
            const parentToggle = parent.querySelector('.toggle-children');
            const parentChildren = parent.querySelector('.tree-children');
            if (parentToggle && parentChildren) {
                parentToggle.classList.add('expanded');
                parentChildren.classList.add('expanded');
            }
            parent = parent.parentElement?.closest('.tree-item');
        }
    }
}

function addToCart(productId, quantity = 1) {
        console.log('🟡 Добавляем товар:', productId, 'количество:', quantity);
        
        fetch('/ajax/add_to_cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=add&product_id=' + productId + '&quantity=' + quantity
        })
        .then(response => response.json())
        .then(data => {
            console.log('🟢 Ответ от сервера:', data);
            
            if (data.success) {
                showNotification('✅ Товар добавлен в корзину');
                
                // Просто вызываем функцию без параметров!
                if (window.updateCartCounter) {
                    window.updateCartCounter();  // ← НИКАКИХ true!
                }
            }
        });
    }

<?php
function renderTree($tree, $activeSectionId = 0, $path = array(), $level = 1) {
    if (empty($tree)) return '';

    $html = '';
    foreach ($tree as $sectionId => $section) {
        $hasChildren = !empty($section['CHILDREN']);
        $active = ($sectionId == $activeSectionId) ? ' active' : '';
        
        $inPath = false;
        foreach ($path as $pathSection) {
            if ($pathSection['ID'] == $sectionId) {
                $inPath = true;
                break;
            }
        }

        $iconHtml = $section['IMAGE'] 
            ? '<div class="section-icon"><img src="' . htmlspecialchars($section['IMAGE']) . '" alt=""></div>'
            : '<div class="section-icon"><i class="fas fa-folder"></i></div>';

        $expanded = ($inPath || $active) ? 'expanded' : '';
        
        $html .= '<div class="tree-item level-' . $level . '" data-section-id="' . $sectionId . '">';
        $html .= '<div class="tree-header' . $active . '">';
        $html .= $iconHtml;
        $html .= '<a href="#" onclick="loadSection(' . $sectionId . '); return false;" class="section-name">' . htmlspecialchars($section['NAME']) . '</a>';
        if ($hasChildren) {
            $html .= '<button class="toggle-children ' . $expanded . '"><i class="fas fa-chevron-right"></i></button>';
        }
        $html .= '</div>';
        if ($hasChildren) {
            $html .= '<div class="tree-children ' . $expanded . '">';
            $html .= renderTree($section['CHILDREN'], $activeSectionId, $path, $level + 1);
            $html .= '</div>';
        }
        $html .= '</div>';
    }
    return $html;
}

function hasChildren($sectionId, $allSections) {
    foreach ($allSections as $section) {
        if ($section['IBLOCK_SECTION_ID'] == $sectionId) return true;
    }
    return false;
}
?>
</script>

<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
?>