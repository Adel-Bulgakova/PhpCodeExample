<?php
class user {

    # Возвращает информацию о пользователе
    function user_data($user_id = 0){
        global $db, $profile_image_query;
        $response = array();

        $result = $db -> sql_query("SELECT `id`, `login`, `name`,
            CASE  WHEN `name` != ''
                  THEN `name`
                  ELSE 'No name'
                  END AS `display_name`,
            `email`,
            `about`,
            CONCAT('$profile_image_query', `id`) AS `profile_image`,
            `image_changed_date`,
            `etag_user`,
            `etag_img`,
            `is_check_official`                         
            FROM `users` WHERE `id` = '$user_id' AND `is_deleted` = '0'", "", "array");
        if (sizeof($result) > 0 AND !empty($result[0])) {

            $followers = $this -> followers($user_id);
            $followers_count = sizeof($followers);
            $following = $this -> following($user_id);
            $following_count = sizeof($following);
            # Возвращает массив id пользователей, которые подписаны на запрашиваемого пользователя и на которых подписан запрашиваемый пользователь (взаимные читатели)
            $mutual_following = array_values(array_intersect($following, $followers));
            $total_likes_count = $this -> total_likes_count($user_id);

            $blocked = $this -> blocked($user_id);
            $blocked_count = sizeof($blocked);

            $user_data["id"] = $result[0]["id"];
            $user_data["login"] = $result[0]["login"];
            $user_data["name"] = $result[0]["name"];
            $user_data["display_name"] = $result[0]["display_name"];
            $user_data["email"] = $result[0]["email"];
            $user_data["about"] = $result[0]["about"];
            $user_data["profile_image"] =  $result[0]["profile_image"];
            $user_data["image_changed_date"] = $result[0]["image_changed_date"];
            $user_data["etag"] = $result[0]["etag_user"];
            $user_data["etag_img"] = $result[0]["etag_img"];
            $user_data["is_official"] = intval($result[0]["is_check_official"]);
            $user_data["followers_count"] = $followers_count;
            $user_data["following_count"] = $following_count;
            $user_data["blocked_count"] = $blocked_count;
            $user_data["followers"] = $followers;
            $user_data["following"] = $following;
            $user_data["mutual_following"] = $mutual_following;
            $user_data["total_likes_count"] = $total_likes_count;

            $user_data["blocked"] = $blocked;
            $user_data["devices"] = array();
            $user_data["streams"]["online"] = array();
            $user_data["streams"]["archive"] = array();
            $user_data["streams"]["oncoming"] = array(); # При расширении функционала будут отображаться предстоящие трансляции
            $user_data["streams"]["recent"] = array();

            $result_devices = $db -> sql_query("SELECT * FROM `devices` WHERE `user_id` = '$user_id' AND `is_deleted` = '0'", "", "array");
            if (sizeof($result_devices) > 0){
                foreach ($result_devices as $val) {
                    $device_id = $val["id"];
                    $device_uuid = $val["device_uuid"];
                    $device_model = $val["device_model"];
                    $operating_system = $val["operating_system"];
                    $new_stream_notify = $val["new_stream_notify_settings"];
                    $new_follower_notify = $val["new_follower_notify_settings"];
                    $device_is_blocked = $val["is_blocked"];

                    $device["device_uuid"] = $device_uuid;
                    $device["device_model"] = $device_model;
                    $device["operating_system"] = $operating_system;
                    $device["lang"] = $this -> get_lang_by_device_id($device_id);
                    $device["settings"]["new_stream"] = $new_stream_notify;
                    $device["settings"]["new_follower"] = $new_follower_notify;
                    $device["is_blocked"] = $device_is_blocked;
                    $user_data["devices"][] = $device;
                }
            }

            $result_streams = $db -> sql_query("SELECT `uuid`, `start_date`, `end_date` FROM `streams` WHERE `user_id` = '$user_id' AND `is_excess` = '0' AND `is_blocked` = '0' AND `is_deleted` = '0'", "", "array");
            if (sizeof($result_streams) > 0){
                foreach ($result_streams as $value) {
                    $stream_uuid = $value["uuid"];
                    $stream_end_date = $value["end_date"];
                    if ($stream_end_date == 0){
                        $user_data["streams"]["online"][] = $stream_uuid;
                    } else {
                        $user_data["streams"]["archive"][] = $stream_uuid;
                    }

                    #Возвращает список uuid трансляций, записанных в последние 24 часа
                    $recent_streams_filter = time() - 24*60*60;
                    if ($stream_end_date != 0 AND intval($value["start_date"]) > $recent_streams_filter){
                        $user_data["streams"]["recent"][] = $stream_uuid;
                    }
                }
            }

            $response["status"] = "OK";
            $response["data"] = $user_data;

        } else {
            $response["status"] = "NOT-FOUND";
        }

        return $response;
    }

    # Возвращает информацию о каждом пользователе из массива id пользователей
    function users_array_data($data) {
        $response = array();

        if (sizeof($data) > 0) {
            foreach ($data as $user_id) {
                $user_data = $this -> user_data($user_id);
                array_push($response, $user_data);
            }
        }
        return $response;
    }

    # Возвращает информацию о каждом пользователе из массива id пользователей (версия 2, будет использоваться в качестве основной и переименована в users_array_data)
    function users_array_data2($users_array) {
        global $db, $profile_image_query;

        $response = array();
        $users_array_to_string = "'" . implode("','", $users_array) . "'";

        $res = $db -> sql_query("SELECT `id`, `login`, `name`,
            CASE  WHEN `name` != ''
                  THEN `name`
                  ELSE 'No name'
                  END AS `display_name`,
            `email`,
            `about`,
            CONCAT('$profile_image_query', `id`) AS `profile_image`,
            `etag_user` AS `etag`,
            `etag_img`,
            `is_check_official`
            FROM `users` WHERE `id` IN (". $users_array_to_string . ") AND `is_blocked` = '0' AND `is_deleted` = '0'", "", "array");

        foreach ($res as $row) {
            $user_id = $row["id"];

            $followers = $this -> followers($user_id);
            $following = $this -> following($user_id);
            $row["followers"] = $followers;
            $row["following"] = $following;
            $row["blocked"] = $this -> blocked($user_id);
            # Возвращает массив id пользователей, которые подписаны на запрашиваемого пользователя и на которых подписан запрашиваемый пользователь (взаимные читатели)
            $row["mutual_following"] = array_values(array_intersect($following, $followers));
            $row["total_likes_count"] = $this -> total_likes_count($user_id);

            $streams["online"] = array();
            $streams["archive"] = array();
            $streams["oncoming"] = array(); # При расширении функционала будут отображаться предстоящие трансляции
            $streams["recent"] = array();

            $result_streams = $db -> sql_query("SELECT `uuid`, `start_date`, `end_date`  FROM `streams` WHERE `user_id` = '$user_id' AND `is_excess` = '0' AND `is_blocked` = '0' AND `is_deleted` = '0'", "", "array");
            if (sizeof($result_streams) > 0){
                foreach ($result_streams as $value) {
                    $stream_uuid = $value["uuid"];
                    $stream_end_date = $value["end_date"];
                    if ($stream_end_date == 0){
                        $streams["online"][] = $stream_uuid;
                    } else {
                        $streams["archive"][] = $stream_uuid;
                    }

                    #Возвращает список uuid трансляций, записанных в последние 24 часа
                    $recent_streams_filter = time() - 24*60*60;
                    if ($stream_end_date != 0 AND intval($value["start_date"]) > $recent_streams_filter){
                        $streams["recent"][] = $stream_uuid;
                    }
                }
            }

            $row["streams"] = $streams;
            $response[] = $row;
        }
        return $response;
    }

    # Возвращает etag для каждого элемента из массива id пользователей
    function users_etags_data($data){
        global $db;
        $response = array();
        $s = "'" . implode("','", $data) . "'";

        $res = $db -> sql_query("SELECT `id`, `etag_user`  FROM `users` WHERE `id` IN (". $s . ") AND `is_deleted` = '0'", "", "array");

        foreach ($data as $user_id) {
            $user_data = false;

            foreach ($res as $value) {
                $id = $value["id"];
                $etag = $value["etag_user"];

                if ($user_id == $id) {
                    $user_data = true;

                    $response[$user_id] = $etag;
                    break;
                }
            }
            if (!$user_data) {
                $response[$user_id] = "NOT-FOUND";
            }
        }

        return $response;
    }

    # Возвращает массив id пользователей в порядке убывания популярности
    function users_top ($users_count = 25) {
        global $db;

        $response = array();
        $rated_users = $db -> sql_query("SELECT `id`, `etag_user` FROM `users` WHERE `is_blocked` = '0' AND `is_deleted` = '0' ORDER BY `rating` DESC LIMIT $users_count", "", "array");

        foreach ($rated_users as $v){
            $user_id = $v["id"];
            $response[] = $user_id;
        }

        return $response;
    }

    # Возвращает массив пар id и etag пользователей в порядке убывания популярности
    function users_top_v2 ($users_count = 25) {
        global $db;

        $response = array();
        $rated_users = $db -> sql_query("SELECT `id`, `etag_user` FROM `users` WHERE `is_blocked` = '0' AND `is_deleted` = '0' ORDER BY `rating` DESC LIMIT $users_count", "", "array");

        foreach ($rated_users as $v){
            $user_id = $v["id"];
            $etag = $v["etag_user"];
            $u[0] = $user_id;
            $u[1] = $etag;
            $response[] = $u;
        }

        return $response;
    }

    # Возвращает массив пар id и etag оффициальных источников в порядке убывания популярности
    function users_official_top($users_count = 25) {
        global $db;

        $response = array();
        $rated_users = $db -> sql_query("SELECT `id`, `etag_user` FROM `users` WHERE `is_official` = '1' AND `is_check_official` = '1' AND `is_blocked` = '0' AND `is_deleted` = '0' ORDER BY `rating` DESC LIMIT $users_count", "", "array");

        foreach ($rated_users as $v){
            $user_id = $v["id"];
            $etag = $v["etag_user"];
            $u[0] = $user_id;
            $u[1] = $etag;
            $response[] = $u;
        }

        return $response;
    }

    # Возвращает массив Id пользователей, имя или описание которых содержит искомую фразу
    function users_search($query = "") {
        global $db;
        $response = array();
        if (strlen($query) > 2) {
            $result_profiles = $db -> sql_query("SELECT `id` FROM `users` WHERE (`name` LIKE '%".$query."%' AND `is_blocked` = '0' AND `is_deleted` = '0') OR (`login` LIKE '%".$query."%' AND `is_blocked` = '0' AND `is_deleted` = '0') OR (`about` LIKE '%".$query."%' AND `is_blocked` = '0' AND `is_deleted` = '0') GROUP BY `id`", "", "array");
            if (sizeof($result_profiles) > 0) {
                foreach ($result_profiles as $value) {
                    $hero_id = $value["id"];
                    $response[] = $hero_id;
                }
            }
        }
        return $response;
    }

    # Возвращает массив Id пользователей, имя или описание которых содержит искомую фразу (версия 2 с etag)
    function users_search_v2($query = "") {
        global $db;
        $response = array();
        if (strlen($query) > 2) {
            $result_profiles = $db -> sql_query("SELECT `id`, `etag_user` FROM `users` WHERE (`name` LIKE '%".$query."%' AND `is_blocked` = '0' AND `is_deleted` = '0') OR (`login` LIKE '%".$query."%' AND `is_blocked` = '0' AND `is_deleted` = '0') OR (`about` LIKE '%".$query."%' AND `is_blocked` = '0' AND `is_deleted` = '0') GROUP BY `id`", "", "array");
            if (sizeof($result_profiles) > 0) {
                foreach ($result_profiles as $value) {
                    $hero_id = $value["id"];
                    $etag = $value["etag_user"];
                    $data[0] = $hero_id;
                    $data[1] = $etag;
                    $response[] = $data;
                }
            }
        }
        return $response;
    }  

    # Возвращает именя пользователя
    function profile_name($user_id = 0) {
        global $db;
        $result_profile = $db -> sql_query("SELECT `name` FROM `users` WHERE `id` = '$user_id' AND `is_deleted` = '0'", "", "array");
        $profile_name = "No name";
        if (sizeof($result_profile) > 0 AND !empty($result_profile[0]["name"])) {
            $profile_name = $result_profile[0]["name"];
        }
        return $profile_name;
    }

    # Получение html-кода с изображением профиля
    function profile_image_html($user_id = 0) {
        global $profile_image_query;
        $profile_image_url = $profile_image_query.$user_id;
        $profile_image = "<div class=\"profile_image\" style=\"background: url('$profile_image_url') 100% 100% no-repeat;  background-size: cover;\"></div>";
        return $profile_image;
    }

    # Возвращает массив id подписчиков
    function followers($user_id = 0) {
        global $db;
        $followers = array();

        $result = $db -> sql_query("SELECT `user_id` FROM `users_actions_log` LEFT JOIN `users` ON `users`.`id` = `users_actions_log`.`user_id`  WHERE `hero_id` = '$user_id' AND `users_actions_id` = '3' AND `users`.`is_deleted` = '0' GROUP BY `user_id`", "", "array");
        if (sizeof($result) > 0){
            foreach ($result as $value) {
                array_push($followers, $value["user_id"]);
            }
        }

        return $followers;
    }

    # Возвращает массив id пользователей, на которых подписан запрашиваемый пользователь
    function following($user_id = 0) {
        global $db;
        $following = array();
        $result = $db -> sql_query("SELECT `hero_id` FROM `users_actions_log` LEFT JOIN `users` ON `users`.`id` = `users_actions_log`.`hero_id` WHERE `user_id` = '$user_id' AND `users_actions_id` = '3' AND `users`.`is_deleted` = '0' GROUP BY `hero_id`", "", "array");
        if (sizeof($result) > 0){
            foreach ($result as $value) {
                array_push($following, $value["hero_id"]);
            }
        }
        return $following;
    }

    function mutual_following ($user_id = 0) {
        $followers = $this -> followers($user_id);
        $following = $this -> following($user_id);
        # Возвращает массив id пользователей, которые подписаны на запрашиваемого пользователя и на которых подписан запрашиваемый пользователь (взаимные читатели)
        $mutual_following = array_values(array_intersect($following, $followers));
        return $mutual_following;
    }

    # Возвращает массив id заблокированных пользователей
    function blocked($user_id = 0) {
        global $db;
        $blocked = array();

        $result = $db -> sql_query("SELECT `user_id` FROM `users_actions_log` LEFT JOIN `users` ON `users`.`id` = `users_actions_log`.`user_id`  WHERE `hero_id` = '$user_id' AND `users_actions_id` = '6' AND `users`.`is_deleted` = '0' GROUP BY `user_id`", "", "array");
        if (sizeof($result) > 0){
            foreach ($result as $value) {
                array_push($blocked, $value["user_id"]);
            }
        }
        return $blocked;
    }

    # Возвращает общее количество лайков по всем трансляциям запрашиваемого пользователя (кроме заблокированных)
    function total_likes_count($user_id = 0) {
        global $db;

        $result = $db -> sql_query("SELECT COUNT(*) FROM `users_actions_log` LEFT JOIN `streams` ON `streams`.`id` = `users_actions_log`.`stream_id` LEFT JOIN `users` ON `users`.`id` = `users_actions_log`.`user_id` WHERE `users_actions_log`.`hero_id` = '$user_id' AND `users_actions_log`.`users_actions_id` = '1'  AND `users`.`is_blocked` = '0' AND `streams`.`is_excess` = '0' AND `streams`.`is_blocked` = '0'", "", "array");

        $total_likes_count = $result[0]["COUNT(*)"];

        return $total_likes_count;
    }

    # Возвращает настройки языка для запрашиваемого устройства
    function get_lang_by_device_id($device_id = 0){
        global $db;

        $lang = "ru"; #Язык устройства по умолчанию

        $result = $db -> sql_query("SELECT `lang` FROM `devices` WHERE `id` = '$device_id' AND `is_deleted` = '0'", "", "array");

        if (sizeof($result) > 0){
            $device_lang = $result[0]["lang"];

            if (preg_match("/en/i", $device_lang)) {
                $lang = "en";
            }
        }

        return $lang;
    }

    #Данный метод произодит авторизацию или создание нового пользователя сайта в системе
    function user_auth($data) {
        global $db, $log_file, $project_options;

        $ip = $this -> get_client_ip();
        $time = date("H:i");

        $current_date = time();

        #Удаление лишних символов в строке
        $trimmed_number = preg_replace("/[^0-9]/", "", $data["phone"]);
        $phone= "+".$trimmed_number;
        
        $hash_code = $data["hash_code"];

        #Поиск введенного пользователем кода
        $result_code = $db -> sql_query("SELECT * FROM `users_auth_hash_codes` WHERE `phone_number` = '$phone' AND `hash_code` = '$hash_code' AND `is_used` = '0' ORDER BY id DESC LIMIT 1", "", "array");
        if (sizeof($result_code) > 0 AND !empty($result_code[0])) {
            $code_created_date_limit = $current_date - 60*5;
            if ($result_code[0]["created_date"] >= $code_created_date_limit) { # Проверка срока действия существующего кода

                $result_user = $db -> sql_query("SELECT * FROM `users` WHERE `phone` = '$phone' AND `id` = (SELECT MAX(`id`) FROM `users` WHERE `phone` = '$phone' GROUP BY `phone`)", "", "array");

                if (sizeof($result_user) > 0 AND !empty($result_user[0])) { #Пользователь с данным номером телефона существует в системе
                    $user_id = $result_user[0]["id"];
                    $user_deleted_status = $result_user[0]["is_deleted"];
                    $user_deleted_date = $result_user[0]["deleted_date"];
                    $recover_account_date_limit = $user_deleted_date + 60*60*72;

                    if ($user_deleted_status == 0) { #Пользователь с данным номером телефона существует в системе и он не удален
                        $db -> sql_query("UPDATE `users` SET `last_connected` = '$current_date', `last_connected_by` = 'site' WHERE `id` = '$user_id' AND `is_deleted` = '0'");
                        $create_new_account = 0;
                        session_start();
                        $_SESSION["uid"] = $user_id;
                        $this -> get_web_session_id($user_id, "phone_number");
                        $response["status"] = "OK";
                        error_log("[$time $ip] user_auth_site: OK прошел авторизацию user_id = $user_id\n", 3, $log_file);
                    } else if ($user_deleted_status == 1 AND $current_date < $recover_account_date_limit) {#Пользователь удален, но существует возможность восстановления аккаунта в течение 72 часов после его удаления
                        $create_new_account = 0;
                        $response["status"] = "ACC-DELETED";#Возможность восстановления аккаунта пользователя в течение 72 часов после его удаления
                        $response["user_id"] = $user_id;
                        $response["message"] = _RECOVER_ACCOUNT_INFO;
                        error_log("[$time $ip] hash_generate: ACC-DELETED user-id - $user_id, phone- $phone\n", 3, $log_file);
                    }  else { #Пользователь удален, срок восставления аккаунта истек
                        $create_new_account = 1;
                    }
                } else {
                    $create_new_account = 1;
                }

                if ($create_new_account == 1) {

                    $user_uuid = gen_uuid(6);
                    $etag_user = gen_uuid(12);
                    $etag_img = gen_uuid(12);

                    if ($res_add_user = $db -> sql_query("INSERT INTO `users` (`id`, `user_uuid`, `login`, `phone`, `password`, `fb_id`, `vk_id`, `tw_id`, `name`, `image_url`, `image_changed_date`, `etag_user`, `etag_img`, `location`, `email`, `birth_day`, `about`, `created_date`, `deleted_date`, `last_changed`, `last_connected`, `last_connected_by`, `accepted`, `created_by`, `hash`, `email_confirm`, `is_official`, `is_check_official`, `is_blocked`, `is_deleted`) VALUES (NULL, '$user_uuid', '', '$phone', '', '', '', '', '', '', '', '$etag_user', '$etag_img', '', '', '', '', '$current_date', '', '$current_date', '$current_date', 'site', '1', 'site', '', '0', '0', '0', '0', '0')")) {

                        $user_id = $db -> sql_nextid($res_add_user);

                        session_start();
                        $_SESSION["uid"] = $user_id;
                        $this -> get_web_session_id($user_id, "phone_number");
                        $response["status"] = "OK";
                        error_log("[$time $ip] user_auth_site: OK создан user_id = $user_id\n", 3, $log_file);

                    } else {
                        $response["status"] = "ERROR";
                        $response["message"] = _ERROR_REGISTRATION . " " . _REPEAT_LATER;
                        error_log("[$time $ip] user_auth_site: SQL-ERROR ошибка выполнения запроса\n", 3, $log_file);

                    }
                }

                #Отмечаем использованный код
                $db -> sql_query("UPDATE `users_auth_hash_codes` SET `is_used`='1' WHERE `phone_number` = '$phone' AND `hash_code` = '$hash_code'", "", "array");

            } else {
                $response["status"] = "ERROR";
                $response["message"] = "Время действия кода истекло. Повторите отправку кода.";
                error_log("[$time $ip] user_auth_site: введен ошибочный код hash-code - $hash_code\n", 3, $log_file);
            }

        } else {
            $response["status"] = "ERROR";
            $response["message"] = "Вы ввели неправильный код";
            error_log("[$time $ip] user_auth_site: ERROR введен ошибочный код hash-code - $hash_code\n", 3, $log_file);
        }

        return $response;        
    }

    # Данный метод произодит отправку хеш-кода на номер телефона пользователя для последующей авторизации пользователя на сайте
    function send_code_to_user($data, $request_type) {
        global $db, $project_options;
        $current_date = time();

        $client_data = $this -> get_client_ip();
        #Удаление лишних символов в строке
        $trimmed_number = preg_replace("/[^0-9]/", "", $data["phone"]);
        $phone= "+".$trimmed_number;

        $result_access_code = get_access_code($request_type, $client_data);
        $access_code = $result_access_code["access_code"];

        $postdata['phone'] = $phone;
        $postdata['lang'] = $data['lang'];
        $postdata = json_encode($data);

        $context = stream_context_create(
            array(
                'http' => array(
                    'header'=> "Content-type: application/json\r\n"
                        . "Request-Type: " . $request_type . "\r\n"
                        . "Client-Data: " . $client_data . "\r\n"
                        . "Access-Code: " . $access_code . "\r\n",
                    'method' => 'POST',
                    'content' => $postdata
                )
            )
        );
        $hash_generate_result =  json_decode(file_get_contents("https://$_SERVER[HTTP_HOST]/api/v1/users/hash_generate", false ,$context), true);

        if ($hash_generate_result["status"] == "OK") {
            $response["status"] = $hash_generate_result["status"];

            #Сохранение в базе отправленного кода для последующего сравнения с введенным пользователем кодом
            $hash_code = $hash_generate_result["message"];
            $db -> sql_query("INSERT INTO `users_auth_hash_codes`(`id`, `phone_number`, `hash_code`, `created_date`, `is_used`) VALUES (NULL, '$phone', '$hash_code', '$current_date', '0')");

        } else {
            $response["status"] = "ERROR";
            $response["message"] = "Неудачная попытка отправки сообщения, попробуйте позднее.";
        }
        return $response;
    }

    # Данный метод произодит запрос восстановления аккаунта к api проекта
    function recovery_account_site($data, $request_type) {

        $client_data = $this -> get_client_ip();

        $result_access_code = get_access_code($request_type, $client_data);
        $access_code = $result_access_code["access_code"];

        $postdata['user_id'] = prepair_str($data["user_id"]);
        $postdata = json_encode($data);

        $context = stream_context_create(
            array(
                'http'=>array(
                    'header'=> "Content-type: application/json\r\n"
                        . "Request-Type: " . $request_type . "\r\n"
                        . "Client-Data: " . $client_data . "\r\n"
                        . "Access-Code: " . $access_code . "\r\n",
                    'method' => 'POST',
                    'content' => $postdata
                )
            )
        );
        $user_recovery_result =  json_decode(file_get_contents("https://$_SERVER[HTTP_HOST]/api/v1/users/user_recovery", false ,$context), true);

        if ($user_recovery_result["status"] == "OK") {
            $response["status"] = "OK";
            $user_id = $user_recovery_result["user_id"];
            session_start();
            $_SESSION["uid"] = $user_id;
            $this -> get_web_session_id($user_id, "recover");
        } else {
            $response["status"] = "ERROR";
            $response["message"] = "Неудачная попытка восстановления аккаунта, попробуйте позднее.";
        }
        return $response;
    }

    # Данный метод произодит авторизацию или создание нового пользователя мобильного приложения в системе
    # В случае авторизации проверяется устройство пользователя по device_uuid, если данное устройство не зарегистрировано, то оно регистрируется
    function user_add_app($data, $current_request_type) {
        global $db, $log_file, $project_options;
        $client_request_access = client_request_access_status($current_request_type);

        if ($client_request_access["status"] == "OK") {
            $ip = $this -> get_client_ip();
            $time = date("H:i");
            $current_date = time();

            $expected_params = array();
            if (isset($data["phone"]) AND strlen($data["phone"]) > 7) {
                #Удаление лишних символов в строке
                $trimmed_number = preg_replace("/[^0-9]/", "", $data["phone"]);

                if (strlen($trimmed_number) == 11) {
                    #Если получен номер в формате 89110000000, заменяем первый символ на 7
                    $trimmed_number = substr_replace($trimmed_number, '7', 0, 1);
                }
                $phone = "+".$trimmed_number;
            } else {
                $expected_params[] = "phone";
            }

            if (isset($data["operating_sys"]) AND !empty($data["operating_sys"])) {
                $operating_sys = prepair_str($data["operating_sys"]);
            } else {
                $expected_params[] = "operating_sys";
            }

            if (isset($data["device_model"]) AND !empty($data["device_model"])) {
                $device_model = prepair_str($data["device_model"]);
            } else {
                $expected_params[] = "device_model";
            }

            if (isset($data["device_uuid"]) AND !empty($data["device_uuid"])) {
                $device_uuid = prepair_str($data["device_uuid"]);
            } else {
                $expected_params[] = "device_uuid";
            }

            if (isset($data["device_token"]) AND !empty($data["device_token"])) {
                $device_token = prepair_str($data["device_token"]);
            } else {
                $expected_params[] = "device_token";
            }

            if (isset($data["lang"]) AND !empty($data["lang"])) {
                $lang = prepair_str($data["lang"]);
                if (preg_match("/en/i", $lang)) {
                    $recover_account_info = "Данный аккаунт удален. Вы можете восстановить аккаунт в течение 72 часов с момента его удаления.";
                } else {
                    $recover_account_info = "Данный аккаунт удален. Вы можете восстановить аккаунт в течение 72 часов с момента его удаления.";
                }

            } else {
                $expected_params[] = "lang";
            }

            if (count($expected_params) > 0 ){
                $expected = implode(", ", $expected_params);
                $error = "Не получены параметры: $expected";
                $response["status"] = "ERROR";
                $response["message"] = $error;
                error_log("[$time $ip] user_add: $error\n", 3, $log_file);
            } else {

                $phone = "+".$trimmed_number;

                $result_user = $db -> sql_query("SELECT * FROM `users` WHERE `phone` = '$phone' AND `id` = (SELECT MAX(`id`) FROM `users` WHERE `phone` = '$phone' GROUP BY `phone`)", "", "array");

                if (sizeof($result_user) > 0 AND !empty($result_user[0])) { #Пользователь с данным номером телефона существует в системе
                    $user_id = $result_user[0]["id"];
                    $user_deleted_status = $result_user[0]["is_deleted"];
                    $user_deleted_date = $result_user[0]["deleted_date"];
                    $recover_account_date_limit = $user_deleted_date + 60*60*72;

                    if ($user_deleted_status == 0) { #Пользователь с данным номером телефона существует в системе и он не удален
                        $create_new_account = 0;

                        $db -> sql_query("UPDATE `users` SET `last_connected` = '$current_date', `last_connected_by` = 'app' WHERE `id` = '$user_id' AND `is_deleted` = '0'");
                        $result_device = $db -> sql_query("SELECT * FROM `devices` WHERE `user_id` = '$user_id' AND `device_uuid` = '$device_uuid' AND `is_deleted` = '0'", "", "array");

                        if (sizeof($result_device) > 0) {
                            $device_id = $result_device[0]["id"];
                            #Устройство пользователя зарегистрировано раннее, производится обновление данных
                            $device_sql_query = array();
                            $device_sql_query[] = "`operating_system` = '$operating_sys'";
                            $device_sql_query[] = "`device_token` = '$device_token'";
                            $device_sql_query[] = "`device_token_is_correct` = '1'";
                            $device_sql_query[] = "`lang` = '$lang'";

                            $device_sql = implode(", ", $device_sql_query);

                            $db -> sql_query("UPDATE `devices` SET $device_sql WHERE `user_id` = '$user_id' AND `device_uuid` = '$device_uuid'");
                            $log_file_data = "авторизация  user_id - $user_id, обновление данных устройства device_id - '$device_id' $device_sql\n";

                        } else {
                            $sha1_encrypt_id = sha1($user_id.$device_uuid);

                            #Устройство пользователя не найдено по device_uuid, производится регистрация нового устройства
                            $res_add_device = $db -> sql_query("INSERT INTO `devices` (`id`, `sha1_encrypt_id`, `user_id`, `operating_system`, `device_model`, `device_uuid`, `device_token`, `lang`, `new_stream_notify_settings`, `new_follower_notify_settings`, `ratio`, `is_blocked`, `is_deleted`) VALUES (NULL, '$sha1_encrypt_id', '$user_id', '$operating_sys', '$device_model', '$device_uuid', '$device_token', '$lang', '1', '1', '0', '0', '0')");
                            $log_file_data = "авторизация  user_id - $user_id, регистрация нового устройства device_uuid - '$device_uuid'\n";
                            $device_id = $db -> sql_nextid($res_add_device);
                        }
                        $response["status"] = "OK";
                        $response["message"] = $user_id;
                        $response["session_id"] = $this -> get_device_session_id($device_id, "phone_number");
                        error_log("[$time $ip] user_auth_app: OK прошел авторизацию user_id = $user_id, $log_file_data\n", 3, $log_file);

                    } else if ($user_deleted_status == 1 AND $current_date < $recover_account_date_limit) {#Пользователь удален, но существует возможность восстановления аккаунта в течение 72 часов после его удаления
                        $create_new_account = 0;
                        $response["status"] = "ACC-DELETED"; #Возможность восстановления аккаунта пользователя в течение 72 часов после его удаления
                        $response["user_id"] = $user_id;
                        $response["message"] = $recover_account_info;
                        error_log("[$time $ip] hash_generate: ACC-DELETED user-id - $user_id, phone- $phone\n", 3, $log_file);
                    }  else { #Пользователь удален, срок восставления аккаунта
                        $create_new_account = 1;
                    }
                } else {
                    $create_new_account = 1;
                }

                if ($create_new_account == 1) {
                    #Создание нового пользователя
                    $result_last_id = $db -> sql_query("SELECT max(id) FROM `users`", "", "array");
                    $last_id = $result_last_id[0]["max(id)"];
                    $lodin_id = $last_id + 1;
                    $login = $lodin_id."profile";

                    $user_uuid = gen_uuid(6);
                    $etag_user = gen_uuid(12);
                    $etag_img = gen_uuid(12);

                    if ($result_add_user = $db -> sql_query("INSERT INTO `users` (`id`, `user_uuid`, `login`, `phone`, `password`, `fb_id`, `vk_id`, `tw_id`, `name`, `image_url`, `image_changed_date`, `etag_user`, `etag_img`, `location`, `email`, `birth_day`, `about`, `created_date`, `deleted_date`, `last_changed`, `last_connected`, `last_connected_by`, `accepted`, `created_by`, `hash`, `email_confirm`, `is_official`, `is_check_official`, `is_blocked`, `is_deleted`) VALUES (NULL, '$user_uuid', '$login', '$phone', '', '', '', '', '', '', '', '$etag_user', '$etag_img', '', '', '', '', '$current_date', '', '$current_date', '$current_date', 'app', '1', 'app', '', '0', '0', '0', '0', '0')")) {

                        $user_id = $db -> sql_nextid($result_add_user);

                        $sha1_encrypt_id = sha1($user_id.$device_uuid);

                        $res_add_device = $db -> sql_query("INSERT INTO `devices` (`id`, `sha1_encrypt_id`, `user_id`, `operating_system`, `device_model`, `device_uuid`, `device_token`, `lang`, `new_stream_notify_settings`, `new_follower_notify_settings`, `ratio`, `is_blocked`, `is_deleted`) VALUES (NULL, '$sha1_encrypt_id', '$user_id', '$operating_sys', '$device_model', '$device_uuid', '$device_token', '$lang', '1', '1', '0', '0', '0')");
                        $device_id = $db -> sql_nextid($res_add_device);

                        $response["status"] = "OK";
                        $response["message"] = $user_id;
                        $response["session_id"] = $this -> get_device_session_id($device_id, "phone_number");
                        error_log("[$time $ip] user_add_app: OK успешно создан user id = $user_id ($operating_sys)\n", 3, $log_file);

                    } else {
                        $response["status"] = "ERROR";
                        $response["message"] = "Ошибка выполнения запроса";
                        error_log("[$time $ip] user_add_app: SQL-ERROR ошибка выполнения запроса добавления пользователя\n", 3, $log_file);
                    }
                }
            }

            return $response;
        } else {
            return $client_request_access;
        }
    }

    # Метод обновления etag при измении объекта пользователь
    function etag_user_update($user_id = 0){
        global $db;
        $etag_user = gen_uuid(12);
        $db -> sql_query("UPDATE `users` SET `etag_user` = '$etag_user' WHERE `id` = '$user_id'");
    }

    # Метод обновления etag при измении изображения пользователя
    function etag_img_update($user_id = 0){
        global $db;
        $etag_img = gen_uuid(12);
        $db -> sql_query("UPDATE `users` SET `etag_img` = '$etag_img' WHERE `id` = '$user_id'");
    }

    # Метод закрытия текущей сессии мобильного приложения пользователя (выход из учетной записи на устройстве)
    function user_logout_app($current_request_type) {
        global $log_file;

        $client_request_access = client_request_access_status($current_request_type);

        if ($client_request_access["status"] == "OK") {
            $ip = $this -> get_client_ip();
            $time = date("H:i");

            if (isset($client_request_access["device_id"])) {
                $device_id = $client_request_access["device_id"];
                $this -> destroy_device_sessions($device_id);
                $response["status"] = "OK";
            } else {
                $response["status"] = "ACCESS-DENIED";
                $response["message"] = "NOT-FOUND";
                $log_file_info = implode(', ', $client_request_access);
                error_log("[$time $ip] user_logout_app: NOT-FOUND $log_file_info\n", 3, $log_file);
            }
            return $response;
        } else {
            return $client_request_access;
        }
    }

    # Метод закрытия всех текущих сессий пользователя
    function user_logout_all_devices($current_request_type) {
        global $log_file;

        $client_request_access = client_request_access_status($current_request_type);

        if ($client_request_access["status"] == "OK" ) {
            $ip = $this -> get_client_ip();
            $time = date("H:i");

            if (isset($client_request_access["user_id"])) {
                $user_id = $client_request_access["user_id"];
                $this -> destroy_all_sessions($user_id);
                $response["status"] = "OK";
                error_log("[$time $ip] user_logout_all_devices: OK user_id - $user_id\n", 3, $log_file);
            } else {
                $response["status"] = "ACCESS-DENIED";
                $response["message"] = "NOT-FOUND";
                error_log("[$time $ip] user_logout_all_devices: ACCESS-DENIED\n", 3, $log_file);
            }

            return $response;
        } else {
            return $client_request_access;
        }

    }

    # Закрытие всех сессий пользователя (в веб-приложении и мобильных приложениях)
    function destroy_all_sessions($user_id = 0){
        global $db, $log_file;

        $ip = $this -> get_client_ip();
        $time = date("H:i");
        $current_date = time();

        $result_devices = $db -> sql_query("SELECT `devices`.`id` AS `device_id` FROM `users` LEFT JOIN `devices` ON `users`.`id` = `devices`.`user_id` WHERE `users`.`id` = '$user_id' AND `users`.`is_deleted` = '0' AND `devices`.`is_deleted` = '0'", "", "array");

        if (sizeof($result_devices) > 0){
            foreach ($result_devices as $value) {
                $device_id = $value["device_id"];
                $result_sessions_devices = $db -> sql_query("SELECT * FROM `users_sessions_devices` WHERE `device_id` = '$device_id' AND `end_date` = '0'", "", "array");
                if (sizeof($result_sessions_devices) > 0){
                    foreach ($result_sessions_devices as $v) {
                        $session_id = $v["session_id"];
                        $db -> sql_query("UPDATE `users_sessions_devices` SET `end_date` = '$current_date' WHERE `device_id` = '$device_id'");
                        error_log("[$time $ip] destroy_all_sessions: OK device-session closed session_id - $session_id \n", 3, $log_file);
                    }
                }
            }
        }

        $result_sessions_web = $db -> sql_query("SELECT * FROM `users_sessions_web` WHERE `user_id` = '$user_id' AND `end_date` = '0'", "", "array");
        if (sizeof($result_sessions_web) > 0){
            foreach ($result_sessions_web as $val) {
                $web_session_id = $val["session_id"];
                $db -> sql_query("UPDATE `users_sessions_web` SET `end_date` = '$current_date' WHERE `session_id` = '$web_session_id'");
                error_log("[$time $ip] destroy_all_sessions: OK web-session closed session_id - $web_session_id \n", 3, $log_file);
            }
        }

    }

    # Метод закрытия сессии мобильного приложения пользователя
    function destroy_device_sessions($device_id = 0){
        global $db, $log_file;

        $ip = $this -> get_client_ip();
        $time = date("H:i");
        $current_date = time();

        $result_expired_sessions = $db -> sql_query("SELECT * FROM `users_sessions_devices` WHERE `device_id` = '$device_id' AND `end_date` = '0'", "", "array");
        if (sizeof($result_expired_sessions) > 0){
            foreach ($result_expired_sessions as $value) {
                $expired_session_id = $value["session_id"];
                $db -> sql_query("UPDATE `users_sessions_devices` SET `end_date` = '$current_date' WHERE `session_id` = '$expired_session_id'");
                error_log("[$time $ip] destroy_devices_sessions: закрыта сессия session_id - $expired_session_id \n", 3, $log_file);
            }
        }
    }

    # Метод проверки текущей сессии веб-приложения
    function check_web_session_id(){
        global $db, $log_file;

        $ip = $this -> get_client_ip();
        $time = date("H:i");
        $current_date = time();

        $user_id = $_SESSION["uid"];
        $current_session_id = $_SESSION["web_session_id"];
        $current_user_agent = $_SERVER["HTTP_USER_AGENT"];

        $result = false;

        $result_sessions = $db -> sql_query("SELECT * FROM `users_sessions_web` WHERE `user_id` = '$user_id' AND `id` = (SELECT MAX(`id`) FROM `users_sessions_web` WHERE `user_id` = '$user_id' AND `user_agent` = '$current_user_agent' GROUP BY `user_id`)", "", "array");
        if (sizeof($result_sessions) > 0) {
            foreach ($result_sessions as $value) {
                $session_id = $value["session_id"];
                $end_date = $value["end_date"];
                if ($current_session_id == $session_id) {
                    if ($end_date == 0) {
                        $result = true;
                    }
                } else {
                    if ($end_date == 0) {
                        $db -> sql_query("UPDATE `users_sessions_web` SET `end_date` = '$current_date' WHERE `session_id` = '$session_id' AND `user_id` = '$user_id' AND `user_agent` = '$current_user_agent'");
                    }
                }
            }
        } else {
            error_log("[$time $ip] check_web_session_id: sessions not-found user_id - $user_id, user_agent = $current_user_agent\n", 3, $log_file);
        }
        return $result;
    }

    #Сохранение в проект изображения профиля пользователя в методах авторизации через социальные сети
    function save_user_image_from_url($image_url = "", $user_uuid = ""){
        global $log_file, $profile_images_dir, $tmp_profile_images_dir;

        $response["status"] = "";
        $response["message"] = "";

        $ip = $this -> get_client_ip();
        $time = date("H:i");

        $user_image = file_get_contents($image_url);
        $extention = get_image_mime_type($user_image); #Формат изображения

        $tmp_image_url = $tmp_profile_images_dir.$user_uuid.".".$extention;
        $fp = fopen($tmp_image_url, "w");
        fwrite($fp, $user_image); #Сохранение временного изображения (в исходном формате)
        fclose($fp);

        $size = getimagesize($tmp_image_url);
        if ($size) {
            $width = $size["0"];
            $height = $size["1"];
            $mime = $size["mime"];
            if (preg_match("/png/i",$mime)) {
                $jpg_image = imagecreatefrompng($image_url);
            } else if (preg_match("/gif/i",$mime)){
                $jpg_image = imagecreatefromgif($image_url);
            } else if (preg_match("/jpeg/i",$mime)){
                $jpg_image = imagecreatefromjpeg($image_url);
            }

            if ($jpg_image) {
                $tmp = imagecreatetruecolor($width, $height); #Создание временного изображения (в формате jpg)
                imagecopyresampled($tmp, $jpg_image, 0, 0, 0, 0, $width, $height, $width, $height);
                $image_url = $profile_images_dir.$user_uuid.".jpg";
                imagejpeg($tmp, $image_url); #Сохранение временного изображения (в формате jpg)
                unlink($tmp_image_url); #Удаление временного изображения (в исходном формате из директории tmp_images)

                $response["status"] = "OK";
                $response["image_url"] = $image_url;
                error_log("[$time $ip] save_img_from_url: OK создано изображение профиля image_url = $image_url \n", 3, $log_file);
            } else {
                $response["status"] = "ERROR";
                error_log("[$time $ip] save_img_from_url: ERROR не удалось создать изображение image_url = $image_url, user_uuid = $user_uuid \n", 3, $log_file);
            }
        } else {
            $response["status"] = "ERROR";
            error_log("[$time $ip] save_img_from_url: ERROR не удалось получить размер изображения image_url = $image_url, user_uuid = $user_uuid\n", 3, $log_file);
        }
        return $response;
    }

    function image_tmp($file_data, $user_id){
        global $db, $tmp_profile_images_dir, $log_file;

        $ip = $this -> get_client_ip();
        $time = date("H:i");

        $array["danger"] = "";
        $array["success"] = "";

        $result = $db -> sql_query("SELECT * FROM `users` WHERE `id` = '$user_id' AND `is_deleted` = '0'", "", "array");
        if (sizeof($result) > 0 AND !empty($result[0])) {

            $user_uuid = $result[0]["user_uuid"];
            if (empty($user_uuid)) { #Поддержка пользователей, зарегистрированных ранее 31.03.2016
                $user_uuid = gen_uuid(6);
                $db -> sql_query("UPDATE `users` SET `user_uuid` = '$user_uuid' WHERE `id` = '$user_id' AND `is_deleted` = '0' LIMIT 1");
            }

            $tmp_name = $file_data["profile_image"]["tmp_name"];
            $error = $file_data["profile_image"]["error"];
            $file_size = $file_data["profile_image"]["size"];
            $img_info = getimagesize($tmp_name);

            if ($img_info === FALSE) {
                $array["danger"] = _ERROR_IMAGE_FORMAT;
                error_log("[$time $ip] image_tmp: недопустимый формат файла user_id = $user_id\n", 3, $log_file);
            } else if ($file_size > 1024*10*1024) {
                $array["danger"] = _ERROR_IMAGE_SIZE;
                error_log("[$time $ip] image_tmp: недопустимый размер файла user_id = $user_id\n", 3, $log_file);
            } else if ($error > 0) {
                $array["danger"] = _ERROR_GET_IMAGE;
                error_log("[$time $ip] image_tmp: код ошибки $error user_id = $user_id\n", 3, $log_file);
            } else {
                $width = $img_info[0];
                $height = $img_info[1];

                switch ($img_info[2]) {
                    case IMAGETYPE_GIF  :
                        $jpg_image = imagecreatefromgif($tmp_name);
                        break;
                    case IMAGETYPE_JPEG :
                        $jpg_image = imagecreatefromjpeg($tmp_name);
                        break;
                    case IMAGETYPE_PNG  :
                        $jpg_image = imagecreatefrompng($tmp_name);
                        break;
                }
                if ($jpg_image) {
                    $tmp = imagecreatetruecolor($width, $height);
                    imagecopyresampled($tmp, $jpg_image, 0, 0, 0, 0, $width, $height, $width, $height);
                    $image_url = $tmp_profile_images_dir.$user_uuid.".jpg";
                    imagejpeg($tmp, $image_url);
                    $array["success"] = "OK";
                    $array["user_data"] = $user_uuid;
                } else {
                    $array["danger"] = _ERROR_IMAGE_FORMAT;
                    error_log("[$time $ip] image_tmp: недопустимый формат файла user_id = $user_id\n", 3, $log_file);
                }
            }
        } else {
            $array["danger"] = "Ошибка. Попробуйте позднее";
            error_log("[$time $ip] image_tmp: пользователь не найден user_id = $user_id\n", 3, $log_file);
        }

        return $array;
    }

    # Метод редактирования пользователя (для веб приложения)
    function user_edit($data, $user_id = 0) {
        global $db, $log_file, $profile_images_dir, $tmp_profile_images_dir;

        $ip = $this -> get_client_ip();
        $time = date("H:i");
        $current_date = time();

        $user_id = prepair_str($user_id);
        $warning_params = array();
        $sql_query = "";

        $result = $db -> sql_query("SELECT * FROM `users` WHERE `id` = '$user_id' AND `is_deleted` = '0'", "", "array");
        if (sizeof($result) > 0) {

            $user_uuid = $result[0]["user_uuid"];
            $current_email = $result[0]["email"];

            if (isset($data["login"]) AND !empty($data["login"])) {
                $login = prepair_str($data["login"]);
                $result_login = $db -> sql_query("SELECT `id` FROM `users` WHERE `login` = '$login' AND `id` != '$user_id' AND `is_deleted` = '0'", "", "array");
                if (sizeof($result_login) > 0) {
                    $warning_params[] = "login";
                    $response["message"][] = _OTHER_LOGIN;
                }
            } else {
                $warning_params[] = "empty login";
                $response["message"][] = _ERROR_FILL_LOGIN;
            }

            if (isset($data["email"]) AND !empty($data["email"]) AND filter_var($data["email"], FILTER_VALIDATE_EMAIL) AND $data["email"] != $current_email) {
                $email = prepair_str($data["email"]);
                $sql_query[] = "email = '$email'";
                $result_email = $db -> sql_query("SELECT `id` FROM `users` WHERE `email` = '$email' AND `id` != '$user_id' AND `is_deleted` = '0'", "", "array");
                if (sizeof($result_email) > 0) {
                    $warning_params[] = "email";
                    $response["message"][] = _OTHER_EMAIL;
                }
            }

            $name = "";
            if (isset($data["name"]) AND !empty($data["name"])){
                $name = prepair_str($data["name"]);
            }

            $about = "";
            if (isset($data["about"]) AND !empty($data["about"])){
                $about = $data["about"];
            }

            $is_official = 0;
            if (isset($data["is_official"]) AND ($data["is_official"] == "on")){
                $is_official = 1;
            }

            $is_image_changed = prepair_str($data["is_image_changed"]);

            if (count($warning_params) > 0 ){
                $warning_params = implode(", ", $warning_params);
                $response["status"] = "ERROR";
                error_log("[$time $ip] user_edit: $warning_params\n", 3, $log_file);
            } else {
                # При изменении изображения профиля: копирование временного изображения в директорию с изображениями профилей пользователей
                if ($is_image_changed == 1){
                    $tmp_image = $tmp_profile_images_dir.$user_uuid.".jpg";
                    $image = $profile_images_dir.$user_uuid.".jpg";

                    if (copy($tmp_image, $image)) {
                        #Удаление временного изображения
                        unlink($tmp_image);
                        $sql_query = "image_url = '$image', image_changed_date = '$current_date',";
                        $this -> etag_img_update($user_id); # Обновление etag изображения пользователя
                    } else {
                        error_log("[$time $ip] user_edit: не удалось скопировать изображение\n", 3, $log_file);
                    }
                }

                if (!empty($about)){#Описание профиля может содержать теги, запись тегов в базу
                    $data_tags = get_hashtags_from_string($about);

                    $result_user_tags = $db -> sql_query("SELECT `profiles_tags_data`.`name` AS `tag_name` FROM `profiles_tags` LEFT JOIN `profiles_tags_data` ON `profiles_tags_data`.`id` = `profiles_tags`.`tag_id` WHERE `profiles_tags`.`user_id` = '$user_id' AND `profiles_tags`.`is_deleted` = '0'", "", "array");
                    #Создание массива тегов аккаунта
                    $user_tags  = array();
                    foreach ($result_user_tags as $value){
                        array_push($user_tags, $value["tag_name"]);
                    }

                    $result_existing_tags = $db -> sql_query("SELECT * FROM `profiles_tags_data`", "", "array");
                    $existing_tags  = array();
                    foreach ($result_existing_tags as $value){
                        array_push($existing_tags, $value["name"]);
                    }

                    foreach ($data_tags as $value){
                        if ($value != "") {
                            if ((sizeof($user_tags) > 0) && (in_array($value, $user_tags))){#Проверка существования данного тега в аккаунте
                                $array["success"][] = $value." exist";
                            } else {
                                if (in_array($value, $existing_tags)){ #Получение id существующего тега
                                    $result_tag = $db -> sql_query("SELECT `id` FROM `profiles_tags_data` WHERE `name` = '$value'", "", "array");
                                    $tag_id = $result_tag[0]["id"];
                                } else { #Создание нового тега
                                    $result_add_tag = $db -> sql_query("INSERT INTO `profiles_tags_data` (`id`, `name`, `is_disabled`) VALUES (NULL, '$value', '0')");
                                    $tag_id = $db -> sql_nextid($result_add_tag);
                                }
                                #Тег был добавлен, затем удален и сейчас добавляется снова
                                $result = $db -> sql_query("SELECT * FROM `profiles_tags` WHERE `tag_id` = '$tag_id' AND `user_id` = '$user_id' AND `is_deleted` = '1'", "", "array");
                                if ((sizeof($result) > 0)) {
                                    $db -> sql_query("UPDATE `profiles_tags` SET `is_deleted` = '0' WHERE `user_id` = '$user_id' AND `tag_id` = '$tag_id' AND `is_deleted` = '1'");
                                } else {
                                    $db -> sql_query("INSERT INTO `profiles_tags` (`id`, `user_id`, `tag_id`, `is_deleted`) VALUES (NULL, '$user_id', '$tag_id', '0')");
                                }
                            }
                        }
                    }

                    #Удаленные теги помечаются is_deleted = '1'
                    $result_deleted_tags = array_diff($user_tags, $data_tags);
                    if (sizeof($result_deleted_tags) > 0){
                        foreach ($result_deleted_tags as $value){
                            $result_id = $db -> sql_query("SELECT `id` FROM `profiles_tags_data` WHERE `name` = '$value'", "", "array");
                            if (sizeof($result_id) > 0){
                                $id = $result_id[0]["id"];
                                $db -> sql_query("UPDATE `profiles_tags` SET `is_deleted` = '1' WHERE `tag_id` = '$id' AND `user_id` = '$user_id'");
                            }
                        }
                    }
                }

                # Редактирование профиля
                if ($db -> sql_query("UPDATE `users` SET $sql_query `login` = '$login', `name` = '$name', `about` = '$about', `last_changed` = '$current_date', `is_official` = '$is_official' WHERE `id` = '$user_id' AND `is_deleted` = '0' LIMIT 1")){
                    $this -> etag_user_update($user_id); # Обновление etag пользователя
                    $response["status"] = "OK";
                    $response["message"] = "<div class=\"alert alert-success\">" . _REQUEST_SUCCESS_INFO . "</div>";
                } else {
                    $response["status"] = "ERROR";
                    $response["message"][] = _REQUEST_FAILED_INFO;
                    error_log("[$time $ip] user_edit: sql_error user_id = $user_id\n", 3, $log_file);
                }
            }

        } else {
            $response["status"] = "ERROR";
            $response["message"][] = _REQUEST_FAILED_INFO;
            error_log("[$time $ip] user_edit: не найден user_id = $user_id\n", 3, $log_file);
        }

        return $response;
    }

    # Метод редактирования пользователя (для мобильных приложений)
    function user_edit_app($data, $current_request_type){
        global $db, $log_file, $profile_images_dir;

        $client_request_access = client_request_access_status($current_request_type);

        if ($client_request_access["status"] == "OK") {
            $response = array();

            $ip = $this -> get_client_ip();
            $time = date("H:i");
            $current_date = time();
            $user_id = $client_request_access["user_id"];

            if ($data){
                $result = $db -> sql_query("SELECT * FROM `users` WHERE `id` = '$user_id' AND `is_deleted` = '0'", "", "array");
                if (sizeof($result) > 0 AND !empty($result[0])) {

                    $user_uuid = $result[0]["user_uuid"];
                    $current_email = $result[0]["email"];
                    
                    if (empty($user_uuid)) { #Поддержка пользователей, зарегистрированных ранее 31.03.2016
                        $user_uuid = gen_uuid(6);
                        $db -> sql_query("UPDATE `users` SET `user_uuid` = '$user_uuid' WHERE `id` = '$user_id' AND `is_deleted` = '0' LIMIT 1");
                    }

                    $sql_query = array();
                    $warning_array = array();

                    $image_data = "";
                    if (isset($data["image_data"]) AND !empty($data["image_data"])) {
                        $image_data = $data["image_data"];
                        $image_url = $profile_images_dir.$user_uuid.".jpg";
                        $sql_query[] = "image_url = '$image_url'";
                        $sql_query[] = "image_changed_date = '$current_date'";
                    }

                    if (isset($data["login"]) AND !empty($data["login"])) {
                        $login = prepair_str($data["login"]);
                        $sql_query[] = "login = '$login'";
                        $result_login = $db -> sql_query("SELECT `id` FROM `users` WHERE `login` = '$login' AND `id` != '$user_id' AND `is_deleted` = '0'", "", "array");
                        if (sizeof($result_login) > 0) {
                            $warning_array[] = "login";
                        }
                    }

                    if (isset($data["phone"]) AND !empty($data["phone"])) {
                        $phone = prepair_str($data["phone"]);
                        $sql_query[] = "phone = '$phone'";
                        $result_phone = $db -> sql_query("SELECT `id` FROM `users` WHERE `phone` = '$phone' AND `id` != '$user_id' AND `is_deleted` = '0'", "", "array");
                        if (sizeof($result_phone) > 0) {
                            $warning_array[] = "phone";
                        }
                    }

                    if (isset($data["email"]) AND !empty($data["email"]) AND filter_var($data["email"], FILTER_VALIDATE_EMAIL) AND $data["email"] != $current_email) {
                        $email = prepair_str($data["email"]);
                        $sql_query[] = "email = '$email'";
                        $result_email = $db -> sql_query("SELECT `id` FROM `users` WHERE `email` = '$email' AND `id` != '$user_id' AND `is_deleted` = '0'", "", "array");
                        if (sizeof($result_email) > 0) {
                            $warning_array[] = "email";
                        }
                    }

                    if (isset($data["name"]) AND !empty($data["name"])) {
                        $name = prepair_str($data["name"]);
                        $sql_query[] = "name = '$name'";
                    }

                    if (isset($data["about"]) AND !empty($data["about"])) {
                        $about = $data["about"];
                        $sql_query[] = "about = '$about'";
                    }

                    if (count($warning_array) > 0 ){
                        $warning_params = implode(", ", $warning_array);
                        $response["status"] = "ERROR";
                        $response["message"] = "Требуется введение уникальных параметров: $warning_params";
                    } else {
                        if (count($sql_query) > 0 ){
                            $sql_query[] = "`last_changed` = '$current_date'";
                            $sql = implode(", ", $sql_query);

                            if (!empty($image_data)){
                                $base64_decode = base64_decode($image_data);

                                $url = "data://application/octet-stream;base64,"  . $image_data;
                                $size =  getimagesize($url);

                                if ($size) {
                                    $image_url = $profile_images_dir.$user_uuid.".jpg";
                                    $tmp_image = imagecreatefromstring($base64_decode);
                                    if ($tmp_image !== false) {

                                        $this -> etag_img_update($user_id); # Обновление etag изображения пользователя

                                        imagejpeg($tmp_image, $image_url);
                                        imagedestroy($tmp_image);
                                        error_log("[$time $ip] user_edit_app: создано изображение профиля image_url = $image_url, user_id = $user_id\n", 3, $log_file);
                                    }
                                    else {
                                        error_log("[$time $ip] user_edit_app: ошибка imagecreatefromstring user_id = $user_id\n", 3, $log_file);
                                    }
                                } else {
                                    error_log("[$time $ip] user_edit_app: не удалось получить размер изображения user_id = $user_id\n", 3, $log_file);
                                }
                            }

                            if (!empty($about)){
                                $data_tags = get_hashtags_from_string($about);
                                $result_user_tags = $db -> sql_query("SELECT `profiles_tags_data`.`name` AS `tag_name` FROM `profiles_tags` LEFT JOIN `profiles_tags_data` ON `profiles_tags_data`.`id` = `profiles_tags`.`tag_id` WHERE `profiles_tags`.`user_id` = '$user_id' AND `profiles_tags`.`is_deleted` = '0'", "", "array");
                                #Создание массива тегов аккаунта
                                $user_tags  = array();
                                foreach ($result_user_tags as $value){
                                    array_push($user_tags, $value["tag_name"]);
                                }

                                #Создание массива всех тегов проекта (профиль)
                                $result_existing_tags = $db -> sql_query("SELECT*FROM profiles_tags_data", "", "array");
                                $existing_tags  = array();
                                foreach ($result_existing_tags as $value){
                                    array_push($existing_tags, $value["name"]);
                                }

                                foreach ($data_tags as $value){
                                    if ($value != "") {
                                        if (!in_array($value, $user_tags)){#Проверка существования данного тега в аккаунте

                                            if (in_array($value, $existing_tags)){ #Получение id существующего тега
                                                $result_tag = $db -> sql_query("SELECT `id` FROM `profiles_tags_data` WHERE `name` = '$value'", "", "array");
                                                $tag_id = $result_tag[0]["id"];
                                            } else { #Создание нового тега
                                                $result_add_tag = $db -> sql_query("INSERT INTO `profiles_tags_data` (`id`, `name`, `is_disabled`) VALUES (NULL, '$value', '0')");
                                                $tag_id = $db -> sql_nextid($result_add_tag);
                                            }
                                            #Тег был добавлен, затем удален и сейчас добавляется снова
                                            $result = $db -> sql_query("SELECT * FROM `profiles_tags` WHERE `tag_id` = '$tag_id' AND `user_id` = '$user_id' AND `is_deleted` = '1'", "", "array");
                                            if ((sizeof($result) > 0)) {
                                                $db -> sql_query("UPDATE `profiles_tags` SET `is_deleted` = '0' WHERE `user_id` = '$user_id' AND `tag_id` = '$tag_id' AND `is_deleted` = '1'");
                                            } else {
                                                $db -> sql_query("INSERT INTO `profiles_tags` (`id`, `user_id`, `tag_id`, `is_deleted`) VALUES (NULL, '$user_id', '$tag_id', '0')");
                                            }
                                        }
                                    }
                                }

                                #Удаленные теги помечаются is_deleted = '1'
                                $result_deleted_tags = array_diff($user_tags, $data_tags);
                                if (sizeof($result_deleted_tags) > 0){
                                    foreach ($result_deleted_tags as $value){
                                        $result_id = $db -> sql_query("SELECT `id` FROM `profiles_tags_data` WHERE `name` = '$value'", "", "array");
                                        if (sizeof($result_id) > 0){
                                            $id = $result_id[0]["id"];
                                            $db -> sql_query("UPDATE `profiles_tags` SET `is_deleted` = '1' WHERE `tag_id` = '$id' AND `user_id` = '$user_id'");
                                        }
                                    }
                                }
                            }

                            if ($db -> sql_query("UPDATE `users` SET $sql WHERE `id` = '$user_id' AND `is_deleted` = '0' LIMIT 1")){
                                $this -> etag_user_update($user_id); # Обновление etag пользователя
                                $response["status"] = "OK";
                            } else {
                                $response["status"] = "ERROR";
                                $response["message"] = "Ошибка выполнения запроса";

                                error_log("[$time $ip] user_edit_app: SQL-ERROR user_id = $user_id\n", 3, $log_file);
                            }
                        }

                        if (isset($client_request_access["device_uuid"])) {
                            $device_uuid = $client_request_access["device_uuid"];
                        } else {
                            if (isset($data["device_uuid"]) AND !empty($data["device_uuid"])) {
                                $device_uuid = prepair_str($data["device_uuid"]);
                            }
                        }


                        if ($device_uuid AND ((isset($data["device_token"]) AND !empty($data["device_token"]))) OR ((isset($data["lang"]) AND !empty($data["lang"])))) {
                            $device_sql_query = array();

                            if (isset($data["device_token"]) AND !empty($data["device_token"])) {
                                $device_token = $data["device_token"];
                                $device_sql_query[] = "`device_token` = '$device_token'";
                                $device_sql_query[] = "`device_token_is_correct` = '1'";
                            }

                            if (isset($data["lang"]) AND !empty($data["lang"])) {
                                $lang = $data["lang"];
                                $device_sql_query[] = "lang = '$lang'";
                            }

                            $device_sql = implode(", ", $device_sql_query);

                            $result_device = $db -> sql_query("SELECT * FROM `devices` WHERE `user_id` = '$user_id' AND `device_uuid` = '$device_uuid'", "", "array");
                            if (sizeof($result_device) > 0) {
                                if ($db -> sql_query("UPDATE `devices` SET $device_sql WHERE `user_id` = '$user_id' AND `device_uuid` = '$device_uuid'")){
                                    $response["status_device_edit"] = "OK";
                                    error_log("[$time $ip] user_edit_app: OK $device_sql (device_uuid = '$device_uuid', user_id = '$user_id')\n", 3, $log_file);
                                } else {
                                    $response["status_device_edit"] = "ERROR";
                                    error_log("[$time $ip] user_edit_app: SQL-ERROR ошибка изменения device_token = '$device_token' (device_uuid = '$device_uuid', user_id = '$user_id')\n", 3, $log_file);
                                }

                            } else {
                                $response["status_device_edit"] = "ERROR";
                                error_log("[$time $ip] user_edit_app: ERROR не найдено устройство device_uuid = '$device_uuid', user_id = '$user_id' при попытке изменения device_token = '$device_token'\n", 3, $log_file);
                            }
                        }
                    }

                } else {
                    $response["status"] = "NOT-FOUND";
                    error_log("[$time $ip] user_edit_app: NOT-FOUND не найден user_id = $user_id\n", 3, $log_file);
                }
            } else {
                $response["status"] = "ERROR";
                error_log("[$time $ip] user_edit_app: ERROR не получены параметры post запроса\n", 3, $log_file);
            }
            return $response;
        } else {
            return $client_request_access;
        }
    }


    # Редактирование настроек push-уведомлений (метод для приложений)
    function device_settings_edit($data, $current_request_type){
        global $db, $log_file;

        $client_request_access = client_request_access_status($current_request_type);

        if ($client_request_access["status"] == "OK") {
            $ip = $this -> get_client_ip();
            $time = date("H:i");

            $user_id = $client_request_access["user_id"];
            $device_uuid = $client_request_access["device_uuid"];

            $sql_query = array();

            if (isset($data["new_stream"]) AND ($data["new_stream"] == 0 OR $data["new_stream"] == 1)) {
                $new_stream = $data["new_stream"];
                $sql_query[] = "new_stream_notify_settings = '$new_stream'";
            }

            if (isset($data["new_follower"]) AND ($data["new_follower"] == 0 OR $data["new_follower"] == 1)) {
                $new_follower = $data["new_follower"];
                $sql_query[] = "new_follower_notify_settings = '$new_follower'";
            }

            $sql = implode(", ", $sql_query);

            if ($result_update_device = $db -> sql_query("UPDATE `devices` SET $sql WHERE `device_uuid` = '$device_uuid' AND `user_id` = '$user_id' AND `is_deleted` = '0'")) {

                $this -> etag_user_update($user_id); # Обновление etag пользователя

                $response["status"] = "OK";
                error_log("[$time $ip] device_settings_edit: OK заданы настройки для устройства device_uuid = $device_uuid ($sql)\n", 3, $log_file);
            } else {
                $response["status"] = "SQL-ERROR";
                error_log("[$time $ip] device_settings_edit: SQL-ERROR device_uuid = $device_uuid\n", 3, $log_file);
            }

            return $response;
        } else {
            return $client_request_access;
        }
    }

    # Отправка отзыва
    function feedback_add($data, $current_request_type){
        global $db, $log_file;

        $client_request_access = client_request_access_status($current_request_type);

        if ($client_request_access["status"] == "OK") {
            $ip = $this -> get_client_ip();
            $time = date("H:i");
            $current_date = time();

            $device_id = $client_request_access["device_id"];

            $expected_params = array();

            if (isset($data["text"]) AND ($data["text"] != '')) {
                $feedback_text = $data["text"];
            } else {
                $expected_params[] = "text";
            }

            if (count($expected_params) > 0 ){
                $expected = implode(", ", $expected_params);
                $error = "Не получены параметры: $expected";
                $response["status"] = "ERROR";
                $response["message"] = $error;
                error_log("[$time $ip] feedback_add: $error\n", 3, $log_file);
                return $response;
            }

            if ($db -> sql_query("INSERT INTO `users_feedback`(`id`, `device_id`, `feedback_text`, `created_date`) VALUES (NULL, ".$device_id.", '$feedback_text', ".$current_date.")")) {

                $response["status"] = "OK";
                error_log("[$time $ip] feedback_add: OK device_id = $device_id, feedback_text = $feedback_text\n", 3, $log_file);
                return $response;
            }

            $response["status"] = "SQL-ERROR";
            error_log("[$time $ip] feedback_add: SQL-ERROR device_id = $device_id\n", 3, $log_file);
            return $response;

        }

        return $client_request_access;
    }

    # Получение сообщений чатов со службой поддержки
    function support_service_chats_get($user_id = 0, $current_request_type){
        global $db, $log_file;

        $client_request_access = client_request_access_status($current_request_type);

        if ($client_request_access["status"] == "OK") {
            $ip = $this -> get_client_ip();
            $time = date("H:i");

            $requester_user_id = $client_request_access["user_id"];
            # Сообщения чата пользователя доступны только самому пользователю
            if ($requester_user_id == $user_id ) {
                $result = $db -> sql_query("SELECT `support_service_chats`.`id` as `chat_id`, `accepted_by_admin`, `message_by`, `message`, `created_date` FROM `support_service_chats` LEFT JOIN `support_service_chats_msg` ON `support_service_chats`.`id` = `support_service_chats_msg`.`support_service_chat_id` WHERE `user_id` ='$user_id' AND `message` != '' ORDER BY `created_date` ASC", "", "array");

                $chats = array();
                foreach ($result as $v) {
                    $chat_id = $v["chat_id"];
                    $admin_id = $v["accepted_by_admin"];

                    $message_data["message_by"] = $v["message_by"];
                    $message_data["message"] = $v["message"];
                    $message_data["created_date"] = $v["created_date"];

                    $chat_data["admin_id"] = $admin_id;
                    $chat_data["messages"] = $message_data;
                    $chats[$chat_id]["admin_id"] = $admin_id;
                    $chats[$chat_id]["messages"][] = $message_data;
                }

                $response["status"] = "OK";
                $response["chats"] = $chats;
                error_log("[$time $ip] support_service_chats_get: OK user_id - $user_id\n", 3, $log_file);
                return $response;
            }
            $response["status"] = "ACCESS-DENIED";
            error_log("[$time $ip] support_service_chats_get: ACCESS-DENIED requester_user_id - $requester_user_id, user_id - $user_id\n", 3, $log_file);
            return $response;
        }

        return $client_request_access;
    }


    # Получение сообщений чатов со службой поддержки вариант 2 (выбрать потом один вариант)
    function support_service_chats_get2($user_id = 0, $current_request_type){
        global $db, $log_file;

        $client_request_access = client_request_access_status($current_request_type);

        if ($client_request_access["status"] == "OK") {
            $ip = $this -> get_client_ip();
            $time = date("H:i");

            $requester_user_id = $client_request_access["user_id"];
            # Сообщения чата пользователя доступны только самому пользователю
            if ($requester_user_id == $user_id ) {
                $result = $db -> sql_query("SELECT `support_service_chats`.`id` as `chat_id`, `accepted_by_admin`, `message_by`, `message`, `created_date` FROM `support_service_chats` LEFT JOIN `support_service_chats_msg` ON `support_service_chats`.`id` = `support_service_chats_msg`.`support_service_chat_id` WHERE `user_id` ='$user_id' AND `message` != '' ORDER BY `created_date` ASC", "", "array");

                $messages = array();
                foreach ($result as $v) {
                    $message_by = $v["message_by"];

                    $message_data["message_by"] = $message_by;
                    $message_data["message"] = $v["message"];
                    $message_data["created_date"] = $v["created_date"];

                    if ($message_by =="admin") {
                        $message_data["admin_id"] = $v["accepted_by_admin"];
                    }

                    $messages[] = $message_data;
                }

                $response["status"] = "OK";
                $response["messages"] = $messages;
                error_log("[$time $ip] support_service_chats_get2: OK user_id - $user_id\n", 3, $log_file);
                return $response;
            }
            $response["status"] = "ACCESS-DENIED";
            error_log("[$time $ip] support_service_chats_get2: ACCESS-DENIED requester_user_id - $requester_user_id, user_id - $user_id\n", 3, $log_file);
            return $response;
        }

        return $client_request_access;
    }    

    function user_recovery($data, $current_request_type) {
        global $db, $log_file;

        $client_request_access = client_request_access_status($current_request_type);
        if ($client_request_access["status"] == "OK") {

            $ip = $this -> get_client_ip();
            $time = date("H:i");
            $current_date = time();

            $user_id = $data["user_id"];

            $result_user = $db -> sql_query("SELECT `id` FROM `users` WHERE `id` = '$user_id'", "", "array");
            if (sizeof($result_user) > 0 AND !empty($result_user[0])) {
                if ( $db -> sql_query("UPDATE `users` SET `last_connected` = '$current_date', `deleted_date` = '0', `is_deleted` = '0' WHERE `id` = '$user_id'")){
                    $response["status"] = "OK";
                    $response["user_id"] = $user_id;

                    if (isset($client_request_access["device_id"]) AND $client_request_access["device_id"] != 0){
                        $device_id = $client_request_access["device_id"];
                        $session_id = $this -> get_device_session_id($device_id, "recover");
                        $response["session_id"] = $session_id;
                    }

                    error_log("[$time $ip] user_recovery: OK user_id - $user_id\n", 3, $log_file);
                } else {
                    $response["status"] = "ERROR";
                    error_log("[$time $ip] user_recovery: SQL-ERROR user_id - $user_id\n", 3, $log_file);
                }
            } else {
                $response["status"] = "NOT-FOUND";
                error_log("[$time $ip] user_recovery: NOT-FOUND user_id - $user_id\n", 3, $log_file);
            }
            return $response;
        } else {
            return $client_request_access;
        }
    }

    # Восстановление аккаунта пользователя
    function user_recovery_social_networks($data, $current_request_type) {
        global $db, $log_file;

        $client_request_access = client_request_access_status($current_request_type);
        if ($client_request_access["status"] == "OK") {

            $ip = $this -> get_client_ip();
            $time = date("H:i");
            $current_date = time();

            $user_id = $data["user_id"];

            $result_user = $db -> sql_query("SELECT `id` FROM `users` WHERE `id` = '$user_id'", "", "array");
            if (sizeof($result_user) > 0 AND !empty($result_user[0])) {
                if ( $db -> sql_query("UPDATE `users` SET `last_connected` = '$current_date', `deleted_date` = '0', `is_deleted` = '0' WHERE `id` = '$user_id'")){
                    $response["status"] = "OK";
                    $response["user_id"] = $user_id;
                    error_log("[$time $ip] user_recovery: OK user_id - $user_id\n", 3, $log_file);
                } else {
                    $response["status"] = "ERROR";
                    error_log("[$time $ip] user_recovery: SQL-ERROR user_id - $user_id\n", 3, $log_file);
                }
            } else {
                $response["status"] = "NOT-FOUND";
                error_log("[$time $ip] user_recovery: NOT-FOUND user_id - $user_id\n", 3, $log_file);
            }
            return $response;
        } else {
            return $client_request_access;
        }
    }

    # Метод авторизации пользователя через vk (для мобильных приложений)
    function auth_vk_app($data, $current_request_type){
        global $db, $log_file, $project_options;

        $client_request_access = client_request_access_status($current_request_type);

        if ($client_request_access["status"] == "OK") {
            $ip = $this -> get_client_ip();
            $time = date("H:i");
            $current_date = time();

            $expected_params = array();
            if (isset($data["vk_id"]) AND !empty($data["vk_id"])) {
                $vk_id = prepair_str($data["vk_id"]);
            } else {
                $expected_params[] = "vk_id";
            }

            if (isset($data["first_name"]) AND !empty($data["first_name"])) {
                $first_name = prepair_str($data["first_name"]);
            } else {
                $expected_params[] = "first_name";
            }

            if (isset($data["last_name"]) AND !empty($data["last_name"])) {
                $last_name = prepair_str($data["last_name"]);
            } else {
                $expected_params[] = "last_name";
            }

            if (isset($data["screen_name"]) AND !empty($data["screen_name"])) {
                $screen_name = prepair_str($data["screen_name"]);
            } else {
                $expected_params[] = "screen_name";
            }

            $image_url = "";
            $image_changed_date = 0;
            if (isset($data["image_url"]) AND !empty($data["image_url"])) {
                $image_url = prepair_str($data["image_url"]);
                $image_changed_date = $current_date;
            }

            $email = "";
            if (isset($data["email"]) AND !empty($data["email"])) {
                $email = prepair_str($data["email"]);
            }

            if (isset($data["operating_sys"]) AND !empty($data["operating_sys"])) {
                $operating_sys = prepair_str($data["operating_sys"]);
            } else {
                $expected_params[] = "operating_sys";
            }

            if (isset($data["device_model"]) AND !empty($data["device_model"])) {
                $device_model = prepair_str($data["device_model"]);
            } else {
                $expected_params[] = "device_model";
            }

            if (isset($data["device_uuid"]) AND !empty($data["device_uuid"])) {
                $device_uuid = prepair_str($data["device_uuid"]);
            } else {
                $expected_params[] = "device_uuid";
            }

            if (isset($data["device_token"]) AND !empty($data["device_token"])) {
                $device_token = prepair_str($data["device_token"]);
            } else {
                $expected_params[] = "device_token";
            }

            $lang = "ru";
            if (isset($data["lang"]) AND !empty($data["lang"])) {
                $lang = prepair_str($data["lang"]);
                if (preg_match("/en/i", $lang)) {
//                    TODO translate this
                    $recover_account_info = "Данный аккаунт удален. Вы можете восстановить аккаунт в течение 72 часов с момента его удаления.";
                } else {
                    $recover_account_info = "Данный аккаунт удален. Вы можете восстановить аккаунт в течение 72 часов с момента его удаления.";
                }
            } else {
                $expected_params[] = "lang";
            }

            if (count($expected_params) > 0 ){
                $expected = implode(", ", $expected_params);
                $error = "Не получены параметры: $expected";
                $response["status"] = "ERROR";
                $response["message"] = $error;
                error_log("[$time $ip] vk_auth_app: $error\n", 3, $log_file);
            } else {
                $name = $first_name." ".$last_name;
                $login = $screen_name;

                $res_account_by_id = $db -> sql_query("SELECT * FROM `users` WHERE `vk_id` = '$vk_id' AND `id` = (SELECT MAX(`id`) FROM `users` WHERE `vk_id` = '$vk_id' GROUP BY `vk_id`)", "", "array");
                if (sizeof($res_account_by_id) > 0 AND !empty($res_account_by_id[0])) {
                    $user_id = $res_account_by_id[0]["id"];

                    $user_deleted_status = $res_account_by_id[0]["is_deleted"];
                    $user_deleted_date = $res_account_by_id[0]["deleted_date"];
                    $recover_account_date_limit = $user_deleted_date + 60*60*72;

                    if ($user_deleted_status == 0) { #Пользователь с данным vk_id существует в системе и он не удален, запись обновляется
                        $create_new_account = 0;
                        $db -> sql_query("UPDATE `users` SET `last_connected` = '$current_date', `last_connected_by` = 'vk_app' WHERE `id` = '$user_id' AND `is_deleted` = '0'");

                        $res_device = $db -> sql_query("SELECT * FROM `devices` WHERE `device_uuid` = '$device_uuid' AND `user_id` = '$user_id' AND `is_deleted` = '0'", "", "array");
                        if (sizeof($res_device) > 0) {
                            $device_id = $res_device[0]["id"];
                        } else {#Добавление нового устройства, если оно отсутствует
                            $sha1_encrypt_id = sha1($user_id.$device_uuid);
                            $res_add_device = $db -> sql_query("INSERT INTO `devices` (`id`, `sha1_encrypt_id`, `user_id`, `operating_system`, `device_model`, `device_uuid`, `device_token`, `lang`,`new_stream_notify_settings`, `new_follower_notify_settings`, `ratio`, `is_blocked`, `is_deleted`) VALUES (NULL, '$sha1_encrypt_id', '$user_id', '$operating_sys', '$device_model', '$device_uuid', '$device_token', '$lang', '1', '1', '0', '0', '0');");
                            $device_id = $db -> sql_nextid($res_add_device);
                        }

                        $response["status"] = "OK";
                        $response["user_id"] = $user_id;
                        $response["session_id"] = $this -> get_device_session_id($device_id, "vk");
                        error_log("[$time $ip] vk_auth_app: OK прошел авторизацию user_id - $user_id, vk_id - $vk_id \n", 3, $log_file);

                    } else if ($user_deleted_status == 1 AND $current_date < $recover_account_date_limit) {#Пользователь удален, но существует возможность восстановления аккаунта в течение 72 часов после его удаления
                        $create_new_account = 0;
                        $response["status"] = "ACC-DELETED";
                        $response["user_id"] = $user_id;
                        $response["message"] = $recover_account_info;
                        error_log("[$time $ip] vk_auth_app: ACC-DELETED user-id - $user_id, vk_id - $vk_id\n", 3, $log_file);
                    } else { #Пользователь удален, срок восставления аккаунта истек
                        $create_new_account = 1;
                    }
                } else {
                    if (!empty($email)) {
                        #Создание уникального логина
                        $login = stristr($email, '@', true);
                        $res_account_by_login = $db -> sql_query("SELECT * FROM `users` WHERE `login` = '$login' AND `is_deleted` = '0'", "", "array");
                        if (sizeof($res_account_by_login) > 0 AND !empty($res_account_by_login[0])) {
                            $login = $login.gen_uuid_num(4);
                        }

                        #Если получен email, поиск в системе пользователя с таким email
                        $res_account_by_email = $db -> sql_query("SELECT * FROM `users` WHERE `email` = '$email' AND `id` = (SELECT MAX(`id`) FROM `users` WHERE `email` = '$email' GROUP BY `email`)", "", "array");
                        if (sizeof($res_account_by_email) > 0 AND !empty($res_account_by_email[0])) {

                            $user_id = $res_account_by_email[0]["id"];
                            $account_by_email_vk_id = $res_account_by_email[0]["vk_id"];
                            $user_deleted_status = $res_account_by_email[0]["is_deleted"];
                            $user_deleted_date = $res_account_by_email[0]["deleted_date"];
                            $recover_account_date_limit = $user_deleted_date + 60*60*72;

                            if ($user_deleted_status == 0) { #Пользователь с данным email существует в системе и он не удален, запись обновляется
                                if ($account_by_email_vk_id == "") { #Если пользователь с таким email существует и к нему не привязан аккаунт vkontakte(добавление vk_id)
                                    $create_new_account = 0;
                                    $db -> sql_query("UPDATE `users` SET `vk_id` = '$vk_id', `last_connected` = '$current_date', `last_connected_by` = 'vk_app' WHERE `id` = '$user_id' AND `is_deleted` = '0'");

                                    $res_device = $db -> sql_query("SELECT * FROM `devices` WHERE `device_uuid` = '$device_uuid' AND `user_id` = '$user_id' AND `is_deleted` = '0'", "", "array");
                                    if (sizeof($res_device) > 0 AND !empty($res_device[0])) {
                                        $device_id = $res_device[0]["id"];
                                    } else {#Добавление нового устройства, если оно отсутствует
                                        $sha1_encrypt_id = sha1($user_id.$device_uuid);
                                        $res_add_device = $db -> sql_query("INSERT INTO `devices` (`id`, `sha1_encrypt_id`, `user_id`, `operating_system`, `device_model`, `device_uuid`, `device_token`, `lang`,`new_stream_notify_settings`, `new_follower_notify_settings`, `ratio`,  `is_blocked`, `is_deleted`) VALUES (NULL, '$sha1_encrypt_id', '$user_id', '$operating_sys', '$device_model', '$device_uuid', '$device_token', '$lang', '1', '1', '0', '0', '0')");
                                        $device_id = $db -> sql_nextid($res_add_device);
                                    }

                                    $response["status"] = "OK";
                                    $response["user_id"] = $user_id;
                                    $response["session_id"] = $this -> get_device_session_id($device_id, "vk");
                                    error_log("[$time $ip] vk_auth_app: OK к существующему аккаунту добавлен vk_id - $vk_id, прошел авторизацию user_id - $user_id\n", 3, $log_file);

                                } else {
                                    $create_new_account = 1;
                                    error_log("[$time $ip] vk_auth_app: к существующему email - $email не добавлен vk_id - $vk_id, привязан ранее vk_id - $account_by_email_vk_id\n", 3, $log_file);
                                }

                            } else if ($user_deleted_status == 1 AND $current_date < $recover_account_date_limit) {#Пользователь удален, но существует возможность восстановления аккаунта в течение 72 часов после его удаления
                                $create_new_account = 0;
                                $response["status"] = "ACC-DELETED";
                                $response["user_id"] = $user_id;
                                $response["message"] = $recover_account_info;
                                error_log("[$time $ip] vk_auth_app: ACC-DELETED user-id - $user_id, vk_id - $vk_id\n", 3, $log_file);
                            } else { #Пользователь удален, срок восставления аккаунта истек
                                $create_new_account = 1;
                            }

                        } else {
                            $create_new_account = 1;
                        }

                    } else {
                        $create_new_account = 1;
                    }
                }

                if ($create_new_account == 1) {
                    #Пользователь с полученным vk_id не зарегистрирован в системе

                    $user_uuid = gen_uuid(6);
                    $etag_user = gen_uuid(12);
                    $etag_img = gen_uuid(12);

                    if ($image_url != ""){
                        #Сохранение в проект фото пользователя
                        $save_image = $this -> save_user_image_from_url($image_url, $user_uuid);
                        if ($save_image["status"] == "OK") {
                            $image_url = $save_image["image_url"];
                        }
                    }

                    #Создание нового пользователя
                    if ($res_add_user = $db -> sql_query("INSERT INTO `users` (`id`, `user_uuid`, `login`, `phone`, `password`, `fb_id`, `vk_id`, `tw_id`, `name`, `image_url`, `image_changed_date`,  `etag_user`, `etag_img`, `location`, `email`, `birth_day`, `about`, `created_date`, `deleted_date`, `last_changed`, `last_connected`, `last_connected_by`, `accepted`, `created_by`, `hash`, `email_confirm`, `is_official`, `is_check_official`, `is_blocked`, `is_deleted`) VALUES (NULL, '$user_uuid', '$login', '', '', '', '$vk_id', '', '$name', '$image_url', '$image_changed_date', '$etag_user', '$etag_img', '', '$email', '', '', '$current_date', '', '$current_date', '$current_date', 'vk_app', '1', 'vk_app', '', '0', '0', '0', '0', '0')")) {
                        $user_id = $db -> sql_nextid($res_add_user);

                        $sha1_encrypt_id = sha1($user_id.$device_uuid);

                        $res_add_device = $db -> sql_query("INSERT INTO `devices` (`id`, `sha1_encrypt_id`, `user_id`, `operating_system`, `device_model`, `device_uuid`, `device_token`, `lang`, `new_stream_notify_settings`, `new_follower_notify_settings`, `ratio`, `is_blocked`, `is_deleted`) VALUES (NULL, '$sha1_encrypt_id', '$user_id', '$operating_sys', '$device_model', '$device_uuid', '$device_token', '$lang', '1', '1', '0', '0', '0');");

                        $device_id = $db -> sql_nextid($res_add_device);

                        $response["status"] = "OK";
                        $response["user_id"] = $user_id;
                        $response["session_id"] = $this -> get_device_session_id($device_id, "vk");
                        error_log("[$time $ip] vk_auth_app: OK создан user_id - $user_id, vk_id - $vk_id\n", 3, $log_file);
                    } else {
                        $response["status"] = "ERROR";
                        $response["message"] = "Ошибка выполнения запроса";
                        error_log("[$time $ip] vk_auth_app: SQL-ERROR ошибка выполнения запроса user_id - $user_id, vk_id - $vk_id\n", 3, $log_file);
                    }
                }
            }

            return $response;

        } else {
            return $client_request_access;
        }
    }

    # Метод авторизации пользователя через fb (для мобильных приложений)
    function auth_fb_app($data, $current_request_type){
        global $db, $log_file, $project_options;

        $client_request_access = client_request_access_status($current_request_type);

        if ($client_request_access["status"] == "OK") {
            $ip = $this -> get_client_ip();
            $time = date("H:i");
            $current_date = time();

            $expected_params = array();
            if (isset($data["fb_id"]) AND !empty($data["fb_id"])) {
                $fb_id = prepair_str($data["fb_id"]);
            } else {
                $expected_params[] = "fb_id";
            }

            if (isset($data["name"]) AND !empty($data["name"])) {
                $name = prepair_str($data["name"]);
            } else {
                $expected_params[] = "name";
            }

            $image_url = "";
            $image_changed_date = 0;
            if (isset($data["image_url"]) AND !empty($data["image_url"])) {
                $image_url = prepair_str($data["image_url"]);
                $image_changed_date = $current_date;
            }

            if (isset($data["email"]) AND !empty($data["email"])) {
                $email = prepair_str($data["email"]);
            } else {
                $expected_params[] = "email";
            }

            if (isset($data["operating_sys"]) AND !empty($data["operating_sys"])) {
                $operating_sys = prepair_str($data["operating_sys"]);
            } else {
                $expected_params[] = "operating_sys";
            }

            if (isset($data["device_model"]) AND !empty($data["device_model"])) {
                $device_model = prepair_str($data["device_model"]);
            } else {
                $expected_params[] = "device_model";
            }

            if (isset($data["device_uuid"]) AND !empty($data["device_uuid"])) {
                $device_uuid = prepair_str($data["device_uuid"]);
            } else {
                $expected_params[] = "device_uuid";
            }

            if (isset($data["device_token"]) AND !empty($data["device_token"])) {
                $device_token = prepair_str($data["device_token"]);
            } else {
                $expected_params[] = "device_token";
            }

            $lang = "ru";
            if (isset($data["lang"]) AND !empty($data["lang"])) {
                $lang = prepair_str($data["lang"]);
                if (preg_match("/en/i", $lang)) {
//                    TODO translate this
                    $recover_account_info = "Данный аккаунт удален. Вы можете восстановить аккаунт в течение 72 часов с момента его удаления.";
                } else {
                    $recover_account_info = "Данный аккаунт удален. Вы можете восстановить аккаунт в течение 72 часов с момента его удаления.";
                }
            } else {
                $expected_params[] = "lang";
            }

            if (count($expected_params) > 0 ){
                $expected = implode(", ", $expected_params);
                $error = "Не получены параметры: $expected";
                $response["status"] = "ERROR";
                $response["message"] = $error;
                error_log("[$time $ip] fb_auth_app: $error\n", 3, $log_file);
            } else {
                $login = "id".$fb_id;
                $res_account_by_id = $db -> sql_query("SELECT * FROM `users` WHERE `fb_id` = '$fb_id' AND `id` = (SELECT MAX(`id`) FROM `users` WHERE `fb_id` = '$fb_id' GROUP BY `fb_id`)", "", "array");
                #Если пользователь существует
                if (sizeof($res_account_by_id) > 0 AND !empty($res_account_by_id[0])) { #Если пользователь с таким fb_id уже существует в системе, обновление записи

                    $user_id = $res_account_by_id[0]["id"];

                    $user_deleted_status = $res_account_by_id[0]["is_deleted"];
                    $user_deleted_date = $res_account_by_id[0]["deleted_date"];
                    $recover_account_date_limit = $user_deleted_date + 60*60*72;

                    if ($user_deleted_status == 0) { #Пользователь с данным fb_id существует в системе и он не удален
                        $create_new_account = 0;

                        $db -> sql_query("UPDATE `users` SET `last_connected` = '$current_date', `last_connected_by` = 'fb_app' WHERE `id` = '$user_id' AND `is_deleted` = '0'");

                        $res_device = $db -> sql_query("SELECT * FROM `devices` WHERE `device_uuid` = '$device_uuid' AND `user_id` = '$user_id' AND `is_deleted` = '0'", "", "array");
                        if (sizeof($res_device) > 0 AND !empty($res_device[0])) {
                            $device_id = $res_device[0]["id"];
                        } else {#Добавление нового устройства, если оно отсутствует
                            $sha1_encrypt_id = sha1($user_id.$device_uuid);
                            $res_add_device = $db -> sql_query("INSERT INTO `devices` (`id`, `sha1_encrypt_id`, `user_id`, `operating_system`, `device_model`, `device_uuid`, `device_token`, `lang`,`new_stream_notify_settings`, `new_follower_notify_settings`, `ratio`, `is_blocked`, `is_deleted`) VALUES (NULL, '$sha1_encrypt_id', '$user_id', '$operating_sys', '$device_model', '$device_uuid', '$device_token', '$lang', '1', '1', '0', '0', '0');");
                            $device_id = $db -> sql_nextid($res_add_device);
                        }

                        $response["status"] = "OK";
                        $response["user_id"] = $user_id;
                        $response["session_id"] = $this -> get_device_session_id($device_id, "fb");
                        error_log("[$time $ip] fb_auth_app: OK прошел авторизацию user_id - $user_id, fb_id - $fb_id  \n", 3, $log_file);

                    } else if ($user_deleted_status == 1 AND $current_date < $recover_account_date_limit) {#Пользователь удален, но существует возможность восстановления аккаунта в течение 72 часов после его удаления
                        $create_new_account = 0;
                        $response["status"] = "ACC-DELETED";
                        $response["user_id"] = $user_id;
                        $response["message"] = $recover_account_info;
                        error_log("[$time $ip] fb_auth_app: ACC-DELETED user-id - $user_id, fb_id - $fb_id\n", 3, $log_file);
                    } else { #Пользователь удален, срок восставления аккаунта истек
                        $create_new_account = 1;
                    }
                } else {

                    if (!empty($email)) {
                        #Если получен email, поиск в системе пользователя с таким email
                        $res_account_by_email = $db -> sql_query("SELECT * FROM `users` WHERE `email` = '$email' AND `is_deleted` = '0' AND `id` = (SELECT MAX(`id`) FROM `users` WHERE `email` = '$email' GROUP BY `email`)", "", "array");
                        if (sizeof($res_account_by_email) > 0 AND $res_account_by_email[0] != "") {
                            $user_id = $res_account_by_email[0]["id"];
                            $account_by_email_fb_id = $res_account_by_email[0]["fb_id"];
                            if ($account_by_email_fb_id == "" OR $account_by_email_fb_id == $fb_id) { #Если пользователь с таким email существует и к нему не привязан аккаунт facebook, обновление данной записи и добавление fb_id
                                $create_new_account = 0;
                                $db -> sql_query("UPDATE `users` SET `fb_id` = '$fb_id', `last_connected` = '$current_date', `last_connected_by` = 'fb_app' WHERE `id` = '$user_id' AND `is_deleted` = '0'");

                                $res_device = $db -> sql_query("SELECT * FROM `devices` WHERE `device_uuid` = '$device_uuid' AND `user_id` = '$user_id' AND `is_deleted` = '0'", "", "array");
                                if (sizeof($res_device) > 0 AND !empty($res_device[0])) {
                                    $device_id = $res_device[0]["id"];
                                } else {#Добавление нового устройства, если оно отсутствует
                                    $sha1_encrypt_id = sha1($user_id.$device_uuid);
                                    $res_add_device = $db -> sql_query("INSERT INTO `devices` (`id`, `sha1_encrypt_id`, `user_id`, `operating_system`, `device_model`, `device_uuid`, `device_token`, `lang`,`new_stream_notify_settings`, `new_follower_notify_settings`, `ratio`, `is_blocked`, `is_deleted`) VALUES (NULL, '$sha1_encrypt_id', '$user_id', '$operating_sys', '$device_model', '$device_uuid', '$device_token', '$lang', '1', '1', '0', '0', '0')");
                                    $device_id = $db -> sql_nextid($res_add_device);
                                }

                                $response["status"] = "OK";
                                $response["user_id"] = $user_id;
                                $response["session_id"] = $this -> get_device_session_id($device_id, "fb");
                                error_log("[$time $ip] fb_auth_app: к существующему аккаунту добавлен fb_id, прошел авторизацию user_id - $user_id, fb_id - $fb_id\n", 3, $log_file);

                            } else {
                                $create_new_account = 1;
                            }
                        } else {
                            $create_new_account = 1;
                        }

                    } else {
                        $create_new_account = 1;
                    }
                }


                if ($create_new_account == 1) {
                    $user_uuid = gen_uuid(6);
                    $etag_user = gen_uuid(12);
                    $etag_img = gen_uuid(12);

                    if ($image_url != ""){
                        #Сохранение в проект фото пользователя
                        $save_image = $this-> save_user_image_from_url($image_url, $user_uuid);
                        if ($save_image["status"] == "OK") {
                            $image_url = $save_image["image_url"];
                        }
                    }

                    #Создание нового пользователя
                    if ($res_add_user = $db -> sql_query("INSERT INTO `users` (`id`, `user_uuid`, `login`, `phone`, `password`, `fb_id`, `vk_id`, `tw_id`, `name`, `image_url`, `image_changed_date`, `etag_user`, `etag_img`, `location`, `email`, `birth_day`, `about`, `created_date`, `deleted_date`, `last_changed`, `last_connected`, `last_connected_by`, `accepted`, `created_by`, `hash`, `email_confirm`, `is_official`, `is_check_official`, `is_blocked`, `is_deleted`) VALUES (NULL, '$user_uuid', '$login', '', '', '$fb_id', '', '', '$name', '$image_url', '$image_changed_date', '$etag_user', '$etag_img', '', '$email', '', '', '$current_date', '', '$current_date', '$current_date', 'fb_app', '1', 'fb_app', '', '0', '0', '0', '0', '0')")) {

                        $user_id = $db -> sql_nextid($res_add_user);

                        $sha1_encrypt_id = sha1($user_id.$device_uuid);
                        $res_add_device = $db -> sql_query("INSERT INTO `devices` (`id`, `sha1_encrypt_id`, `user_id`, `operating_system`, `device_model`, `device_uuid`, `device_token`, `lang`, `new_stream_notify_settings`, `new_follower_notify_settings`, `ratio`, `is_blocked`, `is_deleted`) VALUES (NULL, '$sha1_encrypt_id', '$user_id', '$operating_sys', '$device_model', '$device_uuid', '$device_token', '$lang', '1', '1', '0', '0', '0');");

                        $device_id = $db -> sql_nextid($res_add_device);

                        $response["status"] = "OK";
                        $response["user_id"] = $user_id;
                        $response["session_id"] = $this -> get_device_session_id($device_id, "fb");
                        error_log("[$time $ip] fb_auth_app: OK создан user_id - $user_id, fb_id - $fb_id\n", 3, $log_file);
                    } else {
                        $response["status"] = "ERROR";
                        $response["message"] = "Ошибка выполнения запроса";
                        error_log("[$time $ip] fb_auth_app: SQL-ERROR ошибка выполнения запроса user_id - $user_id, fb_id - $fb_id\n", 3, $log_file);
                    }
                }
            }

            return $response;
        } else {
            return $client_request_access;
        }
    }

    # Метод авторизации пользователя через tw (для мобильных приложений)
    function auth_tw_app($data, $current_request_type){
        global $db, $log_file, $project_options;

        $client_request_access = client_request_access_status($current_request_type);

        if ($client_request_access["status"] == "OK") {
            $ip = $this -> get_client_ip();
            $time = date("H:i");
            $current_date = time();

            $expected_params = array();
            if (isset($data["tw_id"]) AND !empty($data["tw_id"])) {
                $tw_id = prepair_str($data["tw_id"]);
            } else {
                $expected_params[] = "tw_id";
            }

            if (isset($data["name"]) AND !empty($data["name"])) {
                $name = prepair_str($data["name"]);
            } else {
                $expected_params[] = "name";
            }

            if (isset($data["screen_name"]) AND !empty($data["screen_name"])) {
                $screen_name = prepair_str($data["screen_name"]);
            } else {
                $expected_params[] = "screen_name";
            }

            $image_url = "";
            $image_changed_date = 0;
            if (isset($data["image_url"]) AND !empty($data["image_url"])) {
                $image_url = prepair_str($data["image_url"]);
                $image_changed_date = $current_date;
            }

            $email = "";
            if (isset($data["email"]) AND !empty($data["email"])) {
                $email = prepair_str($data["email"]);
            }

            if (isset($data["operating_sys"]) AND !empty($data["operating_sys"])) {
                $operating_sys = prepair_str($data["operating_sys"]);
            } else {
                $expected_params[] = "operating_sys";
            }

            if (isset($data["device_model"]) AND !empty($data["device_model"])) {
                $device_model = prepair_str($data["device_model"]);
            } else {
                $expected_params[] = "device_model";
            }

            if (isset($data["device_uuid"]) AND !empty($data["device_uuid"])) {
                $device_uuid = prepair_str($data["device_uuid"]);
            } else {
                $expected_params[] = "device_uuid";
            }

            if (isset($data["device_token"]) AND !empty($data["device_token"])) {
                $device_token = prepair_str($data["device_token"]);
            } else {
                $expected_params[] = "device_token";
            }

            $lang = "ru";
            if (isset($data["lang"]) AND !empty($data["lang"])) {
                $lang = prepair_str($data["lang"]);
                if (preg_match("/en/i", $lang)) {
//                    TODO translate this
                    $recover_account_info = "Данный аккаунт удален. Вы можете восстановить аккаунт в течение 72 часов с момента его удаления.";
                } else {
                    $recover_account_info = "Данный аккаунт удален. Вы можете восстановить аккаунт в течение 72 часов с момента его удаления.";
                }
            } else {
                $expected_params[] = "lang";
            }

            if (count($expected_params) > 0 ){
                $expected = implode(", ", $expected_params);
                $error = "Не получены параметры: $expected";
                $response["status"] = "ERROR";
                $response["message"] = $error;
                error_log("[$time $ip] tw_auth_app: $error\n", 3, $log_file);
            } else {

                #Создание уникального логина
                $login = $screen_name;
                $res_account_by_login = $db -> sql_query("SELECT * FROM `users` WHERE `login` = '$login' AND `is_deleted` = '0'", "", "array");
                if (sizeof($res_account_by_login) > 0 AND !empty($res_account_by_login[0])) {
                    $login = $login.gen_uuid_num(4);
                }

                $res_account_by_id = $db -> sql_query("SELECT * FROM `users` WHERE `tw_id` = '$tw_id' AND `id` = (SELECT MAX(`id`) FROM `users` WHERE `tw_id` = '$tw_id' GROUP BY `tw_id`)", "", "array");
                if (sizeof($res_account_by_id) > 0 AND !empty($res_account_by_id[0])) {
                    $user_id = $res_account_by_id[0]["id"];
                    $user_deleted_status = $res_account_by_id[0]["is_deleted"];
                    $user_deleted_date = $res_account_by_id[0]["deleted_date"];
                    $recover_account_date_limit = $user_deleted_date + 60*60*72;

                    if ($user_deleted_status == 0) { #Пользователь с данным tw_id существует в системе и он не удален
                        $create_new_account = 0;
                        $db -> sql_query("UPDATE `users` SET `last_connected` = '$current_date', `last_connected_by` = 'tw_app' WHERE `id` = '$user_id' AND `is_deleted` = '0'");

                        $res_device = $db -> sql_query("SELECT * FROM `devices` WHERE `device_uuid` = '$device_uuid' AND `user_id` = '$user_id' AND `is_deleted` = '0'", "", "array");
                        if (sizeof($res_device) > 0 AND !empty($res_device[0])) {
                            $device_id = $res_device[0]["id"];
                        } else {#Добавление нового устройства, если оно отсутствует
                            $sha1_encrypt_id = sha1($user_id.$device_uuid);
                            $res_add_device = $db -> sql_query("INSERT INTO `devices` (`id`, `sha1_encrypt_id`, `user_id`, `operating_system`, `device_model`, `device_uuid`, `device_token`, `lang`,`new_stream_notify_settings`, `new_follower_notify_settings`, `ratio`,  `is_blocked`, `is_deleted`) VALUES (NULL, '$sha1_encrypt_id', '$user_id', '$operating_sys', '$device_model', '$device_uuid', '$device_token', '$lang', '1', '1', '0', '0', '0');");
                            $device_id = $db -> sql_nextid($res_add_device);
                        }

                        $response["status"] = "OK";
                        $response["user_id"] = $user_id;
                        $response["session_id"] = $this -> get_device_session_id($device_id, "tw");
                        error_log("[$time $ip] tw_auth_app: OK прошел авторизацию user_id - $user_id, tw_id - $tw_id \n", 3, $log_file);

                    } else if ($user_deleted_status == 1 AND $current_date < $recover_account_date_limit) {#Пользователь удален, но существует возможность восстановления аккаунта в течение 72 часов после его удаления
                        $create_new_account = 0;
                        $response["status"] = "ACC-DELETED";
                        $response["user_id"] = $user_id;
                        $response["message"] = $recover_account_info;
                        error_log("[$time $ip] tw_auth_app: ACC-DELETED user-id - $user_id, tw_id - $tw_id\n", 3, $log_file);
                    } else { #Пользователь удален, срок восставления аккаунта истек
                        $create_new_account = 1;
                    }
                } else {#Пользователь с полученным tw_id не зарегистрирован в системе
                    if (!empty($email)) {
                        #Если получен email, поиск в системе пользователя с таким email
                        $res_account_by_email = $db -> sql_query("SELECT * FROM `users` WHERE `email` = '$email' AND `is_deleted` = '0'  AND `id` = (SELECT MAX(`id`) FROM `users` WHERE `email` = '$email' GROUP BY `email`)", "", "array");
                        if (sizeof($res_account_by_email) > 0 AND !empty($res_account_by_email[0])) {

                            $create_new_account = 0;

                            $user_id = $res_account_by_email[0]["id"];
                            $account_by_email_tw_id = $res_account_by_email[0]["tw_id"];
                            if ($account_by_email_tw_id == "" OR $account_by_email_tw_id == $tw_id) { #Если пользователь с таким email существует и к нему не привязан аккаунт twitter, обновление данной записи и добавление tw_id
                                $db -> sql_query("UPDATE `users` SET `tw_id` = '$tw_id', `last_connected` = '$current_date', `last_connected_by` = 'tw_app' WHERE `id` = '$user_id' AND `is_deleted` = '0' LIMIT 1");

                                $res_device = $db -> sql_query("SELECT * FROM `devices` WHERE `device_uuid` = '$device_uuid' AND `user_id` = '$user_id' AND `is_deleted` = '0'", "", "array");
                                if (sizeof($res_device) > 0 AND !empty($res_device[0])) {
                                    $device_id = $res_device[0]["id"];
                                } else {#Добавление нового устройства, если оно отсутствует
                                    $sha1_encrypt_id = sha1($user_id.$device_uuid);
                                    $res_add_device = $db -> sql_query("INSERT INTO `devices` (`id`, `sha1_encrypt_id`, `user_id`, `operating_system`, `device_model`, `device_uuid`, `device_token`, `lang`,`new_stream_notify_settings`, `new_follower_notify_settings`, `ratio`,  `is_blocked`, `is_deleted`) VALUES (NULL, '$sha1_encrypt_id', '$user_id', '$operating_sys', '$device_model', '$device_uuid', '$device_token', '$lang', '1', '1', '0', '0', '0')");
                                    $device_id = $db -> sql_nextid($res_add_device);
                                }

                                $response["status"] = "OK";
                                $response["user_id"] = $user_id;
                                $response["session_id"] = $this -> get_device_session_id($device_id, "tw");
                                error_log("[$time $ip] tw_auth_app: к существующему аккаунту добавлен tw_id, прошел авторизацию user_id - $user_id, tw_id - $tw_id\n", 3, $log_file);

                            } else {
                                $create_new_account = 1;
                            }
                        } else {
                            $create_new_account = 1;
                        }

                    } else {
                        $create_new_account = 1;
                    }
                }

                if ($create_new_account) {
                    $user_uuid = gen_uuid(6);
                    $etag_user = gen_uuid(12);
                    $etag_img = gen_uuid(12);

                    if ($image_url != ""){
                        #Сохранение в проект фото пользователя
                        $save_image = $this -> save_user_image_from_url($image_url, $user_uuid);
                        if ($save_image["status"] == "OK") {
                            $image_url = $save_image["image_url"];
                        }
                    }

                    #Создание нового пользователя
                    if ($res_add_user = $db -> sql_query("INSERT INTO `users` (`id`, `user_uuid`, `login`, `phone`, `password`, `fb_id`, `vk_id`, `tw_id`, `name`, `image_url`, `image_changed_date`, `etag_user`, `etag_img`, `location`, `email`, `birth_day`, `about`, `created_date`, `deleted_date`, `last_changed`, `last_connected`, `last_connected_by`, `accepted`, `created_by`, `hash`, `email_confirm`, `is_official`, `is_check_official`, `is_blocked`, `is_deleted`) VALUES (NULL, '$user_uuid', '$login', '', '', '', '', '$tw_id', '$name', '$image_url', '$image_changed_date', '$etag_user', '$etag_img', '', '$email', '', '', '$current_date', '', '$current_date', '$current_date', 'tw_app', '1', 'tw_app', '', '0', '0', '0', '0', '0')")) {

                        $user_id = $db -> sql_nextid($res_add_user);

                        $sha1_encrypt_id = sha1($user_id.$device_uuid);
                        $res_add_device = $db -> sql_query("INSERT INTO `devices` (`id`, `sha1_encrypt_id`, `user_id`, `operating_system`, `device_model`, `device_uuid`, `device_token`, `lang`, `new_stream_notify_settings`, `new_follower_notify_settings`, `ratio`,`is_blocked`, `is_deleted`) VALUES (NULL, '$sha1_encrypt_id', '$user_id', '$operating_sys', '$device_model', '$device_uuid', '$device_token', '$lang', '1', '1', '0', '0', '0')");
                        $device_id = $db -> sql_nextid($res_add_device);

                        $response["status"] = "OK";
                        $response["user_id"] = $user_id;
                        $response["session_id"] = $this -> get_device_session_id($device_id, "tw");
                        error_log("[$time $ip] tw_auth_app: OK создан user_id - $user_id, tw_id - $tw_id\n", 3, $log_file);
                    } else {
                        $response["status"] = "ERROR";
                        $response["message"] = "Ошибка выполнения запроса";
                        error_log("[$time $ip] tw_auth_app: SQL-ERROR ошибка выполнения запроса\n", 3, $log_file);
                    }
                }
            }

            return $response;
        } else {
            return $client_request_access;
        }
    }

    # Получение тегов профиля владельца трансляции
    function get_user_tags($user_id = 0){
        global $db;

        $profile_tags = array();
        if (user($user_id)){
            $result_tags = $db -> sql_query("SELECT `profiles_tags_data`.`name` AS `tag_name` FROM `profiles_tags` LEFT JOIN `profiles_tags_data` ON `profiles_tags`.`tag_id` = `profiles_tags_data`.`id` WHERE `profiles_tags`.`user_id` = '$user_id' AND `profiles_tags`.`is_deleted` = '0' AND `profiles_tags_data`.`is_disabled` = '0'", "", "array");

            foreach ($result_tags as $value){
                array_push($profile_tags, $value["tag_name"]);
            }
        }
        return $profile_tags;
    }

    # Метод удаления пользователем своего аккаунта (аккаунт можно восстановить в течение 72 часов с момента удаления)
    function user_delete($user_id = 0, $current_request_type){
        global $db, $log_file;

        $client_request_access = client_request_access_status($current_request_type);

        if ($client_request_access["status"] == "OK") {
            $response = array();
            $current_date = time();

            $ip = $this -> get_client_ip();
            $time = date("H:i");

            if (isset($client_request_access["user_id"])) {
                $user_id = $client_request_access["user_id"];
                $this -> destroy_all_sessions($user_id);
            }

            if ($db -> sql_query("UPDATE `users` SET `deleted_date` = '$current_date', `is_deleted` = '1' WHERE `id` = '$user_id'")) {
                $response["status"] = "OK";
                session_unset();
                session_destroy();
                error_log("[$time $ip] user_delete: OK user_id - $user_id\n", 3, $log_file);
            } else {
                $response["status"] = "ERROR";
                $response["message"] = "Ошибка выполнения запроса";

                error_log("[$time $ip] user_delete: SQL-ERROR user_id - $user_id\n", 3, $log_file);
            }

            return $response;
        } else {
            return $client_request_access;
        }
    }

    # Метод окончательного удаления аккаунта пользователя(если аккаунт был удален более 72 часов назад). Метод вызывается крон-файлом update_accounts.php
    function user_delete_total(){
        global $db, $user_delete_log_file, $stream;

        $current_date = time();
        $time = date("H:i");

        $filter_date = $current_date - 72*60*60;

        $result = $db -> sql_query("SELECT `id`, `image_url` FROM `users` WHERE `deleted_date` < '$filter_date' AND `is_deleted` = '1'", "", "array");
        if (sizeof($result) > 0) {
            foreach ($result as $value) {
                $user_id = $value["id"];
                $user_image_url = $value["image_url"];
                $image_delete_info = "";
                if (file_exists($user_image_url)) { #Удаление фото профиля пользователя
                    if (unlink($user_image_url)) {
                        $image_delete_info = ", фото профиля удалено";
                    }
                }
                $streams_delete_info = "";
                $result_streams = $db -> sql_query("SELECT `uuid` FROM `streams` WHERE `user_id` = '$user_id' AND `is_deleted` = '0'", "", "array");
                if (sizeof($result_streams) > 0) {
                    $deleted_streams_uuid_array = array();
                    foreach ($result_streams as $v) {# Удаление трансляций пользователя
                        $stream_uuid = $v["uuid"];
                        $stream -> stream_delete($stream_uuid);
                        array_push($deleted_streams_uuid_array, $stream_uuid);
                    }
                    $deleted_streams_uuid = implode(", ", $deleted_streams_uuid_array);
                    $streams_delete_info = ", трансляции пользователя удалены: $deleted_streams_uuid";
                }
                $db -> sql_query("UPDATE `users` SET `deleted_date` = '$current_date', `is_deleted` = '1', `rating` = '0' WHERE `id` = '$user_id'");
                error_log("[$time] user_delete_total: OK профиль $user_id удален $image_delete_info $streams_delete_info\n", 3, $user_delete_log_file);
            }

        } else {
            error_log("[$time] user_delete_total: NOT-FOUND устаревшие аккаунты не найдены\n", 3, $user_delete_log_file);
        }
    }

    # Формирование html кода для вставки в Popup информации о пользователе при клике на иконку профиля пользователя
    function profile_html_get($data) {
        global $stream, $lang;
        $current_date = time();

        $hero_id = prepair_str($data["profile_id"]);
        $html = "";

        $user_id = get_current_session_user(); # Получение id пользователя в текущей сессии

        $user_data = $this -> user_data($hero_id);
        if ($user_data["status"] == 'OK') {

            $follow_state = 0;
            $follow_title = _FOLLOW;
            $followers_array = $user_data["data"]["followers"];
            if (in_array($user_id, $followers_array)) {
                $follow_state = 1;
                $follow_title = _IS_FOLLOWED;
            }

            $follow_state_btn = "";
            if ($user_id AND $user_id != $hero_id) {# Ограничение возможности подписаться на себя
                $follow_state_btn = "<div class=\"follow_state_block\"><button type=\"button\" id=\"follow\" data-profile-id=\"$hero_id\" class=\"btn theme-button-default\" data-following-state=\"$follow_state\" title=\"$follow_title\"><span>$follow_title</span> <i class=\"fa fa-star\"></i></button></div>";
            }

            $description = $user_data["data"]["about"];
            $profile_image = $this -> profile_image_html($hero_id);
            $profile_name = $user_data["data"]["display_name"];
            $followers_count = $user_data["data"]["followers_count"];
            $following_count = $user_data["data"]["following_count"];

            $online_streams_array = $user_data["data"]["streams"]["online"];
            $archive_streams_array = $user_data["data"]["streams"]["archive"];
            $streams_array = array_merge($online_streams_array, $archive_streams_array);
            if (sizeof($streams_array) == 0) {
                $streams_html = _NO_LAST_STREAMS;
            } else {
                $streams_html = "";

                $streams_data = $stream -> streams_array_data($streams_array, $user_id, $lang);
                foreach ($streams_data as $stream_data) {
                    $stream_uuid = $stream_data["uuid"];
                    $stream_name = $stream_data["name"];
                    $clients_count = $stream_data["client_count"];
                    $locality_desc = $stream_data["locality"];
                    $locality = "";
                    if (!empty($locality_desc)) {
                        $locality = "<i class=\"fa fa-location-arrow\"></i> $locality_desc";
                    }
                    $thumb = $stream_data["thumb"];

                    $stream_status = $stream_data["status"];
                    if ($stream_status == 1) {
                        $stream_duration_icon = "";
                        $stream_date_info = "<button type=\"button\" class=\"btn theme-button-default\">Live</button>";
                    } else {
                        $stream_duration = gmdate("i:s", $stream_data["duration"]);
                        $stream_duration_icon = "<div class=\"stream_duration\">$stream_duration</div>";

                        $time_difference = $current_date - $stream_data["end_date"];
                        $stream_date_info = refine_data($time_difference) . " " . _AGO;
                    }

                    $streams_html .= "<div class=\"row\">
                                <div class=\"col-xs-12 col-sm-4 stream_preview\">
                                    <a href=\"/index.php?route=page_play&user=$hero_id&uuid=$stream_uuid\">
                                        <div class=\"thumbnail\" style=\"background: #D6D6D6 url('$thumb') center center no-repeat; background-size: cover;)\">
                                            <div class=\"client_count\">
                                                <div class=\"client_icon\"><i class=\"fa fa-eye fa-lg\"></i></div><div class=\"client_count_data\"><span>$clients_count</span> " . _WATCH . "</div>
                                            </div>
                                            <i class=\"icon-play\"></i>
                                            $stream_duration_icon
                                        </div>
				                    </a>
				                </div>
				                <div class=\"col-xs-12 col-sm-3\">
				                    <p>$stream_name</p>
				                    <p class=\"dull_text\">$locality</p>
				                </div>
				                <div class=\"col-xs-12 col-sm-3 dull_text\">$stream_date_info</div>
				             </div>";
                }
            }

            $html =  "
                    <div class=\"popup_body_header text-center\">
                        $profile_image
                        <div class=\"profile_name\">$profile_name</div>
                        <div class=\"profile_description\">$description</div>
                        <div class=\"flex_profile_data\">
                            <div>
                                <p>$followers_count</p>
                                <p class=\"profile_data\">" . _FOLLOWERS . "</p>
                            </div>
                            <div>
                                <p>$following_count</p>
                                <p class=\"profile_data\">" . _FOLLOWING . "</p>
                            </div>
                        </div>
                        $follow_state_btn                        
                        <div class=\"popup_body_content\">
                            $streams_html
                        </div>
                    </div>";
        }

        return $html;
    }

    # Метод получения изображения пользователя
    function profile_image_get($user_id = 0){
        global $db, $log_file;
        $ip = $this -> get_client_ip();
        $time = date("H:i");
        $profile_image = file_get_contents("$_SERVER[DOCUMENT_ROOT]/assets/images/default_profile.jpg");
        $result_image = $db -> sql_query("SELECT `image_url` FROM `users` WHERE `id` = '$user_id' AND `is_deleted` = '0'", "", "array");
        if (sizeof($result_image) > 0 AND $result_image[0]["image_url"] != ""){
            $image_url = $result_image[0]["image_url"];
            $context = stream_context_create(array(
                    'http' => array(
                        'timeout' => 3
                    )
                )
            );
            if (file_get_contents($image_url, 0, $context) !== false) {
                $profile_image = file_get_contents($image_url);
            } else {
                error_log("[$time $ip] profile_image_get: не получено изображение $image_url, user_id = $user_id\n", 3, $log_file);
            }
        }
        header('Content-Length: '.strlen($profile_image));
        return $profile_image;
    }


    # Метод получения списка устройств пользователя
    function devices($user_id = 0, $current_request_type) {
        global $db, $log_file;

        $client_request_access = client_request_access_status($current_request_type);

        if ($client_request_access["status"] == "OK") {
            $ip = $this -> get_client_ip();
            $time = date("H:i");
            $current_date = time();
            $response = array();

            $result_devices = $db -> sql_query("SELECT * FROM `devices` WHERE `user_id` = '$user_id' AND `is_deleted` = '0'", "", "array");

            return $response;
        } else {
            return $client_request_access;
        }
    }

    # Метод изменения статуса блокировки устройства
    function device_block($data, $current_request_type) {
        global $db, $log_file;

        $client_request_access = client_request_access_status($current_request_type);

        if ($client_request_access["status"] == "OK") {
            $ip = $this -> get_client_ip();
            $time = date("H:i");
            $current_date = time();

            $expected_params = array();

            if (isset($client_request_access["user_id"]) AND isset($client_request_access["device_uuid"]) ) {
                $user_id = $client_request_access["user_id"];
                $device_uuid = $client_request_access["device_uuid"];
            } else {
                if (isset($data["user_id"]) AND !empty($data["user_id"])) {
                    $user_id = $data["user_id"];
                } else {
                    $expected_params[] = "user_id";
                }

                if (isset($data["device_uuid"]) AND !empty($data["device_uuid"])) {
                    $device_uuid = $data["device_uuid"];
                } else {
                    $expected_params[] = "device_uuid";
                }
            }

            if (isset($data["blocked_device_uuid"]) AND !empty($data["blocked_device_uuid"])) {
                $blocked_device_uuid = $data["blocked_device_uuid"];
            } else {
                $expected_params[] = "blocked_device_uuid";
            }

            if (count($expected_params) > 0 ){

                $expected = implode(", ", $expected_params);
                $error = "Не получены параметры: $expected";
                $response["status"] = "ERROR";
                $response["message"] = $error;
                error_log("[$time $ip] device_block_app: $error\n", 3, $log_file);

            } else {

                $result_blocked_device = $db -> sql_query("SELECT * FROM `devices` WHERE `device_uuid` = '$blocked_device_uuid' AND `user_id` = '$user_id' AND `is_deleted` = '0'", "", "array");
                if (sizeof($result_blocked_device) > 0) {
                    $blocked_device_id = $result_blocked_device[0]["id"];
                }

                if ($blocked_device_id AND $device_uuid != $blocked_device_uuid) {

                    $result_state = $db -> sql_query("SELECT * FROM `devices_blocks_log` WHERE `blocked_device_id` = '$blocked_device_id' AND `user_id` = '$user_id' AND `unblocked_date` = '0'", "", "array");

                    if (sizeof($result_state) > 0 AND !empty($result_state[0])) {
                        #Если устройство было заблокировано, то оно разблокировывается и запись обновляется
                        if ($result_new_state = $db -> sql_query("UPDATE devices_blocks_log SET unblocked_date = '$current_date', unblock_requester = '$device_uuid' WHERE blocked_device_id = '$blocked_device_id' AND user_id = '$user_id'") AND $result_unblock = $db -> sql_query("UPDATE devices SET is_blocked = '0' WHERE id = '$blocked_device_id'")) {
                            $response["status"] = "OK";
                            $response["state"] = 0;

                            error_log("[$time $ip] device_block_app: user_id = $user_id разблокировал устройство blocked_device_id = $blocked_device_id \n", 3, $log_file);
                        } else {
                            $response["status"] = "ERROR";
                            $response["message"] = "Ошибка выполнения запроса";
                            error_log("[$time $ip] device_block_app: SQL-ERROR не обновлен devices_blocks_log\n", 3, $log_file);
                        }

                    } else {
                        #Если пользователь впервые блокирует устройство, то создается новая запись
                        if ($db -> sql_query("INSERT INTO `devices_blocks_log`(`id`, `user_id`, `blocked_device_id`, `blocked_date`, `block_requester`, `unblocked_date`, `unblock_requester`) VALUES (NULL, '$user_id', '$blocked_device_id', '$current_date', '$device_uuid','0', '')") AND $result_block = $db -> sql_query("UPDATE devices SET is_blocked = '1' WHERE id = '$blocked_device_id'")) {

                            $response["status"] = "OK";
                            $response["state"] = 1;
                            error_log("[$time $ip] device_block_app: user_id = $user_id заблокировал устройство blocked_device_id = $blocked_device_id \n", 3, $log_file);
                        } else {
                            $response["status"] = "ERROR";
                            $response["message"] = "Ошибка выполнения запроса";
                            error_log("[$time $ip] device_block_app: SQL-ERROR не добавлена запись в devices_blocks_log\n", 3, $log_file);
                        }
                    }
                } else {
                    $response["status"] = "ERROR";
                    $response["message"] = "NOT-FOUND";
                    error_log("[$time $ip] device_block_app: не найден blocked_device_uuid или device_uuid == blocked_device_uuid (device_uuid = $device_uuid, blocked_device_uuid - $blocked_device_uuid)\n", 3, $log_file);
                }
            }
            return $response;
        } else {
            return $client_request_access;
        }
    }

    # Метод получения id сессии мобильного приложения
    function get_device_session_id($device_id = 0, $connected_by = ""){
        global $db, $log_file;

        $ip = $this -> get_client_ip();
        $time = date("H:i");
        $current_date = time();

        $result = $db -> sql_query("SELECT `id` FROM `devices` WHERE `id` = '$device_id' AND `is_deleted` = '0'", "", "array");

        if (sizeof($result) > 0){
            # Закрытие предыдущих сессий устройства (в случае их наличия)
            $this -> destroy_device_sessions($device_id);

            $session_id = gen_uuid(32);
            $db -> sql_query("INSERT INTO `users_sessions_devices`(`id`, `session_id`, `device_id`, `connected_by`, `start_date`, `end_date`) VALUES (NULL, '$session_id', '$device_id', '$connected_by', '$current_date', '0')");
            error_log("[$time $ip] get_device_session_id: открыта новая сессия session_id - $session_id \n", 3, $log_file);

        } else {
            $session_id = "";
            error_log("[$time $ip] get_device_session_id: NOT-FOUND не найдено устройство пользователя device_id - $device_id\n", 3, $log_file);
        }

        return $session_id;
    }

    # Метод получения id сессии веб-приложения
    function get_web_session_id($user_id = 0, $connected_by = ""){
        global $db, $log_file;

        $ip = $this -> get_client_ip();
        $time = date("H:i");
        $current_date = time();

        $result = $db -> sql_query("SELECT `id` FROM `users` WHERE `id` = '$user_id' AND `is_deleted` = '0'", "", "array");

        if (sizeof($result) > 0){
            $session_id = gen_uuid(32);
            $user_agent = $_SERVER["HTTP_USER_AGENT"];
            $db -> sql_query("INSERT INTO `users_sessions_web`(`id`, `session_id`, `user_id`, `ip_address`, `user_agent`, `connected_by`, `start_date`, `end_date`) VALUES (NULL, '$session_id', '$user_id', '$ip', '$user_agent', '$connected_by', '$current_date', '0')");

            $_SESSION["web_session_id"] = $session_id;
            error_log("[$time $ip] get_web_session_id: открыта новая сессия session_id - $session_id \n", 3, $log_file);

        } else {
            $session_id = "";
            error_log("[$time $ip] get_web_session_id: NOT-FOUND user_id - $user_id\n", 3, $log_file);
        }

        return $session_id;
    }  

    # Получение ip адреса клиента
    function get_client_ip() {
        if (isset($_SERVER['HTTP_CLIENT_IP']))
            $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
        else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
            $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        else if(isset($_SERVER['HTTP_X_FORWARDED']))
            $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
        else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
            $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
        else if(isset($_SERVER['HTTP_FORWARDED']))
            $ipaddress = $_SERVER['HTTP_FORWARDED'];
        else if(isset($_SERVER['REMOTE_ADDR']))
            $ipaddress = $_SERVER['REMOTE_ADDR'];
        else
            $ipaddress = 'UNKNOWN';
        return $ipaddress;
    }
}
?>