<?php
echo "
    <div id=\"popup-auth\" class=\"mfp-hide white-popup-block-with-close-btn\">
        <div class=\"popup_content\">
        
            <div class=\"popup_header text-left\">
                <p>" . _AUTHORIZATION . "</p>
                <button title=\"Close\" type=\"button\" class=\"mfp-close popup-close-icon popup-auth-close-icon\"><img src=\"/assets/images/icons_svg/close_icon.svg\"></button>
            </div>
            
            <form id=\"send_code_form\" method=\"post\" role=\"form\" class=\"form-horizontal\">
                <div class=\"col-xs-3\">
                    <div class=\"form-group\">
                        <select class=\"phone_code_select form-control\" name=\"code\" data-live-search=\"true\" data-width=\"fit\">
                            <optgroup>
                            <option value=\"+7\" selected>+7 Russia</option>
                            </optgroup>
                        </select>
                    </div>
                </div>
                <div class=\"col-xs-7\">
                    <div class=\"form-group\">
                        <input type=\"text\" class=\"form-control\" name=\"phone_number\" placeholder=\"" . _ENTER_PHONE_NUMBER . "\" />
                    </div>
                </div>
                    
				<div id=\"result_send_code\" style=\"margin-bottom: 15px;\"></div>
				<div class=\"clearfix\">
					<input type=\"submit\" class=\"btn theme-button\" value=\"" . _SEND_CODE . "\" />
				</div>
			</form>
				
			<form id=\"check_code_form\" method=\"post\" role=\"form\" hidden>
                <div class=\"form-group\">
                    <input type=\"text\" class=\"form-control\" name=\"hash_code\" placeholder=\"" . _ENTER_CODE_FROM_SMS . "\"/>
                </div>
                    
				<div id=\"result_check_code\" style=\"margin-bottom: 15px;\"></div>
				<div class=\"clearfix\">
				    <input type=\"hidden\" name=\"phone\" value=\"\"/>
					<input type=\"submit\" class=\"btn theme-button\" value=\"" . _SEND . "\" />
					<a href=\"#\" id=\"send_new_code\">" ._SEND_NEW_CODE. "</a>
				</div>
			</form>
			
			<form id=\"recovery_account_form\" method=\"post\" role=\"form\" hidden>
                <div class=\"form-group\"></div>                    
				<div id=\"result_recovery_account\" style=\"margin-bottom: 15px;\"></div>
				<div class=\"clearfix\">
				    <input type=\"hidden\" name=\"phone\" value=\"\"/>
				    <input type=\"hidden\" name=\"user_id\" value=\"\"/>
					<input type=\"submit\" class=\"btn theme-button\" value=\"" . _RECOVER_ACCOUNT . "\" />
				</div>
			</form>

        </div>

        <div class=\"auth_icons\">" . _SING_IN_WITH_NETWORKS . "&nbsp;&nbsp;
            <a href=\"/index.php?route=proc_auth_fb\" title=\"" . _SINGIN_FB . "\"><span class=\"social_net_badge social_net_fb\"><span class=\"social_net_icon\"></span></span></a>&nbsp;&nbsp;<a href=\"/twitter_auth.php\"  title=\"" . _SINGIN_TW . "\"><span class=\"social_net_badge social_net_tw\"><span class=\"social_net_icon\"></span></span></a>&nbsp;&nbsp;<a href=\"/index.php?route=proc_auth_vk\" title=\"" . _SINGIN_VK . "\"><span class=\"social_net_badge social_net_vk\"><span class=\"social_net_icon\"></span></span></a>
            </div><br>
            <div>" ._ACCEPT_SERVICE_USE . "</div>
        </div>
        
    </div>
";
?>