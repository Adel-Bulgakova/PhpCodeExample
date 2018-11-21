<?php
class action {
    # Изменение статуса отметки like для трансляции (осуществляется через webcocket)
    function like($data, $current_request_type) {
        global $db, $log_file, $user, $stream;

        $client_request_access = client_request_access_status($current_request_type);
        if ($client_request_access["status"] == "OK") {
            $user_id = $client_request_access["user_id"];

            $ip = $user -> get_client_ip();
            $time = date("H:i");
            $current_date = time();

            $expected_params = array();
            if (isset($data["stream_uuid"]) AND !empty($data["stream_uuid"])) {
                $stream_uuid = $data["stream_uuid"];
            } else {
                $expected_params[] = "stream_uuid";
            }

            if (count($expected_params) > 0 ){
                $expected = implode(", ", $expected_params);
                $error = "Не получены параметры: $expected";
                $response["status"] = "ERROR";
                $response["message"] = $error;
                error_log("[$time $ip] action_like: $error\n", 3, $log_file);
            } else {

                $result_hero = $db -> sql_query("SELECT `streams`.`id` AS `stream_id`, `streams`.`user_id` AS `hero_id` FROM `streams` LEFT JOIN `users` ON `streams`.`user_id` = `users`.`id` WHERE `uuid` = '$stream_uuid' AND `streams`.`is_deleted` = '0' AND `users`.`is_deleted` = '0'", "", "array");
                $stream_id = $result_hero[0]["stream_id"];
                $hero_id = $result_hero[0]["hero_id"];

                if ($stream_id AND $hero_id) {
                    $result_state = $db -> sql_query("SELECT * FROM `users_actions_log` WHERE (`users_actions_id` = '1' AND `hero_id` = '$hero_id' AND `user_id` = '$user_id' AND `stream_id` = '$stream_id') OR (`users_actions_id` = '2' AND `hero_id` = '$hero_id' AND `user_id` = '$user_id' AND `stream_id` = '$stream_id')", "", "array");

                    if (sizeof($result_state) > 0 AND !empty($result_state[0])) {
                        $current_state = $result_state[0]["users_actions_id"];

                        if ($current_state == "1") {
                            #Если пользователь отмечал like, а теперь отмечает unlike, то обновление данной записи (изменение users_actions_id и метки времени)
                            $new_state = 2;
                            $response_data = '0';
                            $title = _LIKE;
                        } else {
                            #Если пользователь отмечал like, затем unlike, а теперь  снова like, то обновление данной записи (изменение users_actions_id и метки времени)
                            $new_state = 1;
                            $response_data = '1';
                            $title = _UNLIKE;
                        }
                        if ($result_new_state = $db -> sql_query("UPDATE `users_actions_log` SET `users_actions_id` = '$new_state', `created_date` = '$current_date' WHERE `users_actions_id` = '$current_state' AND `hero_id` = '$hero_id' AND `user_id` = '$user_id' AND `stream_id` = '$stream_id'")) {

                            $stream -> etag_stream_update($stream_uuid); # Обновление etag трансляции
                            $user -> etag_user_update($user_id); # Обновление etag зрителя

                            $response["status"] = "OK";
                            $response["state"] = $response_data;
                            $response["title"] = $title;
                        } else {
                            $response["status"] = "ERROR";
                            $response["message"] = "Ошибка выполнения запроса";
                            error_log("[$time $ip] action_like_app: SQL-ERROR не добавлена запись в users_actions_log stream_uuid - $stream_uuid\n", 3, $log_file);
                        }
                    } else {
                        #Если пользователь впервые отмечает like, то создается новая запись
                        if ($db -> sql_query("INSERT INTO `users_actions_log`(`id`, `users_actions_id`, `hero_id`, `user_id`, `stream_id`, `comment`, `created_date`) VALUES (NULL, '1', '$hero_id', '$user_id', '$stream_id', '', '$current_date')")) {

                            $stream -> etag_stream_update($stream_uuid); # Обновление etag трансляции
                            $user -> etag_user_update($user_id); # Обновление etag зрителя

                            $response["status"] = "OK";
                            $response["state"] = 1;
                            $response["title"] = _UNLIKE;
                        } else {
                            $response["status"] = "ERROR";
                            $response["message"] = "Ошибка выполнения запроса";
                            error_log("[$time $ip] action_like_app: SQL-ERROR не добавлена запись в users_actions_log stream_uuid - $stream_uuid\n", 3, $log_file);
                        }
                    }
                } else {
                    $response["status"] = "NOT-FOUND";
                    error_log("[$time $ip] action_like_app: NOT-FOUND stream_uuid - $stream_uuid\n", 3, $log_file);
                }
            }

            return $response;
        } else {
            return $client_request_access;
        }
    }

