<?php
header("Content-Type: application/json; charset=utf-8");
$action = $_POST["action"];
$category_id = "";
if (isset($_POST["category_id"]) AND $_POST["category_id"] != "" AND $_POST["category_id"] != 0) {
	$category_id = $_POST["category_id"];
}
$result = array();

switch ($action) {
	case "add" :
		$result = category_add();
		break;
	case "edit" :
		$result = category_edit($category_id);
		break;
	case "delete" :
		$result = category_delete($category_id);
		break;
}

function category_add(){
	global $db;
	$current_date = time();

	$expected_params = array();
	if (isset($_POST["name_ru"]) AND !empty($_POST["name_ru"])){
		$name_ru = $_POST["name_ru"];
	} else {
		$expected_params[] = "название категории на русском языке";
	}

	if (isset($_POST["name_en"]) AND !empty($_POST["name_en"])){
		$name_en = $_POST["name_en"];
	} else {
		$expected_params[] = "название категории на английском языке";
	}

	if (count($expected_params) > 0 ){
		$expected = implode(", ", $expected_params);
		$error = "Не получены параметры: $expected";
		$response["status"] = "ERROR";
		$response["message"] = $error;
		return $response;
	}
	if ($db -> sql_query("INSERT INTO `streams_categories_data`(`id`, `name_ru`, `name_en`, `created_date`, `is_active`, `is_deleted`) VALUES (NULL , '$name_ru', '$name_en', '$current_date','1','0')")){
		$response["status"] = "OK";
		$response["message"] = "Категория успешно добавлена";
		return $response;
	}
	$response["status"] = "ERROR";
	$response["message"] = "Не удалось добавить категорию. Попробуйте позднее.";
	return $response;
}

function category_edit($category_id = 0){
	global $db;
	$current_date = time();

	$expected_params = array();
	if (isset($_POST["name_ru"]) AND !empty($_POST["name_ru"])){
		$name_ru = $_POST["name_ru"];
	} else {
		$expected_params[] = "название категории на русском языке";
	}

	if (isset($_POST["name_en"]) AND !empty($_POST["name_en"])){
		$name_en = $_POST["name_en"];
	} else {
		$expected_params[] = "название категории на английском языке";
	}

	$active_status = 0;
	if (isset($_POST["active_status"]) AND $_POST["active_status"] = 1) {
		$active_status = 1;
	}

	if (count($expected_params) > 0 ){
		$expected = implode(", ", $expected_params);
		$error = "Не получены параметры: $expected";
		$response["status"] = "ERROR";
		$response["message"] = $error;
		return $response;
	}
	if ($db -> sql_query("UPDATE `streams_categories_data` SET `name_ru` = '$name_ru', `name_en` = '$name_en', `is_active` = '$active_status'  WHERE `id` = '$category_id'")){
		$response["status"] = "OK";
		$response["message"] = "Категория успешно отредактирована";
		return $response;
	}
	$response["status"] = "ERROR";
	$response["message"] = "Не удалось добавить категорию. Попробуйте позднее.";
	return $response;
	
}

function category_delete($category_id = 0){
	global $db;
	$result = $db -> sql_query("SELECT * FROM `streams_categories_data` WHERE `id` = '$category_id' AND `is_deleted` = '0'", "", "array");
	if (sizeof($result) > 0){
		$db -> sql_query("UPDATE `streams_categories_data` SET `is_deleted` = '1' WHERE `id` = '$category_id'");
		$response["status"] = "OK";
		return $response;
	}
	$response["status"] = "ERROR";
	$response["message"] = "Категория не найдена. Попробуйте позднее.";
	return $response;
}

echo json_encode($result);
?>