<?php
session_start();
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

header('Content-Type: application/json');

// Проверяем авторизацию
global $USER;
$userId = $USER->IsAuthorized() ? $USER->GetID() : session_id();

// Файл для хранения данных о зрителях
$seatsFile = $_SERVER["DOCUMENT_ROOT"] . "/ajax/seats_data.json";

// Функция для чтения данных
function getSeatsData($file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        return json_decode($content, true) ?: ['seats' => [], 'last_cleanup' => time()];
    }
    return ['seats' => [], 'last_cleanup' => time()];
}

// Функция для сохранения данных
function saveSeatsData($file, $data) {
    file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT));
}

// Функция для очистки AFK пользователей (больше 15 минут)
function cleanupAFKUsers(&$data) {
    $currentTime = time();
    $timeout = 15 * 60; // 15 минут в секундах
    
    foreach ($data['seats'] as $seatId => $user) {
        if ($currentTime - $user['last_activity'] > $timeout) {
            unset($data['seats'][$seatId]);
        }
    }
    
    $data['last_cleanup'] = $currentTime;
}

// Получаем параметры
$action = $_POST['action'] ?? $_GET['action'] ?? '';

// Данные о рядах
$rows = [
    'row1' => ['seats' => 4, 'prefix' => 'r1'],
    'row2' => ['seats' => 5, 'prefix' => 'r2']
];

switch ($action) {
    case 'register':
        // Регистрируем пользователя и сажаем на случайное место
        $data = getSeatsData($seatsFile);
        
        // Очищаем AFK
        cleanupAFKUsers($data);
        
        // Проверяем, есть ли пользователь уже на месте
        $existingSeat = null;
        foreach ($data['seats'] as $seatId => $user) {
            if ($user['user_id'] === $userId) {
                $existingSeat = $seatId;
                break;
            }
        }
        
        if ($existingSeat) {
            // Обновляем активность
            $data['seats'][$existingSeat]['last_activity'] = time();
            saveSeatsData($seatsFile, $data);
            
            echo json_encode([
                'success' => true,
                'already_seated' => true,
                'seat' => $existingSeat,
                'seats' => $data['seats']
            ]);
            break;
        }
        
        // Ищем свободное место
        $freeSeats = [];
        
        foreach ($rows as $rowKey => $rowData) {
            for ($i = 1; $i <= $rowData['seats']; $i++) {
                $seatId = $rowData['prefix'] . $i;
                if (!isset($data['seats'][$seatId])) {
                    $freeSeats[] = $seatId;
                }
            }
        }
        
        if (count($freeSeats) > 0) {
            // Выбираем случайное свободное место
            $randomSeat = $freeSeats[array_rand($freeSeats)];
            
            // Сажаем пользователя
            $data['seats'][$randomSeat] = [
                'user_id' => $userId,
                'last_activity' => time(),
                'is_authorized' => $USER->IsAuthorized(),
                'user_name' => $USER->IsAuthorized() ? $USER->GetLogin() : 'Гость'
            ];
            
            saveSeatsData($seatsFile, $data);
            
            echo json_encode([
                'success' => true,
                'seat' => $randomSeat,
                'seats' => $data['seats']
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'error' => 'Все места заняты',
                'seats' => $data['seats']
            ]);
        }
        break;
        
    case 'update_activity':
        // Обновляем активность пользователя
        $data = getSeatsData($seatsFile);
        
        foreach ($data['seats'] as $seatId => $user) {
            if ($user['user_id'] === $userId) {
                $data['seats'][$seatId]['last_activity'] = time();
                saveSeatsData($seatsFile, $data);
                break;
            }
        }
        
        echo json_encode(['success' => true]);
        break;
        
    case 'leave':
        // Пользователь покидает страницу
        $data = getSeatsData($seatsFile);
        
        foreach ($data['seats'] as $seatId => $user) {
            if ($user['user_id'] === $userId) {
                unset($data['seats'][$seatId]);
                saveSeatsData($seatsFile, $data);
                break;
            }
        }
        
        echo json_encode(['success' => true]);
        break;
        
    case 'get_seats':
        // Получаем текущее состояние мест
        $data = getSeatsData($seatsFile);
        cleanupAFKUsers($data);
        saveSeatsData($seatsFile, $data);
        
        // Фильтруем - пользователь не видит себя
        $seatsForUser = [];
        foreach ($data['seats'] as $seatId => $user) {
            if ($user['user_id'] !== $userId) {
                $seatsForUser[$seatId] = [
                    'is_occupied' => true,
                    'is_me' => false
                ];
            } else {
                $seatsForUser[$seatId] = [
                    'is_occupied' => true,
                    'is_me' => true
                ];
            }
        }
        
        echo json_encode([
            'success' => true,
            'seats' => $seatsForUser,
            'my_seat' => $data['seats'][$userId] ?? null
        ]);
        break;
        
    default:
        echo json_encode(['success' => false, 'error' => 'Unknown action']);
        break;
}
?>