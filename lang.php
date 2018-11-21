<?php
	if (isset($_GET["lang"])) {
		$lang = $_GET["lang"];
		$_SESSION["langs"] = $lang;
	}

if (isset($_SESSION["langs"])) {
	switch ($_SESSION["langs"]) {
		case "ru" :
			$lang = "ru";
			require_once "lang/ru.php";
			break;
		case "en" :
			$lang = "en";
			require_once "lang/en.php";
			break;
		default :
			$lang = "ru";
			require_once "lang/ru.php";
			break;
	}

} else {
	if (isset($_SERVER["HTTP_ACCEPT_LANGUAGE"])){
		$lang = substr($_SERVER["HTTP_ACCEPT_LANGUAGE"], 0, 2);
	} else {
		$lang = "ru";
	}
		switch ($lang) {
		case "ru" :
			$lang = "ru";
			include "lang/ru.php";
			break;
		case "en" :
			$lang = "en";
			include "lang/en.php";
			break;
		default :
			$lang = "ru";
			include "lang/ru.php";
			break;
	}
}