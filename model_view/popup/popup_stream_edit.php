<?php
echo "
    <div id=\"popup-stream-edit\" class=\"mfp-hide white-popup-block-with-close-btn\">
    
        <button title=\"Close\" type=\"button\" class=\"mfp-close popup-close-icon popup-stream-edit-close-icon\"><img src=\"/assets/images/icons_svg/close_icon.svg\"></button>
        
        <div class=\"popup_content text-center\">
        
            <form method=\"post\" name=\"stream_edit_from\" class=\"form-horizontal\" role=\"form\" data-stream-uuid=\"\">            
                <p>" . _STREAM_EDIT_POPUP_HEADER. "</p>

                <div class=\"form-group\">
                    <label class=\"control-label col-sm-2\" for=\"stream_name\">" . _STREAM_NAME . "</label>
                    <div class=\"col-sm-8\">
                        <input type=\"text\" class=\"form-control\" id=\"stream_name\" name=\"stream_name\">
                    </div>
                </div>

                <div class=\"form-group text-left\">
                    <div class=\"col-sm-offset-2 col-sm-8\">
                        <div class=\"radio\">
                            <label><input type=\"radio\" value=\"public\" name=\"stream_permissions_state\">" . _PUBLIC_STREAM . "</label>
                        </div>
                        <div class=\"radio\">
                            <label><input type=\"radio\" value=\"private\" class=\"\" name=\"stream_permissions_state\">" . _PRIVATE_STREAM . " <i class=\"fa fa-question-circle fa-lg\" aria-hidden=\"true\" title=\"" . _PRIVATE_STREAM_DESC . "\"></i></label>
                        </div>
                        <div class=\"stream_watchers\"><div class=\"stream_watchers_profiles\"></div></div>
                    </div>
                </div>

                <div class=\"form-group text-left\">
                    <div class=\"col-sm-offset-2 col-sm-8\">
                        <div class=\"checkbox\">
                            <label><input type=\"checkbox\" name=\"chat_permissions\">" . _PERMISSION_CHAT_FOR_ALL . "</label>
                        </div>
                    </div>
                </div>

                <div class=\"form-group text-left\">
                    <div class=\"col-sm-offset-2 col-sm-8\">
                        <div class=\"checkbox\">
                            <label><input type=\"checkbox\" name=\"on_map\">" . _SHARE_LOCATION . "</label>
                        </div>
                    </div>
                </div>

                <div class=\"result_stream_edit\"></div>

            

                <div class=\"popup_body_control text-center\">
                    <button type=\"reset\" class=\"btn btn-default popup-stream-edit-close-icon\">" . _CANCEL . "</button>
                    <input type=\"submit\" class=\"btn theme-button\" value=\"" . _SAVE ."\">
                </div>

            </form>

            <div class=\"col-xs-10 text-center settings_group_header\">
                <p>" . _STREAM_DELETE. " <i class=\"fa fa-question-circle fa-lg\" aria-hidden=\"true\" title=\"" . _STREAM_DELETE_DESC. "\"></i></p>
            </div>
            <div class=\"col-xs-10 text-center\">
                <button type=\"button\" class=\"btn theme-button\" id=\"stream_delete\">" . _STREAM_DELETE . "</button>
            </div>
        
        </div>
    </div>";
?>