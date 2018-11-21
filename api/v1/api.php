<?php
class api {

    function access_token_get(){
        global $db, $log_file, $project_options, $user;

        $response = array();
        $ip = $user -> get_client_ip();
        $time = date("H:i");

        $oauth_signature = $_SERVER["HTTP_AUTH_SIGNATURE"];
        $request_type = $_SERVER["HTTP_REQUEST_TYPE"];
        
        $access_token = gen_uuid(32); #Генерация access_token
        $oauth_timestamp = time();
        $access_token_expired_date = intval($oauth_timestamp) + 60*5; #Время жизни токена - 5 минут

        $site_auth_login = $project_options["site_auth_login"];
        $site_auth_pass = $project_options["site_auth_pass"];
        
        $result_device_by_oauth_signature = $db -> sql_query("SELECT `id`, `device_uuid`, `user_id` FROM `devices` WHERE `sha1_encrypt_id` = '$oauth_signature' AND `is_deleted` = '0'", "", "array");

        $result_user_by_oauth_signature = $db -> sql_query("SELECT `id` FROM `users` WHERE SHA1(CONCAT('$site_auth_login', '$site_auth_pass', `id`)) = '$oauth_signature' AND `is_deleted` = '0'", "", "array");

        $result_admin_by_oauth_signature = $db -> sql_query("SELECT `id` FROM `support_service_admins` WHERE SHA1(CONCAT('$site_auth_login', '$site_auth_pass', `id`, 'admin')) = '$oauth_signature' AND `is_deleted` = '0'", "", "array");

        if (sizeof($result_device_by_oauth_signature) > 0) { #Запрос подписан пользователем мобильного устройства, производится чтение подписи и определение прав доступа к api проекта данного пользователя с данного устройства
            $device_id = $result_device_by_oauth_signature[0]["id"];
            $device_uuid = $result_device_by_oauth_signature[0]["device_uuid"];
            $user_id = $result_device_by_oauth_signature[0]["user_id"];

            #Проверка доступа связки пользователь/устройство к api проекта
            $user_access_status = user_access_status($user_id, $device_uuid);

            if ($user_access_status["status"] == "OK") { #Пользователь/устройство имеет доступ к api проекта
                if ($db -> sql_query("INSERT INTO `access_tokens_devices`(`id`, `access_token`, `request_type`, `device_id`, `oauth_timestamp`, `access_token_expired_date`) VALUES (NULL, '$access_token', '$request_type', '$device_id', '$oauth_timestamp', '$access_token_expired_date')")) {
                    $response["status"] = "OK";
                    $response["access_token"] = $access_token;
                    $response["expired_date"] = $access_token_expired_date;
                } else {
                    $response["status"] = "SQL-ERROR";
                    error_log("[$time $ip] access_token_get: SQL-ERROR (oauth_signature - $oauth_signature, request_type - $request_type, devices)\n", 3, $log_file);
                }
            } else { #Доступ пользователя/устройства к api проекта ограничен
                $status = $user_access_status["status"];
                $message = $user_access_status["message"];
                $response["status"] = $status;
                $response["message"] = $message;
                error_log("[$time $ip] access_token_get: $status, $message (oauth_signature - $oauth_signature, request_type - $request_type, devices)\n", 3, $log_file);
            }
        } else if (sizeof($result_user_by_oauth_signature) > 0) {#Запрос подписан пользователем web-версии проекта, производится чтение подписи и определение данного пользователя
            $user_id = $result_user_by_oauth_signature[0]["id"];
            $client_info = $_SERVER["HTTP_USER_AGENT"];
            if ($db -> sql_query("INSERT INTO `access_tokens_web`(`id`, `access_token`, `request_type`, `user_id`, `client_info`, `oauth_timestamp`, `access_token_expired_date`) VALUES (NULL, '$access_token', '$request_type', '$user_id', '$client_info', '$oauth_timestamp', '$access_token_expired_date')")) {
                $response["status"] = "OK";
                $response["access_token"] = $access_token;
                $response["expired_date"] = $access_token_expired_date;
            } else {
                $response["status"] = "SQL-ERROR";
                error_log("[$time $ip] access_token_get: SQL-ERROR (oauth_signature - $oauth_signature, request_type - $request_type, web_users)\n", 3, $log_file);
            }
        } else if (sizeof($result_admin_by_oauth_signature) > 0){
            $admin_id = $result_admin_by_oauth_signature[0]["id"];
            $client_info = $_SERVER["HTTP_USER_AGENT"];
            if ($db -> sql_query("INSERT INTO `access_tokens_admins`(`id`, `access_token`, `request_type`, `admin_id`, `client_info`, `oauth_timestamp`, `access_token_expired_date`) VALUES (NULL, '$access_token', '$request_type', '$admin_id', '$client_info', '$oauth_timestamp', '$access_token_expired_date')")) {
                $response["status"] = "OK";
                $response["access_token"] = $access_token;
                $response["expired_date"] = $access_token_expired_date;
            } else {
                $response["status"] = "SQL-ERROR";
                error_log("[$time $ip] access_token_get: SQL-ERROR (oauth_signature - $oauth_signature, request_type - $request_type, admins)\n", 3, $log_file);
            }
        } else {
            $response["status"] = "ACCESS-DENIED";
            error_log("[$time $ip] access_token_get: ACCESS-DENIED (oauth_signature - $oauth_signature, request_type - $request_type)\n", 3, $log_file);
        }

        return $response;
    }

    function access_code_get(){
        global $db, $log_file, $user;
        
        $valid_request_types = ["users/hash_generate", "users/auth_fb", "users/auth_vk", "users/auth_tw", "users/user_add", "users/user_recovery"];

        $response = array();
        $ip = $user -> get_client_ip();
        $time = date("H:i");
        
        $request_type = $_SERVER["HTTP_REQUEST_TYPE"];
        $client_info = $_SERVER["HTTP_CLIENT_DATA"];

        $access_code = gen_uuid(32); #Генерация access_code
        $oauth_timestamp = time();
        $access_code_expired_date = intval($oauth_timestamp) + 60*5; #Время жизни кода - 5 минут
        if (in_array($request_type, $valid_request_types)) {
            if ($db -> sql_query("INSERT INTO `access_codes`(`id`, `access_code`, `request_type`, `client_info`, `oauth_timestamp`, `access_code_expired_date`) VALUES (NULL, '$access_code', '$request_type', '$client_info', '$oauth_timestamp', '$access_code_expired_date')")) {
                $response["status"] = "OK";
                $response["access_code"] = $access_code;
                $response["expired_date"] = $access_code_expired_date;
            } else {
                $response["status"] = "ERROR";
                error_log("[$time $ip] access_code_get: SQL-ERROR request_type - $request_type, client_info - $client_info\n", 3, $log_file);
            }
        } else {
            $response["status"] = "INVALID-REQUEST-TYPE";
            error_log("[$time $ip] access_code_get: INVALID-REQUEST-TYPE request_type - $request_type, client_info - $client_info\n", 3, $log_file);
        }

        return $response;
    }

    function access_code_check($current_request_type) {
        global $db, $log_file, $user;

        $ip = $user -> get_client_ip();
        $time = date("H:i");
        $current_date = time();

        $access_code = $_SERVER["HTTP_ACCESS_CODE"];
        $client_data = $_SERVER["HTTP_CLIENT_DATA"];
        $result_access_code = $db -> sql_query("SELECT `access_code_expired_date`, `request_type` FROM `access_codes` WHERE `access_code` = '$access_code'", "", "array");

        if (sizeof($result_access_code) > 0){
            $access_code_expired_date = $result_access_code[0]["access_code_expired_date"];
            $request_type = $result_access_code[0]["request_type"];

            if ($request_type == $current_request_type) { #Сравнение текущего request_type с  request_type, для которого был получен access_code
                if ($current_date <= $access_code_expired_date) { #Проверка срока действия access_code
                    $response["status"] = "OK";
                    return $response;
                }
                $response["status"] = "ACCESS-CODE-EXPIRED";
                error_log("[$time $ip] client_request_access: ACCESS-CODE-EXPIRED request_type - $request_type, current_request_type - $current_request_type, access_code - $access_code\n", 3, $log_file);
                return $response;
            }

            $response["status"] = "INVALID-REQUEST-TYPE";
            error_log("[$time $ip] client_request_access: INVALID-REQUEST-TYPE request_type - $request_type, current_request_type - $current_request_type, access_code - $access_code, client_data - $client_data\n", 3, $log_file);
            return $response;
        }

        $response["status"] = "INVALID-ACCESS-CODE";
        error_log("[$time $ip] client_request_access: INVALID-ACCESS-CODE current_request_type - $current_request_type, access_code - $access_code, client_data - $client_data\n", 3, $log_file);

        return $response;
    }

