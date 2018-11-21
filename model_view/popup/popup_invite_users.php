<?php
echo "
    <div id=\"popup-invite-users\" class=\"mfp-hide white-popup-block-with-close-btn\">
        <div class=\"popup_content\"></div>
        <button title=\"Close\" type=\"button\" class=\"mfp-close popup-close-icon popup-invite-users-close-icon\"><img src=\"/assets/images/icons_svg/close_icon.svg\"></button>
        
        <div class=\"popup_body_control text-center\">
            <button class=\"btn btn-default popup-invite-users-close-icon\">" . _CANCEL . "</button>
            <button id=\"invite_users_confirm\" class=\"btn theme-button\">" . _CONFIRM ."</button>
        </div>
    </div>";
?>