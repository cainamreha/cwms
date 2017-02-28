<?php
namespace Concise;


// Common einbinden
require_once "../../inc/common.php";
require_once "../inc/checkAdminAccess.inc.php";
require_once(PROJECT_DOC_ROOT . "/inc/classes/Debugging/class.DebugConsole.php"); // Klasse DebugConsole

echo '<html><head><title>PHP und MySQL</title>';
echo '<meta http-equiv="content-type" content="text/html; charset=UTF-8">';
echo '</head>';
echo '<body style="margin:0px;padding:0px;">';
//Wenn ein Pfadname übergeben wurde...
if(isset($_GET['filename'])) echo DebugConsole::printCode($_GET['filename']);
echo '</body>';
echo '</html>';
?>