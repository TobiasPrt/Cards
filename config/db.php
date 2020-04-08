<?php
// some comment here
////////////////////////////////////////////////////////////
//	Speichername	:	db.php 		  	     			  //
//	Beschreibung	:	enthält Funktionen für Datenbank  //
//	Version			:	1.1.2 				 			  //
////////////////////////////////////////////////////////////

/**
 * Verbindung zur Datenbank
 *
 * @return false / Datenbankobjekt
 */
function db_connect() {
	// statisch, damit man nicht mehrmals verbindet
	static $connection;

	// Verbinden, wenn noch nicht passiert
	if (!isset($connection)) {
		// mit Datenbank verbinden
		$connection = mysqli_connect(
			'******',
			'******',
			'******',
			'******'
		);

		// Zeichensatz definieren
		mysqli_set_charset($connection, 'utf-8');
	}

	// Fehlerbehandlung
	if ($connection === false) {
		// Fehlermeldung ausgeben
		return mysqli_connect_error();
	}

	// bei Erfolg Objekt als return value zurückgeben
	return $connection;
}

/**
 * Befehle an die Datenbank senden
 *
 * @param $result prepared statement mit bereits binded values
 * @return mysqli objekt inkl fehler
 */
function db_execute($result) {
	$connection = db_connect();
	// Befehl ausühren
	$result->execute();
	// Rückgabewert
	return $result;
}

/**
 * Select Funktion als Shorthand
 *
 * @param $selector Spalte/* die ausgewählt werden soll, kann auch array mit mehreren sein
 * @param $table Relation aus der ausgewählt werden soll
 * @param $condition array(column, operator, value)
 * @return false / assoziativer array vom Befehl
 */
function db_select($selector, $table, $condition = '') {
	$connection = db_connect();
	// in array umwandeln, falls es ein string ist
	if (is_string($selector)) {
		$selector = array($selector);
	}
	// Array für Ergebnis außerhalb von for-Schleife deklarieren, damit er nicht überschrieben wird
	$result_array = array();
	// jedes array-Element in $selector durchlaufen
	for ($i = 0; $i < sizeof($selector); $i++) {
		if ($condition != '') {
			// für jeden selektor einen einzelnen Query vorbereiten
			$stmt = $connection->prepare("SELECT $selector[$i] FROM $table WHERE $condition[0] $condition[1] ?");
			// parameter als String binden
			$stmt->bind_param("s", strval($condition[2]));
		} else {
			// condition weglassen, wenn nicht als parameter gegeben
			$stmt = $connection->prepare("SELECT $selector[$i] FROM $table");
		}
		// ausführen mit db_execute Funktion
		$stmt = db_execute($stmt);
		// Ergebnis aus mysqli Objekt extrahieren
		$result = $stmt->get_result();
		// prüfen ob Ergebnis vorhanden ist
		if (!($result->num_rows === 0)) {
			// falls mehrere fälle zu treffen sollten für eine Bedingung von einem selektor, mehrere in ein Array schreiben
			while ($row = $result->fetch_assoc()) {
				$result_array[$selector[$i]][] = $row[$selector[$i]];
			}
		}
	}
	// string zurückgeben wenn nichts gefunden wurde
	if (sizeof($result_array) === 0) {
		return "nothing found";
	}
	if (sizeof($result_array[$selector[0]]) == 1) {
		$result_array = array_map('join', $result_array);
	}
	// Rückgabewert
	return $result_array;
}

/**
 * Update Funktion als shorthand
 *
 * @param $table Relation in der geändert wird
 * @param $columns Spalten in denen Änderungen passieren sollen, string oder array
 * @param $values neuer Value, array oder string
 * @param $condition Bedingung für Ort an dem geändert wird, array oder string
 * @return Ergebnis von mysqli_query
 */
