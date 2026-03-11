<?
define("NEED_AUTH", true);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Личный кабинет");

// Получаем данные пользователя
global $USER;
$userID = $USER->GetID();

// Функция для получения данных пользователя с UF полями
function getUserData($userId) {
    $rsUser = CUser::GetList(
        ($by = "id"),
        ($order = "desc"),
        array("ID" => $userId),
        array("SELECT" => array("UF_BIRTHDAY"))
    );
    return $rsUser->Fetch();
}

// Получаем данные пользователя
$arUser = getUserData($userID);

// Определяем переменные
$success = '';
$error = '';

// Обработка сохранения
if ($_SERVER["REQUEST_METHOD"] == "POST" && check_bitrix_sessid()) {
    $user = new CUser;
    $fields = array();
    
    // Основные поля
    if (isset($_POST['NAME']) && $_POST['NAME'] != $arUser['NAME']) {
        $fields['NAME'] = $_POST['NAME'];
    }
    if (isset($_POST['LAST_NAME']) && $_POST['LAST_NAME'] != $arUser['LAST_NAME']) {
        $fields['LAST_NAME'] = $_POST['LAST_NAME'];
    }
    if (isset($_POST['EMAIL']) && $_POST['EMAIL'] != $arUser['EMAIL']) {
        $fields['EMAIL'] = $_POST['EMAIL'];
    }
    if (isset($_POST['PERSONAL_PHONE']) && $_POST['PERSONAL_PHONE'] != $arUser['PERSONAL_PHONE']) {
        $fields['PERSONAL_PHONE'] = $_POST['PERSONAL_PHONE'];
    }
    if (isset($_POST['PERSONAL_CITY']) && $_POST['PERSONAL_CITY'] != $arUser['PERSONAL_CITY']) {
        $fields['PERSONAL_CITY'] = $_POST['PERSONAL_CITY'];
    }
    
    // Смена пароля
    if (!empty($_POST['NEW_PASSWORD'])) {
        if ($_POST['NEW_PASSWORD'] == $_POST['CONFIRM_PASSWORD']) {
            $fields['PASSWORD'] = $_POST['NEW_PASSWORD'];
            $fields['CONFIRM_PASSWORD'] = $_POST['CONFIRM_PASSWORD'];
        } else {
            $error = "Пароли не совпадают";
        }
    }
    
    if (!empty($fields)) {
        if ($user->Update($userID, $fields)) {
            $success = "Данные успешно сохранены";
            // Получаем свежие данные
            $arUser = getUserData($userID);
        } else {
            $error = $user->LAST_ERROR;
        }
    }
}
?>

<?if (!$USER->IsAuthorized()):?> 
<script>
    window.location.href = "/login/?backurl=<?=urlencode($APPLICATION->GetCurPage())?>";
</script> 
<?endif;?>

