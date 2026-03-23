// ============ ГАЛЕРЕЯ-КАРУСЕЛЬ (5 ПОЗИЦИЙ) ==========
let carouselItems = [];
let currentIndex = 0;
let isAnimating = false;

function initCarousel() {
    const track = document.getElementById('carouselTrack');
    if (!track) return;
    
    carouselItems = Array.from(document.querySelectorAll('.carousel-item'));
    if (carouselItems.length === 0) return;
    
    // ВСЕГДА начинаем с первого изображения, а не ищем center
    currentIndex = 0; // Всегда с первого
    
    // Устанавливаем transition для всех элементов
    carouselItems.forEach(item => {
        item.style.transition = 'all 0.5s cubic-bezier(0.4, 0, 0.2, 1)';
    });
    
    createDots();
    updatePositions();
    startAutoScroll();
    
    // Останавливаем автоскроллинг при наведении на карусель
    const gallery = document.querySelector('.gallery-carousel');
    if (gallery) {
        gallery.addEventListener('mouseenter', stopAutoScroll);
        gallery.addEventListener('mouseleave', startAutoScroll);
    }
    
    // Обработчики кнопок
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    
    if (prevBtn) {
        const newPrev = prevBtn.cloneNode(true);
        prevBtn.parentNode.replaceChild(newPrev, prevBtn);
        newPrev.addEventListener('click', (e) => {
            e.preventDefault();
            if (!isAnimating) prevImage();
        });
    }
    
    if (nextBtn) {
        const newNext = nextBtn.cloneNode(true);
        nextBtn.parentNode.replaceChild(newNext, nextBtn);
        newNext.addEventListener('click', (e) => {
            e.preventDefault();
            if (!isAnimating) nextImage();
        });
    }
    
    // Клики по изображениям
    track.addEventListener('click', (e) => {
        if (isAnimating) return;
        const item = e.target.closest('.carousel-item');
        if (!item) return;
        
        if (item.classList.contains('left')) {
            prevImage();
        } else if (item.classList.contains('right')) {
            nextImage();
        }
    });
}

// ============ АВТОСКРОЛЛИНГ КАРУСЕЛИ ==========
let autoScrollInterval = null;
let autoScrollDelay = 5000; // 5 секунд

function startAutoScroll() {
    if (autoScrollInterval) clearInterval(autoScrollInterval);
    autoScrollInterval = setInterval(() => {
        if (!isAnimating && carouselItems.length > 1) {
            nextImage();
        }
    }, autoScrollDelay);
}

function stopAutoScroll() {
    if (autoScrollInterval) {
        clearInterval(autoScrollInterval);
        autoScrollInterval = null;
    }
}

