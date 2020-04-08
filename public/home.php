<?php
// Dateien einbinden
require '../config/config.php';

if (!isset($_SESSION['login_id'])) {
	header('location: access_denied.php');
	exit();
}
?>

<!-- HTML -->
<!DOCTYPE html>
<html lang="de">

	<!-- Head einfügen -->
	<?php $title = 'Home - Cards';
	include 'components/head.php'; ?>

	<body>
		<!-- Navigation -->
		<?php include 'components/navigation_lr.php'; ?>

		<!-- Menu + Skript -->
		<?php include 'components/menu.php'; ?>

		<!-- Main Content -->
		<?php include 'components/list.php'; ?>
	</body>

</html>