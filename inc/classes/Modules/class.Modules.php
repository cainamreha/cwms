<?php
namespace Concise;


/**
 * Klasse für eingebundene Module
 *
 */

class Modules extends ContentsEngine
{
	

    /**
     * Modultyp
     *
     * @access public
     * @var    string
     */
    public $modType = "";
 
    /**
     * Daten-ID (falls per GET übermittelt, ausgelesen in Contents-Klasse, sonst Darstellungsform z.B. Liste)
     *
     * @access public
     * @var    string
     */
    public $dataID = "";
 
    /**
     * Url zum aktuellen Datensatz
     *
     * @access public
     * @var    string
     */
    public $dataUrl = "";
 
    /**
     * Datentabelle
     *
     * @access public
     * @var    string
     */
    public $dataTable = "";
    public $dataTableDB = "";
 
    /**
     * Kategorietabelle
     *
     * @access public
     * @var    string
     */
    public $catTable = "";
    public $catTableDB = "";
	 
	/**
	 * Leseberechtigung für Benutzergruppe.
	 *
	 * @access protected
     * @var    boolean
     */
	protected $readPermission = false;
	 
	/**
	 * Schreibberechtigung für Benutzergruppe.
	 *
	 * @access protected
     * @var    boolean
     */
	protected $writePermission = false;
	 
	/**
	 * Leseberechtigung für Benutzergruppe.
	 *
	 * @access protected
     * @var    array
     */
	protected $readCommentsGroups = array();
	 
	/**
	 * Schreibberechtigung für Benutzergruppe.
	 *
	 * @access protected
     * @var    array
     */
	protected $writeCommentsGroups = array();
	 
	/**
	 * Rating-Berechtigung für Benutzergruppe.
	 *
	 * @access protected
     * @var    array
     */
	protected $ratingGroups = array();
	 
	/**
	 * (Alternativer) Templatename des zu verwendenden Templates.
	 *
	 * @access public
     * @var    string
     */
	public $useTpl = "";
	 
	/**
	 * Filter für die db Suche.
	 *
	 * @access public
     * @var    string
     */
	public $dbFilter = "";
	 
	/**
	 * Datumsfilter für die db Suche (Datum in Zukunft bzw. abgelaufene Termine).
	 *
	 * @access public
     * @var    string
     */
	public $dbFilterDate = "";
	 
	/**
	 * Benutzergruppen Filter.
	 *
	 * @access public
     * @var    string
     */
	public $dbFilterGroup = "";
	 
	/**
	 * Sortierung für die db Suche.
	 *
	 * @access public
     * @var    string
     */
	public $dbOrder = "";
	
	
	
	/**
	 * Methode zur Bearbeitung von Formulareingaben zur Erstellung eines "sicheren" Textes
	 * 
	 * @param	string Textstring
	 * @access	public
	 * @return	string
	 */
	public static function safeText($formString)
	{
		
		return nl2br(htmlspecialchars(trim($formString)));
		
	}



