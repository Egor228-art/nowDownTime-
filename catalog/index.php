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
    "PROPERTY_PLAYERS_COUNT", "PROPERTY_GAME_TIME"
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
        'all_sections' => $productSections,
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

<head>
    <!-- Подключаем Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel='stylesheet' href='/catalog/catalog.css'>
</head>

<div class="catalog-page">
    <!-- Боковая панель (левая) -->
    <div class="catalog-sidebar left-sidebar" id="catalogSidebar">
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

    <!-- Правая боковая панель с фильтрами (копия левой) -->
    <div class="catalog-sidebar right-sidebar" id="filterSidebar">
        <div class="sidebar-content" style="height: 100%; display: flex; flex-direction: column;">
            <div class="sidebar-header">
                <span class="sidebar-title">Фильтры</span>
                <div class="sidebar-filter-icon">
                    <i class="fas fa-sliders-h"></i>
                </div>
            </div>

            <div class="filter-sidebar-content">
                <div class="filter-groups-container">
                    <!-- Фильтр по цене -->
                    <div class="filter-group">
                        <div class="filter-group-content price-filter-content">
                            <div class="price-range">
                                <div class="price-inputs">
                                    <div class="input-wrapper">
                                        <input type="number" id="sidebarMinPrice" placeholder="От" value="0">
                                    </div>
                                    <span>—</span>
                                    <div class="input-wrapper">
                                        <input type="number" id="sidebarMaxPrice" placeholder="До" value="10000">
                                    </div>
                                </div>
                                <div class="price-slider-container">
                                    <div class="price-slider-track" id="sidebarPriceSliderTrack"></div>
                                    <input type="range" id="sidebarPriceMinRange" class="price-slider" min="0" max="10000" step="10" value="0">
                                    <input type="range" id="sidebarPriceMaxRange" class="price-slider" min="0" max="10000" step="10" value="10000">
                                </div>
                                <div class="price-values">
                                    <span class="ruble-icon"><i class="fas fa-ruble-sign"></i></span>
                                    <span id="sidebarPriceMinValue">0</span> — <span id="sidebarPriceMaxValue">10000</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Фильтр по игрокам -->
                    <div class="filter-group">
                        <div class="filter-group-title">
                            <span class="chevron-icon"><i class="fas fa-chevron-up"></i></span>
                            <span class="title-text">
                                <span>Количество игроков</span>
                                <span class="title-icon"><i class="fas fa-users"></i></span>
                            </span>
                        </div>
                        <div class="filter-group-content">
                            <div class="filter-options">
                                <label class="filter-option">
                                    <input type="checkbox" class="filter-checkbox" data-filter="players" value="1-1">
                                    <span>1 игрок</span>
                                </label>
                                <label class="filter-option">
                                    <input type="checkbox" class="filter-checkbox" data-filter="players" value="2-2">
                                    <span>2 игрока</span>
                                </label>
                                <label class="filter-option">
                                    <input type="checkbox" class="filter-checkbox" data-filter="players" value="3-4">
                                    <span>3-4 игрока</span>
                                </label>
                                <label class="filter-option">
                                    <input type="checkbox" class="filter-checkbox" data-filter="players" value="5-6">
                                    <span>5-6 игроков</span>
                                </label>
                                <label class="filter-option">
                                    <input type="checkbox" class="filter-checkbox" data-filter="players" value="6-999">
                                    <span>6+ игроков</span>
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Фильтр по времени -->
                    <div class="filter-group">
                        <div class="filter-group-title">
                            <span class="chevron-icon"><i class="fas fa-chevron-up"></i></span>
                            <span class="title-text">
                                <span>Время игры</span>
                                <span class="title-icon"><i class="fas fa-clock"></i></span>
                            </span>
                        </div>
                        <div class="filter-group-content">
                            <div class="filter-options">
                                <label class="filter-option">
                                    <input type="checkbox" class="filter-checkbox" data-filter="time" value="0-30">
                                    <span>до 30 мин</span>
                                </label>
                                <label class="filter-option">
                                    <input type="checkbox" class="filter-checkbox" data-filter="time" value="30-60">
                                    <span>30-60 мин</span>
                                </label>
                                <label class="filter-option">
                                    <input type="checkbox" class="filter-checkbox" data-filter="time" value="60-120">
                                    <span>60-120 мин</span>
                                </label>
                                <label class="filter-option">
                                    <input type="checkbox" class="filter-checkbox" data-filter="time" value="120-999">
                                    <span>более 120 мин</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Кнопки применения фильтров -->
                <div class="filter-sidebar-footer">
                    <button class="filter-btn clear-btn" id="sidebarClearFilters">
                        <i class="fas fa-times"></i>
                        Сбросить
                    </button>
                    <button class="filter-btn apply-btn" id="sidebarApplyFilters">
                        <i class="fas fa-check"></i>
                        Применить
                    </button>
                </div>
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
                <button class="filter-toggle-btn" id="filterToggle">
                    <i class="fas fa-sliders-h"></i>
                    <span class="filter-text">Фильтры</span>
                    <span class="filter-count" id="filterCount"></span>
                </button>
            </div>

            <div class="products-grid" id="productsGrid">
                <? if (empty($products)): ?>
                    <div class="alert alert-info">В этом разделе пока нет товаров</div>
                <? else: ?>
                    <? foreach ($products as $product): ?>
                        <div class="product-card" 
                            data-price="<?= $product['price_value'] ?>"
                            data-players="<?= htmlspecialchars($product['players_count']) ?>"
                            data-time="<?= htmlspecialchars($product['game_time']) ?>"
                            onclick="window.location='<?= $product['url'] ?>'">
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
                                    <button class="product-cart" onclick="event.stopPropagation(); addToCart(this, <?= $product['id'] ?>)">
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