<div class="boardgame-profile">
    <!-- Фон с параллакс эффектом -->
    <div class="parallax-bg" id="parallaxBg"></div>
    <div class="overlay"></div>
    
    <div class="profile-container-box">
        <div class="boardgame-profile">
            <div class="profile-container">
                <!-- Шапка профиля как карточка персонажа -->
                <div class="character-card">
                    <div class="character-avatar">
                        <i class="fas fa-dragon"></i>
                    </div>
                    <div class="character-info">
                        <div class="character-name-wrapper">
                            <span class="character-level">1</span>
                            <h1 class="character-name">
                                <?=htmlspecialcharsbx($arUser['NAME'] ?: 'Без имени')?> 
                                <?=htmlspecialcharsbx($arUser['LAST_NAME'] ?: '')?>
                            </h1>
                            <span class="character-class">покупатель</span>
                        </div>
                        
                        <!-- Прогресс бар -->
                        <div class="progress-bar-container">
                            <div class="progress-label">
                                <i class="fas fa-star"></i>
                                <span>0 / 10000 опыта</span>
                            </div>
                            <div class="progress-track">
                                <div class="progress-fill" style="width: 0%"></div>
                            </div>
                        </div>
                    </div>
                    <a href="/?logout=yes&<?=bitrix_sessid_get()?>" class="logout-btn">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Выйти</span>
                    </a>
                </div>

                <!-- Моя коллекция - ПОДШАПКА -->
                <div class="collection-section">
                    <div class="section-header-with-button">
                        <h3 class="section-subtitle">
                            <i class="fas fa-dice-d20"></i>
                            Моя коллекция
                        </h3>
                        <!--<button class="view-all-btn" onclick="openCollectionModal()">
                            <span>Показать все</span>
                            <i class="fas fa-arrow-right"></i>
                        </button>-->
                    </div>
                    
                    <div class="games-mini-grid empty-collection">
                        <div class="empty-collection-message">
                            <i class="fas fa-box-open"></i>
                            <p>У вас пока нет игр в коллекции</p>
                        </div>
                    </div>
                </div>

                <!-- Инвентарь (статистика) -->
                <div class="inventory-grid">
                    <div class="inventory-item">
                        <div class="item-icon">
                            <i class="fas fa-shopping-bag"></i>
                        </div>
                        <div class="item-info">
                            <span class="item-value">0</span>
                            <span class="item-label">Активных заказов</span>
                        </div>
                    </div>
                    <div class="inventory-item">
                        <div class="item-icon">
                            <i class="fas fa-scroll"></i>
                        </div>
                        <div class="item-info">
                            <span class="item-value">0</span>
                            <span class="item-label">История заказов</span>
                        </div>
                    </div>
                    <div class="inventory-item">
                        <div class="item-icon">
                            <i class="fas fa-heart"></i>
                        </div>
                        <div class="item-info">
                            <span class="item-value">0</span>
                            <span class="item-label">В избранном</span>
                        </div>
                    </div>
                    <div class="inventory-item">
                        <div class="item-icon">
                            <i class="fas fa-coins"></i>
                        </div>
                        <div class="item-info">
                            <span class="item-value">0</span>
                            <span class="item-label">Бонусные монеты</span>
                        </div>
                    </div>
                </div>

                <!-- Достижения - С ОБНОВЛЕННОЙ СОРТИРОВКОЙ -->
                <div class="achievements-section">
                    <div class="section-header-with-button">
                        <h3 class="section-subtitle">
                            <i class="fas fa-trophy"></i>
                            Достижения
                            <span class="rarity-badge">редкие</span>
                        </h3>
                        <!--<button class="view-all-btn" onclick="openAchievementsModal()">
                            <span>Все достижения</span>
                            <i class="fas fa-arrow-right"></i>
                        </button>-->
                    </div>
                    
                    <div class="achievements-grid">
                        <!--<div class="achievement unlocked rare">
                            <div class="achievement-icon">
                                <i class="fas fa-dragon"></i>
                            </div>
                            <span class="achievement-name">Покоритель драконов</span>
                            <span class="achievement-date">редкое</span>
                            <div class="rare-glow"></div>
                        </div>
                        
                        <div class="achievement unlocked">
                            <div class="achievement-icon">
                                <i class="fas fa-crown"></i>
                            </div>
                            <span class="achievement-name">Первый заказ</span>
                            <span class="achievement-date">март 2026</span>
                        </div>
                        
                        <div class="achievement unlocked">
                            <div class="achievement-icon">
                                <i class="fas fa-gift"></i>
                            </div>
                            <span class="achievement-name">Именинник</span>
                            <span class="achievement-date">март 2026</span>
                        </div>
                        
                        <div class="achievement locked rare">
                            <div class="achievement-icon">
                                <i class="fas fa-skull"></i>
                            </div>
                            <span class="achievement-name">Хардкорщик</span>
                            <span class="achievement-date">редкое</span>
                        </div>
                        
                        <div class="achievement locked">
                            <div class="achievement-icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <span class="achievement-name">Компанейский</span>
                            <span class="achievement-date">0/5 игр</span>
                        </div>-->

                        <div class="achievement unlocked">
                            <div class="achievement-icon">
                                <i class="fas fa-crown"></i>
                            </div>
                            <span class="achievement-name">Зарегестрироваться на сайте</span>
                            <span class="achievement-date">Вы успешно прошли регистрацию!</span>
                        </div>
                        
                        <div class="achievement locked">
                            <div class="achievement-icon">
                                <i class="fas fa-clock"></i>
                            </div>
                            <span class="achievement-name">Ветеран</span>
                            <span class="achievement-date">0/365 дней</span>
                        </div>
                    </div>
                </div>

                <!-- Настройки персонажа - ОБНОВЛЕНО с группировкой и датой рождения -->
                <div class="character-settings">
                    <div class="settings-header">
                        <h2 class="settings-title">
                            <i class="fas fa-feather-alt"></i>
                            Настройки персонажа
                        </h2>
                        <p class="settings-desc">Заполните свои данные, чтобы получать бонусы и участвовать в акциях</p>
                    </div>

                    <?if ($success):?>
                        <div class="alert success">
                            <i class="fas fa-check-circle"></i>
                            <?=$success?>
                        </div>
                    <?endif;?>

                    <?if ($error):?>
                        <div class="alert error">
                            <i class="fas fa-exclamation-triangle"></i>
                            <?=$error?>
                        </div>
                    <?endif;?>

                    <form method="post" class="character-form">
                        <?=bitrix_sessid_post()?>
                        
                        <div class="form-grid">
                            <!-- ГРУППА: Основная информация -->
                            <div class="form-group-card">
                                <div class="group-header">
                                    <i class="fas fa-id-card"></i>
                                    <span>Основная информация</span>
                                </div>
                                
                                <!-- Логин -->
                                <div class="form-field">
                                    <label class="field-label">
                                        <i class="fas fa-tag"></i>
                                        Логин
                                    </label>
                                    <div class="field-input disabled">
                                        <input type="text" value="<?=htmlspecialcharsbx($arUser['LOGIN'])?>" readonly disabled>
                                    </div>
                                </div>

                                <!-- Имя -->
                                <div class="form-field">
                                    <label class="field-label">
                                        <i class="fas fa-crown"></i>
                                        Имя
                                    </label>
                                    <div class="field-input <?=($arUser['NAME'] == 'Без имени') ? 'highlight' : ''?>">
                                        <input type="text" 
                                            name="NAME" 
                                            value="<?=htmlspecialcharsbx($arUser['NAME'])?>" 
                                            placeholder="Введите ваше имя">
                                    </div>
                                </div>

                                <!-- Фамилия -->
                                <div class="form-field">
                                    <label class="field-label">
                                        <i class="fas fa-shield-alt"></i>
                                        Фамилия
                                    </label>
                                    <div class="field-input">
                                        <input type="text" 
                                            name="LAST_NAME" 
                                            value="<?=htmlspecialcharsbx($arUser['LAST_NAME'])?>" 
                                            placeholder="Введите фамилию">
                                    </div>
                                </div>
                            </div>

                            <!-- ГРУППА: Контактные данные -->
                            <div class="form-group-card">
                                <div class="group-header">
                                    <i class="fas fa-address-book"></i>
                                    <span>Контактные данные</span>
                                </div>
                                
                                <!-- Email -->
                                <div class="form-field">
                                    <label class="field-label">
                                        <i class="fas fa-envelope"></i>
                                        Email
                                    </label>
                                    <div class="field-input">
                                        <input type="email" 
                                            name="EMAIL" 
                                            value="<?=htmlspecialcharsbx($arUser['EMAIL'])?>" 
                                            placeholder="example@mail.ru"
                                            required>
                                    </div>
                                </div>

                                <!-- Телефон -->
                                <div class="form-field">
                                    <label class="field-label">
                                        <i class="fas fa-phone-alt"></i>
                                        Телефон
                                    </label>
                                    <div class="field-input">
                                        <input type="tel" 
                                            name="PERSONAL_PHONE" 
                                            value="<?=htmlspecialcharsbx($arUser['PERSONAL_PHONE'])?>" 
                                            placeholder="+7 (999) 123-45-67"
                                            id="phoneInput">
                                    </div>
                                </div>

                                <!-- Город -->
                                <div class="form-field">
                                    <label class="field-label">
                                        <i class="fas fa-map-marked-alt"></i>
                                        Город
                                    </label>
                                    <div class="field-input">
                                        <input type="text" 
                                            name="PERSONAL_CITY" 
                                            value="<?=htmlspecialcharsbx($arUser['PERSONAL_CITY'])?>" 
                                            placeholder="Ваш город">
                                    </div>
                                </div>
                            </div>

                            <!-- Разделитель для смены пароля -->
                            <div class="form-divider">
                                <span>Смена пароля</span>
                            </div>

                            <!-- Новый пароль с генератором -->
                            <div class="form-field">
                                <label class="field-label">
                                    <i class="fas fa-key"></i>
                                    Новый пароль
                                </label>
                                <div class="password-wrapper">
                                    <input type="password" 
                                        name="NEW_PASSWORD" 
                                        placeholder="Придумайте или сгенерируйте"
                                        id="newPassword"
                                        class="password-input">
                                    <div class="password-actions">
                                        <button type="button" class="password-action" onclick="togglePassword(this)" title="Показать/скрыть пароль">
                                            <i class="far fa-eye"></i>
                                        </button>
                                        <button type="button" class="password-action dice" onclick="generatePassword()" title="Сгенерировать надежный пароль">
                                            <i class="fas fa-dice-d20"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="password-strength" id="passwordStrength">
                                    <div class="strength-bar"></div>
                                </div>
                            </div>

                            <!-- Подтверждение пароля -->
                            <div class="form-field">
                                <label class="field-label">
                                    <i class="fas fa-check-double"></i>
                                    Подтверждение
                                </label>
                                <div class="password-wrapper">
                                    <input type="password" 
                                        name="CONFIRM_PASSWORD" 
                                        placeholder="Повторите пароль"
                                        id="confirmPassword"
                                        class="password-input">
                                </div>
                                <div class="password-match" id="passwordMatch"></div>
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="save-btn">
                                <i class="fas fa-save"></i>
                                <span>Сохранить изменения</span>
                            </button>
                            <button type="reset" class="reset-btn" onclick="return confirm('Сбросить изменения?')">
                                <i class="fas fa-undo-alt"></i>
                                <span>Сбросить</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно для коллекции -->
        <div class="modal" id="collectionModal">
            <div class="modal-overlay" onclick="closeCollectionModal()"></div>
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title">
                        <i class="fas fa-dice-d20"></i>
                        Моя коллекция игр
                    </h3>
                    <button class="modal-close" onclick="closeCollectionModal()">×</button>
                </div>
                <div class="modal-body">
                    <div class="games-grid">
                        <div class="game-card">
                            <img src="https://via.placeholder.com/150x150/667eea/ffffff?text=Манчкин" alt="Манчкин">
                            <span class="game-title">Манчкин</span>
                            <span class="game-status">в коллекции</span>
                        </div>
                        <div class="game-card">
                            <img src="https://via.placeholder.com/150x150/764ba2/ffffff?text=Колонизаторы" alt="Колонизаторы">
                            <span class="game-title">Колонизаторы</span>
                            <span class="game-status">в коллекции</span>
                        </div>
                        <div class="game-card">
                            <img src="https://via.placeholder.com/150x150/f59e0b/ffffff?text=Имаджинариум" alt="Имаджинариум">
                            <span class="game-title">Имаджинариум</span>
                            <span class="game-status">в коллекции</span>
                        </div>
                        <div class="game-card">
                            <img src="https://via.placeholder.com/150x150/ef4444/ffffff?text=Бэнг" alt="Бэнг!">
                            <span class="game-title">Бэнг!</span>
                            <span class="game-status">в коллекции</span>
                        </div>
                        <div class="game-card">
                            <img src="https://via.placeholder.com/150x150/10b981/ffffff?text=UNO" alt="UNO">
                            <span class="game-title">UNO</span>
                            <span class="game-status">в коллекции</span>
                        </div>
                        <div class="game-card">
                            <img src="https://via.placeholder.com/150x150/8b5cf6/ffffff?text=Мафия" alt="Мафия">
                            <span class="game-title">Мафия</span>
                            <span class="game-status">в коллекции</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Модальное окно для достижений -->
        <div class="modal" id="achievementsModal">
            <div class="modal-overlay" onclick="closeAchievementsModal()"></div>
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title">
                        <i class="fas fa-trophy"></i>
                        Все достижения
                    </h3>
                    <button class="modal-close" onclick="closeAchievementsModal()">×</button>
                </div>
                <div class="modal-body">
                    <div class="achievements-full-grid">
                        <!-- Редкие (сортировка сверху) -->
                        <div class="achievement-card unlocked rare">
                            <div class="achievement-icon-large">
                                <i class="fas fa-dragon"></i>
                            </div>
                            <div class="achievement-info">
                                <span class="achievement-name">Покоритель драконов ★</span>
                                <span class="achievement-desc">Купите 5 игр с драконами (редкое - 8% пользователей)</span>
                                <span class="achievement-progress">100%</span>
                            </div>
                        </div>
                        <div class="achievement-card locked rare">
                            <div class="achievement-icon-large">
                                <i class="fas fa-skull"></i>
                            </div>
                            <div class="achievement-info">
                                <span class="achievement-name">Хардкорщик ★</span>
                                <span class="achievement-desc">Потратьте 100 000 ₽ на игры (редкое - 5% пользователей)</span>
                                <span class="achievement-progress">45 000/100 000</span>
                            </div>
                        </div>
                        
                        <!-- Обычные разблокированные -->
                        <div class="achievement-card unlocked">
                            <div class="achievement-icon-large">
                                <i class="fas fa-crown"></i>
                            </div>
                            <div class="achievement-info">
                                <span class="achievement-name">Первый заказ</span>
                                <span class="achievement-desc">Сделайте свой первый заказ</span>
                                <span class="achievement-progress">100%</span>
                            </div>
                        </div>
                        <div class="achievement-card unlocked">
                            <div class="achievement-icon-large">
                                <i class="fas fa-gift"></i>
                            </div>
                            <div class="achievement-info">
                                <span class="achievement-name">Именинник</span>
                                <span class="achievement-desc">Закажите подарок в день рождения</span>
                                <span class="achievement-progress">100%</span>
                            </div>
                        </div>
                        
                        <!-- Обычные заблокированные -->
                        <div class="achievement-card locked">
                            <div class="achievement-icon-large">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="achievement-info">
                                <span class="achievement-name">Компанейский</span>
                                <span class="achievement-desc">Соберите компанию из 4 друзей</span>
                                <span class="achievement-progress">0/4</span>
                            </div>
                        </div>
                        <div class="achievement-card locked">
                            <div class="achievement-icon-large">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div class="achievement-info">
                                <span class="achievement-name">Ветеран</span>
                                <span class="achievement-desc">Будьте с нами 1 год</span>
                                <span class="achievement-progress">120/365 дней</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

