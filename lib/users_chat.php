<?php
class users_chat {

    # Получение чатов пользователя с информацией о последнем сообщении
    function user_chats ($user_id = 0) {
        global $db;
        $response = array();

        # Поиск чатов текущего пользователя
        $result_chats = $db -> sql_query("SELECT
              `users_chats_members_link`.`chat_id` AS `chatId`, `etag_chat`, `user_id`,
              CASE  WHEN `users`.`name` != ''
                    THEN `users`.`name`
                    ELSE 'No name'
                    END AS `user_display_name`
              FROM `users_chats_members_link`              
              LEFT JOIN `users` ON `users_chats_members_link`.`user_id` = `users`.`id`
              LEFT JOIN `users_chats` ON `users_chats_members_link`.`chat_id` = `users_chats`.`chat_id`
              WHERE `users_chats`.`chat_id` IN (SELECT `chat_id` FROM `users_chats_members_link` WHERE `user_id` = '$user_id' AND `users_chats_members_link`.`is_deleted` = '0' AND `users_chats_members_link`.`is_chat_hidden` = '0' GROUP BY `chat_id`)
                  AND `users`.`is_deleted` = '0'
                  AND `users_chats`.`is_deleted` = '0'", "", "array");

        if (sizeof($result_chats) > 0) {

            $current_user_chats_array = array();

            # Создание массива чатов (с информацией о последнем сообщении чата) текущего пользователя
            foreach ($result_chats as $value) {
                $chat_id = $value["chatId"];
                $etag_chat = $value["etag_chat"];
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
                  AND `users_chats_actions_id` = '1' 
                  AND `users_chats_msg`.`message` != '' 
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
                $current_user_chats_array[$chat_id]["chat_id"] = $chat_id;
                $current_user_chats_array[$chat_id]["etag"] = $etag_chat;
                $current_user_chats_array[$chat_id]["members_data"][] = $member_data;
            }

            # Удаление ключей для получения неассоциативного массива чатов
            $response = array_values($current_user_chats_array);
            return $response;
        }

        return $response;
    }

    # Получение информации о каждом чате из массива id чатов пользователя
    function user_chats_array ($data, $user_id = 0) {
        global $db;
        $response = array();

        $chats_id_to_string = "'" . implode("','", $data) . "'";

        # Поиск чатов текущего пользователя
        $result_chats = $db -> sql_query("SELECT
              `users_chats_members_link`.`chat_id` AS `chatId`, `etag_chat`, `user_id`,
              CASE  WHEN `users`.`name` != ''
                    THEN `users`.`name`
                    ELSE 'No name'
                    END AS `user_display_name`
              FROM `users_chats_members_link`
              LEFT JOIN `users` ON `users_chats_members_link`.`user_id` = `users`.`id`
              LEFT JOIN `users_chats` ON `users_chats_members_link`.`chat_id` = `users_chats`.`chat_id`
              WHERE `users_chats`.`chat_id` IN (SELECT `chat_id` FROM `users_chats_members_link` WHERE `user_id` = '$user_id' AND `users_chats_members_link`.`is_deleted` = '0' AND `users_chats_members_link`.`is_chat_hidden` = '0' GROUP BY `chat_id`)
                  AND `users_chats`.`chat_id` IN (". $chats_id_to_string . ")
                  AND `users`.`is_deleted` = '0'
                  AND `users_chats`.`is_deleted` = '0'", "", "array");

        if (sizeof($result_chats) > 0) {

            $current_user_chats_array = array();

            # Создание массива чатов (с информацией о последнем сообщении чата) текущего пользователя
            foreach ($result_chats as $value) {
                $chat_id = $value["chatId"];
                $etag_chat = $value["etag_chat"];
                $member_id = $value["user_id"];
                $user_display_name = $value["user_display_name"];

                if (!isset($current_user_chats_array[$chat_id]["messages_data"])) {

                    $messages_data = $this -> users_chat_json_format($chat_id, $user_id);
                    $current_user_chats_array[$chat_id]["messages_data"] = array();

                    if ($messages_data["status"] = "OK") {
                        $current_user_chats_array[$chat_id]["messages_data"] = $messages_data["data"];
                    }
                }

                $member_data["id"] = $member_id;
                $member_data["name"] = $user_display_name;
                $current_user_chats_array[$chat_id]["chat_id"] = $chat_id;
                $current_user_chats_array[$chat_id]["etag"] = $etag_chat;
                $current_user_chats_array[$chat_id]["members_data"][] = $member_data;
            }

            # Удаление ключей для получения неассоциативного массива чатов
            $response = array_values($current_user_chats_array);
            return $response;
        }

        return $response;
    }

    # Получение id чатов пользователя и etag для каждого чата
    function user_chats_etags ($user_id = 0) {
        global $db;

        $response = array();

        $result_chats = $db -> sql_query("SELECT `users_chats`.`chat_id` AS `chatId`, `etag_chat` FROM `users_chats_members_link` LEFT JOIN `users_chats` ON `users_chats_members_link`.`chat_id` = `users_chats`.`chat_id` LEFT JOIN `users` ON `users_chats_members_link`.`user_id` = `users`.`id` WHERE `users_chats_members_link`.`user_id` = '$user_id' AND `users_chats`.`is_deleted` = '0'  AND `users`.`is_deleted` = '0'", "", "array");

        foreach ($result_chats as $value) {
            $chat_id = $value["chatId"];
            $etag_chat = $value["etag_chat"];

            $chat_data[0] = $chat_id;
            $chat_data[1] = $etag_chat;
            $response[] = $chat_data;
        }

        return $response;
    }

    # Получение сообщений существующего диалога(вызывается web версией)
    function user_chat_html_format($chat_id = 0, $user_id = 0) {
        global $user;
        $response["html"] = "";

        $res = $this -> users_chat_json_format($chat_id, $user_id);

        if ($res["status"] = "OK" AND sizeof($res["data"]) > 0) {

            $result_messages = $res["data"];

            $messages_by_day_array = array();
            foreach ($result_messages as $value){
                $msg_type =  $value["type"];

                $message_data["type"] = $msg_type;
                if ($msg_type == "msg") {
                    $message_data["user_id"] = $value["user_id"];
                    $message_data["message"] = $value["message"];
                } else if ($msg_type == "invite"){
                    $message_data["user_id"] = $value["invited_by_user_id"];
                    $message_data["invited_user_id"] = $value["invited_user_id"];
                }

                $msg_created_date = $value["msg_created_date"];
                $message_data["type"] = $msg_type;
                $message_data["profile_name"] = $value["profile_name"];
                $message_data["timestamp"] = $value["timestamp"];
                $messages_by_day_array[$msg_created_date][] = $message_data;
            }

            $lines = "";
            foreach ($messages_by_day_array as $key => $val){

                # для формирования даты группы сообщений получаем порядковый номер месяца
                $date_params_array = explode(".", $key);
                $month_name = get_month_name($date_params_array[1]);
                $msg_group_created_date = $date_params_array[0] . " " . $month_name ." " . $date_params_array[2];

                $lines .= "<div class=\"chat_date_line text-center\">$msg_group_created_date</div>";
                foreach ($val as $v) {

                    if ($v["type"] == "msg" AND $v["message"] != ""){
                        $userId = $v["user_id"];
                        $profile_name = $v["profile_name"];
                        $profile_image = $user -> profile_image_html($userId);

                        $message = $v["message"];
                        $msg_created_time = date("H:i", $v["timestamp"]);

                        $lines .= "<div class=\"line\"><div class=\"comment_autor_image\">$profile_image</div><div class=\"comment_message\"><span>$profile_name</span> $message</div><div class=\"msg_created_date\">$msg_created_time</div></div>";
                    }

                    if ($v["type"] == "invite"){
                        $profile_name = $v["profile_name"];

                        $invited_user_id = $v["invited_user_id"];
                        $invited_profile_name = $user -> profile_name($invited_user_id);
                        $lines .= "<div class=\"chat_date_line text-center\">" . $profile_name . " " . _INVITED_TO_CHAT ." ". $invited_profile_name . "</div>";
                    }

                }
            }

            $response["html"] = $lines;
            return $response;
        }

        $response["html"] = _NO_CHAT_MESSAGES;
        return $response;
    }

    # Получение сообщений существующего диалога (мобильная версия)
    function users_chat_json_format($chat_id = 0, $user_id = 0) {
        global $db, $user;

        $res = $db -> sql_query("SELECT `users_chats_members_link`.`user_id`, `last_hiding_chat_date` FROM `users_chats_members_link`               
            LEFT JOIN `users_chats` ON `users_chats_members_link`.`chat_id` = `users_chats`.`chat_id` 
            WHERE `users_chats_members_link`.`user_id` = '$user_id' 
                    AND `users_chats_members_link`.`chat_id` = '$chat_id' 
                    AND `users_chats`.`is_deleted` = '0' 
                    AND `users_chats_members_link`.`is_chat_hidden` = '0' ", "", "array");
        if (sizeof($res) > 0) {

            $last_hiding_chat_date = intval($res[0]["last_hiding_chat_date"]);

            $messages_array = array();

            $result_messages = $db -> sql_query("SELECT
              `message_id`, `users_chats_actions_id`, `message`, `users_chats_members_link`.`user_id` AS `user_id`, `users_chats_msg`.`created_date` AS `timestamp`, `invited_user_id`, `invited_by_user_id`,
              CASE  WHEN `users`.`name` != ''
                    THEN `users`.`name`
                    ELSE 'No name'
                    END AS `profile_name`,
                    CONCAT(DAY(FROM_UNIXTIME(`users_chats_msg`.`created_date`)), '.', MONTH (FROM_UNIXTIME(`users_chats_msg`.`created_date`)), '.', YEAR (FROM_UNIXTIME(`users_chats_msg`.`created_date`))) AS `msg_created_date`
              FROM `users_chats_msg`
              LEFT JOIN `users_chats_members_link` ON `users_chats_members_link`.`id` = `users_chats_msg`.`users_chats_members_link_id`
              LEFT JOIN `users` ON `users_chats_members_link`.`user_id` = `users`.`id`
              WHERE 
                  `users_chats_members_link`.`chat_id` = '$chat_id'
                  AND `users`.`is_deleted` = '0'
                  AND `users_chats_members_link`.`is_deleted` = '0'                  
                  AND `users_chats_msg`.`is_deleted` = '0' 
                  AND `users_chats_msg`.`created_date` > '$last_hiding_chat_date'
                  ORDER BY `timestamp` ASC", "", "array");

            foreach ($result_messages as $value){
                $msg_type = $value["users_chats_actions_id"];
                $return_msg = true;
                
                switch ($msg_type) {

                    case "1":
                        $message_data["type"] = "msg";
                        if ($value["message"] != "") {
                            $message_data["user_id"] = $value["user_id"];
                            $message_data["message"] = $value["message"];
                            $message_data["profile_name"] = $value["profile_name"];
                        } else {
                            $return_msg = false;
                        }
                        break;

                    case "2":
                        $message_data["type"] = "invite";
                        $message_data["user_id"] = $value["invited_by_user_id"];
                        $message_data["invited_user_id"] = $value["invited_user_id"];
                        $message_data["profile_name"] = $user -> profile_name($value["invited_by_user_id"]);
                        break;

                    case "3":
                        $message_data["type"] = "leave";
                        break;

                    default:
                        $return_msg = false;
                        break;

                }

                if ($return_msg) {
                    
                    $message_data["message_id"] = $value["message_id"];
                    $message_data["timestamp"] = $value["timestamp"];
                    $message_data["msg_created_date"] = $value["msg_created_date"];

                    $messages_array[] = $message_data;
                }

            }

            $response["status"] = "OK";
            $response["data"] = $messages_array;
            return $response;
        }

        $response["status"] = "ACCESS-DENIED";
        $response["message"] = "NOT-FOUND";
        return $response;
    }

    # Создание чата между пользователями или получение id существующего чата
    function create ($requester_user_id = 0, $invited_user_id = 0) {
        global $db, $user, $profile_image_query;

        $response = array();
        $requester_user_data["display_name"] = $user -> profile_name($requester_user_id);
        $requester_user_data["profile_image"] = $profile_image_query . $requester_user_id;

        $result_chats = $db -> sql_query("SELECT
              `users_chats_members_link`.`chat_id` AS `chatId`, `user_id`,
              CASE  WHEN `users`.`name` != ''
                    THEN `users`.`name`
                    ELSE 'No name'
                    END AS `user_display_name`
              FROM `users_chats_members_link`              
              LEFT JOIN `users` ON `users_chats_members_link`.`user_id` = `users`.`id`
              LEFT JOIN `users_chats` ON `users_chats_members_link`.`chat_id` = `users_chats`.`chat_id`
              WHERE `users_chats`.`chat_id` IN (SELECT `chat_id` FROM `users_chats_members_link` WHERE `user_id` = '$requester_user_id' GROUP BY `chat_id`)
                  AND `users_chats_members_link`.`user_id` != '$requester_user_id'
                  AND `users`.`is_deleted` = '0'
                  AND `users_chats`.`is_deleted` = '0'", "", "array");

        # Создание массива диалогов пользователя, в котором под ключом id хранится id чата, а под ключом members_data - массив id участников чата (кроме id пользователя, для которого получен массив)
        $current_user_chats_array = array();
        foreach ($result_chats as $value) {
            $chat_id = $value["chatId"];

            $member_data["id"] = $value["user_id"];
            $member_data["name"] = $value["user_display_name"];
            $current_user_chats_array[$chat_id]["members_data"][] = $member_data;
        }

        foreach ($current_user_chats_array as $k => $v) {
            # Определение наличие диалога между двумя пользователями
            if (sizeof($v["members_data"]) == 1 AND $v["members_data"][0]["id"] == $invited_user_id) {
                $response["status"] = "OK";
                $response["chat_id"] = $k;
                $response["user_data"] = $requester_user_data;
                return $response;
            }
        }

        $current_date = time();
        $etag = gen_uuid(12);

        # Создание нового диалога между пользователями
        $res_create_chat = $db -> sql_query("INSERT INTO `users_chats`(`chat_id`, `created_date`, `etag_chat`, `is_deleted`)  VALUES (NULL, '$current_date', '$etag', '0')");
        $chat_id = $db -> sql_nextid($res_create_chat);

        # Создание связи пользователей и id чата
        $db -> sql_query("INSERT INTO `users_chats_members_link`(`id`, `chat_id`, `user_id`, `invited_by_user_id`, `created_date`, `deleted_date`, `is_deleted`, `last_hiding_chat_date`, `is_chat_hidden`)  VALUES (NULL, '$chat_id', '$requester_user_id', '$requester_user_id', '$current_date', '0', '0', '0', '0')");
        $db -> sql_query("INSERT INTO `users_chats_members_link`(`id`, `chat_id`, `user_id`, `invited_by_user_id`, `created_date`, `deleted_date`, `is_deleted`, `last_hiding_chat_date`, `is_chat_hidden`)  VALUES (NULL, '$chat_id', '$invited_user_id', '$requester_user_id','$current_date', '0', '0', '0', '0')");

        $response["status"] = "OK";
        $response["chat_id"] = $chat_id;
        $response["user_data"] = $requester_user_data;
        return $response;
    }

    # Определение права доступа пользователя к чату (вызывается web-socket сервером)
    function connect($chat_id = 0, $user_id = 0) {
        global $db, $profile_image_query, $user;

        $ip = $user -> get_client_ip();
        $time = date("H:i");

        $result = $db -> sql_query("SELECT
              `users_chats_members_link`.`user_id`,
              CASE  WHEN `users`.`name` != ''
                    THEN `users`.`name`
                    ELSE 'No name'
                    END AS `display_name`,
                    CONCAT('$profile_image_query', `users_chats_members_link`.`user_id`) AS `profile_image`
              FROM `users_chats_members_link`            
              LEFT JOIN `users` ON `users_chats_members_link`.`user_id` = `users`.`id`
              LEFT JOIN `users_chats` ON `users_chats_members_link`.`chat_id` = `users_chats`.`chat_id`
              WHERE `users_chats`.`chat_id` = '$chat_id'
                  AND `users_chats_members_link`.`user_id` = '$user_id'
                  AND `users_chats_members_link`.`is_deleted` = '0'
                  AND `users`.`is_deleted` = '0'
                  AND `users_chats`.`is_deleted` = '0'", "", "array");

        if (sizeof($result) > 0) {
            $user_data["display_name"] = $result[0]["display_name"];
            $user_data["profile_image"] = $result[0]["profile_image"];

            $response["status"] = "OK";
            $response["user_data"] = $user_data;
            return $response;
        }

        $response["status"] = "ACCESS-DENIED";
        return $response;
    }

    # Добавление сообщения в чат между пользователями (вызывается web-socket сервером)
    function message_add($data = array(), $user_id = 0) {
        global $db, $log_file, $user;

        $ip = $user -> get_client_ip();
        $time = date("H:i");
        $current_date = time();

        $chat_id = $data["chat_id"];
        $message = $data["message"];
        $connected_users_id_array = $data["connected_users_id_array"];

//        $connected_users_id_to_string = "'" . implode("','", $connected_users_id_array) . "'";
//        error_log("[$time $ip] users_chat_message_add test: connected_users_id_to_string - $connected_users_id_to_string \n", 3, $log_file);

        $result_chat = $db -> sql_query("SELECT `users_chats_members_link`.`id` AS `member_link_id`, `users_chats_members_link`.`is_chat_hidden` FROM `users_chats` LEFT JOIN `users_chats_members_link` ON `users_chats`.`chat_id` = `users_chats_members_link`.`chat_id` LEFT JOIN `users` ON `users_chats_members_link`.`user_id` = `users`.`id` WHERE `users_chats`.`chat_id` = '$chat_id' AND `user_id` = '$user_id' AND `users_chats`.`is_deleted` = '0' AND `users`.`is_blocked` = '0' AND `users`.`is_deleted` = '0'", "", "array");

        if (sizeof($result_chat) > 0 AND !empty($message)) {

            $member_link_id = $result_chat[0]["member_link_id"];
            $is_chat_hidden = intval($result_chat[0]["is_chat_hidden"]);

            $result_message_add = $db -> sql_query("INSERT INTO `users_chats_msg`(`message_id`, `users_chats_actions_id`, `users_chats_members_link_id`, `message`, `invited_user_id`, `created_date`, `is_deleted`) VALUES (NULL, '1', '$member_link_id', '$message', '0', '$current_date', '0')");

            # Изменение статуса видимости чата для пользователя, если он был скрыт ранее (для дальнейшего отображения данного чата в списке всех чатов пользователя)
            if ($is_chat_hidden) {
                $db -> sql_query("UPDATE `users_chats_members_link` SET `is_chat_hidden` = '0' WHERE `chat_id` = '$chat_id' AND `user_id` = '$user_id'");
            }

            $message_id = $db -> sql_nextid($result_message_add);
            $this -> push_notification_chat_message($user_id, $chat_id, $message, $connected_users_id_array);
            $this -> etag_users_chat_update($chat_id);

            $response["status"] = "OK";
            $response["message_id"] = $message_id;
            $response["timestamp"] = $current_date;
            error_log("[$time $ip] users_chat_message_add: OK chat_id - $chat_id, user_id - $user_id, message - $message \n", 3, $log_file);
            ob_end_clean();
            return $response;

        }

        $response["status"] = "ERROR";
        error_log("[$time $ip] users_chat_message_add: ERROR не найден чат chat_id - $chat_id, user_id - $user_id, message - $message \n", 3, $log_file);
        return $response;
    }

    # "Удаление" чата. Если пользователь удалит чат, то он не будет отображаться в списке всех его чатов, но будет доступен его собеседникам. При начале переписки в этом чате будет вызван метод create, который вернет сущетсвующий chat_id, но история переспики не отобразится.
    function chat_hide ($chat_id = 0, $user_id = 0) {
        global $db;
        $response = array();

        $current_date = time();

        $res = $db -> sql_query("SELECT `users_chats_members_link`.`user_id` FROM `users_chats_members_link` LEFT JOIN `users_chats` ON `users_chats_members_link`.`chat_id` = `users_chats`.`chat_id` WHERE `users_chats_members_link`.`user_id` = '$user_id' AND `users_chats_members_link`.`chat_id` = '$chat_id' AND `users_chats`.`is_deleted` = '0'", "", "array");
        if (sizeof($res) > 0) {

            $db -> sql_query("UPDATE `users_chats_members_link` SET `last_hiding_chat_date` = '$current_date', `is_chat_hidden` = '1' WHERE `chat_id` = '$chat_id' AND `user_id` = '$user_id'");
            $this -> etag_users_chat_update($chat_id);

            $response["status"] = "OK";
            return $response;
        }

        $response["status"] = "ACCESS-DENIED";
        $response["message"] = "NOT-FOUND";
        return $response;
    }

    # Получение списка пользователей, которых можно пригласить в чат (взаимные подписчики, за исключением участников чата)
    function invite_users_list($chat_id = 0, $user_id = 0) {
        global $db, $user,$profile_image_query;
        $response = array();

        $mutual_following = $user -> mutual_following($user_id);

        $result_chat_users = $db -> sql_query("SELECT `user_id`
              FROM `users_chats_members_link`
              LEFT JOIN `users` ON `users_chats_members_link`.`user_id` = `users`.`id`
              LEFT JOIN `users_chats` ON `users_chats_members_link`.`chat_id` = `users_chats`.`chat_id`
              WHERE `users_chats`.`chat_id` IN (SELECT `chat_id` FROM `users_chats_members_link` WHERE `user_id` = '$user_id' AND `users_chats_members_link`.`is_deleted` = '0' AND `users_chats_members_link`.`is_chat_hidden` = '0' GROUP BY `chat_id`)
                  AND `users_chats_members_link`.`user_id` != '$user_id'
                  AND `users_chats_members_link`.`chat_id` = '$chat_id'
                  AND `users`.`is_deleted` = '0'
                  AND `users_chats`.`is_deleted` = '0'", "", "array");

        $current_chat_users = array();
        foreach ($result_chat_users as $user_data) {
            $current_chat_users[] = $user_data["user_id"];
        }
        
        $invite_users_list = array_diff($mutual_following, $current_chat_users);

        $invite_users_list_to_string = "'" . implode("','", $invite_users_list) . "'";
        
        $res = $db -> sql_query("SELECT `id`,
            CASE  WHEN `name` != ''
                  THEN `name`
                  ELSE 'No name'
                  END AS `display_name`,
            CONCAT('$profile_image_query', `id`) AS `profile_image`
            FROM `users` WHERE `id` IN (". $invite_users_list_to_string . ") AND `is_blocked` = '0' AND `is_deleted` = '0'", "", "array");

        $response["status"] = "OK";
        $response["data"] = $res;
        return $response;
    }

    # Добавление участника в беседу
    function users_chat_invite($data = array(), $requester_user_id = 0) {
        global $db, $log_file, $user;

        $ip = $user -> get_client_ip();
        $time = date("H:i");
        $current_date = time();

        $chat_id = $data["chat_id"];
        $invited_user_id = $data["invited_user_id"];

        $res_requester_user = $db -> sql_query("SELECT `users_chats_members_link`.`user_id` FROM `users_chats_members_link`               
            LEFT JOIN `users_chats` ON `users_chats_members_link`.`chat_id` = `users_chats`.`chat_id` 
            LEFT JOIN `users` ON `users_chats_members_link`.`user_id` = `users`.`id` 
            WHERE `users_chats_members_link`.`user_id` = '$requester_user_id' 
                    AND `users_chats_members_link`.`chat_id` = '$chat_id' 
                    AND `users_chats`.`is_deleted` = '0' 
                    AND `users_chats_members_link`.`is_chat_hidden` = '0'
                    AND `users`.`is_blocked` = '0' 
                    AND `users`.`is_deleted` = '0' ", "", "array");

        $res_invited_user  = $db -> sql_query("SELECT `users`.`id`, 
            CASE  WHEN `users`.`name` != ''
                    THEN `users`.`name`
                    ELSE 'No name'
                    END AS `invited_user_display_name` 
            FROM `users` WHERE `users`.`id` = '$invited_user_id' AND `users`.`is_blocked` = '0' AND `users`.`is_deleted` = '0' AND `id` NOT IN (SELECT `users_chats_members_link`.`user_id` FROM `users_chats_members_link`               
            LEFT JOIN `users_chats` ON `users_chats_members_link`.`chat_id` = `users_chats`.`chat_id` 
            WHERE `users_chats_members_link`.`user_id` = '$invited_user_id' 
                    AND `users_chats_members_link`.`chat_id` = '$chat_id' 
                    AND `users_chats`.`is_deleted` = '0')", "", "array");

        if (sizeof($res_requester_user) > 0 AND sizeof($res_invited_user) > 0) {

            $invited_user_display_name = $res_invited_user[0]["invited_user_display_name"];

            # Создание нового участника чата
            $member_link = $db -> sql_query("INSERT INTO `users_chats_members_link`(`id`, `chat_id`, `user_id`, `invited_by_user_id`, `created_date`, `deleted_date`, `is_deleted`, `last_hiding_chat_date`, `is_chat_hidden`)  VALUES (NULL, '$chat_id', '$invited_user_id', '$requester_user_id','$current_date', '0', '0', '0', '0')");
            $member_link_id = $db -> sql_nextid($member_link);

            $result_message_add = $db -> sql_query("INSERT INTO `users_chats_msg`(`message_id`, `users_chats_actions_id`, `users_chats_members_link_id`, `message`, `invited_user_id`, `created_date`, `is_deleted`) VALUES (NULL, '2','$member_link_id', '', '$invited_user_id', '$current_date', '0')");

            $message_id = $db -> sql_nextid($result_message_add);
            $this -> etag_users_chat_update($chat_id);
            $this -> push_notification_invite($requester_user_id, $invited_user_id, $chat_id);

            $response["status"] = "OK";
            $response["message_id"] = $message_id;
            $response["timestamp"] = $current_date;
            $response["invited_user_display_name"] = $invited_user_display_name;
            error_log("[$time $ip] users_chat_invite: OK chat_id - $chat_id, requester_user_id - $requester_user_id, invited_user_id - $invited_user_id \n", 3, $log_file);
            return $response;

        }

        $response["status"] = "ERROR";
        error_log("[$time $ip] users_chat_invite: ERROR не найден чат chat_id - $chat_id, requester_user_id - $requester_user_id, invited_user_id - $invited_user_id \n", 3, $log_file);
        return $response;
    }
    
    # Метод создания массивов токенов устройств пользователя (для ios, для android) для дальнейшей отправки push уведомления пользователю о новом сообщении чата
    function push_notification_chat_message($requester_user_id = 0, $chat_id = 0, $text = "", $connected_users_id_array) {
        global $db, $log_file, $ios_push_log_file, $android_push_log_file, $user, $stream;

        $time = date("H:i");
        if (user($requester_user_id) AND $text != "") {

            # Список id пользователей, которые были подключены к вебсокету в момент отправки сообщения и соотвественно прочитавшие его
            $connected_users_id_to_string = "'" . implode("','", $connected_users_id_array) . "'";

            $result_devices = $db -> sql_query("SELECT 
                    `device_token`, `operating_system`
              FROM `users_chats_members_link`
              LEFT JOIN `users` ON `users_chats_members_link`.`user_id` = `users`.`id`
              LEFT JOIN `devices` ON `users_chats_members_link`.`user_id` = `devices`.`user_id`
              LEFT JOIN `users_sessions_devices` ON `devices`.`id` = `users_sessions_devices`.`device_id`
              LEFT JOIN `users_chats` ON `users_chats_members_link`.`chat_id` = `users_chats`.`chat_id`
              WHERE `users_chats`.`chat_id` IN (SELECT `chat_id` FROM `users_chats_members_link` WHERE `user_id` = '$requester_user_id' AND `users_chats_members_link`.`is_deleted` = '0' AND `users_chats_members_link`.`is_chat_hidden` = '0' GROUP BY `chat_id`)
                  AND `users_chats`.`chat_id` = '$chat_id'
                  AND `users_chats_members_link`.`user_id` != '$requester_user_id'
                  AND `users_chats_members_link`.`user_id` NOT IN (". $connected_users_id_to_string . ")
                  AND `users`.`is_deleted` = '0'
                  AND `users_chats`.`is_deleted` = '0'
                  AND `devices`.`device_token_is_correct` = '1'
                  AND `devices`.`is_blocked` = '0'
                  AND `devices`.`is_deleted` = '0'
                  AND `users_sessions_devices`.`end_date` = '0'", "", "array");

            if (sizeof($result_devices) > 0) {

                $ios_tokens = array();
                $android_tokens = array();
                $user_name = $user -> profile_name($requester_user_id);

                foreach ($result_devices as $v) {
                    $device_token = $v["device_token"];
                    $operating_system = $v["operating_system"];

                    # Определение операционной системы устройства польователя
                    if (preg_match("/iOS/i",$operating_system)){
                        $ios_tokens[] = $device_token;
                    } else if (preg_match("/Android/i",$operating_system)) {
                        $android_tokens[] = $device_token;
                    }
                }

                if (sizeof($ios_tokens) > 0){
                    $custom_properties = array(
                        "act" => "4",
                        "chat_id" => $chat_id,
                        "user_name" => $user_name,
                        "user_id" => $requester_user_id
                    );
                    try {
                        $stream -> ios_send_push($ios_tokens, $text, $custom_properties);
                    } catch (Exception $e) {
                        error_log("[$time] push_notification_chat_message: EXCEPTION requester_user_id - $requester_user_id, chat_id - $chat_id, message - $text " . $e->getMessage(). "\n", 3, $ios_push_log_file);
                    } finally {
                        $ios_tokens_for_log = implode(', ', $ios_tokens);
                        error_log("[$time] push_notification_chat_message: requester_user_id - $requester_user_id, chat_id - $chat_id, message - $text, ios_tokens: $ios_tokens_for_log\n", 3, $ios_push_log_file);
                    }
                }

                if (sizeof($android_tokens) > 0){
                    $text = array(
                        "act" => "4",
                        "title" => $user_name,
                        "chat_id" => $chat_id,
                        "text" => $text,
                        "user_id" => $requester_user_id
                    );

                    $stream -> android_send_push($android_tokens, $text);
                    $android_tokens_for_log = implode(', ', $android_tokens);
                    error_log("[$time] push_notification_chat_message: requester_user_id - $requester_user_id, chat_id - $chat_id, message - $text, android_tokens: $android_tokens_for_log\n", 3, $android_push_log_file);
                }

            } else {
                error_log("[$time] push_notification_chat_message: requester_user_id - $requester_user_id, chat_id - $chat_id, message - $text,  не найдены устройства пользователей\n", 3, $log_file);
            }
        } else {
            error_log("[$time] push_notification_chat_message: NOT-FOUND requester_user_id - $requester_user_id, chat_id - $chat_id, message - $text\n", 3, $log_file);
        }
    }

    # Метод создания массивов токенов устройств пользователя (для ios, для android) для дальнейшей отправки push уведомления пользователю о приглашении в беседу
    function push_notification_invite($requester_user_id = 0, $invited_user_id = 0, $chat_id = 0) {
        global $db, $log_file, $ios_push_log_file, $android_push_log_file, $user, $stream;

        $time = date("H:i");
        if (user($requester_user_id) AND user($invited_user_id)) {

            $result_devices = $db -> sql_query("SELECT * FROM `devices` LEFT JOIN `users_sessions_devices` ON `devices`.`id` = `users_sessions_devices`.`device_id` WHERE `devices`.`user_id` = '$invited_user_id' AND `devices`.`device_token_is_correct` = '1' AND `devices`.`is_blocked` = '0' AND `devices`.`is_deleted` = '0' AND `users_sessions_devices`.`end_date` = '0'", "", "array");

            if (sizeof($result_devices) > 0) {

                $ios_tokens = array();
                $android_tokens = array();
                $requester_user_name = $user -> profile_name($requester_user_id);
                $text = $requester_user_name." пригласил Вас в беседу";

                # Если у пользователя несколько устройств
                foreach ($result_devices as $v) {
                    $device_token = $v["device_token"];
                    $operating_system = $v["operating_system"];
                    #Определение операционной системы устройства польователя и доступности токена устройства
                    if (preg_match("/iOS/i",$operating_system)){
                        $ios_tokens[] = $device_token;
                    } else if (preg_match("/Android/i",$operating_system)) {
                        $android_tokens[] = $device_token;
                    }
                }

                if (sizeof($ios_tokens) > 0){
                    $custom_properties = array(
                        "act" => "3",
                        "chat_id" => $chat_id,
                        "user_id" => $requester_user_id
                    );
                    try {
                        $stream -> ios_send_push($ios_tokens, $text, $custom_properties);
                    } catch (Exception $e) {
                        error_log("[$time] push_notification_invite: EXCEPTION requester_user_id - $requester_user_id, invited_user_id - $invited_user_id,  chat_id - $chat_id" . $e->getMessage(). "\n", 3, $ios_push_log_file);
                    } finally {
                        $ios_tokens_for_log = implode(', ', $ios_tokens);
                        error_log("[$time] push_notification_invite: requester_user_id - $requester_user_id, invited_user_id - $invited_user_id,  chat_id - $chat_id, ios_tokens: $ios_tokens_for_log\n", 3, $ios_push_log_file);
                    }
                }

                if (sizeof($android_tokens) > 0){
                    $text = array(
                        "act" => "3",
                        "title" => $text,
                        "chat_id" => $chat_id,
                        "user_id" => $requester_user_id
                    );

                    $stream -> android_send_push($android_tokens, $text);
                    $android_tokens_for_log = implode(', ', $android_tokens);
                    error_log("[$time] push_notification_invite: requester_user_id - $requester_user_id, invited_user_id - $invited_user_id,  chat_id - $chat_id android_tokens: $android_tokens_for_log\n", 3, $android_push_log_file);
                }

            } else {
                error_log("[$time] push_notification_invite: requester_user_id - $requester_user_id, invited_user_id - $invited_user_id,  chat_id - $chat_id не найдены устройства пользователя\n", 3, $log_file);
            }
        } else {
            error_log("[$time] push_notification_invite: не найден requester_user_id - $requester_user_id, invited_user_id - $invited_user_id,  chat_id - $chat_id\n", 3, $log_file);
        }
    }

    # Метод обновления etag при измении объекта чат
    function etag_users_chat_update($chat_id = 0){
        global $db;
        $etag_users_chat = gen_uuid(12);
        $db -> sql_query("UPDATE `users_chats` SET `etag_chat` = '$etag_users_chat' WHERE `chat_id` = '$chat_id'");
    }

}