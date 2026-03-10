<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Регистрация");
?>

<div class="auth-container">
    <div class="auth-box">
        <h1 class="auth-title">Создать аккаунт</h1>
        
        <?$APPLICATION->IncludeComponent(
            "bitrix:main.register",
            "",
            Array(
                "USER_PROPERTY_NAME" => "",
                "SHOW_FIELDS" => Array("NAME", "EMAIL", "PERSONAL_PHONE"),
                "REQUIRED_FIELDS" => Array("NAME", "EMAIL"),
                "AUTH" => "Y",
                "USE_BACKURL" => "Y",
                "SUCCESS_PAGE" => "/",
                "SET_TITLE" => "Y",
                "USER_PROPERTY" => Array()
            )
        );?>
    </div>
</div>

<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
?>