function updatePositions() {
    const total = carouselItems.length;
    // Вычисляем индексы для 5 позиций
    const pos2 = (currentIndex - 2 + total) % total; // далеко левое (невидимое)
    const pos1 = (currentIndex - 1 + total) % total; // левое
    const pos0 = currentIndex;                       // центральное
    const pos_1 = (currentIndex + 1) % total;        // правое
    const pos_2 = (currentIndex + 2) % total;        // далеко правое (невидимое)
    
    // Получаем размеры контейнера
    const wrapper = document.querySelector('.carousel-wrapper');
    const containerWidth = wrapper ? wrapper.clientWidth : window.innerWidth;
    
    // Рассчитываем размеры и позиции
    const centerWidth = containerWidth * 0.7;
    const sideWidth = containerWidth * 0.25;
    const invisibleWidth = containerWidth * 0.15;
    
    const centerLeft = (containerWidth - centerWidth) / 2;
    const leftLeft = containerWidth * 0.02;
    const rightLeft = containerWidth - sideWidth - (containerWidth * 0.02);
    const farLeft = -invisibleWidth; // за левым краем
    const farRight = containerWidth;  // за правым краем
    
    const centerHeight = '85%';
    const sideHeight = '55%';
    const invisibleHeight = '40%';
    
    // Обновляем позиции для всех 5 элементов
    carouselItems.forEach((item, idx) => {
        if (idx === pos0) {
            // Центральное (видимое)
            item.style.width = `${centerWidth}px`;
            item.style.height = centerHeight;
            item.style.left = `${centerLeft}px`;
            item.style.opacity = '1';
            item.style.filter = 'blur(0px)';
            item.style.zIndex = '20';
            item.style.pointerEvents = 'auto';
            item.classList.add('center');
            item.classList.remove('left', 'right', 'far-left', 'far-right');
        } 
        else if (idx === pos1) {
            // Левое (видимое)
            item.style.width = `${sideWidth}px`;
            item.style.height = sideHeight;
            item.style.left = `${leftLeft}px`;
            item.style.opacity = '0.6';
            item.style.filter = 'blur(2px)';
            item.style.zIndex = '10';
            item.style.pointerEvents = 'auto';
            item.classList.add('left');
            item.classList.remove('center', 'right', 'far-left', 'far-right');
        }
        else if (idx === pos_1) {
            // Правое (видимое)
            item.style.width = `${sideWidth}px`;
            item.style.height = sideHeight;
            item.style.left = `${rightLeft}px`;
            item.style.opacity = '0.6';
            item.style.filter = 'blur(2px)';
            item.style.zIndex = '10';
            item.style.pointerEvents = 'auto';
            item.classList.add('right');
            item.classList.remove('center', 'left', 'far-left', 'far-right');
        }
        else if (idx === pos2) {
            // Далеко левое (невидимое)
            item.style.width = `${invisibleWidth}px`;
            item.style.height = invisibleHeight;
            item.style.left = `${farLeft}px`;
            item.style.opacity = '0';
            item.style.filter = 'blur(5px)';
            item.style.zIndex = '5';
            item.style.pointerEvents = 'none';
            item.classList.add('far-left');
            item.classList.remove('center', 'left', 'right', 'far-right');
        }
        else if (idx === pos_2) {
            // Далеко правое (невидимое)
            item.style.width = `${invisibleWidth}px`;
            item.style.height = invisibleHeight;
            item.style.left = `${farRight}px`;
            item.style.opacity = '0';
            item.style.filter = 'blur(5px)';
            item.style.zIndex = '5';
            item.style.pointerEvents = 'none';
            item.classList.add('far-right');
            item.classList.remove('center', 'left', 'right', 'far-left');
        }
        else {
            // Остальные изображения (если их больше 5) - полностью скрыты
            item.style.width = '0';
            item.style.height = '0';
            item.style.opacity = '0';
            item.style.left = '-200%';
            item.style.pointerEvents = 'none';
            item.classList.remove('center', 'left', 'right', 'far-left', 'far-right');
        }
    });
    
    updateDots();
}

function nextImage() {
    if (isAnimating) return;
    isAnimating = true;
    
    // Меняем индекс
    currentIndex = (currentIndex + 1) % carouselItems.length;
    
    // Обновляем позиции с анимацией
    updatePositions();
    
    // Снимаем блокировку после анимации
    setTimeout(() => {
        isAnimating = false;
    }, 400);
}

function prevImage() {
    if (isAnimating) return;
    isAnimating = true;
    
    // Меняем индекс
    currentIndex = (currentIndex - 1 + carouselItems.length) % carouselItems.length;
    
    // Обновляем позиции с анимацией
    updatePositions();
    
    // Снимаем блокировку после анимации
    setTimeout(() => {
        isAnimating = false;
    }, 400);
}

function createDots() {
    const dotsContainer = document.getElementById('carouselDots');
    if (!dotsContainer) return;
    
    dotsContainer.innerHTML = '';
    carouselItems.forEach((_, index) => {
        const dot = document.createElement('div');
        dot.classList.add('dot');
        if (index === currentIndex) dot.classList.add('active');
        dot.addEventListener('click', () => {
            if (isAnimating || index === currentIndex) return;
            
            if (index > currentIndex) {
                const steps = index - currentIndex;
                let step = 0;
                function doNext() {
                    if (step < steps && !isAnimating) {
                        nextImage();
                        step++;
                        setTimeout(doNext, 450);
                    }
                }
                doNext();
            } else {
                const steps = currentIndex - index;
                let step = 0;
                function doPrev() {
                    if (step < steps && !isAnimating) {
                        prevImage();
                        step++;
                        setTimeout(doPrev, 450);
                    }
                }
                doPrev();
            }
        });
        dotsContainer.appendChild(dot);
    });
}

function updateDots() {
    const dots = document.querySelectorAll('#carouselDots .dot');
    dots.forEach((dot, index) => {
        if (index === currentIndex) {
            dot.classList.add('active');
        } else {
            dot.classList.remove('active');
        }
    });
}

