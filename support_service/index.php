<?php
//error_reporting(E_ALL);
//ini_set("display_errors", 1);
session_start();
require_once "function.php";
require_once "switch.php";

function page_index() {
    $page_title = "Активные чаты";
    head($page_title);
    admin_interface();
    require_once "model_view/page_active_chats.php";
    modal_window();
    footer();
}

/*-------Авторизация-------*/
function page_login() {
    $page_name = "Авторизация";
    head($page_name);
    require_once "model_view/page_login.php";
}
/*=======Proc=======*/
function proc_login() {
    require_once "model_proc/proc_login.php";
}

function proc_logout() {
    global $support_service;
    $support_service -> support_service_admin_logout();
}

function page_archive_chats() {
    $page_title = "Архив чатов";
    head($page_title);
    admin_interface();
    require_once "model_view/page_archive_chats.php";
    modal_window();
    footer();
}

function proc_archive_chats_get() {
    require_once "model_proc/proc_archive_chats_get.php";
}

function proc_archive_chat_get() {
    require_once "model_proc/proc_archive_chat_get.php";
}

function page_messages() {
    $page_title = "Сообщения";
    head($page_title);
    admin_interface();
    require_once "model_view/page_messages.php";
    modal_window();
    footer();
}

function proc_messages_get() {
    require_once "model_proc/proc_messages_get.php";
}

function page_admin_profile() {
    $page_title = "Профиль администратора службы поддержки";
    head($page_title);
    admin_interface();
    require_once "model_view/page_admin_profile.php";
    modal_window();
    footer();
}

function proc_profile_edit() {
    require_once "model_proc/proc_profile_edit.php";
}

/*-------Тестирование устройств-------*/
function page_test_tools(){
    $page_name = "Тестирование устройств";
    head($page_name);
	admin_interface();
    require_once "model_view/page_test_tools.php";
    footer();
}
/*=======Proc=======*/
function proc_test_tools_ping(){
    require_once "model_proc/proc_test_tools_ping.php";
}
function proc_test_tools_port(){
    require_once "model_proc/proc_test_tools_port.php";
}

?>