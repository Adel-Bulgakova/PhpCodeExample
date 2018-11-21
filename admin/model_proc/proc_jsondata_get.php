<?php
header("Content-Type: application/json; charset=utf-8");
$query = $_POST["query"];
$result = array();
switch ($query) {

    case "streams_all" :
        $result = get_streams_all();
        break;

    case "streams_excess" :
        $result = get_streams_excess();
        break;

    case "users_all" :
        $result = get_users_all();
        break;

    case "users_official" :
        $result = get_users_official();
        break;

    case "claims_on_streams" :
        $result = get_claims_on_streams();
        break;

    case "claims_on_comments" :
        $result = get_claims_on_comments();
        break;

    case "feedback" :
        $result = get_feedback();
        break;

    case "streams_tags" :
        $result = get_streams_tags();
        break;

    case "profiles_tags" :
        $result = get_profiles_tags();
        break;

    case "support_service_admins" :
        $result = get_support_service_admins();
        break;

    case "support_service_archive_chats" :
        $support_admin_id = $_POST["support_admin_id"];
        $result = get_support_service_archive_chats($support_admin_id);
        break;

    case "notices" :
        $result = get_notices();
        break;

    case "streams_categories" :
        $result = get_streams_categories();
        break;
}

function get_streams_all() {
    global $db, $stream, $snapshot_query, $user;
    $result_streams = $db -> sql_query("SELECT `streams`.`id` AS `stream_id`, `uuid`, `status`, `user_id`, `start_date`, `end_date`, `duration`, `streams`.`is_blocked` AS `stream_is_blocked`, `permissions` FROM `streams` LEFT JOIN `users` ON `streams`.`user_id` = `users`.`id` WHERE `streams`.`is_excess` = '0' AND `streams`.`is_deleted` = '0' AND `users`.`is_deleted` = '0'", "", "array");
    if (sizeof($result_streams) > 0){
        $streams = array();
        foreach ($result_streams as $value) {
            $stream_data = array();
            $stream_id = $value["stream_id"];
            $stream_uuid = $value["uuid"];
            $stream_url = $stream -> generate_stream_url($stream_uuid);
            $user_id = $value["user_id"];
            $stream_name = $stream -> stream_name($stream_uuid);
            $thumb = $snapshot_query.$stream_uuid;

            $stream_preview = "<a data-toggle=\"modal\" data-target=\".bs-modal-lg\" data-stream-uuid=\"$stream_uuid\"   data-stream-url=\"$stream_url\" data-stream-name=\"$stream_name\" class=\"stream_view\"><div class=\"screenshot\" style=\"background: #D6D6D6 url('$thumb') center center no-repeat;  background-size: cover;\"></div></a>";

            $permissions_data = unserialize($value["permissions"]);
            $permissions = "Public";
            if (sizeof($permissions_data) > 0){
                $permissions = "Private";
            }

            $user_profile = $user -> profile_name($user_id);
            $stream_info = "<b>" . $user_profile . "</b><br><br>" . $stream_name."<br><small>" . $permissions . "</small>";

            if (intval($value["end_date"]) == 0) {
                $status = "<button class=\"btn btn-danger btn-xs\">Live</button>";
            } else {
                $status = "<button class=\"btn btn-info btn-xs\">Archive</button>";
            }

            $start_date_int = intval($value["start_date"]);
            $end_date_int = intval($value["end_date"]);
            $start_date = "<span hidden=\"true\">$start_date_int</span>".date("d.m.Y H:i", $start_date_int);
            $end_date = "<span hidden=\"true\">$end_date_int</span>".date("d.m.Y H:i", $end_date_int);

            $result_notify = $db -> sql_query("SELECT * FROM `streams_notify_log` WHERE `stream_id` = '$stream_id'", "", "array");
            $notify_count = sizeof($result_notify);
            $action = "<button data-toggle=\"modal\" data-target=\".bs-modal-lg\" data-stream-id=\"$stream_id\" data-action=\"notify\" title=\"Уведомить пользователя о возможной блокировке трансляции\" class=\"btn-warning btn-xs stream_action\">Notify</button><br>Отправлено: $notify_count<br>";
            if ($value["stream_is_blocked"] == 1) {
                $action .= "<button data-toggle=\"modal\" data-target=\".bs-modal-lg\" data-stream-id=\"$stream_id\" data-action=\"unblock\" title=\"Заблокировать трансляцию\" class=\"btn-primary btn-xs stream_action\">Unblock</button>";
            } else {
                $action .= "<button data-toggle=\"modal\" data-target=\".bs-modal-lg\" data-stream-id=\"$stream_id\" data-action=\"block\" title=\"Разблокировать трансляцию\" class=\"btn-danger btn-xs stream_action\">Block</button>";
            }

            array_push($stream_data, $stream_preview, $stream_info, $stream_uuid, $status, $start_date, $end_date, $action);
            array_push($streams, $stream_data);
        }
        return $streams;
    }

    //ошибка обработки
    return "error";
}

