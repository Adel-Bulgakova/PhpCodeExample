<?php
echo "
    <div id=\"popup-claim-confirm\" class=\"mfp-hide white-popup-block-with-close-btn\">
        <div class=\"popup_content text-center\">
            <p>" . _CLAIM_MODAL_HEADER. "</p>
            <button title=\"Close\" type=\"button\" class=\"mfp-close popup-close-icon popup-claim-confirm-close-icon\"><img src=\"/assets/images/icons_svg/close_icon.svg\"></button>
        </div>

        <div class=\"popup_body_control text-center\">
            <button class=\"btn btn-default popup-claim-confirm-close-icon\">" . _CANCEL . "</button>
            <button id=\"claim_confirm\" class=\"btn theme-button\">" . _CLAIM ."</button>
        </div>
    </div>";
?>