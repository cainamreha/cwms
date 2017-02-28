<?php
// Falls Benutzername gesetzt, Code in DB zurücksetzen
if(isset($GLOBALS['_SESSION']['username'])) {
	
	$sessionUser = $DB->escapeString($GLOBALS['_SESSION']['username']);

	// Code in DB zurücksetzen
	$sql = $DB->query("UPDATE `" . DB_TABLE_PREFIX . "user` 
						SET `logID` = ''
						WHERE `username` = '".$sessionUser."'
					  ");
}

// Cookie löschen
setcookie("conciseLog", "", time()-3600, "/");

// Zerstören der Sitzung
session_destroy();

// Zur Logoutbestätigungsseite gehen
header("Location: " . PROJECT_HTTP_ROOT . "/logout" . PAGE_EXT);
exit;
?>