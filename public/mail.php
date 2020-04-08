<?php
// Dateien einbinden
require '../config/config.php';

$error = generateToken() ?? '';

/**
 * Eingabe & Logindaten überprüfen
 *
 * @return id / false / individueller Fehlercode
 */
function generateToken() {
	// Submit prüfen
	if (!isset($_POST['submitbutton'])) return;
	// prüfen ob alle Felder (richtig) ausgefüllt sind und Fehler zurückgeben
	if ($_POST['mail'] == '') return 'Enter your Mail';

	$user = db_select(
		['U_ID'], 
		'User_List', 
		['u_mail', '=', $_POST['mail']]
	);
	if ($user == 'nothing found') return 'Unknown Mail';

	$token = bin2hex(random_bytes(50));

	db_insert(
		'Password_Token',
		['U_ID', 'pw_token'],
		[$user['U_ID'], $token]
	);

	// Mailinhalte deklarieren
	$to = $_POST['mail'];
	$subject = 'Reset your Password for Cards';
	$message = 'Hi, <br><br> click on this <a href="https://cards.tobiaspoertner.com/password.php?token='.$token.'">Link</a>, to reset your password. Attention: this link is only valid for 15min.<br><br> Best wishes <br><br> Cards-Service';
	$header  = "MIME-Version: 1.0\r\n";
	$header .= "Content-type: text/html; charset=utf-8\r\n";
	$header .= "From: support@cards.tobiaspoertner.com\r\n";
	// Mail online versenden 
	mail($to, $subject, $message, $header);

	return 'A mail to reset your password has been sent to you.';
}
?>

<!-- HTML -->
<!DOCTYPE html>
<html lang="de">
	<head>
		<title>Sign In</title>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
		<meta http-equiv="X-UA-Compatible" content="ie=edge">
		<link rel="stylesheet" type="text/css" href="style.css">
		
	</head>
	<body>
		<main class="content content--center">
			<form class="login" method="POST">
				<div class="login__header">Reset Password</div>
				<div class="error"><?= $error ?></div>
				<div class="loginform">
					<div>
						<span>Mail</span>
						<span><input type="email" name="mail" placeholder="example@mail.com" required></span>
					</div>
				</div>
				<div class="login__button"><button type="submit" name="submitbutton">Reset Password</button></div>
				<div class="register__button"><a href="register.php">Back to the login</a></div>
			</form>
		</main>
	</body>
</html>