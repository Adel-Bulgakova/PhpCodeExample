<?php
function head($page_title = "", $page = "") {
	global $user;
	$user_id = $_SESSION["uid"];
	echo "<!DOCTYPE html>
	<html lang=\"en\">
		<head>
			<title>$page_title</title>
			<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />
			<meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge\" />
			<meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no\" />

			<meta property=\"fb:app_id\" content=\"\">
			<meta property=\"og:type\" content=\"website\" />
			<meta property=\"og:url\" content=\"https://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]\" />
			<meta property=\"og:title\" content />
			<meta property=\"og:description\" content />
			<meta property=\"og:image\" content />

			<link rel=\"preload\" href=\"/assets/fonts/Circe/Circe_Regular.otf\" as=\"font\" type=\"font/otf\" crossorigin>
			<link rel=\"preload\" href=\"/assets/fonts/Circe/Circe_Bold.otf\" as=\"font\" type=\"font/otf\" crossorigin>
			<link rel=\"preload\" href=\"/assets/fonts/Circe/Circe_Extra_Bold.otf\" as=\"font\" type=\"font/otf\" crossorigin>
			
			<link rel=\"icon\" type=\"image/png\" href=\"/assets/images/favicon.png\">
			<link rel=\"stylesheet\" href=\"/assets/lib/bootstrap-custom/css/bootstrap.min.css\" />
			<link rel=\"stylesheet\" href=\"/assets/fonts/font-awesome-4.5.0/css/font-awesome.min.css\" />
			<link rel=\"stylesheet\" href=\"/assets/lib/jquery-ui-1.11.4/jquery-ui.min.css\" />
			<link rel=\"stylesheet\" href=\"/assets/lib/select2/css/select2.min.css\" />
			<link rel=\"stylesheet\" href=\"/assets/css/animate.min.css\" />
			<link rel=\"stylesheet\" href=\"/assets/lib/Magnific-Popup/magnific-popup.css\"/>
			<link rel=\"stylesheet\" href=\"/assets/lib/Magnific-Popup/magnific-popup-styles.css\"/>
			<link rel=\"stylesheet\" href=\"/assets/lib/owl.carousel.2.0.0/assets/owl.carousel.css\" />
			<link rel=\"stylesheet\" href=\"/assets/lib/mediaelement-master/build/mediaelementplayer.css\" />
			<link rel=\"stylesheet\" href=\"/assets/css/styles.css\" />
			<link rel=\"stylesheet\" href=\"/assets/css/page_index.css\" />";

			if ($page == "page_play") {
				echo "
				<script type=\"text/javascript\" src=\"/assets/lib/swfobject.js\"></script>";
			}

			echo "

			<!--[if lt IE 9]>
				<script src=\"https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js\"></script>
				<script src=\"https://oss.maxcdn.com/respond/1.4.2/respond.min.js\"></script>
			<![endif]-->
			
			<script src=\"assets/js/jquery-2.1.4.min.js\"></script>
			<script src=\"assets/js/jquery.validate.min.js\"></script>

		</head>
		<body>
			<div id=\"wrapper\">
				<header class=\"header\">
					<nav class=\"navbar navbar-default\">
						<div class=\"container\">
							<div class=\"row\">
								<div class=\"col-xs-10\">
									<div class=\"navbar-header\">
										<button type=\"button\" class=\"navbar-toggle collapsed\" data-toggle=\"collapse\" data-target=\"#bs-example-navbar-collapse-1\" aria-expanded=\"false\">
											<span class=\"sr-only\">Toggle navigation</span>
											<span class=\"icon-bar\"></span>
											<span class=\"icon-bar\"></span>
											<span class=\"icon-bar\"></span>
										</button>
										<a href=\"/\" class=\"navbar-brand\"></a>
									</div>

									<div class=\"collapse navbar-collapse\" id=\"bs-example-navbar-collapse-1\">
										<ul class=\"nav navbar-nav\">
											<li class=\"active\"><a href=\"/\">Live!</a></li>
											<li><a href=\"/index.php?route=page_official\">" . _OFFICIAL . "</a></li>
											<li><a href=\"/index.php?route=page_top\">" . _TOP_STREAMS . "</a></li>
										</ul>

										<ul class=\"nav navbar-nav navbar-right\">";
											if (isset($user_id) AND !empty($user_id)) {
												$profile_image = $user -> profile_image_html($user_id);
												$profile_name = $user -> profile_name($user_id);
												echo "
												<li><a href=\"/index.php?route=page_profile\" class=\"profile_link\">$profile_image <div>$profile_name</div></a></li>
												<li><a href=\"/index.php?route=proc_logout\">" . _LOGOUT . "</a></li>";
											} else {
												echo "
												<li><a href=\"/index.php?route=page_start\" id=\"login\">" . _AUTHORIZATION . "</a></li>";
											}
											echo "
										</ul>
									</div>
								</div>
							</div>
						</div>
					</nav>
				</header>
				<div id=\"content\">";
}

function top_streams(){
	echo "
			<div class=\"section top_streams\">
				<div class=\"container\">
					<div class=\"row\">
						<div class=\"col-xs-6 col-md-8\">
							<p>" . _TOP_STREAMS . "&nbsp;&nbsp;<a class=\"prev\"><i class=\"fa fa-chevron-left\"></i></a>&nbsp;&nbsp;<a class=\"next\"><i class=\"fa fa-chevron-right\"></i></a></p>
						</div>
						<div class=\"col-xs-4 col-md-2 text-right\"><a href=\"/index.php?route=page_top\" class=\"btn theme-button\">" . _WATCH_ALL . "</a>
						</div>
					</div>
					<div class=\"row data-top\">
						<div class=\"col-xs-10\">";
							get_top_streams("index_page");
							echo "
						</div>
					</div>
				</div>
			</div>
		";
}

function official(){
	echo "
			<div class=\"section official_streams\">
				<div class=\"container\">
					<div class=\"row\">
						<div class=\"col-xs-6 col-md-8\">
							<p>" . _OFFICIAL . "&nbsp;&nbsp;<a class=\"prev\"><i class=\"fa fa-chevron-left\"></i></a>&nbsp;&nbsp;<a class=\"next\"><i class=\"fa fa-chevron-right\"></i></a></p>
						</div>
						<div class=\"col-xs-4 col-md-2 text-right\"><a href=\"index.php?route=page_official\" class=\"btn theme-button\">" . _WATCH_ALL . "</a>
						</div>
					</div>
					<div class=\"row data-official\">
						<div class=\"col-xs-10\">";
							get_official_streams("index_page");
							echo "
						</div>
					</div>
				</div>
			</div>
		";
}