    function all_streams(){ #Для Дениса
        global $db;
        $response = array();
        $result_streams = $db -> sql_query("SELECT `uuid` FROM `streams` WHERE `is_deleted` = '0'", "", "array");
        foreach ($result_streams as $value) {
            array_push($response, $value["uuid"]);
        }
        return $response;
    }
    
    function streams_search($query = "", $current_request_type){
        global $stream;
        $client_request_access = client_request_access_status($current_request_type);

        if ($client_request_access["status"] == "OK") {
            $response = $stream -> streams_search($query);
            return $response;
        }
        return $client_request_access;
    }

    function streams_search_v2($query = "", $current_request_type){
        global $stream;
        $client_request_access = client_request_access_status($current_request_type);

        if ($client_request_access["status"] == "OK") {
            
            $response = $stream -> streams_search_v2($query);
            return $response;
        }
        return $client_request_access;
    }

    function streams_top($streams_count = 25, $current_request_type){
        global $stream;
        $client_request_access = client_request_access_status($current_request_type);

        if ($client_request_access["status"] == "OK") {
            $response = $stream -> get_top_streams($streams_count);
            return $response;
        }
        return $client_request_access;
    }

    function streams_top_v2($streams_count = 25, $current_request_type){
        global $stream;
        $client_request_access = client_request_access_status($current_request_type);

        if ($client_request_access["status"] == "OK") {
            $response = $stream -> get_top_streams_v2($streams_count);
            return $response;
        }
        return $client_request_access;
    }

    function streams_official($streams_count = 25, $current_request_type){
        global $stream;
        $client_request_access = client_request_access_status($current_request_type);

        if ($client_request_access["status"] == "OK") {
            $response = $stream -> get_official_streams($streams_count);
            return $response;
        }
        return $client_request_access;
    }

    function streams_official_v2($streams_count = 25, $current_request_type){
        global $stream;
        $client_request_access = client_request_access_status($current_request_type);

        if ($client_request_access["status"] == "OK") {
            $response = $stream -> get_official_streams_v2($streams_count);
            return $response;
        }
        return $client_request_access;
    }

    function streams(){
        global $db, $stream;
        $response = array();
        $result = $db -> sql_query("SELECT * FROM `streams` WHERE `is_excess` = '0' AND `is_blocked` = '0' AND `is_deleted` = '0'", "", "array");
        if (sizeof($result) > 0) {
            foreach ($result as $value) {
                $stream_uuid = $value["uuid"];

                $stream_clients = $stream -> get_stream_clients($stream_uuid);
                # Количество пользователей, просматривающих трансляцию в данный момент
                $clients_count = sizeof($stream_clients["online"]);

                $stream_data["uuid"] = $value["uuid"];
                $stream_data["user_id"] = $value["user_id"];
                $stream_data["name"] = $value["name"];
                $stream_data["start_date"] = intval($result[0]["start_date"]);
                $stream_data["end_date"] = intval($result[0]["end_date"]);
                $stream_data["url"] = $value["url"];

                $stream_data["client_count"] = $clients_count;
                $stream_data["lat"] = $value["lat"];
                $stream_data["lng"] = $value["lng"];
                $permissions = unserialize($value["permissions"]);
                $stream_data["permissions"] = array();
                if (sizeof($permissions) > 0){
                    $stream_data["permissions"] = $permissions;
                }
                array_push($response, $stream_data);
            }
        }
        return $response;
    }

    function get_streams_in_map_rect($lat_min = 0, $lat_max = 0, $lng_min = 0, $lng_max = 0, $current_request_type) {
        global $db, $log_file, $project_options, $user;

        $client_request_access = client_request_access_status($current_request_type);

        if ($client_request_access["status"] == "OK") {

            $streams_filter_date_map = $project_options["streams_filter_date_map"];
            $filter_date = time() - 60*60*24*$streams_filter_date_map; # Отображение live и записей за последние 15 дней

            $ip = $user -> get_client_ip();
            $time = date("H:i");

            $lat_min = floatval($lat_min);
            $lat_max = floatval($lat_max);
            $lng_min = floatval($lng_min);
            $lng_max = floatval($lng_max);

            $response = array();
            $result = $db -> sql_query("SELECT `uuid`, `status`, `lat`, `lng`, `etag_stream` FROM `streams` LEFT JOIN `users` ON `users`.`id` = `streams`.`user_id` WHERE `streams`.`lat` BETWEEN '$lat_min' AND '$lat_max' AND `streams`.`lng` BETWEEN '$lng_min' AND '$lng_max' AND `streams`.`is_excess` = '0' AND `streams`.`is_blocked` = '0' AND `streams`.`is_deleted` = '0' AND `streams`.`start_date` >= '$filter_date' AND `users`.`is_blocked` = '0' AND `users`.`is_deleted` = '0'", "", "array");
            if (sizeof($result) > 0 AND !empty($result[0])) {
                $streams_uuid_a = array();
                foreach ($result as $value) {
                    $stream_data = array();
                    $stream_uuid = $value["uuid"];
                    array_push($streams_uuid_a, $stream_uuid);
                    array_push($stream_data, $stream_uuid, $value["status"], $value["lat"], $value["lng"], $value["etag_stream"]);
                    array_push($response, $stream_data);
                }
                $streams_uuid = implode(", ", $streams_uuid_a); # Для записи в лог файл
                error_log("[$time $ip] get_streams_in_map_rect: lat_min: $lat_min, lat_max: $lat_max, lng_min: $lng_min, lng_max: $lng_max: $streams_uuid\n", 3, $log_file);
            } else {
                error_log("[$time $ip] get_streams_in_map_rect: lat_min: $lat_min, lat_max: $lat_max, lng_min: $lng_min, lng_max: $lng_max трансляции не найдены\n", 3, $log_file);
            }

            return $response;
        }
        return $client_request_access;
    }

    function stream($stream_uuid = "", $current_request_type) {
        global $stream;

        $client_request_access = client_request_access_status($current_request_type);
        
        if ($client_request_access["status"] == "OK") {

            $user_id = $client_request_access["user_id"];
            $response = $stream -> stream_data($stream_uuid, $user_id);
            return $response;
        }
        return $client_request_access;
    }

    function ws_stream($stream_uuid = "", $current_request_type) {
        global $stream;

        $client_request_access = client_request_access_status($current_request_type);

        if ($client_request_access["status"] == "OK") {
            $response = $stream -> ws_stream_data($stream_uuid);
            return $response;
        }
        return $client_request_access;
    }

    function streams_array($data, $current_request_type) {
        global $stream;

        $client_request_access = client_request_access_status($current_request_type);

        if ($client_request_access["status"] == "OK") {

            $user_id = $client_request_access["user_id"];
            $response = $stream -> streams_array_data($data, $user_id);
            return $response;
        }
        return $client_request_access;
    }
	
	function streams_etags($data, $current_request_type) {
        global $stream;

        $client_request_access = client_request_access_status($current_request_type);

        if ($client_request_access["status"] == "OK") {
            $response = $stream -> streams_etags_data($data);
            return $response;
        }
        return $client_request_access;
    }