    # Изменение статуса подписки одного пользователя на другого
    function follow($data, $current_request_type) {
        global $db, $stream, $log_file, $user;

        $client_request_access = client_request_access_status($current_request_type);
        if ($client_request_access["status"] == "OK") {
            $ip = $user -> get_client_ip();
            $time = date("H:i");
            $current_date = time();

            if (isset($client_request_access["user_id"])) {
                $follower_id = $client_request_access["user_id"];

                if (isset($client_request_access["device_id"])){
                    $device_id = $client_request_access["device_id"];
                    $lang = $user -> get_lang_by_device_id($device_id);
                } else {
                    if (isset($data["lang"]) AND !empty($data["lang"])) {
                        $lang = prepair_str($data["lang"]);
                    } else {
                        $lang = 'ru';
                    }
                }

                $expected_params = array();
                if (isset($data["hero_id"]) AND !empty($data["hero_id"])) {
                    $hero_id = $data["hero_id"];
                } else {
                    $expected_params[] = "hero_id";
                }

                if (count($expected_params) > 0 ){

                    $expected = implode(", ", $expected_params);
                    $error = "Не получены параметры: $expected";
                    $response["status"] = "ERROR";
                    $response["message"] = $error;
                    error_log("[$time $ip] follow: $error\n", 3, $log_file);

                } else {

                    if (user($hero_id) AND $hero_id != $follower_id) {
                        $result_state = $db -> sql_query("SELECT * FROM `users_actions_log` WHERE (`hero_id` = '$hero_id' AND `user_id` = '$follower_id' AND `users_actions_id` = '3') OR (`hero_id` = '$hero_id' AND `user_id` = '$follower_id' AND `users_actions_id` = '4')", "", "array");

                        if ($lang == "en") {
                            $follow_title = "Follow";
                            $is_followed_title = "Is follows";
                        } else {
                            $follow_title = "Подписаться";
                            $is_followed_title = "Вы подписаны";
                        }

                        if (sizeof($result_state) > 0 AND !empty($result_state[0])) {
                            $current_state = $result_state[0]["users_actions_id"];

                            if ($current_state == 3) {
                                #Если пользователь был подписан и отписывается, то обновление данной записи (изменение users_actions_id и метки времени)
                                $new_state = 4;
                                $response_data = 0;
                                $title = $follow_title;
                            } else {
                                #Если запись для этого пользователя и этого героя существует: пользователь подписывался и отписался, то обновление данной записи (изменение users_actions_id, метки времени)
                                $new_state = 3;
                                $response_data = 1;
                                $title = $is_followed_title;
                            }
                            if ($result_new_state = $db -> sql_query("UPDATE `users_actions_log` SET `users_actions_id` = '$new_state', `created_date` = '$current_date' WHERE `users_actions_id` = '$current_state' AND `hero_id` = '$hero_id' AND user_id = '$follower_id'")) {
                                $response["status"] = "OK";
                                $response["state"] = $response_data;
                                $response["title"] = $title;
                                if ($response_data == 1){
                                    $log_message = "подписался на";

                                    $stream -> push_notification_new_follower($hero_id, $follower_id); # Отправка пуш уведомления о подписчике на устройство пользователя
                                } else {
                                    $log_message = "отписался от";
                                }

                                $user -> etag_user_update($hero_id); # Обновление etag пользователя, на которого подписываются/отписываются
                                $user -> etag_user_update($follower_id); # Обновление etag пользователя, который подписывается/отписывается

                                error_log("[$time $ip] action_follow: OK пользователь id = $follower_id $log_message пользователя id = $hero_id\n", 3, $log_file);
                            } else {
                                $response["status"] = "ERROR";
                                $response["message"] = "Ошибка выполнения запроса";
                                error_log("[$time $ip] action_follow: SQL-ERROR не обновлен users_actions_log\n", 3, $log_file);
                            }
                        } else {
                            #Если пользователь впервые подписывается, то создается новая запись
                            if ($db -> sql_query("INSERT INTO `users_actions_log`(`id`, `users_actions_id`, `hero_id`, `user_id`, `stream_id`, `comment`, `created_date`) VALUES (NULL, '3', '$hero_id', '$follower_id', '', '', '$current_date')")){
                                $response["status"] = "OK";
                                $response["state"] = 1;
                                $response["title"] = $is_followed_title;

                                $stream -> push_notification_new_follower($hero_id, $follower_id); # Отправка пуш уведомления о подписчике на устройство пользователя
                                $user -> etag_user_update($hero_id); # Обновление etag пользователя, на которого подписываются
                                $user -> etag_user_update($follower_id); # Обновление etag пользователя, который подписывается

                                error_log("[$time $ip] action_follow: OK пользователь id - $follower_id подписан на пользователя id - $hero_id\n", 3, $log_file);
                            } else {
                                $response["status"] = "ERROR";
                                $response["message"] = "Ошибка выполнения запроса";
                                error_log("[$time $ip] action_follow: SQL-ERROR не добавлена запись в users_actions_log(hero_id = $hero_id, follower_id = $follower_id\n", 3, $log_file);
                            }
                        }
                    } else {
                        $response["status"] = "ERROR";
                        $response["message"] = "Ошибка выполнения запроса";
                        error_log("[$time $ip] action_follow: ERROR follower_id == hero_id follower_id - $follower_id, hero_id - $hero_id\n", 3, $log_file);
                    }
                }

            } else {
                $response["status"] = "ERROR";
                error_log("[$time $ip] action_follow: ERROR follower_id == hero_id follower_id - " .$client_request_access["user_id"] .", hero_id - " . $data["hero_id"]. "\n", 3, $log_file);
            }
            ob_end_clean();
            return $response;

        } else {
            return $client_request_access;
        }
    }

