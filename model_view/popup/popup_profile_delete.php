<?php
echo "
    <div id=\"popup-profile-delete\" class=\"mfp-hide white-popup-block-with-close-btn\">
        <div class=\"popup_content text-center\">
            <p>" . _PROFILE_DELETE_MODAL_HEADER. "</p>
             <button title=\"Close\" type=\"button\" class=\"mfp-close popup-close-icon popup-profile-delete-close-icon\"><img src=\"/assets/images/icons_svg/close_icon.svg\"></button>
        </div>

        <div class=\"popup_body_control text-center\">
            <button class=\"btn btn-default popup-profile-delete-close-icon\">" . _CANCEL . "</button>
            <button id=\"profile_delete_confirm\" class=\"btn theme-button\">" . _CONFIRM ."</button>
        </div>
    </div>";
?>