<?php
namespace Concise;


###################################################
#################  EditPages  #####################
###################################################

// Seitenliste erstellen

// common.php einbinden
require_once "../../inc/common.php";
require_once "../inc/checkEditorAccess.inc.php"; // Berechtigung prüfen

require_once SYSTEM_DOC_ROOT . "/inc/adminclasses/class.EditPages.php"; // EditPages-Klasse
	
	
// Falls der Live-Modus der Website geändert werden soll
if(isset($_GET['sitemode']) && $o_security->get('adminLog')) {

	
	$mode 	= $_GET['sitemode'] ? true : false;	
	$result = Admin::setWebsiteLiveMode($mode);	
	ContentsEngine::$staText	= $o_lng->staText;
	$info	= ContentsEngine::replaceStaText("{s_notice:go" . ($mode ? 'live' : 'stage') . $result . "}");
	
	echo $info;
	
	if(!isset($_GET['ajax'])) die();
	exit;
}



// Falls FE-Theme-Defaults genutzt werden sollen, Init-Objekt neu instanzieren	
if(!empty($GLOBALS['_GET']['fe-theme']))
	$themeType = "fe";
else
	$themeType = "admin";


// Object instanzieren
$o_editPages	= new EditPages($DB, $o_lng, $themeType);
$o_editPages->conductAction();

exit;
?>