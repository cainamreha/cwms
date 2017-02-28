<?php
namespace Concise;


###################################################
##################  ListMedia  ####################
###################################################

// Medienliste erstellen

// common.php einbinden
require_once "../../inc/common.php";
require_once "../inc/checkBackendAccess.inc.php"; // Berechtigung prüfen

require_once SYSTEM_DOC_ROOT . "/inc/adminclasses/class.ListMedia.php"; // ListMedia-Klasse
	
$themeType	= "admin";

// Object instanzieren
$o_listMedia	= new ListMedia($DB, $o_lng, $themeType);
$o_listMedia->conductAction();
				
exit;
	
?>