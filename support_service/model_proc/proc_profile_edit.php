<?php
$admin_id = $_POST["admin_id"];
$name = $_POST["name"];

function user_auth($admin_id = 0, $name = ""){
	global $db;
	$current_date = time();

	if (!empty($name)) {

		$result_support_admin = $db -> sql_query("SELECT `id` FROM `support_service_admins` WHERE `id` = '$admin_id' AND `is_deleted` = '0'", "", "array");
		if ($result_support_admin[0]["id"]) {
			$db -> sql_query("UPDATE `support_service_admins` SET `name` = '$name', `last_change_date` = '$current_date' WHERE  id = '$admin_id' AND is_deleted = '0'");
			$response["status"] = "OK";
			$response["message"] = "Информация успешно сохранена.";
		} else {
			$response["status"] = "ERROR";
			$response["message"] = "Ошибка выполнения запроса. Попробуйте позднее.";
		}
	} else {
		$response["status"] = "ERROR";
		$response["message"] = "Все поля обязательны для заполнения.";
	}
	return $response;
}

$result = user_auth($admin_id, $name);
header("Content-Type: application/json; charset=utf-8");
echo json_encode($result);
?>