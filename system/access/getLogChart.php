<?php
namespace Concise;


// Datei "common.php" und jpGraph-Files einbinden
require_once "../../inc/common.php";
require_once "../inc/checkBackendAccess.inc.php";

require_once "../../inc/classes/Logging/class.Chart.php";

/*
require_once "../../extLibs/jpgraph-2.3.3/src/jpgraph.php";
require_once "../../extLibs/jpgraph-2.3.3/src/jpgraph_bar.php";
require_once "../../extLibs/jpgraph-2.3.3/src/jpgraph_pie.php";
require_once "../../extLibs/jpgraph-2.3.3/src/jpgraph_pie3d.php";
*/
require_once "../../extLibs/jpgraph-3.5.0/src/jpgraph.php";
require_once "../../extLibs/jpgraph-3.5.0/src/jpgraph_bar.php";
require_once "../../extLibs/jpgraph-3.5.0/src/jpgraph_pie.php";
require_once "../../extLibs/jpgraph-3.5.0/src/jpgraph_pie3d.php";


//Hier wird das entsprechende Schaubild aufgerufen
if(isset($_GET['chart'])){
	
	$startDate	= "";
	$endDate	= "";
	
	// Startdatum für Stats
	if(isset($_GET['startdate']) && $_GET['startdate'] != "")
		$startDate = $_GET['startdate'];
	// Enddatum für Stats
	if(isset($_GET['enddate']) && $_GET['enddate'] != "")
		$endDate = $_GET['enddate'];
	
	if($_GET['chart']=="browserChart") {
		$chart = new Chart($DB, $lang);
		$chart->buildBrowserChart($startDate, $endDate);
		
	}
	else if($_GET['chart']=="clickChart") {
		$visits = "";
		$order = 4;
		
		if(isset($_GET['visits']) && ($_GET['visits'] == "pageimp_curmon" || $_GET['visits'] == "pageimp_lastmon" || $_GET['visits'] == "pageimp_all" || $_GET['visits'] == "overview" || $_GET['visits'] == "visits"))
			$visits = $_GET['visits'];
			
		if(isset($_GET['order']) && $_GET['order'] > 0 && $_GET['order'] < 5)
			$order = $_GET['order'];
			
		$pageNum = $GLOBALS['_GET']['pageNum'];
		$totalRows = $GLOBALS['_GET']['totalRows'];
				
		$chart = new Chart($DB, $lang);
		$chart->buildClickChart($pageNum, $totalRows, $order, $visits, $startDate, $endDate);
	}
}

?>