<?php
global $user;
$user_id = $_SESSION["uid"];

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
                            <li class=\"active\"><a href=\"#profile\" data-toggle=\"tab\">" . _PROFILE . "</a></li>
                            <li><a href=\"#following\" data-toggle=\"tab\">" . _FOLLOWING . "</a></li>
                            <li><a href=\"#followers\" data-toggle=\"tab\">" . _FOLLOWERS . "</a></li>
                            <li><a href=\"#blacklist\" data-toggle=\"tab\">" . _BLOCKED_USERS . "</a></li>
                            <li><a href=\"#streams\" data-toggle=\"tab\">" . _STREAMS . "</a></li>
                            <li><a href=\"#chats\" data-toggle=\"tab\">" . _MESSAGES . "</a></li> 
                            <li><a href=\"#settings\" data-toggle=\"tab\">" . _SETTINGS . "</a></li>
                        </ul>
    
                        <div class=\"tab-content col-xs-8\">
    
                            <div class=\"tab-pane active\" id=\"profile\">
                                <div class=\"tab-pane-header text-center\"><h4>" . _PROFILE . "</h4></div>
                                <div class=\"row data\">
                                    <form method=\"post\" enctype=\"multipart/form-data\" name=\"profile_image_edit\" id=\"profile_image_edit\" role=\"form\">
                                    <input type=\"hidden\" name=\"MAX_FILE_SIZE\" value=\"5242880\">
                                        <div class=\"form-group\">
                                            <div class=\"col-xs-6 col-xs-offset-2 text-center\">
                                                <div class=\"profile_image\" style=\"background: url('$profile_image_url') 100% 100% no-repeat;  background-size: cover;\">
                                                    <div class=\"image_cover\" style=\"display: none;\">
                                                        <label class=\"file_upload\">
                                                            <i class=\"fa fa-camera fa-3x\"></i>
                                                            <input type=\"file\" accept=\"image/gif,image/jpeg,image/jpg,image/png\">
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class=\"image_edit_result\"></div>
                                            </div>
                                            <div class=\"col-xs-2\">
                                                <div><i class=\"fa fa-pencil fa-2x\" id=\"image_edit_icon\"></i></div>
                                            </div>
                                        </div>
                                    </form>
    
                                    <form class=\"form-horizontal\" method=\"post\" name=\"profile_edit_form\" id=\"profile_edit_form\" >
                                        <div class=\"form-group\">
                                            <label class=\"col-xs-3 col-md-2 col-md-offset-1 control-label\">" . _LOGIN . "</label>
                                            <div class=\"col-xs-7 col-md-4\">
                                                <input type=\"text\" class=\"form-control\" id=\"login\" name=\"login\" value=\"$login\">
                                            </div>
                                        </div>
    
                                        <div class=\"form-group\">
                                            <label class=\"col-xs-3 col-md-2 col-md-offset-1 control-label\">" . _NAME . "</label>
                                            <div class=\"col-xs-7 col-md-4\">
                                                <input type=\"text\" class=\"form-control\" id=\"name\" name=\"name\" value=\"$name\">
                                            </div>
                                        </div>
    
                                        <div class=\"form-group\">
                                            <label class=\"col-xs-3 col-md-2 col-md-offset-1 control-label\">" . _ABOUT_ME . "</label>
                                            <div class=\"col-xs-7 col-md-4\">
                                                <textarea rows=\"3\" class=\"form-control\" id=\"about\" name=\"about\">$about</textarea>
                                            </div>
                                        </div>
    
                                        <div class=\"form-group\">
                                            <label class=\"col-xs-3 col-md-2 col-md-offset-1 control-label\">" . _EMAIL . "</label>
                                            <div class=\"col-sm-7 col-md-4\">
                                                <input type=\"text\" class=\"form-control\" id=\"email\" name=\"email\" value=\"$email\">
                                            </div>
                                        </div>
    
                                        <div class=\"form-group\">
                                            <label class=\"col-xs-3 col-md-2 col-md-offset-1 control-label\">" . _IS_OFFICIAL . "</label>
                                            <div class=\"col-xs-7 col-md-4\">
                                                <input type=\"checkbox\" name=\"is_official\">
                                            </div>
                                        </div>
    
                                        <div class=\"col-xs-10 col-md-4 col-md-offset-3 text-center\" id=\"result_profile_edit\"></div>
    
                                        <div class=\"form-group\">
                                            <div class=\"col-xs-10 text-center\">
                                                <button type=\"reset\" class=\"btn btn-default\">" . _CANCEL . "</button>
                                                <input type=\"hidden\" name=\"is_image_changed\" value=\"0\">
                                                <input type=\"submit\" value=\"" . _SAVE . "\" class=\"btn theme-button\">
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
    
                            <div class=\"tab-pane\" id=\"following\">
                                <div class=\"tab-pane-header text-center\"><h4>" . _FOLLOWING . "</h4></div>
                                <div class=\"data\"></div>
                            </div>
    
                            <div class=\"tab-pane\" id=\"followers\">
                                <div class=\"tab-pane-header text-center\"><h4>" . _FOLLOWERS . "</h4></div>
                                <div class=\"data\"></div>
                            </div>
    
                            <div class=\"tab-pane\" id=\"blacklist\">
                                <div class=\"tab-pane-header text-center\"><h4>" . _BLOCKED_USERS . "</h4></div>
                                <div class=\"data\"></div>
                            </div>
    
                            <div class=\"tab-pane\" id=\"streams\">
                                <div class=\"tab-pane-header text-center\"><h4>" . _STREAMS . "</h4></div>
                                <div class=\"data\"></div>
                            </div>
                            
                            <div class=\"tab-pane\" id=\"chats\">
                                <div class=\"tab-pane-header text-center\"><h4>" . _MESSAGES . "</h4></div>
                                <div class=\"chats_tab_pane_header search\">
                                    <input type=\"text\" class=\"form-control\" id=\"search_input\" placeholder=\"" . _SEARCH . "\">
                                    <img src=\"/assets/images/icons_svg/close_icon.svg\" id=\"search_query_remove\">
                                </div>
                                <div class=\"data\"></div>
                            </div>
    
                            <div class=\"tab-pane\" id=\"settings\">
                                <div class=\"tab-pane-header text-center\"><h4>" . _SETTINGS . "</h4></div>
                                <div class=\"row data\">
                                   
                                    <div class=\"col-xs-10 text-center settings_group_header\" id=\"devices\">
                                        <p>" . _MY_DEVICES. "</p>
                                        <div class=\"data_devices\"></div>
                                    </div>
    
                                    <!---<div class=\"col-xs-10 text-center settings_group_header\"><p>" . _PROFILE_DELETE. "</p></div>
                                    <div class=\"col-xs-10 text-center\"><button type=\"button\" class=\"btn theme-button\" id=\"profile_delete\">" . _PROFILE_DELETE . "</button></div> --->
                                    
                                    <div class=\"col-xs-10 text-center settings_group_header\">
                                        <p>" . _MANAGE_ACCOUNT ."</p>
                                        <div class=\"col-xs-10 text-center\"><button type=\"button\" class=\"btn theme-button\" id=\"logout_all_devices\">" . _LOGOUT_FROM_ALL_DEVICES ."</button></div>
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
		var PROFILE_DATA = {
			user_id: $user_id,
			is_official: $is_official,
			mutual_following: $mutual_following
		};
	</script>
	<script src=\"/assets/js/project.page.profile.js\"></script>";
}

?>