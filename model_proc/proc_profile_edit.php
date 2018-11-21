<?php
global $user;
$user_id = $_SESSION["uid"];
$data = $_POST;
$result = $user -> user_edit($data, $user_id);
header("Content-Type: application/json; charset=utf-8");
echo json_encode($result);
?>