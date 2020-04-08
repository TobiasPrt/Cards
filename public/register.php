<?php
// Dateien einbinden
require '../config/config.php';

$error = checkRegister() ?? '';

/**
 * Eingabe & Logindaten überprüfen
 *
 * @return id / false / individueller Fehlercode
 */
function checkRegister() {
	// Submit prüfen
	if (!isset($_POST['submitbutton'])) return;
	// prüfen ob alle Felder (richtig) ausgefüllt sind und Fehler zurückgeben
	if ($_POST['mail'] == '') return 'Enter your mail';
	if ($_POST['password'] == '') return 'Enter your password';
	if ($_POST['password'] != $_POST['password2']) return 'Passwords are not matching';
	// ID + Hash aus Datenbank
	$check = db_select(
		['U_ID'], 
		'User_List', 
		['u_mail', '=', $_POST['mail']]
	);
	// Fehler, wenn Mail gefunden wurde
	if ($check != 'nothing found') return 'Mail already registered';

	db_insert(
		'User_List',
		['u_mail', 'u_hash'],
		[$_POST['mail'], password_hash($_POST['password'], PASSWORD_DEFAULT)]
	);
	$user = db_select(
		['U_ID'], 
		'User_List', 
		['u_mail', '=', $_POST['mail']]
	);
	if ($user == 'nothing found') return 'Something went wrong, please contact support@cards.tobiaspoertner.com';

	// Willkommensmail abschicken
	// Mailinhalte deklarieren
	$to = $_POST['mail'];
	$subject = 'Welcome to Cards';
	$message = 'Hi, <br><br> thank you for joining Cards. It is not only the best, but also only way to learn "Medienrezeptions- und Wirkungsforschung" with community driven flash cards online. Click <a href="https://cards.tobiaspoertner.com/">here</a> to log in. <br> If you have any wishes for future versions or if something is not working out as expected, please let me know. You can either directly answer this E-Mail or if we happen to know each other contact me elsewhere.<br><br> Good Luck for the exams <br><br> Tobias <br><br><br> PS: There is also a deck for "Management- und Unternehmensführung" planned. Hit me up should you already have some flash cards prepared.';
	$header  = "MIME-Version: 1.0\r\n";
	$header .= "Content-type: text/html; charset=utf-8\r\n";
	$header .= "From: support@cards.tobiaspoertner.com\r\n";
	$header .= "Reply-To: support@cards.tobiaspoertner.com\r\n";
	$header .= "Return-Path: support@cards.tobiaspoertner.com\r\n";
	$header .= "Organization: Cards\r\n";
	$header .= "X-Priority: 3\r\n";
  	$header .= "X-Mailer: PHP". phpversion() ."\r\n" ;
	// Mail online versenden 
	mail($to, $subject, $message, $header);

	// ID in Session speichern + weiterleiten
	$_SESSION['login_id'] = $user['U_ID'];
	header('location: home.php');
}
?>


<!-- HTML -->
<!DOCTYPE html>
<html lang="de">
	<head>
		<title>Register</title>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
		<meta http-equiv="X-UA-Compatible" content="ie=edge">
		<link rel="stylesheet" type="text/css" href="style.css">
		
	</head>
	<body>
		<main class="content content--center">
			<form class="login" method="POST">
				<div class="login__header">Create Account</div>
				<div class="login__error"><?= $error ?></div>
				<div class="login__form loginform">
					<div class="loginform__input">
						<span>Mail</span>
						<span><input type="email" name="mail" placeholder="example@mail.com" required value="<?= $_POST['mail'] ?? '' ?>"></span>
					</div>
					<div class="loginform__input">
						<span>Password</span>
						<span><input type="password" name="password" placeholder="required" required></span>
					</div>
					<div class="loginform__input">
						<span>Repeat Password</span>
						<span><input type="password" name="password2" placeholder="required" required></span>
					</div>
				</div>
				<div class="login__button"><button type="submit" name="submitbutton">Sign Up ...</button></div>
				<div class="register__button"><a href="mail.php">Forgot Password?</a></div>
			</form>
		</main>
	</body>
</html>