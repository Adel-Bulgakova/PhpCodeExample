<?php
global $db, $lang;
$admin_id = $_SESSION["support_admin"];
echo "
	            <div class=\"right_col\" role=\"main\">
	                <div>
	                    <div class=\"ws_state\"></div>
	                    <div class=\"page-title\">
	                        <div class=\"title_left\">
	                            <h3>Список ожидающих пользователей</h3>
	                        </div>
	                    </div>
	                    <div class=\"clearfix\"></div>
	                    
						<div class=\"row\">
	                        <div class=\"col-xs-12\">
	                            <div class=\"admin_panel\">
	                                <div class=\"admin_panel_content\">
	                                	<div>
		                                	<div class=\"clear\"></div>
		                                	                             
		                                	    <table class=\"table table-striped pending_chats_container\">
                                                    <thead>
                                                        <tr>
                                                            <th>Пользователь</th>
                                                            <th>Дата</th>
                                                            <th>Сообщения,<br>полученные за время ожидания</th>
                                                            <th>Действие</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                    </tbody>
                                                </table>                        
		                                    
										</div>		
									</div>		<!-- end admin_panel_content -->
								</div>		<!-- end admin_panel -->

							</div>		<!-- end col-xs-12 -->
						</div>		<!-- end row -->
						
	                    <div class=\"page-title\">
	                        <div class=\"title_left\">
	                            <h3>Список активных чатов</h3>
	                        </div>
	                    </div>
	                    <div class=\"clearfix\"></div>
	                    
						<div class=\"row active_chats_container\"></div>		
						<!-- end row -->
					</div>	
	            </div>		<!-- end right_col -->		            
				
				<script src=\"/support_service/assets/js/support.activechats.js\"></script>
				<script>
                    var SUPPORT_PAGE_DATA = {
                        lang: 'ru',				
                        admin_id: $admin_id,
                        websocket_server_url: 'wss://$_SERVER[HTTP_HOST]:8888/websocket_server_support/'
                    };			
                </script>
	";
?>