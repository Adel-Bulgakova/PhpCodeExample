<?php
header("Content-Type: application/json; charset=utf-8");

$action = prepair_str($_POST["action"]);
$claim_id = prepair_str($_POST["claim_id"]);
$stream_id = prepair_str($_POST["stream_id"]);
$result = "";

switch ($action) {
    case "block" :
        $result = block_stream($claim_id, $stream_id);
        break;
    case "reject" :
        $result = reject_claim($claim_id, $stream_id);
        break;
    case "delete" :
        $result = delete_claim($claim_id, $stream_id);
        break;
}

function block_stream($claim_id, $stream_id) {
    global $db;
    $current_date = time();
    $db -> sql_query("INSERT INTO streams_blocks_log (id, stream_id, blocked_date, unblocked_date) VALUES (NULL, '$stream_id', '$current_date', '0')");
    $db -> sql_query("UPDATE streams SET is_blocked = '1' WHERE id = '$stream_id'");
    $db -> sql_query("UPDATE claims SET status_id = '2' WHERE id = '$claim_id' AND stream_id = '$stream_id'");
    $result = "Устройство заблокировано";
    return $result;
}

function reject_claim($claim_id, $stream_id) {
    global $db;
    $db -> sql_query("UPDATE claims SET status_id = '3' WHERE id = '$claim_id' AND stream_id = '$stream_id'");
    $result = "Жалоба пользователя отклонена";
    return $result;
}

function delete_claim($claim_id, $stream_id) {
    global $db;
    $db -> sql_query("UPDATE claims SET status_id = '4', is_deleted = '1' WHERE id = '$claim_id' AND stream_id = '$stream_id'");
    $result = "Жалоба пользователя удалена";
    return $result;
}

echo json_encode($result);
?>