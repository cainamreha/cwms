<?php
namespace Concise;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Concise\Events\UserAuthentificationEvent;


require_once PROJECT_DOC_ROOT."/inc/classes/User/events/event.UserAuthentificationEvent.php";

/**
 * Klasse für Login-Funktionen
 * 
 *
 */

class Login extends ContentsEngine
{

	const usernameMaxLen		= 255;
    
	/**
	 * User IP address
	 * 
	 * @access private
     * @var    int
	 */
	private $userIP = "";

	/**
	 * Anzahl an Login-Fehlversuchen (badLogins)
	 * 
	 * @access private
     * @var    int
	 */
	private $badLogins = 0;

	/**
	 * Anzahl an Login-Fehlversuchen (badLogins)
	 * 
	 * @access private
     * @var    int
	 */
	private $bannedUser = false;

	/**
	 * Remember user login
	 * 
	 * @access private
     * @var    boolean
	 */
	private $rememberLogin = false;

	/**
	 * Anzeigen des Formulars zum Anfordern eines neuen Passworts
	 * 
	 * @access private
     * @var    boolean
	 */
	private $forgotPass = false;

	/**
	 * Anzeigen einer Willkommensseite nach erfolgreichem Login
	 * 
	 * @access private
     * @var    boolean
	 */
	private $welcomePage = false;

	/**
	 * true, falls bereits ein Benutzer eingeloggt ist
	 * 
	 * @access private
     * @var    boolean
	 */
	private $isLogged = false;
	
	/**
	 * Benutzer-ID Pad String
	 *
	 * @access private
     * @var    string
     */
	private static $loggedUserIDStr = "";
	
	/**
	 * checkScript
	 *
	 * @access private
     * @var    string
     */
	private $checkScript = "";
	
	/**
	 * Notice tags
	 *
	 * @access protected
     * @var    string
     */
	protected $noticeOpenTag = "";
	protected $noticeClsoeTag = "";


