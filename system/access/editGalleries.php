<?php
namespace Concise;


###################################################
#################  EditGalleries  #################
###################################################

// Galerien editieren

// common.php einbinden
require_once "../../inc/common.php";
require_once "../inc/checkBackendAccess.inc.php"; // Berechtigung prüfen

require_once "../inc/adminclasses/class.EditGalleries.php"; // EditGalleries-Klasse
	

// Object instanzieren
$o_editGalleries	= new EditGalleries($DB, $o_lng);
echo $o_editGalleries->conductAction();
exit;
?>