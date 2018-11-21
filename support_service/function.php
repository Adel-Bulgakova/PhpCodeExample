<?php
function head($page_title = "") {
echo "
<!DOCTYPE html>
<html lang=\"en\">
    <head>
        <title>PROGECT_NAME Support - $page_title</title>
        <meta charset=\"UTF-8\" />
        <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\" />
     
        <link rel=\"stylesheet\" href=\"assets/css/bootstrap.min.css\">
        <link rel=\"stylesheet\" href=\"assets/fonts/font-awesome-4.5.0/css/font-awesome.min.css\">
		<link rel=\"stylesheet\" href=\"assets/css/animate.min.css\" />
		<link rel=\"stylesheet\" href=\"assets/css/icheckGreen.css\">
		<link rel=\"stylesheet\" href=\"assets/css/template.css\" />
		<link rel=\"stylesheet\" href=\"assets/css/custom.css\">
      	
		<!--[if lt IE 9]>
			<script src=\"https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js\"></script>
			<script src=\"https://oss.maxcdn.com/respond/1.4.2/respond.min.js\"></script>
		<![endif]-->
	
		<script src=\"assets/lib/jquery-2.1.4.min.js\"></script>
		<script src=\"assets/lib/jquery.validate.min.js\"></script>
			
    </head>";
}

function admin_interface() {
    echo "
        <body class=\"nav-md\">
            <div class=\"container body\">
                <div class=\"main_container\">
                    <div class=\"col-md-3 left_col\">
                        <div class=\"left_col scroll-view\">

                            <div class=\"navbar nav_title\" style=\"border: 0;\">
                                <span class=\"site_title\"></span>
                            </div>
                            <div class=\"clearfix\"></div>

                            <div id=\"sidebar-menu\" class=\"main_menu_side hidden-print main_menu\">

                            <div class=\"menu_section\">
                                <ul class=\"nav side-menu\">
                                
                                    <li><a href=\"/support_service/\"><i class=\"fa fa-home\"></i> Активные чаты</a></li>
                                    <li><a href=\"/support_service/index.php?route=page_archive_chats\"><i class=\"fa fa-archive\"></i> Архив чатов</a></li>
                                    <li><a href=\"/support_service/index.php?route=page_messages\"><i class=\"fa fa-archive\"></i> Сообщения контактной формы</a></li>
                                    <li><a href=\"/support_service/index.php?route=page_admin_profile\"><i class=\"fa fa-user\"></i> Профиль</a></li>
                                    <li><a href=\"/support_service/index.php?route=page_test_tools\"><i class=\"fa fa-gavel\"></i> Тест устройства</a></li>
                                    <li><a href=\"/support_service/index.php?route=proc_logout\"><i class=\"fa fa-sign-out\"></i> Выход</a></li>
                                </ul>
                            </div>
                        </div>

                    </div>
                </div>

                <div class=\"top_nav\">
                    <div class=\"nav_menu\">
                        <nav class=\"\" role=\"navigation\">
                            <div class=\"nav toggle\">
                                <a id=\"menu_toggle\"><i class=\"fa fa-bars\"></i></a>
                            </div>
                            <ul class=\"nav navbar-nav navbar-right\">
                                <li><a href=\"/support_service/index.php?route=proc_logout\">Выход  <i class=\"fa fa-sign-out\"></i></a></li>
                            </ul>
                        </nav>
                    </div>
                </div>		<!-- end top_nav -->";
}

function modal_window() {
    echo "
    <!-- Modal strat-->
		<div class=\"modal fade bs-modal-lg\" tabindex=\"-1\" role=\"dialog\" aria-hidden=\"true\" id=\"confirm_modal\">
			<div class=\"modal-dialog modal-lg\">
				<div class=\"modal-content\">

					<div class=\"modal-header\">
						<button type=\"button\" class=\"close\" data-dismiss=\"modal\"><span aria-hidden=\"true\">×</span></button>
						<h4 class=\"modal-title\">Подтверждение действия</h4>
					</div>
					<div class=\"modal-body\">
						<p></p>
					</div>
					<div class=\"modal-footer\">
						<button type=\"button\" class=\"btn btn-default\" data-dismiss=\"modal\">Отменить</button>
                        <button type=\"button\" class=\"btn btn-primary\" id=\"confirm\">Подтвердить</button>
					</div>

				</div>
			</div>
		</div>
		<!-- Modal end-->
    ";
}

function footer() {
	echo "
	        </div>		<!-- end main_container -->	
	    </div>		<!-- end container body -->

	    <script src=\"assets/lib/bootstrap.min.js\"></script>
	    <script src=\"assets/lib/jquery.nicescroll.min.js\"></script>
	    <script src=\"assets/lib/jquery.dataTables.min.js\"></script>
	    <script src=\"assets/lib/icheck.min.js\"></script>
	    <script src=\"assets/js/support.template.js\"></script>

    </body>
</html>";
}

function gen_uuid($length = 0) {
    $characters = "ABCDEFGHIJKLMOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
    $characters_length = strlen($characters);
    $random_string = "";
    for ($i = 0; $i < $length; $i++) {
        $random_string .= $characters[rand(0, $characters_length - 1)];
    }
    return $random_string;
}

function prepair_str($str) {
    $str = trim($str);
    $str = preg_replace("/[^\x20-\xFF]/", "", @strval($str));
    $str = strip_tags($str);
    $str = htmlspecialchars($str, ENT_QUOTES);
    $str = mysql_real_escape_string($str);
    return $str;
}
?>