function filters(){
	global $db;
	echo "
		<div class=\"section filters\">
			<div class=\"container\">

				<div class=\"row search\">
					<div class=\"col-xs-10\">
						<form method=\"get\" name=\"search_form\">
							<div class=\"input-group input-group-lg\">
								<input type=\"text\" class=\"form-control\" id=\"search_input\" placeholder=\"" . _SEARCH . "\" data-toggle=\"dropdown\">
								<div class=\"dropdown-menu search_results\"></div>
								<div class=\"input-group-btn\">
									<button type=\"button\" class=\"btn theme-button dropdown-toggle\" data-toggle=\"dropdown\" aria-haspopup=\"true\" aria-expanded=\"false\">
										<span class=\"bar_icons\">
											<span class=\"icon-bar\"></span>
											<span class=\"icon-bar\"></span>
											<span class=\"icon-bar\"></span>
										</span>
										<span class=\"caret\"></span>
									</button>
									<div class=\"dropdown-menu dropdown-menu-right\">
										<p>" . _POPULAR_TAGS . "</p>";
										$result_streams_tags = $db -> sql_query("SELECT stream_tag_id, streams_tags_data.name AS tag_name, COUNT(stream_tag_id) AS tags_count, streams_tags.stream_id AS stream_id FROM streams_tags LEFT JOIN streams_tags_data ON streams_tags_data.id = streams_tags.stream_tag_id LEFT JOIN streams ON streams.id = stream_id WHERE streams.is_excess = '0' AND streams.is_blocked = '0' AND streams.is_deleted = '0' AND streams_tags.is_deleted = '0' AND streams_tags_data.is_disabled = '0' GROUP BY tag_name ORDER BY tags_count DESC LIMIT 10", "", "array");
										if (sizeof($result_streams_tags) > 0){
											foreach ($result_streams_tags as $key => $value) {
												$tag_id = $value["stream_tag_id"];
												$name = $value["tag_name"];
												$line_break = "";
												if ($key % 3 == 0 AND $key != 0) {
													$line_break = "<br>";
												}
												echo "<a href=\"#\" class=\"btn btn-default tag\" data-tag-id=\"$tag_id\">$name</a>$line_break";

											}
										}
								echo "
									</div>
								</div>
							</div>
						</form>
					</div>
				</div>

			</div>
		</div>";
}

function footer($page = "") {
	global $lang, $project_options;
	$year = date("Y");
	$ru_enabled = "";
	$en_enabled = "";

	if ($lang == "ru") {
		$ru_enabled = "current_lang";
	} else {
		$en_enabled = "current_lang";
	}

	$appstore_link  = $project_options["appstore_link"];
	$playstore_link	= $project_options["playstore_link"];

	$current_url = $_SERVER["REQUEST_SCHEME"] . "://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];

	$cut_str = array("&lang=ru", "&lang=en");
	$current_url = str_replace($cut_str, "", $current_url);
	$switch_to_en_link = $current_url."&lang=en";
	$switch_to_ru_link = $current_url."&lang=ru";
	if (!$_SERVER["REQUEST_URI"]) {
		$switch_to_en_link = $current_url."?&lang=en";
		$switch_to_ru_link = $current_url."?&lang=ru";
	}
	echo "
			</div> <!--content end-->
			<footer>
				<div id=\"subfooter\">
					<div class=\"container\">
						<div class=\"row\">
							<div class=\"subfooter-inner col-xs-10 col-sm-5 col-md-6 col-lg-7\">
								<ul id=\"bottom-info-links\">
									<li><a href=\"index.php?route=page_terms\">" . _TERMS_OF_USE . "</a></li>
									<li><a href=\"index.php?route=page_privacy\">" . _PRIVACY_POLICY . "</a></li>
									<li><a href=\"index.php?route=page_help_faq\">" . _FAQ . "</a></li>
									<li><a href=\"index.php?route=page_support\">" . _CHAT_SUPPORT . "</a></li>
									<li><a href=\"index.php?route=page_map\">" . _STREAMS_MAP . "</a></li>
								</ul>
							</div>
							<div class=\"col-xs-10 col-sm-5 col-md-4 col-lg-3\">
								<div class=\"app_links\">
									<a class=\"app_link app_store_link\" href=\"$appstore_link\" title=\"Download on the App Store\"></a>
									<a class=\"app_link play_store_link\"  href=\"$playstore_link\" title=\"Get it on Google Play\"></a>
								</div>
							</div>
						</div>
					</div>
				</div>

				<div id=\"footer\">
					<div class=\"container\">
						<div class=\"row\">
							<div class=\"col-xs-7 text-left\">&copy; " . _PROGECT_NAME . ", $year. " . _ALL_RIGHTS_RESERVED . "</div>
							<div class=\"col-xs-3 text-right langs\"><a href=\"$switch_to_ru_link\" class=\"$ru_enabled\">RU</a> | <a href=\"$switch_to_en_link\" class=\"$en_enabled\">EN</a></div>
						</div>
					</div>
				</div>

			</footer>
		</div><!--wrapper end-->

		<a href=\"#\" class=\"scroll_to_top\"><span>" . _TO_TOP . "</span></a>

		<script>
			var Constants = {
				SEND: \"" . _SEND . "\",
				CANCEL: \"" . _CANCEL . "\",
				CLOSE: \"" . _CLOSE . "\",
				CLAIM: \"" . _CLAIM . "\",
				CLAIM_ACCEPTED: \"" . _CLAIM_ACCEPTED . "\",
				ERROR_SEARCH_RESULTS: \"" . _ERROR_SEARCH_RESULTS . "\",
				NO_STREAMS_BY_TAG: \"" . _NO_STREAMS_BY_TAG . "\",
				REQUEST_FAILED_INFO: \"" . _REQUEST_FAILED_INFO . "\",
				REQUEST_SUCCESS_INFO: \"" . _REQUEST_SUCCESS_INFO . "\",
				ERR_FILL_LOGIN: \"" . _ERROR_FILL_LOGIN . "\",
				ERR_FILL_EMAIL: \"" . _ERROR_FILL_EMAIL . "\",
				CONNECTED: \"" . _CONNECTED. "\",
				LIKED_THIS: \"" . _LIKED_THIS. "\",
				BLOCKED: \"" . _BLOCKED. "\",
				CONNECTION_CLOSED: \"" . _CONNECTION_CLOSED. "\",
				BLOCK_USER: \"" . _BLOCK_USER. "\",
				FOLLOWS: \"" . _FOLLOWS. "\"
			}
		</script>";
	
		if ($page == "page_play" || $page == "page_play2") {
			echo "<script type=\"text/javascript\" src=\"/assets/js/project.websockets.js\"></script>";
		}

		if ($page == "" || $page == "page_index") {
			echo "<script src=\"/assets/js/project.page.index.js\"></script>";
		}

	echo "
		<script src=\"/assets/lib/bootstrap-custom/js/bootstrap.min.js\"></script>
	    <script src=\"/assets/lib/icheck-1.0.2/icheck.min.js\"></script>
	    <script src=\"/assets/lib/jquery.mousewheel.min.js\"></script>
	    <script src=\"/assets/js/jquery.nicescroll.min.js\"></script>
	    <script src=\"/assets/js/jquery.cookie.js\"></script>
	    <script src=\"/assets/js/project.auth.js\"></script>
	    <script src=\"/assets/js/project.scripts.js\"></script>
	    <script src=\"/assets/lib/owl.carousel.2.0.0/owl.carousel.min.js\"></script>
		<script src=\"/assets/lib/Magnific-Popup/jquery.magnific-popup.min.js\"></script>
		<script src=\"/assets/lib/mediaelement-master/build/mediaelement-and-player.js\"></script>
		<script src=\"/assets/lib/oms.min.js\"></script>";

		echo "
    </body>
</html>";
}