function get_streams_excess() {
    global $db, $stream, $snapshot_query, $user;
    $result_streams = $db -> sql_query("SELECT `streams`.`id` AS `stream_id`, `uuid`, `url`, `status`, `user_id`, `start_date`, `end_date`, `streams`.`is_blocked` AS `stream_is_blocked`, `permissions` FROM `streams` LEFT JOIN `users` ON `streams`.`user_id` = `users`.`id` WHERE `streams`.`is_excess` = '1' AND `streams`.`is_deleted` = '0' AND `users`.`is_deleted` = '0'", "", "array");
    if (sizeof($result_streams) > 0){
        $streams = array();
        foreach ($result_streams as $value) {
            $stream_data = array();
            $stream_id = $value["stream_id"];
            $stream_uuid = $value["uuid"];
            $user_id = $value["user_id"];
            $stream_name = $stream -> stream_name($stream_uuid);
            $thumb = $snapshot_query.$stream_uuid;

            $stream_preview = "<a data-toggle=\"modal\" data-target=\".bs-modal-lg\" data-stream-uuid=\"$stream_uuid\" data-stream-name=\"$stream_name\" class=\"stream_view\"><div class=\"screenshot\" style=\"background: #D6D6D6 url('$thumb') center center no-repeat;  background-size: cover;\"></div></a>";

            $permissions_data = unserialize($value["permissions"]);
            $permissions = "Public";
            if (sizeof($permissions_data) > 0){
                $permissions = "Private";
            }

            $user_profile = $user -> profile_name($user_id);
            $stream_info = "<b>" . $user_profile . "</b><br><br>" . $stream_name."<br><small>" . $permissions . "</small>";

            $start_date_int = intval($value["start_date"]);
            $end_date_int = intval($value["end_date"]);
            $start_date = "<span hidden=\"true\">$start_date_int</span>".date("d.m.Y H:i", $start_date_int);
            $end_date = "<span hidden=\"true\">$end_date_int</span>".date("d.m.Y H:i", $end_date_int);

            array_push($stream_data, $stream_preview, $stream_info, $stream_uuid, $start_date, $end_date);
            array_push($streams, $stream_data);
        }
        return $streams;
    }

    //ошибка обработки
    return "error";
}

