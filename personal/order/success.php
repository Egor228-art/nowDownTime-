<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

CModule::IncludeModule("sale");

$orderId = intval($_GET['order_id'] ?? 0);
$orderNumber = $orderId;

if ($orderId > 0 && $USER->IsAuthorized()) {
    $dbOrder = CSaleOrder::GetList(
        [],
        ["ID" => $orderId, "USER_ID" => $USER->GetID()],
        false,
        false,
        ["ID", "ACCOUNT_NUMBER", "DATE_INSERT", "PRICE"]
    );
    
    if ($arOrder = $dbOrder->Fetch()) {
        $orderNumber = $arOrder['ACCOUNT_NUMBER'] ?: $orderId;
        $orderPrice = $arOrder['PRICE'];
        $orderDate = $arOrder['DATE_INSERT'];
    }
}

$APPLICATION->SetTitle("Заказ оформлен");
?>

<div class="success-page">
    <div class="success-container">
        <div class="success-card">
            <!-- Анимация успеха -->
            <div class="success-animation">
                <div class="success-checkmark">
                    <div class="check-icon">
                        <span class="icon-line line-tip"></span>
                        <span class="icon-line line-long"></span>
                        <div class="icon-circle"></div>
                        <div class="icon-fix"></div>
                    </div>
                </div>
            </div>
            
            <h1 class="success-title">Спасибо за заказ!</h1>
            
            <p class="success-message">
                Ваш заказ <strong>№<?= htmlspecialchars($orderNumber) ?></strong> успешно оформлен.<br>
                Мы свяжемся с вами в ближайшее время.
            </p>
            
            <div class="success-buttons">
                <a href="/catalog/" class="btn btn-catalog">
                    <i class="fas fa-shopping-bag"></i>
                    Продолжить покупки
                </a>
                <a href="/personal/order/" class="btn btn-orders">
                    <i class="fas fa-list-alt"></i>
                    Мои заказы
                </a>
            </div>
        </div>
    </div>
</div>

<style>
/* ========== СТРАНИЦА УСПЕХА ========== */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    background: linear-gradient(135deg, var(--Gold) 0%, var(--DragonLight) 100%);
}
.success-page {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 40px 20px;
    position: relative;
    overflow: hidden;
}

.success-container {
    max-width: 500px;
    width: 100%;
    position: relative;
    z-index: 10;
}

.success-card {
    background: white;
    border-radius: 32px;
    padding: 50px 40px;
    text-align: center;
    box-shadow: 0 25px 50px rgba(0,0,0,0.2);
    animation: fadeInUp 0.6s ease;
}

/* Анимация галочки */
.success-animation {
    margin-bottom: 30px;
}

.success-checkmark {
    width: 100px;
    height: 100px;
    margin: 0 auto;
}

.check-icon {
    width: 100px;
    height: 100px;
    position: relative;
    border-radius: 50%;
    box-sizing: content-box;
    border: 4px solid #4caf50;
}

.check-icon::before {
    top: 3px;
    left: -2px;
    width: 30px;
    transform-origin: 100% 50%;
    border-radius: 100px 0 0 100px;
}

.check-icon::after {
    top: 0;
    left: 30px;
    width: 60px;
    transform-origin: 0 50%;
    border-radius: 0 100px 100px 0;
    animation: rotateCircle 4.25s ease-in;
}

.check-icon::before,
.check-icon::after {
    content: '';
    height: 100px;
    position: absolute;
    background: #FFFFFF;
    transform: rotate(-45deg);
}

.icon-line {
    height: 5px;
    background-color: #4caf50;
    display: block;
    border-radius: 2px;
    position: absolute;
    z-index: 10;
}

.icon-line.line-tip {
    top: 56px;
    left: 22px;
    width: 25px;
    transform: rotate(45deg);
    animation: icon-line-tip 0.75s;
}

.icon-line.line-long {
    top: 48px;
    right: 22px;
    width: 47px;
    transform: rotate(-45deg);
    animation: icon-line-long 0.75s;
}

.icon-circle {
    top: -4px;
    left: -4px;
    z-index: 10;
    width: 100px;
    height: 100px;
    border-radius: 50%;
    position: absolute;
    box-sizing: content-box;
    border: 4px solid rgba(76, 175, 80, 0.5);
}

