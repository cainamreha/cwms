<?php
namespace Concise;


/**
 * Klasse Log
 * 
 */

class Log extends ContentsEngine
{

	/**
	 * UserAgent
	 * 
	 * @access public
     * @var    string
	 */
	public $userAgent = "";

	/**
	 * Referer
	 * 
	 * @access public
     * @var    string
	 */
	public $referer = "";

	/**
	 * Browser
	 * 
	 * @access public
     * @var    string
	 */
	public static $browser = "";

	/**
	 * Browser Version
	 * 
	 * @access public
     * @var    string
	 */
	public static $version = "";


	/**
	 * Konstruktor der Klasse
	 * 
	 * @access	public
	 * @param	$DB	object	DB-Objekt
	 */
	public function __construct($DB, $g_Session)
	{
	
		$this->DB			= $DB;

		$this->g_Session	= $g_Session;
		
		// USER-AGENT auslesen
		if(isset($_SERVER['HTTP_USER_AGENT']))
			$this->userAgent	= $_SERVER['HTTP_USER_AGENT'];
		// REFERER auslesen
		if(isset($_SERVER['HTTP_REFERER']))
			$this->referer		= $_SERVER['HTTP_REFERER'];

		// Aktueller Browser
		self::$browser			= $this->getBrowser($this->userAgent);
	
	}

