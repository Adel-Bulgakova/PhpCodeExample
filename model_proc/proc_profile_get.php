<?php
global $user;
$data = $_GET;
$result = $user -> profile_html_get($data);
header("Content-Type: application/json; charset=utf-8");
echo json_encode($result);
?>