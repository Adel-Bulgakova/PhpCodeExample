<?php
global $stream;
$chat_admin_id = 0;
if (isset($_SESSION["uid"]) AND !empty($_SESSION["uid"])) {
    $chat_admin_id = $_SESSION["uid"];
}
$data = $_GET;
$result = $stream -> chat_get($data, $chat_admin_id);
header("Content-Type: application/json; charset=utf-8");
echo json_encode($result);
?>