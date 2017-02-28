<?php
namespace Concise;


/**
 * Klasse Stats
 * 
 */

class Stats
{

	public $DB			= null;
	private $lang		= "";
	private $currYear	= "";
	private $logYears	= array();
	private $formAction	= "";
	private $limitOptions	= array(10,25,50,100,250,500);
	private $userGroups = array("admin","editor","author","guest");
	
	/**
	 * Konstruktor der Klasse Stats
	 * 
	 * @access public
	 */
	public function __construct($DB, $lang)
	{
	
		$this->DB	= $DB;
		$this->lang	= $lang;

		// Session-Vars-Array
		$this->g_Session	= Security::getSessionVars();
    	
		if(empty($this->lang))
			$this->lang		= $this->g_Session['lang'];
		
		$this->formAction	= ADMIN_HTTP_ROOT . '?task=stats';
	
	}
	

	/**
	 * Anzeigen der Browser-Statistik
	 * 
	 * @access	public
     * @param   string	$i	Zahl des Statistikmoduls
     * @param   string	$startDate	Startdatum (default = '')
     * @param   string	$endDate	Enddatum (default = '')
     * @return	string
	 */
	public function showUsedBrowserChart($i = 1, $startDate = "", $endDate = "")
	{
		
		require_once PROJECT_DOC_ROOT . "/inc/classes/Logging/class.Chart.php";
		
		return	'<div id="stats5" class="stats"><div class="statDetails">' .
				'<img src="'.SYSTEM_IMAGE_DIR.'/page-spinner.svg" alt="loading" class="loading" />' .
				
				self::printBrowserStatsImage($startDate, $endDate) .
				
				'</div>' . PHP_EOL .
				'{up}<br class="clearfloat" />' . PHP_EOL .
				'</div>';

	}


	/**
	 * Anzeigen der Klick-Statistik
	 * 
	 * @access	public
     * @param   string	$i	Zahl des Statistikmoduls
     * @param   string $statOrder	Sortierung der Datensätze (default = '')
     * @param   string $visits	Art der Statistik (default = pageimp_all)
     * @param   string $statSince	Datum ab dem Statistiken angezeigt werden sollen (default = '')
     * @param   string $statUntil	Datum bis zu dem Statistiken angezeigt werden sollen (default = '')
     * @return	string
	 */
	public function showClickCountChart($i, $statOrder = "", $visits = "pageimp_all", $statSince = "", $statUntil = "", $pagination = true)
	{
	
		require_once PROJECT_DOC_ROOT . "/inc/classes/Logging/class.Chart.php";
			
		$chart		= "";
		$dbFilter	= "";

		// if given as timestamp
		if((string)(int)$statSince !== (string)$statSince)
			$statSince = Modules::getTimestamp($statSince, 0, 0, 0, "."); // Datum als Timestamp
		// if given as timestamp
		if((string)(int)$statUntil !== (string)$statUntil)
			$statUntil = Modules::getTimestamp($statUntil, 23, 59, 59, "."); // Datum als Timestamp
		
		// Relevant log years
		$this->getLogYears($statSince, $statUntil);

		
		// Falls ein Datum zur Begrenzung mitgegeben wurde
		if(is_numeric($statSince) || is_numeric($statUntil)) {
			
			$dbFilter	= " WHERE ";
			$phraseLink	= "";
			
			if(is_numeric($statSince)) { // Startdatum
				$dbFilter .= "`timestamp` >= " . $this->DB->escapeString($statSince);
				$phraseLink	= " AND ";
			}
			if(is_numeric($statUntil)) // Enddatum
				$dbFilter .= $phraseLink . "`timestamp` <= " . $this->DB->escapeString($statUntil);
		}
		
		if(isset($GLOBALS['_GET']['pageNumV']))
			$pageNumV = $GLOBALS['_GET']['pageNumV'];
		else
			$pageNumV = 0;
		
		if(isset($GLOBALS['_GET']['pageNumC']))
			$pageNumC = $GLOBALS['_GET']['pageNumC'];
		else
			$pageNumC = 0;
		
		$maxRows = 10;
		$startRowV = $pageNumV * $maxRows;
		$startRowC = $pageNumC * $maxRows;
		
		
		$sql		= "SELECT count(*) as clicks, `page_id` FROM (";
		$sqlLoop	= "SELECT `page_id` FROM ";
		$sqlLoopC	= $dbFilter;
		
		$sql		.= $this->getLogTabStr($this->logYears, $this->currYear, $sqlLoop, $sqlLoopC);
			
		$sql		.= ") pool ";

		$sql		.= "GROUP BY `page_id` 
						ORDER BY `clicks` DESC 
						";
		
		$result = $this->DB->query($sql);
				
		$totalRowsV = count($result);
		$totalRowsC = $totalRowsV;
		
		
		if($totalRowsC > 0 && $i == 4) {
			// Klick-Statistik
			$chart .=	'<div id="stats4" class="stats"><div class="statDetails">' . 
						'<img src="'.SYSTEM_IMAGE_DIR.'/page-spinner.svg" alt="loading" class="loading" />' .
						
						self::printClickStatsImage($visits, $statSince, $statUntil, $statOrder, $totalRowsC, $pageNumC) .
						
						($pagination && $i != 6 && $i != 7 ? Modules::getPageNav($maxRows, $totalRowsC, $startRowC, $pageNumC, "task=stats&totalRowsV=".$totalRowsV."&pageNumV=".$pageNumV, "C") : '');
										
		}
		elseif($totalRowsC == 0 && $i == 4)
			$chart .=	'<div id="stats4" class="stats"><div class="statDetails"><p class="noStats">{s_text:nostats}</p>';

		
		if($totalRowsV > 0 && $i != 4) {
			// Besuchsstatistik
			$chart .=	'<div id="stats' . $i . '" class="stats"><div class="statDetails">' . 
						'<img src="'.SYSTEM_IMAGE_DIR.'/page-spinner.svg" alt="loading" class="loading" />' .
						
						self::printVisitStatsImage($visits, $statSince, $statUntil, $statOrder, $totalRowsV, $pageNumV) .
						
						($pagination && $i != 6 && $i != 7 ? Modules::getPageNav($maxRows, $totalRowsV, $startRowV, $pageNumV, "task=stats&totalRowsC=".$totalRowsC."&pageNumC=".$pageNumC, "V") : '');
		}
		elseif($totalRowsV == 0 && $i != 4)
			$chart .=	'<div id="stats'. $i . '" class="stats"><div class="statDetails"><p class="noStats">{s_text:nostats}</p>';
		
		$chart .=		'</div>' . PHP_EOL .
						'{up}<br class="clearfloat" />' . PHP_EOL .
						'</div>';
		
		return $chart;

	}



