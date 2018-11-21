<?php
require_once "../config.php";

if (isset($_GET["route"])) {
	$pg = $_GET["route"];
} else {
	$pg = "";
}

switch ($pg) {
	default :
		if (isset($_SESSION["support_admin"])) {
			page_index();
		} else {
			page_login();
		}
		break;    
        
/*-------Авторизация-------*/           
	case "page_login" :
		if (isset($_SESSION["support_admin"])) {
			page_index();
		} else {
			page_login();
		}
		break;
	/*=======Proc=======*/ 
    case "proc_login" :
        if (isset($_SESSION["support_admin"])) {
            page_index();
        } else {
            proc_login();
        }
        break;

	case "proc_logout" :
        if (isset($_SESSION["support_admin"])) {
            proc_logout();
        } else {
            page_login();
        }
        break;
	
	case "page_archive_chats" :
		if (isset($_SESSION["support_admin"])) {
			page_archive_chats();
		} else {
			page_login();
		}
		break;

	case "proc_archive_chats_get":
		if (isset($_SESSION["support_admin"])) {
			proc_archive_chats_get();
		} else {
			page_login();
		}
		break;

	case "proc_archive_chat_get":
		if (isset($_SESSION["support_admin"])) {
			proc_archive_chat_get();
		} else {
			page_login();
		}
		break;

	case "page_messages" :
		if (isset($_SESSION["support_admin"])) {
			page_messages();
		} else {
			page_login();
		}
		break;

	case "proc_messages_get":
		if (isset($_SESSION["support_admin"])) {
			proc_messages_get();
		} else {
			page_login();
		}
		break;

	case "page_admin_profile" :
		if (isset($_SESSION["support_admin"])) {
			page_admin_profile();
		} else {
			page_login();
		}
		break;
	
	case "proc_profile_edit":
		if (isset($_SESSION["support_admin"])) {
			proc_profile_edit();
		} else {
			page_login();
		}
		break;		

/*-------Cписок трансляций-------*/
	case "page_streams" :
		if (isset($_SESSION["support_admin"])) {
			page_streams();
		} else {
			page_login();
		}
		break;
	/*=======Proc=======*/ 	
	case "proc_streams_get":
		if (isset($_SESSION["support_admin"])) {
			proc_streams_get();
		} else {
			page_login();
		}
		break;
	case "proc_streams_actions":
		if (isset($_SESSION["support_admin"])) {
			proc_streams_actions();
		} else {
			page_login();
		}
		break;

/*-------Список жалоб-------*/
	case "page_claims" :
		if (isset($_SESSION["support_admin"])) {
			page_claims();
		} else {
			page_login();
		}
		break;
	/*=======Proc=======*/
	case "proc_claims_get" :
		if (isset($_SESSION["support_admin"])) {
			proc_claims_get();
		} else {
			page_login();
		}
		break;
	case "proc_claims_actions" :
		if (isset($_SESSION["support_admin"])) {
			proc_claims_actions();
		} else {
			page_login();
		}
		break;
	case "proc_claims_notify" :
		if (isset($_SESSION["support_admin"])) {
			proc_claims_notify();
		} else {
			page_login();
		}
		break;


/*-------Тестирование устройств-------*/
	case "page_test_tools" :
		if (isset($_SESSION["support_admin"])) {
			page_test_tools();
		} else {
			page_login();
		}
		break;
	/*=======Proc=======*/
	case "proc_test_tools_port" :
		if (isset($_SESSION["support_admin"])) {
			proc_test_tools_port();
		} else {
			page_login();
		}
		break;
	case "proc_test_tools_ping" :
		if (isset($_SESSION["support_admin"])) {
			proc_test_tools_ping();
		} else {
			page_login();
		}
		break;

}
?>