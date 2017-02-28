<?php
namespace Concise;



/**
 * Klasse für die Erstellung von Statistik-Grafen mittels JP-Graph
 * 
 */

class Chart
{
    
	private $o_stats	= null;
	private $DB			= null;
	private $lang		= "";
	public $rGraph		= false;


	/**
	 * Konstruktor der Klasse Chart
	 * 
     * @access	public
 	 * @param	object	$DB		DB-Objekt
	 */
	public function __construct($DB, $lang)
	{
	
		// Datenbankobjekt
		$this->DB 		= $DB;
		// Sprache
		$this->lang 	= $lang;
		// Statsobjekt
		$this->o_stats	= $this->initStatsObj();
	
	}

	/**
	 * Gibt eine Stats-Instanz zurück
	 * 
     * @access	public
 	 * @return	object
	 */
	private function initStatsObj()
	{
	
		require_once PROJECT_DOC_ROOT."/inc/classes/Logging/class.Stats.php"; // Statsklasse einbinden	
		
		return new Stats($this->DB, $this->lang);
	
	}

	
    /**
     * Zusammenbauen eines Klick-Schaubildes
     * 
     * @access	public
	 * @param	string	Aktuelle Aufzählungsseite
	 * @param	string	Gesamtzahl an Aufzählungsseiten
	 * @param	string	Sortierung
	 * @param	string	Art der Statistik
	 * @param	string	Startdatum
	 * @param	string	Enddatum
     */
	public function buildClickChart($pageNum, $totalRows, $order, $visits, $startDate = "", $endDate = "")
	{   
	
		$maxRows	= 10;
		$startRow	= $pageNum * $maxRows;
		
		$plotArray	= array();
		$nameArray	= array();
		$plotArray2	= array();
		$nameArray2	= array();
		$multiGraph = false;
		
			
		switch($order) {
				
			case 1:
			case 7:
			case 9:
				$orderByVis = "page ASC";
				$orderBy = "page ASC";
				break;
				
			case 2:
			case 8:
			case 10:
				$orderByVis = "page DESC";
				$orderBy = "page DESC";
				break;
			
			case 3:
			case 5:
				$orderByVis = "COUNT(*) ASC, page ASC";
				$orderBy = "statcount ASC, page ASC";
				break;
				
			case 4:
			case 6:
				$orderByVis = "COUNT(*) DESC, page ASC";
				$orderBy = "statcount DESC, page ASC";
				break;
			
			default:
				$orderByVis = "COUNT(*) DESC, page ASC";
				$orderBy = "statcount DESC, page ASC";
		}
		
		switch($visits) {
			
			case "overview":
				$result1 = $this->o_stats->getVisitStats("unique_pi", $startDate, $endDate);
				$result2 = $this->o_stats->getVisitStats("visits", $startDate, $endDate);
				$result3 = $this->o_stats->getVisitStats("unique_visitors", $startDate, $endDate);
							
				$title = "Gesamtstatistik";
				
				// Datenbeschriftung
				$scriptName1 = "unique page views";
				$scriptName2 = "visits";
				$scriptName3 = "unique visitors";
				
				break;
				
			case "visits":
				$result1 = $this->o_stats->getVisitStats("visits", "currmon");
				$result2 = $this->o_stats->getVisitStats("visits", "lastmon");
				$result3 = $this->o_stats->getVisitStats("visits", "2lastmon");
							
				$title = "Besuche (visits) Monatsvergleich";
			
				// Datenbeschriftung
				$scriptName1 = "aktueller Monat";
				$scriptName2 = "letzter Monat";
				$scriptName3 = "vorletzter Monat";
				
				break;
				
			case "visits_period":
				$result1 = $this->o_stats->getVisitStats("visits_period", $startDate, $endDate, $orderByVis, $startRow, $maxRows);
				$visGroup = "Zeitraum";
				break;
				
			case "pageimp_all":
				$result1 = $this->o_stats->getVisitStats("pageimp", $startDate, $endDate, $orderByVis, $startRow, $maxRows);
				$result2 = $this->o_stats->getVisitStats("unique_pageimp", $startDate, $endDate, $orderByVis, $startRow, $maxRows);					
				$visGroup = "gesamt";
				break;
				
			case "pageimp_curmon":
				$result1 = $this->o_stats->getVisitStats("pageimp", "currmon", "", $orderByVis, $startRow, $maxRows);
				$result2 = $this->o_stats->getVisitStats("unique_pageimp", "currmon", "", $orderByVis, $startRow, $maxRows);
				$visGroup = "aktueller Monat";
				break;
				
			case "pageimp_lastmon":
				$result1 = $this->o_stats->getVisitStats("pageimp", "lastmon", "", $orderByVis, $startRow, $maxRows);
				$result2 = $this->o_stats->getVisitStats("unique_pageimp", "lastmon", "", $orderByVis, $startRow, $maxRows);
				$visGroup = "letzter Monat";
				break;
		}
		
		// Gesamtstatistik
		if($visits == "overview" || $visits == "visits") {
							
			$yAxis = "Anzahl";
							
			//Klicks zu Werte-Array hinzufügen	
			array_push($plotArray,$result1);
			//Skriptnamen zum Array hinzufügen
			array_push($nameArray,$scriptName1);
			
			//Klicks zu Werte-Array hinzufügen	
			array_push($plotArray,$result2);
			//Skriptnamen zum Array hinzufügen
			array_push($nameArray,$scriptName2);

			//Klicks zu Werte-Array hinzufügen	
			array_push($plotArray,$result3);
			//Skriptnamen zum Array hinzufügen
			array_push($nameArray,$scriptName3);
		}
		// Days period
		elseif($visits == "visits_period") {
							
			if(empty($result1)
			|| !is_array($result1)
			|| count($result1)==0
			){
				$plotArray = array("0");
				$nameArray = array("-");
			}
			else {
			
				setlocale (LC_ALL, 'de_DE');
				$dateS	= new \DateTime();
				$dateS->setTimestamp($startDate);
				$dateE	= new \DateTime();
				$dateE->setTimestamp($endDate);
				$days	= $dateS->diff($dateE)->days;
				$d		= 0;
				
				for($d = 0; $d <= $days; $d++){
					$dStr	= $dateS->format('Y-m-d');
					$dName	= strftime('%e.&#8239;%b', $dateS->getTimestamp());
					$dValue	= strftime("%x", $dateS->getTimestamp());
					$cnt	= 0;
					$dLabel	= "";
					
					if($d == 0 || $d == $days)
						$dLabel	= $dValue;
					
					foreach($result1 as $script) {
						if($script['period'] == $dStr)
							$cnt	= $script['COUNT(*)'];
					}
					
					//Klicks zu Werte-Array hinzufügen	
					array_push($plotArray,$cnt);
					array_push($plotArray2,$dName);
					//Date zum Array hinzufügen
					array_push($nameArray,$dLabel);
					array_push($nameArray2,$dValue);
					
					$dateS->add(new \DateInterval('P1D'));
				}
			}
		}
		// Seitenstatistik
		elseif($visits != "") {
			
			$multiGraph = true;
							
			$title = "Seitenstatistik (" . $visGroup . ")";
			$yAxis = "Anzahl";
			
			if(empty($result1)
			|| !is_array($result1)
			|| count($result1)==0
			){
				$plotArray = array("0");
				$nameArray = array("-");
			}else{
			foreach($result1 as $script){
				//Klicks zu Werte-Array hinzufügen	
				array_push($plotArray,$script['COUNT(*)']);
				//Skriptname ohne .php
				$scriptName = $script['page'];
				//Skriptnamen zum Array hinzufügen
				array_push($nameArray,$scriptName);
			}
			}
			if(empty($result2)
			|| !is_array($result2)
			|| count($result2)==0
			){
				$plotArray2 = array("0");
				$nameArray2 = array("-");
			}else{
				foreach($result2 as $script){
					//Klicks zu Werte-Array hinzufügen	
					array_push($plotArray2,$script['COUNT(*)']);
					//Skriptname ohne .php
					$scriptName = $script['page'];
					//Skriptnamen zum Array hinzufügen
					array_push($nameArray2,$scriptName);
				}
			}
		}
		// Klickstatistik (Page Impressions)
		else {
			
			$visits = "pageimp_total";
			$result = $this->o_stats->getVisitStats("pageimp_total", $startDate, $endDate, $orderBy, $startRow, $maxRows);					
			
			$title = "Page Impressions (Seitenaufrufe gesamt)";
			$yAxis = "Anzahl";
			
			if(count($result)==0){
				$plotArray = "0";
				$nameArray = "-";
			}
			else{
				foreach($result as $script){
					//Klicks zu Werte-Array hinzufügen	
					array_push($plotArray,$script['statcount']);
					//Skriptname ohne .php
					$scriptName = $script['page'];
					//Skriptnamen zum Array hinzufügen
					array_push($nameArray,$scriptName);
				}
			}
		}
		
		// Falls RGraph
		if($this->rGraph)
			return $this->printStatsRGraph($nameArray, $nameArray2, $plotArray, $plotArray2, $multiGraph, $visits);
		// Sonst JPGraph
		else
			return $this->printStatsChart($nameArray, $plotArray, $plotArray2, $multiGraph, $visits);		
				
	}
	
	
	/**
	 * Zusammenbauen eines Browserbenutzungs-Schaubilds
	 * 
	 * @param	string	Startdatum
	 * @param	string	Enddatum
	 */
	public function buildBrowserChart($startDate = "", $endDate = "")
	{
		
		$browserArray	= array();
		$totalCount		= 0;
		$dataArray		= array();	
		$dbFilter		= "";
		$minYear		= max(2010, date("Y",$startDate));
		$maxYear		= max(2010, date("Y",$endDate));
		$currYear		= date("Y",time());
		$logYears		= array($minYear, $maxYear);
		$maxY			= $maxYear -1;
		
		while($maxY > $minYear) {
			$logYears[] = $maxY;
			$maxY--;
		}
		$logYears	= array_unique($logYears);
		rsort($logYears);
		
		
		// Falls ein Datum zur Begrenzung mitgegeben wurde
		if(is_numeric($startDate) || is_numeric($endDate)) {
			
			$dbFilter		= " WHERE ";
			$phraseLink		= "";
			
			if(is_numeric($startDate)) {
				$dbFilter		.= "`timestamp` >= " . $this->DB->escapeString($startDate);
				$phraseLink		= " AND ";
			}
			if(is_numeric($endDate)) // Enddatum
				$dbFilter		.= $phraseLink . "`timestamp` <= " . $this->DB->escapeString($endDate);
		}
		
		
		$l		= 1;
		$sql	= "SELECT browser, version, count(browser) as count FROM (";
		
		// Log-Tabelle(n) auslesen
		foreach($logYears as $logTab) {
		
			if($logTab == $currYear)
				$logTab	= "log";
			else
				$logTab = "log_" . $logTab;
			
			$logTab	= $this->DB->escapeString(DB_TABLE_PREFIX . $logTab);
			
			if($l > 1)
				$sql .= " \nUNION \n";
			
			
			// Falls RGraph
			if($this->rGraph)
				$sql .= "(SELECT timestamp, browser, version
							FROM $logTab t$l)";
			else {
				$sql .= "(SELECT timestamp, browser
							FROM $logTab t$l)";
			}
			$l++;
		}
		
		$sql	.= 	") pool $dbFilter
					GROUP BY browser, version
					ORDER BY count DESC, browser, version";
		
	    $result = $this->DB->query($sql);
		#die(HTML::printArray($result));
	    
		
	    // Falls keine Daten vorhanden
		if(count($result)==0)
	    {
	    	$browserArray	= array('No data' => array('count' => 100, 'browser' => '', 'version' => array()));
			$dataArray		= array('100');
			$totalCount		= 100;
	    }
		// Andernfalls Browserstats-Array vorbereiten
		else
		{
			foreach($result as $bData)
			{
			
				$browser	= $bData['browser'];
				$versArr	= explode(".", $bData['version']);
				$version	= reset($versArr);
				$count		= (int)$bData['count'];
					
				// Falls RGraph
				if($this->rGraph) {
		
					if(array_key_exists($browser, $browserArray)) {
						$browserArray[$browser]["version"][] = array	(	"v" => $version,
																			"c" => $count
																		);
						$browserArray[$browser]["count"]	+= $count;
						$totalCount							+= $count;
					}
					else {
						$browserArray[$browser] = array	(	"browser" => $browser,
															"version" => array (array	(	"v" => $version,
																							"c" => $count
																						)
																				),
															"count" => $count
														);
						
						$totalCount				+= $count;
					}
				}
				else {
					$browserArray[] = $browser;
					$dataArray[]    = $count;
				}
			}
		}	
		#die(HTML::printArray($browserArray));
		
		// Falls RGraph
		if($this->rGraph)
			return $this->printBrowserRGraph($browserArray, $totalCount);
		// Sonst JPGraph
		else
			return $this->printBrowserChart($browserArray, $dataArray);
			
	}
	
	
	/**
	 * Ausgeben eines Browserbenutzungs-Schaubilds
	 * 
	 * @param	string	Startdatum
	 * @param	string	Enddatum
	 */
	public function printStatsRGraph($nameArray, $nameArray2, $plotArray, $plotArray2, $multiGraph, $visits)
	{
	
		if(!is_array($nameArray))
			return "";
		
		$labels		= implode('","', $nameArray);
		$data		= "";
		$lineData	= "";
		$labelsIn	= "";
		$tooltips	= "";
		$key		= "{s_option:visitsmonth}";
		$uniqueID	= "-" . mt_rand();
		$barType	= 'Bar';
		$cvsW		= 591;
		$cvsH		= 450;
		$dataCnt	= count($plotArray);
		$allVals	= 0;
		
		if(empty($dataCnt))
			return '<p class="noStats">{s_text:nostats}</p>' . PHP_EOL;
		
		
		for($i = 0; $i < $dataCnt; $i++) {
		
			if($visits == "visits_period") {			
				$allVals	= array_sum($plotArray);
				$data		.= (!empty($plotArray[$i]) ? $plotArray[$i] : 0) . ',';
				$lineData	.= '"' . $nameArray2[$i] . '",';
				$tooltips	.= '"' . $plotArray2[$i] . '<br /><b>' . $plotArray[$i] . '</b>",';
			}
			else {
				$allVals	= array_sum($plotArray);
				$percent	= ($allVals > 0 ? round($plotArray[$i] / $allVals * 100) : 0) . '%';
				$data		.= '[' . (isset($plotArray[$i]) ? $plotArray[$i] : '') . ',' . (isset($plotArray2[$i]) ? $plotArray2[$i] : '') . '],';
				$lineD		= isset($plotArray[$i]) ? round((isset($plotArray2[$i]) ? $plotArray[$i] - ($plotArray[$i] - $plotArray2[$i]) / 2 : $plotArray[$i])) : '';
				$lineData	.= $lineD . ',';
				$labelsIn	.= '"' . $percent . '",';
				$tooltips	.= '"' . $nameArray[$i] . '<br />' . $percent . '",';
			}
		}
		$data		= substr($data, 0, -1);
		$lineData	= substr($lineData, 0, -1);
		$labelsIn	= substr($labelsIn, 0, -1);
		$tooltips	= substr($tooltips, 0, -1);
			
		// 30 days stats
		if($visits == "visits_period") {
			$barType	= 'Line';
			$cvsH		= 295;
			$key		= $allVals. sprintf(ContentsEngine::replaceStaText(" {s_option:visitsperiod}"), $dataCnt);
		}
		elseif(count($plotArray2) > 0) {
			$key	= implode('","', array('{s_text:pis}','{s_text:univis}'));
		}
		$key	= strip_tags(ContentsEngine::replaceStaText($key));

			
		$output		=	'<canvas id="cvs-visit-stats' . $uniqueID . '" width="' . $cvsW . '" height="' . $cvsH . '">[No canvas support]</canvas>';
		
		$output		.=	'<script>' . "\n" .
						'head.ready("ccInitScript",function(){' . "\n" .
						'head.load(
							{rgraphcore: cc.httpRoot + "/extLibs/RGraph/libraries/RGraph.common.core.js"},
							{rgraphdynamic: cc.httpRoot + "/extLibs/RGraph/libraries/RGraph.common.dynamic.js"},
							{rgraphtooltips: cc.httpRoot + "/extLibs/RGraph/libraries/RGraph.common.tooltips.js"},
							{rgrapheffects: cc.httpRoot + "/extLibs/RGraph/libraries/RGraph.common.effects.js"},
							{rgraphkey: cc.httpRoot + "/extLibs/RGraph/libraries/RGraph.common.key.js"},
							{rgraphbar: cc.httpRoot + "/extLibs/RGraph/libraries/RGraph.bar.js"},
							{rgraphpie: cc.httpRoot + "/extLibs/RGraph/libraries/RGraph.pie.js"},
							{rgraphline: cc.httpRoot + "/extLibs/RGraph/libraries/RGraph.line.js"},
							{rgraphrect: cc.httpRoot + "/extLibs/RGraph/libraries/RGraph.drawing.rect.js"},
							{rgraph' . strtolower($barType) . 'chart: cc.httpRoot + "/system/access/js/get' . $barType . 'Chart.js"},
							function (){';
		
		$output		.=	'$.draw' . $barType . '("cvs-visit-stats' . $uniqueID . '", ['.$data.'], ['.$lineData.'], ["'.$labels.'"], ['.$labelsIn.'], ['.$tooltips.'], ["'.$key.'"]);' . "\n" .
						'$("#cvs-visit-stats' . $uniqueID . '").prev(".loading").remove();' . "\n" .
						'}' .
						');' . "\n" .
						'});' . "\n" .
						'</script>' . "\n";
		
		return $output;
	}
	
	
	/**
	 * Ausgeben eines Browserbenutzungs-Schaubilds
	 * 
	 * @param	string	browserArray
	 * @param	string	totalCount
	 */
	public function printBrowserRGraph($browserArray, $totalCount)
	{
		
		if(in_array("No data", $browserArray))
			return false;
		
		$labels		= implode('","', array_keys($browserArray));
		$data		= "";
		$labelsIn	= "";
		$tooltips	= "";
		$key		= str_ireplace("Firefox", "FF", $labels);
		$uniqueID	= uniqid("-");
		
		$i = 0;
		
		foreach($browserArray as $bData) {
		
			$browser	= $bData['browser'];
			$dataCount	= $bData["count"];
			$versions	= $bData["version"];
			$version	= "<br /><table class='adminTable'><tbody>";
			foreach($versions as $vers) {
				$version	.= '<tr><td>Version </td><td>' . ($vers["v"] != "" ? $vers["v"] : "unknown") . ': </td><td>' . round($vers["c"] / $dataCount * 100, 1) . '%</td></tr>';
			}
			$version	.= "</tbody></table>";
			$data		.= $dataCount . ',';
		
			$percent	= round($dataCount / $totalCount * 100) . '%';
			$labelsIn	.= '"' . $percent . '",';
			$tooltips	.= '"<strong>' . $browser . '<br /><br />' . $percent . '</strong><br />' . $version . '",';
			
			$i++;
		}
		
		$data		= substr($data, 0, -1);
		$labelsIn	= substr($labelsIn, 0, -1);
		$tooltips	= substr($tooltips, 0, -1);
		
		$output		=	'<canvas id="cvs-browser-stats' . $uniqueID . '" width="591" height="380">[No canvas support]</canvas>';
		
		$output		.=	'<script>' . "\n" .
						'head.ready("ccInitScript",function(){' . "\n" .
						'head.load(
							{rgraphcore: cc.httpRoot + "/extLibs/RGraph/libraries/RGraph.common.core.js"},
							{rgraphdynamic: cc.httpRoot + "/extLibs/RGraph/libraries/RGraph.common.dynamic.js"},
							{rgraphtooltips: cc.httpRoot + "/extLibs/RGraph/libraries/RGraph.common.tooltips.js"},
							{rgrapheffects: cc.httpRoot + "/extLibs/RGraph/libraries/RGraph.common.effects.js"},
							{rgraphkey: cc.httpRoot + "/extLibs/RGraph/libraries/RGraph.common.key.js"},
							{rgraphbar: cc.httpRoot + "/extLibs/RGraph/libraries/RGraph.bar.js"},
							{rgraphpie: cc.httpRoot + "/extLibs/RGraph/libraries/RGraph.pie.js"},
							{rgraphline: cc.httpRoot + "/extLibs/RGraph/libraries/RGraph.line.js"},
							{rgraphrect: cc.httpRoot + "/extLibs/RGraph/libraries/RGraph.drawing.rect.js"},
							{rgraphpiechart: cc.httpRoot + "/system/access/js/getPieChart.js"},
							function (){' .
								'$.drawPie("cvs-browser-stats' . $uniqueID . '", ['.$data.'], ["'.$labels.'"], ['.$labelsIn.'], ['.$tooltips.'], ["'.$key.'"]);' . "\n" .
								'$("#cvs-browser-stats' . $uniqueID . '").prev(".loading").remove();' . "\n" .
							'}' .
						');' . "\n" .
						'});' . "\n" .
						'</script>' . "\n";
		
		return $output;
	}
	
	
	/**
	 * Ausgeben eines Clickstatistik-Schaubilds
	 * 
	 * @param	string	Startdatum
	 * @param	string	Enddatum
	 */
	public function printStatsChart($nameArray, $plotArray, $plotArray2, $multiGraph, $visits)
	{
	
		//Diagramm erstellen.
		if($multiGraph && $visits != "visits")
			$graph = new Graph(591,460);
		else
			$graph = new Graph(591,420);
		$lines = new LineProperty();
		
		$graph->SetColor("#333366");
		$graph->SetMarginColor("#333366");
		$graph->SetTitleBackground("#E4EBFA");
	
		//Titel des Diagramms setzen
		$graph->title->Set($title . "\r\n ");
		$graph->title->SetColor("#333366");
		$graph->title->SetMargin(15); 
		
		$graph->title->SetFont(FF_VERA,FS_NORMAL,12);
		
		//Skala setzen
		$graph->SetScale("textlin",0,"auto");	
				
		$graph->SetBackgroundGradient("white", "#F6F8FD", GRAD_HOR);	
		$graph->SetFrame(false);

		//Schriftart einstellen:
		//Für LINUX-Umgebung anzupassen!
		$graph->xaxis->SetFont(FF_VERA,FS_NORMAL,9);
		$graph->xaxis->SetColor("#333366");
        //Skriptnamen auf der X-Achse anzeigen	
		$graph->xaxis->SetTickLabels($nameArray);
		$graph->xaxis->SetPos('min');
		$graph->xaxis->title->Align("center");

		if($visits != "overview" && $visits != "visits")
			//Namen um 45° drehen
			$graph->xaxis->SetLabelAngle(45);
		else
			$graph->xaxis->SetLabelMargin(35);
		//Y-Achsenbezeichnungen setzen
		$graph->yaxis->SetFont(FF_VERA,FS_NORMAL,8);
		$graph->yaxis->SetColor("#333366");
		$graph->yaxis->title->Set($yAxis);
		$graph->yaxis->title->SetColor("#333366");
		$graph->yaxis->title->SetFont(FF_VERA,FS_NORMAL,9);
		$graph->yaxis->SetTitleMargin(38);
		
		// Seitenabstände festlegen
		$graph->img->SetMargin(60,25,100,120); 
		
		// Balkendiagramm generieren
		$p1 = new BarPlot($plotArray);
		
		//Farbe verändern	
		$p1->SetFillGradient("#ff9933", "#FFEEAA", GRAD_HOR);
        //Werte anzeigen lassen
		$p1->value->Show();
        //...in folgendem Format		
        $p1->value->SetFormat('%01.0f');
        $p1->value->SetFont(FF_VERA,FS_NORMAL,8);
		
		if($multiGraph) {
			
			$pl2 = new BarPlot($plotArray2);
		
			// Create the grouped bar plot
			$gbplot = new GroupBarPlot(array($p1,$pl2));
			// ...and add it to the graPH
			$graph->Add($gbplot);

			$pl2->SetFillGradient("#B4BBCA", "#F4FBFF", GRAD_HOR);
			$pl2->value->Show();
			$pl2->value->SetFormat('%01.0f');
	        $pl2->value->SetFont(FF_VERA,FS_NORMAL,8);
			$pl2->SetLegend('unique visitors');
			$p1->SetLegend('unique impressions');
			$graph->legend->SetFont(FF_VERA,FS_NORMAL,9);
			$graph->legend->SetColor("#333366","#E4EBFA");
			$graph->legend->SetFillColor("#ffffff");
			$graph->legend->SetShadow(false);
			$graph->legend->SetAbsPos(25,53,'right','top');
			$graph->legend->SetLineSpacing(10);
			$graph->img->SetMargin(60,25,140,120); 
		}
		else
	        //Graph zum Diagramm hinzufügen
			$graph->Add($p1);

		
		//Diagramm anzeigen
		$graph->Stroke();
	}		
	
	
	/**
	 * Ausgeben eines Browserbenutzungs-Schaubilds
	 * 
	 * @param	string	Startdatum
	 * @param	string	Enddatum
	 */
	public function printBrowserChart($browserArray, $dataArray)
	{		
		
		//Kuchendiagramm mit JPGraph erstellen:
		$graph = new PieGraph(591,400);

		$graph->SetFrame(false);
      
		$graph->SetColor("#333366");
		$graph->SetMarginColor("#333366");
		$graph->SetTitleBackground("#E4EBFA");
	
		$graph->title->SetFont(FF_VERA,FS_NORMAL,11);
		
		// Titel des Diagramms setzen
		$graph->title->Set("verwendete Browser\r\n ");
		$graph->title->SetColor("#333366");
		$graph->title->SetMargin(15); 
       
		//Den "Plot" erstellen.
		$p1 = new PiePlot3D($dataArray);
		//Werte anzeigen
		$p1->value->show = true;
		
		$p1->SetSize(0.4);
		$p1->SetStartAngle(45);

		//Legende anlegen
		$p1->SetLegends($browserArray);
		// Position der Legende festlegen
		$graph->legend->Pos(0.5,0.95,"center");
		// Seitenabstände festlegen
		$graph->legend->SetLayout(LEGEND_HOR);
		$graph->legend->SetFont(FF_VERA,FS_NORMAL,9);
		$graph->legend->SetColor("#333366","#E4EBFA");
		$graph->legend->SetFillColor("#ffffff");
		$graph->legend->SetShadow(false);
		$graph->legend->SetVColMargin(5);
		//"Plot" hinzufügen
		$graph->Add($p1);
		//Diagramm zeichnen		
		$graph->Stroke();
	}	
	
}
