<?php
echo "
	<div class=\"section content\">
		<div class=\"container\">

			<div class=\"row\">
				<div class=\"col-xs-10\">
					<h4 class=\"page_title\">" . _STREAMS_MAP . "</h4>
				</div>
			</div>

			<div class=\"row\">
				<div class=\"col-xs-10\" id=\"map_canvas\" style=\"width: 100%; height: 500px; position:center;\"></div>
			</div>
			<div id=\"progress\"><div id=\"progress-bar\"></div></div>
		</div>
	</div>

	<!---<script src=\"//maps.google.com/maps/api/js?key=AIzaSyBBWEwVp8iNMmvIDkxAU9BMo79OjBoIYGg\"></script>--->
	<script src=\"//maps.googleapis.com/maps/api/js?v=3.16&sensor=falsekey=AIzaSyBBWEwVp8iNMmvIDkxAU9BMo79OjBoIYGg\"></script>
	<script src=\"/assets/lib/markerclusterer.js\"></script>
	<script src=\"https://cdnjs.cloudflare.com/ajax/libs/jquery-ui-map/3.0-rc1/jquery.ui.map.js\"></script>
	<script src=\"/assets/js/project.page.map.js\"></script>";
?>
