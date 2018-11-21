<?php
header("Content-Type: application/text; charset=utf-8");
global $db, $log_file, $thumb_query, $user;

$user_id = $_SESSION["uid"];
$ip = $user -> get_client_ip();
$time = date("H:i");
$current_date = time();

$result_devices = $db -> sql_query("SELECT * FROM `devices` WHERE `user_id` = '$user_id' AND `is_deleted` = '0'", "", "array");

if (sizeof($result_devices) > 0 AND !empty($result_devices[0])) {
    $html = "";
    foreach ($result_devices as $value) {
        $device_uuid = $value["device_uuid"];
        $device_model = $value["device_model"];
        $operating_system = $value["operating_system"];

        $html .= "<div class=\"row\">
                            <div class=\"col-xs-10 col-sm-3 col-md-3\">$device_model</div>
                            <div class=\"col-xs-10 col-sm-4 col-md-5\">$operating_system</div>
                            <div class=\"col-xs-10 col-sm-3 col-md-2 text-right\">
                                 <button class=\"btn theme-button device_block\" data-device-uuid=\"$device_uuid\">" . _EDIT . "</button>
                            </div>
                        </div>";
    }

} else {
    $html = "
            <div class=\"row\">
                <div class=\"col-xs-10 text-center\">
                    <p>" . _NO_DEVICES. "</p>
                </div>
            </div>";
}
echo $html;
?>