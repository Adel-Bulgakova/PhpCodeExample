<?php
/*======= DB ========*/
$dbhost = "127.0.0.1";
$dbuname = "";
$dbpass = "";
$dbname = "";
$dbtype = "MySQL";

/*======= Lib ========*/
include "db/db.php";
include "lib/smspilot_1.9.10/smspilot.php";

include "api/v1/api.php";
include "lib/action.php";
include "lib/stream.php";
include "lib/support_service.php";
include "lib/user.php";
include "lib/users_chat.php";

$api = new api();
$action = new action();
$stream = new stream();
$support_service = new support_service();
$user = new user();
$users_chat = new users_chat();

$project_options = array(
    "admin_login" => "",
    "admin_pass" => "",
    "service_name" => "PROGECT_NAME",
    "company_name" => "PROGECT_NAME",
    "company_identify" => "PROGECT_NAME",
    "service_url" => "",
    "service_url_inner" => "",
    "service_support_phone" => "8-800-000-00-00",
    "appstore_link" => "https://itunes.apple.com/",
    "playstore_link" => "https://play.google.com/",
    "apn" => array(
        "cert" => "",
        "passphrase" => "",
        "root_cert" => ""
    ),
    "gsm" => array(
        "url" => "https://android.googleapis.com/gcm/send",
        "server_api_key" => ""

    ),
    "site_auth_login" => "PROGECT_NAME",
    "site_auth_pass" => "",
    "storage_servers" => array(
        ""
    ),
    "streams_filter_date" => 15, # Фильтр трансляций по дате (будут возвращены трансляции за последние 15 дней)
    "streams_filter_date_map" => 15, # Фильтр трансляций по дате для отображения на карте(будут возвращены трансляции за последние 15 дней)
    "streams_categories_count" => 2, # Допустимое количество категорий для одной трансляции
    "ws_urls" => array(
        "ws_stream" => "wss://$_SERVER[HTTP_HOST]:8000/websocket_server/",
        "ws_support" => "wss://$_SERVER[HTTP_HOST]:8888/websocket_server_support/",
        "ws_users_chat" => "wss://$_SERVER[HTTP_HOST]:8009/websocket_server_users_chat/"
    )
);

$log_file = "/www/logs/log_".date("dmY").".log";
$cron_log_file = "/www/logs/cron_".date("dmY").".log";
$ios_push_log_file = "/www/logs/ios_push_".date("dmY").".log";
$android_push_log_file = "/www/logs/android_push_".date("dmY").".log";
$media_query_log_file = "/www/logs/media_".date("dmY").".log"; # Запись запросов к медиа серверу
$app_log_file = "/www/logs/app_log_".date("dmY").".log";
$user_delete_log_file = "/www/logs/user_delete_log_".date("dmY").".log"; # Запись об удалении устаревших профилей пользователей

$profile_image_query = $project_options['service_url_inner']."api/v1/users/profile_image/";
$profile_images_dir = "/www/users/images/";
$tmp_profile_images_dir = "/www/users/tmp_images/";

$cache_snapshots_dir = "/www/cache/snapshots/";
$cache_thumbs_dir = "/www/cache/thumbs/";
$snapshot_query = $project_options['service_url_inner']."api/v1/streams/snapshot/";
$thumb_query = $project_options['service_url_inner']."api/v1/streams/thumb/1.0/";

# Запросы к медиасерверу
$live_stream_snapshot_url = "/admin/thumbs/publish/";
$archive_stream_snapshots_url = "/admin/db/snapshots/";
$archive_streams_url = "/admin/db/thumbs/depth";
$live_streams_url = "/server/streams/publish";
$stream_delete_from_media_server_url = "/admin/arc/del/";

define("SMSPILOT_API", "http://smspilot.ru/api.php");
define("SMSPILOT_APIKEY", "");
define("SMSPILOT_CHARSET", "UTF-8");
define("SMSPILOT_FROM", "");
