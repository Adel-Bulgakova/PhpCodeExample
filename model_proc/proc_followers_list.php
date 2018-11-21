<?php
header("Content-Type: application/text; charset=utf-8");
global $db, $log_file, $user;

$user_id = $_SESSION["uid"];
$ip = $user -> get_client_ip();
$time = date("H:i");

$followers = $user -> followers($user_id);
$following = $user -> following($user_id);

if (sizeof($followers) > 0) {
    foreach ($followers as $follower_id) {
        $profile_image = $user -> profile_image_html($follower_id);
        $profile_name = $user -> profile_name($follower_id);
        if (in_array($follower_id, $following)) {
            $following_state_button = "<button class=\"btn theme-button\">" . _FOLLOW_STATE_POSITIVE . "</button>";
        } else {
            $following_state_button = "<button class=\"btn btn-default\">" . _FOLLOW_ACTION . "</button>";
        }

        echo "
                <div class=\"row\">
                    <div class=\"col-xs-5\"><div class=\"profile_info\" data-profile-id=\"$follower_id\">$profile_image<div class=\"profile_name\">$profile_name</div></div></div>
                    <div class=\"col-xs-5 text-center following_state_icon\" data-profile-id=\"$follower_id\">$following_state_button</div>
                </div>";
    }
} else {
    echo "
            <div class=\"row\">
                <div class=\"col-xs-10 text-center\">
                    <p>" . _NO_FOLLOWERS . "</p>
                </div>
            </div>";
}
?>