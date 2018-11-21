<?php
global $db, $lang;
$admin_id = $_SESSION["support_admin"];
echo "
	            <div class=\"right_col\" role=\"main\">
	                <div>
	                    <div class=\"page-title\">
	                        <div class=\"title_left\">
	                            <h3>Список архивных чатов</h3>
	                        </div>
	                    </div>
	                    <div class=\"clearfix\"></div>
	                    
						<div class=\"row\">
	                        <div class=\"col-xs-12\">
	                            <div class=\"admin_panel\">
	                                <div class=\"admin_panel_content\">
	                                	<div>
		                                	<div class=\"clear\"></div>
		                                	
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
									</div>		<!-- end admin_panel_content -->
								</div>		<!-- end admin_panel -->

							</div>		<!-- end col-xs-12 -->
						</div>		<!-- end row -->
					</div>	
	            </div>		<!-- end right_col -->		            
				
				<script src=\"/support_service/assets/js/support.archivechats.js\"></script>";

?>