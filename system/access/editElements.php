<?php
namespace Concise;


###################################################
################  Edit Elements  ##################
###################################################

// Elemente editieren

// Ggf. FE-Seite setzen (page=admin GET-Parameter überschreiben)
if(!empty($GLOBALS['_GET']['fe'])
&& !empty($GLOBALS['_GET']['red'])
) {
	$GLOBALS['_GET']['page']	= $GLOBALS['_GET']['red'];
}


// common.php einbinden
require_once "../../inc/common.php";
require_once SYSTEM_DOC_ROOT . "/inc/checkBackendAccess.inc.php"; // Berechtigung prüfen

require_once SYSTEM_DOC_ROOT . "/inc/adminclasses/class.EditElements.php"; // EditElements-Klasse


$GLOBALS['_GET']['page']	= str_replace(PAGE_EXT, "", $GLOBALS['_GET']['page']);


// Falls FE-Theme-Defaults genutzt werden sollen, Init-Objekt neu instanzieren	
if(!empty($GLOBALS['_GET']['fe-theme']))
	$themeType = "fe";
else
	$themeType = "admin";

// Object instanzieren
$o_editElements	= new EditElements($DB, $o_lng, $themeType);
$o_editElements->conductAction();

exit;
?>