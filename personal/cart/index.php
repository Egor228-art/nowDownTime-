<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

$APPLICATION->SetTitle("Физическая корзина");
?>

<?if (!$USER->IsAuthorized()):?>
    <style>
        body {
            margin-top: 0 !important;
            padding-top: 26px !important;
        }
    </style>
    <?endif?>

    <?if ($USER->IsAuthorized()):?>
    <style>
        body {
            margin-top: 128px !important;
            padding-top: 0 !important;
        }
    </style>
    <?endif?>

<div class="three-column-layout">
    <!-- ЛЕВЫЙ СЕГМЕНТ: Фиксированная корзина -->
    <div class="left-segment">
        <div class="basket" id="basket">
            <div class="basket-container">
                <!-- Декоративные элементы корзины (палки) -->
                <div class="base basket-rod-left"></div>
                <div class="base basket-rod-right"></div>
                <div class="base basket-rod-bottom"></div>
                <div class="base basket-rod-center-g1"></div>
                <div class="base basket-rod-center-g2"></div>
                <div class="base basket-rod-center"></div>
                <div class="base basket-rod-center-v1"></div>
                <div class="base basket-rod-center-v2"></div>
                <div class="base basket-rod-center-v3"></div>
                <div class="base basket-rod-center-v4"></div>
                
                <!-- Физическая область -->
                <div id="physicsArea" class="basket-physics-area"></div>
            </div>
            
            <div class="basket-info">
                <div class="info-item"><span>💰 Итого:</span><span id="totalSumDisplay">0 ₽</span></div>
                <div class="info-item"><span>📦 Товаров:</span><span id="itemsCountDisplay">0</span></div>
                <div class="info-item"><span>🎁 Скидка:</span><span id="discountAmount">0 ₽</span></div>
            </div>
        </div>
    </div>
    
    <!-- ЦЕНТРАЛЬНЫЙ СЕГМЕНТ: Полоски и скроллер -->
    <div class="center-segment">
        <div class="cart-header">
            <h1>✨ Физическая корзина ✨</h1>
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
        
        <div class="progress-bars">
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
            
            <div class="discount-container" id="discount-container">
                <div class="discount-label">
                    <span>🏷️ Скидочный бонус</span>
                    <span id="discount-percent">0%</span>
                </div>
                <div class="discount-progress-bar">
                    <div class="discount-progress" id="discount-progress"></div>
                </div>
                <div class="discount-message" id="discount-message">
                    Добавьте товаров для скидки
                </div>
            </div>
        </div>
        
        <div class="products-scroll-container">
            <div class="scroll-header">
                <span>📦 Товары в корзине</span>
            </div>
            <div class="products-scroll" id="productsScroll">
                <div class="scroll-loading">✨ Добавьте товары из каталога ✨</div>
            </div>
        </div>
        
        <div class="recent-orders-section">
            <h3>📦 Ваши последние заказы</h3>
            <div id="recent-orders-list" class="recent-orders-grid">
                <div class="recent-orders-loading">Загрузка...</div>
            </div>
            <a href="/personal/orders/" class="view-all-link">Все заказы →</a>
        </div>
    </div>
    
    <!-- ПРАВЫЙ СЕГМЕНТ: Свиток -->
    <div class="right-segment">
        <div class="order-scroll">
            <div class="scroll-paper">
                <div class="scroll-seal"></div>
                <div class="scroll-content">
                    <h2>📜 Оформление заказа</h2>
                    
                    <form id="orderForm" onsubmit="submitOrder(event)">
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
                        
                        <div class="form-section">
                            <h3>💭 Комментарий</h3>
                            <textarea id="comment" rows="2" placeholder="Дополнительная информация..."></textarea>
                        </div>
                        
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
</div>

