<?php
//global $user;
//$user_id = $_SESSION["uid"];
//$result = $user -> user_delete($user_id);
//header("Content-Type: application/json; charset=utf-8");
//echo json_encode($result);

global $project_options;
$user_id = $_SESSION["uid"];
$stream_uuid = prepair_str($_POST["stream_uuid"]);

$request_type = "users/user_delete";
$oauth_signature = sha1($project_options["site_auth_login"].$project_options["site_auth_pass"].$user_id);

$context = stream_context_create(
    array(
        'http'=>array(
            'header'=> "Auth-Signature: " . $oauth_signature . "\r\n" . "Request-Type: " . $request_type . "\r\n",
            'method' => 'GET'
        )
    )
);
$result_access_token =  json_decode(file_get_contents("https://$_SERVER[HTTP_HOST]/api/v1/auth/access_token", false ,$context), true);

$access_token = $result_access_token["access_token"];
$context = stream_context_create(
    array(
        'http'=>array(
            'header'=> "Auth-Signature: " . $oauth_signature . "\r\n"
                . "Request-Type: " . $request_type . "\r\n"
                . "Access-Token: " . $access_token . "\r\n",
            'method' => 'GET'
        )
    )
);

$user_delete_result = file_get_contents("https://$_SERVER[HTTP_HOST]/api/v1/users/user_delete", false ,$context);
header("Content-Type: application/json; charset=utf-8");
echo $user_delete_result;

?>