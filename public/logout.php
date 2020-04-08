<?php
// notwendige Datein integrieren
require '../config/config.php';

if (!isset($_SESSION['login_id'])) {
	header('location: access_denied.php');
	exit();
}

session_destroy();
header('location: index.php');
?>