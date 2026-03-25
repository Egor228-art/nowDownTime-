<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Избранное");
?>

<div class="favorites-page">
    <div class="container">
        <div class="favorites-header">
            <div class="header-icon">
                <i class="fas fa-heart"></i>
                <i class="fas fa-star"></i>
            </div>
            <h1>Мои любимые игры</h1>
            <p>Здесь собраны игры, которые вам понравились. Добавляйте новые и возвращайтесь к любимым в любое время.</p>
        </div>
        
        <div class="favorites-stats">
            <div class="stat-card">
                <div class="stat-value" id="favoritesCount">0</div>
                <div class="stat-label">Игр в избранном</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" id="totalPrice">0 ₽</div>
                <div class="stat-label">Общая стоимость</div>
            </div>
            <div class="stat-card">
                <button class="clear-favorites" onclick="clearAllFavorites()">
                    <i class="fas fa-trash-alt"></i>
                    Очистить всё
                </button>
            </div>
        </div>
        
        <div class="favorites-grid" id="favoritesGrid">
            <div class="loading-spinner">
                <i class="fas fa-spinner fa-spin"></i>
                <span>Загрузка избранного...</span>
            </div>
        </div>
        
        <div class="favorites-empty" id="favoritesEmpty" style="display: none;">
            <div class="empty-icon">
                <i class="fas fa-heart-broken"></i>
                <i class="fas fa-dice-d6"></i>
            </div>
            <h3>Избранное пусто</h3>
            <p>Добавляйте игры в избранное, чтобы они появлялись здесь. Начните с просмотра каталога!</p>
            <a href="/catalog/" class="btn-primary">
                <i class="fas fa-dice-d20"></i>
                В каталог
            </a>
        </div>
    </div>
</div>

<style>
.favorites-page {
    min-height: 100vh;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    padding: 40px 0 80px;
}

.container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 0 20px;
}

.favorites-header {
    text-align: center;
    margin-bottom: 40px;
}

.header-icon {
    margin-bottom: 20px;
}

.header-icon i {
    font-size: 48px;
    margin: 0 10px;
    background: linear-gradient(135deg, #e74c3c, #f39c12);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    animation: heartbeat 1.5s ease-in-out infinite;
}

@keyframes heartbeat {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.1); }
}

.favorites-header h1 {
    font-size: 36px;
    color: #2c3e50;
    margin-bottom: 15px;
}

.favorites-header p {
    font-size: 16px;
    color: #7f8c8d;
    max-width: 600px;
    margin: 0 auto;
}

.favorites-stats {
    display: flex;
    gap: 20px;
    justify-content: center;
    margin-bottom: 40px;
    flex-wrap: wrap;
}

.stat-card {
    background: white;
    border-radius: 20px;
    padding: 20px 30px;
    text-align: center;
    min-width: 150px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.08);
}

.stat-value {
    font-size: 28px;
    font-weight: bold;
    color: #e74c3c;
    margin-bottom: 8px;
}

.stat-label {
    font-size: 13px;
    color: #7f8c8d;
}

.clear-favorites {
    background: none;
    border: none;
    color: #e74c3c;
    cursor: pointer;
    font-size: 14px;
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 16px;
    border-radius: 25px;
    transition: all 0.3s;
}

.clear-favorites:hover {
    background: #e74c3c;
    color: white;
}

.favorites-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 25px;
}

.product-card {
    background: white;
    border-radius: 20px;
    overflow: hidden;
    transition: all 0.3s;
    cursor: pointer;
    box-shadow: 0 5px 15px rgba(0,0,0,0.08);
}

.product-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 30px rgba(0,0,0,0.15);
}

.product-image {
    position: relative;
    padding-top: 100%;
    overflow: hidden;
    background: #f8f9fa;
}

.product-image img {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    object-fit: contain;
    padding: 20px;
}

.favorite-badge {
    position: absolute;
    top: 15px;
    right: 15px;
    background: #e74c3c;
    color: white;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    z-index: 2;
    transition: all 0.3s;
    border: none;
}

