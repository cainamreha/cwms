<?php
namespace Concise;


/**
 * Klasse für Sprachen.
 * 
 */

class Language
{

	/**
	 * Beinhaltet ein Datenbankobjekt
	 *
	 * @access private
     * @var    object
     */
	private $DB = null;

	/**
	 * Beinhaltet ein Security-Objekt
	 *
	 * @access protected
     * @var    object
     */
	protected $o_security = null;

	/**
	 * Array mit Session-Variablen
	 *
	 * @access private
     * @var    array
     */
	private $g_Session = array();
	
	/**
	 * Sprache
	 *
	 * @access public
     * @var    string
     */
	public $lang = "";
	
	/**
	 * vorherige Sprache
	 *
	 * @access public
     * @var    string
     */
	public $oldLang = "";
	
	/**
	 * voreingestellte Sprache (Hauptsprache)
	 *
	 * @access public
     * @var    string
     */
	public $defLang = DEF_LANG;
		
	/**
	 * Beinhaltet die aktuelle Sprache des zu bearbeitenden Inhaltes
	 *
	 * @access public
     * @var    string
     */
	public $editLang = "";
	
	/**
	 * voreingestellte Sprache für den Adminbereich
	 *
	 * @access public
     * @var    string
     */
	public $adminLang = DEF_ADMIN_LANG;
	
	/**
	 * wählbare Sprachen für den Adminbereich
	 *
	 * @access public
     * @var    array
     */
	private $adminLangs = array();
	
	/**
	 * Legt den User-Agent fest
	 *
	 * @access public
     * @var    string
     */
	public $userAgent = "";
	
	/**
	 * Array mit installierten Sprachen (Länderkürzel)
	 *
	 * @access public
     * @var    array
     */
	public $installedLangs = array();
	
	/**
	 * Array mit vordefinierten Sprachen (Länderkürzel)
	 *
	 * @access public
     * @var    array
     */
	public $existingLangs = array();
	
	/**
	 * Array für die vorhandenen Sprachen (Nationalität)
	 *
	 * @access public
     * @var    array
     */
	public $existNation = array();
	
	/**
	 * Array für die vorhandenen Sprachen (Flagge)
	 *
	 * @access public
     * @var    array
     */
	public $existFlag = array();
	
	/**
	 * Statische Sprachbausteine
	 *
	 * @access public
     * @var    array
     */
	public $staText = array();


