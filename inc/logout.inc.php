<?php
// Falls Benutzername gesetzt, Code in DB zur�cksetzen
if(isset($GLOBALS['_SESSION']['username'])) {
	
	$sessionUser = $DB->escapeString($GLOBALS['_SESSION']['username']);

	// Code in DB zur�cksetzen
	$sql = $DB->query("UPDATE `" . DB_TABLE_PREFIX . "user` 
						SET `logID` = ''
						WHERE `username` = '".$sessionUser."'
					  ");
}

// Cookie l�schen
setcookie("conciseLog", "", time()-3600, "/");

// Zerst�ren der Sitzung
session_destroy();

// Zur Logoutbest�tigungsseite gehen
header("Location: " . PROJECT_HTTP_ROOT . "/logout" . PAGE_EXT);
exit;
?>