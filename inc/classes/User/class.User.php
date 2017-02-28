<?php
namespace Concise;


/**
 * Klasse für User-Funktionen
 * 
 *
 */

class User extends ContentsEngine
{

	const usernameMaxLen		= 255;

	/**
	 * Beinhaltet die DB-Benutzertabelle
	 *
	 * @access private
     * @var    array
     */
	private $tableUser = "user";
	
	/**
	 * Beinhaltet die erlaubten Benutzergruppen.
	 *
	 * @access private
     * @var    array
     */
	private static $userGroups = array("public", "subscriber", "guest", "author", "editor", "admin");
	
	/**
	 * Beinhaltet die Backend-Benutzergruppen.
	 *
	 * @access private
     * @var    array
     */
	private static $backendUserGroups = array("author", "editor", "admin");
	
	/**
	 * User neu / edit DB erfolgreich
	 *
	 * @access private
     * @var    array
     */
	private $userSuccess = false;
	
	/**
	 * Site's Domain
	 *
	 * @access private
     * @var    array
     */
	private $siteDomainWWW = "";
	private $siteDomain	= "";
	
	/**
	 * Reg Texte
	 *
	 * @access public
     * @var    array
     */
	public $regtextSubject = "";
	public $regtextThank = "";
	public $regtextMessage = "";
	public $regtextNewsl = "";
	public $regtextGuest = "";
	public $regtextShop = "";
	public $regtextUser = "";
	public $regtextRegNewsl = "";
	public $regtextUnregNewsl = "";
	

	/**
	 * Konstruktor
	 * 
 	 * @param	object	$DB		DB-Objekt
 	 * @param	object	$o_lng	Sprachobjekt
	 * @access	public
	 */
	public function __construct($DB, $o_lng)
	{
		
		// Datenbankobjekt
		$this->DB	= $DB;
		
		// Sprache
		$this->lang	= $o_lng->lang;

		// Session-Vars-Array
		$this->g_Session	= Security::getSessionVars();

		// DB table user
		$this->tableUser	= DB_TABLE_PREFIX . $this->tableUser;
		
		// Domain
		$domainArr					= explode("://", PROJECT_HTTP_ROOT);
		$this->siteDomainWWW		= end($domainArr);
		$this->siteDomain			= str_replace("www.", "", $this->siteDomainWWW);
		
		// Reg Texte
		$this->regtextSubject		= "{s_text:regsubject} " . $this->siteDomain;
		$this->regtextThank			= "{s_text:regthank}" . $this->siteDomain . ".";
		$this->regtextMessage		= "{s_text:regmessage}";
		$this->regtextNewsl			= "{s_text:regtextnewsl}";
		$this->regtextGuest			= "{s_text:regtextguest}";
		$this->regtextShop			= "{s_text:regtextshop}";
		$this->regtextUser			= "{s_notice:reguser}";
		$this->regtextRegNewsl		= "{s_notice:regnewsl}";
		$this->regtextUnregNewsl	= "{s_notice:unregnewsl}";
		
	}
	

	/**
	 * Gibt die System-Benutzergruppen zurück
	 * 
	 * @access	public
	 * @return	array
	 */
	public static function getSystemUserGroups()
	{	
	
		return self::$userGroups;
		
	}
	

	/**
	 * Gibt Backend-Benutzergruppen zurück
	 * 
	 * @access	public
	 * @return	array
	 */
	public static function getBackendUserGroups()
	{	
	
		return self::$backendUserGroups;
		
	}
	

