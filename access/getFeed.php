<?php
namespace Concise;

// checkSiteAccess
require_once "../inc/checkSiteAccess.inc.php";

if(isset($_GET['id']) && is_numeric($_GET['id'])) {
	
	require_once "../inc/common.php";
	require_once(PROJECT_DOC_ROOT."/inc/classes/Modules/class.Feed.php"); // Klasse Feed einbinden (das Skript wird dort unterbrochen)

	$feedID		= $_GET['id'];
	$feedType	= "rss";
	$targetUrl	= "";
	
	if(isset($_GET['ff']) && $_GET['ff'] != "")
		$feedType	= $_GET['ff'];
	
	if(isset($_GET['tp']))
		$targetUrl	= htmlspecialchars(HTML::getLinkPath($_GET['tp'], "current", false, true));
	
	$i_newsfeed = new Feed($DB, $lang, $feedID, $feedType, $targetUrl);
	$i_newsfeed->outputFeed();
}
else {
	header("Location: error" . PAGE_EXT);
	exit;
}
 
exit;
die(); 
?>