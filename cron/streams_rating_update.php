<?php
# Обновление рейтинга трансляций
require_once "/www/config.php";
require_once "/www/function.php";

global $db;
$cron_log_file = "/www/logs/cron_".date("dmY").".log";
$time = date("H:i");

# Критерии отбора в ТОП по удельному весу каждого параметра: TODO(обсудить критерии)
$streams_views_priority = 0.6; # Количество просмотров - 60%
$streams_likes_priority = 0.3; # Количество лайков - 30%
$followers_count_priority = 0.1; # Количество подписчиков - 10%

$result = $db -> sql_query("SELECT `id` AS `streamID`, `user_id` AS `userID`, ROUND(((SELECT COUNT(`id`) FROM `users_actions_log` WHERE `hero_id` = `userID` AND `users_actions_id` = '3')*'$followers_count_priority' + (SELECT COUNT(`streams_clients`.`id`) FROM `streams_clients` WHERE `streams_clients`.`stream_id` = `streamID`)*'$streams_views_priority' + (SELECT COUNT(`id`) FROM `users_actions_log` WHERE `stream_id` = `streamID` AND `users_actions_id` = '1')*'$streams_likes_priority'), 0) AS `rating` FROM `streams` WHERE `is_excess` = '0' AND `is_blocked` = '0' AND `is_deleted` = '0'", "", "array");

foreach ($result as $value) {
    $stream_id = $value["streamID"];
    $rating = $value["rating"];
    $db -> sql_query("UPDATE `streams` SET `rating` = '$rating' WHERE `id` = '$stream_id'");
}

error_log("[$time] users_rating_update\n", 3, $cron_log_file);
?>