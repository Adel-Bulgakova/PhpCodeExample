<?php
//global $db, $action, $lang;
//$user_id = $_SESSION["uid"];
//$hero_id = $_POST["hero_id"];
//
//$result = $action -> follow($hero_id, $user_id, $lang);
//header("Content-Type: application/json; charset=utf-8");
//echo json_encode($result);

global $project_options, $lang;
$user_id = $_SESSION["uid"];
$hero_id = $_POST["hero_id"];

$request_type = "actions/follow";
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

$data['hero_id'] = $hero_id;
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

$follow_action_result = file_get_contents("https://$_SERVER[HTTP_HOST]/api/v1/actions/follow", false ,$context);
echo $follow_action_result;
?>