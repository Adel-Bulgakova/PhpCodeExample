<?php
# Обновление рейтинга пользователей
require_once "/www/config.php";
require_once "/www/function.php";

global $db;
$cron_log_file = "/www/logs/cron_".date("dmY").".log";
$time = date("H:i");

$followers_count_priority = 0.6;
$streams_views_priority = 0.2;
$streams_likes_priority = 0.2;

$result = $db -> sql_query("SELECT `id` AS `userID`, ROUND(((SELECT COUNT(`id`) FROM `users_actions_log` WHERE `hero_id` = `userID` AND `users_actions_id` = '3')*'$followers_count_priority' + (SELECT COUNT(`streams_clients`.`id`) FROM `streams` LEFT JOIN `streams_clients` ON `streams`.`id` = `streams_clients`.`stream_id` WHERE `streams`.`user_id` = `userID` AND `streams`.`is_excess` = '0' AND `streams`.`is_deleted` = '0')*'$streams_views_priority' + (SELECT COUNT(`id`) FROM `users_actions_log` WHERE `hero_id` = `userID` AND `users_actions_id` = '1')*'$streams_likes_priority'), 0) AS `rating` FROM `users` WHERE `is_blocked` = '0' AND `is_deleted` = '0'", "", "array");

foreach ($result as $value) {
    $user_id = $value["userID"];
    $rating = $value["rating"];
    $db -> sql_query("UPDATE `users` SET `rating` = '$rating' WHERE `id` = '$user_id'");
}

error_log("[$time] users_rating_update\n", 3, $cron_log_file);
?>