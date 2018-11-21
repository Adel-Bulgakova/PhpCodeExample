<?php
global $db;
$admin_id = $_POST["admin_id"];
$result_admin = $db -> sql_query("SELECT * FROM `support_service_admins` WHERE `id` = '$admin_id' AND `is_deleted` = '0'", "", "array");
if (sizeof($result_admin) > 0 AND !empty($result_admin[0])){
	$login = $result_admin[0]["login"];
	$name = $result_admin[0]["name"];
	$email = $result_admin[0]["email"];
	$comment = $result_admin[0]["comments"];

	$html = "
			<form id=\"admin_edit_form\" method=\"post\">
				<div class=\"row\">
					<div class=\"col-xs-4\">Логин</div>
					<div class=\"col-xs-8\">
						<input type=\"text\" class=\"form-control\" name=\"admin_login\" readonly=\"readonly\" value=\"$login\">
					</div>
				</div>

				<div class=\"row\">
					<div class=\"col-xs-4\">Имя Фамилия</div>
					<div class=\"col-xs-8\">
						<input type=\"text\" class=\"form-control\" name=\"admin_name\" value=\"$name\">
					</div>
				</div>

				<div class=\"row\">
					<div class=\"col-xs-4\">Создать новый пароль</div>
					<div class=\"col-xs-5\">
						<input type=\"text\" class=\"form-control\" name=\"new_password\" readonly=\"readonly\" placeholder=\"Новый пароль\" value=\"\">
					</div>
					<div class=\"col-xs-3\">
						<button type=\"button\" class=\"btn btn-success btn-sm\" id=\"generate_new\">Сгенерировать</button>
					</div>
				</div>

				<div class=\"row\">
					<div class=\"col-xs-4\">Email</div>
					<div class=\"col-xs-8\">
						<input type=\"email\" class=\"form-control\" name=\"email\" placeholder=\"Email\" value=\"$email\">
					</div>
				</div>

				<div class=\"row\">
					<div class=\"col-xs-4\">Комментарии</div>
					<div class=\"col-xs-8\">
						<input type=\"text\" class=\"form-control\" name=\"comments\" placeholder=\"Комментарии\"  value=\"$comment\">
					</div>
				</div>

				<div class=\"row\">
					<div class=\"col-xs-12 col-md-5 col-md-offset-3\" id=\"admin_edit_result\"></div>
				</div>
				<input type=\"hidden\" name=\"admin_id\" value=\"$admin_id\">
			</form>

			<script>
				$('#generate_new').click(function(){
					var string = '';
					var characters = '0123456789abcdef';
					for (var i =0; i < 12; i++ ) {
						string += characters.charAt(Math.floor(Math.random() * characters.length));
					}
					$('input[name=\"new_password\"]').val(string);
				});
			</script>";
	echo $html;
} else {
	echo "Администратор не найден";
}
?>