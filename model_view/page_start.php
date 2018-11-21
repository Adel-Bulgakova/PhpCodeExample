<?php global $lang;?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<title>PROGECT_NAME</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />

		<link rel="icon" type="image/png" href="/assets/images/favicon.png">
		<link rel="stylesheet" href="/assets/lib/bootstrap-custom/css/bootstrap.min.css" />
		<link rel="stylesheet" href="/assets/fonts/font-awesome-4.5.0/css/font-awesome.min.css" />
		<link rel="stylesheet" href="/assets/lib/Magnific-Popup/magnific-popup.css"/>
		<link rel="stylesheet" href="/assets/lib/Magnific-Popup/magnific-popup-styles.css"/>
		<link rel="stylesheet" href="/assets/css/start_page.css"/>

		<!--[if lt IE 9]>
			<script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
			<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
		<![endif]-->

		<script src="/assets/js/jquery-2.1.4.min.js"></script>
		<script src="/assets/js/jquery.validate.min.js"></script>

	</head>
	<body>
		<div id="wrapper">
			<header>
				<div class="wrapper_container">
					<nav class="text-center">
						<!--<a href="#about">
						<?php echo _ABOUT_PROJECT; ?>
						</a> -->
						<a href="https://itunes.apple.com/ru/genre/ios/id36?mt=8" class="applink appstore-link" title="Download on the App Store">App Store</a>
						<a href="https://play.google.com/store?hl=ru" class="applink playstore-link" title="Download on the Play Store">Google Play Store</a>
					</nav>
				</div>
			</header>

			<div id="content">
				<div class="wrapper_container">
					<a href="/"><img src="/assets/images/files/icon.png" width="196px" alt="PROJECT_NAME"></a>

					<a href="#popup-auth" id="play" class="text-center">
						<img src="/assets/images/icon_play.png" width="150px" alt="PROJECT_NAME">
					</a>
				</div>
			</div>

			<footer>
				<div class="wrapper_container">
					<div class="app_links">
						<a class="app_link app_store_link" href="https://itunes.apple.com/ru/genre/ios/id36?mt=8" title="Download on the App Store"></a>
						<a class="app_link play_store_link"  href="https://play.google.com/store?hl=ru" title="Get it on Google Play"></a>
					</div>
				</div>
			</footer>
		</div>

		<script src="/assets/lib/bootstrap-custom/js/bootstrap.min.js"></script>
		<script src="/assets/lib/jquery.mousewheel.min.js"></script>
		<script src="/assets/lib/Magnific-Popup/jquery.magnific-popup.min.js"></script>
		<script src="/assets/lib/jquery.maskedinput/jquery.maskedinput.js"></script>
		<script src="/assets/js/project.auth.js"></script>

		<script>
			$(document).ready(function(){
				$('#play').magnificPopup({
					type: 'inline',
					preloader: false,
					modal: true,
					callbacks: {
						open: phone_mask()
					}
				});

				$('.popup-auth-close-icon').click(function () {
					$('#popup-auth').magnificPopup('close');
					$('#play').blur();
				});
			});

			var Constants = {
				REQUEST_FAILED_INFO: "<?php echo _REQUEST_FAILED_INFO; ?>",
				ENTER_PHONE_NUMBER: "<?php echo _ENTER_PHONE_NUMBER; ?>",
				ENTER_CORRECT_PHONE_NUMBER: "<?php echo _ENTER_CORRECT_PHONE_NUMBER; ?>",
				ENTER_CODE_FROM_SMS: "<?php echo _ENTER_CODE_FROM_SMS; ?>",
				NEW_CODE_SENT: "<?php echo _NEW_CODE_SENT; ?>",
				LANG: "<?php echo $lang; ?>"
			};

		</script>
    </body>
</html>