<?php
header("Content-Type: application/json; charset=utf-8");
global $db;
$result["claims"] = 0;
$result["claims_comments"] = 0;

$result_claims = $db -> sql_query("SELECT*FROM `claims` WHERE `status_id` = '1' AND `is_deleted` = '0'", "", "array");
$result["claims"] = sizeof($result_claims);

$result_claims_comments = $db -> sql_query("SELECT*FROM `claims_comments` WHERE `status_id` = '1' AND `is_deleted` = '0'", "", "array");
$result["claims_comments"] = sizeof($result_claims_comments);
echo json_encode($result);
?>