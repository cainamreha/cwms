<?php
namespace Concise;

 // this must be the very first line in your PHP file!

require_once "../../inc/common.php";
require_once PROJECT_DOC_ROOT."/inc/classes/Media/class.Media.php";


// falls kein Admin/Editor/Author
if(!$o_security->get('backendLog')) {
	exit;
	die();
}


// You can't simply echo everything right away because we need to set some headers first!
$output		= ''; // Here we buffer the JavaScript code we want to send to the browser.
$fileArray	= array();
$delimiter	= "\n"; // for eye candy... code gets new lines

$output .= '[';

// Pages

// db-Query nach Menu/nicht-Menu-Seiten
$linkQuery = $GLOBALS['DB']->query("SELECT *
									FROM `" . DB_TABLE_PREFIX . $tablePages . "` 
									WHERE `page_id` > 0 
									OR `page_id` < -1001 
									ORDER BY `alias_" . $GLOBALS['_SESSION']['edit_lang'] . "`
									");


foreach($linkQuery as $row) { // Schleife zum Ausgeben der Menupunktliste

	$pageId		= $row['page_id']; // Menuepunktid
	$title		= $row['title_' . $GLOBALS['_SESSION']['edit_lang']]; // Menuepunkttitel
	$alias		= $row['alias_' . $GLOBALS['_SESSION']['edit_lang']]; // Menuepunkttitel
	$link		= HTML::getLinkPath($pageId, "editLang"); // Link mit Pfad holen

	$output .= $delimiter
			. '{title:"'
			. Language::force_utf8($title." (".$alias.")")
			. '", value:"'
			. Language::force_utf8('{#root}/'.$link)
			. '"},';
}


// Documents
$output .=	$delimiter .
			'{title: " ", value: " "},' .
			'{title: "--- Dokumente ---", value: " "},';

$docDir	= "../../" . CC_DOC_FOLDER; // Use your correct (relative!) path here
$basePath	= CC_DOC_FOLDER . "/";


if (is_dir($docDir)) {
		
	// Dokumentname ggf. verschlüsseln
	require_once(PROJECT_DOC_ROOT."/inc/classes/Media/class.FileOutput.php"); // Klasse FileOutput einbinden
	
	$direc		= opendir($docDir);

	while ($file = readdir($direc)) {
		if (!preg_match('~^\.~', $file)) { // no hidden files / directories here...
			 if (is_file("$docDir/$file")) {
				// We got ourselves a file! Make an array entry:
				$fileArray[] = $file;
			}
		}
	}

	closedir($direc);
	
	if(!empty($fileArray)) {
	
		natsort($fileArray); // Array sortieren
		
		foreach($fileArray as $sf) {

			// Dokumentlinkname
			$docLink = FileOutput::getFileHash($sf, "doc", $basePath);
			
			$output .= $delimiter
			. '{title: "' . utf8_encode($sf) . '", value: "{#root}/' . $docLink . '"},';
		}
	}
}

if(strrpos($output, ",") == (strlen($output) -1))
	$output = substr($output, 0, -1); // remove last comma from array item list (breaks some browsers)
	
// Finish code: end of array definition.
$output .= ']';


// Make output a real JavaScript file!
header('Content-type: text/javascript'); // browser will now recognize the file as a valid JS file

// prevent browser from caching
header('pragma: no-cache');
header('expires: 0'); // i.e. contents have already expired

// Now we can send data to the browser because all headers have been set!
echo $output;

?>