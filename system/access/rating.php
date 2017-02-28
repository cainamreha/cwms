<?php
namespace Concise;


// common.php einbinden
require_once "../../inc/common.php";
require_once PROJECT_DOC_ROOT."/inc/classes/Modules/class.Rating.php"; // Ratingklasse einbinden


$o_rating	= new Rating($DB, true);

// Falls eine Bewertung abgegeben wurde
if(isset($GLOBALS['_GET']['starr']) && $GLOBALS['_GET']['starr'] != "") {
	
	$rating	= $o_rating->executeRating();
	if($rating) echo "$rating";
}


// Falls eine Bewertung zurückgesetzt werden soll
elseif(isset($GLOBALS['_GET']['action']) && $GLOBALS['_GET']['action'] == "res") {
		
	$rating	= $o_rating->resetRating();
	if($rating) echo "$rating";
}
exit;
?>