function footer_test($page = "") {
	global $lang, $project_options;
	$year = date("Y");
	$ru_enabled = "";
	$en_enabled = "";

	if ($lang == "ru") {
		$ru_enabled = "current_lang";
	} else {
		$en_enabled = "current_lang";
	}

	$appstore_link  = $project_options["appstore_link"];
	$playstore_link	= $project_options["playstore_link"];

	$current_url = $_SERVER["REQUEST_SCHEME"] . "://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];
	$cut_str = array("&lang=ru", "&lang=en");
	$current_url = str_replace($cut_str, "", $current_url);

	echo "
			</div> <!--content end-->
			<footer>
				<div id=\"subfooter\">
					<div class=\"container\">
						<div class=\"row\">
							<div class=\"subfooter-inner col-xs-10 col-sm-5 col-md-6 col-lg-7\">
								<ul id=\"bottom-info-links\">
									<li><a href=\"index.php?route=page_terms\">" . _TERMS_OF_USE . "</a></li>
									<li><a href=\"index.php?route=page_privacy\">" . _PRIVACY_POLICY . "</a></li>
									<li><a href=\"index.php?route=page_help_faq\">" . _FAQ . "</a></li>
									<li><a href=\"index.php?route=page_support\">" . _CHAT_SUPPORT . "</a></li>
									<li><a href=\"index.php?route=page_map\">" . _STREAMS_MAP . "</a></li>
								</ul>
							</div>
							<div class=\"col-xs-10 col-sm-5 col-md-4 col-lg-3\">
								<div class=\"app_links\">
									<a class=\"app_link app_store_link\" href=\"$appstore_link\" title=\"Download on the App Store\"></a>
									<a class=\"app_link play_store_link\"  href=\"$playstore_link\" title=\"Get it on Google Play\"></a>
								</div>
							</div>
						</div>
					</div>
				</div>

				<div id=\"footer\">
					<div class=\"container\">
						<div class=\"row\">
							<div class=\"col-xs-7 text-left\">&copy; " . _PROGECT_NAME . ", $year. " . _ALL_RIGHTS_RESERVED . "</div>
							<div class=\"col-xs-3 text-right langs\"><a href=\"$current_url&lang=ru\" class=\"$ru_enabled\">RU</a> | <a href=\"$current_url&lang=en\" class=\"$en_enabled\">EN</a></div>
						</div>
					</div>
				</div>

			</footer>
		</div><!--wrapper end-->

		<a href=\"#\" class=\"scroll_to_top\"><span>" . _TO_TOP . "</span></a>

		<script>
			var Constants = {
				SEND: \"" . _SEND . "\",
				CANCEL: \"" . _CANCEL . "\",
				CLOSE: \"" . _CLOSE . "\",
				CLAIM: \"" . _CLAIM . "\",
				CLAIM_ACCEPTED: \"" . _CLAIM_ACCEPTED . "\",
				ERROR_SEARCH_RESULTS: \"" . _ERROR_SEARCH_RESULTS . "\",
				NO_STREAMS_BY_TAG: \"" . _NO_STREAMS_BY_TAG . "\",
				REQUEST_FAILED_INFO: \"" . _REQUEST_FAILED_INFO . "\",
				REQUEST_SUCCESS_INFO: \"" . _REQUEST_SUCCESS_INFO . "\",
				ERR_FILL_LOGIN: \"" . _ERROR_FILL_LOGIN . "\",
				ERR_FILL_EMAIL: \"" . _ERROR_FILL_EMAIL . "\",
				CONNECTED: \"" . _CONNECTED. "\",
				LIKED_THIS: \"" . _LIKED_THIS. "\",
				BLOCKED: \"" . _BLOCKED. "\",
				CONNECTION_CLOSED: \"" . _CONNECTION_CLOSED. "\",
				BLOCK_USER: \"" . _BLOCK_USER. "\",
				FOLLOWS: \"" . _FOLLOWS. "\"
			}
		</script>";

	 	if ($page == "" || $page == "page_index" || $page == "page_play" || $page == "page_official" || $page == "page_top") {
			echo "<script src=\"/assets/js/project.clientcount.js\"></script>";
		}

		if ($page == "" || $page == "page_index") {
			echo "<script src=\"/assets/js/project.page.index.js\"></script>";
		}

	echo "
		<script src=\"/assets/lib/bootstrap-custom/js/bootstrap.min.js\"></script>
	    <script src=\"/assets/lib/icheck-1.0.2/icheck.min.js\"></script>
	    <script src=\"/assets/lib/jquery.mousewheel.min.js\"></script>
	    <script src=\"/assets/js/jquery.nicescroll.min.js\"></script>
	    <script src=\"/assets/js/jquery.cookie.js\"></script>
	    <script src=\"/assets/js/project.auth.js\"></script>
	    <script src=\"/assets/lib/owl.carousel.2.0.0/owl.carousel.min.js\"></script>
		<script src=\"/assets/lib/Magnific-Popup/jquery.magnific-popup.min.js\"></script>
		<script src=\"/assets/lib/mediaelement-master/build/mediaelement-and-player.js\"></script>
		<script src=\"/assets/lib/oms.min.js\"></script>
    </body>
</html>";
}


