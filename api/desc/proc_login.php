<?php
session_start();
$login = $_POST["login"];
$password = $_POST["password"];

$api_auth_login = "";
$api_auth_pass = "";

$response = array();
if ($login == $api_auth_login && $password == $api_auth_pass){
	$_SESSION["api_user"] = "api_user";
	$response["status"] = "OK";
} else {
	$response["status"] = "ERROR";
	$response["message"] = "Комбинация логина и пароля неверна";
}
header("Content-Type: application/json; charset=utf-8");
echo json_encode($response);

?>