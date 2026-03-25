<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Страница не найдена");
?>

<?if (!$USER->IsAuthorized()):?>
    <style>
        body {
            margin-top: 0 !important;
            padding-top: 188px !important;
        }
    </style>
    <?endif?>

    <?if ($USER->IsAuthorized()):?>
    <style>
        body {
            margin-top: 150px !important;
            padding-top: 0 !important;
        }
    </style>
    <?endif?>

<div class="error-404">
    <div class="error-container">
        <!-- Декоративные элементы -->
        <div class="error-decoration">
            <div class="dice-flying dice-1">🎲</div>
            <div class="dice-flying dice-2">🎲</div>
            <div class="dice-flying dice-3">🎲</div>
            <div class="dice-flying dice-4">🎲</div>
        </div>
        
        <div class="error-content">
            <div class="error-number">
                <span class="digit">4</span>
                <span class="digit zero">0</span>
                <span class="digit">4</span>
            </div>
            
            <div class="error-icon">
                <i class="fas fa-dragon"></i>
                <i class="fas fa-map-signs"></i>
            </div>
            
            <h1>Потерялись в мире настолок?</h1>
            <p>Кажется, вы забрели не туда. Возможно, эта страница была удалена, перемещена или никогда не существовала в нашем мире приключений.</p>
            
            <div class="error-actions">
                <a href="/" class="btn-primary">
                    <i class="fas fa-home"></i>
                    <span>На главную</span>
                </a>
                <a href="/catalog/" class="btn-secondary">
                    <i class="fas fa-dice-d6"></i>
                    <span>В каталог</span>
                </a>
                <button class="btn-random" onclick="randomGame()">
                    <i class="fas fa-random"></i>
                    <span>Случайная игра</span>
                </button>
            </div>
            
            <div class="error-game">
                <p class="game-title">🎲 А пока вы здесь, проверьте удачу 🎲</p>
                <div class="dice-roller">
                    <div class="dice" onclick="rollDice()">
                        <div class="dice-face">
                            <span id="diceResult">?</span>
                        </div>
                        <div class="dice-shadow"></div>
                    </div>
                    <div class="dice-info">
                        <button class="btn-roll" onclick="rollDice()">
                            <i class="fas fa-dice-d20"></i>
                            Бросить d20
                        </button>
                        <div class="dice-history" id="diceHistory">
                            <span>История бросков:</span>
                            <div class="history-list"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.error-404 {
    min-height: calc(100vh - 200px);
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    overflow: hidden;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
}

.error-container {
    max-width: 700px;
    width: 100%;
    margin: 40px 20px;
    position: relative;
    z-index: 2;
}

/* Декоративные летающие кубики */
.error-decoration {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    pointer-events: none;
    overflow: hidden;
    z-index: 1;
}

.dice-flying {
    position: absolute;
    font-size: 40px;
    opacity: 0.3;
    animation: floatDice 8s ease-in-out infinite;
}

.dice-1 {
    top: 10%;
    left: -50px;
    animation-delay: 0s;
}

.dice-2 {
    bottom: 20%;
    right: -60px;
    animation-delay: 2s;
    animation-duration: 10s;
}

.dice-3 {
    top: 50%;
    left: -40px;
    animation-delay: 4s;
    animation-duration: 12s;
}

.dice-4 {
    bottom: 30%;
    right: -50px;
    animation-delay: 6s;
    animation-duration: 9s;
}

@keyframes floatDice {
    0% {
        transform: translateX(0) translateY(0) rotate(0deg);
        opacity: 0;
    }
    25% {
        opacity: 0.3;
    }
    75% {
        opacity: 0.3;
    }
    100% {
        transform: translateX(100px) translateY(-50px) rotate(360deg);
        opacity: 0;
    }
}

/* Основной контент */
.error-content {
    background: white;
    border-radius: 32px;
    padding: 50px 40px;
    text-align: center;
    box-shadow: 0 20px 40px rgba(0,0,0,0.1);
    position: relative;
    z-index: 2;
    backdrop-filter: blur(10px);
}

/* Номер ошибки */
.error-number {
    display: flex;
    justify-content: center;
    gap: 20px;
    margin-bottom: 20px;
}

