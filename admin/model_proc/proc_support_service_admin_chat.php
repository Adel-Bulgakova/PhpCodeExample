<?php
global $db, $user;
$chat_id = $_POST["chat_id"];
$result = $db -> sql_query("SELECT * FROM `support_service_chats_msg` LEFT JOIN `support_service_chats` ON `support_service_chats_msg`.`support_service_chat_id` = `support_service_chats`.`id` WHERE `support_service_chat_id` = '$chat_id' AND `message` != ''", "", "array");

if (sizeof($result) > 0){
    echo "<div class=\"support_chat_wrapper\"><div class=\"support_chat_messages\">";

    foreach ($result as $value) {

        $message = $value["message"];
        $created_date = date("d.m.Y H:i", $value["created_date"]);

        if ($value["message_by"] == "admin") {
            $admin_id = $value["accepted_by_admin"];
            $admin_data = json_decode(file_get_contents("https://$_SERVER[HTTP_HOST]/api/v1/support_service/admin/$admin_id"), true);
            $admin_profile_image = $admin_data["data"]["profile_image"];
            $admin_display_name = $admin_data["data"]["display_name"];
            echo "<div class=\"line\"><div class=\"comment_autor_image\"><div class=\"profile_image\" style=\"background: url($admin_profile_image) 100% 100% no-repeat;  background-size: cover;\"></div></div><div class=\"comment_message\" style=\"position:relative; padding-right:15px; min-width: 75%\"><span>" . $admin_display_name . "</span>" . $message . "</div><span style=\"position:absolute; right:0; margin-left: 5px\">$created_date</span></div>";
        } else {
            $user_id = $value["user_id"];

            $profile_name = $user -> profile_name($user_id);
            $profile_image = $user -> profile_image_html($user_id);
            echo "<div class=\"line\"><div class=\"comment_autor_image\">$profile_image</div><div class=\"comment_message\" style=\"position:relative; padding-right:15px; min-width: 75%\"><span>" . $profile_name . "</span>" . $message . "</div><span style=\"position:absolute; right:0; margin-left: 5px\">$created_date</span></div>";
        }
    }

    echo "</div></div>";
} else {
    echo 'No messages';
}
?>