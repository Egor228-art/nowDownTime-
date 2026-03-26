<?php
$this->queryExecute("SET SESSION sql_mode=''");
$this->queryExecute("SET NAMES 'utf8mb4' COLLATE 'utf8mb4_0900_ai_ci'");

error_reporting(0);
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);

// Но логировать всё равно будем (для отладки)
ini_set('log_errors', 1);
ini_set('error_log', $_SERVER['DOCUMENT_ROOT'].'/bitrix/php_interface/error.log');
