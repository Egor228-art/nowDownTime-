console.log('JavaScript file loaded successfully');
console.log('Available functions:', {
    openLoginModal: typeof openLoginModal,
    closeLoginModal: typeof closeLoginModal,
    openRegisterModal: typeof openRegisterModal
});

// Глобальная функция обновления счетчика
window.updateCartCounter = function() {
    fetch('/ajax/add_to_cart.php?action=get')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const counters = document.querySelectorAll('.cart-counter');
                counters.forEach(counter => {
                    counter.textContent = data.cart_count;
                    counter.style.display = data.cart_count > 0 ? 'inline' : 'none';
                });
            }
        })
        .catch(error => console.error('Error updating cart counter:', error));
};

// Обновляем при загрузке
document.addEventListener('DOMContentLoaded', function() {
    if (window.updateCartCounter) {
        window.updateCartCounter();
    }

    loadPopularProducts();
});

// Обновляем при возвращении на страницу
window.addEventListener('pageshow', function(event) {
    if (event.persisted && window.updateCartCounter) {
        window.updateCartCounter();
    }
});

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

    function loadPopularProducts() {
        const grid = document.getElementById('popularGamesGrid');
        if (!grid) return;
        
        fetch('/ajax/get_popular_products.php')
            .then(response => response.json())
            .then(products => {
                if (products.error) {
                    console.error(products.error);
                    grid.innerHTML = '<div class="alert alert-info">Нет данных о популярных товарах</div>';
                    return;
                }
                
                if (products.length === 0) {
                    grid.innerHTML = '<div class="alert alert-info">Популярные товары появятся после просмотров</div>';
                    return;
                }
                
                let html = '';
                products.forEach(product => {
                    html += `
                        <div class="game-card" onclick="window.location='${product.url}'">
                            <div class="game-image">
                                ${product.image ? `<img src="${product.image}" alt="${product.name}">` : '<i class="fas fa-dragon"></i>'}
                                ${product.badge}
                            </div>
                            <div class="game-info">
                                <div class="game-category">${product.category || 'Настольная игра'}</div>
                                <h3>${product.name}</h3>
                                <div class="game-meta">
                                    <span><i class="fas fa-user-friends"></i> ${product.players_count} игр.</span>
                                    <span><i class="fas fa-clock"></i> ${product.game_time} мин</span>
                                </div>
                                <div class="game-price">
                                    <span class="price">${product.price}</span>
                                    <button class="add-to-cart" onclick="event.stopPropagation(); addToCart(${product.id})">
                                        <i class="fas fa-shopping-cart"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    `;
                });
                
                grid.innerHTML = html;
            })
            .catch(error => {
                console.error('Ошибка загрузки популярных товаров:', error);
                grid.innerHTML = '<div class="alert alert-danger">Ошибка загрузки товаров</div>';
            });
    }

    // Простое уведомление
    function showNotification(message) {
        const notification = document.createElement('div');
        notification.className = 'notification';
        notification.innerHTML = `
            <i class="fas fa-check-circle"></i>
            <span>${message}</span>
        `;
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.classList.add('show');
        }, 10);
        
        setTimeout(() => {
            notification.classList.remove('show');
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    }
}

// Функция обновления счетчика избранного
function updateFavoriteCount() {
    fetch('/ajax/toggle_favorite.php?action=get')
        .then(response => response.json())
        .then(data => {
            const countElement = document.getElementById('favoriteCount');
            if (countElement) {
                const count = data.count || 0;
                countElement.textContent = count;
                countElement.style.display = count > 0 ? 'inline-flex' : 'none';
            }
        })
        .catch(error => console.error('Ошибка обновления счетчика:', error));
}

// Вызываем при загрузке страницы
document.addEventListener('DOMContentLoaded', function() {
    updateFavoriteCount();
});