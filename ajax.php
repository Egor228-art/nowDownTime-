<?php
// Файл: /ajax.php (в корне сайта)
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

use Bitrix\Main\Application;
use Bitrix\Main\Context;

$request = Context::getCurrent()->getRequest();
$action = $request->getPost("action");

// Функция для проверки уникальности поля (ИСПРАВЛЕННАЯ)
function isFieldUnique($field, $value, $excludeUserId = 0) {
    if (empty($value)) return true;
    
    // Очищаем значение
    $value = trim($value);
    
    // Создаем фильтр
    $filter = array();
    
    if ($field == "LOGIN") {
        $filter["=LOGIN"] = $value;
    } elseif ($field == "EMAIL") {
        $filter["=EMAIL"] = $value;
    } else {
        return true;
    }
    
    if ($excludeUserId > 0) {
        $filter["!ID"] = $excludeUserId;
    }
    
    // Получаем пользователей
    $rsUser = CUser::GetList(
        ($by = "id"), 
        ($order = "desc"), 
        $filter,
        array("FIELDS" => array("ID", "LOGIN", "EMAIL"))
    );
    
    $user = $rsUser->Fetch();
    
    // Возвращаем true если пользователь НЕ найден (уникален)
    return !$user;
}

if ($action == "getLoginForm") {
    ?>
    <form method="post" class="bx-auth ajax-login-form" id="ajaxLoginForm" onsubmit="return submitLoginForm(this);">
        <?=bitrix_sessid_post()?>
        <input type="hidden" name="AUTH_FORM" value="Y">
        <input type="hidden" name="TYPE" value="AUTH">
        
        <div id="loginFormErrors" class="login-error" style="display: none;"></div>
        
        <div class="form-group">
            <label class="form-label">Логин или Email <span class="required">*</span></label>
            <div class="input-wrapper">
                <i class="input-icon fas fa-user"></i>
                <input type="text" 
                       name="USER_LOGIN" 
                       class="form-input" 
                       required 
                       value="<?=htmlspecialcharsbx($APPLICATION->get_cookie("LOGIN"))?>" 
                       placeholder="Введите ваш логин или email">
            </div>
        </div>
        
        <div class="form-group">
            <label class="form-label">Пароль <span class="required">*</span></label>
            <div class="input-wrapper">
                <i class="input-icon fas fa-lock"></i>
                <input type="password" 
                       name="USER_PASSWORD" 
                       class="form-input" 
                       required 
                       placeholder="Введите ваш пароль">
                <button type="button" class="password-toggle" onclick="togglePassword(this);">
                    <i class="far fa-eye"></i>
                </button>
            </div>
        </div>
        
        <div class="form-group rememberme-group">
            <label class="checkbox-label">
                <input type="checkbox" name="USER_REMEMBER" value="Y" id="user_remember" checked>
                <span class="checkbox-custom"></span>
                <span class="checkbox-text">Запомнить меня</span>
            </label>
        </div>
        
        <button type="submit" name="Login" class="submit-btn">
            <span>Войти</span>
            <i class="fas fa-arrow-right"></i>
        </button>
    </form>
    
    <div class="login-social">
        <div class="social-divider">
            <span>Или войдите через соцсети</span>
        </div>
        <div class="social-buttons">
            <button class="social-btn vk" onclick="alert('Социальная авторизация в разработке')">
                <i class="fab fa-vk"></i>
            </button>
            <button class="social-btn ok" onclick="alert('Социальная авторизация в разработке')">
                <i class="fab fa-odnoklassniki"></i>
            </button>
            <button class="social-btn yandex" onclick="alert('Социальная авторизация в разработке')">
                <i class="fab fa-yandex"></i>
            </button>
        </div>
    </div>
<?php
}
elseif ($action == "getRegisterForm") {
    // ФОРМА РЕГИСТРАЦИИ
?>
    <form method="post" class="bx-auth ajax-register-form" id="ajaxRegisterForm" onsubmit="return submitRegisterForm(this);">
        <?=bitrix_sessid_post()?>
        <input type="hidden" name="REGISTER_FORM" value="Y">
        <input type="hidden" name="TYPE" value="REGISTRATION">
        
        <div class="form-group">
            <label class="form-label">Логин <span class="required">*</span></label>
            <div class="input-wrapper">
                <i class="input-icon fas fa-user"></i>
                <input type="text" 
                       name="LOGIN" 
                       id="registerLogin"
                       class="form-input" 
                       required 
                       placeholder="Придумайте логин"
                       onblur="checkLoginUnique(this.value)">
            </div>
            <small class="form-hint">Только латинские буквы и цифры</small>
            <div class="field-status" id="loginStatus"></div>
        </div>
        
        <div class="form-group">
            <label class="form-label">Email <span class="required">*</span></label>
            <div class="input-wrapper">
                <i class="input-icon fas fa-envelope"></i>
                <input type="email" 
                       name="EMAIL" 
                       id="registerEmail"
                       class="form-input" 
                       required 
                       placeholder="example@mail.ru"
                       onblur="checkEmailUnique(this.value)">
            </div>
            <div class="field-status" id="emailStatus"></div>
        </div>
        
        <div class="form-group">
            <label class="form-label">Телефон</label>
            <div class="input-wrapper">
                <i class="input-icon fas fa-phone"></i>
                <input type="text" 
                       name="PERSONAL_PHONE" 
                       class="form-input" 
                       placeholder="+7 (999) 123-45-67"
                       id="registerPhone">
            </div>
            <small class="form-hint">Для уточнения деталей заказа</small>
        </div>
        
        <div class="form-group">
            <label class="form-label">Пароль <span class="required">*</span></label>
            <div class="input-wrapper">
                <i class="input-icon fas fa-lock"></i>
                <input type="password" 
                       name="PASSWORD" 
                       class="form-input" 
                       required 
                       placeholder="Минимум 6 символов"
                       id="registerPassword">
                <button type="button" class="password-toggle" onclick="togglePassword(this);">
                    <i class="far fa-eye"></i>
                </button>
            </div>
            <div class="password-strength" id="passwordStrength">
                <div class="strength-bar"></div>
            </div>
        </div>
        
        <div class="form-group">
            <label class="form-label">Подтверждение пароля <span class="required">*</span></label>
            <div class="input-wrapper">
                <i class="input-icon fas fa-lock"></i>
                <input type="password" 
                       name="CONFIRM_PASSWORD" 
                       class="form-input" 
                       required 
                       placeholder="Повторите пароль"
                       id="registerConfirmPassword">
            </div>
            <div class="password-match" id="passwordMatch"></div>
        </div>
        
        <div class="form-group agree-group">
            <label class="checkbox-label">
                <input type="checkbox" name="USER_AGREEMENT" value="Y" id="userAgreement" required>
                <span class="checkbox-custom"></span>
                <span class="checkbox-text">Я принимаю условия <a href="/agreement/" target="_blank">пользовательского соглашения</a></span>
            </label>
        </div>
        
        <button type="submit" name="Register" class="submit-btn">
            <span>Зарегистрироваться</span>
            <i class="fas fa-arrow-right"></i>
        </button>
    </form>
    
<style>
    .field-status {
        font-size: 12px;
        margin-top: 5px;
        min-height: 18px;
    }
    .field-status.success {
        color: #10b981;
    }
    .field-status.error {
        color: #ef4444;
    }
    .field-status i {
        margin-right: 4px;
    }
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
        margin-top: 5px;
        min-height: 18px;
    }
