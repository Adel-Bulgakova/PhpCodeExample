<?php
global $api, $db, $thumb_query, $lang, $stream, $snapshot_query, $user, $project_options;
$session_id = $_SESSION["web_session_id"];
$user_id = get_current_session_user();
$ws_url = $project_options["ws_urls"]["ws_stream"];
$url = $_SERVER["REQUEST_SCHEME"] . "://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];
$current_date = time();
$mobile = 0;
if (isset($_SERVER["HTTP_USER_AGENT"]) AND $_SERVER["HTTP_USER_AGENT"] != ""){
	$mobile = mobile_test($_SERVER["HTTP_USER_AGENT"]);
}

$hero_id = prepair_str($_GET["user"]);
$stream_uuid = prepair_str($_GET["uuid"]);

$admin_permissions = 0;
$disabled = "";
$chat_class_name = "";
#Ограничение возможности пожаловаться на свою трансляцию, подписаться на себя
if ($user_id AND $user_id == $hero_id) {
	$admin_permissions = 1; #Пользователь является владельцем трансляции
	$chat_class_name = "bottom_hide";
	$disabled = "disabled";
}

$snapshot = $snapshot_query.$stream_uuid;
$result_stream 	= $db -> sql_query("SELECT `streams`.`id` AS `stream_id`, `streams`.`name` AS `stream_name`, `device_id`, `permissions`, `chat_permissions`, `start_date` FROM `streams` WHERE `uuid` = '$stream_uuid' AND `user_id` = '$hero_id' AND `streams`.`is_blocked` = '0' AND `streams`.`is_deleted` = '0'", "", "array");

