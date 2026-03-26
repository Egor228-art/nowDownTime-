<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();?>

<?// Включаем отображение ошибок (для разработки)
error_reporting(E_ALL);
ini_set('display_errors', 1);?>

<!DOCTYPE html>
<html lang="ru">
<head>
	<? $APPLICATION->ShowHead(); ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="bitrix_sessid" content="<?= bitrix_sessid() ?>">
    <title>nowDownTime+ — магазин настольных игр</title>
    <!-- Font Awesome для иконок -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
	<div id="panel"><? $APPLICATION->ShowPanel(); ?></div>

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
            margin-top: -13px !important;
            padding-top: 0 !important;
        }
    </style>
    <?endif?>

    <!-- ШАПКА (первая линия) -->
    <div class="top-header">
        <div class="container">
            <!-- Логотип -->
            <a href="/" class="logo">
                <img style="filter: drop-shadow(0 0 10px rgba(109, 19, 19, 0.3));" src="/images/logo.png" alt="nowDownTime+" height="40">
            </a>

            <!-- Поиск -->
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" placeholder="Поиск игр, аксессуаров...">
                <button>Найти</button>
            </div>

            <!-- Юзер и корзина -->
            <div class="user-actions">
                <?if ($USER->IsAuthorized()):?>
                    <!-- Пользователь авторизован -->
                    <div class="user-menu">
                        <a class="user-profile">
                            <div class="user-avatar">
                                <i class="far fa-circle-user"></i>
                            </div>
                            <span class="user-name"><?=$USER->GetFullName() ?: $USER->GetLogin()?></span>
                            <i class="fas fa-chevron-down user-arrow"></i>
                        </a>
                        <div class="user-dropdown">
                            <a href="/personal/"><i class="fas fa-user"></i> Личный кабинет</a>
                            <a href="/personal/cart/">
                                <i class="fas fa-shopping-bag"></i> Корзина 
                                <span class="cart-counter" style="display: none;">0</span>
                            </a>
                            <a href="/personal/order/" class="profile-menu-item"><i class="fas fa-box"></i> Мои заказы</a>
                            <a href="/personal/favorites/"><i class="fas fa-heart"></i> Избранное <span id="favorite-counter" class="counter" style="display: none;">0</span></a>
                            <div class="dropdown-divider"></div>
                            <a href="javascript:void(0)" onclick="logoutUser()" class="logout-link"><i class="fas fa-sign-out-alt"></i> Выйти</a>
                            <!-- href="/?logout=yes&<?=bitrix_sessid_get()?>"-->
                        </div>
                    </div>
                <?else:?>
                    <!-- Не авторизован -->
                    <div class="auth-buttons">
                        <a href="#" onclick="openLoginModal(); return false;" class="user-icon">
                            <i class="far fa-circle-user"></i>
                            <span>Войти</span>
                        </a>
                        <a href="#" onclick="openRegisterModal(); return false;" class="user-icon register-link">
                            <span>Регистрация</span>
                        </a>
                    </div>
                <?endif?>
            </div>
        </div>
    </div>

    <!-- ПОДШАПКА (разделы) -->
    <div class="nav-header">
        <div class="container">
            <ul class="nav-menu">
                <li><a href="/catalog/?SECTION_ID=17"><i class="fas fa-dice-d20"></i> D&D</a></li>
                <li><a href="/catalog/?SECTION_ID=16"><i class="fas fa-chess-board"></i> Настольные игры</a></li>
                <li><a href="/catalog/?SECTION_ID=19"><i class="fas fa-puzzle-piece"></i> Головоломки</a></li>
                <li><a href="/catalog/?SECTION_ID=20"><i class="fas fa-users"></i> Для компаний</a></li>
                <li><a href="/catalog/?SECTION_ID=21"><i class="fas fa-child"></i> Детям</a></li>
                <li><a href="/catalog/?SECTION_ID=18"><i class="fas fa-chess-knight"></i> Стратегии</a></li>
            </ul>
        </div>
    </div>

    <!-- Модальное окно входа -->
    <div class="modal" id="loginModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="modal-close" onclick="closeLoginModal()">×</button>
                    <div class="modal-logo">
                        <i class="fas fa-dragon"></i>
                        <span>nowDownTime+</span>
                    </div>
                    <h2 class="modal-title">Добро пожаловать!</h2>
                </div>
                <div class="modal-body">
                    <div id="loginModalContent">
                        <div id="loginFormErrors" class="login-error" style="display: none;"></div>
                        <div class="modal-loading">
                            <div class="spinner-small"></div>
                            <p>Загрузка формы...</p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <p>Нет аккаунта? <a href="#" onclick="openRegisterModal(); return false;">Зарегистрироваться</a></p>
                    <p><a href="#" onclick="openForgotModal(); return false;">Забыли пароль?</a></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Модальное окно регистрации -->
    <div class="modal" id="registerModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="modal-close" onclick="closeRegisterModal()">×</button>
                    <div class="modal-logo">
                        <i class="fas fa-dragon"></i>
                        <span>nowDownTime+</span>
                    </div>
                    <h2 class="modal-title">Создать аккаунт</h2>
                </div>
                <div class="modal-body">
                    <!-- ДОБАВЛЯЕМ ЭТОТ БЛОК ДЛЯ ОШИБОК -->
                    <div id="registerFormErrors" class="login-error" style="display: none;"></div>
                    
                    <div id="registerModalContent">
                        <div class="modal-loading">
                            <div class="spinner-small"></div>
                            <p>Загрузка формы...</p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <p>Уже есть аккаунт? <a href="#" onclick="openLoginModal(); return false;">Войти</a></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Затемняющий фон -->
    <div class="modal-backdrop" id="modalBackdrop" onclick="closeAllModals()"></div>
    <script src="<?=SITE_TEMPLATE_PATH?>/javascript.js"></script>
    <script>
    function logoutUser() {
        // Получаем текущий URL страницы
        var currentUrl = window.location.href;
        
        // Получаем sessid для Битрикса
        var sessid = '';
        
        // Пытаемся получить sessid из мета-тега или глобальной переменной
        if (window.bitrix_sessid) {
            sessid = window.bitrix_sessid;
        } else {
            // Альтернативный способ получить sessid
            var sessidMeta = document.querySelector('meta[name="bitrix_sessid"]');
            if (sessidMeta) {
                sessid = sessidMeta.content;
            }
        }
        
        // Формируем URL для выхода с сохранением текущей страницы
        var logoutUrl = '/?logout=yes&sessid=' + sessid + '&backurl=' + encodeURIComponent(currentUrl);
        
        // Выполняем выход и перенаправление
        window.location.href = logoutUrl;
    }

    window.updateCartCounter = function(force = false) {    
        // Если вызвано принудительно, сначала проверим, есть ли счетчик в DOM
        if (force) {
            let counters = document.querySelectorAll('.cart-counter');
            
            // Если счетчиков нет, но они должны быть — создадим временный
            if (counters.length === 0) {
                const cartLink = document.querySelector('a[href*="cart"]');
                if (cartLink) {
                    const newCounter = document.createElement('span');
                    newCounter.className = 'cart-counter';
                    newCounter.style.display = 'none';
                    cartLink.appendChild(newCounter);
                }
            }
        }
    
    fetch('/ajax/add_to_cart.php?action=get&t=' + Date.now()) // Добавим timestamp чтобы избежать кеша
        .then(response => response.json())
        .then(data => {            
            if (data.success) {
                // Ищем все элементы с классом cart-counter
                let counters = document.querySelectorAll('.cart-counter');
                
                if (counters.length === 0) {
                    // Пробуем найти ссылку на корзину и добавить счетчик
                    const cartLinks = document.querySelectorAll('a[href*="cart"], a[href*="basket"]');
                    cartLinks.forEach(link => {
                        const newCounter = document.createElement('span');
                        newCounter.className = 'cart-counter';
                        newCounter.textContent = data.cart_count;
                        newCounter.style.display = data.cart_count > 0 ? 'inline' : 'none';
                        link.appendChild(newCounter);
                    });
                    
                    // Перечитываем счетчики
                    counters = document.querySelectorAll('.cart-counter');
                }
            }
        })
        .catch(error => {
            console.error('🔴 Ошибка fetch:', error);
        });
};