function top_banners(){
	echo "
		<div class=\"top_banner\">
			<div class=\"banners-container-775\" style=\"display:none\">
				<div><a class=\"banner_link\" href=\"#\" style=\"height:130px; background-image: url('/assets/banners/775/1.jpg'); background-color:#74b3da\"></a></div>
				<div><a class=\"banner_link\" href=\"#\" style=\"height:130px; background-image: url('/assets/banners/775/2.jpg');background-color:#343434\"></a></div>
				<div><a class=\"banner_link\" href=\"#\" style=\"height:130px; background-image: url('/assets/banners/775/3.jpg'); background-color:#e2bfe4\"></a></div>
			</div>

			<div class=\"banners-container-991\" style=\"display:none\">
				<div><a class=\"banner_link\" href=\"#\" style=\"height:130px; background-image: url('/assets/banners/991/1.jpg'); background-color:#74b3da\"></a></div>
				<div><a class=\"banner_link\" href=\"#\" style=\"height:130px; background-image: url('/assets/banners/991/2.jpg');background-color:#343434\"></a></div>
				<div><a class=\"banner_link\" href=\"#\" style=\"height:130px; background-image: url('/assets/banners/991/3.jpg'); background-color:#e2bfe4\"></a></div>
			</div>

			<div class=\"banners-container-1024\" style=\"display:none\">
				<div><a class=\"banner_link\" href=\"#\" style=\"height:130px; background-image: url('/assets/banners/1024/1.jpg'); background-color:#74b3da\"></a></div>
				<div><a class=\"banner_link\" href=\"#\" style=\"height:130px; background-image: url('/assets/banners/1024/2.jpg');background-color:#343434\"></a></div>
				<div><a class=\"banner_link\" href=\"#\" style=\"height:130px; background-image: url('/assets/banners/1024/3.jpg'); background-color:#e2bfe4\"></a></div>
			</div>

			<div class=\"banners-container-1200\" style=\"display:none\">
				<div><a class=\"banner_link\" href=\"#\" style=\"height:130px; background-image: url('/assets/banners/1200/1.jpg'); background-color:#74b3da\"></a></div>
				<div><a class=\"banner_link\" href=\"#\" style=\"height:130px; background-image: url('/assets/banners/1200/2.jpg');background-color:#343434\"></a></div>
				<div><a class=\"banner_link\" href=\"#\" style=\"height:130px; background-image: url('/assets/banners/1200/3.jpg'); background-color:#e2bfe4\"></a></div>
			</div>
		</div>";
}


function get_top_streams($page) {
	global $stream;

	$top_streams_array = $stream -> get_top_streams(10);

	if (empty($top_streams_array)){
		echo _NO_STREAMS;
	} else {

		if ($page == "inner_page"){
			#Отображение первых пяти устройств отсортированного массива
			/*for ($i = 0; $i < 4; $i++){ //чтобы отображалось 4 потока в строке 
				$stream_uuid = $top_streams_array[$i];
				$stream_preview = $stream -> stream_preview_html($stream_uuid, "default");
				echo $stream_preview;
			}*/
			
			for ($i = 0; $i < 4; $i++){
                $stream_uuid = $top_streams_array[$i];
                $stream_preview = $stream -> stream_preview_html($stream_uuid, "top_official");
                echo $stream_preview;
            }
			
		} else {
			echo "
				<div id=\"top-carousel\">";
					for ($i = 0; $i < 9; $i++){
						$stream_uuid = $top_streams_array[$i];

						$stream_preview_slide = $stream -> stream_preview_html($stream_uuid, "slide");
						echo $stream_preview_slide;
					}
			echo "</div>";
		}
	}
}

function get_official_streams($page) {
	global $stream;

	$official_streams_array = $stream -> get_official_streams(10);

	if (empty($official_streams_array)){
		echo _NO_STREAMS;
	} else {
		if ($page == "inner_page"){
			for ($i = 0; $i < 4; $i++){
				$stream_uuid = $official_streams_array[$i];
				$stream_preview = $stream -> stream_preview_html($stream_uuid, "default");
				echo $stream_preview;
			}
		} else {
			echo "
				<div id=\"official-carousel\" class=\"owl-carousel owl-theme\">";
				for ($i = 0; $i < 4; $i++){
					$stream_uuid = $official_streams_array[$i];

					$stream_preview_slide = $stream -> stream_preview_html($stream_uuid, "slide");
					echo $stream_preview_slide;
				}
			echo "</div>";
		}
	}
}

# Проверяет существование пользователя в системе
function user($user_id = 0) {
	global $db;
	$result_user = $db -> sql_query("SELECT * FROM `users` WHERE `id` = '$user_id' AND `is_deleted` = '0'", "", "array");
	if (sizeof($result_user) > 0 AND !empty($result_user[0])){
		return true;
	} else {
		return false;
	}
}

# Проверяет существование администратора службы поддержки в системе
function admin($admin_id = 0) {
	global $db;
	$result_admin = $db -> sql_query("SELECT * FROM `support_service_admins` WHERE `id` = '$admin_id' AND `is_deleted` = '0'", "", "array");
	if (sizeof($result_admin) > 0 AND !empty($result_admin[0])){
		return true;
	} else {
		return false;
	}
}

#Проверяет наличие авторизированного пользователя и возвращает его id
function get_current_session_user() {
	global $db;
	if (isset($_SESSION["uid"]) AND !empty($_SESSION["uid"])){
		$user_id = $_SESSION["uid"];
		$result_user = $db -> sql_query("SELECT `id` FROM `users` WHERE `id` = '$user_id' AND `is_deleted` = '0'", "", "array");
		if (sizeof($result_user) > 0){
			return $user_id;
		} else {
			return false;
		}
	} else {
		return false;
	}
}

#Проверяет право пользователя комментировать запрашиваемую трансляцию
function chat_permissions($stream_uuid = "", $user_id = 0) {
	global $db, $user;
	$result = false;
	$result_stream = $db -> sql_query("SELECT `user_id`, `chat_permissions` FROM `streams` WHERE `uuid` = '$stream_uuid' AND `is_deleted` = '0'", "", "array");
	if (sizeof($result_stream) > 0){
		$hero_id = $result_stream[0]["user_id"];
		#Ограничение возможности комментировать в чате свою трансляцию
		if ($user_id != $hero_id) {
			$chat_permissions_data = $result_stream[0]["chat_permissions"];

			$blocked_users_array = $user -> blocked($hero_id);

			# Находится ли этот пользователь в списке заблокированных владелецем трансляции пользователей
			if (!in_array($user_id, $blocked_users_array)) {
				# Ситуация, когда владелец трансляции разрешил комментировать транляцию только тем пользователям, на которых он подписан
				if ($chat_permissions_data == 0) {
					$following_users = $user -> following($hero_id);
					#Находится ли этот пользователь в списке тех, на которых подписан владелец трансляции
					if (in_array($user_id, $following_users)) {
						$result = true;
					}
				} else {#Ситуация, когда владелец трансляции разрешил всем пользователям комментировать транляцию
					$result = true;
				}
			}
		}
	}
	return $result;
}

