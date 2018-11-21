<?php
class stream {

    # Возвращает детальную информацию о трансляции
    function stream_data($stream_uuid = "", $user_id = 0) {
        global $db, $project_options;

        $result = $db -> sql_query("SELECT `id`, `user_id`, `uuid`, `name`, `status`, `start_date`, `end_date`, `duration`, `permissions`, `chat_permissions`, `etag_stream`, `fb_shares_count`, `vk_shares_count`, `lat`, `lng` FROM `streams` WHERE `uuid` = '$stream_uuid' AND `is_excess` = '0' AND `is_blocked` = '0' AND `is_deleted` = '0'", "", "array");
        if (sizeof($result) > 0) {

            $likes_users_array = $this -> likes($stream_uuid);
            $likes_count = sizeof($likes_users_array);

            $comments_count = $this -> comments_count($stream_uuid);
            $comments_detail = $this -> chat_get_app($stream_uuid);

            #Массив id пользователей, просмотревших трансляцию
            $stream_id = $result[0]["id"];

            $stream_clients = $this -> get_stream_clients($stream_uuid);
            # Массив id пользователей, просмотривающих трансляцию
            $online_clients = $stream_clients["online"];
            # Массив id пользователей, просмотревших трансляцию
            $watched_clients = $stream_clients["watched"];

            $viewed = 0;
            if (in_array($user_id, $online_clients) OR in_array($user_id, $watched_clients)){
                $viewed = 1;
            }

            # Количество пользователей, просматривающих трансляцию в данный момент
            $clients_count = sizeof($stream_clients["online"]);

            # Количество уникальных просмотров трансляции
            $unique_views_count = $this -> get_stream_unique_views($stream_uuid);

            $loc_dynamics = $this -> get_loc_dynamics($stream_id);
            $ori_dynamics = $this -> get_ori_dynamics($stream_id);
            $heading_dynamics = $this -> get_heading_dynamics($stream_id);
            $altitude_dynamics = $this -> get_altitude_dynamics($stream_id);
            $categories = $this -> get_stream_categories($stream_id);

            $shares_fb = $result[0]["fb_shares_count"];
            $shares_vk = $result[0]["vk_shares_count"];
            $shares_total = $shares_fb + $shares_vk;

            $lat = floatval($result[0]["lat"]);
            $lng = floatval($result[0]["lng"]);

            $stream_data["uuid"] = $stream_uuid;
            $stream_data["user_id"] = intval($result[0]["user_id"]);
            $stream_data["name"] = $result[0]["name"];
            $stream_data["status"] = intval($result[0]["status"]);
            $stream_data["start_date"] = intval($result[0]["start_date"]);
            $stream_data["start_date_float"] = floatval($result[0]["start_date"]);
            $stream_data["end_date"] = intval($result[0]["end_date"]);
            $stream_data["duration"] = floatval($result[0]["duration"]);
            $stream_data["url"] = $this -> generate_stream_url($stream_uuid);
            $stream_data["client_count"] = $clients_count;
            $stream_data["unique_clients_count"] = $unique_views_count; # Удалить данный параметр в будущем! (переименован в unique_views_count)
            $stream_data["unique_views_count"] = $unique_views_count;
            $stream_data["watched_clients"] = $watched_clients;
            $stream_data["viewed"] = $viewed;
            $stream_data["lat"] = $lat;
            $stream_data["lng"] = $lng;
            $stream_data["locality"] = $this -> get_locality($lat, $lng);
            $stream_data["loc_dynamics"] = $loc_dynamics;
            $stream_data["ori_dynamics"] = $ori_dynamics;
            $stream_data["heading_dynamics"] = $heading_dynamics;
            $stream_data["altitude_dynamics"] = $altitude_dynamics;

            $permissions = unserialize($result[0]["permissions"]);
            $stream_data["permissions"] = array();
            if (sizeof($permissions) > 0){
                $stream_data["permissions"] = $permissions;
            }
            $stream_data["chat_permissions"] = intval($result[0]["chat_permissions"]);

            $stream_data["thumb"] = $project_options['service_url_inner']."/api/v1/streams/snapshot/$stream_uuid";
            $stream_data["likes_count"] = $likes_count;
            $stream_data["likes_detail"] = $likes_users_array;
            $stream_data["comments_count"] = $comments_count;
            $stream_data["comments"] = $comments_detail;
            $stream_data["shares"]["total"]  = $shares_total;
            $stream_data["shares"]["fb_shares_count"]  = $shares_fb;
            $stream_data["shares"]["vk_shares_count"]  = $shares_vk;

            #Массив id зрителей трансляции
            $stream_data["connections"]  = $stream_clients["online"];
            $stream_data["ws_url"]  = $project_options["ws_urls"]["ws_stream"];
            $stream_data["categories"]  = $categories;

            #Поиск последнего записанного значения ориентации видео
            $last_ori = "";
            $result_ori = $db -> sql_query("SELECT `ori` FROM `streams_ori_dynamics` WHERE `updated_date` = (SELECT MAX(`updated_date`) FROM `streams_ori_dynamics` WHERE `stream_id` = '$stream_id' GROUP BY `stream_id`)", "", "array");
            if (sizeof($result_ori) > 0) {
                $last_ori = $result_ori[0]["ori"];
            }
            $stream_data["last_ori"]  = $last_ori;

            $stream_data["etag"]  = $result[0]["etag_stream"];

            $response["status"] = "OK";
            $response["data"] = $stream_data;
        } else {
            $response["status"] = "ERROR";
            $response["message"] = "NOT-FOUND";
        }

        return $response;
    }

    # Возвращает информацию о трансляции для ws соединения
    function ws_stream_data($stream_uuid = "") {
        global $db;

        $result = $db -> sql_query("SELECT `user_id`, `start_date`, `duration` FROM `streams` WHERE `uuid` = '$stream_uuid' AND `is_excess` = '0' AND `is_blocked` = '0' AND `is_deleted` = '0'", "", "array");
        if (sizeof($result) > 0) {

            $likes_users_array = $this -> likes($stream_uuid);
            $likes_count = sizeof($likes_users_array);

            $response["status"] = "OK";
            $response["data"]["user_id"] = $result[0]["user_id"];
            $response["data"]["start_date_float"] = floatval($result[0]["start_date"]);
            $response["data"]["duration"] = floatval($result[0]["duration"]);
            $response["data"]["likes_count"] = $likes_count;
        } else {
            $response["status"] = "ERROR";
            $response["message"] = "NOT-FOUND";
        }

        return $response;
    }