<!-- Модальное окно фильтров (оставляем для мобильных) -->
<div class="filter-modal" id="filterModal">
    <div class="filter-modal-overlay" id="filterModalOverlay"></div>
    <div class="filter-modal-content">
        <div class="filter-modal-header">
            <h3>Фильтры</h3>
            <button class="filter-modal-close" id="filterModalClose">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div class="filter-modal-body">
            <!-- Фильтр по цене -->
                <div> Цена, ₽ </div>
                <div class="filter-group-content">
                    <div class="price-range">
                        <div class="price-inputs">
                            <input type="number" id="modalMinPrice" placeholder="От" value="0">
                            <span>—</span>
                            <input type="number" id="modalMaxPrice" placeholder="До" value="10000">
                        </div>
                        <div class="price-slider-container">
                            <div class="price-slider-track" id="modalPriceSliderTrack"></div>
                            <input type="range" id="modalPriceMinRange" class="price-slider" min="0" max="10000" step="10" value="0">
                            <input type="range" id="modalPriceMaxRange" class="price-slider" min="0" max="10000" step="10" value="10000">
                        </div>
                        <div class="price-values">
                            <span id="modalPriceMinValue">0</span> ₽ — <span id="modalPriceMaxValue">10000</span> ₽
                        </div>
                    </div>
                </div>
            
            <!-- Фильтр по игрокам -->
            <div class="filter-group">
                <div class="filter-group-title">
                    Количество игроков
                    <i class="fas fa-chevron-down"></i>
                </div>
                <div class="filter-group-content">
                    <div class="filter-options">
                        <label class="filter-option">
                            <input type="checkbox" class="filter-checkbox" data-filter="players" value="1-1">
                            <span>1 игрок</span>
                        </label>
                        <label class="filter-option">
                            <input type="checkbox" class="filter-checkbox" data-filter="players" value="2-2">
                            <span>2 игрока</span>
                        </label>
                        <label class="filter-option">
                            <input type="checkbox" class="filter-checkbox" data-filter="players" value="3-4">
                            <span>3-4 игрока</span>
                        </label>
                        <label class="filter-option">
                            <input type="checkbox" class="filter-checkbox" data-filter="players" value="5-6">
                            <span>5-6 игроков</span>
                        </label>
                        <label class="filter-option">
                            <input type="checkbox" class="filter-checkbox" data-filter="players" value="6-999">
                            <span>6+ игроков</span>
                        </label>
                    </div>
                </div>
            </div>
            
            <!-- Фильтр по времени -->
            <div class="filter-group">
                <div class="filter-group-title">
                    Время игры
                    <i class="fas fa-chevron-down"></i>
                </div>
                <div class="filter-group-content">
                    <div class="filter-options">
                        <label class="filter-option">
                            <input type="checkbox" class="filter-checkbox" data-filter="time" value="0-30">
                            <span>до 30 мин</span>
                        </label>
                        <label class="filter-option">
                            <input type="checkbox" class="filter-checkbox" data-filter="time" value="30-60">
                            <span>30-60 мин</span>
                        </label>
                        <label class="filter-option">
                            <input type="checkbox" class="filter-checkbox" data-filter="time" value="60-120">
                            <span>60-120 мин</span>
                        </label>
                        <label class="filter-option">
                            <input type="checkbox" class="filter-checkbox" data-filter="time" value="120-999">
                            <span>более 120 мин</span>
                        </label>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="filter-modal-footer">
            <button class="filter-btn clear-btn" id="modalClearFilters">
                <i class="fas fa-times"></i>
                Сбросить
            </button>
            <button class="filter-btn apply-btn" id="modalApplyFilters">
                <i class="fas fa-check"></i>
                Применить
            </button>
        </div>
    </div>
</div>

<?if (!$USER->IsAuthorized()):?>
    <style>
        body {
            margin-top: 0 !important;
            padding-top: 160px !important;
        }
    </style>
    <?endif?>

    <?if ($USER->IsAuthorized()):?>
    <style>
        body {
            margin-top: 0 !important;
            padding-top: 120px !important;
        }
    </style>
    <?endif?>