#Возвращает массив, в котором ключами являются параметры прав доступа зрителя для запрашиваемой трансляции
function permissions($stream_uuid = "") {
	global $db, $user;
	$user_id = get_current_session_user(); #Проверяет есть ли авторизированный пользователь

	$result["chat_blocked"] = 1; #Статус заблокированного пользователя владельцем трансляции, 0-не блокирован, 1-блокирован
	$result["auth_state"] = 0; #Статус пользователя, 0-не авторизован, 1-авторизован
	$result["watch"] = 0; #Возможность смотреть трансляцию
	$result["embed"] = 0; #Возможность использовать код плеера для вставки (если пользователь разрешил просмотр всем пользователям)

	$result_stream = $db -> sql_query("SELECT * FROM `streams` WHERE `uuid` = '$stream_uuid' AND `is_excess` = '0' AND `is_blocked` = '0' AND `is_deleted` = '0'", "", "array");
	if (sizeof($result_stream) > 0){
		$hero_id = $result_stream[0]["user_id"];
		$watch_permissions_data = unserialize($result_stream[0]["permissions"]);
		$chat_permissions_data = $result_stream[0]["chat_permissions"];

		if ($user_id){ #Если запрашивает авторизированный пользователь
			$result["auth_state"] = 1;

			$blocked_users_array = $user -> blocked($hero_id); #Массив id заблокированных пользователей

			if (in_array($user_id, $blocked_users_array)) {#Если пользователь находится в списке заблокированных пользователей владельца трансляции
				$result["chat_blocked"] = 1;
			} else {
				if ($chat_permissions_data == 0) { #Владелец трансляции разрешил комментировать только тем пользователям, на которых он подписан
					$following_users = $user -> following($hero_id);
					if (in_array($user_id, $following_users)) {#Если пользователь находится в списке подписок владельца трансляции, то он получает возможность комментировать ее
						$result["chat_blocked"] = 0;
					} else {
						$result["chat_blocked"] = 1;
					}
				} else { #Владелец трансляции разрешил комментировать всем пользователям
					$result["chat_blocked"] = 0;
				}
			}
		} else {
			$result["auth_state"] = 0;
		}

		if (sizeof($watch_permissions_data) > 0) { #Если владелец трансляции разрешил просмотр только определенному списку пользователей
			if ($user_id AND (in_array($user_id, $watch_permissions_data) OR $hero_id == $user_id)) { #Если запрашивает авторизированный пользователь и пользователь находится в списке пользователей, которому разрешен просмотр
				$result["watch"] = 1;
			}
		} else { #Если владелец трансляции разрешил просмотр всем пользователям
			$result["watch"] = 1;
			$result["embed"] = 1;
		}
	}
	return $result;
}

#Возвращает параметры прав доступа связки пользователь/устройство
function user_access_status($user_id = 0, $device_uuid = "") {
	global $db;

	$result = $db -> sql_query("SELECT `users`.`is_blocked` AS `user_is_blocked`, `users`.`is_deleted` AS `user_is_deleted` FROM `devices` LEFT JOIN `users` ON `devices`.`user_id` = `users`.`id` WHERE `device_uuid` = '$device_uuid' AND `devices`.`user_id` = '$user_id' AND `devices`.`is_blocked` = '0' AND `devices`.`is_deleted` = '0'", "", "array");

	if (sizeof($result) > 0 AND !empty($result[0])){
		$user_is_blocked = $result[0]["user_is_blocked"];
		$user_is_deleted = $result[0]["user_is_deleted"];

		if ($user_is_blocked){
			$response["status"] = "ACCESS-DENIED";
			$response["message"] = "ACC-BLOCKED";
		} else if ($user_is_deleted){
			$response["status"] = "ACCESS-DENIED";
			$response["message"] = "ACC-DELETED";
		} else {
			$response["status"] = "OK";
		}
	} else {
		$response["status"] = "ACCESS-DENIED";
		$response["message"] = "NOT-FOUND";
	}

	return $response;
}

