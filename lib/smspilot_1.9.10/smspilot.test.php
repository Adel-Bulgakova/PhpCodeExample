<?php
if (!empty($_POST['apikey']))
	define('SMSPILOT_APIKEY', $_POST['apikey']);

include('smspilot.php');
	
$action = (isset($_POST['action'])) ? $_POST['action'] : '';	

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>SMS Pilot - Отправка СМС</title>
<style type="text/css">
<!--
body,td,th {
	font-family: Arial, Helvetica, sans-serif;
	font-size: 13px;
}
a:link {
	color: #06C;
	text-decoration: underline;
}
a:visited {
	text-decoration: underline;
	color: #06C;
}
a:hover {
	text-decoration: none;
	color: #06C;
}
a:active {
	text-decoration: underline;
	color: #06C;
}
-->
</style>
</head>

<body>
<h1>Пример работы со шлюзом SMSPILOT.RU</h1>
<p><a href="http://www.smspilot.ru/apikey.php">http://www.smspilot.ru/apikey.php</a></p>
<h2>Отправить сообщение</h2>
<?php

if ($action == 'send' ) { // пришел текст из формы?

	$status = sms($_POST['phone'], $_POST['sms'], $_POST['from'] );
	
	if ( $status !== false ):  // сообщение отправилось? ?>
<p style="color: green">Ваше сообщение успешно отправлено, ответ сервера: <?= sms_success() ?></p>
<table border="1" style="border-collapse:collapse"><tr><th>id</th><th>phone</th><th>price</th><th>status</th></tr>
<?php foreach ( $status as $s ): ?>
<tr><td><?= $s['id'] ?></td><td><?= $s['phone'] ?></td><td><?= $s['price'] ?></td><td><?= $s['status'] ?></td></tr>
<?php endforeach; ?>
</table>
<br />
<br />
<?php else: ?>
<p style="color: red">Ошибка! <?= sms_error() ?></p>
<?php endif;
}
?>

<form action="" method="post">
<input type="hidden" name="action" value="send" />
API-ключ<br />
<input type="text" name="apikey" size="80" value="<?php echo (isset($_POST['apikey'])) ? $_POST['apikey'] : SMSPILOT_APIKEY; ?>" /> <a href="http://www.smspilot.ru/apikey.php" target="_blank">что это?</a><br />
<br />
Текст:<br />
<textarea name="sms" cols="60" rows="4"><?php echo (isset($_POST['sms'])) ? $_POST['sms'] : ''; ?></textarea><br />
<br />
Телефон:<br />
<input name="phone" type="text" value="<?php echo (isset($_POST['phone'])) ? $_POST['phone'] : ''; ?>" size="80" />
<em>можно несколько через запятую</em>
<br />
<br />
Отправитель (не обязательно):<br />
<input type="text" name="from" value="<?php echo (isset($_POST['from'])) ? $_POST['from'] : ''; ?>" /><br />
<br />

<input type="submit" value="Отправить SMS" />
</form>
<br />
<br />
<h2>Проверить статус SMS</h2>
<?php
if ( $action == 'status' ) {
	$status = sms_check( $_POST['id'] );
	if ( $status !== false ): ?>
<p style="color: green">Запрос выполнен успешно</p>
<table border="1" style="border-collapse:collapse">
	<tr><th>id</th><th>phone</th><th>price</th><th>status</th></tr>
<?php
		foreach( $status as $s): ?>
	<tr><td><?= $s['id'] ?></td><td><?= $s['phone'] ?></td><td><?= $s['price'] ?></td><td><?= $s['status'] ?></td></tr>
<?php endforeach; ?>
</table>
<br />
<br />
<?php else: ?>
<p style="color: red">Ошибка! <?= sms_error() ?></p>
<?php endif;
}
?>
<form action="" method="post">
<input type="hidden" name="action" value="status" />
<?php echo (isset($result_status)) ? $result_status : ''; ?>
API-ключ<br />
<input type="text" name="apikey" size="80" value="<?php echo (isset($_POST['apikey'])) ? $_POST['apikey'] : SMSPILOT_APIKEY; ?>" /><br />
<br />
ID:<br />
<input type="text" name="id" value="<?php echo (isset($_POST['id'])) ? $_POST['id'] : ''; ?>" /> 
<em>можно несколько через запятую</em><br />

<input type="submit" value="Проверить статус SMS" />
</form>
<br />
<br />
<h2>Проверить пользователя</h2>
<?php
if ( $action == 'info' ) { // проверка

	$info = sms_info();
	
	if ( $info !== false ):  // получаем данные о ключе ?>
<p style="color: green">Запрос выполнен успешно.</p>
<table border="1" style="border-collapse: collapse">
<?php
		foreach( $info as $k => $v): ?>
<tr><td><?= $k ?></td><td><?= $v ?></td></tr>
<?php endforeach; ?>
</table>
<br />
<br />
<?php else: ?>
<p style="color:red">Ошибка! <?= sms_error() ?></p>
<?php endif;
}
?>
<form action="" method="post">
<input type="hidden" name="action" value="info" />
<?php echo (isset($result_info)) ? $result_info : ''; ?>
API-ключ<br />
<input type="text" name="apikey" size="80" value="<?php echo (isset($_POST['apikey'])) ? $_POST['apikey'] : SMSPILOT_APIKEY; ?>" /><br />
<br />
<input type="submit" value="Проверить" />
</form>

</body>
</html>