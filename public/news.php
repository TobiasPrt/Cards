<?php
// Dateien einbinden
require '../config/config.php';

// Zugangsprüfung
if (!isset($_SESSION['login_id'])) {
	header('location: access_denied.php');
	exit();
}
?>

<!-- HTML -->
<!DOCTYPE html>
<html lang="de">

	<!-- Head einfügen -->
	<?php include 'components/head.php'; ?>

	<body>
		<nav class="nav nav--lmr">
			<div>
				<span id="nav" onclick="location.href='home.php';">
					<img class="nav__backimage" src="images/Arrowright.png">
					<a id="Navlink" class="navlink" href="home.php">Home</a>
				</span>
				<span class="nav__header">News</span>
				<span id="Menutrigger">
					<img src="images/Circle.png">
				</span>
			</div>
		</nav>

		<!-- Menu + Skript -->
		<?php include 'components/menu.php'; ?>
		<div class="content content--center content--news">
			<div>
				<article class="upcoming">
					<h3>upcoming</h3>
					<ul>
						<li>area for voting next features</li>
						<li>new Deck: "Management und Unternehmensführung"</li>
						<li>list with all cards</li>
						<li>categories for "Medienrezeptions- und Wirkungsforschung"</li>
					</ul>
				</article>
				<article>
					<h2><span>v.0.2.1</span><span>04.01.2020</span></h2>

					<ul>
						<li>23 new cards for "Medienrezeptions- und Wirkungsforschung" from the following categories: Persuasion, Meinungsführer, Schweigespirale</li>
					</ul>
				</article>
				<article>
					<h2><span>v.0.2.0</span><span>29.12.2019</span></h2>

					<ul>
						<li>this News section</li>
						<li>colored & animated progress bar while learning</li>
						<li>fixed issue where the same card appeared several times in a row</li>
						<li>7 new cards for "Medienrezeptions- und Wirkungsforschung"</li>
						<li>some small tweaks here and there</li>
					</ul>
				</article>
			</div>
			
		</div>	
	</body>

</html>