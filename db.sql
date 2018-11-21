-- phpMyAdmin SQL Dump
-- version 4.5.4.1
-- http://www.phpmyadmin.net
--
-- Хост: localhost
-- Время создания: Ноя 21 2018 г., 14:26
-- Версия сервера: 5.6.33-79.0-log
-- Версия PHP: 5.6.34-pl0-gentoo

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `futbixru_shop`
--

-- --------------------------------------------------------

--
-- Структура таблицы `access_codes`
--

CREATE TABLE `access_codes` (
  `id` int(11) NOT NULL,
  `access_code` varchar(255) NOT NULL,
  `request_type` varchar(255) NOT NULL,
  `client_info` varchar(255) DEFAULT NULL,
  `oauth_timestamp` int(11) NOT NULL,
  `access_token_expired_date` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `access_tokens_admins`
--

CREATE TABLE `access_tokens_admins` (
  `id` int(11) NOT NULL,
  `access_token` varchar(255) NOT NULL,
  `request_type` varchar(255) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `client_info` varchar(255) DEFAULT NULL,
  `oauth_timestamp` int(11) NOT NULL,
  `access_token_expired_date` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `access_tokens_devices`
--

CREATE TABLE `access_tokens_devices` (
  `id` int(11) NOT NULL,
  `access_token` varchar(255) NOT NULL,
  `request_type` varchar(255) NOT NULL,
  `device_id` int(11) NOT NULL,
  `oauth_timestamp` int(11) NOT NULL,
  `access_token_expired_date` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `access_tokens_web`
--

CREATE TABLE `access_tokens_web` (
  `id` int(11) NOT NULL,
  `access_token` varchar(255) NOT NULL,
  `request_type` varchar(255) NOT NULL,
  `user_id` int(11) NOT NULL,
  `client_info` varchar(255) DEFAULT NULL,
  `oauth_timestamp` int(11) NOT NULL,
  `access_token_expired_date` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `CATEGORIES`
--

CREATE TABLE `CATEGORIES` (
  `ID` int(11) NOT NULL,
  `CATEGORY_NAME` varchar(255) NOT NULL,
  `PARENT_ID` int(11) DEFAULT NULL,
  `IS_DELETED` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `claims`
--

CREATE TABLE `claims` (
  `id` int(11) NOT NULL,
  `stream_id` int(11) DEFAULT NULL,
  `by_user_id` int(11) NOT NULL,
  `created_date` int(11) NOT NULL,
  `status_id` int(11) DEFAULT NULL,
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `claims_comments`
--

CREATE TABLE `claims_comments` (
  `id` int(11) NOT NULL,
  `comment_id` int(11) NOT NULL,
  `by_user_id` int(11) NOT NULL,
  `created_date` int(11) NOT NULL,
  `status_id` int(11) DEFAULT NULL,
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `devices`
--

CREATE TABLE `devices` (
  `id` int(11) NOT NULL,
  `sha1_encrypt_id` varchar(255) NOT NULL,
  `user_id` int(11) NOT NULL,
  `operating_system` varchar(255) NOT NULL,
  `device_model` varchar(255) NOT NULL,
  `device_uuid` varchar(255) NOT NULL,
  `device_token` varchar(255) NOT NULL,
  `lang` varchar(255) NOT NULL,
  `new_stream_notify_settings` tinyint(1) NOT NULL DEFAULT '0',
  `new_follower_notify_settings` tinyint(1) NOT NULL DEFAULT '0',
  `ratio` varchar(255) NOT NULL,
  `is_blocked` tinyint(1) NOT NULL DEFAULT '0',
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `devices_blocks_log`
--

CREATE TABLE `devices_blocks_log` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `blocked_device_id` int(11) NOT NULL,
  `blocked_date` int(11) NOT NULL,
  `block_requester` int(11) NOT NULL,
  `unblocked_date` int(11) DEFAULT NULL,
  `unblock_requester` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `notices`
--

CREATE TABLE `notices` (
  `id` int(11) NOT NULL,
  `notice_text` varchar(255) NOT NULL,
  `activated_date` int(11) NOT NULL,
  `deactivated_date` int(11) DEFAULT NULL,
  `created_date` int(11) NOT NULL,
  `deleted_date` int(11) DEFAULT NULL,
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------


--
-- Структура таблицы `profiles_tags`
--

CREATE TABLE `profiles_tags` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `tag_id` int(11) NOT NULL,
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `profiles_tags_data`
--

CREATE TABLE `profiles_tags_data` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `streams`
--

CREATE TABLE `streams` (
  `id` int(11) NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `device_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `ip` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `storage_server` varchar(255) NOT NULL,
  `start_date` int(11) NOT NULL,
  `end_date` int(11) DEFAULT NULL,
  `duration` float DEFAULT NULL,
  `status` int(11) DEFAULT NULL,
  `lat` decimal(10,8) DEFAULT NULL,
  `lng` decimal(11,8) DEFAULT NULL,
  `permissions` int(11) DEFAULT NULL,
  `chat_permissions` int(11) DEFAULT NULL,
  `on_map` tinyint(1) NOT NULL DEFAULT '0',
  `is_updated` tinyint(1) NOT NULL DEFAULT '0',
  `is_blocked` tinyint(1) NOT NULL DEFAULT '0',
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0',
  `etag_stream` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `streams_altitude_dynamics`
--

CREATE TABLE `streams_altitude_dynamics` (
  `id` int(11) NOT NULL,
  `stream_id` int(11) NOT NULL,
  `updated_date` int(11) NOT NULL,
  `time_from_start` float NOT NULL,
  `altitude` float NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `streams_blocks_log`
--

CREATE TABLE `streams_blocks_log` (
  `id` int(11) NOT NULL,
  `stream_id` int(11) NOT NULL,
  `blocked_date` int(11) NOT NULL,
  `unblocked_date` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `streams_categories_data`
--

CREATE TABLE `streams_categories_data` (
  `id` int(11) NOT NULL,
  `name_ru` varchar(255) NOT NULL,
  `name_en` varchar(255) NOT NULL,
  `created_date` int(11) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `streams_categories_link`
--

CREATE TABLE `streams_categories_link` (
  `id` int(11) NOT NULL,
  `stream_id` int(11) NOT NULL,
  `stream_category_id` int(11) NOT NULL,
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `streams_clients`
--

CREATE TABLE `streams_clients` (
  `id` int(11) NOT NULL,
  `stream_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `connected_date` int(11) NOT NULL,
  `disconnected_date` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `streams_coordinates_dynamics`
--

CREATE TABLE `streams_coordinates_dynamics` (
  `id` int(11) NOT NULL,
  `stream_id` int(11) NOT NULL,
  `updated_date` int(11) NOT NULL,
  `time_from_start` float NOT NULL,
  `lat` decimal(10,8) NOT NULL,
  `lng` decimal(11,8) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `streams_heading_dynamics`
--

CREATE TABLE `streams_heading_dynamics` (
  `id` int(11) NOT NULL,
  `stream_id` int(11) NOT NULL,
  `updated_date` int(11) NOT NULL,
  `time_from_start` float NOT NULL,
  `heading` float NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `streams_locality_desc`
--

CREATE TABLE `streams_locality_desc` (
  `stream_id` int(11) NOT NULL,
  `locality_en` varchar(255) NOT NULL,
  `locality_ru` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `streams_notify_log`
--

CREATE TABLE `streams_notify_log` (
  `id` int(11) NOT NULL,
  `stream_id` int(11) NOT NULL,
  `created_date` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `streams_ori_dynamics`
--

CREATE TABLE `streams_ori_dynamics` (
  `id` int(11) NOT NULL,
  `stream_id` int(11) NOT NULL,
  `updated_date` int(11) NOT NULL,
  `time_from_start` float NOT NULL,
  `ori` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `streams_tags`
--

CREATE TABLE `streams_tags` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `stream_id` int(11) NOT NULL,
  `stream_tag_id` int(11) NOT NULL,
  `is_disabled` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `streams_tags_data`
--

CREATE TABLE `streams_tags_data` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `is_disabled` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `support_service_admins`
--

CREATE TABLE `support_service_admins` (
  `id` int(11) NOT NULL,
  `login` varchar(255) NOT NULL,
  `password` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `comments` varchar(255) DEFAULT NULL,
  `created_date` int(11) NOT NULL,
  `last_change_date` int(11) DEFAULT NULL,
  `is_online` tinyint(1) NOT NULL DEFAULT '0',
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `support_service_chats`
--

CREATE TABLE `support_service_chats` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `user_connect_time` int(11) NOT NULL,
  `user_disconnect_time` int(11) DEFAULT NULL,
  `accepted_by_admin` int(11) DEFAULT NULL,
  `admin_connect_time` int(11) DEFAULT NULL,
  `admin_disconnect_time` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `support_service_chats_msg`
--

CREATE TABLE `support_service_chats_msg` (
  `support_service_chat_id` int(11) NOT NULL,
  `message_by` varchar(255) NOT NULL,
  `message` varchar(255) NOT NULL,
  `created_date` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `support_service_msg`
--

CREATE TABLE `support_service_msg` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `message` varchar(255) NOT NULL,
  `created_date` int(11) NOT NULL,
  `status` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `user_uuid` varchar(255) NOT NULL,
  `phone` int(11) NOT NULL,
  `password` varchar(255) DEFAULT NULL,
  `fb_id` varchar(255) DEFAULT NULL,
  `vk_id` varchar(255) DEFAULT NULL,
  `tw_id` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `image_changed_date` int(11) DEFAULT NULL,
  `etag_user` varchar(255) DEFAULT NULL,
  `etag_img` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `birth_day` int(11) DEFAULT NULL,
  `about` varchar(255) DEFAULT NULL,
  `created_date` int(11) NOT NULL,
  `deleted_date` int(11) DEFAULT NULL,
  `last_changed` int(11) DEFAULT NULL,
  `last_connected` int(11) NOT NULL,
  `last_connected_by` varchar(255) DEFAULT NULL,
  `accepted` tinyint(1) NOT NULL DEFAULT '0',
  `created_by` varchar(255) DEFAULT NULL,
  `hash` varchar(255) DEFAULT NULL,
  `email_confirm` tinyint(1) NOT NULL DEFAULT '0',
  `is_official` tinyint(1) NOT NULL DEFAULT '0',
  `is_check_official` tinyint(1) NOT NULL DEFAULT '0',
  `is_blocked` tinyint(1) NOT NULL DEFAULT '0',
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `users_actions_log`
--

CREATE TABLE `users_actions_log` (
  `id` int(11) NOT NULL,
  `users_actions_id` int(11) NOT NULL,
  `hero_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `stream_id` int(11) DEFAULT NULL,
  `comment` varchar(255) DEFAULT NULL,
  `created_date` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `users_auth_hash_codes`
--

CREATE TABLE `users_auth_hash_codes` (
  `id` int(11) NOT NULL,
  `phone_number` int(11) NOT NULL,
  `hash_code` varchar(255) NOT NULL,
  `created_date` int(11) NOT NULL,
  `is_used` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `users_blocks_log`
--

CREATE TABLE `users_blocks_log` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `blocked_date` int(11) NOT NULL,
  `unblocked_date` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `users_chats`
--

CREATE TABLE `users_chats` (
  `chat_id` int(11) NOT NULL,
  `created_date` int(11) NOT NULL,
  `etag_chat` varchar(255) NOT NULL,
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `users_chats_members_link`
--

CREATE TABLE `users_chats_members_link` (
  `id` int(11) NOT NULL,
  `chat_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `invited_by_user_id` int(11) NOT NULL,
  `created_date` int(11) NOT NULL,
  `deleted_date` int(11) DEFAULT NULL,
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0',
  `last_hiding_chat_date` int(11) DEFAULT NULL,
  `is_chat_hidden` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `users_chats_msg`
--

CREATE TABLE `users_chats_msg` (
  `message_id` int(11) NOT NULL,
  `users_chats_actions_id` int(11) NOT NULL,
  `users_chats_members_link_id` int(11) NOT NULL,
  `message` varchar(255) NOT NULL,
  `invited_user_id` int(11) DEFAULT NULL,
  `created_date` int(11) NOT NULL,
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `users_feedback`
--

CREATE TABLE `users_feedback` (
  `id` int(11) NOT NULL,
  `device_id` int(11) NOT NULL,
  `feedback_text` varchar(255) NOT NULL,
  `created_date` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `users_sessions_devices`
--

CREATE TABLE `users_sessions_devices` (
  `id` int(11) NOT NULL,
  `session_id` int(11) NOT NULL,
  `device_id` int(11) NOT NULL,
  `connected_by` varchar(255) NOT NULL,
  `start_date` int(11) NOT NULL,
  `end_date` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `users_sessions_support_admins`
--

CREATE TABLE `users_sessions_support_admins` (
  `id` int(11) NOT NULL,
  `session_id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `ip_address` varchar(255) NOT NULL,
  `start_date` int(11) NOT NULL,
  `end_date` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `users_sessions_web`
--

CREATE TABLE `users_sessions_web` (
  `id` int(11) NOT NULL,
  `session_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `ip_address` varchar(255) NOT NULL,
  `user_agent` varchar(255) NOT NULL,
  `connected_by` varchar(255) NOT NULL,
  `start_date` int(11) NOT NULL,
  `end_date` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `access_codes`
--
ALTER TABLE `access_codes`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `access_tokens_admins`
--
ALTER TABLE `access_tokens_admins`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `access_tokens_devices`
--
ALTER TABLE `access_tokens_devices`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `access_tokens_web`
--
ALTER TABLE `access_tokens_web`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `claims`
--
ALTER TABLE `claims`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `claims_comments`
--
ALTER TABLE `claims_comments`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `devices`
--
ALTER TABLE `devices`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `devices_blocks_log`
--
ALTER TABLE `devices_blocks_log`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `notices`
--
ALTER TABLE `notices`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `profiles_tags`
--
ALTER TABLE `profiles_tags`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `profiles_tags_data`
--
ALTER TABLE `profiles_tags_data`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `streams`
--
ALTER TABLE `streams`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `streams_altitude_dynamics`
--
ALTER TABLE `streams_altitude_dynamics`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `streams_blocks_log`
--
ALTER TABLE `streams_blocks_log`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `streams_categories_data`
--
ALTER TABLE `streams_categories_data`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `streams_categories_link`
--
ALTER TABLE `streams_categories_link`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `streams_clients`
--
ALTER TABLE `streams_clients`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `streams_coordinates_dynamics`
--
ALTER TABLE `streams_coordinates_dynamics`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `streams_heading_dynamics`
--
ALTER TABLE `streams_heading_dynamics`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `streams_notify_log`
--
ALTER TABLE `streams_notify_log`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `streams_ori_dynamics`
--
ALTER TABLE `streams_ori_dynamics`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `streams_tags`
--
ALTER TABLE `streams_tags`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `streams_tags_data`
--
ALTER TABLE `streams_tags_data`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `support_service_admins`
--
ALTER TABLE `support_service_admins`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `support_service_chats`
--
ALTER TABLE `support_service_chats`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `support_service_msg`
--
ALTER TABLE `support_service_msg`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `users_actions_log`
--
ALTER TABLE `users_actions_log`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `users_auth_hash_codes`
--
ALTER TABLE `users_auth_hash_codes`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `users_blocks_log`
--
ALTER TABLE `users_blocks_log`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `users_chats`
--
ALTER TABLE `users_chats`
  ADD PRIMARY KEY (`chat_id`);

--
-- Индексы таблицы `users_chats_members_link`
--
ALTER TABLE `users_chats_members_link`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `users_chats_msg`
--
ALTER TABLE `users_chats_msg`
  ADD PRIMARY KEY (`message_id`);

--
-- Индексы таблицы `users_feedback`
--
ALTER TABLE `users_feedback`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `users_sessions_devices`
--
ALTER TABLE `users_sessions_devices`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `users_sessions_support_admins`
--
ALTER TABLE `users_sessions_support_admins`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `users_sessions_web`
--
ALTER TABLE `users_sessions_web`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `access_codes`
--
ALTER TABLE `access_codes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT для таблицы `access_tokens_admins`
--
ALTER TABLE `access_tokens_admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT для таблицы `access_tokens_devices`
--
ALTER TABLE `access_tokens_devices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT для таблицы `access_tokens_web`
--
ALTER TABLE `access_tokens_web`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT для таблицы `claims`
--
ALTER TABLE `claims`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT для таблицы `claims_comments`
--
ALTER TABLE `claims_comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT для таблицы `devices`
--
ALTER TABLE `devices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT для таблицы `devices_blocks_log`
--
ALTER TABLE `devices_blocks_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT для таблицы `notices`
--
ALTER TABLE `notices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT для таблицы `profiles_tags`
--
ALTER TABLE `profiles_tags`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT для таблицы `profiles_tags_data`
--
ALTER TABLE `profiles_tags_data`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT для таблицы `streams`
--
ALTER TABLE `streams`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT для таблицы `streams_altitude_dynamics`
--
ALTER TABLE `streams_altitude_dynamics`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT для таблицы `streams_blocks_log`
--
ALTER TABLE `streams_blocks_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT для таблицы `streams_categories_data`
--
ALTER TABLE `streams_categories_data`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT для таблицы `streams_categories_link`
--
ALTER TABLE `streams_categories_link`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT для таблицы `streams_clients`
--
ALTER TABLE `streams_clients`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT для таблицы `streams_coordinates_dynamics`
--
ALTER TABLE `streams_coordinates_dynamics`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT для таблицы `streams_heading_dynamics`
--
ALTER TABLE `streams_heading_dynamics`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT для таблицы `streams_notify_log`
--
ALTER TABLE `streams_notify_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT для таблицы `streams_ori_dynamics`
--
ALTER TABLE `streams_ori_dynamics`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT для таблицы `streams_tags`
--
ALTER TABLE `streams_tags`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT для таблицы `streams_tags_data`
--
ALTER TABLE `streams_tags_data`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT для таблицы `support_service_admins`
--
ALTER TABLE `support_service_admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT для таблицы `support_service_chats`
--
ALTER TABLE `support_service_chats`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT для таблицы `support_service_msg`
--
ALTER TABLE `support_service_msg`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT для таблицы `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT для таблицы `users_actions_log`
--
ALTER TABLE `users_actions_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT для таблицы `users_auth_hash_codes`
--
ALTER TABLE `users_auth_hash_codes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT для таблицы `users_blocks_log`
--
ALTER TABLE `users_blocks_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT для таблицы `users_chats`
--
ALTER TABLE `users_chats`
  MODIFY `chat_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT для таблицы `users_chats_members_link`
--
ALTER TABLE `users_chats_members_link`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT для таблицы `users_chats_msg`
--
ALTER TABLE `users_chats_msg`
  MODIFY `message_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT для таблицы `users_feedback`
--
ALTER TABLE `users_feedback`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT для таблицы `users_sessions_devices`
--
ALTER TABLE `users_sessions_devices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT для таблицы `users_sessions_support_admins`
--
ALTER TABLE `users_sessions_support_admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT для таблицы `users_sessions_web`
--
ALTER TABLE `users_sessions_web`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
