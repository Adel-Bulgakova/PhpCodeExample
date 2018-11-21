<?php
global $db, $users_chat;

$action = $_GET["action"];
$query = $_GET["query"];
$user_id = $_SESSION["uid"];
$result = "";

switch ($action) {

    case "search" :
        $result = search($query);
        break;

    case "filter_by_tag" :
        $result = filter_by_tag($query);
        break;

    case "load" :
        header("Content-Type: application/json; charset=utf-8");
        $result = json_encode(load_streams($query));
        break;

}

function search($query = ""){
    global $db, $log_file, $thumb_query, $user;
    $result = "";
    if (strlen($query) > 1) {
        $query_upper = strtoupper($query);
        $query_ucfirst = ucfirst($query);
        $rep = "<b>$query</b>";
        $rep_upper = "<b>$query_upper</b>";
        $rep_ucfirst= "<b>$query_ucfirst</b>";
        $result = "";
        $streams_array = array();
        $profiles_array = array();

        $result_streams = $db -> sql_query("SELECT `uuid`, `user_id`, `streams`.`name` AS `stream_name` FROM `streams` LEFT JOIN `users` ON `streams`.`user_id` = `users`.`id` WHERE `streams`.`name` LIKE '%".$query."%' AND `streams`.`is_excess` = '0' AND `streams`.`is_blocked` = '0' AND `streams`.`is_deleted` = '0' AND `users`.`is_blocked` = '0' AND `users`.`is_deleted` = '0' ORDER BY `streams`.`id` DESC", "", "array");

        if (sizeof($result_streams) > 0){
            foreach ($result_streams as $v) {
                $stream_uuid = $v["uuid"];
                $user_id = $v["user_id"];
                $name = $v["stream_name"];
                $result_name = str_replace($query, $rep, $name);
                $result_name = str_replace($query_upper, $rep_upper, $result_name);
                $result_name = str_replace($query_ucfirst, $rep_ucfirst, $result_name);
                $streams_array[$stream_uuid][] = $user_id;
                $streams_array[$stream_uuid][] = $result_name;
            }
        }

        $result_profiles = $db -> sql_query("SELECT * FROM `users` WHERE (`name` LIKE '%".$query."%' AND `is_blocked` = '0' AND `is_deleted` = '0') OR (`about` LIKE '%".$query."%' AND `is_blocked` = '0' AND `is_deleted` = '0') GROUP BY `id`", "", "array");
        if (sizeof($result_profiles) > 0) {
            foreach ($result_profiles as $value) {
                $hero_id = $value["id"];
                $name = $user -> profile_name($hero_id);
                $result_name = str_replace($query, $rep, $name);
                $result_name = str_replace($query_upper, $rep_upper, $result_name);
                $result_name = str_replace($query_ucfirst, $rep_ucfirst, $result_name);

                $about = $value["about"];
                $result_about = str_replace($query, $rep, $about);
                $result_about = str_replace($query_upper, $rep_upper, $result_about);
                $result_about = str_replace($query_ucfirst, $rep_ucfirst, $result_about);

                $profiles_array[$hero_id][] = $result_name;
                $profiles_array[$hero_id][] = $result_about;
            }
        }

        foreach ($streams_array as $key => $value) {
            $user_id = $value[0];
            $name = $value[1];
            $thumb = $thumb_query . $key;
            $result .= "<a href=\"/index.php?route=page_play&user=$user_id&uuid=$key\"><div class=\"search_results_item_inner\"><div class=\"thumbnail\" style=\"background: #D6D6D6 url('$thumb') center center no-repeat; background-size: cover;)\"><i class=\"icon-play\"></i></div><p>$name</p></div></a>";
        }

        foreach ($profiles_array as $key => $value) {
            $profile_name  = $value[0];
            $profile_about  = $value[1];
            $profile_image = $user -> profile_image_html($key);
            $profile_about_output = "";
            if (!empty($profile_about)) {
                $profile_about_output = "<br><span class=\"profile_about\">$profile_about</span>";
            }
            $result .=  "<br><div class=\"profile_info\" data-profile-id=\"$key\">$profile_image<div class=\"profile_name\"><span>$profile_name</span>$profile_about_output</div></div>";
        }
    }
    return $result;
}

function filter_by_tag($query){
    global $db, $stream;

    #Получение списка трансляций по выбранному тегу
    $result = $db -> sql_query("SELECT `streams`.`uuid` AS `stream_uuid` FROM `streams_tags` LEFT JOIN `streams` ON `streams`.`id` = `streams_tags`.`stream_id` WHERE `streams_tags`.`stream_tag_id` = '$query' AND `streams`.`permissions` = '' AND `streams`.`is_excess` = '0' AND `streams`.`is_blocked` = '0' AND `streams`.`is_deleted` = '0' AND `streams_tags`.`is_deleted` = '0' ORDER BY `streams`.`id` DESC", "", "array");
    if (sizeof($result) < 1 OR empty($result[0])) {
        $html = _NO_STREAMS_BY_TAG;
    } else {
        $html = "";
        foreach ($result as $value) {
            $stream_uuid = $value["stream_uuid"];
            $stream_preview = $stream -> stream_preview_html($stream_uuid);

            $html .= $stream_preview;
        }
    }
    return $html;
}

echo $result;
?>