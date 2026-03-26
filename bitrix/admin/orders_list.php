<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

// Проверка прав доступа
global $USER;
if (!$USER->IsAdmin()) {
    $APPLICATION->AuthForm("Доступ запрещен");
}

// Подключение к БД
global $DB;

// Обработка изменения статуса
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $orderId = intval($_POST['order_id']);
    $status = $_POST['status'];
    
    $validStatuses = ['new', 'processing', 'delivered', 'cancelled'];
    if (in_array($status, $validStatuses)) {
        $DB->Query("UPDATE orders SET STATUS = '" . $DB->ForSql($status) . "' WHERE ID = " . $orderId);
    }
}

// Получение списка заказов
$filter = $_GET['filter'] ?? 'all';
$sql = "SELECT o.*, 
        SUBSTRING_INDEX(SUBSTRING_INDEX(o.COMMENT, 'Имя: ', -1), '\n', 1) as FIRST_NAME,
        SUBSTRING_INDEX(SUBSTRING_INDEX(o.COMMENT, 'Фамилия: ', -1), '\n', 1) as LAST_NAME
        FROM orders o";

if ($filter !== 'all') {
    $sql .= " WHERE o.STATUS = '" . $DB->ForSql($filter) . "'";
}

$sql .= " ORDER BY o.CREATED_AT DESC";
$result = $DB->Query($sql);
?>

<?
$APPLICATION->SetTitle("Управление заказами");
?>

