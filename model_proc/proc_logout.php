<?php
global $db, $log_file, $user;

$session_id = $_SESSION["web_session_id"];
$ip = $user -> get_client_ip();
$time = date("H:i");
$current_date = time();

$db -> sql_query("UPDATE `users_sessions_web` SET `end_date` = '$current_date' WHERE `session_id` = '$session_id'");
error_log("[$time $ip] close_session_web: закрыта сессия session_id - $session_id \n", 3, $log_file);

session_unset();
session_destroy();
header('Location: /');
?>