    # Добавление комментария (используется websocket сервером)
    function comment_add($data, $current_request_type) {
        global $db, $log_file, $stream, $user;

        $client_request_access = client_request_access_status($current_request_type);

        if ($client_request_access["status"] == "OK") {
            $ip = $user -> get_client_ip();
            $time = date("H:i");

            $user_id = $client_request_access["user_id"];

            $expected_params = array();
            if (isset($data["uuid"]) AND !empty($data["uuid"])) {
                $stream_uuid = prepair_str($data["uuid"]);
            } else {
                $expected_params[] = "uuid";
            }

            if (isset($data["comment"]) AND !empty($data["comment"])) {
                $comment = prepair_str($data["comment"]);
            } else {
                $expected_params[] = "comment";
            }

            $created_date = microtime_float();
            if (isset($data["timestamp"]) AND !empty($data["timestamp"])) {
                $created_date = $data["timestamp"];
            }

            if (isset($data["time_from_start"]) AND !empty($data["time_from_start"])) {
                $time_from_start = $data["time_from_start"];
            }

            if (count($expected_params) > 0) {
                $expected = implode(", ", $expected_params);
                $error = "Не получены параметры: $expected";
                $response["status"] = "ERROR";
                $response["message"] = $error;
                error_log("[$time $ip] comment_add: $error\n", 3, $log_file);
            } else {
                $result = $db -> sql_query("SELECT `id`, `user_id` FROM `streams` WHERE `uuid` = '$stream_uuid' AND `is_excess` = '0' AND `is_deleted` = '0'", "", "array");
                if (sizeof($result) > 0){
                    $hero_id = $result[0]["user_id"];
                    $stream_id= $result[0]["id"];

                    #Проверка блокировки данного пользователя
                    $chat_permissions = chat_permissions($stream_uuid, $user_id);
                    if ($chat_permissions){
                        if ($result_comment_add = $db -> sql_query("INSERT INTO `users_actions_log`(`id`, `users_actions_id`, `hero_id`, `user_id`, `stream_id`, `comment`, `created_date`, `time_from_start`) VALUES (NULL, '5', '$hero_id', '$user_id', '$stream_id', '$comment', '$created_date', '$time_from_start')")){
                            $comment_id = $db -> sql_nextid($result_comment_add);

                            $stream -> etag_stream_update($stream_uuid); # Обновление etag трансляции

                            $response["status"] = "OK";
                            $response["comment_id"] = $comment_id;
                        } else {
                            $response["status"] = "ERROR";
                            $response["message"] = "Ошибка выполнения запроса";
                            error_log("[$time $ip] comment_add: sql_error stream_uuid = $stream_uuid, user_id = $user_id\n", 3, $log_file);
                        }
                    } else {
                        $response["status"] = "ERROR-CHAT-PERMISSIONS";
                        error_log("[$time $ip] comment_add: недостаточно прав доступа stream_uuid = $stream_uuid, user_id = $user_id\n", 3, $log_file);
                    }
                } else {
                    $response["status"] = "NOT-FOUND";
                    error_log("[$time $ip] comment_add: не найден stream_uuid = $stream_uuid\n", 3, $log_file);
                }
            }
            return $response;
        } else {
            return $client_request_access;
        }
    }

