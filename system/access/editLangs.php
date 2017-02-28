<?php
namespace Concise;


###############################################
###############  Edit Plugins  ################
###############################################

// Sprachen verwalten

// common.php einbinden
require_once "../../inc/common.php";
require_once "../inc/checkEditorAccess.inc.php"; // Berechtigung prüfen

require_once "../inc/adminclasses/class.EditLangs.php"; // EditLangs-Klasse	


// Object instanzieren
$o_editLangs	= new EditLangs($DB, $o_lng);
echo (string) $o_editLangs->conductAction();
?>