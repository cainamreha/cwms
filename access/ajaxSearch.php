<?php
header("Content-Type: text/html; charset=utf-8");

// checkSiteAccess
require_once __DIR__."/../inc/checkSiteAccess.inc.php";
require_once "../inc/common.php";
require_once PROJECT_DOC_ROOT . "/inc/classes/Modules/class.Search.php";


if(isset($_GET['type']) && $_GET['type'] == "big")
	$searchType = $_GET['type'];
else
	$searchType = "small";	

// Suche starten
if(SEARCH_TYPE != "none" || $o_security->get('adminPage')) {
	$o_search = new Concise\Search($DB, $o_lng, SEARCH_TYPE);
	$o_search->getThemeDefaults($o_security->get('adminPage') ? "admin" : "fe");
	echo Concise\ContentsEngine::replaceStyleDefs($o_search->getSearch($searchType));
}
else
	echo "not allowed";
exit;
?>