<?php
global $user, $project_options;

$user_id = $_SESSION["uid"];
$session_id = $_SESSION["web_session_id"];
$ws_url = $project_options["ws_urls"]["ws_users_chat"];

if (isset($_GET["chat"]) AND $_GET["chat"] != 0) {

    # Получение id существующего чата для продолжения переписки
    $chat_id = $_GET["chat"];
    $ar["type"] = "system_message_connect";
    $ar["chat_id"] = $chat_id;
    $ar["sid"] = $session_id;
    $ar["user_id"] = $user_id;
    $ws_open_params = json_encode($ar);

} else if (isset($_GET["user"]) AND $_GET["user"] != 0){
    $chat_id = 0; # Не удалять
    # Получение id пользователя, с которым начать новый чат
    $invited_user_id = $_GET["user"];
    $ar["type"] = "system_message_create";
    $ar["invited_user_id"] = $invited_user_id;
    $ar["sid"] = $session_id;
    $ar["user_id"] = $user_id;
    $ws_open_params = json_encode($ar);
    
} else {
    header('Location: /index.php?route=page_profile');
}

$user_data = $user -> user_data($user_id);
if ($user_data["status"] == 'NOT-FOUND') {
    header('Location: /index.php?route=page_start');
} else {
    $login 		= $user_data["data"]["login"];
    $name 		= $user_data["data"]["name"];
    $email 		= $user_data["data"]["email"];
    $about      = $user_data["data"]["about"];
    $is_official = $user_data["data"]["is_official"];
    $mutual_following = json_encode($user_data["data"]["mutual_following"]);
    $profile_image_url = $user_data["data"]["profile_image"];
    echo "
	<div class=\"section content page_profile\">
		<div class=\"container\">
			<div class=\"row\">
			    <div class=\"col-xs-10\">

                    <div class=\"tabbable\">
                        <ul class=\"nav nav-pills nav-stacked col-xs-2\">
                            <li><a href=\"/index.php?route=page_profile#profile\">" . _PROFILE . "</a></li>
                            <li><a href=\"/index.php?route=page_profile#following\">" . _FOLLOWING . "</a></li>
                            <li><a href=\"/index.php?route=page_profile#followers\">" . _FOLLOWERS . "</a></li>
                            <li class=\"active\"><a href=\"/index.php?route=page_profile#chats\">" . _MESSAGES . "</a></li>                            
                            <li><a href=\"/index.php?route=page_profile#blacklist\">" . _BLOCKED_USERS . "</a></li>
                            <li><a href=\"/index.php?route=page_profile#streams\">" . _STREAMS . "</a></li>
                            <li><a href=\"/index.php?route=page_profile#settings\">" . _SETTINGS . "</a></li>
                        </ul>
    
                        <div class=\"tab-content col-xs-8\">
    
                            <div class=\"tab-pane active\" id=\"messages\">
                                <div class=\"tab-pane-header text-center\"><h4>" . _MESSAGES . "</h4></div>
                                <div class=\"tab-pane-subheader text-right\"><div class=\"tab-pane-subheader-btn invite_users\"><i class=\"fa fa-plus\"></i> Добавить участников</div></div>                                
                                
                                <div id=\"user_chat_wrapper\">
                                    <div class=\"chat\">
                                        <div id=\"user_chat_messages\"></div>
                                        <form id=\"chat_form\" method=\"post\">
                                            <div id=\"user_chat_bottom\">
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
                                </div>	
                            </div>
                            
                        </div>
                    </div>
                    
                </div>
			</div>
		</div>
	</div>
	<script>
		var PAGE_DATA = {
			sid: '" .$session_id. "',
			chat_id: $chat_id,
			ws_open_params: '" . $ws_open_params . "',
			ws_server_url: '" . $ws_url . "',
			INVITED_TO_CHAT: '" . _INVITED_TO_CHAT . "'
		};
	</script>
	<script src=\"/assets/js/project.websockets.chat.js\"></script>";
}

?>