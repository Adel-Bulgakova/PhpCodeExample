<?php
global $db;
$super_user = $_SESSION["super_user"];
$next_id = $db -> sql_query("SELECT id FROM support_service_admins ORDER BY id DESC LIMIT 1", "", "array");
$next_id = $next_id[0]["id"] + 1;
$admin_login = "admin_".$next_id;
echo "
	            <div class=\"right_col\" role=\"main\">
	                <div class=\"\">

	                    <div class=\"page-title\">
	                        <div class=\"title_left\">
	                            <h3>Добавление администратора службы поддержки</h3>
	                        </div>
	                    </div>
	                    <div class=\"clearfix\"></div>

						<div class=\"row\">
	                        <div class=\"col-xs-12\">
	                            <div class=\"admin_panel\">
	                                <div class=\"admin_panel_content\">
										<form  class=\"form-horizontal form-label-left\" method=\"post\" id=\"admin_add\">

											<div class=\"form-group\">
												<label class=\"control-label col-xs-12 col-sm-3\">Логин</label>
												<div class=\"col-xs-12 col-sm-5\">
													<input type=\"text\" class=\"form-control\" id=\"admin_login\" name=\"admin_login\" readonly=\"readonly\" value=\"$admin_login\">
												</div>
											</div>

											<div class=\"form-group\">
												<label class=\"control-label col-xs-12 col-sm-3\">Имя Фамилия</label>
												<div class=\"col-xs-12 col-sm-5\">
													<input type=\"text\" class=\"form-control\" id=\"admin_name\" name=\"admin_name\" placeholder=\"Имя Фамилия\">
												</div>
											</div>

											<div class=\"form-group\">
												<label class=\"control-label col-xs-12 col-sm-3\">Пароль</label>
												<div class=\"col-xs-12 col-sm-5\">
													<input type=\"text\" class=\"form-control\" id=\"password\" name=\"password\" readonly=\"readonly\" value=\"\" placeholder=\"Пароль\">
												</div>
												<div class=\"col-xs-12 col-sm-4\">
													<button type=\"button\" class=\"btn btn-success btn-sm\" id=\"generate\">Сгенерировать</button>
												</div>
											</div>

											<div class=\"form-group\">
												<label class=\"control-label col-xs-12 col-sm-3\">Комментарии</label>
												<div class=\"col-xs-12 col-sm-5\">
													<input type=\"text\" class=\"form-control\" id=\"comments\" name=\"comments\" placeholder=\"Комментарии\">
												</div>
											</div>

											<div class=\"ln_solid\"></div>
											<div class=\"form-group\">
	                                            <div class=\"col-xs-12 col-md-5 col-md-offset-3\" id=\"admin_add_result\"></div>
	                                        </div>
	                                        <div class=\"form-group\">
	                                            <div class=\"col-xs-12 col-md-5 col-md-offset-3 text-center\">
	                                                <button type=\"submit\" class=\"btn btn-primary\">Добавить</button>
	                                            </div>
	                                        </div>

										</form>
									</div>		<!-- end admin_panel_content -->
								</div>		<!-- end admin_panel -->

							</div>		<!-- end col-xs-12 -->
						</div>		<!-- end row -->

	                    <div class=\"page-title\">
	                        <div class=\"title_left\">
	                            <h3>Управление администраторами службы поддержки</h3>
	                        </div>
	                    </div>
	                    <div class=\"clearfix\"></div>
	                    
						<div class=\"row\">
	                        <div class=\"col-xs-12\">
	                            <div class=\"admin_panel\">
	                                <div class=\"admin_panel_content\">
										<div>
		                                	<div class=\"clear\"></div>
		                                	<table id=\"support_service\" class=\"table table-striped responsive-utilities jambo_table bulk_action\" width=\"100%\">
												<thead>
		                                            <tr class=\"headings\">
		                                                <th class=\"column-title\">Профиль</th>
		                                                <th class=\"column-title\">Логин</th>
		                                                <th class=\"column-title\">Комментарии</th>
		                                                <th class=\"column-title\">Email</th>
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

				<script src=\"/admin/assets/js/admin.support.js\"></script>";
?>