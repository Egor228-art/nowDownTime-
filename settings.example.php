<?php
// Пример настроек для разработки
// Скопируйте в settings.php и укажите свои данные

define('DB_HOST', 'localhost');
define('DB_LOGIN', 'your_login');
define('DB_PASSWORD', 'your_password');
define('DB_NAME', 'your_database');

// Для Bitrix
$DBHost = 'localhost';
$DBLogin = 'your_login';
$DBPassword = 'your_password';
$DBName = 'your_database';

define('BX_USE_MYSQLI', true);
define('DBPersistent', false);
$DBDebug = true;
$DBDebugToFile = false;