    function stream_delete($stream_uuid = "", $current_request_type) {
        global $stream;

        $client_request_access = client_request_access_status($current_request_type);

        if ($client_request_access["status"] == "OK") {
            $response = $stream -> stream_delete($stream_uuid);
            return $response;
        }
        return $client_request_access;
    }

    # Возвращает для каждой трансляции количество клиентов, просматривающих в данный момент
    function get_streams_clients_count($current_request_type) {
        global $stream;
        $client_request_access = client_request_access_status($current_request_type);

        if ($client_request_access["status"] == "OK") {
            $response = $stream -> get_streams_clients_count();
            return $response;
        }
        return $client_request_access;
    }

    function get_stream_clients($stream_uuid = "", $current_request_type) {
        global $stream;

        $client_request_access = client_request_access_status($current_request_type);

        if ($client_request_access["status"] == "OK") {
            $response = $stream -> get_stream_clients($stream_uuid);
            return $response;
        }
        return $client_request_access;
    }

    # Изменение статуса подключения пользователя к просмотру трансляции
    function connect($data, $current_request_type){
        global $db, $log_file, $stream, $user;

        $client_request_access = client_request_access_status($current_request_type);

        if ($client_request_access["status"] == "OK") {
            $ip = $user -> get_client_ip();
            $time = date("H:i");
            $current_date = time();

            $client_id = intval($client_request_access["user_id"]);

            $expected_params = array();
            if (isset($data["uuid"]) AND !empty($data["uuid"])) {
                $stream_uuid = $data["uuid"];
            } else {
                $expected_params[] = "uuid";
            }

            if (isset($data["state"]) AND !empty($data["state"])) {
                $state = $data["state"];
            } else {
                $expected_params[] = "state";
            }

            if (count($expected_params) > 0 ){
                $expected = implode(", ", $expected_params);
                $error = "Не получены параметры: $expected";
                $response["status"] = "ERROR";
                error_log("[$time $ip] connections: query: $state, client_id: $client_id, stream_uuid: $stream_uuid. $error\n", 3, $log_file);
            } else {

                $result = $db -> sql_query("SELECT `streams`.`id` AS `stream_id` FROM `streams` LEFT JOIN `users` ON `streams`.`user_id` = `users`.`id` WHERE `uuid` = '$stream_uuid' AND `streams`.`is_blocked` = '0' AND `streams`.`is_excess` = '0' AND `streams`.`is_deleted` = '0' AND `users`.`is_blocked` = '0' AND `users`.`is_deleted` = '0'", "", "array");

                if (sizeof($result) > 0) {
                    $stream_id = $result[0]["stream_id"];
                    $stream_clients = $stream -> get_stream_clients($stream_uuid);

                    $connections_array = array_map("intval", $stream_clients["online"]);#Создание массива id текущих зрителей, приведение к числовому формату элементов массива

                    $stream -> etag_stream_update($stream_uuid); # Обновление etag трансляции

                    if ($state == "connect") {
                        if (!in_array($client_id, $connections_array)) {# Данный пользователь отстутствует в списке зрителей трансляции
                            # Добавление пользователя
                            $db -> sql_query("INSERT INTO `streams_clients`(`id`, `stream_id`, `user_id`, `connected_date`, `disconnected_date`) VALUES (NULL, '$stream_id', '$client_id', '$current_date', '0')");
                            $response["status"] = "OK";
                            error_log("[$time $ip] connections: $state stream_uuid = $stream_uuid, client_id = $client_id \n", 3, $log_file);
                        } else {
                            $response["status"] = "OK";
                            error_log("[$time $ip] connections: $state клиент добавлен ранее, stream_uuid = $stream_uuid, client_id = $client_id \n", 3, $log_file);
                        }
                    } else if ($state == "disconnect") {
                        $db -> sql_query("UPDATE `streams_clients` SET `disconnected_date` = '$current_date' WHERE `stream_id` = '$stream_id' AND `user_id` = '$client_id' AND `disconnected_date` = '0'");
                        if (in_array($client_id, $connections_array)) {
                            $response["status"] = "OK";
                            error_log("[$time $ip] connections: $state stream_uuid = $stream_uuid, client_id = $client_id \n", 3, $log_file);
                        } else {
                            $response["status"] = "OK";
                            error_log("[$time $ip] connections: $state клиент отсутствует в списке, stream_uuid = $stream_uuid, client_id = $client_id \n", 3, $log_file);
                        }
                    } else {
                        $response["status"] = "ERROR";
                        error_log("[$time $ip] connections: $state stream_uuid = $stream_uuid, client_id = $client_id \n", 3, $log_file);
                    }

                } else {
                    $response["status"] = "NOT-FOUND";
                    error_log("[$time $ip] connections: stream not found stream_uuid = $stream_uuid\n", 3, $log_file);
                }
            }
            return $response;
        }
        return $client_request_access;
    }

    # Изменение статуса подключения пользователя к просмотру трансляции (устаревший метод)
    function connect_old($data, $current_request_type){
        global $db, $log_file, $stream, $user;

        $client_request_access = client_request_access_status($current_request_type);

        if ($client_request_access["status"] == "OK") {
            $ip = $user -> get_client_ip();
            $time = date("H:i");
            $current_date = time();

            $client_id = $client_request_access["user_id"];
            
            $expected_params = array();
            if (isset($data["uuid"]) AND !empty($data["uuid"])) {
                $stream_uuid = $data["uuid"];
            } else {
                $expected_params[] = "uuid";
            }

            if (isset($data["state"]) AND !empty($data["state"])) {
                $state = $data["state"];
            } else {
                $expected_params[] = "state";
            }

            if (count($expected_params) > 0 ){
                $expected = implode(", ", $expected_params);
                $error = "Не получены параметры: $expected";
                $response["status"] = "ERROR";
                error_log("[$time $ip] connections: query: $state, client_id: $client_id, stream_uuid: $stream_uuid. $error\n", 3, $log_file);
            } else {
                error_log("[$time $ip] connections: query - $state, $client_id, $stream_uuid\n", 3, $log_file);

                $result = $db -> sql_query("SELECT * FROM `streams` WHERE `uuid` = '$stream_uuid' AND `is_excess` = '0' AND `is_blocked` = '0' AND `is_deleted` = '0'", "", "array");
                if (sizeof($result) > 0) {
                    $hero_id = $result[0]["user_id"];
                    if (user($hero_id) AND $hero_id != $client_id) { #Не учитываем владельца трансляции
                        $stream_id = $result[0]["id"];
                        $stream_clients = $stream -> get_stream_clients($stream_uuid);

                        $connections_array = array_map("intval", $stream_clients["online"]);#Создание массива id текущих зрителей, приведение к числовому формату элементов массива

                        if ($state == "connect") {
                            if (!in_array($client_id, $connections_array)) {# Данный пользователь отстутствует в списке зрителей трансляции
                                # Добавление пользователя
                                $db -> sql_query("INSERT INTO `streams_clients`(`id`, `stream_id`, `user_id`, `connected_date`, `disconnected_date`) VALUES (NULL, '$stream_id', '$client_id', '$current_date', '0')");
                                $response["status"] = "OK";
                                error_log("[$time] connections: $state stream_uuid = $stream_uuid, client_id = $client_id \n", 3, $log_file);
                            } else {
                                $response["status"] = "OK";
                                error_log("[$time] connections: $state клиент добавлен ранее, stream_uuid = $stream_uuid, client_id = $client_id \n", 3, $log_file);
                            }
                        } else if ($state == "disconnect") {
                            $db -> sql_query("UPDATE `streams_clients` SET `disconnected_date` = '$current_date' WHERE `stream_id` = '$stream_id' AND `user_id` = '$client_id' AND `disconnected_date` = '0'");
                            if (in_array($client_id, $connections_array)) {
                                $response["status"] = "OK";
                                error_log("[$time] connections: $state stream_uuid = $stream_uuid, client_id = $client_id \n", 3, $log_file);
                            } else {
                                $response["status"] = "OK";
                                error_log("[$time] connections: $state клиент отсутствует в списке, stream_uuid = $stream_uuid, client_id = $client_id \n", 3, $log_file);
                            }
                        } else {
                            $response["status"] = "ERROR";
                            error_log("[$time] connections: $state stream_uuid = $stream_uuid, client_id = $client_id \n", 3, $log_file);
                        }
                    } else {
                        $response["status"] = "ERROR";
                        error_log("[$time $ip] connections: не найден пользователь или hero_id = client_id, hero_id($hero_id), client_id($client_id)\n", 3, $log_file);
                    }

                } else {
                    $response["status"] = "NOT-FOUND";
                    error_log("[$time $ip] connections: stream not found stream_uuid = $stream_uuid\n", 3, $log_file);
                }
            }
            return $response;
        }
        return $client_request_access;
    }

