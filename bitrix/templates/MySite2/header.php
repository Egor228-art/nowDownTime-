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
    <title>nowDownTime+ — магазин настольных игр</title>
    <!-- Font Awesome для иконок -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
	<div id="panel"><? $APPLICATION->ShowPanel(); ?></div>

    <!-- ШАПКА (первая линия) -->
    <div class="top-header">
        <div class="container">
            <!-- Логотип -->
            <a href="/" class="logo">
                <img src="/images/logo.png" alt="nowDownTime+" height="40">
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
                            <a href="/personal/orders/"><i class="fas fa-shopping-bag"></i> Мои заказы</a>
                            <a href="/personal/favorites/"><i class="fas fa-heart"></i> Избранное</a>
                            <div class="dropdown-divider"></div>
                            <a href="/?logout=yes&<?=bitrix_sessid_get()?>" class="logout-link"><i class="fas fa-sign-out-alt"></i> Выйти</a>
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
                <li><a href="#"><i class="fas fa-dice-d20"></i> D&D</a></li>
                <li><a href="#"><i class="fas fa-chess-board"></i> Настольные игры</a></li>
                <li><a href="#"><i class="fas fa-puzzle-piece"></i> Головоломки</a></li>
                <li><a href="#"><i class="fas fa-users"></i> Для компаний</a></li>
                <li><a href="#"><i class="fas fa-child"></i> Детям</a></li>
                <li><a href="#" class="highlight"><i class="fas fa-gift"></i> Акции</a></li>
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
<script>
    // Функции для работы с модальными окнами
    function openLoginModal() {
        const modal = document.getElementById('loginModal');
        const backdrop = document.getElementById('modalBackdrop');
        
        if (modal && backdrop) {
            modal.classList.add('show');
            backdrop.classList.add('show');
            document.body.style.overflow = 'hidden';
            
            // Загружаем форму авторизации
            loadLoginForm();
        }
    }

    function closeLoginModal() {
        const modal = document.getElementById('loginModal');
        const backdrop = document.getElementById('modalBackdrop');
        
        if (modal && backdrop) {
            modal.classList.remove('show');
            backdrop.classList.remove('show');
            document.body.style.overflow = '';
        }
    }

    function closeAllModals() {
        closeLoginModal();
    }

    function loadLoginForm() {
        const modalContent = document.getElementById('loginModalContent');
        
        // Показываем загрузку
        modalContent.innerHTML = `
            <div class="modal-loading">
                <div class="spinner-small"></div>
                <p>Загрузка формы...</p>
            </div>
        `;
        
        // Формируем правильный URL с учетом домена
        const ajaxUrl = window.location.origin + '/ajax.php';
        
        fetch(ajaxUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=getLoginForm'
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.text();
        })
        .then(html => {
            modalContent.innerHTML = html;
        })
        .catch(error => {
            console.error('Error:', error);
            modalContent.innerHTML = `
                <div class="login-error show">
                    <i class="fas fa-exclamation-circle"></i>
                    Ошибка загрузки формы. 
                    <a href="/login/" style="color: #991b1b; text-decoration: underline;">Перейти на страницу входа</a>
                </div>
            `;
        });
    }

    function submitLoginForm(form) {
        const formData = new FormData(form);
        formData.append('action', 'login');
        
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<span>Вход...</span> <i class="fas fa-spinner fa-spin"></i>';
        submitBtn.disabled = true;
        
        const errorsDiv = document.getElementById('loginFormErrors');
        errorsDiv.style.display = 'none';
        errorsDiv.innerHTML = '';
        
        fetch('/ajax.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success === 'Y') {
                window.location.reload();  // Просто перезагружаем страницу
            } else {
                errorsDiv.innerHTML = '<i class="fas fa-exclamation-circle"></i> ' + (data.error || 'Ошибка входа');
                errorsDiv.style.display = 'flex';
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }
        })
        .catch(error => {
            console.error('Fetch error:', error);
            errorsDiv.innerHTML = '<i class="fas fa-exclamation-circle"></i> Ошибка соединения с сервером';
            errorsDiv.style.display = 'flex';
            
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        });
        
        return false;
    }

    // Заглушки для других функций
    function openRegisterModal() {
        // Закрываем все модалки
        closeAllModals();
        
        const modal = document.getElementById('registerModal');
        const backdrop = document.getElementById('modalBackdrop');
        
        if (modal && backdrop) {
            modal.classList.add('show');
            backdrop.classList.add('show');
            document.body.style.overflow = 'hidden';
            
            // Загружаем форму регистрации
            loadRegisterForm();
        }
    }

    function openForgotModal() {
        closeLoginModal();
        window.location.href = '/forgot/';
    }

    // Закрытие по Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeAllModals();
        }
    });

    // Инициализация при загрузке страницы
    document.addEventListener('DOMContentLoaded', function() {
        // Предотвращаем закрытие при клике на модалку
        const modal = document.getElementById('loginModal');
        if (modal) {
            modal.addEventListener('click', function(e) {
                e.stopPropagation();
            });
        }
        
        const modalDialog = document.querySelector('.modal-dialog');
        if (modalDialog) {
            modalDialog.addEventListener('click', function(e) {
                e.stopPropagation();
            });
        }
    });

    //  МОДАЛЬНОЕ ОКНО РЕГИСТРАЦИИ

        // Открыть модальное окно регистрации
    function openRegisterModal() {
        closeAllModals(); // Закрываем все модалки
        
        const modal = document.getElementById('registerModal');
        const backdrop = document.getElementById('modalBackdrop');
        
        if (modal && backdrop) {
            modal.classList.add('show');
            backdrop.classList.add('show');
            document.body.style.overflow = 'hidden';
            
            // Загружаем форму регистрации
            loadRegisterForm();
        }
    }

    // Закрыть модальное окно регистрации
    function closeRegisterModal() {
        const modal = document.getElementById('registerModal');
        const backdrop = document.getElementById('modalBackdrop');
        
        if (modal && backdrop) {
            modal.classList.remove('show');
            backdrop.classList.remove('show');
            document.body.style.overflow = '';
        }
    }

    // Загрузить форму регистрации
    function loadRegisterForm() {
        const modalContent = document.getElementById('registerModalContent');
        
        // Показываем загрузку
        modalContent.innerHTML = `
            <div class="modal-loading">
                <div class="spinner-small"></div>
                <p>Загрузка формы...</p>
            </div>
        `;
        
        const ajaxUrl = window.location.origin + '/ajax.php';
        
        fetch(ajaxUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=getRegisterForm'
        })
        .then(response => response.text())
        .then(html => {
            modalContent.innerHTML = html;
            
            // Инициализируем проверку пароля
            initPasswordCheck();
            // Инициализируем маску для телефона
            initPhoneMask();
        })
        .catch(error => {
            console.error('Error:', error);
            modalContent.innerHTML = `
                <div class="login-error show">
                    <i class="fas fa-exclamation-circle"></i>
                    Ошибка загрузки формы. 
                    <a href="/register/" style="color: #991b1b; text-decoration: underline;">Перейти на страницу регистрации</a>
                </div>
            `;
        });
    }

    // Отправить форму регистрации
    function submitRegisterForm(form) {
        const formData = new FormData(form);
        formData.append('action', 'register');
        
        // Показываем состояние загрузки на кнопке
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<span>Регистрация...</span> <i class="fas fa-spinner fa-spin"></i>';
        submitBtn.disabled = true;
        
        // Скрываем предыдущие ошибки
        const errorsDiv = document.getElementById('registerFormErrors');
        if (errorsDiv) {
            errorsDiv.style.display = 'none';
            errorsDiv.innerHTML = '';
        }
        
        // Очищаем телефон перед отправкой
        const phone = formData.get('PERSONAL_PHONE');
        if (phone) {
            const cleanPhone = phone.replace(/\D/g, '');
            if (cleanPhone.length === 11 && cleanPhone[0] === '7') {
                formData.set('PERSONAL_PHONE', cleanPhone.substring(1));
            } else {
                formData.set('PERSONAL_PHONE', cleanPhone);
            }
        }
        
        fetch('/ajax.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success === 'Y') {
                // УСПЕШНАЯ РЕГИСТРАЦИЯ - сразу редирект в личный кабинет
                window.location.href = data.redirect;
            } else {
                // Показываем ошибку
                if (errorsDiv) {
                    errorsDiv.innerHTML = '<i class="fas fa-exclamation-circle"></i> ' + (data.error || 'Ошибка регистрации');
                    errorsDiv.style.display = 'flex';
                }
                
                // Возвращаем кнопку в исходное состояние
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            if (errorsDiv) {
                errorsDiv.innerHTML = '<i class="fas fa-exclamation-circle"></i> Ошибка соединения';
                errorsDiv.style.display = 'flex';
            }
            
            // Возвращаем кнопку в исходное состояние
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        });
        
        return false;
    }

    // Инициализация проверки пароля
    function initPasswordCheck() {
        const password = document.getElementById('registerPassword');
        const confirm = document.getElementById('registerConfirmPassword');
        const strengthDiv = document.getElementById('passwordStrength');
        const matchDiv = document.getElementById('passwordMatch');
        
        if (password) {
            password.addEventListener('input', function() {
                checkPasswordStrength(this.value);
                if (confirm && confirm.value) {
                    checkPasswordMatch(this.value, confirm.value);
                }
            });
        }
        
        if (confirm) {
            confirm.addEventListener('input', function() {
                if (password) {
                    checkPasswordMatch(password.value, this.value);
                }
            });
        }
    }

    // Проверка сложности пароля
    function checkPasswordStrength(password) {
        const strengthBar = document.querySelector('.strength-bar');
        if (!strengthBar) return;
        
        let strength = 0;
        
        if (password.length >= 6) strength += 1;
        if (password.length >= 8) strength += 1;
        if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength += 1;
        if (/\d/.test(password)) strength += 1;
        if (/[^a-zA-Z0-9]/.test(password)) strength += 1;
        
        const width = (strength / 5) * 100;
        strengthBar.style.width = width + '%';
        
        if (strength <= 2) {
            strengthBar.style.background = '#ef4444';
        } else if (strength <= 4) {
            strengthBar.style.background = '#f59e0b';
        } else {
            strengthBar.style.background = '#10b981';
        }
    }

    // Проверка совпадения паролей
    function checkPasswordMatch(password, confirm) {
        const matchDiv = document.getElementById('passwordMatch');
        if (!matchDiv) return;
        
        if (password === confirm) {
            matchDiv.innerHTML = '<i class="fas fa-check-circle" style="color: #10b981;"></i> Пароли совпадают';
            matchDiv.style.color = '#10b981';
        } else {
            matchDiv.innerHTML = '<i class="fas fa-exclamation-circle" style="color: #ef4444;"></i> Пароли не совпадают';
            matchDiv.style.color = '#ef4444';
        }
    }

    // Инициализация маски телефона
    function initPhoneMask() {
        const phoneInput = document.getElementById('registerPhone');
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

    // Обновляем функцию closeAllModals
    function closeAllModals() {
        closeLoginModal();
        closeRegisterModal();
    }

    // Переопределяем openLoginModal чтобы закрывать регистрацию
    const originalOpenLoginModal = openLoginModal;
    openLoginModal = function() {
        closeRegisterModal();
        originalOpenLoginModal();
    }

    // Функция для проверки уникальности логина
    function checkLoginUnique(login) {
        if (!login || login.length < 3) return;
        
        const statusDiv = document.getElementById('loginStatus');
        if (!statusDiv) return;
        
        // Показываем статус
        statusDiv.classList.add('show');
        statusDiv.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Проверка...';
        statusDiv.className = 'field-status show';
        
        fetch('/ajax.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=check_field&field=LOGIN&value=' + encodeURIComponent(login)
        })
        .then(response => response.json())
        .then(data => {
            if (data.unique) {
                statusDiv.innerHTML = '<i class="fas fa-check-circle"></i> Логин свободен';
                statusDiv.className = 'field-status show success';
            } else {
                statusDiv.innerHTML = '<i class="fas fa-exclamation-circle"></i> ' + data.message;
                statusDiv.className = 'field-status show error';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            statusDiv.classList.remove('show');
        });
    }

    // Функция для проверки уникальности email (обновленная)
    function checkEmailUnique(email) {
        if (!email || !email.includes('@')) return;
        
        const statusDiv = document.getElementById('emailStatus');
        if (!statusDiv) return;
        
        // Показываем статус
        statusDiv.classList.add('show');
        statusDiv.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Проверка...';
        statusDiv.className = 'field-status show';
        
        fetch('/ajax.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=check_field&field=EMAIL&value=' + encodeURIComponent(email)
        })
        .then(response => response.json())
        .then(data => {
            if (data.unique) {
                statusDiv.innerHTML = '<i class="fas fa-check-circle"></i> Email свободен';
                statusDiv.className = 'field-status show success';
            } else {
                statusDiv.innerHTML = '<i class="fas fa-exclamation-circle"></i> ' + data.message;
                statusDiv.className = 'field-status show error';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            statusDiv.classList.remove('show');
        });
    }

    // Универсальная функция для показа/скрытия пароля
    function togglePassword(btn) {
        const input = btn.closest('.input-wrapper').querySelector('input');
        const icon = btn.querySelector('i');
        
        if (input && icon) {
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    }

    // Инициализация всех функций при загрузке формы
    function initRegisterForm() {
        const password = document.getElementById('registerPassword');
        const confirm = document.getElementById('registerConfirmPassword');
        
        if (password) {
            password.addEventListener('input', function() {
                checkPasswordStrength(this.value);
                if (confirm && confirm.value) {
                    checkPasswordMatch(this.value, confirm.value);
                }
            });
        }
        
        if (confirm) {
            confirm.addEventListener('input', function() {
                if (password) {
                    checkPasswordMatch(password.value, this.value);
                }
            });
        }
        
        initPhoneMask();
    }

    // Проверка сложности пароля
    function checkPasswordStrength(password) {
        const strengthBar = document.querySelector('#passwordStrength .strength-bar');
        if (!strengthBar) return;
        
        let strength = 0;
        
        // Длина
        if (password.length >= 6) strength += 1;
        if (password.length >= 8) strength += 1;
        
        // Символы
        if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength += 1;
        if (/\d/.test(password)) strength += 1;
        if (/[^a-zA-Z0-9]/.test(password)) strength += 1;
        
        const width = Math.min((strength / 5) * 100, 100);
        strengthBar.style.width = width + '%';
        
        if (strength <= 2) {
            strengthBar.style.background = '#ef4444';
        } else if (strength <= 4) {
            strengthBar.style.background = '#f59e0b';
        } else {
            strengthBar.style.background = '#10b981';
        }
    }

    // Проверка совпадения паролей
    function checkPasswordMatch(password, confirm) {
        const matchDiv = document.getElementById('passwordMatch');
        if (!matchDiv) return;
        
        if (password === confirm) {
            matchDiv.innerHTML = '<i class="fas fa-check-circle" style="color: #10b981;"></i> Пароли совпадают';
            matchDiv.style.color = '#10b981';
        } else {
            matchDiv.innerHTML = '<i class="fas fa-exclamation-circle" style="color: #ef4444;"></i> Пароли не совпадают';
            matchDiv.style.color = '#ef4444';
        }
    }

    // Маска для телефона
    function initPhoneMask() {
        const phoneInput = document.getElementById('registerPhone');
        if (!phoneInput) return;
        
        phoneInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            
            if (value.length > 0) {
                if (value[0] === '7' || value[0] === '8') {
                    value = '7' + value.substring(1);
                } else {
                    value = '7' + value;
                }
                
                let formatted = '+7';
                if (value.length > 1) {
                    formatted += ' (' + value.substring(1, 4);
                }
                if (value.length >= 5) {
                    formatted += ') ' + value.substring(4, 7);
                }
                if (value.length >= 8) {
                    formatted += '-' + value.substring(7, 9);
                }
                if (value.length >= 10) {
                    formatted += '-' + value.substring(9, 11);
                }
                e.target.value = formatted;
            }
        });
    }

    // Обновляем функцию loadRegisterForm
    function loadRegisterForm() {
        const modalContent = document.getElementById('registerModalContent');
        if (!modalContent) return;
        
        modalContent.innerHTML = `
            <div class="modal-loading">
                <div class="spinner-small"></div>
                <p>Загрузка формы...</p>
            </div>
        `;
        
        fetch('/ajax.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=getRegisterForm'
        })
        .then(response => response.text())
        .then(html => {
            modalContent.innerHTML = html;
            initRegisterForm();
        })
        .catch(error => {
            console.error('Error:', error);
            modalContent.innerHTML = `
                <div class="login-error show">
                    <i class="fas fa-exclamation-circle"></i>
                    Ошибка загрузки формы
                </div>
            `;
        });
    }
</script>