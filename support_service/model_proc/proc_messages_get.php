<?php
header("Content-Type: application/json; charset=utf-8");
global $db, $user;

$result = $db -> sql_query("SELECT `user_id`, `support_service_msg`.`email` AS `email_address`, `message`, `support_service_msg`.`created_date` AS `message_date` FROM `support_service_msg` LEFT JOIN `users` ON `support_service_msg`.`user_id` = `users`.`id` WHERE `users`.`is_blocked` = '0' AND `users`.`is_deleted` = '0'", "", "array");

if (sizeof($result) > 0){
    $messages = array();
    
    foreach ($result as $value) {

        $message_data = array();
        $user_id = $value["user_id"];
        $email = $value["email_address"];
        $message = $value["message"];
        $created_date = $value["message_date"];

        $profile_name = $user -> profile_name($user_id);
        $profile_image = $user -> profile_image_html($user_id);
        $profile_info = "<div class=\"profile_info\">$profile_image<div class=\"profile_name\"><span style=\"text-decoration:none\">$profile_name</span></div></div>";

        $created_date = date("d.m.Y H:i", $created_date);

        array_push($message_data, $profile_info, $message, $created_date, $email, '', '');
        array_push($messages, $message_data);
    }
    echo json_encode($messages);
} else {
    //ошибка обработки
    echo json_encode("error");
}
?>