    # Изменение статуса блокировки пользователя владельцем трансляции
    function block($data, $current_request_type) {
        global $db, $log_file, $user;

        $client_request_access = client_request_access_status($current_request_type);
        if ($client_request_access["status"] == "OK") {
            $ip = $user -> get_client_ip();
            $time = date("H:i");
            $current_date = time();

            $user_id = $client_request_access["user_id"];

            if (isset($client_request_access["device_id"])){
                $device_id = $client_request_access["device_id"];
                $lang = $user -> get_lang_by_device_id($device_id);
            } else {
                if (isset($data["lang"]) AND !empty($data["lang"])) {
                    $lang = prepair_str($data["lang"]);
                } else {
                    $lang = 'ru';
                }
            }

            $expected_params = array();
            if (isset($data["blocked_user_id"]) AND !empty($data["blocked_user_id"])) {
                $blocked_user_id = $data["blocked_user_id"];
            } else {
                $expected_params[] = "blocked_user_id";
            }

            $stream_id = 0;
            if (isset($data["stream_uuid"]) AND !empty($data["stream_uuid"])) {
                $stream_uuid = $data["stream_uuid"];

                $result_stream = $db -> sql_query("SELECT * FROM `streams` WHERE `uuid` = '$stream_uuid' AND `is_excess` = '0' AND `is_deleted` = '0'", "", "array");
                if (sizeof($result_stream) > 0) {
                    $stream_id = $result_stream[0]["id"];
                }
            }

            if (count($expected_params) > 0 ){
                $expected = implode(", ", $expected_params);
                $error = "Не получены параметры: $expected";
                $response["status"] = "ERROR";
                $response["message"] = $error;
                error_log("[$time $ip] action_block_app: ERROR $error\n", 3, $log_file);

            } else {

                if (user($blocked_user_id) AND $user_id != $blocked_user_id) {
                    $result_state = $db -> sql_query("SELECT * FROM `users_actions_log` WHERE (`hero_id` = '$user_id' AND `user_id` = '$blocked_user_id' AND `users_actions_id` = '6') OR (`hero_id` = '$user_id' AND `user_id` = '$blocked_user_id' AND `users_actions_id` = '7')", "", "array");

                    if ($lang == "ru") {
                        $block_title = "Заблокировать";
                        $unblock_title = "Разблокировать";
                    } else {
                        $block_title = "Block";
                        $unblock_title = "Unblock";
                    }

                    if (sizeof($result_state) > 0 AND !empty($result_state[0])) {
                        $current_state = $result_state[0]["users_actions_id"];
                        if ($current_state == 6) {
                            #Если пользователь был ранее заблокирован, то обновление данной записи (изменение users_actions_id и метки времени)
                            $new_state = 7;
                            $response_data = 0;
                            $title = $block_title;
                        } else {
                            #Если пользователь был заблокирован, а затем разблокирован (изменение users_actions_id, stream_id, метки времени)
                            $new_state = 6;
                            $response_data = 1;
                            $title = $unblock_title;
                        }
                        if ($result_new_state = $db -> sql_query("UPDATE `users_actions_log` SET `users_actions_id` = '$new_state', `created_date` = '$current_date' WHERE `users_actions_id` = '$current_state' AND `hero_id` = '$user_id' AND `user_id` = '$blocked_user_id'")) {

                            $user -> etag_user_update($user_id); # Обновление etag пользователя, который заблокировал зрителя
                            $user -> etag_user_update($blocked_user_id); # Обновление etag пользователя, который заблокирован/разблокирован
                            
                            $response["status"] = "OK";
                            $response["state"] = $response_data;
                            $response["title"] = $title;

                            if ($new_state == 6) {
                                $action = "заблокировал";
                            } else {
                                $action = "разблокировал";
                            }
                            error_log("[$time $ip] action_block_app: OK user_id - $user_id $action пользователя user_id = $blocked_user_id \n", 3, $log_file);
                        } else {
                            $response["status"] = "ERROR";
                            $response["message"] = "Ошибка выполнения запроса";
                            error_log("[$time $ip] action_block_app: SQL-ERROR не обновлен users_actions_log\n", 3, $log_file);
                        }

                    } else {
                        # Если владелец трансляции впервые блокирует данного пользователя, то создается новая запись
                        if ($db -> sql_query("INSERT INTO `users_actions_log`(`id`, `users_actions_id`, `hero_id`, `user_id`, `stream_id`, `comment`, `created_date`) VALUES (NULL, '6', '$user_id', '$blocked_user_id', '$stream_id', '', '$current_date')")) {

                            $user -> etag_user_update($user_id); # Обновление etag пользователя, который заблокировал зрителя
                            $user -> etag_user_update($blocked_user_id); # Обновление etag пользователя, который заблокирован

                            $response["status"] = "OK";
                            $response["state"] = 1;
                            $response["title"] = $unblock_title;
                        } else {
                            $response["status"] = "ERROR";
                            $response["message"] = "Ошибка выполнения запроса";
                            error_log("[$time $ip] action_block_app: SQL-ERROR не добавлена запись в users_actions_log\n", 3, $log_file);
                        }
                    }
                } else {
                    $response["status"] = "ERROR";
                    error_log("[$time $ip] action_block_app: не найден blocked_user_id или user_id == blocked_user_id (user_id = $user_id, blocked_user_id - $blocked_user_id)\n", 3, $log_file);
                }
            }
            return $response;
        } else {
            return $client_request_access;
        }
    }
    
