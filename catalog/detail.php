<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

// Подключаем модуль инфоблоков
if(!CModule::IncludeModule('iblock')) {
    die('Ошибка: модуль инфоблоков не установлен');
}

// Получаем ID товара из URL
$elementId = intval($_GET['ID']);
if (!$elementId) {
    LocalRedirect('/catalog/');
    return;
}

// Получаем ID инфоблока "Товары"
$iblockId = 0;
$res = CIBlock::GetList(array(), array('CODE' => 'products'));
if ($arRes = $res->Fetch()) {
    $iblockId = $arRes['ID'];
}

if (!$iblockId) {
    echo '<div class="container"><div class="alert alert-danger">Инфоблок не найден</div></div>';
    require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
    return;
}

// Получаем данные товара
$arSelect = array(
    "ID",
    "NAME",
    "IBLOCK_SECTION_ID",
    "DETAIL_PICTURE",
    "PREVIEW_PICTURE",
    "DETAIL_TEXT",
    "PREVIEW_TEXT",
    "PROPERTY_PRICE",
    "PROPERTY_COLOR",
    "PROPERTY_MATERIAL",
    "PROPERTY_PLAYERS",
    "PROPERTY_GAME_TIME",
    "PROPERTY_AGE",
    "PROPERTY_PUBLISHER"
);

$arFilter = array(
    "IBLOCK_ID" => $iblockId,
    "ID" => $elementId,
    "ACTIVE" => "Y"
);

$res = CIBlockElement::GetList(
    array(),
    $arFilter,
    false,
    false,
    $arSelect
);

$product = $res->GetNext();

if (!$product) {
    LocalRedirect('/catalog/');
    return;
}

// Получаем название раздела
$sectionName = '';
if ($product['IBLOCK_SECTION_ID']) {
    $secRes = CIBlockSection::GetById($product['IBLOCK_SECTION_ID']);
    if ($sec = $secRes->Fetch()) {
        $sectionName = $sec['NAME'];
        $sectionId = $sec['ID'];
    }
}

// Получаем картинку
$image = '';
if ($product['DETAIL_PICTURE']) {
    $image = CFile::GetPath($product['DETAIL_PICTURE']);
} elseif ($product['PREVIEW_PICTURE']) {
    $image = CFile::GetPath($product['PREVIEW_PICTURE']);
} else {
    $image = SITE_TEMPLATE_PATH . '/images/no-image.jpg';
}

// Получаем цену
$price = 'Цена по запросу';
if ($product['PROPERTY_PRICE_VALUE']) {
    // Просто отрезаем всё после |
    $price = explode('|', $product['PROPERTY_PRICE_VALUE'])[0] . ' ₽';
    // Добавляем пробелы между тысячами
    $price = number_format((float)$price, 0, '', ' ') . ' ₽';
}

// Формируем характеристики
$props = array();
if ($product['PROPERTY_COLOR_VALUE']) $props['Цвет'] = $product['PROPERTY_COLOR_VALUE'];
if ($product['PROPERTY_MATERIAL_VALUE']) $props['Материал'] = $product['PROPERTY_MATERIAL_VALUE'];
if ($product['PROPERTY_PLAYERS_VALUE']) $props['Игроков'] = $product['PROPERTY_PLAYERS_VALUE'];
if ($product['PROPERTY_GAME_TIME_VALUE']) $props['Время партии'] = $product['PROPERTY_GAME_TIME_VALUE'];
if ($product['PROPERTY_AGE_VALUE']) $props['Возраст'] = $product['PROPERTY_AGE_VALUE'];
if ($product['PROPERTY_PUBLISHER_VALUE']) $props['Издатель'] = $product['PROPERTY_PUBLISHER_VALUE'];

$APPLICATION->SetTitle($product['NAME']);
?>

<div class="container">
    <!-- Хлебные крошки -->
    <div class="breadcrumbs">
        <a href="/">Главная</a> → 
        <a href="/catalog/">Каталог</a> →
        <? if ($sectionName): ?>
            <a href="/catalog/?SECTION_ID=<?= $sectionId ?>"><?= htmlspecialchars($sectionName) ?></a> →
        <? endif; ?>
        <span><?= htmlspecialchars($product['NAME']) ?></span>
    </div>

    <div class="product-detail">
        <!-- Левая колонка с фото -->
        <div class="product-gallery">
            <div class="main-image">
                <img src="<?= htmlspecialchars($image) ?>" 
                     alt="<?= htmlspecialchars($product['NAME']) ?>"
                     id="mainProductImage">
            </div>
            <!-- Здесь потом можно добавить миниатюры -->
        </div>

        <!-- Правая колонка с информацией -->
        <div class="product-info">
            <h1 class="product-title"><?= htmlspecialchars($product['NAME']) ?></h1>
            
            <div class="product-price-block">
                <div class="product-price"><?= htmlspecialchars($price) ?></div>
            </div>

            <!-- Характеристики -->
            <? if (!empty($props)): ?>
                <div class="product-props">
                    <h3>Характеристики</h3>
                    <table class="props-table">
                        <? foreach ($props as $name => $value): ?>
                            <tr>
                                <td><?= htmlspecialchars($name) ?>:</td>
                                <td><?= htmlspecialchars($value) ?></td>
                            </tr>
                        <? endforeach; ?>
                    </table>
                </div>
            <? endif; ?>

            <!-- Кнопки действий -->
            <div class="product-actions">
                <button class="btn-buy" onclick="addToCart(<?= $product['ID'] ?>, 1)">
                    🛒 В корзину
                </button>
                <button class="btn-favorite" onclick="alert('Функция будет позже')">❤️ В избранное</button>
            </div>

            <!-- Краткое описание -->
            <? if ($product['PREVIEW_TEXT']): ?>
                <div class="product-preview">
                    <h3>Кратко о товаре</h3>
                    <p><?= nl2br(htmlspecialchars($product['PREVIEW_TEXT'])) ?></p>
                </div>
            <? endif; ?>
        </div>
    </div>

    <!-- Детальное описание -->
    <? if ($product['DETAIL_TEXT']): ?>
        <div class="product-description">
            <h2>Описание</h2>
            <div class="description-text">
                <?= $product['DETAIL_TEXT'] ?> <!-- HTML не экранируем, если там редактор -->
            </div>
        </div>
    <? endif; ?>
