<?
// Добавляем пункт меню в раздел "Сервис"
AddEventHandler("main", "OnBuildGlobalMenu", "AddOrdersMenu");

function AddOrdersMenu(&$arGlobalMenu, &$arModuleMenu) {
    // Добавляем в раздел "Сервис"
    $arGlobalMenu['services']['items'][] = array(
        "text" => "Управление заказами",
        "url" => "orders_list.php",
        "icon" => "iblock_menu_icon_iblocks",
        "more_url" => array(),
        "title" => "Управление заказами",
    );
    
    // Или создаем отдельный раздел (раскомментировать если нужно)
    /*
    $arGlobalMenu['orders'] = array(
        "menu_id" => "orders",
        "text" => "Заказы",
        "title" => "Управление заказами",
        "sort" => 100,
        "items" => array(
            array(
                "text" => "Список заказов",
                "url" => "orders_list.php",
                "icon" => "iblock_menu_icon_iblocks",
                "title" => "Список заказов",
            ),
        ),
    );
    */
}
?>