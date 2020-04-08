<?php
// Dateien einbinden
require '../config/config.php';

// Zugangsprüfung
if (!isset($_SESSION['login_id']) || !isset($_GET['deck'])) {
	header('location: access_denied.php');
	exit();
}

// Kürzel laden
$deck_kuerzel = db_select(
	['d_kuerzel'],
	'Deck_List',
	['D_ID', '=', $_GET['deck']]
);
$kuerzel_cap = $deck_kuerzel['d_kuerzel'];
$kuerzel_low = strtolower($kuerzel_cap);

// Deck laden
$deck = db_select(
	[$kuerzel_cap.'_ID'],
	$kuerzel_cap.'_Deck'
);

// Submit verarbeiten
if (isset($_POST['submit'])) {
	$oldcard = db_select(
		['Stat_ID', 'C_ID', $kuerzel_low.'s_level'],
		$kuerzel_cap.'_Stats',
		['U_ID', '=', $_SESSION['login_id']]
	);
	$oldstat = array();
	for ($i=0; $i < sizeof($oldcard['C_ID']); $i++) { 
		$oldstat[$i] = array('Stat_ID' => $oldcard['Stat_ID'][$i],'C_ID' => $oldcard['C_ID'][$i], $kuerzel_low.'s_level' => $oldcard[$kuerzel_low.'s_level'][$i]);
	}
	
	$cardid = $_SESSION['card'][$kuerzel_cap.'_ID'];
	$oldstat = array_filter($oldstat, function($var) use ($cardid) {
		return ($var['C_ID'] == $cardid);
	});
	$oldstat = array_shift($oldstat);
	if ($_POST['submit'] == 'wrong') {
		$effect = 1;
		if ($oldstat[$kuerzel_low.'s_level'] == 5) {
			$effect = 0;
		}
	}
	if ($_POST['submit'] == 'nearly') {
		$effect = 0;
		if ($oldstat[$kuerzel_low.'s_level'] == 0) {
			$effect = 1;
		}
	}
	if ($_POST['submit'] == 'right') {
		$effect = -1;
		if ($oldstat[$kuerzel_low.'s_level'] == 0) {
			$effect = 0;
		}
	}
	$newLevel = $oldstat[$kuerzel_low.'s_level'] + $effect;
	db_update(
		$kuerzel_cap.'_Stats',
		[$kuerzel_low.'s_level'],
		[$newLevel],
		['Stat_ID', $oldstat['Stat_ID']]
	);
}

// Stats laden
$stats = db_select(
	['C_ID'],
	$kuerzel_cap.'_Stats',
	['U_ID', '=', $_SESSION['login_id']]
);
if ($stats == 'nothing found') {
	// Deck & Stats vergleichen, ggf. nachfüllen
	for ($i=0; $i < sizeof($deck[$kuerzel_cap.'_ID']); $i++) { 
		db_insert(
			$kuerzel_cap.'_Stats',
			['U_ID', 'C_ID'],
			[$_SESSION['login_id'], $deck[$kuerzel_cap.'_ID'][$i]]
		);
	}
	$stats = db_select(
		['C_ID'],
		$kuerzel_cap.'_Stats',
		['U_ID', '=', $_SESSION['login_id']]
	);
}

// Deck & Stats vergleichen, ggf. nachfüllen
$diff = array_diff($deck[$kuerzel_cap.'_ID'], $stats['C_ID']);
sort($diff);
for ($i=0; $i < sizeof($diff); $i++) { 
	db_insert(
		$kuerzel_cap.'_Stats',
		['U_ID', 'C_ID'],
		[$_SESSION['login_id'], $diff[$i]]
	);
}

// Karten laden & korrekt formatieren, da noch nicht die neue Version der database_library included ist
$cards = db_select(
	['C_ID', $kuerzel_low.'s_level'],
	$kuerzel_cap.'_Stats',
	['U_ID', '=', $_SESSION['login_id']]
);
$keys = array_keys($cards);
for ($i=0; $i < sizeof($cards[$keys[0]]); $i++) {
	$array[$i] = array_column($cards, $i);
	
}
$cards = array();
$array = generateArray($array);
for ($i=0; $i < sizeof($array); $i++) { 
	$cards[$i] = array_fill_keys($keys, $array[$i]);
	for ($j=0; $j < sizeof($keys); $j++) { 
		$cards[$i][$keys[$j]] = $cards[$i][$keys[$j]][$j];
	}
}

