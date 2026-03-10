<?
define("NEED_AUTH", true);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Личный кабинет");
?><?if (!$USER->IsAuthorized()):?> <script>
        window.location.href = "/login/?backurl=<?=urlencode($APPLICATION->GetCurPage())?>";
    </script> <?endif;?>
<div class="personal-container">
	<h1>Личный кабинет</h1>
	<p>
		 Добро пожаловать, <?=$USER->GetFullName() ?: $USER->GetLogin()?>!
	</p>
 <a href="/?logout=yes&<?=bitrix_sessid_get()?>" class="logout-btn">Выйти</a>
	 <?$APPLICATION->IncludeComponent(
	"bitrix:main.profile",
	"",
	Array(
		"AJAX_MODE" => "N",
		"SET_TITLE" => "Y",
		"USER_PROPERTY" => ""
	),
false,
Array(
	'ACTIVE_COMPONENT' => 'Y'
)
);?>
</div>
<style>
.personal-container {
    max-width: 800px;
    margin: 40px auto;
    padding: 30px;
    background: white;
    border-radius: 24px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.05);
}
.logout-btn {
    display: inline-block;
    margin-top: 20px;
    padding: 10px 25px;
    background: #ef4444;
    color: white;
    border-radius: 40px;
    text-decoration: none;
    font-weight: 500;
}
</style><?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
?>