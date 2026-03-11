<?
define("NEED_AUTH", true);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Редактирование профиля");
?>

<?if (!$USER->IsAuthorized()):?> 
<script>
    window.location.href = "/login/?backurl=<?=urlencode($APPLICATION->GetCurPage())?>";
</script> 
<?endif;?>

<div class="personal-page">
    <div class="personal-header" style="padding: 30px;">
        <div class="personal-header-content">
            <h1 class="personal-title">Редактирование профиля</h1>
            <p class="personal-welcome">Заполните или измените ваши данные</p>
        </div>
        <a href="/personal/" class="logout-btn" style="background: rgba(255,255,255,0.2);">
            <i class="fas fa-arrow-left"></i>
            <span>Назад</span>
        </a>
    </div>

    <div class="personal-content">
        <div class="profile-section">
            <div class="section-header">
                <h2 class="section-title">
                    <i class="fas fa-user-cog"></i>
                    Персональная информация
                </h2>
                <p class="section-desc">Особое внимание обратите на поле "Имя" - сейчас там "Без имени", замените на настоящее</p>
            </div>

            <?$APPLICATION->IncludeComponent(
                "bitrix:main.profile",
                "",
                Array(
                    "AJAX_MODE" => "N",
                    "SET_TITLE" => "N",
                    "USER_PROPERTY" => array(
                        "LOGIN",
                        "EMAIL",
                        "NAME",
                        "LAST_NAME",
                        "PERSONAL_PHONE",
                        "PERSONAL_CITY",
                        "PERSONAL_BIRTHDAY"
                    ),
                    "USER_PROPERTY_NAME" => "Персональные данные"
                ),
                false,
                Array('ACTIVE_COMPONENT' => 'Y')
            );?>
        </div>
    </div>
</div>

<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
?>