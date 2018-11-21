<?php
require_once "config.php";
require_once "lang.php";

global $db, $user, $log_file;
if (isset($_GET["route"])) {
	$page = $_GET["route"];
} else {
    $page = "";
}

switch ($page) {
	default :
        if ($user -> check_web_session_id()) {
            page_index("page_index");
        } else {
            page_start();
        }
        break;

    case "page_start" :
        if ($user -> check_web_session_id()) {
            page_index($page);
        } else {
            page_start();
        }
        break;

    /*-------Авторизация и регистрация-------*/
    case "page_password_recovery" :
        page_start();
        break;

	/*=======Proc=======*/
    case "proc_auth" :
        proc_auth();
        break;

	case "proc_logout" :
        if ($user -> check_web_session_id()) {
            proc_logout();
        } else {
            page_start();
        }
        break;

    case "proc_auth_fb" :
        proc_auth_fb();
        break;

    case "proc_auth_vk" :
        proc_auth_vk();
        break;

    case "proc_jsondata_get" :
        if ($user -> check_web_session_id()) {
            proc_jsondata_get();
        } else {
            page_start();
        }
        break;

    /*-------Тестирование устройств-------*/
    case "page_test_tools" :
        if ($user -> check_web_session_id()) {
            page_test_tools($page);
        } else {
            page_start();
        }
        break;

    /*=======Proc=======*/
    case "proc_test_tools_port" :
        if ($user -> check_web_session_id()) {
            proc_test_tools_port();
        } else {
            page_start();
        }
        break;

    case "proc_test_tools_ping" :
        if ($user -> check_web_session_id()) {
            proc_test_tools_ping();
        } else {
            page_start();
        }
        break;

    /*-------Учетная запись-------*/
    case "page_profile" :
        if ($user -> check_web_session_id()) {
            page_profile($page);
        } else {
            page_start();
        }
        break;

    case "page_chat" :
        if ($user -> check_web_session_id()) {
            page_chat($page);
        } else {
            page_start();
        }
        break;

    /*=======Proc=======*/
    case "proc_profile_actions" :
        if ($user -> check_web_session_id()) {
            proc_profile_actions();
        } else {
            page_start();
        }
        break;

    case "proc_profile_edit" :
        if ($user -> check_web_session_id()) {
            proc_profile_edit();
        } else {
            page_start();
        }
        break;

    case "proc_profile_image_tmp" :
        if ($user -> check_web_session_id()) {
            proc_profile_image_tmp();
        } else {
            page_start();
        }
        break;

    case "proc_profile_delete" :
        if ($user -> check_web_session_id()) {
            proc_profile_delete();
        } else {
            page_start();
        }
        break;

    case "proc_following_list" :
        if ($user -> check_web_session_id()) {
            proc_following_list();
        } else {
            page_start();
        }
        break;

    case "proc_followers_list" :
        if ($user -> check_web_session_id()) {
            proc_followers_list();
        } else {
            page_start();
        }
        break;


    case "proc_blocked_list" :
        if ($user -> check_web_session_id()) {
            proc_blocked_list();
        } else {
            page_start();
        }
        break;

    case "proc_streams_list" :
        if ($user -> check_web_session_id()) {
            proc_streams_list();
        } else {
            page_start();
        }
        break;

    case "proc_devices_list" :
        if ($user -> check_web_session_id()) {
            proc_devices_list();
        } else {
            page_start();
        }
        break;

    case "proc_chats_list" :
        if ($user -> check_web_session_id()) {
            proc_chats_list();
        } else {
            page_start();
        }
        break;

    case "proc_chats_list_filter" :
        if ($user -> check_web_session_id()) {
            proc_chats_list_filter();
        } else {
            page_start();
        }
        break;

    case "proc_stream_edit" :
        if ($user -> check_web_session_id()) {
            proc_stream_edit();
        } else {
            page_start();
        }
        break;

    case "proc_stream_delete" :
        if ($user -> check_web_session_id()) {
            proc_stream_delete();
        } else {
            page_start();
        }
        break;

    case "proc_moderate" :
        if ($user -> check_web_session_id()) {
            proc_moderate();
        } else {
            page_start();
        }
        break;

    case "proc_search" :
        if ($user -> check_web_session_id()) {
            proc_search();
        } else {
            page_start();
        }
        break;

    /*-------Просмотр-------*/
    case "page_play" :
        if ($user -> check_web_session_id()) {
            $page_title = "";
            if (isset($_GET["user"])) {
                $hero_id = $_GET["user"];
                $profile_name = $user -> profile_name($hero_id);
                $page_title = $profile_name._IN_PROGECT;
            }
            page_play($page_title, $page);
        } else {
            page_start();
        }
        break;

   /*=======Proc=======*/
    case "proc_profile_get" :
        proc_profile_get();
        break;

    case "proc_follow" :
        if ($user -> check_web_session_id()) {
            proc_follow();
        } else {
            page_start();
        }
        break;

    case "proc_claim" :
        if ($user -> check_web_session_id()) {
            proc_claim();
        } else {
            page_start();
        }
        break;

    case "proc_chat_get" :
        proc_chat_get();
        break;

    case "proc_block" :
        if ($user -> check_web_session_id()) {
            proc_block();
        } else {
            page_start();
        }
        break;

    /*-------Top streams-------*/
    case "page_top" :
        if ($user -> check_web_session_id()) {
            page_top($page);
        } else {
            page_start();
        }
        break;

    /*-------Official streams-------*/
    case "page_official" :
        if ($user -> check_web_session_id()) {
            page_official($page);
        } else {
            page_start();
        }
        break;

    /*-------Terms of service-------*/
    case "page_terms" :
        page_terms($page);
        break;

    /*-------Privacy-------*/
    case "page_privacy" :
        page_privacy($page);
        break;

    /*-------FAQ-------*/
    case "page_help_faq" :
        page_help_faq($page);
        break;

    /*-------Map-------*/
    case "page_map" :
        if ($user -> check_web_session_id()) {
            page_map($page);
        } else {
            page_start();
        }
        break;

    case "proc_markers_get" :
        if ($user -> check_web_session_id()) {
            proc_markers_get();
        } else {
            page_start();
        }
        break;

    /*-------Support page-------*/
	case "page_support" :
        if ($user -> check_web_session_id()) {
            page_support($page);
        } else {
            page_start();
        }
        break;
}