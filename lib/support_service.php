<?php

class support_service {

    # Выход админа из учетной записи
    function support_service_admin_logout () {
        session_unset();
        session_destroy();
        header('Location: /');
    }

    # Возвращает список администраторов тех.поддержки со статусом их активности
    function admins_status($current_request_type) {
        global $db;

        $client_request_access = client_request_access_status($current_request_type);
        if ($client_request_access["status"] == "OK") {

            $response["admins_list"] = array();
            $response["online"] = array();
            $response["offline"] = array();
            $result = $db -> sql_query("SELECT `support_service_admins`.`id` AS `adminID`,
 CASE WHEN (SELECT `id` FROM `users_sessions_support_admins` WHERE `admin_id` = `adminID` AND `end_date` = '0')
      THEN '1'
      ELSE '0'
      END AS `is_online`
 FROM `support_service_admins` WHERE `support_service_admins`.`is_deleted` = '0'", "", "array");

            if (sizeof($result) > 0) {
                foreach ($result as $value) {
                    $response["admins_list"][] = $value["adminID"];
                    if ($value["is_online"] == '1') {
                        $response["online"][] = $value["adminID"];
                    } else {
                        $response["offline"][] = $value["adminID"];
                    }
                }
            }
            return $response;
        } else {
            return $client_request_access;
        }
    }
    
    # Информация об администраторе службы поддержки
    function get_admin_data($admin_id) {
        global $db, $log_file, $user;

        $ip = $user -> get_client_ip();
        $time = date("H:i");
        $result = $db -> sql_query("SELECT `id`, `login`, `name`, `email`, `comments`,
                                  CASE WHEN (SELECT `id` FROM `users_sessions_support_admins` WHERE `admin_id` = `id` AND `end_date` = '0')
                                      THEN '1'
                                      ELSE '0'
                                      END AS `is_online` 
                                  FROM `support_service_admins` WHERE `id` = '$admin_id' AND `is_deleted` = '0'", "", "array");

        if (sizeof($result) > 0) {
            $response["status"] = "OK";
            $response["data"]["id"] = $result[0]["id"];
            $response["data"]["login"] = $result[0]["login"];
            $response["data"]["display_name"] = $result[0]["name"];
            $response["data"]["profile_image"] = "https://$_SERVER[HTTP_HOST]/assets/images/default_profile.jpg";
            $response["data"]["email"] = $result[0]["email"];
            $response["data"]["comments"] = $result[0]["comments"];
            $response["data"]["is_online"] = $result[0]["is_online"];
        } else {
            $response["status"] = "ERROR";
            error_log("[$time $ip] support_service: ERROR не найден админ тех.поддержки admin_id - $admin_id \n", 3, $log_file);
        }
        return $response;
    }
    
    # Добавление сообщения в чат со службой поддержки
    function add_message($data, $current_request_type) {
        global $db, $log_file, $user;

        $client_request_access = client_request_access_status($current_request_type);
        if ($client_request_access["status"] == "OK") {
            $ip = $user -> get_client_ip();
            $time = date("H:i");
            $current_date = time();

            $client_status = $client_request_access["client_status"];
            $add_message = 1;
            if ($client_status == "admin") {
                $client_id = $client_request_access["admin_id"];
            } else if ($client_status == "user"){
                $client_id = $client_request_access["user_id"];
            } else {
                $add_message = 0;
            }

            $chat_id = $data["chat_id"];
            $message = $data["message"];

            if ($add_message == 1) {
                $result_chat = $db -> sql_query("SELECT * FROM `support_service_chats` WHERE `id` = '$chat_id'", "", "array");
                if (sizeof($result_chat) > 0) {

                    $db -> sql_query("INSERT INTO `support_service_chats_msg`(`support_service_chat_id`, `message_by`, `message`, `created_date`) VALUES ('$chat_id', '$client_status', '$message', '$current_date')");
                    $response["status"] = "OK";
                    error_log("[$time $ip] add_message: OK chat_id - $chat_id, client_status - $client_status, client_id - $client_id, message - $message \n", 3, $log_file);

                } else {
                    $response["status"] = "ERROR";
                    error_log("[$time $ip] add_message: ERROR не найден чат chat_id chat_id - $chat_id, client_status - $client_status, client_id - $client_id, message - $message \n", 3, $log_file);
                }

            } else {
                $response["status"] = "ERROR";
                error_log("[$time $ip] add_message: ERROR chat_id - $chat_id, client_status - $client_status, client_id - $client_id, message - $message\n", 3, $log_file);
            }

            return $response;
        } else {
            return $client_request_access;
        }        
    }
    
    # Изменение статуса подключения пользователя или администратора к чату
    function client_connect_to_chat_state($data, $current_request_type) {
        global $db, $log_file, $user;

        $client_request_access = client_request_access_status($current_request_type);
        if ($client_request_access["status"] == "OK") {
            $ip = $user -> get_client_ip();
            $time = date("H:i");
            $current_date = time();

            $client_connect_state = $data["client_connect_state"];
            $client_status = $data["client_status"];
            $client_id = $data["client_id"];

            if ($client_connect_state == "connect") {
                if ($client_status == "user") {
                    $result_old_chat = $db -> sql_query("SELECT `id` FROM `support_service_chats` WHERE `user_id` = '$client_id' AND `user_disconnect_time` = '0'", "", "array");
                    # Обработка ошибки закрытия предыдущего
                    if (sizeof($result_old_chat) > 0) {
                        $chat_id = $result_old_chat[0]["id"];
                        $db -> sql_query("UPDATE `support_service_chats` SET `user_disconnect_time` = '$current_date' WHERE `id` = '$chat_id'");
                        error_log("[$time $ip] support_service: $client_connect_state принудительно закрыт чат chat_id = $chat_id \n", 3, $log_file);
                    }

                    if (user($client_id)) {
                        # Создание нового чата
                        $res_open_chat = $db -> sql_query("INSERT INTO `support_service_chats`(`id`, `user_id`, `user_connect_time`, `user_disconnect_time`, `accepted_by_admin`, `admin_connect_time`, `admin_disconnect_time`) VALUES (NULL, '$client_id', '$current_date', '0', '0', '0', '0')");
                        $chat_id = $db -> sql_nextid($res_open_chat);
                        $response["status"] = "OK";
                        $response["chat_id"] = $chat_id;
                        error_log("[$time $ip] support_service: OK $client_connect_state пользователем открыт чат chat_id = $chat_id, user_id - $client_id \n", 3, $log_file);
                    } else {
                        $response["status"] = "ERROR";
                        error_log("[$time $ip] support_service: ERROR $client_connect_state не найден пользователь user_id - $client_id \n", 3, $log_file);
                    }

                } else if ($client_status == "admin"){
                    $chat_id = $data["chat_id"];

                    if (admin($client_id)) {
                        $result_chat = $db -> sql_query("SELECT * FROM `support_service_chats` WHERE `id` = '$chat_id'", "", "array");
                        if (sizeof($result_chat) > 0) {
                            # Администратор тех.поддержки принял чат с пользователем, обновление
                            $db -> sql_query("UPDATE `support_service_chats` SET `accepted_by_admin` = '$client_id', `admin_connect_time` = '$current_date' WHERE `id` = '$chat_id'");
                            $response["status"] = "OK";
                            error_log("[$time $ip] support_service: OK $client_connect_state пользователем открыт чат chat_id = $chat_id, user_id - $client_id \n", 3, $log_file);
                        } else {
                            $response["status"] = "ERROR";
                            error_log("[$time $ip] support_service: ERROR $client_connect_state не найден чат chat_id = $chat_id, admin_id - $client_id \n", 3, $log_file);
                        }
                    } else {
                        $response["status"] = "ERROR";
                        error_log("[$time $ip] support_service: ERROR $client_connect_state не найден admin admin_id - $client_id,  chat_id = $chat_id \n", 3, $log_file);
                    }
                } else {
                    $response["status"] = "ERROR";
                    error_log("[$time $ip] support_service: $client_connect_state ERROR client_status - $client_status\n", 3, $log_file);
                }
            } else if ($client_connect_state == "disconnect") {
                $chat_id = $data["chat_id"];

                if ($client_status == "user") {
                    if (user($client_id)) {
                        #Закрытие подключения пользователя к чату
                        $result_chat = $db -> sql_query("SELECT * FROM `support_service_chats` WHERE `id` = '$chat_id' AND `user_id` = '$client_id' AND `user_disconnect_time` = '0' ", "", "array");
                        if (sizeof($result_chat) > 0 AND $result_chat[0] != 0 ) {

                            $db -> sql_query("UPDATE `support_service_chats` SET `user_disconnect_time` = '$current_date', `closed_by` = 'user' WHERE `id` = '$chat_id' AND `user_id` = '$client_id'");
                            $response["status"] = "OK";
                            error_log("[$time $ip] support_service: OK $client_connect_state user_id - $client_id, chat_id - $chat_id  \n", 3, $log_file);

                        } else {
                            $response["status"] = "ERROR";
                            error_log("[$time $ip] support_service: ERROR $client_connect_state не найден чат user_id - $client_id, chat_id - $chat_id \n", 3, $log_file);
                        }

                    } else {
                        $response["status"] = "ERROR";
                        error_log("[$time $ip] support_service: ERROR $client_connect_state не найден пользователь user_id - $client_id \n", 3, $log_file);
                    }

                } else if ($client_status == "admin"){
                    if (admin($client_id)) {
                        # Закрытие подключения администратора к чату
                        $result_chat = $db -> sql_query("SELECT * FROM `support_service_chats` WHERE `id` = '$chat_id' AND `accepted_by_admin` = '$client_id' AND `admin_disconnect_time` = '0' ", "", "array");
                        if (sizeof($result_chat) > 0) {

//                            $db -> sql_query("UPDATE `support_service_chats` SET `admin_disconnect_time` = '$current_date', `closed_by` = 'admin' WHERE `id` = '$chat_id' AND `accepted_by_admin` = '$client_id'");

                            $db -> sql_query("UPDATE `support_service_chats` SET `admin_disconnect_time` = '$current_date', `closed_by` = 'admin' WHERE `id` = '$chat_id' AND `accepted_by_admin` = '$client_id'");
                            $response["status"] = "OK";
                            error_log("[$time $ip] support_service: OK $client_connect_state admin_id - $client_id, chat_id - $chat_id  \n", 3, $log_file);

                        } else {
                            $response["status"] = "ERROR";
                            error_log("[$time $ip] support_service: ERROR $client_connect_state не найден чат user_id - $client_id, chat_id - $chat_id \n", 3, $log_file);
                        }

                    } else {
                        $response["status"] = "ERROR";
                        error_log("[$time $ip] support_service: ERROR $client_connect_state не найден admin admin_id - $client_id \n", 3, $log_file);
                    }
                } else {
                    $response["status"] = "ERROR";
                    error_log("[$time $ip] support_service: $client_connect_state ERROR client_status - $client_status\n", 3, $log_file);
                }

            } else {
                $response["status"] = "ERROR";
                error_log("[$time $ip] support_service: ERROR client_connect_state - $client_connect_state\n", 3, $log_file);
            }

            return $response;
        } else {
            return $client_request_access;
        }

    }

    # Изменение статуса подключения администратора службы поддержки к системе (открытие/зактырие страницы активных чатов)
    function support_admin_connect_state($data, $current_request_type) {
        global $db, $log_file, $user;

        $client_request_access = client_request_access_status($current_request_type);
        if ($client_request_access["status"] == "OK" and isset($client_request_access["admin_id"])) {
            $ip = $user -> get_client_ip();
            $time = date("H:i");
            $current_date = time();

            $admin_id = $client_request_access["admin_id"];
            $client_connect_state = $data["client_connect_state"];

            if ($client_connect_state == "connect") {
                $session_id = gen_uuid(32);

                if ($db -> sql_query("INSERT INTO `users_sessions_support_admins`(`id`, `session_id`, `admin_id`, `ip_address`, `start_date`, `end_date`) VALUES (NULL, '$session_id', '$admin_id', '$ip', '$current_date', '0')")) {
                    $response["status"] = "OK";
                    $response["admin_session_id"] = $session_id;
                    error_log("[$time $ip] support_admin_connect_state: OK $client_connect_state admin_id - $admin_id\n", 3, $log_file);
                } else {
                    $response["status"] = "ERROR";
                    error_log("[$time $ip] support_admin_connect_state: SQL-ERROR $client_connect_state admin_id - $admin_id\n", 3, $log_file);
                }

            } else if ($client_connect_state == "disconnect") {
                $result_session = $db -> sql_query("SELECT * FROM `users_sessions_support_admins` WHERE `id` = (SELECT MAX(`id`) FROM `users_sessions_support_admins` WHERE `admin_id` = '$admin_id' AND `end_date` = '0')", "", "array");

                if (sizeof($result_session) > 0) {
                    $session_id = $result_session[0]['id'];
                    $db -> sql_query("UPDATE `users_sessions_support_admins` SET `end_date` = '$current_date' WHERE `id` = '$session_id'");
                    $response["status"] = "OK";
                    error_log("[$time $ip] support_admin_connect_state: OK $client_connect_state admin_id - $admin_id\n", 3, $log_file);
                } else {
                    $response["status"] = "ERROR";
                    error_log("[$time $ip] support_admin_connect_state: ERROR $client_connect_state admin_id - $admin_id\n", 3, $log_file);
                }

            } else {
                $response["status"] = "ERROR";
                error_log("[$time $ip] support_admin_connect_state: ERROR client_connect_state - $client_connect_state\n", 3, $log_file);
            }

            return $response;
        } else {
            return $client_request_access;
        }
    }
}
?>