<?php
namespace Concise;


// Speichern von FE-Änderungen an Textelementen in der DB

// common.php einbinden
require_once "../../inc/common.php";
require_once "../inc/checkBackendAccess.inc.php"; // Berechtigung prüfen

require_once SYSTEM_DOC_ROOT . "/inc/adminclasses/class.feEdit.php"; // feEdit-Klasse


// Falls FE-Theme-Defaults genutzt werden sollen, Init-Objekt neu instanzieren	
if(!empty($GLOBALS['_GET']['fe-theme']))
	$themeType = "fe";
else
	$themeType = "admin";


// Object instanzieren
$o_feEdit	= new FeEdit($DB, $o_lng, $themeType);
$o_feEdit->conductAction();

exit;
?>