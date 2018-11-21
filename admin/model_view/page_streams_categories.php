<?php
echo "
	            <div class=\"right_col\" role=\"main\">
	                <div class=\"\">

	                    <div class=\"page-title\">
	                        <div class=\"title_left\">
	                            <h3>Добавление категории</h3>
	                        </div>
	                    </div>
	                    <div class=\"clearfix\"></div>

						<div class=\"row\">
	                        <div class=\"col-xs-12\">
	                            <div class=\"admin_panel\">
	                                <div class=\"admin_panel_content\">
										<form  class=\"form-horizontal form-label-left\" method=\"post\" id=\"category_add\">
											<div class=\"form-group\">
												<label class=\"control-label col-xs-12 col-sm-3\">Название (русский язык)</label>
												<div class=\"col-xs-12 col-sm-5\">
													<input type=\"text\" class=\"form-control\" id=\"name_ru\" name=\"name_ru\" placeholder=\"Введите название категории на русском языке\">
												</div>
											</div>
											
											<div class=\"form-group\">
												<label class=\"control-label col-xs-12 col-sm-3\">Название (английский язык)</label>
												<div class=\"col-xs-12 col-sm-5\">
													<input type=\"text\" class=\"form-control\" id=\"name_en\" name=\"name_en\" placeholder=\"Введите название категории на английском языке\">
												</div>
											</div>											

											<div class=\"ln_solid\"></div>
											<div class=\"form-group\">
	                                            <div class=\"col-xs-12 col-md-5 col-md-offset-3\" id=\"category_add_result\"></div>
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
	                            <h3>Управление категориями</h3>
	                        </div>
	                    </div>
	                    <div class=\"clearfix\"></div>
	                    
						<div class=\"row\">
	                        <div class=\"col-xs-12\">
	                            <div class=\"admin_panel\">
	                                <div class=\"admin_panel_content\">
										<div>
		                                	<div class=\"clear\"></div>
		                                	<table id=\"streams_categories\" class=\"table table-striped responsive-utilities jambo_table bulk_action\" width=\"100%\">
												<thead>
		                                            <tr class=\"headings\">
		                                                <th class=\"column-title\">Название (ru)</th>
		                                                <th class=\"column-title\">Название (en)</th>
		                                                <th class=\"column-title\">Дата создания</th>
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

				<script src=\"/admin/assets/js/admin.categories.js\"></script>";
?>