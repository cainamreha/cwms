<?php
namespace Concise;


###################################################
###############  Edit Module-Data  ################
###################################################

// Artikel etc. Daten editieren

// common.php einbinden
require_once "../../inc/common.php";
require_once "../inc/checkBackendAccess.inc.php"; // Berechtigung prüfen

require_once PROJECT_DOC_ROOT . "/inc/classes/Admin/interface.AdminTask.php"; // AdminTask-Interface einbinden
require_once SYSTEM_DOC_ROOT . "/inc/admintasks/modules/admin_modules.data.inc.php"; // DataModules-Klasse
require_once SYSTEM_DOC_ROOT . "/inc/adminclasses/class.EditModules.php"; // EditModules-Klasse
	

// Object instanzieren
$o_editModule	= new EditModules($DB, $o_lng);
$o_editModule->conductAction();
exit;
?>