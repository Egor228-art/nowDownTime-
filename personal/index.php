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

<head>
    <link rel='stylesheet' href='/personal/personal.css'>";
</head>

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
<script src="/personal/personal.js"></script>
<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
?>