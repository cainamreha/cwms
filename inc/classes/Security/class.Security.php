<?php
namespace Concise;


/**
 * Sicherheitsfunktionen
 * 
 * 
 */

class Security
{

	/**
	 * Security instance
	 * 
	 * @access private
	 */
	private static $_instance = null;
	
	/**
	 * Session-Vars
	 * 
	 * @access private
	 */
	private static $g_Session = null;
	
	/**
	 * Cookie-Vars
	 * 
	 * @access private
	 */
	private static $g_Cookie = null;
	
	/**
	 * Erstellter Formulartoken
	 * 
	 * @access private
	 */
	private static $token = "";

	/**
	 * Überprüft den Formulartoken und Sessiontoken und gibt einen Token zurück
	 * 
	 * @access private
	 */
	private $tokenCheck = "";

	/**
	 * Überprüft den Formulartoken und Sessiontoken und gibt einen Token zurück
	 * 
	 * @access private
	 */
	private $tokenCheckSes = "";

	/**
	 * Enhält Info über Gültigkeit des Formulartokens (bool)
	 * 
	 * @access private
	 */
	private static $tokenOK = false;

	/**
	 * Loginstatus
	 * 
	 * @access private
	 */
	private $loginStatus = false;

	/**
	 * Loginrefresh
	 * 
	 * @access private
	 */
	private $loginRefresh = false;
 
    /**
     * Benutzername
     *
     * @access private
     * @var    string
     */
    private $loggedUser = "";
 
    /**
     * Benutzer-ID
     *
     * @access private
     * @var    string
     */
    private $loggedUserID = "";
 
    /**
     * Benutzergruppe
     *
     * @access private
     * @var    string
     */
    private $group = "public";
 
    /**
     * Eigene Benutzergruppen
     *
     * @access private
     * @var    array
	 */
    private $ownGroups = array();

	/**
	 * Admin eingeloggt
	 * 
	 * @access private
	 */
	private $adminLog = false;

	/**
	 * Editor oder Admin eingeloggt
	 * 
	 * @access private
	 */
	private $editorLog = false;

	/**
	 * Author, Editor oder Admin eingeloggt
	 * 
	 * @access private
	 */
	private $backendLog = false;

	/**
	 * Adminseite
	 * 
	 * @access private
	 */
	private $adminPage = false;


	/**
	 * Leerer Konstruktor (Singleton)
	 * 
	 * @access private
	 */
	private function __construct() {}


	/**
	 * Cloning verhindern (Singleton)
	 * 
	 * @access private
	 */
	private function __clone() {}
	
	
	/**
     * Instanz über statische Methode zurückgeben
     *
     * @access	public
     * @static
     * @return Singleton
     */
    public static function getInstance()
	{
 
        if( static::$_instance === null) {
			static::$_instance = new static();
        }
        return static::$_instance;
	
    }
	
	
	/**
     * Getter vars
     *
     * @access	public
     * @return private var
     */
    public function get($param)
	{
 
        if(!empty($param)
		&& isset($this->$param)
		)
        
        return $this->$param;
	
    }	
	
	
	/**
	 * Gibt das globale Session-Array zurück
	 * 
	 * @access public
	 */
	public static function getSessionVars()
	{
	
        if( static::$g_Session === null) {
			static::$g_Session = isset($GLOBALS['_SESSION']) ? $GLOBALS['_SESSION'] : array();
        }
        return static::$g_Session;
	
	}
	
	
	/**
	 * Setzt eine Session-Variable
	 * 
	 * @param string var key
	 * @param string var value
	 * @access public
	 */
	public function setSessionVar($key, $val)
	{
	
        $GLOBALS['_SESSION'][$key]	= $val;
	
	}

	
	/**
	 * Löscht eine Session-Variable
	 * 
	 * @param	string $key Session-Variable
	 * @access	public
     * @return  boolean
	 */
	public function unsetSessionKey($key)
	{
	
		$result	= false;
		
		if(isset($GLOBALS['_SESSION'][$key])) {
			unset($GLOBALS['_SESSION'][$key]);
			$result	= true;
		}
		if(isset(static::$g_Session[$key])) {
			unset(static::$g_Session[$key]);
			$result	= true;
		}
		return $result;
	
	}
	
	
	/**
	 * Gibt das globale Cookie-Array zurück
	 * 
	 * @access public
	 */
	public static function getCookies()
	{
	
        if( static::$g_Cookie === null) {
			static::$g_Cookie = $GLOBALS['_COOKIE'];
        }
        return static::$g_Cookie;
	
	}
	
	
	/**
	 * Gibt das globale Cookie-Array zurück
	 * 
	 * @access public
	 */
	public static function getCookie($name)
	{
	
        if(isset(static::$g_Cookie[$name]))
			return static::$g_Cookie[$name];
		else
			return "";
	
	}


