<?php
echo "
    <div id=\"popup-embed\" class=\"mfp-hide white-popup-block-with-close-btn\">
        <div class=\"popup_content\">
            <button title=\"Close\" type=\"button\" class=\"mfp-close popup-close-icon popup-embed-close-icon\"><img src=\"/assets/images/icons_svg/close_icon.svg\"></button>
            <label class=\"embed_url_label\" for=\"embed_url\">" . _EMBED_CODE . "</label>
            <div class=\"embed_url_block\">
                <textarea class=\"embed_url js-embed_url\" readonly=\"readonly\"></textarea>
            </div>
            <div class=\"embed_size_params\">
                <form class=\"form_default\">
                    <div class=\"embed_size\">
                        <label class=\"embed_url_label\" for=\"embed_size\">" . _EMBED_SIZE . "</label>
                        <select id=\"embed_size\" class=\"js-player_size\">
                            <option value=\"480x270\">480x270</option>
                            <option value=\"560x315\">560x315</option>
                            <option value=\"640x360\" selected=\"selected\">640x360</option>
                            <option value=\"960x540\">960x540</option>
                            <option value=\"custom\">" . _EMBED_SIZE_CUSTOM . "</option>
                        </select>
                        <div class=\"option_text js-custom_options\">
                            <input type=\"text\" class=\"text js-custom_width\" value=\"640\" maxlength=\"4\">
                            <input type=\"text\" class=\"text js-custom_height\" value=\"360\" maxlength=\"4\">
                        </div>
                    </div>
    
                    <div class=\"option_group\">
                        <label class=\"embed_url_label\" for=\"embed_options\">" . _EMBED_OPTIONS . "</label>
                        <div class=\"options clearfix\">
                            <input type=\"checkbox\" value=\"Play video on page load\" id=\"auto_play\" name=\"auto_play\" class=\"input_option js-option\">
                            <label for=\"auto_play\" class=\"option_text\">" . _EMBED_OPTIONS_AUTOPLAY . "</label>
                        </div>
                        <div class=\"options clearfix\">
                            <input type=\"checkbox\" value=\"Mute video by default\" id=\"mute\" name=\"mute\" class=\"input_option js-option\">
                            <label for=\"mute\" class=\"option_text\">" . _EMBED_OPTIONS_MUTE . "</label>
                        </div>
                    </div>
                </form>
            </div>            
        </div>
    </div>";
?>