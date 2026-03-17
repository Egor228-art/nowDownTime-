<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("О магазине nowDownTime+");
?>

<div class="about-page">
    <!-- Hero секция -->
    <section class="about-hero">
        <div class="container">
            <h1>О магазине <span class="highlight">nowDownTime+</span></h1>
            <p class="subtitle">Мы не просто продаём игры — мы создаём сообщество</p>
        </div>
    </section>

    <!-- Кто мы -->
    <section class="about-section">
        <div class="container">
            <div class="section-header">
                <h2>🎲 Кто мы</h2>
                <div class="divider"></div>
            </div>
            <div class="content-grid">
                <div class="content-text">
                    <p>Магазин настольных игр <strong>nowDownTime+</strong> — это место, где собираются любители настолок. Мы работаем с 2020 года и за это время помогли выбрать игры тысячам новгородцев.</p>
                    <p>Наша миссия — сделать настольные игры доступными для каждого. Мы не просто продаём коробки с правилами, мы помогаем людям находить новых друзей, интересно проводить время и открывать для себя увлекательные миры.</p>
                    <p>В нашем ассортименте вы найдёте всё: от классических "Монополии" и "Уно" до сложных стратегий и ролевых игр как Dungeons & Dragons.</p>
                </div>
                <div class="content-image">
                    <img src="/images/about-play.jpg" alt="Игроки за столом">
                </div>
            </div>
        </div>
    </section>

    <!-- Контакты -->
    <section class="contacts-section">
        <div class="container">
            <div class="section-header">
                <h2>📍 Контакты</h2>
                <div class="divider"></div>
            </div>
            
            <div class="contacts-grid">
                <div class="contact-card">
                    <i class="fas fa-map-marker-alt"></i>
                    <h3>Адрес</h3>
                    <p>г. Великий Новгород<br>ул. Большая Московская, д. 8</p>
                </div>
                
                <div class="contact-card">
                    <i class="fas fa-phone"></i>
                    <h3>Телефон</h3>
                    <p><a href="tel:+79991234567">+7 (999) 123-45-67</a></p>
                    <p class="small">Ежедневно с 10:00 до 20:00</p>
                </div>
                
                <div class="contact-card">
                    <i class="fas fa-envelope"></i>
                    <h3>Email</h3>
                    <p><a href="mailto:info@nowdowntime.ru">info@nowdowntime.ru</a></p>
                    <p class="small">Ответим в течение 2 часов</p>
                </div>
                
                <div class="contact-card">
                    <i class="fas fa-clock"></i>
                    <h3>Режим работы</h3>
                    <p>Пн-Пт: 10:00–20:00<br>Сб-Вс: 11:00–18:00</p>
                </div>
            </div>
            
            <!-- Карта -->
            <div class="map-container">
                <iframe src="https://yandex.ru/map-widget/v1/?ll=31.275463,58.521475&z=17&pt=31.275463,58.521475,pm2rdm" width="100%" height="400" frameborder="0" allowfullscreen></iframe>
            </div>
        </div>
    </section>

    <!-- Доставка и оплата -->
    <section class="delivery-section">
        <div class="container">
            <div class="section-header">
                <h2>🚚 Доставка и оплата</h2>
                <div class="divider"></div>
            </div>
            
            <div class="services-grid">
                <div class="service-card">
                    <div class="service-icon pickup">
                        <i class="fas fa-store"></i>
                    </div>
                    <h3>Самовывоз</h3>
                    <p>Забирайте заказ из нашего магазина. Бесплатно.</p>
                    <p class="service-note">Заказ готов через 1 час после подтверждения</p>
                </div>
                
                <div class="service-card">
                    <div class="service-icon delivery">
                        <i class="fas fa-truck"></i>
                    </div>
                    <h3>Доставка курьером</h3>
                    <p>По Великому Новгороду — 500 ₽</p>
                    <p class="service-note">Бесплатно при заказе от 5000 ₽</p>
                </div>
                
                <div class="service-card">
                    <div class="service-icon cash">
                        <i class="fas fa-money-bill"></i>
                    </div>
                    <h3>Наличные</h3>
                    <p>При получении курьеру или в магазине</p>
                </div>
                
                <div class="service-card">
                    <div class="service-icon card">
                        <i class="fas fa-credit-card"></i>
                    </div>
                    <h3>Картой онлайн</h3>
                    <p>Visa, MasterCard, МИР через сайт</p>
                </div>
            </div>
            
            <div class="important-info">
                <h4>⚠️ Важно знать</h4>
                <ul>
                    <li>Минимальная сумма заказа — 500 ₽</li>
                    <li>Заказы обрабатываются с 10:00 до 19:00 в будние дни</li>
                    <li>После оформления менеджер свяжется с вами для подтверждения</li>
                    <li>Товары надлежащего качества можно вернуть в течение 14 дней</li>
                </ul>
            </div>
        </div>
    </section>

    <!-- Соцсети -->
    <section class="social-section">
        <div class="container">
            <h3>Мы в соцсетях</h3>
            <div class="social-links">
                <a href="#" class="social-link vk"><i class="fab fa-vk"></i></a>
                <a href="#" class="social-link tg"><i class="fab fa-telegram"></i></a>
                <a href="#" class="social-link discord"><i class="fab fa-discord"></i></a>
                <a href="#" class="social-link youtube"><i class="fab fa-youtube"></i></a>
            </div>
        </div>
    </section>
