<?php
header("Content-Type: application/json; charset=utf-8");
$query = $_POST["query"];
$user_id = $_SESSION["uid"];

global $action, $stream, $user, $users_chat;

switch ($query) {

    case "stream_data" :
        $stream_uuid = $_POST["stream_uuid"];
        $response = $stream -> stream_data($stream_uuid, $user_id);
        echo json_encode($response);
        break;

    case "user_data" :
        $requested_user_id = $_POST["user_id"];
        $response = $user -> user_data($requested_user_id);
        echo json_encode($response);
        break;

    case "users_array_data" :
        $users_id_array = json_decode($_POST["users_array_data"], true);
        $response = $user -> users_array_data($users_id_array);
        echo json_encode($response);
        break;

    case "clients_count" :
        $response = $stream -> get_streams_clients_count();
        echo json_encode($response);
        break;

    # Сообщения чата между пользователями
    case "user_chat_data" :
        $chat_id = $_POST["chat_id"];
        $response = $users_chat -> user_chat_html_format($chat_id, $user_id);
        echo json_encode($response);
        break;

    # "Удаление" чата. Если пользователь удалит чат, то он не будет отображаться в списке всех его чатов, но будет доступен его собеседникам.
    case "user_chat_hide" :
        $chat_id = $_POST["chat_id"];
        $response = $users_chat -> chat_hide($chat_id, $user_id);
        echo json_encode($response);
        break;

    # Получение списка пользователей, которых можно пригласить в чат (взаимные подписчики, за исключением участников чата)
    case "invite_users_list" :
        $chat_id = $_POST["chat_id"];
        $response = $users_chat -> invite_users_list($chat_id, $user_id);
        echo json_encode($response);
        break;
}
?>