</style>
<?php
}
elseif ($action == "login") {
    global $USER;
    
    $login = trim($request->getPost("USER_LOGIN"));
    $password = $request->getPost("USER_PASSWORD");
    $remember = $request->getPost("USER_REMEMBER") == "Y" ? "Y" : "N";
    
    $response = array();
    
    $arAuthResult = $USER->Login($login, $password, $remember);
    
    if ($USER->IsAuthorized()) {
        $response["success"] = "Y";
    } else {
        $errorMsg = "Неверный логин или пароль";
        
        if (is_array($arAuthResult) && isset($arAuthResult["MESSAGE"])) {
            $errorMsg = $arAuthResult["MESSAGE"];
        } elseif (is_string($arAuthResult) && !empty($arAuthResult)) {
            $errorMsg = $arAuthResult;
        }
        
        $response["success"] = "N";
        $response["error"] = $errorMsg;
    }
    
    header('Content-Type: application/json');
    echo json_encode($response);
}
elseif ($action == "register") {
    global $USER;
    
    $response = array();
    
    // Получаем данные из формы
    $login = trim($request->getPost("LOGIN") ?? '');
    $email = trim($request->getPost("EMAIL") ?? '');
    $phone = trim($request->getPost("PERSONAL_PHONE") ?? '');
    $password = $request->getPost("PASSWORD") ?? '';
    $confirmPassword = $request->getPost("CONFIRM_PASSWORD") ?? '';
    $agreement = $request->getPost("USER_AGREEMENT") ?? '';
    
    // Валидация
    if (!$login || !$email || !$password || !$confirmPassword) {
        $response["success"] = "N";
        $response["error"] = "Заполните все обязательные поля";
        header('Content-Type: application/json');
        echo json_encode($response);
        return;
    }
    
    if ($password != $confirmPassword) {
        $response["success"] = "N";
        $response["error"] = "Пароли не совпадают";
        header('Content-Type: application/json');
        echo json_encode($response);
        return;
    }
    
    if (strlen($password) < 6) {
        $response["success"] = "N";
        $response["error"] = "Пароль должен быть не менее 6 символов";
        header('Content-Type: application/json');
        echo json_encode($response);
        return;
    }
    
    if (!$agreement) {
        $response["success"] = "N";
        $response["error"] = "Необходимо принять пользовательское соглашение";
        header('Content-Type: application/json');
        echo json_encode($response);
        return;
    }
    
    // Проверяем уникальность логина
    $filterLogin = array("LOGIN" => $login);
    $rsLogin = CUser::GetList(($by="id"), ($order="desc"), $filterLogin);
    if ($rsLogin->SelectedRowsCount() > 0) {
        $response["success"] = "N";
        $response["error"] = "Логин '$login' уже занят";
        header('Content-Type: application/json');
        echo json_encode($response);
        return;
    }
    
    // Проверяем уникальность email
    $filterEmail = array("EMAIL" => $email);
    $rsEmail = CUser::GetList(($by="id"), ($order="desc"), $filterEmail);
    if ($rsEmail->SelectedRowsCount() > 0) {
        $response["success"] = "N";
        $response["error"] = "Email '$email' уже занят";
        header('Content-Type: application/json');
        echo json_encode($response);
        return;
    }
    
    // Подготавливаем данные для Битрикса
    $userFields = array(
        "LOGIN" => $login,
        "EMAIL" => $email,
        "NAME" => "Без имени", // Заглушка
        "PASSWORD" => $password,
        "CONFIRM_PASSWORD" => $confirmPassword,
        "ACTIVE" => "Y",
        "LID" => SITE_ID,
        "GROUP_ID" => array(2),
    );
    
    // Добавляем телефон, если есть
    if (!empty($phone)) {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        if (strlen($phone) == 11 && $phone[0] == '7') {
            $phone = substr($phone, 1);
        }
        if (strlen($phone) == 10) {
            $userFields["PERSONAL_PHONE"] = $phone;
        }
    }
    
    // Регистрация
    $user = new CUser;
    $userId = $user->Add($userFields);
    
    if (intval($userId) > 0) {
        // АВТОМАТИЧЕСКАЯ АВТОРИЗАЦИЯ
        $USER->Authorize($userId);
        
        // Отправляем успешный ответ с редиректом
        $response["success"] = "Y";
        $response["redirect"] = "/personal/"; // Сразу в личный кабинет
    } else {
        $errorMsg = $user->LAST_ERROR;
        if (empty($errorMsg)) {
            $errorMsg = "Ошибка при регистрации";
        }
        $response["success"] = "N";
        $response["error"] = $errorMsg;
    }
    
    header('Content-Type: application/json');
    echo json_encode($response);
}
elseif ($action == "check_field") {
    $field = $request->getPost("field");
    $value = $request->getPost("value");
    
    $response = array("unique" => true);
    
    if ($field == "LOGIN") {
        // Проверяем существование пользователя с таким логином
        $by = "id";
        $order = "desc";
        $filter = array(
            "LOGIN" => $value,
            "!ID" => false // Это исключит системные ошибки
        );
        
        $rsUser = CUser::GetList($by, $order, $filter);
        
        if ($rsUser->SelectedRowsCount() > 0) {
            $response["unique"] = false;
            $response["message"] = "Логин '" . $value . "' уже занят";
        }
    } elseif ($field == "EMAIL") {
        $by = "id";
        $order = "desc";
        $filter = array(
            "EMAIL" => $value,
            "!ID" => false
        );
        
        $rsUser = CUser::GetList($by, $order, $filter);
        
        if ($rsUser->SelectedRowsCount() > 0) {
            $response["unique"] = false;
            $response["message"] = "Email '" . $value . "' уже занят";
        }
    }
    
    header('Content-Type: application/json');
    echo json_encode($response);
}
?>