	/**
	 * Session-Counter (Benutzer online)
	 * 
	 * @access	public
     * @param   boolean	$classified	Falls true, werden nur Benutzer der eigenen Gruppe gezählt (default = false)
     * @param   array	$groups	Benutzergruppen (default = array())
     * @return	boolean/string
	 */
	public function usersOnline($classified = false, $groups = array(), $wrapTag = false)
	{
    	
		if($classified
		&& !isset($this->g_Session['group'])
		)
			return false;
		

		$prefix		= '{s_text:curronline}: ';
		$restrict	= "";
		
		if($classified
		&& !empty($groups)
		) {
			$prefix		= '{s_text:grouponline}: ';
			$restrict	.= " AND (";
			foreach($groups as $group) {
				$restrict	.= "`value` LIKE '%group|s:" . strlen($group) . ":\"" . $this->DB->escapeString($group) . "\"%' OR ";
			}
			$restrict	= substr($restrict, 0, -4);
			$restrict	.= ")";
		}
		
		// search for visitors online from sessions
		// remove sessionIDs which cannot be found in recent log table (=> bot(?))
		// include non tracked users
		$query = $this->DB->query("SELECT ses.`id`, COUNT(*) 
										FROM `" . DB_TABLE_PREFIX . "sessions` as ses
										WHERE (ses.`id` IN (SELECT `sessionID` FROM `" . DB_TABLE_PREFIX . "log` as log WHERE log.`timestamp` > DATE_SUB(now(), INTERVAL 1 DAY)) OR ses.`value` LIKE '%userid|s:9%')
										$restrict
										");
		
		if(empty($query)
		|| !is_array($query)
		)
			$usersOnline = 0;
		else
			$usersOnline = $query[0]['COUNT(*)'];

		if(!$wrapTag)
			return $usersOnline;
			
		return '<tr><td>' . $prefix . '</td><td><span class="counter online">' . $usersOnline . '</span></td></tr>' . "\r\n";
			
	}



	/**
	 * Besucherstatistiken allgemein
	 * 
	 * @access	public
     * @param   boolean	$recent	Falls true, werden Statistiken von heute und gestern eingschlossen (default = true)
     * @param   boolean	$shortView	Falls true, wird eine Kurzansicht ausgegeben (default = false)
     * @param   string	$statSince	Anfangsdatum, ab dem Statistiken angezeigt werden sollen (default = '')
     * @param   string	$statUntil	Enddatum, bis zu dem Statistiken angezeigt werden sollen (default = '')
     * @return	string
	 */
	public function getStats($recent = true, $shortView = false, $statSince = "", $statUntil = "")
	{
    				
		$pis			= self::getVisitStats("pageimp_total", $statSince, $statUntil, "page", false);
		$piCount 		= 0;
		$stats			= "";
		$linkOpen		= "";
		$linkClose		= "";
		$previewIcon	= ContentsEngine::getIcon("preview", "inline-icon left");
		
		foreach($pis as $pi) {
			$piCount += (int)$pi['statcount'];
		}
		
		if($shortView) {
			$linkOpen	= '<a href="admin?task=stats&stats&statsince=' . $statSince . '&statuntil=' . $statUntil . '" title="{s_header:stats}">';
			$linkClose	= '</a>';			
		}
		
		if($recent) {
			$today		= self::getVisitStats("today", "", "", "", "", "");
			$yesterday	= self::getVisitStats("yesterday", "", "", "", "", "");
			$vOnline	= $this->usersOnline();
			$uOnline	= $this->usersOnline(true, $this->userGroups);
			
			// Users today
			$stats .= '<tr class="highlight"><td>' . $linkOpen . $previewIcon . '<span class="tableCell">{s_label:visitstoday}</span>' . $linkClose . '</td><td>' . $linkOpen . $today . $linkClose . '</td></tr>';
			// Users yesterday
			$stats .= '<tr class="highlight"><td>' . $linkOpen . $previewIcon . '<span class="tableCell">{s_label:visitsyesterday}</span>' . $linkClose . '</td><td>' . $linkOpen . $yesterday . $linkClose . '</td></tr>';		
			// Visitors/users online
			$stats .= '<tr title="{s_label:visitorsonline} / {s_label:usersonline}"><td>' . $linkOpen . $previewIcon . '<span class="tableCell">{s_label:visitorsonline} ({s_label:usersonline})</span>' . $linkClose . '</td><td><span class="visitorsOnline" title="{s_label:visitorsonline}">' . $vOnline . '</span>(<span class="usersOnline" title="{s_label:usersonline}">' . $uOnline . '</span>)</td></tr>';
		}
		
		if(!$shortView)
			$stats .= '<tr title="{s_title:univis}"><td>' . $previewIcon . '<span class="tableCell">{s_text:univis}</span></td><td>'.self::getVisitStats("unique_visitors", $statSince, $statUntil).'</td></tr>';
		
		$stats .=	'<tr title="{s_title:visits'.($shortView ? 'lastmon' : '').'}"><td>' . $linkOpen . $previewIcon . '<span class="tableCell">{s_text:visits'.($shortView ? 'lastmon' : '').'}</span>' . $linkClose . '</td><td>' . $linkOpen . self::getVisitStats("visits", $statSince, $statUntil) . $linkClose . '</td></tr>';
		
		if(!$shortView) {
			$stats .=	'<tr title="{s_title:unipi}"><td>' . $previewIcon . '<span class="tableCell">{s_text:unipi}</span></td><td>'.self::getVisitStats("unique_pi", $statSince, $statUntil).'</td></tr>';
			$stats .=	'<tr title="{s_title:pis}"><td>' . $previewIcon . '<span class="tableCell">{s_text:pis}</span></td><td>' . $piCount . '</td></tr>';
		}
		
		$return	=	'<div id="stats0" class="stats"><div class="statDetails">' .
					'<table class="stats overview adminTable'.($shortView ? ' shortStats' : '').'">' .
					'<thead><tr><th>{s_header:group}</th><th>{s_header:value}</th></tr></thead>' . 
					'<tbody>' . 
					$stats . 
					'</tbody>'.
					'</table>'.
					'</div>' . PHP_EOL .
					'{up}<br class="clearfloat" />' . PHP_EOL .
					'</div>';
					
		return  $return;

	}



	/**
	 * Erstes Datum für Besucherstatistiken
	 * 
     * @param   string	$statYear	Statistikjahr (default = '')
	 * @access	public
     * @return	string
	 */
	public function getFirstStatDate($statYear = "")
	{
    	
		// Falls Archivjahr
		if($statYear != "" && is_numeric($statYear))
			$tabExt	= "_" . $this->DB->escapeString($statYear);
		else
			$tabExt	= "";
		
		$queryFirstStat = $this->DB->query("SELECT MIN(`timestamp`) 
												FROM `" . DB_TABLE_PREFIX . "log" . $tabExt . "`
											");
		
		return  $queryFirstStat[0]['MIN(`timestamp`)'];

	}



	/**
	 * getLogYears
	 * 
     * @param   array	$startDate	startDate (default = 0)
     * @param   string	$endDate	endDate (default = 0)
	 * @access	public
     * @return	string
	 */
	public function getLogYears($startDate = 0, $endDate = 0)
	{
	
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
		
		$this->currYear	= $currYear;
		$this->logYears	= $logYears;
	
	}
	


	/**
	 * Erstes Datum für Besucherstatistiken
	 * 
     * @param   array	$logYears	Statistikarchivjahre
     * @param   string	$currYear	atkuelles (Statistik)Jahr
     * @param   string	$sqlLoop	Sql-Querystring
     * @param   string	$sqlLoopC	Sql-Querystring condition
	 * @access	public
     * @return	string
	 */
	public function getLogTabStr($logYears, $currYear, $sqlLoop, $sqlLoopC = "")
	{
    	
		$sql	= "";
		$l		= 1;
		
		// Log-Tabelle(n) auslesen
		foreach($logYears as $logYear) {
		
			if($logYear == $currYear)
				$logTab	= "log";
			else
				$logTab = "log_" . $logYear;
			
			$logTab		= $this->DB->escapeString(DB_TABLE_PREFIX . $logTab);
			$tabExists	= true;
			
			// Log-Tabelle für jedes Jahr auf Vorhandensein prüfen und ggf. anlegen
			if($logYear < $currYear
			&& !$this->DB->tableExists($logTab)
			) {
				$o_log		= new Log($this->DB, $this->g_Session);
				$tabExists	= $o_log->createLogArchive($logYear); // creates log year table if data exist
			}
			
			if($tabExists) {
			
				if($l > 1)
					$sql .= " \nUNION All \n";
			
				$sql .= "$sqlLoop $logTab t$l $sqlLoopC";
			}
			
			$l++;
		}
		
		return $sql;
	
	}
	
	
	
	/**
	 * Besucherstatistiken
	 * 
	 * @access	public
     * @param   string	$type		Art der Statistik
     * @param   string	$filter		db-Filterkriterien, z.B. StartDate (default = '')
     * @param   string	$endDate	Enddatum (default = '')
     * @param   string	$orderBy	db-Sortierung (default = page_id)
     * @param   string	$startRow	erste Seite der Datenbankeinträge (default = 0)
     * @param   string	$maxRows	maximale Anzahl an Datenbankeinträgen (default = 10)
     * @param   boolean	$counter	Falls true, wird eine Ausgabe (Counter) für die Webseite generiert (default = false)
     * @param   boolean	$classified	Falls true, werden nur Benutzer der eigenen Gruppe gezählt (default = false)
     * @return	boolean/string
	 */
	public function getVisitStats($type, $filter = "", $endDate = "", $orderBy = "`page_id`", $startRow = 0, $maxRows = 10, $counter = false, $classified = false)
	{
		
		if($classified == true
		&& !isset($this->g_Session['group'])
		)		
			return array();

			
		$stats		= 0;
		$prefix		= "{s_text:visitstot}: ";
		$dbFilter	= "";
		$limit		= "";
		
		if(is_numeric($filter))
			$startDate	= $filter;
		else
			$startDate	= time();
		
		if(!is_numeric($endDate))
			$endDate	= time();
		
		
		// Relevant log years
		$this->getLogYears($startDate, $endDate);
				

		if($startRow !== false)
			$limit = " LIMIT " . $startRow . "," . $maxRows;
		
		switch($filter) {
			
			case "currmon":
				$dbFilter = " WHERE DATE(FROM_UNIXTIME(`timestamp`)) BETWEEN date_format(NOW(), '%Y-%m-01') AND last_day(NOW())";
				break;
							
			case "lastmon":
				$dbFilter = " WHERE DATE(FROM_UNIXTIME(`timestamp`)) BETWEEN date_format(NOW() - INTERVAL 1 MONTH, '%Y-%m-01') AND last_day(NOW() - INTERVAL 1 MONTH)";
				break;
							
			case "2lastmon":
				$dbFilter = " WHERE DATE(FROM_UNIXTIME(`timestamp`)) BETWEEN date_format(NOW() - INTERVAL 2 MONTH, '%Y-%m-01') AND last_day(NOW() - INTERVAL 2 MONTH)";
				break;
		}
		
		
		// Falls ein Datum zur Begrenzung mitgegeben wurde
		if(is_numeric($filter) && is_numeric($endDate)) {
			
			$dbFilter	= " WHERE ";
			$phraseLink	= "";
			
			if(is_numeric($filter)) {
				$dbFilter .= "`timestamp` >= " . $this->DB->escapeString($filter);
				$phraseLink	= " AND ";
			}
			if(is_numeric($endDate)) // Enddatum
				$dbFilter .= $phraseLink . "`timestamp` <= " . $this->DB->escapeString($endDate);
		}
		
		if($type == "unique_pi") { // unique page impressions
		
			$sql		= "SELECT `page_id` FROM (";
			$sqlLoop	= "SELECT `timestamp`, `realIP`, `sessionID`, `page_id` FROM ";
			$sqlLoopC	= $dbFilter;
			
			$sql		.= $this->getLogTabStr($this->logYears, $this->currYear, $sqlLoop, $sqlLoopC);
			
			$sql	.= 	") pool 
						GROUP BY `realIP`, `sessionID`, `page_id`
						";
			
			$query = $this->DB->query($sql);
			#die(HTML::printArray($query));
			
			$stats = count($query);
		}
				
		elseif($type == "visits") { // visits
			
			$sql		= "SELECT `timestamp` FROM (";
			$sqlLoop	= "SELECT `timestamp`, `realIP`, `sessionID` FROM ";
			$sqlLoopC	= "$dbFilter 
							GROUP BY `realIP`, `sessionID`";
			
			$sql		.= $this->getLogTabStr($this->logYears, $this->currYear, $sqlLoop, $sqlLoopC);
			
			$sql	.= 	") pool 
						";
			
			$query = $this->DB->query($sql);
			#die(HTML::printArray($sql));

			$stats = count($query);
		}
		
		elseif($type == "visits_period") {
		
			$sql		= "SELECT COUNT(*), DATE(FROM_UNIXTIME(`timestamp`)) AS period FROM (";
			$sqlLoop	= "SELECT `timestamp`, `realIP`, `sessionID` FROM ";
			$sqlLoopC	= " WHERE `timestamp` BETWEEN $startDate AND $endDate
							GROUP BY `realIP`, `sessionID`
							";
			
			$sql		.= $this->getLogTabStr($this->logYears, $this->currYear, $sqlLoop, $sqlLoopC);

			
			$sql	.= 	") pool 
						GROUP BY period
						ORDER BY period DESC
						";
			
			$query = $this->DB->query($sql);
			#die(HTML::printArray($query));

			
			if(is_array($query)
			&& count($query) > 0
			)
				$stats = $query;

			return  $stats;
		}
		
		elseif($type == "unique_visitors") { // unique visitors
			
			$sql		= "SELECT `timestamp` FROM (";
			$sqlLoop	= "SELECT `timestamp`, `realIP` FROM ";
			$sqlLoopC	= $dbFilter;
			
			$sql		.= $this->getLogTabStr($this->logYears, $this->currYear, $sqlLoop, $sqlLoopC);
			
			$sql	.= 	") pool 
						GROUP BY `realIP`
						";

			$query = $this->DB->query($sql);
			#die(HTML::printArray($sql));
			
			$stats = count($query);
		}
				
		elseif($type == "pageimp" || $type == "unique_pageimp") {
			
			if($type == "pageimp")
				$grouping = "`sessionID`,sub.`page_id`";
			else
				$grouping = "`realIP`,sub.`page_id`";
				
			$stats = $this->DB->query("SELECT main.`page_id`,
												`title_" . $this->DB->escapeString($this->lang) . "` AS page,
													COUNT(*)
											FROM (SELECT COUNT(*),
												 sub.`page_id` 
												 FROM `" . DB_TABLE_PREFIX . "log` AS sub 
												 $dbFilter
												 GROUP BY $grouping) AS main
											LEFT JOIN `" . DB_TABLE_PREFIX . "pages` AS p
											ON main.`page_id` = p.`page_id` 
											GROUP BY `page_id`
											ORDER BY $orderBy
											$limit
											");
			
		}
		
		elseif($type == "pageimp_total") {
			
			$sql		= "SELECT COUNT(*) AS statcount, pid, pages.`title_" . $this->DB->escapeString($this->lang) . "` AS page FROM (";
			$sqlLoop	= "SELECT `timestamp`, `page_id` as pid FROM ";
			$sqlLoopC	= $dbFilter;
			
			$sql		.= $this->getLogTabStr($this->logYears, $this->currYear, $sqlLoop, $sqlLoopC);
			
			$sql	.= 	") pool 
						LEFT JOIN `" . DB_TABLE_PREFIX . "pages` AS pages 
						ON pid = pages.`page_id` 
						GROUP BY pid
						ORDER BY $orderBy 
						$limit
						";

			$stats = $this->DB->query($sql);
			#die(HTML::printArray($stats));
		}
						
		elseif($type == "homepage") {
			
			$query = $this->DB->query("SELECT `visits_total` 
											FROM `" . DB_TABLE_PREFIX . "stats` AS stats 
											LEFT JOIN `" . DB_TABLE_PREFIX . "pages` AS pages 
											ON stats.`page_id` = pages.`page_id` 
											WHERE pages.`index_page` = 1
											");
			
			if(is_array($query)
			&& count($query) > 0
			)
				$stats = $query[0]['visits_total'];
		}
						
		elseif($type == "lastmonth") {
			
			$query = $this->DB->query("SELECT `visits_total` 
											FROM `" . DB_TABLE_PREFIX . "stats` AS stats 
											LEFT JOIN `" . DB_TABLE_PREFIX . "pages` AS pages 
											ON stats.`page_id` = pages.`page_id` 
											WHERE pages.`index_page` = 1
											");
			
			if(is_array($query)
			&& count($query) > 0
			)
				$stats = $query[0]['visits_total'];
		}
						
		else {
			
			if($type == "today") {
				$restrict = " AND DATE(FROM_UNIXTIME(`log`.`timestamp`)) = CURDATE()";
				$prefix = "{s_text:visitstoday}: ";
			}
			elseif($type == "yesterday") {
				$restrict = " AND DATE(FROM_UNIXTIME(`log`.`timestamp`)) = DATE_ADD(CURDATE(), INTERVAL -1 DAY)";
				$prefix = "{s_text:visitsyesterday}: ";
			}
			elseif($type == "total") {
				$restrict = "";
				$prefix = "{s_text:visitorstot}: ";
			}
			if(is_numeric($filter))
				$restrict = " AND `log`.`timestamp` >= " . $this->DB->escapeString($filter);
				
				
			$query = $this->DB->query("SELECT COUNT(*) 
											FROM `" . DB_TABLE_PREFIX . "log` AS log 
											LEFT JOIN `" . DB_TABLE_PREFIX . "pages` AS pages 
											ON log.`page_id` = pages.`page_id` 
											WHERE pages.`page_id` > 0
											$restrict 
											GROUP BY `sessionID`
											");
			
			#if($type == "yesterday")
				#die(var_dump($query));
			
			$stats = count($query);
		}
		
		if($counter === true)
			return  '<tr><td>' . $prefix . '</td><td><span class="counter">' . $stats . '</span></td></tr>' . "\r\n";
		else
			return  $stats;
		
	}



	/**
	 *  Referrer-Statistiken
	 * 
	 * @access	public
     * @param   string	$startDate	Startdatum (default = '')
     * @param   string	$endDate	Enddatum (default = '')
     * @param   string	$sortKey	db-Sortierung (default = '')
     * @param   string	$startRow	erste Seite der Datenbankeinträge (default = 0)
     * @param   string	$maxRows	maximale Anzahl an Datenbankeinträgen (default = 10)
     * @return	boolean/string
	 */
	public function getSearchStats($sortKey = 1, $startDate = "", $endDate = "", $startRow = 0, $maxRows = 20)
	{
    	
		$stats		= 0;
		$prefix		= "{s_text:search}: ";
		$dbFilter 	= "";
		$limit		= "";
		$orderBy	= "";
		$pageNavExt	= "";
		$return		= "";
		$loop		= "";
		$return		= '<div id="stats11" class="stats"><div class="statDetails">';
											

		$limit	= " LIMIT " . $startRow . "," . $maxRows;
			
		switch($sortKey) {
			
			case 1:
				$orderBy = "`search_string` ASC, statcount ASC, `timestamp` DESC";
				$pageNavExt	= "by=name&sort=asc";
				break;
				
			case 2:
				$orderBy = "`search_string` DESC, statcount ASC, `timestamp` DESC";
				$pageNavExt	= "by=name&sort=dsc";
				break;
				
			case 3:
				$orderBy = "statcount ASC, `search_string` ASC, `timestamp` DESC";
				$pageNavExt	= "by=count&sort=asc";
				break;
				
			case 4:
				$orderBy = "statcount DESC, `search_string` ASC, `timestamp` DESC";
				$pageNavExt	= "by=name&count=dsc";
				break;
				
			case 5:
				$orderBy = "`results` ASC, statcount DESC, `search_string` ASC, `timestamp` DESC";
				$pageNavExt	= "by=size&sort=asc";
				break;
				
			case 6:
				$orderBy = "`results` DESC, statcount DESC, `search_string` ASC, `timestamp` DESC";
				$pageNavExt	= "by=size&sort=dsc";
				break;
				
			case 9:
				$orderBy = "`timestamp` DESC, `search_string` ASC";
				$pageNavExt	= "by=date&sort=asc";
				break;
				
			case 10:
				$orderBy = "`timestamp` ASC, `search_string` ASC";
				$pageNavExt	= "by=date&sort=dsc";
				break;
				
			default:
				$orderBy = "statcount DESC, `search_string` ASC, `timestamp` DESC";
				$pageNavExt	= "by=size&sort=asc";
				break;
				
		}
		
		
		// Falls ein Datum zur Begrenzung mitgegeben wurde
		if(is_numeric($startDate) || is_numeric($endDate)) {
			
			if(is_numeric($startDate))
				$dbFilter .= " AND UNIX_TIMESTAMP(`timestamp`) >= " . $this->DB->escapeString($startDate);
			
			if(is_numeric($endDate)) // Enddatum
				$dbFilter .= " AND UNIX_TIMESTAMP(`timestamp`) <= " . $this->DB->escapeString($endDate);
		}
		
		
		// Pagination
		if(isset($GLOBALS['_GET']['pageNumS']))
			$pageNumS = $GLOBALS['_GET']['pageNumS'];
		else
			$pageNumS = 0;
		
		$startRow = $pageNumS * $maxRows;
		
		$limit	= " LIMIT " . $startRow . "," . $maxRows;
			
		$query = $this->DB->query("SELECT *, COUNT(*) AS statcount 
											FROM `" . DB_TABLE_PREFIX . "search_strings` 
										WHERE `search_string` != '' 
											$dbFilter
										GROUP BY `search_string` 
										ORDER BY $orderBy 
										");
		
				
		$totalRowsS = count($query);
		
	
		$query = $this->DB->query("SELECT *, COUNT(*) AS statcount 
											FROM `" . DB_TABLE_PREFIX . "search_strings` 
										WHERE `search_string` != '' 
											$dbFilter
										GROUP BY `search_string` 
										ORDER BY $orderBy 
										$limit
										");
		
		#var_dump($query);
		
		$i = 0;
		
		if(is_array($query)
		&& count($query) > 0
		) {
			
			$searchIco	= ContentsEngine::getIcon("search", "inline-icon left");
			
			foreach($query as $searchStr) {
								
				$loop .=	'<tr' . ($i % 2 ? ' class="alternate"' : '') . ' title="' . $searchStr['search_string'] . '">' .
							'<td>' . $searchIco . '<span class="tableCell">' .
							$searchStr['search_string'] . '</span></td>' .
							'<td>' . $searchStr['statcount'] . '</td>' .
							'<td>' . $searchStr['results'] . '</td>' .
							'<td>' . substr(str_replace(" ", "<br />", $searchStr['timestamp']), 0, -3) . '</td></tr>';
				
				$i++;
			}
			
			$statRoot	= ADMIN_HTTP_ROOT . '?task=stats&';			
			
			$return	.=	'<table class="stats search adminTable">' .
						'<tr>' .
						'<th><a href="' . $statRoot . 'by=name&sort=' . ($sortKey == 1 ? 'dsc' : 'asc') . '" class="ajaxLink">{s_common:newsearch}</a>' .
						'<a href="' . $statRoot . 'by=name&sort=asc" class="ajaxLink' . ($sortKey == 1 ? ' sortActive' : '') . '" title="{s_option:asc}"><span class="sortAsc"></span></a>' .
						'<a href="' . $statRoot . 'by=name&sort=dsc" class="ajaxLink' . ($sortKey == 2 ? ' sortActive' : '') . '" title="{s_option:dsc}"><span class="sortDsc"></span></a>' .
						'</th>' .
						'<th><a href="' . $statRoot . 'by=count&sort=' . ($sortKey == 3 ? 'dsc' : 'asc') . '" class="ajaxLink">{s_header:amount} {s_common:search}</a>' .
						'<a href="' . $statRoot . 'by=count&sort=asc" class="ajaxLink' . ($sortKey == 3 ? ' sortActive' : '') . '" title="{s_option:asc}"><span class="sortAsc"></span></a>' .
						'<a href="' . $statRoot . 'by=count&sort=dsc" class="ajaxLink' . ($sortKey == 4 ? ' sortActive' : '') . '" title="{s_option:dsc}"><span class="sortDsc"></span></a>' .
						'</th>' .
						'<th><a href="' . $statRoot . 'by=size&sort=' . ($sortKey == 5 ? 'dsc' : 'asc') . '" class="ajaxLink">{s_header:amount} {s_header:search}</a>' .
						'<a href="' . $statRoot . 'by=size&sort=asc" class="ajaxLink' . ($sortKey == 5 ? ' sortActive' : '') . '" title="{s_option:asc}"><span class="sortAsc"></span></a>' .
						'<a href="' . $statRoot . 'by=size&sort=dsc" class="ajaxLink' . ($sortKey == 6 ? ' sortActive' : '') . '" title="{s_option:dsc}"><span class="sortDsc"></span></a>' .
						'</th>' .
						'<th><a href="' . $statRoot . 'by=date&sort=' . ($sortKey == 9 ? 'dsc' : 'asc') . '" class="ajaxLink">{s_header:lastAccess}</a>' .
						'<a href="' . $statRoot . 'by=date&sort=asc" class="ajaxLink' . ($sortKey == 9 ? ' sortActive' : '') . '" title="{s_option:asc}"><span class="sortAsc"></span></a>' .
						'<a href="' . $statRoot . 'by=date&sort=dsc" class="ajaxLink' . ($sortKey == 10 ? ' sortActive' : '') . '" title="{s_option:dsc}"><span class="sortDsc"></span></a>' .
						'</th>' .
						'</tr>' . 
						$loop . 
						'</table>';
			
			$return .=	Modules::getPageNav($maxRows, $totalRowsS, $startRow, $pageNumS, "task=stats&" . $pageNavExt . "&totalRowsS=".$totalRowsS."&pageNumS=".$pageNumS, "S", false, Modules::getLimitForm($this->limitOptions, $maxRows, $this->formAction . '&' . $pageNavExt));
		}
		else
			$return .=	"<ul><li>{s_text:nostats}</li></ul>";

		$return .=		'</div>' . PHP_EOL .
						'{up}<br class="clearfloat" />' . PHP_EOL .
						'</div>';

		
		return  $return;
		
	}



	/**
	 *  Downloadstatistiken
	 * 
	 * @access	public
     * @param   string	$type	Art der Statistik
     * @param   string	$startDate	Startdatum (default = '')
     * @param   string	$endDate	Endatum (default = '')
     * @param   string	$sortKey	db-Sortierung (default = 1)
     * @param   string	$startRow	erste Seite der Datenbankeinträge (default = 0)
     * @param   string	$maxRows	maximale Anzahl an Datenbankeinträgen (default = 10)
     * @param   boolean	$counter	Falls true, wird eine Ausgabe (Counter) für die Webseite generiert (default = false)
     * @param   boolean	$classified	Falls true, werden nur Benutzer der eigenen Gruppe gezählt (default = false)
     * @return	boolean/string
	 */
	public function getDownloadStats($type, $sortKey = 1, $startDate = "", $endDate = "", $startRow = 0, $maxRows = 10, $counter = false, $classified = false)
	{
		
		if($classified == true && !isset($this->g_Session['group']))		
			return false;
    	
		$stats		= 0;
		$prefix		= "{s_text:downloadstot}: ";
		$dbFilter	= "";
		$limit		= "";
		$return		= "";
		$loop		= "";
		$orderBy	= "";
		$sortKeyStr	= "filename";
		
		$limit		= " LIMIT " . $startRow . "," . $maxRows;
			
		switch($sortKey) {
				
			default;
			case 1;
				$orderBy = "filename ASC";
				break;
				
			case 2:
				$orderBy = "filename DESC";
				break;
			
			case 3;
				$orderBy = "downloads ASC, filename ASC";
				$sortKeyStr	= "downloads";
				break;
				
			case 4;
				$orderBy = "downloads DESC, filename ASC";
				$sortKeyStr	= "downloads";
				break;
			
			case 5;
				$orderBy = "downloads ASC, filename ASC";
				$sortKeyStr	= "filesize";
				break;
				
			case 6;
				$orderBy = "downloads DESC, filename ASC";
				$sortKeyStr	= "filesize";
				break;
				
			case 7;
			case 9;
				$orderBy = "`last_access` ASC, filename ASC";
				$sortKeyStr	= "last_access";
				break;
				
			case 8;
			case 10;
				$orderBy = "`last_access` DESC, filename ASC";
				$sortKeyStr	= "last_access";
				break;				
		}

		
		$query = $this->DB->query("SELECT * 
										FROM `" . DB_TABLE_PREFIX . "download` 
										$dbFilter 
										ORDER BY $orderBy
										");
		
		#var_dump($query);
		
		
		$docFiles = array();
		$dwnlPath = PROJECT_DOC_ROOT . '/' . CC_DOC_FOLDER;
		$fileNum = 0;		
		
		if(is_dir($dwnlPath)) {
			
			$handle = opendir($dwnlPath);
			$i = 0;
			
			while($content = readdir($handle)) {
				
				if( $content != ".." && 
					strpos($content, ".") !== 0
				) {
					
					$fileNum++;
					
					$downloads	= 0;
					$lastAccess = 0;
					$docIcon 	= "pdf.png";
					
					$fileSize	= (int) max(1, round(filesize($dwnlPath . '/' . $content)/1024));
					$fileExt 	= strtolower(substr($content, strrpos($content,'.')+1, strlen($content)-1)); // Bestimmen der Dateinamenerweiterung
					
					if($fileExt == 'zip' )
						$docIcon = "zip.png";
					elseif($fileExt == 'pdf' )
						$docIcon = "pdf.png";
					elseif($fileExt == 'doc' || $fileExt == 'docx')
						$docIcon = "doc.png";
					else
						$docIcon = "icon_file.png";
					
					$q	= 0;
					$entryFound = false;
					
					foreach($query as $dwnlStat) {
						
						if($dwnlStat['filename'] == $content) {
							$entryFound = true;
							$query[$q]['filesize']	= $fileSize;
							$dwnlStat['filesize']	= $fileSize;
							$downloads	= $dwnlStat['downloads'];
							$lastAccess	= $dwnlStat['last_access'];
							$sortKeyDue	= $dwnlStat[$sortKeyStr];
						}
						$q++;
					}
					if(!$entryFound) {
						$dwnlStat	= array();
						$dwnlStat['filename']	= $content;
						$dwnlStat['filesize']	= $fileSize;
						$dwnlStat['downloads']	= $downloads;
						$dwnlStat['last_access']	= $lastAccess;
						$sortKeyDue	= $dwnlStat[$sortKeyStr];
					}
					$docFiles[$content] =	$sortKeyDue . ":::" .
											$docIcon .  ":::" .
											$content . ":::" .
											$downloads . ":::" .
											$fileSize . ":::" .
											$lastAccess;
					
				}
				
				$i++;
			}
			closedir($handle);
		}
		
		// Sortierung nach Schlüssel vornehmen
		natsort($docFiles);
		
		// Sortierung ggf.revers
		if(!($sortKey % 2))
			$docFiles = array_reverse($docFiles);
		
		
		$i = 0;
		
		foreach($docFiles as $file) {
			
			$fileArray = explode(":::", $file);
			
			array_shift($fileArray);
				
			$loop .=	'<tr' . ($i % 2 ? ' class="alternate"' : '') . '>' .
						'<td><img src="' .  SYSTEM_IMAGE_DIR . '/' . $fileArray[0] . '" alt="" /> ' .
						wordwrap($fileArray[1], 25, "\n", true) . '</td>' .
						'<td>' . $fileArray[2] . '</td>' .
						'<td>' . $fileArray[3] . ' kB</td>' .
						'<td>' . substr(str_replace(" ", "<br />", $fileArray[4]), 0, -3) . '</td></tr>';
			
			$i++;
		}
		
		$return	=	'<div id="stats6" class="stats"><div class="statDetails">';
		
		if($fileNum > 0) {
			
			$statRoot	= ADMIN_HTTP_ROOT . '?task=stats&';
			
			$return	.=	'<table class="stats download adminTable">' .
						'<tr>' .
						'<th><a href="' . $statRoot . 'by=name&sort=' . ($sortKey == 1 ? 'dsc' : 'asc') . '" class="ajaxLink">{s_header:filename}</a>' .
						'<a href="' . $statRoot . 'by=name&sort=asc" class="ajaxLink' . ($sortKey == 1 ? ' sortActive' : '') . '" title="{s_option:asc}"><span class="sortAsc"></span></a>' .
						'<a href="' . $statRoot . 'by=name&sort=dsc" class="ajaxLink' . ($sortKey == 2 ? ' sortActive' : '') . '" title="{s_option:dsc}"><span class="sortDsc"></span></a>' .
						'</th>' .
						'<th><a href="' . $statRoot . 'by=count&sort=' . ($sortKey == 3 ? 'dsc' : 'asc') . '" class="ajaxLink">{s_header:downloads}</a>' .
						'<a href="' . $statRoot . 'by=count&sort=asc" class="ajaxLink' . ($sortKey == 3 ? ' sortActive' : '') . '" title="{s_option:asc}"><span class="sortAsc"></span></a>' .
						'<a href="' . $statRoot . 'by=count&sort=dsc" class="ajaxLink' . ($sortKey == 4 ? ' sortActive' : '') . '" title="{s_option:dsc}"><span class="sortDsc"></span></a>' .
						'</th>' .
						'<th><a href="' . $statRoot . 'by=size&sort=' . ($sortKey == 5 ? 'dsc' : 'asc') . '" class="ajaxLink">{s_header:filesize}</a>' .
						'<a href="' . $statRoot . 'by=size&sort=asc" class="ajaxLink' . ($sortKey == 5 ? ' sortActive' : '') . '" title="{s_option:asc}"><span class="sortAsc"></span></a>' .
						'<a href="' . $statRoot . 'by=size&sort=dsc" class="ajaxLink' . ($sortKey == 6 ? ' sortActive' : '') . '" title="{s_option:dsc}"><span class="sortDsc"></span></a>' .
						'</th>' .
						'<th><a href="' . $statRoot . 'by=date&sort=' . ($sortKey == 7 || $sortKey == 9 ? 'dsc' : 'asc') . '" class="ajaxLink">{s_header:lastAccess}</a>' .
						'<a href="' . $statRoot . 'by=date&sort=asc" class="ajaxLink' . ($sortKey == 7 || $sortKey == 9 ? ' sortActive' : '') . '" title="{s_option:asc}"><span class="sortAsc"></span></a>' .
						'<a href="' . $statRoot . 'by=date&sort=dsc" class="ajaxLink' . ($sortKey == 8 || $sortKey == 10 ? ' sortActive' : '') . '" title="{s_option:dsc}"><span class="sortDsc"></span></a>' .
						'</th>' .
						'</tr>' . 
						$loop . 
						'</table>';
						
		}
		else
			$return .=	'<ul><li>{s_text:nodocs}</li></ul>';

		$return .=		'</div>' . PHP_EOL .
						'{up}<br class="clearfloat" />' . PHP_EOL .
						'</div>';

		
		return  $return;
		
	}



	/**
	 *  Referrer-Statistiken
	 * 
	 * @access	public
     * @param   string	$type		Art der Statistik ("external" => externe Verweisseiten, "internal" => interne Verweisseiten)
     * @param   string	$startDate	Startdatum (default = '')
     * @param   string	$endDate	Enddatum (default = '')
     * @param   string	$sortKey	db-Sortierung (default = '')
     * @param   string	$startRow	erste Seite der Datenbankeinträge (default = 0)
     * @param   string	$maxRows	maximale Anzahl an Datenbankeinträgen (default = 10)
     * @param   boolean	$counter	Falls true, wird eine Ausgabe (Counter) für die Webseite generiert (default = false)
     * @param   boolean	$classified	Falls true, werden nur Benutzer der eigenen Gruppe gezählt (default = false)
     * @return	boolean/string
	 */
	public function getRefererStats($type, $sortKey = 1, $startDate = "", $endDate = "", $startRow = 0, $maxRows = 20, $counter = false, $classified = false)
	{
 		
		if($classified == true && !isset($this->g_Session['group']))
		
			return false;
   	
		$stats			= 0;
		$prefix			= "{s_text:referrertot}: ";
		$dbFilter 		= "";
		$limit			= "";
		$orderBy		= "";
		$pageNavExt		= "";
		$return			= "";
		$loop			= "";
		
		
		// Relevant log years
		$this->getLogYears($startDate, $endDate);

		
		$return			= '<div id="stats8" class="stats"><div class="statDetails">';
		$httpRoot		= str_ireplace("http://www.", "", PROJECT_HTTP_ROOT); // url mit www
		$httpRoot		= str_ireplace("http://", "", $httpRoot); // url ohne www
		$httpRoot		= str_ireplace("https://www.", "", $httpRoot); // url mit www
		$httpRoot		= str_ireplace("https://", "", $httpRoot); // url ohne www
		$httpRootWOW	= 'http://'.$httpRoot;
		$httpRootWWW	= 'http://www.'.$httpRoot;
		$httpsRootWOW	= 'https://'.$httpRoot;
		$httpsRootWWW	= 'https://www.'.$httpRoot;

		$filterIntern	= " AND (`referer` " . ($type == 'internal' ? "" : "NOT ") . "LIKE '".$this->DB->escapeString($httpRootWOW)."%' " .
							($type == 'internal' ? "OR" : "AND") . " `referer` " . ($type == 'internal' ? "" : "NOT ") . "LIKE '".$this->DB->escapeString($httpRootWWW)."%' " .
							($type == 'internal' ? "OR" : "AND") . " `referer` " . ($type == 'internal' ? "" : "NOT ") . "LIKE '".$this->DB->escapeString($httpsRootWOW)."%' " .
							($type == 'internal' ? "OR" : "AND") . " `referer` " . ($type == 'internal' ? "" : "NOT ") . "LIKE '".$this->DB->escapeString($httpsRootWWW)."%') ";
											

		
		switch($sortKey) {
			
			case 1:
				$orderBy = "`referer` ASC";
				$pageNavExt	= "by=name&sort=asc";
				break;
				
			case 2:
				$orderBy = "`referer` DESC";
				$pageNavExt	= "by=name&sort=dsc";
				break;
				
			case 3;
			case 5;
				$orderBy = "statcount ASC, `referer` ASC";
				$pageNavExt	= "by=count&sort=asc";
				break;
				
			case 9:
				$orderBy = "`timestamp` DESC, statcount ASC, `referer` ASC";
				$pageNavExt	= "by=date&sort=asc";
				break;
				
			case 10:
				$orderBy = "`timestamp` ASC, statcount ASC, `referer` ASC";
				$pageNavExt	= "by=date&sort=dsc";
				break;
				
			default;
				$orderBy = "statcount DESC, `referer` ASC";
				$pageNavExt	= "by=count&sort=dsc";
				break;
				
		}
		
		
		// Falls ein Datum zur Begrenzung mitgegeben wurde
		if(is_numeric($startDate) || is_numeric($endDate)) {
			
			if(is_numeric($startDate))
				$dbFilter .= " AND `timestamp` >= " . $this->DB->escapeString($startDate);
			
			if(is_numeric($endDate)) // Enddatum
				$dbFilter .= " AND `timestamp` <= " . $this->DB->escapeString($endDate);
		}
		
		
		
		// Pagination
		if(isset($GLOBALS['_GET']['pageNumRef']))
			$pageNumRef = $GLOBALS['_GET']['pageNumRef'];
		else
			$pageNumRef = 0;
		
		$startRow = $pageNumRef * $maxRows;
		
		$limit		= " LIMIT " . $startRow . "," . $maxRows;
		
		
		$sql		= "SELECT COUNT(*) AS statcount, `referer`, `timestamp` FROM (";
		$sqlLoop	= "SELECT `timestamp`, `referer` FROM ";
		$sqlLoopC	= " WHERE `referer` != '' 
						$filterIntern 
						$dbFilter";
		
		$sql		.= $this->getLogTabStr($this->logYears, $this->currYear, $sqlLoop, $sqlLoopC);
		
		$sql	.= 	") pool 
					GROUP BY `referer`
					ORDER BY $orderBy 
					";
		
	    $query = $this->DB->query($sql);
		#die(HTML::printArray($sql ));
		
		$totalRowsRef = count($query);
		
	    $query = $this->DB->query($sql . $limit);
		
				
		$i = 0;
		
		if(is_array($query)
		&& count($query) > 0
		) {
			
			$urlIco	= ContentsEngine::getIcon("url", "inline-icon left");
				
			foreach($query as $referer) {
								
				$loop .=	'<tr' . ($i % 2 ? ' class="alternate"' : '') . ' title="' . $referer['referer'] . '">' .
							'<td>' . $urlIco . '<span class="tableCell">' .
							substr($referer['referer'], 0, 100) . (strlen($referer['referer']) > 100 ? ' (...)' : '') . '</span></td>' .
							'<td>' . $referer['statcount'] . '</td>' .
							'<td>' . date("Y-m-d", $referer['timestamp']) . '<br />' . date("H:i", $referer['timestamp']) . '</td></tr>';
				
				$i++;
			}
			
			$statRoot	= ADMIN_HTTP_ROOT . '?task=stats&';			
			
			$return	.=	'<table class="stats referer adminTable">' .
						'<tr><th><a href="' . $statRoot . 'by=name&sort=' . ($sortKey == 1 ? 'dsc' : 'asc') . '" class="ajaxLink">{s_header:url}</a>' .
						'<a href="' . $statRoot . 'by=name&sort=asc" class="ajaxLink' . ($sortKey == 1 ? ' sortActive' : '') . '" title="{s_option:asc}"><span class="sortAsc"></span></a>' .
						'<a href="' . $statRoot . 'by=name&sort=dsc" class="ajaxLink' . ($sortKey == 2 ? ' sortActive' : '') . '" title="{s_option:dsc}"><span class="sortDsc"></span></a>' .
						'</th>' .
						'<th><a href="' . $statRoot . 'by=size&sort=' . ($sortKey == 3 || $sortKey == 5 ? 'dsc' : 'asc') . '" class="ajaxLink">{s_header:amount}</a>' .
						'<a href="' . $statRoot . 'by=size&sort=asc" class="ajaxLink' . ($sortKey == 3 || $sortKey == 5 ? ' sortActive' : '') . '" title="{s_option:asc}"><span class="sortAsc"></span></a>' .
						'<a href="' . $statRoot . 'by=size&sort=dsc" class="ajaxLink' . ($sortKey == 4 || ($sortKey > 5 && $sortKey < 9) ? ' sortActive' : '') . '" title="{s_option:dsc}"><span class="sortDsc"></span></a>' .
						'</th>' .
						'<th><a href="' . $statRoot . 'by=date&sort=' . ($sortKey == 9 ? 'dsc' : 'asc') . '" class="ajaxLink">{s_header:lastAccess}</a>' .
						'<a href="' . $statRoot . 'by=date&sort=asc" class="ajaxLink' . ($sortKey == 9 ? ' sortActive' : '') . '" title="{s_option:asc}"><span class="sortAsc"></span></a>' .
						'<a href="' . $statRoot . 'by=date&sort=dsc" class="ajaxLink' . ($sortKey == 10 ? ' sortActive' : '') . '" title="{s_option:dsc}"><span class="sortDsc"></span></a>' .
						'</th>' .
						'</tr>' . 
						$loop . 
						'</table>';
			
			$return .=	Modules::getPageNav($maxRows, $totalRowsRef, $startRow, $pageNumRef, "task=stats&" . $pageNavExt . "&totalRowsRef=".$totalRowsRef."&pageNumRef=".$pageNumRef, "Ref", false, Modules::getLimitForm($this->limitOptions, $maxRows, $this->formAction . '&' . $pageNavExt));
		}
		else
			$return .=	"<ul><li>{s_text:nostats}</li></ul>";

		$return .=		'</div>' . PHP_EOL .
						'{up}<br class="clearfloat" />' . PHP_EOL .
						'</div>';

		
		return  $return;
		
	}



	/**
	 *  printClickStatsImage
	 * 
	 * @access	public
     * @param   string	$visits		ID-Suffix (default = '')
     * @param   string	$statSince	Startdatum (default = '')
     * @param   string	$statUntil	Enddatum (default = '')
     * @param   string	$statOrder	db-Sortierung (default = '')
     * @param   string	$totalRows	erste Seite der Datenbankeinträge (default = 0)
     * @param   string	$pageNum	maximale Anzahl an Datenbankeinträgen (default = 10)
     * @return	string
	 */
	public function printClickStatsImage($visits, $statSince, $statUntil, $statOrder, $totalRows, $pageNum)
	{
	
		// Falls alte Version
		if(version_compare(CWMS_VERSION, 2.5, "<")) {
			$forceLoad	= "&load=".time();
			return "<img class='stats' style='display:none' src='".SYSTEM_HTTP_ROOT."/access/getLogChart.php?chart=clickChart&startdate=".$statSince."&enddate=".$statUntil."&order=$statOrder&totalRows=".$totalRows."&pageNum=".$pageNum.$forceLoad."' width=591 height=420 />";
		}
		
		
		// Falls Diagramme mit RGraph 
		$chart = new Chart($this->DB, $this->lang);
		$chart->rGraph	= true;
		$clickChart		= $chart->buildClickChart($pageNum, $totalRows, $statOrder, $visits, $statSince, $statUntil);
		
		return $clickChart;
	}



	/**
	 *  printVisitStatsImage
	 * 
	 * @access	public
     * @param   string	$visits		Visits / ID-Suffix
     * @param   string	$statSince	Startdatum (default = '')
     * @param   string	$statUntil	Enddatum (default = '')
     * @param   string	$statOrder	db-Sortierung (default = '')
     * @param   string	$totalRows	erste Seite der Datenbankeinträge (default = 0)
     * @param   string	$pageNum	maximale Anzahl an Datenbankeinträgen (default = 10)
    * @return	string
	 */
	public function printVisitStatsImage($visits, $statSince, $statUntil, $statOrder, $totalRows, $pageNum)
	{
	
		// Falls alte Version
		if(version_compare(CWMS_VERSION, 2.5, "<")) {
			$forceLoad	= "&load=".time();
			return "<img class='stats' style='display:none' src='".SYSTEM_HTTP_ROOT."/access/getLogChart.php?chart=clickChart&startdate=".$statSince."&enddate=".$statUntil."&order=$statOrder&visits=$visits&totalRows=".$totalRows."&pageNum=".$pageNum.$forceLoad."' width=591 height=" . ($visits == "visits" ? 420 : 460) . " />";
		}
		
		// Falls Diagramme mit RGraph 
		$chart = new Chart($this->DB, $this->lang);
		$chart->rGraph	= true;
		$clickChart		= $chart->buildClickChart($pageNum, $totalRows, $statOrder, $visits, $statSince, $statUntil);
		
		return $clickChart;
	}



	/**
	 *  printBrowserStatsImage
	 * 
	 * @access	public
     * @param   string	$visits		Visits
     * @param   string	$statSince	Startdatum (default = '')
     * @param   string	$statUntil	Enddatum (default = '')
     * @param   string	$statOrder	db-Sortierung (default = '')
     * @param   string	$totalRowsC	erste Seite der Datenbankeinträge (default = 0)
     * @param   string	$pageNumC	maximale Anzahl an Datenbankeinträgen (default = 10)
     * @return	string
	 */
	public function printBrowserStatsImage($startDate, $endDate)
	{
	
		// Falls alte Version
		if(version_compare(CWMS_VERSION, 2.5, "<")) {
			$forceLoad	= "&load=".time();
			return '<img class="stats" src="'.SYSTEM_HTTP_ROOT.'/access/getLogChart.php?chart=browserChart&startdate='.$startDate.'&enddate='.$endDate.$forceLoad.'" style="display:none" width=591 height=420 />';
		}		
		
		// Falls Diagramme mit RGraph 
		$chart = new Chart($this->DB, $this->lang);
		$chart->rGraph	= true;
		$browserChart	= $chart->buildBrowserChart($startDate, $endDate);
		
		return $browserChart;

	}



	/**
	 *  Logs potentieller Robots
	 * 
	 * @access	public
     * @param   string	$type	Art der Statistik
     * @param   string	$startDate	Startdatum (default = '')
     * @param   string	$endDate	Enddatum (default = '')
     * @param   string	$sortKey	db-Sortierung (default = '')
     * @param   string	$startRow	erste Seite der Datenbankeinträge (default = 0)
     * @param   string	$maxRows	maximale Anzahl an Datenbankeinträgen (default = 10)
     * @param   boolean	$counter	Falls true, wird eine Ausgabe (Counter) für die Webseite generiert (default = false)
     * @param   boolean	$classified	Falls true, werden nur Benutzer der eigenen Gruppe gezählt (default = false)
     * @return	boolean/string
	 */
	public function getBotLogs($type, $sortKey = "", $startDate = "", $endDate = "", $startRow = 0, $maxRows = 20, $counter = false, $classified = false)
	{
    	
		$stats		= 0;
		$prefix		= "{s_text:referrertot}: ";
		$dbFilter 	= "";
		$limit		= "";
		$orderBy	= "";
		$pageNavExt	= "";
		$return		= '<div id="stats9" class="stats"><div class="statDetails">';
		$loop		= "";
		$httpRoot	= str_replace("http://www.", "", PROJECT_HTTP_ROOT); // url mit www
		$httpRoot	= str_replace("http://", "", $httpRoot); // url ohne www
		$httpRootWOW	= "http://".$httpRoot;
		$httpRootWWW	= "http://www.".$httpRoot;
		
		switch($sortKey) {
				
			case 1:
				$orderBy = "`userAgent` ASC, `timestamp` DESC";
				$pageNavExt	= "by=name&sort=asc";
				break;
				
			case 2:
				$orderBy = "`userAgent` DESC, `timestamp` DESC";
				$pageNavExt	= "by=name&sort=dsc";
				break;
				
			case 3;
				$orderBy = "logcount ASC, `userAgent` ASC, `timestamp` DESC";
				$pageNavExt	= "by=count&sort=asc";
				break;
				
			case 4;
				$orderBy = "logcount DESC, `userAgent` ASC, `timestamp` DESC";
				$pageNavExt	= "by=count&sort=dsc";
				break;
				
			case 5;
				$orderBy = "`referer` ASC, logcount ASC, `userAgent` ASC, `timestamp` DESC";
				$pageNavExt	= "by=referer&sort=asc";
				break;
				
			case 6;
				$orderBy = "`referer` DESC, logcount ASC,  `userAgent` ASC, `timestamp` DESC";
				$pageNavExt	= "by=referer&sort=dsc";
				break;
				
			case 7:
				$orderBy = "realip ASC, `userAgent` ASC, `timestamp` DESC";
				$pageNavExt	= "by=ip&sort=asc";
				break;
				
			case 8:
				$orderBy = "realip DESC, `userAgent` ASC, `timestamp` DESC";
				$pageNavExt	= "by=ip&sort=dsc";
				break;
				
			case 9:
				$orderBy = "`timestamp` ASC, `userAgent` ASC, realip DESC";
				$pageNavExt	= "by=date&sort=asc";
				break;
				
			case 10:
				$orderBy = "`timestamp` DESC, `userAgent` ASC, realip DESC";
				$pageNavExt	= "by=date&sort=dsc";
				break;
			
			default:
				$orderBy = "logcount DESC, `userAgent` ASC, `timestamp` DESC";
				$pageNavExt	= "by=count&sort=dsc";
				break;
		}
		
		
		// Falls ein Datum zur Begrenzung mitgegeben wurde
		if(is_numeric($startDate) || is_numeric($endDate)) {
			
			if(is_numeric($startDate)) // Startdatum
				$dbFilter .= " AND `timestamp` >= " . $this->DB->escapeString($startDate);
				
			if(is_numeric($endDate)) // Enddatum
				$dbFilter .= " AND `timestamp` <= " . $this->DB->escapeString($endDate);
		}

			
		if($classified == true && !isset($this->g_Session['group']))
		
			return false;
		
		
		// Pagination
		if(isset($GLOBALS['_GET']['pageNumLB']))
			$pageNumLB = $GLOBALS['_GET']['pageNumLB'];
		else
			$pageNumLB = 0;
		
		$startRow		= $pageNumLB * $maxRows;
		
		$limit			= " LIMIT " . $startRow . "," . $maxRows;
		
			
		$query = $this->DB->query("SELECT COUNT(*) AS logcount, `userAgent`, `realIP`, `referer`, `timestamp` 
										FROM `" . DB_TABLE_PREFIX . "log_bots`
									WHERE `userAgent` != '' 
									$dbFilter
									GROUP BY `realIP`
									ORDER BY $orderBy 
									");
		
				
		$totalRowsLB = count($query);
		
	
		$query = $this->DB->query("SELECT COUNT(*) AS logcount, `userAgent`, `realIP`, `referer`, `timestamp` 
										FROM `" . DB_TABLE_PREFIX . "log_bots`
									WHERE `userAgent` != '' 
									$dbFilter
									GROUP BY `realIP`
									ORDER BY $orderBy 
									$limit
									");
		
		#var_dump($query);
				
		$i = 0;
		
		if(is_array($query)
		&& count($query) > 0
		) {
			
			$urlIco	= ContentsEngine::getIcon("url", "inline-icon left");
			
			foreach($query as $botData) {
				
				$realIP		= User::anonymizeIP($botData['realIP'], ANONYMIZE_IP_BYTES);
				$isBot		= Log::checkBot($botData['userAgent']) || strpos($botData['userAgent'], "curl/") === 0 || strlen($botData['userAgent']) <= 50;

				
				$loop .=	'<tr class="cc-table-row' . ($i % 2 ? ' alternate' : '') . ($isBot % 2 ? ' {t_class:bgwarn}' : '') . '" title="' . ($isBot % 2 ? 'Bot: ' : '') . $botData['userAgent'] . '">' .
							'<td class="markBox-cell">' . 
							'<label class="markBox">' . 
							'<input type="checkbox" name="botNr[' . $i . ']" class="addVal" />' .
							'<input type="hidden" name="botIP[' . $i . ']" value="' . $realIP . '" class="getVal" />' .
							'</label>' .
							'</td>' .
							'<td class="userAgent" title="' . htmlspecialchars($botData['userAgent']) . '">' . $urlIco . ' ' .
							htmlspecialchars(substr($botData['userAgent'], 0, 100) . (strlen($botData['userAgent']) > 100 ? ' (...)' : '')) . '</td>' .
							'<td>' .
							'<span class="botIP">' . htmlspecialchars($botData['realIP']) . '</span>' .
							'</td>' .
							'<td class="referer" title="' . htmlspecialchars($botData['referer']) . '">' .
							htmlspecialchars($botData['referer']) .
							'</td>' .
							'<td>' . htmlspecialchars($botData['logcount']) . '</td>' .
							'<td>' . date("Y-m-d", $botData['timestamp']) . '<br />' . date("H:i", $botData['timestamp']) . '</td>' .
							'<td class="editButtons-cell">' .
							'<span class="editButtons-panel">';

				// Button fetch
				$btnDefs	= array(	"type"		=> "button",
										"class"		=> "delcon validIP button-icon-only",
										"value"		=> "",
										"title"		=> "{s_title:validip}",
										"attr"		=> 'data-action="delete" data-url="' . SYSTEM_HTTP_ROOT . '/access/editModules.php?page=admin&action=delip&ip=' . $realIP . '&valid=1"',
										"icon"		=> "fetch"
									);
				
				$loop .=	ContentsEngine::getButton($btnDefs);
				
				
				// Button delete
				$btnDefs	= array(	"type"		=> "button",
										"class"		=> "delcon delIP button-icon-only",
										"value"		=> "",
										"title"		=> "{s_title:delip}",
										"attr"		=> 'data-action="delete" data-url="' . SYSTEM_HTTP_ROOT . '/access/editModules.php?page=admin&action=delip&ip=' . $realIP . '"',
										"icon"		=> "delete"
									);
				
				$loop .=	ContentsEngine::getButton($btnDefs);
				
				$loop .=	'</span>' .
							'</td>' .
							'</tr>' . "\r\n";
				
				$i++;
			}
			
			$statRoot	= ADMIN_HTTP_ROOT . '?task=stats&';			
				
			$return	.=	'<form action="' . SYSTEM_HTTP_ROOT . '/access/editModules.php?page=admin&action=delip&ip=array&' . $pageNavExt . '" method="post" data-history="false">' .
						'<div class="actionBox">' .
						'<label class="markAll markBox"><input type="checkbox" id="markAllLB" data-select="all" /></label>' .
						'<label for="markAllLB" class="markAllLB">{s_label:mark}</label>' .
						'<span class="editButtons-panel">';
				
			// Button delAll
			$btnDefs	= array(	"type"		=> "button",
									"class"		=> "delAll delIP logBots validIP button-icon-only",
									"value"		=> "",
									"title"		=> "{s_title:pubmarked}",
									"attr"		=> 'data-action="delmultiple"',
									"icon"		=> "fetch"
								);
			
			$return	.=	ContentsEngine::getButton($btnDefs);
				
			// Button delAll
			$btnDefs	= array(	"type"		=> "button",
									"class"		=> "delAll delIP logBots button-icon-only",
									"value"		=> "",
									"title"		=> "{s_title:delmarked}",
									"attr"		=> 'data-action="delmultiple"',
									"icon"		=> "delete"
								);
			
			$return	.=	ContentsEngine::getButton($btnDefs);
				
			$return	.=	'</span>' .
						'</div>' .
						'<table class="stats botList adminTable">' .
						'<tr>' .
						'<th>&nbsp;</th>' .
						'<th class="userAgent"><a href="' . $statRoot . 'by=name&sort=' . ($sortKey == 1 ? 'dsc' : 'asc') . '" class="ajaxLink">{s_header:useragent}</a>' .
						'<a href="' . $statRoot . 'by=name&sort=asc" class="ajaxLink' . ($sortKey == 1 ? ' sortActive' : '') . '" title="{s_option:asc}"><span class="sortAsc"></span></a>' .
						'<a href="' . $statRoot . 'by=name&sort=dsc" class="ajaxLink' . ($sortKey == 2 ? ' sortActive' : '') . '" title="{s_option:dsc}"><span class="sortDsc"></span></a>' .
						'</th>' .
						'<th class="realIP"><a href="' . $statRoot . 'by=ip&sort=' . ($sortKey == 7 ? 'dsc' : 'asc') . '" class="ajaxLink">Bot-IP</a>' .
						'<a href="' . $statRoot . 'by=ip&sort=asc" class="ajaxLink' . ($sortKey == 7 ? ' sortActive' : '') . '" title="{s_option:asc}"><span class="sortAsc"></span></a>' .
						'<a href="' . $statRoot . 'by=ip&sort=dsc" class="ajaxLink' . ($sortKey == 8 ? ' sortActive' : '') . '" title="{s_option:dsc}"><span class="sortDsc"></span></a>' .
						'</th>' .
						'<th class="referer"><a href="' . $statRoot . 'by=referer&sort=' . ($sortKey == 5 ? 'dsc' : 'asc') . '" class="ajaxLink">Referer</a>' .
						'<a href="' . $statRoot . 'by=referer&sort=asc" class="ajaxLink' . ($sortKey == 5 ? ' sortActive' : '') . '" title="{s_option:asc}"><span class="sortAsc"></span></a>' .
						'<a href="' . $statRoot . 'by=referer&sort=dsc" class="ajaxLink' . ($sortKey == 6 ? ' sortActive' : '') . '" title="{s_option:dsc}"><span class="sortDsc"></span></a>' .
						'</th>' .
						'<th class="amount"><a href="' . $statRoot . 'by=count&sort=' . ($sortKey == 3 ? 'dsc' : 'asc') . '" class="ajaxLink">{s_header:amount}</a>' .
						'<a href="' . $statRoot . 'by=count&sort=asc" class="ajaxLink' . ($sortKey == 3 ? ' sortActive' : '') . '" title="{s_option:asc}"><span class="sortAsc"></span></a>' .
						'<a href="' . $statRoot . 'by=count&sort=dsc" class="ajaxLink' . ($sortKey == 4 ? ' sortActive' : '') . '" title="{s_option:dsc}"><span class="sortDsc"></span></a>' .
						'</th>' .
						'<th><a href="' . $statRoot . 'by=date&sort=' . ($sortKey == 9 ? 'dsc' : 'asc') . '" class="ajaxLink">{s_header:lastAccess}</a>' .
						'<a href="' . $statRoot . 'by=date&sort=asc" class="ajaxLink' . ($sortKey == 9 ? ' sortActive' : '') . '" title="{s_option:asc}"><span class="sortAsc"></span></a>' .
						'<a href="' . $statRoot . 'by=date&sort=dsc" class="ajaxLink' . ($sortKey == 10 ? ' sortActive' : '') . '" title="{s_option:dsc}"><span class="sortDsc"></span></a>' .
						'</th>' .
						'<th>&nbsp;</th>' .
						'</tr>' . "\r\n" . 
						$loop . 
						'</table>' . PHP_EOL .
						'</form>';

			$return .=	Modules::getPageNav($maxRows, $totalRowsLB, $startRow, $pageNumLB, "task=stats&" . $pageNavExt . "&totalRowsLB=".$totalRowsLB."&pageNumLB=".$pageNumLB, "LB", false, Modules::getLimitForm($this->limitOptions, $maxRows, $this->formAction . '&' . $pageNavExt));
			
		}
		else
			$return .=	"<ul><li>{s_text:nostats}</li></ul>";

		$return .=		'</div>' . PHP_EOL .
						'{up}<br class="clearfloat" />' . PHP_EOL .
						'</div>';

		
		return  $return;
		
	}

}
