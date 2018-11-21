<?php
header("Content-Type: application/json; charset=utf-8");

$action = $_POST["action"];
$user_id = $_POST["user_id"];
$result = "";

switch ($action) {
    case "check" :
        $result = check_official($user_id);
        break;
    case "cancel" :
        $result = cancel_official($user_id);
        break;
}

function check_official($user_id) {
    global $db;
    $db -> sql_query("UPDATE users SET is_check_official = '1' WHERE id = '$user_id' AND is_official = '1'");
    $result = "Подтвержден";
    return $result;
}

function cancel_official($user_id) {
    global $db;
    $db -> sql_query("UPDATE users SET is_check_official = '0', is_official = '0' WHERE id = '$user_id'");
    $result = "Отменен";
    return $result;
}

echo json_encode($result);
?>