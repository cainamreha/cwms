<?php
namespace Concise;


########################################################################
###  Konfigurationsskript für individuelle Formularfelddefinitionen  ###
########################################################################

// Als Datei config.autoForm.xyz.php speichern, um eine db-Tabelle form_xyz als Formularbasis zu verwenden

/*
Erlaubte Werte:

Felder:
	type		-> Ersetzt den Auto-Typ (hidden, default, select, multiple, checkbox, radio, email, url, date, password, file)
	label		-> Ersetzt den DB-Feldnamen beim Label
	value		-> Gibt einen voreingestellten Wert mit
	options		-> Definiert die Optionen (Array) bei Typ select, multiple, checkbox und radio
	notice		-> Feldspezifische Fehlermeldung
	filetypes	-> Falls type = file, kann hier ein Array mit erlaubten Filetypes angegeben werden
	filesize	-> Falls type = file, kann hier die maximale Dateigröße in Byte (z.B. 5242880 = 5MB) angegeben werden
	filefolder	-> Falls type = file, kann hier ein Uploadordner angegeben werden
	fileprefix	-> Falls type = file, kann hier ein Präfix für den Dateinamen angegeben werden
	filereplace	-> Falls type = file, kann hier festgelegt werden ob ggf. vohandene Dateien ohne Meldung überschrieben werden sollen
	usemail		-> Falls type = email, wird die E-Mailadresse aus diesem Feld für den Formmailer verwendet
	showpass	-> Falls type = password, falls true wird das Passwort unverschlüsselt (!) für den Formmailer verwendet, sonst nur Punkte
	link		-> Verlinkt ein Feld mit vorherigem (Array mit Feldnamen des vorherigen Feldes und erwartetem Wert des vorherigen Feldes;
				   Wert = true => Feldinhalte müssen übereinstimmen (e.g. password), false dürfen nicht übereinstimmen)
	header		-> Gibt eine Überschrift für Untergruppen mit
	pagebreak	-> Definiert eine Seite/Schritt für die Verteilung der Felder auf mehrere Seiten

cf_usernotice:
	error		-> Meldung bei Erfolg
	success		-> Meldung bei Fehler (keine Spreicherung)
	errorfield	-> Generelle Fehlermeldung bei Feldern

cf_addfields:
	table		-> DB-Tabelle, aus der Felder, die mit Benutzerdaten verknüpft sind, für FormMailer übernommen werden sollen (keine Speicherung in DB!)
	fields		-> Array mit Feldern aus der Tabelle, die übernommen werden sollen
	labels		-> Array mit Labels für Felder, die übernommen werden sollen
	position	-> Stelle des Formulars, an der die Felder hinzugefügt werden sollen ("top","bottom","Feldname");
				   bei Feldname werden die Felder nach diesem Feld eingefügt

cf_captcha:
	true/false	-> Fügt ein Captcha vor dem (final-)Submitbutton ein

*/


// Benutzernamen auslesen (da hier ForeignKey)
if(isset($GLOBALS['_SESSION']['username']))
	$username = $GLOBALS['_SESSION']['username'];
else
	$username = "";

// Array mit Definitionen für bestimmte Felder
$configArray = array("username"		=> array("type"		=> "hidden",
											 "value"	=> $username,
					 						 "header"	=> "Schritt 1"
											 ),
					 #"weight"		=> array("type"		=> "radio",
						#					 "options"	=> array("one","two","tree")
											#),
					 "login"		=> array(#"type"	=> "radio",
											 #"options"	=> array("one","two","tree"),
											 "label"	=> "former login",
											 #"notice"	=> "please fill",
					 						 #"pagebreak"	=> true,
					 						 "header"	=> "Schritt 2"
											 ),
					 "leer"			=> array("type"		=> "email",
											 "value"	=> "eeeeee",
											 "showpass"	=> false,
											 #"link"		=> array("login", true),
											 "usemail"	=> true
											 ),
					 "multi"		=> array("type"		=> "file",
											 "filetypes"	=> array("pdf","jpg","png"),
											 "filesize"	=> 5242880,
											 "filefolder"	=> "pics",
											 "fileprefix"	=> "cv-",
											 "filereplace"	=> true,
											 #"options"	=> array("one","two","tree"),
					 						 #"pagebreak"	=> true,
					 						 "header"	=> "Schritt 3"
											 ),
					 "cf_usernotice"=> array("success"	=> "{s_notice:formsuccess}",
										 	 "error"	=> "{s_error:formerror}",
										 	 "errorfill"	=> "{s_error:checkform}",
										 	 "errorfield"	=> "{s_error:checkfield}"
											 ),
					 "cf_addfields"	=> array("table"	=> "user",
										 	 "fields"	=> array("username","email","auth_code"),
										 	 "position"	=> "login",
										 	 "labels"	=> array("l_username","l_email","l_auth_code")
											 ),
					 "cf_captcha"	=> false
					 );



/*
ALTER TABLE `autoformtest`
  ADD CONSTRAINT `autoformtest_ibfk_1` 
  FOREIGN KEY (`login`) REFERENCES `user` (`login`);
<?php
namespace Concise;


  echo "</pre>";
echo "</div>";

//Ende der Seite
###############

*/

?>