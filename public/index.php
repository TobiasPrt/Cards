<?php
// Dateien einbinden
require '../config/config.php';

$error = checkLogin() ?? '';

/**
 * Eingabe & Logindaten überprüfen
 *
 * @return id / false / individueller Fehlercode
 */
function checkLogin() {
	// Submit prüfen
	if (!isset($_POST['submitbutton'])) return;
	// prüfen ob alle Felder (richtig) ausgefüllt sind und Fehler zurückgeben
	if ($_POST['mail'] == '') return 'Mail ausfüllen';
	if ($_POST['password'] == '') return 'Passwort ausfüllen';
	// ID + Hash aus Datenbank
	$hash = db_select(
		['U_ID', 'u_hash'], 
		'User_List', 
		['u_mail', '=', $_POST['mail']]
	);
	// Fehler, wenn Mail nicht gefunden wurde
	if ($hash == 'nothing found') return 'Mail wurde nicht gefunden';
	// prüft ob Passwort mit Hash übereinstimmt
	if (!password_verify($_POST['password'], $hash['u_hash'])) return 'Passwort falsch';
	// ID in Session speichern + weiterleiten
	$_SESSION['login_id'] = $hash['U_ID'];
	header('location: home.php');
}
?>

<!-- HTML -->
<!DOCTYPE html>
<html lang="de">

	<!-- Head einfügen -->
	<?php $title = 'Login - Cards';
	include 'components/head.php'; ?>

	<body>
		<main class="content content--center">
			<form class="login" method="POST">
				<div class="login__logo">
					<img src="images/CardsLogo.png">
				</div>
				<div class="login__header">Cards v0.2.1</div>
				<div class="login__error"><?= $error ?></div>

				<div class="login__form loginform">
					<div class="loginform__input">
						<span>Mail</span>
						<span><input type="email" name="mail" placeholder="example@mail.com" value="<?= $_POST['mail'] ?? '' ?>" required></span>
					</div>
					<div class="loginform__input">
						<span>Password</span>
						<span><input type="password" name="password" placeholder="required" required></span>
					</div>
				</div>

				<div class="login__button">
					<button type="submit" name="submitbutton">Sign In ...</button>
				</div>
				
				<div class="register__button">
					<a href="register.php">Sign up or forgot password</a>
				</div>
			</form>
		</main>
	</body>
</html>