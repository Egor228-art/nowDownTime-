<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Мои заказы");
?>

<div class="container">
    <h1>Мои заказы</h1>
    <div id="orders-content">
        <div class="orders-loading">Загрузка заказов...</div>
    </div>
</div>

<style>
.orders-loading {
    text-align: center;
    padding: 60px;
    color: #999;
}

.orders-empty {
    text-align: center;
    padding: 60px 20px;
    background: white;
    border-radius: 12px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.08);
}

.orders-empty p {
    font-size: 18px;
    color: #666;
    margin-bottom: 20px;
}

.orders-list {
    margin: 30px 0;
}

.order-item {
    background: white;
    border-radius: 12px;
    margin-bottom: 15px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.08);
    overflow: hidden;
    transition: all 0.3s;
    border: 1px solid #eee;
}

.order-item:hover {
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    border-color: #e74c3c;
}

.order-header {
    display: grid;
    grid-template-columns: 100px 150px 120px 120px 150px 30px;
    gap: 10px;
    padding: 15px 20px;
    cursor: pointer;
    align-items: center;
    background: white;
    transition: background 0.3s;
}

.order-header:hover {
    background: #f8f9fa;
}

.order-header.expanded {
    background: #f8f9fa;
    border-bottom: 2px solid #e74c3c;
}

.order-number {
    font-weight: 500;
    color: #333;
}

.order-date {
    color: #666;
    font-size: 14px;
}

.order-status {
    display: inline-block;
    padding: 5px 10px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 500;
    text-align: center;
}

.status-new {
    background: #e3f2fd;
    color: #1976d2;
}

.status-processing {
    background: #fff3e0;
    color: #f57c00;
}

.status-delivered {
    background: #e8f5e8;
    color: #388e3c;
}

.status-cancelled {
    background: #ffebee;
    color: #d32f2f;
}

.order-total {
    font-weight: 500;
    color: #e74c3c;
    text-align: right;
}

.order-delivery {
    color: #666;
    font-size: 13px;
}

.expand-icon {
    font-size: 18px;
    color: #999;
    transition: transform 0.3s;
    text-align: center;
}

.order-header.expanded .expand-icon {
    transform: rotate(180deg);
    color: #e74c3c;
}

/* Детали заказа */
.order-details {
    display: none;
    padding: 20px;
    background: #f8f9fa;
    border-top: 1px solid #e0e0e0;
}

.order-details.show {
    display: block;
}

.details-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
    margin-bottom: 25px;
}

.detail-card {
    background: white;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

.detail-card h4 {
    margin: 0 0 15px;
    font-size: 16px;
    color: #333;
    border-bottom: 1px solid #eee;
    padding-bottom: 10px;
}

.detail-row {
    display: flex;
    margin-bottom: 10px;
    font-size: 14px;
}

.detail-label {
    width: 120px;
    color: #666;
}

.detail-value {
    flex: 1;
    color: #333;
    font-weight: 500;
}

.products-table {
    width: 100%;
    border-collapse: collapse;
    background: white;
    border-radius: 8px;
    overflow: hidden;
}

.products-table th {
    background: #f8f9fa;
    padding: 12px;
    text-align: left;
    font-weight: 500;
    color: #666;
    font-size: 13px;
}

.products-table td {
    padding: 12px;
    border-bottom: 1px solid #eee;
}

.products-table tr:last-child td {
    border-bottom: none;
}

.product-name {
    color: #333;
    font-weight: 500;
}

.product-price, .product-sum {
    color: #e74c3c;
    font-weight: 500;
}

.delivery-summary {
    margin-top: 20px;
    padding-top: 20px;
    border-top: 2px solid #eee;
    text-align: right;
    font-size: 18px;
}

.delivery-summary span {
    color: #e74c3c;
    font-weight: bold;
    margin-left: 15px;
}

@media (max-width: 768px) {
    .order-header {
        grid-template-columns: 1fr;
        gap: 5px;
    }
    
    .expand-icon {
        position: absolute;
        top: 15px;
        right: 15px;
    }
    
    .order-item {
        position: relative;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    loadOrders();
});

function loadOrders() {
    fetch('/ajax/get_orders.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                renderOrders(data.orders);
            } else {
                document.getElementById('orders-content').innerHTML = '<div class="alert alert-danger">Ошибка загрузки заказов</div>';
            }
        })
        .catch(error => {
            console.error('Ошибка:', error);
            document.getElementById('orders-content').innerHTML = '<div class="alert alert-danger">Ошибка загрузки заказов</div>';
        });
}

