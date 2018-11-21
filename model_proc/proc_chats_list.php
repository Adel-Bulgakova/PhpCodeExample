<?php
header("Content-Type: application/text; charset=utf-8");
global $db, $log_file, $user, $users_chat;

$user_id = $_SESSION["uid"];
$ip = $user -> get_client_ip();
$time = date("H:i");
$current_date = time();

$current_user_chats_array = $users_chat -> user_chats($user_id);

if (sizeof($current_user_chats_array) > 0) {
    $html = "";
    foreach ($current_user_chats_array as $v) {
        $chat_id = $v["chat_id"];
        $members_data = $v["members_data"];
        $latest_message_data = $v["latest_message_data"];

        # Информация о последнем сообщении чата
        $latest_user_id = $latest_message_data["user_id"];
        $created_date = $latest_message_data["created_date"];
        $message = $latest_message_data["message"];
        $latest_author_name = $latest_message_data["name"];

        # Удаление данных о пользователе из массива всех участников чата, для которого отображается список чатов
        $members_data_id_only = array_column($members_data, "id");
        if (($key = array_search(intval($user_id), $members_data_id_only)) !== false) {
            unset($members_data[$key]);
        }

        $members_names_array = array();
        $members_id_array = array();
        # Перечисляем всех участников чата, если это беседа
        foreach ($members_data as $m) {
            $members_names_array[] = $m["name"];
            $members_id_array[] = $m["id"];
        }

        if (sizeof($members_data) > 1) {
            $member_display_name = implode(", ", $members_names_array);
            $member_id = "none"; # Для отображения стандартного изображения профиля в списке чатов
        } else {
            $member_id = $members_id_array[0]; # Для отображения изображения профиля в списке чатов
            $member_display_name = $members_names_array[0];
        }

        $latest_message_content = $message;
        if ($latest_user_id == $user_id) {
            $latest_message_content = " <div class=\"profile_info\"><div class=\"profile_image\" style=\"background: url('/api/v1/users/profile_image/$user_id') 100% 100% no-repeat;  background-size: cover;\"></div><div class=\"profile_name\"><span>$latest_author_name</span></div><div style=\"margin-left: 15px\">$message</div></div>";
        }

        $html .= "<div class=\"row chats_list_item\">
                    <div class=\"col-xs-10\">
                        <a href=\"/index.php?route=page_chat&chat=$chat_id\">
                            <div class=\"chat_item_profile_image\" style=\"background: url('/api/v1/users/profile_image/$member_id') 100% 100% no-repeat;  background-size: cover;\"></div>
                            <div class=\"chat_item_content\">
                                <div class=\"chat_item_profile_name\">$member_display_name</div>
                                <div class=\"chat_item_latest_message_content\">$latest_message_content</div>    
                            </div>
                        </a>
                        <div class=\"chat_delete_icon text-center\" data-chat-id=\"$chat_id\">
                            <img src=\"/assets/images/icons_svg/close_icon.svg\" style=\"width: 20px;\">
                        </div>                            
                    </div>
                 </div>";
    }

} else {
    $html = "
            <div class=\"row\">
                <div class=\"col-xs-10 text-center\">
                    <p>" . _NO_CHATS. "</p>
                </div>
            </div>";
}

echo $html;
?>