    function update_loc($data, $current_request_type){
        global $db, $log_file, $stream, $user;

        $client_request_access = client_request_access_status($current_request_type);
        if ($client_request_access["status"] == "OK") {
            $ip = $user -> get_client_ip();
            $time = date("H:i");

            $client_id = $client_request_access["user_id"];

            $expected_params = array();
            if (isset($data["uuid"]) AND !empty($data["uuid"])) {
                $stream_uuid = $data["uuid"];
            } else {
                $expected_params[] = "uuid";
            }

            if (isset($data["loc_data"]) AND !empty($data["loc_data"])) {
                $loc_data = $data["loc_data"];
            } else {
                $expected_params[] = "loc_data";
            }

            $updated_date = microtime_float();
            if (isset($data["timestamp"]) AND !empty($data["timestamp"])) {
                $updated_date = $data["timestamp"];
            }

            if (isset($data["time_from_start"]) AND !empty($data["time_from_start"])) {
                $time_from_start = $data["time_from_start"];
            }

            if (count($expected_params) > 0 ){
                $expected = implode(", ", $expected_params);
                $error = "Не получены параметры: $expected";
                $response["status"] = "ERROR";
                error_log("[$time $ip] update_loc: stream_uuid - $stream_uuid $error\n", 3, $log_file);
            } else {
                $lat = $loc_data[0];
                $lng = $loc_data[1];

                $result = $db -> sql_query("SELECT * FROM `streams` WHERE `uuid` = '$stream_uuid' AND `is_excess` = '0' AND `is_blocked` = '0' AND `is_deleted` = '0'", "", "array");
                if (sizeof($result) > 0 AND !empty($result[0])) {
                    $stream_id = $result[0]["id"];
                    $hero_id = $result[0]["user_id"];
                    $on_map = $result[0]["on_map"];

                    if ($client_id == $hero_id AND $on_map == '1'){#Пользователь, отправляющий координаты является владельцем трансляции и для данной трансляции разрешено определение местоположения

                        #Поиск последних записанных координат
                        $result_loc = $db -> sql_query("SELECT * FROM `streams_coordinates_dynamics` WHERE `updated_date` = (SELECT MAX(`updated_date`) FROM `streams_coordinates_dynamics` WHERE `stream_id` = '$stream_id' GROUP BY `stream_id`) AND `lat` = '$lat' AND `lng` = '$lng' ", "", "array");
                        if (sizeof($result_loc) > 0) { #Такие координаты сохранены в последней записи
                            $response["status"] = "ERORR";
                            error_log("[$time $ip] update_loc: ERORR попытка сохранения повторяющихся координат stream_uuid - $stream_uuid ($lat, $lng)\n", 3, $log_file);
                        } else { #Полученные координаты отличаются от последних записанных данных
                            if ($result_update_loc = $db -> sql_query("INSERT INTO `streams_coordinates_dynamics`(`id`, `stream_id`, `updated_date`, `time_from_start`, `lat`, `lng`) VALUES (NULL, '$stream_id', '$updated_date', '$time_from_start', '$lat', '$lng');")){

                                $stream -> etag_stream_update($stream_uuid); # Обновление etag трансляции

                                $response["status"] = "OK";
                                error_log("[$time $ip] update_loc: OK обновлены координаты stream_uuid - $stream_uuid ($lat, $lng)\n", 3, $log_file);
                            } else {
                                $response["status"] = "SQL-ERROR";
                                error_log("[$time $ip] update_loc: SQL-ERROR ошибка выполнения запроса stream_uuid - $stream_uuid ($lat, $lng)\n", 3, $log_file);
                            }
                        }

                    } else {
                        $response["status"] = "ACCESS-DENIED";
                        $response["message"] = "STREAM-ACCESS-DENIED";
                        error_log("[$time $ip] update_loc: ACCESS-DENIED попытка сохранения координат сторонним пользователем client_id - $client_id, hero_id - $hero_id, stream_uuid - $stream_uuid\n", 3, $log_file);
                    }

                } else {
                    $response["status"] = "STREAM-NOT-FOUND";
                    error_log("[$time $ip] update_loc: STREAM-NOT-FOUND stream_uuid - $stream_uuid\n", 3, $log_file);
                }
            }
            return $response;
        }
        return $client_request_access;
    }

    function update_ori($data, $current_request_type){
        global $db, $log_file, $stream, $user;

        $client_request_access = client_request_access_status($current_request_type);
        if ($client_request_access["status"] == "OK") {
            $ip = $user -> get_client_ip();
            $time = date("H:i");
            $updated_date = microtime_float();

            $client_id = $client_request_access["user_id"];

            $expected_params = array();
            if (isset($data["uuid"]) AND !empty($data["uuid"])) {
                $stream_uuid = $data["uuid"];
            } else {
                $expected_params[] = "uuid";
            }

            if (isset($data["ori"]) AND !empty($data["ori"])) {
                $ori = $data["ori"];
            } else {
                $expected_params[] = "ori";
            }

            if (isset($data["timestamp"]) AND !empty($data["timestamp"])) {
                $updated_date = $data["timestamp"];
            }

            if (isset($data["time_from_start"]) AND !empty($data["time_from_start"])) {
                $time_from_start = $data["time_from_start"];
            } else {
                $expected_params[] = "time_from_start";
            }

            if (count($expected_params) > 0 ){
                $expected = implode(", ", $expected_params);
                $error = "Не получены параметры: $expected";
                $response["status"] = "ERROR";
                error_log("[$time $ip] update_ori: ERROR stream_uuid - $stream_uuid $error\n", 3, $log_file);
            } else {
                $result = $db -> sql_query("SELECT * FROM `streams` WHERE `uuid` = '$stream_uuid' AND `is_excess` = '0' AND `is_blocked` = '0' AND `is_deleted` = '0'", "", "array");
                if (sizeof($result) > 0 AND !empty($result[0])) {
                    $stream_id = $result[0]["id"];
                    $hero_id = $result[0]["user_id"];

                    if ($client_id == $hero_id){#Пользователь, отправляющий параметры отриентации является владельцем трансляции

                        #Поиск последнего записанного значения
                        $result_ori = $db -> sql_query("SELECT * FROM `streams_ori_dynamics` WHERE `updated_date` = (SELECT MAX(`updated_date`) FROM `streams_ori_dynamics` WHERE `stream_id` = '$stream_id' GROUP BY `stream_id`) AND `ori` = '$ori' ", "", "array");
                        if (sizeof($result_ori) > 0) { #Такое значение сохранено в последней записи
                            $response["status"] = "ERORR";
                            error_log("[$time $ip] update_ori: ERORR попытка сохранения повторяющейся ориентации stream_uuid - $stream_uuid ($ori)\n", 3, $log_file);
                        } else {#Полученные данные отличаются от последних записанных данных
                            if ($result_update_ori = $db -> sql_query("INSERT INTO `streams_ori_dynamics`(`id`, `stream_id`, `updated_date`, `time_from_start`, `ori`) VALUES (NULL, '$stream_id', '$updated_date', '$time_from_start', '$ori');")){

                                $stream -> etag_stream_update($stream_uuid); # Обновление etag трансляции

                                $response["status"] = "OK";
                                error_log("[$time $ip] update_ori: OK stream_uuid - $stream_uuid ($ori)\n", 3, $log_file);
                            } else {
                                $response["status"] = "SQL-ERROR";
                                error_log("[$time $ip] update_ori: SQL-ERROR ошибка выполнения запроса stream_uuid - $stream_uuid ($ori)\n", 3, $log_file);
                            }
                        }

                    } else {
                        $response["status"] = "ACCESS-DENIED";
                        $response["message"] = "STREAM-ACCESS-DENIED";
                        error_log("[$time $ip] update_ori: ACCESS-DENIED попытка сохранения ориаентации устройства сторонним пользователем (client_id - $client_id, hero_id - $hero_id, stream_uuid - $stream_uuid)\n", 3, $log_file);
                    }

                } else {
                    $response["status"] = "STREAM-NOT-FOUND";
                    error_log("[$time $ip] update_ori: STREAM-NOT-FOUND stream_uuid - $stream_uuid\n", 3, $log_file);
                }
            }
            return $response;
        }
        return $client_request_access;
    }

