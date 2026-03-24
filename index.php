<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Главная - Магазин настольных игр");
?><div>
</div>
 <!-- Герой-блок --> <section class="hero">
<div class="container">
	<div class="hero-text">
		<h1>Погрузись в мир<br>
		 настольных приключений</h1>
		<p>
			 D&amp;D, Манчкин, Имаджинариум, Каркассон и сотни других игр в наличии. Доставка по всей России.
		</p>
 <button onclick="window.location.href='/catalog/'" class="hero-btn">Смотреть новинки →</button>
	</div>
	<div class="hero-image">
 <i class="fas fa-dragon"></i>
	</div>
</div>
 </section>
<div class="container">
	 <!-- Быстрые категории -->
	<div class="categories">
		<div class="category-item">
 <a href="/catalog/?SECTION_ID=17"><i class="fas fa-dice-d6"></i>
			<h4>D&amp;D</h4>
 </a>
		</div>
		<div class="category-item">
 <a href="/catalog/?SECTION_ID=18"> <i class="fas fa-chess-queen"></i>
			<h4>Стратегии</h4>
 </a>
		</div>
		<div class="category-item">
 <a href="/catalog/?SECTION_ID=22"> <i class="fas fa-brain"></i>
			<h4>Кооперативные</h4>
 </a>
		</div>
		<div style="padding-top: 30px;" onclick="location.href='/catalog/?filter_time=0-30'" class="category-item">
 <i class="fas fa-clock"></i>
			<h4>Быстрые игры</h4>
		</div>
		<div class="category-item">
 <a href="/catalog/?SECTION_ID=23"> <i class="fas fa-user-alt"></i>
			<h4>На одного</h4>
 </a>
		</div>
 <a href="/catalog/?SECTION_ID=23"> </a>
	</div>
	 <!-- Популярные игры -->
	<h2 class="section-title">Популярное сейчас</h2>
	<div class="games-grid" id="popularGamesGrid">
		 <?php include($_SERVER["DOCUMENT_ROOT"]."/ajax/get_popular_products.php"); ?>
	</div>
</div>
 <a href="/catalog/?SECTION_ID=23"> <br>
 </a>
<script>
function addToCart(productId, quantity = 1) {
        console.log('🟡 Добавляем товар:', productId, 'количество:', quantity);
        
        fetch('/ajax/add_to_cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=add&product_id=' + productId + '&quantity=' + quantity
        })
        .then(response => response.json())
        .then(data => {
            console.log('🟢 Ответ от сервера:', data);
            
            if (data.success) {
                if (window.updateCartCounter) {
                    window.updateCartCounter();
                }
            }
        });
    }
</script><?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
?>