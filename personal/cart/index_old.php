<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

$APPLICATION->SetTitle("Корзина");
?>

<div class="physical-cart-wrapper">
    <!-- Шапка с анимацией -->
    <div class="cart-header">
        <h1>✨ Магическая корзина ✨</h1>
        <div class="cart-stats">
            <div class="score-display">
                <span class="score-icon">🏆</span>
                <span id="player-score">0</span>
            </div>
            <div class="combo-display" id="combo-display" style="display: none;">
                <span class="combo-icon">⚡</span>
                <span id="combo-count">0</span>
                <span class="combo-text">x COMBO!</span>
            </div>
        </div>
    </div>

    <div class="cart-layout">
        <!-- Левая часть - Корзина-контейнер -->
        <div class="cart-container" id="cart-container">
            <div class="cart-drag-zone">
                <div class="cart-title">
                    🧺 Корзина
                    <span class="cart-hint">Перетаскивайте товары, чтобы они не выпали!</span>
                </div>
                
                <!-- Прогресс-бар стабильности -->
                <div class="stability-container">
                    <div class="stability-label">
                        <span>🔒 Стабильность корзины</span>
                        <span id="stability-timer">5.0 сек</span>
                    </div>
                    <div class="stability-progress-bar">
                        <div class="stability-progress" id="stability-progress"></div>
                    </div>
                    <div class="stability-message" id="stability-message">
                        Удерживайте товары 5 секунд для оформления
                    </div>
                </div>
                
                <!-- Прогресс-бар бесплатной доставки -->
                <div class="shipping-progress-container" id="shipping-progress-container">
                    <div class="shipping-label">
                        <span>🚚 До бесплатной доставки</span>
                        <span id="shipping-remaining">0 ₽</span>
                    </div>
                    <div class="shipping-progress-bar">
                        <div class="shipping-progress" id="shipping-progress"></div>
                    </div>
                </div>
                
                <!-- Список товаров в корзине -->
                <div class="cart-items" id="cart-items">
                    <div class="cart-loading">Загрузка корзины...</div>
                </div>
                
                <!-- Итоговая сумма -->
                <div class="cart-total-block">
                    <div class="cart-total-label">Итого:</div>
                    <div class="cart-total-amount" id="cart-total">0 ₽</div>
                </div>
                
                <!-- Кнопки действий -->
                <div class="cart-actions">
                    <button class="btn-continue" id="btn-continue" onclick="continueShopping()">
                        🛍️ Продолжить покупки
                    </button>
                    <button class="btn-clear" id="btn-clear" onclick="clearCart()">
                        🗑️ Очистить
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Правая часть - Форма оформления (свиток) -->
        <div class="order-scroll">
            <div class="scroll-paper">
                <div class="scroll-seal"></div>
                <div class="scroll-content">
                    <h2>📜 Оформление заказа</h2>
                    
                    <form id="orderForm" onsubmit="submitOrder(event)">
                        <!-- Контактные данные -->
                        <div class="form-section">
                            <h3>👤 Личные данные</h3>
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Фамилия</label>
                                    <input type="text" id="lastName" placeholder="Иванов">
                                    <div class="field-hint" id="lastNameHint">Обязательное поле</div>
                                </div>
                                <div class="form-group">
                                    <label>Имя</label>
                                    <input type="text" id="firstName" placeholder="Иван">
                                    <div class="field-hint" id="firstNameHint">Обязательное поле</div>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label>📞 Телефон</label>
                                    <input type="tel" id="phone" placeholder="+7 (___) ___-__-__">
                                    <div class="field-hint" id="phoneHint">Обязательное поле</div>
                                </div>
                                <div class="form-group">
                                    <label>✉️ Email</label>
                                    <input type="email" id="email" placeholder="ivan@example.com">
                                    <div class="field-hint" id="emailHint">Обязательное поле</div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label>🏙️ Город</label>
                                <input type="text" id="city" placeholder="Ваш город" readonly>
                                <div class="city-detection" id="city-detection">Определяем город...</div>
                            </div>
                        </div>
                        
                        <!-- Способ доставки -->
                        <div class="form-section">
                            <h3>🚚 Доставка</h3>
                            <div class="delivery-options">
                                <label class="delivery-option" onclick="selectDelivery('pickup')">
                                    <input type="radio" name="delivery" value="pickup" checked>
                                    <div class="delivery-info">
                                        <div class="delivery-name">🏪 Самовывоз</div>
                                        <div class="delivery-desc">г. Великий Новгород, ул. Большая Московская, 8</div>
                                        <div class="delivery-price">Бесплатно</div>
                                    </div>
                                </label>
                                
                                <label class="delivery-option" onclick="selectDelivery('delivery')">
                                    <input type="radio" name="delivery" value="delivery">
                                    <div class="delivery-info">
                                        <div class="delivery-name">🚛 Доставка курьером</div>
                                        <div class="delivery-desc">По Великому Новгороду</div>
                                        <div class="delivery-price">+500 ₽</div>
                                    </div>
                                </label>
                            </div>
                            
                            <div class="form-group" id="addressGroup" style="display: none;">
                                <label>📍 Адрес доставки</label>
                                <textarea id="address" rows="2" placeholder="Улица, дом, квартира"></textarea>
                                <div class="field-hint" id="addressHint">Укажите адрес</div>
                            </div>
                        </div>
                        
                        <!-- Способ оплаты -->
                        <div class="form-section">
                            <h3>💳 Оплата</h3>
                            <div class="payment-options">
                                <label class="payment-option">
                                    <input type="radio" name="payment" value="cash" checked>
                                    <div class="payment-name">💰 Наличными при получении</div>
                                </label>
                                
                                <label class="payment-option">
                                    <input type="radio" name="payment" value="card">
                                    <div class="payment-name">💳 Картой онлайн</div>
                                </label>
                            </div>
                        </div>
                        
                        <!-- Комментарий -->
                        <div class="form-section">
                            <h3>💭 Комментарий</h3>
                            <textarea id="comment" rows="2" placeholder="Дополнительная информация..."></textarea>
                        </div>
                        
                        <!-- Кнопка оформления -->
                        <button type="submit" class="btn-submit-order" id="checkoutBtn" disabled>
                            <span class="btn-text">✨ Оформить заказ</span>
                            <span class="btn-total" id="finalTotal">0 ₽</span>
                        </button>
                        
                        <div class="order-hint" id="order-hint">
                            🔒 Удерживайте товары 5 секунд для активации кнопки
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Блок последних заказов -->
    <div class="recent-orders-section">
        <h3>📦 Ваши последние заказы</h3>
        <div id="recent-orders-list" class="recent-orders-grid">
            <div class="recent-orders-loading">Загрузка...</div>
        </div>
        <a href="/personal/orders/" class="view-all-link">Все заказы →</a>
    </div>
</div>

<!-- Контейнер для спавна товаров -->
<div id="product-spawn-area" class="product-spawn-area"></div>

<style>
/* Основные стили корзины */
.physical-cart-wrapper {
    max-width: 1400px;
    margin: 0 auto;
    padding: 20px;
}

.cart-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    padding: 20px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 15px;
    color: white;
}

.cart-header h1 {
    margin: 0;
    font-size: 28px;
}

.cart-stats {
    display: flex;
    gap: 20px;
}

.score-display, .combo-display {
    background: rgba(255,255,255,0.2);
    padding: 8px 15px;
    border-radius: 20px;
    font-weight: bold;
    backdrop-filter: blur(5px);
}

.cart-layout {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 30px;
    margin-bottom: 40px;
}

/* Левая часть - корзина */
.cart-container {
    background: white;
    border-radius: 20px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    overflow: hidden;
    transition: all 0.3s ease;
    border: 2px solid #e0e0e0;
}

.cart-container.shake {
    animation: shake 0.5s ease;
}

@keyframes shake {
    0%, 100% { transform: translateX(0); }
    25% { transform: translateX(-5px); }
    75% { transform: translateX(5px); }
}

.cart-drag-zone {
    padding: 20px;
    min-height: 500px;
}

.cart-title {
    font-size: 24px;
    font-weight: bold;
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 2px solid #f0f0f0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.cart-hint {
    font-size: 12px;
    color: #999;
    font-weight: normal;
}

/* Прогресс-бар стабильности */
.stability-container {
    background: #f8f9fa;
    border-radius: 10px;
    padding: 15px;
    margin-bottom: 20px;
}

.stability-label {
    display: flex;
    justify-content: space-between;
    margin-bottom: 8px;
    font-size: 14px;
    color: #666;
}

.stability-progress-bar {
    height: 8px;
    background: #e0e0e0;
    border-radius: 10px;
    overflow: hidden;
    margin-bottom: 8px;
}

