<?php
namespace Concise;



###################################################
################  Userverwaltung  #################
###################################################

// Benutzerverwaltung

class Admin_User extends Admin implements AdminTask
{

	const usernameMaxLen		= 255;

	private $tableUser			= "user";
	private $o_user				= null;
	public $userQuery			= array();
	public $newsL				= 0;
	public $dbFilter			= "";
	public $filter				= "";
	public $filterAbo			= "all";
	public $filterStatus		= "all";
	public $restrict			= "";
	private $userSearch			= "";
	public $dbOrder				= " ORDER BY `group` DESC, `username` ASC";
	public $pageNum				= 0;
	public $maxRows				= 10;
	public $totalRows			= 0;
	public $sortUser			= "nameasc";
	public $formAction 			= "";
	public $userLang			= DEF_ADMIN_LANG; // Sprache für Admin-/Accountbereich
	public $atSkin				= ADMIN_SKIN;
	private $filterGroup		= "<all>";
	public $ownGroups			= array();
	public $ownGroupArray		= array();
	public $ownGroupsSelectable	= array();
	private $activeSessions		= array();
	private $duplicateUser		= false;
	private $duplicateUserEmail	= false;
	private $userGroupsSelectable	= array();
	private $userGroupsControlBar	= array();
	private $delUser			= "";
	private $delUserDB			= "";
	
