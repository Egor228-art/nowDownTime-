<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Вход на сайт");
?>

<div class="login-page">
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <div class="login-logo">
                    <i class="fas fa-dragon"></i>
                    <span>НастолкиSHOP</span>
                </div>
                <h1 class="login-title">Добро пожаловать!</h1>
                <p class="login-subtitle">Войдите в свой аккаунт</p>
            </div>

            <div class="login-form-wrapper">
                <?// Вывод ошибок если есть ?>
                <?if($_REQUEST["login"] == "no"):?>
                    <div class="login-error">
                        <i class="fas fa-exclamation-circle"></i>
                        Неверный логин или пароль
                    </div>
                <?endif;?>

                <?// Стандартный компонент авторизации ?>
                <?$APPLICATION->IncludeComponent(
                    "bitrix:system.auth.authorize",
                    "",
                    Array(
                        "REGISTER_URL" => "/register/",
                        "FORGOT_PASSWORD_URL" => "/forgot/",
                        "PROFILE_URL" => "/personal/",
                        "SHOW_ERRORS" => "Y"
                    ),
                    false
                );?>
            </div>

            <div class="login-footer">
                <p>Нет аккаунта? <a href="/register/">Зарегистрироваться</a></p>
                <p><a href="/forgot/">Забыли пароль?</a></p>
            </div>
        </div>
    </div>
</div>

<style>
/* Подключаем Font Awesome если ещё не подключен */
@import url('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css');

.login-page {
    min-height: 80vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #f5f7fa 0%, #e9ecf2 100%);
    padding: 40px 20px;
}

.login-container {
    width: 100%;
    max-width: 480px;
    margin: 0 auto;
}

.login-card {
    background: white;
    border-radius: 32px;
    overflow: hidden;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
}

.login-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 40px 40px 30px;
    text-align: center;
}

.login-logo {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    margin-bottom: 25px;
}

.login-logo i {
    font-size: 32px;
    color: white;
}

.login-logo span {
    font-size: 24px;
    font-weight: 700;
    letter-spacing: -0.5px;
}

.login-title {
    font-size: 28px;
    font-weight: 700;
    margin-bottom: 8px;
    color: white;
}

.login-subtitle {
    font-size: 16px;
    opacity: 0.9;
    color: rgba(255, 255, 255, 0.9);
}

.login-form-wrapper {
    padding: 40px;
}

/* Стили для формы Битрикса */
.bx-auth {
    max-width: 100%;
}

.bx-auth label {
    display: block;
    margin-bottom: 8px;
    color: #334155;
    font-weight: 600;
    font-size: 14px;
}

.bx-auth input[type="text"],
.bx-auth input[type="password"] {
    width: 100%;
    padding: 14px 18px;
    border: 2px solid #e2e8f0;
    border-radius: 16px;
    margin-bottom: 20px;
    font-size: 15px;
    transition: all 0.3s;
    background: #f8fafc;
}

.bx-auth input[type="text"]:focus,
.bx-auth input[type="password"]:focus {
    border-color: #667eea;
    background: white;
    outline: none;
    box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
}

.bx-auth input[type="checkbox"] {
    margin-right: 8px;
    transform: scale(1.1);
}

.bx-auth input[type="submit"] {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    border: none;
    padding: 16px 30px;
    border-radius: 40px;
    font-weight: 700;
    font-size: 16px;
    cursor: pointer;
    width: 100%;
    transition: all 0.3s;
    margin-top: 10px;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
}

.bx-auth input[type="submit"]:hover {
    background: linear-gradient(135deg, #5a6fd6, #6842a0);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
}

.bx-auth .bx-auth-line {
    display: none; /* Убираем лишние линии */
}

.bx-auth .bx-auth-note {
    text-align: center;
    color: #64748b;
    font-size: 14px;
    margin: 20px 0;
}

/* Ссылки в форме */
.bx-auth a {
    color: #667eea;
    text-decoration: none;
    font-weight: 500;
    transition: 0.2s;
}

.bx-auth a:hover {
    color: #764ba2;
    text-decoration: underline;
}

/* Чекбокс "Запомнить меня" */
.bx-auth .rememberme {
    display: flex;
    align-items: center;
    margin: 15px 0;
    color: #475569;
    font-size: 14px;
}

/* Социальные кнопки (если нужны) */
.login-social {
    margin-top: 30px;
    text-align: center;
}

.login-social p {
    color: #64748b;
    font-size: 14px;
    margin-bottom: 15px;
    position: relative;
}

.login-social p:before,
.login-social p:after {
    content: "";
    position: absolute;
    top: 50%;
    width: 30%;
    height: 1px;
    background: #e2e8f0;
}

.login-social p:before {
    left: 0;
}

.login-social p:after {
    right: 0;
}

.social-buttons {
    display: flex;
    gap: 12px;
    justify-content: center;
}

.social-btn {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    border: 2px solid #e2e8f0;
    background: white;
    color: #475569;
    font-size: 20px;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
}

.social-btn:hover {
    border-color: #667eea;
    color: #667eea;
    transform: translateY(-2px);
}

.login-footer {
    padding: 30px 40px 40px;
    text-align: center;
    border-top: 1px solid #eef2f6;
    background: #f8fafc;
}

.login-footer p {
    margin: 8px 0;
    color: #475569;
}

.login-footer a {
    color: #667eea;
    text-decoration: none;
    font-weight: 600;
    transition: 0.2s;
}

.login-footer a:hover {
    color: #764ba2;
    text-decoration: underline;
}

.login-error {
    background: #fee2e2;
    border: 1px solid #fecaca;
    color: #991b1b;
    padding: 16px 20px;
    border-radius: 16px;
    margin-bottom: 25px;
    display: flex;
    align-items: center;
    gap: 10px;
    font-weight: 500;
}

.login-error i {
    font-size: 20px;
    color: #dc2626;
}

/* Адаптивность */
@media (max-width: 600px) {
    .login-header {
        padding: 30px 20px;
    }
    
    .login-form-wrapper,
    .login-footer {
        padding: 30px 20px;
    }
    
    .login-title {
        font-size: 24px;
    }
}
</style>

<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
?>