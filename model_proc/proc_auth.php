<?php
global $user, $project_options;;
$data = $_POST;

switch ($data["action"]) {
    case "send_code" :
        $result = $user -> send_code_to_user($data, "users/hash_generate");
        break;
    case "check_code" :
        $result = $user -> user_auth($data);
        break;
    case "recovery_account" :
        $result = $user -> recovery_account_site($data, "users/user_recovery");
        break;
    default:
        $result = array();
        break;
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode($result);
?>