<?php
global $db;
$result_all_users = $db -> sql_query("SELECT * FROM `users` WHERE `is_deleted` = '0'", "", "array");
$all_users_count = sizeof($result_all_users);
$result_users = $db -> sql_query("SELECT * FROM `users` WHERE is_official = '0' AND `is_deleted` = '0'", "", "array");
$users_count = sizeof($result_users);
$result_official = $db -> sql_query("SELECT * FROM users WHERE `is_official` = '1' AND `is_deleted` = '0'", "", "array");
$official_count = sizeof($result_official);
$result_streams = $db -> sql_query("SELECT * FROM `streams` LEFT JOIN `users` ON `streams`.`user_id` = `users`.`id` WHERE `streams`.`is_excess` = '0' AND `streams`.`is_deleted` = '0' AND `users`.`is_deleted` = '0'", "", "array");
$streams_count = sizeof($result_streams);
echo "
	            <div class=\"right_col\" role=\"main\">
	                <div class=\"\">

	                    <div class=\"page-title\">
	                        <div class=\"title_left\">
	                            <h3>Главная</h3>
	                        </div>
	                    </div>
	                    <div class=\"clearfix\"></div>
	                    
						<div class=\"row\">
	                        <div class=\"col-xs-12\">
	                            <div class=\"admin_panel\">

	                                <div class=\"admin_panel_content\">
	                                	<div class=\"row tile_count\">
											<div class=\"animated flipInY col-xs-4 tile_stats_count\">
												<div class=\"left\"></div>
												<div class=\"right\">
													<span class=\"count_top\"><i class=\"fa fa-user\"></i> Количество пользователей</span>
													<div class=\"count\">$all_users_count</div>
												</div>
											</div>
											<div class=\"animated flipInY col-xs-4 tile_stats_count\">
												<div class=\"left\"></div>
												<div class=\"right\">
													<span class=\"count_top\"><i class=\"fa fa-video-camera\"></i> Количество трансляций</span>
													<div class=\"count\">$streams_count</div>
												</div>
											</div>
										</div>

	                                Раздел в  разработке
	                                	<p>Количество пользователей всего:  $all_users_count, в т.ч.</p>
	                                	<ul>
											<li>обычные пользователи  $users_count</li>
											<li>официальные источники   $official_count</li>
										</ul>
	                                	<p>Количество трансляций $streams_count</p>

									</div>		<!-- end admin_panel_content -->
									
								</div>		<!-- end admin_panel -->
							</div>		<!-- end col-xs-12 -->
						</div>		<!-- end row -->
						
					</div>	
	            </div>		<!-- end right_col -->	
	            
				<script src=\"\"></script>
	";
?>