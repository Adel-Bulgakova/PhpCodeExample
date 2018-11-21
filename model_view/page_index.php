<?php
global $db;
if ((isset($_GET["user_id"]) AND !empty($_GET["user_id"])) AND (isset($_GET["hash"]) AND !empty($_GET["hash"]))){
	$user_id = $_GET["user_id"];
	$hash = $_GET["hash"];
	$result_user = $db -> sql_query("SELECT * FROM `users` WHERE `id` = '$user_id' AND `hash` = '$hash' AND `email_confirm` = '0'", "", "array");
	if (sizeof($result_user) > 0){
		$result_confirm_email = $db -> sql_query("UPDATE `users` SET `email_confirm` = '1' WHERE `id` = '$user_id' AND `hash` = '$hash' AND `email_confirm` = '0'");
	}
}
$streams_data = load_streams(1);
$streams = $streams_data["data"];
$total_streams_blocks_count = $streams_data["pages"];
if (!$total_streams_blocks_count) {
	$total_streams_blocks_count = 0;
}
echo "
	<div class=\"section content\">
		<div class=\"container\">
			<div class=\"row data\">
				$streams
			</div>
		</div>
	</div>
	<script>
		var total_streams_blocks_count = $total_streams_blocks_count;
	</script>";
?>