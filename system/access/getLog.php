<?php
namespace Concise;


//Als Erstes die common.php einbinden
require_once "../../inc/common.php";
require_once "../inc/checkAdminAccess.inc.php";


// Falls kein Debug zur Fehlerseite gehen
if(!DEBUG)
	header("Location:" . PROJECT_HTTP_ROOT . "/error" . PAGE_EXT) . exit;

	
//Klasse DebugLogging
require_once(PROJECT_DOC_ROOT . "/inc/classes/Debugging/class.Logging.php"); // Loggingklasse einbinden

echo '<html><head></head>';
echo '<body>';
echo '<a class="standardSubmit" style="float:right;" href="' . PROJECT_HTTP_ROOT . '/admin?deleteLogfile&all">Log Files löschen (alle)</a>';
echo '<a class="standardSubmit" href="' . PROJECT_HTTP_ROOT . '/admin?deleteLogfile">Log File löschen (heute)</a><br class="clearfloat" />';
Logging::getLog();
echo '</body>';
echo '</html>';
?>
