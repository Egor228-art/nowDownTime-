<?php
// Подключаем ядро Битрикс
define("NO_KEEP_STATISTIC", true);
define("NOT_CHECK_PERMISSIONS", true);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

header('Content-Type: application/json');

$action = $_POST['action'] ?? $_GET['action'] ?? '';

if (!$action) {
    echo json_encode(['success' => false, 'error' => 'No action specified']);
    exit;
}

global $DB;

if ($action === 'update_viewer') {
    $userId = intval($_POST['user_id']);
    $userName = $DB->ForSql($_POST['user_name']);
    $userAvatar = $DB->ForSql($_POST['user_avatar']);
    $productId = intval($_POST['product_id']);
    $sessionId = $DB->ForSql(session_id() ?: md5($_SERVER['HTTP_USER_AGENT'] . $_SERVER['REMOTE_ADDR']));
    
    if ($userId > 0) {
        // Удаляем неактивных (старше 30 секунд)
        $DB->Query("DELETE FROM active_viewers WHERE last_active < DATE_SUB(NOW(), INTERVAL 30 SECOND)");
        
        // Проверяем существует ли запись
        $exists = $DB->Query("SELECT id FROM active_viewers 
                              WHERE user_id = $userId AND product_id = $productId AND session_id = '$sessionId'");
        
        if ($exists->Fetch()) {
            $DB->Query("UPDATE active_viewers 
                        SET last_active = NOW(), user_name = '$userName', user_avatar = '$userAvatar'
                        WHERE user_id = $userId AND product_id = $productId AND session_id = '$sessionId'");
        } else {
            $DB->Query("INSERT INTO active_viewers (user_id, user_name, user_avatar, product_id, session_id, last_active, created_at) 
                        VALUES ($userId, '$userName', '$userAvatar', $productId, '$sessionId', NOW(), NOW())");
        }
        
        // Получаем всех активных зрителей для этого товара
        $viewers = [];
        $res = $DB->Query("SELECT user_id, user_name, user_avatar FROM active_viewers 
                           WHERE product_id = $productId AND last_active > DATE_SUB(NOW(), INTERVAL 30 SECOND)
                           ORDER BY created_at DESC LIMIT 9");
        
        while ($row = $res->Fetch()) {
            $viewers[] = [
                'id' => $row['user_id'],
                'name' => $row['user_name'],
                'avatar' => $row['user_avatar']
            ];
        }
        
        echo json_encode(['success' => true, 'viewers' => $viewers]);
        exit;
    }
    
    echo json_encode(['success' => false]);
    exit;
}

if ($action === 'remove_viewer') {
    $userId = intval($_POST['user_id']);
    $productId = intval($_POST['product_id']);
    $sessionId = $DB->ForSql(session_id() ?: md5($_SERVER['HTTP_USER_AGENT'] . $_SERVER['REMOTE_ADDR']));
    
    $DB->Query("DELETE FROM active_viewers 
                WHERE user_id = $userId AND product_id = $productId AND session_id = '$sessionId'");
    
    echo json_encode(['success' => true]);
    exit;
}

if ($action === 'get_viewers') {
    $productId = intval($_POST['product_id']);
    
    $viewers = [];
    $res = $DB->Query("SELECT user_id, user_name, user_avatar FROM active_viewers 
                       WHERE product_id = $productId AND last_active > DATE_SUB(NOW(), INTERVAL 30 SECOND)
                       ORDER BY created_at DESC LIMIT 9");
    
    while ($row = $res->Fetch()) {
        $viewers[] = [
            'id' => $row['user_id'],
            'name' => $row['user_name'],
            'avatar' => $row['user_avatar']
        ];
    }
    
    echo json_encode(['success' => true, 'viewers' => $viewers]);
    exit;
}

echo json_encode(['success' => false, 'error' => 'Unknown action']);
exit;