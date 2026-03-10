<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Восстановление пароля");
?>

<div class="auth-container">
    <div class="auth-box">
        <h1 class="auth-title">Восстановление пароля</h1>
        
        <?$APPLICATION->IncludeComponent(
            "bitrix:system.auth.forgotpasswd",
            "",
            Array()
        );?>
    </div>
</div>

<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
?>