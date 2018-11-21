<?php
echo "
    <div id=\"popup-stream-delete\" class=\"mfp-hide white-popup-block-with-close-btn\">
        <div class=\"popup_content text-center\">
            <p>" . _STREAM_DELETE_POPUP_HEADER. "</p>
            <button title=\"Close\" type=\"button\" class=\"mfp-close popup-close-icon popup-stream-delete-close-icon\"><img src=\"/assets/images/icons_svg/close_icon.svg\"></button>
        </div>

        <div class=\"popup_body_control text-center\">
            <button class=\"btn btn-default popup-stream-delete-close-icon\">" . _CANCEL . "</button>
            <button id=\"stream_delete_confirm\" class=\"btn theme-button\">" . _CONFIRM ."</button>
        </div>
    </div>";
?>