<?php
//global $action;
//$user_id = $_SESSION["uid"];
//$stream_uuid = $_POST["stream_uuid"];
//$result = $action -> claim($stream_uuid, $user_id);
//header("Content-Type: application/json; charset=utf-8");
//echo json_encode($result);

global $project_options;
$user_id = $_SESSION["uid"];
$stream_uuid = prepair_str($_POST["stream_uuid"]);
$request_type = "actions/claim";
$oauth_signature = sha1($project_options["site_auth_login"].$project_options["site_auth_pass"].$user_id);

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

$claim_action_result = file_get_contents("https://$_SERVER[HTTP_HOST]/api/v1/actions/claim/$stream_uuid", false ,$context);
header("Content-Type: application/json; charset=utf-8");
echo $claim_action_result;
?>