	/**
	 * Token verifizieren
	 * 
	 * @access public
	 */
	public function checkToken()
	{
	
		// Token auslesen
		if(isset($GLOBALS['_POST']['token'])) {
			
			$this->tokenCheck = $GLOBALS['_POST']['token']; // Post-Token
				
			if(isset(static::$g_Session['token'])) { // Sessionvariable für token auslesen, falls gesezt
				$this->tokenCheckSes = static::$g_Session['token']; // Session-Token
				self::unsetSessionKey('token');
			}
			static::$tokenOK = $this->verifyToken();
		}
		
		return static::$tokenOK;
	
	}
	


	/**
	 * Überprüft den Formulartoken und Sessiontoken
	 * 
	 * @access public
	 */
	public function verifyToken()
	{
		#die(var_dump($this->tokenCheck . "<br>". $this->tokenCheckSes));
		if($this->tokenCheck == $this->tokenCheckSes)
		
			return true;
			
		else
		
			return false;
	
	}
	
	
	
	/**
	 * Gibt einen (Formular-)Token zurück
	 * 
	 * @access public
	 */
	public function getToken()
	{
	
		static::$token = md5(session_id().(time()*rand(1,5))); // Token erstellen
				
		return static::$token;
	
	}
	
	
	
	/**
	 * Setzt einen Sessiontoken
	 * 
	 * @param string token
	 * @access public
	 */
	public function setToken($token)
	{
	
		$GLOBALS['_SESSION']['token']	= $token; // Token an Session übergeben

	}
	
	
	
	/**
	* Diese Methode korrigiert alle übergebenen Parameter (Slashes)
	* 
	* Egal ob POST oder GET-Parameter, die Methode berichtigt beide Arrays und
	* entfernt die Slashes, die durch PHP automatisch eingefügt wurden.
	* 
	*/
	public static function globalStripSlashes()
	{

		if (get_magic_quotes_gpc() == 1)
		{
			isset($GLOBALS['_GET']) ? $GLOBALS['_GET'] = array_map(array ('self', 'recursiveStripSlashes'), $GLOBALS['_GET']) : '';
			isset($GLOBALS['_POST']) ? $GLOBALS['_POST'] = array_map(array ('self', 'recursiveStripSlashes'), $GLOBALS['_POST']) : '';
		}

	}


	/**
	 * Rekursive Hilfsfunktion zur Entfernung von Backslashes
	 * 
	 * @param varchar Wert, dessen Slashes entfernt werden sollen
	 * 
	 * @return Gibt den übergebenen Wert ohne Slashes zurück
	 */
	private static function recursiveStripSlashes($value)
	{
		//Prüfen, ob der Wert ein Array ist
		if (is_array($value))
		{
			//Rekursiver Aufruf dieser Methode 
			return array_map(array ('self', 'recursiveStripSlashes'), $value);
		}
		else
		{
			//Rückgabe des berichtigten Wertes
			return stripslashes($value);
		}
	}



	/**
	 * Überprüft, ob ein User ausgeloggt werden soll und führt ggf. Logout aus
	 * 
	 * @param object DB
	 * @return boolean
	 */
	public function checkLogout($DB)
	{
	
		if(empty($DB))
			return false;
		
		// Falls auf logout geklickt wurde
		if(isset($GLOBALS['_GET']['logout']) && $GLOBALS['_GET']['logout'] == "true")
			require_once(PROJECT_DOC_ROOT . "/inc/logout.inc.php");
			
		return true;
		
	}



