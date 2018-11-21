<?php
$login = $_POST["login"];
$password = $_POST["password"];

function user_auth($login = "", $password = ""){
	global $project_options;
	$array = array();
	$array["danger"] = "";
	$array["success"] = "";
	if ($login != "" && $password != "") {
		if ($login == $project_options["admin_login"] && $password == $project_options["admin_pass"]){
			$_SESSION["super_user"] = 1;
			$array["success"] = 1;
		}else{
			$array["danger"] = "Комбинация логина и пароля неверна";
		}
	}else{
		$array["danger"] = "Все поля обязательны для заполнения";
	}
	ob_end_clean();
	return $array;
}

$result = user_auth($login, $password);
header("Content-Type: application/json; charset=utf-8");
echo json_encode($result);
?>