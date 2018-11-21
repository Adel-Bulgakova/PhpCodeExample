<?php
global $db;
$super_user = $_SESSION["super_user"];
echo "
	            <div class=\"right_col\" role=\"main\">
	                <div class=\"\">                   
	                    
	                    <div class=\"page-title\">
	                        <div class=\"title_left\">
	                            <h3>Список жалоб на комментарии пользователей</h3>
	                        </div>
	                    </div>
	                    <div class=\"clearfix\"></div>
	                    
						<div class=\"row\">
	                        <div class=\"col-xs-12\">
	                            <div class=\"admin_panel\">
	                                <div class=\"admin_panel_content\">
	                                	<div>
		                                	<div class=\"clear\"></div>
		                                    <table id=\"claims_comments\" class=\"table table-striped responsive-utilities jambo_table bulk_action\" width=\"100%\">
												<thead>
		                                            <tr class=\"headings\">
		                                            	<th class=\"column-title\">Текст<br>комментария</th>
		                                                <th class=\"column-title\">Профиль<br>автора комментария</th>
		                                                <th class=\"column-title\">Профиль<br>зрителя</th>
		                                                <th class=\"column-title\">Дата<br>поступления</th>
		                                                <th class=\"column-title\">Статус</th>
		                                                <th class=\"column-title\">Действие</th>
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

				<script src=\"/admin/assets/js/admin.claims.comments.js\"></script>";
?>