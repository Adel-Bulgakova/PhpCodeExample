<?php
header("Content-Type: application/json; charset=utf-8");
$action = $_POST["action"];
$notice_id = 0;
if (isset($_POST["notice_id"]) AND $_POST["notice_id"] != "" AND $_POST["notice_id"] != 0) {
	$notice_id = $_POST["notice_id"];
}
$result = array();

switch ($action) {
	case "add" :
		$result = notice_add();
		break;
	case "edit" :
		$result = notice_edit($notice_id);
		break;
	case "send" :
		$result = notice_send($notice_id);
		break;
	case "delete" :
		$result = notice_delete($notice_id);
		break;
}

function notice_add(){
	global $db;
	$response = array();
	$expected_params = array();
	$current_date = time();

	if (isset($_POST["notice_text"]) AND $_POST["notice_text"] != ""){
		$notice_text = prepair_str($_POST["notice_text"]);
	} else {
		$expected_params[] = "текст уведомления";
	}

	if (isset($_POST["activated_date"]) AND $_POST["activated_date"] != ""){
		$activated_date = strtotime($_POST["activated_date"]);
	} else {
		$expected_params[] = "дата начала активности";
	}

	if (isset($_POST["without_expiration_param"]) AND $_POST["without_expiration_param"] == 1){
		$deactivated_date = 0;
	} else {
		if (isset($_POST["deactivated_date"]) AND $_POST["deactivated_date"] != ""){
			$deactivated_date = strtotime($_POST["deactivated_date"]);
		} else {
			$expected_params[] = "дата завершения активности";
		}
	}

	if (count($expected_params) > 0 ){
		$expected = implode(", ", $expected_params);
		$error = "Не получены параметры: $expected";
		$response["status"] = "ERROR";
		$response["message"] = $error;
		return $response;
	}

	if ($deactivated_date != 0 AND $activated_date >= $deactivated_date) {
		$response["status"] = "ERROR";
		$response["message"] = "Проверьте правильность дат активности уведомления";
		return $response;
	}

	if ($db -> sql_query("INSERT INTO `notices`(`id`, `notice_text`, `activated_date`, `deactivated_date`, `created_date`, `deleted_date`, `is_deleted`) VALUES (NULL, '$notice_text', '$activated_date', '$deactivated_date', '$current_date', '0', '0')")) {

		$response["status"] = "OK";
		$response["message"] = "Уведомление успешно создано";
		return $response;
	}

	$response["status"] = "ERROR";
	$response["message"] = "Ошибка создания уведомления. Попробуйте позднее.";
	return $response;

}

function notice_edit($notice_id = 0){
	global $db;

	$response = array();
	$expected_params = array();
	$current_date = time();

	if (isset($_POST["notice_text"]) AND $_POST["notice_text"] != ""){
		$notice_text = prepair_str($_POST["notice_text"]);
	} else {
		$expected_params[] = "текст уведомления";
	}

	if (isset($_POST["activated_date"]) AND $_POST["activated_date"] != ""){
		$activated_date = strtotime($_POST["activated_date"]);
	} else {
		$expected_params[] = "дата начала активности";
	}

	if (isset($_POST["without_expiration_param"]) AND $_POST["without_expiration_param"] == 1){
		$deactivated_date = 0;
	} else {
		if (isset($_POST["deactivated_date"]) AND $_POST["deactivated_date"] != ""){
			$deactivated_date = strtotime($_POST["deactivated_date"]);
		} else {
			$expected_params[] = "дата завершения активности";
		}
	}

	if (count($expected_params) > 0 ){
		$expected = implode(", ", $expected_params);
		$error = "Не получены параметры: $expected";
		$response["status"] = "ERROR";
		$response["message"] = $error;
		return $response;
	}

	if ($deactivated_date != 0 AND $activated_date >= $deactivated_date) {
		$response["status"] = "ERROR";
		$response["message"] = "Проверьте правильность дат активности уведомления";
		return $response;
	}

	if ($db -> sql_query("UPDATE `notices` SET `notice_text` = '$notice_text', `activated_date` = '$activated_date', `deactivated_date` ='$deactivated_date' WHERE `id` = '$notice_id'")) {

		$response["status"] = "OK";
		$response["message"] = "Изменения успешно сохранены.";
		return $response;
	}

	$response["status"] = "ERROR";
	$response["message"] = "Ошибка редактирования уведомления. Попробуйте позднее.";
	return $response;
}


