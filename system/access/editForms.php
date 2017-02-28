<?php
namespace Concise;


###################################################
#################  EditForms  #####################
###################################################

// EditForms

// common.php einbinden
require_once "../../inc/common.php";
require_once "../inc/checkBackendAccess.inc.php"; // Berechtigung prüfen

require_once "../inc/adminclasses/class.EditForms.php"; // EditForms-Klasse

$o_editForm = new EditForms($DB, $o_lng);
$o_editForm->conductAction();
exit;
?>