function renderOrders(orders) {
    const content = document.getElementById('orders-content');
    
    if (!orders || orders.length === 0) {
        content.innerHTML = `
            <div class="orders-empty">
                <p>У вас пока нет заказов</p>
                <a href="/catalog/" class="btn-catalog">Перейти в каталог</a>
            </div>
        `;
        return;
    }
    
    let html = '<div class="orders-list">';
    
    orders.forEach(order => {
        const date = new Date(order.CREATED_AT).toLocaleDateString('ru-RU');
        const statusClass = getStatusClass(order.STATUS);
        const statusText = getStatusText(order.STATUS);
        
        html += `
            <div class="order-item" data-order-id="${order.ID}">
                <div class="order-header" onclick="toggleOrder(${order.ID})">
                    <div class="order-number">${order.ORDER_NUMBER}</div>
                    <div class="order-date">${date}</div>
                    <div><span class="order-status ${statusClass}">${statusText}</span></div>
                    <div class="order-total">${formatPrice(order.TOTAL_PRICE)}</div>
                    <div class="order-delivery">${order.DELIVERY_ADDRESS ? 'Доставка' : 'Самовывоз'}</div>
                    <div class="expand-icon">▼</div>
                </div>
                <div class="order-details" id="order-details-${order.ID}">
                    <div class="details-loading">Загрузка деталей...</div>
                </div>
            </div>
        `;
    });
    
    html += '</div>';
    content.innerHTML = html;
}

function toggleOrder(orderId) {
    const details = document.getElementById(`order-details-${orderId}`);
    const header = details.previousElementSibling;
    
    if (details.classList.contains('show')) {
        details.classList.remove('show');
        header.classList.remove('expanded');
    } else {
        // Закрываем все другие открытые заказы
        document.querySelectorAll('.order-details.show').forEach(el => {
            el.classList.remove('show');
            el.previousElementSibling.classList.remove('expanded');
        });
        
        details.classList.add('show');
        header.classList.add('expanded');
        
        // Если детали еще не загружены - загружаем
        if (details.innerHTML.includes('Загрузка деталей')) {
            loadOrderDetails(orderId);
        }
    }
}

function loadOrderDetails(orderId) {
    fetch('/ajax/get_order_detail.php?id=' + orderId)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                renderOrderDetails(orderId, data.order, data.items);
            }
        })
        .catch(error => {
            console.error('Ошибка загрузки деталей:', error);
            document.getElementById(`order-details-${orderId}`).innerHTML = '<div class="alert alert-danger">Ошибка загрузки</div>';
        });
}

function renderOrderDetails(orderId, order, items) {
    const detailsDiv = document.getElementById(`order-details-${orderId}`);
    
    const date = new Date(order.CREATED_AT).toLocaleDateString('ru-RU', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
    
    let itemsHtml = '';
    let subtotal = 0;
    
    items.forEach(item => {
        const sum = item.PRICE * item.QUANTITY;
        subtotal += sum;
        
        itemsHtml += `
            <tr>
                <td class="product-name">${item.PRODUCT_NAME}</td>
                <td class="product-price">${formatPrice(item.PRICE)}</td>
                <td>${item.QUANTITY}</td>
                <td class="product-sum">${formatPrice(sum)}</td>
            </tr>
        `;
    });
    
    const deliveryPrice = parseFloat(order.DELIVERY_PRICE) || 0;
    const total = subtotal + deliveryPrice;
    
    const html = `
        <div class="details-grid">
            <div class="detail-card">
                <h4>Информация о заказе</h4>
                <div class="detail-row">
                    <span class="detail-label">Дата:</span>
                    <span class="detail-value">${date}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Статус:</span>
                    <span class="detail-value"><span class="order-status ${getStatusClass(order.STATUS)}">${getStatusText(order.STATUS)}</span></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Способ оплаты:</span>
                    <span class="detail-value">${order.PAYMENT_METHOD === 'card' ? 'Картой онлайн' : 'Наличными при получении'}</span>
                </div>
            </div>
            
            <div class="detail-card">
                <h4>Доставка</h4>
                <div class="detail-row">
                    <span class="detail-label">Способ:</span>
                    <span class="detail-value">${order.DELIVERY_ADDRESS ? 'Доставка курьером' : 'Самовывоз'}</span>
                </div>
                ${order.DELIVERY_ADDRESS ? `
                <div class="detail-row">
                    <span class="detail-label">Адрес:</span>
                    <span class="detail-value">${order.DELIVERY_ADDRESS}</span>
                </div>
                ` : ''}
                <div class="detail-row">
                    <span class="detail-label">Стоимость:</span>
                    <span class="detail-value">${deliveryPrice > 0 ? formatPrice(deliveryPrice) : 'Бесплатно'}</span>
                </div>
            </div>
        </div>
        
        <h4 style="margin: 20px 0 10px;">Состав заказа</h4>
        <table class="products-table">
            <thead>
                <tr>
                    <th>Товар</th>
                    <th>Цена</th>
                    <th>Кол-во</th>
                    <th>Сумма</th>
                </tr>
            </thead>
            <tbody>
                ${itemsHtml}
            </tbody>
        </table>
        
        <div class="delivery-summary">
            Подытог: ${formatPrice(subtotal)}<br>
            ${deliveryPrice > 0 ? `Доставка: +${formatPrice(deliveryPrice)}<br>` : ''}
            <span>Итого: ${formatPrice(total)}</span>
        </div>
        
        ${order.COMMENT ? `
        <div style="margin-top: 20px; padding: 15px; background: #fff3e0; border-radius: 8px;">
            <strong>Комментарий:</strong> ${order.COMMENT}
        </div>
        ` : ''}
    `;
    
    detailsDiv.innerHTML = html;
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

function formatPrice(price) {
    return new Intl.NumberFormat('ru-RU').format(price) + ' ₽';
}
</script>

<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
?>