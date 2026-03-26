<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

// Простая проверка админа
global $USER;
if (!$USER->IsAdmin()) {
    die("Только для администратора");
}

// ID админа, которого НЕ трогаем
$ADMIN_ID = 1;

$action = $_GET['action'] ?? '';

// Простой список пользователей
if ($action == 'list') {
    global $DB;
    $sql = "SELECT ID, LOGIN, EMAIL FROM b_user WHERE ID != " . $ADMIN_ID . " ORDER BY ID DESC LIMIT 50";
    $result = $DB->Query($sql);
    
    echo "<h2>Пользователи (кроме админа):</h2>";
    echo "<ul>";
    while ($user = $result->Fetch()) {
        echo "<li>";
        echo "ID: " . $user['ID'] . " | ";
        echo "Логин: " . $user['LOGIN'] . " | ";
        echo "Email: " . $user['EMAIL'] . " | ";
        echo "<a href='?action=delete&id=" . $user['ID'] . "' onclick='return confirm(\"Удалить?\")'>[Удалить]</a>";
        echo "</li>";
    }
    echo "</ul>";
    echo "<p><a href='?action=delete_all'>Удалить ВСЕХ (кроме админа)</a></p>";
}

// Удаление одного
elseif ($action == 'delete' && isset($_GET['id'])) {
    $userId = intval($_GET['id']);
    if ($userId == $ADMIN_ID) {
        die("Нельзя удалить админа!");
    }
    
    $user = new CUser;
    if ($user->Delete($userId)) {
        echo "✅ Пользователь удален!";
    } else {
        echo "❌ Ошибка: " . $user->LAST_ERROR;
    }
    echo "<p><a href='?action=list'>Назад</a></p>";
}

// Удаление всех (по одному, чтобы не перегружать)
elseif ($action == 'delete_all') {
    global $DB;
    $sql = "SELECT ID FROM b_user WHERE ID != " . $ADMIN_ID . " ORDER BY ID";
    $result = $DB->Query($sql);
    
    $count = 0;
    while ($user = $result->Fetch()) {
        $userObj = new CUser;
        if ($userObj->Delete($user['ID'])) {
            $count++;
        }
        // Небольшая задержка, чтобы не нагружать сервер
        usleep(100000); // 0.1 секунды
    }
    
    echo "✅ Удалено пользователей: " . $count;
    echo "<p><a href='?action=list'>Назад</a></p>";
}

// Главная страница скрипта
if (!$action) {
    echo "<h1>Удаление пользователей</h1>";
    echo "<p><a href='?action=list'>Показать список пользователей</a></p>";
    echo "<p><a href='/'>&larr; На главную</a></p>";
}
?>