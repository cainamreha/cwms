<?php
namespace Concise;


###################################################
#################  ListPages  #####################
###################################################

// Seitenliste erstellen

// common.php einbinden
require_once "../../inc/common.php";
require_once "../inc/checkBackendAccess.inc.php"; // Berechtigung prüfen

require_once SYSTEM_DOC_ROOT . "/inc/adminclasses/class.ListPages.php"; // ListPages-Klasse



// Falls FE-Theme-Defaults genutzt werden sollen, Init-Objekt neu instanzieren	
if(!empty($GLOBALS['_GET']['fe-theme']))
	$themeType = "fe";
else
	$themeType = "admin";


// Object instanzieren
$o_listPages	= new ListPages($DB, $o_lng, $themeType);
$o_listPages->conductAction();

exit;
?>