<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

$orderId = intval($_GET['order_id'] ?? 0);
?>

<div class="container" style="text-align: center; padding: 60px 20px;">
    <div style="font-size: 64px; color: #27ae60; margin-bottom: 20px;">✅</div>
    <h1>Спасибо за заказ!</h1>
    <p style="font-size: 18px; color: #666; margin-bottom: 30px;">
        Ваш заказ №<?= $orderId ?> успешно оформлен.<br>
        Мы свяжемся с вами в ближайшее время.
    </p>
    <a href="/catalog/" class="btn-catalog">Продолжить покупки</a>
</div>

<style>
.btn-catalog {
    display: inline-block;
    padding: 15px 40px;
    background: linear-gradient(135deg, #eabb66, #e74c3c);
    color: white;
    text-decoration: none;
    border-radius: 8px;
    font-size: 16px;
    font-weight: 500;
    transition: all 0.3s;
}

.btn-catalog:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(231, 76, 60, 0.3);
}
</style>

<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
?>