	/**
	 * Liest die Sprache aus der Session aus, falls gesetzt, sonst auf default lang.
	 * 
     * @access	public
 	 * @param	object	$DB		DB-Objekt
     * @param	verfügbare Adminsprachen (default = array())
	 */
	public function __construct($DB, $adminLangs = array())
	{
	
		// Datenbankobjekt binden
		$this->DB			= $DB;

		// Security-Objekt
		$this->o_security	= Security::getInstance();

		// Session-Vars-Array
		$this->g_Session	= Security::getSessionVars();
		
		// Adminlangs
		$this->adminLangs	= $adminLangs;

		
		// Falls nicht Installationsseite, Info zu installierten Sprachen auslesen
		if($this->DB != null && (!isset($GLOBALS['_GET']['page']) || $GLOBALS['_GET']['page'] != "_install")) {
	
			// Andernfalls Sprachen aus db auslesen
			// Suche nach aktueller Seite mit Pfad in anderer Sprache
			$query_lang = $this->DB->query("SELECT *    
											FROM `" . DB_TABLE_PREFIX . "lang` 
											ORDER by `sort_id`
											");
			
			
			foreach($query_lang as $lang) {
				
				$this->installedLangs[]	= $lang['nat_code'];
				$this->existNation[]	= $lang['nationality'];
				$this->existFlag[]		= $lang['flag_file'];
				
				if($lang['def_lang'] == 1)
					$this->defLang		= $lang['nat_code'];
			}
		}

	}



	/**
	 * Liest die Sprache aus der Session aus, falls gesetzt, sonst auf default lang.
	 * 
	 * @access	public
	 * @return	string Sprache
	 */
	public function getLang()
	{
		
		// Adminsprache auslesen
		$this->adminLang	= $this->getAdminLang();
		
		// Edit lang
		$this->editLang		= $this->getEditLang();

		
		// Falls bereits eine Sprache in der Session gesetzt ist, diese auslesen als oldLang
		if(isset($this->g_Session['lang']))
			$this->oldLang	= $this->g_Session['lang'];

		
		// Sprache auslesen, falls über Get-Parameter übermittelt...
		if(isset($GLOBALS['_GET']['lang'])) {
			$lang = $GLOBALS['_GET']['lang'];
		}
		
		// Sprache admin page...
		elseif($this->o_security->isAdminPage()) {
			$lang	= $this->editLang;
		}
		
		// Sprache auslesen, je nach subdomain...
		elseif(LANG_URL_TYPE == "subdomain" && (empty($GLOBALS['_GET']['page']) || $GLOBALS['_GET']['page'] != "admin")) {
			$lang	= preg_replace("/(http[s]?:\/\/)([a-zA-Z_-]{2,5})(\..*)/", "$2", PROJECT_HTTP_ROOT);
		}
		
		// Sprache auslesen, default LANG_URL_TYPE...
		elseif(LANG_URL_TYPE == "default" && !empty($GLOBALS['_GET']['page']) && $GLOBALS['_GET']['page'] != "admin") {
			$lang	= preg_replace("/([a-zA-Z_-]{2,5})\/(.*)/", "$1", $GLOBALS['_GET']['page']);
		}
		
		// Andernfalls Sprache aus Session auslesen, falls definiert...
		elseif(isset($this->g_Session['lang'])) {
			$lang = $this->g_Session['lang']; // gewählte Session Sprache festlegen
		}
		
		// Sonst default lang
		else {
			$lang = $this->defLang;
		}
		
		// Sonst Browsersprache auslesen und übernehmen oder default lang
		/*
		$this->getBrowserLang();
		*/
		
		// Falls Sprache unbekannt
		if(!in_array($lang, $this->installedLangs)) {
			$lang = $this->defLang;
		}

		$this->o_security->setSessionVar('lang', $lang);
		
		// aktuelle Sprache auch für Adminbereich festlegen
		$this->editLang	= $lang;
		$this->o_security->setSessionVar('edit_lang', $lang);
		
		// Falls install page
		if(!empty($GLOBALS['_GET']['page'])
		&& $GLOBALS['_GET']['page'] == "_install"
		)
			$this->adminLang = $lang;
		
	
		// Methode zum Einbinden der statischen Seitentexte aufrufen
		$this->staticText($lang, $this->adminLang);
		
		$this->lang = $lang;

		return $this->lang;
		
	}



	/**
	 * Listet Sprachen aus dem Ordner "langs" auf.
	 * 
	 * @access	public
	 * @return	array vordefinierte Sprachen aus de
	 */
	public function getExistingLangs()
	{
		
		$existingLangs	= array();
		$langDir		= PROJECT_DOC_ROOT . '/langs';
		
		if(is_dir($langDir)) {
			
			// Sprachordner ins Array einlesen
			$handle = opendir($langDir);
			
			while($content = readdir($handle)) {
				if( $content != ".." && 
					strpos($content, ".") !== 0
				) {
					$existingLangs[] = $content;
				}
			}
			closedir($handle);
			
			sort($existingLangs);
			
			return $existingLangs;
		}
		else
			die("language dir not found.");		
		
	}
	

	/**
	 * Sprache für die Adminbereich auslesen
	 * 
	 * @access public
	 * @return string
	 */
	public function getAdminLang()
	{
	
		if(!empty($this->g_Session['admin_lang']))
			$this->adminLang = $this->g_Session['admin_lang'];
		
		if(!array_key_exists($this->adminLang, $this->adminLangs))
			$this->adminLang = DEF_ADMIN_LANG;
		
		return $this->adminLang;
		
	}	
	

	/**
	 * Sprache für die Bearbeitung auslesen
	 * 
	 * @access public
	 * @return string
	 */
	public function getEditLang()
	{
	
		// Sprache für die Bearbeitung von Seiten im Adminbereich auslesen
		if(!empty($GLOBALS['_POST']['editLang'])) { // Sprache auslesen, falls über Post gesetzt
			$editLang	= $GLOBALS['_POST']['editLang'];
			$this->o_security->setSessionVar('edit_lang', $editLang);
		}

		elseif(!empty($this->g_Session['edit_lang'])) // Sprache auslesen, falls in Session gesetzt
			$editLang = $this->g_Session['edit_lang'];
			
		else {
			$editLang = $this->defLang;  // Andernfalls Sprache auf default setzen
			$this->o_security->setSessionVar('edit_lang', $editLang);
		}
		
		if(!in_array($editLang, $this->installedLangs))
			$editLang = $this->defLang;
		
		return $editLang;
		
	}	
	

	/**
	 * Browsersprache auslesen
	 * 
	 * @access public
	 * @return string
	 */
	public function getBrowserLang()
	{
	
		// Browsersprache auslesen
		$browserLang = "";
		
		// USER-AGENT auslesen
		if(isset($GLOBALS['_SERVER']['HTTP_USER_AGENT']))
			$this->userAgent = $GLOBALS['_SERVER']['HTTP_USER_AGENT'];
		
		if(Log::checkBot($this->userAgent) == true
		&& isset($GLOBALS['_SERVER']['HTTP_ACCEPT_LANGUAGE'])
		) {
			
			$browserLang = strtolower(substr($GLOBALS['_SERVER']['HTTP_ACCEPT_LANGUAGE'], 0, 2)); // Browsersprache auslesen
			
		}
	
		return $browserLang;
		
	}	
	


	/**
	 * Datei mit statischen Sprachwerten laden
	 * 
	 * @access private
     * @param    aktuelle Sprache
	 */
	private function staticText($lang, $adminLang)
	{
    	
		$defLangFile	= PROJECT_DOC_ROOT . '/langs/' . $lang . '/staticText_' . $lang . '.ini';
		$adminLangFile	= SYSTEM_DOC_ROOT . '/langs/' . $adminLang . '/staticTextAdmin_' . $adminLang . '.ini';
		$useAdminLang	= false;
		$defaultText	= array();
		$adminText		= array();
		
		// Seitentexte und Menuepunkte (statisch) in array-Variablen einlesen
		// Im Folgenden werden alle statischen Seitentexte in der jeweiligen Sprache aus dem Array "$staText" ausgelesen
		
		// Falls eine Seite des Adminbereichs angezeigt wird, Adminsprachbausteine auslesen
		if(isset($GLOBALS['_GET']['page'])
		&& ($GLOBALS['_GET']['page'] == "admin" || $GLOBALS['_GET']['page'] == "_install")
		) {
			$defLangFile	= $adminLangFile;
		}
		// sonst Projekt-spezifische Sprachbausteine ggf. plus Adminsprachbausteine einbinden
		else {
			if(isset($this->g_Session['group'])
			&& ($this->g_Session['group'] == "admin"
				|| $this->g_Session['group'] == "editor"
				|| $this->g_Session['group'] == "author")
			)
				$useAdminLang	= true;
		}
		
		// pcre.backtrack_limit hochsetzen (def 100k vor PHP 5.3.7 seitdem 1000k) = Limit für Anzahl an preg_xyz
		// In settings gesetzt
		#@ini_set('pcre.backtrack_limit',10000);
		
		if(file_exists($defLangFile)) {
			if(!$this->staText = parse_ini_file($defLangFile, true))
				die("Sprachdatei für statische Seitentexte konnte nicht eingebunden werden.");
		}
		else
			die("Sprachdatei für statische Seitentexte nicht vorhanden.");

		if($useAdminLang) {
			
			if(file_exists($adminLangFile)) {
				
				if($adminText = parse_ini_file($adminLangFile, true)) {
					$this->staText		= array_merge_recursive_simple($adminText, $this->staText);
					$GLOBALS['staText'] = $this->staText;
				}
				else
					die("Sprachdatei für statische Seitentexte (Admin) konnte nicht eingebunden werden.");
			}
			else
				die("Sprachdatei für statische Seitentexte (Admin) nicht vorhanden.");
		}
		
	}	


	/**
	 * Sprachauswahl Schaltfläche generieren
	 * 
	 * @access public
     * @param   Seiten-ID
     * @param   Typ (default = flag)
     * @param	Trennzeichen (default =  | )
     * @param	Aktive Sprache anzeigen, falls true (default = true)
     * @param	Installationsseite, falls true (default = false)
	 * @return	string
	 */
	public function getLangSelector($pageId, $type = "flag", $separator = " | ", $showActiveLang = true, $install = false)
	{
    	
		$return	= '<div id="changeLang" class="' . ($type == "flag" ? 'flagType' : 'textType') . ' langSelector">' . "\r\n" .
				  '<ul class="langList {t_class:pills}">' . "\r\n";
				  
		$qs		= "";
		$i		= 0;
		$j		= 1;
		
		foreach($this->installedLangs as $lang) {
			
			if($showActiveLang || $lang != $this->lang) {
				
				if($separator != "" && $j > 1)
					$return .=  '<span class="seperator sep-' . $j . '">' . $separator . '</span>';
				
				$return .= '<li class="lang-' . $j . ($lang == $this->lang ? ' activeLang {t_class:active}' : '') . '">' . "\r\n";
				
				if($type == "flag")
					$o_lngOutput = '<span class="langItem"><img src="' . PROJECT_HTTP_ROOT . '/langs/' . $lang . '/' . $this->existFlag[$i] . '" alt="' . $this->existNation[$i] . '" title="' . $this->existNation[$i] . '" /></span>';
				else
					$o_lngOutput = '<span class="langItem">' . $this->existNation[$i] . '</span>';
					
				if($lang == $this->lang)
					$return .= '<a href="#">' .
								$o_lngOutput .
								'</a>' . "\r\n";
				
				else {
					
					if(!$install) {
						$qs			= ContentsEngine::getQueryStr();
						$link		= HTML::getLinkPath($pageId, $lang, true, true);
					}
					else
						$link		= PROJECT_HTTP_ROOT . '/' . "install.html";
					
					$return .=	'<a href="' . $link . '?lang=' . $lang . ($qs != "" ? '&' . $qs : '') . '">' .
								$o_lngOutput .
								'</a>' . "\r\n";
				
				}
				
				$return .= '</li>' . "\r\n";
				
				$j++;
			}
				
			$i++;
			
		} // Ende foreach
		
		$return .=	'</ul>' . "\n" .
					'</div>' . "\r\n";
		
		return $return;
		
	}



	/**
	 *  Methode zur Umwandlung vom Datumsformat
	 * 
	 * @access	public
     * @param	aktuelles Datum
     * @param	aktuelle Sprache
	 * @return	string
	 */
	public function changeDateFormat($date, $lang)
	{
		
		$dateArr	= explode(" ", $date);
		$dateStr	= reset($dateArr);
		$timeStr	= substr(end($dateArr), 0, 5);
		
		list($y, $m , $d) = explode("-", $dateStr);
		
		// Ausgabeformat je nach Sprache
		if($lang == "de")
			$dateStr = $d.".".$m.".".$y." ".$timeStr;
		else
			$dateStr = $m."-".$d."-".$y." ".$timeStr;
	
		return $dateStr;

	}



	/**
	 * Prüft einen String auf UTF-8-Kompatibilität.
	 *
	 * RegEx von Martin Dürst
	 *
	 * @source http://www.w3.org/International/questions/qa-forms-utf-8.html
	 * @access	public
	 * @param string $str String to check
	 * @return boolean
	 */
	public static function is_utf8($str)
	{
	// pcre.backtrack_limit hochsetzen (def 100k vor PHP 5.3.7 seitdem 1000k) = Limit für Anzahl an preg_xyz
	// Sonst Probleme mit Apache Appcrash!!!
	// In settings gesetzt
	@ini_set('pcre.backtrack_limit',1000);
	@ini_set('pcre.recursion_limit',1000);

		return preg_match("/^(
			 [\x09\x0A\x0D\x20-\x7E]            # ASCII
		   | [\xC2-\xDF][\x80-\xBF]             # non-overlong 2-byte
		   |  \xE0[\xA0-\xBF][\x80-\xBF]        # excluding overlongs
		   | [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}  # straight 3-byte
		   |  \xED[\x80-\x9F][\x80-\xBF]        # excluding surrogates
		   |  \xF0[\x90-\xBF][\x80-\xBF]{2}     # planes 1-3
		   | [\xF1-\xF3][\x80-\xBF]{3}          # planes 4-15
		   |  \xF4[\x80-\x8F][\x80-\xBF]{2}     # plane 16
		  )*$/xU",
		  $str);
	}