// ============ ОТКРЫТИЕ ИЗОБРАЖЕНИЙ В ПОЛНЫЙ ЭКРАН ==========
window.openFullscreen = function(clickedIndex) {
    const allImages = [];
    const allItems = document.querySelectorAll('.carousel-item img');
    allItems.forEach(img => {
        if (img.src) {
            allImages.push({
                src: img.src,
                alt: img.alt || 'Изображение'
            });
        }
    });
    
    if (allImages.length === 0) return;
    
    let currentImageIndex = 0;
    const centerImg = document.querySelector('.carousel-item.center img');
    if (centerImg) {
        currentImageIndex = allImages.findIndex(img => img.src === centerImg.src);
        if (currentImageIndex === -1) currentImageIndex = 0;
    }
    
    if (window.Fancybox) {
        window.Fancybox.show([allImages[currentImageIndex]], {
            startIndex: 0,
            infinite: true,
        });
    } else {
        window.open(centerImg.src, '_blank');
    }
};

// Обновление при изменении размера окна
let resizeTimeout;
window.addEventListener('resize', () => {
    clearTimeout(resizeTimeout);
    resizeTimeout = setTimeout(() => {
        if (!isAnimating) {
            updatePositions();
        }
    }, 200);
});

// Инициализация карусели
document.addEventListener('DOMContentLoaded', () => {
    initCarousel();
});

// ============ ДРАКОН ============
const dragon = document.getElementById('dragon');
const pupils = document.querySelectorAll('.dragon-pupil');

if (dragon && pupils.length) {
    document.addEventListener('mousemove', (e) => {
        const rect = dragon.getBoundingClientRect();
        const centerX = rect.left + rect.width / 2;
        const centerY = rect.top + rect.height / 2;
        const angle = Math.atan2(e.clientY - centerY, e.clientX - centerX);
        const moveX = Math.sin(angle) * 3;
        const moveY = Math.cos(angle) * 3;
        pupils.forEach(pupil => pupil.style.transform = `translate(${moveX}px, ${-moveY}px)`);
    });

    const buyBtn = document.querySelector('.btn-buy');
    if (buyBtn) {
        buyBtn.addEventListener('mouseenter', () => dragon.classList.add('mouth-open'));
        buyBtn.addEventListener('mouseleave', () => dragon.classList.remove('mouth-open'));
        buyBtn.addEventListener('click', () => {
            dragon.classList.add('mouth-open');
            setTimeout(() => dragon.classList.remove('mouth-open'), 500);
        });
    }
}

// ============ ВСЕ ЗРИТЕЛИ (ОДИНАКОВЫЙ СТИЛЬ, СЛУЧАЙНЫЕ МЕСТА) ==========
class ViewersManager {
    constructor() {
        this.viewers = [];
        // Все доступные места в зале (9 мест)
        this.allSeats = [
            // 1 ряд (верхний) - 4 места
            'r21', 'r22', 'r23', 'r24',
            // 2 ряд (нижний) - 5 мест
            'r11', 'r12', 'r13', 'r14', 'r15'
        ];
        this.init();
    }
    
    init() {
        // Перемешиваем все места
        const shuffledSeats = this.shuffleArray([...this.allSeats]);
        
        // Берем 4 случайных места для зрителей (тестовый + 3 обычных)
        const selectedSeats = shuffledSeats.slice(0, 4);
        
        // Список имен и иконок для обычных зрителей
        const randomUsers = [
            { name: 'Алексей', icon: '👤' },
            { name: 'Мария', icon: '👩' },
            { name: 'Дмитрий', icon: '👨' },
            { name: 'Елена', icon: '👩' },
            { name: 'Сергей', icon: '👨' },
            { name: 'Анна', icon: '👩' },
            { name: 'Иван', icon: '👨' },
            { name: 'Ольга', icon: '👩' },
            { name: 'Павел', icon: '👨' },
            { name: 'Татьяна', icon: '👩' }
        ];
        
        const shuffledUsers = this.shuffleArray([...randomUsers]);
        
        // Сажаем зрителей на выбранные места
        selectedSeats.forEach((seat, index) => {
            if (index === 0) {
                // Первый зритель - тестовый (без аватарки, с призраком)
                this.addTestViewer(seat);
            } else {
                // Остальные - обычные зрители (с аватаркой)
                const user = shuffledUsers[index % shuffledUsers.length];
                this.addViewer(seat, user.name, user.icon);
            }
        });
        
        console.log(`Посажено ${selectedSeats.length} зрителей на места:`, selectedSeats);
        this.updateCount();
    }
    
