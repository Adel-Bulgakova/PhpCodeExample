<?php
header("Content-Type: application/text; charset=utf-8");
global $db, $log_file, $stream, $thumb_query, $user;

$user_id = $_SESSION["uid"];

$user_data = $user -> user_data($user_id);
if ($user_data["status"] == 'NOT-FOUND') {
    echo "
	<div class=\"section content page_profile\">
		<div class=\"container\">
			<div class=\"row\">
			    <div class=\"col-xs-10\"> </div>
			</div>
        </div>
    </div>";
} else {
    $live 		= $user_data["data"]["streams"]["online"];
    $recorded 		= $user_data["data"]["streams"]["archive"];
    $oncoming 		= $user_data["data"]["streams"]["oncoming"];

    $html = "<h4 class=\"subtitle\">Online</h4>";
    if (sizeof($live) > 0 ){
        foreach ($live as $live_uuid) {
            $html .= stream_info($live_uuid, $user_id);
        }
    } else {
        $html .= "<p class=\"dull_text\" >" . _NO_STREAMS. "</p>";
    }

    $html .= "<h4 class=\"subtitle\">" . _RECORDED_STREAMS . "</h4>";
    if (sizeof($recorded) > 0 ){
        foreach ($recorded as $recorded_uuid) {
            $html .= stream_info($recorded_uuid, $user_id);
        }
    } else {
        $html .= "<p class=\"dull_text\" >" . _NO_STREAMS. "</p>";
    }

    $html .= "<h4 class=\"subtitle\">" . _ONCOMING_STREAMS . "</h4>";
    if (sizeof($oncoming) > 0 ){
        foreach ($oncoming as $oncoming_uuid) {
            $html .= stream_info($oncoming_uuid, $user_id);
        }
    } else {
        $html .= "<p class=\"dull_text\" >" . _NO_STREAMS. "</p>";
    }

}

function stream_info ($stream_uuid = "", $user_id = 0) {
    global $stream;
    $result = "";
    $current_date = time();

    $stream_data = $stream -> stream_data($stream_uuid, $user_id);
    if ($stream_data["status"] == 'OK') {
        $stream_name = $stream_data["data"]["name"];
        $locality = $stream_data["data"]["locality"];
        $stream_start_date = $stream_data["data"]["start_date"];
        $stream_end_date = $stream_data["data"]["end_date"];
        $permissions = $stream_data["data"]["permissions"];
        $chat_permissions = $stream_data["data"]["chat_permissions"];
        $stream_thumb = $stream_data["data"]["thumb"];

        if (sizeof($permissions) == 0) {
            $permissions_state = _PERMISSION_ALL_USERS;
        } else {
            $permissions_state = _PERMISSION_PRIVATE;
        }

        if ($chat_permissions == 1) {
            $chat_permissions_state = _PERMISSION_ALL_USERS;
        } else {
            $chat_permissions_state = _PERMISSION_CHAT_PRIVATE;
        }

        if ($stream_end_date == 0) {
            $stream_online_status = "<div class=\"stream_status\">Online</div>";
            $stream_date_info = "";
        } else {
            $stream_online_status = "";
            $time_difference = $current_date - $stream_start_date;
            $stream_date_info = refine_data($time_difference) . " " . _AGO;
        }

        $result = "<div class=\"row\">
                                <div class=\"col-xs-10 col-sm-3 col-md-3 stream_preview\">
                                    <a href=\"/index.php?route=page_play&user=$user_id&uuid=$stream_uuid\">
                                        <div class=\"thumbnail\" style=\"background: #D6D6D6 url('$stream_thumb') center center no-repeat; background-size: cover;)\">                                            
                                            $stream_online_status
                                            <i class=\"icon-play\"></i>
                                        </div>
                                    </a>
                                </div>
                                <div class=\"col-xs-10 col-sm-4 col-md-5\">
                                    <p>$stream_name</p>
                                    <p class=\"dull_text\">$locality</p>
                                    <p class=\"dull_text\">$stream_date_info</p>
                                    <p class=\"dull_text\">" . _STREAM_WATCH_PERMISSION . ": $permissions_state</p>
                                    <p class=\"dull_text\">" . _STREAM_CHAT_PERMISSION . ": $chat_permissions_state</p>
                                </div>
                                <div class=\"col-xs-10 col-sm-3 col-md-2 text-right\">
                                    <button class=\"btn theme-button stream_edit\" data-stream-uuid=\"$stream_uuid\">" . _EDIT . "</button>
                                </div>
                             </div>";
    }
    return $result;
}
echo $html;
?>