function db_update($table, $columns, $values, $condition) {
	$connection = db_connect();
	// string zu array konvertieren, wenn string mitgegeben wurde,
	// bei nur einer Spalte die geändert werden soll
	if (is_string($columns)) {
		$columns = array($columns);
		$values = array($values);
	}
	// jedes array element durchlaufen
	for ($i = 0; $i < sizeof($columns); $i++) {
		// statement preparen, zum schutz vor sql-injection
		$result = $connection->prepare(
			"UPDATE $table SET $columns[$i] = ? WHERE $condition[0] = ?"
		);
		// values binden, und typ definieren
		$result->bind_param("si", $values[$i], $condition[1]);
		// ergebnis weitergeben an
		$result = db_execute($result);
		$ergebnis[] = db_error($result);
	}
	// mysqli Objekt mit oder ohne Fehler zurückgeben
	return $ergebnis;
}

/**
 * Insert Funktion als shorthand
 *
 * @param $table Relation in die etwas eingefügt werden soll
 * @param $columns spalten definieren, in die eingefügt werden soll
 * @param $values values die eingefügt werden sollen
 * @return Ergebnis von mysqli_query
 */
function db_insert($table, $columns, $values) {
	$connection = db_connect();
	// string zu array konvertieren, damit data type search geht
	if (is_string($columns)) {
		$columns = array($columns);
		$values = array($values);
	}
	// data type des zu ändernden Feldes aus db extrahieren, damit binding der paramenter geht
	if ($result = $connection->query("SELECT * FROM $table")) {
		// Get field information for all columns
		while ($column_info = $result->fetch_field()) {
			$rows[$column_info->name]['name'] = $column_info->name;
			$rows[$column_info->name]['type'] = $column_info->type;
		}
	}
	// überprüft die Datentypen von jeder Spalte die im gegebenen Array ist
	// Die Funktion gibt ints zurück, die einen datentypen repräsentieren
	// z.B. 3 = int, 254 = char, 253 = varchar, 5 = double usw.
	// bind_param kennt aber nut int, double, string und blob
	// deshalb ist es angeraten alles was kein int oder double ist als string anzugeben
	$datatypes = '';
	for ($i = 0; $i < sizeof($columns); $i++) {
		switch ($rows[$columns[$i]]['type']) {
		case 1:
		case 2:
		case 3:
		case 4:
		case 9:
			$datatypes .= "i";
			break;
		case 5:
			$datatypes .= "d";
			break;
		case 253:
		case 254:
			$datatypes .= "s";
			break;
		default:
			$datatypes .= "s";
			break;
		}
	}

	// array zu string konvertieren und elemente mit komma trennen
	$columns = "(" . implode(", ", $columns) . ")";
	// ? String für prepare Funktion generieren anhand der länge an zu setzenden values
	// die prepare und bind_param unterstützen beide keine arrays
	$string = "(";
	$string .= str_repeat('?,', sizeof($values));
	$string[strlen($string) - 1] = ")";
	// statement preparen wegen sql-injection
	$result = $connection->prepare(
		"INSERT INTO $table $columns VALUES $string"
	);
	// values binden, und typ definieren
	// ...$values bedeutet, das er so lange extra parameter aus $values zieht bis der array leer ist
	$result->bind_param($datatypes, ...$values);
	// ergebnis weitergeben an
	$result = db_execute($result);
	$ergebnis[] = db_error($result);
	// mysqli Objekt mit oder ohne Fehler zurückgeben
	return $ergebnis;
}

/**
 * Errorbehandlung Funktion
 *
 * @param $result mysqli object zur Untersuchung
 * @return Array, der sowohl das mysqli Objekt enthält, als auch Fehlermeldungen
 */
function db_error($result) {
	$ergebnis['error']['mysqli_objekt'] = $result;
	if ($result->affected_rows == 0) {
		$ergebnis['error']['Fehlerliste'][] = "keine Änderung erfolgt";
	}
	if (!isset($ergebnis['error']['Fehlerliste'])) {
		$ergebnis['error']['Fehlerliste'] = 'success';
	}
	return $ergebnis;
}

?>