<?
require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);
header('Access-Control-Allow-Credentials: true');

$action = $_POST['action'] ?? '';
$productId = intval($_POST['product_id'] ?? 0);

if (!$productId) {
    echo json_encode(['success' => false, 'message' => 'Не указан товар']);
    return;
}

// Работаем с куками
$favorites = array();
if (isset($_COOKIE['favorites'])) {
    $favorites = json_decode($_COOKIE['favorites'], true);
    if (!is_array($favorites)) {
        $favorites = array();
    }
}

if ($action === 'toggle') {
    $key = array_search($productId, $favorites);
    if ($key !== false) {
        unset($favorites[$key]);
        $favorites = array_values($favorites);
        $result = ['success' => true, 'action' => 'removed'];
    } else {
        $favorites[] = $productId;
        $result = ['success' => true, 'action' => 'added'];
    }
    
    setcookie('favorites', json_encode($favorites), time() + 60*60*24*30, '/', '', false, true);
    echo json_encode($result);
} elseif ($action === 'get') {
    echo json_encode(['success' => true, 'favorites' => $favorites]);
}
?>