#Возвращает статус доступа web-пользователя, пользователя мобильного приложения к api проектa или администратора службы поддержки
function client_request_access_status($current_request_type) {
	global $api, $db, $log_file, $user, $project_options;

	$ip = $user -> get_client_ip();
	$time = date("H:i");
	$current_date = time();

	$site_auth_login = $project_options["site_auth_login"];
	$site_auth_pass = $project_options["site_auth_pass"];

	if (isset($_SERVER["HTTP_ACCESS_CODE"])) { #Проверка кода доступа, полученного для методов авторизации, регистрации, генерации кода по номеру телефона
		
		$response = $api -> access_code_check($current_request_type);
		return $response;

	} else {#Проверка access_token доступа, полученного авторизированными пользователями
		if (isset($_SERVER["HTTP_AUTH_SIGNATURE"]) AND isset($_SERVER["HTTP_ACCESS_TOKEN"])) {
			$oauth_signature = $_SERVER["HTTP_AUTH_SIGNATURE"];
			$access_token = $_SERVER["HTTP_ACCESS_TOKEN"];

			$result_access_token_for_device = $db -> sql_query("SELECT * FROM `access_tokens_devices` WHERE `access_token` = '$access_token'", "", "array");

			$result_access_token_for_web_user = $db -> sql_query("SELECT * FROM `access_tokens_web` WHERE `access_token` = '$access_token'", "", "array");

			$result_access_token_for_admin = $db -> sql_query("SELECT * FROM `access_tokens_admins` WHERE `access_token` = '$access_token'", "", "array");

			if (sizeof($result_access_token_for_device) > 0){ #Поиск устройства и пользователя по полученной подписи запроса

				$access_token_expired_date = $result_access_token_for_device[0]["access_token_expired_date"];
				$request_type = $result_access_token_for_device[0]["request_type"];

				if ($request_type == $current_request_type) { #Сравнение текущего request_type с  request_type, для которого был получен access_token
					if ($current_date <= $access_token_expired_date) { #Проверка срока действия access_token
						$device_id = $result_access_token_for_device[0]["device_id"];

						$result_device_by_oauth_signature = $db -> sql_query("SELECT * FROM `devices` WHERE `sha1_encrypt_id` = '$oauth_signature' AND `id` = '$device_id'", "", "array");

						$device_uuid = $result_device_by_oauth_signature[0]["device_uuid"];
						$user_id = $result_device_by_oauth_signature[0]["user_id"];
						$user_access_status = user_access_status($user_id, $device_uuid);

						if ($user_access_status["status"] == "OK") {
							if (isset($_SERVER["HTTP_SESSION_ID"])) {
								$session_id = $_SERVER["HTTP_SESSION_ID"];
								$result_session_id = $db -> sql_query("SELECT `id` FROM `users_sessions_devices` WHERE `session_id` = '$session_id' AND `device_id` = '$device_id' AND `end_date` = '0'", "", "array");
								if (sizeof($result_session_id) > 0) {
									$response["status"] = "OK";
									$response["client_status"] = "user";
									$response["user_id"] = $user_id;
									$response["device_uuid"] = $device_uuid;
									$response["device_id"] = $device_id;
									return $response;
								}

								$response["status"] = "ACC-SESSION-EXPIRED";
								error_log("[$time $ip] client_request_access: ACC-SESSION-EXPIRED request_type - $request_type, current_request_type - $current_request_type, access_token - $access_token, oauth_signature - $oauth_signature, session_id - $session_id\n", 3, $log_file);
								return $response;
							}

							$response["status"] = "ACC-SESSION-EXPIRED";
							error_log("[$time $ip] client_request_access: ACC-SESSION-EXPIRED request_type - $request_type, current_request_type - $current_request_type, access_token - $access_token, oauth_signature - $oauth_signature\n", 3, $log_file);
							return $response;
						}

						$status = $user_access_status["status"];
						$message = $user_access_status["message"];
						$response["status"] = $status;
						$response["message"] = $message;
						error_log("[$time $ip] client_request_access: $status $message request_type - $request_type, current_request_type - $current_request_type, access_token - $access_token, oauth_signature - $oauth_signature\n", 3, $log_file);
						return $response;
					}

					$response["status"] = "ACCESS-TOKEN-EXPIRED";
					error_log("[$time $ip] client_request_access: ACCESS-TOKEN-EXPIRED request_type - $request_type, current_request_type - $current_request_type, access_token - $access_token, oauth_signature - $oauth_signature\n", 3, $log_file);
					return $response;
				}

				$response["status"] = "INVALID-REQUEST-TYPE";
				error_log("[$time $ip] client_request_access: INVALID-REQUEST-TYPE request_type - $request_type, current_request_type - $current_request_type, access_token - $access_token, oauth_signature - $oauth_signature\n", 3, $log_file);
				return $response;

			} else if (sizeof($result_access_token_for_web_user) > 0) { #Подпись не найдена среди пользователей мобильных приложений, осуществляется поиск пользователя по полученной подписи запроса среди web-пользователей
				$access_token_expired_date = $result_access_token_for_web_user[0]["access_token_expired_date"];
				$request_type = $result_access_token_for_web_user[0]["request_type"];
				$user_id = $result_access_token_for_web_user[0]["user_id"];

				if ($request_type == $current_request_type) { #Сравнение текущего request_type с  request_type, для которого был получен access_token
					if ($current_date <= $access_token_expired_date) { #Проверка срока действия access_token
						$result_user_by_oauth_signature = $db -> sql_query("SELECT `id` FROM `users` WHERE SHA1(CONCAT('$site_auth_login', '$site_auth_pass', `id`)) = '$oauth_signature' AND `id` = '$user_id' AND `is_deleted` = '0'", "", "array");

						if (sizeof($result_user_by_oauth_signature) > 0){
							$response["status"] = "OK";
							$response["client_status"] = "user";
							$response["user_id"] = $user_id;
							error_log("[$time $ip] client_request_access: OK request_type - $request_type, current_request_type - $current_request_type, access_token - $access_token, oauth_signature - $oauth_signature users\n", 3, $log_file);
							return $response;
						}

						$status = "ACCESS-DENIED";
						$message = "NOT-FOUND";
						$response["status"] = $status;
						$response["message"] = $message;
						error_log("[$time $ip] client_request_access: $status $message request_type - $request_type, current_request_type - $current_request_type, access_token - $access_token, oauth_signature - $oauth_signature\n", 3, $log_file);
						return $response;
					}
					$response["status"] = "ACCESS-TOKEN-EXPIRED";
					error_log("[$time $ip] client_request_access: ACCESS-TOKEN-EXPIRED request_type - $request_type, current_request_type - $current_request_type, access_token - $access_token, oauth_signature - $oauth_signature\n", 3, $log_file);
					return $response;
				}

				$response["status"] = "INVALID-REQUEST-TYPE";
				error_log("[$time $ip] client_request_access: INVALID-REQUEST-TYPE request_type - $request_type, current_request_type - $current_request_type, access_token - $access_token, oauth_signature - $oauth_signature\n", 3, $log_file);
				return $response;

			} else if (sizeof($result_access_token_for_admin) > 0) {#Подпись не найдена среди пользователей мобильных приложений и web-пользователей, осуществляется поиск пользователя по полученной подписи запроса среди администраторов службы поддержки
				$access_token_expired_date = $result_access_token_for_admin[0]["access_token_expired_date"];
				$request_type = $result_access_token_for_admin[0]["request_type"];
				$admin_id = $result_access_token_for_admin[0]["admin_id"];

				if ($request_type == $current_request_type) { #Сравнение текущего request_type с  request_type, для которого был получен access_token
					if ($current_date <= $access_token_expired_date) { #Проверка срока действия access_token

						# Поиск администратора службы поддержки по полученной подписи
						$result_admin_by_oauth_signature = $db -> sql_query("SELECT `id` FROM `support_service_admins` WHERE SHA1(CONCAT('$site_auth_login', '$site_auth_pass', `id`, 'admin')) = '$oauth_signature' AND `id` = '$admin_id' AND `is_deleted` = '0'", "", "array");

						if (sizeof($result_admin_by_oauth_signature) > 0){
							$response["status"] = "OK";
							$response["client_status"] = "admin";
							$response["admin_id"] = $admin_id;
							return $response;
						}
						$status = "ACCESS-DENIED";
						$message = "NOT-FOUND";
						$response["status"] = $status;
						$response["message"] = $message;
						error_log("[$time $ip] client_request_access: $status $message request_type - $request_type, current_request_type - $current_request_type, access_token - $access_token, oauth_signature - $oauth_signature\n", 3, $log_file);
						return $response;
					}

					$response["status"] = "ACCESS-TOKEN-EXPIRED";
					error_log("[$time $ip] client_request_access: ACCESS-TOKEN-EXPIRED request_type - $request_type, current_request_type - $current_request_type, access_token - $access_token, oauth_signature - $oauth_signature\n", 3, $log_file);
					return $response;
				}

				$response["status"] = "INVALID-REQUEST-TYPE";
				error_log("[$time $ip] client_request_access: INVALID-REQUEST-TYPE request_type - $request_type, current_request_type - $current_request_type, access_token - $access_token, oauth_signature - $oauth_signature\n", 3, $log_file);
				return $response;

			}

			$response["status"] = "INVALID-ACCESS-TOKEN";
			error_log("[$time $ip] client_request_access: INVALID-ACCESS-TOKEN current_request_type - $current_request_type, access_token - $access_token, oauth_signature - $oauth_signature\n", 3, $log_file);
			return $response;
		}

		$response["status"] = "ACCESS-DENIED";
		error_log("[$time $ip] client_request_access: not authorized user current_request_type - $current_request_type, HTTP_AUTH_SIGNATURE - ".$_SERVER["HTTP_AUTH_SIGNATURE"]. ", HTTP_ACCESS_TOKEN ". $_SERVER["HTTP_ACCESS_TOKEN"] ."\n", 3, $log_file);
		return $response;
	}
}

# Возвращает access_token для выполнения запроса к api проекта
function get_access_token($request_type, $user_id) {
	global $project_options;
	$oauth_signature = sha1($project_options["site_auth_login"].$project_options["site_auth_pass"].$user_id);

	$context = stream_context_create(
		array(
			'http'=>array(
				'header'=> "Auth-Signature: " . $oauth_signature . "\r\n" . "Request-Type: " . $request_type . "\r\n",
				'method' => 'GET'
			)
		)
	);
	$result_access_token =  json_decode(file_get_contents($project_options['service_url_inner']."api/v1/auth/access_token", false ,$context), true);
	return $result_access_token;
}

