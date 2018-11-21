<?php
#Получение количества расшариваний трансляций в vk и facebook, API twitter не позволяет получить количество расшариваний
require_once "/www/config.php";
require_once "/www/function.php";

global $db, $project_options;
$cron_log_file = "/www/logs/cron_".date("dmY").".log";
$time = date("H:i");

$result_streams = $db -> sql_query("SELECT `uuid`, `user_id` FROM `streams` WHERE `is_excess` = '0' AND `is_blocked` = '0' AND `is_deleted` = '0'", "", "array");

if (sizeof($result_streams) > 0) {
    foreach ($result_streams AS $value) {
        $user_id = $value["user_id"];
        $stream_uuid = $value["uuid"];
        $stream_url_encode = urlencode($project_options["service_url_inner"]."index.php?route=page_play&user=$user_id&uuid=$stream_uuid");
        $fb_shares = 0;
        $vk_shares = 0;

        if ($result_fb_shares = json_decode(file_get_contents("http://graph.facebook.com/?id=$stream_url_encode"), true)) {
            if ($result_fb_shares["shares"]) {
                $fb_shares = $result_fb_shares["shares"];
            }
        }

        if ($result_vk_shares = file_get_contents("http://vk.com/share.php?act=count&index=1&url=$stream_url_encode")) {
            $substr =  str_replace("VK.Share.count(1, ", "", $result_vk_shares);
            $vk_shares  = intval($substr);
        }

        $update = $db -> sql_query("UPDATE `streams` SET `fb_shares_count` = '$fb_shares', `vk_shares_count` = '$vk_shares' WHERE `uuid` = '$stream_uuid'");
        if (!$update) {
            error_log("[$time] shares_counter: sql_error не выполнено обновление данных потока $stream_uuid\n", 3, $cron_log_file);
        }
    }
    error_log("[$time] shares_counter: данные обновлены\n", 3, $cron_log_file);

} else {
    error_log("[$time] shares_counter: не получен список потоков\n", 3, $cron_log_file);
}

?>