function get_users_all() {
    global $db, $user;
    $result_users = $db -> sql_query("SELECT * FROM `users` WHERE `is_official` = '0' AND `is_deleted` = '0'", "", "array");
    if (sizeof($result_users) > 0){
        $users = array();
        foreach ($result_users as $key => $value) {
            $user_data = array();

            $user_id = $value["id"];
            $login = $value["login"];
            $phone = $value["phone"];
            $email = $value["email"];
            $fb_id = $value["fb_id"];
            $vk_id = $value["vk_id"];
            $tw_id = $value["tw_id"];
            $last_connected_timestamp = $value["last_connected"];

            $profile_name = $user -> profile_name($user_id);
            $profile_image = $user -> profile_image_html($user_id);
            $profile_info = "<a href=\"/admin/index.php?route=page_user&user_id=$user_id\" target=\"_blank\"><div class=\"profile_info\">$profile_image<div class=\"profile_name\"><span>$profile_name</span></div></div><br><div>$login</div></a>";

            $social_ids = "VK id: $vk_id<br>Fb id: $fb_id<br>Tw id: $tw_id";

            $last_connected_day = date("d.m.Y", $last_connected_timestamp);
            $last_connected_time = date("H:i", $last_connected_timestamp);
            $last_connected = "<span hidden>$last_connected_timestamp</span>$last_connected_day<br>$last_connected_time";

            $result_devices = $db -> sql_query("SELECT * FROM `devices` WHERE `user_id` = '$user_id' AND `is_deleted` = '0'", "", "array");
            $devices_count = sizeof($result_devices);

            $status = $value["is_blocked"];
            if ($status == "1") {
                $status = "<i class=\"fa fa-circle waiting\"></i>";
                $action = "<a href=\"#\" data-toggle=\"modal\" data-target=\".bs-modal-lg\" data-user-id=\"$user_id\" data-action=\"unblock\">Разблокировать</a>";
            } else {
                $status = "<i class=\"fa fa-circle done\"></i>";
                $action = "<a href=\"#\" data-toggle=\"modal\" data-target=\".bs-modal-lg\" data-user-id=\"$user_id\" data-action=\"block\">Блокировать</a>";
            }

            array_push($user_data, $profile_info, $phone, $email, $social_ids, $last_connected, $devices_count, $status, $action);
            array_push($users, $user_data);
        }
        return $users;
    }

    //ошибка обработки
    return "error";
}

function get_users_official() {
    global $db, $user;
    $result_users = $db -> sql_query("SELECT * FROM `users` WHERE `is_official` = '1' AND `is_deleted` = '0'", "", "array");
    if (sizeof($result_users) > 0 AND $result_users[0] != ""){
        $users = array();
        foreach ($result_users as $value) {
            $user_data = array();

            $user_id = $value["id"];
            $login = $value["login"];
            $email = $value["email"];
            $fb_id = $value["fb_id"];
            $vk_id = $value["vk_id"];
            $tw_id = $value["tw_id"];

            $profile_name = $user -> profile_name($user_id);
            $profile_image = $user -> profile_image_html($user_id);
            $profile_info = "<div class=\"profile_info\">$profile_image<div class=\"profile_name\"><span>$profile_name</span></div></div><br><div>$login</div>";

            $social_ids = "VK id: $vk_id<br>Fb id: $fb_id<br>Tw id: $tw_id";
            $last_connected = date("d.m.Y H:i", $value["last_connected"]);

            $result_devices = $db -> sql_query("SELECT * FROM `devices` WHERE `user_id` = '$user_id' AND `is_deleted` = '0'", "", "array");
            $devices_count = sizeof($result_devices);

            $status = $value["is_check_official"];
            $action = "";
            if ($status == "0") {
                $status = "<i class=\"fa fa-circle waiting\">&nbsp;&nbsp;Ожидает подтверждения</i>";
                $action = "<a href=\"#\" data-toggle=\"modal\" data-target=\".bs-modal-lg\" data-user-id=\"$user_id\" data-action=\"check\">Подтвердить</a>";
            } else {
                $status = "<i class=\"fa fa-circle done\">&nbsp;&nbsp;Подтвержден</i>";
                $action = "<a href=\"#\" data-toggle=\"modal\" data-target=\".bs-modal-lg\" data-user-id=\"$user_id\" data-action=\"cancel\">Отменить</a>";
            }

            array_push($user_data, $profile_info, $email, $social_ids, $last_connected, $devices_count, $status, $action);
            array_push($users, $user_data);
        }
        return $users;
    }

    //ошибка обработки
    return "error";
}

