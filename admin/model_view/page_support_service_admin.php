<?php
global $db, $support_service, $snapshot_query;

$admin_id = prepair_str($_GET["admin_id"]);
$result_admin = $support_service -> get_admin_data($admin_id);

if ($result_admin["status"] == "OK"){
    $login = $result_admin["data"]["login"];
    $display_name = $result_admin["data"]["display_name"];
    $profile_image = $result_admin["data"]["profile_image"];
    $email = $result_admin["data"]["email"];
    $comment = $result_admin["data"]["comments"];

    echo "
	            <div class=\"right_col\" role=\"main\">
	                <div class=\"\">

	                    <div class=\"page-title\">
	                        <div class=\"title_left\">
	                            <h3>Профиль администратора службы поддержки</h3>
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
                                                    <li>$comment</li>
                                                </ul>
                                            </div>
                                            <div class=\"col-md-9 col-sm-9 col-xs-12\">
                                                <div class=\"profile_title\">
                                                    <div class=\"col-md-6\">
                                                        <h2>Admin Activity Report</h2>
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
	                                    <h2>Чаты админстратора службы поддержки</h2>
	                                    <div class=\"clearfix\"></div>
                                    </div>

	                                <div class=\"admin_panel_content\">
	                                    <div class=\"row\">
	                                        <div class=\"col-xs-12 user_followers_data_block\">
	                                            <table id=\"archive_chats\" class=\"table table-striped responsive-utilities jambo_table bulk_action\" width=\"100%\">
												<thead>
		                                            <tr class=\"headings\">
		                                            	<th class=\"column-title\">#</th>
		                                                <th class=\"column-title\">Профиль пользователя</th>
		                                                <th class=\"column-title\">Дата создания чата</th></th>
		                                                <th class=\"column-title\">Дата принятия<br>чата администратором</th>
		                                                <th class=\"column-title\">Количество<br>сообщений</th>
		                                                <th class=\"column-title\">Чат закрыт</th>
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
                    var ADMIN_DATA = {
                        admin_id: $admin_id
                    };
                </script>

				<script src=\"/admin/assets/js/admin.support_service_admin.js\"></script>
	";

} else {
    echo "
	            <div class=\"right_col\" role=\"main\">
	                <div class=\"\">

	                    <div class=\"page-title\">
	                        <div class=\"title_left\">
	                            <h3>Профиль админстратора службы поддержки</h3>
	                        </div>
	                    </div>
	                    <div class=\"clearfix\"></div>

						<div class=\"row\">
	                        <div class=\"col-xs-12\">
	                            <div class=\"admin_panel\">

	                                <div class=\"admin_panel_content\">
	                                	Админстратор службы поддержки не найден
									</div>		<!-- end admin_panel_content -->

								</div>		<!-- end admin_panel -->
							</div>		<!-- end col-xs-12 -->
						</div>		<!-- end row -->

					</div>
	            </div>		<!-- end right_col -->
	";
}
?>