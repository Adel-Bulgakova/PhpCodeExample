<?php
header("Content-Type: application/json; charset=utf-8");
global $db, $stream, $snapshot_query, $profile_image_query, $project_options, $user;

$streams_filter_date_map = $project_options["streams_filter_date_map"];
$filter_date = time() - 60*60*24*$streams_filter_date_map; # Отображение live и записей за последние 72 часа (2 дня)

$result = $db -> sql_query("SELECT * FROM `streams` LEFT JOIN `users` ON `users`.`id` = `streams`.`user_id` WHERE `streams`.`lat` != '0' AND `streams`.`lng` != '0' AND `streams`.`is_excess` = '0' AND `streams`.`is_blocked` = '0' AND `streams`.`is_deleted` = '0' AND `streams`.`start_date` >= '$filter_date' AND `users`.`is_blocked` = '0' AND `users`.`is_deleted` = '0'", "", "array");
$markers_array = array();
if (sizeof($result) > 0) {

    foreach ($result as $value) {

        $stream_uuid = $value["uuid"];
        $permissions = permissions($stream_uuid);
        $watch_permissions = $permissions["watch"];
        if ($watch_permissions = 1){


            $user_id = $value["user_id"];
            $stream_name = $stream -> stream_name($stream_uuid);
            $profile_image = $user -> profile_image_html($user_id);
            $profile_name = $user -> profile_name($user_id);
            $snapshot = $snapshot_query.$stream_uuid;

            $stream_status = 0;
            $end_date = intval($value["end_date"]);
            if ($end_date == 0) {
                $stream_status = 1;
            }
            $marker["lat"] = $value["lat"];
            $marker["lng"] = $value["lng"];
            $marker["stream_status"] = $stream_status;
            $marker["profile_id"] = $user_id;
            $marker["profile_image_url"] = $profile_image_query.$user_id;
            $marker["profile_name"] = $profile_name;
            $marker["stream_uuid"] = $stream_uuid;
            $marker["stream_name"] = $stream_name;
            $marker["snapshot_url"] = $snapshot;

            $markers_array[] = $marker;
        }
    }
}
echo json_encode($markers_array);
?>