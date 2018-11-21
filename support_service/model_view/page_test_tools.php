<?php
global $db;
$super_user = $_SESSION["support_admin"];
echo "
	<div class=\"right_col\" role=\"main\">
		<div class=\"\">
	
			<div class=\"page-title\">
				<div class=\"title_left\">
					<h3>Тестирование устройства</h3>
				</div>
			</div>
			<div class=\"clearfix\"></div>
			<div class=\"row\">
	                    
				<div class=\"col-xs-12 col-md-6\">
					<div class=\"admin_panel\">
						<div class=\"admin_panel_title\">Тестирование IP</div>
						<div class=\"admin_panel_content\">
							<form  class=\"form-horizontal form-label-left\" method=\"post\" id=\"test_ping\">
								<div class=\"form-group\">
									<label class=\"control-label col-xs-12 col-sm-3\"> Введите IP адрес камеры</label>
									<div class=\"col-xs-12 col-sm-6\">
										<input type=\"text\" class=\"form-control\" name=\"ip\" placeholder=\"146.66.204.186\">
									</div>
								</div>
	                            <div class=\"form-group\">
	                       			<div class=\"col-xs-12 col-sm-9 col-sm-offset-3\">
	                            		<button type=\"submit\" class=\"btn btn-primary\">Тестировать</button>
	                            	</div>
	                            </div>

							</form>
						</div>
					</div>
				</div>
							
				<div class=\"col-xs-12 col-md-6\">
					<div class=\"admin_panel\">
						<div class=\"admin_panel_title\">Результат</div>
						<div class=\"admin_panel_content\" id=\"result_ip\"></div>
					</div>
				</div>

				<div class=\"clearfix\"></div>

				<div class=\"col-xs-12 col-md-6\">
					<div class=\"admin_panel\">
						<div class=\"admin_panel_title\">Тестирование порта</div>
						<div class=\"admin_panel_content\">
							<form  class=\"form-horizontal form-label-left\" method=\"post\" id=\"test_port\">
								<div class=\"form-group\">
									<label class=\"control-label col-xs-12 col-sm-3\"> Введите IP адрес камеры</label>
									<div class=\"col-xs-12 col-sm-6\">
										<input type=\"text\" class=\"form-control\" name=\"ip\" placeholder=\"146.66.204.186\">
									</div>
								</div>
								<div class=\"form-group\">
									<label class=\"control-label col-xs-12 col-sm-3\"> Введите порт камеры</label>
									<div class=\"col-xs-12 col-sm-6\">
										<input type=\"text\" class=\"form-control\" name=\"port\" placeholder=\"554\">
									</div>
								</div>
								<div class=\"form-group\">
									<div class=\"col-xs-12 col-sm-9 col-sm-offset-3\">
										<button type=\"submit\" class=\"btn btn-primary\">Тестировать</button>
									</div>
								</div>

							</form>
						</div>
					</div>
				</div>

				<div class=\"col-xs-12 col-md-6\">
				<div class=\"admin_panel\">
					<div class=\"admin_panel_title\">Результат</div>
					<div class=\"admin_panel_content\" id=\"result_port\"></div>
				</div>
			</div>
	    	</div>

		</div>
	</div>		<!-- end right_col -->
	            
	<script src=\"/support_service/assets/js/support.test.tools.js\"></script>";
?>