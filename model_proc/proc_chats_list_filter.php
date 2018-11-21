<?php
header("Content-Type: application/text; charset=utf-8");
global $db, $log_file, $user;

$user_id = $_SESSION["uid"];
$ip = $user -> get_client_ip();
$time = date("H:i");
$current_date = time();

$query = "";
if (isset($_POST["query"]) AND strlen($_POST["query"]) > 1) {
    $query = $_POST["query"];
}

# Поиск чатов текущего пользователя
$result_chats = $db -> sql_query("SELECT
              `users_chats_members_link`.`chat_id` AS `chatId`, `user_id`,
              CASE  WHEN `users`.`name` != ''
                    THEN `users`.`name`
                    ELSE 'No name'
                    END AS `user_display_name`
              FROM `users_chats_members_link`              
              LEFT JOIN `users` ON `users_chats_members_link`.`user_id` = `users`.`id`
              LEFT JOIN `users_chats` ON `users_chats_members_link`.`chat_id` = `users_chats`.`chat_id`
              WHERE `users_chats`.`chat_id` IN (SELECT `chat_id` FROM `users_chats_members_link` WHERE `user_id` = '$user_id' AND `users_chats_members_link`.`is_deleted` = '0' AND `users_chats_members_link`.`is_chat_hidden` = '0' GROUP BY `chat_id`)
                  AND `users_chats`.`chat_id` IN (SELECT `chat_id` FROM `users_chats_members_link` LEFT JOIN `users` ON `users_chats_members_link`.`user_id` = `users`.`id` WHERE `users`.`name` LIKE '%" . $query . "%' AND `users_chats_members_link`.`user_id` != '$user_id' AND `users_chats_members_link`.`is_deleted` = '0' AND `users_chats_members_link`.`is_chat_hidden` = '0' GROUP BY `chat_id`)
                  AND `users_chats_members_link`.`user_id` != '$user_id'
                  AND `users`.`is_deleted` = '0'
                  AND `users_chats`.`is_deleted` = '0'", "", "array");

$current_user_chats_array = array();

# Создание массива чатов (с информацией о последнем сообщении чата) текущего пользователя
foreach ($result_chats as $value) {
    $chat_id = $value["chatId"];
    $member_id = $value["user_id"];
    $user_display_name = $value["user_display_name"];

    if (!isset($current_user_chats_array[$chat_id]["latest_message_data"])) {

        # Получение последнего сообщения чата
        $result_latest_message = $db -> sql_query("SELECT
              `message`, `users_chats_members_link`.`user_id` AS `latest_msg_user_id`, `users_chats_msg`.`created_date` AS `latest_msg_created_date`,
              CASE  WHEN `users`.`name` != ''
                    THEN `users`.`name`
                    ELSE 'No name'
                    END AS `user_display_name`
              FROM `users_chats_msg`
              LEFT JOIN `users_chats_members_link` ON `users_chats_members_link`.`id` = `users_chats_msg`.`users_chats_members_link_id`
              LEFT JOIN `users` ON `users_chats_members_link`.`user_id` = `users`.`id`
              WHERE 
                  `users_chats_members_link`.`chat_id` = '$chat_id'
                  AND `users_chats_msg`.`is_deleted` = '0' 
                  AND `users`.`is_deleted` = '0'
                  ORDER BY `message_id` DESC", "", "array");

        if (sizeof($result_latest_message) == 0) {
            # Если нет сообщений, данный чат отображаться не будет в списке всех чатов
            continue;
        }

        $latest_message_data["user_id"] = $result_latest_message[0]["latest_msg_user_id"];
        $latest_message_data["created_date"] = $result_latest_message[0]["latest_msg_created_date"];
        $latest_message_data["message"] = $result_latest_message[0]["message"];
        $latest_message_data["name"] = $result_latest_message[0]["user_display_name"];

        $current_user_chats_array[$chat_id]["latest_message_data"] = $latest_message_data;
    }

    $member_data["id"] = $member_id;
    $member_data["name"] = $user_display_name;
    $current_user_chats_array[$chat_id]["members_data"][] = $member_data;
}

$html = "";

# Массив id пользователей, с которыми есть взаимная подписка
$mutual_following_id_array = $user -> mutual_following($user_id);

if (sizeof($current_user_chats_array) > 0) {
    foreach ($current_user_chats_array as $k => $v) {
        $members_data = $v["members_data"];
        $latest_message_data = $v["latest_message_data"];

        # Информация о последнем сообщении чата
        $latest_user_id = $latest_message_data["user_id"];
        $created_date = $latest_message_data["created_date"];
        $message = $latest_message_data["message"];
        $latest_author_name = $latest_message_data["name"];

        if (sizeof($members_data) > 1) {
            $members_names_array = array();
            # Перечисляем всех участников чата, если это беседа
            foreach ($members_data as $m) {
                $members_names_array[] = $m["name"];
            }

            $member_display_name = implode(", ", $members_names_array);
            $member_id = "none"; # Для получения стандартного изображения профиля в списке чатов (для беседы)

        } else {

            $member_id = $members_data[0]["id"]; # Для получения изображения профиля в списке чатов
            $member_display_name = $members_data[0]["name"];

            #  Удаление пользователя из массива id взаимных подписчиков, так как чат между ними уже существует
            if (($key = array_search($member_id, $mutual_following_id_array)) !== false) {
                unset($mutual_following_id_array[$key]);
            }
        }

        $latest_message_content = $message;
        if ($latest_user_id == $user_id) {
            $latest_message_content = " <div class=\"profile_info\"><div class=\"profile_image\" style=\"background: url('/api/v1/users/profile_image/$user_id') 100% 100% no-repeat;  background-size: cover;\"></div><div class=\"profile_name\"><span>$latest_author_name</span></div><div style=\"margin-left: 15px\">$message</div></div>";
        }

        $html .= "<div class=\"row chats_list_item\">
                    <div class=\"col-xs-10\">
                        <a href=\"/index.php?route=page_chat&chat=$k\">
                            <div class=\"chat_item_profile_image\" style=\"background: url('/api/v1/users/profile_image/$member_id') 100% 100% no-repeat;  background-size: cover;\"></div>
                            <div class=\"chat_item_content\">
                                <div class=\"chat_item_profile_name\">$member_display_name</div>
                                <div class=\"chat_item_latest_message_content\">$latest_message_content</div>    
                            </div>
                        </a>
                        <div class=\"chat_delete_icon text-center\" data-chat-id=\"$k\">
                            <i class=\"fa fa-remove\"></i>
                        </div>                            
                    </div>
                 </div>";
    }

}

if (sizeof($mutual_following_id_array) > 0) {

    $recommended_users_list_html = "";
    # Отображение списка взаимных подписчиков, найденных по поисковой фразе, с которыми можно начать переписку
    foreach ($mutual_following_id_array as $recommended_user_id) {
        $match_res = $db -> sql_query("SELECT `name` FROM `users` WHERE `name` LIKE '%" . $query . "%' AND `id` = '$recommended_user_id' AND `is_deleted` = '0' LIMIT 1", "", "array");

        if (sizeof($match_res) > 0) {

            $recommended_user_name = $match_res[0]["name"];

            $recommended_users_list_html .= "<div class=\"row chats_list_item\">
                        <div class=\"col-xs-10\">
                            <a href=\"/index.php?route=page_chat&user=$recommended_user_id\">
                                <div class=\"chat_item_profile_image\" style=\"background: url('/api/v1/users/profile_image/$recommended_user_id') 100% 100% no-repeat;  background-size: cover;\"></div>
                                <div class=\"chat_item_content\">
                                    <div class=\"chat_item_profile_name\">$recommended_user_name</div>
                                    <div class=\"chat_item_latest_message_content\">" . _START_CHAT . " </div>    
                                </div>
                            </a>                      
                        </div>
                     </div>";
        }
    }

    if ($recommended_users_list_html != "") {
        $html .= "<div class=\"row chats_list_group_header\">
                    <div class=\"col-xs-10\">
                      " . _RECOMMENDED_USERS . "                      
                    </div>
                 </div>";
        $html .= $recommended_users_list_html;
    }
    
}

if ($html == "") {
    $html = "
            <div class=\"row\">
                <div class=\"col-xs-10 text-center\">
                    <p>" . _NO_CHATS . "</p>
                </div>
            </div>";
}

echo $html;
?>