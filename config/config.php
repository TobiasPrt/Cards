<?php
///////////////////////////////////////////////
//	Speichername	:	config.php 		     //
//	Beschreibung	:	Konfigurationsdatei  //
//	Version			:	0.0.1 				 //
///////////////////////////////////////////////

// alle Fehlermeldungen sollen gezeigt werden
error_reporting(0);

// Session starten
session_start();

// Session automatisch löschen und Nutzer ausloggen nach 30min 
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > 1800)) {
    session_unset();
    session_destroy();
    header('location: index.php');
}
$_SESSION['LAST_ACTIVITY'] = time();


// Testing
// echo 'Session: ';
// print_r($_SESSION);
// echo "<br><br>";
// echo 'Post: ';
// print_r($_POST);
// echo "<br><br>";
// $_SESSION['login_id'] = 1;

// Dateien einbinden
require 'db.php';

// Seite prüfen und Variablen festlegen
switch ($_SERVER['SCRIPT_NAME']) {
	case '/cards/public/sites/home.php':
		$CURRENT_PAGE = 'Home';
		$TITLE = 'Home - Cards';
		break;
	case '/cards/public/sites/settings.php':
		$CURRENT_PAGE = 'Settings';
		$TITLE = 'Settings - Cards';
		break;
	case '/cards/public/sites/mail.php':
		$CURRENT_PAGE = 'Mail';
		$TITLE = 'Reset Password - Cards';
		break;
	case '/cards/public/sites/password.php':
		$CURRENT_PAGE = 'Password';
		$TITLE = 'Enter new Password - Cards';
		break;
	case '/cards/public/sites/register.php':
		$CURRENT_PAGE = 'Register';
		$TITLE = 'Create Account - Cards';
		break;
	case '/cards/public/sites/game.php':
		$CURRENT_PAGE = 'Game';
		$TITLE = 'Learn - Cards';
		break;

	default:
		$CURRENT_PAGE = 'Index';
		$TITLE = 'Welcome to Cards!';
}

/**
 * macht aus jedem Element aus einem Array einen weiteren Array
 *
 * @param $array Array, das überprüft werden soll
 * @return neuer mehrdimensionaler Array
 */
function generateArray($array) {
	foreach ($array as $key => $val) {
		if (!is_array($array[$key])) {
			$newArray[$key] = array($array[$key]);
		}		
	}
	return $newArray ?? $array;
}

?>