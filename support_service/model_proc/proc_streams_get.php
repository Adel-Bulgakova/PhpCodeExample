<?php
header("Content-Type: application/json; charset=utf-8");
global $db, $stream, $thumb_query;

$result_streams = $db -> sql_query("SELECT `id`, `uuid`, `user_id`, `start_date`, `is_blocked` FROM `streams` WHERE `status` = '1' AND `is_excess` = '0' AND `is_deleted` = '0'", "", "array");
if (sizeof($result_streams) > 0){
    $streams = array();
    foreach ($result_streams as $value) {
        $stream_data = array();
        $stream_id = $value["id"];
        $stream_uuid = $value["uuid"];
        $stream_url = $stream -> generate_stream_url($stream_uuid);
        $stream_name = $stream -> stream_name($stream_uuid);
        $thumb = $thumb_query.$stream_uuid;

        $stream_preview = "<a data-toggle=\"modal\" data-target=\".bs-modal-lg\" data-stream-uuid=\"$stream_uuid\"   data-stream-url=\"$stream_url\" data-stream-name=\"$stream_name\" class=\"stream_view\"><div class=\"screenshot\" style=\"background: #D6D6D6 url('$thumb') center center no-repeat;  background-size: cover;\"></div></a>";

        $user_id = $value["user_id"];
        $result_profile = $db -> sql_query("SELECT `name`, `login`, `email` FROM `users` WHERE `id` = '$user_id' AND `is_deleted` = '0'", "", "array");
        $user_profile = current(array_filter($result_profile[0]));

        $stream_info = "<b>".$user_profile."</b><br><br>".$stream_name;

        $start_date = intval($value["start_date"]);
        $start_date = date("d.m.Y H:i", $start_date);

        $result_notify = $db -> sql_query("SELECT `id` FROM `streams_notify_log` WHERE `stream_id` = '$stream_id'", "", "array");
        $notify_count = sizeof($result_notify);
        $notify = "<a href=\"#\" data-toggle=\"modal\" data-target=\".bs-modal-lg\" data-stream-id=\"$stream_id\" data-action=\"notify\" class=\"stream_action\">Отправить<br>предупреждение</a><br><br>Отправлено: $notify_count";

        $status = $value["is_blocked"];
        $action = "";
        if ($status == "1") {
            $status = "<i class=\"fa fa-circle waiting\"></i>";
            $action = "<a href=\"#\" data-toggle=\"modal\" data-target=\".bs-modal-lg\" data-stream-id=\"$stream_id\" data-action=\"unblock\" class=\"stream_action\">Разблокировать</a>";
        } else {
            $status = "<i class=\"fa fa-circle done\"></i>";
            $action = "<a href=\"#\" data-toggle=\"modal\" data-target=\".bs-modal-lg\" data-stream-id=\"$stream_id\" data-action=\"block\" class=\"stream_action\">Заблокировать</a>";
        }

        array_push($stream_data, $stream_preview, $stream_info, $stream_uuid, $start_date, $status, $notify, $action);
        array_push($streams, $stream_data);
    }
    echo json_encode($streams);
} else {
    //ошибка обработки
    echo json_encode("error");
}
?>