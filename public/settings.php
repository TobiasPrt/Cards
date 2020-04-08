<?php
// Dateien einbinden
require '../config/config.php';

if (!isset($_SESSION['login_id'])) {
	header('location: access_denied.php');
	exit();
}

$errorpassword = checkPassword();
$errormail = checkNewMail();
$errorproposal = checkProposal();


function checkProposal() {
	if (!isset($_POST['submitnewcard'])) return;

	$to = 'support@cards.tobiaspoertner.com';
	$subject = 'Card Proposal';
	$message = 'Deck: '.$_POST['deck'].'<br>
				Front: '.$_POST['front'].'<br>
				Back: '.$_POST['back'].'
	';
	$header  = "MIME-Version: 1.0\r\n";
	$header .= "Content-type: text/html; charset=utf-8\r\n";
	$header .= "From: support@cards.tobiaspoertner.com\r\n";
	// Mail online versenden 
	mail($to, $subject, $message, $header);
	return 'Proposal sent';
}


/**
 * Eingabe & Logindaten überprüfen
 *
 * @return id / false / individueller Fehlercode
 */
function checkPassword() {
	// Submit prüfen
	if (!isset($_POST['submitpasswordchange'])) return;
	// prüfen ob alle Felder (richtig) ausgefüllt sind und Fehler zurückgeben
	if ($_POST['password'] == '') return 'Enter your old password';
	// ID + Hash aus Datenbank
	$hash = db_select(
		['u_hash'], 
		'User_List', 
		['U_ID', '=', $_SESSION['login_id']]
	);
	// Fehler, wenn nichts gefunden wurde
	if ($hash == 'nothing found') return 'Something went wrong, please contact support@cards.tobiaspoertner.com';
	if (!password_verify($_POST['password'], $hash['u_hash'])) return 'This is not your current password';
	if ($_POST['password1'] == '') return 'Enter your new password twice';
	if ($_POST['password2'] == '') return 'Enter your new password twice';
	if ($_POST['password1'] != $_POST['password2']) return 'Passwords are not matching';
	
	


	if (password_verify($_POST['password1'], $hash['u_hash'])) return 'Do not use a password you have already used';


	db_update(
		'User_List',
		['u_hash'],
		[password_hash($_POST['password1'], PASSWORD_DEFAULT)],
		['U_ID', $_SESSION['login_id']]
	);
	return 'Your password has been changed successful';
}

/**
 * Eingabe & Logindaten überprüfen
 *
 * @return id / false / individueller Fehlercode
 */
function checkNewMail() {
	// Submit prüfen
	if (!isset($_POST['submitmailchange'])) return;
	// prüfen ob alle Felder (richtig) ausgefüllt sind und Fehler zurückgeben
	if ($_POST['password'] == '') return 'Enter your old password';
	// ID + Hash aus Datenbank
	$hash = db_select(
		['u_hash'], 
		'User_List', 
		['U_ID', '=', $_SESSION['login_id']]
	);
	// Fehler, wenn nichts gefunden wurde
	if ($hash == 'nothing found') return 'Something went wrong, please contact support@cards.tobiaspoertner.com';
	if (!password_verify($_POST['password'], $hash['u_hash'])) return 'This is not your current password';
	if ($_POST['mail1'] == '') return 'Enter your new mail twice';
	if ($_POST['mail2'] == '') return 'Enter your new mail twice';
	if ($_POST['mail1'] != $_POST['mail2']) return 'Mails are not matching';
	
	$checkmail = db_select(
		['U_ID'],
		'User_List',
		['u_mail', '=', $_POST['mail1']]
	);
	if ($checkmail != 'nothing found') return 'This mail is already used by another user';

	db_update(
		'User_List',
		['u_mail'],
		[$_POST['mail1']],
		['U_ID', $_SESSION['login_id']]
	);
	return 'Your mail has been changed successful';
}


?>

