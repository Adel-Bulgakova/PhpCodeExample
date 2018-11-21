<?php
global $db;
echo "
	<div class=\"section content\">
		<div class=\"container\">
			<div class=\"row\">
				<div class=\"col-xs-10\">
					<h4 class=\"page_title\">" . _TOP_STREAMS . "</h4>
				</div>
			</div>
			<div class=\"row\">
				<div class=\"col-xs-10\">";
					get_top_streams("inner_page");
					echo "
				</div>
			</div>
		</div>
	</div>
	";
?>