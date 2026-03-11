// Просто чтобы добавить немного жизни: показываем alert при клике на кнопку "Найти" или "В корзину"
document.querySelector('.search-box button').addEventListener('click', function(e) {
    e.preventDefault();
    alert('Поиск: ' + document.querySelector('.search-box input').value);
});

document.querySelector('.cart-btn').addEventListener('click', function() {
    alert('Переход в корзину');
});

document.querySelector('.hero-btn').addEventListener('click', function() {
    alert('Скоро здесь будет каталог новинок!');
});

// Кнопки добавления в корзину
document.querySelectorAll('.add-to-cart').forEach(btn => {
    btn.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        alert('Товар добавлен в корзину!');
    });
});



// Функции для работы с модальными окнами
function openLoginModal() {
    document.getElementById('loginModal').classList.add('show');
    document.getElementById('modalBackdrop').classList.add('show');
    document.body.style.overflow = 'hidden';
    
    // Загружаем форму входа через AJAX
    fetch('/login/?ajax=Y')
        .then(response => response.text())
        .then(html => {
            document.getElementById('loginModalContent').innerHTML = html;
        })
        .catch(error => {
            document.getElementById('loginModalContent').innerHTML = '<div class="login-error">Ошибка загрузки формы</div>';
        });
}

function closeLoginModal() {
    document.getElementById('loginModal').classList.remove('show');
    document.getElementById('modalBackdrop').classList.remove('show');
    document.body.style.overflow = '';
}

function closeAllModals() {
    closeLoginModal();
    // Здесь можно добавить закрытие других модалок
}

// Закрытие по Escape
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeAllModals();
    }
});

// Функции для открытия других модалок (можно сделать аналогично)
function openRegisterModal() {
    closeLoginModal();
    alert('Здесь будет модальное окно регистрации');
    // TODO: добавить модалку регистрации
}

function openForgotModal() {
    closeLoginModal();
    alert('Здесь будет модальное окно восстановления пароля');
    // TODO: добавить модалку восстановления
}

function togglePassword(btn) {
    // Находим предыдущий элемент (поле ввода)
    const input = btn.previousElementSibling;
    const icon = btn.querySelector('i');
    
    if (input && input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else if (input) {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}