<!-- HTML -->
<!DOCTYPE html>
<html lang="de">
	<head>
		<title>Settings</title>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
		<meta http-equiv="X-UA-Compatible" content="ie=edge">
		<link rel="stylesheet" type="text/css" href="style.css">
		
	</head>
	<body>
		<nav class="nav nav--lmr">
			<div>
				<span id="nav">
					<img class="nav__backimage" src="images/Arrowright.png">
					<a id="Navlink" class="navlink" href="home.php">Home</a>
				</span>
				<span class="nav__header">Settings</span>
				<span id="Menutrigger">
					<img src="images/Circle.png">
				</span>
			</div>
		</nav>

		<!-- Menu + Skript -->
		<?php include 'components/menu.php'; ?>

		<div class="error"><?= $errormail ?? ($errorpassword ?? $errorproposal) ?></div>

		<main class="list list--settings">
			<button id="ToggleMail" class="list__item">
				<span>change mail</span>
				<span><img src="images/Arrowright.png"></span>
			</button>
			<button id="TogglePassword" class="list__item">
				<span>change password</span>
				<span><img src="images/Arrowright.png"></span>
			</button>
			<button id="ToggleCard" class="list__item">
				<span>propose card</span>
				<span><img src="images/Arrowright.png"></span>
			</button>
			<!-- <button onclick="location.href='next.php';" class="list__item">
				<span>what's next?</span>
				<span><img src="images/Arrowright.png"></span>
			</button> -->
			<button onclick="location.href='mailto:support@cards.tobiaspoertner.com';" class="list__item">
				<span>contact support</span>
				<span><img src="images/Arrowright.png"></span>
			</button>
		</main>



		<!-- Mail zurücksetzen -->
		<div id="Mail" class="mail">
			<main class="content content--center content-settings">
				<form class="login login--settings" method="POST">
					<div class="login__header">Change Mail</div>
					<div class="login__form loginform loginform--settings">
						<div>
							<span>Current Password</span>
							<span><input type="password" name="password" placeholder="required" required></span>
						</div>
						<div>
							<span>New Mail</span>
							<span><input type="email" name="mail1" placeholder="required" required></span>
						</div>
						<div>
							<span>New Mail</span>
							<span><input type="email" name="mail2" placeholder="required" required></span>
						</div>
					</div>

					<div class="login__button">
						<button type="submit" name="submitmailchange">Save</button>
					</div>
				</form>
			</main>
		</div>

		<!-- Passwort zurücksetzen -->
		<div id="Password" class="password">
			<main class="content content--center content-settings">
				<form class="login login--settings" method="POST">
					<div class="login__header">Change Password</div>
					<div class="login__form loginform loginform--settings">
						<div>
							<span>Current Password</span>
							<span><input type="password" name="password" placeholder="required" required></span>
						</div>
						<div>
							<span>New Password</span>
							<span><input type="password" name="password1" placeholder="required" required></span>
						</div>
						<div>
							<span>New Password</span>
							<span><input type="password" name="password2" placeholder="required" required></span>
						</div>
					</div>

					<div class="login__button">
						<button type="submit" name="submitpasswordchange">Save</button>
					</div>
				</form>
			</main>
		</div>

		<!-- Karte vorschlagen -->
		<div id="Card" class="newcard">
			<main class="content content--center content-settings">
				<form class="login login--settings" method="POST">
					<div class="login__header">Propose Card</div>
					<div class="login__form loginform loginform--settings">
						<div>
							<span>Deck</span>
							<span><input type="text" name="deck" placeholder="required" required></span>
						</div>
						<div>
							<span>Front</span>
							<span><textarea name="front" placeholder="required" required></textarea></span>
						</div>
						<div>
							<span>Back</span>
							<span><textarea name="back" placeholder="required" required></textarea></span>
						</div>
					</div>

					<div class="login__button">
						<button type="submit" name="submitnewcard">Propose</button>
					</div>
				</form>
			</main>
		</div>

		<!-- JavaScript Logik -->
		<script type="text/javascript">
			document.getElementById('ToggleMail').addEventListener('click', function() {
				toggleSettings('Mail')		
			})
			document.getElementById('TogglePassword').addEventListener('click', function() {
				toggleSettings('Password')
			})
			document.getElementById('ToggleCard').addEventListener('click', function() {
				toggleSettings('Card')
			})

			function toggleSettings(ContainerID) {
				document.getElementById(ContainerID).classList.toggle('show--settings');
				document.getElementById('Navlink').innerHTML = 'Settings';
				document.getElementById('Navlink').style.pointerEvents = 'none';
			}

			document.getElementById('nav').addEventListener('click', function() {
				if (document.getElementById('Mail').classList.contains('show--settings')) {
					document.getElementById('Mail').classList.toggle('show--settings');
				}
				if (document.getElementById('Password').classList.contains('show--settings')) {
					document.getElementById('Password').classList.toggle('show--settings');
				}
				if (document.getElementById('Card').classList.contains('show--settings')) {
					document.getElementById('Card').classList.toggle('show--settings');
				}
				document.getElementById('Navlink').innerHTML = 'Home';
				document.getElementById('Navlink').style.pointerEvents = '';
			})
		</script>
	</body>
</html>