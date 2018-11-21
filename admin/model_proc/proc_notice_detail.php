<?php
global $db;
$notice_id = $_POST["notice_id"];
$result = $db -> sql_query("SELECT * FROM `notices` WHERE `id` = '$notice_id' AND `is_deleted` = '0'", "", "array");
if (sizeof($result) > 0){
	$notice_text = $result[0]["notice_text"];
	$deactivated_date_timestamp = $result[0]["deactivated_date"];

	$activated_date = date("d.m.Y H:i", $result[0]["activated_date"]);

	$checked_state = "";
	$readonly_state = "";
	$deactivated_date = date("d.m.Y H:i", $deactivated_date_timestamp);
	if ($deactivated_date_timestamp == 0) {
		$deactivated_date ='';
		$checked_state = "checked=\"checked\"";
		$readonly_state = "readonly=\"readonly\"";
	}

	$html = "
			<form class=\"form-horizontal form-label-left\" id=\"notice_edit_form\" method=\"post\">
				<div class=\"form-group\">
					<div class=\"control-label col-xs-4\">Текст уведомления</div>
					<div class=\"col-xs-8\">
						<input type=\"text\" class=\"form-control\" name=\"notice_text\" value=\"$notice_text\">
					</div>
				</div>

				<div class=\"form-group\">
					<div class=\"control-label col-xs-4\">Начало активности</div>
					<div class=\"col-xs-8\">
						<input type=\"text\" class=\"form-control activated_date\" name=\"activated_date\" value=\"$activated_date\">
					</div>
				</div>
				
				<div class=\"form-group\">
					<div class=\"control-label col-xs-4\">Завершение активности</div>
					<div class=\"col-xs-8\">
						<input type=\"text\" class=\"form-control deactivated_date\" name=\"deactivated_date\" $readonly_state value=\"$deactivated_date\">
					</div>
				</div>
				
				<div class=\"form-group\">
					<label class=\"control-label col-xs-4\">Бессрочно</label>
					<div class=\"col-xs-8\">
						<input type=\"checkbox\" class=\"without_expiration_param\" name=\"without_expiration_param\" $checked_state  value=\"1\">
					</div>
				</div>

				<div class=\"form-group\">
					<div class=\"col-xs-12 col-md-5 col-md-offset-3\" id=\"notice_edit_result\"></div>
				</div>
				<input type=\"hidden\" name=\"notice_id\" value=\"$notice_id\">
			</form>";
	echo $html;
} else {
	echo "Уведомление не найденo";
}
?>