    function update_heading($data, $current_request_type){
        global $db, $log_file, $stream, $user;

        $client_request_access = client_request_access_status($current_request_type);
        if ($client_request_access["status"] == "OK") {
            $ip = $user -> get_client_ip();
            $time = date("H:i");
            $updated_date = microtime_float();

            $client_id = $client_request_access["user_id"];

            $expected_params = array();
            if (isset($data["uuid"]) AND !empty($data["uuid"])) {
                $stream_uuid = $data["uuid"];
            } else {
                $expected_params[] = "uuid";
            }

            if (isset($data["heading"]) AND !empty($data["heading"])) {
                $heading = $data["heading"];
            } else {
                $expected_params[] = "heading";
            }

            if (isset($data["timestamp"]) AND !empty($data["timestamp"])) {
                $updated_date = $data["timestamp"];
            }

            if (isset($data["time_from_start"]) AND !empty($data["time_from_start"])) {
                $time_from_start = $data["time_from_start"];
            }

            if (count($expected_params) > 0 ){
                $expected = implode(", ", $expected_params);
                $error = "Не получены параметры: $expected";
                $response["status"] = "ERROR";
                error_log("[$time $ip] update_heading: stream_uuid - $stream_uuid $error\n", 3, $log_file);
            } else {
                $result = $db -> sql_query("SELECT `id`, `user_id` FROM `streams` WHERE `uuid` = '$stream_uuid' AND `is_excess` = '0' AND `is_blocked` = '0' AND `is_deleted` = '0'", "", "array");
                if (sizeof($result) > 0) {
                    $stream_id = $result[0]["id"];
                    $hero_id = $result[0]["user_id"];

                    if ($client_id == $hero_id){#Пользователь, отправляющий параметры отриентации является владельцем трансляции

                        #Поиск последнего записанного значения
                        $result_heading = $db -> sql_query("SELECT * FROM `streams_heading_dynamics` WHERE `updated_date` = (SELECT MAX(`updated_date`) FROM `streams_heading_dynamics` WHERE `stream_id` = '$stream_id' GROUP BY `stream_id`) AND heading = '$heading' ", "", "array");
                        if (sizeof($result_heading) > 0) { #Такое значение сохранено в последней записи
                            $response["status"] = "ERORR";
                            error_log("[$time $ip] update_heading: ERORR попытка сохранения повторяющихся координат stream_uuid - $stream_uuid ($heading)\n", 3, $log_file);
                        } else {#Полученные данные отличаются от последних записанных данных
                            if ($result_update_heading = $db -> sql_query("INSERT INTO `streams_heading_dynamics`(`id`, `stream_id`, `updated_date`, `time_from_start`, `heading`) VALUES (NULL, '$stream_id', '$updated_date', '$time_from_start', '$heading');")){

                                $stream -> etag_stream_update($stream_uuid); # Обновление etag трансляции

                                $response["status"] = "OK";
                                error_log("[$time $ip] update_heading: OK stream_uuid - $stream_uuid ($heading)\n", 3, $log_file);
                            } else {
                                $response["status"] = "SQL-ERROR";
                                error_log("[$time $ip] update_heading: SQL-ERROR ошибка выполнения запроса stream_uuid - $stream_uuid ($heading)\n", 3, $log_file);
                            }
                        }
                    } else {
                        $response["status"] = "ACCESS-DENIED";
                        $response["message"] = "STREAM-ACCESS-DENIED";
                        error_log("[$time $ip] update_heading: ACCESS-DENIED попытка сохранения ориаентации устройства сторонним пользователем (client_id - $client_id, hero_id - $hero_id, stream_uuid - $stream_uuid)\n", 3, $log_file);
                    }

                } else {
                    $response["status"] = "STREAM-NOT-FOUND";
                    error_log("[$time $ip] update_heading: STREAM-NOT-FOUND (stream_uuid - $stream_uuid)\n", 3, $log_file);
                }
            }
            return $response;
        }
        return $client_request_access;
    }
    
    # Обновление данных о высоте над уровнем моря
    function update_altitude($data, $current_request_type){
        global $db, $log_file, $stream, $user;

        $client_request_access = client_request_access_status($current_request_type);
        if ($client_request_access["status"] == "OK") {
            $ip = $user -> get_client_ip();
            $time = date("H:i");
            $updated_date = microtime_float();

            $client_id = $client_request_access["user_id"];

            $expected_params = array();
            if (isset($data["uuid"]) AND !empty($data["uuid"])) {
                $stream_uuid = $data["uuid"];
            } else {
                $expected_params[] = "uuid";
            }

            if (isset($data["altitude"]) AND ($data["altitude"] >= -11100 && $data["altitude"] <= 15000)) {
                $altitude = $data["altitude"];
            } else {
                $expected_params[] = "altitude";
            }

            if (isset($data["timestamp"]) AND !empty($data["timestamp"])) {
                $updated_date = $data["timestamp"];
            }

            if (isset($data["time_from_start"]) AND !empty($data["time_from_start"])) {
                $time_from_start = $data["time_from_start"];
            }

            if (count($expected_params) > 0 ){
                $expected = implode(", ", $expected_params);
                $error = "Не получены параметры: $expected";
                $response["status"] = "ERROR";
                error_log("[$time $ip] update_altitude: stream_uuid - $stream_uuid $error\n", 3, $log_file);
            } else {
                $result = $db -> sql_query("SELECT `id`, `user_id` FROM `streams` WHERE `uuid` = '$stream_uuid' AND `is_excess` = '0' AND `is_blocked` = '0' AND `is_deleted` = '0'", "", "array");
                if (sizeof($result) > 0) {
                    $stream_id = $result[0]["id"];
                    $hero_id = $result[0]["user_id"];

                    if ($client_id == $hero_id){#Пользователь, отправляющий параметры высоты является владельцем трансляции

                        #Поиск последнего записанного значения
                        $result_altitude = $db -> sql_query("SELECT * FROM `streams_altitude_dynamics` WHERE `updated_date` = (SELECT MAX(`updated_date`) FROM `streams_altitude_dynamics` WHERE `stream_id` = '$stream_id' GROUP BY `stream_id`) AND altitude = '$altitude' ", "", "array");
                        if (sizeof($result_altitude) > 0) { #Такое значение сохранено в последней записи
                            $response["status"] = "ERORR";
                            error_log("[$time $ip] update_altitude: ERORR попытка сохранения повторяющейся высоты stream_uuid - $stream_uuid ($altitude)\n", 3, $log_file);
                        } else {#Полученные данные отличаются от последних записанных данных
                            if ($result_update_altitude = $db -> sql_query("INSERT INTO `streams_altitude_dynamics`(`id`, `stream_id`, `updated_date`, `time_from_start`, `altitude`) VALUES (NULL, '$stream_id', '$updated_date', '$time_from_start', '$altitude');")){

                                $stream -> etag_stream_update($stream_uuid); # Обновление etag трансляции

                                $response["status"] = "OK";
                                error_log("[$time $ip] update_altitude: OK stream_uuid - $stream_uuid ($altitude)\n", 3, $log_file);
                            } else {
                                $response["status"] = "SQL-ERROR";
                                error_log("[$time $ip] update_altitude: SQL-ERROR ошибка выполнения запроса stream_uuid - $stream_uuid ($altitude)\n", 3, $log_file);
                            }
                        }
                    } else {
                        $response["status"] = "ACCESS-DENIED";
                        $response["message"] = "STREAM-ACCESS-DENIED";
                        error_log("[$time $ip] update_altitude: ACCESS-DENIED попытка сохранения высоты устройства сторонним пользователем (client_id - $client_id, hero_id - $hero_id, stream_uuid - $stream_uuid)\n", 3, $log_file);
                    }

                } else {
                    $response["status"] = "STREAM-NOT-FOUND";
                    error_log("[$time $ip] update_altitude: STREAM-NOT-FOUND (stream_uuid - $stream_uuid)\n", 3, $log_file);
                }
            }
            return $response;
        }
        return $client_request_access;
    }
    
