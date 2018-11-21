<?php
require_once "/www/config.php";
require_once "/www/lang.php";

if (isset($_GET["route"])) {
	$pg = $_GET["route"];
} else {
	$pg = "";
}

switch ($pg) {
	default :
		if (isset($_SESSION["super_user"])) {
			page_index();
		} else {
			page_login();
		}
		break;    
        
/*-------Авторизация-------*/           
	case "page_login" :
		if (isset($_SESSION["super_user"])) {
			page_index();
		} else {
			page_login();
		}
		break;
	/*=======Proc=======*/ 
    case "proc_login" :
        if (isset($_SESSION["super_user"])) {
            page_index();
        } else {
            proc_login();
        }
        break;
	case "proc_logout" :
        if (isset($_SESSION["super_user"])) {
            proc_logout();
        } else {
            page_login();
        }
        break;

	case "proc_jsondata_get":
		if (isset($_SESSION["super_user"])) {
			proc_jsondata_get();
		} else {
			page_login();
		}
		break;

/*-------Cписок трансляций-------*/
	case "page_streams" :
		if (isset($_SESSION["super_user"])) {
			page_streams();
		} else {
			page_login();
		}
		break;

	case "page_streams_excess" :
		if (isset($_SESSION["super_user"])) {
			page_streams_excess();
		} else {
			page_login();
		}
		break;

	case "proc_streams_actions":
		if (isset($_SESSION["super_user"])) {
			proc_streams_actions();
		} else {
			page_login();
		}
		break;

	case "page_streams_categories" :
		if (isset($_SESSION["super_user"])) {
			page_streams_categories();
		} else {
			page_login();
		}
		break;

	case "proc_streams_categories_actions" :
		if (isset($_SESSION["super_user"])) {
			proc_streams_categories_actions();
		} else {
			page_login();
		}
		break;

	case "proc_streams_category_detail" :
		if (isset($_SESSION["super_user"])) {
			proc_streams_category_detail();
		} else {
			page_login();
		}
		break;

	/*-------Список пользователей-------*/
	case "page_users" :
		if (isset($_SESSION["super_user"])) {
			page_users();
		} else {
			page_login();
		}
		break;

	case "proc_users_actions" :
		if (isset($_SESSION["super_user"])) {
			proc_users_actions();
		} else {
			page_login();
		}
		break;
	
	case "page_user" :
		if (isset($_SESSION["super_user"])) {
			page_user();
		} else {
			page_login();
		}
		break;

/*-------Список официальных источников-------*/
	case "page_official" :
		if (isset($_SESSION["super_user"])) {
			page_official();
		} else {
			page_login();
		}
		break;

	case "proc_official_actions" :
		if (isset($_SESSION["super_user"])) {
			proc_official_actions();
		} else {
			page_login();
		}
		break;

/*-------Список жалоб-------*/
	case "page_claims" :
		if (isset($_SESSION["super_user"])) {
			page_claims();
		} else {
			page_login();
		}
		break;

	case "proc_claims_actions" :
		if (isset($_SESSION["super_user"])) {
			proc_claims_actions();
		} else {
			page_login();
		}
		break;

	case "proc_claims_notify" :
		if (isset($_SESSION["super_user"])) {
			proc_claims_notify();
		} else {
			page_login();
		}
		break;


	case "page_claims_comments" :
		if (isset($_SESSION["super_user"])) {
			page_claims_comments();
		} else {
			page_login();
		}
		break;

	case "page_feedback" :
		if (isset($_SESSION["super_user"])) {
			page_feedback();
		} else {
			page_login();
		}
		break;
/*-------Streams Tags-------*/
	case "page_streams_tags" :
		if (isset($_SESSION["super_user"])) {
			page_streams_tags();
		} else {
			page_login();
		}
		break;

	case "proc_streams_tags_actions" :
		if (isset($_SESSION["super_user"])) {
			proc_streams_tags_actions();
		} else {
			page_login();
		}
		break;

/*-------Profiles Tags-------*/
	case "page_profiles_tags" :
		if (isset($_SESSION["super_user"])) {
			page_profiles_tags();
		} else {
			page_login();
		}
		break;

	case "proc_profiles_tags_actions" :
		if (isset($_SESSION["super_user"])) {
			proc_profiles_tags_actions();
		} else {
			page_login();
		}
		break;

/*-------Управление администраторами службы поддержки-------*/
	case "page_support_service" :
		if (isset($_SESSION["super_user"])) {
			page_support_service();
		} else {
			page_login();
		}
		break;

	case "page_support_service_admin" :
		if (isset($_SESSION["super_user"])) {
			page_support_service_admin();
		} else {
			page_login();
		}
		break;

	case "proc_admin_detail" :
		if (isset($_SESSION["super_user"])) {
			proc_admin_detail();
		} else {
			page_login();
		}
		break;
	case "proc_admin_actions" :
		if (isset($_SESSION["super_user"])) {
			proc_admin_actions();
		} else {
			page_login();
		}
		break;
	case "proc_support_service_admin_chat" :
		if (isset($_SESSION["super_user"])) {
			proc_support_service_admin_chat();
		} else {
			page_login();
		}
		break;

/*-------Уведомления-------*/
	case "page_notice" :
		if (isset($_SESSION["super_user"])) {
			page_notice();
		} else {
			page_login();
		}
		break;
	case "proc_notice_actions" :
		if (isset($_SESSION["super_user"])) {
			proc_notice_actions();
		} else {
			page_login();
		}
		break;
	case "proc_notice_detail" :
		if (isset($_SESSION["super_user"])) {
			proc_notice_detail();
		} else {
			page_login();
		}
		break;
/*-------Payment-------*/
	case "page_payment" :
		if (isset($_SESSION["super_user"])) {
			page_payment();
		} else {
			page_login();
		}
		break;

/*-------Тестирование устройств-------*/
	case "page_test_tools" :
		if (isset($_SESSION["super_user"])) {
			page_test_tools();
		} else {
			page_login();
		}
		break;
	/*=======Proc=======*/
	case "proc_test_tools_port" :
		if (isset($_SESSION["super_user"])) {
			proc_test_tools_port();
		} else {
			page_login();
		}
		break;
	case "proc_test_tools_ping" :
		if (isset($_SESSION["super_user"])) {
			proc_test_tools_ping();
		} else {
			page_login();
		}
		break;

}
?>