<script>
    // Глобальные данные
    const sectionsData = <?= json_encode($arAllSections) ?>;
    let currentSectionId = <?= $sectionId ?: 0 ?>;
    let navigationStack = [<?= $sectionId ?: 0 ?>].filter(id => id > 0);
    let horizontalMode = false;
    let activeFilters = {
        price: { min: 0, max: 10000 },
        players: [],
        time: []
    };

    document.addEventListener('DOMContentLoaded', function() {
        const sidebar = document.getElementById('catalogSidebar');
        const filterSidebar = document.getElementById('filterSidebar');
        const modeToggle = document.getElementById('modeToggle');
        const modeToggleHorizontal = document.getElementById('modeToggleHorizontal');
        const horizontalNav = document.getElementById('horizontalNav');
        const backBtn = document.getElementById('backHorizontalNav');
        const filterToggle = document.getElementById('filterToggle');
        const urlParams = new URLSearchParams(window.location.search);
        const timeFilter = urlParams.get('filter_time');

        if (timeFilter === '0-30') {
            // Ждем загрузки всех элементов
            setTimeout(function() {
                // Находим и активируем чекбокс "до 30 мин" в правой панели
                const timeCheckbox = document.querySelector('.catalog-sidebar.right-sidebar .filter-checkbox[data-filter="time"][value="0-30"]');
                if (timeCheckbox) {
                    timeCheckbox.checked = true;
                    
                    // Также активируем в модальном окне (для мобильных)
                    const modalCheckbox = document.querySelector('.filter-modal .filter-checkbox[data-filter="time"][value="0-30"]');
                    if (modalCheckbox) {
                        modalCheckbox.checked = true;
                    }
                    
                    // Применяем фильтры
                    if (typeof applySidebarFilters === 'function') {
                        applySidebarFilters();
                    }
                }
            }, 500);
        }
        
        // Раскрытие подразделов
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
        
        // Обработка кликов по разделам
        document.querySelectorAll('.tree-header').forEach(header => {
            header.addEventListener('click', function(e) {
                if (e.target.closest('.toggle-children')) return;
                e.preventDefault();
                
                const treeItem = this.closest('.tree-item');
                if (treeItem) {
                    const sectionId = parseInt(treeItem.dataset.sectionId);
                    if (sectionId) {
                        navigationStack = [sectionId];
                        loadSection(sectionId);
                    }
                }
            });
        });
        
        // Переключение меню
        modeToggle.addEventListener('click', function() {
            // В горизонтальном режиме скрываем обе боковые панели
            sidebar.style.display = 'none';
            filterSidebar.style.display = 'none';
            
            // Показываем горизонтальное меню
            horizontalNav.style.display = 'block';
            horizontalNav.classList.remove('hiding-complete', 'collapsed');
            isNavCollapsed = false;
            
            // Показываем кнопку фильтров
            if (filterToggle) {
                filterToggle.style.display = 'inline-flex';
            }
            
            // Новая анимация появления сверху вниз
            horizontalNav.style.transform = 'translateY(-100%)';
            horizontalNav.style.opacity = '0';
            
            setTimeout(() => {
                horizontalNav.classList.add('active');
                horizontalNav.style.transform = '';
                horizontalNav.style.opacity = '';
            }, 10);
            
            horizontalMode = true;
            updateHorizontalNav(currentSectionId);
        });

        modeToggleHorizontal.addEventListener('click', function() {
            hideHorizontalNavComplete();
        });

        function hideHorizontalNavComplete() {
            // Убираем класс active и запускаем анимацию скрытия справа налево
            horizontalNav.classList.remove('active');
            horizontalNav.classList.add('hiding-complete');
            setTimeout(() => {
                horizontalNav.classList.remove('hiding-complete');
                horizontalNav.style.display = 'none';
                
                // Возвращаем обе боковые панели
                sidebar.style.display = 'block';
                filterSidebar.style.display = 'block';
                
                // Скрываем кнопку фильтров
                if (filterToggle) {
                    filterToggle.style.display = 'none';
                }
                
                horizontalMode = false;
                // Сбрасываем состояние сворачивания при скрытии меню
                isNavCollapsed = false;
            }, 400); // Время должно совпадать с длительностью анимации
        }

        backBtn.addEventListener('click', function() {
            if (currentSectionId === 0) return;
            
            const parentId = sectionsData[currentSectionId]?.IBLOCK_SECTION_ID || 0;
            
            if (parentId > 0) {
                loadSection(parentId);
                updateHorizontalNav(parentId);
            } else {
                loadSection(0);
                updateHorizontalNav(0);
            }
            navigationStack = [];
        });
            
        attachHorizontalCardHandlers();
        highlightActiveSection(currentSectionId);
        
        // Инициализация фильтров в правой панели
        initSidebarFilters();
        initFilterModal();
        
        // По умолчанию на десктопе кнопка фильтров скрыта
        if (window.innerWidth >= 769) {
            filterToggle.style.display = 'none';
        }
    });

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
                
                // Обновляем диапазон цен в фильтрах после загрузки товаров
                updatePriceRangeFromProducts();
                updateSidebarPriceRange();
            })
            .catch(error => {
                console.error('Ошибка:', error);
                document.getElementById('productsGrid').innerHTML = '<div class="alert alert-danger">Ошибка загрузки товаров</div>';
            });
    }

    // Добавьте эти переменные в начало скрипта
    let isNavCollapsed = false;

    // Замените функцию hideHorizontalNavComplete на эту:
    function hideHorizontalNavComplete() {
        horizontalNav.classList.remove('active');
        horizontalNav.classList.add('hiding-complete');
        setTimeout(() => {
            horizontalNav.classList.remove('hiding-complete');
            horizontalNav.style.display = 'none';
            sidebar.style.display = 'block';
            document.getElementById('filterSidebar').style.display = 'block';
            horizontalMode = false;
            // Сбрасываем состояние сворачивания при скрытии меню
            isNavCollapsed = false;
        }, 400);
    }

    // Добавьте новую функцию для сворачивания/разворачивания
    function toggleNavCollapse() {
        const horizontalNav = document.getElementById('horizontalNav');
        const collapseIcon = document.getElementById('collapseIcon');
        
        isNavCollapsed = !isNavCollapsed;
        
        if (isNavCollapsed) {
            horizontalNav.classList.add('collapsed');
            if (collapseIcon) {
                collapseIcon.className = 'fas fa-chevron-down';
            }
        } else {
            horizontalNav.classList.remove('collapsed');
            if (collapseIcon) {
                collapseIcon.className = 'fas fa-chevron-up';
            }
        }
    }

    // Функция для обновления состояния кнопки назад
    function updateBackButton(sectionId) {
        const backBtn = document.getElementById('backHorizontalNav');
        if (sectionId > 0) {
            backBtn.style.opacity = '1';
            backBtn.style.visibility = 'visible';
        } else {
            backBtn.style.opacity = '0';
            backBtn.style.visibility = 'hidden';
        }
    }

    // Переопределяем updateHorizontalNav с поддержкой сворачивания
    function updateHorizontalNav(sectionId) {
        const navContent = document.getElementById('horizontalNavContent');
        const navTitle = document.getElementById('horizontalNavTitle');
        
        if (sectionId > 0) {
            navTitle.textContent = sectionsData[sectionId]?.NAME || 'Раздел';
            updateBackButton(sectionId);
            
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
            updateBackButton(0);
            
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
        
        // Сбрасываем состояние сворачивания при загрузке нового раздела
        if (isNavCollapsed) {
            isNavCollapsed = false;
            document.getElementById('horizontalNav').classList.remove('collapsed');
            const collapseIcon = document.getElementById('collapseIcon');
            if (collapseIcon) {
                collapseIcon.className = 'fas fa-chevron-up';
            }
        }
        
        attachHorizontalCardHandlers();
    }

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

    function updateProductsGrid(products) {
        console.log('Получены товары:', products);
        
        const grid = document.getElementById('productsGrid');
        
        if (!products || products.length === 0) {
            grid.innerHTML = '<div class="alert alert-info">В этом разделе пока нет товаров</div>';
            return;
        }
        
        let html = '';
        products.forEach(product => {
            let badgeText = 'Настольные игры';
            
            if (product.all_sections && product.all_sections.length > 0) {
                const sectionsWithDepth = [];
                
                product.all_sections.forEach(section => {
                    let depth = 1;
                    let currentId = section.id;
                    
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
                
                sectionsWithDepth.sort((a, b) => b.depth - a.depth);
                
                if (sectionsWithDepth.length > 0) {
                    badgeText = sectionsWithDepth[0].name;
                }
            }
            
            let playersText = '2-4 игрока';
            if (product.players_count) {
                playersText = product.players_count.includes('-') 
                    ? product.players_count + ' игроков'
                    : product.players_count + ' игрок';
            }
            
            let timeText = '30-60 мин';
            if (product.game_time) {
                timeText = product.game_time.includes('-')
                    ? product.game_time + ' мин'
                    : product.game_time + ' мин';
            }
            
            html += `
                <div class="product-card" 
                    data-price="${product.price_value}"
                    data-players="${product.players_count}"
                    data-time="${product.game_time}"
                    onclick="window.location='${product.url}'">
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
                            <button class="product-cart" onclick="event.stopPropagation(); addToCart(this, <?= $product['id'] ?>)">
                                <i class="fas fa-shopping-cart"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `;
        });
        
        grid.innerHTML = html;
        updatePriceRangeFromProducts();
        updateSidebarPriceRange();
    }

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

window.addToCart = function(button, productId, quantity = 1) {
    console.log('🟡 Добавляем товар:', productId);
    
    // Получаем данные из карточки
    const productCard = button.closest('.product-card');
    let productName = '';
    let productPrice = 0;
    
    if (productCard) {
        const titleEl = productCard.querySelector('.product-title');
        if (titleEl) productName = titleEl.textContent.trim();
        
        const priceEl = productCard.querySelector('.product-price');
        if (priceEl) {
            const priceText = priceEl.textContent.trim();
            productPrice = parseFloat(priceText.replace(/[^\d]/g, '')) || 0;
        }
    }
    
    fetch('/ajax/add_to_cart.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `action=add&product_id=${productId}&quantity=${quantity}&name=${encodeURIComponent(productName)}&price=${productPrice}`
    })
    .then(response => response.json())
    .then(data => {
        console.log('🟢 Ответ:', data);
        
        if (data.success) {
            showNotification(`Товар "${productName}" добавлен в корзину!`, 'success');
            
            // Обновляем счетчик в шапке
            if (typeof updateCartCounter === 'function') {
                updateCartCounter();
            }
        } else {
            showNotification('Ошибка: ' + (data.message || 'неизвестная'), 'error');
        }
    })
    .catch(error => {
        console.error('Ошибка:', error);
        showNotification('Ошибка соединения', 'error');
    });
};

// Удаление товара
function removeItem(item) {
    if (item.pendingRemoval) return;
    item.pendingRemoval = true;
    
    fetch('/ajax/add_to_cart.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `action=remove&product_id=${item.productId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Удаляем визуально
            if (item.element) item.element.remove();
            
            totalPrice -= item.price;
            updateBasketStats();
            updateDiscount(totalPrice);
            
            // Обновляем скроллер
            const remaining = items.filter(i => i.productId == item.productId && !i.pendingRemoval).length - 1;
            if (remaining <= 0) {
                const index = cartProducts.findIndex(p => p.id == item.productId);
                if (index !== -1) cartProducts.splice(index, 1);
            }
            renderScrollItems();
        } else {
            item.pendingRemoval = false;
            showNotification('Ошибка удаления', 'error');
        }
    });
}

