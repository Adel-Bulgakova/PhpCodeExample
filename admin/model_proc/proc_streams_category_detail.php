<?php
global $db;
$category_id = $_POST["category_id"];
$result = $db -> sql_query("SELECT * FROM `streams_categories_data` WHERE `id` = '$category_id' AND `is_deleted` = '0'", "", "array");
if (sizeof($result) > 0){
	$name_ru = $result[0]["name_ru"];
	$name_en = $result[0]["name_en"];
	$active_status = $result[0]["is_active"];

	$checked_state = "checked=\"checked\"";
	if ($active_status == 0) {
		$checked_state = "";
	}
	$html = "
			<form id=\"category_edit_form\" method=\"post\">
				<div class=\"row\">
					<div class=\"col-xs-4\">Название (русский язык)</div>
					<div class=\"col-xs-8\">
						<input type=\"text\" class=\"form-control\" name=\"name_ru\" value=\"$name_ru\">
					</div>
				</div>

				<div class=\"row\">
					<div class=\"col-xs-4\">Название (английский язык)</div>
					<div class=\"col-xs-8\">
						<input type=\"text\" class=\"form-control\" name=\"name_en\" value=\"$name_en\">
					</div>
				</div>

				<div class=\"form-group\">
					<label class=\"control-label col-xs-4\">Статус активности</label>
					<div class=\"col-xs-8\">
						<input type=\"checkbox\" name=\"active_status\" $checked_state value=\"$active_status\">
					</div>
				</div>

				<div class=\"row\">
					<div class=\"col-xs-12 col-md-6 col-md-offset-3\" id=\"category_edit_result\"></div>
				</div>
				<input type=\"hidden\" name=\"category_id\" value=\"$category_id\">
			</form>";
	echo $html;
} else {
	echo "Категория не найдена. Попробуйте позднее.";
}
?>