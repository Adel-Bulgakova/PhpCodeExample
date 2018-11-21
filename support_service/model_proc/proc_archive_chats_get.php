<?php
header("Content-Type: application/json; charset=utf-8");
$support_admin_id = $_SESSION["support_admin"];
global $db, $user;

$result = $db -> sql_query("SELECT `id`, `user_id`, `user_connect_time`, `admin_connect_time`, COUNT(`support_service_chats_msg`.`message`) AS `messages_count`, `closed_by` FROM `support_service_chats` LEFT JOIN `support_service_chats_msg` ON `support_service_chats`.`id` = `support_service_chats_msg`.`support_service_chat_id` WHERE `accepted_by_admin` ='$support_admin_id' AND `closed_by` != '' GROUP BY `id`", "", "array");

if (sizeof($result) > 0){
    $chats = array();

    foreach ($result as $value) {

        $chat = array();
        $chat_id = $value["id"];
        $chat_data = "<div class=\"chat_id\" data-chat-id=\"$chat_id\">Чат №$chat_id</div>";

        $user_id = $value["user_id"];
        $profile_name = $user -> profile_name($user_id);
        $profile_image = $user -> profile_image_html($user_id);
        $profile_info = "<div class=\"profile_info\">$profile_image<div class=\"profile_name\"><span style=\"text-decoration:none\">$profile_name</span></div></div>";

        $user_connect_time = date("d.m.Y H:i", $value["user_connect_time"]);
        $admin_connect_time = date("d.m.Y H:i", $value["admin_connect_time"]);
        $messages_count = $value["messages_count"];
        $closed_by = $value["closed_by"];

        array_push($chat, $chat_data, $profile_info, $user_connect_time, $admin_connect_time, $messages_count, $closed_by);
        array_push($chats, $chat);
    }
    echo json_encode($chats);
} else {
    //ошибка обработки
    echo json_encode("error");
}
?>