	public function __construct($DB, $o_lng, $task, $init = false)
	{

		// Admin-Elternklasse aufrufen
		parent::__construct($DB, $o_lng);
		
		parent::$task = $task;
		
		$this->tableUser 		= DB_TABLE_PREFIX . $this->tableUser;

		$this->o_user			= new User($this->DB, $this->o_lng);
		
		$this->formAction 		= ADMIN_HTTP_ROOT . "?task=user";

	}
	
	
	public function getTaskContents($ajax = false)
	{

		// Enthält Headerbox
		$this->adminHeader		=	'{s_text:adminuser}' . PHP_EOL .
									$this->closeTag("#headerBox");
		
		// #adminContent
		$this->adminContent 	=	$this->openAdminContent();

		$this->adminContent 	.=	'<div class="adminArea">' . PHP_EOL;						

		
		$showBackButton = true;
		

		// Ggf. zu große POST-Requests abfangen
		if($checkPostSize	= $this->checkPostRequestTooLarge())
			$this->error	= $this->getNotificationStr(sprintf(ContentsEngine::replaceStaText("{s_error:postrequest}"), $checkPostSize), "error");


		// Restriktion der Benutzergruppen
		$this->dbFilter = "WHERE `username` != ''";
		
		// Falls editor
		if($this->loggedUserGroup == "editor") {
			
			array_splice($this->userGroups, 5, 1); // Admin-Gruppe entfernen
			$this->dbFilter .= " AND `group` != 'admin' AND (`group` != 'editor' OR `username` = '" . $this->DB->escapeString($this->loggedUser) . "')";
		}
		elseif($this->loggedUserGroup == "author") { // Admin- und Editor-Gruppe entfernen
			
			$this->userGroups = array("author");
			$this->dbFilter .= " AND `username` = '" . $this->DB->escapeString($this->loggedUser) . "'";
		}

		// Eigene Benutzergruppen aus Benutzerarray entfernen
		$this->ownGroups	= $GLOBALS['ownUserGroups'];
		
		// Falls nicht Admin, auch aus ControlBar entfernen
		if(!$this->adminLog)
			$this->userGroups	= array_diff($this->userGroups, $this->ownGroups);

		// Usergroups ControlBar
		$this->userGroupsControlBar	= $this->userGroups;

		// Falls editorLog, ControlBar request auslesen
		if($this->editorLog)
			$this->evalUserControlBarRequest();
		
		
		// Falls Admin, eigene Benutzergruppen jetzt entfernen
		if($this->adminLog)
			$this->userGroups	= array_diff($this->userGroups, $this->ownGroups);

		
		// Datenbanksuche nach bereits vorhandenen Benutzern
		$queryCount = $this->DB->query("SELECT COUNT(*)   
											FROM `" . $this->tableUser . "` 
											$this->dbFilter 
											$this->dbOrder
											");

		#var_dump($queryCount);

		// Falls die Suche erfolgreich war Benutzer anzeigen
		if(is_array($queryCount)
		&& $queryCount[0]['COUNT(*)'] > 0) {
				
			$this->totalRows = $queryCount[0]["COUNT(*)"];

			// Pagination
			if (isset($GLOBALS['_GET']['pageNum']))
				$this->pageNum = $GLOBALS['_GET']['pageNum'];
				

			$startRow = $this->pageNum * $this->maxRows;
			$query_limit = " LIMIT " . $startRow . "," . $this->maxRows;
			$queryString = "task=user&sort_user=$this->sortUser&abo=$this->filterAbo&status=$this->filterStatus&limit=$this->maxRows";

			$dataNav = Modules::getPageNav($this->maxRows, $this->totalRows, $startRow, $this->pageNum, $queryString, "", false, parent::getLimitForm($this->limitOptions, $this->maxRows));
								

			// Suche nach Benutzern
			$this->userQuery = $this->DB->query("SELECT *   
												FROM `" . $this->tableUser . "` 
												$this->dbFilter 
												$this->dbOrder
												$query_limit 
												");
						
			#var_dump($this->userQuery);
		}



		// Falls ein neuer Benutzer angelegt werden soll und editorLog
		if(isset($GLOBALS['_POST']['new_user']) && $this->editorLog) {
			
			
			// Restriktion der Benutzergruppen bei Editor (eine Ebene unter der eigenen)
			if($this->loggedUserGroup == "editor") {
				array_splice($this->userGroups, 4, 1);
			}

			$this->userGroupsSelectable	= $this->userGroups;
			$this->ownGroupsSelectable	= $this->loggedUserOwnGroups;
			
			if($this->adminLog)
				$this->ownGroupsSelectable	= $this->ownGroups;

			
			if(isset($GLOBALS['_POST']['new_userG'])) {
			
			
				$newUserN = trim($GLOBALS['_POST']['new_userN']);
				$newUserP1 = trim($GLOBALS['_POST']['new_userP1']);
				$newUserP2 = trim($GLOBALS['_POST']['new_userP2']);
				$newUserM = trim($GLOBALS['_POST']['new_userM']);
				$newUserRN = trim($GLOBALS['_POST']['new_userRN']);
				$newUserA = trim($GLOBALS['_POST']['new_userA']);
				$newUserT = trim($GLOBALS['_POST']['new_userT']);
				$newUserLN = trim($GLOBALS['_POST']['new_userLN']);
				$newUserFN = trim($GLOBALS['_POST']['new_userFN']);
				$newUserS = trim($GLOBALS['_POST']['new_userS']);
				$newUserZ = trim($GLOBALS['_POST']['new_userZ']);
				$newUserC = trim($GLOBALS['_POST']['new_userC']);
				$newUserCn = trim($GLOBALS['_POST']['new_userCn']);
				$newUserPh = trim($GLOBALS['_POST']['new_userPh']);
				$newUserCp = trim($GLOBALS['_POST']['new_userCp']);
				$newUserG = trim($GLOBALS['_POST']['new_userG']);
				
				
				// Überprüfung auf doppelten Benutzernamen/E-Mail
				$this->duplicateUser		= $this->o_user->checkUserExists($newUserN);
				// Überprüfung auf doppelten Benutzernamen/E-Mail
				$this->duplicateUserEmail	= $this->o_user->checkUserExists($newUserM);

				
				if($newUserN == "")
					$errorN = "{s_error:fill}";
				elseif($this->duplicateUser)
					$errorN = "{s_error:userexist}";
				elseif(preg_match("/@/", $newUserN) && $newUserN != $GLOBALS['_POST']['new_userM'])
					$errorN = "{s_error:nomailuser}";
				elseif(!preg_match("/^[a-zA-Z0-9]+$/", $newUserN) && !filter_var($newUserN, FILTER_VALIDATE_EMAIL))
					$errorN = "{s_error:wronguser}";
				elseif(strlen($newUserN) > self::usernameMaxLen)
					$errorN = "{s_error:userlen1}";
				elseif(strlen($newUserN) < 4)
					$errorN = "{s_error:userlen2}";
				elseif(is_numeric($newUserN))
					$errorN = "{s_error:wronguser2}";
			
				
				
				if($newUserP1 == "" && $GLOBALS['_POST']['new_userG'] != "subscriber")
					$errorP1 = "{s_error:fill}";
				elseif(!preg_match("/^[a-zA-Z0-9\$§%&#_-]+$/", $newUserP1))
					$errorP1 = "{s_error:wrongpass}";
				elseif(strlen($newUserP1) > PASSWORD_MAX_LENGTH)
					$errorP1 = sprintf(ContentsEngine::replaceStaText("{s_error:passlen2}"), PASSWORD_MAX_LENGTH);
				elseif(strlen($newUserP1) < PASSWORD_MIN_LENGTH)
					$errorP1 = sprintf(ContentsEngine::replaceStaText("{s_error:passlen1}"), PASSWORD_MIN_LENGTH);
			
				 
				if($newUserP2 == "" && $newUserP1 == "" && $GLOBALS['_POST']['new_userG'] != "subscriber")
					$errorP2 = "{s_error:fill}";
				elseif($newUserP2 == "")
					$errorP2 = "{s_error:userpass1}";
				elseif($newUserP2 != $newUserP1)
					$errorP2 = "{s_error:userpass2}";
			
				
				 
				if($newUserM == "")
					$errorM = "{s_error:fill}";
				elseif($this->duplicateUserEmail)
					$errorM = "{s_error:mailexist}";
				elseif(!filter_var($newUserM, FILTER_VALIDATE_EMAIL))
					$errorM = "{s_error:mail2}";
				elseif(strlen($newUserM) > 254)
					$errorM = "{s_error:mail2}";
			
				
				
				if($newUserRN == "")
					$newUserRN = User::getMailLocalPart($newUserN);				
				 
				if($newUserRN != "" && $this->o_user->checkAuthorExists($newUserA))
					$errorRN = "{s_error:userexist}";
				elseif($newUserRN != "" && !preg_match("/^[\w \.-]+$/u", $newUserRN))
					$errorRN = "{s_error:wronguser}";
				elseif(strlen($newUserRN) > 100)
					$errorRN = "{s_error:userlen1}";
			
				
				 
				if(strlen($newUserA) > 1)
					$errorA = "{s_error:check}";				
				 
				if(strlen($newUserT) > 20)
					$errorT = "{s_error:check}";
				 
				if(strlen($newUserLN) > 100)
					$errorLN = "{s_error:check}";
				 
				if(strlen($newUserFN) > 100)
					$errorFN = "{s_error:check}";
				 
				if(strlen($newUserS) > 100)
					$errorS = "{s_error:check}";
				 
				if($newUserZ != "" && !is_numeric($newUserZ))
					$errorZ = "{s_error:check}";
				elseif(strlen($newUserZ) > 5)
					$errorZ = "{s_error:check}";
				 
				if(strlen($newUserC) > 100)
					$errorC = "{s_error:check}";
				 
				if(strlen($newUserCn) > 100)
					$errorCn = "{s_error:check}";
				 
				if(strlen($newUserPh) > 100)
					$errorPh = "{s_error:check}";
				 
				if(strlen($newUserCp) > 100)
					$errorCp = "{s_error:check}";
				
				if($newUserG == "")
					$errorG = "{s_error:fill}";
				elseif(strlen($newUserG) > 64)
					$errorG = "{s_error:langlenN}";
				elseif(!in_array($newUserG, $this->userGroups))
					$errorG = "{s_error:check}";

				
				// Falls eigene Benutzergruppen ausgewählt waren
				if(isset($GLOBALS['_POST']['new_userOG']) && $GLOBALS['_POST']['new_userOG'][0] != "" && $newUserG != "subscriber") {
			
					$this->ownGroupArray	= $GLOBALS['_POST']['new_userOG'];
					$newUserOG		= implode(",", $this->ownGroupArray);
				}
				else {
					$this->ownGroupArray	= array();
					$newUserOG		= "";
				}

				// Newsletter
				if((isset($GLOBALS['_POST']['newsl']) && $GLOBALS['_POST']['newsl'] == "on") || $newUserG == "subscriber") 
					$this->newsL = 1;
			

				// Falls keine Fehler aufgetaucht sind			
				if(!isset($errorN) && !isset($errorP1) && !isset($errorP2) && !isset($errorG) && !isset($errorM)) {
								
					$userName		= $this->DB->escapeString($newUserN);
					$userSalt		= Security::generatePassword(9);				
					$userPass		= Security::hashPassword($this->DB->escapeString($newUserP1), CC_SALT . $userSalt);
					$userMail		= $this->DB->escapeString($newUserM);
					$userRealName	= $this->DB->escapeString($newUserRN);
					$userGroup		= $this->DB->escapeString($newUserG);
					$userOwnGroups	= $this->DB->escapeString($newUserOG);
					$userGender		= $this->DB->escapeString($newUserA);
					$userTitle		= $this->DB->escapeString($newUserT);
					$userLastName	= $this->DB->escapeString($newUserLN);
					$userFirstName	= $this->DB->escapeString($newUserFN);
					$userStreet		= $this->DB->escapeString($newUserS);
					$userZipCode	= $this->DB->escapeString($newUserZ);
					$userCity		= $this->DB->escapeString($newUserC);
					$userCountry	= $this->DB->escapeString($newUserCn);
					$userPhone		= $this->DB->escapeString($newUserPh);
					$userCompany	= $this->DB->escapeString($newUserCp);
					$this->userLang	= $this->DB->escapeString($this->userLang);
					$this->atSkin	= $this->DB->escapeString($this->atSkin);
					$authCode		= md5(uniqid(time()));

					
					// db-Tabelle sperren
					$lock = $this->DB->query("LOCK TABLES `" . $this->tableUser . "`");
					
				
				
					// Einfügen der neuen Sprachfelder
					$insertSQL = $this->DB->query("INSERT INTO `" . $this->tableUser . "`  
														(`username`, `password`, `salt`, `group`, `own_groups`, `author_name`, `email`, `gender`, `title`, `last_name`, `first_name`, `street`, `zip_code`, `city`, `country`, `phone`, `company`, `lang`, `at_skin`, `newsletter`, `auth_code`) 
														VALUES ('$userName', '$userPass', '$userSalt', '$userGroup', '$userOwnGroups', '$userRealName', '$userMail', '$userGender', '$userTitle', '$userLastName', '$userFirstName', '$userStreet', '$userZipCode', '$userCity', '$userCountry', '$userPhone', '$userCompany', '$this->userLang', '$this->atSkin', $this->newsL, '$authCode')
														");
			
					// db-Sperre aufheben
					$unLock = $this->DB->query("UNLOCK TABLES");
					
					
					if($insertSQL === true) {

						$this->setSessionVar('notice', "{s_notice:newuser}"); // Benachrichtigung in Session speichern
						$this->unsetSessionKey('edit_user');
						
						header("Location: " . ADMIN_HTTP_ROOT . "?task=user");
						exit;
					}
					else
						$error = "{s_error:error}";
			
				}
				
			} // Ende if submit new user
			

			$this->adminContent .=	'<h2 class="toggle cc-section-heading cc-h2">{s_header:newuser}</h2>' . PHP_EOL;
			
			$this->adminContent .=	'<div class="adminBox">' . PHP_EOL;

			if(isset($error))
				$this->adminContent .= '<p class="notice error">' . $error . '</p>';

			$this->adminContent .=	'<form action="'.$this->formAction.'" name="adminfm" method="post">' . PHP_EOL .
									'<ul class="framedItems">' . PHP_EOL;
			
			// User name
			$this->adminContent .=	'<li><label>{s_label:userN}</label>' . PHP_EOL;
			
			if(isset($GLOBALS['_POST']['new_userN']) && isset($errorN))
				$this->adminContent .= '<p class="notice">' . $errorN . '</p>';
			
			$this->adminContent .=	'<input type="text" name="new_userN" id="username" maxlength="' . self::usernameMaxLen . '"';
			
			isset($newUserN) ? $value = htmlspecialchars($newUserN) : $value = "";
								
			$this->adminContent .=	' value="' . $value . '" /></li>' . PHP_EOL;
			
			// Password
			$this->adminContent .=	'<li><label>{s_label:userP1}</label>' . PHP_EOL;
								
			if(isset($GLOBALS['_POST']['new_userP1']) && isset($errorP1))
				$this->adminContent .= '<p class="notice">' . $errorP1 . '</p>';
			
			$this->adminContent .=	'<span id="pw1-messages" class="notice"></span>' . PHP_EOL;
			
			$this->adminContent .=	'<input type="password" name="new_userP1" id="password1" class="password-checker" maxlength="'.PASSWORD_MAX_LENGTH.'" data-minlength="'.PASSWORD_MIN_LENGTH.'" data-tooshort="' . sprintf(ContentsEngine::replaceStaText("{s_error:passlen1}"), PASSWORD_MIN_LENGTH) . '" data-sameasuser="{s_error:pwsameasuser}" data-toolong="' . sprintf(ContentsEngine::replaceStaText("{s_error:passlen2}"), PASSWORD_MAX_LENGTH) . '" data-pwquality="{s_form:pwweak},{s_form:pwnormal},{s_form:pwmedium},{s_form:pwstrong},{s_form:pwverystrong}"';
			
			isset($newUserP1) ? $value = htmlspecialchars($newUserP1) : $value = "";

			$this->adminContent .=	' value="' . $value . '" />' . PHP_EOL;
			$this->adminContent .=	'<span id="pw-checker-box"></span>' . PHP_EOL;
			
			// Password repeat
			$this->adminContent .=	'<label>{s_label:userP2}</label>' . PHP_EOL;
								
			if(isset($GLOBALS['_POST']['new_userP2']) && isset($errorP2))
				$this->adminContent .= '<p class="notice">' . $errorP2 . '</p>';

			$this->adminContent .=	'<span id="pw2-messages"></span>' . PHP_EOL;
			
			$this->adminContent .=	'<input type="password" name="new_userP2" id="password2" maxlength="'.PASSWORD_MAX_LENGTH.'" data-pwnomatch="{s_error:userpass2}"';
			
			isset($newUserP2) ? $value = htmlspecialchars($newUserP2) : $value = "";

			$this->adminContent .=	' value="' . $value . '" />' . PHP_EOL;
			$this->adminContent .=	'</li>' . PHP_EOL;

			// E-Mail
			$this->adminContent .=	'<li><label>{s_label:userM}</label>' . PHP_EOL;
								
			if(isset($GLOBALS['_POST']['new_userM']) && isset($errorM))
				$this->adminContent .= '<p class="notice">' . $errorM . '</p>';
			
			$this->adminContent .=	'<input type="text" name="new_userM" maxlength="254"';
			
			isset($newUserM) ? $value = htmlspecialchars($newUserM) : $value = "";

			$this->adminContent .=	' value="' . $value . '" />' . PHP_EOL;
			
			// Newsl
			$this->adminContent .=	'<br class="clearfloat"><br />' . PHP_EOL . 
									'<label class="markBox"><input name="newsl" id="newsl" type="checkbox"' . (isset($this->newsL) && $this->newsL == 1 ? ' checked="checked"' : '') . (isset($newUserG) && $newUserG == "subscriber" ? 'disabled="true"' : '') . ' /></label>' . PHP_EOL .
									'<label class="inline-label" for="newsl"">{s_label:newsl}</label>' . PHP_EOL .
									'</li>' . "\n";
			
			// Real name / author name
			$this->adminContent .=	'<li><label>{s_label:userRN}</label>' . PHP_EOL;
			
			if(isset($GLOBALS['_POST']['new_userRN']) && isset($errorRN))
				$this->adminContent .= '<p class="notice">' . $errorRN . '</p>';
			
			$this->adminContent .=	'<input type="text" name="new_userRN" maxlength="100"';
			
			isset($newUserRN) ? $value = htmlspecialchars($newUserRN) : $value = "";
								
			$this->adminContent .=	' value="' . $value . '" /></li>' . PHP_EOL;
			
			// User details
			$this->adminContent .=	'<li>' . PHP_EOL .
									'<label class="markBox"><input type="checkbox" name="showUserDetails" id="showUserDetails" class="showUserDetails toggleDetails" data-toggle="userDetailsBox"' . (isset($GLOBALS['_POST']['showUserDetails']) ? ' checked="checked"' : '') . ' /></label>' . PHP_EOL .
									'<label for="showUserDetails" class="showUserDetails inline-label">{s_label:adduserdetails}</label>' . PHP_EOL .
									'<div id="userDetailsBox" class="userDetails detailsDiv"' . (!isset($GLOBALS['_POST']['showUserDetails']) ? ' style="display:none;"' : '') . '>' . PHP_EOL .
									'<div class="leftBox"><label>{s_form:anrede}</label>' . PHP_EOL;
			
			if(isset($GLOBALS['_POST']['new_userA']) && isset($errorA))
				$this->adminContent .= '<p class="notice">' . $errorA . '</p>';
			
			$this->adminContent .=	'<select name="new_userA">' . PHP_EOL .
									'<option value="m"' . (isset($newUserA) && $newUserA == "m" ? ' selected="selected"' : '') . '>{s_form:herr}</option>' .
									'<option value="f"' . (isset($newUserA) && $newUserA == "f" ? ' selected="selected"' : '') . '>{s_form:frau}</option>' .
									'</select></div>' .
									'<div class="leftBox"><label>{s_form:grade}</label>' . PHP_EOL;
			
			if(isset($GLOBALS['_POST']['new_userT']) && isset($errorT))
				$this->adminContent .= '<p class="notice">' . $errorT . '</p>';
			
			$this->adminContent .=	'<select name="new_userT">' . PHP_EOL .
									'<option value="">---</option>' .
									'<option value="Dr."' . (isset($newUserT) && $newUserT == "Dr." ? ' selected="selected"' : '') . '>{s_form:dr}</option>' .
									'<option value="Prof. Dr."' . (isset($newUserT) && $newUserT == "Prof. Dr." ? ' selected="selected"' : '') . '>{s_form:prof}</option>' .
									'<option value="Prof. Dr. Dr."' . (isset($newUserT) && $newUserT == "Prof. Dr. Dr." ? ' selected="selected"' : '') . '>{s_form:profdr}</option>' .
									'</select></div>' .
									'<br class="clearfloat" />' .
									'<label>{s_label:userLN}</label>' . PHP_EOL;
			
			if(isset($GLOBALS['_POST']['new_userLN']) && isset($errorLN))
				$this->adminContent .= '<p class="notice">' . $errorLN . '</p>';
			
			$this->adminContent .=	'<input type="text" name="new_userLN" maxlength="100"';
			
			isset($newUserLN) ? $value = htmlspecialchars($newUserLN) : $value = "";
								
			$this->adminContent .=	' value="' . $value . '" />' . PHP_EOL .
									'<label>{s_label:userFN}</label>' . PHP_EOL;
			
			if(isset($GLOBALS['_POST']['new_userFN']) && isset($errorFN))
				$this->adminContent .= '<p class="notice">' . $errorFN . '</p>';
			
			$this->adminContent .=	'<input type="text" name="new_userFN" maxlength="100"';
			
			isset($newUserFN) ? $value = htmlspecialchars($newUserFN) : $value = "";
								
			$this->adminContent .=	' value="' . $value . '" />' . PHP_EOL .
									'<label>{s_label:userS}</label>' . PHP_EOL;
			
			if(isset($GLOBALS['_POST']['new_userS']) && isset($errorS))
				$this->adminContent .= '<p class="notice">' . $errorS . '</p>';
			
			$this->adminContent .=	'<input type="text" name="new_userS" maxlength="100"';
			
			isset($newUserS) ? $value = htmlspecialchars($newUserS) : $value = "";
								
			$this->adminContent .=	' value="' . $value . '" />' . PHP_EOL .
									'<label>{s_label:userZ}</label>' . PHP_EOL;
			
			if(isset($GLOBALS['_POST']['new_userZ']) && isset($errorZ))
				$this->adminContent .= '<p class="notice">' . $errorZ . '</p>';
			
			$this->adminContent .=	'<input type="text" name="new_userZ" maxlength="5"';
			
			isset($newUserZ) ? $value = htmlspecialchars($newUserZ) : $value = "";
								
			$this->adminContent .=	' value="' . $value . '" />' . PHP_EOL .
									'<label>{s_label:userC}</label>' . PHP_EOL;
			
			if(isset($GLOBALS['_POST']['new_userC']) && isset($errorC))
				$this->adminContent .= '<p class="notice">' . $errorC . '</p>';
			
			$this->adminContent .=	'<input type="text" name="new_userC" maxlength="100"';
			
			isset($newUserC) ? $value = htmlspecialchars($newUserC) : $value = "";
								
			$this->adminContent .=	' value="' . $value . '" />' . PHP_EOL .
									'<label>{s_label:userCn}</label>' . PHP_EOL;
			
			if(isset($GLOBALS['_POST']['new_userCn']) && isset($errorCn))
				$this->adminContent .= '<p class="notice">' . $errorCn . '</p>';
			
			$this->adminContent .=	'<input type="text" name="new_userCn" maxlength="100"';
			
			isset($newUserCn) ? $value = htmlspecialchars($newUserCn) : $value = "";
								
			$this->adminContent .=	' value="' . $value . '" />' . PHP_EOL .
									'<label>{s_form:phone}</label>' . PHP_EOL;
			
			if(isset($GLOBALS['_POST']['new_userPh']) && isset($errorPh))
				$this->adminContent .= '<p class="notice">' . $errorPh . '</p>';
			
			$this->adminContent .=	'<input type="text" name="new_userPh" maxlength="100"';
			
			isset($newUserPh) ? $value = htmlspecialchars($newUserPh) : $value = "";
								
			$this->adminContent .=	' value="' . $value . '" />' . PHP_EOL .
									'<label>{s_label:userCp}</label>' . PHP_EOL;
			
			if(isset($GLOBALS['_POST']['new_userCp']) && isset($errorCp))
				$this->adminContent .= '<p class="notice">' . $errorCp . '</p>';
			
			$this->adminContent .=	'<input type="text" name="new_userCp" maxlength="100"';
			
			isset($newUserCp) ? $value = htmlspecialchars($newUserCp) : $value = "";
								
			$this->adminContent .=	' value="' . $value . '" />' . PHP_EOL .
									'</div></li>' . PHP_EOL;
									
			// User groups						
			$this->adminContent .=	'<li><label>{s_label:userG}</label>' . PHP_EOL;
								
			if(isset($GLOBALS['_POST']['new_userG']) && isset($errorG))
				$this->adminContent .= '<p class="notice">' . $errorG . '</p>';
			
			// Default-Benutzergruppen
			$this->adminContent .=	'<select name="new_userG" id="selGroup">' . PHP_EOL;
			
			// Benutzergruppen auslesen
			foreach($this->userGroupsSelectable as $group) {
				if ($group != "public")
					$this->adminContent .='<option value="' . $group . '"' . (isset($newUserG) && $newUserG == $group ? ' selected="selected"' : (!isset($newUserG) && $group == "guest" ? ' selected="selected"' : '')) . '>{s_option:group' . $group . '}</option>' . PHP_EOL; // Benutzergruppe
			}
			$this->adminContent .=	'</select>' . PHP_EOL;
			
			// Eigene Benutzergruppen
			if($this->editorLog && count($this->ownGroupsSelectable) > 0) {
				
				$this->adminContent .=	'<br class="clearfloat" /><div' . (isset($newUserG) && $newUserG == "subscriber" ? ' style="display:none;"' : '') . '>' . PHP_EOL .
										'<label>{s_label:setusergroup}</label>' . PHP_EOL .
										'<select name="new_userOG[]" multiple="multiple" size="' . (count($this->ownGroupsSelectable) +1) . '" id="selOwnGroups" class="selgroup">' . PHP_EOL .
										'<option value="">{s_option:choose}</option>' . PHP_EOL;
				
				foreach($this->ownGroupsSelectable as $ownGroup) {
					$this->adminContent .=	'<option value="' . $ownGroup . '"' . (in_array($ownGroup, $this->ownGroupArray) ? ' selected="selected"' : '') . '>' . $ownGroup . '</option>' . PHP_EOL; // Benutzergruppe
				}
				$this->adminContent .=	'</select></div>' . PHP_EOL;
			}
			
			$this->adminContent .=	'<br class="clearfloat" /></li>' . PHP_EOL;
			
			$this->adminContent .=	'<li class="submit change">' . "\n";
			
			// Button submit (new)
			$btnDefs	= array(	"type"		=> "submit",
									"name"		=> "new_user",
									"class"		=> "change",
									"value"		=> "{s_button:adduser}",
									"icon"		=> "ok"
								);
			
			$this->adminContent .=	parent::getButton($btnDefs);
			
			$this->adminContent .=	'<input name="new_user" type="hidden" value="{s_button:adduser}" />' . PHP_EOL . 
									parent::getTokenInput();
			
			$this->adminContent .=	'</li>' . PHP_EOL .
									'</ul>' . PHP_EOL .
									'</form>' . PHP_EOL;

			// Password checker script
			$this->adminContent .=	'<script src="' . SYSTEM_HTTP_ROOT . '/access/js/password-checker.min.js"></script>' . PHP_EOL;
			
			$this->adminContent .=	'</div>' . PHP_EOL;
		
		}


		// Falls ein vorhandener Benutzer bearbeitet werden soll
		elseif(isset($GLOBALS['_POST']['edit_user'])) {
			
			$this->adminContent .=	'<h2 class="toggle cc-section-heading cc-h2">{s_header:edituser}</h2>' . PHP_EOL;
			
			$this->adminContent .=	'<div class="adminBox">' . PHP_EOL;
			
			
			$editUser = $GLOBALS['_POST']['edit_user'];

			
			// Userdaten einlesen, falls vorhanden
			$editUserQuery = $this->DB->query("SELECT * 
												FROM `" . $this->tableUser . "` 
												$this->dbFilter 
												AND `username` = '$editUser'
												");
			
			#var_dump($editUserQuery);
			if(count($editUserQuery) > 0) {
					
				$editUserID		= str_pad($editUserQuery[0]['userid'], 9, '0', STR_PAD_LEFT);
				$editUserN		= $editUserQuery[0]['username'];
				$editUserNold	= $editUserN;
				$editUserPold	= $editUserQuery[0]['password'];
				$editUserSalt	= $editUserQuery[0]['salt'];
				$editUserP1		= "";
				$editUserP2		= "";
				$editUserM		= $editUserQuery[0]['email'];
				$editUserRN		= $editUserQuery[0]['author_name'];
				$editUserA		= $editUserQuery[0]['gender'];
				$editUserT		= $editUserQuery[0]['title'];
				$editUserLN		= $editUserQuery[0]['last_name'];
				$editUserFN		= $editUserQuery[0]['first_name'];
				$editUserS		= $editUserQuery[0]['street'];
				$editUserZ		= $editUserQuery[0]['zip_code'];
				$editUserC		= $editUserQuery[0]['city'];
				$editUserCn		= $editUserQuery[0]['country'];
				$editUserPh		= $editUserQuery[0]['phone'];
				$editUserCp		= $editUserQuery[0]['company'];
				$editUserG		= $editUserQuery[0]['group'];
				$editUserOG		= $editUserQuery[0]['own_groups'];
				$editUserL		= $editUserQuery[0]['lang'];
				$this->atSkin	= $editUserQuery[0]['at_skin'];
				$this->newsL	= $editUserQuery[0]['newsletter'];
				
				$this->ownGroupArray	= array_filter(explode(",", $editUserOG));
				
				$this->userGroupsSelectable	= $this->userGroups;
				$this->ownGroupsSelectable	= $this->ownGroupArray;
				
				if($this->adminLog)
					$this->ownGroupsSelectable	= $this->ownGroups;
			
				if($this->loggedUserGroup == "editor"
				&& $editUserID === $this->loggedUserID
				) {
					$this->userGroupsSelectable	= array("editor");
					$this->ownGroupsSelectable	= $this->loggedUserOwnGroups;
				}
				
			
				// Falls das Formular zum Ändern von Benutzerdaten abgeschickt wurde
				if(isset($GLOBALS['_POST']['edit_userG'])) {
				
				
					$editUserN		= trim($GLOBALS['_POST']['edit_userN']);
					$editUserP1		= trim($GLOBALS['_POST']['edit_userP1']);
					$editUserP2		= trim($GLOBALS['_POST']['edit_userP2']);
					$editUserM		= trim($GLOBALS['_POST']['edit_userM']);
					$editUserRN		= trim($GLOBALS['_POST']['edit_userRN']);
					$editUserA		= trim($GLOBALS['_POST']['edit_userA']);
					$editUserT		= trim($GLOBALS['_POST']['edit_userT']);
					$editUserLN		= trim($GLOBALS['_POST']['edit_userLN']);
					$editUserFN		= trim($GLOBALS['_POST']['edit_userFN']);
					$editUserS		= trim($GLOBALS['_POST']['edit_userS']);
					$editUserZ		= trim($GLOBALS['_POST']['edit_userZ']);
					$editUserC		= trim($GLOBALS['_POST']['edit_userC']);
					$editUserCn		= trim($GLOBALS['_POST']['edit_userCn']);
					$editUserCp		= trim($GLOBALS['_POST']['edit_userCp']);
					$editUserG		= trim($GLOBALS['_POST']['edit_userG']);
					
					
					// Überprüfung auf doppelten Benutzernamen/E-Mail
					$this->duplicateUser		= $this->o_user->checkUserExists($editUserN, $editUserID);
					// Überprüfung auf doppelten Benutzernamen/E-Mail
					$this->duplicateUserEmail	= $this->o_user->checkUserExists($editUserM, $editUserID);
					
					
					
					if($editUserN == "")
						$errorN = "{s_error:fill}";
					elseif($this->duplicateUser)
						$errorN = "{s_error:userexist}";
					elseif(preg_match("/@/", $editUserN) && $editUserN != $GLOBALS['_POST']['edit_userM'])
						$errorN = "{s_error:nomailuser}";
					elseif(!preg_match("/^[a-zA-Z0-9]+$/", $editUserN) && !filter_var($editUserN, FILTER_VALIDATE_EMAIL))
						$errorN = "{s_error:wronguser}";
					elseif(strlen($editUserN) > self::usernameMaxLen)
						$errorN = "{s_error:userlen1}";
					elseif(strlen($editUserN) < 4)
						$errorN = "{s_error:userlen2}";
				
					
					 
					if($editUserP1 != "") {
						
						if(!preg_match("/^[a-zA-Z0-9\$§%&#_-]+$/", $editUserP1))
							$errorP1 = "{s_error:wrongpass}";
						elseif(strlen($editUserP1) > PASSWORD_MAX_LENGTH)
							$errorP1 = sprintf(ContentsEngine::replaceStaText("{s_error:passlen2}"), PASSWORD_MAX_LENGTH);
						elseif(strlen($editUserP1) < PASSWORD_MIN_LENGTH)
							$errorP1 = sprintf(ContentsEngine::replaceStaText("{s_error:passlen1}"), PASSWORD_MIN_LENGTH);
					}
					else {
						if($editUserP1 == "" && $editUserPold == "" && $GLOBALS['_POST']['edit_userG'] != "subscriber")
							$errorP1 = "{s_error:fill}";
					}
				
					
					 
					if($editUserP2 == "" && $editUserP1 != "")
						$errorP2 = "{s_error:userpass1}";
					elseif($editUserP2 != $editUserP1)
						$errorP2 = "{s_error:userpass2}";
				
					
					 
					if($editUserM == "")
						$errorM = "{s_error:fill}";
					elseif($this->duplicateUserEmail)
						$errorM = "{s_error:mailexist}";
					elseif(!filter_var($editUserM, FILTER_VALIDATE_EMAIL))
						$errorM = "{s_error:mail2}";
					elseif(strlen($editUserM) > 254)
						$errorM = "{s_error:mail2}";
					
					
					if($editUserRN == "")
						$editUserRN = User::getMailLocalPart($editUserN);
					 
					if($editUserRN != "" && $this->o_user->checkAuthorExists($editUserRN, $editUserID))
						$errorRN = "{s_error:userexist}";
					elseif($editUserRN != "" && !preg_match("/^[\w \.-]+$/u", $editUserRN))
						$errorRN = "{s_error:wronguser}";
					elseif(strlen($editUserRN) > 100)
						$errorRN = "{s_error:userlen1}";
					
					 
					if(strlen($editUserA) > 1)
						$errorA = "{s_error:check}";
					
					 
					if(strlen($editUserT) > 20)
						$errorT = "{s_error:check}";
					
					 
					if(strlen($editUserLN) > 100)
						$errorLN = "{s_error:check}";
					
					 
					if(strlen($editUserFN) > 100)
						$errorFN = "{s_error:check}";
					
					 
					if(strlen($editUserS) > 100)
						$errorS = "{s_error:check}";
					
					 
					if($editUserZ != "" && !is_numeric($editUserZ))
						$errorZ = "{s_error:wronguser}";
					elseif(strlen($editUserZ) > 100)
						$errorZ = "{s_error:check}";
					
					 
					if(strlen($editUserC) > 100)
						$errorC = "{s_error:check}";
					
					 
					if(strlen($editUserCn) > 100)
						$errorCn = "{s_error:check}";
					
					 
					if(strlen($editUserCp) > 100)
						$errorCp = "{s_error:check}";
				

					// Falls eine Bilddatei für den Upload ausgewählt war
					if(isset($GLOBALS['_FILES']['edit_userFile']) && $GLOBALS['_FILES']['edit_userFile']['name'] != "") {
					
						$editF			= $GLOBALS['_FILES']['edit_userFile']['name'];
						$editFTmp		= $GLOBALS['_FILES']['edit_userFile']['tmp_name'];
									
						$upload_file	= $editF;
						$upload_tmpfile	= $editFTmp;
						$fileExt		= strtolower(Files::getFileExt($upload_file)); // Bestimmen der Dateinamenerweiterung
						
						$fixName		= "avatar_" . $editUserID . '.' . $fileExt;
						$folder			= CC_USER_FOLDER . '/img';
					
						User::deleteUserImage($editUserID); // Ggf. vorhandene Datei löschen
						$upload = Files::uploadFile($upload_file, $upload_tmpfile, $folder, "image", 180, 180, true, $fixName, "", false); // File-Upload
							
						if($upload !== true) {
							$errorUserFile = $upload;
						}
					}
				
					
					// Falls der geloggte Benutzer kein Author ist
					if($this->loggedUserGroup != "author") {
											 
						if($editUserG == "")
							$errorG = "{s_error:fill}";
						elseif(strlen($editUserG) > 64)
							$errorG = "{s_error:langlenN}";
						elseif(!in_array($editUserG, $this->userGroupsSelectable))
							$errorG = "{s_error:check}";
						
							
						// Falls eigene Benutzergruppen ausgewählt waren
						if(isset($GLOBALS['_POST']['edit_userOG']) && $GLOBALS['_POST']['edit_userOG'][0] != "" && $editUserG != "subscriber") {
					
							$this->ownGroupArray	= $GLOBALS['_POST']['edit_userOG'];
							$editUserOG		= implode(",", $this->ownGroupArray);
						}
						elseif((isset($GLOBALS['_POST']['edit_userOG']) && $GLOBALS['_POST']['edit_userOG'][0] == "") || $editUserG == "subscriber") {
							$this->ownGroupArray	= array();
							$editUserOG		= "";
						}
					}
					

					// Backend-Sprache
					if(isset($GLOBALS['_POST']['edit_userL']) && array_key_exists($GLOBALS['_POST']['edit_userL'], $GLOBALS['adminLangs']))
						$editUserL = $GLOBALS['_POST']['edit_userL'];
					elseif(!array_key_exists($editUserL, $GLOBALS['adminLangs']))
						$editUserL = $this->userLang;
					

					// Backend-Skin
					if(!empty($GLOBALS['_POST']['at_skin']))
						$this->atSkin = $GLOBALS['_POST']['at_skin'];
					else
						$this->atSkin = "";
					
					
					// Newsletter
					if((isset($GLOBALS['_POST']['newsl']) && $GLOBALS['_POST']['newsl'] == "on") || $editUserG == "subscriber")
						$this->newsL = 1;
					else
						$this->newsL = 0;
					
					
					// Falls keine Fehler aufgetaucht sind			
					if(!isset($errorN) && !isset($errorP1) && !isset($errorP2) && !isset($errorG) && !isset($errorM) && !isset($errorUserFile)) {
									
						$editUser = $this->DB->escapeString($editUser);
						$userName = $this->DB->escapeString($editUserN);
						
						if($editUserP1 == "") {
							$userPass	= $this->DB->escapeString($editUserPold);
							$userSalt	= $this->DB->escapeString($editUserSalt);
						}
						else {
							$userSalt	= Security::generatePassword(9);
							$userPass	= Security::hashPassword($this->DB->escapeString($editUserP1), CC_SALT . $userSalt);
						}
						
						$userGroup		= $this->DB->escapeString($editUserG);
						$userOwnGroups	= $this->DB->escapeString($editUserOG);
						$userRealName 	= $this->DB->escapeString($editUserRN);
						$userGender		= $this->DB->escapeString($editUserA);
						$userTitle		= $this->DB->escapeString($editUserT);
						$userLastName	= $this->DB->escapeString($editUserLN);
						$userFirstName	= $this->DB->escapeString($editUserFN);
						$userStreet		= $this->DB->escapeString($editUserS);
						$userZipCode	= $this->DB->escapeString($editUserZ);
						$userCity		= $this->DB->escapeString($editUserC);
						$userCountry	= $this->DB->escapeString($editUserCn);
						$userPhone		= $this->DB->escapeString($editUserPh);
						$userCompany	= $this->DB->escapeString($editUserCp);
						$userMail 		= $this->DB->escapeString($editUserM);
						$this->userLang	= $this->DB->escapeString($editUserL);
						$this->atSkin	= $this->DB->escapeString($this->atSkin);
			
						// db-Tabelle sperren
						$lock = $this->DB->query("LOCK TABLES `" . $this->tableUser . "`");
						
					
					
						// db-Update
						$updateSQL = $this->DB->query("UPDATE `" . $this->tableUser . "`  
															SET `username` = '$userName',
																`password` = '$userPass',
																`salt` = '$userSalt',
																`group` = '$userGroup',
																`own_groups` = '$userOwnGroups',
																`author_name` = '$userRealName',
																`email` = '$userMail',
																`gender` = '$userGender',
																`title` = '$userTitle',
																`last_name` = '$userLastName',
																`first_name` = '$userFirstName',
																`street` = '$userStreet',
																`zip_code` = '$userZipCode',
																`city` = '$userCity',
																`country` = '$userCountry',
																`phone` = '$userPhone',
																`company` = '$userCompany',
																`lang`	= '$this->userLang',
																`at_skin`	= '$this->atSkin',
																`newsletter` = $this->newsL
															WHERE `username` = '$editUser'
															");
						
						#var_dump($updateSQL);
						
						// db-Sperre aufheben
						$unLock = $this->DB->query("UNLOCK TABLES");
						
						
						if($updateSQL === true) {
			
							$this->setSessionVar('notice', "{s_notice:edituser}"); // Benachrichtigung in Session speichern
							$this->unsetSessionKey('edit_user');
							
							// Falls der editierte Benutzer, selbst der eingeloggte Benutzer ist, Session aktualisieren und Benutzername und Sprache für den Adminbereich direkt übernehmen
							if($this->loggedUser == $editUserNold) {
								$this->setSessionVar('username', $editUserN); // (neuen) Benutzernamen in Session speichern
								$this->setSessionVar('author_name', $editUserRN); // (neuen) Namen in Session speichern
								$this->setSessionVar('admin_lang', $editUserL); // Sprache in Session speichern
								$this->setSessionVar('at_skin', $this->atSkin); // Skin in Session speichern
							}

							header("Location: " . ADMIN_HTTP_ROOT . "?task=user");
							exit;
						}
						else
							$error = "{s_error:error}";
				
					}
					
				} // Ende if submit edit user

				
				// Ggf. Fehlermeldung
				if(isset($error))
					$this->adminContent .= '<p class="notice error">' . $error . '</p>';
			
				$this->adminContent .=	'<form action="'.$this->formAction.'" name="adminfm" method="post" enctype="multipart/form-data">' . PHP_EOL .
										'<ul class="framedItems">' . PHP_EOL;
				
				// User name
				$this->adminContent .=	'<li><label>{s_label:userN}</label>' . PHP_EOL;
				
				if(isset($GLOBALS['_POST']['edit_userN']) && isset($errorN))
					$this->adminContent .= '<p class="notice">' . $errorN . '</p>';
				
				$this->adminContent .=	'<input type="text" name="edit_userN" id="username" maxlength="' . self::usernameMaxLen . '"';
				
				isset($editUserN) ? $value = htmlspecialchars($editUserN) : $value = "";
									
				$this->adminContent .=	' value="' . $value . '" /></li>' . PHP_EOL;
				
				// Password
				$this->adminContent .=	'<li><label>{s_label:userPold}</label>' . PHP_EOL;
									
				$this->adminContent .=	'<input type="password" name="edit_userPold" value="'.str_pad("0",PASSWORD_MAX_LENGTH).'" readonly="readonly" class="readonly" maxlength="'.PASSWORD_MAX_LENGTH.'"';
				
				isset($editUserP1) ? $value = htmlspecialchars($editUserP1) : $value = "";
			
				$this->adminContent .=	' value="' . $value . '" />' . PHP_EOL;
				
				// Password new
				$this->adminContent .=	'<label>{s_label:userP1new}</label>' . PHP_EOL;
									
				if(isset($GLOBALS['_POST']['edit_userP1']) && isset($errorP1))
					$this->adminContent .= '<p class="notice">' . $errorP1 . '</p>';
				
				$this->adminContent .=	'<span id="pw1-messages" class="notice"></span>' . PHP_EOL;

				$this->adminContent .=	'<input type="password" name="edit_userP1" id="password1" class="password-checker" maxlength="'.PASSWORD_MAX_LENGTH.'" data-minlength="'.PASSWORD_MIN_LENGTH.'" data-tooshort="' . sprintf(ContentsEngine::replaceStaText("{s_error:passlen1}"), PASSWORD_MIN_LENGTH) . '" data-sameasuser="{s_error:pwsameasuser}" data-toolong="' . sprintf(ContentsEngine::replaceStaText("{s_error:passlen2}"), PASSWORD_MAX_LENGTH) . '" data-pwquality="{s_form:pwweak},{s_form:pwnormal},{s_form:pwmedium},{s_form:pwstrong},{s_form:pwverystrong}"';
				
				isset($editUserP1) ? $value = htmlspecialchars($editUserP1) : $value = "";
			
				$this->adminContent .=	' value="' . $value . '" />' . PHP_EOL;
				$this->adminContent .=	'<span id="pw-checker-box"></span>' . PHP_EOL;
				
				// Password new repeat
				$this->adminContent .=	'<label>{s_label:userP2new}</label>' . PHP_EOL;
									
				if(isset($GLOBALS['_POST']['edit_userP2']) && isset($errorP2))
					$this->adminContent .= '<p class="notice">' . $errorP2 . '</p>';
				
				$this->adminContent .=	'<span id="pw2-messages"></span>' . PHP_EOL;
				
				$this->adminContent .=	'<input type="password" name="edit_userP2" id="password2" maxlength="'.PASSWORD_MAX_LENGTH.'" data-pwnomatch="{s_error:userpass2}"';
				
				isset($editUserP2) ? $value = htmlspecialchars($editUserP2) : $value = "";
			
				$this->adminContent .=	' value="' . $value . '" />' . PHP_EOL;
				$this->adminContent .=	'</li>' . PHP_EOL;
				
				// E-Mail
				$this->adminContent .=	'<li><label>{s_label:userM}</label>' . PHP_EOL;
									
				if(isset($GLOBALS['_POST']['edit_userM']) && isset($errorM))
					$this->adminContent .= '<p class="notice">' . $errorM . '</p>';
				
				$this->adminContent .=	'<input type="text" name="edit_userM" maxlength="254"';
				
				isset($editUserM) ? $value = htmlspecialchars($editUserM) : $value = "";
			
				$this->adminContent .=	' value="' . $value . '" />' . PHP_EOL;
				
				// Newsl
				$this->adminContent .=	'<br class="clearfloat"><br />' . PHP_EOL . 
										'<label class="markBox"><input name="newsl" id="newsl" type="checkbox"' . (isset($this->newsL) && $this->newsL == 1 ? ' checked="checked"' : '') . (isset($editUserG) && $editUserG == "subscriber" ? 'disabled="true"' : '') . ' /></label>' . "\n" .
										'<label class="inline-label" for="newsl">{s_label:newsl}</label>' . "\n" .
										'</li>' . "\n";
				
				// User real name / author name
				$this->adminContent .=	'<li>' . PHP_EOL .
										'<div class="fullBox">' . PHP_EOL .
										'<label>{s_label:userRN}</label>' . PHP_EOL;
				
				if(isset($GLOBALS['_POST']['edit_userRN']) && isset($errorRN))
					$this->adminContent .= '<p class="notice">' . $errorRN . '</p>';
				
				$this->adminContent .=	'<input type="text" name="edit_userRN" maxlength="100"';
				
				isset($editUserRN) ? $value = htmlspecialchars($editUserRN) : $value = "";
									
				$this->adminContent .=	' value="' . $value . '" /></div>' . PHP_EOL;				
				
				// Benutzerbild, falls vorhanden, sonst empty_avatar.png
				$userFileSrc	= User::getUserImageSrc($editUserID, true);
				
				$this->adminContent .=	'<div class="leftBox">' . PHP_EOL;
				
				$this->adminContent .=	'<br /><label>{s_label:userimage}</label>' . PHP_EOL;
				
				if(isset($GLOBALS['_FILES']['edit_userFile']) && isset($errorUserFile))
					$this->adminContent .= '<p class="notice">' . $errorUserFile . '</p>';
				
				$this->adminContent .=	'<input type="file" name="edit_userFile" /><br class="clearfloat" /><br />';
				
				$this->adminContent .=	'</div>' . PHP_EOL;
				$this->adminContent .=	'<div class="rightBox">' . PHP_EOL;
				
				$this->adminContent .=	'<div class="listObject">' . PHP_EOL .
										'<div class="previewBox"><img class="userImage preview" alt="user-file" title="{s_label:userimage}" src="' . $userFileSrc[0] . '" data-img-src="' . $userFileSrc[0] . '" />' . PHP_EOL;
										
				if($userFileSrc[1]) {
	
					// Button delete
					$btnDefs	= array(	"type"		=> "button",
											"class"		=> 'deleteUserImage inline-icon button-icon-only',
											"text"		=> "",
											"title"		=> '{s_title:deluserimage}',
											"attr"		=> 'data-url="' . SYSTEM_HTTP_ROOT . '/access/editModules.php?page=admin&action=deluserimage&userid=' . $editUserID . '"',
											"icon"		=> "delete"
										);
					
					$this->adminContent .=	parent::getButton($btnDefs);
					
				}
				
				$this->adminContent .=	'</div>' . PHP_EOL . 
										'</div>' . PHP_EOL . 
										'</div>' . PHP_EOL;
				
				$this->adminContent .=	'<br class="clearfloat" />' . PHP_EOL;
				$this->adminContent .=	'</li>' . PHP_EOL;
				
				
				// Benutzerdetails
				$this->adminContent .=	'<li>' . PHP_EOL .
										'<label class="markBox"><input type="checkbox" name="showUserDetails" id="showUserDetails" class="showUserDetails toggleDetails" data-toggle="userDetailsBox"' . (isset($GLOBALS['_POST']['showUserDetails']) ? ' checked="checked"' : '') . ' /></label>' . PHP_EOL .
										'<label for="showUserDetails" class="showUserDetails inline-label">{s_label:moduserdetails}</label>' . PHP_EOL .
										'<div id="userDetailsBox" class="userDetails detailsDiv"' . (!isset($GLOBALS['_POST']['showUserDetails']) ? ' style="display:none;"' : '') . '>' . PHP_EOL;
				
				
				$this->adminContent .=	'<div class="leftBox"><label>{s_form:anrede}</label>' . PHP_EOL;
				
				if(isset($GLOBALS['_POST']['edit_userA']) && isset($errorA))
					$this->adminContent .= '<p class="notice">' . $errorA . '</p>';
				
				$this->adminContent .=	'<select name="edit_userA">' . PHP_EOL .
										'<option value="m"' . (isset($editUserA) && $editUserA == "m" ? ' selected="selected"' : '') . '>{s_form:herr}</option>' .
										'<option value="f"' . (isset($editUserA) && $editUserA == "f" ? ' selected="selected"' : '') . '>{s_form:frau}</option>' .
										'</select></div>' .
										'<div class="leftBox"><label>{s_form:grade}</label>' . PHP_EOL;
				
				if(isset($GLOBALS['_POST']['edit_userT']) && isset($errorT))
					$this->adminContent .= '<p class="notice">' . $errorT . '</p>';
				
				$this->adminContent .=	'<select name="edit_userT">' . PHP_EOL .
										'<option value="">---</option>' .
										'<option value="Dr."' . (isset($editUserT) && $editUserT == "Dr." ? ' selected="selected"' : '') . '>{s_form:dr}</option>' .
										'<option value="Prof. Dr."' . (isset($editUserT) && $editUserT == "Prof. Dr." ? ' selected="selected"' : '') . '>{s_form:prof}</option>' .
										'<option value="Prof. Dr. Dr."' . (isset($editUserT) && $editUserT == "Prof. Dr. Dr." ? ' selected="selected"' : '') . '>{s_form:profdr}</option>' .
										'</select></div>' .
										'<br class="clearfloat" />' .
										'<label>{s_label:userLN}</label>' . PHP_EOL;
				
				if(isset($GLOBALS['_POST']['edit_userLN']) && isset($errorLN))
					$this->adminContent .= '<p class="notice">' . $errorLN . '</p>';
				
				$this->adminContent .=	'<input type="text" name="edit_userLN" maxlength="100"';
				
				isset($editUserLN) ? $value = htmlspecialchars($editUserLN) : $value = "";
									
				$this->adminContent .=	' value="' . $value . '" />' . PHP_EOL;
				
				$this->adminContent .=	'<label>{s_label:userFN}</label>' . PHP_EOL;
				
				if(isset($GLOBALS['_POST']['edit_userFN']) && isset($errorFN))
					$this->adminContent .= '<p class="notice">' . $errorFN . '</p>';
				
				$this->adminContent .=	'<input type="text" name="edit_userFN" maxlength="100"';
				
				isset($editUserFN) ? $value = htmlspecialchars($editUserFN) : $value = "";
									
				$this->adminContent .=	' value="' . $value . '" />' . PHP_EOL .
										'<label>{s_label:userS}</label>' . PHP_EOL;
				
				if(isset($GLOBALS['_POST']['edit_userS']) && isset($errorS))
					$this->adminContent .= '<p class="notice">' . $errorS . '</p>';
				
				$this->adminContent .=	'<input type="text" name="edit_userS" maxlength="100"';
				
				isset($editUserS) ? $value = htmlspecialchars($editUserS) : $value = "";
									
				$this->adminContent .=	' value="' . $value . '" />' . PHP_EOL .
										'<label>{s_label:userZ}</label>' . PHP_EOL;
				
				if(isset($GLOBALS['_POST']['edit_userZ']) && isset($errorZ))
					$this->adminContent .= '<p class="notice">' . $errorZ . '</p>';
				
				$this->adminContent .=	'<input type="text" name="edit_userZ" maxlength="5"';
				
				isset($editUserZ) ? $value = htmlspecialchars($editUserZ) : $value = "";
									
				$this->adminContent .=	' value="' . $value . '" />' . PHP_EOL .
										'<label>{s_label:userC}</label>' . PHP_EOL;
				
				if(isset($GLOBALS['_POST']['edit_userC']) && isset($errorC))
					$this->adminContent .= '<p class="notice">' . $errorC . '</p>';
				
				$this->adminContent .=	'<input type="text" name="edit_userC" maxlength="100"';
				
				isset($editUserC) ? $value = htmlspecialchars($editUserC) : $value = "";
									
				$this->adminContent .=	' value="' . $value . '" />' . PHP_EOL .
										'<label>{s_label:userCn}</label>' . PHP_EOL;
				
				if(isset($GLOBALS['_POST']['edit_userCn']) && isset($errorCn))
					$this->adminContent .= '<p class="notice">' . $errorCn . '</p>';
				
				$this->adminContent .=	'<input type="text" name="edit_userCn" maxlength="100"';
				
				isset($editUserCn) ? $value = htmlspecialchars($editUserCn) : $value = "";
									
				$this->adminContent .=	' value="' . $value . '" />' . PHP_EOL .
										'<label>{s_form:phone}</label>' . PHP_EOL;
				
				if(isset($GLOBALS['_POST']['edit_userPh']) && isset($errorPh))
					$this->adminContent .= '<p class="notice">' . $errorPh . '</p>';
				
				$this->adminContent .=	'<input type="text" name="edit_userPh" maxlength="100"';
				
				isset($editUserPh) ? $value = htmlspecialchars($editUserPh) : $value = "";
									
				$this->adminContent .=	' value="' . $value . '" />' . PHP_EOL .
										'<label>{s_label:userCp}</label>' . PHP_EOL;
				
				if(isset($GLOBALS['_POST']['edit_userCp']) && isset($errorCp))
					$this->adminContent .= '<p class="notice">' . $errorCp . '</p>';
				
				$this->adminContent .=	'<input type="text" name="edit_userCp" maxlength="100"';
				
				isset($editUserCp) ? $value = htmlspecialchars($editUserCp) : $value = "";
									
				$this->adminContent .=	' value="' . $value . '" />' . PHP_EOL;
				
				$this->adminContent .=	'</div></li>' . PHP_EOL;
				// Ende Benutzerdetails
				
				
				// Benutzergruppe(n)
				$this->adminContent .=	'<li><label>{s_label:userG}</label>' . PHP_EOL;
									
				if(isset($GLOBALS['_POST']['edit_userG']) && isset($errorG))
					$this->adminContent .= '<p class="notice">' . $errorG . '</p>';
				
				// Default-Benutzergruppen
				$this->adminContent .=	'<select name="edit_userG" id="selGroup">' . PHP_EOL;
				
				// Benutzergruppen auslesen
				foreach($this->userGroupsSelectable as $group) {
					if ($group != "public")
						$this->adminContent .='<option value="' . $group . '"' . (isset($editUserG) && $editUserG == $group ? ' selected="selected"' : '') . '>' . (isset(parent::$staText['option']['group' . $group]) ? '{s_option:group' . $group . '}' : $group) . '</option>' . PHP_EOL; // Benutzergruppe
				}
			
				$this->adminContent .=	'</select>' . PHP_EOL;
				
				// Eigene Benutzergruppen
				if($this->editorLog && count($this->ownGroupsSelectable) > 0) {
					
					$this->adminContent .=	'<br class="clearfloat" /><div' . ($editUserG == "subscriber" ? ' style="display:none;"' : '') . '>' . PHP_EOL .
										'<label>{s_label:setusergroup}</label>' . PHP_EOL .
										'<select name="edit_userOG[]" multiple="multiple" size="' . (count($this->ownGroupsSelectable) +1) . '" id="selOwnGroups" class="selgroup">' . PHP_EOL .
										'<option value="">{s_option:choose}</option>' . PHP_EOL;
					
					foreach($this->ownGroupsSelectable as $ownGroup) {
						$this->adminContent .='<option value="' . $ownGroup . '"' . (in_array($ownGroup, $this->ownGroupArray) ? ' selected="selected"' : '') . '>' . $ownGroup . '</option>' . PHP_EOL; // Benutzergruppe
					}
					$this->adminContent .=	'</select></div>' . PHP_EOL;
				}
				
				$this->adminContent .=	'<br class="clearfloat" /></li>' . PHP_EOL;
				
				// Sprache im Backend
				$this->adminContent .=	'<li><label>{s_label:userL}</label>' . PHP_EOL;
				
				if(isset($GLOBALS['_POST']['edit_userL']) && isset($errorL))
					$this->adminContent .= '<p class="notice">' . $errorL . '</p>';
				
				$this->adminContent .=	'<select name="edit_userL" id="selLang" style="float:none">' . PHP_EOL;
				
				// Backend-Sprachen
				foreach($GLOBALS['adminLangs'] as $key => $adminLang) {
				
					$this->adminContent .='<option value="' . $key . '"' . (isset($editUserL) && $editUserL == $key ? ' selected="selected"' : '') . ' style="background:url(' . SYSTEM_IMAGE_DIR . '/flag_' . $key . '.png) no-repeat 99px center">' . $adminLang . '</option>' . PHP_EOL; // Benutzergruppe
				}
						
				$this->adminContent .=	'</select> ' .
										parent::getIcon('lang-' . $editUserL, "inline-icon background-icon", 'style="background:url(' . SYSTEM_IMAGE_DIR . '/flag_' . $editUserL . '.png) no-repeat center center"') .
										'<br class="clearfloat" /></li>' . PHP_EOL;
				
						
				// Admin-Skin
				$this->adminContent .=	'<li><label>{s_label:setadminskin}</label>' . PHP_EOL .
										'<select class="skinSelect left" name="at_skin" onchange="$(this).closest(\'form\').attr(\'data-ajax\',\'false\');">' . PHP_EOL;
								
				foreach($this->adminSkins as $skin) {
					
					$this->adminContent .=	'<option value="'.$skin.'"' . ($this->atSkin == $skin ? ' selected="selected"' : '');
					$this->adminContent .=	' data-img-src="' . SYSTEM_IMAGE_DIR . '/skin-' . $skin . '.png"';
					$this->adminContent .=	' data-img-label="skin-' . $skin . '"';
					$this->adminContent .=	' data-title="skin-' . $skin . '"';
					$this->adminContent .=	'>'.$skin.'</option>' . PHP_EOL;
				}
				
				$this->adminContent .=	'</select>' . PHP_EOL .
										'<div id="skinSelectionBox" class="choose imagePicker">' . PHP_EOL .
										'</div>' . PHP_EOL;
										'<br class="clearfloat" /></li>' . PHP_EOL;

										
				$this->adminContent .=	'<li class="submit change">' . "\n";
			
				// Button submit (edit)
				$btnDefs	= array(	"type"		=> "submit",
										"name"		=> "edit_user",
										"class"		=> "change",
										"value"		=> "{s_button:takechange}",
										"icon"		=> "ok"
									);
				
				$this->adminContent .=	parent::getButton($btnDefs);
			
				$this->adminContent .=	'<input name="edit_user" type="hidden" value="' . $GLOBALS['_POST']['edit_user'] . '" />' . PHP_EOL . 
										parent::getTokenInput();
			
				$this->adminContent .=	'</li>' . PHP_EOL .
										'</ul>' . PHP_EOL .
										'</form>' . PHP_EOL;

				// Password checker script
				$this->adminContent .=	'<script src="' . SYSTEM_HTTP_ROOT . '/access/js/password-checker.js"></script>' . PHP_EOL;
				
				// getUserScriptTag
				$this->adminContent .=	$this->getUserScriptTag();

			} // Ende, falls Userdaten vorhanden
			else
				$this->adminContent .=	'<p class="notice error">{s_text:nouser}</p>' . PHP_EOL;
			
			$this->adminContent .=	'</div>' . PHP_EOL;
			
		} // Ende Benutzer bearbeiten



		// Falls ein Benutzer gelöscht werden soll
		elseif(isset($GLOBALS['_POST']['del_user'])) {

			$delUsersArr		= array();
			$this->delUser		= $GLOBALS['_POST']['del_user'];
			
			
			// Falls mehrere Benutzer gelöscht werden sollen
			if ($this->delUser == "array"
			&& isset($GLOBALS['_POST']['userNames'])
			) {
			
				$delUsersArr	= $GLOBALS['_POST']['userNames'];			
			}
			else
				$delUsersArr[]	= $this->delUser;

			
			// Falls keine Benutzer
			if(!is_array($delUsersArr)
			|| count($delUsersArr) == 0
			) {
				$this->adminContent .= 	'</div>' . PHP_EOL;				
				$this->adminContent .= 	'<h2 class="cc-section-heading cc-h2">{s_header:deluser}</h2>' . PHP_EOL . 
										'<p class="notice error">{s_text:nouser}</p>' . PHP_EOL;				
				$this->adminContent	.= $this->getBackButtons($showBackButton);				
				$this->adminContent	.= $this->closeAdminContent();
				
				return $this->adminContent;
			}

			
			// Falls keine Löschberechtigung
			if($this->loggedUserGroup != "admin"
			&& $this->loggedUserGroup != "editor"
			&& !in_array($this->loggedUser, $delUsersArr)
			) {
				$this->adminContent .= 	'</div>' . PHP_EOL;				
				$this->adminContent .= 	'<h2 class="cc-section-heading cc-h2">{s_header:deluser}</h2>' . PHP_EOL . 
										'<p class="notice error">{s_error:noaccess}</p>' . PHP_EOL;				
				$this->adminContent	.= $this->getBackButtons($showBackButton);				
				$this->adminContent	.= $this->closeAdminContent();
				
				return $this->adminContent;
			}
			
			
			$deleted	= false;
			$noTarget	= false;
			$queryExt	= "";
			$queryAdmins	= array(0 => array(
											"admincnt" => 1
											)
									);
			
			// del header
			$this->adminContent .=	'<h2 class="cc-section-heading cc-h2">{s_header:deluser}</h2>' . PHP_EOL;
			
		
			// DB del string
			foreach($delUsersArr as $delUser) {
				
				$queryExt	.= " `username` = '" . $this->DB->escapeString($delUser) . "' OR ";
				
			}
			$queryExt	= substr($queryExt, 0, -4);
			
			
			// db-Tabelle sperren
			$lock = $this->DB->query("LOCK TABLES `" . $this->tableUser . "`");
			
			
			// Datenbanksuche nach zu löschendem Benutzer
			$query = $this->DB->query("SELECT * 
											FROM `" . $this->tableUser . "` 
											$this->dbFilter 
											AND (`username` = '' 
											OR $queryExt)
											", false);
			#die(var_dump($query));
			
			if(is_array($query)
			&& count($query) > 0
			) {
			
				$deletableUsers	= count($query);
				
				foreach($delUsersArr as $uKey => $delUser) {
			
					$this->delUserDB = $delUser;
					
					
					if($this->loggedUserGroup == "admin") {
					
						$queryAdmins = $this->DB->query("SELECT COUNT(*) as admincnt 
														FROM `" . $this->tableUser . "` 
														WHERE `group` = 'admin' 
														AND `username` != '" . $this->DB->escapeString($this->loggedUser) . "'
														", false);
						
						#var_dump($queryAdmins[0]['admincnt']);
					}			
					
					// Falls letzter Admin
					if(is_array($queryAdmins)
					&& $queryAdmins[0]['admincnt'] == 0
					&& $this->loggedUser == $delUser
					) {

							$this->adminContent .=	'<p class="notice error">' . $delUser . ' &#9654; {s_error:dellastadmin}</p>' . PHP_EOL;
							
							unset($delUsersArr[$uKey]);
							$deletableUsers--;

					}
					
					// Falls das Löschen bestätigt wurde
					elseif(isset($GLOBALS['_POST']['delete']) && $GLOBALS['_POST']['delete'] != "") {
				
				
						$deleteSQL1 = $this->DB->query("DELETE 
															FROM `" . $this->tableUser . "` 
															WHERE `username` = '$this->delUserDB'
															");
							
						// db-Sperre aufheben
						$unLock = $this->DB->query("UNLOCK TABLES");


						// Falls Benutzer gelöscht wurde
						if($deleteSQL1 === true) {
							
							$this->adminContent .=	'<p class="notice success">' . $delUser . ' &#9654; {s_notice:deluser}</p>' . PHP_EOL;				
							$deleted = true;
							
							// Falls gelöschter Benutzer aktuell eingeloggt, Benutzer ausloggen
							if($this->loggedUser == $delUser)
								$this->o_user->logoutUser($delUser);
						
						}
						// Falls Fehler
						else {

							$this->adminContent .=	'<p class="notice error">' . $delUser . ' &#9654; {s_error:error}</p>' . PHP_EOL;
												
							$noTarget = true;
							
						}
				
					} // Ende löschen bestätigt
				
				} // Ende foreach
				
				
				// Falls keine zum löschen freigegebenen Benutzer vorhanden
				if($deletableUsers == 0)
					$noTarget = true;
			
			
			} // Ende falls zu löschender Eintrag existiert

			else {

				$this->adminContent .=	'<p class="notice error">{s_text:nouser}</p>' . PHP_EOL;
									
				$noTarget = true;
				
			}
			
			
			// db-Sperre aufheben
			$unLock = $this->DB->query("UNLOCK TABLES");


			
			// Formular: Löschen bestätigen
			if($deleted == false && $noTarget == false) {
									
				$this->adminContent .=	'<p class="notice error">{s_header:deluser}</p>' . PHP_EOL . 
										'<ul class="framedItems">' . PHP_EOL . 
										'<li>' . PHP_EOL . 
										'<span class="delbox">' . PHP_EOL . 
										'{s_text:deluser} <span class="" title="{s_title:deluser}">&nbsp;</span><br /><br /><strong>' . implode("<br />", $delUsersArr) . '</strong></span>' . PHP_EOL . 
										'</li>' . PHP_EOL . 
										'<li class="change submit">' . PHP_EOL . 
										'<form action="" id="adminfm2" method="post">' . PHP_EOL;
			
				// Button cancel
				$btnDefs	= array(	"type"		=> "submit",
										"name"		=> "cancel",
										"class"		=> "cancel right",
										"value"		=> "{s_button:cancel}",
										"icon"		=> "cancel"
									);
				
				$this->adminContent	.=	parent::getButton($btnDefs);
				
				$this->adminContent	.=	'<input name="cancel" type="hidden" value="{s_button:cancel}" />' . PHP_EOL . 
										parent::getTokenInput() . 
										'</form>' . PHP_EOL .
										'<form action="" id="adminfm" method="post"' . (in_array($this->loggedUser, $delUsersArr) ? ' data-ajax="false"' : '') . '>' . PHP_EOL;
				
				// Button delete-ok
				$btnDefs	= array(	"type"		=> "submit",
										"name"		=> "submit",
										"class"		=> "delete",
										"value"		=> "{s_button:delete}",
										"icon"		=> "delete"
									);
				
				$this->adminContent	.=	parent::getButton($btnDefs);
										
				$this->adminContent	.=	'<input type="hidden" name="step" id="step" value="del" /> ' . PHP_EOL . 
										'<input type="hidden" name="delete" value="true" />' . PHP_EOL . 
										'<input type="hidden" name="del_user" value="array" />' . PHP_EOL;
										
					
				foreach($delUsersArr as $delUser) {
					$this->adminContent .=	'<input type="hidden" name="userNames[]" value="' . $delUser . '" />' . PHP_EOL;
				}
				
				$this->adminContent	.=	parent::getTokenInput() . 
										'</form>' . PHP_EOL . 
										'</li>' . PHP_EOL . 
										'</ul>' . PHP_EOL;
			}
		}


		
		// Andernfalls Benutzer auflisten
		else {
				
			// Notifications
			$this->adminContent 	.= $this->getSessionNotifications("notice", true);

			
			$showBackButton = false;			


			// Falls editorLog, Rubrik neuen Benutzer anlegen einfügen
			if($this->editorLog) {
				$this->adminContent .= 	'<h2 class="toggle cc-section-heading cc-h2">{s_header:newuser}</h2>' . PHP_EOL .
										'<ul class="editList cc-ist cc-list-large">' . PHP_EOL .
										'<li class="listItem">' . "\r" .
										'<span class="listName">{s_label:adduser}</span>' . PHP_EOL . 
										'<span class="editButtons-panel">' . PHP_EOL;
	
				$this->adminContent .=	'<form action="" class="adminfm1" method="post">' . PHP_EOL;
				
				// Button new
				$btnDefs	= array(	"type"		=> "submit",
										"name"		=> "new_user",
										"class"		=> 'newUser ajaxSubmit button-icon-only',
										"value"		=> "new_user",
										"text"		=> "",
										"title"		=> '{s_label:adduser}',
										"attr"		=> 'data-ajaxform="true"',
										"icon"		=> "new"
									);
				
				$this->adminContent .=	parent::getButton($btnDefs);
										
				$this->adminContent .=	'<input type="hidden" name="new_user" value="new_user" />' . PHP_EOL . 
										parent::getTokenInput() . 
										'</form>' . PHP_EOL .
										'</span>' . PHP_EOL . 
										'</li>' . PHP_EOL . 
										'</ul>' . PHP_EOL;
			}
			
			// Benutzerliste
			$this->adminContent .= 	'<h2 class="toggle cc-section-heading cc-h2">{s_header:userlist}</h2>' . PHP_EOL .
									'<div class="adminBox">' . PHP_EOL;
			
			
			// Falls editorLog, ControlBar einfügen
			if($this->editorLog) {
			
				$this->adminContent .= 	'<div class="controlBar"><form action="'.$this->formAction.'" method="post">' . PHP_EOL . 
										'<div class="dataCatSelection left"><label>{s_label:usergroup}</label>' . PHP_EOL .
										'<select name="filter_group" class="listCat" data-action="autosubmit">' . PHP_EOL . 
										'<option value="all"' . ($this->filterGroup == "<all>" ? ' selected="selected"' : '') . '>{s_option:allgroup}</option>' . PHP_EOL;
			
				foreach($this->userGroupsControlBar as $userGroup) {
					
					if($userGroup != "public")
						$this->adminContent .='<option value="' . $userGroup . '"' . ($this->filterGroup == $userGroup ? ' selected="selected"' : '') . '>' . (isset(parent::$staText['option']['group' . $userGroup]) ? '{s_option:group' . $userGroup . '}' : $userGroup) . '</option>' . PHP_EOL;						
				}
				
				$this->adminContent .= 	'</select></div>' . PHP_EOL .
										'<div class="sortOption left"><label>{s_label:sort}</label>' . PHP_EOL .
										'<select name="sort_user" class="listSort" data-action="autosubmit">' . PHP_EOL;
				
				$sortOptions = array("nameasc" => "{s_option:nameasc}",
									 "namedsc" => "{s_option:namedsc}",
									 "groupasc" => "{s_option:groupasc}",
									 "groupdsc" => "{s_option:groupdsc}",
									 "dateasc" => "Reg-{s_option:dateasc}",
									 "datedsc" => "Reg-{s_option:datedsc}"
									 );
				
				foreach($sortOptions as $key => $value) { // Sortierungsoptionen
					
					$this->adminContent .='<option value="' . $key . '"';
					
					if(isset($GLOBALS['_POST']['sort_user']) && $key == $this->sortUser)
						$this->adminContent .=' selected="selected"';
						
					$this->adminContent .= '>' . $value . '</option>' . PHP_EOL;
				
				}
									
				$this->adminContent .= 	'</select></div>' . PHP_EOL;
				
				
				// Limit
				$this->adminContent .= 	'<div class="sortOption small left"><label>{s_label:limit}</label>' . PHP_EOL;
				
				$this->adminContent	.=	$this->getLimitSelect($this->limitOptions, $this->maxRows);

				$this->adminContent .= 	'</div>' . PHP_EOL .
										'<div class="filterOptions cc-table-cell">' . PHP_EOL .
										'<div class="filterOption left"><label for="all">{s_label:all}</label>' . PHP_EOL .
										'<label class="radioBox markBox">' . PHP_EOL .
										'<input type="radio" name="filter_abo" id="all" value="all"' . ($this->filterAbo == "all" ? ' checked="checked"' : '') . ' data-action="filterlist" />' . PHP_EOL .
										'</label>' . PHP_EOL .
										'</div>' . PHP_EOL .
										'<div class="filterOption left"><label for="abo">{s_label:abo}</label>' . PHP_EOL .
										'<label class="radioBox markBox">' . PHP_EOL .
										'<input type="radio" name="filter_abo" id="abo" value="abo"' . ($this->filterAbo == "abo" ? ' checked="checked"' : '') . ' data-action="filterlist" />' . PHP_EOL .
										'</label>' . PHP_EOL .
										'</div>' . PHP_EOL .
										'<div class="filterOption left"><label for="nonabo">{s_label:nonabo}</label>' . PHP_EOL .
										'<label class="radioBox markBox">' . PHP_EOL .
										'<input type="radio" name="filter_abo" id="nonabo" value="unabo"' . ($this->filterAbo == "unabo" ? ' checked="checked"' : '') . ' data-action="filterlist" />' . PHP_EOL .
										'</label>' . PHP_EOL .
										'</div>' . PHP_EOL .
										'<div class="filterOption left"><label for="online">online</label>' . PHP_EOL .
										'<label class="radioBox markBox">' . PHP_EOL .
										'<input type="radio" name="filter_status" id="online" value="online"' . ($this->filterStatus == "online" ? ' checked="checked"' : '') . ' data-action="filterlist" />' . PHP_EOL .
										'</label>' . PHP_EOL .
										'</div>' . PHP_EOL .
										'<div class="filterOption left"><label for="offline">offline</label>' . PHP_EOL .
										'<label class="radioBox markBox">' . PHP_EOL .
										'<input type="radio" name="filter_status" id="offline" value="offline"' . ($this->filterStatus == "offline" ? ' checked="checked"' : '') . ' data-action="filterlist" />' . PHP_EOL .
										'</label>' . PHP_EOL .
										'</div>' . PHP_EOL .
										'</div>' . PHP_EOL;
		
				// Suchfunktion
				$this->adminContent .=	'<div id="ccUserSearch" class="userSearch popup-panel-right hide-init ui-tooltip ui-widget ui-corner-all ui-widget-content"><label>{s_label:searchfor}</label>' .
										'<span class="singleInput-panel">' . PHP_EOL .
										'<input type="text" name="userSearch" class="userSearch input-button-right" value="" />' . PHP_EOL .
										'<span class="editButtons-panel">' . PHP_EOL;

				// Button search
				$btnDefs	= array(	"type"		=> "submit",
										"name"		=> "reset_search_val",
										"class"		=> 'resetSearchVal button-icon-only',
										"value"		=> "true",
										"text"		=> "",
										"title"		=> '{s_label:reset}',
										"icon"		=> "search"
									);
				
				$this->adminContent .=	parent::getButton($btnDefs);
										
				$this->adminContent .=	'</span>' . PHP_EOL .
										'</span>' . PHP_EOL .
										'</div>' . PHP_EOL;
			
			
				$this->adminContent .=	'</form>' . PHP_EOL;
		
				// Button panel
				$this->adminContent		.= 	'<span class="editButtons-panel">' . PHP_EOL;
				
				// Button search
				$btnDefs	= array(	"type"		=> "button",
										"class"		=> 'toggleUserSearch button-icon-only',
										"text"		=> "",
										"title"		=> '{s_title:search}',
										"attr"		=> 'data-toggle="ccUserSearch"',
										"icon"		=> "search"
									);
					
				$this->adminContent .=	parent::getButton($btnDefs);

				$this->adminContent .=	'</div>' . PHP_EOL;
				
				
				// Falls Gruppenfilter oder Abofilter, Filter löschen Button einfügen
				if((!empty($this->filterGroup) && $this->filterGroup != "<all>")
				|| (!empty($this->filterAbo) && $this->filterAbo != "all")
				|| (!empty($this->filterStatus) && $this->filterStatus != "all")
				|| !empty($this->userSearch)
				) {
					
					$filter	= "";
					
					if(!empty($this->filterGroup) && $this->filterGroup != "<all>")
						$filter	.= '<strong>' . (isset(parent::$staText['option']['group' . $this->filterGroup]) ? '{s_option:group' . $this->filterGroup . '}' : $this->filterGroup) . '</strong>';
				
					
					if(!empty($this->filterAbo) && $this->filterAbo != "all") {
						$filter .= ($filter != "" ? ' | ' : '');
						$filter	.= '<strong>{s_label:' . ($this->filterAbo == "abo" ? '' : 'non') . 'abo}' . '</strong>';
					}
				
					
					if(!empty($this->filterStatus) && $this->filterStatus != "all") {
						$filter .= ($filter != "" ? ' | ' : '');
						$filter	.= '<strong>' . $this->filterStatus . '</strong>';
					}
				
					
					if(!empty($this->userSearch)) {
						$searchIco	= parent::getIcon("search", "inline-icon");
						$filter .= ($filter != "" ? ' | ' : '');
						$filter	.= '<strong>' . $searchIco . '&nbsp;' . $this->userSearch . '</strong>';
					}
					
					
					$this->adminContent .=	'<span class="showHiddenListEntries actionBox cc-hint">' . PHP_EOL;
			
					// Filter icon
					$this->adminContent .=	'<span class="listIcon">' . PHP_EOL .
											parent::getIcon("filter", "inline-icon") .
											'</span>' . "\n";

					$this->adminContent .=	'{s_label:filter}: ' . $filter;
					
					$this->adminContent .=	'<form action="'.$this->formAction.'" method="post">' . PHP_EOL;
					
					$this->adminContent .=	'<span class="editButtons-panel">' . PHP_EOL;
			
					// Button remove filter
					$btnDefs	= array(	"type"		=> "submit",
											"class"		=> 'removefilter ajaxSubmit button-icon-only',
											"text"		=> "",
											"title"		=> '{s_title:removefilter}',
											"icon"		=> "close"
										);
					
					$this->adminContent .=	parent::getButton($btnDefs);
											
					$this->adminContent .=	'<input type="hidden" value="all" name="filter_group">' . PHP_EOL .
											'<input type="hidden" value="all" name="filter_abo">' . PHP_EOL .
											'<input type="hidden" value="all" name="filter_status">' . PHP_EOL .
											'</span>' . PHP_EOL .
											'</form>' . PHP_EOL .
											'</span>' . PHP_EOL .
											'</span>' . PHP_EOL;
				}
				
			} // Ende falls editorLog

			
			
			
			// Falls Benutzer vorhanden					
			if(count($this->userQuery) > 0) {
				
				$i			= 1;
				$userCnt	= 0;
				$userList	= "";
				
				// Actionbox
				$this->activeSessions	= $this->getActiveSessions();
				
				// Actionbox
				$this->adminContent .=	$this->getUserActionBox();

				
				// Auslesen der vorhandenen Benutzer
				foreach($this->userQuery as $user) {
				
					$userID			= str_pad($user['userid'], 9, '0', STR_PAD_LEFT);
					$userOnline		= $this->checkUserOnline($userID);
					
					if(($this->filterStatus == "online" && !$userOnline)
					|| ($this->filterStatus == "offline" && ($userOnline || !in_array($user['group'], $this->backendUserGroups)))
					)
						continue;
					
					$statusIcon		= $userOnline ? 'online' : 'offline';
					$statusClass	= 'user-status-icon' . (!in_array($user['group'], $this->backendUserGroups) ? ' user-status-unknown' : '');
					$statusTitle	= $userOnline ? 'online' : (!in_array($user['group'], $this->backendUserGroups) ? 'unknown' : 'offline');
					$userIcon		= $user['username'] == $this->loggedUser ? 'admin' : 'user';
					$newslIcon		= parent::getIcon("newsletter", "newsletter-icon inline-icon left", 'title="' . ($user['newsletter'] == 1 ? '{s_option:yesn} ->' : '{s_common:non}') . ' {s_option:groupsubscriber}"');
					$newslAboIcon	= $user['newsletter'] == 1 ? parent::getIcon("check", "inline-icon left") : parent::getIcon("cancel", "inline-icon left");
					
					$userList .= 	'<li class="userList listItem ' .
									$user['group'] .
									($user['username'] == $this->loggedUser ? ' loggedUser' : '') .
									($userOnline ? ' userOnline' : '') .
									($i%2 ? '' : ' alternate') .
									'" data-menu="context" data-target="contextmenu-' . $i . '">' . PHP_EOL;
			
					// Markbox
					$userList .=	'<label class="markBox">' . 
									'<input type="checkbox" name="userNames[' . $i . ']" value="' . $user['username'] . '" class="addVal" />' .
									'</label>';			
					
					$userList .= 	'<span class="listUser cc-table-cell">' . PHP_EOL .
									'<span class="userIcon listIcon cc-table-cell">' . PHP_EOL .
									parent::getIcon($userIcon, 'usergroup-' . $user['group'] . ' usergroup-icon inline-icon' . (!$user['active'] ? ' cc-status-inactive' : ''), 'title="' . ($user['username'] == $this->loggedUser ? '{s_title:me}' : $user['username'] . ' (' . $user['group'] . ')') . (!$user['active'] ? '<br />({s_common:inactive})' : '') . '"') .
									parent::getIcon($statusIcon, $statusClass . ' inline-icon', 'title="' . $statusTitle . '"') .
									'</span>' . PHP_EOL . 
									'<span class="userName cc-table-cell" title="' . ($user['username'] == $this->loggedUser ? '{s_title:me}' : $user['username'] . ' (' . $user['group'] . ')') . '">' .
									(strlen($user['username']) < 25 ? $user['username'] : User::getMailLocalPart($user['username']) . "@...") .
									'</span>' . PHP_EOL . 
									'<span class="userGroup cc-table-cell" title="{s_label:usergroup}: ' . $user['group'] . ($user['own_groups'] != "" ? ',' . $user['own_groups'] : '') . '">' .
									$user['group'] . ($user['own_groups'] != "" ? ',' . $user['own_groups'] : '') .
									($user['own_groups'] != "" && !$this->checkUserGroupExists($user['own_groups']) ? parent::getIcon("warning", "inline-icon", 'title="{s_title:groupnotexits}"') : '') .
									'</span>' .
									'<span class="userMail cc-table-cell" title="' . $user['email'] . '">' . $user['email'] . '</span>' . PHP_EOL .
									'<span class="userNewsl cc-table-cell">' .
									$newslIcon .
									$newslAboIcon .
									'</span>' . PHP_EOL . 
									'</span>' . PHP_EOL;
											
					$userList .= 	'<span class="editButtons-panel" data-id="contextmenu-' . $i . '">' . PHP_EOL;
					
					$userList .=	'<form action="" class="adminfm1" method="post">' . PHP_EOL;
			
					// Button edit
					$btnDefs	= array(	"type"		=> "submit",
											"name"		=> "edit_user",
											"class"		=> 'button-icon-only',
											"value"		=> $user['username'],
											"text"		=> "",
											"title"		=> '{s_label:edituser}',
											"attr"		=> 'data-ajaxform="true" data-menuitem="true" data-id="item-id-' . $i . '"',
											"icon"		=> "edit"
										);
					
					$userList .=	parent::getButton($btnDefs);
					
					$userList .=	'<input type="hidden" name="edit_user" value="' . $user['username'] . '" />' . PHP_EOL . 
									parent::getTokenInput() . 
									'</form>' . PHP_EOL;
			
					$userList .=	'<form action="" class="adminfm2" method="post">' . PHP_EOL;
					
					// Button delete
					$btnDefs	= array(	"type"		=> "submit",
											"name"		=> "del_user",
											"class"		=> 'delTpl button-icon-only',
											"value"		=> $user['username'],
											"text"		=> "",
											"title"		=> '{s_label:deluser}',
											"attr"		=> 'data-ajaxform="true" data-menuitem="true" data-id="item-id-' . $i . '"',
											"icon"		=> "delete"
										);
					
					$userList .=	parent::getButton($btnDefs);
											
					$userList .=	'<input type="hidden" name="del_user" value="' . $user['username'] . '" />' . PHP_EOL . 
									parent::getTokenInput() . 
									'</form>' . PHP_EOL . 
									'</span>' . PHP_EOL;
					
					$i++;
					$userCnt++;
				
				}
				
				if(!$userCnt) {
					$this->adminContent .=	'<p class="notice error">{s_text:nouser}</p></div>' . PHP_EOL;
				}
				else {
					// User list
					$this->adminContent .=	'<ul id="cc-userList" class="editList">' . PHP_EOL;
					$this->adminContent .=	$userList;
					$this->adminContent .=	'</ul>' . PHP_EOL .
											$dataNav . 
											'</div>' . PHP_EOL;
				}
				
				// Contextmenü-Script
				$this->adminContent .=	$this->getContextMenuScript();

		
			} // Ende if count userQuery > 0
			else
				$this->adminContent .=		'<p class="notice error">{s_text:nouser}</p></div>' . PHP_EOL;
				
		} // Ende else

		$this->adminContent .=	'</div>' . PHP_EOL;




		// Zurückbuttons
		$this->adminContent	.= $this->getBackButtons($showBackButton);
		
		
		// #adminContent close
		$this->adminContent	.= $this->closeAdminContent();
		
		
		return $this->adminContent;

	}

	

	/**
	 * Methode zum Generieren einer ControlBar
	 * 
	 * @access	private
	 * @return	string
	 */
	private function evalUserControlBarRequest()
	{
	
		// Falls das Formular zur Filterung von Daten abgeschickt wurde
		if(isset($GLOBALS['_POST']['filter_group']) && $GLOBALS['_POST']['filter_group'] != "") {
			$groupP = $GLOBALS['_POST']['filter_group'];
			
			if($groupP == "all") {
				$this->filterGroup = "";
				// Ggf. filter_group aus Session löschen
				$this->unsetSessionKey('filter_group');
			}
			else {
				$this->setSessionVar('filter_group', $groupP);
				$this->filterGroup = $groupP;
				$groupDB = $this->DB->escapeString($this->filterGroup);
				$this->restrict = " AND (`group` = '" . $groupDB . "' OR FIND_IN_SET('" . $groupDB . "', `own_groups`))";
			}
		}
		
		elseif(!empty($this->g_Session['filter_group'])) {
			$this->filterGroup = $this->g_Session['filter_group'];
			$groupDB = $this->DB->escapeString($this->filterGroup);
			$this->restrict = " AND (`group` = '" . $groupDB . "' OR FIND_IN_SET('" . $groupDB . "', `own_groups`))";
		}
		
		// User search
		if(!empty($GLOBALS['_POST']['userSearch'])) {
			$this->userSearch	= $GLOBALS['_POST']['userSearch'];
			$userSearchDB		= $this->DB->escapeString($this->userSearch);
			$this->restrict .= " AND (`username` LIKE '%" . $userSearchDB . "%' OR `email` LIKE '%" . $userSearchDB . "%')";
		}
		
		// Get-Parameter auslesen
		if(!empty($GLOBALS['_GET']['sort_user']))
			$GLOBALS['_POST']['sort_user'] = $GLOBALS['_GET']['sort_user'];
		
		if(!empty($GLOBALS['_GET']['abo']))
			$GLOBALS['_POST']['abo'] = $GLOBALS['_GET']['abo'];
		
		if(!empty($GLOBALS['_GET']['status']))
			$GLOBALS['_POST']['status'] = $GLOBALS['_GET']['status'];


		// Anzahl an Einträgen pro Seite		
		$this->maxRows = self::getLimit();
		
		
		// Filter für Newsletterempfänger
		if(isset($GLOBALS['_POST']['filter_abo'])) {
		
			$this->filterAbo = $GLOBALS['_POST']['filter_abo'];
			
			$this->setSessionVar('filter_abo', $this->filterAbo);
		}		
		elseif(!empty($this->g_Session['filter_abo'])) {
			
			$this->filterAbo = $this->g_Session['filter_abo'];
		}
		
		if($this->filterAbo != "all")					
			$this->filter = " `newsletter` = " . ($this->filterAbo == "abo" ? "1" : "0");
		else
			$this->filter = " `newsletter` >= 0";
		
		
		// Filter für Status
		if(isset($GLOBALS['_POST']['filter_status'])) {
		
			$this->filterStatus	= $GLOBALS['_POST']['filter_status'];
			
			$this->setSessionVar('filter_status', $this->filterStatus);
		}		
		elseif(!empty($this->g_Session['filter_status'])) {
			
			$this->filterStatus = $this->g_Session['filter_status'];
		}
	
		
		// Sort user list
		if(isset($GLOBALS['_POST']['sort_user']))
			$this->sortUser = $GLOBALS['_POST']['sort_user'];
		
		
		switch($this->sortUser) {
			
			case "nameasc":
				$this->dbOrder = " ORDER BY `username` ASC";
				break;
				
			case "namedsc":
				$this->dbOrder = " ORDER BY `username` DESC";
				break;
				
			case "groupasc":
				$this->dbOrder = " ORDER BY `group` ASC, `username` ASC";
				break;
				
			case "groupdsc":
				$this->dbOrder = " ORDER BY `group` DESC, `username` ASC";
				break;
				
			case "dateasc":
				$this->dbOrder = " ORDER BY `reg_date` ASC, `username` ASC";
				break;
				
			case "datedsc":
				$this->dbOrder = " ORDER BY `reg_date` DESC, `username` ASC";
				break;
				
		}
		
		
		$this->dbFilter = $this->dbFilter . $this->restrict;
		if($this->dbFilter != "" && $this->filter != "")
			$this->dbFilter .= " AND" . $this->filter;
	
	}
	
	

	/**
	 * getUserActionBox
	 * @access protected
	 */
	protected function getUserActionBox()
	{

		// Checkbox zur Mehrfachauswahl zum Löschen und Publizieren
		$output =		'<div class="actionBox">';
			
		// Formular für Multi-Action
		$output .=		'<form action="' . $this->formAction . '" method="post">' . PHP_EOL;
		
		$output .=		'<label class="markAll markBox" data-mark="#cc-userList"><input type="checkbox" id="markAllLB" data-select="all" /></label>' .
						'<label for="markAllLB" class="markAllLB"> {s_label:mark}</label>' . PHP_EOL .
						'<span class="editButtons-panel">' . PHP_EOL;
		/*
		// Button publish
		$btnDefs	= array(	"type"		=> "submit",
								"class"		=> 'pubAll activateUser button-icon-only',
								"text"		=> "",
								"title"		=> '{s_title:pubuser}',
								"icon"		=> "publish"
							);
		
		$output .=	parent::getButton($btnDefs);
		
		// Button unpublish
		$btnDefs	= array(	"type"		=> "submit",
								"class"		=> 'pubAll activateUser unpublish button-icon-only',
								"text"		=> "",
								"title"		=> '{s_title:unpubuser}',
								"icon"		=> "unpublish"
							);
		
		$output .=	parent::getButton($btnDefs);
		*/
		// Button delete
		$btnDefs	= array(	"type"		=> "submit",
								"class"		=> 'delAll delUsers button-icon-only',
								"text"		=> "",
								"title"		=> '{s_title:delmarked}',
								"attr"		=> 'data-action="delmultiple"',
								"icon"		=> "delete"
							);
		
		$output .=	parent::getButton($btnDefs);

		
		// Alle Seitenstatus/Löschen Button
		$output .=		'<input type="hidden" name="del_user" value="array" />' . PHP_EOL .
						'<input type="hidden" class="multiAction" name="multiAction" value="' . $this->formAction . '&array=1" />' . PHP_EOL;
		
		
		$output .=		'</span>' .
						'</form>' . PHP_EOL .
						'</div>' . PHP_EOL;
		
		return $output;
	
	}
	

	// getActiveSessions
	public function getActiveSessions()
	{
		
		// Datenbanksuche nach zu löschendem Benutzer
		$query = $this->DB->query("SELECT `value` 
										FROM `" . DB_TABLE_PREFIX . "sessions` 
										", false);
		#die(var_dump($query));
		
		if(empty($query)
		|| !is_array($query)
		)
			return array();
			
		return $query;

	}
	

	// checkUserOnline
	public function checkUserOnline($userID)
	{
		
		// Datenbanksuche nach zu löschendem Benutzer
		foreach($this->activeSessions as $sess) {			
			if(strpos($sess['value'], 'userid|s:9:"' . $userID . '";') !== false)
				return true;			
		}
			
		return false;

	}
	

	// getTplScriptTag
	public function getUserScriptTag($hide = false)
	{

		return	'<script>' . PHP_EOL .
				'head.ready("jquery", function(){' . PHP_EOL .
				'head.load({imagepickercss: "' . PROJECT_HTTP_ROOT . '/extLibs/jquery/image-picker/image-picker.css"});' . PHP_EOL .
				'head.load({imagepicker: "' . PROJECT_HTTP_ROOT . '/extLibs/jquery/image-picker/image-picker.min.js"});' . PHP_EOL .
				'head.ready("imagepicker", function(){' . PHP_EOL .
					'$(document).ready(function(){' . PHP_EOL .
						'$("select.skinSelect").imagepicker({
							target_box: $("#skinSelectionBox"),
							hide_select: ' . ($hide ? 'true' : 'false') . ',
							show_label: false,
							limit: undefined,
							initialized: function(){
								$("#skinSelectionBox, #skinSelectionBox ul").show();
								$("#skinSelectionBox ul li").each(function(i,e){
									$(this).attr("title", $("select.skinSelect").children(":nth-child(" + (i+1) + ")").attr("data-title"));
								});
							}
						});' . PHP_EOL .
					'});' . PHP_EOL .
				'});' . PHP_EOL .
				'});' . PHP_EOL .
				'</script>' . PHP_EOL;
	
	}
	


	/**
	 * getBackButtons
	 * 
	 * @access public
	 * @return string
	 */
	public function getBackButtons($showBackButton = false)
	{

		// Zurückbuttons
		$output		=	'<p>&nbsp;</p>' . PHP_EOL . 
						'<div class="adminArea">' . PHP_EOL . 
						'<ul>' . PHP_EOL .
						'<li class="submit back">' . PHP_EOL;

		if($showBackButton) {
			// Button back
			$btnDefs	= array(	"href"		=> ADMIN_HTTP_ROOT . '?task=user',
									"class"		=> "left",
									"text"		=> "{s_button:adminuser}",
									"icon"		=> "usermenu"
								);
			
			$output	 .=	parent::getButtonLink($btnDefs);
		}
		
		
		// Button back
		$output		.=	$this->getButtonLinkBacktomain();
				
		$output		.=	'<br class="clearfloat" />' . PHP_EOL .
						'</li>' . PHP_EOL . 
						'</ul>' . PHP_EOL . 
						'<p>&nbsp;</p>' . PHP_EOL . 
						'<p>&nbsp;</p>' . PHP_EOL . 
						'</div>' . PHP_EOL;
		
		return $output;
	
	}
}