// Загрузка корзины при старте
function loadCart() {
    fetch('/ajax/add_to_cart.php?action=get_full')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.items) {
                // Очищаем текущую корзину
                document.querySelectorAll('.product-item').forEach(el => el.remove());
                items = [];
                cartProducts = [];
                totalPrice = 0;
                
                data.items.forEach(item => {
                    if (item.NAME && item.PRICE > 0) {
                        cartProducts.push({
                            id: item.PRODUCT_ID,
                            name: item.NAME,
                            price: item.PRICE,
                            imageUrl: null
                        });
                        
                        for (let i = 0; i < item.QUANTITY; i++) {
                            createProduct({
                                id: item.PRODUCT_ID,
                                name: item.NAME,
                                price: item.PRICE,
                                imageUrl: null
                            });
                        }
                    }
                });
                
                renderScrollItems();
                updateBasketStats();
            }
        });
}

// Функция показа уведомлений
function showNotification(message, type = 'success') {
    console.log('Уведомление:', message, type);
    
    // Удаляем старые уведомления
    const oldNotif = document.querySelector('.temp-notification');
    if (oldNotif) oldNotif.remove();
    
    const notif = document.createElement('div');
    notif.className = 'temp-notification';
    notif.innerHTML = `
        <div style="
            position: fixed;
            top: 20px;
            right: 20px;
            background: ${type === 'success' ? '#27ae60' : '#e74c3c'};
            color: white;
            padding: 12px 20px;
            border-radius: 10px;
            z-index: 10001;
            animation: slideInRight 0.3s ease;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            font-size: 14px;
            font-family: sans-serif;
        ">
            ${type === 'success' ? '✅' : '❌'} ${message}
        </div>
    `;
    document.body.appendChild(notif);
    
    setTimeout(() => {
        if (notif) {
            notif.style.opacity = '0';
            notif.style.transition = 'opacity 0.3s';
            setTimeout(() => notif.remove(), 300);
        }
    }, 3000);
}

// Добавляем анимацию, если её нет
if (!document.querySelector('#notification-styles')) {
    const style = document.createElement('style');
    style.id = 'notification-styles';
    style.textContent = `
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
    `;
    document.head.appendChild(style);
}

