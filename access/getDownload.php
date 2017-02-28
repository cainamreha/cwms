<?php
namespace Concise;

// Anzeigen von Dokumentdateien oder Backup-Dateien

// checkSiteAccess
require_once "../inc/checkSiteAccess.inc.php";

require_once "../inc/common.php";

$userAgent = "";


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


// Falls eine Dokumentdatei angezeigt/heruntergeladen werden soll
if(isset($_GET['file']) && $_GET['file'] != "") {
	
	require_once(PROJECT_DOC_ROOT."/inc/classes/Media/class.Files.php"); // Klasse Files einbinden
	
	$doc			= deCrypt($_GET['file']);
	$docPath		= "";
	
	// Pfad zur Dokumentdatei
	// Falls files-Ordner, den Pfad ermitteln
	if(strpos($doc, "/") !== false) {
		$filesDoc	= explode("/", $doc);
		$doc		= array_pop($filesDoc);
		$docPath	= implode("/", $filesDoc) . '/';
		$folder		= PROJECT_DOC_ROOT . '/' . CC_FILES_FOLDER . '/' . $docPath;
	}
	else
		$folder		= PROJECT_DOC_ROOT . '/' . CC_DOC_FOLDER . '/';
		
	$doc			= Files::getValidFileName($doc);
	$dwnl			= basename($doc);

	if (!file_exists($folder . $dwnl)) {
		header("location: " . PROJECT_HTTP_ROOT . "/error" . PAGE_EXT);
		exit;
	}
	
	$timestamp = time();
	
	// MIME-Type  
	$mimeType	= Files::getMimeType($folder . $dwnl);
	
	// User-Agent
	if(isset($_SERVER['HTTP_USER_AGENT']))
		$userAgent = $_SERVER['HTTP_USER_AGENT'];
				
	if(Log::checkBot($userAgent) !== true) { // Wenn kein Robot, log auf true setzen
		
		
		$dwnlDB = $GLOBALS['DB']->escapeString($docPath . $dwnl);
		
		// Eintrag in DB
		$countDwnl = $GLOBALS['DB']->query("INSERT INTO `" . DB_TABLE_PREFIX . "download` 
												(`filename`,
												`downloads`)
											VALUES 
												('$dwnlDB',
												1)
											ON DUPLICATE KEY UPDATE `downloads` = `downloads`+1
											");
			
	}
	
}

// Falls eine Backup-Datei angezeigt werden soll
// Falls eine Dokumentdatei angezeigt/heruntergeladen werden soll
if(isset($_GET['bkpfile']) && $_GET['bkpfile'] != "") {
	
	if($o_security->get('adminLog')) {
		
		$doc		= $_GET['bkpfile'];
		$folder		= PROJECT_DOC_ROOT . "/backup/";
		$mimeType	= "text";
		$dwnl		= basename($doc);
	}
	else
		header("Location:" . PROJECT_HTTP_ROOT . "/error" . PAGE_EXT) . exit;
	
}


if(file_exists($folder . $dwnl)) {

	header("Content-type:\" $mimeType \"");
	header("Content-Disposition: attachment; filename=\"$dwnl\"");
	
	readfile($folder . $dwnl);
}
else
	header("location: " . PROJECT_HTTP_ROOT . "/error" . PAGE_EXT);
exit; 
?>