<?php
//error_reporting(E_ALL);
session_start();
require_once "function.php";
require_once "switch.php";

function page_index($page = "") {
    $page_title = _PROGECT_NAME;
    head($page_title, $page);
    top_banners();
    top_streams();
    official();
    filters();
    require_once "model_view/page_index.php";
    require_once "model_view/popup/popup_profile.php";
    footer($page);
}

function page_start(){
    require_once "model_view/page_start.php";
    require_once "model_view/popup/popup_auth.php";
}

/*=======Proc=======*/
function proc_auth() {
    require_once "model_proc/proc_auth.php";
}

function proc_logout() {
    require_once "model_proc/proc_logout.php";
}

function proc_auth_fb(){
    require_once "model_proc/proc_auth_fb.php";
}

function proc_auth_vk(){
    require_once "model_proc/proc_auth_vk.php";
}

function proc_jsondata_get(){
    require_once "model_proc/proc_jsondata_get.php";
}

/*-------Учетная запись-------*/
function page_profile($page = ""){
    $page_title = _MY_PROFILE;
    head($page_title, $page);
    require_once "model_view/page_profile.php";
    require_once "model_view/popup/popup_profile.php";
    require_once "model_view/popup/popup_profile_delete.php";
    require_once "model_view/popup/popup_stream_delete.php";
    require_once "model_view/popup/popup_stream_edit.php";
    footer($page);
}

function page_chat($page = ""){
    $page_title = _MY_PROFILE;
    head($page_title, $page);
    require_once "model_view/page_chat.php";
    require_once "model_view/popup/popup_invite_users.php";
    footer($page);
}

/*=======Proc=======*/
function proc_profile_actions(){
    require_once "model_proc/proc_profile_actions.php";
}

function proc_profile_edit(){
    require_once "model_proc/proc_profile_edit.php";
}

function proc_profile_image_tmp(){
    require_once "model_proc/proc_profile_image_tmp.php";
}

function proc_profile_delete(){
    require_once "model_proc/proc_profile_delete.php";
}

function proc_following_list(){
    require_once "model_proc/proc_following_list.php";
}

function proc_followers_list(){
    require_once "model_proc/proc_followers_list.php";
}

function proc_blocked_list(){
    require_once "model_proc/proc_blocked_list.php";
}

function proc_streams_list(){
    require_once "model_proc/proc_streams_list.php";
}

function proc_devices_list(){
    require_once "model_proc/proc_devices_list.php";
}

function proc_chats_list(){
    require_once "model_proc/proc_chats_list.php";
}

function proc_chats_list_filter(){
    require_once "model_proc/proc_chats_list_filter.php";
}

function proc_stream_start(){
    require_once "model_proc/proc_stream_start.php";
}

function proc_stream_edit(){
    require_once "model_proc/proc_stream_edit.php";
}

function proc_stream_delete(){
    require_once "model_proc/proc_stream_delete.php";
}

function proc_moderate(){
    require_once "model_proc/proc_moderate.php";
}

function proc_search(){
    require_once "model_proc/proc_search.php";
}


/*-------Просмотр трансляции-------*/
#Страница просмотра видео с использование вебсокетов
function page_play($page_title, $page = "") {
    head($page_title, $page);
    require_once "model_view/page_play.php";
    top_streams();
    official();
//    require_once "model_view/popup/popup_auth.php";
    require_once "model_view/popup/popup_blocked_info.php";
    require_once "model_view/popup/popup_claim_confirm.php";
    require_once "model_view/popup/popup_embed.php";
    require_once "model_view/popup/popup_profile.php";
    footer($page);
}

/*=======Proc=======*/
function proc_profile_get(){
    require_once "model_proc/proc_profile_get.php";
}

function proc_follow(){
    require_once "model_proc/proc_follow.php";
}

function proc_claim(){
    require_once "model_proc/proc_claim.php";
}

function proc_chat_get(){
    require_once "model_proc/proc_chat_get.php";
}

function proc_block(){
    require_once "model_proc/proc_block.php";
}

/*-------Top streams-------*/
function page_top($page = ""){
    $page_title = _TOP_STREAMS;
    head($page_title, $page);
    require_once "model_view/page_top.php";
    require_once "model_view/popup/popup_profile.php";
    footer($page);
}

/*-------Official streams-------*/
function page_official($page = ""){
    $page_title = _OFFICIAL;
    head($page_title, $page);
    require_once "model_view/page_official.php";
    require_once "model_view/popup/popup_profile.php";
    footer($page);
}

/*-------Terms of service-------*/
function page_terms($page = ""){
    $page_title = _TERMS_OF_USE;
    head($page_title, $page);
    require_once "model_view/page_terms.php";
    footer($page);
}

/*-------Privacy-------*/
function page_privacy($page = ""){
    $page_title = _PRIVACY_POLICY;
    head($page_title, $page);
    require_once "model_view/page_privacy.php";
    footer($page);
}

/*-------FAQ-------*/
function page_help_faq($page = ""){
    $page_title = _FAQ;
    head($page_title, $page);
    require_once "model_view/page_help_faq.php";
    footer($page);
}

/*-------Map-------*/
function page_map($page = ""){
    $page_title = _STREAMS_MAP;
    head($page_title, $page);
    require_once "model_view/page_map.php";
    footer($page);
}

function proc_markers_get(){
    require_once "model_proc/proc_markers_get.php";
}

/*-------Support page-------*/
function page_support($page) {
    $page_name = _SUPPORT_PAGE;
    head($page_name, $page);
	require_once "model_view/page_support.php";
	footer($page);
}