	/**
	 * Gibt Backend-Authoren zurück
	 * 
	 * @access	public
     * @param	string group Benutzergruppe zur Berechtigung
	 * @return	array
	 */
	public function getAuthorNames($group)
	{	
		
		$outputArr	= array();

		// Restriktion der Benutzergruppen
		$dbFilter = "WHERE `username` != ''";
		
		if($this->g_Session['group'] == "editor") {
			
			$dbFilter .= " AND `group` != 'admin' AND (`group` != 'editor' OR `username` = '" . $this->DB->escapeString($this->g_Session['username']) . "')";
		}
		elseif($this->g_Session['group'] == "author") { // Admin- und Editor-Gruppe entfernen
			
			$dbFilter .= " AND `username` = '" . $this->DB->escapeString($this->g_Session['username']) . "'";
		}
		
		
		// Suche nach Benutzern
		$authorNames = $this->DB->query("SELECT `userid`, `author_name` 
													FROM `" . $this->tableUser . "` 
												$dbFilter
												");
		
		
		if(is_array($authorNames)
		&& count($authorNames) > 0
		) {
			$i = 1;
			foreach($authorNames as $author) {
				$outputArr[$author['userid']] = $author['author_name'];
				$i++;
			}
		}
		return $outputArr;
	
	}

	

	/**
	 * Überprüfen auf bereits vorhandenen Benutzer
	 * 
	 * @param	string 		$username zu überprüfender Benutzername
	 * @param	boolean/int $id Benutzerid des aktuellen Datensatzes (default = false)
	 * @access	public
	 * @return	string
	 */
	public function checkUserExists($username, $id = false)
	{
	
		$filterCurrent = "";
		
		if($id !== false)
			$filterCurrent = " AND `userid` != ".$this->DB->escapeString($id);
		
		// Suche nach Benutzern
		$duplicateUserQuery = $this->DB->query("SELECT `username`,`email` 
													FROM `" . $this->tableUser . "` 
												WHERE (`username` = '".$this->DB->escapeString($username)."'
													OR `email` = '".$this->DB->escapeString($username)."')".
													$filterCurrent."
												");
		if(is_array($duplicateUserQuery)
		&& count($duplicateUserQuery) > 0
		) {
			return true;
		}
		else
			return false;
	}
	

	/**
	 * Überprüfen auf bereits vorhandenen Authorennamen
	 * 
	 * @param	string 		$author zu überprüfender Author
	 * @param	boolean/int $id Benutzerid des aktuellen Datensatzes (default = false)
	 * @access	public
	 * @return	string
	 */
	public function checkAuthorExists($author, $id = false)
	{

		$filterCurrent = "";
		
		if($id !== false)
			$filterCurrent = " AND `userid` != ".$this->DB->escapeString($id);
		
		// Suche nach Benutzern
		$duplicateAuthorQuery = $this->DB->query("SELECT `author_name` 
														FROM `" . $this->tableUser . "` 
														WHERE `author_name` = '".$this->DB->escapeString($author)."'".
														$filterCurrent."
													  ");

		if(count($duplicateAuthorQuery) > 0) {
			return true;
		}
		else
			return false;
	}
	

	/**
	 * Liest url-Parameter zur Änderung von Benutzerdaten aus bzw. gibt ein Registrierungsformular zurück
	 * 
	 * @access	public
     * @param	string Art des Registrierungsformulars
	 * @return	string
	 */
	public function getRegPage($type)
	{
	
		// Falls keine Änderung von Benutzerdaten, Registrierungsformular ausgeben
		if(empty($GLOBALS['_GET']['un'])
		|| empty($GLOBALS['_GET']['ac'])
		)
			return $this->printRegForm($type);
		
		
		// Änderung von Benutzerdaten
		$regUser	= $GLOBALS['_GET']['un'];
		$authCode	= $GLOBALS['_GET']['ac'];
		$action		= false;
		$formAction	= empty($this->formAction) ? PROJECT_HTTP_ROOT . "/" . HTML::getLinkPath(-1006) : $this->formAction;
		$formExt	= "";
		$regModType = "newsl";
		$unregConfirm	= false;
		
		// Falls Newsletter-Subscription geändert werden soll
		if(isset($GLOBALS['_GET']['newsletter']) && ($GLOBALS['_GET']['newsletter'] == "no" || $GLOBALS['_GET']['newsletter'] == "yes")) {
			$regModType	= "newsl";
			$addNewsL	= $GLOBALS['_GET']['newsletter'];
			$action		= $this->subscriptionStatus($regUser, $authCode, $addNewsL);
		}
		
		// Falls das Benutzerkonto gelöscht werden soll
		elseif(isset($GLOBALS['_GET']['account']) && $GLOBALS['_GET']['account'] == "no") {
			$regModType = "account";
			
			// Die Löschung des Kontos bestätigen
			if(isset($GLOBALS['_POST']['confirm'])) {
				$action = $this->deleteUser($regUser, $authCode);
				
				// Falls Löschung erfolgreich, Seite neu laden
				if($action === true) {
					header("Location:" . $formAction . "?account=no&un=".$regUser."&ac=".$authCode."&rem=1") . exit;
				}
			}
			elseif(isset($GLOBALS['_POST']['cancel'])) {
				$unregConfirm	= "no";
				
				$formExt	=	'<p>';
	
				// Button link back
				$btnDefs	= array(	"href"		=> PROJECT_HTTP_ROOT . '/login' . PAGE_EXT,
										"class"		=> '{t_class:btnsec} formbutton alt',
										"text"		=> "{s_link:gotologin}",
										"icon"		=> "login"
									);
					
				$formExt .=		parent::getButtonLink($btnDefs);
				
				$formExt .=		'</p>' . "\n";								  
			}
			elseif(isset($GLOBALS['_GET']['rem'])) {
				$action = true;
			}
			else {
				$formAction .= "?" . $GLOBALS['_SERVER']['QUERY_STRING'];
				$unregConfirm	= "yes";
		
				// Button confirm
				$btnDefs	= array(	"type"		=> "submit",
										"name"		=> "confirm",
										"id"		=> "submitreg",
										"class"		=> '{t_class:btnpri} formbutton ok',
										"value"		=> "{s_text:unreg}",
										"icon"		=> "ok"
									);
					
				$formExt =	parent::getButton($btnDefs);				
		
				// Button cancel
				$btnDefs	= array(	"type"		=> "submit",
										"name"		=> "cancel",
										"id"		=> "submitreg",
										"class"		=> '{t_class:btnsec} formbutton alt',
										"value"		=> "{s_common:cancel}",
										"icon"		=> "cancel"
									);
					
				$formExt .=	parent::getButton($btnDefs);
				
			}
		}
		
		if(isset($addNewsL) && $addNewsL == "yes")
			$prefix = "";
		else
			$prefix = "un";
			
		
		// Formular generieren
		$output =	'<div id="regForm" class="form {t_class:fullrow} {t_class:margintm} {t_class:marginbm}">' . "\n" .
					'<div class="top"></div>' . "\r\n" .
					'<div class="center">' . "\r\n" .
					'<form action="' . $formAction . '#regForm" method="post" id="regform" class="{t_class:form}">' . "\n" . 
					'<fieldset>' . "\n" . 
					'<legend>{s_form:user}</legend>' . "\n";							
		
		$output .=  '<h2 class="regFormHeader">{s_header:'.$prefix.'reg'.$regModType.'}' .
					parent::getIcon("register") .
					'</h2>' . "\n";
	
		// Error box
		$output	.=	'<div class="formErrorBox">' . "\r\n";
					
		if($action == true && $action !== "nouser")
			$output .=  $this->getNotificationStr('{s_notice:'.$prefix.'reg'.$regModType.($action !== true ? 'nc' : '').'}', "success");
		elseif($action === "nouser")
			$output .=  $this->getNotificationStr("{s_notice:unregaccountnc}", "error");
		elseif($unregConfirm == "yes")
			$output .=  $this->getNotificationStr("{s_notice:unregconfirm}", "error");
		elseif($unregConfirm == "no")
			$output .=  $this->getNotificationStr("{s_notice:nounreg}", "success");
		else
			$output .= 	$this->getNotificationStr("{s_error:error}", "error");
		
		$output .= 		'</div>' . "\n" . // close errorBox
						$formExt . 
						'</fieldset>' . "\n" . 
						'</form>' . "\n" . 
						'</div>' . "\r\n" .
						'<div class="bottom"></div>' . "\r\n" .
						'</div>' . "\r\n";

		
		return $output;
			
	}
	
	
	
	/**
	 * Erstellt ein Registrierungsformular.
	 * 
	 * @access	public
     * @param	varchar Art des Registrierungsformulars (default = "")
     * @param	boolean Option Newsletter abonieren hinzufügen, wenn true (default = true)
     * @param	boolean Benutzerdaten bearbeiten, wenn true (default = false)
	 * @return	string
    */
	public function printRegForm($type = "", $newsLetter = true, $editUser = false)
	{
		
		if($type == "")
			$type = REGISTRATION_TYPE;
		
		$newsL		= 0;
		
		$formAction	= empty($this->formAction) ? PROJECT_HTTP_ROOT . "/" . HTML::getLinkPath($editUser ? -1007 : -1006) . ($editUser ? '?edac=1' : '') : $this->formAction;
		$notice		= "";
		$queryExt	= "";
		$insExt1	= "";
		$insExt2	= "";
		$updExt		= "";
		
		// Variablen Registrierungsformular (shopuser)
		$formOfAddress	= "";
		$title			= "";
		$name			= "";
		$firstName		= "";
		$number			= "";
		$street			= "";
		$zipCode		= "";
		$city			= "";
		$country		= "";
		$company		= "";
		$phone			= "";

		// Fehlervariablen für shopuser-Typ
		$errorName		=	"";
		$errorFirstName	=	"";
		$errorNumber	=	"";
		$errorStreet	=	"";
		$errorCity		=	"";
		$errorZipCode	=	"";
		$errorCompany	=	"";
		$errorPhone		=	"";
		$errorMail		=	"";
		$countryOptions = explode(",", parent::$staText['form']['countries']); // Landesauswahl
		
		
		// Falls Benutzerdaten bearbeitet werden sollen, geloggted Benutzer auslesen
		if($editUser === true && isset($this->g_Session['username'])) {
			
			$loggedUser		= $this->g_Session['username'];
			$loggedUserDb	= $this->DB->escapeString($loggedUser);
			
			
			// Benutzerdaten auslesen
			$loggedUserQuery = $this->DB->query("SELECT * 
														FROM `" . $this->tableUser . "` 
														WHERE `username` = '$loggedUserDb' 
														");
			#var_dump($loggedUserQuery);
			
			if(count($loggedUserQuery) != 1)
				die("No user data found.");
				
			elseif($type == "shopuser") {
			
				// Benutzerdaten des geloggten Benutzers
				$formOfAddress	= Modules::safeText($loggedUserQuery[0]['gender']);
				$title			= Modules::safeText($loggedUserQuery[0]['title']);
				$name			= Modules::safeText($loggedUserQuery[0]['last_name']);
				$firstName		= Modules::safeText($loggedUserQuery[0]['first_name']);
				$streetNr		= explode(" ", $loggedUserQuery[0]['street']);
				$number			= Modules::safeText(array_pop($streetNr));
				$street			= Modules::safeText(implode(" ", $streetNr));
				$zipCode		= Modules::safeText($loggedUserQuery[0]['zip_code']);
				$city			= Modules::safeText($loggedUserQuery[0]['city']);
				$country		= Modules::safeText($loggedUserQuery[0]['country']);
				$company		= Modules::safeText($loggedUserQuery[0]['company']);
				$phone			= Modules::safeText($loggedUserQuery[0]['phone']);
		
			}
			
			$queryExt	= " AND `username` != '$loggedUserDb'";

		}

		
		// Falls das Formular abgeschickt wurde
		if(isset($GLOBALS['_POST']['submitreg'])) {
			
			$checkOK	= true;
			$userPass	= "";
			$userGroup	= "guest";
			
			$regUser	= trim($GLOBALS['_POST']['email']);
			$regUserDb	= $this->DB->escapeString($regUser);
			$userMail	= $regUserDb;
			
			// User salt
			$userSalt	= Security::generatePassword(9);
				
			
			// Überprüfung ob username bereits vorhanden ist
			$userQuery = $this->DB->query("SELECT `email` 
													FROM `" . $this->tableUser . "` 
												WHERE (`username` = '$regUserDb' 
													OR `email` = '$regUserDb') 
													$queryExt
												");
			#var_dump($userQuery);
			
			if($regUser == "")
				$errorM = "{s_error:mail1}";
			elseif(!filter_var($regUser, FILTER_VALIDATE_EMAIL))
				$errorM = "{s_error:mail2}";
			elseif(strlen($regUser) > self::usernameMaxLen)
				$errorM = "{s_error:mail2}";
			if($editUser == false && (!isset($this->g_Session['captcha']) || ($GLOBALS['_POST']['captcha_confirm'] != $this->g_Session['captcha']))) // Captcha überprüfen, falls nicht "edit"
				$errorCap = "{s_error:captcha}";
			elseif(count($userQuery) > 0)
				$errorM = "{s_error:mailexist}";


			if(isset($GLOBALS['_POST']['password1'])) {
				
				$regUserP1 = trim($GLOBALS['_POST']['password1']);
				 
				if($regUserP1 == "" && $editUser == false)
					$errorP1 = "{s_error:fill}";
				elseif($regUserP1 != "" && !preg_match("/^[a-zA-Z0-9\$§&#_-]+$/", $regUserP1))
					$errorP1 = "{s_error:wrongpass}";
				elseif(strlen($regUserP1) > PASSWORD_MAX_LENGTH)
		 			$errorP1 = sprintf(ContentsEngine::replaceStaText("{s_error:passlen2}"), PASSWORD_MAX_LENGTH);
				elseif($regUserP1 != "" && strlen($regUserP1) < PASSWORD_MIN_LENGTH)
		 			$errorP1 = sprintf(ContentsEngine::replaceStaText("{s_error:passlen1}"), PASSWORD_MIN_LENGTH);
				else
					if($editUser === true && $regUserP1 == "")
						$userPass = false;
					else
						$userPass = Security::hashPassword($this->DB->escapeString($regUserP1), CC_SALT . $userSalt);
			}
			
			if(isset($GLOBALS['_POST']['password2'])) {
				
				$regUserP2 = trim($GLOBALS['_POST']['password2']);
				 
				if($regUserP2 == "" && $regUserP1 == "" && $editUser == false)
					$errorP2 = "{s_error:fill}";
				elseif($regUserP2 == "" && $regUserP1 != "")
					$errorP2 = "{s_error:userpass1}";
				elseif($regUserP2 != $regUserP1)
					$errorP2 = "{s_error:userpass2}";
			}
			
			// Falls das Benutzerpasswort geändert werden soll
			if($userPass !== false) {
				$updExt =  "`password` = '$userPass',
							`salt` = '$userSalt',";
			}
			
			// Newsletter
			if(isset($GLOBALS['_POST']['newsl']) && $GLOBALS['_POST']['newsl'] == "on")
				$newsL = 1;


			// Falls das shopuser-Registrierungsformular ausgewertet werden soll
			if($type == "shopuser") {
				
				$formOfAddress	= Modules::safeText($GLOBALS['_POST']['formofaddress']);
				$title			= Modules::safeText($GLOBALS['_POST']['title']);
				if($title == "---")
					$title = "";
				$name			= Modules::safeText($GLOBALS['_POST']['name']);
				$firstName		= Modules::safeText($GLOBALS['_POST']['firstname']);
				$street			= Modules::safeText($GLOBALS['_POST']['street']);
				$number			= Modules::safeText($GLOBALS['_POST']['number']);
				$zipCode		= Modules::safeText($GLOBALS['_POST']['zipCode']);
				$city			= Modules::safeText($GLOBALS['_POST']['city']);
				$country		= Modules::safeText($GLOBALS['_POST']['country']);
				$company		= Modules::safeText($GLOBALS['_POST']['company']);
				$phone			= Modules::safeText($GLOBALS['_POST']['phone']);
				
				// Falls Name leer ist...
				if (empty($name)) {
					// ...Meldung ausgeben
					$errorName = '{s_error:name}';
					$checkOK = false;
				}
		
				// Falls Name zu lang ist...
				elseif (strlen($name) > 50) {
					// ...Meldung ausgeben
					$errorName = '{s_error:nametoolong}';
					$checkOK = false;
				}
				
				// Falls Vorname zu lang ist...
				if (strlen($firstName) > 50) {
					// ...Meldung ausgeben
					$errorFirstName = '{s_error:nametoolong}';
					$checkOK = false;
				}
				
				// Falls Straße leer ist...
				if (empty($street)) {
					// ...Meldung ausgeben
					$errorStreet = '{s_error:street}';
					$checkOK = false;
				}
		
				// Falls Straße zu lang ist...
				elseif (strlen($street) > 100) {
					// ...Meldung ausgeben
					$errorStreet = '{s_error:nametoolong}';
					$checkOK = false;
				}
				
				// Falls Hausnummer leer ist...
				if (strlen($number) > 10 || !preg_match("/^[0-9a-z \-\+]{1,10}$/i", $number)) {
					// ...Meldung ausgeben
					$errorNumber = '{s_error:number}';
					$checkOK = false;
				}
				
				// Falls Postleitzahl zu lang ist...
				if (!preg_match("/^[0-9]{4,5}$/", $zipCode)) {
					// ...Meldung ausgeben
					$errorZipCode = '{s_error:zipcode}';
					$checkOK = false;
				}
				
				// Falls Stadt leer ist...
				if (empty($city)) {
					// ...Meldung ausgeben
					$errorCity = '{s_error:city}';
					$checkOK = false;
				}
		
				// Falls Stadt zu lang ist...
				elseif (strlen($city) > 100) {
					// ...Meldung ausgeben
					$errorCity = '{s_error:nametoolong}';
					$checkOK = false;
				}
				
				// Falls Firma zu lang ist...
				if (strlen($company) > 50) {
					// ...Meldung ausgeben
					$errorFirstName = '{s_error:nametoolong}';
					$checkOK = false;
				}
				
				// Falls Telefon falsch ist...
				if ($phone != "" && !preg_match("/^[0-9 \-\+\/()]+$/", $phone)) {
					// ...Meldung ausgeben
					$errorPhone = '{s_error:phone}';
					$checkOK = false;
				}
								
			} // Ende if shopuser
			
	
			// Falls keine Fehler aufgetaucht sind			
			if(!isset($errorM) && !isset($errorP1) && !isset($errorP2) && !isset($errorCap) && $checkOK === true) {
				
				// Authentifizierungscode
				$authCode = md5(uniqid(time()));

				// Newsletter
				if($type == "newsletter")
					$userGroup = "subscriber";
				
				elseif($type == "shopuser") {
						
					$formOfAddressDB	= $this->DB->escapeString($formOfAddress);
					$titleDB			= $this->DB->escapeString($title);
					if($title == "---")
						$titleDB = "";
					$nameDB				= $this->DB->escapeString($name);
					$firstNameDB		= $this->DB->escapeString($firstName);
					$numberDB			= $this->DB->escapeString($number);
					$streetDB			= $this->DB->escapeString($street) . " " . $numberDB;
					$zipCodeDB			= $this->DB->escapeString($zipCode);
					$cityDB				= $this->DB->escapeString($city);
					$countryDB			= $this->DB->escapeString($country);
					$companyDB			= $this->DB->escapeString($company);
					$phoneDB			= $this->DB->escapeString($phone);
					
					// Datenbankstrings
					$insExt1	.= "`gender`," .
								   "`title`," .
								   "`last_name`," .
								   "`first_name`," .
								   "`street`," .
								   "`zip_code`," .
								   "`city`," .
								   "`country`," .
								   "`company`," .
								   "`phone`,";

					$insExt2	.= "'$formOfAddressDB'," .
								   "'$titleDB'," .
								   "'$nameDB'," .
								   "'$firstNameDB'," .
								   "'$streetDB'," .
								   "'$zipCodeDB'," .
								   "'$cityDB'," .
								   "'$countryDB'," .
								   "'$companyDB'," .
								   "'$phoneDB',";

					$updExt		.= "`gender` = '$formOfAddressDB'," .
								   "`title` = '$titleDB'," .
								   "`last_name` = '$nameDB'," .
								   "`first_name` = '$firstNameDB'," .
								   "`street` = '$streetDB'," .
								   "`zip_code` = '$zipCodeDB'," .
								   "`city` = '$cityDB'," .
								   "`country` = '$countryDB'," .
								   "`company` = '$companyDB'," .
								   "`phone` = '$phoneDB',";

				}

				
				// db-Tabelle sperren
				$lock = $this->DB->query("LOCK TABLES `" . $this->tableUser . "`");
				
			
				if($editUser === true) {
						
					// Update der Benutzerdaten
					$updateSQL = $this->DB->query("UPDATE `" . $this->tableUser . "` 
													    SET  $updExt
															 `email` = '$userMail',
															 `newsletter` = $newsL,
															 `auth_code` = '$authCode' 
														WHERE `username` = '$loggedUserDb'
														");
					#var_dump($updateSQL);
				}
				else {
				
					// Einfügen der neuen Benutzerdaten
					$updateSQL = $this->DB->query("INSERT INTO `" . $this->tableUser . "`  
														(".$insExt1."`username`, `password`, `salt`, `group`, `email`, `newsletter`, `auth_code`) 
														VALUES (".$insExt2."'$regUserDb', '$userPass', '$userSalt', '$userGroup', '$userMail', $newsL, '$authCode')
														");
				}
			
				// db-Sperre aufheben
				$unLock = $this->DB->query("UNLOCK TABLES");
				
				
				// Falls das DB-Update erfolgreich war
				if($updateSQL === true) {
				
					$this->userSuccess	= true;
					
					$mailStatus		= false;
					
					$this->regtextSubject	= str_replace("{#domain}", $this->siteDomain, $this->regtextSubject);
					
					$subject		= htmlspecialchars(ContentsEngine::replaceStaText($this->regtextSubject));
					$subject		= '=?utf-8?B?'.base64_encode($subject).'?=';
					$regMessage		= "";
					$regTextExt		= "";
					
					if(REGISTRATION_TYPE == "shopuser") {
						if($firstName != "")
							$addressUser = ($title != "" ? $title . ' ' : '') . $firstName . ' ' . $name; // Benutzeranrede
						else {
							$addressUser = $formOfAddress == "f" ? '{s_form:frau} ' : '{s_form:herr} '; // Benutzeranrede
							$addressUser .= ($title != "" ? $title . ' ' : '') . $name; // Benutzeranrede
						}
						$regTextExt		= $this->getRegMessage($this->regtextShop);
					}
					else {
						$addressUser	= htmlspecialchars(self::getMailLocalPart($regUser)); // Benutzeranrede
						
						if(REGISTRATION_TYPE == "account")
							$regTextExt .= $this->getRegMessage($this->regtextGuest);
						
						if($newsL == 1)
							$regTextExt .= $this->getRegMessage($this->regtextNewsl);
					}
					
					// Falls Accountdaten bearbeitet wurden, Erfolgsmeldung ausgeben und E-Mail an Benutzer
					if($editUser === true) {
						$this->success	= $this->getNotificationStr("{s_notice:modaccount}", "success");
						$regMessage 	= $this->regtextSubject . '.<br />{s_notice:modaccount}';
					}
					// Andernfalls Neuregistrierung, Erfolgsmeldung und E-Mail an Benutzer
					else {
					
						$this->success	= $this->getNotificationStr($this->regtextUser, "success");
						
						if(REGISTRATION_TYPE != "none" && REGISTRATION_TYPE != "newsletter") {
							
							// Button link login
							$btnDefs	= array(	"href"		=> HTML::getLinkPath(-1002),
													"class"		=> '{t_class:btnpri} formbutton ok right',
													"text"		=> "{s_form:loginnow}",
													"icon"		=> "login"
												);
								
							$this->success .=		parent::getButtonLink($btnDefs);				
						}
						
						$regMessage  = $this->getRegMessage($this->regtextThank);
						$regMessage .= $this->getRegMessage($this->regtextMessage);
					}
				
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
										<p>{s_text:hello} ".$addressUser.",</p>
										<p>&nbsp;</p>
										<p>" . $regMessage . "</p>
										<p>&nbsp;</p>
										" . $regTextExt . "
										<p>&nbsp;</p>
										<p>" . (REGISTRATION_TYPE != "newsletter" && REGISTRATION_TYPE != "none" ? '{s_form:loginnow} -> ' : '') . "<a href='" . PROJECT_HTTP_ROOT . (REGISTRATION_TYPE != "newsletter" && REGISTRATION_TYPE != "none" ? '/login' . PAGE_EXT : '') . "'>" . $this->siteDomainWWW . "</a></p>
										<hr>
										<table>
										<tbody>
										<tr>" .
										($newsL == 1 && REGISTRATION_TYPE != "none" ? "<td><a href='" . PROJECT_HTTP_ROOT . "/registration" . PAGE_EXT . "?newsletter=no&amp;un=" . $regUser . "&amp;ac=" . $authCode . "'>" . $this->regtextUnregNewsl . "</td>" : "")
										.
										($newsL == 0 && $newsLetter && REGISTRATION_TYPE != "none" ? "<td><a href='" . PROJECT_HTTP_ROOT . "/registration" . PAGE_EXT . "?newsletter=yes&amp;un=" . $regUser . "&amp;ac=" . $authCode . "'>" . $this->regtextRegNewsl . "</td>" : "")
										.
										($userGroup == 'guest' && REGISTRATION_TYPE != "none" ? "<td><a href='" . PROJECT_HTTP_ROOT . "/registration" . PAGE_EXT . "?account=no&amp;un=" . $regUser . "&amp;ac=" . $authCode . "'>{s_text:unreg}</td>" : "") .
										"</tr>
										</tbody>
										</table>
									</body>
								</html>
								";
			
					$htmlMail = ContentsEngine::replaceStaText($htmlMail) . "\n";
	
			
					// Klasse phpMailer einbinden
					require_once(PROJECT_DOC_ROOT . '/inc/classes/phpMailer/class.phpMailer.php');
					
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
						$this->error	= 	$this->getNotificationStr('{s_error:error}<br /><br />' . $mail->ErrorInfo, "error");
												
					
				} // Ende if update true
				else {
					// Falls neue Registrierung, Fehlermeldung ausgeben (bei editUser wurden die Benutzerdaten geändert, daher kein Fehler bei fehlerhaftem E-Mailversand
					if(!$editUser)
						$this->error	= 	$this->getNotificationStr('{s_error:error}', "error");
				}
		
			}
			else			
				$notice = $this->getNotificationStr('{s_error:checkform}', "error");
						
		} // Ende if submit
		
		// Andernfalls false kein Formular abgeschickt wurde und Benutzerdaten bearbeitet werden sollen
		else {
			if(!isset($GLOBALS['_POST']['submitreg']) && $editUser === true && isset($loggedUser) && count($loggedUserQuery) == 1) {
				
				$regUser	= $loggedUserQuery[0]['email'];
				$newsL		= $loggedUserQuery[0]['newsletter'];
			}
			elseif(!isset($GLOBALS['_POST']['submitreg']) && $editUser === true && count($loggedUserQuery) != 1)
				die('no user date found or data ambigous.');
		}
		
		// Formular generieren
		$extForm		= "";
		$extFormShop	= "";
		$noticeOpenTag	= '<span class="notice {t_class:texterror}">';
		$noticeCloseTag	= '</span>';
		
		switch($type) {
		
			// Falls Newsletterabonent
			case "newsletter":
				$header 	=	"{s_header:newsl}";
				$classExt	=	"newsletter";
				$extForm	=	'<input type="hidden" name="newsl" value="on" />' . "\n";
				$notice		=	"";
				break;
			
			// Falls Shopbenutzer
			case "shopuser":
				$extFormShop	.=	'<h5 class="fieldSet">{s_form:addressdata}</h5>' . "\r\n" .
									'<li class="{t_class:formrow}">' . "\r\n" . 
									'<ul class="{t_class:row}">' . "\r\n" .
									'<li class="fieldLeft {t_class:halfrow}">' . "\r\n" .
									'<label for="formofaddress">{s_form:anrede}<em>&#42;</em></label>' . "\r\n" . // Anrede
									'<select name="formofaddress" id="formofaddress" class="{t_class:select} {t_class:field}" aria-required="true" data-validation="required" tabindex="4">' . "\r\n" . 
									'<option' . "\r\n";
								
					if($formOfAddress == "m")
						$extFormShop .= ' selected="selected"';
							
					$extFormShop .=	' value="m">{s_form:herr}</option>' . "\r\n" . 
									'<option';
							
					if($formOfAddress == "f") 
						$extFormShop .=	' selected="selected"';
						
					$extFormShop .= ' value="f">{s_form:frau}</option>' . "\r\n" . 
									'</select>' . "\r\n" . 
									'</li>' . "\r\n" .
									'<li class="fieldRight {t_class:halfrow}">' . "\r\n" .
									'<label for="title">{s_form:grade}<em>&nbsp;</em></label>' . "\r\n" . // Titel
									'<select name="title" id="title" class="{t_class:select} {t_class:field}" tabindex="5">' . "\r\n" . 
									'<option>---</option>' . "\r\n" . 
									'<option';
								
					if($title == "Dr.")
						$extFormShop .= ' selected="selected"';
						
					$extFormShop .='>{s_form:dr}</option>' . "\r\n" . 
									'<option';
								
					if($title == "Prof. Dr.")
						$extFormShop .= ' selected="selected"';
						
					$extFormShop .='>{s_form:prof}</option>' . "\r\n" . 
									'<option';
								
					if($title == "Prof. Dr. Dr.")
						$extFormShop .= ' selected="selected"';
						
					$extFormShop .=	'>{s_form:profdr}</option>' . "\r\n" . 
									'</select>' . "\r\n" . 
									'</li>' . "\r\n" .
									'<br class="clearfloat" />' . "\r\n" .
									'</ul>' . "\r\n" . 
									'</li>' . "\r\n" . 
									'<li class="{t_class:formrow}">' . "\r\n" .
									'<ul class="{t_class:row}">' . "\r\n" .
									'<li class="fieldLeft {t_class:halfrow}">' . "\r\n" .
									'<label for="name">{s_form:name}<em>&#42;</em></label>' . "\r\n"; // Name
					
					if($errorName != "")
						$extFormShop .= $noticeOpenTag . $errorName . $noticeCloseTag . "\r\n";
			
					$extFormShop .= '<input name="name" type="text" id="name" class="{t_class:input} {t_class:field}" aria-required="true" tabindex="6" value="' . $name . '" maxlength="50" data-validation="required" data-validation-length="max50" />' . "\r\n" . 
									'</li>' . "\r\n" .
									'<li class="fieldRight {t_class:halfrow}">' . "\r\n" .
									($errorName != "" ? '<span class="notice fill">&nbsp;</span>' . "\r\n" : '') .
									'<label for="firstname">{s_form:firstname}<em>&nbsp;</em></label>' . "\r\n"; // Vorname
					
					if($errorFirstName != "")
						$extFormShop .= $noticeOpenTag . $errorFirstName . $noticeCloseTag . "\r\n";
			
					$extFormShop .=	'<input name="firstname" type="text" id="firstname" class="{t_class:input} {t_class:field}" tabindex="7" value="' . $firstName . '" maxlength="50" />' . "\r\n" . 
									'</li>' . "\r\n" .
									'<br class="clearfloat" />' . "\r\n" .
									'</ul>' . "\r\n" . 
									'</li>' . "\r\n" . 
									'<li class="{t_class:formrow}">' . "\r\n" .
									'<ul class="{t_class:row}">' . "\r\n" .
									'<li class="fieldLeft {t_class:halfrow}">' . "\r\n" .
									($errorNumber != "" && $errorStreet == "" ? '<span class="notice fill">&nbsp;</span>' . "\r\n" : '') .
									'<label for="street">{s_form:street}<em>&#42;</em></label>' . "\r\n"; // Straße
					
					if($errorStreet != "")
						$extFormShop .= $noticeOpenTag . $errorStreet . $noticeCloseTag . "\r\n";
			
					$extFormShop .=	'<input name="street" type="text" id="street" class="{t_class:input} {t_class:field}" aria-required="true" tabindex="8" value="' . $street . '" maxlength="100" data-validation="required" data-validation-length="max100" />' . "\r\n" . 
									'</li>' . "\r\n" .
									'<li class="fieldRight {t_class:halfrow}">' . "\r\n" .
									($errorStreet != "" && $errorNumber == "" ? '<span class="notice fill">&nbsp;</span>' . "\r\n" : '') .
									'<label for="number">{s_form:number}<em>&#42;</em></label>' . "\r\n"; // Hausnummer
					
					if($errorNumber != "")
						$extFormShop .= $noticeOpenTag . $errorNumber . $noticeCloseTag . "\r\n";
			
					$extFormShop .=	'<input name="number" type="text" id="number" class="{t_class:input} {t_class:field}" aria-required="true" tabindex="9" value="' . $number . '" maxlength="10" data-validation="required" data-validation-length="max10" />' . "\r\n" . 
									'</li>' . "\r\n" .
									'<br class="clearfloat" />' . "\r\n" .
									'</li>' . "\r\n" . 
									'</ul>' . "\r\n" . 
									'<li class="{t_class:formrow}">' . "\r\n" .
									'<ul class="{t_class:row}">' . "\r\n" .
									'<li class="fieldLeft {t_class:halfrow}">' . "\r\n" .
									($errorCity != "" && $errorZipCode == "" ? '<span class="notice fill">&nbsp;</span>' . "\r\n" : '') .
									'<label for="zipCode">{s_form:zipcode}<em>&#42;</em></label>' . "\r\n"; // Plz
					
					if($errorZipCode != "")
						$extFormShop .= $noticeOpenTag . $errorZipCode . $noticeCloseTag . "\r\n";
			
					$extFormShop .=	'<input name="zipCode" type="text" id="zipCode" class="{t_class:input} {t_class:field}" aria-required="true" tabindex="10" value="' . $zipCode . '" maxlength="5" data-validation="required" data-validation-length="max5" />' . "\r\n" . 
									'</li>' . "\r\n" .
									'<li class="fieldRight {t_class:halfrow}">' . "\r\n" .
									($errorZipCode != "" && $errorCity == "" ? '<span class="notice fill">&nbsp;</span>' . "\r\n" : '') .
									'<label for="city">{s_form:city}<em>&#42;</em></label>' . "\r\n"; // Ort
					
					if($errorCity != "")
						$extFormShop .= $noticeOpenTag . $errorCity . $noticeCloseTag . "\r\n";
			
					$extFormShop .=	'<input name="city" type="text" id="city" class="{t_class:input} {t_class:field}" aria-required="true" tabindex="11" value="' . $city . '" maxlength="100" data-validation="required" data-validation-length="max100" />' . "\r\n" . 
									'</li>' . "\r\n" .
									'<br class="clearfloat" />' . "\r\n" .
									'</ul>' . "\r\n" . 
									'</li>' . "\r\n" . 
									'<li class="{t_class:formrow}">' . "\r\n" .
									'<label for="country">{s_form:country}<em>&#42;</em></label>' . "\r\n" . // Landesauswahl
									'<select name="country" id="country" class="{t_class:select} {t_class:field}" aria-required="true" tabindex="12" data-validation="required">' . "\r\n";
								
					foreach($countryOptions as $countyOpt) {
						$extFormShop .=	'<option value="' . $countyOpt . '"' . "\r\n";
								
					if($country == $countyOpt)
						$extFormShop .= ' selected="selected"';
							
					$extFormShop .=	'>' . $countyOpt . '</option>' . "\r\n";
					}
					
					$extFormShop .=	'</select>' . "\r\n" . 
									'</li>' . "\r\n" .
									'<li class="{t_class:formrow}">' . "\r\n" .
									'<label for="company">{s_form:company}</label>' . "\r\n"; // Firma
					
					if($errorCompany != "")
						$extFormShop .= $noticeOpenTag . $errorCompany . $noticeCloseTag . "\r\n";
			
					$extFormShop .=	'<input name="company" type="text" id="company" class="{t_class:input} {t_class:field}" tabindex="13" value="' . $company . '" maxlength="50" />' . "\r\n" . 
									'</li>' . "\r\n" . 
									'<li class="{t_class:formrow}">' . "\r\n" .
									'<label for="phone">{s_form:phone2}</label>' . "\r\n"; // Telefon
					
					if($errorPhone != "")
						$extFormShop .= $noticeOpenTag . $errorPhone . $noticeCloseTag . "\r\n";
			
					$extFormShop .=	'<input name="phone" type="text" id="phone" class="{t_class:input} {t_class:field}" value="' . $phone . '" maxlength="50" tabindex="14" />' . "\r\n" . 
									'<input type="text" name="m-mail" id="m-mail" class="emptyfield" value="" />' . "\r\n" . // Mock field
									'</li>' . "\r\n";
					
					$extFormShop .=	'</li>' . "\r\n";

			
			// Falls Benutzerkonto oder Shopbenutzer
			case "account":
			case "shopuser":
				$header		=	$editUser === true ? "{s_header:modaccount}" : "{s_header:register}";
				$classExt	=	"register";
				$extForm	=	'<li class="{t_class:formrow}">' . "\r\n" . 
								'<label for="password1">{s_label:password1}' . ($editUser === true ? ' {s_label:nopwchange}' : '<em>&#42;</em>') . '</label>' . "\n" . 
								(isset($errorP1) ? $noticeOpenTag . $errorP1 . $noticeCloseTag : '') . 
								'<span class="{t_class:inputgroup}">' . "\n" . 
								'<span class="{t_class:inputaddon}">' . parent::getIcon("lock", "", "", "") . '</span>' . "\n" . 
								'<input type="password" name="password1" id="password1" class="{t_class:input} {t_class:field}" tabindex="2" value="' . (isset($regUserP1) ? htmlspecialchars($regUserP1) : '') . '" maxlength="'.PASSWORD_MAX_LENGTH.'" data-validation="length" data-validation-length="'.PASSWORD_MIN_LENGTH.'-'.PASSWORD_MAX_LENGTH.'" />' . "\n" . 
								'</span>' . "\r\n" .
								'</li>' . "\r\n" .
								'<li class="{t_class:formrow}">' . "\r\n" . 
								'<label for="password2">{s_label:password2}' . ($editUser === true ? '' : '<em>&#42;</em>') . '</label>' . "\n" . 
								(isset($errorP2) ? $noticeOpenTag . $errorP2 . $noticeCloseTag : '') . 
								'<span class="{t_class:inputgroup}">' . "\n" . 
								'<span class="{t_class:inputaddon}">' . parent::getIcon("lock", "", "", "") . '</span>' . "\n" . 
								'<input type="password" name="password2" id="password2" class="{t_class:input} {t_class:field}" tabindex="3" value="' . (isset($regUserP2) ? htmlspecialchars($regUserP2) : '') . '" maxlength="'.PASSWORD_MAX_LENGTH.'" data-validation="confirmation" data-validation-confirm="password1" />' . "\n" .
								'</span>' . "\r\n" .
								'</li>' . "\r\n";
				break;
				
		}
		
		// Falls die Registrierung erfolgreich war bzw. eine (Fehler-)Meldung vorliegt
		if(!empty($this->success)
		|| !empty($this->error)
		) {
			$regForm = 	'<div id="regForm" class="form {t_class:fullrow} {t_class:margintm} {t_class:marginbm}">' . "\n" . 
						'<div class="top"></div>' . "\r\n" .
						'<div class="center">' . "\r\n" .
                		'<form action="' . $formAction . '#regForm" method="post" id="regform" class="{t_class:form}">' . "\n" . 
						'<fieldset>' . "\n" . 
						'<legend>{s_form:user}</legend>' . "\n" . 
						'<h2 class="regFormHeader">'.$header.'</h2>' . "\n";
		
			// Error box
			$regForm .=	'<div class="formErrorBox">' . "\r\n";
						
			$regForm .=	$this->success;
			$regForm .=	$this->error;
						
			if($editUser === true) {
		
				// Button link back
				$btnDefs	= array(	"href"		=> HTML::getLinkPath(-1007),
										"class"		=> '{t_class:btnsec} formbutton back alt',
										"text"		=> "{s_button:back}",
										"icon"		=> "back"
									);
					
				$regForm .=		parent::getButtonLink($btnDefs);				
			}
			
			$regForm .=	'</div>' . "\n" . // close errorBox
						'</fieldset>' . "\n" . 
						'</form>' . "\n" . 
						'</div>' . "\r\n" .
						'<div class="bottom"></div>' . "\r\n" .
						'</div>' . "\r\n";
			
			return $regForm;
		}
			
		$regForm = 		'<div id="regForm" class="form {t_class:fullrow} {t_class:margintm} {t_class:marginbm}">' . "\n" .
						'<div class="top"></div>' . "\r\n" .
						'<div class="center">' . "\r\n" .
                		'<form action="' . $formAction . '#regForm" method="post" id="regform" class="{t_class:form}">' . "\n" .
						'<fieldset>' . "\n" .
						'<legend>{s_form:user}</legend>' . "\n" .
						'<h2 class="regFormHeader">'.$header.'</h2>' . "\n" .
						'<div class="formErrorBox">' . "\r\n" . // Error box
						$notice	.
						'</div>' . "\n" .
						'<ul>' . "\n" .
						'<p class="footnote topNote {t_class:alert} {t_class:info}">{s_form:req}</p>' . "\r\n" .
						($editUser === true ? '<h5 class="fieldSet">{s_header:logindata}</h5>' . "\r\n" : '');
								
		// E-Mail einbinden
		$regForm .= 	'<li class="{t_class:formrow}">' . "\n" .
						'<label for="email">{s_label:user}<em>&#42;</em></label>' . "\n" .
						(isset($errorM) ? $noticeOpenTag . $errorM . $noticeCloseTag : '') .
						'<span class="{t_class:inputgroup}">' . "\n" .
						'<span class="{t_class:inputaddon}">@</span>' . "\n" .
						'<input type="text" name="email" id="email" class="{t_class:input} {t_class:field}" maxlength="' . self::usernameMaxLen . '" tabindex="1" value="' . (isset($regUser) ? htmlspecialchars($regUser) : '') . '" data-validation="email" data-validation-length="max' . self::usernameMaxLen . '" />' . "\n" .
						'</span>' . "\n" .
						'</li>' . "\n";
							
		// Formularfelder einbinden
		$regForm .= 	$extForm . $extFormShop;
		
						
		if($newsLetter == true || $type == "newsletter")
			$regForm .= '<li class="{t_class:rowcheckbox}">' . "\r\n" . 
						'<label for="newsl">' . "\n" .
						'<input type="checkbox" name="newsl" id="newsl" class="newsletter {t_class:checkbox}" tabindex="' . ($type == "newsletter" ? 2 : 15) . '"' . ($newsL == 1 || $type == "newsletter" ? ' checked="checked"' : '') . ($type == "newsletter" ? ' disabled="true"' : '') . ' />' . "\r\n" .
						'{s_label:newsl}</label>' . "\n" .
						'</li>' . "\n";
		
		// Falls nicht Edit (sprich bereits eingeloggt), Captcha einbinden
		if(!$editUser) {
			$regForm .=		'<li class="{t_class:formrow}">' . "\r\n" . 
							'<ul class="{t_class:row}">' . "\r\n" .
							'<li class="fieldLeft {t_class:halfrowsm}">' . "\r\n" .
							'<label for="captcha_confirm">{s_form:captcha}<em>&#42;</em></label>' . "\r\n" .
							(isset($errorCap) ? $noticeOpenTag . $errorCap . $noticeCloseTag : '') . "\r\n" .						
							'<input name="captcha_confirm" type="text" id="captcha_confirm" class="{t_class:input} {t_class:field}" tabindex="' . ($type == "newsletter" ? 4 : 17) . '" area-required="true" data-validation="required" />' . "\r\n" . 
							'</li>' . "\r\n" . 
							'<li class="fieldRight {t_class:halfrowsm}">' . "\r\n" .
							'<span class="captchaBox">' . "\r\n" .
							'<label>&nbsp;</label><br />' . "\r\n" .
							'<img src="' . PROJECT_HTTP_ROOT . '/access/captcha.php" alt="{s_form:capalt}" title="{s_form:captit}" class="captcha" />' . "\r\n";
			
		
			// Button caprel
			$btnDefs	= array(	"href"		=> PROJECT_HTTP_ROOT . '/access/captcha.php',
									"text"		=> '',
									"class"		=> "caprel button-icon-only {t_class:btninf} {t_class:btnsm}",
									"title"		=> '{s_form:capreltit}',
									"attr"		=> 'tabindex="' . ($type == "newsletter" ? 3 : 16) . '"',
									"icon"		=> "refresh",
									"icontext"	=> ""
								);
			
			$regForm .=		parent::getButtonLink($btnDefs);
							
			$regForm .=		'</span>' . "\r\n" . 
							'</li>' . "\r\n" . 
							'</ul>' . "\r\n" . 
							'</li>' . "\r\n";
		}
		
		$regForm .=		'<li class="submitPanel {t_class:formrow}">' . "\r\n";
		
		if($editUser === true) {
		
			// Button link back
			$btnDefs	= array(	"href"		=> HTML::getLinkPath(-1007),
									"class"		=> '{t_class:btnsec} formbutton back alt',
									"text"		=> "{s_button:back}",
									"icon"		=> "back"
								);
				
			$regForm .=		parent::getButtonLink($btnDefs);				
		}
		
		// Button confirm
		$btnDefs	= array(	"type"		=> "submit",
								"name"		=> "submitreg",
								"id"		=> "submitreg",
								"class"		=> '{t_class:btnpri} formbutton ok' . ($editUser ? ' {t_class:right}' : ''),
								"value"		=> "{s_button:submit}",
								"attr"		=> 'tabindex="' . ($type == "newsletter" ? 5 : 18) . '"',
								"icon"		=> "ok"
							);
			
		$regForm .=		parent::getButton($btnDefs);				
			
		$regForm .=		'<input type="hidden" name="submitreg" value="{s_button:submit}" />' . "\n" . 
						'<input type="hidden" name="token" value="' . parent::$token . '" class="token" />' . "\r\n" . 
						'</li>' . "\n" . 
						'</ul>' . "\n" . 
						'</fieldset>' . "\n" . 
						'</form>' . "\n" . 
						'</div>' . "\r\n" .
						'<div class="bottom">' . "\r\n";
		
		if($type != "newsletter") {
		
			$regForm .=		'<hr /><p class="{t_class:alert} {t_class:info} {t_class:margintl}">{s_form:regged} ';
		
			// Button link goto login page
			$btnDefs	= array(	"href"		=> PROJECT_HTTP_ROOT . "/" . HTML::getLinkPath(-1002),
									"class"		=> 'gotoLogin {t_class:btnlink}',
									"text"		=> "{s_link:gotologin}",
									"attr"		=> 'tabindex="5"',
									"icon"		=> "signin"
								);
				
			$regForm .=	parent::getButtonLink($btnDefs);
			$regForm .=	'</p>' . "\n";
		}
		
		$regForm .=		'</div>' . "\r\n" .
						'</div>' . "\r\n" .
						'<script type="text/javascript">' . "\n" . 
						'/* <![CDATA[ */' . "\n" . 
						($this->headJS ? 'head.ready("jquery",function(){' : '') .
						'$(document).ready(function() {' .
						'$("#email").focus();' . 
						'});' . "\n" . 
						($this->headJS ? '});' : '') .
						'/* ]]> */' . "\n" . 
						'</script>' . "\n";
			 												
							
		return $regForm;
	
	}



	/**
	 * Gibt Benutzerinhalte für "mein Bereich" zurück
	 * 
	 * @access	public
	 * @param	string Benutzer-ID
	 * @param	string Benutzername
	 * @return	boolean
	 */
	public function getPersonalPage($userID, $username)
	{
	
		$output		= "";
		$greetName	= htmlspecialchars(self::getMailLocalPart($username));
		
		$output .= 		'<div id="personalPage" class="form {t_class:fullrow} {t_class:margintm} {t_class:marginbm}">' . "\n";

		if(REGISTRATION_TYPE != "account" && REGISTRATION_TYPE != "shopuser")
			$output .= 		'<h1>{s_header:userpage}</h1>' . "\n" .
							'<p>{s_header:welcome} ' . $greetName . '.</p>' . "\n" .
							'</div>' . "\n";
		
		else {
			
			// Falls die Benutzerdaten bearbeitet werden sollen
			if(isset($GLOBALS['_GET']['edac']) && $GLOBALS['_GET']['edac'] == "1") {
				$output .= 		'<h1>{s_header:userpage}</h1>' . "\n" .
								$this->printRegForm(REGISTRATION_TYPE, true, true);
			}
			
			// Andernfalls Daten für Benutzerbereich festlegen
			else {
				$output .= 		'<h1>{s_header:userpage}</h1>' . "\n" .
								'<p class="editAccount">';
			
				// Button link edit user
				$btnDefs	= array(	"href"		=> "?edac=1",
										"class"		=> 'editAccount {t_class:btnpri}',
										"text"		=> "{s_header:modaccount}",
										"icon"		=> "user"
									);
					
				$output .=	parent::getButtonLink($btnDefs);
				
				$output .=		'</p>' . "\n" .
								'<h2 class="regFormHeader">{s_header:welcome} ' . $greetName . '.</h2>' . "\n";
				
				// Dateien in Benutzerordner
				#$output .=		$this->getUserFiles($userID, "testfile", "");
				#$output .= 	$this->getUserFiles($username, "pdfs", "Fragebogen-");
				#$output .= 	$this->getUserFiles($username, "pics", "cv-");
				
				$output 	.= $this->getUserData($username);
				
				// Warenkorb holen, falls SHopuser
				if(REGISTRATION_TYPE == "shopuser")
					$output		.= "{cart}";
			}
			
		}
		
		$output .= 		'</div>' . "\n";
		
		return $output;
		
	}



	/**
	 * Gibt Benutzerdaten für den geloggten Benutzer für "mein Bereich" zurück
	 * 
	 * @access	public
	 * @param	string Benutzername
	 * @param	string Ordnername
	 * @param	string Dateinamen-Präfix (default = '')
	 * @return	boolean
	 */
	public function getUserData($username)
	{
	
		$output = "";
		
		$loggedUserDb	= $this->DB->escapeString($username);
		
		
		// Benutzerdaten auslesen
		$loggedUserQuery = $this->DB->query("SELECT * 
													FROM `" . $this->tableUser . "` 
													WHERE `username` = '$loggedUserDb' 
													");
		#var_dump($loggedUserQuery);
		
		if(count($loggedUserQuery) == 1) {
						
			// Benutzerdaten des geloggten Benutzers
			$formOfAddress	= Modules::safeText($loggedUserQuery[0]['gender']);
			if($formOfAddress == "f")
				$formOfAddress = parent::$staText['form']['frau'];
			else
				$formOfAddress = parent::$staText['form']['herr'];
			$title			= Modules::safeText($loggedUserQuery[0]['title']);
			$name			= Modules::safeText($loggedUserQuery[0]['last_name']);
			$firstName		= Modules::safeText($loggedUserQuery[0]['first_name']);
			$streetNr		= explode(" ", $loggedUserQuery[0]['street']);
			$number			= Modules::safeText(array_pop($streetNr));
			$street			= Modules::safeText(implode(" ", $streetNr));
			$zipCode		= Modules::safeText($loggedUserQuery[0]['zip_code']);
			$city			= Modules::safeText($loggedUserQuery[0]['city']);
			$country		= Modules::safeText($loggedUserQuery[0]['country']);
			$company		= Modules::safeText($loggedUserQuery[0]['company']);
			$email			= Modules::safeText($loggedUserQuery[0]['email']);
			$phone			= Modules::safeText($loggedUserQuery[0]['phone']);
			$newsLetter		= Modules::safeText($loggedUserQuery[0]['newsletter']);
			if($newsLetter == 1)
				$newsLetter = parent::getIcon("ok");
			else
				$newsLetter = "-";
	
			// Benutzerdetails
			$output = "	<table class='userDetails {t_class:table} {t_class:tablestr}'>
							<thead>
								<tr>
								<th colspan='4'>{s_header:userdata}</th>
								</tr>
							</thead>
							<tbody>
								<tr>
								<td>{s_form:address}</td><td>&nbsp;</td><td colspan='3'>$formOfAddress $title $firstName $name</td>
								</tr>
								<tr>
								<td>&nbsp;</td><td>&nbsp;</td><td colspan='3'>$street $number<br />$zipCode $city<br />$country</td>
								</tr>
								<tr class='alternate'>
								<td>{s_form:company}</td><td>&nbsp;</td><td colspan='3'>$company</td>
								</tr>
								<tr>
								<td>{s_form:phone}</td><td>&nbsp;</td><td colspan='3'>$phone</td>
								</tr>
								<tr class='alternate'>
								<td>{s_form:email}</td><td>&nbsp;</td><td colspan='3'>$email</td>
								</tr>
								<tr>
								<td>Newsletter</td><td>&nbsp;</td><td colspan='3'>$newsLetter</td>
								</tr>
							</tbody>
						</table>
						<p>&nbsp;</p>
						";
		}
							
		return $output;
		
	}



	/**
	 * Gibt Benutzer-Dateien für den geloggten Benutzer für "mein Bereich" zurück
	 * 
	 * @access	public
	 * @param	string Benutzername
	 * @param	string Ordnername
	 * @param	string Dateinamen-Präfix (default = '')
	 * @return	boolean
	 */
	public function getUserFiles($userID, $folder, $prefix = "")
	{
		
		$output = "";
		
		// Klasse Files einbinden
		require_once PROJECT_DOC_ROOT . "/inc/classes/Media/class.Files.php";
		
		$folderUrl	= PROJECT_HTTP_ROOT . '/_user/' . $folder;
		$folder		= PROJECT_DOC_ROOT . '/_user/' . $folder;
		
		// Bilderordner ins Array einlesen
		if(is_dir($folder)) {
		
			$handle = opendir($folder);
		
			while($content = readdir($handle)) {
				if( $content != ".." && 
					strpos($content, ".") !== 0
				) {
					
					$baseName	= basename($content);
					$fileName	= substr($baseName, 0, strrpos($baseName,'.')); // Bestimmen des Dateinamens ohne Erweiterung
					$fileExt	= strtolower(substr($baseName, strrpos($baseName,'.')+1, strlen($baseName)-1)); // Bestimmen der Dateinamenerweiterung
					$icon		= PROJECT_HTTP_ROOT . '/' . IMAGE_DIR;
					if($fileExt == "pdf")
						$icon	.= "pdf.png";
					elseif($fileExt == "zip")
						$icon	.= "zip.png";
					elseif($fileExt == "doc" || $fileExt == "docx")
						$icon	.= "doc.png";
					elseif($fileExt == "jpg" || $fileExt == "jpeg" || $fileExt == "png" || $fileExt == "gif" || $fileExt == "bmp")
						$icon	.= "icon_image.png";
					else
						$icon	.= "icon_file.png";
						
					if($prefix != "")
						$userSeek	= substr($fileName, strpos($fileName, $prefix) + strlen($prefix));
					else
						$userSeek	= $fileName;
					
					if(Files::getValidFileName($userID, true) == $userSeek) {

						$userFile[] = $content;
						if(REGISTRATION_TYPE == "account")
							$output .= 	'<p><span class="fileIcon"><img src="' . $icon . '" alt="icon" /></span> ' . "\n" .
										'<a href="' . $folderUrl . '/' . $baseName . '?pf=' . $prefix . '">' . $baseName . '</a></p>' . "\n";
					}
					
				}
			}
			closedir($handle);
			
		}
		else
			return "folder \"" . $folder . "\" does not exist.";
	
		return $output;
		
	}



	/**
	 * Gibt eine Liste von Benutzer-E-Mails zurück
	 * 
	 * @access	public
	 * @param	array 	$userGroup	Benutzergruppe(n)
     * @param	boolean $newsLetter	wenn true, werden nur E-Mails von Benutzern, die den Newsletter abonieren ausgewählt (default = true)
     * @param	boolean $inclAuthor	wenn true, wird die E-Mail des aktuell geloggten Benutzers (Author) mit ausgewählt (default = false)
     * @param	array	$resendMails wenn nicht leer, wird die E-Mail an diese Benutzer erneut gesendet, daher nur diese wählen
	 * @return	boolean
	 */
	public function getUserEmail($userGroup, $newsLetter = true, $inclAuthor = false, $resendMails = array())
	{
	
		$restrict	= "";
		
		if(in_array("<all>", $userGroup)) {
			$restrict	.= "`group` != ''";
		}
		else {
			$restrict	.= "(";
			foreach($userGroup as $group) {
				$group	= $this->DB->escapeString($group);
				$restrict	.= "`group` = '".$group."' OR FIND_IN_SET('" . $group . "', `own_groups`) OR ";
			}
			$restrict	= substr($restrict, 0, -4);
			$restrict	.= ")";
		}
		
		// Falls nur Newsletterempfänger adressiert werden sollen
		if($newsLetter)
			$restrict	.= " AND `newsletter` = 1";
		
		// Falls der Newsletterauthor mit angeschrieben werden soll
		if($inclAuthor && isset($this->g_Session['username'])) {
			$authorDB	= $this->DB->escapeString($this->g_Session['username']);
			$restrict	.= " OR `username` = '$authorDB'";
		}
		
		// Falls ein Newsletter erneut gesendet werden soll sind hier die Empfänger im Array enthalten
		if(count($resendMails) > 0) {

			$restrict	.= " AND (";
			
			foreach($resendMails as $retryReceip) {
				$email = $this->DB->escapeString($retryReceip);
				$restrict	.= "`email` = '$email' OR ";
			}
			
			$restrict	= substr($restrict, 0, -4);
			$restrict	.= ")";
		}
		
		//Eingeschränkte Ergebnismenge
		$sql = "SELECT `username`, `email`, `auth_code` 
				FROM `" . $this->tableUser . "` 
				WHERE $restrict";
				
		//Direkt auf das globale Objekt zugreifen
		$result = $this->DB->query($sql);
		
		if(count($result) > 0)
			return $result;
		else
			return false;
	}   



	/**
	 * Gibt ein Benutzer-Bild (Avatar) zurück
	 * 
	 * @access	public
	 * @param	string 	$userID	Benutzer-ID
	 * @param	boolean	$returnArray	Bildquellpfad und Angabe über eigenes Bild als Array zurückgeben (default = false)
	 * @return	string/array	Bildquellpfad (, eigenes Benutzerbild)
	 */
	public static function getUserImageSrc($userID, $returnArray = false)
	{

		// Benutzerbild ermitteln
		$baseName		= PROJECT_DOC_ROOT . '/_user/img/avatar_' . $userID;
		$fileExt		= "";
		$userFileSrc	= array("", false);
		
		if(file_exists($baseName . '.png')) {
			$fileExt	= ".png";
		}
		elseif(file_exists($baseName . '.jpg')) {
			$fileExt	= ".jpg";
		}
		elseif(file_exists($baseName . '.jpeg')) {
			$fileExt	= ".jpeg";
		}
		
		// Benutzerbild, falls vorhanden
		if($fileExt	!= "") {
			
			// User-ID verschlüsseln
			require_once(PROJECT_DOC_ROOT."/inc/classes/Modules/class.myCrypt.php"); // Klasse myCrypt einbinden
			
			// myCrypt Instanz
			$crypt = new myCrypt();
							
			// Encrypt Name
			$userFileSrc[0]	=	PROJECT_HTTP_ROOT . '/_user/img/avatar_' . $crypt->encrypt($userID) . $fileExt;
			$userFileSrc[1]	=	true;
		}
		// Andernfalls leerer Avatar
		else
			$userFileSrc[0]	=	SYSTEM_IMAGE_DIR . '/empty_avatar.png';
		
		if($returnArray)
			return $userFileSrc; // Bildpfad als Array zurückgeben
		else
			return $userFileSrc[0]; // Nur Bildpfad zurückgeben
	
	}



	/**
	 * Löscht ein Benutzer-Bild (Avatar)
	 * 
	 * @access	public
	 * @param	array 	$userID	Benutzer-ID
	 * @return	string
	 */
	public static function deleteUserImage($userID)
	{

		// Benutzerbild ermitteln
		$baseName	= PROJECT_DOC_ROOT . '/_user/img/avatar_' . $userID;
		
		if(file_exists($baseName . '.png'))
			@unlink($baseName . '.png');
		if(file_exists($baseName . '.jpg'))
			@unlink($baseName . '.jpg');
		if(file_exists($baseName . '.jpeg'))
			@unlink($baseName . '.jpeg');
		
		return true;
	
	}
	
	


	/**
	 * Methode zum Herausnehmen oder Hinzufügen eines Newsletterempfängers
	 * 
	 * @access	public
	 * @param	string Benutzer
	 * @param	string Authorisierungscode
	 * @param	string Aussage über Hinzufügen (yes/no)
	 * @return	boolean/varchar
	 */
	public function subscriptionStatus($userName, $authCode, $addNewsl)
	{
		
		$delUser	= 0;
		$unregUser	= 0;
		$userName	= $this->DB->escapeString($userName);
		$authCode	= $this->DB->escapeString($authCode);
		$return		= false;
		
		if($addNewsl == "yes")
			$newsL = 1;
		else
			$newsL = 0;
			
		// db-Tabelle sperren
		$lock = $this->DB->query("LOCK TABLES `" . $this->tableUser . "`");

		if($addNewsl == "no") {
			
			$deleteSQL = $this->DB->query("DELETE 
													FROM `" . $this->tableUser . "` 
												WHERE (`username` = '$userName' 
													OR `email` = '$userName') 
													AND `auth_code` = '$authCode' 
													AND `group` = 'subscriber'
												");
		
			$delUser = mysqli_affected_rows($this->DB->MySQLiObj);
		}
		
		$updateSQL = $this->DB->query("UPDATE `" . $this->tableUser . "` 
												SET `newsletter` = $newsL 
											WHERE (`username` = '$userName' 
												OR `email` = '$userName') 
												AND `auth_code` = '$authCode'
											");
	
		$unregUser = mysqli_affected_rows($this->DB->MySQLiObj);
		
		// Falls keine Aktualisierung erfolgt ist, überprüfen ob z.B. Newsletter bereits auf gewünschtem Status war
		if($unregUser == 0 && $delUser == 0) {
						
			$querySQL = $this->DB->query( "SELECT `newsletter` 
													FROM `" . $this->tableUser . "` 
												WHERE (`username` = '$userName' 
													OR `email` = '$userName') 
													AND `auth_code` = '$authCode'
												");
			
			
			// Falls Subscriber-Status unverändert, "nochange" zurückgeben
			if(count($querySQL) > 0 && $querySQL[0]['newsletter'] == $newsL)
				$return = "nochange";
			else
				$return = "nouser";
		}
		else
			$return = true; // True, falls Änderung erfolgt
			
		// db-Sperre aufheben
		$unLock = $this->DB->query("UNLOCK TABLES");
		
		return $return;
	
	}   



	/**
	 * Methode zum Löschen eines Benutzers
	 * 
	 * @access	public
	 * @param	string Benutzer
	 * @param	string Authorisierungscode
	 * @return	boolean
	 */
	public function deleteUser($userName, $authCode)
	{
		
		$delUser	= 0;
		$delUserDB	= $this->DB->escapeString($userName);
		$authCode	= $this->DB->escapeString($authCode);
		$return		= false;
		
		// db-Tabelle sperren
		$lock = $this->DB->query("LOCK TABLES `" . $this->tableUser . "`");



		$deleteSQL = $this->DB->query("DELETE 
											FROM `" . $this->tableUser . "` 
										WHERE (`username` = '$delUserDB' 
											OR `email` = '$delUserDB') 
											AND `auth_code` = '$authCode'
										");
	
		$delUser = mysqli_affected_rows($this->DB->MySQLiObj);
		
		// Falls keine Löschung erfolgt ist, überprüfen ob Benutzer vorhanden ist
		if($delUser == 0) {
						
			$querySQL = $this->DB->query( "SELECT `username` 
													FROM `" . $this->tableUser . "` 
												WHERE (`username` = '$delUserDB' 
													OR `email` = '$delUserDB') 
													AND `auth_code` = '$authCode'
												");
			
			
			// Falls Subscriber-Status unverändert, "nochange" zurückgeben
			if(count($querySQL) == 0)
				$return = "nouser";
		}
		else
			$return = true;
		
		// db-Sperre aufheben
		$unLock = $this->DB->query("UNLOCK TABLES");
		
								
		// Falls der Benutzer dessen Konto gerade gelöscht wurde derzeit eingeloggt ist, den Benutzer ausloggen
		if($return === true && isset($this->g_Session['username']) && $this->g_Session['username'] == $userName)
			$this->logoutUser($userName, false);
			

		return $return;
			
	}   



	/**
	 * Methode zum Ausloggen eines Benutzers
	 * 
	 * @access	public
	 * @param	string Benutzername
	 * @param	boolean gotoLogoutPage Wenn true, zur Logoutseite gehen
	 * @return	boolean
	 */
	public function logoutUser($userName, $gotoLogoutPage = true)
	{
		
		$sessionUser = $this->DB->escapeString($userName);

		// Code in DB zurücksetzen
		$sql = $this->DB->query("UPDATE `" . $this->tableUser . "` 
										SET `logID` = ''
										WHERE `username` = '".$sessionUser."'
									 ");

		// Cookie löschen
		setcookie("conciseLog", "", time()-3600, "/");
		
		
		// Zerstören der Sitzung
		session_destroy();

		// Zur Logoutbestätigungsseite gehen, falls gesetzt
		if($gotoLogoutPage) {
			header("Location: " . PROJECT_HTTP_ROOT . "/logout" . PAGE_EXT);
			exit;
		}
	}



	/**
	 * Replace placeholders and return appropriate Message format
	 * 
	 * @param	string Text
	 * @access	public
	 * @return	boolean
	 */
	public function getRegMessage($text)
	{
	
		if(strpos($text, "{#domain}") !== false)
			$text	= str_replace("{#domain}", $this->siteDomain, $text);
		
		if(strpos($text, "<") !== 0)
			$text	= '<p>' . $text . '</p>';		
		
		return $text;
	
	}	
	

	/**
	 * getMailLocalPart
	 * @access public
	 */
	public static function getMailLocalPart($email)
	{
	
		$mlArr	= explode("@", $email);
		$ml		= reset($mlArr);
		
		return $ml;
	
	}
	
	
	/**
	 * Ermittelt die echte IP des Benutzers
	 *
	 * Da diese auch hinter weiteren Angaben versteckt sein kann.
	 * 
	 * @access	public
	 * @param	null/boolean	$anonymize wenn true, wird letztes Oktett auf 0 gesetzt (default = null)
	 * @param	boolean			$cookie wenn true, überprüft evtl. vorhandenes ID-Cookie (default = false)
	 * @return	varchar Gibt die ermittelte IP-Adresse zurück
	 */
	public static function getRealIP($anonymize = null, $cookie = false)
	{
	
		if($anonymize === null)
			$anonymize = ANONYMIZE_IP;
		
		$realipExt = "";
		
		if (isset ($_SERVER["HTTP_CLIENT_IP"]))
		{
			$realip = $_SERVER["HTTP_CLIENT_IP"];
		}
		elseif (isset ($_SERVER["HTTP_X_FORWARDED_FOR"]))
		{
			$realip = $_SERVER["HTTP_X_FORWARDED_FOR"];
		}
		elseif (isset ($_SERVER["REMOTE_ADDR"]))
		{
			$realip = $_SERVER["REMOTE_ADDR"];
		}
		else
		{
			$realip = '0.0.0.0';
		}
		
		// Ggf. IP anonymisieren
		if($anonymize === true) {
		
			$realip = self::anonymizeIP($realip, ANONYMIZE_IP_BYTES);
		}
		
		// Falls ein Cookie zum Merken der IP gesetzt werden soll
		if($cookie === true) {
			
			if(isset($GLOBALS['_COOKIE']['realip_aid'])) {
				
				$realip = $GLOBALS['_COOKIE']['realip_aid'];
				return $realip;
			}
			
			$realipExt = '-'.time();
			$exp = time()+60*60*24*180;
			
			setcookie("realip_aid", $realip . $realipExt, $exp, "/");
		}
		
		return $realip . $realipExt;
	}
	
	
	
	/**
	 * Erstellt eine anonymisierte IP
	 *
	 * @access	public
	 * @param	string		$ip IP
	 * @param	integer		$bytes	Anzahl zu anonymisierender Bytes (default = 2)
	 * @return	varchar		anonymisierte IP-Adresse
	 */
	public static function anonymizeIP($ip, $bytes = 2)
	{
	
		if($bytes == 2)
			return long2ip(ip2long($ip) & 0xFFFFFF00);
		
		if($bytes == 4)
			return long2ip(ip2long($ip) & 0xFFFF0000);
		
		if($bytes == 6)
			return long2ip(ip2long($ip) & 0xFF000000);
		
		else
			return $ip;
	
	}

}
