<?php
header("Content-Type: application/json; charset=utf-8");

$action = $_POST["action"];
$user_id = $_POST["user_id"];
$result = "";

switch ($action) {
    case "block" :
        $result = block_user($user_id);
        break;
    case "unblock" :
        $result = unblock_user($user_id);
        break;
}

//TODO блокировка устройств заблокированного пользователя, unbind in js
function block_user($user_id) {
    global $db;
    $current_date = time();
    $db -> sql_query("INSERT INTO users_blocks_log (id, user_id, blocked_date, unblocked_date) VALUES (NULL, '$user_id', '$current_date', '0')");
    $db -> sql_query("UPDATE users SET is_blocked = '1' WHERE id = '$user_id' LIMIT 1");
    $result = "Пользователь заблокирован";
    return $result;
}

function unblock_user($user_id) {
    global $db;
    $current_date = time();
    $db -> sql_query("UPDATE users_blocks_log SET unblocked_date = '$current_date' WHERE user_id = '$user_id'");
    $db -> sql_query("UPDATE users SET is_blocked = '0' WHERE id = '$user_id'");
    $result = "Пользователь разблокирован";
    return $result;
}

echo json_encode($result);

?>