    shuffleArray(array) {
        for (let i = array.length - 1; i > 0; i--) {
            const j = Math.floor(Math.random() * (i + 1));
            [array[i], array[j]] = [array[j], array[i]];
        }
        return array;
    }
    
    addTestViewer(seatId) {
        const spot = document.querySelector(`.viewer-spot[data-seat="${seatId}"]`);
        if (!spot) {
            console.log(`Место ${seatId} не найдено`);
            return;
        }
        
        // Очищаем место
        spot.classList.remove('occupied', 'my-seat');
        spot.classList.add('test-viewer');
        
        // Удаляем аватарку если есть
        const oldAvatar = spot.querySelector('.viewer-avatar');
        if (oldAvatar) oldAvatar.remove();
        const oldTooltip = spot.querySelector('.viewer-tooltip');
        if (oldTooltip) oldTooltip.remove();
        
        // Добавляем призрака в entity
        const entity = spot.querySelector('.viewer-entity');
        if (entity) {
            // Удаляем старый контент
            const oldGhost = entity.querySelector('.ghost-icon');
            if (oldGhost) oldGhost.remove();
            
            // Добавляем иконку призрака
            const ghostIcon = document.createElement('span');
            ghostIcon.className = 'ghost-icon';
            ghostIcon.innerHTML = '👻';
            ghostIcon.style.cssText = `
                position: absolute;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                font-size: 28px;
                opacity: 0.9;
                color: white;
                text-shadow: 0 0 5px rgba(0,0,0,0.3);
                z-index: 5;
                pointer-events: none;
            `;
            entity.appendChild(ghostIcon);
        }
        
        this.viewers.push({
            seatId: seatId,
            isTest: true
        });
        
        console.log(`Тестовый зритель (👻) на месте ${seatId}`);
    }
    
    addViewer(seatId, userName, avatarIcon = '👤') {
        const spot = document.querySelector(`.viewer-spot[data-seat="${seatId}"]`);
        if (!spot) {
            console.log(`Место ${seatId} не найдено`);
            return;
        }
        
        // Очищаем место
        spot.classList.remove('test-viewer', 'my-seat');
        spot.classList.add('occupied');
        
        // Удаляем старую аватарку и тултип если есть
        const oldAvatar = spot.querySelector('.viewer-avatar');
        if (oldAvatar) oldAvatar.remove();
        const oldTooltip = spot.querySelector('.viewer-tooltip');
        if (oldTooltip) oldTooltip.remove();
        
        // Удаляем призрака из entity
        const entity = spot.querySelector('.viewer-entity');
        if (entity) {
            const oldGhost = entity.querySelector('.ghost-icon');
            if (oldGhost) oldGhost.remove();
        }
        
        // Создаем аватарку на спинке сиденья
        const avatar = document.createElement('div');
        avatar.className = 'viewer-avatar';
        avatar.innerHTML = avatarIcon;
        
        // Создаем тултип с ником
        const tooltip = document.createElement('div');
        tooltip.className = 'viewer-tooltip';
        tooltip.textContent = userName;
        
        // Добавляем в спот
        spot.appendChild(avatar);
        spot.appendChild(tooltip);
        
        this.viewers.push({
            seatId: seatId,
            userName: userName,
            avatarIcon: avatarIcon,
            isTest: false
        });
        
        console.log(`Зритель ${userName} ${avatarIcon} на месте ${seatId}`);
    }
    
    updateCount() {
        const viewersCount = document.getElementById('viewersCount');
        if (viewersCount) {
            viewersCount.textContent = this.viewers.length;
        }
    }
    
    refresh() {
        // Удаляем всех текущих зрителей
        this.viewers.forEach(viewer => {
            const spot = document.querySelector(`.viewer-spot[data-seat="${viewer.seatId}"]`);
            if (spot) {
                spot.classList.remove('occupied', 'test-viewer', 'my-seat');
                const avatar = spot.querySelector('.viewer-avatar');
                if (avatar) avatar.remove();
                const tooltip = spot.querySelector('.viewer-tooltip');
                if (tooltip) tooltip.remove();
                const entity = spot.querySelector('.viewer-entity');
                if (entity) {
                    const ghost = entity.querySelector('.ghost-icon');
                    if (ghost) ghost.remove();
                }
            }
        });
        this.viewers = [];
        
        // Создаем новых
        this.init();
    }
}

// ============ ИНИЦИАЛИЗАЦИЯ ЗРИТЕЛЕЙ ==========
let viewersManager = null;