function get_claims_on_streams() {
    global $db, $stream, $snapshot_query, $user;
    $result_claims = $db -> sql_query("SELECT * FROM `claims` WHERE `is_deleted` = '0'", "", "array");
    if (sizeof($result_claims) > 0){
        $claims = array();
        foreach ($result_claims as $value) {
            $claim = array();
            $watcher_id = $value["by_user_id"];
            if (user($watcher_id)) {
                $claim_id = $value["id"];
                $status = $value["status_id"];

                $stream_id = $value["stream_id"];

                $watcher_name = $user -> profile_name($watcher_id);
                $watcher_image = $user -> profile_image_html($watcher_id);
                $watcher_profile_info = "<div class=\"profile_info\">$watcher_image<div class=\"profile_name\"><span>$watcher_name</span></div></div>";

                $created_date = date("d.m.Y H:i", $value["created_date"]);

                $result_stream = $db -> sql_query("SELECT * FROM `streams` WHERE `id` = '$stream_id' AND `is_excess` = '0' AND `is_deleted` = '0'", "", "array");
                if (sizeof($result_stream) > 0){
                    $stream_uuid = $result_stream[0]["uuid"];
                    $stream_url = $stream -> generate_stream_url($stream_uuid);
                    $stream_name = $stream -> stream_name($stream_uuid);

                    $hero_id = $result_stream[0]["user_id"];
                    $hero_name = $user -> profile_name($hero_id);
                    $hero_image = $user -> profile_image_html($hero_id);
                    $hero_profile_info = "<div class=\"profile_info\">$hero_image<div class=\"profile_name\"><span>$hero_name</span></div></div>";

                    $snapshot = $snapshot_query.$stream_uuid;

                    $stream_preview = "<a href=\"#\" data-toggle=\"modal\" data-target=\".bs-modal-lg\" data-stream-uuid=\"$stream_uuid\"   data-stream-url=\"$stream_url\" data-stream-name=\"$stream_name\" class=\"stream_view\"><div class=\"screenshot\" style=\"background: url('$snapshot') center center no-repeat;  background-size: cover;\"></div></a>";

                    $stream_info = "<div>$stream_name</div><br>$hero_profile_info";

                    if ($status == "1") { #Жалоба ожидает рассмотрения
                        $status = "Ожидает рассмотрения";
                        $action = "<div class=\"actions\" data-claim-id=\"$claim_id\" data-stream-id=\"$stream_id\">
                            <a data-toggle=\"modal\" data-target=\".bs-modal-lg\" data-action=\"block\"><i class=\"fa fa-lock fa-2x\"></i>&nbsp;&nbsp;Заблокировать устройство</i></a><br>
                            <a data-toggle=\"modal\" data-target=\".bs-modal-lg\" data-action=\"reject\"><i class=\"fa fa-thumbs-down fa-2x\"></i>&nbsp;&nbsp;Отклонить жалобу</a><br>
                            <a data-toggle=\"modal\" data-target=\".bs-modal-lg\" data-action=\"delete\"><i class=\"fa fa-remove fa-2x\"></i>&nbsp;&nbsp;Удалить жалобу</a>
                        </div>";
                    } else if ($status == "2") { #Устройство заблокировано
                        $status = "Устройство заблокировано";
                        $action = "<div class=\"actions\" data-claim-id=\"$claim_id\" data-stream-id=\"$stream_id\">
                            <i class=\"fa fa-lock fa-2x\"></i>&nbsp;&nbsp;Устройсто заблокировано
                        </div>";
                    } else { #Жалоба отклонена (status = 3)
                        $status = "Жалоба отклонена";
                        $action = "<div class=\"actions\" data-claim-id=\"$claim_id\" data-stream-id=\"$stream_id\">
                            <i class=\"fa fa-thumbs-down fa-2x\"></i>&nbsp;&nbsp;Жалоба отклонена
                        </div>";
                    }

                    array_push($claim, $stream_preview, $stream_info, $watcher_profile_info, $created_date, $status, $action);
                    array_push($claims, $claim);
                }
            }
        }
        return $claims;
    }

    //ошибка обработки
    return "error";
}

