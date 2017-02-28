<?php
namespace Concise;

###############################################
###############  Userbereich  #################
###############################################

// Benutzerspezifische Daten
// Verwaltung der Inhalte des Ordners "/user"

// checkSiteAccess
require_once "../inc/checkSiteAccess.inc.php";
// common.php einbinden
require_once "../inc/common.php";
// Klasse Files einbinden
require_once PROJECT_DOC_ROOT . "/inc/classes/Media/class.Files.php";

// Falls kein geloggter Benutzer oder kein Dokument spezifiziert und kein Benutzerbild (Avatar, =öffentlich), zur Fehlerseite gehen
if((!isset($_SESSION['userid']) || empty($_GET['userfile']))
	&& strpos($_GET['userfile'], "img/avatar_") === false
) {
	header("location: " . PROJECT_HTTP_ROOT . "/error" . PAGE_EXT);
	exit;
}

// Entschlüsselungsfunktion
function deCrypt($enc) {

	// Dokumentname entschlüsseln
	require_once(PROJECT_DOC_ROOT."/inc/classes/Modules/class.myCrypt.php"); // Klasse myCrypt einbinden
	
	// myCrypt Instanz
	$crypt = new myCrypt();
	
	// Decrypt String
	$dec = $crypt->decrypt($enc);
	
	return $dec;
}

// Falls ein Benutzerbild (Avatar, =öffentlich) geladen werden soll
if(strpos($_GET['userfile'], "img/avatar_") === 0) {

	$userFile		= explode(".", str_replace("img/avatar_", "", $_GET['userfile']));
	$userFile[0]	= deCrypt($userFile[0]);
	$fileExt		= $userFile[1];
	$fileName		= strval(Files::getValidFileName('avatar_' . implode(".", $userFile)));
	$userFile		= PROJECT_DOC_ROOT . '/' . CC_USER_FOLDER . '/img/' . $fileName;
	
	// Dateiinhalt auslesen und anzeigen
	header("Content-type: image/" . ($fileExt == "png" ? "png" : "jpeg"));
	header('Content-Disposition: inline;');
	readfile($userFile);
	exit;
}


// Benutzerspezifische Dateien
$userID			= $_SESSION['userid'];
$userFile		= deCrypt($_GET['userfile']);
$folder			= PROJECT_DOC_ROOT . '/' . CC_USER_FOLDER . '/';

if(strpos($userFile, "/") !== false) {
	$fileArr	= explode("/", $userFile);
	$userFile	= array_pop($fileArr);
	$filePath	= implode("/", $fileArr) . '/';
	$folder	   .= $filePath;
}

$userFile		= Files::getValidFileName($userFile);
$baseName		= basename($userFile);
$fileExt		= strtolower(substr($baseName, strrpos($baseName,'.')+1, strlen($baseName)-1)); // Bestimmen der Dateinamenerweiterung
$fileName		= substr($baseName, 0, strrpos($baseName,'.')); // Bestimmen des Dateinamens ohne Erweiterung

// Präfix für den Dateinamen auslesen, um Benutzernamen zu extrahieren
if(isset($_GET['pf']) && $_GET['pf'] != "") {
	
	$prefix		= $_GET['pf'];
	$userSeek	= substr($fileName, strpos($fileName, $prefix) + strlen($prefix));
}
else
	$userSeek	= $fileName;

// Falls Benutzername aus Datei, nicht mit dem Benutzernamen des geloggten Benutzers übereinstimmt, zur Fehlerseite gehen
// WICHTIG!!! Hier wird entschieden ob der Benutzer Zugriffsberechtigung hat!
if(strval(Files::getValidFileName($userID, true)) !== strval($userSeek)) {
	header("location: " . PROJECT_HTTP_ROOT . "/error" . PAGE_EXT);
	exit;
}


// Falls Datei nicht existiert, abbrechen
if(!file_exists($folder . $userFile)) {
	die("file not found: " . $baseName);
}


// MIME-Type
switch($fileExt) {
	
	case 'zip':
		$mimeType = 'application/x-zip-compressed';
		break;
		
	case 'doc';
		 'docx';
		$mimeType = 'application/msword';
		break;
		
	case 'pdf':
		$mimeType = 'application/pdf';
		break;
		
	case 'jpg';
		 'jpeg';
		$mimeType = 'image/jpeg';
		break;
		
	case 'png':
		$mimeType = 'image/png';
		break;
		
	case 'tiff';
		 'tif';
		$mimeType = 'image/tiff';
		break;

	default:
		$mimeType = 'application/octet-stream';

}

// Header setzen
header("Content-type:\" $mimeType \"");
header("Content-Disposition: attachment; filename=\"$fileName\"");

// Dateiinhalt auslesen und anzeigen
readfile($folder . $userFile);

exit;
?>