    # Получение скриншота трансляции
    function get_stream_snapshot($stream_uuid = "") {
        global $db, $stream, $archive_stream_snapshots_url, $live_stream_snapshot_url, $media_query_log_file, $user;

        $ip = $user -> get_client_ip();
        $time = date("H:i");

        $snapshot = file_get_contents("$_SERVER[DOCUMENT_ROOT]/assets/images/default.jpg");

        $storage_server_data = $stream -> get_server($stream_uuid);
        if ($storage_server_data["status"] = "OK") {
            $storage_server = $storage_server_data["server"];
            $result_stream = $db -> sql_query("SELECT `end_date` FROM `streams` WHERE `uuid` = '$stream_uuid' AND `is_excess` = '0' AND `is_deleted` = '0'", "", "array");
            if (sizeof($result_stream) > 0) {
                if (intval($result_stream[0]["end_date"]) > 0) {
                    #Это архивная запись, запрос первого скриншота из массива всех скриншотов записи, возвращаемого медиасервером
                    $snapshot_data_url = "http://$storage_server:8082".$archive_stream_snapshots_url.$stream_uuid;
                    if ($data = file_get_contents($snapshot_data_url, 0, stream_context_create(array("http" => array("timeout" => "3"))))) {
                        $snapshots_data = json_decode($data, true);
                        $snapshot = base64_decode($snapshots_data[0][1]);

                        error_log("[$time $ip] get_stream_snapshot: OK $snapshot_data_url\n", 3, $media_query_log_file);

                    } else {
                        error_log("[$time $ip] get_stream_snapshot: timeout $snapshot_data_url\n", 3, $media_query_log_file);
                    }

                } else {
                    # Live
                    $snapshot_data_url = "http://$storage_server:8082".$live_stream_snapshot_url.$stream_uuid;
                    if ($snapshot = file_get_contents($snapshot_data_url, 0, stream_context_create(array("http" => array("timeout" => "3"))))) {
                        error_log("[$time $ip] get_stream_snapshot: OK $snapshot_data_url \n", 3, $media_query_log_file);
                    } else {
                        $snapshot = file_get_contents("$_SERVER[DOCUMENT_ROOT]/assets/images/default.jpg");
                        error_log("[$time $ip] get_stream_snapshot: timeout $snapshot_data_url\n", 3, $media_query_log_file);
                    }
                }
            }
        }

        header("Content-Length: ".strlen($snapshot));
        return $snapshot;
    }