    # Возвращает информацию о каждом потоке из массива uuid потоков
    function streams_array_data($data, $user_id = 0, $lang = "en"){
        global $dbhost, $dbuname, $dbpass, $dbname, $project_options;
        $snapshot_url = $project_options['service_url_inner']."api/v1/streams/snapshot/";

        $response = array();
        $mysqli = new mysqli($dbhost, $dbuname, $dbpass, $dbname);
        $mysqli -> set_charset("utf8");
        $s = "'" . implode("','", $data) . "'";

        $res = $mysqli -> query("SELECT `id`, `streams`.`uuid` AS `uuid`, `streams`.`user_id` AS `user_id`, `streams`.`status` AS `status`,  ROUND(`start_date`, 0) AS `start_date`, ROUND(`end_date`, 0) AS `end_date`, `duration`, CONCAT('$snapshot_url', `streams`.`uuid`) AS `thumb`, `permissions`, `chat_permissions`, `lat`, `lng`, 
    CASE  WHEN `streams`.`name` != ''
          THEN `streams`.`name`
          WHEN  '$lang' LIKE '%ru%'
          THEN 'Без названия'
          ELSE 'No name'
          END AS `name`, 
    CASE  WHEN '$lang' LIKE '%ru%' AND `streams`.`on_map` = 1
          THEN (SELECT `locality_ru` FROM `streams_locality_desc` WHERE `streams_locality_desc`.`stream_id` =  `streams`.`id`)
          WHEN `streams`.`on_map` = 1 
          THEN (SELECT `locality_en` FROM `streams_locality_desc` WHERE `streams_locality_desc`.`stream_id` =  `streams`.`id`)
          ELSE ''
        END AS `locality`,
    CASE  WHEN `streams`.`id` IN (SELECT `streams_clients`.`stream_id` FROM `streams_clients` WHERE `streams_clients`.`user_id` = '$user_id' AND `streams_clients`.`stream_id` = `streams`.`id`)
          THEN 1 
          ELSE 0 
          END AS `viewed`,
          (SELECT COUNT(DISTINCT `user_id`) FROM `streams_clients` WHERE `streams_clients`.`stream_id` = `streams`.`id`) AS `unique_views_count`,
        (SELECT COUNT(`id`) FROM `streams_clients` WHERE `streams_clients`.`stream_id` = `streams`.`id`) AS `views_count`,
       (SELECT COUNT(DISTINCT `user_id`) FROM `streams_clients` WHERE `streams_clients`.`stream_id` = `streams`.`id` AND `disconnected_date` = '0') AS `client_count`, 
       (SELECT COUNT(*) FROM `users_actions_log` LEFT JOIN `users` ON `users`.`id` = `users_actions_log`.`user_id` WHERE `users_actions_log`.`stream_id` = `streams`.`id` AND `users_actions_id` = '1' AND `users`.`is_deleted` = '0') AS `likes_count`, `streams`.`etag_stream` AS `etag`
       FROM `streams` WHERE `streams`.`uuid` IN (". $s . ") AND `streams`.`is_excess` = '0' AND `streams`.`is_blocked` = '0' AND `streams`.`is_deleted` = '0' ORDER BY `streams`.`id` DESC");

        while($row = mysqli_fetch_assoc($res)) {
            $permissions_array = unserialize($row["permissions"]);
            $row["permissions"] = array();
            if (sizeof($permissions_array) > 0){
                $row["permissions"] = $permissions_array;
            }

            $stream_id = $row["id"];
            $row["categories"] = array();

            $res_categories = $mysqli -> query("SELECT `stream_category_id` FROM `streams_categories_link` LEFT JOIN `streams_categories_data` ON `streams_categories_link`.`stream_category_id` = `streams_categories_data`.`id` WHERE `stream_id` = '$stream_id' AND `streams_categories_link`.`is_deleted` = '0' AND `streams_categories_data`.`is_active` = '1' AND `streams_categories_data`.`is_deleted` = '0'");
            while($row_cat = mysqli_fetch_assoc($res_categories)) {
                $row["categories"][] = $row_cat["stream_category_id"];
            }

            $row["url"] = $this -> generate_stream_url($row["uuid"]);
            
            $response[] = $row;
        }
        $mysqli -> close();
        return $response;
    }

    # Возвращает etag для каждого элемента из массива uuid трансляций
    function streams_etags_data($data){
        global $db;
        $response = array();
        $s = "'" . implode("','", $data) . "'";

        $res = $db -> sql_query("SELECT `uuid`, `etag_stream`  FROM `streams` WHERE `uuid` IN (". $s . ") AND `is_excess` = '0'  AND `is_deleted` = '0'", "", "array");

        foreach ($data as $stream_uuid) {
            $stream_data = false;

            foreach ($res as $value) {
                $uuid = $value["uuid"];
                $etag = $value["etag_stream"];

                if ($uuid == $stream_uuid) {
                    $stream_data = true;

                    $response[$uuid] = $etag;
                    break;
                }
            }
            if (!$stream_data) {
                $response[$stream_uuid] = "NOT-FOUND";
            }
        }

        return $response;
    }

    # Обновление etag трансляции
    function etag_stream_update($stream_uuid = ""){
    	global $db;
        $etag_stream = gen_uuid(12);
        $db -> sql_query("UPDATE `streams` SET `etag_stream` = '$etag_stream' WHERE `uuid` = '$stream_uuid'");
	}	
    	
    # Возвращает название трансляции
    function stream_name($stream_uuid = "") {
        global $db;
        $stream_name = _NO_STREAM_NAME;
        $result_stream = $db -> sql_query("SELECT `name` FROM `streams` WHERE `uuid` = '$stream_uuid'", "", "array");
        if (sizeof($result_stream) > 0 AND !empty($result_stream[0]["name"])){
            $stream_name = $result_stream[0]["name"];
        }
        return $stream_name;
    }

    # Возвращает количество комментариев к трансляции
    function comments_count($stream_uuid = "") {
        global $db;
        
        $result = $db -> sql_query("SELECT * FROM `users_actions_log` LEFT JOIN `streams` ON `users_actions_log`.`stream_id` = `streams`.`id` LEFT JOIN `users` ON `streams`.`user_id` = `users`.`id` WHERE `users_actions_log`.`users_actions_id` = '5' AND `users_actions_log`.`created_date` >= `streams`.`start_date` AND `users_actions_log`.`hero_id` = `streams`.`user_id`  AND `streams`.`uuid` = '$stream_uuid' AND `streams`.`is_excess` = '0' AND `streams`.`is_blocked` = '0' AND `streams`.`is_deleted` = '0' AND `users`.`is_blocked` = '0' AND `users`.`is_deleted` = '0'  ORDER BY `users_actions_log`.`created_date` ASC", "", "array");

        $comments_count = sizeof($result);
        return $comments_count;
    }

    # Возвращает массив id пользователей, отметивших like запрашиваемой трансляции
    function likes($stream_uuid = "") {
        global $db;
        $likes = array();

        $result = $db -> sql_query("SELECT `users_actions_log`.`user_id` AS `liked_user_id` FROM `users_actions_log` LEFT JOIN `streams` ON `streams`.`id` = `users_actions_log`.`stream_id` LEFT JOIN `users` ON `users`.`id` = `users_actions_log`.`user_id` WHERE `streams`.`uuid` = '$stream_uuid' AND `users_actions_log`.`users_actions_id` = '1' AND `streams`.`is_excess` = '0' AND `streams`.`is_blocked` = '0' AND `streams`.`is_deleted` = '0' AND `users`.`is_blocked` = '0' AND `users`.`is_deleted` = '0'", "", "array");

        if (sizeof($result) > 0){
            foreach ($result as $value) {
                array_push($likes, $value["liked_user_id"]);
            }
        }

        return $likes;
    }

    # Возвращает общее количество лайков по всем трансляциям запрашиваемого пользователя
    function total_likes_count($user_id = 0) {
        global $db;
        $total_likes_count = 0;

        $result = $db -> sql_query("SELECT `uuid` FROM `streams` WHERE `user_id` = '$user_id' AND `is_excess` = '0' AND `is_blocked` = '0' AND `is_deleted` = '0'", "", "array");

        if (sizeof($result) > 0){

            foreach ($result as $value) {
                $stream_uuid = $value["uuid"];
                $likes_count = sizeof($this -> likes($stream_uuid));
                $total_likes_count = $total_likes_count + $likes_count;
            }
        }

        return $total_likes_count;
    }

    # Для каждой трансляции возвращает количество клиентов, просматривающих в данный момент
    function get_streams_clients_count() {
        global $db;
        $response = array();
        $result = $db -> sql_query("SELECT * FROM `streams` WHERE `is_excess` = '0' AND `is_blocked` = '0' AND `is_deleted` = '0'", "", "array");
        if (sizeof($result) > 0) {
            foreach ($result as $value) {
                $stream_uuid = $value["uuid"];
                $stream_clients = $this -> get_stream_clients($stream_uuid);
                $clients_count = sizeof($stream_clients["online"]);
                $response[$stream_uuid] = $clients_count;
            }
        }
        return $response;
    }
    
    # Возвращает словарь массивов id пользователей, просмотревших трансляцию и просматривающих ее в данный момент
    function get_stream_clients($stream_uuid = "") {
        global $db;

        $response["online"] = array();
        $response["watched"] = array();

        $result = $db -> sql_query("SELECT * FROM `streams` WHERE `uuid` = '$stream_uuid' AND `is_excess` = '0' AND `is_deleted` = '0'", "", "array");
        if (sizeof($result) > 0 AND !empty($result[0])) {
            $stream_id = $result[0]["id"];
            $result_clients = $db -> sql_query("SELECT * FROM `streams_clients` WHERE `stream_id` = '$stream_id'", "", "array");
            foreach ($result_clients as $value) {
                $client_id = $value["user_id"];
                if ($value["disconnected_date"] == 0) {
                    if (!in_array($client_id,  $response["online"])) {
                        #Список клиентов, которые просматривают данную трансляцию
                        $response["online"][] = $client_id;
                    }
                } else {
                    if (!in_array($client_id,  $response["watched"])) {
                        #Список клиентов, которые просматривали данную трансляцию
                        $response["watched"][] = $client_id;
                    }
                }
            }
        }
        return $response;
    }

    # Возвращает общее количество просмотров трансляции
    function get_stream_views($stream_uuid = "") {
        global $db;

        $result = $db -> sql_query("SELECT `streams_clients`.`id` AS `view_id` FROM `streams` LEFT JOIN `streams_clients` ON `streams`.`id` = `streams_clients`.`stream_id` WHERE `uuid` = '$stream_uuid' AND `is_excess` = '0' AND `is_deleted` = '0'", "", "array");
        $response = sizeof($result);

        return $response;
    }

    # Возвращает количество уникальных просмотров трансляции
    function get_stream_unique_views($stream_uuid = "") {
        global $db;

        $result = $db -> sql_query("SELECT `streams_clients`.`user_id` FROM `streams` LEFT JOIN `streams_clients` ON `streams`.`id` = `streams_clients`.`stream_id` WHERE `uuid` = '$stream_uuid' GROUP BY `streams_clients`.`user_id`", "", "array");
        $response = sizeof($result);

        return $response;
    }

    # Возвращает динамику изменения координат трансляции
    function get_loc_dynamics($stream_id = 0){
        global $db;

        $loc_dynamics = array();

        $result_loc_dynamics = $db -> sql_query("SELECT `updated_date`, `time_from_start`, `lat`, `lng` FROM `streams_coordinates_dynamics` WHERE `stream_id` = '$stream_id'", "", "array");
        foreach ($result_loc_dynamics as $val){
            $updated_date = $val["updated_date"];
            $time_from_start = $val["time_from_start"];
            $lat = $val["lat"];
            $lng = $val["lng"];
            $loc_dynamics[$time_from_start][] = $lat;
            $loc_dynamics[$time_from_start][] = $lng;
        }
        return $loc_dynamics;
    }

    # Возвращает динамику изменения ориентации трансляции
    function get_ori_dynamics($stream_id = 0){
        global $db;

        $ori_dynamics = array();

        $result_ori_dynamics = $db -> sql_query("SELECT `time_from_start`, `ori` FROM `streams_ori_dynamics` WHERE `stream_id` = '$stream_id'", "", "array");
        foreach ($result_ori_dynamics as $val){
            $time_from_start = $val["time_from_start"];
            $ori = $val["ori"];
            $ori_dynamics[$time_from_start] = $ori;
        }
        return $ori_dynamics;
    }

    # Возвращает динамику изменения ориентации трансляции (новый вариант!)
    function get_ori_dynamics2($stream_id = 0){
        global $db;

        $ori_dynamics = array();

        $result_ori_dynamics = $db -> sql_query("SELECT * FROM `streams_ori_dynamics` WHERE `stream_id` = '$stream_id'", "", "array");
        foreach ($result_ori_dynamics as $val){
            $time_from_start = $val["time_from_start"];
            $ori = $val["ori"];
            $obj["time"] = floatval($time_from_start);
            $obj["ori"] = $ori;
            array_push($ori_dynamics, $obj);
        }
        return $ori_dynamics;
    }

    # Возвращает динамику изменения направления камеры
    function get_heading_dynamics($stream_id = 0){
        global $db;

        $heading_dynamics = array();

        $result_heading_dynamics = $db -> sql_query("SELECT `time_from_start`, `heading` FROM `streams_heading_dynamics` WHERE `stream_id` = '$stream_id'", "", "array");
        foreach ($result_heading_dynamics as $val){
            $time_from_start = $val["time_from_start"];
            $heading = $val["heading"];
            $heading_dynamics[$time_from_start] = $heading;
        }
        return $heading_dynamics;
    }

    # Возвращает динамику изменения положения камеры относительно уровня моря
    function get_altitude_dynamics($stream_id = 0){
        global $db;

        $altitude_dynamics = array();

        $result_altitude_dynamics = $db -> sql_query("SELECT `time_from_start`, `altitude` FROM `streams_altitude_dynamics` WHERE `stream_id` = '$stream_id'", "", "array");
        foreach ($result_altitude_dynamics as $val){
            $time_from_start = $val["time_from_start"];
            $altitude = $val["altitude"];
            $altitude_dynamics[$time_from_start] = $altitude;
        }
        return $altitude_dynamics;
    }

    # Возвращает динамику изменения направления камеры для flash плеера
    function get_heading_dynamics_for_flash_player($stream_uuid = ""){
        global $db;

        $heading_dynamics = array();

        $result_heading_dynamics = $db -> sql_query("SELECT `time_from_start`, `heading` FROM `streams_heading_dynamics` LEFT JOIN `streams` ON `streams`.`id` = `streams_heading_dynamics`.`stream_id` WHERE `uuid` = '$stream_uuid' AND `streams`.`is_excess` = '0' AND `streams`.`is_deleted` = '0'", "", "array");
        foreach ($result_heading_dynamics as $val){
            $heading_data["time_from_start"] = $val["time_from_start"];
            $heading_data["heading"] = $val["heading"];
            $heading_dynamics[] = $heading_data;
        }
        return $heading_dynamics;
    }

    # Возвращает динамику изменения ориентации камеры для flash плеера
    function get_ori_dynamics_for_flash_player($stream_uuid = ""){
        global $db, $project_options;

        $site_auth_login = $project_options["site_auth_login"];
        $site_auth_pass = $project_options["site_auth_pass"];

        if (isset($_SERVER["HTTP_AUTH_SIGNATURE"]) AND $_SERVER["HTTP_AUTH_SIGNATURE"] == sha1($site_auth_login.$site_auth_pass)) {

            $ori_dynamics = array();
            
            $result_ori_dynamics = $db -> sql_query("SELECT `time_from_start`, `ori` FROM `streams_ori_dynamics` LEFT JOIN `streams` ON `streams`.`id` = `streams_ori_dynamics`.`stream_id` WHERE `uuid` = '$stream_uuid' AND `streams`.`is_excess` = '0' AND `streams`.`is_deleted` = '0'", "", "array");
            foreach ($result_ori_dynamics as $val){
                $ori_data["time_from_start"] = $val["time_from_start"];
                $ori_data["ori"] = $val["ori"];
                $ori_dynamics[] = $ori_data;
            }
            
            $response["status"] = "OK";
            $response["ori_dynamics"] = $ori_dynamics;
            return $response;
        }

        $response["status"] = "ACCESS-DENIED";
        return $response;        
    }

    # Возвращает количество расшариваний трансляции в социальных сетях (api twitter не позволяет получить количество расшариваний)
    function share_count($stream_uuid = ""){
        global $db, $log_file, $user;
        $ip = $user -> get_client_ip();
        $time = date("H:i");
        $share_count["total"] = 0;
        $share_count["fb_shares_count"] = 0;
        $share_count["vk_shares_count"] = 0;
        $result_stream = $db -> sql_query("SELECT `fb_shares_count`, `vk_shares_count` FROM `streams` WHERE `uuid` = '$stream_uuid' AND `is_excess` = '0' AND `is_deleted` = '0'", "", "array");

        if (sizeof($result_stream) > 0) {
            $fb_shares = intval($result_stream[0]["fb_shares_count"]);
            $vk_shares = intval($result_stream[0]["vk_shares_count"]);

            $share_count["total"] = $fb_shares + $vk_shares;
            $share_count["fb_shares_count"] = $fb_shares;
            $share_count["vk_shares_count"] = $vk_shares;

        } else {
            error_log("[$time $ip] share_count: не найдена трансляция stream_uuid - $stream_uuid \n", 3, $log_file);
        }

        return $share_count;
    }

    # Получение списка комментариев к запрашиваемой трансляции (веб-версия)
    function chat_get($data, $chat_admin_id = 0){
        global $db, $user;
        $response["html"] = "";

        $stream_id 	= prepair_str($data["stream_id"]);
        $result = $db -> sql_query("SELECT `user_id`, `start_date` FROM `streams` WHERE `id` = '$stream_id' AND `is_excess` = '0' AND `is_deleted` = '0'", "", "array");
        $hero_id = $result[0]["user_id"];
        $stream_start_date = intval($result[0]["start_date"]);
        $permissions = 0;
        $response["permissions"] = 0;

        if ($hero_id) {
            $result_chat = $db -> sql_query("SELECT `users_actions_log`.`id` AS `line_id`, `users_actions_id`, `users_actions_log`.`user_id` AS `user_id`, `stream_id`, `comment` FROM `users_actions_log` LEFT JOIN `users` ON `users_actions_log`.`user_id` = `users`.`id` WHERE `users_actions_log`.`hero_id` = '$hero_id' AND `users`.`is_blocked` = '0' AND `users`.`is_deleted` = '0' AND `users_actions_log`.`created_date` >= '$stream_start_date' ORDER BY `users_actions_log`.`created_date` ASC", "", "array");

            if (sizeof($result_chat) > 0) {
                $lines = array();

                if (($hero_id == $chat_admin_id)) {
                    $permissions = 1;
                    $response["permissions"] = 1;
                }

                $blocked_users_array = $user -> blocked($hero_id);

                foreach ($result_chat as $value){
                    $line_id = $value["line_id"];
                    $user_id = $value["user_id"];
                    $action = $value["users_actions_id"];
                    $current_stream_id = $value["stream_id"];

                    $profile_image = $user -> profile_image_html($user_id);
                    $profile_name = $user -> profile_name($user_id);
                    $hero_name = $user -> profile_name($hero_id);

                    $comment = "";
                    $blocked_state = 0;
                    if (in_array($user_id, $blocked_users_array)) {
                        $blocked_state = 1;
                    }
                    switch ($action) {
                        #Добавлен новый like
                        case "1" :
                            if ($current_stream_id == $stream_id) {
                                $comment = _LIKED_THIS;
                            }
                            break;
                        #Добавлен новый подписчик
                        case "3" :
                            $comment = _FOLLOWS." ".$hero_name;
                            break;
                        #Добавлен новый комментарий
                        case "5" :
                            if ($current_stream_id == $stream_id) {
                                $comment = $value["comment"];
                            }
                            break;
                        #Владелец трансляции заблокировал зрителя в чате
                        case "6" :
                            $comment = "<span>" . _BLOCKED . "</span>";
                            break;
                        #Трансляция завершена
                        case "8" :
                            if ($current_stream_id == $stream_id) {
                                $comment = "<span>" . _STREAM_CANCELED . "</span>";
                            }
                            break;
                    }

                    if (!empty($comment)){
                        if ($permissions == 1 AND $user_id != $chat_admin_id) {
                            #Если чат запрашивает владелец трансляции, то у него есть возможность заблокировать зрителя
                            $line = "<div class=\"line\" data-line-id=\"$line_id\"><div class=\"comment_autor_image\">$profile_image</div><div class=\"comment_message\"><span>$profile_name</span> $comment</div><div class=\"block_user\" data-user-id=\"$user_id\" data-blocked-state=\"$blocked_state\" data-toggle=\"tooltip\" title=\"" . _BLOCK_USER . "\"><i class=\"fa fa-lock fa-lg\"></i></div></div>";
                        } else {
                            $line = "<div class=\"line\" data-line-id=\"$line_id\"><div class=\"comment_autor_image\">$profile_image</div><div class=\"comment_message\"><span>$profile_name</span> $comment</div></div>";
                        }
                        array_push($lines, $line);
                    }
                }
                $response["html"] = $lines;
            } else {
                $response["html"] = "empty";
            }
        }

        return $response;
    }

    # Возвращает сервер записи трансляции
    function get_server($stream_uuid = ""){
        global $db, $project_options;

//        Раскомментировать при использовании нескольких серверов записи

//        $result = $db -> sql_query("SELECT `storage_server` FROM `streams` WHERE `uuid` = '$stream_uuid' AND `is_excess` = '0' AND `is_deleted` = '0'", "", "array");
//        if (sizeof($result) > 0) {
//            $response["status"] = "OK";
//            $response["server"] = $result[0]["storage_server"];
//        } else {
//            $response["status"] = "ERROR";
//        }



        $response["status"] = "OK";
        $response["server"] = $project_options["storage_servers"][0];
        return $response;
    }

    # Метод регистрации новой трансляции
    function stream_start_app($data, $current_request_type) {
        global $db, $log_file, $user, $project_options;

        $client_request_access = client_request_access_status($current_request_type);
        if ($client_request_access["status"] == "OK") {

            $ip = $user -> get_client_ip();
            $time = date("H:i");
            $current_date = time();

            $stream_name = "";
            $on_map = 0;
            $permissions = array();
            $lat = 0;
            $lng = 0;
            $categories_array = array();

            $expected_params = array();

            $user_id = $client_request_access["user_id"];
            $device_uuid = $client_request_access["device_uuid"];
            $device_id = $client_request_access["device_id"];            
            
            if (isset($data["stream_name"]) AND !empty($data["stream_name"])) {
                $stream_name = prepair_str($data["stream_name"]);
            }

            if (isset($data["permissions"])) {
                if (sizeof($data["permissions"]) > 0 AND is_array($data["permissions"])){
                    $permissions = $data["permissions"];
                }
                $permissions_sql = serialize($permissions);
            } else {
                $expected_params[] = "permissions";
            }

            if (isset($data["chat_permissions"]) AND ($data["chat_permissions"] == 0 OR $data["chat_permissions"] == 1)) {
                $chat_permissions = $data["chat_permissions"];
            } else {
                $expected_params[] = "chat_permissions";
            }

            if (isset($data["lat"]) AND !empty($data["lat"]) AND isset($data["lng"]) AND !empty($data["lng"])) {
                $lat = prepair_str($data["lat"]);
                $lng = prepair_str($data["lng"]);
                $on_map = 1;
            }

            if (isset($data["categories_array"]) AND is_array($data["categories_array"]) AND sizeof($data["categories_array"]) > 0) {
                $categories_array = $data["categories_array"];
            }

            if (count($expected_params) > 0 ){
                $expected = implode(", ", $expected_params);
                $error = "Не получены параметры: $expected";
                $response["status"] = "ERROR";
                $response["message"] = $error;
                error_log("[$time $ip] stream_start: ERROR user_id - $user_id, device_uuid - $device_uuid $error\n", 3, $log_file);
            } else {

                $storage_server = $project_options["storage_servers"][0];
                $stream_uuid = gen_uuid(16);
                $etag_stream = gen_uuid(12);
                $start_date_ms = microtime_float();

                # Проверка наличия в данный момент незавершенной трансляции с устройства пользователя
                $result_existing_stream = $db -> sql_query("SELECT `uuid` FROM `streams` WHERE `device_id` = '$device_id' AND `end_date` = '0' AND `is_excess` = '0' AND `is_blocked` = '0' AND `is_deleted` = '0'", "", "array");

                if (sizeof($result_existing_stream) > 0) {
                    $current_streams = array();
                    foreach ($result_existing_stream as $v) {
                        array_push($current_streams, $v["uuid"]);
                    }
                    $current_streams = implode(", ", $current_streams);
                    $response["status"] = "ERROR";
                    $response["unfinished_streams_array"] = $current_streams;
                    $response["message"] = "Последняя трансляция еще не завершена";
                    error_log("[$time $ip] stream_start: ERROR наличие незавершенных трансляций $current_streams с устройства device_id - $device_id \n", 3, $log_file);

                } else {
                    if ($result_add_stream = $db -> sql_query("INSERT INTO `streams` (`id`, `uuid`, `device_id`, `user_id`, `ip`, `name`, `storage_server`, `start_date`, `end_date`, `duration`, `status`, `lat`, `lng`, `permissions`, `chat_permissions`, `on_map`, `is_updated`, `is_blocked`, `is_deleted`, `etag_stream`) VALUES (NULL, '$stream_uuid', '$device_id','$user_id', '$ip', '$stream_name', '$storage_server', '$start_date_ms', '0', '0', '1', '$lat', '$lng', '$permissions_sql', '$chat_permissions', '$on_map', '0', '0', '0', '$etag_stream')")) {
                        $stream_id = $db -> sql_nextid($result_add_stream);

                        $user -> etag_user_update($user_id); # Обновление etag владельца трансляции
                        $data_tags = get_hashtags_from_string($stream_name);

                        $result_streams_tags = $db -> sql_query("SELECT * FROM `streams_tags_data`", "", "array");
                        #Создание массива всех тегов
                        $existing_streams_tags = array();
                        foreach ($result_streams_tags as $value) {
                            array_push($existing_streams_tags, $value["name"]);
                        }

                        foreach ($data_tags as $value) {
                            if (!empty($value)) {
                                if (in_array($value, $existing_streams_tags)) { #Получение id существующего тега
                                    $result_tag = $db -> sql_query("SELECT `id` FROM `streams_tags_data` WHERE `name` = '$value'", "", "array");
                                    $tag_id = $result_tag[0]["id"];
                                } else { #Создание нового тега
                                    $result_add_tag = $db -> sql_query("INSERT INTO `streams_tags_data` (`id`, `name`, `is_disabled`) VALUES (NULL, '$value', '0')");
                                    $tag_id = $db -> sql_nextid($result_add_tag);
                                }
                                $db -> sql_query("INSERT INTO `streams_tags` (`id`, `user_id`, `stream_id`, `stream_tag_id`, `is_deleted`) VALUES (NULL, '$user_id', '$stream_id', '$tag_id', '0')");
                            }
                        }

                        if ($on_map == 1) {
                            $locality_en = $this -> get_locality($lat, $lng, "en");
                            $locality_ru = $this -> get_locality($lat, $lng, "ru");

                            try {
                                $db -> sql_query("INSERT INTO `streams_locality_desc` (`stream_id`, `locality_en`, `locality_ru`) VALUES (\"".$stream_id."\", \"".$locality_en."\", \"".$locality_ru."\")");
                            } catch (Exception $e) {
                                error_log("[$time $ip] stream_start: SQL-ERROR LOCALITY ADD " . $e->getMessage() . "\n", 3, $log_file);
                            }
                        }

                        if (sizeof($categories_array) > 0) {
                            $this -> link_stream_categories($stream_id, $categories_array);
                        }

                        $this -> push_notification_new_stream($stream_uuid);
                        $stream_data = $this -> stream_data($stream_uuid, $user_id);

                        if ($stream_data["status"] == "OK") {
                            $response["status"] = "OK";
                            $response["message"] = $stream_uuid;
                            $response["url"] = $stream_data["data"]["url"];
                            $response["data"] = $stream_data["data"];
                            error_log("[$time $ip] stream_start: OK создана трансляция stream_uuid  -  $stream_uuid\n", 3, $log_file);
                        } else {
                            $response["status"] = "ERROR";
                            $response["message"] = "Ошибка выполнения запроса";
                            $status = $stream_data["status"];
                            $message = $stream_data["message"];
                            error_log("[$time $ip] stream_start: $status $message ошибка получения данных о трансляции stream_uuid  -  $stream_uuid\n", 3, $log_file);
                        }

                    } else {
                        $response["status"] = "ERROR";
                        $response["message"] = "Ошибка выполнения запроса";
                        error_log("[$time $ip] stream_start: SQL-ERROR ошибка выполнения запроса\n", 3, $log_file);
                    }
                }

            }
            ob_end_clean();
            return $response;
        } else {
            return $client_request_access;
        }
    }

    # Получение списка комментариев к запрашиваемой трансляции (мобильная версия)
    function chat_get_app($stream_uuid = "") {
        global $db, $profile_image_query, $user;
        $response = array();

        $result = $db -> sql_query("SELECT `id`, `user_id`, `start_date` FROM `streams` WHERE `uuid` = '$stream_uuid' AND `is_excess` = '0' AND `is_deleted` = '0'", "", "array");
        $stream_id = $result[0]["id"];
        $hero_id = $result[0]["user_id"];
        $stream_start_date = intval($result[0]["start_date"]);

        if ($hero_id) {
            $result_chat = $db -> sql_query("SELECT `users_actions_id`, `users_actions_log`.`user_id` AS `user_id`, `stream_id`, `comment`, `users_actions_log`.`created_date` AS `created_date`, `users_actions_log`.`time_from_start` AS `time_from_start` FROM `users_actions_log` LEFT JOIN `users` ON `users_actions_log`.`user_id` = `users`.`id` WHERE `users_actions_log`.`hero_id` = '$hero_id' AND `users`.`is_blocked` = '0' AND `users`.`is_deleted` = '0' AND `users_actions_log`.`created_date` >= '$stream_start_date' ORDER BY `users_actions_log`.`created_date` ASC", "", "array");
            if (sizeof($result_chat) > 0) {
                $data = array();
                $hero_name = $user -> profile_name($hero_id);

                foreach ($result_chat as $value){
                    $user_id = $value["user_id"];

                    $profile_image_url = $profile_image_query.$user_id;
                    $display_name = $user -> profile_name($user_id);

                    $action = $value["users_actions_id"];
                    $current_stream_id = $value["stream_id"];
                    $timestamp = $value["created_date"];
                    $time_from_start = $value["time_from_start"];
                    $comment_type = "";
                    $comment = "";
                    switch ($action) {
                        #Добавлен новый like
                        case "1" :
                            if ($current_stream_id == $stream_id) {
                                $comment_type = "new_like";
                                $comment = _LIKED_THIS;
                            }
                            break;
                        #Добавлен новый подписчик
                        case "3" :
                            $comment_type = "new_follower";
                            $comment = _FOLLOWS." ".$hero_name;
                            break;
                        #Добавлен новый комментарий
                        case "5" :
                            if ($current_stream_id == $stream_id) {
                                $comment_type = "message";
                                $comment = $value["comment"];
                            }
                            break;
                        #Владелец трансляции заблокировал зрителя в чате
                        case "6" :
                            $comment_type = "user_blocked";
                            $comment = _BLOCKED;
                            break;
                    }

                    if (!empty($comment)) {
                        $data["user_id"] = $user_id;
                        $data["client_display_name"] = $display_name;
                        $data["client_image_url"] = $profile_image_url;
                        $data["type"] = $comment_type;
                        $data["comment"] = $comment;
                        $data["timestamp"] = $timestamp;
                        $data["time_from_start"] = $time_from_start;
                        array_push($response, $data);
                    }

                }
            }
        }

        return $response;
    }

    # Получение 5 последних комментариев к запрашиваемой трансляции
    function recent_chat_get_app($stream_uuid = "", $current_request_type) {
        global $db, $user;

        $client_request_access = client_request_access_status($current_request_type);

        if ($client_request_access["status"] == "OK") {
            $response = array();

            $result = $db -> sql_query("SELECT `id`, `user_id`, `start_date` FROM `streams` WHERE `uuid` = '$stream_uuid' AND `is_excess` = '0' AND `is_deleted` = '0'", "", "array");
            $stream_id = $result[0]["id"];
            $hero_id = $result[0]["user_id"];
            $stream_start_date = intval($result[0]["start_date"]);

            if ($hero_id) {
                $result_chat = $db -> sql_query("SELECT `users_actions_log`.`id` AS `line_id`, `users_actions_id`, `users_actions_log`.`user_id` AS `user_id`, `stream_id`, `comment`, `users_actions_log`.`created_date` AS `created_date`, `users_actions_log`.`time_from_start` AS `time_from_start` FROM `users_actions_log` LEFT JOIN `users` ON `users_actions_log`.`user_id` = `users`.`id` WHERE `users_actions_log`.`hero_id` = '$hero_id' AND `users`.`is_blocked` = '0' AND `users`.`is_deleted` = '0' AND `users_actions_log`.`created_date` >= '$stream_start_date' ORDER BY `users_actions_log`.`created_date` ASC", "", "array");
                if (sizeof($result_chat) > 0) {
                    $data = array();

                    foreach ($result_chat as $value){
                        $comment_id = $value["line_id"];
                        $user_id = $value["user_id"];
                        $action = $value["users_actions_id"];
                        $current_stream_id = $value["stream_id"];
                        $timestamp = $value["created_date"];
                        $time_from_start = $value["time_from_start"];
                        $hero_name = $user -> profile_name($hero_id);
                        $comment_title = "";
                        $comment = "";
                        switch ($action) {
                            #Добавлен новый like
                            case "1" :
                                if ($current_stream_id == $stream_id) {
                                    $comment_title = "new_like";
                                    $comment = _LIKED_THIS;
                                }
                                break;
                            #Добавлен новый подписчик
                            case "3" :
                                $comment_title = "new_follower";
                                $comment = _FOLLOWS." ".$hero_name;
                                break;
                            #Добавлен новый комментарий
                            case "5" :
                                if ($current_stream_id == $stream_id) {
                                    $comment_title = "message";
                                    $comment = $value["comment"];
                                }
                                break;
                            #Владелец трансляции заблокировал зрителя в чате
                            case "6" :
                                $comment_title = "user_blocked";
                                $comment = _BLOCKED;
                                break;
                        }

                        if (!empty($comment)) {
                            $data["comment_id"] = $comment_id;
                            $data["user_id"] = $user_id;
                            $data["title"] = $comment_title;
                            $data["comment"] = $comment;
                            $data["timestamp"] = $timestamp;
                            $data["time_from_start"] = $time_from_start;
                            array_push($response, $data);
                        }
                    }

                    #Возвращает последние 5 записей чата
                    if (sizeof($response) > 5) {
                        $response = array_slice($response, -5);
                    }
                }
            }

            return $response;
        } else {
            return $client_request_access;
        }
    }

    # Метод создания массивов токенов устройств подписчиков(для ios, для android) для дальнейшей отправки push уведомлений о начале трансляции
    function push_notification_new_stream($stream_uuid = "") {
        global $db, $log_file, $ios_push_log_file, $android_push_log_file, $user;

        $time = date("H:i");

        $result = $db -> sql_query("SELECT `user_id`, `permissions` FROM `streams` WHERE `uuid` = '$stream_uuid' AND `is_excess` = '0' AND `is_blocked` = '0' AND `is_deleted` = '0'", "", "array");
        if (sizeof($result) > 0) {
            $user_id = $result[0]["user_id"];
            $user_name = $user -> profile_name($user_id);

            $permissions_to_watch_stream = unserialize($result[0]["permissions"]);
            if (sizeof($permissions_to_watch_stream) > 0){
                # Определение списка пользователей, которым доступен просмотр трансляции и отправка им уведомления о начале трансляции
                $watchers_id_array = $permissions_to_watch_stream;
            } else {
                # Определение подписчиков владельца трансляции и отправка им уведомления о начале трансляции
                $watchers_id_array = $user -> followers($user_id);
            }

            if (sizeof($watchers_id_array) > 0) {
                # Cоздание массивов токенов устройств для ios и android
                $ios_tokens = array();
                $android_tokens = array();

                foreach ($watchers_id_array as $watcher_id) {
                    $result_devices = $db -> sql_query("SELECT `device_token`, `operating_system` FROM `devices` LEFT JOIN `users_sessions_devices` ON `devices`.`id` = `users_sessions_devices`.`device_id` WHERE `devices`.`user_id` = '$watcher_id' AND `devices`.`device_token_is_correct` = '1' AND `devices`.`new_stream_notify_settings` = '1' AND `devices`.`is_blocked` = '0' AND `devices`.`is_deleted` = '0' AND `users_sessions_devices`.`end_date` = '0'", "", "array");

                    if (sizeof($result_devices) > 0) {
                        # Если у пользователя несколько устройств
                        foreach ($result_devices as $v) {
                            $device_token = $v["device_token"];
                            $operating_system = $v["operating_system"];
                            # Определение операционной системы устройства польователя и доступности токена устройства
                            if (preg_match("/iOS/i",$operating_system)){
                                $ios_tokens[] = $device_token;
                            } else if (preg_match("/Android/i",$operating_system)) {
                                $android_tokens[] = $device_token;
                            }
                        }

                    } else {
                        error_log("[$time] push_notification_new_stream: watcher_id - $watcher_id: не найдены устройства пользователя\n", 3, $log_file);
                    }
                }

                if (sizeof($ios_tokens) > 0){
                    $text = $user_name . " в прямом эфире";
                    $custom_properties = array(
                        "act" => "1",
                        "stream_uuid" => $stream_uuid,
                        "user_id" => $user_id
                    );
                    try {
                        $this -> ios_send_push($ios_tokens, $text, $custom_properties);
                    } catch (Exception $e) {
                        error_log("[$time] push_notification_new_stream: EXCEPTION stream_uuid - $stream_uuid " . $e->getMessage(). "\n", 3, $ios_push_log_file);
                    } finally {
                        $ios_tokens_for_log = implode(', ', $ios_tokens);
                        error_log("[$time] push_notification_new_stream: stream_uuid - $stream_uuid, ios_tokens: $ios_tokens_for_log\n", 3, $ios_push_log_file);
                    }
                }

                if (sizeof($android_tokens) > 0){
                    $text = array(
                        "act" => "1",
                        "title" => $user_name . " в прямом эфире",
                        "stream_uuid" => $stream_uuid
                    );
                    try {
                        $this -> android_send_push($android_tokens, $text);
                    } catch (Exception $e) {
                        error_log("[$time] push_notification_new_stream: EXCEPTION stream_uuid - $stream_uuid " . $e->getMessage(). "\n", 3, $ios_push_log_file);
                    } finally {
                        $android_tokens_for_log = implode(', ', $android_tokens);
                        error_log("[$time] push_notification_new_stream: stream_uuid - $stream_uuid android_tokens: $android_tokens_for_log\n", 3, $android_push_log_file);
                    }
                }
            }
        }
    }

    # Метод создания массивов токенов устройств подписчиков(для ios, для android) для дальнейшей отправки push уведомления пользователю о новом подписчике
    function push_notification_new_follower($user_id = 0, $follower_id = 0) {
        global $db, $log_file, $ios_push_log_file, $android_push_log_file, $user;

        $time = date("H:i");
        if (user($user_id) AND user($follower_id)) {

            $result_devices = $db -> sql_query("SELECT * FROM `devices` LEFT JOIN `users_sessions_devices` ON `devices`.`id` = `users_sessions_devices`.`device_id` WHERE `devices`.`user_id` = '$user_id' AND `devices`.`device_token_is_correct` = '1' AND `devices`.`new_stream_notify_settings` = '1' AND `devices`.`is_blocked` = '0' AND `devices`.`is_deleted` = '0' AND `users_sessions_devices`.`end_date` = '0'", "", "array");

            if (sizeof($result_devices) > 0) {

                $ios_tokens = array();
                $android_tokens = array();
                $follower_name = $user -> profile_name($follower_id);
                $text = $follower_name." подписался на Вас";

                #Если у пользователя несколько устройств
                foreach ($result_devices as $v) {
                    $device_token = $v["device_token"];
                    $operating_system = $v["operating_system"];
                    #Определение операционной системы устройства польователя и доступности токена устройства
                    if (preg_match("/iOS/i",$operating_system)){
                        $ios_tokens[] = $device_token;
                    } else if (preg_match("/Android/i",$operating_system)) {
                        $android_tokens[] = $device_token;
                    }

//TODO отправка с учетом языковых настроек устройства
//                    $lang = $v["lang"];
//                    if (preg_match("/en/i", $lang)) {
//                        $text = $follower_name." subscribed to you";
//                    } else {
//                        $text = $follower_name." подписался на Вас";
//                    }
                }

                if (sizeof($ios_tokens) > 0){
                    $custom_properties = array(
                        "act" => "0",
                        "follower_id" => $follower_id
                    );
                    try {
                        $this -> ios_send_push($ios_tokens, $text, $custom_properties);
                    } catch (Exception $e) {
                        error_log("[$time] push_notification_new_follower: EXCEPTION user_id - $user_id, follower_id - $follower_id " . $e->getMessage(). "\n", 3, $ios_push_log_file);
                    } finally {
                        $ios_tokens_for_log = implode(', ', $ios_tokens);
                        error_log("[$time] push_notification_new_follower: user_id - $user_id, follower_id - $follower_id ios_tokens: $ios_tokens_for_log\n", 3, $ios_push_log_file);
                    }
                }

                if (sizeof($android_tokens) > 0){
                    $text = array(
                        "act" => "0",
                        "title" => $text,
                        "follower_id" => $follower_id
                    );

                    $this -> android_send_push($android_tokens, $text);
                    $android_tokens_for_log = implode(', ', $android_tokens);
                    error_log("[$time] push_notification_new_follower: user_id($user_id), follower_id($follower_id) android_tokens: $android_tokens_for_log\n", 3, $android_push_log_file);
                }

            } else {
                error_log("[$time] push_notification_new_follower: user_id($user_id) не найдены устройства пользователя\n", 3, $log_file);
            }
        } else {
            error_log("[$time] push_notification_new_follower: не найден user_id($user_id) или follower_id($follower_id)\n", 3, $log_file);
        }
    }

    # Отправка push уведомлений на ios устройства подписчиков
    function ios_send_push($ios_tokens_array, $text, $custom_properties) {
        global $db, $project_options, $ios_push_log_file;

        $time = date("H:i");

        require_once "ApnsPHP/Autoload.php";
        require_once "ApnsPHP/Message.php";

        $push = new ApnsPHP_Push(ApnsPHP_Abstract::ENVIRONMENT_PRODUCTION, $project_options["apn"]["cert"]);
        $push -> setProviderCertificatePassphrase($project_options["apn"]["passphrase"]);
        $push -> setRootCertificationAuthority($project_options["apn"]["root_cert"]);

        $message = new ApnsPHP_Message();
        $listTokens = array();
        foreach ($ios_tokens_array as $token) {
            try {
                $message -> addRecipient($token);
                $listTokens[] = $token;
            } catch (Exception $e) {
                $db -> sql_query("UPDATE `devices` SET `device_token_is_correct` = '0' WHERE `device_token` = '$token'");
                error_log("[$time] apns_push: EXCEPTION invalid token $token checked\n", 3, $ios_push_log_file);
            }
        }

        $push -> connect();
        $message -> setBadge(2);
        $message -> setSound();
        $message -> setText($text);
        $message -> setContentAvailable();
        
        foreach ($custom_properties as $property_name => $property_value) {
            $message -> setCustomProperty($property_name, $property_value);
        }
        $push -> add($message);
        $push -> send();
        $push -> disconnect();

        $a_error_queue = $push -> getErrors();
        if (!empty($a_error_queue)) {
            error_log("[$time] apns_push: oшибка отправки ios  -  " . print_r($a_error_queue, true) ."\n", 3, $ios_push_log_file);
            if (is_array($a_error_queue)) {
                foreach($a_error_queue as $error) {
                    if (isset($error["ERRORS"]) && is_array($error["ERRORS"])) {
                        foreach ($error["ERRORS"] as $m) {
                            if (isset($m["statusMessage"]) && $m["statusMessage"] == "Invalid token") {
                                $array_ID = $m["identifier"] - 1;
                                $device_token = $listTokens[$array_ID];
                                if (isset($listTokens[$array_ID])) {
                                    $db -> sql_query("UPDATE `devices` SET `device_token_is_correct` = '0' WHERE `device_token` = '$device_token'");
                                    error_log("[$time] apns_push: invalid token $device_token checked\n", 3, $ios_push_log_file);
                                }
                            }

                        }
                    }
                }
            }
        } else {
            error_log("[$time] apns_push: no errors\n", 3, $ios_push_log_file);
        }
    }

    # Проверка токенов на доступность, метод вызывается cron файлом (/cron/apns_feedback.php)
    function apns_feedback() {
        global $db, $project_options, $ios_push_log_file;
        require_once "ApnsPHP/Autoload.php";

        $time = date("H:i");

        $feedback = new ApnsPHP_Feedback(
            ApnsPHP_Abstract::ENVIRONMENT_PRODUCTION, $project_options["apn"]["cert"]
        );
        $feedback -> setProviderCertificatePassphrase($project_options["apn"]["passphrase"]);
        $feedback -> setRootCertificationAuthority($project_options["apn"]["root_cert"]);

        $feedback -> connect();

        $aDeviceTokens = $feedback -> receive();
        if (!empty($aDeviceTokens)) {
            foreach ($aDeviceTokens as $DeviceToken) {
                /**
                 * формат
                 * [timestamp] => 1406040206
                 * [tokenLength] => 32
                 * [deviceToken] => 738d005a11bca268e2f1bffbfed88a456e261020b9277883cde14d9c8f47cde0
                 */

                #Отмечаем ошибочный токен, чтобы не учитывать его в дальнейшем
                $device_token = $DeviceToken["deviceToken"];
                $db -> sql_query("UPDATE `devices` SET `device_token_is_correct` = '0' WHERE `device_token` = '$device_token'");

                error_log("[$time] apns_feedback: invalid token $device_token deleted\n", 3, $ios_push_log_file);
            }
        } else {
            error_log("[$time] apns_feedback: all tokens are valid\n", 3, $ios_push_log_file);
        }

        $feedback -> disconnect();
    }

    # Отправка push уведомлений о начале трансляции на android устройства подписчиков
    function android_send_push($android_tokens, $text) {
        global $db, $project_options, $android_push_log_file;

        $time = date("H:i");

        require_once "CodeMonkeysRu/GCM/Exception.php";
        require_once "CodeMonkeysRu/GCM/Message.php";
        require_once "CodeMonkeysRu/GCM/Response.php";
        require_once "CodeMonkeysRu/GCM/Sender.php";

        $sender = new CodeMonkeysRu\GCM\Sender($project_options["gsm"]["server_api_key"]);
        $message = new CodeMonkeysRu\GCM\Message($android_tokens, array("message" => $text));

        try {
            $response = $sender -> send($message);

            if ($response -> getFailureCount() > 0) {
                $a_invalid_device_tokens = $response -> getInvalidRegistrationIds();
                foreach($a_invalid_device_tokens as $device_token) {
                    $db -> sql_query("UPDATE `devices` SET `device_token_is_correct` = '0' WHERE `device_token` = '$device_token'");
                    error_log("[$time] android_push: invalid token $device_token deleted\n", 3, $android_push_log_file);
                }
            }
            if ($response -> getSuccessCount()) {
                error_log("[$time] android_push: отправлено сообщений на " . $response -> getSuccessCount() ." устройств\n", 3, $android_push_log_file);
            }
        } catch (CodeMonkeysRu\GCM\Exception $e) {

            switch ($e -> getCode()) {
                case CodeMonkeysRu\GCM\Exception::ILLEGAL_API_KEY:
                case CodeMonkeysRu\GCM\Exception::AUTHENTICATION_ERROR:
                case CodeMonkeysRu\GCM\Exception::MALFORMED_REQUEST:
                case CodeMonkeysRu\GCM\Exception::UNKNOWN_ERROR:
                case CodeMonkeysRu\GCM\Exception::MALFORMED_RESPONSE:
                    error_log("[$time] android_push: oшибка отправления device_token: " . $e -> getCode() ." " . $e -> getMessage() . "\n", 3, $android_push_log_file);
                    break;
            }
        }
    }

    # Метод редактирования трансляции (для веб приложения)
    function stream_edit($data, $user_id = 0) {
        global $db, $log_file, $user, $project_options;
        $response = array();

        $ip = $user -> get_client_ip();
        $time = date("H:i");

        $stream_uuid = prepair_str($data["stream_uuid"]);

        $result = $db -> sql_query("SELECT `id` FROM `streams` WHERE `uuid` = '$stream_uuid' AND `user_id` = '$user_id' AND `is_excess` = '0' AND `is_blocked` = '0' AND `is_deleted` = '0'", "", "array");
        if (sizeof($result) > 0) {
            $stream_id = $result[0]["id"];

            $sql_query = array();
            $users_id_array = array();
            $permissions = "";
            if ($data["stream_permissions_state"] == "private") {
                if (isset($data["permissions"])){
                    if (is_array($data["permissions"])) {
                        foreach($data["permissions"] as $value){
                            $users_id_array[] = intval($value);
                        }
                    } else {
                        $value = $data["permissions"];
                        $users_id_array[] = intval($value);
                    }
                }

                if (sizeof($users_id_array) > 0){
                    $permissions = serialize($users_id_array);
                }
            }
            
            $sql_query[] = "permissions = '$permissions'";

            if (isset($data["chat_permissions"]) AND $data["chat_permissions"] == "on") {
                $sql_query[] = "chat_permissions = '1'";
            } else {
                $sql_query[] = "chat_permissions = '0'";
            }

            if (isset($data["on_map"]) AND $data["on_map"] == "on") {
                $sql_query[] = "on_map = '1'";
            } else {
                $sql_query[] = "on_map = '0'";
            }

            if (isset($data["stream_name"]) AND !empty($data["stream_name"])) {
                $stream_name = prepair_str($data["stream_name"]);
                $sql_query[] = "name = '$stream_name'";

                $data_tags = get_hashtags_from_string($stream_name);

                $result_stream_tags = $db -> sql_query("SELECT `streams_tags_data`.`name` AS `tag_name` FROM `streams_tags` LEFT JOIN `streams_tags_data` ON `streams_tags_data`.`id` = `streams_tags`.`stream_tag_id` WHERE `streams_tags`.`stream_id` = '$stream_id' AND `streams_tags`.`is_deleted` = '0'", "", "array");
                #Создание массива тегов потока
                $stream_tags  = array();
                foreach ($result_stream_tags as $value){
                    array_push($stream_tags, $value["tag_name"]);
                }

                #Создание массива всех тегов проекта (поток)
                $result_streams_tags = $db -> sql_query("SELECT * FROM `streams_tags_data`", "", "array");
                #Создание массива всех тегов
                $existing_streams_tags = array();
                foreach ($result_streams_tags as $value) {
                    array_push($existing_streams_tags, $value["name"]);
                }

                foreach ($data_tags as $value){
                    if ($value != "") {
                        if (!in_array($value, $stream_tags)){#Проверка существования данного тега в аккаунте

                            if (in_array($value, $existing_streams_tags)){ #Получение id существующего тега
                                $result_tag = $db -> sql_query("SELECT `id` FROM `streams_tags_data` WHERE `name` = '$value'", "", "array");
                                $tag_id = $result_tag[0]["id"];
                            } else { #Создание нового тега
                                $result_add_tag = $db -> sql_query("INSERT INTO `streams_tags_data` (`id`, `name`, `is_disabled`) VALUES (NULL, '$value', '0')");
                                $tag_id = $db -> sql_nextid($result_add_tag);
                            }
                            #Ситуация: тег был добавлен, затем удален и сейчас добавляется снова
                            $result = $db -> sql_query("SELECT * FROM streams_tags WHERE stream_tag_id = '$tag_id' AND stream_id = '$stream_id' AND is_deleted = '1'", "", "array");
                            if ((sizeof($result) > 0) AND !empty($result[0])) {
                                $db -> sql_query("UPDATE streams_tags SET is_deleted = '0' WHERE stream_id = '$stream_id' AND stream_tag_id = '$tag_id' AND is_deleted = '1'");
                            } else {
                                $db -> sql_query("INSERT INTO streams_tags (id, user_id, stream_id, stream_tag_id, is_deleted) VALUES (NULL, '$user_id', '$stream_id', '$tag_id', '0')");
                            }
                        }
                    }
                }

                #Удаленные теги помечаются is_deleted = '1'
                $result_deleted_tags = array_diff($stream_tags, $data_tags);
                if (sizeof($result_deleted_tags) > 0){
                    foreach ($result_deleted_tags as $value){
                        $result_id = $db -> sql_query("SELECT id FROM streams_tags_data WHERE name = '$value'", "", "array");
                        if (sizeof($result_id) > 0 AND !empty($result_id[0])) {
                            $id = $result_id[0]["id"];
                            $db -> sql_query("UPDATE `streams_tags` SET `is_deleted` = '1' WHERE `stream_tag_id` = '$id' AND `stream_id` = '$stream_id'");
                        }
                    }
                }
            }

            if (count($sql_query) > 0 ){
                $sql = implode(", ", $sql_query);

                if ($db -> sql_query("UPDATE `streams` SET $sql WHERE `uuid` = '$stream_uuid' AND `is_deleted` = '0' LIMIT 1")){
                    $this -> etag_stream_update($stream_uuid); # Обновление etag трансляции

                    if (isset($data["categories_array"]) AND is_array($data["categories_array"])) {
                        $categories_array = $data["categories_array"];
                        # В случае, если получен пустой массив все предвдущие связи удаляются
                        $this -> link_stream_categories($stream_id, $categories_array);
                    }

                    $response["status"] = "OK";
                    $response["sql"] = $sql;
                } else {
                    $response["status"] = "ERROR";
                    error_log("[$time $ip] stream_edit: sql_error uuid = $stream_uuid\n", 3, $log_file);
                }
            }

        } else {
            $response["status"] = "NOT-FOUND";
            error_log("[$time $ip] stream_edit: не найден uuid = $stream_uuid\n", 3, $log_file);
        }

        return $response;
    }

    # Метод редактирования трансляции (для мобильных приложений)
    function stream_edit_app($stream_uuid = "", $data, $current_request_type){
        global $db, $log_file, $user;

        $client_request_access = client_request_access_status($current_request_type);
        if ($client_request_access["status"] == "OK") {
            $response = array();

            $ip = $user -> get_client_ip();
            $time = date("H:i");

            if (isset($client_request_access["user_id"])) {
                $user_id = $client_request_access["user_id"];
            } else {
                if (isset($data["user_id"]) AND !empty($data["user_id"])) {
                    $user_id = prepair_str($data["user_id"]);
                }
            }

            $result = $db->sql_query("SELECT * FROM `streams` WHERE uuid = '$stream_uuid' AND `user_id` = '$user_id' AND `is_excess` = '0' AND `is_deleted` = '0'", "", "array");
            if (sizeof($result) > 0 AND !empty($result[0])) {
                $stream_id = $result[0]["id"];

                $sql_query = array();
                $warning_array = array();

                if (isset($data["permissions"])) {
                    $permissions = array();
                    if (sizeof($data["permissions"]) > 0){
                        $permissions = serialize($data["permissions"]);
                    }
                    $sql_query[] = "permissions = '$permissions'";
                }

                if (isset($data["on_map"]) AND !empty($data["on_map"])) {
                    $on_map = prepair_str($data["on_map"]);
                    $sql_query[] = "on_map = '$on_map'";
                }

                if (isset($data["stream_name"]) AND !empty($data["stream_name"])) {
                    $stream_name = prepair_str($data["stream_name"]);
                    $sql_query[] = "name = '$stream_name'";

                    $data_tags = get_hashtags_from_string($stream_name);

                    $result_stream_tags = $db->sql_query("SELECT `streams_tags_data`.`name` AS `tag_name` FROM `streams_tags` LEFT JOIN `streams_tags_data` ON `streams_tags_data`.`id` = `streams_tags`.`stream_tag_id` WHERE `streams_tags`.`stream_id` = '$stream_id' AND `streams_tags`.`is_deleted` = '0'", "", "array");
                    #Создание массива тегов потока
                    $stream_tags = array();
                    foreach ($result_stream_tags as $value) {
                        array_push($stream_tags, $value["tag_name"]);
                    }

                    #Создание массива всех тегов проекта (поток)
                    $result_streams_tags = $db->sql_query("SELECT * FROM `streams_tags_data`", "", "array");
                    #Создание массива всех тегов
                    $existing_streams_tags = array();
                    foreach ($result_streams_tags as $value) {
                        array_push($existing_streams_tags, $value["name"]);
                    }

                    foreach ($data_tags as $value) {
                        if ($value != "") {
                            if (!in_array($value, $stream_tags)) {#Проверка существования данного тега в аккаунте

                                if (in_array($value, $existing_streams_tags)) { #Получение id существующего тега
                                    $result_tag = $db->sql_query("SELECT `id` FROM `streams_tags_data` WHERE `name` = '$value'", "", "array");
                                    $tag_id = $result_tag[0]["id"];
                                } else { #Создание нового тега
                                    $result_add_tag = $db -> sql_query("INSERT INTO `streams_tags_data` (`id`, `name`, `is_disabled`) VALUES (NULL, '$value', '0')");
                                    $tag_id = $db -> sql_nextid($result_add_tag);
                                }
                                #Ситуация: тег был добавлен, затем удален и сейчас добавляется снова
                                $result = $db -> sql_query("SELECT * FROM `streams_tags` WHERE `stream_tag_id` = '$tag_id' AND `stream_id` = '$stream_id' AND `is_deleted` = '1'", "", "array");
                                if ((sizeof($result) > 0) AND !empty($result[0])) {
                                    $db -> sql_query("UPDATE `streams_tags` SET `is_deleted` = '0' WHERE `stream_id` = '$stream_id' AND `stream_tag_id` = '$tag_id' AND `is_deleted` = '1'");
                                } else {
                                    $db -> sql_query("INSERT INTO `streams_tags` (`id`, `user_id`, `stream_id`, `stream_tag_id`, `is_deleted`) VALUES (NULL, '$user_id', '$stream_id', '$tag_id', '0')");
                                }
                            }
                        }
                    }

                    #Удаленные теги помечаются is_deleted = '1'
                    $result_deleted_tags = array_diff($stream_tags, $data_tags);
                    if (sizeof($result_deleted_tags) > 0) {
                        foreach ($result_deleted_tags as $value) {
                            $result_id = $db -> sql_query("SELECT `id` FROM `streams_tags_data` WHERE `name` = '$value'", "", "array");
                            if (sizeof($result_id) > 0 AND !empty($result_id[0])) {
                                $id = $result_id[0]["id"];
                                $db -> sql_query("UPDATE `streams_tags` SET `is_deleted` = '1' WHERE `stream_tag_id` = '$id' AND `stream_id` = '$stream_id'");
                            }
                        }
                    }
                }

                if (count($warning_array) > 0) {
                    $warning_params = implode(", ", $warning_array);
                    $response["status"] = "ERROR";
                    $response["message"] = "Требуется введение уникальных параметров: $warning_params";
                } else {
                    if (count($sql_query) > 0) {
                        $sql = implode(", ", $sql_query);

                        if ($db -> sql_query("UPDATE `streams` SET $sql WHERE `uuid` = '$stream_uuid' AND `is_deleted` = '0' LIMIT 1")) {
                            $this -> etag_stream_update($stream_uuid); # Обновление etag трансляции

                            if (isset($data["categories_array"]) AND is_array($data["categories_array"])) {
                                $categories_array = $data["categories_array"];
                                # В случае, если получен пустой массив все предвдущие связи удаляются
                                $this -> link_stream_categories($stream_id, $categories_array);
                            }

                            $response["status"] = "OK";
                        } else {
                            $response["status"] = "ERROR";
                            $response["message"] = "Ошибка выполнения запроса";

                            error_log("[$time $ip] stream_edit_app: sql_error uuid = $stream_uuid\n", 3, $log_file);
                        }
                    }
                }

            } else {
                $response["status"] = "NOT-FOUND";
                error_log("[$time $ip] stream_edit_app: не найден uuid = $stream_uuid\n", 3, $log_file);
            }

            return $response;
        } else {
            return $client_request_access;
        }
    }

    # Метод завершения трансляции (для мобильных приложений)
    function stream_cancel($stream_uuid = "", $current_request_type) {
        global $db, $log_file, $user;

        $client_request_access = client_request_access_status($current_request_type);

        if ($client_request_access["status"] == "OK") {
            $response = array();

            $current_date = time();
            $ip = $user -> get_client_ip();
            $time = date("H:i");

            $result = $db -> sql_query("SELECT `id`, `user_id`, `start_date` FROM `streams` WHERE `uuid` = '$stream_uuid' AND `is_deleted` = '0'", "", "array");
            if (sizeof($result) > 0) {
                $stream_id = $result[0]["id"];
                $user_id = $result[0]["user_id"];
                $start_date_float = $result[0]["start_date"];
                $current_date_float = microtime_float();
                $stream_duration_float = $current_date_float - $start_date_float;

                if ($db -> sql_query("UPDATE `streams` SET `end_date` = '$current_date_float', `duration` = '$stream_duration_float', `status` = '0' WHERE `uuid` = '$stream_uuid'")) {
                    $db -> sql_query("INSERT INTO `users_actions_log` (`id`, `hero_id`, `stream_id`, `user_id`, `users_actions_id`, `comment`, `created_date`) VALUES (NULL, '$user_id', '$stream_id', '$user_id', '8', '', '$current_date')");
                    $this -> etag_stream_update($stream_uuid); # Обновление etag трансляции
                    $user -> etag_user_update($user_id); # Обновление etag пользователя
                    $response["status"] = "OK";
                    error_log("[$time $ip] stream_cancel: OK stream_uuid - $stream_uuid\n", 3, $log_file);
                } else {
                    $response["status"] = "ERROR";
                    $response["message"] = "Ошибка выполнения запроса";

                    error_log("[$time $ip] stream_cancel: SQL-ERROR stream_uuid - $stream_uuid\n", 3, $log_file);
                }
            } else {
                $response["status"] = "NOT-FOUND";
                error_log("[$time $ip] stream_cancel: NOT-FOUND не найден stream_uuid - $stream_uuid\n", 3, $log_file);
            }

            return $response;
        } else {
            return $client_request_access;
        }
    }

    # Удаление трансляции
    function stream_delete($stream_uuid = ""){
        global $db, $log_file, $stream_delete_from_media_server_url, $user;

        $response = array();

        $ip = $user -> get_client_ip();
        $time = date("H:i");
        $current_date = time();

        $result = $db -> sql_query("SELECT `storage_server`, `user_id` FROM `streams` WHERE `uuid` = '$stream_uuid' AND `is_deleted` = '0'", "", "array");
        $storage_server = $result[0]["storage_server"];
        $user_id = $result[0]["user_id"];
        if (sizeof($result) > 0) {
            if ($db -> sql_query("UPDATE `streams` SET `deleted_date` = '$current_date', `is_deleted` = '1', `rating` = '0' WHERE `uuid` = '$stream_uuid' AND `is_deleted` = '0'")) {
                $user -> etag_user_update($user_id); # Обновление etag владельца трансляции
                $response["status"] = "OK";
                error_log("[$time $ip] stream_delete: OK stream_uuid - $stream_uuid\n", 3, $log_file);
            } else {
                $response["status"] = "ERROR";
                $response["message"] = "Ошибка выполнения запроса";

                error_log("[$time $ip] stream_delete: SQL-ERROR stream_uuid - $stream_uuid\n", 3, $log_file);
            }
        } else {
            $response["status"] = "NOT-FOUND";
            error_log("[$time $ip] stream_delete: NOT-FOUND не найден stream_uuid - $stream_uuid\n", 3, $log_file);
        }

        $stream_delete_query_url = "http://$storage_server:8082".$stream_delete_from_media_server_url.$stream_uuid;
        $stream_delete_response = file_get_contents($stream_delete_query_url);
        error_log("[$time $ip] stream_delete_from_media_server: $stream_delete_response stream_uuid - $stream_uuid\n", 3, $log_file);
        return $response;
    }

    function stream_moderate_result($data, $user_id = "") {
        global $db;
        $array["html"] = "";
        $array["result"] = "";
        $stream_id 	= prepair_str($data["stream_id"]);
        $result_notify = $db -> sql_query("SELECT * FROM `streams_notify_log` WHERE `stream_id` = '$stream_id'", "", "array");
        $result_block = $db -> sql_query("SELECT * FROM `streams` WHERE `id` = '$stream_id' AND `is_blocked` = '1'", "", "array");
        if (sizeof($result_notify) > 0 AND !empty($result_notify[0])) {
            if (sizeof($result_block) > 0 AND !empty($result_block[0])) {
                $array["html"] = "<div class=\"alert alert-warning\">" . _STREAM_BLOCKED . "</div>";
                $array["result"] = "block";
            } else {
                $array["html"] = "<div class=\"alert alert-warning\"><button class=\"close\" data-dismiss=\"alert\">×</button>" . _STREAM_NOTIFICATION . "</div>";
                $array["result"] = "notify";
            }
        }
        return $array;
    }    

    function streams_search($query = "") {
        global $db;
        $response = array();
        if (strlen($query) > 2) {
            $result_streams = $db -> sql_query("SELECT `uuid` FROM `streams` WHERE (`name` LIKE '%".$query."%' AND `is_excess` = '0' AND `is_blocked` = '0' AND `is_deleted` = '0') GROUP BY `id`", "", "array");
            if (sizeof($result_streams) > 0 AND $result_streams[0] != "") {
                foreach ($result_streams as $value) {
                    $response[] = $value["uuid"];
                }
            }
        }
        return $response;
    }

    function streams_search_v2($query = "") {
        global $db;
        $response = array();
        if (strlen($query) > 2) {
            $result_streams = $db -> sql_query("SELECT `uuid`, `etag_stream` FROM `streams` WHERE (`name` LIKE '%".$query."%' AND `is_excess` = '0' AND `is_blocked` = '0' AND `is_deleted` = '0') GROUP BY `id`", "", "array");
            if (sizeof($result_streams) > 0 AND $result_streams[0] != "") {
                foreach ($result_streams as $value) {
                    $s[0] = $value["uuid"];
                    $s[1] = $value["etag_stream"];
                    $response[] = $s;
                }
            }
        }
        return $response;
    }
    
    # Вовзвращает список uuid популярных трансляций
    function get_top_streams($streams_count = 25) {
        global $db;

        $response = array();
        $rated_streams = $db -> sql_query("SELECT `uuid`, `streams`.`rating` AS `stream_rating` FROM `streams` LEFT JOIN `users` ON `streams`.`user_id` = `users`.`id`  WHERE `streams`.`is_excess` = '0' AND `streams`.`is_blocked` = '0' AND `streams`.`is_deleted` = '0' AND `users`.`is_blocked` = '0' AND `users`.`is_deleted` = '0' ORDER BY `stream_rating` DESC LIMIT $streams_count", "", "array");

        foreach ($rated_streams as $value){
            $uuid = $value["uuid"];
            $response[] = $uuid;
        }

        return $response;
    }

    # Возвращает список uuid популярных трансляций (версия, возвращающающая etag для каждой трансляции)
    function get_top_streams_v2($streams_count = 25) {
        global $db;

        $response = array();
        $rated_streams = $db -> sql_query("SELECT `uuid`, `etag_stream`, `streams`.`rating` AS `stream_rating` FROM `streams` LEFT JOIN `users` ON `streams`.`user_id` = `users`.`id`  WHERE `streams`.`is_excess` = '0' AND `streams`.`is_blocked` = '0' AND `streams`.`is_deleted` = '0' AND `users`.`is_blocked` = '0' AND `users`.`is_deleted` = '0' ORDER BY `stream_rating` DESC LIMIT $streams_count", "", "array");

        foreach ($rated_streams as $value){
            $s[0] = $value["uuid"];
            $s[1] = $value["etag_stream"];
            $response[] = $s;
        }
        return $response;
    }

    # Вовзвращает список uuid трансляций официальных источников
    function get_official_streams($streams_count = 25) {
        global $db;

        $response = array();
        $rated_streams = $db -> sql_query("SELECT `uuid`, `streams`.`rating` AS `stream_rating` FROM `streams` LEFT JOIN `users` ON `streams`.`user_id` = `users`.`id`  WHERE `streams`.`is_excess` = '0' AND `streams`.`is_blocked` = '0' AND `streams`.`is_deleted` = '0' AND `users`.`is_check_official` = '1' AND `users`.`is_blocked` = '0' AND `users`.`is_deleted` = '0' ORDER BY `stream_rating` DESC LIMIT $streams_count", "", "array");

        foreach ($rated_streams as $value){
            $uuid = $value["uuid"];
            $response[] = $uuid;
        }

        return $response;
    }

    # Вовзвращает список uuid трансляций официальных источников (версия, возвращающающая etag для каждой трансляции)
    function get_official_streams_v2($streams_count = 25) {
        global $db;

        $response = array();
        $rated_streams = $db -> sql_query("SELECT `uuid`, `etag_stream`, `streams`.`rating` AS `stream_rating` FROM `streams` LEFT JOIN `users` ON `streams`.`user_id` = `users`.`id`  WHERE `streams`.`is_excess` = '0' AND `streams`.`is_blocked` = '0' AND `streams`.`is_deleted` = '0' AND `users`.`is_check_official` = '1' AND `users`.`is_blocked` = '0' AND `users`.`is_deleted` = '0' ORDER BY `stream_rating` DESC LIMIT $streams_count", "", "array");

        foreach ($rated_streams as $value){
            $s[0] = $value["uuid"];
            $s[1] = $value["etag_stream"];
            $response[] = $s;
        }

        return $response;
    }

    # Метод возвращает html код описания трансляции
    function stream_preview_html($stream_uuid = "", $view = "default"){
        global $db, $project_optins, $stream, $snapshot_query, $user;

        $current_date = time();

        $stream_preview = "";
        $result_stream = $db -> sql_query("SELECT `id`, `user_id`, `start_date`, `end_date`, `duration` FROM `streams` WHERE `uuid` = '$stream_uuid' AND `is_excess` = '0' AND `is_blocked` = '0' AND `is_deleted` = '0'", "", "array");
        if (sizeof($result_stream) > 0) {
            $stream_id = $result_stream[0]["id"];

            $result_clients = $db -> sql_query("SELECT `id` FROM `streams_clients` WHERE `stream_id` = '$stream_id' AND `disconnected_date` = '0'", "", "array");

            $client_count_data = sizeof($result_clients);

            $hero_id = $result_stream[0]["user_id"];
            $stream_start_date = intval($result_stream[0]["start_date"]);
            $stream_name = $stream -> stream_name($stream_uuid);
            $profile_image = $user -> profile_image_html($hero_id);
            $profile_name = $user -> profile_name($hero_id);
            $stream_statistics = stream_statistics($stream_uuid);
            $thumb = $snapshot_query . $stream_uuid;

            $stream_live_icon = "";
            $time_difference = $current_date - $stream_start_date;
            $stream_date_info = refine_data($time_difference) . " " . _AGO;
            $stream_duration = gmdate("H:i:s", $result_stream[0]["duration"]);
            $stream_duration_icon = "<div class=\"stream_duration\">$stream_duration</div>";

            if ($result_stream[0]["end_date"] == 0) {
                $stream_duration_icon = "";
                $stream_live_icon = "<div class=\"stream_live_icon\">Live</div>";
            }

            if ($view == "default") {
                $stream_preview = "
					<div class=\"col-xs-10 col-sm-5 col-md-2 stream_preview\" data-stream=\"$stream_uuid\">
						<div class=\"profile_info\" data-profile-id=\"$hero_id\">$profile_image<div class=\"profile_name\"><span>$profile_name</span></div></div>
						<a href=\"" . $project_optins["service_url_inner"] . "index.php?route=page_play&user=$hero_id&uuid=$stream_uuid\" title=\"$stream_name\">
							<div class=\"thumbnail\" style=\"background: #D6D6D6 url('$thumb') center center no-repeat; background-size: cover;)\">
								<div class=\"client_count\">
									<div class=\"client_icon\"><i class=\"fa fa-eye fa-lg\"></i></div><div class=\"client_count_data\"><span>$client_count_data</span> " . _WATCH . "</div>
								</div>
								$stream_live_icon
								<i class=\"icon-play\"></i>
								$stream_duration_icon
							</div>
							<p class=\"stream_name\">$stream_name</p>
							<p class=\"dull_text\">$stream_date_info</p>
							<p class=\"stream_statistics\">$stream_statistics</p>
						</a>
					</div>";
            } else if ($view == "top_official") {
                $stream_preview = "
					<div class=\"col-xs-10 col-sm-5 col-md-2-1 stream_preview stream_preview-top\" data-stream=\"$stream_uuid\">
						<div class=\"profile_info\" data-profile-id=\"$hero_id\">$profile_image<div class=\"profile_name\"><span>$profile_name</span></div></div>
						<a href=\"" . $project_optins["service_url_inner"] . "index.php?route=page_play&user=$hero_id&uuid=$stream_uuid\" title=\"$stream_name\">
							<div class=\"thumbnail\" style=\"background: #D6D6D6 url('$thumb') center center no-repeat; background-size: cover;)\">
								<div class=\"client_count\">
									<div class=\"client_icon\"><i class=\"fa fa-eye fa-lg\"></i></div><div class=\"client_count_data\"><span>$client_count_data</span> " . _WATCH . "</div>
								</div>
								$stream_live_icon
								<i class=\"icon-play\"></i>
								$stream_duration_icon
							</div>
							<p class=\"stream_name\">$stream_name</p>
							<p class=\"dull_text\">$stream_date_info</p>
							<p class=\"stream_statistics\">$stream_statistics</p>
						</a>
					</div>";
            } else if ($view == "slide") {
                $stream_preview =  "
					<div class=\"stream_preview stream_preview-top\" data-stream=\"$stream_uuid\">
						<div class=\"profile_info\" data-profile-id=\"$hero_id\">$profile_image<div class=\"profile_name\"><span>$profile_name</span></div></div>
						<a href=\"" . $project_optins["service_url_inner"] . "index.php?route=page_play&user=$hero_id&uuid=$stream_uuid\" title=\"$stream_name\">
							<div class=\"thumbnail\" style=\"background: #D6D6D6 url('$thumb') center center no-repeat; background-size: cover;)\">
								<div class=\"client_count\">
									<div class=\"client_icon\"><i class=\"fa fa-eye fa-lg\"></i></div><div class=\"client_count_data\"><span>$client_count_data</span> " . _WATCH . "</div>
								</div>
								$stream_live_icon
								<i class=\"icon-play\"></i>
								$stream_duration_icon
							</div>
							<p class=\"stream_name\">$stream_name</p>
							<p class=\"dull_text\">$stream_date_info</p>
							<p class=\"stream_statistics\">$stream_statistics</p>
						</a>
					</div>";
            }
        }

        return $stream_preview;
    }

    # Метод возвращает описание местоположения на карте по заданным координатам
    function get_locality($lat = 0, $lng = 0, $lang = "en") {
        $locality = "";
        if (isset($_SESSION["langs"])) {
            $lang = $_SESSION["langs"];
        } else {
            if (isset($_SERVER["HTTP_ACCEPT_LANGUAGE"])){
                $lang = substr($_SERVER["HTTP_ACCEPT_LANGUAGE"], 0, 2);
            }
        }
        if ($lat != 0 AND $lng != 0) {
            $geocode = file_get_contents("http://maps.googleapis.com/maps/api/geocode/json?latlng=".$lat.",".$lng."&language=".$lang);
            $output = json_decode($geocode);
            if ($output -> status == "OK") {
                $data = array();
                for ($i = 0; $i < count($output -> results[0] -> address_components); $i++) {
                    $data_key = $output -> results[0] -> address_components[$i] -> types[0];
                    $data_value = $output -> results[0] -> address_components[$i] -> long_name;
                    $data[$data_key] = $data_value;
                }
                $locality .= "";

                foreach ($data AS $k => $v) {
                    switch ($k){
                        case "premise":
                            $locality .= $v.", ";
                            break;
                        case "sublocality_level_1":
                            $locality .= $v.", ";
                            break;
                        case "locality":
                            $locality .= $v.", ";
                            break;
                        case "administrative_area_level_1":
                            if (isset($data["locality"]) AND $data["locality"] != $v ){
                                $locality .= $v.", ";
                            }
                            break;
                        case "country":
                            $locality .= $v;
                            break;
                    }
                }
            }
        }
        return $locality;
    }

    # Метод возвращает url трансляции в зависимости от статуса (0 - запись, 1 - Live)
    function generate_stream_url ($stream_uuid = "") {
        global $db, $project_options;

        $storage_server = $project_options["storage_servers"][0];

        $response = "";
        $character = '$';

        # TODO использовать это формирование url в ситуации с использованием нескольких серверов записи
//        $res = $db -> sql_query("SELECT
//                  CASE  WHEN `status` = '0'
//                        THEN CONCAT('http://', `storage_server`, ':8080/hls/nvr/', '$stream_uuid', '/p.m3u8?t=', ROUND(`start_date`*1000, 0), '$character', 'd=', ROUND(`duration`*1000, 0))
//                        ELSE CONCAT('rtmp://', `storage_server`, ':1935/live/', '$stream_uuid')
//                        END AS `stream_url`  FROM `streams` WHERE `uuid` = '$stream_uuid'", "", "array");

        # Формирование url в ситуации с использованием одного сервера 171.25.232.423. Используется сейчас
        $result = $db -> sql_query("SELECT 
                  CASE  WHEN `status` = '0'
                        THEN CONCAT('http://', '$storage_server', ':8080/hls/nvr/', '$stream_uuid', '/p.m3u8?t=', ROUND(`start_date`*1000, 0), '$character', 'd=', ROUND(`duration`*1000, 0))
                        ELSE CONCAT('rtmp://', '$storage_server', ':1935/live/', '$stream_uuid')
                        END AS `stream_url`  FROM `streams` WHERE `uuid` = '$stream_uuid'", "", "array");

        if (sizeof($result) > 0) {
            $response = $result[0]["stream_url"];
        }
        return $response;
    }

    # Метод возвращает массив id категорий трансляций для запрашиваемой трансляции
    function get_stream_categories ($stream_id = 0) {
        global $db;
        $response = array();
        $result = $db -> sql_query("SELECT `stream_category_id` FROM `streams_categories_link` LEFT JOIN `streams` ON `streams_categories_link`.`stream_id` = `streams`.`id` LEFT JOIN `streams_categories_data` ON `streams_categories_link`.`stream_category_id` = `streams_categories_data`.`id` WHERE `stream_id` = '$stream_id' AND `streams_categories_link`.`is_deleted` = '0' AND `streams`.`is_excess` = '0' AND `streams`.`is_blocked` = '0' AND `streams`.`is_deleted` = '0' AND `streams_categories_data`.`is_active` = '1' AND `streams_categories_data`.`is_deleted` = '0'", "", "array");

        if (sizeof($result) > 0) {
            foreach ($result as $value) {
                $response[] = $value["stream_category_id"];
            }
        }
        return $response;
    }

    # Метод создает/обновляет связи трансляции и категорий
    function link_stream_categories ($stream_id = 0, $categories_array = array()) {
        global $db, $project_options;
        $current_date = time();

        $available_categories_data = $this -> get_available_streams_categories(); # Получение массива всех доступных категорий трансляций
        $available_categories_id_array = array();
        foreach ($available_categories_data as $a_cat_id) {
            $available_categories_id_array[] = $a_cat_id["id"];
        }

        $categories_array_slice = array_slice($categories_array, 0, $project_options["streams_categories_count"]); # Использование только доступного количества категорий трансляции

        # Удаление существующих связей
        $db -> sql_query("UPDATE `streams_categories_link` SET `is_deleted` = '1' WHERE `stream_id` = '$stream_id'");

        foreach ($categories_array_slice as $cat_id) {
            if (in_array($cat_id, $available_categories_id_array)) {# Создание связи трансляции и категории в случае, если id категории находится в массиве id доступных категория трансляций
                $db -> sql_query("INSERT INTO `streams_categories_link`(`id`, `stream_id`, `stream_category_id`, `created_date`, `is_deleted`) VALUES (NULL, '$stream_id', '$cat_id', '$current_date', '0')");
            }
        }
    }

    # Метод возвращает массив id всех доступных категорий трансляций
    function get_available_streams_categories () {
        global $db;
        $response = array();
        $result = $db -> sql_query("SELECT `id`, `name_ru`, `name_en` FROM `streams_categories_data` WHERE `is_active` = '1' AND `is_deleted` = '0'", "", "array");

        if (sizeof($result) > 0) {
            foreach ($result as $value) {
                $cat_data["id"] = intval($value["id"]);
                $cat_data["name_ru"] = $value["name_ru"];
                $cat_data["name_en"] = $value["name_en"];
                $response[] = $cat_data;
            }
        }
        return $response;
    }
}
?>