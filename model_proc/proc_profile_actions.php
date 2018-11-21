<?php
global $user, $project_options;
$user_id = $_SESSION["uid"];
$oauth_signature = sha1($project_options["site_auth_login"].$project_options["site_auth_pass"].$user_id);
$data = $_POST;

switch ($data["action"]) {
    case "logout_all_devices" :
        $request_type = "users/user_logout_all_devices";
        $result_access_token = get_access_token($request_type, $user_id);
        $access_token = $result_access_token["access_token"];

        $context = stream_context_create(
            array(
                'http'=>array(
                    'header'=> "Auth-Signature: " . $oauth_signature . "\r\n"
                        . "Request-Type: " . $request_type . "\r\n"
                        . "Access-Token: " . $access_token . "\r\n",
                    'method' => 'POST'
                )
            )
        );
        $result = json_decode(file_get_contents("https://$_SERVER[HTTP_HOST]/api/v1/users/user_logout_all_devices", false ,$context), true);
        break;

    default:
        $result = array();
        break;
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode($result);
?>