</div>

<style>
.about-page {
    --primary: #e74c3c;
    --primary-dark: #c0392b;
    --secondary: #eabb66;
    --dark: #2c3e50;
    --light: #ecf0f1;
    --text: #34495e;
}

/* Hero секция */
.about-hero {
    background: linear-gradient(135deg, var(--secondary), var(--primary));
    color: white;
    padding: 80px 0;
    text-align: center;
    margin-bottom: 60px;
}

.about-hero h1 {
    font-size: 48px;
    margin-bottom: 20px;
    text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
}

.about-hero .highlight {
    background: rgba(255,255,255,0.2);
    padding: 0 10px;
    border-radius: 8px;
}

.about-hero .subtitle {
    font-size: 20px;
    opacity: 0.95;
    max-width: 600px;
    margin: 0 auto;
}

/* Общие стили секций */
.about-section,
.contacts-section,
.delivery-section {
    padding: 60px 0;
    border-bottom: 1px solid #eee;
}

.section-header {
    text-align: center;
    margin-bottom: 40px;
}

.section-header h2 {
    font-size: 36px;
    color: var(--dark);
    margin-bottom: 15px;
}

.divider {
    width: 80px;
    height: 4px;
    background: linear-gradient(to right, var(--secondary), var(--primary));
    margin: 0 auto;
    border-radius: 2px;
}

/* Кто мы */
.content-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 40px;
    align-items: center;
}

.content-text p {
    font-size: 16px;
    line-height: 1.8;
    color: var(--text);
    margin-bottom: 20px;
}

.content-image img {
    width: 100%;
    border-radius: 12px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.15);
}

/* Контакты */
.contacts-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 30px;
    margin-bottom: 40px;
}

.contact-card {
    text-align: center;
    padding: 30px 20px;
    background: white;
    border-radius: 12px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.05);
    transition: transform 0.3s;
}

.contact-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(231, 76, 60, 0.15);
}

.contact-card i {
    font-size: 40px;
    color: var(--primary);
    margin-bottom: 15px;
}

.contact-card h3 {
    color: var(--dark);
    margin-bottom: 10px;
    font-size: 18px;
}

.contact-card p {
    color: var(--text);
    margin-bottom: 5px;
}

.contact-card a {
    color: var(--primary);
    text-decoration: none;
    font-weight: 500;
}

.contact-card a:hover {
    text-decoration: underline;
}

.contact-card .small {
    font-size: 13px;
    color: #999;
}

.map-container {
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
}

/* Доставка и оплата */
.services-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 30px;
    margin-bottom: 40px;
}

.service-card {
    text-align: center;
    padding: 30px 20px;
    background: white;
    border-radius: 12px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.05);
}

.service-icon {
    width: 80px;
    height: 80px;
    line-height: 80px;
    border-radius: 50%;
    margin: 0 auto 20px;
    font-size: 32px;
    color: white;
}

.service-icon.pickup { background: linear-gradient(135deg, #3498db, #2980b9); }
.service-icon.delivery { background: linear-gradient(135deg, #e67e22, #d35400); }
.service-icon.cash { background: linear-gradient(135deg, #27ae60, #229954); }
.service-icon.card { background: linear-gradient(135deg, #9b59b6, #8e44ad); }

.service-card h3 {
    color: var(--dark);
    margin-bottom: 10px;
    font-size: 20px;
}

.service-card p {
    color: var(--text);
    margin-bottom: 10px;
    line-height: 1.6;
}

.service-note {
    font-size: 13px;
    color: #999;
    padding-top: 10px;
    border-top: 1px dashed #eee;
}

.important-info {
    background: #fff3e0;
    padding: 30px;
    border-radius: 12px;
    border-left: 5px solid var(--primary);
}

.important-info h4 {
    color: var(--primary-dark);
    margin-bottom: 15px;
    font-size: 18px;
}

.important-info ul {
    list-style: none;
    padding: 0;
}

.important-info li {
    padding: 8px 0 8px 25px;
    position: relative;
    color: var(--text);
}

.important-info li:before {
    content: "✓";
    color: var(--primary);
    position: absolute;
    left: 0;
    font-weight: bold;
}

/* Соцсети */
.social-section {
    background: var(--dark);
    color: white;
    padding: 60px 0;
    text-align: center;
}

.social-section h3 {
    font-size: 28px;
    margin-bottom: 30px;
}

.social-links {
    display: flex;
    gap: 20px;
    justify-content: center;
}

.social-link {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: rgba(255,255,255,0.1);
    color: white;
    font-size: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s;
}

.social-link:hover {
    transform: translateY(-5px);
}

.social-link.vk:hover { background: #4c75a3; }
.social-link.tg:hover { background: #0088cc; }
.social-link.discord:hover { background: #7289da; }
.social-link.youtube:hover { background: #ff0000; }

/* Адаптивность */
@media (max-width: 768px) {
    .about-hero h1 { font-size: 32px; }
    .content-grid { grid-template-columns: 1fr; }
    .contacts-grid { grid-template-columns: 1fr; }
    .services-grid { grid-template-columns: 1fr; }
    .social-links { flex-wrap: wrap; }
}
</style>

<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
?>