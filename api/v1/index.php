<?php
include "/www/config.php";
include "s/www/function.php";
global $action, $api, $db, $stream, $project_options, $support_service, $user, $users_chat;

$api_url_path = "https://" . $_SERVER["HTTP_HOST"];
$base_storage_server = $project_options["storage_servers"][0];

if (isset($_GET["request"])) {
    $query = explode("/", rtrim($_GET["request"], "/"));

    switch ($query[0]) {
        
        case "auth":
            switch ($query[1]) {
                #Получение зарегистрированными пользователями токена доступа к api проекта
                case "access_token" :
                    echo json_encode($api -> access_token_get());
                    exit();
                    break;

                #Получение кода доступа к api проекта в методах авторизации, создания новых пользователей, запроса кода по номеру телефона
                case "access_code" :
                    echo json_encode($api -> access_code_get());
                    exit();
                    break;
            }
            break;

        case "streams" :
            $current_request_type = $query[0]."/".$query[1];
            switch ($query[1]) {
                case "all_streams" :
                    echo json_encode($api -> all_streams());
                    exit();
                    break;
                
                case "search" :
                    echo json_encode($api -> streams_search(prepair_str($query[2]), $current_request_type));
                    exit();
                    break;

                case "search_v2" :
                    echo json_encode($api -> streams_search_v2(prepair_str($query[2]), $current_request_type));
                    exit();
                    break;

                case "top" :
                    echo json_encode($api -> streams_top(prepair_str($query[2]), $current_request_type));
                    exit();
                    break;

                case "top_v2" :
                    echo json_encode($api -> streams_top_v2(prepair_str($query[2]), $current_request_type));
                    exit();
                    break;

                case "official" :
                    echo json_encode($api -> streams_official(prepair_str($query[2]), $current_request_type));
                    exit();
                    break;

                case "official_v2" :
                    echo json_encode($api -> streams_official_v2(prepair_str($query[2]), $current_request_type));
                    exit();
                    break;

                case "stream" :
                    echo json_encode($api -> stream(prepair_str($query[2]), $current_request_type));
                    exit();
                    break;

                case "ws_stream" :
                    echo json_encode($api -> ws_stream(prepair_str($query[2]), $current_request_type));
                    exit();
                    break;

                case "streams_array" :
                    echo json_encode($api -> streams_array(json_decode(file_get_contents('php://input'), true), $current_request_type));
                    exit();
                    break;
					
				case "streams_etags" :
                    echo json_encode($api -> streams_etags(json_decode(file_get_contents('php://input'), true), $current_request_type));
                    exit();
                    break;
                
                case "stream_start" :
                    echo json_encode($stream -> stream_start_app(json_decode(file_get_contents('php://input'), true), $current_request_type));
                    exit();
                    break;

                case "stream_edit" :
                    echo json_encode($stream -> stream_edit_app(prepair_str($query[2]), json_decode(file_get_contents('php://input'), true), $current_request_type));
                    exit();
                    break;

                case "stream_cancel" :
                    echo json_encode($stream -> stream_cancel(prepair_str($query[2]), $current_request_type));
                    exit();
                    break;

                case "stream_delete" :
                    echo json_encode($api -> stream_delete(prepair_str($query[2]), $current_request_type));
                    exit();
                    break;

                case "comments_get" :
                    echo json_encode($stream -> recent_chat_get_app(prepair_str($query[2]), $current_request_type));
                    exit();
                    break;

                case "clients_count" :
                    echo json_encode($api -> get_streams_clients_count($current_request_type));
                    exit();
                    break;
                
                case "clients" :
                    echo json_encode($api -> get_stream_clients(prepair_str($query[2]), $current_request_type));
                    exit();
                    break;

                case "connect" :
                    echo json_encode($api -> connect(json_decode(file_get_contents('php://input'), true), $current_request_type));
                    exit();
                    break;

                case "loc" :
                    echo json_encode($api -> update_loc(json_decode(file_get_contents('php://input'), true), $current_request_type));
                    exit();
                    break;

                case "ori" :
                    echo json_encode($api -> update_ori(json_decode(file_get_contents('php://input'), true), $current_request_type));
                    exit();
                    break;

                case "heading" :
                    echo json_encode($api -> update_heading(json_decode(file_get_contents('php://input'), true), $current_request_type));
                    exit();
                    break;

                case "altitude" :
                    echo json_encode($api -> update_altitude(json_decode(file_get_contents('php://input'), true), $current_request_type));
                    exit();
                    break;

                case "snapshot" :
                    header('Content-Type: image/jpeg');
                    echo $api -> get_stream_snapshot(prepair_str($query[2]));
                    exit();
                    break;

                case "thumb" :
                    header("Content-Type: image/jpeg");
                    echo $api -> get_stream_thumb(prepair_str($query[2]), prepair_str($query[3]));
                    exit();
                    break;

                case "streams_in_map_rect" :
                    echo json_encode($api -> get_streams_in_map_rect(prepair_str($query[2]), prepair_str($query[3]), prepair_str($query[4]), prepair_str($query[5]), $current_request_type));
                    exit();
                    break;

                case "categories" :
                    echo json_encode($stream -> get_available_streams_categories());
                    exit();
                    break;

                default :
                    echo json_encode($api -> streams());
                    exit();
                    break;
            }
            break;

        case "users":
            $current_request_type = $query[0]."/".$query[1];
            switch ($query[1]) {
                case "search" :
                    echo json_encode($api -> users_search(prepair_str($query[2]), $current_request_type));
                    exit();
                    break;

                case "search_v2" :
                    echo json_encode($api -> users_search_v2(prepair_str($query[2]), $current_request_type));
                    exit();
                    break;

                case "top" :
                    echo json_encode($api -> users_top(prepair_str($query[2]), $current_request_type));
                    exit();
                    break;

                case "top_v2" :
                    echo json_encode($api -> users_top_v2(prepair_str($query[2]), $current_request_type));
                    exit();
                    break;

                case "official_top" :
                    echo json_encode($api -> users_official_top(prepair_str($query[2]), $current_request_type));
                    exit();
                    break;

                case "users_array" :
                    echo json_encode($api -> users_array(json_decode(file_get_contents('php://input'), true), $current_request_type));
                    exit();
                    break;

                case "users_array2" :
                    echo json_encode($api -> users_array2(json_decode(file_get_contents('php://input'), true), $current_request_type));
                    exit();
                    break;

                case "users_etags" :
                    echo json_encode($api -> users_etags(json_decode(file_get_contents('php://input'), true), $current_request_type));
                    exit();
                    break;
                
                case "user" :
                    echo json_encode($api -> user(prepair_str($query[2]), $current_request_type));
                    exit();
                    break;

                case "auth_vk" :
                    echo json_encode($user -> auth_vk_app(json_decode(file_get_contents('php://input'), true), $current_request_type));
                    exit();
                    break;

                case "auth_fb" :
                    echo json_encode($user -> auth_fb_app(json_decode(file_get_contents('php://input'), true), $current_request_type));
                    exit();
                    break;

                case "auth_tw" :
                    echo json_encode($user -> auth_tw_app(json_decode(file_get_contents('php://input'), true), $current_request_type));
                    exit();
                    break;

                case "hash_generate" :
                    echo json_encode($api -> hash_generate(json_decode(file_get_contents('php://input'), true), $current_request_type));
                    exit();
                    break;

                case "user_add" :
                    echo json_encode($user -> user_add_app(json_decode(file_get_contents('php://input'), true), $current_request_type));
                    exit();
                    break;

                case "user_logout" :
                    echo json_encode($user -> user_logout_app($current_request_type));
                    exit();
                    break;

                case "user_logout_all_devices" :
                    echo json_encode($user -> user_logout_all_devices($current_request_type));
                    exit();
                    break;

                case "user_edit" :
                    echo json_encode($user -> user_edit_app(json_decode(file_get_contents('php://input'), true), $current_request_type));
                    exit();
                    break;

                case "user_recovery" :
                    echo json_encode($user -> user_recovery(json_decode(file_get_contents('php://input'), true), $current_request_type));
                    exit();
                    break;

                case "user_delete" :
                    echo json_encode($user -> user_delete(prepair_str($query[2]), $current_request_type));
                    exit();
                    break;

                case "profile_image" :
                    header('Content-Type: image/jpeg');
                    echo $user -> profile_image_get(prepair_str($query[2]));
                    exit();
                    break;

                case "devices" :
                    echo json_encode($user -> devices(prepair_str($query[2])), $current_request_type);
                    exit();
                    break;
                
                case "device_block" :
                    echo json_encode($user -> device_block(json_decode(file_get_contents('php://input'), true), $current_request_type));
                    exit();
                    break;
                
                case "device_settings_edit" :
                    echo json_encode($user -> device_settings_edit(json_decode(file_get_contents('php://input'), true), $current_request_type));
                    exit();
                    break;

                case "feedback" :
                    echo json_encode($user -> feedback_add(json_decode(file_get_contents('php://input'), true), $current_request_type));
                    exit();
                    break;

                case "support_service_chats" :
                    echo json_encode($user -> support_service_chats_get(prepair_str($query[2]), $current_request_type));
                    exit();
                    break;

                case "support_service_chats2" :
                    echo json_encode($user -> support_service_chats_get2(prepair_str($query[2]), $current_request_type));
                    exit();
                    break;
                
                default :
                    echo "";
                    exit();
                    break;
            }
            break;

        case "users_chat" :

            $current_request_type = $query[0]."/".$query[1];
            switch ($query[1]) {

                case "chats" :
                    echo json_encode($api -> users_chat_get_chats($current_request_type));
                    exit();
                    break;

                case "chats_array" :
                    echo json_encode($api -> users_chat_get_chats_array(json_decode(file_get_contents('php://input'), true), $current_request_type));
                    exit();
                    break;                

                case "chats_etags" :
                    echo json_encode($api -> users_chat_chats_etags($current_request_type));
                    exit();
                    break;

                case "chat" :
                    echo json_encode($api -> users_chat_json_format(prepair_str($query[2]), $current_request_type));
                    exit();
                    break;

                case "create" :
                    echo json_encode($api -> users_chat_create(prepair_str($query[2]), $current_request_type));
                    exit();
                    break;
                
                case "connect" :
                    echo json_encode($api -> connect_to_users_chat_permission(prepair_str($query[2]), $current_request_type));
                    exit();
                    break;

                case "message_add" :
                    echo json_encode($api -> users_chat_message_add(json_decode(file_get_contents('php://input'), true), $current_request_type));
                    exit();
                    break;                     

                case "hide" :
                    echo json_encode($api -> users_chat_hide(prepair_str($query[2]), $current_request_type));
                    exit();
                    break;                

                case "invite" :
                    echo json_encode($api -> users_chat_invite(json_decode(file_get_contents('php://input'), true), $current_request_type));
                    exit();
                    break;
                
                default :
                    echo "";
                    exit();
                    break;
            }
            break;      
                    
        case "actions" :
            $current_request_type = $query[0]."/".$query[1];
            switch ($query[1]) {
                case "like" :
                    echo json_encode($action -> like(json_decode(file_get_contents('php://input'), true), $current_request_type));
                    exit();
                    break;
                
                case "follow" :                    
                    echo json_encode($action -> follow(json_decode(file_get_contents('php://input'), true), $current_request_type));
                    exit();
                    break;

                case "follow2" :
                    if (array_key_exists(4, $query)) {
                        $lang = prepair_str($query[4]);
                    } else {
                        $lang = "ru";
                    }
                    echo json_encode($action -> follow2(prepair_str($query[2]), prepair_str($query[3]), $lang));
                    exit();
                    break;

                case "block" :
                    echo json_encode($action -> block(json_decode(file_get_contents('php://input'), true), $current_request_type));
                    exit();
                    break;

                case "claim" :
                    echo json_encode($action -> claim(prepair_str($query[2]), prepair_str($query[3]), $current_request_type));
                    exit();
                    break;

                case "claim_comment" :
                    echo json_encode($action -> claim_comment(prepair_str($query[2]), $current_request_type));
                    exit();
                    break;

                case "comment" :
                    echo json_encode($action -> comment_add(json_decode(file_get_contents('php://input'), true), $current_request_type));
                    exit();
                    break;

                case "message_to_support" :
                    echo json_encode($action -> message_to_support(json_decode(file_get_contents('php://input'), true), $current_request_type));
                    exit();
                    break;

                default :
                    echo "The request failed";
                    exit();
                    break;
            }
            break;

        case "media" :
            switch ($query[1]) {
                case "server" :
                    echo json_encode($stream -> get_server(prepair_str($query[2])));
                    exit();
                    break;

                case "thumbs" :
                    echo json_encode($api -> get_thumbs(prepair_str($query[2])));
                    exit();
                    break;

                case "live" :
                    echo json_encode($api -> get_live_streams());
                    exit();
                    break;

                case "archive_streams" :
                    echo json_encode($api -> get_archive_streams());
                    exit();
                    break;

                case "archive" :
                    echo json_encode($api -> get_archive_stream(prepair_str($query[2])));
                    exit();
                    break;

                case "speed_test" :
                    echo json_encode($api -> speed_test(json_decode(file_get_contents('php://input'), true)));
                    exit();
                    break;

            }
            break;

        case "player" :
            $player_options = $_GET;
            echo $api -> get_player($player_options);
            exit();
            break;

        case "support_service":
            $current_request_type = $query[0]."/".$query[1];
            switch ($query[1]) {
                case "admins_status" :
                    echo json_encode($support_service -> admins_status($current_request_type));
                    exit();
                    break;

                case "admin" :
                    echo json_encode($api -> get_admin_data(prepair_str($query[2]), $current_request_type));
                    exit();
                    break;

                case "add_message" :
                    echo json_encode($support_service -> add_message(json_decode(file_get_contents('php://input'), true), $current_request_type));
                    exit();
                    break;

                case "client_connect_state" :
                    echo json_encode($support_service -> client_connect_to_chat_state(json_decode(file_get_contents('php://input'), true), $current_request_type));
                    exit();
                    break;

                case "support_admin_connect_state" :
                    echo json_encode($support_service -> support_admin_connect_state(json_decode(file_get_contents('php://input'), true), $current_request_type));
                    exit();
                    break;
            }
            break;

        # Ответ сервера на запрос от flash плеера
        case "flash_player":
            switch ($query[1]) {
                case "heading_dynamics" :
                    echo json_encode($stream -> get_heading_dynamics_for_flash_player(prepair_str($query[2])));
                    exit();
                    break;
                case "ori_dynamics" :
                    echo json_encode($stream -> get_ori_dynamics_for_flash_player(prepair_str($query[2])));
                    exit();
                    break;
            }
            break;

        case "server" :
            $current_request_type = $query[0]."/".$query[1];
            switch ($query[1]) {
                case "notices" :
                    echo json_encode($api -> get_server_notices($current_request_type));
                    exit();
                    break;
            }
            break;

        case "log" :
            echo json_encode($api -> write_log(json_decode(file_get_contents('php://input'), true)));
            exit();
            break;
        
        default :
            header('HTTP/1.1 404 Not Found');
            echo '404 Not Found or Unused Function';
            break;
    }
} else {
    header('HTTP/1.1 404 Not Found');
    echo '404 Not Found or Unused Function';
}
?>