	/**
	 * Gibt den Titel eines Datensatzes zurück
	 * 
	 * @param	string Alias
	 * @access	public
	 * @return	string
	 */
	public static function getDataHeader($DB, $dataID, $dataTable, $lang)
	{
		
		// db-Query nach allen Feeds
		$dataHeader = $DB->query("SELECT `header_" . $DB->escapeString($lang) . "` 
											FROM `" . DB_TABLE_PREFIX . $DB->escapeString($dataTable) . "` 
											WHERE 
											`id` = " . $DB->escapeString($dataID) . "
											", false);

		if(isset($dataHeader[0]['header_' .$lang]))		
			return $dataHeader[0]['header_' .$lang];		
		else		
			return false;
	}	



	/**
	 * Methode zur Umschreibung von Aliasen
	 * 
	 * @param	string	Alias
	 * @param	boolean	urlencode (default = true)
	 * @access	public
	 * @return	string
	 */
	public static function getAlias($alias, $urlencode = true)
	{
		
		$search = array('ä', 'ö', 'ü', 'Ä', 'Ö', 'Ü', 'ß', 'é', 'è', 'à', 'á', 'â', 'ô', 'û', 'í', 'ì', 'ó', 'ò', 'ç', 'r', '&amp;', '&quot;', '&frasl;', '&', '+', ' ', '–', '\'', '/', '.', ',', ':', '?', '!', '"', '\'', '(', ')', '[', ']', '{', '}', "„", "“", "’", "´", "`");
		$replace = array('ae', 'oe', 'ue', 'Ae', 'Oe', 'Ue', 'ss', 'e', 'e', 'a', 'a', 'a', 'o', 'u', 'i', 'i', 'o', 'o', 'c', 'r', '-', '', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-', '', '', '', '', '-', '-', '-', '-', '', '', '', '', '');

		$alias = str_ireplace($search, $replace, $alias); // Ersetzen der Umlaute
		#$alias = str_ireplace($search, $replace, utf8_decode($alias)); // Ersetzen der Umlaute falls ansi-Format

		while(strpos($alias, "--") !== false || strpos($alias, "__") !== false)
			$alias = str_replace(array("--","__"), "-", $alias);
		
		$alias	= trim($alias, "-");
		
		if($alias != "" && !preg_match("/^[a-zA-Z0-9_-]+$/", $alias)) { // Falls noch falsche Zeichen vorhanden sind				
			
			if($urlencode)
				$alias = urlencode($alias);
			else
				$alias = false;
		}

		return $alias;
		
	}	



	/**
	 * Gibt ein Array mit Linkattributen zurück
	 * 
	 * @param	string $link
	 * @access	public
	 * @return	array
	 */
	public function getLinkAttr($link)
	{
	
		$linkAttr	= array("","");
		
		if(empty($link))
			return $linkAttr;
		
		if(strpos($link, "#") === 0) {
			$linkAttr[0] 	= $link;
			$linkAttr[1] 	= "sectionLink";
			return $linkAttr;
		}
		
		if(strpos($link, "{root}/") !== false
		|| strpos($link, "{sitelink}/") !== false
		) {
			$intLink = str_replace(array("{root}/","{sitelink}/"), "", $link);
			$intLink = explode("?", $intLink);
			$linkAttr[0] 	= PROJECT_HTTP_ROOT . "/" . $intLink[0] . (strpos($link, PAGE_EXT) === false ? PAGE_EXT : "") . (isset($intLink[1]) ? '?' . $intLink[1] : '');
			$linkAttr[1] 	= "siteLink";
			return $linkAttr;
		}
		if(strpos($link, "https://") !== false)
			$linkAttr[0] = 'https://' . str_replace("https://", "", $link);
		else
			$linkAttr[0] = 'http://' . str_replace("http://", "", $link);
		
		$linkAttr[1] = "extLink";
		return $linkAttr;
	
	}



	/**
	 * Gibt Anzahl der Begrenzung von Auflistungen zurück
	 * 
	 * @param	string $limit Standard-Limit (default = 10)
	 * @access	public
	 * @return	string
	 */
	public function getLimit($limit = 10)
	{

		if(isset($this->g_Session['limitPageNo']))
			$limit = (int)$this->g_Session['limitPageNo'];
		
		if(isset($GLOBALS['_GET']['limit']))
			$limit = (int)$GLOBALS['_GET']['limit'];
		
		if(isset($GLOBALS['_POST']['limit']))
			$limit = (int)$GLOBALS['_POST']['limit'];
		
		$this->o_security->setSessionVar('limitPageNo', $limit);

		return (int)$limit;
	}



	/**
	 * getLimitForm
	 * 
     * @param   array	$limitOptions
     * @param   int		$maxRows
     * @param   string	$formAction
     * @param   array	$hiddenFields
	 * @access	public
     * @return	string
	 */
	public static function getLimitForm($limitOptions, $maxRows, $formAction = "", $hiddenFields = array())
	{
	
		$output		 =	'<span class="cc-list-limit-select {t_class:formrow}">' . PHP_EOL .
						'<form class="{t_class:form} {t_class:forminl}" action="' . htmlspecialchars($formAction) . '" method="post">' . PHP_EOL;
		
		$output		 .=	self::getLimitSelect($limitOptions, $maxRows);
	
		if(!empty($hiddenFields)) {
			foreach($hiddenFields as $hf) {
				$output		 .=	'<input type="hidden" name="' . $hf["name"] . '" value="' . $hf["val"] . '" />' . PHP_EOL;
			}
		}
		
		$output		 .=	'</form>' . PHP_EOL;
		$output		 .=	'</span>' . PHP_EOL;
		
		return $output;
	
	}



	/**
	 * Gibt Select zur Begrenzung von Auflistungen zurück
	 * 
	 * @param	array $limitOptions
	 * @param	int $limit Current limit
	 * @access	public
	 * @return	string
	 */
	public static function getLimitSelect($limitOptions, $limit)
	{

		$output	=	'<select name="limit" class="listLimit {t_class:fieldinl} {t_class:select}" data-action="autosubmit">' . PHP_EOL;		
		
		// Ergebnisse pro Seite
		foreach($limitOptions as $value) {
		
			$output	.=	'<option value="' . $value . '"';
			
			if($limit == $value)
				$output	.=' selected="selected"';
				
			$output	.= '>' . $value . '</option>' . PHP_EOL;
		
		}
							
		$output	.= '</select>' . PHP_EOL;

		return $output;
	
	}
	
	

	/**
	 * Liefert eine Pagination-Navigation
	 * 
     * @param	string maximale Anzahl an Datensätzen
     * @param	string Gesamtzahl an Datensätzen
     * @param	string Nummer des ersten anzuzeigenden Datensatzes
     * @param	string Seitenzahl der aktuell anzuzeigenden Datensätze
     * @param	string anzuhängender Querystring (default = '')
     * @param	string Zusatz zur Unterscheidung bei mehreren Pagination auf einer Seite (default = '')
     * @param	boolean Falls QS-Parameter als url hinzugefügt werden sollen, auf true setzen (default = false)
     * @param	string Select-Element zur Begrenzung der maximalen Anzahl an Seiten (default = '')
	 * @access	public
	 * @return	string
	 */
	public static function getPageNav($maxRows, $totalRows, $startRow, $pageNum, $queryStringExt = "", $differ = "", $modRewrite = false, $limitSel = "")
	{	
		
		$urlAnk = "";
		
		if($queryStringExt != "") {
			
			if(strpos($queryStringExt, "::", 0) !== false) // Falls der Doppelpunkt vorhanden, kein QS sondern url
				$queryStringExt = substr($queryStringExt, 2);
			
			if($modRewrite === true)
				$queryString = "totalRows".$differ."=" . $totalRows;
			else {
				$queryStringExt .= "&amp;";
				$queryString = $queryStringExt . "totalRows".$differ."=" . $totalRows;
			}
		}
		else {
			#$queryStringExt .= "&amp;";
			$queryString = $queryStringExt . "totalRows".$differ."=" . $totalRows;
		}
		
		if($differ == "C" && $modRewrite === true) // Falls Kommentare augelistet werden, Ankerlink anhängen
			$urlAnk = "#commentSection";
		
		$totalPages = ceil($totalRows/$maxRows)-1;

		$pageNav  =	'<div class="pageNav {t_class:center} {t_class:centertxt}">' . "\r\n" . 
					'<div class="pageNr">' . ($startRow + 1) . "-" . min($startRow + $maxRows, $totalRows) . '<strong>(' . $totalRows . ')</strong>' .
					($totalRows > 10 && $limitSel != "" ? '&nbsp;' .  $limitSel : '') . // Limit select
					'</div>' . "\r\n";
		
		$pageNav .=	'<nav>' . "\r\n";
		$pageNav .=	'<ul class="{t_class:pagination}">' . "\r\n";
		
		// Falls mehr als eine Ergebnisseite vorhanden ist
		if($totalRows > $maxRows) {
			
			if($modRewrite === true) {
				$urlExt		= (strpos($queryStringExt, "://") === false ? PROJECT_HTTP_ROOT . '/' : '') . $queryStringExt . '?' . $queryString;
			}
			elseif(!empty($GLOBALS['_SERVER'])) {
				$baseUrl	= explode("?", $GLOBALS['_SERVER']['REQUEST_URI']);
				$urlExt		= (!empty($GLOBALS['_SERVER']['HTTPS']) && $GLOBALS['_SERVER']['HTTPS'] != "off" ? 'https' : 'http') .'://' . $GLOBALS['_SERVER']['HTTP_HOST'] . reset($baseUrl) . '?' . $queryString;
			}
			else {
				$urlExt		= '?' . $queryString;
			}
			
			$pageNav .=	'<li>' . "\r\n" . 
						'<a href="' . $urlExt . '&amp;pageNum'.$differ.'=0'.$urlAnk.'" rel="first">|&laquo; {s_link:first}</a>' . "\r\n" . 
						'</li>' . "\r\n" .
						'<li>' . "\r\n" . 
						'<a href="' . $urlExt . '&amp;pageNum'.$differ.'=' . max(0, $pageNum - 1) .$urlAnk.'" rel="prev">&laquo; {s_link:previous}</a>' . "\r\n" .'</li>' . "\r\n";
						
			$morePagesRgt = '<li class="{t_class:disabled}"><span> ... </span></li>' . "\n"; // Variable für die Anzeige von Punkten (..., rechts), falls mehr als 3 Ergebnisseiten vorhanden sind
			$morePagesLft = ""; // Variable für die Anzeige von Punkten (..., links), falls mehr als 3 Ergebnisseiten vorhanden sind
			
			// Falls mehr als 3 Ergebnisseiten vorhanden sind, Variablen für die Schleife zum Anzeigen der Seitennav zuweisen
			if($totalRows/$maxRows > 3) {
				if($pageNum > 0) { // Falls die Ergebnisseitenzahl größer 0 ist, ...
					$pageNumber = $pageNum - 1; // ... Beginn der Schleifenzählung auf 1 kleiner als aktuelle Seitenzahl setzen ...
					$thirdPage = $pageNum + 2; // ... Ende der Schleifenzählung auf 2 größer als aktuelle Seitenzahl setzen ...
					
					if($thirdPage > ceil($totalRows/$maxRows)) { // Falls das Ende der Schleife größer als Seitenzahl, ...
						$thirdPage = ceil($totalRows/$maxRows); // ... Seitenzahl auf maximale Seitenzahl setzen ...
						$pageNumber = $pageNum - 2; // ... Beginn der Schleife auf 2 kleiner als aktuelle Seitenzahl setzen (damit die letzten 3 Seitennummern angezeigt werden) ...
						$morePagesRgt = ""; // ... und die Punkte rechts entfernen
					}
					if($pageNum == ($totalPages -1)) // Falls die vorletzte Seite angezeigt wird, ...
						$morePagesRgt = ""; // ... die Punkte rechts entfernen
				}
				else { // Falls die erste Ergebnisseite angezeigt wird, ... 
					$pageNumber = $pageNum; // ... Beginn der Schleifenzählung auf aktuelle Seitenzahl setzen ...
					$thirdPage = 3; // ... Ende der Schleifenzählung auf 3 setzen ...
				}
				if($pageNum > 1) // Falls die Ergebnisseitenzahl größer 1 ist, ...
					$morePagesLft = '<li class="{t_class:disabled}"><span> ... </span></li>' . "\n"; // ... Anzeige von Punkten (links) festlegen
			}
			else { // Andernfalls wenn weniger als 3 Ergebnisseiten vorhanden sind, ...
				$pageNumber		= 0; // ... Beginn der Schleifenzählung auf 0 setzen ...
				$thirdPage		= ceil($totalRows/$maxRows); // ... und Ende der Schleifenzählung auf maximale Seitenzahl setzen ...
				$morePagesRgt	= ""; // ... die Punkte rechts entfernen
			}
			
			$pageNav .=	$morePagesLft; // Anzeige der Punkte links
			
			// Schleife für Anzeige von Ergebnisseitenblöcken
			for($pageNumber; $pageNumber < $thirdPage; $pageNumber++) {
				
				if($pageNum == $pageNumber)
					$pageNav .= '<li class="{t_class:active}">' . "\r\n" . 
								'<span class="actPage">' . ($pageNumber + 1) . '</span>' . "\r\n" . // Zahl der aktiven Seite
								'</li>' . "\r\n";
				else
					$pageNav .=	'<li>' . "\r\n" . 
								'<a href="' . $urlExt . '&amp;pageNum'.$differ.'=' . $pageNumber .$urlAnk.'">' . ($pageNumber + 1) . '</a>' . "\r\n" . // Link zur Ergebnisseite
								'</li>' . "\r\n";
				
			}
			$pageNav .= $morePagesRgt; // Anzeige der Punkte rechts
			$pageNav .= '<li>' . "\r\n" . 
						'<a href="' . $urlExt . '&amp;pageNum'.$differ.'=' . min($totalPages, ($pageNum + 1)) .$urlAnk.'" rel="next">{s_link:next} &raquo;</a>' . "\r\n" . 
						'</li>' . "\r\n" .
						'<li>' . "\r\n" . 
						'<a href="' . $urlExt . '&amp;pageNum'.$differ.'=' . $totalPages .$urlAnk.'" rel="last">{s_link:last} &raquo;|</a>' . "\r\n" .
						'</li>' . "\r\n";
						
		}
		
		$pageNav .=		'</ul>' . "\r\n";
		$pageNav .=		'</nav>' . "\r\n";
		$pageNav .=		'</div>' . "\r\n";
		
		
		return $pageNav;
		
	}



	/**
	 * Gibt die Größe einer Datei zurück
	 * 
     * @param	string	File src
     * @param	boolean falls true, überprüfen auf Vorhandensein einer mobilen Version der Datei
	 * @access	public
	 * @return	string
	 */
	public static function getImagePath($imgSrc, $mobileSrc = false)
	{
		
		// Pfad zur Bilddatei
		// Falls files-Ordner, den Pfad ermitteln
		if(strpos($imgSrc, "/") !== false) {
			$filesImg	= explode("/", $imgSrc);
			$img		= array_pop($filesImg);					
			$imgPath	= CC_FILES_FOLDER . "/" . implode("/", $filesImg) . "/";
		}
		elseif(strpos($imgSrc, "{img_root}") === 0) {
			$imgPath	= IMAGE_DIR;
			$img		= str_replace("{img_root}", "", $imgSrc);					
		}
		else {
			$imgPath	= CC_IMAGE_FOLDER . "/";
			$img		= $imgSrc;
		}
		
		if($mobileSrc) {
			return self::getMobileImageSrc($img, $imgPath);
		}
		
		return $imgPath . $img;
	
	}



	/**
	 * Gibt die mobile Version einer Bilddatei zurück
	 * 
     * @param	string	File src
     * @param	string	Ordner
	 * @access	public
	 * @return	string
	 */
	public static function getMobileImageSrc($file, $folder)
	{

		$output		= "";
		
		if(strpos($folder, PROJECT_HTTP_ROOT) === 0)
			$absPath	= str_replace(PROJECT_HTTP_ROOT, PROJECT_DOC_ROOT, $folder);
		else
			$absPath	= PROJECT_DOC_ROOT . '/' . $folder;
		
		// Get srcset if mobile and no thumbnail
		if(ContentsEngine::$device["isPhone"]
		&& file_exists($absPath . 'small/' . $file)
		)
			return $folder . 'small/' . $file;
		
		if(ContentsEngine::$device["isMobile"]
		&& file_exists($absPath . 'medium/' . $file)
		)
			return $folder . 'medium/' . $file;
		
		return $folder . $file;
	
	}	



	/**
	 * Gibt einen srcset-String für eine Bilddatei zurück
	 * 
     * @param	string	File src
     * @param	string	Ordner
	 * @access	public
	 * @return	string
	 */
	public static function getImageSrcset($file, $folder)
	{

		$output		= "";
		
		if(strpos($folder, PROJECT_HTTP_ROOT) === 0)
			$absPath	= str_replace(PROJECT_HTTP_ROOT, PROJECT_DOC_ROOT, $folder);
		else {
			$absPath	= PROJECT_DOC_ROOT . '/' . $folder;
			$folder		= PROJECT_HTTP_ROOT . '/' . $folder;
		}
		
		// Get srcset if mobile and no thumbnail
		if(ContentsEngine::$device["isPhone"]
		&& file_exists($absPath . 'small/' . $file)
		)
			$output	.= ' ' . $folder . 'small/' . $file . ' ' . SMALL_IMG_SIZE . 'w,';
		
		if(ContentsEngine::$device["isMobile"]
		&& file_exists($absPath . 'medium/' . $file)
		)
			$output	.= ' ' . $folder . 'medium/' . $file . ' ' . MEDIUM_IMG_SIZE . 'w,';
		
		if($output != "")
			$output	= substr($output, 0, -1);
		
		return $output;
	
	}
	


	/**
	 * Gibt die Größe einer Datei zurück
	 * 
     * @param	string	Datei
     * @param	string	Ordner
     * @param	boolean falls true, überprüfen auf Vorhandensein der Datei
	 * @access	public
	 * @return	string
	 */
	public static function getFileSizeString($file, $folder, $check = false)
	{

		$output	= ' <span class="fileSize">';
		$path	= PROJECT_DOC_ROOT . '/' . $folder . (strrpos($folder, "/") == strlen($folder)-1 ? '' : '/') . $file;
		$fe		= is_file($path);
		
		if($check || $fe) {
			
			if($fe)
				$fileSize	= (int) max(1, round(filesize($path)/1024));
			else
				$fileSize	= "0";
			
			$unit		= "kB";
		
			if($fileSize > 1024) {
				$fileSize	= round($fileSize/1024, 1);
				$unit		= "MB";
			}
			$output	.= '(';
						 
			if($check)
				$output	.= '{s_text:filesize} ';
							 
			$output	.= $fileSize . ' ' . $unit . ')';				 
		}
		else
			$output .= "(file not found)";
		
		$output .= "</span>";
		
		return $output;
		
	}
	
	

	/**
	 * Überprüft das Datum und gibt ggf. heute zurück
	 * 
     * @param	string Datum
     * @param	string Tag
     * @param	string Jahr
     * @param	string Monat
	 * @access	public
	 * @return	string
	 */
	public static function getDateString($date, $dataDay, $dataYear, $dataMon)
	{
		
		$today		= getdate();  
		$currDate	= mktime(0,0,0,$today['mon'],$today['mday'],$today['year']);
		
		$splitDate	= explode(".", $date);
		$unixDate	= mktime(0,0,0,$splitDate[1],$splitDate[0],$splitDate[2]);
		
		return $dataDay." ".$dataMon." ".$dataYear . ($unixDate == $currDate ? ' <span class="today"> ({s_date:today})</span>' : '');
		
	}



	/**
	 * Methode zum Ausgeben eines formatierten Datums
	 * 
	 * @param	string	$timestamp Zeitstempel
	 * @param	string	$lang Sprache (default = de)
	 * @param	boolean	$time falls, true wird die Uhrzeit mit zurückgegeben (default = true)
	 * @access	public
     * @return  string
	 */
	public static function getFormattedDateString($timestamp = "", $lang = "de", $time = true)
	{
	
		// Falls kein Zeitstempel mitgegeben wurde, aktuellen Timestamp verwenden
		if($timestamp == "")
			$timestamp = time();
		
		$timeStr = "";
		
		setlocale(LC_TIME, $lang . "_" . strtoupper($lang) . ".UTF-8", $lang . '_' . strtoupper($lang), strtolower($lang));
		
		// Falls Deutsch
		if($lang == "de") {
			$dateStr	= "%d. %B %Y";
			$timeStr	= " %H:%M";
		}
		// Sonst englisches Format
		else {
			$dateStr	= "%B %d %Y";
			$timeStr	= " %I:%M %p";
		}
		
		if($time)
			$dateStr .= $timeStr;
		
		return Language::force_utf8(strftime($dateStr, $timestamp));
	  
	}



	/**
	 * Methode zum Ausgeben eines nach locale formatierten Datums
	 * 
	 * @param	string	$date Datum entweder als "Y-m-d" oder als timestamp
	 * @param	string	$lang Sprache
	 * @param	string	$time Datum mit Uhrzeit ausgeben (default = false)
	 * @access	public
     * @return  string
	 */
	public static function getLocalDateString($date, $lang, $time = false)
	{

		// Falls kein Zeitstempel mitgegeben wurde, aktuellen Timestamp verwenden
		$date	= trim($date);
		
		if(empty($date))
			$timestamp	= time();
		
		elseif(is_numeric($date))
			$timestamp	= $date;
		
		elseif(preg_match("/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/", $date)) {
			$dSplit		= explode("-", $date);
			$timestamp	= mktime(0, 0, 0, $dSplit[1], $dSplit[2], $dSplit[0]);
		}		
		elseif(preg_match("/^[0-9]{2}\/[0-9]{2}\/[0-9]{4}$/", $date)) {
			$dSplit		= explode("/", $date);
			$timestamp	= mktime(0, 0, 0, $dSplit[0], $dSplit[1], $dSplit[2]);
		}		
		elseif(preg_match("/^[0-9]{2}-[0-9]{2}-[0-9]{4}$/", $date)) {
			$dSplit		= explode("-", $date);
			$timestamp	= mktime(0, 0, 0, $dSplit[0], $dSplit[1], $dSplit[2]);
		}		
		elseif(preg_match("/^[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}$/", $date)) {
			$dtStr		= str_replace(array(" ", ":"), "-", $date);
			$dSplit		= explode("-", $dtStr);
			$timestamp	= mktime($dSplit[3], $dSplit[4], $dSplit[5], $dSplit[1], $dSplit[2], $dSplit[0]);
		}		
		elseif(preg_match("/^[0-9]{2}.[0-9]{2}.[0-9]{4}$/", $date)) {
			$dSplit		= explode(".", $date);
			$timestamp	= mktime(0, 0, 0, $dSplit[1], $dSplit[0], $dSplit[2]);
		}		
		elseif(preg_match("/^[0-9]{2}.[0-9]{2}.[0-9]{4} [0-9]{2}:[0-9]{2}$/", $date)) {
			$dtStr		= str_replace(array(" ", ":"), ".", $date);
			$dSplit		= explode(".", $dtStr);
			$timestamp	= mktime($dSplit[3], $dSplit[4], 0, $dSplit[1], $dSplit[0], $dSplit[2]);
		}		
		elseif(preg_match("/^[0-9]{2}.[0-9]{2}.[0-9]{4} [0-9]{2}:[0-9]{2}:[0-9]{2}$/", $date)) {
			$dtStr		= str_replace(array(" ", ":"), ".", $date);
			$dSplit		= explode(".", $dtStr);
			$timestamp	= mktime($dSplit[3], $dSplit[4], $dSplit[5], $dSplit[1], $dSplit[0], $dSplit[2]);
		}

		setlocale(LC_TIME, $lang . "_" . strtoupper($lang) . ".UTF-8", $lang . '_' . strtoupper($lang), strtolower($lang));
		
		// Falls englisches Format
		if($lang == "en") {
			$dateStr	= "%m/%d/%Y" . ($time ? " %H:%M" : "");
		}
		// Sonst internationales Format
		else {
			$dateStr	= "%d.%m.%Y" . ($time ? " %H:%M" : "");
		}
		
		return Language::force_utf8(strftime($dateStr, $timestamp));
	  
	}



	/**
	 * Format eines nach locale formatierten Datums
	 * 
	 * @param	string	$lang Sprache
	 * @param	string	$time Datum mit Uhrzeit ausgeben (default = false)
	 * @access	public
     * @return  string
	 */
	public static function getLocalDateFormat($lang, $time = false)
	{

		// Falls englisches Format
		if($lang == "en") {
			$dateStr	= "%m/%d/%Y" . ($time ? " %H:%M" : "");
		}
		// Sonst internationales Format
		else {
			$dateStr	= "%d.%m.%Y" . ($time ? " %H:%M" : "");
		}
		
		return $dateStr;
	  
	}



	/**
	 * Format eines nach combodate (x-editable) formatierten Datums
	 * 
	 * @param	string	$lang Sprache
	 * @param	string	$time Datum mit Uhrzeit ausgeben (default = false)
	 * @access	public
     * @return  string
	 */
	public static function getComboDateFormat($lang, $time = false)
	{

		// Falls englisches Format
		if($lang == "en") {
			$dateStr	= "MM/DD/YYYY" . ($time ? " hh:mm" : "");
		}
		// Sonst internationales Format
		else {
			$dateStr	= "DD.MM:YYYY" . ($time ? " hh:mm" : "");
		}
		
		return $dateStr;
	  
	}



	/**
	 * Gibt einen String des heutigen Datums zurück
	 * 
     * @param	boolean time falls true, Zeit mit zurückgeben (default = false)
	 * @access	public
	 * @return	string
	 */
	public static function getCurrentDate($time = false)
	{
		
		$today	= time();
		$date	= date("Y-m-d", $today);
		
		if($time)
			$date .= " " . self::getCurrentTime($today);
		
		return $date;
	}



	/**
	 * Gibt einen String der aktuellen Uhrzeit zurück
	 * 
     * @param	string Timestamp
	 * @access	public
	 * @return	string
	 */
	public static function getCurrentTime($timestamp = "")
	{
		
		if($timestamp == "")
			$timestamp = time();
		
		return date("H:i:s", $timestamp);
	}



	/**
	 * Gibt den Timestamp eines Datums zurück
	 * 
     * @param	string Datum
     * @param	string Stunde (default = 0)
     * @param	string Minute (default = 0)
     * @param	string Trennzeichen (default = -)
     * @param	string Uhrzeit
	 * @access	public
	 * @return	string
	 */
	public static function getTimestamp($date, $hour = 0, $min = 0, $sec = 0, $separator = "-", $time = "")
	{
	
		$dateArr	= explode($separator, $date);
		
		if($time != "") { // Format yyyy-mm-dd hh:mm:ss (e.g. bei "abgelaufenes Datum")
			$timeArr	= explode(":", $time);
			$timestamp	= mktime((int)reset($timeArr), (int)next($timeArr), (int)end($timeArr), (int)next($dateArr), (int)end($dateArr), (int)reset($dateArr));
		}
		
		elseif($separator == "-") // Format yyyy-mm-dd
			$timestamp	= mktime($hour,$min,$sec,(int)next($dateArr),(int)end($dateArr),(int)reset($dateArr));
		
		elseif($separator == ".") // Format dd.mm.yyyy
			$timestamp	= mktime($hour,$min,$sec,(int)next($dateArr),(int)reset($dateArr),(int)end($dateArr));
		
		return $timestamp;

	}



	/**
	 * Gibt einen landesspezifisch formatierten Preis zurück
	 * 
     * @param	string Preis
     * @param	string Sprache
	 * @access	public
	 * @return	string
	 */
	public static function getPrice($price, $lang)
	{
		
		$price = (float)$price;
		
		if($lang == "en")
			$price = number_format($price, 2, '.', ',');
		else
			$price = number_format($price, 2, ',', '.');
		
		return $price;
		
	}
	


	/**
	 * Installiert die Starrater-Klasse
	 * 
	 * @access	public
	 */
	public static function installRater()
	{

		require_once(PROJECT_DOC_ROOT."/inc/classes/Modules/class.Rating.php"); // Klasse einbinden
			
	}
	


	/**
	 * setDatePicker
	 * 
     * @param	string themeConf
	 * @access	public
     * @return	string
	 */
	public function setDatePicker($themeConf)
	{
	
		$jsFW		= isset($themeConf["jsframework"]["framework"]) ? $themeConf["jsframework"]["framework"] : "jquery";
		$jsFWV		= isset($themeConf["jsframework"]["version"]) ? $themeConf["jsframework"]["version"] : JQUERY_VERSION;
		$jsUIV		= isset($themeConf["jsframework"]["uiversion"]) ? $themeConf["jsframework"]["uiversion"] : JQUERY_UI_VERSION;
		$jsUIT		= isset($themeConf["jsframework"]["uitheme"]) ? $themeConf["jsframework"]["uitheme"] : JQUERY_UI_THEME;
		
		// Datepicker einbinden
		#$this->scriptFiles["jtimepicker"]	= "extLibs/" . $jsFW . "/ui/" . $jsFW . ".jtimepicker.js";
		$this->scriptFiles["datepickerui"]	= "extLibs/" . $jsFW . "/ui/" . $jsFW . "-ui-" . $jsUIV . ".custom-datepicker.min.js";
		$this->scriptFiles["datepicker"]	= JS_DIR . "datepicker.js";
		$this->cssFiles[]					= "extLibs/" . $jsFW . "/ui/" . $jsUIT . "/" . $jsFW . "-ui-" .$jsUIV . ".custom.min.css";
		
	}
	


	/**
	 * getNumSpinner
	 * 
     * @param	string	element
     * @param	int 	min
     * @param	int		max
     * @param	array	themeConf
	 * @access	public
     * @return	string
	 */
	public function getNumSpinner($ele, $min, $max, $themeConf)
	{
	
		// Number spinner head files
		$jsFW		= isset($themeConf["jsframework"]["framework"]) ? $themeConf["jsframework"]["framework"] : "jquery";
		$jsFWV		= isset($themeConf["jsframework"]["version"]) ? $themeConf["jsframework"]["version"] : JQUERY_VERSION;
		$jsUIV		= isset($themeConf["jsframework"]["uiversion"]) ? $themeConf["jsframework"]["uiversion"] : JQUERY_UI_VERSION;
		$jsUIT		= isset($themeConf["jsframework"]["uitheme"]) ? $themeConf["jsframework"]["uitheme"] : JQUERY_UI_THEME;
		
		// Number spinner
		$this->cssFiles[]				= "extLibs/" . $jsFW . "/ui/" . $jsUIT . "/" . $jsFW . "-ui-" .$jsUIV . ".custom.min.css";
		$this->scriptFiles["uispinner"]	= "extLibs/" . $jsFW . "/ui/" . $jsFW . "-ui-" . $jsUIV . ".custom-spinner.min.js";
		
		$output	=	'<script>' .
					'head.ready("uispinner", function(){' .
						'$(document).ready(function(){' .
							'$("' . $ele . '").spinner({min:' . $min . ', max:' . $max. '});' .
						'});' .
					'});' .
					'</script>';

		return $output;
	
	}
	


	/**
	 * Gibt eine Social-Bar zurück
	 * 
     * @param	string type
     * @param	string url
     * @param	string title
     * @param	string text
     * @param	string keywords
     * @param	string image
     * @param	string style
     * @param	boolean group buttons
	 * @access	public
     * @return	string
	 */
	public function getSocialBar($type = "default", $url = "", $title = "", $text = "", $keywords = "", $image = "", $style = "def", $groupButtons = false)
	{
	
		$sb			= "";
		$url		= urlencode($url == "" ? parent::$currentURL : $url);
		$title		= $title != "" ? urlencode($title) : "";
		$text		= $text != "" ? urlencode($text) : "";
		$keywords	= $keywords != "" ? urlencode($keywords) : "";
		$image		= $image != "" ? urlencode($image) : "";
		
		// Default
		if($type == "default") {
		
			$btn	= '{t_class:btn';
			
			if(!empty($style))
				$btn	.= $style;
			else
				$btn	.= 'def';

			$btn		.= '}';
		
			$this->scriptFiles["sharing"]	= 'access/js/sharing.js';
			
			$imgParam	= !empty($image) ? '&picture=' . $image : "";
			
			// Facebook-Button
			$btnDefs	= array(	"href"		=> 'https://www.facebook.com/sharer/sharer.php?u=' . $url . $imgParam,
									"class"		=> 'sharingButton btnFacebook ' . $btn . ' {t_class:btnsm}',
									"text"		=> "{s_title:share}",
									"attr"		=> 'rel="nofollow"',
									"icon"		=> "facebook",
									"icontext"	=> ""
								);
			
			$sb	.=	ContentsEngine::getButtonLink($btnDefs);

			// Twitter-Button
			$btnDefs	= array(	"href"		=> 'https://twitter.com/intent/tweet?url=' . $url . ($text != "" ? '&text=' . $text : "") . ($keywords != "" ? '&hashtags=' . $keywords : ""),
									"class"		=> 'sharingButton btnTwitter ' . $btn . ' {t_class:btnsm}',
									"text"		=> "{s_title:share}",
									"attr"		=> 'rel="nofollow"',
									"icon"		=> "twitter",
									"icontext"	=> ""
								);
			
			$sb	.=	ContentsEngine::getButtonLink($btnDefs);
			
			// Google +1-Button
			$btnDefs	= array(	"href"		=> 'https://plus.google.com/share?url=' . $url,
									"class"		=> 'sharingButton btnGoogle ' . $btn . ' {t_class:btnsm}',
									"text"		=> "{s_title:share}",
									"attr"		=> 'rel="nofollow"',
									"icon"		=> "googleplus",
									"icontext"	=> ""
								);
			
			$sb	.=	ContentsEngine::getButtonLink($btnDefs);
			
			// LinkedIn-Button
			$btnDefs	= array(	"href"		=> 'http://www.linkedin.com/shareArticle?mini=true&url=' . $url . ($title != "" ? '&title=' . $title : "") . ($text != "" ? '&summary=' . $text : ""),
									"class"		=> 'sharingButton btnLinkedin ' . $btn . ' {t_class:btnsm}',
									"text"		=> "{s_title:share}",
									"attr"		=> 'rel="nofollow"',
									"icon"		=> "linkedin",
									"icontext"	=> ""
								);
			
			$sb	.=	ContentsEngine::getButtonLink($btnDefs);
			
			
			// Falls die Buttons gruppiert werden sollen
			if($groupButtons)
				$sb	= '<span class="{t_class:btngroup}">' . $sb . '</span>';
		
		}				
		
				
		// AddToAny
		if($type == "addtoany") {

			// AddToAny
			$sb .=	'<!-- AddToAny BEGIN -->' . "\r\n" .
					'<div class="a2a_kit a2a_kit_size_32 a2a_default_style">' . "\r\n" .
					'<a class="a2a_dd" href="http://www.addtoany.com/share_save"></a>' . "\r\n" .
					'<a class="a2a_button_facebook"></a>' . "\r\n" .
					'<a class="a2a_button_twitter"></a>' . "\r\n" .
					'<a class="a2a_button_google_plus"></a>' . "\r\n" .
					'</div>' . "\r\n" .
					'<script type="text/javascript">var a2a_config = a2a_config || {};a2a_config.locale = "de";</script>' . "\r\n" .
					'<script type="text/javascript" src="//static.addtoany.com/menu/page.js"></script>' . "\r\n" .
					'<!-- AddToAny END -->' . "\r\n";
		}
		
		return $sb;
	}
	
} // Ende Klasse