	/**
	 * Versucht, einen String nach UTF-8 zu konvertieren.
	 *
	 * @author	Thomas Scholz <http://toscho.de>
	 * @access	public
	 * @param	string $str Zu kodierender String
	 * @param	string $inputEnc Vermutete Kodierung des Strings
	 * @return	string
	 */
	public static function force_utf8($str, $inputEnc='UTF-8')
	{
		if (self::is_utf8($str) )
		{
			// Nichts zu tun.
			return $str;
		}
	
		if ( strtoupper($inputEnc) == 'ISO-8859-1')
		{
			return utf8_encode($str);
		}
	
		if ( function_exists('mb_convert_encoding') )
		{
			return mb_convert_encoding($str, 'UTF-8', $inputEnc);
		}
	
		if ( function_exists('iconv') )
		{
			return iconv($inputEnc, 'UTF-8', $str);
		}
	
		else
		{
			// Alternativ kann man auch den Originalstring ausgeben.
			trigger_error(
			'Kann String nicht nach UTF-8 kodieren in Datei '
			. __FILE__ . ', Zeile ' . __LINE__ . '!', E_USER_ERROR);
		}
	}


} // Ende class language



// Function zum rekursiven Zusammenführen von Arrays
function array_merge_recursive_simple() {

	if (func_num_args() < 2) {
		trigger_error(__FUNCTION__ .' needs two or more array arguments', E_USER_WARNING);
		return;
	}
	$arrays = func_get_args();
	$merged = array();
	while ($arrays) {
		$array = array_shift($arrays);
		if (!is_array($array)) {
			trigger_error(__FUNCTION__ .' encountered a non array argument', E_USER_WARNING);
			return;
		}
		if (!$array)
			continue;
		foreach ($array as $key => $value)
			if (is_string($key))
				if (is_array($value) && array_key_exists($key, $merged) && is_array($merged[$key]))
					$merged[$key] = call_user_func(__FUNCTION__, $merged[$key], $value);
				else
					$merged[$key] = $value;
			else
				$merged[] = $value;
	}
	return $merged;
}