</div>

<style>
    .breadcrumbs {
        margin: 20px 0;
        color: #666;
        font-size: 14px;
    }

    .breadcrumbs a {
        color: #e74c3c;
        text-decoration: none;
    }

    .breadcrumbs a:hover {
        text-decoration: underline;
    }

    .product-detail {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 40px;
        margin: 30px 0;
    }

    .product-gallery {
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 5px 15px rgba(0,0,0,0.08);
    }

    .main-image {
        height: 400px;
        overflow: hidden;
    }

    .main-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .product-info {
        background: white;
        padding: 30px;
        border-radius: 12px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.08);
    }

    .product-title {
        margin: 0 0 20px;
        font-size: 28px;
        color: #333;
    }

    .product-price-block {
        margin-bottom: 25px;
        padding-bottom: 25px;
        border-bottom: 1px solid #eee;
    }

    .product-price {
        font-size: 32px;
        font-weight: bold;
        color: #e74c3c;
    }

    .product-props {
        margin-bottom: 25px;
    }

    .product-props h3 {
        margin: 0 0 15px;
        font-size: 18px;
        color: #333;
    }

    .props-table {
        width: 100%;
        border-collapse: collapse;
    }

    .props-table td {
        padding: 8px 0;
        border-bottom: 1px solid #f0f0f0;
    }

    .props-table td:first-child {
        color: #666;
        width: 40%;
    }

    .props-table td:last-child {
        color: #333;
        font-weight: 500;
    }

    .product-actions {
        display: flex;
        gap: 15px;
        margin-bottom: 25px;
    }

    .btn-buy, .btn-favorite {
        padding: 15px 30px;
        border: none;
        border-radius: 8px;
        font-size: 16px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s;
    }

    .btn-buy {
        background: linear-gradient(135deg, #eabb66, #e74c3c);
        color: white;
        flex: 2;
    }

    .btn-buy:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(231, 76, 60, 0.3);
    }

    .btn-favorite {
        background: white;
        color: #666;
        border: 1px solid #ddd;
        flex: 1;
    }

    .btn-favorite:hover {
        background: #f8f9fa;
        color: #e74c3c;
        border-color: #e74c3c;
    }

    .product-preview {
        margin-top: 25px;
        padding-top: 25px;
        border-top: 1px solid #eee;
    }

    .product-preview h3 {
        margin: 0 0 10px;
        font-size: 16px;
        color: #666;
    }

    .product-preview p {
        color: #333;
        line-height: 1.6;
        margin: 0;
    }

    .product-description {
        margin: 40px 0;
        padding: 30px;
        background: white;
        border-radius: 12px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.08);
    }

    .product-description h2 {
        margin: 0 0 20px;
        font-size: 24px;
        color: #333;
    }

    .description-text {
        color: #666;
        line-height: 1.8;
    }

    @media (max-width: 768px) {
        .product-detail {
            grid-template-columns: 1fr;
        }
    }
</style>

<script>
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

    function showNotification(message, type = 'success') {
        // Создаем уведомление
        const notification = document.createElement('div');
        notification.className = `cart-notification cart-notification--${type}`;
        notification.textContent = message;
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: ${type === 'success' ? '#27ae60' : '#e74c3c'};
            color: white;
            padding: 15px 25px;
            border-radius: 8px;
            z-index: 10000;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            animation: slideIn 0.3s ease;
        `;
        
        document.body.appendChild(notification);
        
        // Удаляем через 3 секунды
        setTimeout(() => {
            notification.remove();
        }, 3000);
    }

    if (window.updateCartCounter) {
    console.log('Удаляем старую updateCartCounter');
    delete window.updateCartCounter;
}

// Создаем новую правильную функцию
window.updateCartCounter = function() {
    console.log('🟢 Обновляем счетчик с сервера');
    
    fetch('/ajax/add_to_cart.php?action=get&t=' + Date.now())
        .then(response => response.json())
        .then(data => {
            console.log('🟢 Данные с сервера:', data);
            
            if (data.success) {
                const counters = document.querySelectorAll('.cart-counter');
                counters.forEach(counter => {
                    counter.textContent = data.cart_count; // Берем с сервера!
                    counter.style.display = data.cart_count > 0 ? 'inline' : 'none';
                    console.log('✅ Счетчик обновлен на:', data.cart_count);
                });
            }
        })
        .catch(error => {
            console.error('🔴 Ошибка:', error);
        });
};

// Вызываем при загрузке
document.addEventListener('DOMContentLoaded', function() {
    console.log('Header loaded');
    window.updateCartCounter();
});

// Обновляем каждые 5 секунд
setInterval(function() {
    console.log('Periodic cart update');
    window.updateCartCounter();
}, 5000);

    // Добавляем стили для анимации
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
    `;
    document.head.appendChild(style);
</script>

<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
?>