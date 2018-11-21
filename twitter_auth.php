<?php
require_once "config.php";
require_once "lang.php";
require_once "function.php";

global $db, $log_file, $project_options, $user;
$consumer_key = "";
$consumer_secret = "";
$access_token = "";
$access_token_secret = "";

$oauth_callback = $project_options["service_url_inner"]."twitter_auth.php";
$request_token_url = "https://api.twitter.com/oauth/request_token";
$access_token_url = "https://api.twitter.com/oauth/access_token";
$auth_url = "https://api.twitter.com/oauth/authorize";
$request_uri = "https://api.twitter.com/1.1/account/verify_credentials.json";
$v = "1.0";
$oauth_timestamp = time();
$oauth_nonce = md5(uniqid(rand(), true));

$key = $consumer_secret."&";
$oauth_token_secret = "";

$ip = $user -> get_client_ip();
$time = date("H:i");

if (!isset($_GET["oauth_token"]) && !isset($_GET["oauth_verifier"])) {
    $params = array(
        "oauth_callback=" . urlencode($oauth_callback) . "&",
        "oauth_consumer_key=" . $consumer_key . "&",
        "oauth_nonce=" . $oauth_nonce . "&",
        "oauth_signature_method=HMAC-SHA1" . "&",
        "oauth_timestamp=" . $oauth_timestamp . "&",
        "oauth_version=1.0"
    );

    $oauth_base_text = implode("", array_map("urlencode", $params));
    $oauth_base_text = "GET&" . urlencode($request_token_url) . "&" . $oauth_base_text;
    $oauth_signature = base64_encode(hash_hmac("sha1", $oauth_base_text, $key, true));

    $params = array(
        "&" . "oauth_consumer_key=" . $consumer_key,
        "oauth_nonce=" . $oauth_nonce,
        "oauth_signature=" . urlencode($oauth_signature),
        "oauth_signature_method=HMAC-SHA1",
        "oauth_timestamp=" . $oauth_timestamp,
        "oauth_version=1.0"
    );
    $url = $request_token_url . "?oauth_callback=" . urlencode($oauth_callback) . implode("&", $params);
    $response = file_get_contents($url);
    parse_str($response, $response);
    $oauth_token = $response["oauth_token"];
    $oauth_token_secret = $response["oauth_token_secret"];

    header("Location: " . $auth_url . "?oauth_token=" . $oauth_token . "");
} elseif ((isset($_GET["oauth_token"]) && isset($_GET["oauth_verifier"])) && (($_GET["oauth_token"]) != "" && ($_GET["oauth_verifier"]) != "" )){

    $oauth_token = $_GET["oauth_token"];
    $oauth_verifier = $_GET["oauth_verifier"];
    $oauth_timestamp = time();
    $oauth_nonce = md5(uniqid(rand(), true));

    $oauth_base_text = "GET&" . urlencode($access_token_url) . "&";
    $params = array(
        "oauth_consumer_key=" . $consumer_key . "&",
        "oauth_nonce=" . $oauth_nonce . "&",
        "oauth_signature_method=HMAC-SHA1" . "&",
        "oauth_token=" . $oauth_token . "&",
        "oauth_timestamp=" . $oauth_timestamp . "&",
        "oauth_verifier=" . $oauth_verifier . "&",
        "oauth_version=1.0"
    );
    $key =  $consumer_secret . "&" . $oauth_token_secret;
    $oauth_base_text = "GET&" . urlencode($access_token_url) . "&". implode("", array_map("urlencode", $params));
    $oauth_signature = base64_encode(hash_hmac("sha1", $oauth_base_text, $key, true));

    $params = array(
        "oauth_nonce=" . $oauth_nonce,
        "oauth_signature_method=HMAC-SHA1",
        "oauth_timestamp=" . $oauth_timestamp,
        "oauth_consumer_key=" . $consumer_key,
        "oauth_token=" . urlencode($oauth_token),
        "oauth_verifier=" . urlencode($oauth_verifier),
        "oauth_signature=" . urlencode($oauth_signature),
        "oauth_version=1.0"
    );

    $url = $access_token_url . "?" . implode("&", $params);
    $response = file_get_contents($url);
    parse_str($response, $response);

    $oauth_nonce = md5(uniqid(rand(), true));
    $oauth_timestamp = time();
    $oauth_token = $response["oauth_token"];
    $oauth_token_secret = $response["oauth_token_secret"];

    $params = array(
        "include_email=true&",
        "oauth_consumer_key=" . $consumer_key . "&",
        "oauth_nonce=" . $oauth_nonce . "&",
        "oauth_signature_method=HMAC-SHA1" . "&",
        "oauth_timestamp=" . $oauth_timestamp . "&",
        "oauth_token=" . $oauth_token . "&",
        "oauth_version=1.0"
    );

    $oauth_base_text = "GET&" . urlencode($request_uri) . "&" . implode("", array_map("urlencode", $params));
    $key = $consumer_secret . "&" . $oauth_token_secret;

    $signature = base64_encode(hash_hmac("sha1", $oauth_base_text, $key, true));
    $params = array(
        "include_email=true&",
        "oauth_consumer_key=" . $consumer_key,
        "oauth_nonce=" . $oauth_nonce,

        "oauth_signature=" . urlencode($signature),
        "oauth_signature_method=HMAC-SHA1",

        "oauth_timestamp=" . $oauth_timestamp,
        "oauth_token=" . urlencode($oauth_token),
        "oauth_version=1.0"
    );

    $url = $request_uri . '?' . implode("&", $params);

    $response = file_get_contents($url);
    $user_info = json_decode($response, true);

    if (isset($user_info["id"])) {

        $tw_id = $user_info["id"];
        $name = $user_info["name"];
        $image_url = $user_info["profile_image_url_https"];

        #Создание уникального логина
        $login = mb_strtolower($user_info["screen_name"]);
        $res_account_by_login = $db -> sql_query("SELECT * FROM `users` WHERE `login` = '$login' AND `is_deleted` = '0'", "", "array");
        if (sizeof($res_account_by_login) > 0 AND !empty($res_account_by_login[0])) {
            $rand = gen_uuid_num(4);
            $login = $login.$rand;
        }

        if (isset($user_info["email"]) AND !empty($user_info["email"])) {
            $has_email = 1;
            $email = $user_info["email"];
        } else {
            $has_email = 0;
            $email = "";
        }
        $current_date = time();

        $res_account_by_id = $db -> sql_query("SELECT * FROM `users` WHERE `tw_id` = '$tw_id' AND `id` = (SELECT MAX(`id`) FROM `users` WHERE `tw_id` = '$tw_id' GROUP BY `tw_id`)", "", "array");

        if (sizeof($res_account_by_id) > 0 AND !empty($res_account_by_id[0])) { #Если пользователь с таким tw_id уже существует в системе, обновление записи
            $user_id = $res_account_by_id[0]["id"];
            $user_deleted_status = $res_account_by_id[0]["is_deleted"];
            $user_deleted_date = $res_account_by_id[0]["deleted_date"];
            $recover_account_date_limit = $user_deleted_date + 60*60*72;

            if ($user_deleted_status == 0) { #Пользователь с данным tw_id существует в системе и он не удален
                $create_new_account = 0;

                $db -> sql_query("UPDATE `users` SET `last_connected` = '$current_date', `last_connected_by` = 'tw' WHERE `tw_id` = '$tw_id' AND `is_deleted` = '0' LIMIT 1");
                session_start();
                $_SESSION["uid"] = $user_id;
                $user -> get_web_session_id($user_id, "tw");
                error_log("[$time $ip] tw_auth: прошел авторизацию user_id - $user_id\n", 3, $log_file);
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

                error_log("[$time $ip] hash_generate: ACC-DELETED user-id - $user_id, tw_id - $tw_id\n", 3, $log_file);
            } else {#Пользователь удален, срок восставления аккаунта истек
                $create_new_account =  1;
            }

        } else {#Пользователь с полученным tw_id не зарегистрирован в системе
            if (!empty($email)) {

                #Если получен email, поиск в системе пользователя с таким email
                $res_account_by_email = $db -> sql_query("SELECT * FROM `users` WHERE `email` = '$email' AND `id` = (SELECT MAX(`id`) FROM `users` WHERE `email` = '$email' GROUP BY `email`)", "", "array");

//                $res_account_by_email = $db -> sql_query("SELECT*FROM users WHERE email = '$email' AND is_deleted = '0'", "", "array");
                if (sizeof($res_account_by_email) > 0 AND !empty($res_account_by_email[0])) {

                    $user_id = $res_account_by_email[0]["id"];
                    $account_by_email_tw_id = $res_account_by_email[0]["tw_id"];
                    $user_deleted_status = $res_account_by_email[0]["is_deleted"];
                    $user_deleted_date = $res_account_by_email[0]["deleted_date"];
                    $recover_account_date_limit = $user_deleted_date + 60*60*72;
                    if ($user_deleted_status == 0) { #Пользователь с данным email существует в системе и он не удален, запись обновляется
                        if ($account_by_email_tw_id == ""){ #Если пользователь с таким email существует и к нему не привязан аккаунт twitter, обновление данной записи и добавление tw_id
                            $create_new_account = 0;
                            $db -> sql_query("UPDATE `users` SET `tw_id` = '$tw_id', `last_connected` = '$current_date', `last_connected_by` = 'tw' WHERE `id` = '$user_id' AND `is_deleted` = '0' LIMIT 1");
                            session_start();
                            $_SESSION["uid"] = $user_id;
                            $user -> get_web_session_id($user_id, "tw");
                            error_log("[$time $ip] tw_auth: к существующему аккаунту добавлен tw_id, прошел авторизацию user_id = $user_id\n", 3, $log_file);
                            header("Location: https://$_SERVER[HTTP_HOST]/index.php?route=page_index");
                        } else {
                            $create_new_account = 1;
                            error_log("[$time $ip] tw_auth: к существующему email - $email не добавлен tw_id - $tw_id, привязан ранее tw_id - $account_by_email_tw_id\n", 3, $log_file);
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
                        error_log("[$time $ip] tw_auth: ACC-DELETED user-id - $user_id, tw_id - $tw_id\n", 3, $log_file);
                    } else { #Пользователь удален, срок восставления аккаунта истек
                        $create_new_account = 1;
                    }

                } else {
                    $create_new_account = 1;
                }
            } else {
                $create_new_account = 1;
            }

            if ($create_new_account) {
                $user_uuid = gen_uuid(6);
                $etag_user = gen_uuid(12);
                $etag_img = gen_uuid(12);

                if ($image_url){
                    #Сохранение в проект фото пользователя
                    $save_image = $user -> save_user_image_from_url($image_url, $user_uuid);
                    if ($save_image["status"] == "OK") {
                        $image_url = $save_image["image_url"];
                    }
                }

                $res_add_user = $db -> sql_query("INSERT INTO `users` (`id`, `user_uuid`, `login`, `phone`, `password`, `fb_id`, `vk_id`, `tw_id`, `name`, `image_url`, `image_changed_date`, `etag_user`, `etag_img`, `location`, `email`, `birth_day`, `about`, `created_date`, `deleted_date`, `last_changed`, `last_connected`, `last_connected_by`, `accepted`, `created_by`, `hash`, `email_confirm`, `is_official`, `is_check_official`, `is_blocked`, `is_deleted`) VALUES (NULL, '$user_uuid', '$login', '', '', '', '', '$tw_id', '$name', '$image_url', '$current_date', '$etag_user', '$etag_img', '', '$email', '', '', '$current_date', '', '$current_date', '$current_date', 'tw', '1', 'tw', '', '0', '0', '0', '0', '0')");
                $user_id = $db -> sql_nextid($res_add_user);

                session_start();
                $_SESSION["uid"] = $user_id;
                $user -> get_web_session_id($user_id, "tw");
                error_log("[$time $ip] tw_auth: успешно создан user_id = $user_id\n", 3, $log_file);
                header("Location: https://$_SERVER[HTTP_HOST]/index.php?route=page_index");
            }
        }
    } else {
        head(_AUTHORIZATION);
        echo "<div class=\"container\"><div class=\"row\"><div class=\"col-xs-10 text-center\">" . _ERR_AUTH_UID . "</div></div></div>";
        footer();

        error_log("[$time $ip] tw_auth: не получен uid\n", 3, $log_file);
    }

} else {
    head(_AUTHORIZATION);
    echo "<div class=\"container\"><div class=\"row\"><div class=\"col-xs-10 text-center\">" . _ERR_AUTH_ACCESS_TOKEN . "</div></div></div>";
    footer();

    error_log("[$time $ip] tw_auth: не получен access_token\n", 3, $log_file);
}
?>