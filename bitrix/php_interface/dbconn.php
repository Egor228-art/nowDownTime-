<?php
// ============ ЖЕСТКОЕ ПОДАВЛЕНИЕ ОШИБОК ============
error_reporting(0);
ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');

// Отключаем все предупреждения конкретно для функций работы со строками
if (PHP_VERSION_ID >= 80000) {
    // Для PHP 8+ отключаем конкретные деприкейшены
    set_error_handler(function($errno, $errstr) {
        if (strpos($errstr, 'strcasecmp') !== false) return true;
        if (strpos($errstr, 'Passing null to parameter') !== false) return true;
        if (strpos($errstr, 'Deprecated') !== false) return true;
        if (strpos($errstr, 'Undefined array key') !== false) return true;
        return false; // остальные ошибки показываем
    }, E_ALL);
}

// Дальше идет стандартный код Битрикса
define("BX_USE_MYSQLI", true);

define("BX_FILE_PERMISSIONS", 0644);
define("BX_DIR_PERMISSIONS", 0755);
@umask(~(BX_FILE_PERMISSIONS | BX_DIR_PERMISSIONS) & 0777);

define("BX_DISABLE_INDEX_PAGE", true);

mb_internal_encoding("UTF-8");
