<?php
global $stream;
$user_id = $_SESSION["uid"];
$data = $_POST;
$result = $stream -> stream_edit($data, $user_id);
header("Content-Type: application/json; charset=utf-8");
echo json_encode($result);
?>