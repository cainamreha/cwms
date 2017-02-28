<?php
namespace Concise;


###############################################
###############  Edit Plugins  ################
###############################################

// Plugins editieren

// common.php einbinden
require_once "../../inc/common.php";
require_once "../inc/checkAdminAccess.inc.php"; // Berechtigung prüfen

require_once "../inc/adminclasses/class.EditPlugins.php"; // EditPlugins-Klasse	


// Object instanzieren
$o_editPlugins	= new EditPlugins($DB, $o_lng);
$o_editPlugins->conductAction();
?>