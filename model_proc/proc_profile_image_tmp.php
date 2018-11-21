<?php
global $user;
$user_id = $_SESSION["uid"];
$file_data = $_FILES;
$result = $user -> image_tmp($file_data, $user_id);
header("Content-Type: application/json; charset=utf-8");
echo json_encode($result);
?>