	/**
	 * Logging aufrufen
	 * 
     * @param   string	$pageId	ID der aufgerufenen Seite
     * @param   string	$lang	Sprache
	 * @access public
	 */
	public function doLog($pageId, $lang)
	{
		
		$log = false;
		
		//Session-Nummer		
		$sessionID		= $this->DB->escapeString(session_id());
		
		$lang			= $this->DB->escapeString(strtolower($lang));
		$currentStamp	= time();

		
		// Referer Spam ausmisten
		if($this->referer != ""
		&& self::checkSpam($this->referer)
		) {
			$this->logPotentialBot(User::getRealIP(), $this->userAgent, $this->referer, $currentStamp);
			$this->gotoErrorPage(403); // Goto error page with status forbidden
			return false;
		}

		
		// USER-AGENT auslesen
		if($this->userAgent != "") {
			
			if(self::checkBot($this->userAgent) !== true) // Wenn kein Robot, log auf true setzen
				$log = true;
		}

		// Wenn eine Systemseite außer der Suchseite und der Registrierungsseite (z.B. admin, Fehlerseite) aufgerufen wird oder Author etc., Klicks nicht zählen
		if(($pageId < 1 && $pageId != -1004 && $pageId != -1006)
		|| (isset($this->g_Session['group'])
			&& ($this->g_Session['group'] == "admin"
			|| $this->g_Session['group'] == "editor"
			|| $this->g_Session['group'] == "author")
			)
		)
			$log = false;

		
		$pageId			= $this->DB->escapeString($pageId);
		
		// Click nicht zählen
		if(!$log)
			return false;
		
					
		// Falls der Klick gezählt werden soll
		$realIP			= User::getRealIP(ANONYMIZE_IP, true);
		$realIPdb		= $this->DB->escapeString($realIP);
		$refererDb		= $this->DB->escapeString($this->referer);
		
		$lastLog	= $this->DB->query("SELECT MAX(`timestamp`) 
											FROM `" . DB_TABLE_PREFIX . "log` 
										WHERE `realIP` = '$realIPdb' 
											AND `page_id` = $pageId
										", false);
		
		// Insert into table log
		$sql = "INSERT INTO `" . DB_TABLE_PREFIX . "log` 
				(`realIP`, `sessionID`, `timestamp`, `page_id`, `lang`, `referer`, `browser`, `version`)".
				"VALUES ('".$realIPdb."','".$sessionID."',".$currentStamp.",'".$pageId."','".$lang."','".$refererDb."','".self::$browser."','".self::$version."')
				";
		
		$this->DB->query($sql);
		
		
		// Falls der UserAgent unbekannt ist (pot. Bot?) Eintrag in Tabelle log_bots vornehmen
		if(self::$browser == "other"
		|| empty($this->referer)
		) {
		
			$this->logPotentialBot(User::getRealIP(), $this->userAgent, $this->referer, $currentStamp);
		
		}
		
		// Ggf. Eintrag in Tabelle "stats"
		if(!is_array($lastLog)
		|| count($lastLog) == 0
		)
			return false;
			
		$lastStamp		= $lastLog[0]["MAX(`timestamp`)"];
		$visitTimes		= array();
		
		$visitTimes		= $this->DB->query("SELECT `last_update` 
												FROM `" . DB_TABLE_PREFIX . "stats` 
											WHERE `page_id` = $pageId
											", false);
		
		if(!is_array($visitTimes)
		|| count($visitTimes) == 0
		)
			$visitTimes[0]['last_update'] = 0;
		
		if($lastStamp == NULL || $lastStamp+VISIT_COUNT_INTERVAL <= $currentStamp) { // Falls der letzte Besuch länger als x sec her ist, Besuch zählen
			
			$currMon		=	date("Y-m", $currentStamp);
			$currYear		=	date("Y");
			$lastVisMon		=	substr($visitTimes[0]['last_update'], 0, 7);
			$lastVisYear	=	substr($visitTimes[0]['last_update'], 0, 4);
			
			// aktueller Monat
			if($lastVisMon == $currMon) {
				$visitsIns = "visits_lastmon+1";
				$visitsUpd = "visits_lastmon = visits_lastmon+1";
			}
			else {
				$visitsIns = "1";
				$visitsUpd = "visits_lastmon = 1";
			}
			// aktuelles Jahr
			if($lastVisYear == $currYear) {
				$visitsIns .= ", visits_lastyear+1";
				$visitsUpd .= ", visits_lastyear = visits_lastyear+1";
			}
			else {
				$visitsIns .= ", 1";
				$visitsUpd .= ", visits_lastyear = 1";
			}
			
			#var_dump($currDate);
			$sql = $this->DB->query("INSERT INTO `" . DB_TABLE_PREFIX . "stats` 
										(`page_id`, `visits_total`, `visits_lastmon`, `visits_lastyear`) 
										VALUES ($pageId, `visits_total`+1, $visitsIns)
										ON DUPLICATE KEY UPDATE 
										`visits_total` = `visits_total`+1, 
										$visitsUpd
										");
			#var_dump($sql);
		}
	}
	

	/**
	 * Aktuellen Browser ermitteln
	 * 
     * @param   string	$userAgent
	 * @access	public
     * @return	string
	 */
	public function getBrowser($userAgent)
	{
	
		if (stripos($userAgent, 'Edge/') !== false)
		{
			$browser = "Edge";
		}
		else if ((stripos($userAgent, 'MSIE') !== false || stripos($userAgent, 'Trident') !== false) && stripos($userAgent, 'Opera') === false)
		{
			$browser = "IE";
		}
		else if (stripos($userAgent, 'Firefox') !== false && stripos($userAgent, 'Safari') === false)
		{
			$browser = "Firefox";
		}
		else if (stripos($userAgent, 'Chrome') !== false)
		{
			$browser = "Chrome";
		}
		else if (stripos($userAgent, 'Opera Mobi') !== false)
		{
			$browser = "oMobile";
		}
		else if (stripos($userAgent, 'Opera') !== false)
		{
			$browser = "Opera";
		}
		else if (stripos($userAgent, 'iPad') !== false)
		{
			$browser = "iPad";
		}
		else if (stripos($userAgent, 'iPhone') !== false)
		{
			$browser = "iPhone";
		}
		else if (stripos($userAgent, 'Android') !== false && stripos($userAgent, 'Mobile') !== false)
		{
			$browser = "aPhone";
		}
		else if (stripos($userAgent, 'Android') !== false)
		{
			$browser = "aPad";
		}
		else if (stripos($userAgent, 'BlackBerry') !== false)
		{
			$browser = "BlackBerry";
		}
		else if (stripos($userAgent, 'PlayBook') !== false)
		{
			$browser = "PlayBook";
		}
		else if (stripos($userAgent, 'Kindle') !== false)
		{
			$browser = "Kindle";
		}
		else if (stripos($userAgent, 'Windows Phone') !== false)
		{
			$browser = "wPhone";
		}
		else if (stripos($userAgent, 'Safari') !== false)
		{
			$browser = "Safari";
		}
		else
		{
			$browser = "other";
		}

		// Falls Browser erkannt, Version auslesen
		if($browser != "other")
			$this->getVersion($browser);
		
		return $browser;
	
	}
    

	/**
	 * Aktuellen Browser Version ermitteln
	 * 
     * @param   string	$userAgent
	 * @access	public
     * @return	string
	 */
	public function getVersion($browser)
	{
	
		// Edge
		if($browser == "Edge") {
		
			if(stripos($this->userAgent,'Edge/') !== false) {
				$aresult = explode('/', stristr($this->userAgent, 'Edge'));
				if(isset($aresult[1])) {
					$aversion = explode(' ', $aresult[1]);
					$this->setVersion($aversion[0]);
					return true;
				}
			}
			return false;
		}
	
		// IE
		if($browser == "IE") {
		
			// Test for versions > IE 1.5
			if(stripos($this->userAgent, 'msie') !== false) {
				$aresult = explode(' ', stristr(str_replace(';', '; ', $this->userAgent), 'msie'));
				if(isset($aresult[1])) {
					$this->setVersion(str_replace(array('(', ')', ';'), '', $aresult[1]));
					return true;
				}
			}
			// Test for versions > IE 10
			if(stripos($this->userAgent, 'trident') !== false) {
				$result = explode('rv:', $this->userAgent);
				if(isset($result[1])) {
					$this->setVersion(preg_replace('/[^0-9.]+/', '', $result[1]));
					return true;
				}
			}
			return false;
		}
	
		// Firefox
		if($browser == "Firefox") {
		
            if (preg_match("/Firefox[\/ \(]([^ ;\)]+)/i", $this->userAgent, $matches)) {
                $this->setVersion($matches[1]);
                return true;
            } else if (preg_match("/Firefox$/i", $this->userAgent, $matches)) {
                $this->setVersion("");
                return true;
            }
			return false;
		}
	
		// Chrome
		if($browser == "Chrome") {
		
            $aresult = explode('/', stristr($this->userAgent, 'Chrome'));
            $aversion = explode(' ', $aresult[1]);
            $this->setVersion($aversion[0]);
            return true;
		}
	
		// Opera
		if($browser == "Opera" || $browser == "Opera Mobi") {
		
			if (stripos($this->userAgent, 'opera mini') !== false) {
				$resultant = stristr($this->userAgent, 'opera mini');
				if (preg_match('/\//', $resultant)) {
					$aresult = explode('/', $resultant);
					$aversion = explode(' ', $aresult[1]);
					$this->setVersion($aversion[0]);
				} else {
					$aversion = explode(' ', stristr($resultant, 'opera mini'));
					$this->setVersion($aversion[1]);
				}
				return true;
			} else if (stripos($this->userAgent, 'opera') !== false) {
				$resultant = stristr($this->userAgent, 'opera');
				if (preg_match('/Version\/(1*.*)$/', $resultant, $matches)) {
					$this->setVersion($matches[1]);
				} else if (preg_match('/\//', $resultant)) {
					$aresult = explode('/', str_replace("(", " ", $resultant));
					$aversion = explode(' ', $aresult[1]);
					$this->setVersion($aversion[0]);
				} else {
					$aversion = explode(' ', stristr($resultant, 'opera'));
					$this->setVersion(isset($aversion[1]) ? $aversion[1] : "");
				}
				return true;
			} else if (stripos($this->userAgent, 'OPR') !== false) {
				$resultant = stristr($this->userAgent, 'OPR');
				if (preg_match('/\//', $resultant)) {
					$aresult = explode('/', str_replace("(", " ", $resultant));
					$aversion = explode(' ', $aresult[1]);
					$this->setVersion($aversion[0]);
				}
				return true;
			}
			return false;
		}
	
		// Safari
		if($browser == "Safari") {
		
            $aresult = explode('/', stristr($this->userAgent, 'Version'));
            if (isset($aresult[1])) {
                $aversion = explode(' ', $aresult[1]);
                $this->setVersion($aversion[0]);
				return true;
            }
			return false;
		}
	
		// iPad
		if($browser == "iPad") {
		
			return false;
		}
	
		// iPhone
		if($browser == "iPhone") {
		
			return false;
		}
		
		return false;
	}

	
    /**
     * Set the version of the browser
     * @param string $version The version of the Browser
     */
    public function setVersion($version)
    {
	
		self::$version = preg_replace('/[^0-9,.,a-z,A-Z-]/', '', $version);

	}

	
    /**
     * Log-Tabellen Cleanup
     */
    public function cleanupLogTable()
    {

		$result		= true;		
		$queryExt	= "";
		$querySpam	= array();
		$querySpam2	= array();
		$querySpam3	= array();
		
		// db-Tabelle sperren
		$lock = $this->DB->query("LOCK TABLES `" . DB_TABLE_PREFIX . "log`, `" . DB_TABLE_PREFIX . "log_bots`");
		
		// Spam
		// Tabelle bots, referer spam etc. ausmisten
		$querySpam = $this->DB->query(	"SELECT sip,cntts FROM (
											SELECT SUBSTRING(`realIP`,1,8) as sip, COUNT(*) AS cnt, cntts FROM (
												SELECT *, COUNT(*) AS cntts 
												FROM `" . DB_TABLE_PREFIX . "log` as p1
												GROUP BY `realIP`,`sessionID`,`timestamp`
											) AS p
											WHERE cntts > 2
											GROUP BY `realIP`,`timestamp` 
										) AS q 
											WHERE cnt > 1 
											OR cntts > 2
										");

		
		// Tabelle bots, referer spam etc. ausmisten
		$querySpam2 = $this->DB->query(	"SELECT * FROM (
											SELECT `realIP`,`lang`,`timestamp`,`browser`,`version`, COUNT(`timestamp`) AS cntts 
												FROM `" . DB_TABLE_PREFIX . "log` as p2 
												GROUP BY `lang`,`timestamp`,`browser`,`version`
											) AS p 
											WHERE cntts > 2
										");
		
		// Tabelle bots, referer spam etc. ausmisten (2 sec step spam)
		$querySpam3 = $this->DB->query(	"SELECT sip, cntts FROM (
											SELECT SUBSTRING(`realIP`,1,8) as sip, COUNT(*) AS cntts 
												FROM `" . DB_TABLE_PREFIX . "log` as p2 
												WHERE `referer` = ''
												GROUP BY `timestamp` DIV 13 
											) as p
											WHERE cntts >= 6
											GROUP BY sip
										");
		
		
		if(is_array($querySpam2)
		&& count($querySpam2) > 0
		) {
		
			$queryExt2	= "";
			
			foreach($querySpam2 as $bot) {
				
				// Falls es sich um einen Bot handelt, Daten aus Tabellen log und log_bots löschen
				if(!empty($bot)) {
					
					$botL		= $this->DB->escapeString($bot['lang']);
					$botT		= $this->DB->escapeString($bot['timestamp']);
					$botB		= $this->DB->escapeString($bot['browser']);
					$botV		= $this->DB->escapeString($bot['version']);
					$queryExt2 .= "(`lang` = '".$botL."' AND `timestamp` = '".$botT."' AND `browser` = '".$botB."' AND `version` = '".$botV."') OR ";
				}
			}
			
			$queryExt2	= substr($queryExt2, 0, -4);
			
			
			// Tabelle bots, referer spam etc. ausmisten
			$querySpam4 = $this->DB->query(	"SELECT sip FROM (
												SELECT SUBSTRING(`realIP`,1,8) as sip 
													FROM `" . DB_TABLE_PREFIX . "log` AS p1
													WHERE $queryExt2
												) AS p
											");
			
			if(is_array($querySpam4)
			&& count($querySpam4) > 0
			) {
				if(is_array($querySpam3)
				&& count($querySpam3) > 0
				)
					$querySpam3	= array_merge($querySpam3, $querySpam4);
				else
					$querySpam3	= $querySpam4;
			}			
		}


		if(is_array($querySpam)
		&& count($querySpam) > 0
		) {
			
			foreach($querySpam as $bot) {
				
				// Falls es sich um einen Bot handelt, Daten aus Tabellen log und log_bots löschen
				if(!empty($bot)
				&& !empty($bot['sip'])
				) {
					
					$botIP		= $this->DB->escapeString($bot['sip']);
					$queryExt .= "`realIP` LIKE '".$botIP."%' OR ";
				}
			}
		}

		
		if(is_array($querySpam3)
		&& count($querySpam3) > 0
		) {
			
			foreach($querySpam3 as $bot) {
				
				// Falls es sich um einen Bot handelt, Daten aus Tabellen log und log_bots löschen
				if(!empty($bot)
				&& !empty($bot['sip'])
				) {
					
					$botIP		= $this->DB->escapeString($bot['sip']);
					$queryExt .= "`realIP` LIKE '".$botIP."%' OR ";
				}
			}
			
		}
		

		$realIP = User::getRealIP(ANONYMIZE_IP, true);

		// Delete own logs
		if(!empty($realIP)
		&& (!empty($GLOBALS['_COOKIE']['conciseLogging_off'])
			|| (!empty($GLOBALS['_COOKIE']['realip_aid'])
			&& $GLOBALS['_COOKIE']['realip_aid'] == $realIP)
			)
		) {
			$queryExt .=  "`realIP` = '" . $realIP . "' OR ";					
		
		}
		// Wrong IP
		$queryExt .=  "`realIP` LIKE '0.0.0.0%'";					
		
		
		// Delete log entries
		$deleteSQL1 = $this->DB->query("DELETE FROM `" . DB_TABLE_PREFIX . "log` 
									WHERE $queryExt
								 ");
		
		if($deleteSQL1 === true) {
		
			// Delete bot entries
			$deleteSQL2 = $this->DB->query("DELETE FROM `" . DB_TABLE_PREFIX . "log_bots` 
										WHERE $queryExt
									 ");
			
		}
		else
			$result	= false;

		
		// db-Sperre aufheben
		$unLock = $this->DB->query("UNLOCK TABLES");

		return $result;
	
	}

	
    /**
     * Bot-Logs Cleanup
     */
    public function cleanupBotLogs()
    {

		$result	= true;
	
		// Tabelle log_bots ausmisten
		$query = $this->DB->query(	"SELECT `userAgent`, `realIP` 
										FROM `" . DB_TABLE_PREFIX . "log_bots`
									 WHERE `userAgent` != '' 
										GROUP BY `realIP`
									");
		
				
		if(is_array($query)
		&& count($query) > 0
		) {
			
			$queryExt1	= "";
			$queryExt2	= "";
			
			foreach($query as $bot) {
				
				$isBot		= self::checkBot($bot['userAgent']);
				
				// Falls es sich um einen Bot handelt, Daten aus Tabellen log und log_bots löschen
				if($isBot) {
					
					$botIP		= $this->DB->escapeString($bot['realIP']);
					$queryExt1 .= "`realIP` LIKE '".$botIP."-%' OR ";
					$queryExt2 .= "`realIP` = '".$botIP."' OR ";
				}
			}
			
			// Falls Botdaten gefunden wurden
			if($queryExt1 != "") {
				
				$queryExt1 = substr($queryExt1, 0, -4);
				$queryExt2 = substr($queryExt2, 0, -4);
				
				// db-Tabelle sperren
				$lock = $this->DB->query("LOCK TABLES `" . DB_TABLE_PREFIX . "log`, `" . DB_TABLE_PREFIX . "log_bots`");
						
				
				// log-Eintrag löschen
				$deleteSQL1 = $this->DB->query("DELETE FROM `" . DB_TABLE_PREFIX . "log` 
													 WHERE $queryExt1
													");
				
				// log-Eintrag löschen
				$deleteSQL2 = $this->DB->query("DELETE FROM `" . DB_TABLE_PREFIX . "log_bots` 
													 WHERE $queryExt2
													");
								
					
				// db-Sperre aufheben
				$unLock = $this->DB->query("UNLOCK TABLES");
				
				if($deleteSQL1 !== true
				|| $deleteSQL2 !== true
				)
					$result	= false;
				
			}
			
		}
		
		return $result;
	
	}

	
    /**
     * Falls neues Jahr, neue Log-Tabelle
     */
    public function changeLogSeason()
    {

		// vergangenes Jahr
		$year	= date("Y", time()) -1;

		// Archiv der Logs des vergangenen Jahres erstellen (Tabelle `log_year`)
		$this->createLogArchive($year);

	}

	
    /**
     * Erstellt falls nicht vorhanden eine Kopie der Log-Tabelle und verschiebt Einträge des angegebenen Jahres in diese
     */
    public function createLogArchive($year)
    {
	
		$year	= $this->DB->escapeString($year);
		
		// db-Tabelle sperren
		$lock = $this->DB->query("LOCK TABLES `" . DB_TABLE_PREFIX . "log`");

		$sqlQ	= "SELECT `id` FROM `" . DB_TABLE_PREFIX . "log` WHERE YEAR(FROM_UNIXTIME(`timestamp`)) = " . $year . " LIMIT 1";

		$resultQ = $this->DB->query($sqlQ);
		
		if(!is_array($resultQ)
		|| count($resultQ) == 0
		) {
			// db-Sperre aufheben
			$unLock = $this->DB->query("UNLOCK TABLES");			
			return false;
		}
		
		$sql1	= "CREATE TABLE IF NOT EXISTS `" . DB_TABLE_PREFIX . "log_" . $year . "` LIKE `" . DB_TABLE_PREFIX . "log`";
		$sql2	= "INSERT INTO `" . DB_TABLE_PREFIX . "log_" . $year . "` SELECT * FROM `" . DB_TABLE_PREFIX . "log` WHERE YEAR(FROM_UNIXTIME(`timestamp`)) = " . $year;
		$sql3	= "DELETE FROM `" . DB_TABLE_PREFIX . "log` WHERE YEAR(FROM_UNIXTIME(`timestamp`)) = " . $year;
		
		$result1 = $this->DB->query($sql1);
		$result2 = $this->DB->query($sql2);
		
		if($result1 === true
		&& $result2 === true
		) {
			$result3 = $this->DB->query($sql3);
			
			// db-Sperre aufheben
			$unLock = $this->DB->query("UNLOCK TABLES");
			
			return true;
		}
			
		// db-Sperre aufheben
		$unLock = $this->DB->query("UNLOCK TABLES");

		return false;

	}


	/**
	 * Statistiken von letztem Monat/Jahr bereinigen
	 * 
	 * @access	public
     * @return	string
	 */
	public function updateStatsTable()
	{
		
		// Statistiken von letztem Monat/Jahr bereinigen
		$updateSQL1a = "UPDATE `" . DB_TABLE_PREFIX . "stats`
						SET `visits_lastmon` = 0 
						WHERE Month(`last_update`) != Month(CURRENT_TIMESTAMP)
						";
		
		$updateSQL1b = "UPDATE `" . DB_TABLE_PREFIX . "stats` 
						SET `visits_lastyear` = 0 
						WHERE Year(`last_update`) != Year(CURRENT_TIMESTAMP)
						";

		$result1a = $this->DB->query($updateSQL1a);
		$result1b = $this->DB->query($updateSQL1b);
		
		if($result1a === true && $result1b === true)
			return true;
		else
			return false;
	}


	/**
	 *  Referer-Spam-Erkennung
	 * 
	 * @access	public
     * @param   string	$referer	Referer
     * @return	boolean
	 */
	public static function checkSpam($referer)
	{
    	
		if(!empty($referer))
			return false;
		
		// Botliste einlesen
		$bl = file(PROJECT_DOC_ROOT . '/inc/blacklist.txt');
		
		if(empty($bl)
		|| !is_array($bl)
		)
			return false;
		
		$i = 0;
		$sum = count($bl);
	
		while ($i < $sum) {
			if(!empty($bl[$i])
			&& stripos($referer, $bl[$i]) !== false
			)
				return true;
			$i++;
		}
		
		return false;
	}


	/**
	 *  Robot-Erkennung
	 * 
	 * @access	public
     * @param   string	$userAgent	User Agent
     * @return	boolean
	 */
	public static function checkBot($userAgent = "")
	{
    	
		if($userAgent == "" && isset($_SERVER['HTTP_USER_AGENT']))
			$userAgent = $_SERVER['HTTP_USER_AGENT'];
		
		// Botliste einlesen
		require(PROJECT_DOC_ROOT . '/inc/botlist.inc.php');
		
		$userAgent = strtolower($userAgent);
		$i = 0;
		$sum = count($bots);
	
		while ($i < $sum) {
			if(strpos($userAgent, $bots[$i]) !== false) return true;
			$i++;
		}
		
		return false;
	}



	/**
	 *  Eintragen von potentiellen Bots in Tabelle `log_bots`
	 * 
	 * @access	public
     * @param   string	$realIP		current realIP
     * @param   string	$userAgent	current userAgent
     * @param   string	$referer	current referer
     * @param   string	$timestamp	current timestamp
     * @return	boolean
	 */
	public function logPotentialBot($realIP, $userAgent, $referer, $timestamp)
	{
    	
		$realIP			= $this->DB->escapeString($realIP);
		$userAgent		= $this->DB->escapeString($userAgent);
		$referer		= $this->DB->escapeString($referer);
		$timestamp		= $this->DB->escapeString($timestamp);
		
		$sql = "INSERT INTO `" . DB_TABLE_PREFIX . "log_bots` 
				(`userAgent`, `realIP`, `referer`, `timestamp`)".
				"VALUES ('".$userAgent."','".$realIP."','".$referer."',".$timestamp.")
				";
		
		$result = $this->DB->query($sql); // Daten einfügen
		
		return $result;
	
	}


}