// Fortschritt berechnen
$fortschritt = 0.00001;
$full = sizeof($cards) * 5;
$left = 0;
for ($i=0; $i < sizeof($cards); $i++) { 
	$left += intval($cards[$i][$kuerzel_low.'s_level']);
}
$decimal = 0;
settype($decimal, 'double');
$decimal = 1 - ($left / $full);
settype($fortschritt, 'double');
$fortschritt = round($decimal * 100);
if ($fortschritt <= 70) {
	$red = 100;
	$green = $fortschritt / 0.7;
}

if ($fortschritt > 70) {
	$red = 3.33 * (100 - $fortschritt);
	$green = 100;
}

// Farbe anhand des Status definieren

// Karten gewichten & mischen
$gamecards = array();
$cards = generateArray($cards);
for ($i=0; $i < sizeof($cards); $i++) {

	$j = 0;
	do {
		$gamecards[] = $cards[$i];
		$j++;
	} while ($j < ($cards[$i][$kuerzel_low.'s_level'] * 2));
}
shuffle($gamecards);

// zufällige Karte auswählen & in Session speichern
$card = db_select(
	[$kuerzel_cap.'_ID', $kuerzel_low.'_cat', $kuerzel_low.'_front', $kuerzel_low.'_back'],
	$kuerzel_cap.'_Deck',
	[$kuerzel_cap.'_ID', '=', $gamecards[0]['C_ID']]
);
while ($_SESSION['card'][$kuerzel_cap.'_ID'] == $card[$kuerzel_cap.'_ID']) {
	shuffle($gamecards);
	$card = db_select(
		[$kuerzel_cap.'_ID', $kuerzel_low.'_cat', $kuerzel_low.'_front', $kuerzel_low.'_back'],
		$kuerzel_cap.'_Deck',
		[$kuerzel_cap.'_ID', '=', $gamecards[0]['C_ID']]
	);
}
$_SESSION['card'] = $card;
?>

<!-- HTML -->
<!DOCTYPE html>
<html lang="de">

	<!-- Head einfügen -->
	<?php include 'components/head.php'; ?>

	<body>
		<nav class="nav nav--lmr">
			<div>
				<span id="nav">
					<img class="nav__backimage" src="images/Arrowright.png">
					<a id="Navlink" class="navlink" href="home.php">Home</a>
				</span>
				<span class="nav__header">Learn</span>
				<span id="Menutrigger">
					<img src="images/Circle.png">
				</span>
			</div>
		</nav>

		<!-- Menu + Skript -->
		<?php include 'components/menu.php'; ?>
		<div class="content content--center content--game">
			<div class="fortschritt">
				<style type="text/css">
					.fortschritt div {
						display: flex;
						justify-content: flex-start;
						align-items: center;
						height: 30px;
						border-radius: 5px;
						min-width: 2px;
						font-family: var(--Light);
						font-size: 0.9rem;
						animation: 0.5s ease-out 1 progressBar;
					}
					@keyframes progressBar {
						0% {
							width: 0;
							background-color: rgba(100%, 0%, 0%, 0.6);
						}
						<?php 
						if ($fortschritt > 70) {
							echo '
								'.($fortschritt / 100 * 100 - ($fortschritt-70)).'% {
									background-color: rgba(100%, 100%, 0%, 0.6);
								}
							';
						}


						?>
						
						100% {
							width: <?= 1*$fortschritt ?>%;
							background-color: rgba(<?= $red ?>%, <?= $green ?>%, 0%, 0.6);
						}
					}
				</style>
				<div style="width: <?= 1*$fortschritt ?>%; background-color: rgba(<?= $red ?>%, <?= $green ?>%, 0%, 0.6);"><?= $fortschritt ?>%</div>
			</div>
			<div class="flipCard"> 
			  <div class="card" onclick="this.classList.toggle('flipped');"> 
			    <div class="side front">
			    	<?= $card[$kuerzel_low.'_front'] ?>
			    </div> 
			    <div class="side back">
			    	<?= $card[$kuerzel_low.'_back'] ?>
			    </div> 
			  </div> 
			</div>
			<form method="POST" class="gameform">
				<button type="submit" name="submit" value="wrong" class="gamebutton gamebutton--wrong">wrong</button>
				<button type="submit" name="submit" value="nearly" class="gamebutton gamebutton--nearly">nearly</button>
				<button type="submit" name="submit" value="right" class="gamebutton gamebutton--right">got it</button>
			</form>
		</div>	
	</body>

</html>