<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');

header('Content-Type: application/json');

global $USER;

if (!$USER->IsAuthorized()) {
    echo json_encode(['success' => true, 'user' => null]);
    return;
}

$userData = [
    'NAME' => $USER->GetFullName(),
    'EMAIL' => $USER->GetEmail(),
    'PERSONAL_PHONE' => $USER->GetParam('PERSONAL_PHONE'),
    'PERSONAL_STREET' => $USER->GetParam('PERSONAL_STREET')
];

echo json_encode([
    'success' => true,
    'user' => $userData
]);