.favorite-badge:hover {
    transform: scale(1.1);
}

.product-badge {
    position: absolute;
    top: 15px;
    left: 15px;
    background: #f39c12;
    color: white;
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 12px;
}

.product-info {
    padding: 20px;
}

.product-title {
    font-size: 16px;
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 12px;
    min-height: 44px;
}

.product-details {
    display: flex;
    gap: 15px;
    margin-bottom: 15px;
}

.product-detail {
    display: flex;
    align-items: center;
    gap: 5px;
    font-size: 12px;
    color: #7f8c8d;
}

.product-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 15px;
}

.product-price {
    font-size: 20px;
    font-weight: bold;
    color: #e74c3c;
}

.product-cart {
    background: linear-gradient(135deg, #e74c3c, #c0392b);
    border: none;
    width: 40px;
    height: 40px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s;
    color: white;
}

.product-cart:hover {
    transform: scale(1.05);
}

.loading-spinner {
    text-align: center;
    padding: 60px;
    grid-column: 1 / -1;
}

.loading-spinner i {
    font-size: 48px;
    color: #e74c3c;
    margin-bottom: 15px;
    display: block;
}

.favorites-empty {
    text-align: center;
    padding: 60px 20px;
    background: white;
    border-radius: 30px;
    margin-top: 20px;
}

.empty-icon i {
    font-size: 64px;
    margin: 0 10px;
    color: #e0e0e0;
}

.favorites-empty h3 {
    font-size: 24px;
    color: #2c3e50;
    margin-bottom: 15px;
}

.btn-primary {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 12px 28px;
    background: linear-gradient(135deg, #e74c3c, #c0392b);
    color: white;
    text-decoration: none;
    border-radius: 50px;
    transition: all 0.3s;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(231, 76, 60, 0.3);
}

@media (max-width: 768px) {
    .favorites-grid {
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    }
}
</style>

<script>
// Загрузка избранного
function loadFavorites() {
    const grid = document.getElementById('favoritesGrid');
    const emptyBlock = document.getElementById('favoritesEmpty');
    const countSpan = document.getElementById('favoritesCount');
    const totalPriceSpan = document.getElementById('totalPrice');
    
    fetch('/ajax/toggle_favorite.php?action=get')
        .then(response => response.json())
        .then(data => {
            console.log('Избранное:', data);
            const favorites = data.favorites || [];
            
            if (favorites.length === 0) {
                grid.style.display = 'none';
                emptyBlock.style.display = 'block';
                countSpan.textContent = '0';
                totalPriceSpan.textContent = '0 ₽';
                return;
            }
            
            grid.style.display = 'grid';
            emptyBlock.style.display = 'none';
            countSpan.textContent = favorites.length;
            
            grid.innerHTML = '<div class="loading-spinner"><i class="fas fa-spinner fa-spin"></i><span>Загрузка игр...</span></div>';
            
            // Загружаем данные о каждом товаре
            const promises = favorites.map(productId => {
                return fetch(`/ajax/get_product_data.php?id=${productId}`)
                    .then(res => res.json())
                    .catch(() => null);
            });
            
            Promise.all(promises).then(products => {
                const validProducts = products.filter(p => p && p.id);
                let totalPrice = 0;
                
                if (validProducts.length === 0) {
                    grid.style.display = 'none';
                    emptyBlock.style.display = 'block';
                    return;
                }
                
                let html = '';
                validProducts.forEach(product => {
                    totalPrice += product.price || 0;
                    
                    html += `
                        <div class="product-card" onclick="window.location='/catalog/detail.php?ID=${product.id}'">
                            <div class="product-image">
                                <img src="${product.image || '/upload/no-image.jpg'}" alt="${product.name}">
                                <button class="favorite-badge" onclick="event.stopPropagation(); removeFromFavorites(${product.id})">
                                    <i class="fas fa-heart"></i>
                                </button>
                                <div class="product-badge">${product.category || 'Настольная игра'}</div>
                            </div>
                            <div class="product-info">
                                <h3 class="product-title">${escapeHtml(product.name)}</h3>
                                <div class="product-details">
                                    <div class="product-detail">
                                        <i class="fas fa-users"></i>
                                        <span>${product.players || '2-4'} игр.</span>
                                    </div>
                                    <div class="product-detail">
                                        <i class="fas fa-clock"></i>
                                        <span>${product.time || '30-60'} мин</span>
                                    </div>
                                </div>
                                <div class="product-footer">
                                    <div class="product-price">${formatPrice(product.price)}</div>
                                    <button class="product-cart" onclick="event.stopPropagation(); addToCart(${product.id})">
                                        <i class="fas fa-shopping-cart"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    `;
                });
                
                totalPriceSpan.textContent = formatPrice(totalPrice);
                grid.innerHTML = html;
            });
        })
        .catch(error => {
            console.error('Ошибка:', error);
            grid.innerHTML = '<div class="loading-spinner"><span>Ошибка загрузки. Попробуйте обновить страницу.</span></div>';
        });
}

// Удаление из избранного
function removeFromFavorites(productId) {
    fetch('/ajax/toggle_favorite.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `action=remove&product_id=${productId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showMessage('🗑️ Удалено из избранного', '#7f8c8d');
            loadFavorites(); // Перезагружаем список
            updateFavoriteCounter();
        }
    });
}