// Функция обновления счетчика корзины в шапке
function updateCartCounter() {
    fetch('/ajax/add_to_cart.php?action=get_count')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.count > 0) {
                const counter = document.getElementById('cart-counter');
                if (counter) {
                    counter.textContent = data.count;
                    counter.style.display = 'inline-block';
                }
            }
        })
        .catch(error => console.error('Ошибка обновления счетчика:', error));
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

    // ===== ФИЛЬТРЫ ДЛЯ ПРАВОЙ ПАНЕЛИ =====

    // Обновление диапазона цен в правой панели
    function updateSidebarPriceRange() {
        let minRange = document.getElementById('sidebarPriceMinRange');
        let maxRange = document.getElementById('sidebarPriceMaxRange');
        
        if (!minRange || !maxRange) return;
        
        let maxPrice = 0;
        document.querySelectorAll('.product-card').forEach(card => {
            let price = parseFloat(card.dataset.price) || 0;
            if (price > maxPrice) maxPrice = price;
        });
        
        maxPrice = Math.ceil(maxPrice / 100) * 100;
        if (maxPrice < 1000) maxPrice = 1000;
        
        minRange.max = maxPrice;
        maxRange.max = maxPrice;
        maxRange.value = activeFilters.price.max;
        minRange.value = activeFilters.price.min;
        
        document.getElementById('sidebarMaxPrice').value = maxPrice;
        document.getElementById('sidebarMaxPrice').max = maxPrice;
        document.getElementById('sidebarPriceMaxValue').textContent = maxPrice;
        
        updateSidebarPriceSliderTrack();
    }

    // Обновление трека слайдера в правой панели
    function updateSidebarPriceSliderTrack() {
        let minRange = document.getElementById('sidebarPriceMinRange');
        let maxRange = document.getElementById('sidebarPriceMaxRange');
        let track = document.getElementById('sidebarPriceSliderTrack');
        
        if (!minRange || !maxRange || !track) return;
        
        let min = parseInt(minRange.value);
        let max = parseInt(maxRange.value);
        let total = parseInt(minRange.max);
        
        let minPercent = (min / total) * 100;
        let maxPercent = (max / total) * 100;
        
        track.style.setProperty('--min-percent', minPercent + '%');
        track.style.setProperty('--max-percent', maxPercent + '%');
    }

    // Инициализация фильтров в правой панели
    function initSidebarFilters() {
        // Инициализация сворачивания групп фильтров
        document.querySelectorAll('.catalog-sidebar.right-sidebar .filter-group-title').forEach(header => {
            let content = header.nextElementSibling;
            header.classList.add('collapsed');
            content.classList.add('collapsed');
            content.style.maxHeight = '0';
            
            header.addEventListener('click', function() {
                this.classList.toggle('collapsed');
                content.classList.toggle('collapsed');
                
                if (content.classList.contains('collapsed')) {
                    content.style.maxHeight = '0';
                } else {
                    content.style.maxHeight = content.scrollHeight + 'px';
                }
            });
        });
        
        // Инициализация слайдеров цены
        let minRange = document.getElementById('sidebarPriceMinRange');
        let maxRange = document.getElementById('sidebarPriceMaxRange');
        let minInput = document.getElementById('sidebarMinPrice');
        let maxInput = document.getElementById('sidebarMaxPrice');
        let minVal = document.getElementById('sidebarPriceMinValue');
        let maxVal = document.getElementById('sidebarPriceMaxValue');
        
        if (minRange && maxRange) {
            let maxPrice = findMaxPrice();
            
            minRange.max = maxPrice;
            maxRange.max = maxPrice;
            minRange.value = activeFilters.price.min;
            maxRange.value = activeFilters.price.max;
            
            if (minInput) minInput.value = activeFilters.price.min;
            if (maxInput) {
                maxInput.value = activeFilters.price.max;
                maxInput.max = maxPrice;
            }
            if (minVal) minVal.textContent = activeFilters.price.min;
            if (maxVal) maxVal.textContent = activeFilters.price.max;
            
            updateSidebarPriceSliderTrack();
            
            minRange.addEventListener('input', function() {
                let min = parseInt(this.value);
                let max = parseInt(maxRange.value);
                if (min > max) this.value = max;
                if (minInput) minInput.value = this.value;
                if (minVal) minVal.textContent = this.value;
                updateSidebarPriceSliderTrack();
            });
            
            maxRange.addEventListener('input', function() {
                let max = parseInt(this.value);
                let min = parseInt(minRange.value);
                if (max < min) this.value = min;
                if (maxInput) maxInput.value = this.value;
                if (maxVal) maxVal.textContent = this.value;
                updateSidebarPriceSliderTrack();
            });
            
            if (minInput) {
                minInput.addEventListener('change', function() {
                    let val = parseInt(this.value) || 0;
                    val = Math.min(val, parseInt(maxRange.value));
                    val = Math.max(val, 0);
                    this.value = val;
                    minRange.value = val;
                    if (minVal) minVal.textContent = val;
                    updateSidebarPriceSliderTrack();
                });
            }
            
            if (maxInput) {
                maxInput.addEventListener('change', function() {
                    let val = parseInt(this.value) || 0;
                    val = Math.max(val, parseInt(minRange.value));
                    val = Math.min(val, parseInt(maxInput.max));
                    this.value = val;
                    maxRange.value = val;
                    if (maxVal) maxVal.textContent = val;
                    updateSidebarPriceSliderTrack();
                });
            }
        }
        
        // Обработчики для чекбоксов
        document.querySelectorAll('.catalog-sidebar.right-sidebar .filter-checkbox').forEach(cb => {
            cb.addEventListener('change', function() {
                // Не применяем фильтры сразу, ждем кнопку "Применить"
            });
        });
        
        // Кнопка "Применить" в правой панели
        document.getElementById('sidebarApplyFilters')?.addEventListener('click', function(e) {
            e.preventDefault();
            applySidebarFilters();
        });
        
        // Кнопка "Сбросить" в правой панели
        document.getElementById('sidebarClearFilters')?.addEventListener('click', function(e) {
            e.preventDefault();
            resetSidebarFilters();
        });
    }

    // Применение фильтров из правой панели
    function applySidebarFilters() {
        activeFilters.price = {
            min: parseInt(document.getElementById('sidebarPriceMinRange')?.value || 0),
            max: parseInt(document.getElementById('sidebarPriceMaxRange')?.value || findMaxPrice())
        };
        
        activeFilters.players = [];
        activeFilters.time = [];
        
        document.querySelectorAll('.catalog-sidebar.right-sidebar .filter-checkbox:checked').forEach(cb => {
            let type = cb.dataset.filter;
            let value = cb.value;
            
            if (type === 'players') {
                activeFilters.players.push(value);
            } else if (type === 'time') {
                activeFilters.time.push(value);
            }
        });
        
        filterProducts();
        
        // Синхронизируем с модальными фильтрами
        syncFiltersWithModal();
        
        // Обновляем счетчик фильтров
        updateFilterCount();
    }

    // Сброс фильтров в правой панели
    function resetSidebarFilters() {
        document.querySelectorAll('.catalog-sidebar.right-sidebar .filter-checkbox').forEach(cb => {
            cb.checked = false;
        });
        
        let minRange = document.getElementById('sidebarPriceMinRange');
        let maxRange = document.getElementById('sidebarPriceMaxRange');
        
        if (minRange && maxRange) {
            let max = maxRange.max;
            minRange.value = 0;
            maxRange.value = max;
            
            document.getElementById('sidebarMinPrice').value = 0;
            document.getElementById('sidebarMaxPrice').value = max;
            document.getElementById('sidebarPriceMinValue').textContent = '0';
            document.getElementById('sidebarPriceMaxValue').textContent = max;
        }
        
        activeFilters = {
            price: { min: 0, max: findMaxPrice() },
            players: [],
            time: []
        };
        
        document.querySelectorAll('.product-card').forEach(card => {
            card.style.display = '';
        });
        
        // Синхронизируем с модальными фильтрами
        syncFiltersWithModal();
        
        // Обновляем счетчик фильтров
        updateFilterCount();
    }

    // Синхронизация фильтров с модальным окном
    function syncFiltersWithModal() {
        // Синхронизируем цену
        let modalMinRange = document.getElementById('modalPriceMinRange');
        let modalMaxRange = document.getElementById('modalPriceMaxRange');
        let sidebarMinRange = document.getElementById('sidebarPriceMinRange');
        let sidebarMaxRange = document.getElementById('sidebarPriceMaxRange');
        
        if (modalMinRange && sidebarMinRange) {
            modalMinRange.value = sidebarMinRange.value;
            modalMinRange.max = sidebarMinRange.max;
        }
        
        if (modalMaxRange && sidebarMaxRange) {
            modalMaxRange.value = sidebarMaxRange.value;
            modalMaxRange.max = sidebarMaxRange.max;
        }
        
        document.getElementById('modalMinPrice').value = document.getElementById('sidebarMinPrice').value;
        document.getElementById('modalMaxPrice').value = document.getElementById('sidebarMaxPrice').value;
        document.getElementById('modalPriceMinValue').textContent = document.getElementById('sidebarPriceMinValue').textContent;
        document.getElementById('modalPriceMaxValue').textContent = document.getElementById('sidebarPriceMaxValue').textContent;
        
        // Синхронизируем чекбоксы
        document.querySelectorAll('.filter-modal .filter-checkbox').forEach(modalCb => {
            let sidebarCb = document.querySelector(`.catalog-sidebar.right-sidebar .filter-checkbox[data-filter="${modalCb.dataset.filter}"][value="${modalCb.value}"]`);
            if (sidebarCb) {
                modalCb.checked = sidebarCb.checked;
            }
        });
        
        // Обновляем трек слайдера в модальном окне
        updateModalPriceSliderTrack();
    }

    // Обновление счетчика фильтров
    function updateFilterCount() {
        let count = activeFilters.players.length + activeFilters.time.length;
        let filterCount = document.getElementById('filterCount');
        let filterBtn = document.getElementById('filterToggle');
        
        if (filterCount) {
            if (count > 0) {
                filterCount.textContent = count;
                filterCount.style.display = 'inline-block';
                filterBtn.classList.add('has-filters');
            } else {
                filterCount.style.display = 'none';
                filterBtn.classList.remove('has-filters');
            }
        }
    }

    // Обновление трека слайдера в модальном окне
    function updateModalPriceSliderTrack() {
        let minRange = document.getElementById('modalPriceMinRange');
        let maxRange = document.getElementById('modalPriceMaxRange');
        let track = document.getElementById('modalPriceSliderTrack');
        
        if (!minRange || !maxRange || !track) return;
        
        let min = parseInt(minRange.value);
        let max = parseInt(maxRange.value);
        let total = parseInt(minRange.max);
        
        let minPercent = (min / total) * 100;
        let maxPercent = (max / total) * 100;
        
        track.style.background = `linear-gradient(to right, 
            #e0e0e0 0%, 
            #e0e0e0 ${minPercent}%, 
            var(--Gold) ${minPercent}%, 
            var(--GoldFake) ${maxPercent}%, 
            #e0e0e0 ${maxPercent}%)`;
    }

    // ===== ФИЛЬТРЫ ДЛЯ МОДАЛЬНОГО ОКНА =====

    function parseRangeValue(value) {
        if (!value) return { min: 0, max: 999 };
        
        let str = value.toString();
        let numbers = str.match(/\d+/g);
        
        if (!numbers) return { min: 0, max: 999 };
        
        let nums = numbers.map(Number);
        
        if (str.includes('-')) {
            return { min: Math.min(...nums), max: Math.max(...nums) };
        }
        
        return { min: nums[0], max: nums[0] };
    }

    // Обновление диапазона цен
    function updatePriceRangeFromProducts() {
        let minRange = document.getElementById('priceMinRange');
        let maxRange = document.getElementById('priceMaxRange');
        
        if (!minRange || !maxRange) return;
        
        let maxPrice = 0;
        document.querySelectorAll('.product-card').forEach(card => {
            let price = parseFloat(card.dataset.price) || 0;
            if (price > maxPrice) maxPrice = price;
        });
        
        maxPrice = Math.ceil(maxPrice / 100) * 100;
        if (maxPrice < 1000) maxPrice = 1000;
        
        minRange.max = maxPrice;
        maxRange.max = maxPrice;
        maxRange.value = activeFilters.price.max;
        
        document.getElementById('maxPrice').value = maxPrice;
        document.getElementById('priceMaxValue').textContent = maxPrice;
    }

    // Фильтрация товаров
    function filterProducts() {
        let products = document.querySelectorAll('.product-card');
        let visibleCount = 0;
        
        products.forEach(card => {
            let show = true;
            
            let price = parseFloat(card.dataset.price) || 0;
            if (price < activeFilters.price.min || price > activeFilters.price.max) {
                show = false;
            }
            
            if (show && activeFilters.players.length > 0) {
                let players = parseRangeValue(card.dataset.players);
                let match = false;
                
                activeFilters.players.forEach(filter => {
                    let filterRange = parseRangeValue(filter);
                    if (players.min <= filterRange.max && players.max >= filterRange.min) {
                        match = true;
                    }
                });
                
                if (!match) show = false;
            }
            
            if (show && activeFilters.time.length > 0) {
                let time = parseRangeValue(card.dataset.time);
                let match = false;
                
                activeFilters.time.forEach(filter => {
                    let filterRange = parseRangeValue(filter);
                    if (time.min <= filterRange.max && time.max >= filterRange.min) {
                        match = true;
                    }
                });
                
                if (!match) show = false;
            }
            
            if (show) {
                card.style.display = '';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });
        
        return visibleCount;
    }

    // Поиск максимальной цены
    function findMaxPrice() {
        let max = 0;
        document.querySelectorAll('.product-card').forEach(card => {
            let price = parseFloat(card.dataset.price) || 0;
            if (price > max) max = price;
        });
        max = Math.ceil(max / 100) * 100;
        return max || 10000;
    }

    // Инициализация модальных фильтров
    function initFilterModal() {
        // Инициализация сворачивания групп фильтров
        document.querySelectorAll('.filter-modal .filter-group-title').forEach(header => {
            let content = header.nextElementSibling;
            header.classList.add('collapsed');
            content.classList.add('collapsed');
            content.style.maxHeight = '0';
            
            header.addEventListener('click', function() {
                this.classList.toggle('collapsed');
                content.classList.toggle('collapsed');
                
                if (content.classList.contains('collapsed')) {
                    content.style.maxHeight = '0';
                } else {
                    content.style.maxHeight = content.scrollHeight + 'px';
                }
            });
        });
        
        // Инициализация слайдеров цены
        let minRange = document.getElementById('modalPriceMinRange');
        let maxRange = document.getElementById('modalPriceMaxRange');
        let minInput = document.getElementById('modalMinPrice');
        let maxInput = document.getElementById('modalMaxPrice');
        let minVal = document.getElementById('modalPriceMinValue');
        let maxVal = document.getElementById('modalPriceMaxValue');
        
        if (minRange && maxRange) {
            let maxPrice = findMaxPrice();
            
            minRange.max = maxPrice;
            maxRange.max = maxPrice;
            minRange.value = activeFilters.price.min;
            maxRange.value = activeFilters.price.max;
            
            if (minInput) minInput.value = activeFilters.price.min;
            if (maxInput) {
                maxInput.value = activeFilters.price.max;
                maxInput.max = maxPrice;
            }
            if (minVal) minVal.textContent = activeFilters.price.min;
            if (maxVal) maxVal.textContent = activeFilters.price.max;
            
            updateModalPriceSliderTrack();
            
            minRange.addEventListener('input', function() {
                let min = parseInt(this.value);
                let max = parseInt(maxRange.value);
                if (min > max) this.value = max;
                if (minInput) minInput.value = this.value;
                if (minVal) minVal.textContent = this.value;
                updateModalPriceSliderTrack();
            });
            
            maxRange.addEventListener('input', function() {
                let max = parseInt(this.value);
                let min = parseInt(minRange.value);
                if (max < min) this.value = min;
                if (maxInput) maxInput.value = this.value;
                if (maxVal) maxVal.textContent = this.value;
                updateModalPriceSliderTrack();
            });
            
            if (minInput) {
                minInput.addEventListener('change', function() {
                    let val = parseInt(this.value) || 0;
                    val = Math.min(val, parseInt(maxRange.value));
                    val = Math.max(val, 0);
                    this.value = val;
                    minRange.value = val;
                    if (minVal) minVal.textContent = val;
                    updateModalPriceSliderTrack();
                });
            }
            
            if (maxInput) {
                maxInput.addEventListener('change', function() {
                    let val = parseInt(this.value) || 0;
                    val = Math.max(val, parseInt(minRange.value));
                    val = Math.min(val, parseInt(maxInput.max));
                    this.value = val;
                    maxRange.value = val;
                    if (maxVal) maxVal.textContent = val;
                    updateModalPriceSliderTrack();
                });
            }
        }
        
        // Кнопка "Применить" в модальном окне
        document.getElementById('modalApplyFilters')?.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Обновляем активные фильтры из модального окна
            activeFilters.price = {
                min: parseInt(document.getElementById('modalPriceMinRange')?.value || 0),
                max: parseInt(document.getElementById('modalPriceMaxRange')?.value || findMaxPrice())
            };
            
            activeFilters.players = [];
            activeFilters.time = [];
            
            document.querySelectorAll('.filter-modal .filter-checkbox:checked').forEach(cb => {
                let type = cb.dataset.filter;
                let value = cb.value;
                
                if (type === 'players') {
                    activeFilters.players.push(value);
                } else if (type === 'time') {
                    activeFilters.time.push(value);
                }
            });
            
            filterProducts();
            
            // Синхронизируем с правой панелью
            syncFiltersWithSidebar();
            
            // Обновляем счетчик фильтров
            updateFilterCount();
            
            closeFilterModal();
        });
        
        // Кнопка "Сбросить" в модальном окне
        document.getElementById('modalClearFilters')?.addEventListener('click', function(e) {
            e.preventDefault();
            
            document.querySelectorAll('.filter-modal .filter-checkbox').forEach(cb => {
                cb.checked = false;
            });
            
            let minRange = document.getElementById('modalPriceMinRange');
            let maxRange = document.getElementById('modalPriceMaxRange');
            
            if (minRange && maxRange) {
                let max = maxRange.max;
                minRange.value = 0;
                maxRange.value = max;
                
                document.getElementById('modalMinPrice').value = 0;
                document.getElementById('modalMaxPrice').value = max;
                document.getElementById('modalPriceMinValue').textContent = '0';
                document.getElementById('modalPriceMaxValue').textContent = max;
            }
            
            activeFilters = {
                price: { min: 0, max: findMaxPrice() },
                players: [],
                time: []
            };
            
            document.querySelectorAll('.product-card').forEach(card => {
                card.style.display = '';
            });
            
            // Синхронизируем с правой панелью
            syncFiltersWithSidebar();
            
            // Обновляем счетчик фильтров
            updateFilterCount();
            
            closeFilterModal();
        });
        
        // Открытие модального окна
        let toggle = document.getElementById('filterToggle');
        let modal = document.getElementById('filterModal');
        let overlay = document.getElementById('filterModalOverlay');
        let close = document.getElementById('filterModalClose');
        
        if (toggle) {
            toggle.addEventListener('click', function() {
                modal.classList.add('active');
                document.body.style.overflow = 'hidden';
                
                // Синхронизируем с правой панелью перед открытием
                syncModalWithSidebar();
            });
        }
        
        window.closeFilterModal = function() {
            modal.classList.remove('active');
            document.body.style.overflow = '';
        };
        
        if (overlay) overlay.addEventListener('click', closeFilterModal);
        if (close) close.addEventListener('click', closeFilterModal);
        
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && modal.classList.contains('active')) {
                closeFilterModal();
            }
        });
    }

    // Синхронизация модального окна с правой панелью
    function syncModalWithSidebar() {
        let sidebarMinRange = document.getElementById('sidebarPriceMinRange');
        let sidebarMaxRange = document.getElementById('sidebarPriceMaxRange');
        let modalMinRange = document.getElementById('modalPriceMinRange');
        let modalMaxRange = document.getElementById('modalPriceMaxRange');
        
        if (sidebarMinRange && modalMinRange) {
            modalMinRange.value = sidebarMinRange.value;
            modalMinRange.max = sidebarMinRange.max;
        }
        
        if (sidebarMaxRange && modalMaxRange) {
            modalMaxRange.value = sidebarMaxRange.value;
            modalMaxRange.max = sidebarMaxRange.max;
        }
        
        document.getElementById('modalMinPrice').value = document.getElementById('sidebarMinPrice').value;
        document.getElementById('modalMaxPrice').value = document.getElementById('sidebarMaxPrice').value;
        document.getElementById('modalPriceMinValue').textContent = document.getElementById('sidebarPriceMinValue').textContent;
        document.getElementById('modalPriceMaxValue').textContent = document.getElementById('sidebarPriceMaxValue').textContent;
        
        document.querySelectorAll('.filter-modal .filter-checkbox').forEach(modalCb => {
            let sidebarCb = document.querySelector(`.catalog-sidebar.right-sidebar .filter-checkbox[data-filter="${modalCb.dataset.filter}"][value="${modalCb.value}"]`);
            if (sidebarCb) {
                modalCb.checked = sidebarCb.checked;
            }
        });
        
        updateModalPriceSliderTrack();
    }

    // Синхронизация правой панели с модальным окном
    function syncFiltersWithSidebar() {
        let sidebarMinRange = document.getElementById('sidebarPriceMinRange');
        let sidebarMaxRange = document.getElementById('sidebarPriceMaxRange');
        let modalMinRange = document.getElementById('modalPriceMinRange');
        let modalMaxRange = document.getElementById('modalPriceMaxRange');
        
        if (sidebarMinRange && modalMinRange) {
            sidebarMinRange.value = modalMinRange.value;
            sidebarMinRange.max = modalMinRange.max;
        }
        
        if (sidebarMaxRange && modalMaxRange) {
            sidebarMaxRange.value = modalMaxRange.value;
            sidebarMaxRange.max = modalMaxRange.max;
        }
        
        document.getElementById('sidebarMinPrice').value = document.getElementById('modalMinPrice').value;
        document.getElementById('sidebarMaxPrice').value = document.getElementById('modalMaxPrice').value;
        document.getElementById('sidebarPriceMinValue').textContent = document.getElementById('modalPriceMinValue').textContent;
        document.getElementById('sidebarPriceMaxValue').textContent = document.getElementById('modalPriceMaxValue').textContent;
        
        document.querySelectorAll('.catalog-sidebar.right-sidebar .filter-checkbox').forEach(sidebarCb => {
            let modalCb = document.querySelector(`.filter-modal .filter-checkbox[data-filter="${sidebarCb.dataset.filter}"][value="${sidebarCb.value}"]`);
            if (modalCb) {
                sidebarCb.checked = modalCb.checked;
            }
        });
        
        updateSidebarPriceSliderTrack();
    }

    // Запуск
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Страница загружена');
        
        // Инициализация сворачивания
        const collapseToggle = document.getElementById('collapseToggle');
        if (collapseToggle) {
            collapseToggle.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                toggleNavCollapse();
            });
        }

        // Добавляем обработчик клика на заголовок горизонтального меню
        const horizontalNavHeader = document.querySelector('.horizontal-nav-header');
        if (horizontalNavHeader) {
            horizontalNavHeader.addEventListener('click', function(e) {
                // Не сворачиваем, если клик по кнопке
                if (e.target.closest('button')) return;
                toggleNavCollapse();
            });
        }
        
        // Сбрасываем состояние при смене раздела
        const originalLoadSection = loadSection;
        loadSection = function(sectionId) {
            originalLoadSection(sectionId);
            // Разворачиваем меню при загрузке нового раздела
            if (isNavCollapsed) {
                toggleNavCollapse();
            }
        };

        // Добавляем кнопку сворачивания в горизонтальное меню
        addCollapseButton();
    });
</script>

<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
?>