// Вызываем при загрузке
document.addEventListener('DOMContentLoaded', function() {
    console.log('Header loaded');
    window.updateCartCounter(true); // Принудительно при загрузке
});

// Обновляем при возвращении на страницу
window.addEventListener('pageshow', function(event) {
    if (event.persisted) {
        console.log('Page restored from cache');
        window.updateCartCounter(tue);
    }
});

(function() {
    // Функция для установки отступа
    function setHeaderMargin() {
        const topHeader = document.querySelector('.top-header');
        const navHeader = document.querySelector('.nav-header');
        
        if (topHeader && navHeader) {
            const headerHeight = topHeader.offsetHeight + navHeader.offsetHeight;
            
            // Устанавливаем и CSS-переменную, и прямой стиль для надежности
            document.documentElement.style.setProperty('--header-height', headerHeight + 'px');
            document.body.style.marginTop = headerHeight + 'px';
            
            console.log('Header margin set to:', headerHeight + 'px');
            return true;
        }
        return false;
    }
    
    // Пытаемся установить сразу
    if (!setHeaderMargin()) {
        // Если элементы еще не загружены - пробуем снова
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', setHeaderMargin);
        } else {
            // Если DOM уже загружен, но элементов нет - ждем
            setTimeout(setHeaderMargin, 50);
        }
    }
    
    // Страховка - проверяем через небольшие интервалы
    let attempts = 0;
    const interval = setInterval(function() {
        attempts++;
        if (setHeaderMargin() || attempts > 20) { // Максимум 20 попыток (1 секунда)
            clearInterval(interval);
        }
    }, 50);
})();
</script>