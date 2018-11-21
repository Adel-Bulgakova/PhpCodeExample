<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);
session_start();
require_once "function.php";
require_once "switch.php";

function page_index() {
    $page_name = "Главная";
    head($page_name);
	admin_interface();
    require_once "model_view/page_index.php";
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
    session_unset();
    session_destroy();
  	header('Location: /admin/');
}

function proc_jsondata_get() {
    require_once "model_proc/proc_jsondata_get.php";
}

/*-------Cписок трансляций-------*/
function page_streams() {
    $page_name = "Cписок трансляций";
    head($page_name);
	admin_interface();
    require_once "model_view/page_streams.php";
    modal_window();
	footer();
}

function page_streams_excess() {
    $page_name = "Список трансляций, отсутствующих на медиасервере";
    head($page_name);
    admin_interface();
    require_once "model_view/page_streams_excess.php";
    modal_window();
    footer();
}

function proc_streams_actions() {
    require_once "model_proc/proc_streams_actions.php";
}

function page_streams_categories() {
    $page_name = "Управление категориями";
    head($page_name);
    admin_interface();
    require_once "model_view/page_streams_categories.php";
    modal_window();
    footer();
}

function proc_streams_categories_actions() {
    require_once "model_proc/proc_streams_categories_actions.php";
}

function proc_streams_category_detail() {
    require_once "model_proc/proc_streams_category_detail.php";
}

/*-------Список пользователей-------*/
function page_users(){
    $page_name = "Пользователи";
    head($page_name);
    admin_interface();
    require_once "model_view/page_users.php";
    modal_window();
    footer();
}

function proc_users_actions(){
    require_once "model_proc/proc_users_actions.php";
}

/*-------Информация о пользователе-------*/
function page_user(){
    $page_name = "Профиль пользователя";
    head($page_name);
    admin_interface();
    require_once "model_view/page_user.php";
    modal_window();
    footer();
}

/*-------Список официальных источников-------*/
function page_official(){
    $page_name = "Официальные источники";
    head($page_name);
    admin_interface();
    require_once "model_view/page_official.php";
    modal_window();
    footer();
}

function proc_official_actions(){
    require_once "model_proc/proc_official_actions.php";
}

/*-------Список жалоб-------*/
function page_claims(){
    $page_name = "Жалобы на видео";
    head($page_name);
    admin_interface();
    require_once "model_view/page_claims.php";
    modal_window();
    footer();
}

function proc_claims_actions(){
    require_once "model_proc/proc_claims_actions.php";
}
function proc_claims_notify(){
    require_once "model_proc/proc_claims_notify.php";
}

function page_claims_comments(){
    $page_name = "Жалобы на комментарии";
    head($page_name);
    admin_interface();
    require_once "model_view/page_claims_comments.php";
    modal_window();
    footer();
}

function page_feedback(){
    $page_name = "Отзывы";
    head($page_name);
    admin_interface();
    require_once "model_view/page_feedback.php";
    footer();
}

/*-------Streams Tags-------*/
function page_streams_tags(){
    $page_name = "Модерация тегов трансляций";
    head($page_name);
    admin_interface();
    require_once "model_view/page_streams_tags.php";
    modal_window();
    footer();
}

function proc_streams_tags_actions(){
    require_once "model_proc/proc_streams_tags_actions.php";
}

/*-------Profiles Tags-------*/
function page_profiles_tags(){
    $page_name = "Модерация тегов профилей пользователей";
    head($page_name);
    admin_interface();
    require_once "model_view/page_profiles_tags.php";
    modal_window();
    footer();
}

function proc_profiles_tags_actions(){
    require_once "model_proc/proc_profiles_tags_actions.php";
}

/*-------Управление администраторами службы поддержки-------*/
function page_support_service(){
    $page_name = "Управление администраторами службы поддержки";
    head($page_name);
    admin_interface();
    require_once "model_view/page_support_service.php";
    modal_window();
    footer();
}

function page_support_service_admin(){
    $page_name = "Профиль администратора службы поддержки";
    head($page_name);
    admin_interface();
    require_once "model_view/page_support_service_admin.php";
    modal_window();
    footer();
}

function proc_admin_detail(){
    require_once "model_proc/proc_admin_detail.php";
}
function proc_admin_actions(){
    require_once "model_proc/proc_admin_actions.php";
}
function proc_support_service_admin_chat(){
    require_once "model_proc/proc_support_service_admin_chat.php";
}

/*-------Уведоления-------*/
function page_notice(){
    $page_name = "Уведомления";
    head($page_name);
    admin_interface();
    require_once "model_view/page_notice.php";
    modal_window();
    footer();
}
function proc_notice_actions(){
    require_once "model_proc/proc_notice_actions.php";
}

function proc_notice_detail(){
    require_once "model_proc/proc_notice_detail.php";
}
/*-------Способы оплаты-------*/
function page_payment(){
    $page_name = "Управление способами оплаты";
    head($page_name);
    admin_interface();
    require_once "model_view/page_payment.php";
    footer();
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