	/**
	 * Stellt ein Login-Formular dar
	 * 
 	 * @param	object	$DB				DB-Objekt
 	 * @param	object	$o_lng			Sprachobjekt
     * @param	string	$o_dispatcher	Event dispatcher Objekt
	 * @access	public
    */
	public function __construct($DB, $o_lng, $o_dispatcher)
	{
		
		// Datenbankobjekt
		$this->DB	= $DB;
		
		// Sprache
		$this->lang	= $o_lng->lang;
		
		// Event dispatcher Objekt
		$this->o_dispatcher	= $o_dispatcher;		

		if($this->o_dispatcher === null)		
			$this->initEventDispatcher();
		
		// Events
		// UserAuthentificationEvent
		$this->o_userAuthentificationEvent	= new UserAuthentificationEvent($this->DB, $o_lng);		
		
		// Security-Objekt
		$this->o_security	= Security::getInstance();		

		// Session-Vars-Array
		$this->g_Session	= Security::getSessionVars();

		$this->loggedUser	= $this->o_security->get('loggedUser');
		$this->loggedUserID	= $this->o_security->get('loggedUserID');
		$this->group		= $this->o_security->get('group');
		$this->adminPage	= $this->o_security->get('adminPage');

		$this->checkScript	= PROJECT_HTTP_ROOT . "/_checkLogin.html";
		
		$this->noticeOpenTag	= '<span class="notice {t_class:texterror}">';
		$this->noticeCloseTag	= '</span>';
	
	}
	
	
	/**
	 * initEventDispatcher
	 * 
	 * @access	public
    */
	public function initEventDispatcher()
	{
	
		// EventDispatcher-Objekt
		$this->o_dispatcher	= new EventDispatcher();
		
		// Aktive Plugins aus DB
		$this->activePlugins	= $this->getActivePlugins(true);

		// Aktive Plug-ins anmelden
		$this->registerPlugIns();
		
		// Globale Event Listener anmelden
		$this->addEventListeners("global");
	
		// User event listeners
		$this->addEventListeners("user");
	
	}	
	
	
	/**
	 * Stellt ein Login-Formular dar.
	 * 
	 * @access	public
     * @param	string Art des Loginformulars (default = default)
     * @param	string Name des Skripts für die Formularauswertung (default = '')
    */
	public function printLoginForm($type = "default", $checkScript = "")
	{
		
		// Falls ein Get-Parameter zum Anzeigen eines Formulars für die Anforderung eines neuen Passworts vorliegt
		if(isset($GLOBALS['_GET']['fp']) && $GLOBALS['_GET']['fp'] == "1" && $type == "default") {
		
			$this->forgotPass = true;
		
		}

		// Falls sich ein Besucher eingeloggt hat, Willkommensseite anzeigen
		elseif(isset($GLOBALS['_GET']['login']) && $GLOBALS['_GET']['login'] == "1" && $this->o_security->get('loginStatus') === true) {
		
			$this->welcomePage = true;
		
		}

		// Falls bereits ein Besucher eingeloggt ist, Logout-Button statt Loginformular anzeigen
		elseif($this->o_security->get('loginStatus') === true) {
		
			$this->isLogged = true;
		
		}

		// Check script
		if($checkScript != "")
			$this->checkScript	= $checkScript;
	
	
		// Falls erfolgreicher Login
		if($this->welcomePage === true && $type == "default") {
		
			$output = 		'<div class="{t_class:fullrow} {t_class:margintm} {t_class:marginbm}">' . "\n" .
							'<h2>{s_header:login}</h2>' . "\n" .
							'<p>{s_header:welcome} ' . $this->loggedUser . '.</p>' . "\n" .
							'</div>' . "\n";
			
			return $output;
		}
		
		// Falls bereits ein Benutzer eingelogged ist
		if($this->isLogged === true) {
			
			$userImage	= "";
		
			// Falls Adminbereich, Benutzerbild hinzufügen
			if($this->adminPage)
				$userImage	= 	self::getUserImage($this->loggedUserID);
			
			$output		=	'<div' . ($type == "default" ? ' id="logForm"' : '') . ' class="logForm loggedIn' . ($type == "default" ? ' {t_class:margintm} {t_class:marginbm}' : ' {t_class:panel}') . '">' . "\n" . 
							'<h2 class="logFormHeader cc-section-heading cc-h2 {t_class:panelhead} {t_class:marginbm}">{s_header:login}</h2>' . "\n";
						
			// Verlorene Session Meldung
			if(isset($GLOBALS['_GET']['timeout']) && $GLOBALS['_GET']['timeout'] == "1" && $type == "default")
				$output .= $this->getNotificationStr("{s_text:errorserver}", "error");
			
		
			$output .= 		'<p>' . $userImage . '{s_text:loggedinas} <span class="loggedUser">' . $this->loggedUser . '</span></p>' . "\n" .
							'<br class="clearfloat" />' . "\n";
						
			// Falls ein Button zum Benutzeraccount angezeigt werden soll (Adminbereich)
			if($this->adminPage) {

				$output .= 	'<form class="{t_class:form}" method="post" action="' . ADMIN_HTTP_ROOT . '?task=user" data-getcontent="fullpage">' . "\r\n";
			
				// Button user
				$btnDefs	= array(	"type"		=> "submit",
										"name"		=> "edit_user",
										"id"		=> "submit",
										"class"		=> 'editAccount formbutton {t_class:btnpri}',
										"value"		=> "{s_label:moduserdetails}",
										"icon"		=> "user"
									);
					
				$output .=	parent::getButton($btnDefs);
				
				$output .=	'<input type="hidden" value="' . $this->loggedUser . '" name="edit_user">' . "\r\n" .
							'</form>' . "\r\n";
			}
			
			// Falls ein Button zu "mein Bereich" angezeigt werden soll
			elseif((
					(REGISTRATION_TYPE == "account" || REGISTRATION_TYPE == "shopuser") && 
					!empty($this->loggedUserID) && 
					!empty($this->loggedUser) && 
					!empty($this->group) && 
					($this->group == "guest" || in_array($this->group, $GLOBALS['ownUserGroups']))
				   ) || $this->o_security->get('backendLog')
			) {
				$output .= 	'<p>' . "\r\n";
			
				// Button link user
				$btnDefs	= array(	"href"		=> PROJECT_HTTP_ROOT . '/' . HTML::getLinkPath(($this->o_security->get('backendLog') ? -1001 : -1007), $this->lang, ($this->o_security->get('backendLog') ? false : true)),
										"class"		=> 'admin account formbutton {t_class:btnpri}' . ($type == "small" ? ' {t_class:btnblock}' : ''),
										"text"		=> "{s_link:account}",
										"icon"		=> "user"
									);
					
				$output .=	parent::getButtonLink($btnDefs);
				
				$output .=	'</p>' . "\r\n";
			}
			
			$output .= 		'<p>' . "\r\n";
			
			// Button link logout
			$btnDefs	= array(	"href"		=> "?logout=true",
									"class"		=> 'logoutUser logout account formbutton alt {t_class:btnsec}' . ($type == "small" ? ' {t_class:btnblock}' : ''),
									"text"		=> (!isset($GLOBALS['_GET']['page']) || $GLOBALS['_GET']['page'] != "admin" ? '{s_text:logoutuser}' : $this->loggedUser . ' {s_title:logout}'),
									"icon"		=> "logout"
								);
				
			$output .=	parent::getButtonLink($btnDefs);
			
			$output .=		'</p>' . "\n";
							
			$output .= 		'<br class="clearfloat" />' . "\n" .
							'</div>' . "\r\n";
						
			return $output;
		}
		
		// Logform forgotPass ausgeben
		if($this->forgotPass)
			return $this->forgotPass();
		
		
		// Logform ausgeben
		$formExt		= "";
		$formExtRem		= "";
		
		if(REMEMBER_USER === true)
			$formExtRem .=	'<li class="{t_class:rowcheckbox}">' . "\n" . 
							'<label class="rememberMe"><input type="checkbox" name="rememberMe"' . (isset($this->g_Session['rememberMe']) && $this->g_Session['rememberMe'] === true ? ' checked="checked"' : '') . ' class="{t_class:checkbox}" tabindex="' . ($type == "default" ? 3 : 23) . '" />{s_label:rememberme}</label>' .
							'</li>' . "\n";
		
		if(LOGFORM_TYPE == "forgotPass")
			$formExt .=	'<div class="forgotPW {t_class:' . (REGISTRATION_TYPE != "none" ? 'halfrowsm' : 'fullrow') . '}">' .
						parent::getIcon("lock", "", "") .
						'<a href="' . PROJECT_HTTP_ROOT . '/login' . PAGE_EXT . '?fp=1" rel="nofollow" tabindex="' . ($type == "default" ? 5 : 25) . '" class="link">{s_link:forgotPW}</a></div>';
		
		if(REGISTRATION_TYPE != "none")
			$formExt .=	'<div class="register {t_class:halfrowsm}">' .
						parent::getIcon("user", "", "") .
						'<a href="' . PROJECT_HTTP_ROOT . '/registration' . PAGE_EXT . '" tabindex="' . ($type == "default" ? 6 : 26) . '" class="link">{s_link:register}</a></div>';
		
		if($formExt != "")
			$formExt	=	'<br /><div class="{t_class:fullrow} {t_class:alert} {t_class:info}">' .
							'<div class="{t_class:row}">' . $formExt . '</div>' . "\n" .
							'</div>' . "\n";
		
		
  
		$loginForm = 	'<div' . ($type == "default" ? ' id="logForm"' : '') . ' class="logForm form' . ($type == "default" ? ' {t_class:margintm} {t_class:marginbm}' : ' {t_class:panel}') . '">' . "\n" .
						'<' . ($type == "default" ? 'h2' : 'p') . ' class="logFormHeader {t_class:panelhead} {t_class:marginbm}">{s_header:login}</' . ($type == "default" ? 'h2' : 'p') . '>' . "\n" . 
						'<form class="{t_class:form}" action="' . $this->checkScript . '" method="post">' . "\n" . 
						'<fieldset>' . "\n" . 
						($type == "default" ? '<legend>{s_form:user}</legend>' . "\n" : '');
		
		// Error box
		$loginForm .=	'<div class="formErrorBox">' . "\r\n";
						
		// Falls Login nicht erlaubt
		if (!$this->loginAllowed()) {
			$loginForm .= 	$this->getNotificationStr("{s_notice:badlogin}", "error") .
							'</div>' . "\n" . // close errorBox
							'</fieldset>' . "\n" . 
							'</form>' . "\n" . 
							'</div>' . "\n";
		
			return $loginForm;
		}
		

		// Verlorene Session Meldung
		if(isset($GLOBALS['_GET']['timeout']) && $GLOBALS['_GET']['timeout'] == "1" && $type == "default")
			$loginForm .= $this->getNotificationStr("{s_notice:errortimeout}", "error");
		
		// Fehlermeldung aus Plugin
		if(!empty($this->g_Session['autherror'])) {
			$loginForm .= $this->getNotificationStr($this->g_Session['autherror'], "error");
			$this->unsetSessionKey('autherror');
		}
		
		// Fehlversuchsmeldung
		elseif(isset($GLOBALS['_GET']['login']) && $GLOBALS['_GET']['login'] == "0" && $type == "default") {
			$loginForm .= $this->getNotificationStr("{s_notice:loginfail}", "error");
			
			if(isset($this->g_Session['badLogins']) && $this->g_Session['badLogins'] < 4)
				$loginForm .= $this->getNotificationStr('{s_notice:remtries} ' . $this->g_Session['badLogins'], "error");
		}
		
		
		$loginForm .= 	'</div>' . "\n" . // close errorBox
						'<ul>' . "\n" . 
						'<li class="{t_class:formrow}">' . "\n" . 
						'<label for="username' . ($type == "small" ? '2' : '') . '">{s_common:user}</label>' . "\n" . 
						'<ul class="{t_class:inputgroup}">' . "\n" . 
						'<span class="{t_class:inputaddon}">' . parent::getIcon("user", "", "", "") . '</span>' . "\n" . 
						'<input type="text" name="username" id="username' . ($type == "small" ? '2' : '') . '" class="{t_class:input} {t_class:field}" maxlength="' . self::usernameMaxLen . '" tabindex="' . ($type == "default" ? 1 : 21) . '" value="' . (isset($this->g_Session['falsename']) && $type == "default" ? htmlspecialchars($this->g_Session['falsename']) : '') . '" aria-required="true" data-validation="required" data-validation-length="max' . self::usernameMaxLen . '" />' . "\n" . 
						'</ul>' . "\n" . 
						'</li>' . "\n" . 
						'<li class="{t_class:formrow}">' . "\n" . 
						'<label for="password' . ($type == "small" ? '2' : '') . '">{s_common:password}</label>' . "\n" . 
						'<ul class="{t_class:inputgroup}">' . "\n" . 
						'<span class="{t_class:inputaddon}">' . parent::getIcon("lock", "", "", "") . '</span>' . "\n" . 
						'<input type="password" name="password" id="password' . ($type == "small" ? '2' : '') . '" class="{t_class:input} {t_class:field}" maxlength="'.PASSWORD_MAX_LENGTH.'" tabindex="' . ($type == "default" ? 2 : 22) . '" aria-required="true" data-validation="required" data-validation-length="max' . PASSWORD_MAX_LENGTH . '" />' . "\n" . 
						'</ul>' . "\n" . 
						'</li>' . "\n" . 
						$formExtRem .
						'<li class="{t_class:formrow} submitPanel">' . "\n" . 
						parent::getTokenInput();
		
		// Button submit
		$btnDefs	= array(	"type"		=> "submit",
								"name"		=> "loginbutton",
								"class"		=> 'loginbutton formbutton {t_class:btnpri}' . ($type == "small" ? ' {t_class:btnblock}' : ''),
								"value"		=> "{s_common:login}",
								"attr"		=> 'tabindex="' . ($type == "default" ? 4 : 24) . '"',
								"icon"		=> "ok"
							);
			
		$loginForm .=	parent::getButton($btnDefs);
		
		$loginForm .=	'</li>' . "\n" . 
						'</ul>' . "\n" . 
						$formExt .
						'</fieldset>' . "\n";
		
		// Events
		$this->o_userAuthentificationEvent->setCheckScript($this->checkScript);
		// dispatch event extend_log_form
		$this->o_dispatcher->dispatch('auth.extend_log_form', $this->o_userAuthentificationEvent);
		
		$loginForm .= $this->o_userAuthentificationEvent->getOutput();
		
		$loginForm .=	'</form>' . "\n" . 
						'</div>' . "\r\n" .
						'<script type="text/javascript">' . "\n" . 
						($this->headJS ? 'head.ready(function(){' : '') .
						'$(document).ready(function() {' .  
						'if($("#username").length){ $("#username").select(); }' .
						'});' . "\n" . 
						($this->headJS ? '});' : '') .
						'</script>' . "\n";
		
		if(isset($this->g_Session['falsename']) && $type == "default")
			$this->unsetSessionKey('falsename');
				
		
		return $loginForm;
		
	}
	
   
	/**
	 * Prüft, ob eine korrekte Benutzername-Password-Kombination eingegeben wurde.
	 * 
	 * @access	public
	 * @return	boolean/string Gibt Benutzergruppe zurück oder false, falls der Login nicht erfolgreich war
	*/
	public function checkLoginData()
	{
	
		//Wenn Sperre besteht, false zurück geben.
		if (!$this->loginAllowed()) {
			$this->bannedUser = true;
			return false;
		}

		
		// Events
		// dispatch event add_head_code
		$userAuth		= $this->o_dispatcher->dispatch('auth.check_user_auth', $this->o_userAuthentificationEvent);
		
		$loginUser		= $this->o_userAuthentificationEvent->checkLoginUser();
		$userDetails	= $this->o_userAuthentificationEvent->getUserDetails();
		$redirectUser	= $this->o_userAuthentificationEvent->getRedirectUser();
 
		if(!empty($userDetails)) {
			$this->rememberLogin = true;
			return $this->loginUser($userDetails);
		}
		
		if(!empty($redirectUser)) {
			session_write_close();
			return $this->redirectUser($loginUser);
		}
		
		// propagation is stopped
		if($this->o_userAuthentificationEvent->isPropagationStopped())
			return false;
		
		
		// Default login via form
		// Falls falsche Eingaben
		if(!isset($GLOBALS['_POST']['username']) || $GLOBALS['_POST']['username'] == ""
		|| !isset($GLOBALS['_POST']['password']) || $GLOBALS['_POST']['password'] == ""
		|| strlen($GLOBALS['_POST']['username']) > self::usernameMaxLen
		)
			return false;

		
		// Benutzer über Loginformular
		return $this->getUserByLogFormAuthentification($GLOBALS['_POST']['username'], $GLOBALS['_POST']['password']);
	
	}
	
	
	/**
	 * Benutzer über Loginformular
	 * 
	 * @param	$userAuth	
	 * @param	$password	
	 * @access	public
	 * @return	boolean Gibt Autherfolg zurück
	*/
	public function getUserByLogFormAuthentification($userAuth, $password)
	{
	
		//UN/PW
		if(empty($userAuth)
		|| empty($password)
		)
			return false;
		
	
		//IP-Adresse
		$this->userIP = $this->DB->escapeString(User::getRealIP());

		//Erste drei Buchstaben des Loginnamens
		$firstChar = substr($userAuth,0,3);
		
		//Eingeschränkte Ergebnismenge
		$sql = "SELECT `userid`, `username`, `password`, `salt`, `group`, `own_groups`, `author_name`, `email`, `lang`, `at_skin` 
				FROM `" . DB_TABLE_PREFIX . "user` 
				WHERE (`username` LIKE '".$firstChar."%' 
				OR `email` LIKE '".$firstChar."%') 
				AND `group` != 'subscriber'";
				
		//Direkt auf das globale Objekt zugreifen
		$result = $this->DB->query($sql);
		
		
		// Rememer Me
		$this->rememberLogin = false;
		
		if(isset($GLOBALS['_POST']['rememberMe']) && $GLOBALS['_POST']['rememberMe'] == "on")
			$this->rememberLogin = true;
		
		$this->setSessionVar('rememberMe', $this->rememberLogin);
		
		
		foreach($result as $userDetails){
			
			// Login mit Benutzernamen oder E-Mail-Adresse
			if(($userAuth == $userDetails['username']
			 || $userAuth == $userDetails['email'])
			&& Security::hashPassword($password, CC_SALT . $userDetails['salt']) == $userDetails['password']
			) {
			
				//korrekte Kombination, User einloggen
				return $this->loginUser($userDetails);
				
			}
		
		}
		
		//Wenn die Methode hier ankommt, ist keine Authentifizierung
		//möglich und es wird false zurückgegeben.
		//Eintragen eines falschen Loginversuchs:
		$sql = "INSERT INTO `" . DB_TABLE_PREFIX . "badlogin` (ip,timestamp,triedUsername) VALUES ('".$this->userIP."','".time()."','".$this->DB->escapeString($userAuth)."')";
		
		$this->DB->query($sql);
		
		
		if($this->setPotentialIntrusion())
			$this->setSessionVar('loginAttemptsExheeded', true);
		
		$this->setSessionVar('falsename', $userAuth);
		
		return false;
	
	}   
	
   
	/**
	 * Login user
	 * 
	 * @param	$userDetails	
	 * @access	private
	 * @return	boolean/string Gibt Benutzergruppe des geloggten Users zurück
	*/
	private function loginUser($userDetails)
	{
	
		if(!is_array($userDetails)
		|| empty($userDetails)
		)
			return false;
		
	
		//Session_id neu setzen (gegen SESSION FIXATION) und löschen der alten Session
		session_regenerate_id(true);
		
		$timeStamp	= time();
		$logDate	= date("d.m.Y  H:i",$timeStamp);
		$logDateDB	= date("Y-m-d  H:i:s",$timeStamp);
		
		//Daten des Benutzers in die Session eintragen
		$this->setSessionVar('userid',			str_pad($userDetails['userid'], 9, '0', STR_PAD_LEFT));
		$this->setSessionVar('username',		$userDetails['username']);
		$this->setSessionVar('group',			$userDetails['group']);
		if($userDetails['own_groups'] != "")
			$this->setSessionVar('own_groups',	explode(",", $userDetails['own_groups']));
		$this->setSessionVar('loggedInSince',	$logDate);
		$this->setSessionVar('author_name',		$userDetails['author_name']);
		$this->setSessionVar('usermail',		$userDetails['email']);
		$this->setSessionVar('admin_lang',		$userDetails['lang']);
		$this->setSessionVar('at_skin',			$userDetails['at_skin']);
		
		
		//eventuelle fehlgeschlagene Loginversuche von dieser IP-Adresse löschen
		$this->unsetSessionKey('badLogins');
		
		$sql = "DELETE FROM `" . DB_TABLE_PREFIX . "badlogin` WHERE ip = '".$this->userIP."'";
		
		$this->DB->query($sql);
		
		
		// Falls rememberMe gesetzt ist, cookie setzen und in DB eintragen
		if($this->rememberLogin === true) {
		
			$sql = array(1);
			
			// Eindeutigkeit gewährleisten
			while(count($sql) > 0) {
				
				$rememberCode = md5(uniqid($userDetails['username']));
				
				$sql = $this->DB->query("SELECT * FROM `" . DB_TABLE_PREFIX . "user` 
											WHERE `logID` = '".$this->DB->escapeString($rememberCode)."'
										");
			}
			
			// Code in DB eintragen
			$sql = $this->DB->query("UPDATE `" . DB_TABLE_PREFIX . "user` 
										SET `logID` = '".$this->DB->escapeString($rememberCode)."',
											`last_log` = '".$this->DB->escapeString($logDateDB)."'
										WHERE `username` = '".$this->DB->escapeString($userDetails['username'])."'
									");
			
			// Cookie setzen
			if($sql === true) {
				$exp = time()+60*60*24*7;
				setcookie("conciseLog", $rememberCode, $exp, "/");
			}
			
		}
		else { // Andernfalls
			// Code in DB zurücksetzen
			$sql = $this->DB->query("UPDATE `" . DB_TABLE_PREFIX . "user` 
										SET `logID` = '',
											`last_log` = '".$this->DB->escapeString($logDateDB)."'
										WHERE `username` = '".$this->DB->escapeString($userDetails['username'])."'
									");
			
			// Cookie löschen
			setcookie("conciseLog", "", time()-3600, "/");
		}
		
		// Falls editorLog und noch kein Cookie für fe-Edit-Mode gesetzt ist, fe-Edit-Mode Cookie setzen
		if(($userDetails['group'] == "admin"
		 || $userDetails['group'] == "editor")
		&& !isset($GLOBALS['_COOKIE']['feMode'])
		)
			setcookie("feMode", "on", time()+60*60*24*180, "/");
		
		
		// Benutzergruppe zurückgeben
		return $userDetails['group'];
	
	}
	
   
	/**
	 * Verweist den Benutzer nach Login auf eine bestimmte Seite.
	 * 
	 * @param	$userLogin	
	 * @access	public
	 * @return	boolean/string Gibt Benutzergruppe zurück oder false, falls der Login nicht erfolgreich war
	*/
	public function redirectUser($userLogin)
	{

		// Falls fehlerhafter Login
		if($userLogin === false) {
		
			// Falls Staging login fehlerhaft
			if(isset($GLOBALS['_GET']['staginglog']) && $GLOBALS['_GET']['staginglog'] == 1 && isset($GLOBALS['_POST']['username']))
				header("Location: " . PROJECT_HTTP_ROOT . "/_login.html?login=0" . ($this->bannedUser ? '&banned=1' : '')) . exit;
			else
				header("Location: " . PROJECT_HTTP_ROOT . "/" . HTML::getLinkPath(-1002) . "?login=0") . exit;
			return false;
		}
		
		
		// Falls Staging login, ggf. Verzögerung einbauen, da sonst Session u.U. noch nicht gespeichert
		#if(isset($GLOBALS['_GET']['staginglog']))
		#	sleep(0.5);
		

		// Falls vorhandene Benutzergruppe bzw. erfolgreicher Login
		// Entweder zu "mein Bereich" oder zur Login-Seite gehen
		if($userLogin == "guest") // Falls Gast
			header("Location: " . PROJECT_HTTP_ROOT . "/" . HTML::getLinkPath(REGISTRATION_TYPE == "account" || REGISTRATION_TYPE == "shopuser" ? -1007 : -1002, $this->lang) . "?login=1") . exit; 
		elseif( $userLogin == "author" || // Falls Author
				$userLogin == "editor" || // Falls Editor
				$userLogin == "admin" // Falls Admin
		)
			header("Location: " . (!empty($GLOBALS['_GET']['red']) ? $GLOBALS['_GET']['red'] : ADMIN_HTTP_ROOT)) . exit;
		elseif(in_array($userLogin, $ownUserGroups)) // Falls selbstdefinierte Gruppe
			header("Location: " . PROJECT_HTTP_ROOT) . exit;
		else // Falls keine bekannte Benutzergruppe
			header("Location: " . PROJECT_HTTP_ROOT . "/" . HTML::getLinkPath(-1002, $this->lang) . "?login=0") . exit;

		return true;

	}
	


	/**
	 * Intrusion detection
	 *
	 * Wenn die Anzahl erlaubter Login-Versuche überschritten ist, 
	 * wird das Anmelden für die eingestellte Dauer unterbunden.
	 * 
	 * @access	private
	 */
	private function setPotentialIntrusion()
	{
	
		//IP-Adresse des Benutzers
		$this->userIP = $this->DB->escapeString(User::getRealIP());

		//Anzahl der "ungültigen Logins" prüfen.
		$sql = "SELECT count(*) as count FROM `" . DB_TABLE_PREFIX . "badlogin` "." WHERE ip = '".$this->userIP."' AND active = 1";
		
		$result = $this->DB->query($sql);

		$this->badLogins = $result[0]['count'];
		
		if ($this->badLogins > MAX_ALLOWED_BAD_LOGINS)
		{
			//Eine Sperre für diesen IP-Bereich setzen.
			$banSQL = "INSERT INTO `" . DB_TABLE_PREFIX . "bannedip` (ip,setAt,until) VALUES ('".$this->userIP."','".time()."','". (time() + LOGIN_BAN_TIME)."')";
			
			$this->DB->query($banSQL);
			
			//Anschließend die aufgelaufenen badLogins des IP-Bereichs inaktiv setzen
			$setInactiv = "UPDATE `" . DB_TABLE_PREFIX . "badlogin` SET active = 0 WHERE ip = '".$this->userIP."'";
			$this->DB->query($setInactiv);
			$this->unsetSessionKey('badLogins');
			return true;
		}
		else
			$this->setSessionVar('badLogins', MAX_ALLOWED_BAD_LOGINS - $this->badLogins +1);
		
		return false;

	}


	/**
	 * Überprüft, ob der Benutzer (per IP identifiziert) sich einloggen darf
	 * 
	 * @access	public
	 * @return	boolean Gibt zurück, ob der Login von der entsprechenden IP erlaubt ist.
	 */
	public function loginAllowed()
	{

		//Schauen, ob gerade eine Sperre für diesen IP-Bereich besteht.
		//Die aktuelle Zeit muss kleiner als "until" sein, und die 
		//IP muss stimmen.
		$askBan = "SELECT * FROM `" . DB_TABLE_PREFIX . "bannedip` WHERE "."until > '".time()."' AND ip = '" . $this->DB->escapeString(User::getRealIP()) . "'";
		$result = $this->DB->query($askBan);

		//Wenn ein Datensatz vorhanden, dann ist der IP-Bereich gesperrt.
		if (count($result) != 0)
		{
			return false;
		}
		else
		{
			return true;
		}

	}



	/**
	 * Methode zum Zurücksetzen eines Passworts.
	 * 
	 * @access	public
	 * @return	string Gibt das Formular zum Zurücksetzen eines Passworts zurück.
     */
	public function forgotPass()
	{	
		
		$formAction	= PROJECT_HTTP_ROOT . '/login' . PAGE_EXT . '?fp=1';
		
		if(isset($GLOBALS['_POST']['resPass'])) {
			
			
			$userPass	= "";
			$userGroup	= "";
			$regUser	= $GLOBALS['_POST']['username'];
			$regUserDb	= $this->DB->escapeString($regUser);
			$userMail	= $regUserDb;
			
			// Überprüfung ob Benutzer(mail) existiert
			$userQuery = $this->DB->query("SELECT `email`, `group`, `newsletter`
												FROM `" . DB_TABLE_PREFIX . "user` 
												WHERE `username` = '$regUserDb' 
												OR `email` = '$regUserDb'
												");
			#var_dump($userQuery);
			if(is_array($userQuery)
			&& count($userQuery) > 0
			)
				$userGroup = $userQuery[0]['group'];
			
			if($regUser == "")
				$errorM = "{s_error:mail1}";
			elseif(strlen($regUser) > self::usernameMaxLen)
				$errorM = "{s_error:mail2}";
			elseif(!filter_var($regUser, FILTER_VALIDATE_EMAIL))
				$errorM = "{s_error:mail2}";
			if(!isset($this->g_Session['captcha']) || ($GLOBALS['_POST']['captcha_confirm'] != $this->g_Session['captcha']))
				$errorCap = "{s_error:captcha}";
			elseif(count($userQuery) == 0)
				$errorM = "{s_error:usernotexist}";
			elseif($userGroup == "subscriber")
				$errorM = "{s_error:noaccount}";



			// Falls keine Fehler aufgetaucht sind			
			if(!isset($errorM) && !isset($errorCap) && count($userQuery) == 1 && $userGroup != "subscriber") {
				
				$newPass	= Security::generatePassword();
				$newSalt	= Security::generatePassword(9);
				$newPassDb	= Security::hashPassword($newPass, CC_SALT . $newSalt);
				$authCode	= md5(uniqid(time()));
				$newsL		= $userQuery[0]['newsletter'];
				
				$mailStatus	= false;
				
				// db-Tabelle sperren
				$lock = $this->DB->query("LOCK TABLES `" . DB_TABLE_PREFIX . "user`");
				
			
			
				// Einfügen der neuen Sprachfelder
				$updateSQL = $this->DB->query("UPDATE `" . DB_TABLE_PREFIX . "user` 
													SET `password` = '$newPassDb',
														`salt` = '$newSalt', 
														`auth_code` = '$authCode' 
													WHERE `username` = '$regUserDb' 
													OR `email` = '$regUserDb'
													");
		
				// db-Sperre aufheben
				$unLock = $this->DB->query("UNLOCK TABLES");
				
				
				if($updateSQL === true) {
					
					$domArr		= explode("://", PROJECT_HTTP_ROOT);
					$domain		= end($domArr);
					$domainOnly = str_replace("www.", "", $domain);
					$subject	= htmlspecialchars(ContentsEngine::replaceStaText("{s_text:newpasssubject}") . " - " . $domainOnly);
					$subject	= '=?utf-8?B?'.base64_encode($subject).'?=';
					$success	= $this->getNotificationStr("{s_notice:respass}", "success");
					
					// Nachricht
					$htmlMail = "
								<html>
									<head>
										<title>User notification</title>
										<style type='text/css'>
											table { font-size:11px; border:1px solid #D3D3D3; padding:5px; border-collapse:collapse; }
											tr { vertical-align:top; padding:10px; }
											td { padding: 5px 20px; 5px 10px}
											tr td:first-child { background:#D3D3D3; }
											td.border { border-bottom:1px solid #D3D3D3; }
											td.borderL { border-bottom:1px solid #FFF; }
										</style>
									</head>
									<body>
										<p>{s_text:hello} ".htmlspecialchars(User::getMailLocalPart($regUser)).",</p>
										<p>&nbsp;</p>
										<p>{s_text:newpass} ".$newPass."</p>
										<p>&nbsp;</p>
										<p>{s_text:newpass2} ".$domain.".</p>
										<p>&nbsp;</p>
										<p>&nbsp;</p>
										<hr>
										<table>
										<tr>" .
										($newsL == 1 && REGISTRATION_TYPE != "none" ? "<td><a href='" . PROJECT_HTTP_ROOT . "/registration" . PAGE_EXT . "?newsletter=no&amp;un=" . $regUser . "&amp;ac=" . $authCode . "'>{s_text:unregnewsl}</td>" : "") .
										($newsL == 0 && REGISTRATION_TYPE != "none" ? "<td><a href='" . PROJECT_HTTP_ROOT . "/registration" . PAGE_EXT . "?newsletter=yes&amp;un=" . $regUser . "&amp;ac=" . $authCode . "'>{s_text:regnewsl}</td>" : "")
										.
										($userGroup != "subscriber" && (REGISTRATION_TYPE == "account" || REGISTRATION_TYPE == "shopuser") ? "<td><a href='" . PROJECT_HTTP_ROOT . "/registration" . PAGE_EXT . "?account=no&amp;un=" . $regUser . "&amp;ac=" . $authCode . "'>{s_text:unreg}</td>" : "") .
										"</tr>
										</table>
									</body>
								</html>
								";
			
					$htmlMail = ContentsEngine::replaceStaText($htmlMail) . "\n";
					
					
					// Klasse phpMailer einbinden
					require_once(PROJECT_DOC_ROOT . '/inc/classes/phpMailer/class.phpMailer.php');
					require_once(PROJECT_DOC_ROOT . '/inc/classes/phpMailer/class.smtp.php');
					
					// Instanz von PHPMailer bilden
					$mail = new \PHPMailer();
								
					
					// E-Mail-Parameter für SMTP
					$mail->setMailParameters(AUTO_MAIL_EMAIL, AUTO_MAIL_AUTHOR, $regUser, $subject, $htmlMail, true, "", "smtp");
		
					// E-Mail senden per phpMailer (SMTP)
					$mailStatus = $mail->Send();
					
					// Falls Versand per SMTP erfolglos, per Sendmail probieren
					if($mailStatus !== true) {
						
						// E-Mail-Parameter für php Sendmail
						$mail->setMailParameters(AUTO_MAIL_EMAIL, AUTO_MAIL_AUTHOR, $regUser, $subject, $htmlMail, true, "", "sendmail");
						
						// E-Mail senden per phpMailer (Sendmail)
						$mailStatus = $mail->Send();
					}
					
					// Falls Versand per Sendmail erfolglos, per mail() probieren
					if($mailStatus !== true) {
						
						// E-Mail-Parameter für php mail()
						$mail->setMailParameters(AUTO_MAIL_EMAIL, AUTO_MAIL_AUTHOR, $regUser, $subject, $htmlMail, true);
						
						// E-Mail senden per phpMailer (mail())
						$mailStatus = $mail->Send();
					}
					
					// Falls keine E-Mail versandt wurde, Fehlermeldung ausgeben
					if($mailStatus !== true)
						$success = 	$this->getNotificationStr('{s_error:error}<br /><br />' . $mail->ErrorInfo, "error");
					
				}
				else
					$success = 	$this->getNotificationStr("{s_error:error}", "error");
		
			}

		} // Ende if submit
		
		
		// Falls die Registrierung erfolgreich war
		if(isset($success)) {
			$regForm = 	'<div id="regForm" class="form {t_class:fullrow} {t_class:margintm} {t_class:marginbm}">' . "\n" . 
						'<div class="top"></div>' . "\r\n" .
						'<div class="center">' . "\r\n" .
                		'<form id="regform" class="{t_class:form}" action="" method="post">' . "\n" . 
						'<fieldset>' . "\n" . 
						'<legend>{s_form:user}</legend>' . "\n" . 
						'<h2 class="logFormHeader">{s_header:respass}</h2>' . "\n" . 
						$success .
						'</fieldset>' . "\n" . 
						'</form>' . "\n" . 
						'</div>' . "\r\n" .
						'<div class="bottom"></div>' . "\r\n" .
						'</div>' . "\n";
			
			return $regForm;
		}
			
		$regForm = 		'<div id="regForm" class="form {t_class:fullrow} {t_class:margintm} {t_class:marginbm}">' . "\n" . 
						'<div class="top"></div>' . "\r\n" .
						'<div class="center">' . "\r\n" .
                		'<form id="regform" class="{t_class:form}" action="' . $formAction  . '" method="post">' . "\n" . 
						'<fieldset>' . "\n" . 
						'<legend>{s_form:user}</legend>' . "\n" . 
						'<h2 class="logFormHeader">{s_header:respass}</h2>' . "\n" . 
						'<p>{s_text:respass}</p>' . "\n" .
						'<div class="formErrorBox"></div>' . "\r\n" .
						'<ul>' . "\n" . 
						'<li class="{t_class:formrow}">' . "\n" .
						'<label for="username">{s_label:user}</label>' . "\n" . 
						(isset($errorM) ? $this->noticeOpenTag . $errorM . $this->noticeCloseTag : '') . 
						'<input type="text" name="username" id="username" class="{t_class:input} {t_class:field}" maxlength="' . self::usernameMaxLen . '" tabindex="1" value="' . (isset($regUser) ? htmlspecialchars($regUser) : '') . '" aria-required="true" data-validation="required" data-validation-length="max' . self::usernameMaxLen . '" />' . "\n" .
						'</li>' . "\n" .
						'<li class="{t_class:formrow} {t_class:row}">' . "\r\n" . 
						'<span class="fieldLeft {t_class:halfrowsm}">' . "\r\n" .
						'<label for="captcha_confirm">{s_form:captcha}</label>' . "\r\n" .
						(isset($errorCap) ? $this->noticeOpenTag . $errorCap . $this->noticeCloseTag : '') . "\r\n" .						
						'<input name="captcha_confirm" type="text" id="captcha_confirm" class="{t_class:input} {t_class:field}" tabindex="3" aria-required="true" data-validation="required" />' . "\r\n" .
						'</span>' . "\r\n";
						
		$regForm .=		'<span class="fieldRight {t_class:halfrowsm}">' . "\r\n" .
						'<label>&nbsp;</label><br />' . "\r\n" .
						'<img src="' . PROJECT_HTTP_ROOT . '/access/captcha.php" alt="{s_form:capalt}" title="{s_form:captit}" class="captcha" />' . "\r\n";
		
		// Button caprel
		$btnDefs	= array(	"href"		=> PROJECT_HTTP_ROOT . '/access/captcha.php',
								"text"		=> '',
								"class"		=> "caprel button-icon-only {t_class:btninf} {t_class:btnsm}",
								"title"		=> '{s_form:capreltit}',
								"attr"		=> 'tabindex="2"',
								"icon"		=> "refresh",
								"icontext"	=> ""
							);
		
		$regForm .=		parent::getButtonLink($btnDefs);
		
		$regForm .=		'</span>' . "\r\n" .
						'</li>' . "\r\n";
			
		$regForm .=		'<li class="submitPanel {t_class:formrow}">' . "\r\n";
		
		// Button submit
		$btnDefs	= array(	"type"		=> "submit",
								"name"		=> "resPass",
								"id"		=> "resPass",
								"class"		=> '{t_class:btnpri} formbutton',
								"value"		=> "{s_button:respass}",
								"attr"		=> 'tabindex="4"',
								"icon"		=> "ok"
							);
			
		$regForm .=	parent::getButton($btnDefs);
		
		$regForm .=		parent::getTokenInput() . 
						'</li>' . "\n" . 
						'</ul>' . "\n" . 
						'</fieldset>' . "\n" . 
						'</form>' . "\n" . 
						'</div>' . "\r\n" .
						'<div class="bottom"><hr />' . "\r\n" .
						'<p class="{t_class:margintm}">{s_form:regged}</p>' . "\r\n";
		
		// Button goto login page
		$btnDefs	= array(	"href"		=> PROJECT_HTTP_ROOT . "/" . HTML::getLinkPath(-1002),
								"class"		=> 'gotoLogin {t_class:btnsec}',
								"text"		=> "{s_link:gotologin}",
								"attr"		=> 'tabindex="5"',
								"icon"		=> "signin"
							);
			
		$regForm .=	parent::getButtonLink($btnDefs);
						
		$regForm .=		'</div>' . "\r\n" .
						'</div>' . "\n" .
						'<script type="text/javascript">' . "\n" . 
						($this->headJS ? 'head.ready("jquery",function(){' : '') .
						'$(document).ready(function() {' .  
						'$("#username").focus();' . 
						'});' . "\n" . 
						($this->headJS ? '});' : '') .
						'</script>' . "\n";
			 												
			
		return $regForm;
	
	}



	/**
	 * Gibt ein Benutzerbild zurück
	 * 
	 * @param	int				$userID			user id
	 * @access	public
	 * @return	string Gibt das Formular zum Zurücksetzen eines Passworts zurück.
     */
	public static function getUserImage($userID)
	{
		
		$output			= "";
		$userIDStr		= str_pad($userID, 9, '0', STR_PAD_LEFT);
		$userImgUrl		= User::getUserImageSrc($userIDStr);
	
		// Benutzerbild, falls vorhanden
		if(!empty($userImgUrl)) {
			$output 	= '<span class="cc-userImage-box cc-userimg-box"><img class="userImage cc-userimg" src="' . $userImgUrl . '" alt="user-avatar" /></span>' . "\n";
		}
		
		return $output;
	
	}

}
