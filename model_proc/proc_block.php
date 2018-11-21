<?php
//global $action;
//$user_id = $_SESSION["uid"];
//$blocked_user_id = $_POST["user_id"];
//$data["user_id"] = $user_id;
//$result = $action -> block($user_id, $blocked_user_id);
//header("Content-Type: application/json; charset=utf-8");
//echo json_encode($result);

global $project_options, $lang;
$user_id = $_SESSION["uid"];
$blocked_user_id = $_POST["blocked_user_id"];
$request_type = "actions/block";
$oauth_signature = sha1($project_options["site_auth_login"].$project_options["site_auth_pass"].$user_id);

$result_access_token = get_access_token($request_type, $user_id);
$access_token = $result_access_token["access_token"];

$data['blocked_user_id'] = $blocked_user_id;
$data['lang'] = $lang;
$postdata = json_encode($data);

$context = stream_context_create(
    array(
        'http'=>array(
            'header'=> "Content-type: application/json\r\n" ."Auth-Signature: " . $oauth_signature . "\r\n"
                . "Request-Type: " . $request_type . "\r\n"
                . "Access-Token: " . $access_token . "\r\n",
            'method' => 'POST',
            'content' => $postdata

        )
    )
);

$block_action_result = file_get_contents("https://$_SERVER[HTTP_HOST]/api/v1/actions/block", false ,$context);
header("Content-Type: application/json; charset=utf-8");
echo $block_action_result;
?>