# Возвращает access_code для выполнения запросов авторизации или отправки кода на номер телефона
function get_access_code($request_type, $client_data) {
	global $project_options;
	$context = stream_context_create(
		array(
			'http'=>array(
				'header'=> "Request-Type: " . $request_type . "\r\n"
					. "Client-Data: " . $client_data . "\r\n",
				'method' => 'GET'
			)
		)
	);
	$result_access_code =  json_decode(file_get_contents($project_options['service_url_inner']."api/v1/auth/access_code", false ,$context), true);
	return $result_access_code;
}

function load_streams($current_page = 1){
	global $db, $stream;
	$user_id = get_current_session_user();

	$result_streams = $db -> sql_query("SELECT `streams`.`uuid` AS `stream_uuid`, `permissions`, `streams`.`user_id` AS `hero_id` FROM `streams` LEFT JOIN `users` ON `streams`.`user_id` = `users`.`id` WHERE `streams`.`is_blocked` = '0' AND `streams`.`is_excess` = '0' AND `streams`.`is_deleted` = '0' AND `users`.`is_check_official` = '0' AND `users`.`is_blocked` = '0' AND `users`.`is_deleted` = '0' ORDER BY `streams`.`id` DESC", "", "array");
	if (sizeof($result_streams) < 1){
		$response["data"] =  _NO_STREAMS;
		$response["load"] = 0; # Загружать ли новые трансляции при следующем скролле вниз
	} else {
		$available_streams_array = array();
		foreach ($result_streams as $value) {
			$hero_id = $value["hero_id"];
			$uuid = $value["stream_uuid"];
			$watch_permissions_data = unserialize($value["permissions"]);
			if (sizeof($watch_permissions_data) > 0) {
				if ($user_id AND (in_array($user_id, $watch_permissions_data) OR $user_id == $hero_id)) {
					array_push($available_streams_array, $uuid);
				}
			} else {
				array_push($available_streams_array, $uuid);
			}
		}

		$streams_count = sizeof($available_streams_array);
		$streams_on_page = 20;
		$pages = $streams_count/$streams_on_page;
		$pages = ceil($pages);
		$start_view = $streams_on_page*($current_page - 1);

		if (!is_numeric($current_page) OR ($current_page < 1) OR ($current_page > $pages)) {
			$current_page = 1;
		}

		$limit = $streams_on_page*$current_page;
		if ($current_page == $pages) {
			$limit = $streams_count;
		}

		$response["data"] = "";
		for ($i = $start_view; $i < $limit; $i++) {
			$stream_preview = $stream -> stream_preview_html($available_streams_array[$i], "default");
			$response["data"] .= $stream_preview;
		}

		$response["pages"] = $pages;
	}
	return $response;
}

function get_image_mime_type($image_data) {
	$image_mime_types = array("jpeg" => "FFD8", "png" => "89504E470D0A1A0A", "gif" => "474946", "bmp" => "424D", "tiff" => "4949", "tiff" => "4D4D");

	foreach ($image_mime_types as $mime => $hexbytes) {
		$bytes = get_bytes_from_hexstring($hexbytes);
		if (substr($image_data, 0, strlen($bytes)) == $bytes)
			return $mime;
	}
	return "jpeg";
}

function get_bytes_from_hexstring($hexdata) {
	for ($count = 0; $count < strlen($hexdata); $count += 2)
		$bytes[] = chr(hexdec(substr($hexdata, $count, 2)));
	return implode($bytes);
}

# Получение статистики (likes, views, followers, shares)
function stream_statistics($stream_uuid = ""){
	global $db, $stream;

	$likes_count = 0;
	$share_count = 0;
	$views_count = 0;

	$result = $db -> sql_query("SELECT `uuid`, `user_id` FROM `streams` WHERE `uuid` = '$stream_uuid' AND `is_deleted` = '0'", "", "array");

	if (sizeof($result) > 0) {
		$stream_uuid = $result[0]["uuid"];

		$likes_count = sizeof($stream ->likes($stream_uuid));
		$share_count = $stream -> share_count($stream_uuid);
		$share_count = $share_count["total"];
		$views_count = $stream -> get_stream_views($stream_uuid);
	}

	$stream_statistics = "<i class=\"fa fa-heart-o\" title=\"" . _LIKE_COUNT . "\"><b class=\"stream_statistics_number\" title=\"" . _LIKE_COUNT . "\">&nbsp;$likes_count&nbsp;&nbsp;</b></i><i class=\"fa fa-eye\" title=\"" . _VIEWS_COUNT . "\"><b class=\"stream_statistics_number\" title=\"" . _VIEWS_COUNT . "\">&nbsp;$views_count&nbsp;&nbsp;</b></i><i class=\"fa fa-share-alt\" title=\"" . _SHARES_COUNT . "\"><b class=\"stream_statistics_number\" title=\"" . _SHARES_COUNT . "\">&nbsp;$share_count</b></i>";

	return $stream_statistics;
}

function get_hashtags_from_string($string) {
	$result = FALSE;
	preg_match_all("/(#\w+)/u", $string, $matches);
	if ($matches) {
		$tags_array = array_count_values($matches[0]);
		$tags = array_keys($tags_array);
		$result = array();
		foreach ($tags as $value) {
			$tag = preg_replace("/#/", "", $value);
			array_push($result, $tag);
		}
	}
	return $result;
}

function gen_uuid_num($length = 0) {
	$characters = "0123456789";
	$characters_length = strlen($characters);
	$random_string = "";
	for ($i = 0; $i < $length; $i++) {
		$random_string .= $characters[rand(0, $characters_length - 1)];
	}
	return $random_string;
}

function gen_uuid($length = 0) {
	$characters = "ABCDEFGHIJKLMOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
	$characters_length = strlen($characters);
	$random_string = "";
	for ($i = 0; $i < $length; $i++) {
		$random_string .= $characters[rand(0, $characters_length - 1)];
	}
	return $random_string;
}

function prepair_str($str) {
	$str = trim($str);
	$str = preg_replace("/[^\x20-\xFF]/", "", @strval($str));
	$str = strip_tags($str);
	$str = htmlspecialchars($str, ENT_QUOTES);
	$str = mysql_real_escape_string($str);
	return $str;
}

function mobile_test($user_agent) {
	if (preg_match('/(alcatel|amoi|android|avantgo|blackberry|benq|cell|cricket|docomo|elaine|htc|iemobile|iphone|ipad|ipaq|ipod|j2me|java|midp|mini|mmp|mobi|motorola|nec-|nokia|palm|panasonic|philips|phone|playbook|sagem|sharp|sie-|silk|smartphone|sony|symbian|t-mobile|telus|up\.browser|up\.link|vodafone|wap|webos|wireless|xda|xoom|zte)/i', $user_agent)) {
		return 1;
	} else {
		return 0;
	}
}

