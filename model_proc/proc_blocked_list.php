<?php
header("Content-Type: application/text; charset=utf-8");
global $db, $log_file, $user;

$user_id = $_SESSION["uid"];
$ip = $user -> get_client_ip();
$time = date("H:i");

$blocked = $user -> blocked($user_id);
if (sizeof($blocked) > 0) {
    foreach ($blocked as $value) {
        $profile_image = $user -> profile_image_html($value);
        $profile_name = $user -> profile_name($value);
        echo "
                <div class=\"row\">
                    <div class=\"col-xs-5\"><div class=\"profile_info\" data-profile-id=\"$value\">$profile_image<div class=\"profile_name\">$profile_name</div></div></div>
                    <div class=\"col-xs-5 text-center blocked_state\" data-profile-id=\"$value\"><button class=\"btn theme-button\">" . _UNBLOCK_USER . "</button></div>
                </div>";
    }
} else {
    echo "
            <div class=\"row\">
                <div class=\"col-xs-10 text-center\">
                    <p>" . _NO_BLOCKED_USERS . "</p>
                </div>
            </div>";
}
?>