    # Получение уменьшенного превью-изображения трансляции
    function get_stream_thumb($percent = 1, $stream_uuid = "") {
        global $db, $stream, $archive_stream_snapshots_url, $live_stream_snapshot_url, $media_query_log_file, $user;

        if ($percent < 0.1 OR $percent > 2) {
            $percent = 1;
        }
        $ip = $user -> get_client_ip();
        $time = date("H:i");

        $snapshot = file_get_contents("$_SERVER[DOCUMENT_ROOT]/assets/images/default.jpg");
        $storage_server_data = $stream -> get_server($stream_uuid);
        if ($storage_server_data["status"] = "OK") {
            $storage_server = $storage_server_data["server"];
            $result_stream = $db -> sql_query("SELECT `end_date` FROM `streams` WHERE `uuid` = '$stream_uuid' AND `is_excess` = '0' AND `is_deleted` = '0'", "", "array");

            if (sizeof($result_stream) > 0) {
                if (intval($result_stream[0]["end_date"]) > 0) {
                    #Это архивная запись, запрос первого скриншота из массива всех скриншотов записи, возвращаемого медиасервером
                    $snapshot_data_url = "http://$storage_server:8082".$archive_stream_snapshots_url.$stream_uuid;
                    if ($data = file_get_contents($snapshot_data_url, 0, stream_context_create(array("http" => array("timeout" => "3"))))) {
                        $snapshots_data = json_decode($data, true);
                        $snapshot = base64_decode($snapshots_data[0][1]);

                        error_log("[$time $ip] get_stream_thumb: OK $snapshot_data_url\n", 3, $media_query_log_file);

                    } else {
                        error_log("[$time $ip] get_stream_thumb: timeout $snapshot_data_url\n", 3, $media_query_log_file);
                    }
                } else {
                    #Live
                    $snapshot_data_url = "http://$storage_server:8082".$live_stream_snapshot_url.$stream_uuid;
                    if ($snapshot = file_get_contents($snapshot_data_url, 0, stream_context_create(array("http" => array("timeout" => "3"))))) {
                        error_log("[$time $ip] get_stream_thumb: OK $snapshot_data_url \n", 3, $media_query_log_file);
                    } else {
                        $snapshot = file_get_contents("$_SERVER[DOCUMENT_ROOT]/assets/images/default.jpg");
                        error_log("[$time $ip] get_stream_thumb: timeout $snapshot_data_url\n", 3, $media_query_log_file);
                    }
                }
            }
        }

        if ($img = ImageCreateFromString($snapshot)) {
            $width = imagesx($img);
            $height = imagesy($img);
            $newwidth = $width * $percent;
            $newheight = $height * $percent;
            $thumb = imagecreatetruecolor($newwidth, $newheight);
            imagecopyresized($thumb, $img, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
            imagejpeg($thumb, null, 80);
            imagedestroy($thumb);
            exit(0);
        }

        header("Content-Length: ".strlen($img));
        return $img;
    }

    # Генерация hash кода при регистрации пользователя в приложении
    function hash_generate($data, $current_request_type) {
        global $log_file, $user;

        $client_request_access = client_request_access_status($current_request_type);
        if ($client_request_access["status"] == "OK") {

            $ip = $user -> get_client_ip();
            $time = date("H:i");

            $hash_code = gen_uuid_num(4);

            if (isset($data["phone"]) AND !empty($data["phone"])) {
                $lang = $data["lang"];

                #Удаление лишних символов в строке
                $trimmed_number = preg_replace("/[^0-9]/", "", $data["phone"]);

                if (preg_match("/en/i",$lang)) {
                    $message = "Enter code " . $hash_code . " in PROGECT_NAME.";
                } else {
                    $message = "Введите код " . $hash_code . " в PROGECT_NAME.";
                }

                if (strlen($trimmed_number) == 11) {
                    #Если получен номер в формате 79110000000, заменяем первый символ на 7
                    $trimmed_number = substr_replace($trimmed_number, '7', 0, 1);
                }

                if (sms($trimmed_number, $message) !== FALSE){
                    $response["status"] = "OK";
                    $response["message"] = $hash_code;
                    error_log("[$time $ip] hash_generate: OK отправлено sms на $trimmed_number, code - $hash_code\n", 3, $log_file);
                } else {
                    $error = sms_error();
                    $response["status"] = "ERROR";
                    $response["message"] = $error;
                    error_log("[$time $ip] hash_generate: ERROR $error\n", 3, $log_file);
                }

            } else {
                $response["status"] = "ERROR";
                $response["message"] = "Не получен номер телефона";

                error_log("[$time $ip] hash_generate: ERROR не получен номер телефона\n", 3, $log_file);
            }
            return $response;
        }
        return $client_request_access;
    }

    # Возвращает информацию о каждом пользователе из массива id пользователей
    function users_array($data, $current_request_type){
        global $user;
        $client_request_access = client_request_access_status($current_request_type);

        if ($client_request_access["status"] == "OK") {
            $response = $user -> users_array_data($data);
            return $response;
        }
        return $client_request_access;
    }

    # Возвращает информацию о каждом пользователе из массива id пользователей
    function users_array2($data, $current_request_type){
        global $user;
        $client_request_access = client_request_access_status($current_request_type);

        if ($client_request_access["status"] == "OK") {
            $response = $user -> users_array_data2($data);
            return $response;
        }
        return $client_request_access;
    }

    # Возвращает etag для каждого элемента из массива id пользователей
    function users_etags($data, $current_request_type){
        global $user;
        $client_request_access = client_request_access_status($current_request_type);

        if ($client_request_access["status"] == "OK") {
            $response = $user -> users_etags_data($data);
            return $response;
        }
        return $client_request_access;
    }

    # Возвращает информацию о пользователе
    function user($user_id = 0, $current_request_type){
        global $user;
        $client_request_access = client_request_access_status($current_request_type);

        if ($client_request_access["status"] == "OK") {
            $response = $user -> user_data($user_id);
            return $response;
        }
        return $client_request_access;
    }

    function users_search($query = "", $current_request_type){
        global $user;
        $client_request_access = client_request_access_status($current_request_type);

        if ($client_request_access["status"] == "OK") {
            $response = $user -> users_search($query);
            return $response;
        }
        return $client_request_access;
    }

    function users_search_v2($query = "", $current_request_type){
        global $user;
        $client_request_access = client_request_access_status($current_request_type);

        if ($client_request_access["status"] == "OK") {
            $response = $user -> users_search_v2($query);
            return $response;
        }
        return $client_request_access;
    }

    function users_top($users_count = 25, $current_request_type){
        global $user;        
        $client_request_access = client_request_access_status($current_request_type);

        if ($client_request_access["status"] == "OK") {            
            $response = $user -> users_top($users_count);
            return $response;
        }
        return $client_request_access;
    }

    function users_top_v2($users_count = 25, $current_request_type){
        global $user;
        $client_request_access = client_request_access_status($current_request_type);

        if ($client_request_access["status"] == "OK") {
            $response = $user -> users_top_v2($users_count);
            return $response;
        }
        return $client_request_access;
    }

    function users_official_top($users_count = 25, $current_request_type){
        global $user;
        $client_request_access = client_request_access_status($current_request_type);

        if ($client_request_access["status"] == "OK") {
            $response = $user -> users_official_top($users_count);
            return $response;
        }
        return $client_request_access;
    }

    function get_player($player_options){
        global $db, $log_file, $stream, $user;
        $ip = $user -> get_client_ip();
        $time = date("H:i");

        $stream_uuid = $player_options["stream_uuid"];
        $width = $player_options["width"];
        $height = $player_options["height"];
        $auto_play = $player_options["auto_play"];
        $mute = $player_options["mute"];
        if ($height/$width != 0.5625) {
            $width = 640;
            $height = 360;
        }
        
        $result = $db -> sql_query("SELECT `permissions` FROM `streams` LEFT JOIN `users` ON `users`.`id` = `streams`.`user_id` WHERE `uuid` = '$stream_uuid' AND `streams`.`is_excess` = '0' AND `streams`.`is_blocked` = '0' AND `streams`.`is_deleted` = '0' AND `users`.`is_blocked` = '0' AND `users`.`is_deleted` = '0'", "", "array");

        if (sizeof($result) > 0) {
            $data_url = $stream -> generate_stream_url($stream_uuid);
            $watch_permissions = unserialize($result[0]["permissions"]);
            if (sizeof($watch_permissions) == 0){ #Если владелец трансляции разрешил просмотр всем пользователям
                $response = "<object id=\"player\" width=\"$width\" height=\"$height\" codebase=\"http://fpdownload.macromedia.com/get/flashplayer/current/swflash.cab\"><param name=\"movie\" value=\"/player/stream_player.swf\"><param name=\"bgcolor\" value=\"#000\"><param name=\"allowFullScreen\" value=\"true\"><param name=\"wmode\" value=\"opaque\"><param name=\"allowScriptAccess\" value=\"sameDomain\"><param name=\"flashvars\" value=\"stream_url=$data_url&autoPlay=$auto_play&scaleMode=letterbox&xml_url=/player/params.xml\"><embed src=\"/player/stream_player.swf\" type=\"application/x-shockwave-flash\" bgcolor=\"#000\" wmode=\"direct\" loop=\"false\" quality=\"high\" allowScriptAccess=\"sameDomain\" allowFullScreen=\"true\" flashvars=\"stream_url=$data_url&autoPlay=$auto_play&scaleMode=letterbox&xml_url=/player/params.xml\" width=\"$width\" height=\"$height\" pluginspage=\"http://www.macromedia.com/go/getflashplayer\"></object>";
                error_log("[$time $ip] get_player: OK запрошен stream_uuid - $stream_uuid \n", 3, $log_file);
            } else {
                $response = "<h3>Error permissions</h3>";
                error_log("[$time $ip] get_player: ERROR нет прав доступа stream_uuid - $stream_uuid \n", 3, $log_file);
            }
        } else {
            $response = "<h3>Stream not found</h3>";
            error_log("[$time $ip] get_player: ERROR не найдена трансляция stream_uuid - $stream_uuid \n", 3, $log_file);
        }

        return $response;
    }

    function get_thumbs($stream_uuid = ""){
        global $stream, $archive_stream_snapshots_url, $media_query_log_file, $user;
        $response = array();
        $storage_server_data = $stream -> get_server($stream_uuid);
        if ($storage_server_data["status"] = "OK") {

            $storage_server = $storage_server_data["server"];
            $thumbs_query_url = "http://$storage_server:8082".$archive_stream_snapshots_url.$stream_uuid;
            $thumbs_array = json_decode(file_get_contents($thumbs_query_url), true);
            foreach ($thumbs_array as $value) {

                $timestamp = intval($value[0]); #Метка времени
                $image_data = $value[1];

                $thumb = array();
                array_push($thumb, $timestamp, $image_data);
                array_push($response, $thumb);
            }
        }

        if (sizeof($response) > 0) {
            $ip = $user -> get_client_ip();
            $time = date("H:i");
            error_log("[$time $ip] get_thumbs: OK\n", 3, $media_query_log_file);
        }

        return $response;
    }

    # Получение списка онлайн трансляций со всех доступных серверов
    function get_live_streams(){
        global $live_streams_url, $media_query_log_file, $project_options, $user;

        $response = array();

        $storage_servers_array = $project_options["storage_servers"];
        foreach ($storage_servers_array as $storage_server) {
            $live_streams_query_url = "http://$storage_server:8080".$live_streams_url;
            $live_streams_array = json_decode(file_get_contents($live_streams_query_url), true);
            foreach ($live_streams_array as $value) {
                $stream_uuid = $value[0];
                array_push($response, $stream_uuid);
            }
        }

        if (sizeof($response) > 0) {
            $ip = $user -> get_client_ip();
            $time = date("H:i");
            error_log("[$time $ip] get_live_streams: OK\n", 3, $media_query_log_file);
        }
        return $response;
    }

    # Получение списка записей со всех доступных серверов
    function get_archive_streams(){
        global $archive_streams_url, $media_query_log_file, $project_options, $user;

        $response = array();
        $storage_servers_array = $project_options["storage_servers"];
        foreach ($storage_servers_array as $storage_server) {
            $archive_streams_query_url = "http://$storage_server:8082".$archive_streams_url;
            $archive_streams_array = json_decode(file_get_contents($archive_streams_query_url), true);
            foreach ($archive_streams_array as $value) {
                $stream_uuid = $value[0];
                $start_date = intval($value[1]);
                $end_date= intval($value[2]);
                $response[$stream_uuid][] = $start_date;
                $response[$stream_uuid][] = $end_date;
            }
        }

        if (sizeof($response) > 0) {
            $ip = $user -> get_client_ip();
            $time = date("H:i");
            error_log("[$time $ip] get_archive_streams: OK\n", 3, $media_query_log_file);
        }
        return $response;
    }

    function get_archive_stream($stream_uuid = ""){
        global $archive_streams_url, $media_query_log_file, $stream, $user;
        $response = array();

        $storage_server_data = $stream -> get_server($stream_uuid);
        if ($storage_server_data["status"] = "OK") {
            $storage_server = $storage_server_data["server"];

            $archive_stream_query_url = "http://$storage_server:8082".$archive_streams_url."/".$stream_uuid;
            $archive_stream_array = json_decode(file_get_contents($archive_stream_query_url), true);

            if ($archive_stream_array) {
                $start_date = intval($archive_stream_array[0]);
                $end_date = intval($archive_stream_array[1]);
                $response[] = $start_date;
                $response[] = $end_date;
                $ip = $user -> get_client_ip();
                $time = date("H:i");
                error_log("[$time $ip] OK $archive_stream_query_url \n", 3, $media_query_log_file);
            }
        }

        return $response;
    }

    # Метод, необходимый для создания новой записи в файле лога (для мобильных приложений)
    function write_log($text = ""){
        global $app_log_file, $user;

        $ip = $user -> get_client_ip();
        $time = date("H:i");

        if (!empty($text)) {
            $response["status"] = "OK";
            error_log("[$time $ip] app_log: $text \n", 3, $app_log_file);
        } else {
            $response["status"] = "ERROR";
        }
        return $response;
    }

    function get_server_notices($current_request_type) {
        global $db;
        $client_request_access = client_request_access_status($current_request_type);

        if ($client_request_access["status"] == "OK") {
            
            $response = array();
            $result = $db -> sql_query("SELECT * FROM `notices` WHERE `deleted_date` = '0' AND `is_deleted` = '0'", "", "array");

            if (sizeof($result) > 0) {
                foreach ($result as $value) {
                    $notice_data = array();

                    $id = $value["id"];
                    $notice_text = $value["notice_text"];
                    $activated_date_timestamp = $value["activated_date"];
                    $deactivated_date_timestamp = $value["deactivated_date"];

                    array_push($notice_data, $id, $notice_text, $activated_date_timestamp, $deactivated_date_timestamp);
                    array_push($response, $notice_data);
                }
            }
            return $response;
        }
        return $client_request_access;
    }

    # Метод, необходимый для тестирования скорости интернета (для мобильных приложений), сохраняет полученный текст в файл, затем удаляет его
    function speed_test($content = ""){
        $file_name = gen_uuid(16);
        $file_path  = $_SERVER['DOCUMENT_ROOT']."/speed_test/".$file_name.".txt";
        $fp = fopen($file_path, "w");
        fwrite($fp, $content);
        fclose($fp);
        $response["status"] = $content;
        unlink($file_path);
        return $response;
    }

    # SUPPORT SERVICE
    function get_admin_data($users_count = 25, $current_request_type){
        global $support_service;
        $client_request_access = client_request_access_status($current_request_type);

        if ($client_request_access["status"] == "OK") {
            $response = $support_service -> get_admin_data($users_count);
            return $response;
        }
        return $client_request_access;
    }

    # CHAT BETWEEN USERS

    # Получение чатов пользователя с информацией о последнем сообщении
    function users_chat_get_chats($current_request_type) {
        global $users_chat;
        $client_request_access = client_request_access_status($current_request_type);

        if ($client_request_access["status"] == "OK") {
            $user_id = $client_request_access["user_id"];

            $response = $users_chat -> user_chats($user_id);
            return $response;
        }
        return $client_request_access;
    }

    # Получение информации о каждом чате из массива id чатов пользователя
    function users_chat_get_chats_array($data, $current_request_type) {
        global $users_chat;
        $client_request_access = client_request_access_status($current_request_type);

        if ($client_request_access["status"] == "OK") {
            $user_id = $client_request_access["user_id"];

            $response = $users_chat -> user_chats_array($data, $user_id);
            return $response;
        }
        
        return $client_request_access;
    }

    function users_chat_chats_etags($current_request_type) {
        global $users_chat;
        $client_request_access = client_request_access_status($current_request_type);

        if ($client_request_access["status"] == "OK") {
            $user_id = $client_request_access["user_id"];

            $response = $users_chat -> user_chats_etags($user_id);
            return $response;
        }
        return $client_request_access;
    }

    # Получение сообщений существующего диалога (мобильная версия)
    function users_chat_json_format($chat_id = 0, $current_request_type) {
        global $users_chat;
        $client_request_access = client_request_access_status($current_request_type);

        if ($client_request_access["status"] == "OK") {
            $user_id = $client_request_access["user_id"];

            $response = $users_chat -> users_chat_json_format($chat_id, $user_id);
            return $response;
        }
        return $client_request_access;
    }

    # Создание чата между пользователями или получение id существующего чата
    function users_chat_create ($user_id = 0, $current_request_type) {
        global $users_chat;
        $client_request_access = client_request_access_status($current_request_type);

        if ($client_request_access["status"] == "OK") {
            $requester_user_id = $client_request_access["user_id"];

            $response = $users_chat -> create($requester_user_id, $user_id);
            return $response;
        }
        return $client_request_access;
    }

    # Добавление сообщения в чат между пользователями
    function connect_to_users_chat_permission($chat_id = 0, $current_request_type) {
        global $users_chat;
        $client_request_access = client_request_access_status($current_request_type);

        if ($client_request_access["status"] == "OK") {
            $user_id = $client_request_access["user_id"];

            $response = $users_chat -> connect($chat_id, $user_id);
            return $response;
        }
        return $client_request_access;
    }
    
    # Добавление сообщения в чат между пользователями
    function users_chat_message_add($data = array(), $current_request_type) {
        global $users_chat;
        $client_request_access = client_request_access_status($current_request_type);

        if ($client_request_access["status"] == "OK") {
            $user_id = $client_request_access["user_id"];
            
            $response = $users_chat -> message_add($data, $user_id);
            return $response;
        }
        return $client_request_access;
    }    

    # "Удаление" чата. Если пользователь удалит чат, то он не будет отображаться в списке всех его чатов, но будет доступен его собеседникам. При начале переписки в этом чате будет вызван метод create, который вернет сущетсвующий chat_id, но история переспики не отобразится.
    function users_chat_hide($chat_id = 0, $current_request_type) {
        global $users_chat;
        $client_request_access = client_request_access_status($current_request_type);

        if ($client_request_access["status"] == "OK") {
            $user_id = $client_request_access["user_id"];

            $response = $users_chat -> chat_hide($chat_id, $user_id);
            return $response;
        }
        return $client_request_access;
    }

    # Добавление участника в беседу
    function users_chat_invite($data = array(), $current_request_type) {
        global $users_chat;
        $client_request_access = client_request_access_status($current_request_type);

        if ($client_request_access["status"] == "OK") {
            $user_id = $client_request_access["user_id"];

            $response = $users_chat -> users_chat_invite($data, $user_id);
            return $response;
        }
        return $client_request_access;
    }
    
}