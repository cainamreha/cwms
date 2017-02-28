<?php
// checkSiteAccess
require_once "../inc/checkSiteAccess.inc.php";
require_once('../inc/common.php'); // common.php einbinden
require_once PROJECT_DOC_ROOT."/inc/classes/Modules/class.Rating.php"; // Ratingklasse einbinden


// Falls eine Bewertung abgegeben wurde
if(isset($GLOBALS['_GET']['starr']) && $GLOBALS['_GET']['starr'] != "") {

	$o_rating	= new Concise\Rating($DB, true);
	$rating		= $o_rating->executeRating();
	if($rating)
		echo "$rating";
	exit;
}
?>