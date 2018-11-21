<?php
header("Content-Type: application/json; charset=utf-8");
$action = $_POST["action"];
$admin_id = "";
if (isset($_POST["admin_id"]) AND $_POST["admin_id"] != "" AND $_POST["admin_id"] != 0) {
	$admin_id = $_POST["admin_id"];
}
$result = array();

switch ($action) {
	case "add" :
		$result = admin_add();
		break;
	case "edit" :
		$result = admin_edit($admin_id);
		break;
	case "delete" :
		$result = admin_delete($admin_id);
		break;
}

function admin_add(){
	global $db, $project_options;
	$array = array();
	$array["success"] = "";
	$array["danger"] = "";

	if (isset($_POST["admin_login"]) AND $_POST["admin_login"] != ""){
		$admin_login = prepair_str($_POST["admin_login"]);
	} else {
		$array["danger"][] = "Введите логин администратора";
	}

	if (isset($_POST["admin_name"]) AND $_POST["admin_name"] != ""){
		$admin_name = prepair_str($_POST["admin_name"]);
	} else {
		$array["danger"][] = "Введите имя администратора";
	}

	if (isset($_POST["password"]) AND $_POST["password"] != ""){
		$password = prepair_str($_POST["password"]);
		$password = md5($password);
	} else {
		$array["danger"][] = "Введите пароль администратора";
	}

	$comments = "";
	if (isset($_POST["comments"]) AND $_POST["comments"] != ""){
		$comments = prepair_str($_POST["comments"]);
	}

	$current_date = time();

	if (count($array["danger"]) > 0 AND $array["danger"] != "") {
		return ($array);
	} else {
		if ($res_add_admin = $db -> sql_query("INSERT INTO `support_service_admins` (`id`, `login`, `password`, `name`, `email`, `comments`, `created_date`, `last_change_date`, `is_online`, `is_deleted`) VALUES (NULL, '$admin_login', '$password', '$admin_name', '', '$comments', '$current_date', '', '0');")) {
			$array["success"] = "Администратор $admin_name успешно добавлен";
		} else {
			$array["danger"][] = "Ошибка добавления администратора";
		}
		return $array;
	}
}

function admin_edit($admin_id){
	global $db;
	$array = array();
	$array["success"] = "";
	$array["danger"] = "";

	$current_date = time();

	if (isset($_POST["admin_name"]) AND $_POST["admin_name"] != ""){
		$admin_name = prepair_str($_POST["admin_name"]);
	} else {
		$array["danger"][] = "Введите имя администратора";
	}

	$comments = "";
	if (isset($_POST["comments"]) AND $_POST["comments"] != ""){
		$comments = prepair_str($_POST["comments"]);
	}

	$email = "";
	if (isset($_POST["email"]) AND $_POST["email"] != ""){
		$email = prepair_str($_POST["email"]);
	}

	$password_sql = "";
	if (isset($_POST["new_password"]) AND $_POST["new_password"] != ""){
		$password = md5(prepair_str($_POST["new_password"]));
		$password_sql = "password = '$password', ";
	}

	if (count($array["danger"]) > 0 AND $array["danger"] != "") {
		return ($array);
	} else {
		if ($db -> sql_query("UPDATE support_service_admins SET $password_sql name = '$admin_name', email = '$email', comments = '$comments', last_change_date = '$current_date' WHERE id = '$admin_id' AND is_deleted = '0' LIMIT 1")) {
			$array["success"] = "Изменения сохранены";
		} else {
			$array["danger"][] = "Ошибка сохранения изменений";
		}
	}
	return $array;
}

function admin_delete($admin_id){
	global $db;
	$result_admins = $db -> sql_query("SELECT*FROM support_service_admins WHERE id = '$admin_id'", "", "array");
	if (count($result_admins) > 0 AND $result_admins[0] != ""){
		$db -> sql_query("UPDATE support_service_admins SET is_deleted = '1' WHERE id = '$admin_id' AND is_deleted = '0'");
		$result["html"] = "Администратор удален";
	} else {
		$result["html"] = "Администратор не найден";
	}
	return $result;
}

echo json_encode($result);
?>