<style>
    .orders-table {
        width: 100%;
        border-collapse: collapse;
        background: white;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    
    .orders-table th {
        background: #f5f5f5;
        padding: 12px;
        text-align: left;
        font-weight: 500;
        color: #333;
        border-bottom: 2px solid #ddd;
    }
    
    .orders-table td {
        padding: 12px;
        border-bottom: 1px solid #eee;
        vertical-align: middle;
    }
    
    .status-select {
        padding: 5px 8px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 12px;
    }
    
    .status-new { background: #e3f2fd; }
    .status-processing { background: #fff3e0; }
    .status-delivered { background: #e8f5e8; }
    .status-cancelled { background: #ffebee; }
    
    .btn-save {
        padding: 5px 12px;
        background: #2fc6f6;
        color: white;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 12px;
    }
    
    .btn-save:hover {
        background: #25a9d4;
    }
    
    .filter-tabs {
        margin: 20px 0;
        display: flex;
        gap: 10px;
    }
    
    .filter-tab {
        padding: 8px 16px;
        background: #f5f5f5;
        border-radius: 4px;
        text-decoration: none;
        color: #333;
        font-size: 13px;
    }
    
    .filter-tab.active {
        background: #2fc6f6;
        color: white;
    }
    
    .order-number {
        font-weight: bold;
        color: #2fc6f6;
    }
    
    .order-total {
        font-weight: bold;
        color: #e74c3c;
    }
</style>

<div class="adm-workarea">
    <div class="adm-detail-content-wrap">
        <div class="adm-detail-content">
            <div class="adm-detail-title">Управление заказами</div>
            
            <div class="filter-tabs">
                <a href="?filter=all" class="filter-tab <?= $filter == 'all' ? 'active' : '' ?>">Все</a>
                <a href="?filter=new" class="filter-tab <?= $filter == 'new' ? 'active' : '' ?>">Новые</a>
                <a href="?filter=processing" class="filter-tab <?= $filter == 'processing' ? 'active' : '' ?>">В обработке</a>
                <a href="?filter=delivered" class="filter-tab <?= $filter == 'delivered' ? 'active' : '' ?>">Доставленные</a>
                <a href="?filter=cancelled" class="filter-tab <?= $filter == 'cancelled' ? 'active' : '' ?>">Отменённые</a>
            </div>
            
            <table class="orders-table">
                <thead>
                    <tr>
                        <th>№ заказа</th>
                        <th>Дата</th>
                        <th>Клиент</th>
                        <th>Сумма</th>
                        <th>Доставка</th>
                        <th>Статус</th>
                        <th>Действие</th>
                    </tr>
                </thead>
                <tbody>
                    <? while ($order = $result->Fetch()): 
                        $date = new DateTime($order['CREATED_AT']);
                        $clientName = trim(($order['LAST_NAME'] ?? '') . ' ' . ($order['FIRST_NAME'] ?? ''));
                    ?>
                    <tr>
                        <td class="order-number"><?= htmlspecialchars($order['ORDER_NUMBER']) ?></td>
                        <td><?= $date->format('d.m.Y H:i') ?></td>
                        <td><?= $clientName ?: 'Не указан' ?></td>
                        <td class="order-total"><?= number_format($order['TOTAL_PRICE'], 0, '', ' ') ?> ₽</td>
                        <td><?= $order['DELIVERY_ADDRESS'] ? 'Доставка' : 'Самовывоз' ?></td>
                        <td>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="order_id" value="<?= $order['ID'] ?>">
                                <select name="status" class="status-select status-<?= $order['STATUS'] ?>" onchange="this.form.submit()">
                                    <option value="new" <?= $order['STATUS'] == 'new' ? 'selected' : '' ?>>Новый</option>
                                    <option value="processing" <?= $order['STATUS'] == 'processing' ? 'selected' : '' ?>>В обработке</option>
                                    <option value="delivered" <?= $order['STATUS'] == 'delivered' ? 'selected' : '' ?>>Доставлен</option>
                                    <option value="cancelled" <?= $order['STATUS'] == 'cancelled' ? 'selected' : '' ?>>Отменён</option>
                                </select>
                                <input type="hidden" name="update_status" value="Y">
                            </form>
                        </td>
                        <td>
                            <button class="btn-save" onclick="showDetails(<?= $order['ID'] ?>)">Детали</button>
                        </td>
                    </tr>
                    <tr id="details-<?= $order['ID'] ?>" style="display: none;">
                        <td colspan="7">
                            <div id="details-content-<?= $order['ID'] ?>">
                                Загрузка...
                            </div>
                        </td>
                    </tr>
                    <? endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function showDetails(orderId) {
    const row = document.getElementById('details-' + orderId);
    
    if (row.style.display === 'none') {
        row.style.display = 'table-row';
        loadOrderDetails(orderId);
    } else {
        row.style.display = 'none';
    }
}

function loadOrderDetails(orderId) {
    fetch('/ajax/get_order_detail.php?id=' + orderId)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                renderOrderDetails(orderId, data.order, data.items);
            }
        });
}

function renderOrderDetails(orderId, order, items) {
    const content = document.getElementById('details-content-' + orderId);
    
    let itemsHtml = '';
    let subtotal = 0;
    
    items.forEach(item => {
        const sum = item.PRICE * item.QUANTITY;
        subtotal += sum;
        
        itemsHtml += `
            <tr>
                <td>${item.PRODUCT_NAME}</td>
                <td>${formatPrice(item.PRICE)}</td>
                <td>${item.QUANTITY}</td>
                <td>${formatPrice(sum)}</td>
            </tr>
        `;
    });
    
    const deliveryPrice = parseFloat(order.DELIVERY_PRICE) || 0;
    const total = subtotal + deliveryPrice;
    
    const html = `
        <div style="padding: 20px; background: #fafafa;">
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; margin-bottom: 20px;">
                <div style="background: white; padding: 15px; border-radius: 6px;">
                    <strong>Клиент:</strong><br>
                    Телефон: ${order.PHONE ? '+7' + order.PHONE : 'Не указан'}<br>
                    Email: ${order.EMAIL || 'Не указан'}
                </div>
                <div style="background: white; padding: 15px; border-radius: 6px;">
                    <strong>Доставка:</strong><br>
                    ${order.DELIVERY_ADDRESS ? order.DELIVERY_ADDRESS : 'Самовывоз'}<br>
                    Стоимость: ${deliveryPrice > 0 ? formatPrice(deliveryPrice) : 'Бесплатно'}
                </div>
                <div style="background: white; padding: 15px; border-radius: 6px;">
                    <strong>Оплата:</strong><br>
                    ${order.PAYMENT_METHOD === 'card' ? 'Картой онлайн' : 'Наличными'}
                </div>
            </div>
            
            <table style="width: 100%; border-collapse: collapse; background: white;">
                <thead>
                    <tr style="background: #f0f0f0;">
                        <th style="padding: 10px; text-align: left;">Товар</th>
                        <th style="padding: 10px; text-align: left;">Цена</th>
                        <th style="padding: 10px; text-align: left;">Кол-во</th>
                        <th style="padding: 10px; text-align: left;">Сумма</th>
                    </tr>
                </thead>
                <tbody>
                    ${itemsHtml}
                </tbody>
            </table>
            
            <div style="margin-top: 15px; text-align: right; font-size: 16px;">
                <strong>Итого: <span style="color: #e74c3c;">${formatPrice(total)}</span></strong>
            </div>
            
            ${order.COMMENT ? `
            <div style="margin-top: 15px; padding: 15px; background: #fff3e0; border-radius: 6px;">
                <strong>Комментарий:</strong> ${order.COMMENT}
            </div>
            ` : ''}
        </div>
    `;
    
    content.innerHTML = html;
}

function formatPrice(price) {
    return new Intl.NumberFormat('ru-RU').format(price) + ' ₽';
}
</script>

<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>