if (sizeof($result_stream) < 1 OR empty($result_stream[0])) {
	echo "
		<div class=\"section content\">
			<div class=\"container text-center\"><div class=\"row\"><h3>" . _STREAM_NOT_FOUND . "</h3></div></div>
		</div>";
} else {

	$permissions = permissions($stream_uuid);
	$watch_permissions = $permissions["watch"];
	$user_chat_blocked_state = $permissions["chat_blocked"];
	$user_auth_state = $permissions["auth_state"];
	$embed_permissions = $permissions["embed"];

	if ($watch_permissions == 0) {
		echo "
			<div class=\"section content\">
				<div class=\"container text-center\"><div class=\"row\"><h3>" . _ERROR_PERMISSION . "</h3></div></div>
			</div>";
	} else {
		$profile_name = $user -> profile_name($hero_id);
		$profile_image = $user -> profile_image_html($hero_id);

		$data_url = $stream -> generate_stream_url($stream_uuid);
		$stream_id = $result_stream[0]["stream_id"];
		$stream_name = $stream -> stream_name($stream_uuid);

		$like_state = 0;
		$like_title = _LIKE;
		$result_like = $db -> sql_query("SELECT * FROM `users_actions_log` WHERE `stream_id` = '$stream_id' AND `users_actions_id` = '1' AND `user_id` = '$user_id'", "", "array");
		if (sizeof($result_like) > 0 AND !empty($result_like[0])) {
			$like_state = 1;
			$like_title = _UNLIKE;
		}

		$follow_state = 0;
		$follow_title = _FOLLOW;
		$result_followers = $db -> sql_query("SELECT * FROM `users_actions_log` WHERE `hero_id` = '$hero_id' AND `users_actions_id` = '3' AND `user_id` = '$user_id'", "", "array");
		if (sizeof($result_followers) > 0) {
			$follow_state = 1;
			$follow_title = _IS_FOLLOWED;
		}
		$thumb = $thumb_query . $stream_uuid;

		$stream_start_date = intval($result_stream[0]["start_date"]);
		$time_difference = $current_date - $stream_start_date;
		$stream_date_info = refine_data($time_difference) . " " . _AGO;

		$stream_statistics = stream_statistics($stream_uuid);
		echo "
		<div id=\"page_play\" class=\"section content\">
			<div class=\"container\">
				<div class=\"row\">
					<div class=\"col-xs-10\">
						<div class=\"profile_info\" data-profile-id=\"$hero_id\">$profile_image<div class=\"profile_name\">$profile_name</div></div>
					</div>
				</div>
				<div class=\"row\">
					<div class=\"col-xs-10\">
						<div class=\"block player_container\">
							<div class=\"player_wrapper\">";
							if (!$mobile) {

								echo "
									<script type=\"text/javascript\">
										$(document).ready(function(){
										
										 	var flashvars = {
												scaleMode: 'letterbox',
												xml_url: 'swf_player/params.xml'
											};
											var params = {
												allowFullScreen: 'true',
												allowScriptAccess: 'always',
												wmode: 'opaque'
											};
										
											var attributes = {};
											var embedHandler = function (e){ 
											   if (e.success){
												  console.log('The embed was successful!');
											   } else {
												  console.log('The embed failed!');
											   }											 
											};
											
											var el = document.getElementById('player');
											swfobject.embedSWF('swf_player/streamPlayer.swf', 'player', '100%', '100%', 10, 'swf_player/expressInstall.swf', flashvars, params, attributes, embedHandler);																						
										});
										
										function flashIsReady(){
											var url = '" . $data_url . "';
											var obj = new Object();
											if (url.indexOf('rtmp') >= 0) {
												obj.rtmp = [{'streamUrl': url}];
											} else if (url.indexOf('hls') >= 0) {
												obj.hls = [{'streamUrl': url}];
											} else if (url.indexOf('hds') >= 0){
												obj.hds = [{'streamUrl': url}];
											} else {
												console.log('Url содержит недопустимый формат.');
											}
											var box = (navigator.appName.indexOf('Microsoft')!=-1 ? window : document)['player'];
											box.sendToActionScript(obj);
										}
									</script>
									
									<div id=\"player\" class=\"player_container_inner\">
										<a href=\"http://www.adobe.com/go/getflashplayer\">
											<img src=\"http://www.adobe.com/images/shared/download_buttons/get_flash_player.gif\" alt=\"Get Adobe Flash player\" style=\"border:none;\">
										</a>
    								</div>";

							} else {
								echo "<video style=\"background-color:black;\" height=\"100%\" width=\"100%\" src=\"$data_url\" poster=\"$thumb\" controls autoplay></video>";
							}
							echo "
							</div>
							<div class=\"stream_data\">
								<div><p class=\"stream_statistics\">$stream_statistics</p></div><div class=\"text-right\"> HD <span href=\"#popup-embed\" class=\"link_embed text-center\"><span class=\"icon_embed\"><i class=\"fa fa-code\"></i></span></span></div>
							</div>
						</div><div class=\"block chat_container\">
							<div class=\"chat_wrapper\">
								<div class=\"chat $chat_class_name\">
									<div id=\"chat_messages\"></div>
									<form id=\"chat_form\" method=\"post\">
										<div id=\"chat_bottom\">
											<input type=\"hidden\" name=\"hero_id\" value=\"$hero_id\"/>
											<div class=\"left\">
												<input type=\"text\" name=\"message\" maxlength=\"255\" placeholder=\"say something\"/>
											</div>
											<div class=\"right\">
												<input type=\"submit\" class=\"btn theme-button\" name=\"send-comment\" value=\"" . _SEND . "\">
											</div>
										</div>
									</form>
								</div>
								<div class=\"actions\">
									<div class=\"actions-inner text-left\">
										<button type=\"button\" id=\"like\" data-like-state=\"$like_state\" class=\"btn theme-button-default $disabled\"> <i class=\"fa fa-heart\"></i></button> <button type=\"button\" id=\"follow\" class=\"btn theme-button-default $disabled\" data-following-state=\"$follow_state\" title=\"$follow_title\"><span>$follow_title</span> <i class=\"fa fa-star\"></i></button> <button type=\"button\" id=\"claim\" class=\"btn theme-button-default $disabled\" title=\"" . _CLAIM . "\"><i class=\"fa fa-ban\"></i></button>
										</div><div class=\"actions-inner text-right\">
											<a onclick=\"Share.vkontakte('$url','$stream_name','$snapshot','Смотрите $profile_name в проекте PROJECT_NAME')\"><span class=\"social_net_badge social_net_vk\"><span class=\"social_net_icon\"></span></span></a>
											<a onclick=\"Share.facebook('$url','$stream_name','$snapshot','Смотрите $profile_name в проекте PROJECT_NAME')\"><span class=\"social_net_badge social_net_fb\"><span class=\"social_net_icon\"></span></span></a>
											<a onclick=\"Share.twitter('$url','$stream_name')\"><span class=\"social_net_badge social_net_tw\"><span class=\"social_net_icon\"></span></span></a>
										</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class=\"row\">
					<div class=\"col-xs-10\">
						<p class=\"stream_name\">$stream_name</p>
						<p class=\"dull_text\">$stream_date_info</p>
					</div>
				</div>";

				$recorded_streams = $db -> sql_query("SELECT `uuid`, `permissions` FROM `streams` WHERE `uuid` != '$stream_uuid' AND `user_id` = '$hero_id' AND `end_date` != '0' AND `is_excess` = '0' AND `is_blocked` = '0' AND `is_deleted` = '0'", "", "array");

				if (sizeof($recorded_streams) > 0) {
					echo "
						<div class=\"row\">
							<div class=\"col-xs-10\">
								<p class=\"recorded-streams\">" . _RECORDED_STREAMS . "&nbsp;&nbsp;$profile_name&nbsp;&nbsp;<a class=\"prev\"><i class=\"fa fa-chevron-left\"></i></a>&nbsp;&nbsp;<a class=\"next\"><i class=\"fa fa-chevron-right\"></i></a></p>
							</div>
						</div>
						<div class=\"row\">
							<div class=\"col-xs-10\">
								<div id=\"streams-carousel\" class=\"owl-carousel owl-theme\">";
									foreach ($recorded_streams as $value) {

										$recorded_permissions_data = unserialize($value["permissions"]);
										if (sizeof($recorded_permissions_data) > 0 AND (in_array($user_id, $recorded_permissions_data) OR $user_id == $hero_id)) {
											$recorded_permissions = 1;
										} else if (sizeof($recorded_permissions_data) == 0 OR $user_id == $hero_id) {
											$recorded_permissions = 1;
										} else {
											$recorded_permissions = 0;
										}
										if ($recorded_permissions == 1){
											$recorded_stream_uuid = $value["uuid"];
											$recorded_preview_slide = $stream -> stream_preview_html($recorded_stream_uuid, "slide");
											echo $recorded_preview_slide;
										}
									}
								echo "
								</div>
							</div>
						</div>";
				}
			echo "
			</div>
		</div>

		<script>
			var Share = {
				vkontakte: function(page_url, title, img, text) {
					url  = 'http://vkontakte.ru/share.php?';
					url += 'url=' + encodeURIComponent(page_url);
					url += '&title='+ encodeURIComponent(title);
					url += '&description='+ encodeURIComponent(text);
					url += '&image='+ encodeURIComponent(img);
					url += '&noparse=true';
					Share.popup(url);
				},
				facebook: function(page_url, title, img, text) {
					url  = 'http://www.facebook.com/dialog/feed?app_id=409945959194208&display=popup';
					url += '&name='+ encodeURIComponent(title);
					url += '&description='+ encodeURIComponent(text);
					url += '&link='+ encodeURIComponent(page_url);
					url += '&picture='+ encodeURIComponent(img);
					Share.popup(url);
				},
				twitter: function(page_url, title) {
					url  = 'http://twitter.com/share?';
					url += 'text='+ encodeURIComponent(title);
					url += '&url='+ encodeURIComponent(page_url);
					url += '&counturl='+ encodeURIComponent(page_url);
					Share.popup(url);
				},
				popup: function(url) {
					window.open(url,'','toolbar=0,status=0,width=626,height=436');
				}
			};
			
			var STREAM_DATA = {
				admin_permissions: $admin_permissions,
				client_id: $user_id,
				embed_permissions: $embed_permissions,	
				hero_id: $hero_id,
				hero_display_name: \"" . $profile_name . "\",
				lang: \"" . $lang . "\",
				sid: \"" .$session_id. "\",
				stream_id: $stream_id,
				stream_uuid: \"" . $stream_uuid . "\",
				user_chat_blocked_state: $user_chat_blocked_state,
				user_auth_state: $user_auth_state,							
				websocket_server_url: \"" .$ws_url. "\"
			};
		</script>";
	}
}
?>