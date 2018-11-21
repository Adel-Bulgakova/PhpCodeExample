<?php
$login = $_POST["login"];
$password = $_POST["password"];

function user_auth($login = "", $password = ""){
	global $db, $user;
	$array["danger"] = "";
	$array["success"] = "";

	$ip = $user -> get_client_ip();
	$current_date = time();
	$password_md5 = md5($password);

	if ($login != "" AND $password != "") {
		$result_support_admin = $db -> sql_query("SELECT `id`, `password` FROM `support_service_admins` WHERE `login` = '$login' AND `is_deleted` = '0'", "", "array");
		$admin_password = $result_support_admin[0]["password"];
		$support_admin_id = $result_support_admin[0]["id"];

		if ($admin_password == $password_md5){
			$_SESSION["support_admin"] = $support_admin_id;
			$array["success"] = 1;
		} else {
			$array["danger"] = "Комбинация логина и пароля неверна";
		}
	} else {
		$array["danger"] = "Все поля обязательны для заполнения";
	}
	return $array;
}

$result = user_auth($login, $password);
header("Content-Type: application/json; charset=utf-8");
echo json_encode($result);
?>