<?php
global $db, $snapshot_query, $stream, $user;

$user_id = prepair_str($_GET["user_id"]);
$user_data = $user -> user_data($user_id);

if ($user_data["status"] == "OK"){
    $login = $user_data["data"]["login"];
    $display_name = $user_data["data"]["display_name"];
    $email = $user_data["data"]["email"];
    $about = $user_data["data"]["about"];
    $profile_image = $user_data["data"]["profile_image"];

    $followers_data = $user_data["data"]["followers"];
    $followers = array();
    foreach ($followers_data as $follower) {
        $follower_data = array();
        $follower_name = $user -> profile_name($follower);
        $follower_image = $user -> profile_image_html($follower);
        $follower_info = "<a href=\"/admin/index.php?route=page_user&user_id=$follower\" target=\"_blank\"><div class=\"profile_info\">$follower_image<div class=\"profile_name\"><span>$follower_name</span></div></div></a>";

        $date_of_follow_timestamp = get_action_date(3, $user_id, $follower);
        $date_of_follow = date("d.m.Y H:i", $date_of_follow_timestamp);
        array_push($follower_data, $follower_info, $date_of_follow);
        array_push($followers, $follower_data);
    }
    $followers = json_encode($followers);

    $following_data = $user_data["data"]["following"];
    $following = array();
    foreach ($following_data as $following_user) {
        $following_user_data = array();
        $following_name = $user -> profile_name($following_user);
        $following_image = $user -> profile_image_html($following_user);
        $following_info = "<a href=\"/admin/index.php?route=page_user&user_id=$following_user\" target=\"_blank\"><div class=\"profile_info\">$following_image<div class=\"profile_name\"><span>$following_name</span></div></div></a>";

        $date_of_follow_timestamp = get_action_date(3, $following_user, $user_id);
        $date_of_follow = date("d.m.Y H:i", $date_of_follow_timestamp);
        array_push($following_user_data, $following_info, $date_of_follow);
        array_push($following, $following_user_data);
    }
    $following = json_encode($following);


    $blocked_data = $user_data["data"]["blocked"];
    $blocked = array();
    foreach ($blocked_data as $blocked_user) {
        $blocked_user_data = array();
        $blocked_user_name = $user -> profile_name($blocked_user);
        $blocked_user_image = $user -> profile_image_html($blocked_user);
        $blocked_user_info = "<a href=\"/admin/index.php?route=page_user&user_id=$blocked_user\" target=\"_blank\"><div class=\"profile_info\">$blocked_user_image<div class=\"profile_name\"><span>$blocked_user_name</span></div></div></a>";

        $date_of_block_timestamp = get_action_date(6, $user_id, $blocked_user);
        $date_of_block = date("d.m.Y H:i", $date_of_block_timestamp);
        array_push($blocked_user_data, $blocked_user_info, $date_of_block);
        array_push($blocked, $blocked_user_data);
    }
    $blocked = json_encode($blocked);


    $user_devices_data = $user_data["data"]["devices"];
    $user_devices = array();
    foreach ($user_devices_data as $device) {
        $device_data = array();
        $device_uuid = $device["device_uuid"];
        $device_model = $device["device_model"];
        $operating_system = $device["operating_system"];
        $device_is_blocked = $device["is_blocked"];
        array_push($device_data, $device_uuid, $device_model, $operating_system, $device_is_blocked);
        array_push($user_devices, $device_data);
    }
    $devices = json_encode($user_devices);

    $streams_online_data = $user_data["data"]["streams"]["online"];
    $streams_online = array();
    foreach ($streams_online_data as $stream_online_uuid) {
        $stream_online_data = array();
        $stream_online_query = $stream -> stream_data($stream_online_uuid, $user_id);

        if ($stream_online_query["status"] == "OK"){
            $thumb = $snapshot_query.$stream_online_uuid;
            $stream_online_url = $stream_online_query["data"]["url"];
            $stream_online_name = $stream_online_query["data"]["name"];
            
            $stream_preview = "<a data-toggle=\"modal\" data-target=\".bs-modal-lg\" data-stream-uuid=\"$stream_online_uuid\"   data-stream-url=\"$stream_online_url\" data-stream-name=\"$stream_online_name\" class=\"stream_view\"><div class=\"screenshot\" style=\"background: #D6D6D6 url('$thumb') center center no-repeat;  background-size: cover;\"></div></a>";

            $stream_online_start_date = date("d.m.Y H:i", $stream_online_query["data"]["start_date"]);
            array_push($stream_online_data, $stream_preview, $stream_online_uuid, $stream_online_name, $stream_online_start_date);
            array_push($streams_online, $stream_online_data);
        }
    }
    $streams_online = json_encode($streams_online);

    $streams_archive_data = $user_data["data"]["streams"]["archive"];
    $streams_archive = array();
    foreach ($streams_archive_data as $stream_archive_uuid) {
        $stream_archive_data = array();
        $stream_archive_query = $stream -> stream_data($stream_archive_uuid, $user_id);

        if ($stream_archive_query["status"] == "OK"){
            $thumb = $snapshot_query.$stream_archive_uuid;
            $stream_archive_url = $stream_archive_query["data"]["url"];
            $stream_archive_name = $stream_archive_query["data"]["name"];

            $stream_preview = "<a data-toggle=\"modal\" data-target=\".bs-modal-lg\" data-stream-uuid=\"$stream_archive_uuid\"   data-stream-url=\"$stream_archive_url\" data-stream-name=\"$stream_archive_name\" class=\"stream_view\"><div class=\"screenshot\" style=\"background: #D6D6D6 url('$thumb') center center no-repeat;  background-size: cover;\"></div></a>";

            $stream_archive_start_date = date("d.m.Y H:i", $stream_archive_query["data"]["start_date"]);
            $stream_archive_end_date = date("d.m.Y H:i", $stream_archive_query["data"]["end_date"]);
            array_push($stream_archive_data, $stream_preview, $stream_archive_uuid, $stream_archive_name, $stream_archive_start_date, $stream_archive_end_date);
            array_push($streams_archive, $stream_archive_data);
        }

    }
    $streams_archive = json_encode($streams_archive);


    $streams_recent_data = $user_data["data"]["streams"]["recent"];
    $streams_recent = array();
    foreach ($streams_recent_data as $stream_recent) {
        $stream_recent_data = array();
        array_push($stream_recent_data, $stream_recent);
        array_push($streams_recent, $stream_recent_data);
    }
    $streams_recent = json_encode($streams_recent);

    echo "
	            <div class=\"right_col\" role=\"main\">
	                <div class=\"\">

	                    <div class=\"page-title\">
	                        <div class=\"title_left\">
	                            <h3>Профиль пользователя</h3>
	                        </div>
	                    </div>
	                    <div class=\"clearfix\"></div>

						<div class=\"row\">
	                        <div class=\"col-xs-12\">
	                            <div class=\"admin_panel\">
	                                <div class=\"admin_panel_title\">
	                                    <h2>$display_name</h2>
	                                    <div class=\"clearfix\"></div>
                                    </div>

	                                <div class=\"admin_panel_content\">
	                                    <div class=\"row\">
                                            <div class=\"col-md-3 col-sm-3 col-xs-12 profile_left\">
                                                <div class=\"profile_img\">
                                                    <div id=\"crop - avatar\">
                                                        <div class=\"avatar-view\" style=\"background: url('$profile_image') 100% 100% no-repeat;  background-size: cover;\">
                                                            
                                                        </div>
                                                    </div>
                                                </div>
                                                <ul class=\"list-unstyled user_data\">
                                                    <li>$about</li>
                                                </ul>
                                            </div>
                                            <div class=\"col-md-9 col-sm-9 col-xs-12\">
                                                <div class=\"profile_title\">
                                                    <div class=\"col-md-6\">
                                                        <h2>User Activity Report</h2>
                                                    </div>
                                                    <div class=\"col-md-6\">
                                                        <div id=\"reportrange\" class=\"pull-right\" style=\"margin-top: 5px; background: #fff; cursor: pointer; padding: 5px 10px; border: 1px solid #E6E9ED\">
                                                        </div >
                                                    </div >
                                                </div >
                                            </div >
                                        </div>
									</div>		<!-- end admin_panel_content -->

								</div>		<!-- end admin_panel -->
							</div>		<!-- end col-xs-12 -->
						</div>		<!-- end row -->
						
						<div class=\"row\">
	                        <div class=\"col-xs-12\">
	                            <div class=\"admin_panel\">
	                                <div class=\"admin_panel_title\">
	                                    <h2>Подписчики пользователя</h2>
	                                    <div class=\"clearfix\"></div>
                                    </div>

	                                <div class=\"admin_panel_content\">
	                                    <div class=\"row\">
	                                        <div class=\"col-xs-12 user_followers_data_block\">
	                                            <table id=\"followers\" class=\"table table-striped projects\">
                                                    <thead>
                                                        <tr>
                                                            <th>Profile</th>
                                                            <th>Date of follow</th>
                                                        </tr>
                                                    </thead>
		                                        </table>                                      
                                            </div>                                            
                                        </div>
									</div>		<!--end admin_panel_content-->

								</div>		<!--end admin_panel-->
							</div>		<!--end col - xs - 12-->
						</div>		<!--end row-->
						
						<div class=\"row\">
	                        <div class=\"col-xs-12\">
	                            <div class=\"admin_panel\">
	                                <div class=\"admin_panel_title\">
	                                    <h2>Подписки пользователя</h2>
	                                    <div class=\"clearfix\"></div>
                                    </div>

	                                <div class=\"admin_panel_content\">
	                                    <div class=\"row\">
	                                        <div class=\"col-xs-12 user_followers_data_block\">
	                                            <table id=\"following\" class=\"table table-striped projects\">
                                                    <thead>
                                                        <tr>
                                                            <th>Profile</th>
                                                            <th>Date of follow</th>
                                                        </tr>
                                                    </thead>
		                                        </table> 	                                        
                                            </div>                                            
                                        </div>
									</div>		<!--end admin_panel_content-->

								</div>		<!--end admin_panel-->
							</div>		<!--end col - xs - 12-->
						</div>		<!--end row-->
						
						<div class=\"row\">
	                        <div class=\"col-xs-12\">
	                            <div class=\"admin_panel\">
	                                <div class=\"admin_panel_title\">
	                                    <h2>Заблокированные пользователи</h2>
	                                    <div class=\"clearfix\"></div>
                                    </div>

	                                <div class=\"admin_panel_content\">
	                                    <div class=\"row\">
	                                        <div class=\"col-xs-12 user_followers_data_block\">
	                                            <table id=\"blocked\" class=\"table table-striped projects\">
                                                    <thead>
                                                        <tr>
                                                            <th>Profile</th>
                                                            <th>Date of block</th>
                                                        </tr>
                                                    </thead>
		                                        </table>  
                                            </div>                                            
                                        </div>
									</div>		<!--end admin_panel_content-->

								</div>		<!--end admin_panel-->
							</div>		<!--end col - xs - 12-->
						</div>		<!--end row-->
						
						<div class=\"row\">
	                        <div class=\"col-xs-12\">
	                            <div class=\"admin_panel\">
	                                <div class=\"admin_panel_title\">
	                                    <h2>Устройства пользователя</h2>
	                                    <div class=\"clearfix\"></div>
                                    </div>

	                                <div class=\"admin_panel_content\">
	                                    <div class=\"row\">
	                                        <div class=\"col-xs-12\">
	                                        
                                                <table id=\"user_devices\" class=\"table table-striped projects\">
                                                    <thead>
                                                        <tr>
                                                            <th style=\"width: 20%\">Device uuid</th>
                                                            <th>Device model</th>
                                                            <th>Operating system</th>
                                                            <th style=\"width: 20%\">Is device blocked</th>
                                                        </tr>
                                                    </thead>
		                                        </table>
	                                        
                                            </div>                                            
                                        </div>
									</div>		<!--end admin_panel_content-->

								</div>		<!--end admin_panel-->
							</div>		<!--end col - xs - 12-->
						</div>		<!--end row-->
						
						<div class=\"row\">
	                        <div class=\"col-xs-12\">
	                            <div class=\"admin_panel\">
	                                <div class=\"admin_panel_title\">
	                                    <h2>Live streams</h2>
	                                    <div class=\"clearfix\"></div>
                                    </div>

	                                <div class=\"admin_panel_content\">
	                                    <div class=\"row\">
	                                        <div class=\"col-xs-12\">
	                                        
                                                <table id=\"streams_online\" class=\"table table-striped projects\">
                                                    <thead>
                                                        <tr>
                                                            <th>Скриншот</th>
                                                            <th>Тайтл</th>
                                                            <th>Название<br>трансляции</th>
                                                            <th>Начало<br>трансляции</th>
                                                        </tr>
                                                    </thead>
		                                        </table>
	                                        
                                            </div>                                            
                                        </div>
									</div>		<!--end admin_panel_content-->

								</div>		<!--end admin_panel-->
							</div>		<!--end col - xs - 12-->
						</div>		<!--end row-->
						
						<div class=\"row\">
	                        <div class=\"col-xs-12\">
	                            <div class=\"admin_panel\">
	                                <div class=\"admin_panel_title\">
	                                    <h2>Archive streams</h2>
	                                    <div class=\"clearfix\"></div>
                                    </div>

	                                <div class=\"admin_panel_content\">
	                                    <div class=\"row\">
	                                        <div class=\"col-xs-12\">
	                                        
                                                <table id=\"streams_archive\" class=\"table table-striped projects\">
                                                    <thead>
                                                        <tr>
                                                            <th>Скриншот</th>
                                                            <th>Тайтл</th>
                                                            <th>Название<br>трансляции</th>
                                                            <th>Начало<br>трансляции</th>
                                                            <th>Завершение<br>трансляции</th>
                                                        </tr>
                                                    </thead>
		                                        </table>
	                                        
                                            </div>                                            
                                        </div>
									</div>		<!--end admin_panel_content-->

								</div>		<!--end admin_panel-->
							</div>		<!--end col - xs - 12-->
						</div>		<!--end row-->
						
						<div class=\"row\">
	                        <div class=\"col-xs-12\">
	                            <div class=\"admin_panel\">
	                                <div class=\"admin_panel_title\">
	                                    <h2>Recent streams</h2>
	                                    <div class=\"clearfix\"></div>
                                    </div>

	                                <div class=\"admin_panel_content\">
	                                    <div class=\"row\">
	                                        <div class=\"col-xs-12\">
	                                        
                                                <table id=\"streams_recent\" class=\"table table-striped projects\">
                                                    <thead>
                                                        <tr>
                                                            <th>Stream uuid</th>
                                                        </tr>
                                                    </thead>
		                                        </table>
	                                        
                                            </div>                                            
                                        </div>
									</div>		<!--end admin_panel_content-->

								</div>		<!--end admin_panel-->
							</div>		<!--end col - xs - 12-->
						</div>		<!--end row-->

					</div>
	            </div>		<!-- end right_col -->
	            <script>
                    var PROFILE_DATA = {
                        followers: $followers,
                        following: $following,
                        blocked: $blocked,
                        devices: $devices,
                        streams_online: $streams_online,
                        streams_archive: $streams_archive,
                        streams_recent: $streams_recent
                    };
                </script>

				<script src=\"/admin/assets/js/admin.user.data.js\"></script>
	";

} else {
    echo "
	            <div class=\"right_col\" role=\"main\">
	                <div class=\"\">

	                    <div class=\"page-title\">
	                        <div class=\"title_left\">
	                            <h3>Профиль пользователя</h3>
	                        </div>
	                    </div>
	                    <div class=\"clearfix\"></div>

						<div class=\"row\">
	                        <div class=\"col-xs-12\">
	                            <div class=\"admin_panel\">

	                                <div class=\"admin_panel_content\">
	                                	Пользователь не найден

									</div>		<!-- end admin_panel_content -->

								</div>		<!-- end admin_panel -->
							</div>		<!-- end col-xs-12 -->
						</div>		<!-- end row -->

					</div>
	            </div>		<!-- end right_col -->
	";
}
?>