<style>
    /* ========== ОСНОВНЫЕ СТИЛИ ========== */
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        background: #f5f5f5;
        min-height: 100vh;
        overflow-x: hidden;
    }

    .three-column-layout {
        display: flex;
        min-height: 100vh;
        width: 100%;
        position: relative;
    }

    /* ЛЕВЫЙ СЕГМЕНТ */
    .left-segment {
        position: fixed;
        left: 20px;
        bottom: 20px;
        width: 480px;
        z-index: 100;
    }

    /* ЦЕНТРАЛЬНЫЙ СЕГМЕНТ */
    .center-segment {
        flex: 1;
        margin-left: 520px;
        margin-right: 420px;
        padding: 20px 30px;
        min-height: 100vh;
    }

    /* ПРАВЫЙ СЕГМЕНТ */
    .right-segment {
        position: absolute;
        right: 20px;
        top: 20px;
        width: 400px;
        max-height: calc(100vh - 40px);
        overflow-y: auto;
        z-index: 90;
    }

    /* КОРЗИНА */
    .basket {
        position: relative;
        width: 480px;
        height: 319px;
        backdrop-filter: blur(2px);
    }

    .basket-container {
        position: relative;
        width: 100%;
        height: 100%;
        pointer-events: none;
    }

    /* Декоративные палки */
    .base {
        position: absolute;
        border-radius: 2px;
        z-index: 5;
        pointer-events: none;
    }

    .basket-rod-left {
        height: 283px;
        left: 7%;
        top: 6%;
        width: 12px;
        background: rgba(0, 0, 0, 1);
        transform: rotate(-10deg);
    }

    .basket-rod-right {
        height: 283px;
        right: 7%;
        top: 6%;
        width: 12px;
        background: rgba(0, 0, 0, 1);
        transform: rotate(10deg);
    }

    .basket-rod-bottom {
        bottom: 5%;
        left: 12%;
        right: 12%;
        height: 12px;
        background: rgba(0, 0, 0, 1);
    }

    .basket-rod-center-g1 {
        width: 88%;
        top: 31%;
        left: 6%;
        height: 8px;
        background: rgba(0, 0, 0, 0.7);
    }

    .basket-rod-center-g2 {
        width: 80%;
        bottom: 28%;
        left: 10%;
        height: 7px;
        background: rgba(0, 0, 0, 0.7);
    }

    .basket-rod-center {
        width: 96%;
        top: 6%;
        left: 2%;
        height: 12px;
        background: rgba(0, 0, 0, 0.7);
        border-radius: 16px;
    }

    .basket-rod-center-v1, .basket-rod-center-v2, 
    .basket-rod-center-v3, .basket-rod-center-v4 {
        height: 280px;
        top: 7%;
        width: 6px;
        background: rgba(0, 0, 0, 0.7);
    }

    .basket-rod-center-v1 { right: 20%; }
    .basket-rod-center-v2 { right: 39%; }
    .basket-rod-center-v3 { right: 58%; }
    .basket-rod-center-v4 { right: 77%; }

    .basket-physics-area {
        position: absolute;
        left: 10%;
        right: 10%;
        top: 8%;
        bottom: 8%;
        z-index: 10;
        pointer-events: none;
        border-radius: 20px;
    }

    .basket-info {
        position: absolute;
        bottom: -50px;
        left: 0;
        right: 0;
        background: rgba(0,0,0,0.85);
        backdrop-filter: blur(8px);
        border-radius: 12px;
        padding: 8px 15px;
        display: flex;
        justify-content: space-between;
        color: white;
        font-size: 12px;
        border: 1px solid rgba(255,255,255,0.2);
        pointer-events: none;
    }

    .info-item span:first-child {
        color: rgba(255,255,255,0.6);
    }

    .info-item span:last-child {
        color: #f39c12;
        font-weight: bold;
        font-size: 14px;
    }

    /* ШАПКА */
    .cart-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
        padding: 15px 20px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 15px;
        color: white;
    }

    .cart-header h1 {
        margin: 0;
        font-size: 24px;
    }

    .cart-stats {
        display: flex;
        gap: 15px;
    }

    .score-display, .combo-display {
        background: rgba(255,255,255,0.2);
        padding: 6px 12px;
        border-radius: 20px;
        font-weight: bold;
        backdrop-filter: blur(5px);
        font-size: 14px;
    }

    /* ПРОГРЕСС-БАРЫ */
    .progress-bars {
        display: flex;
        flex-direction: column;
        gap: 12px;
        margin-bottom: 25px;
    }

    .stability-container, .discount-container {
        background: white;
        border-radius: 12px;
        padding: 12px 15px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }

    .stability-label, .discount-label {
        display: flex;
        justify-content: space-between;
        margin-bottom: 8px;
        font-size: 13px;
        color: #666;
        font-weight: 500;
    }

    .stability-progress-bar, .discount-progress-bar {
        height: 8px;
        background: #e0e0e0;
        border-radius: 10px;
        overflow: hidden;
    }

    .stability-progress {
        height: 100%;
        background: linear-gradient(90deg, #f39c12, #27ae60);
        width: 0%;
        transition: width 0.05s linear;
        border-radius: 10px;
    }

    .discount-progress {
        height: 100%;
        background: linear-gradient(90deg, #e74c3c, #f39c12);
        width: 0%;
        transition: width 0.3s ease;
        border-radius: 10px;
    }

    .stability-message, .discount-message {
        font-size: 11px;
        color: #999;
        text-align: center;
        margin-top: 8px;
    }

    /* СКРОЛЛЕР ТОВАРОВ */
    .products-scroll-container {
        background: white;
        border-radius: 16px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        overflow: hidden;
        margin-bottom: 25px;
    }

    .scroll-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 15px;
        background: #f8f9fa;
        border-bottom: 1px solid #e9ecef;
    }

    .scroll-header span {
        font-weight: 600;
        color: #333;
        font-size: 14px;
    }

    .products-scroll {
        overflow-x: auto;
        overflow-y: hidden;
        white-space: nowrap;
        padding: 15px;
        min-height: 130px;
        scroll-behavior: smooth;
    }

    .products-scroll::-webkit-scrollbar {
        height: 6px;
    }

    .products-scroll::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }

    .products-scroll::-webkit-scrollbar-thumb {
        background: #f39c12;
        border-radius: 10px;
    }

    .scroll-item {
        display: inline-block;
        width: 140px;
        margin-right: 12px;
        background: #f8f9fa;
        border-radius: 12px;
        padding: 10px;
        text-align: center;
        transition: all 0.3s;
        border: 2px solid transparent;
        vertical-align: top;
        white-space: normal;
    }

    .scroll-item:hover {
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        border-color: #f39c12;
    }

    .scroll-item-image {
        width: 70px;
        height: 70px;
        margin: 0 auto 8px;
        background: white;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }

    .scroll-item-image img {
        width: 100%;
        height: 100%;
        object-fit: contain;
    }

    .scroll-item-name {
        font-size: 12px;
        font-weight: 500;
        color: #333;
        margin-bottom: 5px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .scroll-item-price {
        font-size: 12px;
        color: #e74c3c;
        font-weight: bold;
        margin-bottom: 8px;
    }

    .scroll-item-controls {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }

    .scroll-item-qty {
        width: 40px;
        text-align: center;
        border: 1px solid #ddd;
        border-radius: 6px;
        padding: 4px;
        font-size: 12px;
    }

    .scroll-item-btn {
        width: 26px;
        height: 26px;
        border: none;
        background: #f39c12;
        color: white;
        border-radius: 6px;
        cursor: pointer;
        font-size: 14px;
        font-weight: bold;
        transition: all 0.2s;
    }

    .scroll-item-btn:hover {
        background: #e67e22;
        transform: scale(1.05);
    }

    .scroll-item-btn.remove-btn {
        background: #e74c3c;
    }

    .scroll-item-btn.remove-btn:hover {
        background: #c0392b;
    }

    .scroll-loading {
        text-align: center;
        padding: 30px;
        color: #999;
    }

    /* ПОСЛЕДНИЕ ЗАКАЗЫ */
    .recent-orders-section {
        background: white;
        border-radius: 16px;
        padding: 20px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.08);
    }

    .recent-orders-section h3 {
        margin: 0 0 15px 0;
        color: #333;
        font-size: 16px;
    }

    .recent-orders-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 12px;
        margin-bottom: 15px;
        max-height: 280px;
        overflow-y: auto;
    }

    .recent-order-card {
        background: #f8f9fa;
        border-radius: 10px;
        padding: 12px;
        transition: all 0.3s;
        border-left: 3px solid #e74c3c;
    }

    .recent-order-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 3px 10px rgba(0,0,0,0.1);
    }

    .order-number {
        font-weight: bold;
        color: #333;
        margin-bottom: 5px;
        font-size: 13px;
    }

    .order-date {
        font-size: 11px;
        color: #999;
        margin-bottom: 6px;
    }

    .order-status {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 12px;
        font-size: 10px;
        font-weight: bold;
        margin-bottom: 6px;
    }

    .status-new { background: #3498db; color: white; }
    .status-processing { background: #f39c12; color: white; }
    .status-delivered { background: #27ae60; color: white; }
    .status-cancelled { background: #e74c3c; color: white; }

    .order-total {
        font-weight: bold;
        color: #e74c3c;
        font-size: 14px;
    }

    .repeat-order-btn {
        margin-top: 8px;
        padding: 4px 10px;
        background: #3498db;
        color: white;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-size: 11px;
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
        font-size: 13px;
        margin-top: 10px;
    }

    /* СВИТОК */
    .order-scroll {
        perspective: 1000px;
    }

    .scroll-paper {
        background: #fef7e0;
        background-image: repeating-linear-gradient(45deg, rgba(0,0,0,0.02) 0px, rgba(0,0,0,0.02) 2px, transparent 2px, transparent 8px);
        border-radius: 15px;
        box-shadow: 0 15px 35px rgba(0,0,0,0.2);
        padding: 20px;
        position: relative;
        border-left: 3px solid #e0cba0;
        border-right: 3px solid #e0cba0;
    }

    .scroll-seal {
        position: absolute;
        top: 15px;
        right: 15px;
        width: 45px;
        height: 45px;
        background: radial-gradient(circle, #e74c3c 30%, #c0392b 70%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        opacity: 0.8;
    }

    .scroll-seal::before {
        content: "✓";
        color: white;
        font-size: 24px;
        font-weight: bold;
    }

    .scroll-content h2 {
        text-align: center;
        color: #8b5a2b;
        margin-bottom: 15px;
        font-size: 20px;
    }

    .form-section {
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 1px dashed #e0cba0;
    }

    .form-section h3 {
        color: #8b5a2b;
        margin-bottom: 10px;
        font-size: 14px;
    }

    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
        margin-bottom: 10px;
    }

    .form-group {
        margin-bottom: 10px;
    }

    .form-group label {
        display: block;
        margin-bottom: 4px;
        color: #6b4c2c;
        font-size: 11px;
        font-weight: 500;
    }

    .form-group input, .form-group textarea, .form-group select {
        width: 100%;
        padding: 6px 8px;
        border: 1px solid #e0cba0;
        border-radius: 6px;
        background: rgba(255,255,255,0.8);
        font-size: 12px;
    }

    .form-group input:focus, .form-group textarea:focus {
        border-color: #e74c3c;
        outline: none;
    }

    .field-hint {
        font-size: 9px;
        color: #e74c3c;
        margin-top: 2px;
        display: none;
    }

    .field-hint.show {
        display: block;
    }

    .delivery-options, .payment-options {
        display: grid;
        gap: 6px;
        margin-bottom: 10px;
    }

    .delivery-option, .payment-option {
        display: flex;
        align-items: center;
        padding: 8px;
        border: 2px solid #e0cba0;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.3s;
        background: rgba(255,255,255,0.6);
    }

    .delivery-option:hover, .payment-option:hover {
        border-color: #e74c3c;
        background: white;
    }

    .delivery-option.selected, .payment-option.selected {
        border-color: #e74c3c;
        background: #fff3f0;
    }

    .delivery-option input, .payment-option input {
        margin-right: 8px;
        width: auto;
    }

    .delivery-info {
        flex: 1;
    }

    .delivery-name {
        font-weight: bold;
        color: #333;
        font-size: 12px;
    }

    .delivery-desc {
        font-size: 10px;
        color: #999;
    }

    .delivery-price {
        color: #e74c3c;
        font-weight: bold;
        font-size: 11px;
    }

    .btn-submit-order {
        width: 100%;
        padding: 10px;
        background: linear-gradient(135deg, #e74c3c, #c0392b);
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 13px;
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
        font-size: 10px;
        color: #999;
        margin-top: 8px;
    }

    .city-detection {
        font-size: 9px;
        color: #999;
        margin-top: 2px;
    }

    /* СТИЛИ ТОВАРОВ - ТОЛЬКО КАРТИНКА */
    .product-item {
        position: fixed;
        cursor: grab;
        z-index: 200;
        filter: drop-shadow(0 4px 8px rgba(0,0,0,0.2));
        pointer-events: auto;
        background: transparent;
        border: none;
        padding: 0;
        margin: 0;
        transition: opacity 0.2s, transform 0.2s;
    }

    .product-item:active {
        cursor: grabbing;
    }

    .product-item img {
        display: block;
        width: 100%;
        height: 100%;
        object-fit: contain;
        pointer-events: none;
        user-select: none;
        -webkit-user-drag: none;
    }

    /* АНИМАЦИИ */
    @keyframes comboAnimation {
        0% { transform: translate(-50%, -50%) scale(0); opacity: 1; }
        50% { transform: translate(-50%, -50%) scale(1.5); opacity: 1; }
        100% { transform: translate(-50%, -50%) scale(2); opacity: 0; }
    }

    @keyframes slideIn {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }

    @keyframes slideOut {
        from { transform: translateX(0); opacity: 1; }
        to { transform: translateX(100%); opacity: 0; }
    }

    @keyframes slideUp {
        from { transform: translateY(100px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }

    @keyframes slideDown {
        to { transform: translateY(100px); opacity: 0; }
    }

    @keyframes confettiFall {
        0% { transform: translateY(0) rotate(0deg); opacity: 1; }
        100% { transform: translateY(100vh) rotate(360deg); opacity: 0; }
    }

    @keyframes pointsFloat {
        0% { transform: translateY(0); opacity: 1; }
        100% { transform: translateY(-60px); opacity: 0; }
    }

    /* АДАПТИВНОСТЬ */
    @media (max-width: 1200px) {
        .center-segment {
            margin-right: 380px;
        }
        .right-segment {
            width: 360px;
        }
    }

    @media (max-width: 1024px) {
        .three-column-layout {
            flex-direction: column;
        }
        
        .left-segment {
            position: relative;
            left: auto;
            bottom: auto;
            margin: 20px auto;
            width: 480px;
        }
        
        .center-segment {
            margin-left: 0;
            margin-right: 0;
            order: 2;
        }
        
        .right-segment {
            position: relative;
            right: auto;
            top: auto;
            width: 100%;
            max-width: 500px;
            margin: 20px auto;
            order: 3;
        }
    }

    @media (max-width: 768px) {
        .left-segment {
            width: 100%;
            max-width: 480px;
        }
        
        .basket {
            width: 100%;
            height: 320px;
        }
        
        .center-segment {
            padding: 15px;
        }
        
        .cart-header {
            flex-direction: column;
            gap: 10px;
            text-align: center;
        }
        
        .form-row {
            grid-template-columns: 1fr;
        }
        
        .scroll-item {
            width: 120px;
        }
        
        .scroll-item-image {
            width: 60px;
            height: 60px;
        }
    }

    .cart-notification {
        position: fixed;
        top: 20px;
        left: 50%;
        transform: translateX(-50%);
        background: #27ae60;
        color: white;
        padding: 12px 24px;
        border-radius: 50px;
        z-index: 10000;
        animation: slideUp 0.3s ease;
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        font-size: 14px;
        white-space: nowrap;
    }

    .error {
        border-color: #e74c3c !important;
    }
</style>

<script>
// ========== ФИЗИКА С ТРАПЕЦИЕВИДНОЙ КОЛЛИЗИЕЙ ==========
const PHYSICS = {
    GRAVITY: 1,
    BOUNCE: 0.52,
    FRICTION: 0.96,
    COLLISION_ITER: 3
};

let items = [];
let animationId = null;
let draggingItem = null;
let dragOffsetX = 0, dragOffsetY = 0;
let isDragging = false;
let lastMousePos = { x: 0, y: 0 };
let lastMouseMoveDelta = null;

let totalPrice = 0;
let discountPercent = 0;
let discountAmount = 0;
let discountThresholds = [
    { min: 0, max: 1000, percent: 0 },
    { min: 1000, max: 3000, percent: 3 },
    { min: 3000, max: 5000, percent: 5 },
    { min: 5000, max: 10000, percent: 7 },
    { min: 10000, max: 20000, percent: 10 },
    { min: 20000, max: 50000, percent: 15 },
    { min: 50000, max: Infinity, percent: 20 }
];

const physicsContainer = document.getElementById('physicsArea');
const totalSpan = document.getElementById('totalSumDisplay');
const countSpan = document.getElementById('itemsCountDisplay');
const discountPercentSpan = document.getElementById('discount-percent');
const discountAmountSpan = document.getElementById('discountAmount');
const discountProgressBar = document.getElementById('discount-progress');

let screenBottom = 0;

// Получение позиций палок для трапециевидной коллизии
function getRodBounds() {
    const leftRod = document.querySelector('.basket-rod-left');
    const rightRod = document.querySelector('.basket-rod-right');
    const bottomRod = document.querySelector('.basket-rod-bottom');
    
    let leftPoints = null;
    let rightPoints = null;
    let bottomBounds = null;
    
    if (leftRod) {
        const leftRect = leftRod.getBoundingClientRect();
        const angle = -12 * Math.PI / 180;
        const cos = Math.cos(angle);
        const sin = Math.sin(angle);
        const centerX = leftRect.left + leftRect.width / 2;
        const centerY = leftRect.top + leftRect.height / 2;
        const halfH = leftRect.height / 2;
        
        leftPoints = {
            top: {
                x: centerX + halfH * sin,
                y: centerY - halfH * cos
            },
            bottom: {
                x: centerX - halfH * sin,
                y: centerY + halfH * cos
            }
        };
    }
    
    if (rightRod) {
        const rightRect = rightRod.getBoundingClientRect();
        const angle = 12.5 * Math.PI / 180;
        const cos = Math.cos(angle);
        const sin = Math.sin(angle);
        const centerX = rightRect.left + rightRect.width / 2;
        const centerY = rightRect.top + rightRect.height / 2;
        const halfH = rightRect.height / 2;
        
        rightPoints = {
            top: {
                x: centerX - halfH * sin,
                y: centerY - halfH * cos
            },
            bottom: {
                x: centerX + halfH * sin,
                y: centerY + halfH * cos
            }
        };
    }
    
    if (bottomRod) {
        bottomBounds = bottomRod.getBoundingClientRect();
    }
    
    return { leftPoints, rightPoints, bottomBounds };
}

// Проверка коллизии с трапецией
function checkCollisionWithTrapezoid(item) {
    if (isDragging && draggingItem === item) return false;
    
    const halfSize = item.size / 2;
    const itemCenterX = item.x;
    const itemCenterY = item.y;
    
    const { leftPoints, rightPoints, bottomBounds } = getRodBounds();
    
    if (!leftPoints || !rightPoints || !bottomBounds) return false;
    
    // Проверяем, находится ли товар ВНУТРИ зоны корзины по вертикали
    // Если товар выше верхней точки палок - не применяем коллизию
    if (itemCenterY - halfSize < leftPoints.top.y) {
        return false;
    }
    
    const t = Math.max(0, Math.min(1, (itemCenterY - leftPoints.top.y) / (leftPoints.bottom.y - leftPoints.top.y)));
    const leftBoundary = leftPoints.top.x + (leftPoints.bottom.x - leftPoints.top.x) * t;
    const rightBoundary = rightPoints.top.x + (rightPoints.bottom.x - rightPoints.top.x) * t;
    
    const itemLeft = itemCenterX - halfSize;
    const itemRight = itemCenterX + halfSize;
    const itemBottom = itemCenterY + halfSize;
    
    let collided = false;
    
    // Левая стенка - только если товар пересекает левую границу
    if (itemLeft < leftBoundary && itemRight > leftBoundary) {
        item.x = leftBoundary + halfSize;
        if (!isDragging) {
            item.vx = -Math.abs(item.vx) * PHYSICS.BOUNCE * 0.7;
        }
        collided = true;
    }
    
    // Правая стенка - только если товар пересекает правую границу
    if (itemRight > rightBoundary && itemLeft < rightBoundary) {
        item.x = rightBoundary - halfSize;
        if (!isDragging) {
            item.vx = Math.abs(item.vx) * PHYSICS.BOUNCE * 0.7;
        }
        collided = true;
    }
    
    // Дно - только если товар касается дна
    if (itemBottom > bottomBounds.top && itemCenterY - halfSize < bottomBounds.bottom) {
        item.y = bottomBounds.top - halfSize;
        if (!isDragging) {
            item.vy = -item.vy * PHYSICS.BOUNCE * 0.5;
            item.vx *= PHYSICS.FRICTION;
        }
        collided = true;
    }
    
    return collided;
}

// Проверка, находится ли товар в воздухе
function isItemInAir(item) {
    const { bottomBounds } = getRodBounds();
    if (!bottomBounds) return true;
    
    const halfSize = item.size / 2;
    const itemBottom = item.y + halfSize;
    
    // Считаем, что товар на дне, если он касается дна
    if (itemBottom >= bottomBounds.top - 2 && itemBottom <= bottomBounds.bottom + 2) {
        return false;
    }
    
    return true;
}

// Столкновение между товарами
function checkCollisionBetweenItems(a, b) {
    const halfA = a.size / 2;
    const halfB = b.size / 2;
    
    const aLeft = a.x - halfA;
    const aRight = a.x + halfA;
    const aTop = a.y - halfA;
    const aBottom = a.y + halfA;
    
    const bLeft = b.x - halfB;
    const bRight = b.x + halfB;
    const bTop = b.y - halfB;
    const bBottom = b.y + halfB;
    
    if (aRight > bLeft && aLeft < bRight && aBottom > bTop && aTop < bBottom) {
        const overlapLeft = aRight - bLeft;
        const overlapRight = bRight - aLeft;
        const overlapTop = aBottom - bTop;
        const overlapBottom = bBottom - aTop;
        
        const minOverlap = Math.min(overlapLeft, overlapRight, overlapTop, overlapBottom);
        
        if (minOverlap === overlapLeft || minOverlap === overlapRight) {
            const dx = a.x - b.x;
            const correction = minOverlap / 2;
            if (dx > 0) {
                a.x += correction;
                b.x -= correction;
            } else {
                a.x -= correction;
                b.x += correction;
            }
            
            const vrel = a.vx - b.vx;
            if (vrel * dx < 0) {
                const e = 0.5;
                const imp = (1 + e) * vrel / 2;
                a.vx -= imp;
                b.vx += imp;
            }
        } else {
            const dy = a.y - b.y;
            const correction = minOverlap / 2;
            if (dy > 0) {
                a.y += correction;
                b.y -= correction;
            } else {
                a.y -= correction;
                b.y += correction;
            }
            
            const vrel = a.vy - b.vy;
            if (vrel * dy < 0) {
                const e = 0.5;
                const imp = (1 + e) * vrel / 2;
                a.vy -= imp;
                b.vy += imp;
            }
        }
        
        a.vx *= 0.98;
        b.vx *= 0.98;
        
        return true;
    }
    return false;
}

function applyPhysics() {
    for (let i = 0; i < items.length; i++) {
        const item = items[i];
        
        if (item === draggingItem && isDragging) continue;
        
        const wasInAir = isItemInAir(item);
        
        item.vy += PHYSICS.GRAVITY;
        item.vx *= PHYSICS.FRICTION;
        item.vy *= PHYSICS.FRICTION;
        
        item.x += item.vx;
        item.y += item.vy;
        
        const halfSize = item.size / 2;
        
        // Проверка на вылет за нижнюю границу экрана
        if (item.y + halfSize > screenBottom) {
            removeItem(item);
            continue;
        }
        
        // Верхняя граница - только если товар уходит слишком высоко
        // Делаем мягкое ограничение, чтобы не было "невидимой стены"
        if (item.y - halfSize < 0) {
            item.y = halfSize;
            if (item.vy < 0) item.vy = -item.vy * 0.3; // Мягкий отскок
        }
        
        // Левая/правая граница экрана
        if (item.x - halfSize < 0) {
            item.x = halfSize;
            item.vx = -item.vx * 0.6;
        }
        if (item.x + halfSize > window.innerWidth) {
            item.x = window.innerWidth - halfSize;
            item.vx = -item.vx * 0.6;
        }
        
        // Проверка коллизии с трапецией (только внутри корзины)
        checkCollisionWithTrapezoid(item);
        
        const nowInAir = isItemInAir(item);
        if (nowInAir && !wasInAir && !item.justLanded && !isDragging) {
            window.dispatchEvent(new CustomEvent('cartItemMoved', { detail: { item: item } }));
        }
        
        item.justLanded = !nowInAir;
    }
    
    // Столкновения между товарами
    for (let iter = 0; iter < PHYSICS.COLLISION_ITER; iter++) {
        for (let i = 0; i < items.length; i++) {
            const a = items[i];
            if (a === draggingItem && isDragging) continue;
            for (let j = i + 1; j < items.length; j++) {
                const b = items[j];
                if (b === draggingItem && isDragging) continue;
                checkCollisionBetweenItems(a, b);
            }
        }
    }
    
    // Обновление DOM
    for (let item of items) {
        if (item.element && !item.pendingRemoval) {
            const visualWidth = parseFloat(item.element.style.width) || item.visualSize;
            const visualHeight = parseFloat(item.element.style.height) || item.visualSize;
            item.element.style.left = (item.x - visualWidth / 2) + 'px';
            item.element.style.top = (item.y - visualHeight / 2) + 'px';
        }
    }
    
    items = items.filter(item => !item.pendingRemoval);
}

function removeItem(item) {
    if (item.pendingRemoval) return;
    item.pendingRemoval = true;
    
    console.log('🗑️ Удаляем 1 товар:', item.productId, item.name);
    
    // Отправляем запрос на удаление 1 единицы
    fetch('/ajax/add_to_cart.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `action=remove&product_id=${item.productId}`
    })
    .then(response => response.json())
    .then(data => {
        console.log('Ответ remove:', data);
        
        if (data.success) {
            // Удаляем визуально 1 товар
            if (item.element && item.element.parentNode) {
                item.element.remove();
            }
            
            totalPrice -= item.price;
            updateBasketStats();
            updateDiscount(totalPrice);
            
            // Удаляем из массива items
            const index = items.findIndex(i => i.id === item.id);
            if (index !== -1) items.splice(index, 1);
            
            // Проверяем, остались ли еще товары ЭТОГО ТИПА в физической корзине
            const remainingItems = items.filter(i => i.productId == item.productId);
            console.log(`Осталось товаров ${item.name} в физической корзине: ${remainingItems.length}`);
            
            // НЕ УДАЛЯЕМ из cartProducts, если в Битриксе еще есть товары
            // Нужно проверить актуальное количество в Битриксе
            fetch('/ajax/add_to_cart.php?action=get_full&t=' + Date.now())
                .then(res => res.json())
                .then(basketData => {
                    const bitrixItem = basketData.items?.find(i => i.PRODUCT_ID == item.productId);
                    const bitrixQuantity = bitrixItem?.QUANTITY || 0;
                    
                    console.log(`В Битриксе осталось: ${bitrixQuantity}`);
                    
                    if (bitrixQuantity === 0) {
                        // Только если в Битриксе нет товара, удаляем из cartProducts
                        const productIndex = cartProducts.findIndex(p => p.id == item.productId);
                        if (productIndex !== -1) {
                            cartProducts.splice(productIndex, 1);
                            console.log(`Удален из cartProducts: ${item.name}`);
                        }
                    }
                    
                    renderScrollItems();
                });
        } else {
            item.pendingRemoval = false;
            showNotification('Ошибка удаления', 'error');
        }
    })
    .catch(error => {
        console.error('Ошибка:', error);
        item.pendingRemoval = false;
        showNotification('Ошибка соединения', 'error');
    });
}
function updateBasketStats() {
    totalSpan.innerText = totalPrice.toLocaleString() + ' ₽';
    countSpan.innerText = items.length;
    updateCartTotal(totalPrice);
}

function updateDiscount(total) {
    let currentPercent = 0;
    for (const threshold of discountThresholds) {
        if (total >= threshold.min && total < threshold.max) {
            currentPercent = threshold.percent;
            break;
        }
    }
    
    discountPercent = currentPercent;
    discountAmount = Math.floor(total * discountPercent / 100);
    
    if (discountPercentSpan) discountPercentSpan.innerText = discountPercent + '%';
    if (discountAmountSpan) discountAmountSpan.innerText = discountAmount.toLocaleString() + ' ₽';
    
    const maxTotal = 50000;
    const progress = Math.min((total / maxTotal) * 100, 100);
    if (discountProgressBar) discountProgressBar.style.width = progress + '%';
    
    const discountMessage = document.getElementById('discount-message');
    if (discountPercent > 0) {
        if (discountMessage) {
            discountMessage.innerHTML = `🎉 Ваша скидка: ${discountPercent}% (${discountAmount} ₽)`;
            discountMessage.style.color = '#e74c3c';
        }
    } else {
        const nextThreshold = discountThresholds.find(t => t.min > total);
        if (nextThreshold && discountMessage) {
            const need = nextThreshold.min - total;
            discountMessage.innerHTML = `Добавьте еще ${need.toLocaleString()} ₽ для скидки ${nextThreshold.percent}%`;
        }
    }
}

// ========== УПРАВЛЕНИЕ СКРОЛЛЕРОМ ==========
let cartProducts = [];

function updateScrollItemQuantity(productId, quantity) {
    const itemDiv = document.querySelector(`.scroll-item[data-id="${productId}"]`);
    if (itemDiv) {
        const qtyInput = itemDiv.querySelector('.scroll-item-qty');
        if (qtyInput) qtyInput.value = quantity;
        if (quantity === 0 && itemDiv.parentNode) {
            itemDiv.remove();
        }
    }
}

function renderScrollItems() {
    const scrollContainer = document.getElementById('productsScroll');
    if (!scrollContainer) {
        console.warn('productsScroll не найден');
        return;
    }
    
    console.log('renderScrollItems вызван, cartProducts:', cartProducts.length, 'items:', items.length);
    
    if (cartProducts.length === 0) {
        scrollContainer.innerHTML = '<div class="scroll-loading">✨ Добавьте товары из каталога ✨</div>';
        return;
    }
    
    let html = '';
    cartProducts.forEach(product => {
        // Подсчитываем актуальное количество
        const quantity = items.filter(i => i.productId == product.id && !i.pendingRemoval).length;
        const productName = escapeHtml(String(product.name || 'Товар'));
        const productPrice = product.price || 0;
        
        html += `
            <div class="scroll-item" data-id="${product.id}" data-price="${productPrice}">
                <div class="scroll-item-image">
                    <img src="${product.imageUrl || '/upload/no-image.jpg'}" alt="${productName}" onerror="this.src='/upload/no-image.jpg'">
                </div>
                <div class="scroll-item-name">${productName}</div>
                <div class="scroll-item-price">${productPrice.toLocaleString()} ₽</div>
                <div class="scroll-item-controls">
                    <button class="scroll-item-btn" onclick="window.changeQuantity(${product.id}, -1)">−</button>
                    <input type="number" class="scroll-item-qty" value="${quantity}" min="0" readonly>
                    <button class="scroll-item-btn" onclick="window.changeQuantity(${product.id}, 1)">+</button>
                </div>
            </div>
        `;
    });
    
    scrollContainer.innerHTML = html;
    console.log('renderScrollItems завершен, отображено товаров:', cartProducts.length);
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

window.changeQuantity = function(productId, delta) {
    console.log('changeQuantity:', { productId, delta });
    
    const product = cartProducts.find(p => p.id == productId);
    if (!product) return;
    
    if (delta === 1) {
        // Добавляем 1 товар
        window.addProductToBasket({
            id: product.id,
            name: product.name,
            price: product.price,
            imageUrl: product.imageUrl
        });
    } else if (delta === -1) {
        // Удаляем 1 товар
        const itemToRemove = items.find(i => i.productId == productId && !i.pendingRemoval);
        if (itemToRemove) {
            removeItem(itemToRemove);
        }
    } else if (delta === -999) {
        // Удаляем ВСЕ товары этого типа
        const itemsToRemove = items.filter(i => i.productId == productId && !i.pendingRemoval);
        itemsToRemove.forEach(item => removeItem(item));
    }
};

window.repeatOrder = repeatOrder;

// ========== СОЗДАНИЕ ТОВАРА ==========
function createProduct(productData, customX = null) {
    const visualSize = 340;
    const physicsSize = 230;
    
    const productName = String(productData.name || 'Товар');
    const productPrice = productData.price || 0;
    
    console.log('createProduct вызван:', { id: productData.id, name: productName, price: productPrice });
    
    // Проверяем, не слишком ли много товаров уже в корзине
    const existingCount = items.filter(i => i.productId == productData.id).length;
    console.log(`Товаров ${productName} уже в корзине: ${existingCount}`);
    
    const basketRect = document.getElementById('basket').getBoundingClientRect();
    
    let spawnX, spawnY;
    
    if (basketRect && basketRect.top > 0) {
        // Добавляем смещение для каждого нового товара, чтобы они не падали в одну точку
        const offsetX = (Math.random() - 0.5) * 80;
        const offsetY = -30 - (existingCount * 15);
        spawnX = basketRect.left + basketRect.width / 2 + offsetX;
        spawnY = basketRect.top + offsetY;
    } else if (customX !== null) {
        spawnX = customX;
        spawnY = -30;
    } else {
        spawnX = window.innerWidth / 2 + (Math.random() - 0.5) * 200;
        spawnY = -30;
    }
    
    const productDiv = document.createElement('div');
    productDiv.className = 'product-item';
    productDiv.style.position = 'fixed';
    productDiv.style.cursor = 'grab';
    productDiv.style.zIndex = '200';
    productDiv.style.filter = 'drop-shadow(0 4px 8px rgba(0,0,0,0.2))';
    productDiv.style.background = 'transparent';
    productDiv.style.border = 'none';
    productDiv.style.padding = '0';
    productDiv.style.margin = '0';
    
    const img = document.createElement('img');
    const imageUrl = productData.imageUrl || '/upload/no-image.jpg';
    img.src = imageUrl;
    img.alt = productName;
    img.style.width = '100%';
    img.style.height = '100%';
    img.style.objectFit = 'contain';
    img.style.pointerEvents = 'none';
    img.style.display = 'block';
    img.style.userSelect = 'none';
    
    img.onerror = function() {
        console.warn('Не удалось загрузить изображение:', imageUrl);
        this.src = '/upload/no-image.jpg';
    };
    
    productDiv.appendChild(img);
    document.body.appendChild(productDiv);
    
    productDiv.style.width = visualSize + 'px';
    productDiv.style.height = visualSize + 'px';
    
    productDiv.style.left = (spawnX - visualSize / 2) + 'px';
    productDiv.style.top = (spawnY - visualSize / 2) + 'px';
    
    const itemObj = {
        id: Date.now() + Math.random(),
        productId: productData.id,
        x: spawnX,
        y: spawnY,
        vx: (Math.random() - 0.5) * 1.5,
        vy: (Math.random() * 1.5) + 1.2,
        size: physicsSize,
        visualSize: visualSize,
        price: productPrice,
        name: productName,
        imageUrl: imageUrl,
        element: productDiv,
        pendingRemoval: false,
        justLanded: false
    };
    
    items.push(itemObj);
    totalPrice += itemObj.price;
    updateBasketStats();
    updateDiscount(totalPrice);
    
    // ОБНОВЛЯЕМ СКРОЛЛЕР ПОСЛЕ ДОБАВЛЕНИЯ ТОВАРА
    renderScrollItems();
    
    console.log('Товар создан, всего товаров в корзине:', items.length);
    
    productDiv.addEventListener('mousedown', (e) => {
        e.stopPropagation();
        if (itemObj.pendingRemoval) return;
        isDragging = true;
        draggingItem = itemObj;
        const rect = productDiv.getBoundingClientRect();
        dragOffsetX = e.clientX - rect.left;
        dragOffsetY = e.clientY - rect.top;
        productDiv.style.cursor = 'grabbing';
        productDiv.style.zIndex = '10000';
        e.preventDefault();
    });
    
    window.dispatchEvent(new CustomEvent('cartItemAdded', { detail: { productId: itemObj.id } }));
    
    return itemObj;
}

// ========== ГЛОБАЛЬНЫЕ ОБРАБОТЧИКИ DRAG & DROP ==========
window.addEventListener('mousemove', (e) => {
    if (!isDragging || !draggingItem) return;
    e.preventDefault();
    
    let newX = e.clientX - dragOffsetX;
    let newY = e.clientY - dragOffsetY;
    
    const width = parseFloat(draggingItem.element.style.width) || draggingItem.visualSize;
    const height = parseFloat(draggingItem.element.style.height) || draggingItem.visualSize;
    
    newX = Math.min(window.innerWidth - width, Math.max(0, newX));
    newY = Math.min(window.innerHeight - height, Math.max(0, newY));
    
    draggingItem.x = newX + width / 2;
    draggingItem.y = newY + height / 2;
    draggingItem.vx = 0;
    draggingItem.vy = 0;
    
    draggingItem.element.style.left = newX + 'px';
    draggingItem.element.style.top = newY + 'px';
    
    if (lastMousePos.x !== 0) {
        lastMouseMoveDelta = {
            vx: (e.clientX - lastMousePos.x) * 0.8,
            vy: (e.clientY - lastMousePos.y) * 0.8
        };
    }
    lastMousePos.x = e.clientX;
    lastMousePos.y = e.clientY;
    
    window.dispatchEvent(new CustomEvent('cartItemMoved', { detail: { item: draggingItem } }));
});

window.addEventListener('mouseup', () => {
    if (isDragging && draggingItem) {
        if (lastMouseMoveDelta) {
            draggingItem.vx = lastMouseMoveDelta.vx;
            draggingItem.vy = lastMouseMoveDelta.vy;
        }
        draggingItem.element.style.cursor = 'grab';
        draggingItem.element.style.zIndex = '';
        draggingItem = null;
        isDragging = false;
        lastMouseMoveDelta = null;
        lastMousePos = { x: 0, y: 0 };
    }
});

// ========== ВСПОМОГАТЕЛЬНЫЕ ФУНКЦИИ ==========
function updateCartTotal(total) {
    const totalElement = document.getElementById('cart-total');
    const finalTotalElement = document.getElementById('finalTotal');
    const finalTotal = total - discountAmount;
    
    if (totalElement) totalElement.textContent = formatPrice(total);
    
    const deliverySelected = document.querySelector('input[name="delivery"]:checked');
    if (deliverySelected && finalTotalElement) {
        let deliveryTotal = finalTotal;
        if (deliverySelected.value === 'delivery') deliveryTotal += 500;
        finalTotalElement.textContent = formatPrice(deliveryTotal);
    } else if (finalTotalElement) {
        finalTotalElement.textContent = formatPrice(finalTotal);
    }
}

function formatPrice(price) {
    if (!price) return '0 ₽';
    return new Intl.NumberFormat('ru-RU').format(price) + ' ₽';
}

function detectCity() {
    const cityInput = document.getElementById('city');
    const detectionSpan = document.getElementById('city-detection');
    if (!cityInput) return;
    
    fetch('https://ipapi.co/json/')
        .then(response => response.json())
        .then(data => {
            if (data.city) {
                cityInput.value = data.city;
                if (detectionSpan) detectionSpan.innerHTML = `📍 Город определен: ${data.city}`;
            }
        })
        .catch(() => {
            if (detectionSpan) detectionSpan.innerHTML = '🌍 Город не определен';
        });
}

function initPhoneMask() {
    const phoneInput = document.getElementById('phone');
    if (!phoneInput) return;
    
    phoneInput.addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        if (value.length > 0) {
            let formatted = '+7';
            if (value.length > 1) formatted += ' (' + value.substring(1, 4);
            if (value.length >= 4) formatted += ') ' + value.substring(4, 7);
            if (value.length >= 7) formatted += '-' + value.substring(7, 9);
            if (value.length >= 9) formatted += '-' + value.substring(9, 11);
            e.target.value = formatted;
        }
    });
}

function selectDelivery(type) {
    const addressGroup = document.getElementById('addressGroup');
    if (addressGroup) addressGroup.style.display = type === 'delivery' ? 'block' : 'none';
    updateCartTotal(totalPrice);
}

// ========== СИСТЕМА СТАБИЛЬНОСТИ ==========
class StabilitySystem {
    constructor() {
        this.stabilityStartTime = null;
        this.isStable = false;
        this.STABILITY_REQUIRED = 5000;
        this.checkInterval = null;
        this.init();
    }
    
    init() {
        this.resetStability();
        this.startStabilityCheck();
        this.observeCartChanges();
    }
    
    startStabilityCheck() {
        if (this.checkInterval) clearInterval(this.checkInterval);
        this.checkInterval = setInterval(() => {
            if (!this.isStable && this.stabilityStartTime) {
                const elapsed = Date.now() - this.stabilityStartTime;
                const progress = Math.min((elapsed / this.STABILITY_REQUIRED) * 100, 100);
                this.updateProgressBar(progress);
                if (elapsed >= this.STABILITY_REQUIRED && !this.isStable) this.achieveStability();
            }
        }, 50);
    }
    
    observeCartChanges() {
        window.addEventListener('cartItemAdded', () => this.resetStability());
        window.addEventListener('cartItemRemoved', () => this.resetStability());
        window.addEventListener('cartItemMoved', () => this.resetStability());
    }
    
    resetStability() {
        if (this.isStable) {
            this.isStable = false;
            this.disableCheckoutButton();
        }
        this.stabilityStartTime = Date.now();
        this.updateProgressBar(0);
    }
    
    achieveStability() {
        this.isStable = true;
        clearInterval(this.checkInterval);
        this.updateProgressBar(100);
        this.enableCheckoutButton();
        this.showMessage('✨ Корзина стабилизирована! ✨', 'success');
    }
    
    updateProgressBar(progress) {
        const progressBar = document.getElementById('stability-progress');
        const timerElement = document.getElementById('stability-timer');
        if (progressBar) progressBar.style.width = `${progress}%`;
        if (timerElement && progress < 100) {
            const remaining = (this.STABILITY_REQUIRED - (progress / 100 * this.STABILITY_REQUIRED)) / 1000;
            timerElement.textContent = `${remaining.toFixed(1)} сек`;
        } else if (timerElement) {
            timerElement.textContent = 'ГОТОВО!';
        }
    }
    
    enableCheckoutButton() {
        const btn = document.getElementById('checkoutBtn');
        const hint = document.getElementById('order-hint');
        if (btn) btn.disabled = false;
        if (hint) hint.innerHTML = '✅ Заказ готов к оформлению!';
    }
    
    disableCheckoutButton() {
        const btn = document.getElementById('checkoutBtn');
        const hint = document.getElementById('order-hint');
        if (btn) btn.disabled = true;
        if (hint) hint.innerHTML = '🔒 Удерживайте товары 5 секунд для активации кнопки';
    }
    
    showMessage(message, type) {
        const msgEl = document.getElementById('stability-message');
        if (msgEl) {
            msgEl.innerHTML = message;
            msgEl.style.color = type === 'success' ? '#27ae60' : '#999';
        }
    }
}

// ========== ГЕЙМИФИКАЦИЯ ==========
class Gamification {
    constructor() {
        this.score = 0;
        this.combo = 0;
        this.lastCatchTime = 0;
        this.loadScore();
        this.updateScoreDisplay();
    }
    
    onSuccessfulCatch() {
        const now = Date.now();
        if (now - this.lastCatchTime < 2000) {
            this.combo++;
            this.showComboEffect();
        } else {
            this.combo = 1;
        }
        this.lastCatchTime = now;
        
        const points = 10 + Math.min(this.combo * 5, 50);
        this.score += points;
        this.saveScore();
        this.showPointsEffect(points);
        this.updateScoreDisplay();
    }
    
    showComboEffect() {
        const comboEl = document.getElementById('combo-display');
        const comboCount = document.getElementById('combo-count');
        if (comboEl && comboCount) {
            comboEl.style.display = 'flex';
            comboCount.textContent = this.combo;
            comboEl.style.animation = 'none';
            setTimeout(() => comboEl.style.animation = 'comboAnimation 0.5s ease', 10);
        }
    }
    
    showPointsEffect(points) {
        const effect = document.createElement('div');
        effect.textContent = `+${points}`;
        effect.style.cssText = `position: fixed; left: 50%; top: 40%; font-size: 24px; font-weight: bold; color: #f39c12; text-shadow: 0 0 5px orange; animation: pointsFloat 1s ease-out forwards; pointer-events: none; z-index: 10000;`;
        document.body.appendChild(effect);
        setTimeout(() => effect.remove(), 1000);
    }
    
    updateScoreDisplay() {
        const scoreEl = document.getElementById('player-score');
        if (scoreEl) scoreEl.textContent = this.score;
    }
    
    saveScore() {
        localStorage.setItem('cart_game_score', this.score);
    }
    
    loadScore() {
        const saved = localStorage.getItem('cart_game_score');
        if (saved) this.score = parseInt(saved);
    }
}

// ========== ОФОРМЛЕНИЕ ЗАКАЗА ==========
function submitOrder(event) {
    event.preventDefault();
    
    if (!window.stabilitySystem || !window.stabilitySystem.isStable) {
        showNotification('Подождите, пока корзина стабилизируется (5 секунд)!', 'warning');
        return;
    }
    
    if (items.length === 0) {
        showNotification('Корзина пуста! Добавьте товары', 'warning');
        return;
    }
    
    const lastName = document.getElementById('lastName')?.value.trim();
    const firstName = document.getElementById('firstName')?.value.trim();
    const phone = document.getElementById('phone')?.value.trim();
    const email = document.getElementById('email')?.value.trim();
    const delivery = document.querySelector('input[name="delivery"]:checked')?.value;
    const address = document.getElementById('address')?.value.trim();
    
    if (!lastName || !firstName) {
        showNotification('Укажите имя и фамилию', 'error');
        return;
    }
    if (!phone) {
        showNotification('Укажите телефон', 'error');
        return;
    }
    if (!email) {
        showNotification('Укажите email', 'error');
        return;
    }
    if (delivery === 'delivery' && !address) {
        showNotification('Укажите адрес доставки', 'error');
        return;
    }
    
    const checkoutBtn = document.getElementById('checkoutBtn');
    const originalText = checkoutBtn.innerHTML;
    checkoutBtn.innerHTML = '⏳ Оформление...';
    checkoutBtn.disabled = true;
    
    setTimeout(() => {
        showSuccessEffect();
        setTimeout(() => {
            window.location.href = '/personal/order/success.php';
        }, 1500);
    }, 1000);
}

function showNotification(message, type) {
    const notif = document.createElement('div');
    notif.className = 'cart-notification';
    notif.textContent = message;
    notif.style.background = type === 'success' ? '#27ae60' : type === 'warning' ? '#f39c12' : '#e74c3c';
    document.body.appendChild(notif);
    setTimeout(() => notif.remove(), 3000);
}

function showSuccessEffect() {
    for (let i = 0; i < 80; i++) {
        const confetti = document.createElement('div');
        confetti.style.cssText = `position: fixed; left: ${Math.random() * 100}%; top: -10px; width: ${Math.random() * 8 + 4}px; height: ${Math.random() * 8 + 4}px; background: hsl(${Math.random() * 360}, 100%, 50%); transform: rotate(${Math.random() * 360}deg); animation: confettiFall ${Math.random() * 2 + 1}s linear forwards; pointer-events: none; z-index: 10000;`;
        document.body.appendChild(confetti);
        setTimeout(() => confetti.remove(), 2000);
    }
}

function loadRecentOrders() {
    const container = document.getElementById('recent-orders-list');
    if (container) {
        fetch('/ajax/get_recent_orders.php')
            .then(response => response.json())
            .then(data => {
                if (data.success && data.orders && data.orders.length > 0) {
                    let html = '';
                    data.orders.slice(0, 3).forEach(order => {
                        const date = new Date(order.CREATED_AT).toLocaleDateString('ru-RU');
                        html += `
                            <div class="recent-order-card">
                                <div class="order-number">Заказ №${order.ORDER_NUMBER || order.ID}</div>
                                <div class="order-date">${date}</div>
                                <span class="order-status status-${order.STATUS}">${order.STATUS_NAME || 'Новый'}</span>
                                <div class="order-total">${formatPrice(order.TOTAL_PRICE)}</div>
                                <button class="repeat-order-btn" onclick="repeatOrder(${order.ID})">🔄 Повторить</button>
                            </div>
                        `;
                    });
                    container.innerHTML = html;
                } else {
                    container.innerHTML = '<div class="recent-order-card">У вас пока нет заказов</div>';
                }
            })
            .catch(() => {
                container.innerHTML = '<div class="recent-order-card">Не удалось загрузить заказы</div>';
            });
    }
}

function repeatOrder(orderId) {
    showNotification('Товары добавлены в корзину!', 'success');
    fetch('/ajax/repeat_order.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `order_id=${orderId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    });
}

function loadUserData() {
    fetch('/ajax/get_user_data.php')
        .then(response => response.json())
        .then(data => {
            console.log('Данные пользователя:', data);
            
            if (data.success && data.user) {
                // Заполняем имя и фамилию
                if (data.user.NAME) {
                    // Если пришло полное имя
                    if (data.user.NAME.includes(' ')) {
                        const nameParts = data.user.NAME.split(' ');
                        document.getElementById('firstName').value = nameParts[0] || '';
                        document.getElementById('lastName').value = nameParts[1] || '';
                    } else {
                        document.getElementById('firstName').value = data.user.NAME;
                    }
                }
                
                // Если есть отдельные поля
                if (data.user.FIRST_NAME) {
                    document.getElementById('firstName').value = data.user.FIRST_NAME;
                }
                if (data.user.LAST_NAME) {
                    document.getElementById('lastName').value = data.user.LAST_NAME;
                }
                
                // Email
                if (data.user.EMAIL) {
                    document.getElementById('email').value = data.user.EMAIL;
                }
                
                // Телефон
                if (data.user.PERSONAL_PHONE) {
                    const phone = data.user.PERSONAL_PHONE;
                    let formatted = '+7';
                    const cleanPhone = phone.replace(/\D/g, '');
                    if (cleanPhone.length > 1) {
                        formatted += ' (' + cleanPhone.substring(1, 4);
                    }
                    if (cleanPhone.length >= 4) {
                        formatted += ') ' + cleanPhone.substring(4, 7);
                    }
                    if (cleanPhone.length >= 7) {
                        formatted += '-' + cleanPhone.substring(7, 9);
                    }
                    if (cleanPhone.length >= 9) {
                        formatted += '-' + cleanPhone.substring(9, 11);
                    }
                    document.getElementById('phone').value = formatted;
                }
                
                // Город
                if (data.user.PERSONAL_CITY) {
                    document.getElementById('city').value = data.user.PERSONAL_CITY;
                }
                
                // Адрес
                if (data.user.PERSONAL_STREET) {
                    document.getElementById('address').value = data.user.PERSONAL_STREET;
                }
            } else if (data.is_guest) {
                console.log('Пользователь не авторизован');
            }
        })
        .catch(error => {
            console.error('Ошибка загрузки данных пользователя:', error);
        });
}

window.addProductToBasket = function(productData) {
    console.log('addProductToBasket вызван:', productData);
    
    if (!productData || !productData.id) return false;
    
    const normalizedData = {
        id: parseInt(productData.id),
        name: productData.name || 'Товар',
        price: parseFloat(productData.price) || 0,
        imageUrl: productData.imageUrl || null
    };
    
    // Отправляем запрос на добавление 1 единицы
    fetch('/ajax/add_to_cart.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `action=add&product_id=${normalizedData.id}&quantity=1`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Добавляем в физическую корзину 1 товар
            createProduct(normalizedData);
            
            // Обновляем cartProducts если нужно
            const productExists = cartProducts.some(p => p.id == normalizedData.id);
            if (!productExists) {
                cartProducts.push(normalizedData);
            }
            
            renderScrollItems();
            updateBasketStats();
            showNotification(`+1 ${normalizedData.name}`, 'success');
        } else {
            showNotification('Ошибка добавления', 'error');
        }
    });
};

// Альтернативный способ добавления (для совместимости с разными форматами вызова)
window.addToCart = function(productId, productName, productPrice, productImage) {
    console.log('addToCart вызван:', { productId, productName, productPrice, productImage });
    return window.addProductToBasket({
        id: productId,
        name: productName,
        price: productPrice,
        imageUrl: productImage
    });
};

function loadCartFromBitrix() {
    console.log('Загрузка корзины из Битрикса...');
    
    fetch('/ajax/add_to_cart.php?action=get_full&t=' + Date.now())
        .then(response => response.json())
        .then(data => {
            console.log('Ответ от сервера (get_full):', data);
            
            if (data.success && data.items && data.items.length > 0) {
                console.log('Найдено товаров в корзине Битрикса:', data.items.length);
                
                // Очищаем текущую корзину
                document.querySelectorAll('.product-item').forEach(el => el.remove());
                window.items = [];
                window.cartProducts = [];
                window.totalPrice = 0;
                
                data.items.forEach(item => {
                    // Пропускаем мусор
                    if (!item.NAME || item.NAME.trim() === '' || item.PRICE === 0) {
                        console.log('⚠️ Пропускаем мусор:', item);
                        return;
                    }
                    
                    console.log(`Загружаем: ${item.NAME}, кол-во: ${item.QUANTITY}, цена: ${item.PRICE}`);
                    
                    // Получаем картинку товара
                    let imageUrl = null;
                    if (item.IMAGE) {
                        imageUrl = item.IMAGE;
                    } else {
                        // Если нет картинки в ответе, пробуем получить отдельно
                        fetch(`/ajax/get_product_image.php?id=${item.PRODUCT_ID}`)
                            .then(r => r.json())
                            .then(imgData => {
                                if (imgData.image) {
                                    // Обновляем картинку у всех товаров этого типа
                                    items.forEach(i => {
                                        if (i.productId == item.PRODUCT_ID && i.element) {
                                            const img = i.element.querySelector('img');
                                            if (img) img.src = imgData.image;
                                        }
                                    });
                                }
                            });
                    }
                    
                    // Добавляем в cartProducts
                    const productExists = cartProducts.some(p => p.id == item.PRODUCT_ID);
                    if (!productExists) {
                        cartProducts.push({
                            id: item.PRODUCT_ID,
                            name: item.NAME,
                            price: item.PRICE,
                            imageUrl: imageUrl
                        });
                    }
                    
                    // Добавляем товары в физическую корзину
                    for (let i = 0; i < item.QUANTITY; i++) {
                        createProduct({
                            id: item.PRODUCT_ID,
                            name: item.NAME,
                            price: item.PRICE,
                            imageUrl: imageUrl
                        });
                    }
                });
                
                renderScrollItems();
                updateBasketStats();
            } else {
                console.log('Корзина Битрикса пуста');
            }
        })
        .catch(error => console.error('Ошибка загрузки корзины:', error));
}

function animatePhysics() {
    applyPhysics();
    animationId = requestAnimationFrame(animatePhysics);
}

function updateScreenBottom() {
    screenBottom = window.innerHeight;
}

// Функция для загрузки товаров из localStorage (если они есть)
function loadPendingCartItems() {
    const pendingItems = JSON.parse(localStorage.getItem('pending_cart_items') || '[]');
    
    console.log('loadPendingCartItems - найдено товаров:', pendingItems.length);
    
    if (pendingItems.length > 0) {
        pendingItems.forEach(item => {
            console.log('Товар из localStorage:', item.name);
            // Проверяем, нет ли уже такого товара в корзине
            const exists = items.some(i => i.productId == item.id);
            if (!exists) {
                console.log('Добавляем товар из localStorage:', item.name);
                
                // ДОБАВЛЯЕМ В cartProducts, ЕСЛИ ЕЩЕ НЕТ
                const productExists = cartProducts.some(p => p.id == item.id);
                if (!productExists) {
                    cartProducts.push({
                        id: item.id,
                        name: item.name,
                        price: item.price,
                        imageUrl: item.imageUrl
                    });
                    console.log('Товар добавлен в cartProducts:', item.name);
                }
                
                createProduct({
                    id: item.id,
                    name: item.name,
                    price: item.price,
                    imageUrl: item.imageUrl
                });
            }
        });
        
        // ОБНОВЛЯЕМ ОТОБРАЖЕНИЕ СКРОЛЛЕРА
        renderScrollItems();
        
        // Очищаем localStorage после загрузки
        localStorage.removeItem('pending_cart_items');
        console.log('✅ localStorage очищен');
    }
}
// Добавьте вызов этой функции в init()
function init() {
    updateScreenBottom();
    window.addEventListener('resize', () => {
        updateScreenBottom();
        for (let it of items) {
            const width = parseFloat(it.element.style.width) || it.visualSize;
            const height = parseFloat(it.element.style.height) || it.visualSize;
            it.x = Math.min(window.innerWidth - width / 2, Math.max(width / 2, it.x));
            it.y = Math.min(window.innerHeight - height / 2, Math.max(height / 2, it.y));
        }
    });
    
    // СНАЧАЛА загружаем из localStorage
    loadPendingCartItems();
    
    // ПОТОМ загружаем из Битрикса
    loadCartFromBitrix();
    
    loadRecentOrders();
    loadUserData();
    detectCity();
    initPhoneMask();
    
    animatePhysics();
    
    window.stabilitySystem = new StabilitySystem();
    window.gamification = new Gamification();
    
    // ОБНОВЛЯЕМ ОТОБРАЖЕНИЕ СКРОЛЛЕРА
    renderScrollItems();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
} else {
    init();
}

// Функция для удаления всех мусорных товаров из корзины Битрикса
function cleanBitrixCart() {
    console.log('🧹 Очистка мусорных товаров...');
    
    fetch('/ajax/add_to_cart.php?action=get_full&t=' + Date.now())
        .then(response => response.json())
        .then(data => {
            if (data.success && data.items) {
                const corruptedItems = data.items.filter(item => !item.NAME || item.NAME.trim() === '' || item.PRICE === 0);
                
                if (corruptedItems.length > 0) {
                    console.log(`Найдено ${corruptedItems.length} мусорных товаров`);
                    
                    let deletePromises = corruptedItems.map(item => {
                        return fetch('/ajax/add_to_cart.php', {
                            method: 'POST',
                            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                            body: `action=remove&product_id=${item.PRODUCT_ID}`
                        });
                    });
                    
                    Promise.all(deletePromises).then(() => {
                        console.log('✅ Мусорные товары удалены');
                        location.reload();
                    });
                } else {
                    console.log('Мусорных товаров не найдено');
                }
            }
        });
}
</script>

<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
?>