function get_claims_on_comments() {
    global $db, $user;

    $result_claims_comments = $db -> sql_query("SELECT `claims_comments`.`id` AS `claim_comment_id`, `claims_comments`.`comment_id` AS `comment_id`, `claims_comments`.`by_user_id` AS `watcher_id`, `claims_comments`.`created_date` AS `claim_created_date`, `status_id`, `users_actions_log`.`comment` AS `text`, `users_actions_log`.`user_id` AS `author_id` FROM `claims_comments` LEFT JOIN `users_actions_log` ON `claims_comments`.`comment_id` = `users_actions_log`.`id` WHERE `users_actions_log`.`users_actions_id` = '5'", "", "array");

    if (sizeof($result_claims_comments) > 0){
        $claims_comments = array();
        foreach ($result_claims_comments as $value) {
            $claim_comment = array();

            $author_id = $value["author_id"];
            $watcher_id = $value["watcher_id"];
            if (user($watcher_id) AND user($author_id)) {
                $claim_comment_id = $value["claim_comment_id"];
                $text = $value["text"];

                $author_name = $user -> profile_name($author_id);
                $author_image = $user -> profile_image_html($author_id);
                $author_profile_info = "<div class=\"profile_info\">$author_image<div class=\"profile_name\"><span>$author_name</span></div></div>";

                $watcher_name = $user ->profile_name($watcher_id);
                $watcher_image = $user -> profile_image_html($watcher_id);
                $watcher_profile_info = "<div class=\"profile_info\">$watcher_image<div class=\"profile_name\"><span>$watcher_name</span></div></div>";

                $claim_created_date = date("d.m.Y H:i", $value["claim_created_date"]);
                $claim_comment_status = $value["status_id"];
                $action = '';


                $status = '';
                array_push($claim_comment, $text, $author_profile_info, $watcher_profile_info, $claim_created_date, $status, $action);
                array_push($claims_comments, $claim_comment);
            }
        }

        return $claims_comments;
    }

    //ошибка обработки
    return "error";
}

function get_feedback() {
    global $db, $user;

    $result_feedbacks = $db -> sql_query("SELECT `feedback_text`, `users_feedback`.`created_date` AS `feedback_created_date`, `device_model`, `operating_system`, `users`.`id` AS `userId`, `users`.`name` AS `user_name` FROM `users_feedback` LEFT JOIN `devices` ON `users_feedback`.`device_id` = `devices`.`id` LEFT JOIN `users` ON `users`.`id` = `devices`.`user_id` WHERE `feedback_text` != ''", "", "array");

    if (sizeof($result_feedbacks) > 0){
        $feedbacks = array();
        foreach ($result_feedbacks as $value) {
            $feedback = array();

            $user_id = $value["userId"];
            $feedback_text = $value["feedback_text"];
            $device_model = $value["device_model"];
            $operating_system = $value["operating_system"];

            $user_name = $user -> profile_name($user_id);
            $user_image = $user -> profile_image_html($user_id);
            $user_profile_info = "<div class=\"profile_info\">$user_image<div class=\"profile_name\"><span>$user_name</span></div></div>";

            $feedback_created_date = date("d.m.Y H:i", $value["feedback_created_date"]);

            array_push($feedback, $user_profile_info, $feedback_text, $device_model, $operating_system, $feedback_created_date);
            array_push($feedbacks, $feedback);
        }

        return $feedbacks;
    }

    //ошибка обработки
    return "error";
}

function get_streams_tags() {
    global $db;

    $result_tags = $db -> sql_query("SELECT * FROM `streams_tags_data`", "", "array");
    if (sizeof($result_tags) > 0){
        $tags = array();
        foreach ($result_tags as $value) {
            $tag = array();
            $tag_id = $value["id"];
            $tag_name = $value["name"];
            $status = $value["is_disabled"];
            if ($status == "0") {
                $status = "<i class=\"fa fa-circle done\">&nbsp;&nbsp;Разрешен</i>";
                $action = "<a data-toggle=\"modal\" data-target=\".bs-modal-lg\" data-tag-id=\"$tag_id\" data-action=\"disable\">Запретить тег</a><br>";
            } else {
                $status = "<i class=\"fa fa-circle waiting\">&nbsp;&nbsp;Запрещен</i>";
                $action = "<a data-toggle=\"modal\" data-target=\".bs-modal-lg\" data-tag-id=\"$tag_id\" data-action=\"enable\">Разрешить тег</a><br>";
            }
            array_push($tag, $tag_name, $status, $action);
            array_push($tags, $tag);
        }

        return $tags;
    }

    //ошибка обработки
    return "error";
}

