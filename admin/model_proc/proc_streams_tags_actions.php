<?php
header("Content-Type: application/json; charset=utf-8");

$action = $_POST["action"];
$tag_id = $_POST["tag_id"];
$result = "";

switch ($action) {
    case "disable" :
        $result = disable_tag($tag_id);
        break;
    case "enable" :
        $result = enable_tag($tag_id);
        break;
}
#Запретить использование тега
function disable_tag($tag_id) {
    global $db;
    $db -> sql_query("UPDATE streams_tags_data SET is_disabled = '1' WHERE id = '$tag_id'");
    $result = "Тег запрещен";
    return $result;
}
#Разрешить использование тега
function enable_tag($tag_id) {
    global $db;
    $db -> sql_query("UPDATE streams_tags_data SET is_disabled = '0' WHERE id = '$tag_id'");
    $result = "Тег разрешен";
    return $result;
}

echo json_encode($result);
?>