function num2word($num, $words) {
	$num = $num % 100;
	if ($num > 19) {
		$num = $num % 10;

	}
	switch ($num) {
		case 1: {
			return($words[0]);
		}
		case 2: case 3: case 4: {
		return($words[1]);
	}
		default: {
			return($words[2]);
		}
	}

}

function refine_data ($time) {
	global $lang;

	$time = ($time < 1)? 1 : $time;
	$tokens = array (
		31536000 => array (
			'year' => array (
				'ru' => array('год', 'года', 'лет'), 'en' => 'year'
			),
		),
		2592000 => array (
			'month' => array (
				'ru' => array('месяц', 'месяца', 'месяцев'), 'en' => 'month'
			),
		),
		86400 => array (
			'day' => array (
				'ru' => array('день', 'дня', 'дней'), 'en' => 'day'
			),
		),
		3600 => array (
			'hour' => array (
				'ru' => array('час', 'часа', 'часов'), 'en' => 'hour'
			),
		),
		60 => array (
			'minute' => array (
				'ru' => array('минута', 'минуты', 'минут'), 'en' => 'minute'
			),
		),
		1 => array (
			'second' => array (
				'ru' => array('секунда', 'секунды', 'секунд'), 'en' => 'second'
			),
		),
	);

	foreach ($tokens as $unit => $values) {
		if ($time < $unit) continue;
		$number_of_units = floor($time / $unit);
		$current_state = current(array_keys($values));
		if ($lang == "en") {
			$current_unit_value = $values[$current_state]["en"];
			return $number_of_units.' '.$current_unit_value.(($number_of_units > 1) ? 's' : '');
		} else if ($lang == "ru"){
			$current_unit_values = $values[$current_state]["ru"];
			$current_state_str = num2word($number_of_units, $current_unit_values);
			return $number_of_units.' '.$current_state_str;
		}
	}
}

function get_month_name ($month_num = 0) {
	switch (intval($month_num)) {
		case "1":
			$month_name = _JAN;
			break;
		case "2":
			$month_name = _FEB;
			break;
		case "3":
			$month_name = _MAR;
			break;
		case "4":
			$month_name = _APR;
			break;
		case "5":
			$month_name = _MAY;
			break;
		case "6":
			$month_name = _JUNE;
			break;
		case "7":
			$month_name = _JULE;
			break;
		case "8":
			$month_name = _AUG;
			break;
		case "9":
			$month_name = _SEPT;
			break;
		case "10":
			$month_name = _OCT;
			break;
		case "11":
			$month_name = _NOV;
			break;
		case "12":
			$month_name = _DEC;
			break;

		default:
			$month_name = "";
			break;

	}
	return $month_name;
}

# Шаблон почтовой рассылки. Возвращает  html код письма с текстом
function email_template($content = "") {
	global $project_options;

	$service_url_inner = $project_options["service_url_inner"];
	$appstore_link  = $project_options["appstore_link"];
	$playstore_link	= $project_options["playstore_link"];

	$email_html = "<div marginwidth=\"0\" marginheight=\"0\" style=\"font-family:&quot;Helvetica Neue&quot;,arial,verdana,sans-serif;margin:0;padding:0;background-color:#ffffff;width:100%!important\">
        <table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" height=\"100%\" width=\"100%\" style=\"margin:0;padding:0;background-color:#ffffff;height:100%!important;width:100%!important\">
            <tbody>
                <tr>
                    <td align=\"center\" valign=\"top\" style=\"font-family:&quot;Helvetica Neue&quot;,arial,verdana,sans-serif;border-collapse:collapse\">
                        <table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">
                            <tbody>
                                <tr>
                                    <td align=\"left\" valign=\"top\" style=\"font-family:&quot;Helvetica Neue&quot;,arial,verdana,sans-serif;border-collapse:collapse\">
                                        <table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">
                                            <tbody>
                                                <tr>
                                                    <td width=\"100%\" style=\"font-family:&quot;Helvetica Neue&quot;,arial,verdana,sans-serif;border-collapse:collapse\"><div style=\"background-color:#80C242;min-height:4px;width:100%\"></div>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    <td align=\"left\" valign=\"top\" style=\"font-family:&quot;Helvetica Neue&quot;,arial,verdana,sans-serif;border-collapse:collapse;padding:0 20px\">
                                        <table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"500\" style=\"margin:24px auto\">
                                            <tbody>
                                                <tr>
                                                    <td width=\"411\" style=\"font-family:&quot;Helvetica Neue&quot;,arial,verdana,sans-serif;border-collapse:collapse;vertical-align:top\">
                                                        <a href=\"$service_url_inner\" target=\"_blank\"><img src=\"/assets/images/files/logo_color.png\" width=\"150\" height=\"38\" style=\"float:left;margin-top:8px;margin-bottom:12px\" class=\"CToWUd\"></a>
                                                    </td>

                                                </tr>
                                            </tbody>
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    <td align=\"left\" valign=\"top\" style=\"font-family:&quot;Helvetica Neue&quot;,arial,verdana,sans-serif;border-collapse:collapse;padding:0 20px\">
                                        <table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"500\" style=\"margin:0 auto;border-top:1px solid #edeeef;border-bottom:1px solid #edeeef;padding:30px 0\">

                                            <tbody>
                                                <tr>
                                                    <td valign=\"top\" style=\"font-family:&quot;Helvetica Neue&quot;,arial,verdana,sans-serif;border-collapse:collapse\">
                                                        <table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\" style=\"padding:0 0 30px 0\">
                                                            <tbody>
                                                                <tr>

                                                                    <td width=\"500\" style=\"vertical-align:top\">
                                                                       $content
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                        <table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\" style=\"border-top:1px solid #edeeef;padding:30px 0 0 0\">
                                                            <tbody>
                                                                <tr>
                                                                    <td width=\"50%\"><a href=\"$appstore_link\" title=\"Download on the App Store\"><img src=\"/assets/images/available_appstore.png\" width=\"120\" height=\"35\" style=\"float:right;margin-top:8px;margin-right:5px;margin-bottom:12px\" class=\"CToWUd\"></a></td>
                                                                    <td width=\"50%\"><a href=\"$playstore_link\" title=\"Get it on Google Play\"><img src=\"/assets/images/available_playstore.png\" width=\"120\" height=\"35\" style=\"float:left;margin-top:8px;margin-left:5px;margin-bottom:12px\" class=\"CToWUd\"></a></td>
                                                                </tr>
                                                            </tbody>

                                                        </table>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </td>
                                </tr>

                            </tbody>
                        </table>
                    </td>
                </tr>
            </tbody>
        </table>
	<div>";
	return $email_html;
}

function microtime_float() {
	list($usec, $sec) = explode(" ", microtime());
	return ((float)$usec + (float)$sec);
}
?>