.icon-fix {
    top: 8px;
    left: -26px;
    z-index: 1;
    width: 35px;
    height: 85px;
    position: absolute;
    transform: rotate(-45deg);
    background-color: white;
}

/* Заголовок */
.success-title {
    font-size: 28px;
    color: #333;
    margin-bottom: 20px;
    font-weight: 600;
}

.success-message {
    font-size: 16px;
    color: #666;
    line-height: 1.6;
    margin-bottom: 35px;
}

.success-message strong {
    color: #e74c3c;
    font-size: 18px;
}

/* Кнопки */
.success-buttons {
    display: flex;
    gap: 15px;
    justify-content: center;
    flex-wrap: wrap;
}

.btn {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 12px 28px;
    border-radius: 50px;
    font-size: 14px;
    font-weight: 500;
    text-decoration: none;
    transition: all 0.3s;
    cursor: pointer;
}

.btn i {
    font-size: 14px;
}

.btn-catalog {
    background: linear-gradient(135deg, #e74c3c, #c0392b);
    color: white;
    border: none;
}

.btn-catalog:hover {
    transform: translateY(-3px);
    box-shadow: 0 5px 20px rgba(231,76,60,0.4);
}

.btn-orders {
    background: #f8f9fa;
    color: #333;
    border: 1px solid #e9ecef;
}

.btn-orders:hover {
    background: #e9ecef;
    transform: translateY(-2px);
}

/* Анимации */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes icon-line-tip {
    from {
        width: 0;
        opacity: 0;
    }
    to {
        width: 25px;
        opacity: 1;
    }
}

@keyframes icon-line-long {
    from {
        width: 0;
        opacity: 0;
    }
    to {
        width: 47px;
        opacity: 1;
    }
}

@keyframes rotateCircle {
    from {
        transform: rotate(-45deg);
    }
    to {
        transform: rotate(-45deg);
    }
}

/* Адаптивность */
@media (max-width: 768px) {
    .success-card {
        padding: 35px 25px;
    }
    
    .success-title {
        font-size: 24px;
    }
    
    .success-message {
        font-size: 14px;
    }
    
    .success-buttons {
        flex-direction: column;
        gap: 12px;
    }
    
    .btn {
        justify-content: center;
        padding: 10px 20px;
    }
}
</style>

<script>
// Конфетти
(function() {
    const colors = ['#f39c12', '#e74c3c', '#27ae60', '#3498db', '#9b59b6', '#1abc9c'];
    
    function createConfetti() {
        const confetti = document.createElement('div');
        const size = Math.random() * 8 + 4;
        const color = colors[Math.floor(Math.random() * colors.length)];
        const startX = Math.random() * window.innerWidth;
        const duration = Math.random() * 3 + 2;
        const delay = Math.random() * 2;
        
        confetti.style.cssText = `
            position: fixed;
            top: -10px;
            left: ${startX}px;
            width: ${size}px;
            height: ${size}px;
            background: ${color};
            transform: rotate(${Math.random() * 360}deg);
            pointer-events: none;
            z-index: 9999;
            animation: fall ${duration}s linear ${delay}s forwards;
        `;
        
        document.body.appendChild(confetti);
        
        setTimeout(() => {
            confetti.remove();
        }, (duration + delay) * 1000);
    }
    
    // Добавляем анимацию падения
    const style = document.createElement('style');
    style.textContent = `
        @keyframes fall {
            0% {
                top: -10px;
                transform: rotate(0deg);
                opacity: 1;
            }
            100% {
                top: 100vh;
                transform: rotate(360deg);
                opacity: 0;
            }
        }
    `;
    document.head.appendChild(style);
    
    // Запускаем конфетти
    for (let i = 0; i < 150; i++) {
        setTimeout(() => createConfetti(), i * 30);
    }
    
    // Вторая волна через 2 секунды
    setTimeout(() => {
        for (let i = 0; i < 80; i++) {
            setTimeout(() => createConfetti(), i * 40);
        }
    }, 2000);
})();
</script>

<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
?>