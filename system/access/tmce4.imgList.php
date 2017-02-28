<?php
namespace Concise;

 // this must be the very first line in your PHP file!

require_once "../../inc/common.php";


// falls kein Admin/Editor/Author
if(!$o_security->get('backendLog')) {
	exit;
	die();
}


// You can't simply echo everything right away because we need to set some headers first!
$output		= ''; // Here we buffer the JavaScript code we want to send to the browser.
$fileArray	= array();
$delimiter	= "\n"; // for eye candy... code gets new lines

$output		.= '[';

$directory	= array("../../" . CC_IMAGE_FOLDER, substr("../../".IMAGE_DIR, 0, -1)); // Use your correct (relative!) path here

// Since TinyMCE3.x you need absolute image paths in the list...
#$abspath = preg_replace('~^/?(.*)/[^/]+$~', '/$1', $_SERVER['SCRIPT_NAME']);
#$abspathDB = "{#root}";
$abspath = PROJECT_HTTP_ROOT;
$i = 1;

foreach($directory as $imgDir) {
	
	// Für Speicherung in der DB Pfade mit Platzhaltern ersetzen
	if($i == 2) { // Falls Theme-Verzeichnis
		$abspath .= '/'.IMAGE_DIR;
		$imgDirDB = "";
	}
	else // Sonst rel. Pfad ersetzen
		$imgDirDB = str_replace("../..", "", $imgDir)."/";

	
	if (is_dir($imgDir)) {
		
		$direc = opendir($imgDir);
	
		while ($file = readdir($direc)) {
			if (!preg_match('~^\.~', $file)) { // no hidden files / directories here...
				 if (is_file("$imgDir/$file") != FALSE) {
					// We got ourselves a file! Make an array entry:
					$fileArray[] = $file;
				}
			}
		}
	
		closedir($direc);
		
	
		if(!empty($fileArray)) {
		
			natsort($fileArray); // Array sortieren
			
			foreach($fileArray as $sf) {
				
				$output .= $delimiter
				. '{title: "' . utf8_encode($sf) . '", value: "' . utf8_encode($abspath.$imgDirDB."$sf") . '"},';
			}
			
			$fileArray = array(); // Array leeren
		}
		
		if($i == count($directory))
			$output = substr($output, 0, -1); // remove last comma from array item list (breaks some browsers)
		else {
			$output .= $delimiter .
			'{title: " ", value: " "},' .
			'{title: "--- Theme Graphiken ---", value: " "},';
		}
		$output .= $delimiter;
	}
	$i++;
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