document.addEventListener('DOMContentLoaded', function() {
    setTimeout(function() {
        viewersManager = new ViewersManager();
    }, 500);
});

// Функция для обновления зрителей (можно вызвать вручную в консоли)
window.refreshViewers = function() {
    if (viewersManager) {
        viewersManager.refresh();
        console.log('Зрители обновлены');
    }
};

setInterval(function() {
    if (viewersManager && viewersManager.viewers.length > 0) {
        let needRefresh = false;
        
        viewersManager.viewers.forEach(viewer => {
            const spot = document.querySelector(`.viewer-spot[data-seat="${viewer.seatId}"]`);
            if (spot) {
                const hasClass = viewer.isTest ? 
                    spot.classList.contains('test-viewer') : 
                    spot.classList.contains('occupied');
                if (!hasClass) {
                    needRefresh = true;
                }
            } else {
                needRefresh = true;
            }
        });
        
        if (needRefresh) {
            console.log('Зрители были удалены, восстанавливаем...');
            viewersManager.refresh();
        }
    }
}, 2000);

// ============ ОТЗЫВЫ ============
let draggedItem = null;

function makeDraggable(item) {
    item.addEventListener('mousedown', startDrag);
    item.addEventListener('mousemove', onDrag);
    item.addEventListener('mouseup', endDrag);
}

function startDrag(e) {
    if (e.button !== 0) return;
    draggedItem = this;
    draggedItem.classList.add('dragging');
    const rect = draggedItem.getBoundingClientRect();
    draggedItem.style.left = rect.left + 'px';
    draggedItem.style.top = rect.top + 'px';
    draggedItem.style.width = rect.width + 'px';
    draggedItem.style.position = 'fixed';
    draggedItem.style.animation = 'none';
    e.preventDefault();
}

function onDrag(e) {
    if (!draggedItem) return;
    draggedItem.style.left = (e.clientX - 100) + 'px';
    draggedItem.style.top = (e.clientY - 50) + 'px';
}

function endDrag(e) {
    if (!draggedItem) return;
    const pinnedArea = document.querySelector('.pinned-reviews');
    if (pinnedArea) {
        const rect = pinnedArea.getBoundingClientRect();
        if (e.clientX >= rect.left && e.clientX <= rect.right && e.clientY >= rect.top && e.clientY <= rect.bottom) {
            const clone = draggedItem.cloneNode(true);
            clone.classList.add('pinned-review');
            clone.classList.remove('stream-item', 'dragging');
            clone.style.position = '';
            clone.style.left = '';
            clone.style.top = '';
            clone.style.width = '';
            clone.style.animation = '';
            const unpin = document.createElement('button');
            unpin.className = 'unpin-btn';
            unpin.innerHTML = '<i class="fas fa-times"></i>';
            unpin.onclick = () => clone.remove();
            clone.appendChild(unpin);
            pinnedArea.appendChild(clone);
            draggedItem.remove();
            showNotification('📌 Отзыв закреплен на доске');
        }
    }
    draggedItem.classList.remove('dragging');
    draggedItem.style.position = '';
    draggedItem.style.left = '';
    draggedItem.style.top = '';
    draggedItem.style.width = '';
    draggedItem.style.animation = '';
    draggedItem = null;
}

// Добавляем обработчики для существующих отзывов
document.querySelectorAll('.stream-item').forEach(makeDraggable);

function likeReview(id, event) {
    if (event) event.stopPropagation();
    const btn = event ? event.currentTarget : null;
    if (!btn) return;
    const countSpan = btn.querySelector('.likes-count');
    fetch('/ajax/review_like.php', { 
        method: 'POST', 
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'review_id=' + id + '&action=like'
    })
    .then(r => r.json())
    .then(data => { 
        if (data.success && countSpan) countSpan.textContent = data.likes; 
    })
    .catch(err => console.error('Error:', err));
}

function dislikeReview(id, event) {
    if (event) event.stopPropagation();
    const btn = event ? event.currentTarget : null;
    if (!btn) return;
    const countSpan = btn.querySelector('.dislikes-count');
    fetch('/ajax/review_like.php', { 
        method: 'POST', 
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'review_id=' + id + '&action=dislike'
    })
    .then(r => r.json())
    .then(data => { 
        if (data.success && countSpan) countSpan.textContent = data.dislikes; 
    })
    .catch(err => console.error('Error:', err));
}

