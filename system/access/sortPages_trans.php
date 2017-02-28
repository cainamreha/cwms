<?php
namespace Concise;


###################################################
################  Sort by cut  ####################
###################################################

// Seiten neu sortieren

// common.php einbinden
require_once "../../inc/common.php";
require_once "../inc/checkBackendAccess.inc.php"; // Berechtigung prüfen

require_once SYSTEM_DOC_ROOT . "/inc/adminclasses/class.SortPages.php"; // SortPages-Klasse


$menuItemTarget	= 1;
$dbError		= "";

if(isset($_GET['sort'])
&& ($_GET['sort'] == "below" || $_GET['sort'] == "child")
)
	$sortType = $_GET['sort'];
	
if(!empty($_GET['move']))
	$moveId = $_GET['move'];

if(isset($_GET['targetid'])
&& $_GET['targetid'] != ""
)
	$targetId = $_GET['targetid'];

if(isset($_GET['menuitem'])
&& $_GET['menuitem'] != ""
&& $targetId == "new"
) {
	$menuItemTarget = (int)$_GET['menuitem'];
}

setcookie('sort_id', $moveId);

// Object instanzieren
$o_sortPages	= new SortPages($DB, $tablePages);
$result			= $o_sortPages->sortPageTrans($sortType, $moveId, $targetId, $menuItemTarget);

if(!$result) {
	$dbError = '<script type="text/javascript">jAlert(ln.severeerror, ln.alerttitle);</script>';
	#header("location: " . ADMIN_HTTP_ROOT . "?task=sort");
}

// get sort page
require_once PROJECT_DOC_ROOT . "/inc/classes/Admin/interface.AdminTask.php"; // AdminTask-Interface einbinden
require_once SYSTEM_DOC_ROOT . "/inc/admintasks/sort/admin_sort.inc.php"; // Admin-Task einbinden
$adminTask	= 'sort';
$adminE		= new Admin_Sort($GLOBALS['DB'], $GLOBALS['o_lng'], $adminTask);
// Theme-Setup
$adminE->getThemeDefaults("admin");
$ajaxOutput	= $adminE->getTaskContents(true);
echo ContentsEngine::replaceStaText($ajaxOutput);
echo $dbError;

exit;
?>