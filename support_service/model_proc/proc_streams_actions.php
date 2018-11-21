<?php
$action = $_POST["action"];
$stream_id = $_POST["stream_id"];
$result = "";

switch ($action) {
    case "notify" :
        $result = notify_user($stream_id);
        break;
    case "block" :
        $result = block_device($stream_id);
        break;
    case "unblock" :
        $result = unblock_device($stream_id);
        break;
}

#Уведомить пользователя о возможной блокировке трансляции (в случае продолжения трансляции запрещенных материалов)
function notify_user($stream_id) {
    global $db;
    $current_date = time();
    $db -> sql_query("INSERT INTO streams_notify_log (id, stream_id, created_date) VALUES (NULL, '$stream_id', '$current_date')");
    $result = "Уведомление отправлено";
    return $result;
}

#Заблокировать устройство
function block_device($stream_id) {
    global $db;
    $current_date = time();
    $db -> sql_query("INSERT INTO streams_blocks_log (id, stream_id, blocked_date, unblocked_date) VALUES (NULL, '$stream_id', '$current_date', '0')");
    $db -> sql_query("UPDATE streams SET is_blocked = '1' WHERE id = '$stream_id'");
    $result = "Устройство заблокировано";
    return $result;
}

#Разблокировать устройство
function unblock_device($stream_id) {
    global $db;
    $current_date = time();
    $db -> sql_query("UPDATE streams SET is_blocked = '0' WHERE id = '$stream_id'");
    $db -> sql_query("UPDATE streams_blocks_log SET unblocked_date = '$current_date' WHERE stream_id = '$stream_id'");
    $result = "Устройство разблокировано";
    return $result;
}

echo $result;
?>