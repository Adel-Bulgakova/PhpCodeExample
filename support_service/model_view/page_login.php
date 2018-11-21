<?php
echo "
	<body style=\"background:#F7F7F7;\">
		<div id=\"wrapper\">
            <div id=\"login\" class=\"animate form\">
            	<section class=\"login_content\">                    
	                <form id=\"loginform\" method=\"post\"> 
	                    <h1>Вход</h1>
	                    <p>Введите имя пользователя и пароль</p>
	                    <div>
	                        <input type=\"text\" class=\"form-control\" id=\"username\" name=\"login\" placeholder=\"Логин\"/>
	                    </div>
	                    <div>
	                        <input type=\"password\" class=\"form-control\" id=\"password\" name=\"password\" placeholder=\"Пароль\"/>
	                    </div>
	                    <div id=\"result_login\"></div>
	                    <div class=\"clearfix text-center\">
	                        <input type=\"submit\" class=\"btn btn-default submit\" value=\"Войти\" />
	                    </div>
	                </form>
                </section> 
            </div>
		</div>
		<script src=\"/support_service/assets/js/support.login.js\"></script>
	</body>
</html>";
?>