    # Жалоба на трансляцию
    function claim($stream_uuid = "", $user_id1 = 0, $current_request_type) {
        global $db, $log_file, $user;

        $client_request_access = client_request_access_status($current_request_type);

        if ($client_request_access["status"] == "OK") {

            if (isset($client_request_access["user_id"])) {
                $user_id = $client_request_access["user_id"];
            } else {
                $user_id = $user_id1;
            }

            $ip = $user -> get_client_ip();
            $time = date("H:i");

            $result_stream = $db -> sql_query("SELECT * FROM `streams` WHERE `uuid` = '$stream_uuid' AND `is_excess` = '0' AND `is_deleted` = '0'", "", "array");
            $stream_id = $result_stream[0]["id"];
            $hero_id = $result_stream[0]["user_id"];

            $current_date = time();
            if (user($user_id) AND $stream_id){
                if ($user_id != $hero_id){ #Ограничение возможности пожаловаться на свою трансляцию
                    if ($result_claim = $db -> sql_query("INSERT INTO `claims` (`id`, `stream_id`, `by_user_id`, `created_date`, `status_id`, `is_deleted`) VALUES (NULL, '$stream_id', '$user_id', '$current_date', '1', '0');")) {
                        $response["status"] = "OK";
                    } else {
                        $response["status"] = "ERROR";
                        error_log("[$time $ip] action_claim: SQL-ERROR user_id = $user_id, stream_id = $stream_id\n", 3, $log_file);
                    }
                } else {
                    $response["status"] = "ERROR";
                    error_log("[$time $ip] action_claim: SQL-ERROR user_id == hero_id, user_id = $user_id, stream_uuid - $stream_uuid\n", 3, $log_file);
                }
            } else {
                $response["status"] = "NOT-FOUND";
                error_log("[$time $ip] action_claim: NOT-FOUND stream_uuid - $stream_uuid, user_id - $user_id\n", 3, $log_file);
            }

            return $response;
        } else {
            return $client_request_access;
        }
    }

