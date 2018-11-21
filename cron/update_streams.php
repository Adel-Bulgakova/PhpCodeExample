<?php
require_once "/www/config.php";
require_once "/www/function.php";

global $archive_streams_url, $db, $live_streams_query, $live_streams_url, $media_query_log_file, $project_options;
$time = date("H:i:s");
$cron_log_file = "/www/logs/cron_".date("dmY").".log";
# Обновление записей в базе

# Поиск необновленных записей
$result_streams = $db -> sql_query("SELECT `uuid`, `storage_server` FROM `streams` WHERE `is_updated` = '0' AND `is_blocked` = '0' AND `is_deleted` = '0'", "", "array");
if (sizeof($result_streams) > 0) {

    $archive_streams_a = array();
    $live_streams_a = array();

    # Получение всех серверов записи
    $storage_servers_array = $project_options["storage_servers"];
    foreach ($storage_servers_array as $storage_server) {
        $archive_streams_query_url = "http://$storage_server:8082".$archive_streams_url;
        # Получение архивных записей текущего сервера
        $archive_streams = json_decode(file_get_contents($archive_streams_query_url, 0, stream_context_create(array("http" => array("timeout" => "3")))), true);
        if (sizeof($archive_streams) > 0){
            foreach ($archive_streams as $archive_stream) {
                $stream_uuid = prepair_str($archive_stream[0]);
                $start_date_ms = $archive_stream[1];
                $end_date_ms = $archive_stream[2];
                
                $stream_duration_ms = $end_date_ms - $start_date_ms;

                $archive_streams_a[$stream_uuid]["start_date_ms"] = $start_date_ms;
                $archive_streams_a[$stream_uuid]["end_date_ms"] = $end_date_ms;
                $archive_streams_a[$stream_uuid]["d_ms"] = $stream_duration_ms;
                $archive_streams_a[$stream_uuid]["d_float_s"] = floatval($stream_duration_ms/1000);
            }
            error_log("[$time] update_streams: $storage_server OK archive_streams\n", 3, $cron_log_file);
        } else {
            error_log("[$time] update_streams: $storage_server EMPTY archive_streams\n", 3, $cron_log_file);
        }

        # Получение live трансляций текущего сервера
        $live_streams_query_url = "http://$storage_server:8080".$live_streams_url;
        $live_streams_data = file_get_contents($live_streams_query_url, 0, stream_context_create(array("http" => array("timeout" => "3"))));
        if ($live_streams_data) {
            $live_streams = json_decode($live_streams_data, true);
            foreach ($live_streams as $live) {
                $stream_uuid = prepair_str($live[0]);
                $live_streams_a[] = $stream_uuid;
            }
            error_log("[$time] update_streams: $storage_server OK live_streams\n", 3, $cron_log_file);
        } else {
            error_log("[$time] update_streams: $storage_server EMPTY live_streams\n", 3, $cron_log_file);
        }
    }

    # Перебираем трансляции в базе (is_deleted = 0, is_updated = 0, is_blocked = 0)
    foreach ($result_streams as $stream) {
        $stream_uuid = $stream["uuid"];
        $storage_server = $stream["storage_server"];

        #Поиск данной трансляции в массиве live трансляций
        if (in_array($stream_uuid, $live_streams_a)) {
            $db -> sql_query("UPDATE `streams` SET `is_excess` = '0' WHERE `uuid` = '$stream_uuid' AND `is_updated` = '0'");
            error_log("[$time] update_streams: $stream_uuid live\n", 3, $cron_log_file);
        } else if (array_key_exists($stream_uuid, $archive_streams_a)){
            #Поиск данной трансляции в массиве архивных трансляций
            $start_date_ms =  $archive_streams_a[$stream_uuid]["start_date_ms"];
            $end_date_ms = $archive_streams_a[$stream_uuid]["end_date_ms"];

            $stream_duration_ms = $archive_streams_a[$stream_uuid]["d_ms"];#Продолжительность трансляции в миллисекундах для формирования url
            $stream_duration_float_s = $archive_streams_a[$stream_uuid]["d_float_s"];#Продолжительность трансляции в секундах записи в базу

            $start_date_float_s = $start_date_ms/1000;
            $end_date_float_s = $end_date_ms/1000;
            $etag_stream = gen_uuid(12);
            
            # Отмечаем обновленную архивную трансляцию is_updated = '1', обновляем данные
            if ($db -> sql_query("UPDATE `streams` SET `status` = '0', `start_date` = '$start_date_float_s', `end_date` = '$end_date_float_s', `duration` = '$stream_duration_float_s', `is_updated` = '1', `is_excess` = '0', `etag_stream` = '$etag_stream' WHERE `uuid` = '$stream_uuid'")) {
                error_log("[$time] update_streams: $stream_uuid обновлен\n", 3, $cron_log_file);
            } else {
                error_log("[$time] update_streams: archive sql_error не выполнено обновление потока $stream_uuid\n", 3, $cron_log_file);
            }

        } else {
            $db -> sql_query("UPDATE `streams` SET `is_excess` = '1' WHERE `uuid` = '$stream_uuid' AND `is_updated` = '0' AND `is_deleted` = '0'");
//            if ($db -> sql_query("UPDATE `streams` SET `is_excess` = '1' WHERE `uuid` = '$stream_uuid' AND `is_updated` = '0' AND `is_deleted` = '0'")) {
//                error_log("[$time] update_streams: трансляция $stream_uuid отстутствует на медиасервере\n", 3, $cron_log_file);
//            } else {
//                error_log("[$time] update_streams: sql_error не выполнено удаление потока $stream_uuid\n", 3, $cron_log_file);
//            }
        }
    }

//    error_log("[$time] update_streams: список потоков обновлен\n", 3, $cron_log_file);

} else {
    error_log("[$time] update_streams: список необновленных потоков в базе пуст\n", 3, $cron_log_file);
}
?>