function notice_send($notice_id = 0){
	global $db, $android_push_log_file, $ios_push_log_file, $log_file, $stream;
	$response = array();
	$current_date = time();
	$time = date("H:i");

	$result = $db -> sql_query("SELECT `notice_text` FROM `notices` WHERE `id` = '$notice_id' AND `deactivated_date` > '$current_date' AND `is_deleted` = '0'", "", "array");
	if (sizeof($result) > 0){

		$notice_text = $result[0]["notice_text"];

		# Cоздание массивов токенов устройств для ios и android
		$ios_tokens = array();
		$android_tokens = array();

		# Получение device_token всех устройств
		$result_tokens = $db -> sql_query("SELECT `device_token`, `operating_system` FROM `users` LEFT JOIN `devices` ON `users`.`id` = `devices`.`user_id` LEFT JOIN `users_sessions_devices` ON `devices`.`id` = `users_sessions_devices`.`device_id` WHERE `devices`.`device_token_is_correct` = '1' AND `devices`.`is_blocked` = '0' AND `devices`.`is_deleted` = '0' AND `users_sessions_devices`.`end_date` = '0' AND `users`.`is_blocked` = '0' AND `users`.`is_deleted` = '0' ", "", "array");

		foreach ($result_tokens as $token_data) {
			$device_token = $token_data["device_token"];
			$operating_system = $token_data["operating_system"];

			# Определение операционной системы устройства польователя и доступности токена устройства
			if (preg_match("/iOS/i",$operating_system)){
				$ios_tokens[] = $device_token;
			} else if (preg_match("/Android/i",$operating_system)) {
				$android_tokens[] = $device_token;
			}
		}

		if (sizeof($ios_tokens) > 0){
			$text = $notice_text;
			$custom_properties = array(
				"act" => "2",
				"notice_id" => $notice_id
			);
			try {
				$stream -> ios_send_push($ios_tokens, $text, $custom_properties);
			} catch (Exception $e) {
				error_log("[$time] push_notification_notice: EXCEPTION  notice_id = $notice_id " . $e->getMessage(). "\n", 3, $ios_push_log_file);
			} finally {
				$ios_tokens_for_log = implode(', ', $ios_tokens);
				error_log("[$time] push_notification_notice: notice_id = $notice_id, ios_tokens: $ios_tokens_for_log\n", 3, $ios_push_log_file);
			}
		}

		if (sizeof($android_tokens) > 0){
			$text = array(
				"act" => "2",
				"title" => $notice_text,
				"notice_id" => $notice_id
			);
			try {
				$stream -> android_send_push($android_tokens, $text);
			} catch (Exception $e) {
				error_log("[$time] push_notification_notice: EXCEPTION  notice_id = $notice_id" . $e->getMessage(). "\n", 3, $ios_push_log_file);
			} finally {
				$android_tokens_for_log = implode(', ', $android_tokens);
				error_log("[$time] push_notification_notice: notice_id = $notice_id android_tokens: $android_tokens_for_log\n", 3, $android_push_log_file);
			}
		}

		$response["status"] = "OK";
		$response["message"] = "Уведомление успешно отправлено на устройства пользователей.";
		return $response;
	}

	$response["status"] = "ERROR";
	$response["message"] = "Ошибка отправки уведомления. Попробуйте позднее";
	return $response;
}

function notice_delete($notice_id = 0){
	global $db;
	$response = array();
	$current_date = time();

	$result = $db -> sql_query("SELECT `id` FROM `notices` WHERE `id` = '$notice_id' AND `is_deleted` = '0'", "", "array");
	if (sizeof($result) > 0){
		$db -> sql_query("UPDATE `notices` SET `deleted_date` = '$current_date', `is_deleted` = '1' WHERE `id` = '$notice_id'");
		$response["status"] = "OK";
		$response["message"] = "Уведомление удалено";
	} else {
		$response["status"] = "ERROR";
		$response["message"] = "Ошибка удаления уведомления. Попробуйте позднее.";
	}
	return $response;
}

echo json_encode($result);
?>