<?php
global $db, $log_file, $project_options, $user;
$client_id = "";
$client_secret = "";
$redirect_uri = "/index.php?route=proc_auth_fb";
$v = "2.5";

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

	header("Location: https://www.facebook.com/dialog/oauth?". urldecode(http_build_query($params)) ."");

} else if (isset($_GET["code"])){
	$code = $_GET["code"];

	$params = array(
		"client_id"     => $client_id,
		"client_secret" => $client_secret,
		"redirect_uri"  => $redirect_uri,
		"code"          => $code
	);

	$tokenInfo = null;
	parse_str(file_get_contents("https://graph.facebook.com/oauth/access_token?" . urldecode(http_build_query($params))), $tokenInfo);

	if (count($tokenInfo) > 0 && isset($tokenInfo["access_token"])) {
		$params = array(
			"access_token"  => $tokenInfo["access_token"],
			"fields"        => "cover,name,email",
			);
		$user_info = json_decode(file_get_contents("https://graph.facebook.com/me?" . urldecode(http_build_query($params))), true);
//		print_r($user_info);

		if (isset($user_info["id"])) {
			$fb_id = $user_info["id"];
			$name = $user_info["name"];
			$image_url = "http://graph.facebook.com/$fb_id/picture?type=large";

			if (isset($user_info["email"]) AND !empty($user_info["email"])) {
				$email = $user_info["email"];
				
			} else {
				$email = "";
			}

			$current_date = time();

			$res_account_by_id = $db -> sql_query("SELECT * FROM `users` WHERE `fb_id` = '$fb_id' AND `id` = (SELECT MAX(`id`) FROM `users` WHERE `fb_id` = '$fb_id' GROUP BY `fb_id`)", "", "array");
			
			if (sizeof($res_account_by_id) > 0 AND !empty($res_account_by_id[0])) { #Если пользователь с таким fb_id уже существует в системе, обновление записи

				$user_id = $res_account_by_id[0]["id"];
				$user_deleted_status = $res_account_by_id[0]["is_deleted"];
				$user_deleted_date = $res_account_by_id[0]["deleted_date"];
				$recover_account_date_limit = $user_deleted_date + 60*60*72;

				if ($user_deleted_status == 0) { #Пользователь с данным fb_id существует в системе и он не удален
					$create_new_account = 0;

					$db -> sql_query("UPDATE `users` SET `last_connected` = '$current_date', `last_connected_by` = 'fb' WHERE `fb_id` = '$fb_id' AND `is_deleted` = '0' LIMIT 1");
					session_start();
					$_SESSION["uid"] = $user_id;
					$user -> get_web_session_id($user_id, "fb");
					error_log("[$time $ip] fb_auth: прошел авторизацию user_id - $user_id\n", 3, $log_file);
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

					error_log("[$time $ip] hash_generate: ACC-DELETED user-id - $user_id, fb_id - $fb_id\n", 3, $log_file);
				} else {#Пользователь удален, срок восставления аккаунта истек
					$create_new_account =  1;
				}
				
			} else {#Пользователь с полученным fb_id не зарегистрирован в системе
				if (!empty($email)) {
					#Создание уникального логина
					$login = stristr($email, '@', true);
					$res_account_by_login = $db -> sql_query("SELECT * FROM `users` WHERE `login` = '$login' AND `is_deleted` = '0'", "", "array");
					if (sizeof($res_account_by_login) > 0 AND $res_account_by_login[0] != "") {
						$login = $login.gen_uuid_num(4);
					}
					
					#Если получен email, поиск в системе пользователя с таким email
					$res_account_by_email = $db -> sql_query("SELECT * FROM `users` WHERE `email` = '$email' AND `id` = (SELECT MAX(`id`) FROM `users` WHERE `email` = '$email' GROUP BY `email`)", "", "array");

					if (sizeof($res_account_by_email) > 0 AND !empty($res_account_by_email[0])) {
						$user_id = $res_account_by_email[0]["id"];
						$account_by_email_fb_id = $res_account_by_email[0]["fb_id"];
						$user_deleted_status = $res_account_by_email[0]["is_deleted"];
						$user_deleted_date = $res_account_by_email[0]["deleted_date"];
						$recover_account_date_limit = $user_deleted_date + 60*60*72;
						if ($user_deleted_status == 0) { #Пользователь с данным email существует в системе и он не удален, запись обновляется
							if ($account_by_email_fb_id == ""){ #Если пользователь с таким email существует и к нему не привязан аккаунт facebook, обновление данной записи и добавление fb_id
								$create_new_account = 0;
								$db -> sql_query("UPDATE `users` SET `fb_id` = '$fb_id', `last_connected` = '$current_date', `last_connected_by` = 'fb' WHERE `id` = '$user_id' AND `is_deleted` = '0' LIMIT 1");
								session_start();
								$_SESSION["uid"] = $user_id;
								$user -> get_web_session_id($user_id, "fb");
								error_log("[$time $ip] fb_auth: к существующему аккаунту добавлен fb_id, прошел авторизацию user_id - $user_id\n", 3, $log_file);
								header("Location: https://$_SERVER[HTTP_HOST]/index.php?route=page_index");
							} else {
								$create_new_account = 1;
								error_log("[$time $ip] fb_auth: к существующему email - $email не добавлен fb_id - $fb_id, привязан ранее fb_id - $account_by_email_fb_id\n", 3, $log_file);
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
							error_log("[$time $ip] fb_auth: ACC-DELETED user-id - $user_id, fb_id - $fb_id\n", 3, $log_file);
							
						} else { #Пользователь удален, срок восставления аккаунта истек
							$create_new_account = 1;
						}
						
					} else {
						$create_new_account = 1;
					}
				} else {
					$email = "";
					$login = "id".$fb_id;
					$create_new_account = 1;
				}
			}

			if ($create_new_account) {
				$user_uuid = gen_uuid(6);
				$etag_user = gen_uuid(12);
				$etag_img = gen_uuid(12);

				if (file_get_contents($image_url)){
					#Сохранение в проект фото пользователя
					$save_image = $user -> save_user_image_from_url($image_url, $user_uuid);
					if ($save_image["status"] == "OK") {
						$image_url = $save_image["image_url"];
					}
				}

				$res_add_user = $db -> sql_query("INSERT INTO `users` (`id`, `user_uuid`, `login`, `phone`, `password`, `fb_id`, `vk_id`, `tw_id`, `name`, `image_url`, `image_changed_date`, `etag_user`, `etag_img`, `location`, `email`, `birth_day`, `about`, `created_date`, `deleted_date`, `last_changed`, `last_connected`, `last_connected_by`, `accepted`, `created_by`, `hash`, `email_confirm`, `is_official`, `is_check_official`, `is_blocked`, `is_deleted`) VALUES (NULL, '$user_uuid', '$login', '', '', '$fb_id', '', '', '$name', '$image_url', '$current_date', '$etag_user', '$etag_img', '', '$email', '', '', '$current_date', '', '$current_date', '$current_date', 'fb', '1', 'fb', '', '0', '0', '0', '0', '0')");
				$user_id = $db -> sql_nextid($res_add_user);

				session_start();
				$_SESSION["uid"] = $user_id;
				$user -> get_web_session_id($user_id, "fb");
				error_log("[$time $ip] fb_auth: успешно создан user_id - $user_id\n", 3, $log_file);
				header("Location: https://$_SERVER[HTTP_HOST]/index.php?route=page_index");
			}

		} else {
			head(_AUTHORIZATION);
			echo "<div class=\"container\"><div class=\"row\"><div class=\"col-xs-10 text-center\">" . _ERR_AUTH_UID . "</div></div></div>";
			footer();

			error_log("[$time $ip] fb_auth: не получен uid\n", 3, $log_file);
		}
	} else {
		head(_AUTHORIZATION);
		echo "<div class=\"container\"><div class=\"row\"><div class=\"col-xs-10 text-center\">" . _ERR_AUTH_ACCESS_TOKEN . "</div></div></div>";
		footer();

		error_log("[$time $ip] fb_auth: не получен access_token\n", 3, $log_file);
	}
}
?>