// Очистка всего избранного
function clearAllFavorites() {
    if (confirm('Вы уверены, что хотите удалить все игры из избранного?')) {
        fetch('/ajax/toggle_favorite.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'action=clear'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showMessage('🧹 Избранное очищено', '#27ae60');
                loadFavorites();
                updateFavoriteCounter();
            }
        });
    }
}

// Добавление в корзину
function addToCart(productId) {
    fetch('/ajax/add_to_cart.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `action=add&product_id=${productId}&quantity=1`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showMessage('✅ Товар добавлен в корзину!', '#27ae60');
        } else {
            showMessage('❌ Ошибка добавления', '#e74c3c');
        }
    });
}

// Обновление счетчика
function updateFavoriteCounter() {
    fetch('/ajax/toggle_favorite.php?action=get')
        .then(response => response.json())
        .then(data => {
            const counter = document.getElementById('favorite-counter');
            if (counter) {
                const count = data.count || 0;
                counter.textContent = count;
                counter.style.display = count > 0 ? 'inline-flex' : 'none';
            }
        });
}

// Вспомогательные функции
function formatPrice(price) {
    if (!price || price === 0) return 'Цена по запросу';
    return new Intl.NumberFormat('ru-RU').format(price) + ' ₽';
}

function escapeHtml(str) {
    if (!str) return '';
    return str.replace(/[&<>]/g, function(m) {
        if (m === '&') return '&amp;';
        if (m === '<') return '&lt;';
        if (m === '>') return '&gt;';
        return m;
    });
}

function showMessage(message, color) {
    const notif = document.createElement('div');
    notif.innerHTML = `
        <div style="
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: ${color};
            color: white;
            padding: 12px 24px;
            border-radius: 50px;
            z-index: 10001;
            animation: slideIn 0.3s ease;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            font-size: 14px;
        ">
            ${message}
        </div>
    `;
    document.body.appendChild(notif);
    setTimeout(() => {
        notif.style.opacity = '0';
        notif.style.transition = 'opacity 0.3s';
        setTimeout(() => notif.remove(), 300);
    }, 3000);
}

// Добавляем анимации
if (!document.querySelector('#fav-animation')) {
    const style = document.createElement('style');
    style.id = 'fav-animation';
    style.textContent = `
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
    `;
    document.head.appendChild(style);
}

// Запускаем загрузку
document.addEventListener('DOMContentLoaded', function() {
    loadFavorites();
    updateFavoriteCounter();
});
</script>

<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
?>