<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Главная - Магазин настольных игр");
?>

<!-- Герой-блок -->
<section class="hero">
    <div class="container">
        <div class="hero-text">
            <h1>Погрузись в мир<br>настольных приключений</h1>
            <p>D&D, Манчкин, Имаджинариум, Каркассон и сотни других игр в наличии. Доставка по всей России.</p>
            <button class="hero-btn">Смотреть новинки →</button>
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
            <i class="fas fa-dice-d6"></i>
            <h4>D&D</h4>
        </div>
        <div class="category-item">
            <i class="fas fa-chess-queen"></i>
            <h4>Стратегии</h4>
        </div>
        <div class="category-item">
            <i class="fas fa-brain"></i>
            <h4>Кооперативные</h4>
        </div>
        <div class="category-item">
            <i class="fas fa-clock"></i>
            <h4>Быстрые игры</h4>
        </div>
        <div class="category-item">
            <i class="fas fa-crown"></i>
            <h4>Хиты</h4>
        </div>
    </div>

    <!-- Популярные игры -->
    <h2 class="section-title">Популярное сейчас</h2>
    <div class="games-grid">
        <!-- Карточка 1 -->
        <div class="game-card">
            <div class="game-image">
                <i class="fas fa-dragon"></i>
                <div class="badge">Хит</div>
            </div>
            <div class="game-info">
                <div class="game-category">D&D</div>
                <h3>Подземелья и драконы: Стартовый набор</h3>
                <div class="game-meta">
                    <span><i class="fas fa-user-friends"></i> 2-6 игр.</span>
                    <span><i class="fas fa-clock"></i> 60-120 мин</span>
                </div>
                <div class="game-price">
                    <span class="price">3 490 ₽</span>
                    <button class="add-to-cart"><i class="fas fa-shopping-cart"></i></button>
                </div>
            </div>
        </div>

        <!-- Карточка 2 -->
        <div class="game-card">
            <div class="game-image">
                <i class="fas fa-hat-wizard"></i>
            </div>
            <div class="game-info">
                <div class="game-category">Фэнтези</div>
                <h3>Манчкин: Делюкс издание</h3>
                <div class="game-meta">
                    <span><i class="fas fa-user-friends"></i> 3-6 игр.</span>
                    <span><i class="fas fa-clock"></i> 40-90 мин</span>
                </div>
                <div class="game-price">
                    <span class="price">2 990 ₽</span>
                    <button class="add-to-cart"><i class="fas fa-shopping-cart"></i></button>
                </div>
            </div>
        </div>

        <!-- Карточка 3 -->
        <div class="game-card">
            <div class="game-image">
                <i class="fas fa-city"></i>
                <div class="badge">Скидка</div>
            </div>
            <div class="game-info">
                <div class="game-category">Градостроение</div>
                <h3>Каркассон: Королевский подарок</h3>
                <div class="game-meta">
                    <span><i class="fas fa-user-friends"></i> 2-5 игр.</span>
                    <span><i class="fas fa-clock"></i> 35-45 мин</span>
                </div>
                <div class="game-price">
                    <span class="price">2 490 ₽</span>
                    <button class="add-to-cart"><i class="fas fa-shopping-cart"></i></button>
                </div>
            </div>
        </div>

        <!-- Карточка 4 -->
        <div class="game-card">
            <div class="game-image">
                <i class="fas fa-theater-masks"></i>
            </div>
            <div class="game-info">
                <div class="game-category">Вечеринка</div>
                <h3>Имаджинариум: Союзмультфильм</h3>
                <div class="game-meta">
                    <span><i class="fas fa-user-friends"></i> 4-8 игр.</span>
                    <span><i class="fas fa-clock"></i> 40-60 мин</span>
                </div>
                <div class="game-price">
                    <span class="price">2 790 ₽</span>
                    <button class="add-to-cart"><i class="fas fa-shopping-cart"></i></button>
                </div>
            </div>
        </div>
    </div>
</div>
<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
?>