// ============ ВКЛАДКИ ============
document.querySelectorAll('.bookmark').forEach(bookmark => {
    bookmark.addEventListener('click', function() {
        const pageId = this.dataset.page;
        document.querySelectorAll('.bookmark').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        document.querySelectorAll('.book-page').forEach(p => p.classList.remove('active'));
        const targetPage = document.getElementById('page-' + pageId);
        if (targetPage) targetPage.classList.add('active');
    });
});

// ============ КОРЗИНА ============
function addToCart(productId) {
    fetch('/ajax/add_to_cart.php', { 
        method: 'POST', 
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'action=add&product_id=' + productId + '&quantity=1' 
    })
    .then(r => r.json())
    .then(data => { 
        if (data.success) {
            showNotification('✅ Товар добавлен в корзину!');
            if (window.updateCartCounter) window.updateCartCounter();
        } else {
            showNotification('❌ Ошибка при добавлении в корзину', 'error');
        }
    })
    .catch(err => {
        console.error('Error:', err);
        showNotification('❌ Ошибка при добавлении в корзину', 'error');
    });
}

function toggleFavorite(productId) {
    const btn = document.getElementById('favoriteBtn');
    if (!btn) return;
    
    fetch('/ajax/favorite.php', { 
        method: 'POST', 
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'action=toggle&product_id=' + productId 
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            btn.classList.toggle('active');
            btn.innerHTML = data.action === 'added' ? '<i class="fas fa-heart"></i> В избранном' : '<i class="fas fa-heart"></i> В избранное';
            showNotification(data.action === 'added' ? '❤️ Добавлено в избранное' : '💔 Удалено из избранного');
        }
    })
    .catch(err => console.error('Error:', err));
}

function takeSeat() {
    showNotification('🎭 Вы заняли место в зале! Добро пожаловать в кинотеатр!');
    setTimeout(() => location.reload(), 1000);
}

// ============ ОТЗЫВЫ (МОДАЛКА) ============
function openReviewModal() {
    const modal = document.getElementById('reviewModal');
    if (modal) {
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
        setRating(5);
    }
}

function closeReviewModal() {
    const modal = document.getElementById('reviewModal');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = '';
    }
}

function setRating(rating) {
    const stars = document.querySelectorAll('#ratingStars span');
    stars.forEach((star, i) => {
        star.textContent = i < rating ? '★' : '☆';
        star.classList.toggle('active', i < rating);
    });
    const ratingInput = document.getElementById('reviewRating');
    if (ratingInput) ratingInput.value = rating;
}

function submitReview(event) {
    event.preventDefault();
    const form = document.getElementById('reviewForm');
    if (!form) return;
    
    fetch('/ajax/add_review.php', { 
        method: 'POST', 
        body: new FormData(form) 
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showNotification('✅ Отзыв отправлен на модерацию!');
            closeReviewModal();
            form.reset();
        } else {
            showNotification('❌ Ошибка: ' + (data.message || 'Неизвестная ошибка'), 'error');
        }
    })
    .catch(err => {
        console.error('Error:', err);
        showNotification('❌ Ошибка при отправке отзыва', 'error');
    });
}

function showNotification(msg, type = 'success') {
    const n = document.createElement('div');
    n.className = 'notification';
    n.style.background = type === 'success' ? '#27ae60' : '#e74c3c';
    n.innerHTML = msg;
    document.body.appendChild(n);
    setTimeout(() => n.remove(), 3000);
}

// ============ СЧЕТЧИК КОРЗИНЫ ============
window.updateCartCounter = function() {
    fetch('/ajax/add_to_cart.php?action=get&t=' + Date.now())
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                document.querySelectorAll('.cart-counter').forEach(counter => {
                    counter.textContent = data.cart_count;
                    counter.style.display = data.cart_count > 0 ? 'inline' : 'none';
                });
            }
        })
        .catch(err => console.error('Error:', err));
};

// ============ ИНИЦИАЛИЗАЦИЯ ============
document.addEventListener('DOMContentLoaded', () => {
    // Инициализируем звезды рейтинга
    setRating(5);
    
    // Обновляем счетчик корзины
    if (window.updateCartCounter) window.updateCartCounter();
    
    // Отправляем просмотр товара
    const productIdElement = document.querySelector('input[name="product_id"]');
    if (productIdElement && productIdElement.value) {
        fetch('/ajax/track_view.php', { 
            method: 'POST', 
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'product_id=' + productIdElement.value 
        }).catch(err => console.error('Error tracking view:', err));
    }
});