function get_profiles_tags() {
    global $db;

    $result_tags = $db -> sql_query("SELECT * FROM `profiles_tags_data`", "", "array");
    if (sizeof($result_tags) > 0){
        $tags = array();
        foreach ($result_tags as $value) {
            $tag = array();
            $tag_id = $value["id"];
            $tag_name = $value["name"];
            $status = $value["is_disabled"];
            if ($status == "0") {
                $status = "<i class=\"fa fa-circle done\">&nbsp;&nbsp;Разрешен</i>";
                $action = "<a data-toggle=\"modal\" data-target=\".bs-modal-lg\" data-tag-id=\"$tag_id\" data-action=\"disable\">Запретить тег</a><br>";
            } else {
                $status = "<i class=\"fa fa-circle waiting\">&nbsp;&nbsp;Запрещен</i>";
                $action = "<a data-toggle=\"modal\" data-target=\".bs-modal-lg\" data-tag-id=\"$tag_id\" data-action=\"enable\">Разрешить тег</a><br>";
            }
            array_push($tag, $tag_name, $status, $action);
            array_push($tags, $tag);
        }
        return $tags;
    }

    //ошибка обработки
    return "error";
}

function get_support_service_admins() {
    global $db, $user;
    $result_admins = $db -> sql_query("SELECT `id`, `login`, `name`, `email`, `comments`,
                                  CASE WHEN (SELECT COUNT(*) FROM `users_sessions_support_admins` WHERE `admin_id` = `support_service_admins`.`id` AND `end_date` = '0') > 0
                                      THEN 1
                                      ELSE 0
                                      END AS `is_online`  FROM `support_service_admins` WHERE `is_deleted` = '0'", "", "array");
    if (sizeof($result_admins) > 0){
        $admins = array();
        foreach ($result_admins as $value) {
            $admin = array();
            $support_service_admin_id = $value["id"];
            $admin_login = $value["login"];
            $admin_name = $value["name"];

            $profile_image = $user -> profile_image_html(0);
            $admin_profile = "<a href=\"/admin/index.php?route=page_support_service_admin&admin_id=$support_service_admin_id\" target=\"_blank\"><div class=\"profile_info\">$profile_image<div class=\"profile_name\"><span>$admin_name</span></div></div></a>";

            $email = $value["email"];
            $comments = $value["comments"];
            $status = $value["is_online"];
            if ($status == '0'){
                $status = "<i class=\"fa fa-circle waiting\"> Offline</i>";
            } else {
                $status = "<i class=\"fa fa-circle done\"> Online</i>";
            }

            $action = "<p data-admin-id=\"$support_service_admin_id\"><button class=\"btn btn-info btn-xs\" data-toggle=\"modal\" data-target=\".bs-modal-lg\" data-action=\"edit\">Редактировать</button>&nbsp;&nbsp;<button class=\"btn btn-danger btn-xs\"  data-toggle=\"modal\" data-target=\".bs-modal-lg\" data-action=\"delete\">Удалить</button></p>";

            array_push($admin, $admin_profile, $admin_login, $comments, $email, $status, $action);
            array_push($admins, $admin);
        }
        return $admins;
    }

    //ошибка обработки
    return "error";
}

function get_support_service_archive_chats ($support_admin_id = 0) {
    global $db, $user;

    $result = $db -> sql_query("SELECT `id`, `user_id`, `user_connect_time`, `admin_connect_time`, COUNT(`support_service_chats_msg`.`message`) AS `messages_count`, `closed_by` FROM `support_service_chats` LEFT JOIN `support_service_chats_msg` ON `support_service_chats`.`id` = `support_service_chats_msg`.`support_service_chat_id` WHERE `accepted_by_admin` ='$support_admin_id' AND `closed_by` != '' GROUP BY `id`", "", "array");

    if (sizeof($result) > 0){
        $chats = array();

        foreach ($result as $value) {

            $chat = array();
            $chat_id = $value["id"];
            $chat_data = "<div class=\"chat_id\" data-chat-id=\"$chat_id\">Чат №$chat_id</div>";

            $user_id = $value["user_id"];
            $profile_name = $user -> profile_name($user_id);
            $profile_image = $user -> profile_image_html($user_id);
            $profile_info = "<div class=\"profile_info\">$profile_image<div class=\"profile_name\"><span style=\"text-decoration:none\">$profile_name</span></div></div>";

            $user_connect_time = date("d.m.Y H:i", $value["user_connect_time"]);
            $admin_connect_time = date("d.m.Y H:i", $value["admin_connect_time"]);
            $messages_count = $value["messages_count"];
            $closed_by = $value["closed_by"];

            array_push($chat, $chat_data, $profile_info, $user_connect_time, $admin_connect_time, $messages_count, $closed_by);
            array_push($chats, $chat);
        }
        return $chats;
    }

    //ошибка обработки
    return "error";
}

function get_notices() {
    global $db;
    $result = $db -> sql_query("SELECT * FROM `notices` WHERE `deleted_date` = '0' AND `is_deleted` = '0'", "", "array");
    $current_date = time();
    if (sizeof($result) > 0){
        $notices = array();
        foreach ($result as $value) {
            $notice_data = array();

            $id = $value["id"];
            $notice_text = $value["notice_text"];
            $deactivated_date_timestamp =  $value["deactivated_date"];

            $activated_date = date("d.m.Y H:i", $value["activated_date"]);
            $deactivated_date = "Бессрочно";
            if ($deactivated_date_timestamp != 0) {
                $deactivated_date = date("d.m.Y H:i", $value["deactivated_date"]);
            }

            $status = "<button class=\"btn btn-info btn-xs\">Archive</button>";
            $notice_action = "";
            if ($deactivated_date_timestamp == 0 OR $deactivated_date_timestamp > $current_date) {
                $status = "<button class=\"btn btn-success btn-xs\">Active</button>";
                $notice_action = "<button class=\"btn btn-default btn-xs\" data-toggle=\"modal\" data-target=\".bs-modal-lg\" data-notice-id=\"$id\" data-action=\"send\">Send push</button>";
            }
            $action = "<button class=\"btn btn-warning btn-xs\" data-toggle=\"modal\" data-target=\".bs-modal-lg\" data-notice-id=\"$id\" data-action=\"edit\">Edit</button>   <button class=\"btn btn-danger btn-xs\" data-toggle=\"modal\" data-target=\".bs-modal-lg\" data-notice-id=\"$id\" data-action=\"delete\">Delete</button>";

            array_push($notice_data, $id, $notice_text, $activated_date, $deactivated_date, $status, $notice_action, $action);
            array_push($notices, $notice_data);
        }
        return $notices;
    }

    //ошибка обработки
    return "error";
}

# Получение списка категорий трансляций
function get_streams_categories() {
    global $db;
    $result = $db -> sql_query("SELECT * FROM `streams_categories_data` WHERE `is_deleted` = '0'", "", "array");
    if (sizeof($result) > 0){
        $streams_categories = array();
        foreach ($result as $value) {
            $categorу_data = array();

            $id = $value["id"];
            $name_ru = $value["name_ru"];
            $name_en =  $value["name_en"];
            $active_status =  $value["is_active"];
            $created_date = date("d.m.Y H:i", $value["created_date"]);

            $status = "<button class=\"btn btn-info btn-xs\">Archive</button>";
            if ($active_status == 1) {
                $status = "<button class=\"btn btn-success btn-xs\">Active</button>";
            }

            $action = "<button class=\"btn btn-warning btn-xs\" data-toggle=\"modal\" data-target=\".bs-modal-lg\" data-categorу-id=\"$id\" data-action=\"edit\">Edit</button>   <button class=\"btn btn-danger btn-xs\" data-toggle=\"modal\" data-target=\".bs-modal-lg\" data-categorу-id=\"$id\" data-action=\"delete\">Delete</button>";

            array_push($categorу_data, $name_ru, $name_en, $created_date, $status, $action);
            array_push($streams_categories, $categorу_data);
        }
        return $streams_categories;
    }

    //ошибка обработки
    return "error";
}


echo json_encode($result);
?>