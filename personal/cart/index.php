<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

$APPLICATION->SetTitle("Корзина");
?>

<div class="container">
    <h1>Корзина</h1>
    
    <div id="cart-content">
        <!-- Здесь будет загружаться корзина через AJAX -->
        <div class="cart-loading">Загрузка корзины...</div>
    </div>
</div>

<style>
	.recent-orders {
		margin-top: 40px;
		padding: 20px;
		background: #f8f9fa;
		border-radius: 12px;
		border: 1px solid #e9ecef;
	}

	.recent-orders h3 {
		margin: 0 0 15px;
		font-size: 18px;
		color: #333;
	}

	.recent-orders-list {
		display: flex;
		flex-direction: column;
		gap: 10px;
		margin-bottom: 15px;
	}

	.recent-order-item {
		display: flex;
		justify-content: space-between;
		align-items: center;
		padding: 12px 15px;
		background: white;
		border-radius: 8px;
		border: 1px solid #e0e0e0;
		transition: all 0.3s;
	}

	.recent-order-item:hover {
		border-color: #e74c3c;
		box-shadow: 0 2px 8px rgba(231, 76, 60, 0.1);
	}

	.recent-order-info {
		flex: 1;
	}

	.recent-order-number {
		font-weight: 500;
		color: #333;
		margin-bottom: 4px;
	}

	.recent-order-date {
		font-size: 12px;
		color: #999;
	}

	.recent-order-status {
		font-size: 12px;
		padding: 3px 8px;
		border-radius: 12px;
		margin-left: 10px;
	}

	.recent-order-total {
		font-weight: 500;
		color: #e74c3c;
		margin: 0 15px;
	}

	.recent-order-link {
		color: #999;
		text-decoration: none;
		font-size: 18px;
		transition: all 0.3s;
	}

	.recent-order-link:hover {
		color: #e74c3c;
		transform: translateX(3px);
	}

	.recent-orders-loading {
		text-align: center;
		padding: 20px;
		color: #999;
	}

	.recent-orders-empty {
		text-align: center;
		padding: 20px;
		color: #999;
		background: white;
		border-radius: 8px;
	}

	.view-all-orders {
		display: inline-block;
		color: #e74c3c;
		text-decoration: none;
		font-size: 14px;
		font-weight: 500;
		transition: all 0.3s;
	}

	.view-all-orders:hover {
		transform: translateX(5px);
	}

	.checkout-form-compact {
		margin-top: 30px;
		padding: 30px;
		background: white;
		border-radius: 12px;
		box-shadow: 0 5px 15px rgba(0,0,0,0.08);
	}

	.checkout-form-compact h2 {
		margin: 0 0 20px;
		font-size: 20px;
		color: #333;
		border-bottom: 1px solid #eee;
		padding-bottom: 15px;
	}

	.checkout-form-compact h3 {
		margin: 20px 0 15px;
		font-size: 16px;
		color: #666;
	}

	.checkout-form-compact .form-row {
		display: grid;
		grid-template-columns: 1fr 1fr;
		gap: 15px;
		margin-bottom: 15px;
	}

	.checkout-form-compact .form-group {
		margin-bottom: 15px;
	}

	.checkout-form-compact label {
		display: block;
		margin-bottom: 5px;
		color: #666;
		font-size: 14px;
	}

	.checkout-form-compact input,
	.checkout-form-compact textarea,
	.checkout-form-compact select {
		width: 100%;
		padding: 10px 12px;
		border: 2px solid #e0e0e0;
		border-radius: 8px;
		font-size: 14px;
		transition: all 0.3s;
	}

	.checkout-form-compact input:focus,
	.checkout-form-compact textarea:focus {
		border-color: #e74c3c;
		outline: none;
	}

	.checkout-form-compact input.error {
		border-color: #e74c3c;
		background: #fff3f3;
	}

	/* Блоки доставки и оплаты */
	.delivery-options-compact,
	.payment-options-compact {
		display: flex;
		gap: 20px;
		margin-bottom: 20px;
	}

	.delivery-option-compact,
	.payment-option-compact {
		flex: 1;
		display: flex;
		align-items: center;
		gap: 10px;
		padding: 15px;
		border: 2px solid #e0e0e0;
		border-radius: 8px;
		cursor: pointer;
		transition: all 0.3s;
	}

	.delivery-option-compact:hover,
	.payment-option-compact:hover {
		border-color: #e74c3c;
	}

	.delivery-option-compact.selected,
	.payment-option-compact.selected {
		border-color: #e74c3c;
		background: #fff3f0;
	}

	.delivery-option-compact input[type="radio"],
	.payment-option-compact input[type="radio"] {
		width: 16px;
		height: 16px;
		margin: 0;
	}

	.delivery-info-compact {
		flex: 1;
	}

	.delivery-name-compact {
		font-weight: 500;
		color: #333;
		margin-bottom: 2px;
	}

	.delivery-price-compact {
		color: #e74c3c;
		font-size: 14px;
	}

	.delivery-desc-compact {
		font-size: 12px;
		color: #999;
	}

	/* Кнопка оформления */
	.checkout-button {
		width: 100%;
		padding: 18px;
		background: linear-gradient(135deg, #eabb66, #e74c3c);
		color: white;
		border: none;
		border-radius: 8px;
		font-size: 18px;
		font-weight: 500;
		cursor: pointer;
		transition: all 0.3s;
		margin-top: 20px;
	}

	.checkout-button:hover {
		transform: translateY(-2px);
		box-shadow: 0 5px 15px rgba(231, 76, 60, 0.3);
	}

	.checkout-button:disabled {
		opacity: 0.5;
		cursor: not-allowed;
	}

	/* Подсказка для незаполненных полей */
	.field-hint {
		font-size: 12px;
		color: #e74c3c;
		margin-top: 3px;
		display: none;
	}

	.field-hint.show {
		display: block;
	}

	/* Адаптивность */
	@media (max-width: 768px) {
		.delivery-options-compact,
		.payment-options-compact {
			flex-direction: column;
		}
		
		.checkout-form-compact .form-row {
			grid-template-columns: 1fr;
		}
	}

	.cart-table {
		width: 100%;
		border-collapse: collapse;
		background: white;
		border-radius: 12px;
		overflow: hidden;
		box-shadow: 0 5px 15px rgba(0,0,0,0.08);
	}

	.cart-table th {
		background: #f8f9fa;
		padding: 15px;
		text-align: left;
		font-weight: 500;
		color: #666;
		border-bottom: 2px solid #eee;
	}

	.cart-table td {
		padding: 15px;
		border-bottom: 1px solid #eee;
		vertical-align: middle;
	}

	.cart-table tr:last-child td {
		border-bottom: none;
	}

	.cart-product {
		display: flex;
		align-items: center;
		gap: 15px;
	}

	.cart-product-image {
		width: 80px;
		height: 80px;
		border-radius: 8px;
		overflow: hidden;
	}

	.cart-product-image img {
		width: 100%;
		height: 100%;
		object-fit: cover;
	}

	.cart-product-name {
		font-weight: 500;
		color: #333;
		text-decoration: none;
	}

	.cart-product-name:hover {
		color: #e74c3c;
	}

	.cart-price {
		font-weight: 500;
		color: #e74c3c;
	}

	/* Стили для блока количества */
	.cart-quantity-block {
		display: flex;
		align-items: center;
		gap: 5px;
	}

	.cart-quantity-btn {
		width: 32px;
		height: 32px;
		border: 1px solid #ddd;
		background: white;
		border-radius: 6px;
		cursor: pointer;
		font-size: 18px;
		font-weight: bold;
		color: #666;
		transition: all 0.3s;
		display: flex;
		align-items: center;
		justify-content: center;
	}

	.cart-quantity-btn:hover:not(:disabled) {
		background: #f8f9fa;
		border-color: #e74c3c;
		color: #e74c3c;
	}

	.cart-quantity-btn:disabled {
		opacity: 0.5;
		cursor: not-allowed;
	}

	.cart-quantity-input {
		width: 60px;
		height: 32px;
		border: 1px solid #ddd;
		border-radius: 6px;
		text-align: center;
		font-size: 14px;
	}

	.cart-quantity-input::-webkit-inner-spin-button,
	.cart-quantity-input::-webkit-outer-spin-button {
		opacity: 1;
		height: 24px;
	}

	.btn-remove {
		background: none;
		border: none;
		color: #999;
		cursor: pointer;
		font-size: 20px;
		padding: 5px 10px;
		transition: all 0.3s;
	}

	.btn-remove:hover {
		color: #e74c3c;
	}

	.cart-summary {
		margin-top: 30px;
		padding: 20px;
		background: white;
		border-radius: 12px;
		box-shadow: 0 5px 15px rgba(0,0,0,0.08);
		text-align: right;
	}

	.cart-total {
		font-size: 24px;
		font-weight: bold;
		color: #333;
		margin-bottom: 20px;
	}

	.cart-total span {
		color: #e74c3c;
		margin-left: 15px;
	}

	.cart-actions {
		display: flex;
		gap: 15px;
		justify-content: flex-end;
	}

	.btn-checkout {
		padding: 15px 40px;
		background: linear-gradient(135deg, #eabb66, #e74c3c);
		color: white;
		border: none;
		border-radius: 8px;
		font-size: 16px;
		font-weight: 500;
		cursor: pointer;
		transition: all 0.3s;
	}

	.btn-checkout:hover {
		transform: translateY(-2px);
		box-shadow: 0 5px 15px rgba(231, 76, 60, 0.3);
	}

	.btn-clear {
		padding: 15px 30px;
		background: white;
		color: #666;
		border: 1px solid #ddd;
		border-radius: 8px;
		font-size: 16px;
		cursor: pointer;
		transition: all 0.3s;
	}

	.btn-clear:hover {
		background: #f8f9fa;
		color: #e74c3c;
		border-color: #e74c3c;
	}

	.cart-empty {
		text-align: center;
		padding: 60px 20px;
		background: white;
		border-radius: 12px;
		box-shadow: 0 5px 15px rgba(0,0,0,0.08);
	}

	.cart-empty p {
		font-size: 18px;
		color: #666;
		margin-bottom: 20px;
	}

	.btn-catalog {
		display: inline-block;
		padding: 15px 40px;
		background: linear-gradient(135deg, #eabb66, #e74c3c);
		color: white;
		text-decoration: none;
		border-radius: 8px;
		font-weight: 500;
		transition: all 0.3s;
	}

	.btn-catalog:hover {
		transform: translateY(-2px);
		box-shadow: 0 5px 15px rgba(231, 76, 60, 0.3);
	}

	.cart-loading {
		text-align: center;
		padding: 60px;
		color: #999;
	}

	/* Анимация обновления */
	@keyframes update-pulse {
		0% { background-color: #fff; }
		50% { background-color: #fff3e0; }
		100% { background-color: #fff; }
	}

	.cart-updating {
		animation: update-pulse 0.5s ease;
	}
</style>

<script>
	document.addEventListener('DOMContentLoaded', function() {
		loadCart();
	});

	function loadCart() {
		fetch('/ajax/add_to_cart.php?action=get_full&t=' + Date.now())
			.then(response => response.json())
			.then(data => {
				if (data.success) {
					renderCart(data);
				} else {
					document.getElementById('cart-content').innerHTML = '<div class="alert alert-danger">Ошибка загрузки корзины</div>';
				}
			})
			.catch(error => {
				console.error('Ошибка:', error);
				document.getElementById('cart-content').innerHTML = '<div class="alert alert-danger">Ошибка загрузки корзины</div>';
			});
	}

	// ЕДИНСТВЕННАЯ функция renderCart (с формой оформления)
	function renderCart(data) {
		const cartContent = document.getElementById('cart-content');
		
		if (data.items.length === 0) {
			cartContent.innerHTML = `
				<div class="cart-empty">
					<p>Ваша корзина пуста</p>
					<a href="/catalog/" class="btn-catalog">Перейти в каталог</a>
				</div>
			`;
			return;
		}
		
		let html = `
			<table class="cart-table">
				<thead>
					<tr>
						<th>Товар</th>
						<th>Цена</th>
						<th>Количество</th>
						<th>Сумма</th>
						<th></th>
					</tr>
				</thead>
				<tbody>
		`;
		
		data.items.forEach(item => {
			const price = parseInt(item.PRICE) || 0;
			const sum = price * item.QUANTITY;
			
			html += `
				<tr data-product-id="${item.PRODUCT_ID}" data-price="${price}" class="cart-row">
					<td>
						<div class="cart-product">
							<div class="cart-product-image">
								<img src="${item.IMAGE || '/upload/no-image.jpg'}" alt="${item.NAME}">
							</div>
							<a href="/catalog/detail.php?ID=${item.PRODUCT_ID}" class="cart-product-name">${item.NAME}</a>
						</div>
					</td>
					<td class="cart-price price-${item.PRODUCT_ID}">${formatPrice(price)}</td>
					<td>
						<div class="cart-quantity-block">
							<button class="cart-quantity-btn" onclick="decrementQuantity(${item.PRODUCT_ID}, this)" 
									${item.QUANTITY <= 1 ? 'disabled' : ''}>−</button>
							<input type="number" class="cart-quantity-input" value="${item.QUANTITY}" min="1" 
								onchange="updateCartItem(${item.PRODUCT_ID}, this.value)"
								onkeyup="if(this.value) updateCartItem(${item.PRODUCT_ID}, this.value)"
								id="qty_${item.PRODUCT_ID}">
							<button class="cart-quantity-btn" onclick="incrementQuantity(${item.PRODUCT_ID}, this)">+</button>
						</div>
					</td>
					<td class="cart-price sum-${item.PRODUCT_ID}">${formatPrice(sum)}</td>
					<td>
						<button class="btn-remove" onclick="removeFromCart(${item.PRODUCT_ID})">×</button>
					</td>
				</tr>
			`;
		});
		
		html += `
				</tbody>
			</table>
			
			<div class="cart-summary">
				<div class="cart-total">
					Итого: <span id="cart-total">${formatPrice(data.total)}</span>
				</div>
			</div>
			
			<!-- Форма оформления заказа -->
			<div class="checkout-form-compact">
				<h2>Оформление заказа</h2>
				
				<form id="orderForm" onsubmit="submitOrder(event)">
					<!-- Контактные данные -->
					<div class="form-row">
						<div class="form-group">
							<label for="lastName">Фамилия</label>
							<input type="text" id="lastName" name="lastName" placeholder="Ваша фамилия">
							<div class="field-hint" id="lastNameHint">Укажите фамилию</div>
						</div>
						<div class="form-group">
							<label for="firstName">Имя</label>
							<input type="text" id="firstName" name="firstName" placeholder="Ваше имя">
							<div class="field-hint" id="firstNameHint">Укажите имя</div>
						</div>
					</div>
					
					<div class="form-row">
						<div class="form-group">
							<label for="phone">Телефон</label>
							<input type="tel" id="phone" name="phone" placeholder="+7 (___) ___-__-__">
							<div class="field-hint" id="phoneHint">Укажите телефон для связи</div>
						</div>
						<div class="form-group">
							<label for="email">Email</label>
							<input type="email" id="email" name="email" placeholder="your@email.ru">
							<div class="field-hint" id="emailHint">Укажите email для уведомлений</div>
						</div>
					</div>
					
					<h3>Способ доставки</h3>
					<div class="delivery-options-compact">
						<label class="delivery-option-compact" onclick="selectDelivery('pickup')">
							<input type="radio" name="delivery" value="pickup" checked onchange="updateOrderTotal()">
							<div class="delivery-info-compact">
								<div class="delivery-name-compact">Самовывоз</div>
								<div class="delivery-desc-compact">г. Великий Новгород, ул. Большая Московская, 8</div>
							</div>
							<div class="delivery-price-compact">Бесплатно</div>
						</label>
						
						<label class="delivery-option-compact" onclick="selectDelivery('delivery')">
							<input type="radio" name="delivery" value="delivery" onchange="updateOrderTotal()">
							<div class="delivery-info-compact">
								<div class="delivery-name-compact">Доставка курьером</div>
								<div class="delivery-desc-compact">По Великому Новгороду</div>
							</div>
							<div class="delivery-price-compact">+500 ₽</div>
						</label>
					</div>
					
					<div class="form-group" id="addressGroup" style="display: none;">
						<label for="address">Адрес доставки</label>
						<textarea id="address" name="address" rows="2" placeholder="Улица, дом, квартира"></textarea>
						<div class="field-hint" id="addressHint">Укажите адрес доставки</div>
					</div>
					
					<h3>Способ оплаты</h3>
					<div class="payment-options-compact">
						<label class="payment-option-compact">
							<input type="radio" name="payment" value="cash" checked>
							<div class="payment-name">Наличными при получении</div>
						</label>
						
						<label class="payment-option-compact">
							<input type="radio" name="payment" value="card">
							<div class="payment-name">Картой онлайн</div>
						</label>
					</div>
					
					<div class="form-group">
						<label for="comment">Комментарий к заказу</label>
						<textarea id="comment" name="comment" rows="2" placeholder="Дополнительная информация..."></textarea>
					</div>
					
					<button type="submit" class="checkout-button" id="checkoutBtn">
						Оформить заказ · <span id="finalTotal">${formatPrice(data.total)}</span>
					</button>
				</form>
			</div>

			<!-- Последние заказы -->
			<div class="recent-orders">
				<h3>Ваши последние заказы</h3>
				<div id="recent-orders-list" class="recent-orders-list">
					<div class="recent-orders-loading">Загрузка...</div>
				</div>
				<a href="/personal/orders/" class="view-all-orders">Все заказы →</a>
			</div>
		`;
		
		cartContent.innerHTML = html;
		
		// Загружаем данные пользователя, если авторизован
		loadUserData();
		
		// Инициализация маски телефона
		initPhoneMask();

		// Загружаем последнии 3 заказа
		loadRecentOrders();
	}

	// Функция для увеличения количества
	function incrementQuantity(productId, btn) {
		const input = document.getElementById(`qty_${productId}`);
		const newValue = parseInt(input.value) + 1;
		input.value = newValue;
		updateCartItem(productId, newValue);
	}

	// Функция для уменьшения количества
	function decrementQuantity(productId, btn) {
		const input = document.getElementById(`qty_${productId}`);
		const currentValue = parseInt(input.value);
		
		if (currentValue > 1) {
			const newValue = currentValue - 1;
			input.value = newValue;
			updateCartItem(productId, newValue);
		}
	}

	function formatPrice(price) {
		if (!price) return '0 ₽';
		return new Intl.NumberFormat('ru-RU').format(price) + ' ₽';
	}

	function updateCartItem(productId, quantity) {
		const row = document.querySelector(`tr[data-product-id="${productId}"]`);
		if (row) {
			row.classList.add('cart-updating');
			const buttons = row.querySelectorAll('button');
			buttons.forEach(btn => btn.disabled = true);
			const input = row.querySelector('input');
			if (input) input.disabled = true;
		}
		
		fetch('/ajax/add_to_cart.php', {
			method: 'POST',
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded',
			},
			body: 'action=update&product_id=' + productId + '&quantity=' + quantity
		})
		.then(response => response.json())
		.then(data => {
			if (data.success) {
				const price = parseInt(row.dataset.price);
				
				const sumElement = document.querySelector(`.sum-${productId}`);
				if (sumElement) {
					sumElement.textContent = formatPrice(price * quantity);
				}
				
				recalculateTotal();
				
				if (window.updateCartCounter) {
					window.updateCartCounter();
				}
			}
		})
		.catch(error => {
			console.error('Ошибка при обновлении:', error);
			showNotification('Ошибка при обновлении количества', 'error');
		})
		.finally(() => {
			if (row) {
				row.classList.remove('cart-updating');
				const buttons = row.querySelectorAll('button');
				buttons.forEach(btn => btn.disabled = false);
				const input = row.querySelector('input');
				if (input) input.disabled = false;
				
				const decrementBtn = row.querySelector('.cart-quantity-btn:first-child');
				if (decrementBtn && quantity <= 1) {
					decrementBtn.disabled = true;
				}
			}
		});
	}

	function recalculateTotal() {
		let total = 0;
		
		document.querySelectorAll('tr[data-product-id]').forEach(row => {
			const productId = row.dataset.productId;
			const sumElement = document.querySelector(`.sum-${productId}`);
			if (sumElement) {
				const sumText = sumElement.textContent;
				const sum = parseInt(sumText.replace(/[^\d]/g, '')) || 0;
				total += sum;
			}
		});
		
		const totalElement = document.getElementById('cart-total');
		if (totalElement) {
			totalElement.textContent = formatPrice(total);
		}
	}

	function removeFromCart(productId) {
		if (!confirm('Удалить товар из корзины?')) return;
		
		fetch('/ajax/add_to_cart.php', {
			method: 'POST',
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded',
			},
			body: 'action=remove&product_id=' + productId
		})
		.then(response => response.json())
		.then(data => {
			if (data.success) {
				loadCart();
				if (window.updateCartCounter) {
					window.updateCartCounter();
				}
			}
		});
	}

	function clearCart() {
		if (!confirm('Очистить корзину?')) return;
		
		fetch('/ajax/add_to_cart.php', {
			method: 'POST',
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded',
			},
			body: 'action=clear'
		})
		.then(response => response.json())
		.then(data => {
			if (data.success) {
				loadCart();
				if (window.updateCartCounter) {
					window.updateCartCounter();
				}
			}
		});
	}

	function showNotification(message, type = 'success') {
		const notification = document.createElement('div');
		notification.className = `cart-notification cart-notification--${type}`;
		notification.textContent = message;
		notification.style.cssText = `
			position: fixed;
			top: 20px;
			right: 20px;
			background: ${type === 'success' ? '#27ae60' : '#e74c3c'};
			color: white;
			padding: 15px 25px;
			border-radius: 8px;
			z-index: 10000;
			box-shadow: 0 4px 15px rgba(0,0,0,0.2);
			animation: slideIn 0.3s ease;
		`;
		
		document.body.appendChild(notification);
		
		setTimeout(() => {
			notification.remove();
		}, 3000);
	}

	function checkout() {
		window.location.href = '/personal/order/';
	}

	// Функции для оформления заказа
	function loadUserData() {
		fetch('/ajax/get_user_data.php')
			.then(response => response.json())
			.then(data => {
				if (data.success && data.user) {
					if (data.user.NAME) {
						const nameParts = data.user.NAME.split(' ');
						if (nameParts.length >= 2) {
							document.getElementById('firstName').value = nameParts[1] || '';
							document.getElementById('lastName').value = nameParts[0] || '';
						} else {
							document.getElementById('firstName').value = data.user.NAME;
						}
					}
					
					if (data.user.EMAIL) {
						document.getElementById('email').value = data.user.EMAIL;
					}
					
					if (data.user.PERSONAL_PHONE) {
						const phone = data.user.PERSONAL_PHONE;
						let formatted = '+7';
						if (phone.length > 1) {
							formatted += ' (' + phone.substring(0, 3);
						}
						if (phone.length >= 3) {
							formatted += ') ' + phone.substring(3, 6);
						}
						if (phone.length >= 6) {
							formatted += '-' + phone.substring(6, 8);
						}
						if (phone.length >= 8) {
							formatted += '-' + phone.substring(8, 10);
						}
						document.getElementById('phone').value = formatted;
					}
					
					if (data.user.PERSONAL_STREET) {
						document.getElementById('address').value = data.user.PERSONAL_STREET;
					}
				}
			})
			.catch(error => console.error('Ошибка загрузки данных пользователя:', error));
	}

	function initPhoneMask() {
		const phoneInput = document.getElementById('phone');
		if (!phoneInput) return;
		
		phoneInput.addEventListener('input', function(e) {
			let value = e.target.value.replace(/\D/g, '');
			
			if (value.length > 0) {
				let formatted = '+7';
				if (value.length > 1) {
					formatted += ' (' + value.substring(1, 4);
				}
				if (value.length >= 4) {
					formatted += ') ' + value.substring(4, 7);
				}
				if (value.length >= 7) {
					formatted += '-' + value.substring(7, 9);
				}
				if (value.length >= 9) {
					formatted += '-' + value.substring(9, 11);
				}
				e.target.value = formatted;
			}
		});
	}

	function selectDelivery(type) {
		const addressGroup = document.getElementById('addressGroup');
		if (type === 'delivery') {
			addressGroup.style.display = 'block';
		} else {
			addressGroup.style.display = 'none';
		}
	}

	function updateOrderTotal() {
		const totalElement = document.getElementById('cart-total');
		const finalTotalElement = document.getElementById('finalTotal');
		const deliverySelected = document.querySelector('input[name="delivery"]:checked').value;
		
		let total = parseFloat(totalElement.textContent.replace(/[^\d]/g, '')) || 0;
		
		if (deliverySelected === 'delivery') {
			total += 500;
		}
		
		finalTotalElement.textContent = formatPrice(total);
	}

	function submitOrder(event) {
		event.preventDefault();
		
		const lastName = document.getElementById('lastName').value.trim();
		const firstName = document.getElementById('firstName').value.trim();
		const phone = document.getElementById('phone').value.trim();
		const email = document.getElementById('email').value.trim();
		const delivery = document.querySelector('input[name="delivery"]:checked').value;
		const payment = document.querySelector('input[name="payment"]:checked').value;
		const comment = document.getElementById('comment').value.trim();
		
		let hasErrors = false;
		
		// Валидация...
		if (!lastName) {
			document.getElementById('lastNameHint').classList.add('show');
			document.getElementById('lastName').classList.add('error');
			hasErrors = true;
		} else {
			document.getElementById('lastNameHint').classList.remove('show');
			document.getElementById('lastName').classList.remove('error');
		}
		
		if (!firstName) {
			document.getElementById('firstNameHint').classList.add('show');
			document.getElementById('firstName').classList.add('error');
			hasErrors = true;
		} else {
			document.getElementById('firstNameHint').classList.remove('show');
			document.getElementById('firstName').classList.remove('error');
		}
		
		if (!phone) {
			document.getElementById('phoneHint').classList.add('show');
			document.getElementById('phone').classList.add('error');
			hasErrors = true;
		} else {
			const cleanPhone = phone.replace(/\D/g, '');
			if (cleanPhone.length < 10) {
				document.getElementById('phoneHint').textContent = 'Некорректный номер телефона';
				document.getElementById('phoneHint').classList.add('show');
				document.getElementById('phone').classList.add('error');
				hasErrors = true;
			} else {
				document.getElementById('phoneHint').classList.remove('show');
				document.getElementById('phone').classList.remove('error');
			}
		}
		
		if (!email) {
			document.getElementById('emailHint').classList.add('show');
			document.getElementById('email').classList.add('error');
			hasErrors = true;
		} else {
			const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
			if (!emailRegex.test(email)) {
				document.getElementById('emailHint').textContent = 'Некорректный email';
				document.getElementById('emailHint').classList.add('show');
				document.getElementById('email').classList.add('error');
				hasErrors = true;
			} else {
				document.getElementById('emailHint').classList.remove('show');
				document.getElementById('email').classList.remove('error');
			}
		}
		
		if (delivery === 'delivery') {
			const address = document.getElementById('address').value.trim();
			if (!address) {
				document.getElementById('addressHint').classList.add('show');
				document.getElementById('address').classList.add('error');
				hasErrors = true;
			} else {
				document.getElementById('addressHint').classList.remove('show');
				document.getElementById('address').classList.remove('error');
			}
		}
		
		if (hasErrors) {
			showNotification('Заполните все обязательные поля', 'error');
			return;
		}
		
		const checkoutBtn = document.getElementById('checkoutBtn');
		const originalText = checkoutBtn.innerHTML;
		checkoutBtn.innerHTML = 'Оформление...';
		checkoutBtn.disabled = true;
		
		const cleanPhone = phone.replace(/\D/g, '');
		
		fetch('/ajax/create_order.php', {
			method: 'POST',
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded',
			},
			body: new URLSearchParams({
				action: 'create',
				lastName: lastName,
				firstName: firstName,
				phone: cleanPhone,
				email: email,
				delivery: delivery,
				address: delivery === 'delivery' ? document.getElementById('address').value.trim() : '',
				payment: payment,
				comment: comment
			})
		})
		.then(response => response.json())
		.then(data => {
			if (data.success) {
				fetch('/ajax/add_to_cart.php?action=clear', { method: 'POST' })
					.then(() => {
						window.location.href = '/personal/order/success.php?order_id=' + data.order_id;
					});
			} else {
				showNotification(data.error || 'Ошибка оформления заказа', 'error');
				checkoutBtn.innerHTML = originalText;
				checkoutBtn.disabled = false;
			}
		})
		.catch(error => {
			console.error('Ошибка:', error);
			showNotification('Ошибка оформления заказа', 'error');
			checkoutBtn.innerHTML = originalText;
			checkoutBtn.disabled = false;
		});
	}

	function loadRecentOrders() {
		const recentOrdersList = document.getElementById('recent-orders-list');
		if (!recentOrdersList) return;
		
		fetch('/ajax/get_recent_orders.php')
			.then(response => response.json())
			.then(data => {
				if (data.success && data.orders.length > 0) {
					renderRecentOrders(data.orders);
				} else {
					recentOrdersList.innerHTML = `
						<div class="recent-orders-empty">
							У вас пока нет заказов
						</div>
					`;
				}
			})
			.catch(error => {
				console.error('Ошибка загрузки заказов:', error);
				recentOrdersList.innerHTML = `
					<div class="recent-orders-empty">
						Не удалось загрузить заказы
					</div>
				`;
			});
	}

	function renderRecentOrders(orders) {
		const recentOrdersList = document.getElementById('recent-orders-list');
		
		let html = '';
		orders.forEach(order => {
			const date = new Date(order.CREATED_AT).toLocaleDateString('ru-RU', {
				day: 'numeric',
				month: 'short'
			});
			
			const statusClass = getStatusClass(order.STATUS);
			const statusText = getStatusText(order.STATUS);
			
			html += `
				<div class="recent-order-item">
					<div class="recent-order-info">
						<div class="recent-order-number">${order.ORDER_NUMBER}</div>
						<div class="recent-order-date">${date}</div>
					</div>
					<span class="order-status ${statusClass} recent-order-status">${statusText}</span>
					<div class="recent-order-total">${formatPrice(order.TOTAL_PRICE)}</div>
					<a href="/personal/orders/?id=${order.ID}" class="recent-order-link">→</a>
				</div>
			`;
		});
		
		recentOrdersList.innerHTML = html;
	}

	function getStatusClass(status) {
		const classes = {
			'new': 'status-new',
			'processing': 'status-processing',
			'delivered': 'status-delivered',
			'cancelled': 'status-cancelled'
		};
		return classes[status] || 'status-new';
	}

	function getStatusText(status) {
		const texts = {
			'new': 'Новый',
			'processing': 'В обработке',
			'delivered': 'Доставлен',
			'cancelled': 'Отменён'
		};
		return texts[status] || 'Новый';
	}
</script>

<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
?>