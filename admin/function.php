<?php
function head($page_name = "") {
echo "
<!DOCTYPE html>
<html lang=\"en\">
    <head>
        <title>ADMIN - $page_name</title>
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
	
	        <link rel=\"stylesheet\" href=\"assets/lib/datepicker/css/bootstrap-datetimepicker.min.css\">
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
                                    <li><a href=\"/admin/\"><i class=\"fa fa-home\"></i> Главная</a></li>
                                    <li><a><i class=\"fa fa-video-camera\"></i> Трансляции<span class=\"fa fa-chevron-down\"></span></a>
                                        <ul class=\"nav child_menu\" style=\"display: none\">
                                            <li><a href=\"/admin/index.php?route=page_streams\"> Список трансляций</a></li>
                                            <li><a href=\"/admin/index.php?route=page_streams_excess\"> Список трансляций, отсутствующих на медиасервере</a></li>
                                            <li><a href=\"/admin/index.php?route=page_streams_categories\"> Управление категориями</a></li>
                                        </ul>
                                    </li>
                                    <li><a><i class=\"fa fa-user\"></i> Пользователи<span class=\"fa fa-chevron-down\"></span></a>
                                        <ul class=\"nav child_menu\" style=\"display: none\">
                                            <li><a href=\"/admin/index.php?route=page_users\">Список пользователей</a></li>
                                            <li><a href=\"/admin/index.php?route=page_official\">Официальные источники</a></li>
                                        </ul>
                                    </li>
                                    <li class=\"claims_li\"><a><i class=\"fa fa-exclamation-triangle\"></i> Жалобы и отзывы<span class=\"fa fa-chevron-down\"></span></a>
                                        <ul class=\"nav child_menu\" style=\"display: none\">
                                            <li><a href=\"/admin/index.php?route=page_claims\"> Жалобы на видео <span id=\"new_claims\" class=\"badge bg-red\"></span></a></li>
                                            <li><a href=\"/admin/index.php?route=page_claims_comments\"> Жалобы на комментарии <span id=\"new_claims_comments\" class=\"badge bg-red\"></span></a></li>
                                            <li><a href=\"/admin/index.php?route=page_feedback\"> Отзывы</a></li>
                                        </ul>
                                    </li>
                                    <li><a><i class=\"fa fa-tags fa-2x\"></i> Модерация тегов<span class=\"fa fa-chevron-down\"></span></a>
                                        <ul class=\"nav child_menu\" style=\"display: none\">
                                            <li><a href=\"/admin/index.php?route=page_streams_tags\"> Теги трансляций</a></li>
                                            <li><a href=\"/admin/index.php?route=page_profiles_tags\"> Теги профилей</a></li>
                                        </ul>
                                    </li>
                                    <li><a><i class=\"fa fa-info fa-2x\"></i> Служба поддержки<span class=\"fa fa-chevron-down\"></span></a>
                                        <ul class=\"nav child_menu\" style=\"display: none\">
                                            <li><a href=\"/admin/index.php?route=page_support_service\"> Служба поддержки</a></li>
                                        </ul>
                                    </li>
                                    <li><a><i class=\"fa fa-info fa-2x\"></i> Уведомления<span class=\"fa fa-chevron-down\"></span></a>
                                        <ul class=\"nav child_menu\" style=\"display: none\">
                                            <li><a href=\"/admin/index.php?route=page_notice\"> Уведомления</a></li>
                                        </ul>
                                    </li>
                                    <li><a><i class=\"fa fa-cc-visa fa-2x\"></i> Способы оплаты<span class=\"fa fa-chevron-down\"></span></a>
                                        <ul class=\"nav child_menu\" style=\"display: none\">
                                            <li><a href=\"/admin/index.php?route=page_payment\"> Способы оплаты</a></li>
                                        </ul>
                                    </li>
                                    <li><a><i class=\"fa fa-gavel\"></i> Тест устройства<span class=\"fa fa-chevron-down\"></span></a>
                                        <ul class=\"nav child_menu\" style=\"display: none\">
                                            <li><a href=\"/admin/index.php?route=page_test_tools\"> Тест устройства</a></li>
                                        </ul>
                                    </li>
                                     <li><a href=\"/admin/index.php?route=proc_logout\"><i class=\"fa fa-sign-out\"></i> Выход</a></li>
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
                                <li><a href=\"/admin/index.php?route=proc_logout\">Выход  <i class=\"fa fa-sign-out\"></i></a></li>
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
	    <script src=\"assets/js/admin.template.js\"></script>

	    <script>
	    //                Обновление данных о новых жалобах
//	        var check_claims = setInterval(function() { check_new_claims(); }, 5000);
	        function check_new_claims(){
                $.ajax({
                    type: 'GET',
                    dataType: 'JSON',
                    url: 'index.php?route=proc_claims_notify',
                    success: function(data){
                        $('#new_cliams').text(data.claims);
                        $('#new_cliams_comments').text(data.claims_commnets);
                    },
                    error: function(xhr, status, error){
                        console.log(error);
                    }
                });
            }

            //TODO!
            $(document).ready(function() {           
                if ($('.claims_li').hasClass('active')) {
                    console.log('active');
                    $('#new_cliams').text('0');
                    $('#new_cliams_comments').text('0');
                } else {
                    console.log('not active');
                }

            });
        </script>

    </body>
</html>";
}

#Проверяет существование пользователя в системе
function user($user_id = 0) {
    global $db;
    $result_user = $db -> sql_query("SELECT * FROM users WHERE id = '$user_id' AND is_deleted = '0'", "", "array");
    if (sizeof($result_user) > 0 AND !empty($result_user[0])){
        return true;
    } else {
        return false;
    }
}

function get_action_date($action_id = 0, $hero_id = 0, $user_id = 0){
    global $db;
    $action_date = 0;
    $result_action = $db -> sql_query("SELECT * FROM `users_actions_log` WHERE `users_actions_id` = '$action_id' AND `hero_id` = '$hero_id' AND `user_id`= '$user_id'", "", "array");
    if (sizeof($result_action) > 0 AND !empty($result_action[0])){
        $action_date = $result_action[0]["created_date"];
    }
    return $action_date;
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