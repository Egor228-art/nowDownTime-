<!-- ПОДВАЛ (как просил: лого, разделы, о нас, соцсети) -->
<head>
    <?php $APPLICATION->ShowHead(); ?>
    <meta charset="<?= LANG_CHARSET ?>">
    <title><?php $APPLICATION->ShowTitle() ?></title>
    
    <!-- Твои стили -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="<?=SITE_TEMPLATE_PATH?>/footer.css">
</head>

    <footer>
        <div class="container">
            <div class="footer-grid">
                <!-- Колонка 1: Лого + о нас + соцсети -->
                <div class="footer-col">
                    <div class="logo">
                        <img src="/images/logo.png" alt="nowDownTime+" height="40">
                    </div>
                    <p>Крупнейший магазин настольных игр в России. D&D, классика, новинки и редкие издания. Помогаем найти игру для любой компании.</p>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-vk"></i></a>
                        <a href="#"><i class="fab fa-telegram-plane"></i></a>
                        <a href="#"><i class="fab fa-youtube"></i></a>
                        <a href="#"><i class="fab fa-discord"></i></a>
                    </div>
                </div>

                <!-- Колонка 2: Разделы каталога (повтор подшапки) -->
                <div class="footer-col" style="content: none;">
                    <h4>Каталог</h4>
                    <ul>
                        <li><a href="#"><i class="fas fa-dice-d20"></i> D&D</a></li>
                        <li><a href="#"><i class="fas fa-chess-board"></i> Настольные игры</a></li>
                        <li><a href="#"><i class="fas fa-puzzle-piece"></i> Головоломки</a></li>
                        <li><a href="#"><i class="fas fa-users"></i> Для компаний</a></li>
                        <li><a href="#"><i class="fas fa-child"></i> Детям</a></li>
                        <li><a href="#"><i class="fas fa-gift"></i> Подарочные наборы</a></li>
                    </ul>
                </div>

                <!-- Колонка 3: Информация -->
                <div class="footer-col">
                    <h4>Информация</h4>
                    <ul>
                        <li><a href="#">О магазине</a></li>
                        <li><a href="#">Доставка и оплата</a></li>
                        <li><a href="#">Политика конфиденциальности</a></li>
                        <li><a href="#">Оптовикам</a></li>
                        <li><a href="#">Блог</a></li>
                    </ul>
                </div>

                <!-- Колонка 4: Контакты -->
                <div class="footer-col">
                    <h4>Контакты</h4>
                    <ul>
                        <li><i class="fas fa-phone"></i> 8 (800) 555-35-35</li>
                        <li><i class="fas fa-envelope"></i> info@nowDownTime.com</li>
                        <li><i class="fas fa-map-marker-alt"></i> Москва, ул. Игровая, 12</li>
                        <li><i class="fas fa-clock"></i> Пн-Вс: 10:00 - 21:00</li>
                    </ul>
                </div>
            </div>

            <div class="copyright">
                © <?=date('Y')?> nowDownTime+. Все права защищены.
            </div>
        </div>
    </footer>
</body>
</html>