<?php
	session_start();
	error_reporting(E_ALL);
	ini_set('display_errors', '1');

	// Session security flags
	ini_set('session.cookie_httponly', 1);
	ini_set('session.use_only_cookies', 1);
	ini_set('session.cookie_secure', 1);

	//Set timezone
	date_default_timezone_set("Europe/London");
	putenv("TZ=Europe/London");

	function __autoload($class) {
		require_once 'class.'.$class.'.php';
	}

	// Setup app
	try {
		$app = new app();
	} catch (Exception $e) {
		die($e->getMessage());
	}

	$minifier = new loader($custom_css, $custom_js);

	if ($app->user->loggedIn) {
        if (defined('PAGE_PRIV') && !$app->user->{PAGE_PRIV.'_priv'}) {
	        require_once('error.php');
	    }		
    } else {
        array_push($minifier->custom_js, 'guest.js');
        array_push($minifier->custom_css, 'guest.css');

        if (!defined('PAGE_PUBLIC') || !PAGE_PUBLIC) {
	        require_once('error.php');
	    }
    }
?>