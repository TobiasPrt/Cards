<?php
// Dateien einbinden
require '../config/config.php';

$result = checkToken();

$error = checkPassword($result) ?? '';


// Logik
/**
 * überprüft das Token und entsprechend den Zugang zur Seite
 *
 */
function checkToken() {
	// Prüfung auf Token
	if (!isset($_GET['token'])) header('location: access_denied.php');
	// ID + Zeit aus Datenbank holen
	$result = db_select(
		['U_ID', 'pw_time'],
		'Password_Token',
		['pw_token', '=', $_GET['token']]
	);
	// Prüfung auf Übereinstimmung von Token
	if ($result == 'nothing found') header('location: access_denied.php');
	// prüfen, ob seit versenden der Mai mehr als 15 Minuten vergangen sind
	$start = strtotime($result['pw_time']);
	$now = strtotime('now');
	if ($now - $start > 900) header('location: access_denied.php');

	return $result;
}


/**
 * Eingabe & Logindaten überprüfen
 *
 * @return id / false / individueller Fehlercode
 */
function checkPassword($result) {
	// Submit prüfen
	if (!isset($_POST['submitbutton'])) return;
	// prüfen ob alle Felder (richtig) ausgefüllt sind und Fehler zurückgeben
	if ($_POST['password'] == '') return 'Enter your new password twice';
	if ($_POST['password2'] == '') return 'Enter your new password twice';
	if ($_POST['password'] != $_POST['password2']) return 'Passwords are not matching';
	// ID + Hash aus Datenbank
	$hash = db_select(
		['u_hash'], 
		'User_List', 
		['U_ID', '=', $result['U_ID']]
	);
	// Fehler, wenn nichts gefunden wurde
	if ($hash == 'nothing found') return 'Something went wrong, please contact support@cards.tobiaspoertner.com';


	if (password_verify($_POST['password'], $hash['u_hash'])) return 'Do not use a password you have already used';


	db_update(
		'User_List',
		['u_hash'],
		[password_hash($_POST['password'], PASSWORD_DEFAULT)],
		['U_ID', $result['U_ID']]
	);
	return 'Your password has been reseted successful';
}
?>


<!-- HTML -->
<!DOCTYPE html>
<html lang="de">
	<head>
		<title>Set new password</title>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
		<meta http-equiv="X-UA-Compatible" content="ie=edge">
		<link rel="stylesheet" type="text/css" href="style.css">
		
	</head>
	<body>
		<main class="loginmain">
			<form class="loginform" method="POST">
				<div class="header">Set new password</div>
				<div class="error"><?= $error ?></div>
				<div class="login">
					<div>
						<span>Password</span>
						<span><input type="password" name="password" placeholder="required" required></span>
					</div>
					<div>
						<span>Repeat Password</span>
						<span><input type="password" name="password2" placeholder="required" required></span>
					</div>
				</div>
				<div class="login__button"><button type="submit" name="submitbutton">Change Password</button></div>
				<div class="register-button"><a href="index.php">to the login</a></div>
			</form>
		</main>
	</body>
</html>