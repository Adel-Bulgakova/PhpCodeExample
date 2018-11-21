<?php
$user_id = get_current_session_user();
$session_id = $_SESSION["web_session_id"];
global $db, $lang, $project_options;
$ws_url = $project_options["ws_urls"]["ws_support"];
echo "
	<div class=\"section content\">
		<div class=\"container\">

			<div class=\"row\">
				<div class=\"col-xs-10\">
					<h4 class=\"page_title\">" . _SUPPORT_PAGE ."</h4>
				</div>
			</div>
			
			<div class=\"row\">
			    <div class=\"col-xs-10\">
					<button class=\"btn theme-button\" id=\"btn_open_dialog\">" . _START_CHAT_WITH_SUPPORT_SERVICE ."</button>
			    
                    <div id=\"support_chat_wrapper\" data-chat-id=\"\">
                        <div id=\"support_chat_messages\"></div>
                        <form id=\"support_chat_form\" method=\"post\">
                            <div id=\"support_chat_bottom\">
                                <div class=\"left\">
                                    <input type=\"text\" name=\"message\" maxlength=\"255\" placeholder=\"" . _WRITE_MESSAGE_TO_SUPPORT_SERVICE ."\"/>
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
	
	<script src=\"/assets/js/project.websockets.support.js\"></script>
	<script>
	    var SUPPORT_PAGE_DATA = {
			lang: \"" . $lang . "\",
			sid: \"" .$session_id. "\",
			user_id: $user_id,
			websocket_server_url: \"" .$ws_url. "\"
		};			
	</script>";
?>
