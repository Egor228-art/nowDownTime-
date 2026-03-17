// Функция для показа/скрытия пароля
function togglePassword(btn) {
    const wrapper = btn.closest('.password-wrapper');
    const input = wrapper.querySelector('.password-input');
    const icon = btn.querySelector('i');
    
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

// Генератор случайного пароля
function generatePassword() {
    const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*';
    const passwordInput = document.getElementById('newPassword');
    const confirmInput = document.getElementById('confirmPassword');
    
    let password = '';
    for (let i = 0; i < 12; i++) {
        password += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    
    passwordInput.value = password;
    confirmInput.value = password;
    
    // Анимация кубика
    const dice = document.querySelector('.password-action.dice i');
    if (dice) {
        dice.style.transform = 'rotate(360deg)';
        setTimeout(() => {
            dice.style.transform = 'none';
        }, 300);
    }
    
    checkPasswordStrength(password);
    checkPasswordMatch(password, password);
}

// Проверка сложности пароля
function checkPasswordStrength(password) {
    const strengthBar = document.querySelector('.strength-bar');
    if (!strengthBar) return;
    
    let strength = 0;
    
    if (password.length >= 8) strength += 1;
    if (password.length >= 10) strength += 1;
    if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength += 1;
    if (/\d/.test(password)) strength += 1;
    if (/[^a-zA-Z0-9]/.test(password)) strength += 1;
    
    const width = (strength / 5) * 100;
    strengthBar.style.width = width + '%';
    
    if (strength <= 2) strengthBar.style.background = '#ef4444';
    else if (strength <= 3) strengthBar.style.background = '#f59e0b';
    else if (strength <= 4) strengthBar.style.background = 'var(--Gold)';
    else strengthBar.style.background = '#10b981';
}

// Проверка совпадения паролей
function checkPasswordMatch(password, confirm) {
    const matchDiv = document.getElementById('passwordMatch');
    if (!matchDiv) return;
    
    if (confirm.length > 0) {
        if (password === confirm) {
            matchDiv.innerHTML = '<i class="fas fa-check-circle"></i> Пароли совпадают';
            matchDiv.className = 'password-match success';
        } else {
            matchDiv.innerHTML = '<i class="fas fa-exclamation-circle"></i> Пароли не совпадают';
            matchDiv.className = 'password-match error';
        }
    } else {
        matchDiv.innerHTML = '';
    }
}

// Функции для модальных окон
function openCollectionModal() {
    document.getElementById('collectionModal').classList.add('show');
    document.body.style.overflow = 'hidden';
}

function closeCollectionModal() {
    document.getElementById('collectionModal').classList.remove('show');
    document.body.style.overflow = '';
}

function openAchievementsModal() {
    document.getElementById('achievementsModal').classList.add('show');
    document.body.style.overflow = 'hidden';
}

function closeAchievementsModal() {
    document.getElementById('achievementsModal').classList.remove('show');
    document.body.style.overflow = '';
}

// Закрытие по Escape
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeCollectionModal();
        closeAchievementsModal();
    }
});

// Инициализация
document.addEventListener('DOMContentLoaded', function() {
    const newPass = document.getElementById('newPassword');
    const confirmPass = document.getElementById('confirmPassword');
    
    if (newPass && confirmPass) {
        newPass.addEventListener('input', function() {
            checkPasswordStrength(this.value);
            if (confirmPass.value) {
                checkPasswordMatch(this.value, confirmPass.value);
            }
        });
        
        confirmPass.addEventListener('input', function() {
            if (newPass.value) {
                checkPasswordMatch(newPass.value, this.value);
            }
        });
    }
    
    // Маска для телефона
    const phoneInput = document.getElementById('phoneInput');
    if (phoneInput) {
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
});

// Оптимизированный параллакс с throttling
let ticking = false;
let lastOffset = 0;

function updateParallax() {
    const bgImage = document.getElementById('parallaxBg');
    if (!bgImage) return;
    
    const scrollY = window.scrollY;
    const windowHeight = window.innerHeight;
    const docHeight = document.documentElement.scrollHeight - windowHeight;
    
    // Нормализуем значение от 0 до 1
    const progress = Math.min(scrollY / docHeight, 1);
    
    // Фон двигается от -50px до 50px в зависимости от прогресса
    const offset = -50 + (progress * 100);
    
    // Плавное изменение
    lastOffset += (offset - lastOffset) * 0.1;
    
    bgImage.style.transform = 'translateY(' + lastOffset + 'px)';
    
    ticking = false;
}

window.addEventListener('scroll', function() {
    if (!ticking) {
        window.requestAnimationFrame(updateParallax);
        ticking = true;
    }
});

// Инициализация
document.addEventListener('DOMContentLoaded', function() {
    const bgImage = document.getElementById('parallaxBg');
    if (bgImage) {
        bgImage.style.transition = 'transform 0.05s linear';
        updateParallax();
    }
});

// Функция для обновления прогресс-бара
function updateProgressBar(percent) {
    const progressFill = document.querySelector('.progress-fill');
    if (progressFill) {
        progressFill.style.width = percent + '%';
        
        // Меняем цвет в зависимости от процента (опционально)
        if (percent < 30) {
            progressFill.style.background = 'linear-gradient(90deg, #ff6b6b, #ff8e8e)';
        } else if (percent < 70) {
            progressFill.style.background = 'linear-gradient(90deg, var(--Gold), #ffaa00)';
        } else {
            progressFill.style.background = 'linear-gradient(90deg, var(--Gold), var(--DragonLight))';
        }
    }
}