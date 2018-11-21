<?php
global $db;
$super_user = $_SESSION["super_user"];
echo "
	            <div class=\"right_col\" role=\"main\">
	                <div>
	                    <div class=\"page-title\">
	                        <div class=\"title_left\">
	                            <h3>Список трансляций</h3>
	                        </div>
	                    </div>
	                    <div class=\"clearfix\"></div>
	                    
						<div class=\"row\">
	                        <div class=\"col-xs-12\">
	                            <div class=\"admin_panel\">
	                                <div class=\"admin_panel_content\">
	                                	<div>
		                                	<div class=\"clear\"></div>	
		                                    <table id=\"streams\" class=\"table table-striped responsive-utilities jambo_table bulk_action\" width=\"100%\">
												<thead>
		                                            <tr class=\"headings\">
		                                                <th class=\"column-title\">Скриншот</th>
		                                                <th class=\"column-title\">Профиль/<br>Название<br>трансляции</th>
		                                                <th class=\"column-title\">Тайтл</th>
		                                                <th class=\"column-title\">Статус<br>трансляции</th>
		                                                <th class=\"column-title\">Начало<br>трансляции</th>
		                                                <th class=\"column-title\">Завершение<br>трансляции</th>
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
				
				<script src=\"/admin/assets/js/admin.streams.js\"></script>";
?>