	/**
	 * Überprüft, ob Website im Live-Betrieb ist
	 * 
	 * @return boolean LiveMode ist true oder false
	 */
	public function checkLiveMode()
	{

		// Falls kein LiveMode und kein Backendbenutzer geloggt ist
		if(!WEBSITE_LIVE && !$this->backendLog) {

			// Falls Staging login
			if(isset($GLOBALS['_GET']['page'])
			&& ($GLOBALS['_GET']['page'] == "_login"
			||  $GLOBALS['_GET']['page'] == "login")
			) {
				if( !isset($GLOBALS['_GET']['staginglog']) || 
					$GLOBALS['_GET']['staginglog'] != 1 || 
					!isset($GLOBALS['_POST']['username'])
				)
					header("Location: " . PROJECT_HTTP_ROOT . "/_login.html") . exit;
			}
			// Andernfalls Platzhalterseite
			else
				header("Location: " . PROJECT_HTTP_ROOT . "/_index.html?paused=1") . exit;
		}
	}


	/**
	 * Überprüft, ob ein Benutzer angemeldet ist
	 * 
	 * @param object DB
	 * @return boolean Loginstatus ist true oder false
	 */
	public function checkLoginStatus($DB)
	{

		if(empty($DB))
			return false;
		
		
		// Falls username und group gesetzt
		if(isset(static::$g_Session['username'])
		&& isset(static::$g_Session['group'])
		) {
				
			// Zugangsberechtigung festlegen
			$this->group		= $this->getUserGroup();
			$this->ownGroups	= $this->getUserGroup("own_groups");
			$this->setAccessLevel($this->group);
			$this->getLoggedUserDetails();
			$this->loginStatus	= true;
			return true;
		}


		// Falls ein Cookie zum "eingelogged bleiben" gesetzt wurde
		if(!empty($GLOBALS['_COOKIE']['conciseLog'])) { // Falls rememberMe gesetzt
			
			$rememberCode = $GLOBALS['_COOKIE']['conciseLog'];
			
			$sql = $DB->query( "SELECT *, UNIX_TIMESTAMP(`last_log`) AS lastlog 
									FROM `" . DB_TABLE_PREFIX . "user` 
								WHERE `logID` = '".$DB->escapeString($rememberCode)."'
								");
			
			if(is_array($sql)
			&& count($sql) == 1
			&& !empty($sql[0]['userid'])
			) {
				
				// Zugangsberechtigung festlegen
				$this->loginStatus						= true;
				$this->loginRefresh						= true;
				
				// Benutzername
				$this->loggedUser						= $sql[0]['username'];
				
				// Benutzer-ID
				$this->loggedUserID						= (int)$sql[0]['userid'];

				// Gruppen
				$this->group							= $sql[0]['group'];
				
				if($sql[0]['own_groups'] != "")
					$this->ownGroups					= explode(",", $sql[0]['own_groups']);
				
				$this->setAccessLevel($this->group);
				
				$userID									= str_pad($this->loggedUserID, 9, '0', STR_PAD_LEFT);
				$lastLog								= date("d.m.Y H:i", $sql[0]['lastlog']);
				
				// update global session
				$GLOBALS['_SESSION']['userid']			= $userID;
				$GLOBALS['_SESSION']['username']		= $this->loggedUser;
				$GLOBALS['_SESSION']['group']			= $this->group;
				$GLOBALS['_SESSION']['own_groups']		= $this->ownGroups;
				$GLOBALS['_SESSION']['admin_lang']		= $sql[0]['lang'];
				$GLOBALS['_SESSION']['loggedInSince']	= $lastLog;
				$GLOBALS['_SESSION']['author_name']		= $sql[0]['author_name'];
				$GLOBALS['_SESSION']['usermail']		= $sql[0]['email'];
				$GLOBALS['_SESSION']['at_skin']			= $sql[0]['at_skin'];
				
				// update session array var
				static::$g_Session['userid']			= $userID;
				static::$g_Session['username']			= $this->loggedUser;
				static::$g_Session['group']				= $this->group;
				static::$g_Session['own_groups']		= $this->ownGroups;
				static::$g_Session['admin_lang']		= $sql[0]['lang'];
				static::$g_Session['loggedInSince']		= $lastLog;
				static::$g_Session['author_name']		= $sql[0]['author_name'];
				static::$g_Session['usermail']			= $sql[0]['email'];
				static::$g_Session['at_skin']			= $sql[0]['at_skin'];
				
				// Refresh Cookie conciseLog
				$exp = time()+60*60*24*7;
				setcookie("conciseLog", $rememberCode, $exp, "/");
				
				return true;
			}
		}
		
		$this->loginStatus = false;
		return false;

	}


	/**
	 * Gibt die Benutzergruppe(n) des eingeloggten Benutzers zurück
	 * 
	 * @access	private
	 * @param	varchar	groupType		Art der Benutzergruppe (default = 'group')
	 * @return	string|array	Usergroup
	 */
	private function getUserGroup($groupType = "group")
	{
	
		if(isset(static::$g_Session[$groupType]) && static::$g_Session[$groupType] != "")
			return static::$g_Session[$groupType];
		elseif($groupType == "group")
			return "public";
		else
			return array();
		
	}


	/**
	 * Gibt Daten des eingeloggten Benutzers zurück
	 * 
	 * @access	private
	 */
	private function getLoggedUserDetails()
	{
		
		// Benutzername
		$this->loggedUser	= $this->getLoggedUserName();
		
		// Benutzer-ID
		$this->loggedUserID = $this->getLoggedUserID();
		
	}


	/**
	 * Gibt Benutzernamen des eingeloggten Benutzers zurück
	 * 
	 * @access	public
	 */
	private function getLoggedUserName()
	{
		
		// Benutzername
		if(isset(static::$g_Session['username']) && static::$g_Session['username'] != "")
			return static::$g_Session['username'];
	
		return "";
	
	}


	/**
	 * Gibt ID des eingeloggten Benutzers zurück
	 * 
	 * @access	public
	 */
	private function getLoggedUserID()
	{
		
		// Benutzer-ID
		if(isset(static::$g_Session['userid']) && static::$g_Session['userid'] != "")
			return (int)static::$g_Session['userid'];
	
		return "";
	
	}


	/**
	 * Legt die Zugangsberechtigung des eingeloggten Benutzers fest
	 * 
	 * @access	private
	 * @param	varchar	group		Benutzergruppe
	 */
	private function setAccessLevel($group)
	{
	
		// Backend-Logs
		if($group == "author") {
			$this->backendLog	= true; // Admin-Log
		}
		if($group == "editor") {
			$this->editorLog	= true; // Editor-Log
			$this->backendLog	= true; // Backend-Log
		}
		if($group == "admin") {
			$this->adminLog		= true; // Admin-Log
			$this->editorLog	= true; // Editor-Log
			$this->backendLog	= true; // Backend-Log
		}
		
		// Überprüfen ob Adminseite
		$this->adminPage		= self::isAdminPage();		
		
		require_once(PROJECT_DOC_ROOT . "/inc/classes/Admin/class.Admin.php"); // Adminklasse einbinden
	
	}


	/**
	 * Legt die Zugangsberechtigung des eingeloggten Benutzers fest
	 * 
	 * @return	string	Usergroup
	 */
	public static function isAdminPage()
	{

		// Admin-Page auf true setzen, falls Admin- oder Installationsseite
		if(isset($GLOBALS['_GET']['page']) && 
		  ($GLOBALS['_GET']['page'] == "admin" || $GLOBALS['_GET']['page'] == "_install")
		) {
			return true;
		}
			
		return false;
	}


	/**
	 * Gibt ein neu generiertes Password zurück
	 * 
	 * @param int	 $length	Länge des Passworts
	 * @return varchar
	 */
	public static function generatePassword($length = "")
	{

		$pwd	= "";
		$length	= (int)!empty($length) && is_numeric($length) ? PASSWORD_AUTO_LENGTH : $length;
		
		for ($i = 0; $i < PASSWORD_AUTO_LENGTH; $i++)
		{
			switch (rand(0, 5))
			{
				case 0 :
				case 1 : //Kleinbuchstabe
					$pwd = $pwd.chr(rand(97, 122));
					break;
				case 2 :
				case 3 : //Großbuchstabe anfügen
					$pwd = $pwd.chr(rand(65, 90));
					break;
				case 4 : //Sonderzeichen
					$pwd = $pwd.chr(rand(35, 38));
					break;
				case 5 : //Ziffer
					$pwd = $pwd.rand(0, 9);
					break;
			}
		}
		return $pwd;
	}


	/**
	 * Verifiziert ein übergebenes Passwort
	 * 
	 * @param varchar password		zu verifizierendes Passwort
	 * @param boolean restrictive	Methode der Überprüfung (default = false)
	 * @return boolean/string		Passwort ist entweder gültig oder nicht 
	 */
	public static function verifyPassword($password, $restrictive = false)
	{
		
		if(!$restrictive) {

			if($password == "")
				return "{s_error:fill}";
			elseif(!preg_match("/^[a-zA-Z0-9\$§&#_-]+$/", $password))
				return "{s_error:wrongpass}";
			elseif(mb_strlen($password, "UTF-8") < PASSWORD_MIN_LENGTH)
				return sprintf(ContentsEngine::replaceStaText("{s_error:passlen1}"), PASSWORD_MIN_LENGTH);
			elseif(mb_strlen($password, "UTF-8") > PASSWORD_MAX_LENGTH)
				return sprintf(ContentsEngine::replaceStaText("{s_error:passlen2}"), PASSWORD_MAX_LENGTH);
			else
				return true;
		}
		else {
				
			//Die einzelnen Regeln überprüfen:
			
			//Mindestlänge
			if(strlen($password) < PASSWORD_MIN_LENGTH)
				return false;
	
			//Dann verifizieren, dass nur die erlaubten Sonderzeichen
			//sowie Ziffern und Buchstaben drin sind.
			$regexp = '/[^\!|\"|\#|\$|\%|\&|\d|a-zA-Z0-9]/';
			//Diese Zeichen dürfen drin vorkommen.
			//sind aber verneint...wenn also etwas anderes drin vorkommt..
			//wird die 1 zurückgegeben.	 
			$i = preg_match($regexp, $password);
			if ($i == 1)
				return false;
	
			$empty = array ();
			
			//Mindestens zwei Ziffern:
			$i = preg_match_all('/[0-9]/', $password, $empty);
			if ($i < 2)
				return false;
	
			//Groß- und Kleinbuchstaben:
			//also abbrechen, wenn nicht ein einziger 
			//Großbuchstabe vorhanden ist.
			$i = preg_match_all('/[A-Z]/', $password, $empty);
			if ($i == 0)
				return false;
	
			//Auch abbrechen, wenn nicht ein einziger 
			//Kleinbuchstabe vorhanden ist.
			$i = preg_match_all('/[a-z]/', $password, $empty);
			if ($i == 0)
				return false;
	
			return true;
		}
		
	}


	/**
	 * Überprüft ein übergebenes Passwort
	 * 
	 * @param varchar password		Passwort
	 * @param varchar salt			Salt
	 * @return string
	 */
	public static function hashPassword($password, $salt)
	{
	
		return hash('sha256', $password . $salt);
	
	}
	

	/**
	 * Funktion für sicheres mysql
	 * 
	 * @param varchar zu überprüfender Wert, Typ des zu überprüfenden Wertes
	 * 
	 * @return varchar sicherer String 
	 */
	public static function getSQLValueString($theValue, $theType, $theDefinedValue = "", $theNotDefinedValue = "") 
	{
	  if (PHP_VERSION < 6) {
		$theValue = get_magic_quotes_gpc() ? stripslashes($theValue) : $theValue;
	  }
	
	  $theValue = mysql_escape_string($theValue);
	
	  switch ($theType) {
		case "text":
		  $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
		  break;    
		case "long":
		case "int":
		  $theValue = ($theValue != "") ? intval($theValue) : "NULL";
		  break;
		case "float":
		  $theValue = ($theValue != "") ? floatval($theValue) : "NULL";
		  break;
		case "double":
		  $theValue = ($theValue != "") ? doubleval($theValue) : "NULL";
		  break;
		case "date":
		  $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
		  break;
		case "defined":
		  $theValue = ($theValue != "") ? $theDefinedValue : $theNotDefinedValue;
		  break;
	  }
	  return $theValue;
	}



	/**
	 * Methode zur Bearbeitung von Formulareingaben zur Erstellung eines "sicheren" Textes
	 * 
	 * @param varchar zu überprüfender String
	 * @return varchar sicherer String 
	 */
	public static function secString($string)
	{
		$secString = htmlspecialchars(trim($string));
		return $secString;
	}


}
