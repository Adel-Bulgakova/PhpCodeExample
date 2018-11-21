<?
if (filter_var($_GET["ip"], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
	$host = $_GET["ip"];
	exec("ping $host -c 3 2>&1", $output, $retval);
	if ($retval == 0) {
		echo "
			<div class=\"alert alert-success text-center\">
				<div>Устройство ONLINE</div>";
		foreach ($output as $v) {
			echo "$v<br>";
		}
		echo "</div>";
	} else {
		echo "
			<div class=\"alert alert-danger text-center\">
				<div>Устройство OFFLINE</div>
				<div>Хост недоступен</div>
			</div>";
	}
} else {
	echo "<div class=\"alert alert-warning text-center\">
			<div>Некорректный IP-адрес</div>
		</div>";
}
?>