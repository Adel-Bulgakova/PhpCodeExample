<?php
global $db;
echo "
	            <div class=\"right_col\" role=\"main\">
	                <div class=\"\">

	                    <div class=\"page-title\">
	                        <div class=\"title_left\">
	                            <h3>Создание уведомления для push-нотификации</h3>
	                        </div>
	                    </div>
	                    <div class=\"clearfix\"></div>

						<div class=\"row\">
	                        <div class=\"col-xs-12\">
	                            <div class=\"admin_panel\">
	                                <div class=\"admin_panel_content\">
										<form  class=\"form-horizontal form-label-left\" method=\"post\" id=\"notice_add\">

											<div class=\"form-group\">
												<label class=\"control-label col-xs-12 col-sm-3\">Текст уведомления</label>
												<div class=\"col-xs-12 col-sm-5\">
													<textarea class=\"form-control\" name=\"notice_text\" rows=\"3\"></textarea>
												</div>
											</div>

											<div class=\"form-group\">
												<label class=\"control-label col-xs-12 col-sm-3\">Начало активности</label>
												<div class=\"col-xs-12 col-sm-5\">
													<input name=\"activated_date\" class=\"date-picker form-control activated_date active\" required=\"required\" type=\"text\" placeholder=\"Начало активности\">
												</div>
											</div>							
																						
											<div class=\"form-group\">
												<label class=\"control-label col-xs-12 col-sm-3\">Завершение активности</label>
												<div class=\"col-xs-12 col-sm-5\">
													<input name=\"deactivated_date\" class=\"date-picker form-control deactivated_date\" type=\"text\" placeholder=\"Завершение активности\">
												</div>
											</div>
											
											<div class=\"form-group\">
												<label class=\"control-label col-xs-12 col-sm-3\">Бессрочно</label>
												<div class=\"col-xs-12 col-sm-5\">
													<input class=\"without_expiration_param\" name=\"without_expiration_param\" type=\"checkbox\" value=\"1\">
												</div>
											</div>

											<div class=\"ln_solid\"></div>
											<div class=\"form-group\">
	                                            <div class=\"col-xs-12 col-md-5 col-md-offset-3\" id=\"notice_add_result\"></div>
	                                        </div>
	                                        <div class=\"form-group\">
	                                            <div class=\"col-xs-12 col-md-5 col-md-offset-3 text-center\">
	                                                <button type=\"submit\" class=\"btn btn-primary\">Создать уведомление</button>
	                                            </div>
	                                        </div>

										</form>
									</div>		<!-- end admin_panel_content -->
								</div>		<!-- end admin_panel -->

							</div>		<!-- end col-xs-12 -->
						</div>		<!-- end row -->

	                    <div class=\"page-title\">
	                        <div class=\"title_left\">
	                            <h3>Управление уведомлениями</h3>
	                        </div>
	                    </div>
	                    <div class=\"clearfix\"></div>
	                    
						<div class=\"row\">
	                        <div class=\"col-xs-12\">
	                            <div class=\"admin_panel\">
	                                <div class=\"admin_panel_content\">
										<div>
		                                	<div class=\"clear\"></div>
		                                	<table id=\"notices\" class=\"table table-striped responsive-utilities jambo_table bulk_action\" width=\"100%\">
												<thead>
		                                            <tr class=\"headings\">
		                                                <th class=\"column-title\">№</th>
		                                                <th class=\"column-title\">Текст уведомления</th>
		                                                <th class=\"column-title\">Дата начала активности</th>
		                                                <th class=\"column-title\">Дата завершения активности</th>
		                                                <th class=\"column-title\">Статус</th>
		                                                <th class=\"column-title\">Отправить<br>уведомление</th>
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
	            
	            <script src=\"/admin/assets/lib/moment.js\"></script>	            
                <script src=\"/admin/assets/lib/datepicker/js/bootstrap-datetimepicker.min.js\"></script>
				<script src=\"/admin/assets/js/admin.notice.js\"></script>";
?>