.digit {
    font-size: 100px;
    font-weight: 800;
    background: linear-gradient(135deg, #e74c3c, #f39c12);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    line-height: 1;
    text-shadow: 5px 5px 15px rgba(231, 76, 60, 0.2);
    animation: glow 2s ease-in-out infinite;
}

.digit.zero {
    position: relative;
}

.digit.zero::before {
    content: '🎲';
    position: absolute;
    font-size: 40px;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    opacity: 0;
    transition: opacity 0.3s;
}

@keyframes glow {
    0%, 100% {
        text-shadow: 5px 5px 15px rgba(231, 76, 60, 0.2);
    }
    50% {
        text-shadow: 5px 5px 25px rgba(231, 76, 60, 0.5);
    }
}

/* Иконки */
.error-icon {
    margin-bottom: 20px;
}

.error-icon i {
    font-size: 48px;
    margin: 0 10px;
    background: linear-gradient(135deg, #e74c3c, #f39c12);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.error-icon i:first-child {
    animation: bounce 2s ease-in-out infinite;
}

.error-icon i:last-child {
    animation: bounce 2s ease-in-out infinite reverse;
}

@keyframes bounce {
    0%, 100% {
        transform: translateY(0);
    }
    50% {
        transform: translateY(-10px);
    }
}

/* Заголовок и текст */
.error-404 h1 {
    font-size: 28px;
    color: #2c3e50;
    margin-bottom: 20px;
    font-weight: 600;
}

.error-404 p {
    font-size: 16px;
    color: #7f8c8d;
    max-width: 450px;
    margin: 0 auto 30px;
    line-height: 1.6;
}

/* Кнопки */
.error-actions {
    display: flex;
    gap: 15px;
    justify-content: center;
    margin-bottom: 50px;
    flex-wrap: wrap;
}

.btn-primary, .btn-secondary, .btn-random {
    padding: 12px 28px;
    border-radius: 50px;
    text-decoration: none;
    font-weight: 500;
    transition: all 0.3s;
    display: inline-flex;
    align-items: center;
    gap: 10px;
    font-size: 14px;
    cursor: pointer;
    border: none;
}

.btn-primary {
    background: linear-gradient(135deg, #e74c3c, #c0392b);
    color: white;
    box-shadow: 0 4px 15px rgba(231, 76, 60, 0.3);
}

.btn-secondary {
    background: white;
    color: #2c3e50;
    border: 2px solid #e74c3c;
}

.btn-random {
    background: #f8f9fa;
    color: #e74c3c;
    border: 2px solid #e0e0e0;
}

.btn-primary:hover, .btn-secondary:hover, .btn-random:hover {
    transform: translateY(-3px);
}

.btn-primary:hover {
    box-shadow: 0 6px 20px rgba(231, 76, 60, 0.4);
}

.btn-secondary:hover {
    background: #e74c3c;
    color: white;
    border-color: #e74c3c;
}

.btn-random:hover {
    border-color: #e74c3c;
    background: white;
}

/* Игровой блок */
.error-game {
    background: linear-gradient(135deg, #fef9e6, #fff5e0);
    padding: 30px;
    border-radius: 24px;
    margin-top: 20px;
    border: 1px solid rgba(231, 76, 60, 0.2);
}

.game-title {
    font-size: 14px;
    color: #e74c3c;
    font-weight: 500;
    margin-bottom: 25px;
    letter-spacing: 1px;
}

.dice-roller {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 30px;
    flex-wrap: wrap;
}

/* Кубик */
.dice {
    width: 100px;
    height: 100px;
    cursor: pointer;
    position: relative;
    transition: all 0.3s;
}

.dice-face {
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, #e74c3c, #c0392b);
    border-radius: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 10px 20px rgba(0,0,0,0.2);
    position: relative;
    z-index: 2;
    transition: all 0.3s;
}

.dice-face span {
    font-size: 48px;
    font-weight: bold;
    color: white;
    text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
}

.dice-shadow {
    position: absolute;
    bottom: -10px;
    left: 10%;
    width: 80%;
    height: 20px;
    background: rgba(0,0,0,0.1);
    border-radius: 50%;
    filter: blur(8px);
    z-index: 1;
    transition: all 0.3s;
}

.dice:hover {
    transform: scale(1.05);
}

.dice:hover .dice-shadow {
    transform: scale(1.1);
    filter: blur(12px);
}

/* Информация о броске */
.dice-info {
    text-align: center;
}

.btn-roll {
    padding: 10px 24px;
    background: white;
    border: 2px solid #e74c3c;
    border-radius: 50px;
    color: #e74c3c;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
}

.btn-roll:hover {
    background: #e74c3c;
    color: white;
    transform: translateY(-2px);
}

.dice-history {
    margin-top: 15px;
    font-size: 12px;
    color: #7f8c8d;
}

.history-list {
    display: flex;
    gap: 8px;
    justify-content: center;
    margin-top: 8px;
    flex-wrap: wrap;
}

.history-item {
    display: inline-block;
    width: 30px;
    height: 30px;
    background: white;
    border-radius: 8px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 12px;
    border: 1px solid #e0e0e0;
}

.history-item.critical-success {
    background: #27ae60;
    color: white;
    border-color: #27ae60;
}

.history-item.critical-fail {
    background: #e74c3c;
    color: white;
    border-color: #e74c3c;
}

/* Анимации */
@keyframes roll {
    0% {
        transform: rotate(0deg) scale(1);
    }
    25% {
        transform: rotate(90deg) scale(1.1);
    }
    50% {
        transform: rotate(180deg) scale(1.2);
    }
    75% {
        transform: rotate(270deg) scale(1.1);
    }
    100% {
        transform: rotate(360deg) scale(1);
    }
}

.rolling {
    animation: roll 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55);
}

.rolling .dice-shadow {
    animation: shadowPulse 0.5s ease;
}

@keyframes shadowPulse {
    0%, 100% {
        transform: scale(1);
        opacity: 0.6;
    }
    50% {
        transform: scale(1.3);
        opacity: 0.3;
    }
}

/* Адаптивность */
@media (max-width: 768px) {
    .error-content {
        padding: 30px 20px;
    }
    
    .digit {
        font-size: 60px;
    }
    
    .error-404 h1 {
        font-size: 22px;
    }
    
    .error-404 p {
        font-size: 14px;
    }
    
    .error-actions {
        gap: 10px;
    }
    
    .btn-primary, .btn-secondary, .btn-random {
        padding: 10px 20px;
        font-size: 12px;
    }
    
    .dice-roller {
        flex-direction: column;
    }
    
    .dice {
        width: 80px;
        height: 80px;
    }
    
    .dice-face span {
        font-size: 36px;
    }
}
</style>

<script>
let diceHistory = [];

function rollDice() {
    const dice = document.querySelector('.dice');
    const resultSpan = document.getElementById('diceResult');
    
    dice.classList.add('rolling');
    
    setTimeout(() => {
        const result = Math.floor(Math.random() * 20) + 1;
        resultSpan.textContent = result;
        
        // Сохраняем в историю
        diceHistory.unshift(result);
        if (diceHistory.length > 5) diceHistory.pop();
        updateHistory();
        
        // Критический успех или провал
        if (result === 20) {
            showNotification("🎉 КРИТИЧЕСКИЙ УСПЕХ! +100 к удаче! 🎉", "success");
            createConfetti();
        } else if (result === 1) {
            showNotification("💀 КРИТИЧЕСКИЙ ПРОВАЛ! Но не расстраивайтесь, бывает... 💀", "fail");
        } else if (result >= 15) {
            showNotification(`✨ Удача улыбается! Выпало ${result} ✨`, "good");
        } else if (result <= 5) {
            showNotification(`😅 Не повезло... Выпало ${result}`, "bad");
        } else {
            showNotification(`🎲 Выпало ${result}`, "normal");
        }
        
        dice.classList.remove('rolling');
        
        // Вибрация на мобильных
        if (window.navigator && window.navigator.vibrate) {
            window.navigator.vibrate(100);
        }
    }, 500);
}

function updateHistory() {
    const historyList = document.querySelector('.history-list');
    if (!historyList) return;
    
    historyList.innerHTML = diceHistory.map(value => {
        let className = 'history-item';
        if (value === 20) className += ' critical-success';
        if (value === 1) className += ' critical-fail';
        return `<span class="${className}">${value}</span>`;
    }).join('');
    
    if (diceHistory.length === 0) {
        historyList.innerHTML = '<span class="history-item">—</span>';
    }
}

function showNotification(message, type = "normal") {
    const colors = {
        success: '#27ae60',
        good: '#f39c12',
        fail: '#e74c3c',
        bad: '#e67e22',
        normal: '#3498db'
    };
    
    const notification = document.createElement('div');
    notification.textContent = message;
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        left: 50%;
        transform: translateX(-50%);
        background: ${colors[type] || colors.normal};
        color: white;
        padding: 12px 24px;
        border-radius: 50px;
        z-index: 10000;
        animation: slideDown 0.3s ease;
        font-size: 14px;
        font-weight: 500;
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        white-space: nowrap;
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.opacity = '0';
        notification.style.transition = 'opacity 0.3s';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

function createConfetti() {
    for (let i = 0; i < 50; i++) {
        const confetti = document.createElement('div');
        confetti.style.cssText = `
            position: fixed;
            left: ${Math.random() * 100}%;
            top: -10px;
            width: ${Math.random() * 8 + 4}px;
            height: ${Math.random() * 8 + 4}px;
            background: hsl(${Math.random() * 360}, 100%, 50%);
            transform: rotate(${Math.random() * 360}deg);
            animation: confettiFall ${Math.random() * 2 + 1}s linear forwards;
            pointer-events: none;
            z-index: 10001;
        `;
        document.body.appendChild(confetti);
        setTimeout(() => confetti.remove(), 2000);
    }
}

function randomGame() {
    const gameIds = [317, 318, 319, 320]; // ID ваших товаров
    const randomId = gameIds[Math.floor(Math.random() * gameIds.length)];
    window.location.href = `/catalog/detail.php?ID=${randomId}`;
}

// Добавляем стили для анимаций
const style = document.createElement('style');
style.textContent = `
    @keyframes slideDown {
        from {
            transform: translateX(-50%) translateY(-100%);
            opacity: 0;
        }
        to {
            transform: translateX(-50%) translateY(0);
            opacity: 1;
        }
    }
    
    @keyframes confettiFall {
        to {
            transform: translateY(100vh) rotate(360deg);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);
</script>

<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
?>