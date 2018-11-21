<?php
session_start();
error_reporting(E_ALL);
?>

<!DOCTYPE html>
<html lang=\"en\">
    <head>
        <title>PROGECT_NAME api login</title>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />

        <link rel="stylesheet" href="/admin/assets/css/bootstrap.min.css">
        <link rel="stylesheet" href="/admin/assets/fonts/font-awesome-4.5.0/css/font-awesome.min.css">
		<link rel="stylesheet" href="/admin/assets/css/animate.min.css" />
		<link rel="stylesheet" href="/admin/assets/css/icheckGreen.css">
		<link rel="stylesheet" href="/admin/assets/css/template.css" />
		<link rel="stylesheet" href="/admin/assets/css/custom.css">

		<!--[if lt IE 9]>
			<script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
			<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
		<![endif]-->

		<script src="/admin/assets/lib/jquery-2.1.4.min.js"></script>
		<script src="/admin/assets/lib/jquery.validate.min.js"></script>

    </head>
    <body style="background:#F7F7F7;">
        <div id="wrapper">
            <div id="login" class="animate form">
                <section class="login_content">

                    <?php
                        if (($_SESSION["api_user"] == "api_user")) :
                    ?>

                        <form>
                            <h1>Доступные версии</h1>
                            <a href="/api/desc/v1" class="btn btn-primary">Версия 1</a>
                        </form>

                    <?php
                        else :
                    ?>

                        <form id="loginform" method="post">
                            <h1>Вход</h1>
                            <p>Введите имя пользователя и пароль</p>
                            <div>
                                <input type="text" class="form-control" id="username" name="login" placeholder="Логин"/>
                            </div>
                            <div>
                                <input type="password" class="form-control" id="password" name="password" placeholder="Пароль"/>
                            </div>
                            <div id="result_login"></div>
                            <div class="clearfix text-center">
                                <input type="submit" class="btn btn-default submit" value="Войти" />
                            </div>
                        </form>

                            <script>
                                $(document).ready(function(){
                                    $('#loginform').validate({
                                        submitHandler: function(form) {
                                            $('button[type=submit]').attr('disabled', 'true');
                                            $.ajax({
                                                type: 'POST',
                                                dataType: 'json',
                                                url: 'proc_login.php',
                                                data:  $('#loginform').serialize(),
                                                success: function(data) {
                                                    if (data.status == "ERROR") {
                                                        $('#result_login').html('<div class="alert alert-danger" role="alert">' + data.message + '</div>');
                                                    } else {
                                                        window.location = "/api/desc/";
                                                    }
                                                }
                                            });
                                        }
                                    });
                                });
                            </script>


                        <?php endif; ?>
                </section>
            </div>
        </div>
    </body>
</html>
