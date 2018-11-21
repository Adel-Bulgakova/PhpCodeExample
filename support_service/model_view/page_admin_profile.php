<?php
global $db;
$admin_id = $_SESSION["support_admin"];
$result = $db -> sql_query("SELECT*FROM support_service_admins WHERE id = '$admin_id' AND is_deleted = '0'", "", "array");
$login = $result[0]["login"];
$name = $result[0]["name"];
$email = $result[0]["email"];

echo "
	            <div class=\"right_col\" role=\"main\">
	                <div>
	                    <div class=\"page-title\">
	                        <div class=\"title_left\">
	                            <h3>Профиль администратора службы поддержки</h3>
	                        </div>
	                    </div>
	                    <div class=\"clearfix\"></div>
	                    
						<div class=\"row\">
	                        <div class=\"col-xs-12\">
	                            <div class=\"admin_panel\">
	                                <div class=\"admin_panel_content\">
	                                	<div>
		                                	<div class=\"clear\"></div>	
		                                    <form class=\"form-horizontal\" method=\"post\" name=\"admin_profile_edit_form\" id=\"admin_profile_edit_form\" >
                                                <div class=\"form-group\">
                                                    <label class=\"col-xs-3 col-md-2 col-md-offset-1 control-label\">Логин</label>
                                                    <div class=\"col-xs-7 col-md-4\">
                                                        <input type=\"text\" class=\"form-control\" readonly value=\"$login\">
                                                    </div>
                                                </div>
            
                                                <div class=\"form-group\">
                                                    <label class=\"col-xs-3 col-md-2 col-md-offset-1 control-label\">Имя</label>
                                                    <div class=\"col-xs-7 col-md-4\">
                                                        <input type=\"text\" class=\"form-control\" id=\"name\" name=\"name\" value=\"$name\">
                                                    </div>
                                                </div>
            
                                                <div class=\"form-group\">
                                                    <label class=\"col-xs-3 col-md-2 col-md-offset-1 control-label\">Email</label>
                                                    <div class=\"col-sm-7 col-md-4\">
                                                        <input type=\"text\" class=\"form-control\" id=\"email\" name=\"email\" value=\"$email\" readonly=\"readonly\">
                                                    </div>
                                                </div>
            
                                                <div class=\"col-xs-10 col-md-4 col-md-offset-3 text-center\" id=\"result_profile_edit\"></div>
            
                                                <div class=\"form-group\">
                                                    <div class=\"col-xs-10 text-center\">
                                                        <button type=\"reset\" class=\"btn btn-default\">Отменить</button>
                                                        <input type=\"hidden\" value=\"$admin_id\" name=\"admin_id\">
                                                        <input type=\"submit\" value=\"Сохранить\" class=\"btn btn-primary\">
                                                    </div>
                                                </div>
                                            </form>
										</div>		
									</div>		<!-- end admin_panel_content -->
								</div>		<!-- end admin_panel -->

							</div>		<!-- end col-xs-12 -->
						</div>		<!-- end row -->
	                    
					</div>	
	            </div>		<!-- end right_col -->		            
				
				<script src=\"/support_service/assets/js/support.profile.js\"></script>";
?>