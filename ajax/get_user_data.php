<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

$response = ['success' => false, 'message' => ''];

global $USER;

if ($USER->IsAuthorized()) {
    $userId = $USER->GetID();
    
    $dbUser = CUser::GetByID($userId);
    if ($arUser = $dbUser->Fetch()) {
        $response['success'] = true;
        $response['user'] = [
            'ID' => $arUser['ID'],
            'NAME' => $arUser['NAME'],
            'LAST_NAME' => $arUser['LAST_NAME'],
            'SECOND_NAME' => $arUser['SECOND_NAME'],
            'EMAIL' => $arUser['EMAIL'],
            'LOGIN' => $arUser['LOGIN'],
            'PERSONAL_PHONE' => $arUser['PERSONAL_PHONE'],
            'PERSONAL_CITY' => $arUser['PERSONAL_CITY'],
            'PERSONAL_STREET' => $arUser['PERSONAL_STREET'],
            'PERSONAL_ZIP' => $arUser['PERSONAL_ZIP']
        ];
    } else {
        $response['message'] = 'Пользователь не найден';
    }
} else {
    // Для неавторизованных пользователей пробуем получить данные из сессии/куки
    $sessionData = $_SESSION['ORDER_DATA'] ?? [];
    if (!empty($sessionData)) {
        $response['success'] = true;
        $response['user'] = $sessionData;
        $response['is_guest'] = true;
    } else {
        $response['success'] = true;
        $response['user'] = null;
        $response['is_guest'] = true;
        $response['message'] = 'Гость';
    }
}

header('Content-Type: application/json');
echo json_encode($response, JSON_UNESCAPED_UNICODE);
?>