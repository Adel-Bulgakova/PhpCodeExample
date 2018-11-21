<?php
if (filter_var($_GET["ip"], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
	if (isset($_GET["port"])) {
		$port = $_GET["port"];
	} else {
		$port = "554";
	}
	$host = $_GET["ip"];
	$waitTimeoutInSeconds = 1;
	if ($fp = fsockopen($host, $port, $errCode, $errStr, $waitTimeoutInSeconds)) {
		echo "			
			<div class=\"alert alert-success text-center\">
				
				<div class=\"port\">Устройство ONLINE</div>
				<div class=\"offOnline\">Порт открыт</div>
			</div>
		";
	} else {
		echo "
			<div class=\"alert alert-danger text-center\">
				<div class=\"port\">Устройство OFFLINE</div>
				<div class=\"offOnline\">Порт закрыт</div><hr> 
				<div id=\"err\">code $errCode err $errStr</div> 
			</div>
		";
	}
	fclose($fp);

} else {
	echo "<div class=\"alert alert-warning text-center\">
		<div>Некорректный IP-адрес</div>
	</div>";
}
?>