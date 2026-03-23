<?
require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');

header('Content-Type: application/json');

global $USER;
if (!$USER->IsAuthorized()) {
    echo json_encode(['success' => false, 'message' => 'Необходимо авторизоваться']);
    return;
}

$productId = intval($_POST['product_id'] ?? 0);
$rating = intval($_POST['rating'] ?? 5);
$text = trim($_POST['text'] ?? '');
$pros = trim($_POST['pros'] ?? '');
$cons = trim($_POST['cons'] ?? '');

if (!$productId || !$text) {
    echo json_encode(['success' => false, 'message' => 'Заполните обязательные поля']);
    return;
}

// Проверяем, покупал ли пользователь товар
if (CModule::IncludeModule('sale')) {
    $userId = $USER->GetID();
    
    $arOrderFilter = array(
        'USER_ID' => $userId,
        'LID' => SITE_ID,
        'STATUS_ID' => array('F', 'P')
    );
    
    $dbOrders = CSaleOrder::GetList(array(), $arOrderFilter, false, false, array('ID'));
    $orderIds = array();
    while ($arOrder = $dbOrders->Fetch()) {
        $orderIds[] = $arOrder['ID'];
    }
    
    if (!empty($orderIds)) {
        $dbBasket = CSaleBasket::GetList(
            array(),
            array(
                'ORDER_ID' => $orderIds,
                'PRODUCT_ID' => $productId
            ),
            false,
            false,
            array('ID')
        );
        
        if (!$dbBasket->Fetch()) {
            echo json_encode(['success' => false, 'message' => 'Вы можете оставить отзыв только после покупки']);
            return;
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'У вас нет оплаченных заказов']);
        return;
    }
}

// Сохраняем отзыв
if (CModule::IncludeModule('iblock')) {
    $reviewsIblockId = 0;
    $res = CIBlock::GetList(array(), array('CODE' => 'reviews'));
    if ($arRes = $res->Fetch()) {
        $reviewsIblockId = $arRes['ID'];
    }
    
    if ($reviewsIblockId) {
        $el = new CIBlockElement;
        
        $arFields = array(
            "IBLOCK_ID" => $reviewsIblockId,
            "NAME" => "Отзыв на товар #" . $productId,
            "ACTIVE" => "N",
            "PREVIEW_TEXT" => $text,
            "PROPERTY_VALUES" => array(
                "PRODUCT_ID" => $productId,
                "RATING" => $rating,
                "USER_NAME" => $USER->GetFullName() ?: $USER->GetLogin(),
                "PRO" => $pros,
                "CONTRA" => $cons
            ),
            "DATE_CREATE" => new \Bitrix\Main\Type\DateTime()
        );
        
        $id = $el->Add($arFields);
        
        if ($id) {
            echo json_encode(['success' => true, 'review_id' => $id]);
        } else {
            echo json_encode(['success' => false, 'message' => $el->LAST_ERROR]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Инфоблок отзывов не найден']);
    }
}
?>