<style>
    /* ===== СТИЛИ ДЛЯ ЛИЧНОГО КАБИНЕТА ===== */
    @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Playfair+Display:wght@400;700&display=swap');

    .boardgame-profile {
        border-radius: 34px;
        position: relative;
        z-index: 1;
        max-width: 1000px;
        margin: 70px auto 0;
        padding: 10px 10px;
        background: rgba(255, 255, 255, 0.1); /* Очень прозрачный белый */
    }

    .profile-container {
        max-width: 1000px;
        margin: 0 auto;
    }

    /* Карточка персонажа */
    .character-card {
        background: linear-gradient(135deg, var(--DragonLight) 0%, var(--Gold) 100%);
        border-radius: 24px 24px 0 0;
        padding: 30px;
        display: flex;
        align-items: center;
        gap: 30px;
        color: white;
        box-shadow: 0 20px 40px rgba(102, 126, 234, 0.2);
    }

    .character-avatar {
        width: 80px;
        height: 80px;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 40px;
        color: white;
        border: 3px solid rgba(255, 255, 255, 0.3);
    }

    .character-info {
        flex: 1;
    }

    .character-name-wrapper {
        display: flex;
        align-items: center;
        gap: 15px;
        margin-bottom: 15px;
        flex-wrap: wrap;
    }

    .character-level {
        background: rgba(255, 255, 255, 0.2);
        padding: 4px 12px;
        border-radius: 40px;
        font-size: 14px;
        font-weight: 600;
    }

    .character-name {
        font-size: 24px;
        font-weight: 700;
        font-family: 'Playfair Display', serif;
        margin: 0;
    }

    .character-class {
        background: rgba(255, 255, 255, 0.15);
        padding: 4px 12px;
        border-radius: 40px;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    /* Прогресс бар */
    .progress-bar-container {
        max-width: 400px;
    }

    .progress-label {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 8px;
        font-size: 13px;
        opacity: 0.9;
    }

    .progress-label i {
        color: #ffd700;
    }

    .progress-track {
        background: rgba(255, 255, 255, 0.2);
        border-radius: 40px;
        height: 8px;
        overflow: hidden;
        position: relative;
    }

    .progress-fill {
        height: 100%;
        border-radius: 40px;
        transition: width 0.3s;
        background: linear-gradient(90deg, var(--Gold) 0%, red 100%);
        background-size: 200% 100%; /* Делаем градиент в 2 раза шире */
        background-position: 0% 0%; /* Начальная позиция */
        transition: background-position 0.3s ease;
    }

    /* Чем больше ширина, тем больше смещается градиент */
    .progress-fill[style*="width: 10"] {
        background-position: 0% 90%; /* Почти не видно правую часть */
    }

    .progress-fill[style*="width: 20"] {
        background-position: 0% 80%;
    }

    .progress-fill[style*="width: 30"] {
        background-position: 0% 70%;
    }

    .progress-fill[style*="width: 40"] {
        background-position: 0% 60%;
    }

    .progress-fill[style*="width: 50"] {
        background-position: 0% 50%;
    }

    .progress-fill[style*="width: 60"] {
        background-position: 0% 40%;
    }

    .progress-fill[style*="width: 70"] {
        background-position: 0% 30%;
    }

    .progress-fill[style*="width: 80"] {
        background-position: 0% 20%;
    }

    .progress-fill[style*="width: 90"] {
        background-position: 0% 10%;
    }

    .progress-fill[style*="width: 100"] {
        background-position: 0% 0%; /* Полностью виден градиент */
    }

    /* Кнопка выхода */
    .logout-btn {
        background: rgba(255, 255, 255, 0.2);
        color: white;
        padding: 12px 24px;
        border-radius: 40px;
        text-decoration: none;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s;
        border: 1px solid rgba(255, 255, 255, 0.3);
    }

    .logout-btn:hover {
        background: rgba(255, 255, 255, 0.3);
        transform: translateY(-2px);
    }

    /* Моя коллекция - ПОДШАПКА */
    .collection-section {
        border-radius: 0 0 24px 24px;
        background: white;
        padding: 25px 30px;
        margin-bottom: 5px;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
        border-bottom: 2px solid #f1f5f9;
    }

    /* Инвентарь */
    .inventory-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 20px;
        margin: 15px 0;
    }

    .inventory-item {
        background: white;
        border-radius: 20px;
        padding: 20px;
        display: flex;
        align-items: center;
        gap: 15px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        transition: transform 0.3s;
    }

    .inventory-item:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 25px rgba(102, 126, 234, 0.15);
    }

    .item-icon {
        width: 48px;
        height: 48px;
        background: linear-gradient(135deg, #eae86615, #a29c4b15);
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
        color: var(--GoldFake);
    }

    .item-info {
        display: flex;
        flex-direction: column;
    }

    .item-value {
        font-size: 22px;
        font-weight: 700;
        color: #1e293b;
    }

    .item-label {
        font-size: 12px;
        color: #64748b;
    }

    /* Секции с подшапкой */
    .section-header-with-button {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }

    .view-all-btn {
        background: none;
        border: 2px solid #e2e8f0;
        color: #64748b;
        padding: 8px 16px;
        border-radius: 40px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s;
    }

    .view-all-btn:hover {
        border-color: var(--DragonLight);
        color: var(--DragonDark);
        transform: translateX(3px);
    }

    /* Коллекция игры */
    .section-subtitle {
        font-size: 18px;
        font-weight: 700;
        color: #1e293b;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .section-subtitle i {
        color: var(--GoldFake);
        font-size: 20px;
    }

    .rarity-badge {
        background: linear-gradient(135deg, #f59e0b, #ef4444);
        color: white;
        padding: 2px 10px;
        border-radius: 40px;
        font-size: 10px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .games-mini-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 15px;
    }

    .game-mini-card {
        background: #fcf8f8;
        border-radius: 16px;
        padding: 15px;
        text-align: center;
        transition: all 0.3s;
        border: 2px solid transparent;
        cursor: pointer;
    }

    .game-mini-card:hover {
        border-color: var(--Gold);
        transform: translateY(-3px);
        box-shadow: 0 10px 20px rgba(102, 126, 234, 0.15);
    }

    .game-mini-card img {
        width: 60px;
        height: 60px;
        border-radius: 12px;
        margin-bottom: 8px;
        object-fit: cover;
    }

    .game-mini-card span {
        display: block;
        font-size: 12px;
        font-weight: 600;
        color: #475569;
    }

    /* Достижения */
    .achievements-section {
        background: white;
        border-radius: 24px;
        padding: 25px;
        margin-bottom: 20px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
    }

    .achievements-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 15px;
    }

    .achievement {
        border: 1px solid #0000004d; /* Прозрачная граница по умолчанию */
        background: #f8fafc;
        border-radius: 16px;
        padding: 15px;
        text-align: center;
        transition: all 0.3s;
        position: relative;
        overflow: hidden;
    }

    .achievement.unlocked {
        background: linear-gradient(135deg, #fcf8f8, #f9f1f1);
        border: 1px solid var(--DragonLight);
    }

    .achievement.locked {
        opacity: 0.7;
        filter: grayscale(0.5);
    }

    .achievement.locked .achievement-icon i {
        color: #94a3b8;
    }

    .achievement-icon i {
        font-size: 24px;
        margin-bottom: 8px;
        color: #f59e0b;
    }

    .achievement-name {
        display: block;
        font-size: 13px;
        font-weight: 600;
        color: #334155;
        margin-bottom: 4px;
    }

    .achievement-date {
        display: block;
        font-size: 10px;
        color: #64748b;
    }

    /* Редкое достижение - ОБНОВЛЕННЫЙ ЭФФЕКТ */
    .achievement.rare {
        position: relative;
        background: linear-gradient(135deg, #fff7ed, #fef3c7);
        border: 2px solid #f59e0b;
        box-shadow: 0 0 15px rgba(245, 158, 11, 0.3);
        animation: rarePulse 2s infinite;
    }

    @keyframes rarePulse {
        0%, 100% { box-shadow: 0 0 15px rgba(245, 158, 11, 0.3); }
        50% { box-shadow: 0 0 25px rgba(245, 158, 11, 0.6); }
    }

    .achievement.rare .achievement-icon i {
        color: #f59e0b;
        filter: drop-shadow(0 0 5px rgba(245, 158, 11, 0.5));
    }

    .rare-glow {
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: conic-gradient(
            from 0deg,
            transparent,
            rgba(255, 215, 0, 0.1),
            transparent 30%,
            transparent 70%,
            rgba(255, 215, 0, 0.1),
            transparent
        );
        animation: rotateGlow 4s linear infinite;
        pointer-events: none;
        opacity: 0.8;
    }

    @keyframes rotateGlow {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }

    .achievement.rare::after {
        content: '★';
        position: absolute;
        top: 5px;
        right: 5px;
        color: #f59e0b;
        font-size: 16px;
        filter: drop-shadow(0 0 3px #f59e0b);
        animation: starPulse 2s infinite;
    }

    @keyframes starPulse {
        0%, 100% { transform: scale(1); opacity: 1; }
        50% { transform: scale(1.2); opacity: 0.8; }
    }

    /* Настройки персонажа */
    .character-settings {
        background: white;
        border-radius: 24px;
        padding: 40px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
    }

    .settings-header {
        margin-bottom: 30px;
        padding-bottom: 20px;
        border-bottom: 2px solid #f1f5f9;
    }

    .settings-title {
        font-size: 24px;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 8px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .settings-title i {
        color: var(--GoldFake);
    }

    .settings-desc {
        color: #64748b;
        font-size: 14px;
        margin-left: 34px;
    }

    /* Алерты */
    .alert {
        padding: 16px 20px;
        border-radius: 16px;
        margin-bottom: 25px;
        display: flex;
        align-items: center;
        gap: 12px;
        font-weight: 500;
    }

    .alert.success {
        background: #d1fae5;
        border: 1px solid #a7f3d0;
        color: #065f46;
    }

    .alert.error {
        background: #fee2e2;
        border: 1px solid #fecaca;
        color: #991b1b;
    }

    /* Группы формы */
    .form-group-card {
        border-radius: 20px;
        padding: 25px;
        margin-bottom: 20px;
        border: 2px solid #f1f5f9af;
        transition: all 0.3s;
    }

    .form-group-card.full-width {
        grid-column: span 2;
    }

    .group-header {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 2px solid #e2e8f0;
        color: #334155;
        font-weight: 700;
        font-size: 16px;
    }

    .group-header i {
        color: var(--GoldFake);
        font-size: 18px;
    }

    .character-form {
        max-width: 100%;
    }

    .form-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 25px;
    }

    .form-field {
        margin-bottom: 5px;
    }

    .field-label {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 8px;
        color: #334155;
        font-weight: 600;
        font-size: 14px;
    }

    .field-label i {
        color: var(--GoldFake);
        font-size: 14px;
    }

    .badge {
        background: #f59e0b;
        color: white;
        padding: 2px 8px;
        border-radius: 40px;
        font-size: 10px;
        font-weight: 600;
        margin-left: 8px;
    }

    .badge.bonus {
        background: #10b981;
    }

    .field-input input {
        width: 100%;
        padding: 14px 16px;
        border: 2px solid #f0e2e2;
        border-radius: 10px;
        font-size: 15px;
        transition: all 0.3s;
        background: #fcf8f8;
        color: #1e293b;
        font-family: 'Montserrat', sans-serif;
    }

    .field-input input:focus {
        border-color: var(--DragonDark);
        background: white;
        outline: none;
        box-shadow: 0 0 0 4px rgba(234, 102, 102, 0.1);
    }

    .field-input.highlight input {
        border-color: #f59e0b;
        background: #fff7ed;
    }

    .field-input.disabled input {
        background: #f1ebeb;
        border-color: #d7caca;
        color: #64748b;
        cursor: not-allowed;
    }

    .field-input.birthday input {
        border-color: #e2e8f0;
        background: white;
    }

    /* Разделитель */
    .form-divider {
        grid-column: span 2;
        position: relative;
        text-align: center;
        margin: 20px 0 10px;
    }

    .form-divider::before {
        content: "";
        position: absolute;
        top: 50%;
        left: 0;
        right: 0;
        height: 1px;
        background: linear-gradient(90deg, transparent, #e2e8f0, transparent);
    }

    .form-divider span {
        position: relative;
        background: white;
        padding: 0 20px;
        color: #64748b;
        font-size: 14px;
        font-weight: 500;
    }

    /* Поле пароля */
    .password-wrapper {
        position: relative;
        display: flex;
        align-items: center;
    }

    .password-input {
        width: 100%;
        padding: 14px 16px !important;
        padding-right: 100px !important;
        border: 2px solid #f0e2e2;
        border-radius: 10px;
    }
    .password-input:focus {
        border-color: var(--DragonDark);
        box-shadow: 0 0 0 4px rgba(234, 102, 102, 0.1);
        outline: none; /* Убираем стандартную обводку браузера */
    }

    .password-actions {
        position: absolute;
        right: 8px;
        display: flex;
        gap: 4px;
    }

    .password-action {
        width: 27px;
        background: none;
        border: none;
        color: #94a3b8;
        font-size: 18px;
        cursor: pointer;
        padding: 8px;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .password-action:hover {
        color: var(--GoldFake);
        transform: scale(1.1);
    }

    .password-action.dice:hover {
        color: #f59e0b;
    }

    /* Индикатор силы пароля */
    .password-strength {
        height: 4px;
        background: #e2e8f0;
        border-radius: 2px;
        margin-top: 8px;
        overflow: hidden;
    }

    .strength-bar {
        height: 100%;
        width: 0;
        transition: width 0.3s, background 0.3s;
    }

    .password-match {
        font-size: 12px;
        margin-top: 6px;
        min-height: 18px;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .password-match.success {
        color: #10b981;
    }

    .password-match.error {
        color: #ef4444;
    }

    /* Кнопки действий */
    .form-actions {
        margin-top: 40px;
        display: flex;
        gap: 20px;
        justify-content: center;
    }

    .save-btn, .reset-btn {
        padding: 14px 40px;
        border-radius: 40px;
        font-weight: 600;
        font-size: 15px;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 10px;
        transition: all 0.3s;
        border: 2px solid;
        font-family: 'Montserrat', sans-serif;
    }

    .save-btn {
        background: linear-gradient(135deg, var(--DragonLight) 0%, var(--Gold) 100%);
        color: white;
        border: none;
        box-shadow: 0 4px 15px rgba(234, 102, 102, 0.3);
    }

    .reset-btn {
        background: white;
        color: #64748b;
        border: 2px solid #e2e8f0;
    }

    .save-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(234, 102, 102, 0.4);
    }

    .reset-btn:hover {
        border-color: #ef4444;
        color: #ef4444;
    }

    /* Модальные окна */
    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        z-index: 10002;
        align-items: center;
        justify-content: center;
    }

    .modal.show {
        display: flex;
    }

    .modal-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.6);
        backdrop-filter: blur(5px);
        z-index: 10002; /* Одинаковый с модалкой */
    }

    .modal-content {
        position: relative;
        background: white;
        border-radius: 32px;
        max-width: 800px;
        width: 90%;
        max-height: 80vh;
        overflow: hidden;
        z-index: 10003;
        animation: modalSlideIn 0.3s ease;
    }

    @keyframes modalSlideIn {
        from {
            opacity: 0;
            transform: translateY(-30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .modal-header {
        background: linear-gradient(135deg, var(--Gold) 0%, var(--DragonLight) 100%);
        color: white;
        padding: 20px 30px;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .modal-title {
        margin: 0;
        font-size: 25px;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .modal-close {
        background: rgba(255, 255, 255, 0.2);
        border: none;
        color: white;
        font-size: 28px;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: 0.2s;
        border: 1px solid rgba(255, 255, 255, 0.3);
    }

    .modal-close:hover {
        background: rgba(255, 255, 255, 0.3);
        transform: rotate(90deg);
    }

    /* Сетка игр в модалке */
    .games-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 20px;
    }

    .game-card {
        background: #fcf8f8;
        border-radius: 16px;
        padding: 15px;
        text-align: center;
        transition: all 0.3s;
    }
    .game-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 20px 40px rgba(231, 92, 92, 0.15);
    }

    .game-card img {
        width: 100%;
        height: 120px;
        object-fit: cover;
        border-radius: 12px;
        margin-bottom: 10px;
    }

    .game-title {
        display: block;
        font-weight: 600;
        color: #334155;
        margin-bottom: 5px;
    }

    .game-status {
        font-size: 11px;
        color: #10b981;
        background: #d1fae5;
        padding: 2px 8px;
        border-radius: 40px;
        display: inline-block;
    }

    /* Сетка достижений в модалке */
    .achievements-full-grid {
        display: flex;
        flex-direction: column;
        gap: 15px;
    }

    .achievement-card {
        background: #f8fafc;
        border-radius: 16px;
        padding: 20px;
        display: flex;
        align-items: center;
        gap: 20px;
        transition: all 0.3s;
    }

    .achievement-card.unlocked {
        background: linear-gradient(135deg, #fcf8f8, #f9f1f1);
        border: 1px solid var(--DragonLight);
    }

    .achievement-card.locked {
        opacity: 0.7;
    }

    .achievement-card.rare {
        border: 2px solid #f59e0b;
        background: linear-gradient(135deg, #fff7ed, #fef3c7);
        animation: rarePulse 2s infinite;
        position: relative;
        overflow: hidden;
    }

    .achievement-card.rare::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: conic-gradient(
            from 0deg,
            transparent,
            rgba(255, 215, 0, 0.1),
            transparent 30%,
            transparent 70%,
            rgba(255, 215, 0, 0.1),
            transparent
        );
        animation: rotateGlow 4s linear infinite;
        pointer-events: none;
    }

    .achievement-icon-large {
        width: 50px;
        height: 50px;
        background: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        color: #f59e0b;
        position: relative;
        z-index: 2;
    }

    .achievement-info {
        flex: 1;
        position: relative;
        z-index: 2;
    }

    .achievement-info .achievement-name {
        font-size: 16px;
        margin-bottom: 5px;
    }

    .achievement-info .achievement-desc {
        font-size: 13px;
        color: #64748b;
        margin-bottom: 5px;
    }

    .achievement-progress {
        font-size: 12px;
        font-weight: 600;
        color: #334155;
    }

    @keyframes slideUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Адаптивность */
    @media (max-width: 768px) {
        .character-card {
            flex-direction: column;
            text-align: center;
        }
        
        .character-name-wrapper {
            justify-content: center;
        }
        
        .progress-bar-container {
            margin: 0 auto;
        }
        
        .inventory-grid,
        .games-mini-grid,
        .achievements-grid {
            grid-template-columns: repeat(2, 1fr);
        }
        
        .form-grid {
            grid-template-columns: 1fr;
        }
        
        .form-group-card.full-width {
            grid-column: span 1;
        }
        
        .form-divider {
            grid-column: span 1;
        }
        
        .form-actions {
            flex-direction: column;
        }
        
        .games-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 480px) {
        .inventory-grid,
        .games-mini-grid,
        .achievements-grid,
        .games-grid {
            grid-template-columns: 1fr;
        }
        
        .character-settings {
            padding: 20px;
        }
        
        .section-header-with-button {
            flex-direction: column;
            gap: 10px;
            align-items: flex-start;
        }
        
        .form-group-card {
            padding: 20px;
        }
    }









.top-header {
    z-index: 99 !important;
}


    /* Параллакс фон */
    .parallax-bg {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 120vh; /* Выше экрана для эффекта движения */
        background: url('/bitrix/templates/MySite2/images/fonProfile3.jpg') no-repeat center center;
        background-size: cover;
        z-index: 0;
        will-change: transform;
        transform: translateY(0); /* Начальная позиция */
    }

    /* Контейнер с контентом */
    .profile-container-box {
        position: relative;
        z-index: 1;
        max-width: 1000px;
        margin: 0 auto;
        padding: 40px 20px;
    }
    /* ===== ФИКС ДЛЯ ШАПКИ ===== */
    

    /* Фикс для подвала */
    footer, .footer,
    #footer, #footer-container,
    [class*="footer"] {
        position: relative !important;
        z-index: 10000 !important;
        pointer-events: auto;
    }

    #bx-panel.bx-panel-fixed {
        z-index: 10001 !important;
    }

    /* Убеждаемся, что body не создает проблем */
    body {
        position: relative;
        margin: 0;
        padding: 0;
        overflow-x: hidden;
        background-color: #f9fafc;
        color: #1e293b;
        line-height: 1.5;
    }

    .games-mini-grid.empty-collection {
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 120px;
    }

    .empty-collection-message {
        text-align: center;
        color: #94a3b8;
    }

    .empty-collection-message i {
        font-size: 32px;
        margin-bottom: 10px;
        color: #cbd5e1;
    }

    .empty-collection-message p {
        font-size: 14px;
        margin: 0;
    }

    /* Обновленные стили для формы */
    .field-input.birthday input[type="date"] {
        font-family: 'Montserrat', sans-serif;
        color: #1e293b;
    }

    .field-input.birthday input[type="date"]::-webkit-calendar-picker-indicator {
        background: transparent;
        color: var(--GoldFake);
        cursor: pointer;
        opacity: 0.6;
    }

    .field-input.birthday input[type="date"]::-webkit-calendar-picker-indicator:hover {
        opacity: 1;
    }
</style>

<script>
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
</script>

<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
?>