.stability-progress {
    height: 100%;
    background: linear-gradient(90deg, #f39c12, #27ae60);
    width: 0%;
    transition: width 0.1s linear;
    border-radius: 10px;
}

.stability-message {
    font-size: 12px;
    color: #999;
    text-align: center;
}

/* Прогресс-бар доставки */
.shipping-progress-container {
    background: #fff3e0;
    border-radius: 10px;
    padding: 15px;
    margin-bottom: 20px;
}

.shipping-label {
    display: flex;
    justify-content: space-between;
    margin-bottom: 8px;
    font-size: 14px;
    color: #e67e22;
}

.shipping-progress-bar {
    height: 8px;
    background: #ffe0b3;
    border-radius: 10px;
    overflow: hidden;
}

.shipping-progress {
    height: 100%;
    background: linear-gradient(90deg, #f39c12, #e67e22);
    width: 0%;
    transition: width 0.3s ease;
    border-radius: 10px;
}

/* Товары в корзине */
.cart-items {
    margin-bottom: 20px;
    max-height: 400px;
    overflow-y: auto;
}

.cart-item {
    display: flex;
    align-items: center;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 10px;
    margin-bottom: 10px;
    transition: all 0.3s ease;
    cursor: grab;
    position: relative;
}

.cart-item:active {
    cursor: grabbing;
}

.cart-item.dragging {
    opacity: 0.5;
    cursor: grabbing;
}

.cart-item.ghost {
    opacity: 0.6;
    filter: blur(2px);
    background: #e0e0e0;
}

.cart-item-image {
    width: 70px;
    height: 70px;
    margin-right: 15px;
    border-radius: 10px;
    overflow: hidden;
}

.cart-item-image img {
    width: 100%;
    height: 100%;
    object-fit: contain;
    background: transparent;
}

.cart-item-info {
    flex: 1;
}

.cart-item-name {
    font-weight: 500;
    margin-bottom: 5px;
    color: #333;
}

.cart-item-price {
    color: #e74c3c;
    font-weight: bold;
}

.cart-item-quantity {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-top: 5px;
}

.quantity-btn {
    width: 28px;
    height: 28px;
    border: 1px solid #ddd;
    background: white;
    border-radius: 6px;
    cursor: pointer;
    font-size: 18px;
    transition: all 0.3s;
}

.quantity-btn:hover {
    background: #e74c3c;
    color: white;
    border-color: #e74c3c;
}

.quantity-input {
    width: 50px;
    text-align: center;
    border: 1px solid #ddd;
    border-radius: 6px;
    padding: 5px;
}

.cart-item-sum {
    font-weight: bold;
    color: #333;
    margin-left: 15px;
    min-width: 80px;
    text-align: right;
}

.btn-remove-item {
    background: none;
    border: none;
    color: #999;
    font-size: 24px;
    cursor: pointer;
    padding: 0 10px;
    transition: all 0.3s;
}

.btn-remove-item:hover {
    color: #e74c3c;
    transform: scale(1.1);
}

/* Итоговая сумма */
.cart-total-block {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 10px;
    margin-bottom: 15px;
}

.cart-total-label {
    font-size: 18px;
    font-weight: bold;
    color: #666;
}

.cart-total-amount {
    font-size: 28px;
    font-weight: bold;
    color: #e74c3c;
}

.cart-actions {
    display: flex;
    gap: 10px;
}

.btn-continue, .btn-clear {
    flex: 1;
    padding: 12px;
    border: none;
    border-radius: 10px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 500;
    transition: all 0.3s;
}

.btn-continue {
    background: #3498db;
    color: white;
}

.btn-continue:hover {
    background: #2980b9;
    transform: translateY(-2px);
}

.btn-clear {
    background: #e74c3c;
    color: white;
}

.btn-clear:hover {
    background: #c0392b;
    transform: translateY(-2px);
}

/* Правая часть - свиток */
.order-scroll {
    perspective: 1000px;
}

.scroll-paper {
    background: #fef7e0;
    background-image: 
        repeating-linear-gradient(45deg, 
            rgba(0,0,0,0.02) 0px, 
            rgba(0,0,0,0.02) 2px,
            transparent 2px, 
            transparent 8px);
    border-radius: 15px;
    box-shadow: 0 15px 35px rgba(0,0,0,0.2);
    padding: 30px;
    position: relative;
    transition: all 0.3s ease;
    border-left: 3px solid #e0cba0;
    border-right: 3px solid #e0cba0;
}

.scroll-paper::before,
.scroll-paper::after {
    content: "";
    position: absolute;
    left: 0;
    right: 0;
    height: 20px;
    background: linear-gradient(90deg, 
        transparent 0%, 
        rgba(224,203,160,0.3) 50%,
        transparent 100%);
    pointer-events: none;
}

.scroll-paper::before {
    top: 0;
    border-radius: 15px 15px 0 0;
}

.scroll-paper::after {
    bottom: 0;
    border-radius: 0 0 15px 15px;
}

.scroll-seal {
    position: absolute;
    top: 20px;
    right: 20px;
    width: 60px;
    height: 60px;
    background: radial-gradient(circle, #e74c3c 30%, #c0392b 70%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.2);
    opacity: 0.8;
    transform: rotate(-15deg);
}

.scroll-seal::before {
    content: "✓";
    color: white;
    font-size: 32px;
    font-weight: bold;
}

.scroll-content {
    position: relative;
    z-index: 1;
}

.scroll-content h2 {
    text-align: center;
    color: #8b5a2b;
    margin-bottom: 25px;
    font-family: 'Georgia', serif;
}

.form-section {
    margin-bottom: 25px;
    padding-bottom: 15px;
    border-bottom: 1px dashed #e0cba0;
}

.form-section h3 {
    color: #8b5a2b;
    margin-bottom: 15px;
    font-size: 18px;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
    margin-bottom: 15px;
}

.form-group {
    margin-bottom: 15px;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    color: #6b4c2c;
    font-size: 13px;
    font-weight: 500;
}

.form-group input,
.form-group textarea,
.form-group select {
    width: 100%;
    padding: 10px;
    border: 1px solid #e0cba0;
    border-radius: 8px;
    background: rgba(255,255,255,0.8);
    font-size: 14px;
    transition: all 0.3s;
}

.form-group input:focus,
.form-group textarea:focus {
    border-color: #e74c3c;
    outline: none;
    box-shadow: 0 0 5px rgba(231,76,60,0.3);
}

.city-detection {
    font-size: 12px;
    color: #999;
    margin-top: 5px;
}

.delivery-options,
.payment-options {
    display: grid;
    gap: 10px;
    margin-bottom: 15px;
}

.delivery-option,
.payment-option {
    display: flex;
    align-items: center;
    padding: 12px;
    border: 2px solid #e0cba0;
    border-radius: 10px;
    cursor: pointer;
    transition: all 0.3s;
    background: rgba(255,255,255,0.6);
}

.delivery-option:hover,
.payment-option:hover {
    border-color: #e74c3c;
    background: white;
}

.delivery-option.selected,
.payment-option.selected {
    border-color: #e74c3c;
    background: #fff3f0;
}

.delivery-option input,
.payment-option input {
    margin-right: 12px;
    width: auto;
}

.delivery-info {
    flex: 1;
}

.delivery-name {
    font-weight: bold;
    color: #333;
}

.delivery-desc {
    font-size: 12px;
    color: #999;
}

.delivery-price {
    color: #e74c3c;
    font-weight: bold;
}

.btn-submit-order {
    width: 100%;
    padding: 15px;
    background: linear-gradient(135deg, #e74c3c, #c0392b);
    color: white;
    border: none;
    border-radius: 10px;
    font-size: 16px;
    font-weight: bold;
    cursor: pointer;
    transition: all 0.3s;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.btn-submit-order:not(:disabled):hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(231,76,60,0.3);
}

.btn-submit-order:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.order-hint {
    text-align: center;
    font-size: 12px;
    color: #999;
    margin-top: 10px;
}

/* Последние заказы */
.recent-orders-section {
    background: white;
    border-radius: 15px;
    padding: 20px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.08);
}

.recent-orders-section h3 {
    margin: 0 0 20px 0;
    color: #333;
}

.recent-orders-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 15px;
    margin-bottom: 20px;
}

.recent-order-card {
    background: #f8f9fa;
    border-radius: 10px;
    padding: 15px;
    transition: all 0.3s;
    border-left: 3px solid #e74c3c;
}

.recent-order-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.order-number {
    font-weight: bold;
    color: #333;
    margin-bottom: 5px;
}

.order-date {
    font-size: 12px;
    color: #999;
    margin-bottom: 8px;
}

.order-status {
    display: inline-block;
    padding: 3px 8px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: bold;
    margin-bottom: 8px;
}

.status-new { background: #3498db; color: white; }
.status-processing { background: #f39c12; color: white; }
.status-delivered { background: #27ae60; color: white; }
.status-cancelled { background: #e74c3c; color: white; }

.order-total {
    font-weight: bold;
    color: #e74c3c;
    font-size: 16px;
}

.repeat-order-btn {
    margin-top: 10px;
    padding: 5px 12px;
    background: #3498db;
    color: white;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-size: 12px;
    transition: all 0.3s;
}

.repeat-order-btn:hover {
    background: #2980b9;
}

.view-all-link {
    display: inline-block;
    color: #e74c3c;
    text-decoration: none;
    font-weight: 500;
}

/* Область спавна товаров */
.product-spawn-area {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    height: 100px;
    pointer-events: none;
    z-index: 999;
}

/* Анимации */
@keyframes comboAnimation {
    0% {
        transform: translate(-50%, -50%) scale(0);
        opacity: 1;
    }
    50% {
        transform: translate(-50%, -50%) scale(1.5);
        opacity: 1;
    }
    100% {
        transform: translate(-50%, -50%) scale(2);
        opacity: 0;
    }
}

@keyframes ghostPulse {
    0%, 100% { opacity: 0.6; }
    50% { opacity: 0.3; }
}

@keyframes resurrection {
    0% {
        filter: blur(2px);
        opacity: 0.5;
    }
    50% {
        filter: blur(0) brightness(1.5);
        opacity: 1;
    }
    100% {
        filter: blur(0);
        opacity: 1;
    }
}

@keyframes slideIn {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

/* Адаптивность */
@media (max-width: 768px) {
    .cart-layout {
        grid-template-columns: 1fr;
    }
    
    .form-row {
        grid-template-columns: 1fr;
    }
    
    .cart-header {
        flex-direction: column;
        gap: 15px;
        text-align: center;
    }
    
    .cart-item {
        flex-wrap: wrap;
    }
    
    .cart-item-sum {
        margin-left: 0;
        width: 100%;
        text-align: left;
        margin-top: 10px;
    }
}

/* Уведомления */
.cart-notification {
    position: fixed;
    top: 20px;
    right: 20px;
    background: #27ae60;
    color: white;
    padding: 15px 25px;
    border-radius: 10px;
    z-index: 10000;
    animation: slideIn 0.3s ease;
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
}
</style>

<script>
// Глобальные переменные
let physicalCart = null;
let gamification = null;
let stabilitySystem = null;
let currentCartItems = [];
let isStable = false;

// Инициализация при загрузке
document.addEventListener('DOMContentLoaded', function() {
    // Инициализируем классы
    physicalCart = new PhysicalCart();
    gamification = new Gamification();
    stabilitySystem = new StabilitySystem();
    
    // Загружаем корзину
    loadCart();
    
    // Определяем город
    detectCity();
    
    // Инициализируем маску телефона
    initPhoneMask();
});

// physical-cart.js
class PhysicalCart {
    constructor() {
        this.activeProducts = []; // Активные товары на странице
        this.cartItems = []; // Товары в корзине
        this.draggedItem = null;
        this.dragOffset = { x: 0, y: 0 };
        this.isDragging = false;
        this.falloutTimeouts = new Map(); // productId -> timeout
        this.ghostElements = new Map(); // productId -> ghostElement
        this.init();
    }
    
    init() {
        this.bindEvents();
        this.startProductSpawner();
    }
    
    bindEvents() {
        // Обработка drag-and-drop для корзины
        const cartContainer = document.getElementById('cart-container');
        if (cartContainer) {
            cartContainer.addEventListener('dragover', (e) => {
                e.preventDefault();
            });
            
            cartContainer.addEventListener('drop', (e) => {
                e.preventDefault();
                const productId = e.dataTransfer.getData('text/plain');
                if (productId) {
                    this.catchProduct(productId, e.clientX, e.clientY);
                }
            });
        }
        
        // Обработка для мобильных (touch)
        if (isMobile) {
            this.initTouchEvents();
        }
    }
    
    // Спавн товаров (для демонстрации)
    startProductSpawner() {
        // Загружаем товары из корзины для спавна
        setInterval(() => {
            if (this.cartItems.length > 0 && Math.random() > 0.7) {
                const randomItem = this.cartItems[Math.floor(Math.random() * this.cartItems.length)];
                if (randomItem && !this.falloutTimeouts.has(randomItem.PRODUCT_ID)) {
                    this.spawnProduct(randomItem);
                }
            }
        }, 8000);
    }
    
    spawnProduct(productData) {
        const productElement = this.createProductElement(productData);
        productElement.style.position = 'fixed';
        productElement.style.top = '-120px';
        productElement.style.left = `${Math.random() * 70 + 15}%`;
        productElement.style.zIndex = '1000';
        productElement.style.cursor = 'grab';
        productElement.setAttribute('data-product-id', productData.PRODUCT_ID);
        
        document.body.appendChild(productElement);
        this.activeProducts.push({
            element: productElement,
            data: productData,
            isFalling: true
        });
        
        // Анимация падения
        this.animateFall(productElement, productData);
        
        // Звук спавна (опционально)
        this.playSound('spawn');
    }
    
    createProductElement(productData) {
        const div = document.createElement('div');
        div.className = 'floating-product';
        div.innerHTML = `
            <div class="floating-product-image">
                <img src="${productData.IMAGE || '/upload/no-image.jpg'}" alt="${productData.NAME}">
            </div>
            <div class="floating-product-name">${productData.NAME}</div>
            <div class="floating-product-price">${this.formatPrice(productData.PRICE)}</div>
        `;
        
        // Добавляем стили
        div.style.cssText = `
            background: white;
            border-radius: 12px;
            padding: 10px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.2);
            cursor: grab;
            transition: transform 0.2s;
            width: 100px;
            text-align: center;
            pointer-events: auto;
        `;
        
        div.querySelector('.floating-product-image').style.cssText = `
            width: 80px;
            height: 80px;
            margin: 0 auto;
            overflow: hidden;
            border-radius: 8px;
        `;
        
        div.querySelector('img').style.cssText = `
            width: 100%;
            height: 100%;
            object-fit: contain;
            background: transparent;
        `;
        
        div.querySelector('.floating-product-name').style.cssText = `
            font-size: 12px;
            margin-top: 5px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        `;
        
        div.querySelector('.floating-product-price').style.cssText = `
            color: #e74c3c;
            font-weight: bold;
            font-size: 12px;
            margin-top: 3px;
        `;
        
        // Добавляем обработчики drag
        div.setAttribute('draggable', 'true');
        div.addEventListener('dragstart', (e) => {
            e.dataTransfer.setData('text/plain', productData.PRODUCT_ID);
            div.style.opacity = '0.5';
        });
        
        div.addEventListener('dragend', (e) => {
            div.style.opacity = '1';
        });
        
        return div;
    }
    
    animateFall(element, productData) {
        let startTime = null;
        const duration = 800;
        const startTop = -120;
        const endTop = window.innerHeight - 150;
        const startRotation = Math.random() * 20 - 10;
        
        function fallAnimation(timestamp) {
            if (!startTime) startTime = timestamp;
            const elapsed = timestamp - startTime;
            const progress = Math.min(elapsed / duration, 1);
            
            // Физика падения с ускорением
            const easeInQuad = progress * progress;
            const currentTop = startTop + (endTop - startTop) * easeInQuad;
            const currentRotation = startRotation * (1 - progress);
            
            element.style.top = `${currentTop}px`;
            element.style.transform = `rotate(${currentRotation}deg)`;
            
            if (progress < 1) {
                requestAnimationFrame(fallAnimation);
            } else {
                this.checkLanding(element, productData);
            }
        }
        
        requestAnimationFrame(fallAnimation);
    }
    
    checkLanding(element, productData) {
        const cartRect = document.getElementById('cart-container').getBoundingClientRect();
        const productRect = element.getBoundingClientRect();
        
        // Проверка пересечения с корзиной
        if (this.isIntersecting(productRect, cartRect)) {
            this.catchProductDirect(productData, element);
        } else {
            this.startFallout(productData, element);
        }
    }
    
    isIntersecting(rect1, rect2) {
        return !(rect1.right < rect2.left || 
                 rect1.left > rect2.right || 
                 rect1.bottom < rect2.top || 
                 rect1.top > rect2.bottom);
    }
    
    catchProduct(productId, x, y) {
        const product = this.activeProducts.find(p => p.data.PRODUCT_ID == productId);
        if (product && !this.falloutTimeouts.has(productId)) {
            this.catchProductDirect(product.data, product.element);
        }
    }
    
    catchProductDirect(productData, element) {
        // Удаляем из активных
        const index = this.activeProducts.findIndex(p => p.element === element);
        if (index !== -1) this.activeProducts.splice(index, 1);
        
        // Отменяем таймер выпадения если есть
        if (this.falloutTimeouts.has(productData.PRODUCT_ID)) {
            clearTimeout(this.falloutTimeouts.get(productData.PRODUCT_ID));
            this.falloutTimeouts.delete(productData.PRODUCT_ID);
        }
        
        // Удаляем призрака если есть
        if (this.ghostElements.has(productData.PRODUCT_ID)) {
            this.ghostElements.get(productData.PRODUCT_ID).remove();
            this.ghostElements.delete(productData.PRODUCT_ID);
        }
        
        // Анимация попадания
        this.catchAnimation(element, () => {
            element.remove();
            
            // Добавляем в корзину
            this.addToCart(productData);
            
            // Геймификация
            if (window.gamification) {
                window.gamification.onSuccessfulCatch(productData);
            }
            
            // Визуальный эффект
            this.showCatchEffect();
        });
    }
    
    catchAnimation(element, callback) {
        element.style.transition = 'all 0.3s ease';
        element.style.transform = 'scale(0.5) rotate(0deg)';
        element.style.opacity = '0';
        
        setTimeout(() => {
            if (callback) callback();
        }, 300);
    }
    
    startFallout(productData, element) {
        // Показываем предупреждение
        this.showFalloutWarning(productData);
        
        // Создаем призрака
        const ghost = this.createGhost(element);
        this.ghostElements.set(productData.PRODUCT_ID, ghost);
        
        // Таймер на 5 секунд
        const timeoutId = setTimeout(() => {
            this.removeProductPermanently(productData, element, ghost);
        }, 5000);
        
        this.falloutTimeouts.set(productData.PRODUCT_ID, timeoutId);
        
        // Добавляем кнопку отмены
        this.addCancelButton(productData, element, ghost);
    }
    
    createGhost(element) {
        const ghost = element.cloneNode(true);
        ghost.classList.add('ghost-product');
        ghost.style.cssText = `
            position: fixed;
            top: ${element.style.top};
            left: ${element.style.left};
            opacity: 0.6;
            filter: blur(2px) grayscale(0.5);
            pointer-events: none;
            z-index: 999;
            animation: ghostPulse 1s ease infinite;
        `;
        ghost.style.transform = element.style.transform;
        
        document.body.appendChild(ghost);
        return ghost;
    }
    
    addCancelButton(productData, element, ghost) {
        const cancelBtn = document.createElement('div');
        cancelBtn.className = 'fallout-cancel-btn';
        cancelBtn.innerHTML = '↺ Отменить';
        cancelBtn.style.cssText = `
            position: fixed;
            top: ${parseFloat(element.style.top) + 50}px;
            left: ${parseFloat(element.style.left) - 30}px;
            background: #e74c3c;
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            cursor: pointer;
            z-index: 1001;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
            transition: all 0.3s;
            animation: slideIn 0.3s ease;
        `;
        
        cancelBtn.onclick = () => {
            this.cancelFallout(productData, element, ghost, cancelBtn);
        };
        
        document.body.appendChild(cancelBtn);
        
        // Сохраняем кнопку
        this.ghostElements.set(productData.PRODUCT_ID + '_btn', cancelBtn);
    }
    
    cancelFallout(productData, element, ghost, cancelBtn) {
        // Очищаем таймер
        clearTimeout(this.falloutTimeouts.get(productData.PRODUCT_ID));
        this.falloutTimeouts.delete(productData.PRODUCT_ID);
        
        // Удаляем призрака и кнопку
        if (ghost) ghost.remove();
        if (cancelBtn) cancelBtn.remove();
        this.ghostElements.delete(productData.PRODUCT_ID);
        this.ghostElements.delete(productData.PRODUCT_ID + '_btn');
        
        // Анимация воскрешения
        this.resurrectionAnimation(element, () => {
            // Возвращаем товар в корзину
            this.returnToCart(productData, element);
        });
    }
    
    resurrectionAnimation(element, callback) {
        element.style.transition = 'all 0.5s ease';
        element.style.filter = 'brightness(1.5) drop-shadow(0 0 10px gold)';
        
        // Поднимаем вверх
        const startTop = parseFloat(element.style.top);
        const endTop = -120;
        
        this.animateRise(element, startTop, endTop, 500, () => {
            element.style.filter = '';
            if (callback) callback();
        });
    }
    
    animateRise(element, start, end, duration, callback) {
        const startTime = performance.now();
        
        function rise(currentTime) {
            const elapsed = currentTime - startTime;
            const progress = Math.min(elapsed / duration, 1);
            const easeOutCubic = 1 - Math.pow(1 - progress, 3);
            const currentTop = start - (start - end) * easeOutCubic;
            
            element.style.top = `${currentTop}px`;
            
            if (progress < 1) {
                requestAnimationFrame(rise);
            } else {
                if (callback) callback();
            }
        }
        
        requestAnimationFrame(rise);
    }
    
    returnToCart(productData, element) {
        // Анимация падения в корзину
        const cartRect = document.getElementById('cart-container').getBoundingClientRect();
        const startRect = element.getBoundingClientRect();
        
        element.style.transition = 'all 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55)';
        element.style.left = `${cartRect.left + cartRect.width / 2}px`;
        element.style.top = `${cartRect.top + cartRect.height / 2}px`;
        element.style.transform = 'scale(0.2) rotate(360deg)';
        element.style.opacity = '0';
        
        setTimeout(() => {
            element.remove();
            this.addToCart(productData);
            
            // Уведомление
            this.showNotification(`Товар "${productData.NAME}" возвращен!`, 'success');
        }, 500);
    }
    
    removeProductPermanently(productData, element, ghost) {
        // Удаляем из корзины
        this.removeFromCart(productData.PRODUCT_ID);
        
        // Удаляем элементы
        element.remove();
        if (ghost) ghost.remove();
        
        // Удаляем кнопку отмены
        const cancelBtn = this.ghostElements.get(productData.PRODUCT_ID + '_btn');
        if (cancelBtn) cancelBtn.remove();
        
        this.falloutTimeouts.delete(productData.PRODUCT_ID);
        this.ghostElements.delete(productData.PRODUCT_ID);
        this.ghostElements.delete(productData.PRODUCT_ID + '_btn');
        
        // Показываем уведомление
        this.showNotification(`Товар "${productData.NAME}" исчез из корзины`, 'error');
    }
    
    addToCart(productData) {
        // Отправляем запрос на добавление в корзину
        fetch('/ajax/add_to_cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=add&product_id=${productData.PRODUCT_ID}&quantity=1`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Обновляем отображение корзины
                loadCart();
                
                // Обновляем счетчик в шапке
                if (window.updateCartCounter) {
                    window.updateCartCounter();
                }
                
                // Сбрасываем стабильность
                if (window.stabilitySystem) {
                    window.stabilitySystem.resetStability();
                }
            }
        });
    }
    
    removeFromCart(productId) {
        fetch('/ajax/add_to_cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=remove&product_id=${productId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                loadCart();
                if (window.updateCartCounter) {
                    window.updateCartCounter();
                }
            }
        });
    }
    
    showFalloutWarning(productData) {
        const warning = document.createElement('div');
        warning.className = 'fallout-warning';
        warning.innerHTML = `
            <div class="warning-content">
                <span class="warning-icon">⚠️</span>
                <span>Товар "${productData.NAME}" выпал из корзины!</span>
                <span class="warning-timer">5 сек</span>
            </div>
        `;
        
        warning.style.cssText = `
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: rgba(231,76,60,0.95);
            color: white;
            padding: 12px 20px;
            border-radius: 10px;
            z-index: 10000;
            animation: slideIn 0.3s ease;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        `;
        
        document.body.appendChild(warning);
        
        // Таймер для удаления предупреждения
        setTimeout(() => {
            if (warning) warning.remove();
        }, 4000);
    }
    
    showCatchEffect() {
        const effect = document.createElement('div');
        effect.innerHTML = '✨';
        effect.style.cssText = `
            position: fixed;
            left: 50%;
            top: 50%;
            font-size: 50px;
            transform: translate(-50%, -50%);
            animation: comboAnimation 0.5s ease-out forwards;
            pointer-events: none;
            z-index: 10000;
        `;
        
        document.body.appendChild(effect);
        
        setTimeout(() => effect.remove(), 500);
    }
    
    showNotification(message, type) {
        const notification = document.createElement('div');
        notification.className = 'cart-notification';
        notification.textContent = message;
        notification.style.background = type === 'success' ? '#27ae60' : '#e74c3c';
        
        document.body.appendChild(notification);
        
        setTimeout(() => notification.remove(), 3000);
    }
    
    formatPrice(price) {
        if (!price) return '0 ₽';
        return new Intl.NumberFormat('ru-RU').format(price) + ' ₽';
    }
    
    playSound(type) {
        // Опционально: добавить звуки
        // Можно использовать Web Audio API
    }
    
    initTouchEvents() {
        // Для мобильных - отключаем drag-and-drop, используем свайпы
        console.log('Mobile mode: using swipe gestures');
    }
}

// gamification.js
class Gamification {
    constructor() {
        this.score = 0;
        this.combo = 0;
        this.lastCatchTime = 0;
        this.maxCombo = 0;
        this.totalCatches = 0;
        this.loadScore();
        this.init();
    }
    
    init() {
        this.updateScoreDisplay();
        this.startComboResetTimer();
    }
    
    onSuccessfulCatch(productData) {
        const now = Date.now();
        
        // Комбо система
        if (now - this.lastCatchTime < 2000) {
            this.combo++;
            if (this.combo > this.maxCombo) {
                this.maxCombo = this.combo;
            }
            this.showComboEffect(this.combo);
        } else {
            this.combo = 1;
        }
        
        this.lastCatchTime = now;
        this.totalCatches++;
        
        // Начисление очков (базовые + бонус за комбо)
        const basePoints = 10;
        const comboBonus = Math.min(this.combo * 5, 50);
        const points = basePoints + comboBonus;
        
        this.score += points;
        
        // Сохраняем очки
        this.saveScore();
        
        // Показываем эффект очков
        this.showPointsEffect(points, productData);
        
        // Обновляем отображение
        this.updateScoreDisplay();
        
        // Визуальные эффекты
        this.showCatchEffect();
        
        // Проверка достижений
        this.checkAchievements();
        
        // Вибрация (если поддерживается)
        if (navigator.vibrate && this.combo > 2) {
            navigator.vibrate(100);
        }
        
        // Звук (опционально)
        this.playSound('catch');
    }
    
    showComboEffect(combo) {
        const comboElement = document.getElementById('combo-display');
        const comboCount = document.getElementById('combo-count');
        
        if (comboElement && comboCount) {
            comboElement.style.display = 'flex';
            comboCount.textContent = combo;
            
            // Анимация комбо
            comboElement.style.animation = 'none';
            setTimeout(() => {
                comboElement.style.animation = 'comboAnimation 0.5s ease';
            }, 10);
        }
        
        // Показываем всплывающий текст комбо
        const comboText = document.createElement('div');
        comboText.textContent = `x${combo} COMBO!`;
        comboText.style.cssText = `
            position: fixed;
            top: 30%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: ${Math.min(24 + combo * 2, 48)}px;
            font-weight: bold;
            color: gold;
            text-shadow: 0 0 10px orange, 0 0 20px red;
            z-index: 10000;
            animation: comboAnimation 0.8s ease-out forwards;
            pointer-events: none;
            white-space: nowrap;
        `;
        
        document.body.appendChild(comboText);
        
        setTimeout(() => comboText.remove(), 800);
    }
    
    showPointsEffect(points, productData) {
        const effect = document.createElement('div');
        effect.textContent = `+${points}`;
        effect.style.cssText = `
            position: fixed;
            left: ${Math.random() * 80 + 10}%;
            top: 40%;
            font-size: 24px;
            font-weight: bold;
            color: #f39c12;
            text-shadow: 0 0 5px orange;
            animation: pointsFloat 1s ease-out forwards;
            pointer-events: none;
            z-index: 10000;
        `;
        
        document.body.appendChild(effect);
        
        setTimeout(() => effect.remove(), 1000);
    }
    
    showCatchEffect() {
        // Эффект вспышки
        const flash = document.createElement('div');
        flash.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(circle, rgba(255,215,0,0.3) 0%, rgba(255,255,255,0) 70%);
            pointer-events: none;
            z-index: 9999;
            animation: flash 0.3s ease-out forwards;
        `;
        
        document.body.appendChild(flash);
        
        setTimeout(() => flash.remove(), 300);
    }
    
    updateScoreDisplay() {
        const scoreElement = document.getElementById('player-score');
        if (scoreElement) {
            scoreElement.textContent = this.score;
        }
    }
    
    saveScore() {
        localStorage.setItem('cart_game_score', this.score);
        localStorage.setItem('cart_game_max_combo', this.maxCombo);
        localStorage.setItem('cart_game_total_catches', this.totalCatches);
    }
    
    loadScore() {
        const savedScore = localStorage.getItem('cart_game_score');
        if (savedScore) {
            this.score = parseInt(savedScore);
        }
        
        const savedMaxCombo = localStorage.getItem('cart_game_max_combo');
        if (savedMaxCombo) {
            this.maxCombo = parseInt(savedMaxCombo);
        }
        
        const savedCatches = localStorage.getItem('cart_game_total_catches');
        if (savedCatches) {
            this.totalCatches = parseInt(savedCatches);
        }
    }
    
    startComboResetTimer() {
        // Сбрасываем комбо если 3 секунды не было catch
        setInterval(() => {
            const now = Date.now();
            if (now - this.lastCatchTime > 3000 && this.combo > 0) {
                this.combo = 0;
                const comboElement = document.getElementById('combo-display');
                if (comboElement) {
                    comboElement.style.display = 'none';
                }
            }
        }, 1000);
    }
    
    checkAchievements() {
        const achievements = {
            100: { name: 'Новичок', message: 'Набрано 100 очков!', icon: '🌟' },
            500: { name: 'Мастер', message: 'Набрано 500 очков!', icon: '🏆' },
            1000: { name: 'Легенда', message: 'Набрано 1000 очков!', icon: '👑' },
            5: { name: 'Комбо-старт', message: 'Комбо x5!', icon: '⚡' },
            10: { name: 'Комбо-мастер', message: 'Комбо x10!', icon: '💫' },
            50: { name: 'Охотник', message: 'Поймано 50 товаров!', icon: '🎯' }
        };
        
        // Проверка достижений по очкам
        for (const [threshold, achievement] of Object.entries(achievements)) {
            const numThreshold = parseInt(threshold);
            if (this.score >= numThreshold && !this.hasAchievement(achievement.name)) {
                this.unlockAchievement(achievement);
            }
        }
        
        // Проверка комбо достижений
        if (this.combo >= 5 && !this.hasAchievement('Комбо-старт')) {
            this.unlockAchievement(achievements[5]);
        }
        
        if (this.combo >= 10 && !this.hasAchievement('Комбо-мастер')) {
            this.unlockAchievement(achievements[10]);
        }
        
        // Проверка количества пойманных товаров
        if (this.totalCatches >= 50 && !this.hasAchievement('Охотник')) {
            this.unlockAchievement(achievements[50]);
        }
    }
    
    hasAchievement(achievementName) {
        const unlocked = localStorage.getItem('cart_achievements') || '[]';
        const achievements = JSON.parse(unlocked);
        return achievements.includes(achievementName);
    }
    
    unlockAchievement(achievement) {
        const unlocked = localStorage.getItem('cart_achievements') || '[]';
        const achievements = JSON.parse(unlocked);
        achievements.push(achievement.name);
        localStorage.setItem('cart_achievements', JSON.stringify(achievements));
        
        this.showAchievementNotification(achievement);
    }
    
    showAchievementNotification(achievement) {
        const notification = document.createElement('div');
        notification.innerHTML = `
            <div style="display: flex; align-items: center; gap: 10px;">
                <span style="font-size: 30px;">${achievement.icon}</span>
                <div>
                    <div style="font-weight: bold;">Достижение разблокировано!</div>
                    <div>${achievement.message}</div>
                </div>
            </div>
        `;
        
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 25px;
            border-radius: 15px;
            z-index: 10001;
            animation: slideDown 0.5s ease, slideUp 0.5s ease 2.5s forwards;
            box-shadow: 0 5px 20px rgba(0,0,0,0.3);
            min-width: 300px;
            text-align: center;
        `;
        
        document.body.appendChild(notification);
        
        setTimeout(() => notification.remove(), 3000);
        
        // Звук достижения
        this.playSound('achievement');
    }
    
    playSound(type) {
        // Опционально: добавить звуки
        // Можно использовать Howler.js или Web Audio API
        if (typeof Audio !== 'undefined') {
            // Простой вариант - используем Web Audio для beep
            try {
                const audioContext = new (window.AudioContext || window.webkitAudioContext)();
                const oscillator = audioContext.createOscillator();
                const gainNode = audioContext.createGain();
                
                oscillator.connect(gainNode);
                gainNode.connect(audioContext.destination);
                
                if (type === 'catch') {
                    oscillator.frequency.value = 880;
                    gainNode.gain.value = 0.1;
                    oscillator.start();
                    gainNode.gain.exponentialRampToValueAtTime(0.00001, audioContext.currentTime + 0.3);
                    oscillator.stop(audioContext.currentTime + 0.3);
                } else if (type === 'achievement') {
                    oscillator.frequency.value = 1318.51; // E6
                    oscillator.start();
                    gainNode.gain.value = 0.2;
                    gainNode.gain.exponentialRampToValueAtTime(0.00001, audioContext.currentTime + 0.5);
                    oscillator.stop(audioContext.currentTime + 0.5);
                }
            } catch(e) {
                // Fallback - просто вибрация
                if (navigator.vibrate) navigator.vibrate(200);
            }
        }
    }
}

// Добавляем анимации в CSS
const style = document.createElement('style');
style.textContent = `
    @keyframes pointsFloat {
        0% {
            transform: translateY(0);
            opacity: 1;
        }
        100% {
            transform: translateY(-100px);
            opacity: 0;
        }
    }
    
    @keyframes flash {
        0% {
            opacity: 1;
        }
        100% {
            opacity: 0;
        }
    }
    
    @keyframes slideDown {
        from {
            transform: translateX(-50%) translateY(-100px);
            opacity: 0;
        }
        to {
            transform: translateX(-50%) translateY(0);
            opacity: 1;
        }
    }
    
    @keyframes slideUp {
        to {
            transform: translateX(-50%) translateY(-100px);
            opacity: 0;
        }
    }
    
    .floating-product {
        transition: all 0.3s ease;
    }
    
    .floating-product:hover {
        transform: scale(1.05);
    }
    
    .ghost-product {
        animation: ghostPulse 1s ease infinite;
    }
    
    @keyframes ghostPulse {
        0%, 100% { opacity: 0.6; filter: blur(2px); }
        50% { opacity: 0.3; filter: blur(4px); }
    }
`;

document.head.appendChild(style);

// stability-system.js
class StabilitySystem {
    constructor() {
        this.stabilityStartTime = null;
        this.isStable = false;
        this.STABILITY_REQUIRED = 5000; // 5 секунд
        this.checkInterval = null;
        this.lastCartChangeTime = null;
        this.stabilityProgress = 0;
        this.init();
    }
    
    init() {
        this.startMonitoring();
    }
    
    startMonitoring() {
        // Мониторим изменения корзины
        this.observeCartChanges();
        
        // Запускаем проверку стабильности
        this.startStabilityCheck();
    }
    
    startStabilityCheck() {
        if (this.checkInterval) {
            clearInterval(this.checkInterval);
        }
        
        this.checkInterval = setInterval(() => {
            if (!this.isStable && this.stabilityStartTime) {
                const elapsed = Date.now() - this.stabilityStartTime;
                const progress = Math.min((elapsed / this.STABILITY_REQUIRED) * 100, 100);
                this.stabilityProgress = progress;
                
                this.updateProgressBar(progress);
                this.updateTimer(elapsed);
                
                if (elapsed >= this.STABILITY_REQUIRED && !this.isStable) {
                    this.achieveStability();
                }
            }
        }, 50); // Обновляем каждые 50ms для плавности
    }
    
    observeCartChanges() {
        // Наблюдаем за изменениями в корзине
        const cartItems = document.getElementById('cart-items');
        if (cartItems) {
            const observer = new MutationObserver((mutations) => {
                this.resetStability();
            });
            
            observer.observe(cartItems, {
                childList: true,
                subtree: true,
                attributes: true
            });
        }
        
        // Также отслеживаем добавление/удаление товаров через глобальные события
        window.addEventListener('cartItemAdded', () => this.resetStability());
        window.addEventListener('cartItemRemoved', () => this.resetStability());
        window.addEventListener('cartItemUpdated', () => this.resetStability());
    }
    
    resetStability() {
        if (this.isStable) {
            this.isStable = false;
            this.disableCheckoutButton();
            this.showMessage('Стабильность нарушена! Удерживайте товары 5 секунд...', 'warning');
        }
        
        this.stabilityStartTime = Date.now();
        this.lastCartChangeTime = Date.now();
        this.stabilityProgress = 0;
        
        this.updateProgressBar(0);
        this.updateTimer(0);
        
        // Сбрасываем стили
        const container = document.getElementById('cart-container');
        if (container) {
            container.classList.remove('stable');
        }
        
        // Анимация тряски
        this.shakeCart();
    }
    
    achieveStability() {
        this.isStable = true;
        clearInterval(this.checkInterval);
        
        // Визуальные эффекты
        const container = document.getElementById('cart-container');
        if (container) {
            container.classList.add('stable');
            container.style.borderColor = '#27ae60';
            container.style.boxShadow = '0 0 20px rgba(39,174,96,0.3)';
        }
        
        // Обновляем прогресс-бар
        this.updateProgressBar(100);
        
        // Показываем эффект
        this.showStabilityEffect();
        
        // Активируем кнопку оформления
        this.enableCheckoutButton();
        
        // Сохраняем стабильное состояние
        this.saveStableCart();
        
        // Уведомление
        this.showMessage('✨ Корзина стабилизирована! Теперь можно оформить заказ ✨', 'success');
        
        // Звук стабилизации
        this.playSound('stability');
        
        // Запускаем эффект конфетти
        this.showConfetti();
    }
    
    updateProgressBar(progress) {
        const progressBar = document.getElementById('stability-progress');
        const timerElement = document.getElementById('stability-timer');
        
        if (progressBar) {
            progressBar.style.width = `${progress}%`;
            
            // Меняем цвет в зависимости от прогресса
            if (progress < 30) {
                progressBar.style.backgroundColor = '#e74c3c';
            } else if (progress < 70) {
                progressBar.style.backgroundColor = '#f39c12';
            } else {
                progressBar.style.backgroundColor = '#27ae60';
            }
        }
        
        if (timerElement) {
            const remaining = Math.max(0, (this.STABILITY_REQUIRED - (progress / 100 * this.STABILITY_REQUIRED)) / 1000);
            timerElement.textContent = `${remaining.toFixed(1)} сек`;
        }
        
        // Обновляем сообщение
        const messageElement = document.getElementById('stability-message');
        if (messageElement && progress < 100) {
            const remainingSeconds = ((this.STABILITY_REQUIRED - (progress / 100 * this.STABILITY_REQUIRED)) / 1000).toFixed(1);
            messageElement.innerHTML = `🔒 Удерживайте товары ${remainingSeconds} сек для оформления`;
        } else if (messageElement && progress >= 100) {
            messageElement.innerHTML = '✅ Корзина стабильна! Можно оформлять заказ';
        }
    }
    
    updateTimer(elapsed) {
        const timerElement = document.getElementById('stability-timer');
        if (timerElement && elapsed < this.STABILITY_REQUIRED) {
            const remaining = (this.STABILITY_REQUIRED - elapsed) / 1000;
            timerElement.textContent = `${remaining.toFixed(1)} сек`;
        } else if (timerElement) {
            timerElement.textContent = 'ГОТОВО!';
        }
    }
    
    showStabilityEffect() {
        // Создаем волновой эффект
        const cartContainer = document.getElementById('cart-container');
        if (cartContainer) {
            const ripple = document.createElement('div');
            ripple.style.cssText = `
                position: absolute;
                top: 50%;
                left: 50%;
                width: 0;
                height: 0;
                border-radius: 50%;
                background: radial-gradient(circle, rgba(39,174,96,0.3) 0%, rgba(39,174,96,0) 70%);
                transform: translate(-50%, -50%);
                animation: ripple 1s ease-out forwards;
                pointer-events: none;
                z-index: 100;
            `;
            
            cartContainer.style.position = 'relative';
            cartContainer.appendChild(ripple);
            
            setTimeout(() => ripple.remove(), 1000);
        }
    }
    
    showConfetti() {
        // Простой эффект конфетти
        const colors = ['#f39c12', '#e74c3c', '#27ae60', '#3498db', '#9b59b6'];
        
        for (let i = 0; i < 50; i++) {
            const confetti = document.createElement('div');
            confetti.style.cssText = `
                position: fixed;
                left: ${Math.random() * 100}%;
                top: -10px;
                width: ${Math.random() * 8 + 4}px;
                height: ${Math.random() * 8 + 4}px;
                background: ${colors[Math.floor(Math.random() * colors.length)]};
                transform: rotate(${Math.random() * 360}deg);
                animation: confettiFall ${Math.random() * 2 + 1}s linear forwards;
                pointer-events: none;
                z-index: 10000;
            `;
            
            document.body.appendChild(confetti);
            
            setTimeout(() => confetti.remove(), 2000);
        }
    }
    
    shakeCart() {
        const container = document.getElementById('cart-container');
        if (container) {
            container.classList.add('shake');
            setTimeout(() => {
                container.classList.remove('shake');
            }, 500);
        }
    }
    
    enableCheckoutButton() {
        const checkoutBtn = document.getElementById('checkoutBtn');
        const orderHint = document.getElementById('order-hint');
        
        if (checkoutBtn) {
            checkoutBtn.disabled = false;
            checkoutBtn.style.opacity = '1';
            checkoutBtn.style.cursor = 'pointer';
            
            // Анимация кнопки
            checkoutBtn.style.animation = 'pulse 0.5s ease 3';
        }
        
        if (orderHint) {
            orderHint.innerHTML = '✅ Заказ готов к оформлению!';
            orderHint.style.color = '#27ae60';
        }
    }
    
    disableCheckoutButton() {
        const checkoutBtn = document.getElementById('checkoutBtn');
        const orderHint = document.getElementById('order-hint');
        
        if (checkoutBtn) {
            checkoutBtn.disabled = true;
            checkoutBtn.style.opacity = '0.5';
            checkoutBtn.style.cursor = 'not-allowed';
            checkoutBtn.style.animation = 'none';
        }
        
        if (orderHint) {
            orderHint.innerHTML = '🔒 Удерживайте товары 5 секунд для активации кнопки';
            orderHint.style.color = '#999';
        }
    }
    
    showMessage(message, type) {
        const messageElement = document.getElementById('stability-message');
        if (messageElement) {
            messageElement.innerHTML = message;
            messageElement.style.color = type === 'success' ? '#27ae60' : '#e74c3c';
            
            setTimeout(() => {
                if (messageElement && !this.isStable) {
                    messageElement.style.color = '#999';
                }
            }, 3000);
        }
        
        // Также показываем уведомление
        this.showNotification(message, type);
    }
    
    showNotification(message, type) {
        const notification = document.createElement('div');
        notification.className = 'cart-notification';
        notification.textContent = message;
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: ${type === 'success' ? '#27ae60' : type === 'warning' ? '#f39c12' : '#e74c3c'};
            color: white;
            padding: 15px 25px;
            border-radius: 10px;
            z-index: 10000;
            animation: slideIn 0.3s ease;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        `;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.style.animation = 'slideOut 0.3s ease forwards';
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    }
    
    saveStableCart() {
        // Сохраняем стабильное состояние корзины
        const cartItems = this.getCartItems();
        localStorage.setItem('stable_cart', JSON.stringify({
            items: cartItems,
            timestamp: Date.now(),
            total: this.getCartTotal()
        }));
    }
    
    getCartItems() {
        const items = [];
        const cartRows = document.querySelectorAll('.cart-item');
        cartRows.forEach(row => {
            const productId = row.dataset.productId;
            const quantity = parseInt(row.querySelector('.quantity-input')?.value || 1);
            if (productId) {
                items.push({ productId, quantity });
            }
        });
        return items;
    }
    
    getCartTotal() {
        const totalElement = document.getElementById('cart-total');
        if (totalElement) {
            const total = parseInt(totalElement.textContent.replace(/[^\d]/g, '')) || 0;
            return total;
        }
        return 0;
    }
    
    playSound(type) {
        // Звук стабилизации
        if (type === 'stability') {
            try {
                const audioContext = new (window.AudioContext || window.webkitAudioContext)();
                const oscillator = audioContext.createOscillator();
                const gainNode = audioContext.createGain();
                
                oscillator.connect(gainNode);
                gainNode.connect(audioContext.destination);
                
                oscillator.frequency.value = 523.25; // C5
                gainNode.gain.value = 0.15;
                oscillator.start();
                
                // Создаем аккорд
                setTimeout(() => {
                    oscillator.frequency.value = 659.25; // E5
                }, 100);
                
                setTimeout(() => {
                    oscillator.frequency.value = 783.99; // G5
                }, 200);
                
                gainNode.gain.exponentialRampToValueAtTime(0.00001, audioContext.currentTime + 0.8);
                oscillator.stop(audioContext.currentTime + 0.8);
            } catch(e) {
                if (navigator.vibrate) navigator.vibrate([100, 50, 100]);
            }
        }
    }
}

// Добавляем анимации
const stabilityStyles = document.createElement('style');
stabilityStyles.textContent = `
    @keyframes ripple {
        0% {
            width: 0;
            height: 0;
            opacity: 0.8;
        }
        100% {
            width: 300px;
            height: 300px;
            opacity: 0;
        }
    }
    
    @keyframes confettiFall {
        0% {
            transform: translateY(0) rotate(0deg);
            opacity: 1;
        }
        100% {
            transform: translateY(100vh) rotate(360deg);
            opacity: 0;
        }
    }
    
    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
    
    @keyframes pulse {
        0% {
            transform: scale(1);
        }
        50% {
            transform: scale(1.05);
        }
        100% {
            transform: scale(1);
        }
    }
    
    .cart-container.stable {
        border-color: #27ae60 !important;
        transition: all 0.3s ease;
    }
`;

document.head.appendChild(stabilityStyles);

// main.js - Основная логика
let currentCartData = null;
let freeShippingThreshold = 3000; // Порог бесплатной доставки

// Загрузка корзины
function loadCart() {
    const cartContent = document.getElementById('cart-items');
    if (!cartContent) return;
    
    cartContent.innerHTML = '<div class="cart-loading">🎯 Загрузка корзины...</div>';
    
    fetch('/ajax/add_to_cart.php?action=get_full&t=' + Date.now())
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                currentCartData = data;
                renderCartItems(data);
                updateCartTotal(data.total);
                updateShippingProgress(data.total);
                
                // Обновляем стабильность
                if (window.stabilitySystem) {
                    window.stabilitySystem.resetStability();
                }
            } else {
                cartContent.innerHTML = '<div class="cart-empty">Корзина пуста</div>';
            }
        })
        .catch(error => {
            console.error('Ошибка:', error);
            cartContent.innerHTML = '<div class="cart-empty">Ошибка загрузки корзины</div>';
        });
}

// Отображение товаров в корзине
function renderCartItems(data) {
    const cartItems = document.getElementById('cart-items');
    
    if (!data.items || data.items.length === 0) {
        cartItems.innerHTML = `
            <div class="cart-empty">
                <div class="empty-cart-icon">🛒</div>
                <p>Ваша корзина пуста</p>
                <button class="btn-continue-shopping" onclick="continueShopping()">
                    Перейти в каталог
                </button>
            </div>
        `;
        return;
    }
    
    let html = '';
    data.items.forEach(item => {
        const price = parseInt(item.PRICE) || 0;
        const sum = price * item.QUANTITY;
        
        html += `
            <div class="cart-item" data-product-id="${item.PRODUCT_ID}" data-price="${price}" draggable="true">
                <div class="cart-item-image">
                    <img src="${item.IMAGE || '/upload/no-image.jpg'}" alt="${item.NAME}">
                </div>
                <div class="cart-item-info">
                    <div class="cart-item-name">${item.NAME}</div>
                    <div class="cart-item-price">${formatPrice(price)}</div>
                    <div class="cart-item-quantity">
                        <button class="quantity-btn" onclick="decrementQuantity(${item.PRODUCT_ID})">−</button>
                        <input type="number" class="quantity-input" value="${item.QUANTITY}" 
                               min="1" onchange="updateQuantity(${item.PRODUCT_ID}, this.value)">
                        <button class="quantity-btn" onclick="incrementQuantity(${item.PRODUCT_ID})">+</button>
                    </div>
                </div>
                <div class="cart-item-sum">${formatPrice(sum)}</div>
                <button class="btn-remove-item" onclick="removeFromCart(${item.PRODUCT_ID})">×</button>
            </div>
        `;
    });
    
    cartItems.innerHTML = html;
    
    // Добавляем обработчики drag-and-drop
    if (!isMobile) {
        initDragAndDrop();
    }
}

// Инициализация drag-and-drop
function initDragAndDrop() {
    const cartItems = document.querySelectorAll('.cart-item');
    const cartContainer = document.getElementById('cart-container');
    
    cartItems.forEach(item => {
        item.addEventListener('dragstart', handleDragStart);
        item.addEventListener('dragend', handleDragEnd);
        item.setAttribute('draggable', 'true');
    });
    
    if (cartContainer) {
        cartContainer.addEventListener('dragover', handleDragOver);
        cartContainer.addEventListener('drop', handleDrop);
    }
}

let draggedItem = null;

function handleDragStart(e) {
    draggedItem = this;
    e.dataTransfer.setData('text/plain', this.dataset.productId);
    this.style.opacity = '0.5';
}

function handleDragEnd(e) {
    if (draggedItem) {
        draggedItem.style.opacity = '';
        draggedItem = null;
    }
}

function handleDragOver(e) {
    e.preventDefault();
}

function handleDrop(e) {
    e.preventDefault();
    const productId = e.dataTransfer.getData('text/plain');
    
    if (productId && draggedItem) {
        // Товар выпал из корзины
        if (window.physicalCart) {
            const productData = currentCartData?.items.find(item => item.PRODUCT_ID == productId);
            if (productData) {
                window.physicalCart.startFallout(productData, draggedItem);
            }
        }
    }
}

// Обновление количества товара
function incrementQuantity(productId) {
    const input = document.querySelector(`.cart-item[data-product-id="${productId}"] .quantity-input`);
    if (input) {
        const newValue = parseInt(input.value) + 1;
        updateQuantity(productId, newValue);
    }
}

function decrementQuantity(productId) {
    const input = document.querySelector(`.cart-item[data-product-id="${productId}"] .quantity-input`);
    if (input) {
        const currentValue = parseInt(input.value);
        if (currentValue > 1) {
            const newValue = currentValue - 1;
            updateQuantity(productId, newValue);
        }
    }
}

function updateQuantity(productId, quantity) {
    const row = document.querySelector(`.cart-item[data-product-id="${productId}"]`);
    if (!row) return;
    
    // Блокируем кнопки на время обновления
    const buttons = row.querySelectorAll('button');
    buttons.forEach(btn => btn.disabled = true);
    const input = row.querySelector('.quantity-input');
    if (input) input.disabled = true;
    
    fetch('/ajax/add_to_cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=update&product_id=${productId}&quantity=${quantity}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            loadCart(); // Перезагружаем корзину
            if (window.updateCartCounter) {
                window.updateCartCounter();
            }
        }
    })
    .catch(error => {
        console.error('Ошибка:', error);
        showNotification('Ошибка при обновлении количества', 'error');
    })
    .finally(() => {
        buttons.forEach(btn => btn.disabled = false);
        if (input) input.disabled = false;
    });
}

function removeFromCart(productId) {
    if (!confirm('Удалить товар из корзины?')) return;
    
    fetch('/ajax/add_to_cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=remove&product_id=${productId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            loadCart();
            if (window.updateCartCounter) {
                window.updateCartCounter();
            }
            
            // Событие удаления
            window.dispatchEvent(new CustomEvent('cartItemRemoved', { detail: { productId } }));
        }
    });
}

function clearCart() {
    if (!confirm('Очистить корзину?')) return;
    
    fetch('/ajax/add_to_cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=clear'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            loadCart();
            if (window.updateCartCounter) {
                window.updateCartCounter();
            }
        }
    });
}

function updateCartTotal(total) {
    const totalElement = document.getElementById('cart-total');
    const finalTotalElement = document.getElementById('finalTotal');
    
    if (totalElement) {
        totalElement.textContent = formatPrice(total);
    }
    
    // Обновляем итоговую сумму с учетом доставки
    const deliverySelected = document.querySelector('input[name="delivery"]:checked');
    if (deliverySelected && finalTotalElement) {
        let finalTotal = total;
        if (deliverySelected.value === 'delivery') {
            finalTotal += 500;
        }
        finalTotalElement.textContent = formatPrice(finalTotal);
    } else if (finalTotalElement) {
        finalTotalElement.textContent = formatPrice(total);
    }
}

function updateShippingProgress(total) {
    const progressBar = document.getElementById('shipping-progress');
    const remainingElement = document.getElementById('shipping-remaining');
    const container = document.getElementById('shipping-progress-container');
    
    if (!container) return;
    
    if (total >= freeShippingThreshold) {
        // Бесплатная доставка достигнута
        progressBar.style.width = '100%';
        if (remainingElement) {
            remainingElement.textContent = 'Достигнуто! 🎉';
        }
        container.style.background = '#e8f5e9';
        showFreeShippingEffect();
    } else {
        const remaining = freeShippingThreshold - total;
        const progress = (total / freeShippingThreshold) * 100;
        
        progressBar.style.width = `${progress}%`;
        if (remainingElement) {
            remainingElement.textContent = `${formatPrice(remaining)}`;
        }
        container.style.background = '#fff3e0';
    }
}

function showFreeShippingEffect() {
    const effect = document.createElement('div');
    effect.innerHTML = '🚚 Бесплатная доставка активирована! 🎉';
    effect.style.cssText = `
        position: fixed;
        bottom: 20px;
        left: 50%;
        transform: translateX(-50%);
        background: linear-gradient(135deg, #27ae60, #2ecc71);
        color: white;
        padding: 15px 30px;
        border-radius: 50px;
        font-weight: bold;
        z-index: 10000;
        animation: slideUp 0.5s ease, slideDown 0.5s ease 2.5s forwards;
        box-shadow: 0 5px 20px rgba(0,0,0,0.2);
    `;
    
    document.body.appendChild(effect);
    
    setTimeout(() => effect.remove(), 3000);
}

function formatPrice(price) {
    if (!price) return '0 ₽';
    return new Intl.NumberFormat('ru-RU').format(price) + ' ₽';
}

// Продолжить покупки
function continueShopping() {
    // Сохраняем последнюю категорию или товар
    const lastCategory = localStorage.getItem('last_category') || '/catalog/';
    window.location.href = lastCategory;
}

// Определение города по IP
function detectCity() {
    const cityInput = document.getElementById('city');
    const detectionSpan = document.getElementById('city-detection');
    
    if (!cityInput) return;
    
    fetch('https://ipapi.co/json/')
        .then(response => response.json())
        .then(data => {
            if (data.city) {
                cityInput.value = data.city;
                if (detectionSpan) {
                    detectionSpan.innerHTML = `📍 Город определен: ${data.city}`;
                    detectionSpan.style.color = '#27ae60';
                }
                
                // Сохраняем город
                localStorage.setItem('user_city', data.city);
            } else {
                if (detectionSpan) {
                    detectionSpan.innerHTML = '🌍 Город не определен, укажите вручную';
                    detectionSpan.style.color = '#f39c12';
                }
            }
        })
        .catch(error => {
            console.error('Ошибка определения города:', error);
            if (detectionSpan) {
                detectionSpan.innerHTML = '🌍 Не удалось определить город';
                detectionSpan.style.color = '#e74c3c';
            }
        });
}

// Маска телефона
function initPhoneMask() {
    const phoneInput = document.getElementById('phone');
    if (!phoneInput) return;
    
    phoneInput.addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        
        if (value.length > 0) {
            let formatted = '+7';
            if (value.length > 1) {
                formatted += ' (' + value.substring(1, 4);
            }
            if (value.length >= 4) {
                formatted += ') ' + value.substring(4, 7);
            }
            if (value.length >= 7) {
                formatted += '-' + value.substring(7, 9);
            }
            if (value.length >= 9) {
                formatted += '-' + value.substring(9, 11);
            }
            e.target.value = formatted;
        }
    });
}

// Выбор доставки
function selectDelivery(type) {
    const addressGroup = document.getElementById('addressGroup');
    if (type === 'delivery') {
        addressGroup.style.display = 'block';
    } else {
        addressGroup.style.display = 'none';
    }
    
    // Обновляем итоговую сумму
    if (currentCartData) {
        updateCartTotal(currentCartData.total);
    }
}

// Отправка заказа
function submitOrder(event) {
    event.preventDefault();
    
    // Проверяем стабильность корзины
    if (window.stabilitySystem && !window.stabilitySystem.isStable) {
        showNotification('Подождите, пока корзина стабилизируется (5 секунд)!', 'warning');
        return;
    }
    
    // Собираем данные формы
    const lastName = document.getElementById('lastName')?.value.trim() || '';
    const firstName = document.getElementById('firstName')?.value.trim() || '';
    const phone = document.getElementById('phone')?.value.trim() || '';
    const email = document.getElementById('email')?.value.trim() || '';
    const city = document.getElementById('city')?.value.trim() || '';
    const delivery = document.querySelector('input[name="delivery"]:checked')?.value || 'pickup';
    const payment = document.querySelector('input[name="payment"]:checked')?.value || 'cash';
    const comment = document.getElementById('comment')?.value.trim() || '';
    const address = document.getElementById('address')?.value.trim() || '';
    
    // Валидация
    let hasErrors = false;
    
    if (!lastName) {
        showFieldError('lastName', 'Укажите фамилию');
        hasErrors = true;
    }
    
    if (!firstName) {
        showFieldError('firstName', 'Укажите имя');
        hasErrors = true;
    }
    
    if (!phone) {
        showFieldError('phone', 'Укажите телефон');
        hasErrors = true;
    } else {
        const cleanPhone = phone.replace(/\D/g, '');
        if (cleanPhone.length < 10) {
            showFieldError('phone', 'Некорректный номер телефона');
            hasErrors = true;
        }
    }
    
    if (!email) {
        showFieldError('email', 'Укажите email');
        hasErrors = true;
    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        showFieldError('email', 'Некорректный email');
        hasErrors = true;
    }
    
    if (delivery === 'delivery' && !address) {
        showFieldError('address', 'Укажите адрес доставки');
        hasErrors = true;
    }
    
    if (hasErrors) {
        showNotification('Заполните все обязательные поля', 'error');
        return;
    }
    
    // Отправляем заказ
    const checkoutBtn = document.getElementById('checkoutBtn');
    const originalText = checkoutBtn.innerHTML;
    checkoutBtn.innerHTML = '⏳ Оформление...';
    checkoutBtn.disabled = true;
    
    const cleanPhone = phone.replace(/\D/g, '');
    
    fetch('/ajax/create_order.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
            action: 'create',
            lastName: lastName,
            firstName: firstName,
            phone: cleanPhone,
            email: email,
            city: city,
            delivery: delivery,
            address: delivery === 'delivery' ? address : '',
            payment: payment,
            comment: comment,
            score: window.gamification?.score || 0
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Очищаем корзину
            fetch('/ajax/add_to_cart.php?action=clear', { method: 'POST' })
                .then(() => {
                    // Показываем эффект успеха
                    showSuccessEffect();
                    
                    // Перенаправляем на страницу успеха
                    setTimeout(() => {
                        window.location.href = '/personal/order/success.php?order_id=' + data.order_id;
                    }, 1500);
                });
        } else {
            showNotification(data.error || 'Ошибка оформления заказа', 'error');
            checkoutBtn.innerHTML = originalText;
            checkoutBtn.disabled = false;
        }
    })
    .catch(error => {
        console.error('Ошибка:', error);
        showNotification('Ошибка оформления заказа', 'error');
        checkoutBtn.innerHTML = originalText;
        checkoutBtn.disabled = false;
    });
}

function showFieldError(fieldId, message) {
    const field = document.getElementById(fieldId);
    const hint = document.getElementById(`${fieldId}Hint`);
    
    if (field) {
        field.classList.add('error');
        field.style.borderColor = '#e74c3c';
    }
    
    if (hint) {
        hint.textContent = message;
        hint.classList.add('show');
        
        setTimeout(() => {
            hint.classList.remove('show');
            if (field) {
                field.classList.remove('error');
                field.style.borderColor = '';
            }
        }, 3000);
    }
}

function showSuccessEffect() {
    // Эффект конфетти
    for (let i = 0; i < 100; i++) {
        const confetti = document.createElement('div');
        confetti.style.cssText = `
            position: fixed;
            left: ${Math.random() * 100}%;
            top: -10px;
            width: ${Math.random() * 10 + 5}px;
            height: ${Math.random() * 10 + 5}px;
            background: hsl(${Math.random() * 360}, 100%, 50%);
            transform: rotate(${Math.random() * 360}deg);
            animation: confettiFall ${Math.random() * 2 + 1}s linear forwards;
            pointer-events: none;
            z-index: 10000;
        `;
        
        document.body.appendChild(confetti);
        
        setTimeout(() => confetti.remove(), 2000);
    }
    
    // Показываем сообщение
    const message = document.createElement('div');
    message.innerHTML = '🎉 Заказ успешно оформлен! 🎉';
    message.style.cssText = `
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: linear-gradient(135deg, #27ae60, #2ecc71);
        color: white;
        padding: 20px 40px;
        border-radius: 15px;
        font-size: 24px;
        font-weight: bold;
        z-index: 10001;
        animation: slideUp 0.5s ease;
        box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        white-space: nowrap;
    `;
    
    document.body.appendChild(message);
    
    setTimeout(() => {
        message.style.animation = 'slideDown 0.5s ease forwards';
        setTimeout(() => message.remove(), 500);
    }, 2000);
}

function showNotification(message, type) {
    const notification = document.createElement('div');
    notification.className = 'cart-notification';
    notification.textContent = message;
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: ${type === 'success' ? '#27ae60' : type === 'warning' ? '#f39c12' : '#e74c3c'};
        color: white;
        padding: 15px 25px;
        border-radius: 10px;
        z-index: 10000;
        animation: slideIn 0.3s ease;
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s ease forwards';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

// Определение мобильного устройства
const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);

// Загрузка последних заказов
function loadRecentOrders() {
    const recentOrdersList = document.getElementById('recent-orders-list');
    if (!recentOrdersList) return;
    
    fetch('/ajax/get_recent_orders.php')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.orders && data.orders.length > 0) {
                renderRecentOrders(data.orders);
            } else {
                recentOrdersList.innerHTML = `
                    <div class="recent-orders-empty">
                        📭 У вас пока нет заказов
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Ошибка загрузки заказов:', error);
            recentOrdersList.innerHTML = `
                <div class="recent-orders-empty">
                    ⚠️ Не удалось загрузить заказы
                </div>
            `;
        });
}

function renderRecentOrders(orders) {
    const recentOrdersList = document.getElementById('recent-orders-list');
    
    let html = '';
    orders.slice(0, 3).forEach(order => {
        const date = new Date(order.CREATED_AT).toLocaleDateString('ru-RU', {
            day: 'numeric',
            month: 'long',
            year: 'numeric'
        });
        
        const statusClass = getStatusClass(order.STATUS);
        const statusText = getStatusText(order.STATUS);
        
        html += `
            <div class="recent-order-card">
                <div class="order-number">Заказ №${order.ORDER_NUMBER || order.ID}</div>
                <div class="order-date">${date}</div>
                <span class="order-status ${statusClass}">${statusText}</span>
                <div class="order-total">${formatPrice(order.TOTAL_PRICE)}</div>
                <button class="repeat-order-btn" onclick="repeatOrder(${order.ID})">
                    🔄 Повторить заказ
                </button>
            </div>
        `;
    });
    
    recentOrdersList.innerHTML = html;
}

function repeatOrder(orderId) {
    fetch('/ajax/repeat_order.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `order_id=${orderId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Товары добавлены в корзину!', 'success');
            loadCart();
        } else {
            showNotification('Ошибка при повторе заказа', 'error');
        }
    });
}

function getStatusClass(status) {
    const classes = {
        'new': 'status-new',
        'processing': 'status-processing',
        'delivered': 'status-delivered',
        'cancelled': 'status-cancelled'
    };
    return classes[status] || 'status-new';
}

function getStatusText(status) {
    const texts = {
        'new': 'Новый',
        'processing': 'В обработке',
        'delivered': 'Доставлен',
        'cancelled': 'Отменён'
    };
    return texts[status] || 'Новый';
}

// Загрузка данных пользователя
function loadUserData() {
    fetch('/ajax/get_user_data.php')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.user) {
                if (data.user.NAME) {
                    const nameParts = data.user.NAME.split(' ');
                    if (nameParts.length >= 2) {
                        document.getElementById('firstName').value = nameParts[1] || '';
                        document.getElementById('lastName').value = nameParts[0] || '';
                    } else {
                        document.getElementById('firstName').value = data.user.NAME;
                    }
                }
                
                if (data.user.EMAIL) {
                    document.getElementById('email').value = data.user.EMAIL;
                }
                
                if (data.user.PERSONAL_PHONE) {
                    const phone = data.user.PERSONAL_PHONE;
                    let formatted = '+7';
                    if (phone.length > 1) {
                        formatted += ' (' + phone.substring(0, 3);
                    }
                    if (phone.length >= 3) {
                        formatted += ') ' + phone.substring(3, 6);
                    }
                    if (phone.length >= 6) {
                        formatted += '-' + phone.substring(6, 8);
                    }
                    if (phone.length >= 8) {
                        formatted += '-' + phone.substring(8, 10);
                    }
                    document.getElementById('phone').value = formatted;
                }
            }
        })
        .catch(error => console.error('Ошибка загрузки данных пользователя:', error));
}

// Инициализация при загрузке
document.addEventListener('DOMContentLoaded', function() {
    loadCart();
    loadRecentOrders();
    loadUserData();
    detectCity();
    initPhoneMask();
    
    // Обновляем итоговую сумму при выборе доставки
    const deliveryRadios = document.querySelectorAll('input[name="delivery"]');
    deliveryRadios.forEach(radio => {
        radio.addEventListener('change', () => {
            if (currentCartData) {
                updateCartTotal(currentCartData.total);
            }
        });
    });
});
</script>

<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
?>