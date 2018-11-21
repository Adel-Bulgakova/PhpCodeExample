<?php
global $db, $log_file, $project_options, $user;
$client_id = "";
$client_secret = "";
$redirect_uri = "/index.php?route=proc_auth_vk";
$v = "5.37";

$ip = $user -> get_client_ip();
$time = date("H:i");

if (!isset($_GET["code"])) {

	$params = array(
		"client_id"     => $client_id,
		"redirect_uri"  => $redirect_uri,
		"display"       => "page",
		"response_type" => "code",
		"scope"         => "email",
		"v"         	=> $v,
	);

	header("Location: https://oauth.vk.com/authorize?". urldecode(http_build_query($params)) ."");

} else if (isset($_GET["code"])){
	$code = $_GET["code"];

	$params = array(
		"client_id"     => $client_id,
		"client_secret" => $client_secret,
		"redirect_uri"  => $redirect_uri,
		"code"          => $code
	);

	$token = json_decode(file_get_contents("https://oauth.vk.com/access_token?" . urldecode(http_build_query($params))), true);
	$email = $token["email"];

	if (isset($token["access_token"])) {
		$params = array(
			"uids"         => $token["user_id"],
			"fields"       => "uid,first_name,has_photo,photo_max,screen_name",
			"access_token" => $token["access_token"]
		);

		$user_info = json_decode(file_get_contents("https://api.vk.com/method/users.get?" . urldecode(http_build_query($params))), true);
		$user_info = $user_info["response"][0];

		if (isset($user_info["uid"])) {
			$vk_id = $user_info["uid"];
			$first_name = $user_info["first_name"];
			$last_name = $user_info["last_name"];
			$name = $first_name." ".$last_name;
			$screen_name = $user_info["screen_name"];

			$image_url = "";
			$has_photo = $user_info["has_photo"];
			if ($has_photo == 1) {
				$image_url = $user_info["photo_max"];
			}

			$current_date = time();

			$res_account_by_id = $db -> sql_query("SELECT * FROM `users` WHERE `vk_id` = '$vk_id' AND `id` = (SELECT MAX(`id`) FROM `users` WHERE `vk_id` = '$vk_id' GROUP BY `vk_id`)", "", "array");

			if (sizeof($res_account_by_id) > 0 AND !empty($res_account_by_id[0])) {
				$user_id = $res_account_by_id[0]["id"];
				$user_deleted_status = $res_account_by_id[0]["is_deleted"];
				$user_deleted_date = $res_account_by_id[0]["deleted_date"];
				$recover_account_date_limit = $user_deleted_date + 60*60*72;

				if ($user_deleted_status == 0) { #Пользователь с данным vk_id существует в системе и он не удален
					$create_new_account = 0;

					$db -> sql_query("UPDATE `users` SET `last_connected` = '$current_date', `last_connected_by` = 'vk' WHERE `vk_id` = '$vk_id' AND `is_deleted` = '0' LIMIT 1");
					session_start();
					$_SESSION["uid"] = $user_id;
					$user -> get_web_session_id($user_id, "vk");
					error_log("[$time $ip] vk_auth: прошел авторизацию user_id = $user_id\n", 3, $log_file);
					header("Location: https://$_SERVER[HTTP_HOST]/index.php?route=page_index");
				} else if ($user_deleted_status == 1 AND $current_date < $recover_account_date_limit) {#Пользователь удален, но существует возможность восстановления аккаунта в течение 72 часов после его удаления
					$create_new_account = 0;

					head(_AUTHORIZATION);
					echo "<div class=\"container\"><div class=\"row\"><div class=\"col-xs-10 text-center\"><form id=\"recovery_account_form\" method=\"post\" role=\"form\"><div class=\"form-group\"></div><div id=\"result_recovery_account\" style=\"margin-bottom: 15px;\">" . _RECOVER_ACCOUNT_INFO . "</div><div class=\"clearfix\"><input type=\"hidden\" name=\"user_id\" value=\"$user_id\"/><input type=\"submit\" class=\"btn theme-button\" value=\"" . _RECOVER_ACCOUNT . "\" /></div></form></div></div></div>
					<script>
					$(document).ready(function(){
						$('#recovery_account_form').validate({
							submitHandler: function(form) {
								$('#result_recovery_account').empty();
								$('input[type=submit]').attr('disabled', 'true');
								$.ajax({
									type: 'POST',
									dataType: 'json',
									url: 'index.php?route=proc_auth',
									data:  $('#recovery_account_form').serialize()+ \"&action=recovery_account\",
									success: function(data) {
										if (data.status == 'OK') {
											window.location = '/';
										} else if (data.status == 'ERROR'){
											$('#result_recovery_account').html(data.message);
										}
										$('input[type=submit]').removeAttr('disabled');
										console.log(data);
									},
									error: function(xhr, status, error){
										console.log(error);
										$('#result_recovery_account').html(" . _REQUEST_FAILED_INFO. ");
									}
								});
							},
							errorPlacement: function(error, element) {
								error.insertAfter(element);
							}
						});
    				});</script>";
					footer();

					error_log("[$time $ip] hash_generate: ACC-DELETED user-id - $user_id, vk_id - $vk_id\n", 3, $log_file);
				} else {#Пользователь удален, срок восставления аккаунта истек
					$create_new_account =  1;
				}

			} else {#Пользователь с полученным vk_id не зарегистрирован в системе
				if (!empty($email)) {

					#Создание уникального логина
					$login = stristr($email, '@', true);
					$res_account_by_login = $db -> sql_query("SELECT * FROM `users` WHERE `login` = '$login' AND `is_deleted` = '0'", "", "array");
					if (sizeof($res_account_by_login) > 0 AND !empty($res_account_by_login[0])) {
						$login = $login.gen_uuid_num(4);
					}

					#Если получен email, поиск в системе пользователя с таким email
					$res_account_by_email = $db -> sql_query("SELECT * FROM `users` WHERE `email` = '$email' AND `id` = (SELECT MAX(`id`) FROM `users` WHERE `email` = '$email' GROUP BY `email`)", "", "array");

					if (sizeof($res_account_by_email) > 0 AND !empty($res_account_by_email[0])) {
						$user_id = $res_account_by_email[0]["id"];
						$account_by_email_vk_id = $res_account_by_email[0]["vk_id"];
						$user_deleted_status = $res_account_by_email[0]["is_deleted"];
						$user_deleted_date = $res_account_by_email[0]["deleted_date"];
						$recover_account_date_limit = $user_deleted_date + 60*60*72;

						if ($user_deleted_status == 0) { #Пользователь с данным email существует в системе и он не удален, запись обновляется
							if ($account_by_email_vk_id == ""){ #Если пользователь с таким email существует и к нему не привязан аккаунт vkontakte, обновление данной записи и добавление vk_id
								$create_new_account = 0;
								$db -> sql_query("UPDATE `users` SET `vk_id` = '$vk_id', last_connected = '$current_date', `last_connected_by` = 'vk' WHERE `id` = '$user_id' AND `is_deleted` = '0' LIMIT 1");
								session_start();
								$_SESSION["uid"] = $user_id;
								$user -> get_web_session_id($user_id, "vk");
								error_log("[$time $ip] vk_auth: к существующему аккаунту добавлен vk_id, прошел авторизацию user_id - $user_id\n", 3, $log_file);
								header("Location: https://$_SERVER[HTTP_HOST]/index.php?route=page_index");
							} else {
								$create_new_account = 1;
								error_log("[$time $ip] vk_auth: к существующему email - $email не добавлен vk_id - $vk_id, привязан ранее vk_id - $account_by_email_vk_id\n", 3, $log_file);
							}
						} else if ($user_deleted_status == 1 AND $current_date < $recover_account_date_limit) {#Пользователь удален, но существует возможность восстановления аккаунта в течение 72 часов после его удаления
							$create_new_account = 0;
							head(_AUTHORIZATION);
							echo "<div class=\"container\"><div class=\"row\"><div class=\"col-xs-10 text-center\"><form id=\"recovery_account_form\" method=\"post\" role=\"form\"><div class=\"form-group\"></div><div id=\"result_recovery_account\" style=\"margin-bottom: 15px;\">" . _RECOVER_ACCOUNT_INFO . "</div><div class=\"clearfix\"><input type=\"hidden\" name=\"user_id\" value=\"$user_id\"/><input type=\"submit\" class=\"btn theme-button\" value=\"" . _RECOVER_ACCOUNT . "\" /></div></form></div></div></div>
					<script>
						$(document).ready(function(){
							$('#recovery_account_form').validate({
								submitHandler: function(form) {
									$('#result_recovery_account').empty();
									$('input[type=submit]').attr('disabled', 'true');
									$.ajax({
										type: 'POST',
										dataType: 'json',
										url: 'index.php?route=proc_auth',
										data:  $('#recovery_account_form').serialize()+\"&action=recovery_account\",
										success: function(data) {
											if (data.status == 'OK') {
												window.location = '/';
											} else if (data.status == 'ERROR'){
												$('#result_recovery_account').html(data.message);
											}
											$('input[type=submit]').removeAttr('disabled');
											console.log(data);
										},
										error: function(xhr, status, error){
											console.log(error);
											$('#result_recovery_account').html(" . _REQUEST_FAILED_INFO. ");
										}
									});
								},
								errorPlacement: function(error, element) {
									error.insertAfter(element);
								}
							});
						});</script>";
							footer();
							error_log("[$time $ip] vk_auth: ACC-DELETED user-id - $user_id, vk_id - $vk_id\n", 3, $log_file);
						} else { #Пользователь удален, срок восставления аккаунта истек
							$create_new_account = 1;
						}

					} else {
						$create_new_account = 1;
					}

				} else {
					$email = "";
					$login = $screen_name;
					$create_new_account = 1;
				}

				if ($create_new_account) {
					$user_uuid = gen_uuid(6);
					$etag_user = gen_uuid(12);
					$etag_img = gen_uuid(12);

					if ($has_photo){
						#Сохранение в проект фото пользователя
						$save_image = $user -> save_user_image_from_url($image_url, $user_uuid);
						if ($save_image["status"] == "OK") {
							$image_url = $save_image["image_url"];
						}
					}

					$res_add_user = $db -> sql_query("INSERT INTO `users` (`id`, `user_uuid`, `login`, `phone`, `password`, `fb_id`, `vk_id`, `tw_id`, `name`, `image_url`, `image_changed_date`, `etag_user`, `etag_img`, `location`, `email`, `birth_day`, `about`, `created_date`, `deleted_date`, `last_changed`, `last_connected`, `last_connected_by`, `accepted`, `created_by`, `hash`, `email_confirm`, `is_official`, `is_check_official`, `is_blocked`, `is_deleted`) VALUES (NULL, '$user_uuid', '$login', '', '', '', '$vk_id', '', '$name', '$image_url', '$current_date', '$etag_user', '$etag_img', '', '$email', '', '', '$current_date', '', '$current_date', '$current_date', 'vk', '1', 'vk', '', '0', '0', '0', '0', '0')");

					$user_id = $db -> sql_nextid($res_add_user);

					session_start();
					$_SESSION["uid"] = $user_id;
					$user -> get_web_session_id($user_id, "vk");
					error_log("[$time $ip] vk_auth: успешно создан user_id = $user_id\n", 3, $log_file);
					header("Location: https://$_SERVER[HTTP_HOST]/index.php?route=page_index");
				}
			}

		} else {
			head(_AUTHORIZATION);
			echo "<div class=\"container\"><div class=\"row\"><div class=\"col-xs-10 text-center\">" . _ERR_AUTH_UID . "</div></div></div>";
			footer();
			error_log("[$time $ip] vk_auth: не получен uid\n", 3, $log_file);
		}

	} else {
		head(_AUTHORIZATION);
		echo "<div class=\"container\"><div class=\"row\"><div class=\"col-xs-10 text-center\">" . _ERR_AUTH_ACCESS_TOKEN . "</div></div></div>";
		footer();
		error_log("[$time $ip] vk_auth: не получен access_token\n", 3, $log_file);
	}

//	TODO {"error":"invalid_grant","error_description":"Code is expired."}
}
?>