    # Жалоба на комментарий
    function claim_comment($comment_id = 0, $current_request_type) {
        global $db, $log_file, $user;

        $client_request_access = client_request_access_status($current_request_type);

        if ($client_request_access["status"] == "OK") {

            $user_id = $client_request_access["user_id"];
            $ip = $user -> get_client_ip();
            $time = date("H:i");
            $current_date = time();

            $result_comment = $db -> sql_query("SELECT * FROM `users_actions_log` WHERE `id` = '$comment_id' AND `users_actions_id` = '5'", "", "array");
            $comment_author_id = $result_comment[0]["user_id"];

            if (user($comment_author_id) AND user($user_id)){
                if ($db -> sql_query("INSERT INTO `claims_comments` (`id`, `comment_id`, `by_user_id`, `created_date`, `status_id`, `is_deleted`) VALUES (NULL, '$comment_id', '$user_id', '$current_date', '1', '0');")) {
                    $response["status"] = "OK";
                } else {
                    $response["status"] = "ERROR";
                    $response["message"] = "Ошибка выполнения запроса";
                    error_log("[$time $ip] action_claim_comment: SQL-ERROR user_id - $user_id, comment_id - $comment_id\n", 3, $log_file);
                }
            } else {
                $response["status"] = "NOT-FOUND";
                error_log("[$time $ip] action_claim_comment: NOT-FOUND не найден comment_id - $comment_id или user_id - $user_id\n", 3, $log_file);
            }

            return $response;

        } else {
            return $client_request_access;
        }
    }

    # Отправка сообщения в службу поддержки пользователей
    function message_to_support($data = array(), $current_request_type) {
        global $db, $log_file, $user;

        $client_request_access = client_request_access_status($current_request_type);
        if ($client_request_access["status"] == "OK") {
            $user_id = $client_request_access["user_id"];

            $ip = $user -> get_client_ip();
            $time = date("H:i");
            $current_date = time();

            $expected_params = array();
            if (isset($data["email"]) AND !empty($data["email"])) {
                $email = $data["email"];
            } else {
                $expected_params[] = "email";
            }

            if (isset($data["message"]) AND !empty($data["message"])) {
                $message = $data["message"];
            } else {
                $expected_params[] = "message";
            }

            if (count($expected_params) > 0 ){
                $expected = implode(", ", $expected_params);
                $error = "Не получены параметры: $expected";
                $response["status"] = "ERROR";
                $response["message"] = $error;
                error_log("[$time $ip] message_to_support: $error\n", 3, $log_file);
            } else {

                if ($db -> sql_query("INSERT INTO `support_service_msg`(`id`, `user_id`, `email`, `message`, `created_date`, `status`) VALUES (NULL, '$user_id', '$email','$message', '$current_date', '')")) {
                    $response["status"] = "OK";
                } else {
                    $response["status"] = "ERROR";
                    $response["message"] = "Ошибка выполнения запроса";
                    error_log("[$time $ip] message_to_support: SQL-ERROR user_id - $user_id, email - $email, message - $message\n", 3, $log_file);
                }
            }

            return $response;
        } else {
            return $client_request_access;
        }
    }    

    function include_lang_file($lang) {
        switch ($lang) {
            case "ru" :
                include "../lang/ru.php";
                break;
            case "en" :
                include "../lang/en.php";
                break;
            default :
                include "../lang/ru.php";
                break;
        }
    }
}
?>