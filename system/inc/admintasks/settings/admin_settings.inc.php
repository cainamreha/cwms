<?php
namespace Concise;



###################################################
###############  Settings-Bereich  ################
###################################################

// Einstellungen verwalten 

class Admin_Settings extends Admin implements AdminTask
{
		
	// Optionen für File-Upload
	private $uploadMethods	= array();

	public function __construct($DB, $o_lng, $task, $init = false)
	{

		// Admin-Elternklasse aufrufen
		parent::__construct($DB, $o_lng);
		
		parent::$task = $task;
		
		$this->formAction	= ADMIN_HTTP_ROOT . '?task=' . $task;

	}
	
	
	public function getTaskContents($ajax = false)
	{

		if(!$this->editorLog)
			return false;
		
		
		// Enthält Headerbox
		$this->adminHeader		=	'{s_text:adminsettings}' . PHP_EOL . 
									'</div><!-- Ende headerBox -->' . PHP_EOL;
		
		// #adminContent
		$this->adminContent 	=	$this->openAdminContent();
							
		$this->adminContent 	.=	'<div class="adminArea settings">' . PHP_EOL;


		// Notification
		$this->notice 			= $this->getSessionNotifications("notice", true);


		// Zeitzonen
		$zones			= timezone_identifiers_list();
		$zoneOptions	= "";
		$subZoneOptions = "";

		$defTimeZone	= date_default_timezone_get();
		$dtzArr			= explode("/", $defTimeZone);
		$selTimeZone	= reset($dtzArr);
		$selTimeZone2	= end($dtzArr);
		$timeZone		= $defTimeZone;
		
		$blockSubmit	= false;
		
		$ownUserGroups	= $GLOBALS['ownUserGroups']; // Eigene Benutzergruppen
		$searchTables	= $GLOBALS['searchTables'];
		$commentTables	= $GLOBALS['commentTables'];
		$emoticonForms	= $GLOBALS['emoticonForms'];


		// Falls das Formular abgeschickt wurde zunächst Zeitzonen checken
		if(isset($GLOBALS['_POST']['submitAll']) && $GLOBALS['_POST']['submitAll'] == "false") {
			
			$blockSubmit	= true;
			$selTimeZone	= $GLOBALS['_POST']['timeZone'];
			$timeZone		= $GLOBALS['_POST']['subTimeZone'];
			
		}
		elseif(isset($GLOBALS['_POST']['subTimeZone']) && $GLOBALS['_POST']['subTimeZone'] != "") {
			
			$selTimeZone	= $GLOBALS['_POST']['timeZone'];
			$timeZone		= $GLOBALS['_POST']['subTimeZone'];
		}

		foreach ($zones as $zone)
		{
			$zone = explode('/', $zone); // 0 => Continent, 1 => City
		   
			// Only use "friendly" continent names
			if ($zone[0] == 'Africa' || $zone[0] == 'America' || $zone[0] == 'Antarctica' || $zone[0] == 'Arctic' || $zone[0] == 'Asia' || $zone[0] == 'Atlantic' || $zone[0] == 'Australia' || $zone[0] == 'Europe' || $zone[0] == 'Indian' || $zone[0] == 'Pacific')
			{       
				if (isset($zone[1]) != '')
				{
					$locations[$zone[0]][$zone[0]. '/' . $zone[1]] = str_replace('_', ' ', $zone[1]); // Creates array(DateTimeZone => 'Friendly name')
				}
			}
		}

		foreach($locations as $key => $value) {
						
			$zoneOptions .= '<option value="' . $key . '"' . PHP_EOL;

			if($selTimeZone == $key) {
				
				$zoneOptions .= ' selected="selected"' . PHP_EOL;
				$i = 0;
				
				foreach($value as $tmZone => $shortTimeZone) {
						
					$subZoneOptions .= '<option value="' . $tmZone . '"' . PHP_EOL;
					$tzArr			 = explode("/", $timeZone);
					
					if($selTimeZone	!= reset($tzArr)) {
						if($i == 0)
							$timeZone = $selTimeZone."/".$shortTimeZone;
					}
					elseif($selTimeZone2 == $shortTimeZone) {
						$subZoneOptions .= ' selected="selected"' . PHP_EOL;
					}
					
					$subZoneOptions .= '>' . $shortTimeZone . '</option>' . PHP_EOL;
					
					$i++;
				}
			}
			
			$zoneOptions .= '>' . $key . '</option>' . PHP_EOL;
		}

		// jQuery-UI_Themes einlesen
		$selUIThemes	= $this->getUIThemes();
		$adminThemes	= $this->getAdminThemes();

		
		// Optionen für File-Upload
		$this->uploadMethods	= Files::getPotUploadMethods();



		// Falls das Formular abgeschickt wurde
		if(isset($GLOBALS['_POST']['submitAll']) && $GLOBALS['_POST']['submitAll'] == "true") {
		
			// Falls Admin
			if($this->adminLog) {
				
				$adminLang		= $GLOBALS['_POST']['adminLang'];
				$setLive		= $GLOBALS['_POST']['setlive'];
				$urlExt			= $GLOBALS['_POST']['urlext'];
				$htmlTitle		= $GLOBALS['_POST']['htmltitle'];
				$setCache		= $GLOBALS['_POST']['setcache'];
				$setHtml5		= $GLOBALS['_POST']['sethtml5'];
				$setLogging		= $GLOBALS['_POST']['setlogging'];
				$setAnalytics	= $GLOBALS['_POST']['setanalytics'];
				$setAnalyticsPos	= $GLOBALS['_POST']['setanalyticspos'];
				$setAnalyticsCode	= trim($GLOBALS['_POST']['setanalyticscode']);
				$setUserGroups	= $GLOBALS['_POST']['setusergroups'];
				$setReg			= $GLOBALS['_POST']['setreg'];
				$setLog			= $GLOBALS['_POST']['setlog'];
				$setRemUser		= $GLOBALS['_POST']['setremuser'];
				$setSession		= $GLOBALS['_POST']['setsession'];
				$setBadLog		= $GLOBALS['_POST']['setbadlog'];
				$setBanTime		= $GLOBALS['_POST']['setbantime'] * 60;
				$setMaxOrder	= $GLOBALS['_POST']['setmaxorder'];
				$setShowOrder	= $GLOBALS['_POST']['setshoworder'];
				$setSearch		= $GLOBALS['_POST']['setsearch'];
				$setSearchRes	= $GLOBALS['_POST']['setsearchres'];
				$searchTabArray	= isset($GLOBALS['_POST']['setsearchtables']) ? $GLOBALS['_POST']['setsearchtables'] : array();
				$setInstPub		= $GLOBALS['_POST']['setinstpub'];
				$setGbRes		= $GLOBALS['_POST']['setgbres'];
				$setGbMod		= $GLOBALS['_POST']['setgbmod'];
				$setComRes		= $GLOBALS['_POST']['setcomres'];
				$setComMod		= $GLOBALS['_POST']['setcommod'];
				$setMaxCom		= $GLOBALS['_POST']['setmaxcom'];
				$comTabArray	= isset($GLOBALS['_POST']['setcomtables']) ? $GLOBALS['_POST']['setcomtables'] : array();
				$emoFormsArray	= isset($GLOBALS['_POST']['setemoforms']) ? $GLOBALS['_POST']['setemoforms'] : array();
				$setMenPrefix	= $GLOBALS['_POST']['setmenprefix'];
				$setSelTheme	= trim($GLOBALS['_POST']['setseltheme']);
				$setHeadCode	= str_replace("\"", "'", trim($GLOBALS['_POST']['setheadcode']));
				$setJQueryV		= trim($GLOBALS['_POST']['setjqueryv']);
				$setJQueryUIV	= trim($GLOBALS['_POST']['setjqueryuiv']);
				$setShipping	= $GLOBALS['_POST']['setshipping'];
				$setShipLimit	= $GLOBALS['_POST']['setshippinglimit'];
				$setAdminTheme	= $GLOBALS['_POST']['setadmintheme'];
				$setHttps		= $GLOBALS['_POST']['sethttps'];
				$debug			= $GLOBALS['_POST']['debug'];
				$setErrorNote	= $GLOBALS['_POST']['seterrornotify'];
				$setErrorMail	= $GLOBALS['_POST']['seterrormail'];
			}
			
			
			$setSiteAuthor	= $GLOBALS['_POST']['setsiteauthor'];
			$setConMail		= $GLOBALS['_POST']['setconmail'];
			$setOrderMail	= $GLOBALS['_POST']['setordermail'];
			$setAutoMail	= $GLOBALS['_POST']['setautomail'];
			$setNewslMail	= $GLOBALS['_POST']['setnewslmail'];
			$setGbMail		= $GLOBALS['_POST']['setgbmail'];
			$setComMail		= $GLOBALS['_POST']['setcommail'];
			$setAutoAuth	= $GLOBALS['_POST']['setautoauth'];
			$setNewslAuth	= $GLOBALS['_POST']['setnewslauth'];
			$setSmtpHost	= $GLOBALS['_POST']['setsmtphost'];
			$setSmtpPort	= $GLOBALS['_POST']['setsmtpport'];
			$setSmtpMail	= $GLOBALS['_POST']['setsmtpmail'];
			$setSmtpUser	= $GLOBALS['_POST']['setsmtpuser'];
			$setSmtpPass	= $GLOBALS['_POST']['setsmtppass'];
			$setFileupload	= trim($GLOBALS['_POST']['setfileupload']);
			$setFiles		= $GLOBALS['_POST']['setfiles'];
			$setImgWidth	= trim($GLOBALS['_POST']['setimgwidth']);
			$setImgHeight	= trim($GLOBALS['_POST']['setimgheight']);
			$setThumbSize	= trim($GLOBALS['_POST']['setthumbsize']);
			$setPlayerWidth	= trim($GLOBALS['_POST']['setplayerwidth']);
			$setJQueryUIT	= trim($GLOBALS['_POST']['setjqueryuit']);
			$setAdminSkin	= $GLOBALS['_POST']['setadminskin'];
			
		
			// Falls Admin
			if($this->adminLog) {
			
				// Analytics-Code-Datei aktualisieren
				$analyticsFile	= trim(@file_get_contents(PROJECT_DOC_ROOT . '/access/js/analytics.js'));
				
				if( $analyticsFile != $setAnalyticsCode) {
					@file_put_contents(PROJECT_DOC_ROOT . '/access/js/analytics.js', $setAnalyticsCode);
				}
				
				// Eigene Benutzergruppen
				$setUserGroups	= array_filter(preg_replace("/\r/", "", explode("\n", $setUserGroups)));
				
				if(count($ownUserGroups) > 0)
					$stringOwnGroups = '"' . implode('","', $ownUserGroups) . '"';
				else	
					$stringOwnGroups = "";
					
				if(count($setUserGroups) > 0)		
					$stringSetGroups = '"' . implode('","', $setUserGroups) . '"';
				else
					$stringSetGroups = "";

				// Suchtabellen
				$potSearchTables	= array("pages","articles","news","planner");
				$selSearchTables	= array();
				
				for($i = 0; $i < 4; $i++) {
					
					if(isset($searchTabArray[$i]))
						$selSearchTables[] = $potSearchTables[$i];
				}
				
				// Kommentartabellen
				$potComTables	= array("gbook","articles","news","planner");
				$selComTables	= array();
				
				for($i = 0; $i < 4; $i++) {
					
					if(isset($comTabArray[$i]))
						$selComTables[] = $potComTables[$i];
				}
				
				// Emoticons
				$potEmoForms	= array("gbook","comments");
				$selEmoForms	= array();
				
				for($i = 0; $i < 4; $i++) {
					
					if(isset($emoFormsArray[$i]))
						$selEmoForms[] = $potEmoForms[$i];
				}
			}
			
			// Inhalte der Settings-Datei einlesen
			if(!$settings = @file_get_contents(PROJECT_DOC_ROOT . '/inc/settings.php')) die("settings file not found");
			else {
				
				// Sicherungskopie anlegen
				copy(PROJECT_DOC_ROOT . '/inc/settings.php', PROJECT_DOC_ROOT . '/inc/settings.php.old');
		
				// Falls Admin
				if($this->adminLog) {
				
					$settings = preg_replace("/'DEF_ADMIN_LANG',\"".DEF_ADMIN_LANG."\"/", "'DEF_ADMIN_LANG',\"$adminLang\"", $settings);
					$settings = preg_replace("/date_default_timezone_set\(\"[a-zA-Z]*\/[a-zA-Z]*\"\)/", "date_default_timezone_set(\"$timeZone\")", $settings);
					$settings = preg_replace("/'WEBSITE_LIVE',".parent::boolToStr(WEBSITE_LIVE)."/", "'WEBSITE_LIVE',$setLive", $settings);
					$settings = preg_replace("/'PAGE_EXT',\"".PAGE_EXT."\"/", "'PAGE_EXT',\".$urlExt\"", $settings);
					$settings = preg_replace("/'HTML_TITLE',\"".HTML_TITLE."\"/", "'HTML_TITLE',\"$htmlTitle\"", $settings);
					$settings = preg_replace("/'CACHE',".parent::boolToStr(CACHE)."/", "'CACHE',$setCache", $settings);
					$settings = preg_replace("/'HTML5',".parent::boolToStr(HTML5)."/", "'HTML5',$setHtml5", $settings);
					$settings = preg_replace("/'CONCISE_LOG',".parent::boolToStr(CONCISE_LOG)."/", "'CONCISE_LOG',$setLogging", $settings);
					$settings = preg_replace("/'ANALYTICS',".parent::boolToStr(ANALYTICS)."/", "'ANALYTICS',$setAnalytics", $settings);
					$settings = preg_replace("/'ANALYTICS_POSITION',\"".ANALYTICS_POSITION."\"/", "'ANALYTICS_POSITION',\"$setAnalyticsPos\"", $settings);
					$settings = preg_replace('/\$ownUserGroups = array\('.$stringOwnGroups.'\)/', '$ownUserGroups = array('.$stringSetGroups.')', $settings);
					$settings = preg_replace("/'REGISTRATION_TYPE',\"".REGISTRATION_TYPE."\"/", "'REGISTRATION_TYPE',\"$setReg\"", $settings);
					$settings = preg_replace("/'LOGFORM_TYPE',\"".LOGFORM_TYPE."\"/", "'LOGFORM_TYPE',\"$setLog\"", $settings);
					$settings = preg_replace("/'REMEMBER_USER',".parent::boolToStr(REMEMBER_USER)."/", "'REMEMBER_USER',$setRemUser", $settings);
					$settings = preg_replace("/'SESSION_UPTIME',\"".SESSION_UPTIME."\"/", "'SESSION_UPTIME',\"-$setSession minutes\"", $settings);
					$settings = preg_replace("/'MAX_ALLOWED_BAD_LOGINS',".MAX_ALLOWED_BAD_LOGINS."/", "'MAX_ALLOWED_BAD_LOGINS',$setBadLog", $settings);
					$settings = preg_replace("/'LOGIN_BAN_TIME',".LOGIN_BAN_TIME."/", "'LOGIN_BAN_TIME',$setBanTime", $settings);
					$settings = preg_replace("/'MAX_ENTRIES_ORDER_FORM',".MAX_ENTRIES_ORDER_FORM."/", "'MAX_ENTRIES_ORDER_FORM',$setMaxOrder", $settings);
					$settings = preg_replace("/'SHOW_ENTRIES_ORDER_FORM',".SHOW_ENTRIES_ORDER_FORM."/", "'SHOW_ENTRIES_ORDER_FORM',$setShowOrder", $settings);
					$settings = preg_replace("/'SEARCH_TYPE',\"".SEARCH_TYPE."\"/", "'SEARCH_TYPE',\"$setSearch\"", $settings);
					$settings = preg_replace("/'SEARCH_MAX_ROWS',".SEARCH_MAX_ROWS."/", "'SEARCH_MAX_ROWS',$setSearchRes", $settings);
					$settings = preg_replace('/\$searchTables = array\("'.implode('","', $searchTables).'"\)/', '$searchTables = array("'.implode('","', $selSearchTables).'")', $settings);
					$settings = preg_replace("/'DATA_PUBLISH_DELAY',".parent::boolToStr(DATA_PUBLISH_DELAY)."/", "'DATA_PUBLISH_DELAY',$setInstPub", $settings);
					$settings = preg_replace("/'GBOOK_MAX_ROWS',".GBOOK_MAX_ROWS."/", "'GBOOK_MAX_ROWS',$setGbRes", $settings);
					$settings = preg_replace("/'GBOOK_MODERATE',".parent::boolToStr(GBOOK_MODERATE)."/", "'GBOOK_MODERATE',$setGbMod", $settings);
					$settings = preg_replace("/'COMMENTS_MAX_ROWS',".COMMENTS_MAX_ROWS."/", "'COMMENTS_MAX_ROWS',$setComRes", $settings);
					$settings = preg_replace("/'COMMENTS_MODERATE',".parent::boolToStr(COMMENTS_MODERATE)."/", "'COMMENTS_MODERATE',$setComMod", $settings);
					$settings = preg_replace("/'MAX_COMMENTS',".MAX_COMMENTS."/", "'MAX_COMMENTS',$setMaxCom", $settings);
					$settings = preg_replace('/\$commentTables = array\("'.implode('","', $commentTables).'"\)/', '$commentTables = array("'.implode('","', $selComTables).'")', $settings);
					$settings = preg_replace('/\$emoticonForms = array\("'.implode('","', $emoticonForms).'"\)/', '$emoticonForms = array("'.implode('","', $selEmoForms).'")', $settings);
					$settings = preg_replace("/'MOD_MENU_PREFIX',\"".MOD_MENU_PREFIX."\"/", "'MOD_MENU_PREFIX',\"$setMenPrefix\"", $settings);
					$settings = preg_replace("/'FE_THEME_SELECTION',".parent::boolToStr(FE_THEME_SELECTION)."/", "'FE_THEME_SELECTION',$setSelTheme", $settings);
					$settings = preg_replace("/'HTML_HEAD_EXT',\"".str_replace(array("/",".","?","+"), array("\/","\.","\?","\+"), HTML_HEAD_EXT)."\"/", "'HTML_HEAD_EXT',\"$setHeadCode\"", $settings);
					$settings = preg_replace("/'JQUERY_VERSION',\"".JQUERY_VERSION."\"/", "'JQUERY_VERSION',\"$setJQueryV\"", $settings);
					$settings = preg_replace("/'JQUERY_UI_VERSION',\"".JQUERY_UI_VERSION."\"/", "'JQUERY_UI_VERSION',\"$setJQueryUIV\"", $settings);
					$settings = preg_replace("/'SHIPPING_CHARGES',\"".SHIPPING_CHARGES."\"/", "'SHIPPING_CHARGES',\"$setShipping\"", $settings);
					$settings = preg_replace("/'SHIPPING_CHARGES_LIMIT',\"".SHIPPING_CHARGES_LIMIT."\"/", "'SHIPPING_CHARGES_LIMIT',\"$setShipLimit\"", $settings);
					$settings = preg_replace("/'ADMIN_THEME',\"".ADMIN_THEME."\"/", "'ADMIN_THEME',\"$setAdminTheme\"", $settings);
					$settings = preg_replace("/'ADMIN_HTTPS_PROTOCOL',".parent::boolToStr(ADMIN_HTTPS_PROTOCOL)."/", "'ADMIN_HTTPS_PROTOCOL',$setHttps", $settings);
					$settings = preg_replace("/'DEBUG',".parent::boolToStr(DEBUG)."/", "'DEBUG',$debug", $settings);
					$settings = preg_replace("/'EH_EMAIL_NOTIFICATION',".parent::boolToStr(EH_EMAIL_NOTIFICATION)."/", "'EH_EMAIL_NOTIFICATION',$setErrorNote", $settings);
					$settings = preg_replace("/'EH_ADMIN_EMAIL',\"".EH_ADMIN_EMAIL."\"/", "'EH_ADMIN_EMAIL',\"$setErrorMail\"", $settings);
				}
				
				
				$settings = preg_replace("/'SITE_AUTHOR',\"".SITE_AUTHOR."\"/", "'SITE_AUTHOR',\"$setSiteAuthor\"", $settings);
				$settings = preg_replace("/'CONTACT_EMAIL',\"".CONTACT_EMAIL."\"/", "'CONTACT_EMAIL',\"$setConMail\"", $settings);
				$settings = preg_replace("/'ORDER_EMAIL',\"".ORDER_EMAIL."\"/", "'ORDER_EMAIL',\"$setOrderMail\"", $settings);
				$settings = preg_replace("/'AUTO_MAIL_EMAIL',\"".AUTO_MAIL_EMAIL."\"/", "'AUTO_MAIL_EMAIL',\"$setAutoMail\"", $settings);
				$settings = preg_replace("/'NEWSLETTER_EMAIL',\"".NEWSLETTER_EMAIL."\"/", "'NEWSLETTER_EMAIL',\"$setNewslMail\"", $settings);
				$settings = preg_replace("/'GBOOK_NOTIFY_EMAIL',\"".GBOOK_NOTIFY_EMAIL."\"/", "'GBOOK_NOTIFY_EMAIL',\"$setGbMail\"", $settings);
				$settings = preg_replace("/'COMMENTS_NOTIFY_EMAIL',\"".COMMENTS_NOTIFY_EMAIL."\"/", "'COMMENTS_NOTIFY_EMAIL',\"$setComMail\"", $settings);
				$settings = preg_replace("/'AUTO_MAIL_AUTHOR',\"".AUTO_MAIL_AUTHOR."\"/", "'AUTO_MAIL_AUTHOR',\"$setAutoAuth\"", $settings);
				$settings = preg_replace("/'NEWSLETTER_AUTHOR',\"".NEWSLETTER_AUTHOR."\"/", "'NEWSLETTER_AUTHOR',\"$setNewslAuth\"", $settings);
				$settings = preg_replace("/'SMTP_HOST',\"".SMTP_HOST."\"/", "'SMTP_HOST',\"$setSmtpHost\"", $settings);
				$settings = preg_replace("/'SMTP_PORT',".SMTP_PORT."/", "'SMTP_PORT',$setSmtpPort", $settings);
				$settings = preg_replace("/'SMTP_MAIL',\"".SMTP_MAIL."\"/", "'SMTP_MAIL',\"$setSmtpMail\"", $settings);
				$settings = preg_replace("/'SMTP_USER',\"".SMTP_USER."\"/", "'SMTP_USER',\"$setSmtpUser\"", $settings);
				$settings = preg_replace("/'SMTP_PASS',\"".SMTP_PASS."\"/", "'SMTP_PASS',\"$setSmtpPass\"", $settings);
				$settings = preg_replace("/'FILE_UPLOAD_METHOD',\"".FILE_UPLOAD_METHOD."\"/", "'FILE_UPLOAD_METHOD',\"$setFileupload\"", $settings);
				$settings = preg_replace("/'USE_FILES_FOLDER',".parent::boolToStr(USE_FILES_FOLDER)."/", "'USE_FILES_FOLDER',$setFiles", $settings);
				$settings = preg_replace("/'IMG_WIDTH',".IMG_WIDTH."/", "'IMG_WIDTH',$setImgWidth", $settings);
				$settings = preg_replace("/'IMG_HEIGHT',".IMG_HEIGHT."/", "'IMG_HEIGHT',$setImgHeight", $settings);
				$settings = preg_replace("/'THUMB_SIZE',".THUMB_SIZE."/", "'THUMB_SIZE',$setThumbSize", $settings);
				$settings = preg_replace("/'DEF_PLAYER_WIDTH',".DEF_PLAYER_WIDTH."/", "'DEF_PLAYER_WIDTH',$setPlayerWidth", $settings);
				$settings = preg_replace("/'JQUERY_UI_THEME',\"".JQUERY_UI_THEME."\"/", "'JQUERY_UI_THEME',\"$setJQueryUIT\"", $settings);
				$settings = preg_replace("/'ADMIN_SKIN',\"".ADMIN_SKIN."\"/", "'ADMIN_SKIN',\"$setAdminSkin\"", $settings);
				
				
				if(!@file_put_contents(PROJECT_DOC_ROOT . '/inc/settings.php', $settings)) {
					$this->setSessionVar('notice', "{s_javascript:settingserror}");
					die("could not write settings file");
				}
				else {
					$this->setSessionVar('notice', "{s_notice:takechange}");
					header("location:" . ADMIN_HTTP_ROOT . "?task=settings");
					exit;
				}
			}
		}
		
		
		// Submit-Buttonpanel
		$submit =	'<li class="submit change">' . PHP_EOL;
		
		// Button submit (new)
		$btnDefs	= array(	"type"		=> "submit",
								"name"		=> "submitSettings",
								"class"		=> "change",
								"value"		=> "{s_button:takechange}",
								"icon"		=> "ok"
							);
		
		$submit	.=	parent::getButton($btnDefs);
		
		$submit .=	'</li>' . PHP_EOL;
		
		
		// Notifications
		// Notice
		$this->adminContent .=	$this->notice;

			
		// Settingsform
		$this->adminContent .=	'<form action="' . $this->formAction . '" method="post" data-getcontent="fullpage">' . PHP_EOL .
								'<input type="hidden" name="submitAll" value="true" />' . PHP_EOL;
							

		// Falls Admin
		if($this->adminLog) {
		
			// Spracheinstellungen
			$this->adminContent .=	'<h2 id="setlang" class="toggleNext cc-section-heading cc-h2' . ($blockSubmit ? '' : ' hideNext') . '">{s_header:setlang}</h2>' . PHP_EOL . 
									'<ul class="framedItems"><li>' . PHP_EOL;
							

			$langOptions = "";

			// Admin-Sprachenauswahl
			foreach($GLOBALS['adminLangs'] as $key => $value) {
							
				$langOptions .= '<option value="' . $key . '"' . PHP_EOL;
				
				if($key == DEF_ADMIN_LANG) {
					$langOptions .= ' selected="selected"' . PHP_EOL;
				}
				
				$langOptions .= '>' . $value . '</option>' . PHP_EOL;
			}

			$this->adminContent .=	'<label>{s_label:adminlang}</label>' . PHP_EOL .
									'<div class="settingsOpt"><select name="adminLang" id="adminLang">' . PHP_EOL . 
									$langOptions . PHP_EOL .  
									'</select>' . PHP_EOL;


			$this->adminContent .=	'</div>' . PHP_EOL .
									'<label>{s_label:timezone}</label>' . PHP_EOL .
									'<div class="settingsOpt"><select name="timeZone" id="timeZone" onChange="$(this).closest(\'form\').children(\'input[name=&quot;submitAll&quot;]\').attr(\'value\',\'false\'); $(this).closest(\'form\').submit();">' . PHP_EOL . 
									$zoneOptions . PHP_EOL .  
									'</select>' . PHP_EOL . 
									'<select name="subTimeZone" id="subTimeZone">' . PHP_EOL . 
									$subZoneOptions . PHP_EOL .  
									'</select>' . PHP_EOL;

			$this->adminContent .= 	'<br class="clearfloat" />' . PHP_EOL .
									'</div>' . PHP_EOL .
									'<br class="clearfloat" />' . PHP_EOL .
									'</li>'.
									$submit . 
									'</ul>' . PHP_EOL;
		}

		// Website-Einstellungen
		$this->adminContent .=	'<h2 id="setweb" class="toggleNext hideNext cc-section-heading cc-h2">{s_header:setpage}</h2>' . PHP_EOL . 
								'<ul class="framedItems">' . PHP_EOL;
							
		$this->adminContent .=	'<h3 class="cc-h3 toggle">{s_header:setpage}</h3>' . PHP_EOL;

		$this->adminContent .=	'<li>' . PHP_EOL;
		
		// Falls Admin
		if($this->adminLog) {
		
			// Website Live-Betrieb
			$this->adminContent .=	'<label>{s_label:setlive}</label>' . PHP_EOL .
									'<div class="settingsOpt"><select name="setlive" id="setlive">' . PHP_EOL . 
									'<option value="false"' . (WEBSITE_LIVE === false ? ' selected="selected"' : '') . '>{s_option:inactive}</option>' . PHP_EOL .
									'<option value="true"' . (WEBSITE_LIVE === true ? ' selected="selected"' : '') . '>{s_option:active}</option>' . PHP_EOL .
									'</select>' . PHP_EOL .
									'</div>' . PHP_EOL;
		}

		// Website-Author
		$this->adminContent .=	'<label>{s_label:setsiteauthor}</label>' . PHP_EOL .
							'<div class="settingsOpt"><input type="text" name="setsiteauthor" id="setsiteauthor" maxlength="50" value="'.SITE_AUTHOR.'" /></div>' . PHP_EOL;
		

		// Falls Admin
		if($this->adminLog) {
		
			// Url-Ext
			$this->adminContent .=	'<label>{s_label:setext}</label>' . PHP_EOL .
								'<div class="settingsOpt"><input type="text" name="urlext" id="urlext" maxlength="10" value="'.substr(PAGE_EXT, 1).'" /></div>' . PHP_EOL;
			
			// Website-Titelsuffix
			$this->adminContent .=	'<label>{s_label:settitle}</label>' . PHP_EOL .
								'<div class="settingsOpt"><input type="text" name="htmltitle" id="htmltitle" maxlength="50" value="'.HTML_TITLE.'" /></div>' . PHP_EOL;
			
			// Cache
			$this->adminContent .=	'<label>{s_label:setcache}</label>' . PHP_EOL .
								'<div class="settingsOpt"><select name="setcache" id="setcache">' . PHP_EOL . 
								'<option value="false"' . (CACHE === false ? ' selected="selected"' : '') . '>{s_option:inactive}</option>' . PHP_EOL .
								'<option value="true"' . (CACHE === true ? ' selected="selected"' : '') . '>{s_option:active}</option>' . PHP_EOL .
								'</select>' . PHP_EOL .
								'</div>' . PHP_EOL;
			
			// HTML5
			$this->adminContent .=	'<label>HTML5</label>' . PHP_EOL .
								'<div class="settingsOpt"><select name="sethtml5" id="sethtml5">' . PHP_EOL . 
								'<option value="false"' . (HTML5 === false ? ' selected="selected"' : '') . '>{s_option:inactive}</option>' . PHP_EOL .
								'<option value="true"' . (HTML5 === true ? ' selected="selected"' : '') . '>{s_option:active}</option>' . PHP_EOL .
								'</select>' . PHP_EOL .
								'</div>' . PHP_EOL;
		}
		
		// Sitemap aktualisieren
		$this->adminContent .=	'<label>{s_label:setsitemap} (sitemap.xml)</label>' . PHP_EOL .
								'<div class="settingsOpt">' . PHP_EOL;
		
		// Button google sitemap page
		$btnDefs	= array(	"href"		=> 'http://www.google.com/webmasters/tools/ping?sitemap=' . PROJECT_HTTP_ROOT . '/sitemap.xml',
								"class"		=> "refreshSitemap",
								"text"		=> "google",
								"icon"		=> 'sitemap',
								"attr"		=> 'target="_blank" onclick=\'var sitemapbutton = $(this); jConfirm(ln.confirmsitemap, ln.confirmtitle, function(result){ if(result === true){ sitemapbutton.next(".cc-icons").addClass("on cc-icon-ok").removeClass("off cc-icon-blocked"); window.open(sitemapbutton.attr("href"), "", "top:0,left=0"); }else{ return false; } }); return false; \''
							);
	
		$this->adminContent .=	parent::getButtonLink($btnDefs);
		
		$this->adminContent .=	parent::getIcon("cancel", "check off");
		$this->adminContent .=	'</div>' . PHP_EOL;
		
		$this->adminContent .=	'<div class="settingsOpt marginTop">' . PHP_EOL;
		
		// Button bing sitemap page
		$btnDefs	= array(	"href"		=> "http://www.bing.com/webmaster/ping.aspx?sitemap=" . PROJECT_HTTP_ROOT . "/sitemap.xml",
								"class"		=> "refreshSitemap",
								"text"		=> "bing",
								"icon"		=> 'sitemap',
								"attr"		=> 'target="_blank" onclick=\'var sitemapbutton = $(this); jConfirm(ln.confirmsitemap, ln.confirmtitle, function(result){ if(result === true){ sitemapbutton.next(".cc-icons").addClass("on cc-icon-ok").removeClass("off cc-icon-blocked"); window.open(sitemapbutton.attr("href"), "", "top:0,left=0"); }else{ return false; } }); return false; \''
							);
	
		$this->adminContent .=	parent::getButtonLink($btnDefs);
		
		$this->adminContent .=	parent::getIcon("cancel", "check off");
		
		$this->adminContent .=	'</div>' . PHP_EOL;

		$this->adminContent .=	'<br class="clearfloat" />' . PHP_EOL .
								'</li>' . PHP_EOL;

		// Statistiken
		$this->adminContent .=	'<h3 class="cc-h3 toggle">{s_label:setstats}</h3>' . PHP_EOL .
								// Intern
								'<li><h4 class="cc-h4">{s_text:internal}</h4>' . PHP_EOL;


		// Falls Admin
		if($this->adminLog) {
		
		$this->adminContent .=	'<label>{s_label:setlogging}</label>' . PHP_EOL .
								'<div class="settingsOpt doubleRow"><select name="setlogging" id="setlogging">' . PHP_EOL . 
								'<option value="false"' . (CONCISE_LOG === false ? ' selected="selected"' : '') . '>{s_option:inactive}</option>' . PHP_EOL .
								'<option value="true"' . (CONCISE_LOG === true ? ' selected="selected"' : '') . '>{s_option:active}</option>' . PHP_EOL .
								'</select>' . PHP_EOL .
								'</div>' . PHP_EOL;		
		}
		
		// Loggin ausschließen (intern)
		$this->adminContent .=	'<label>{s_label:setexcludelog}</label>' . PHP_EOL .
								'<div class="settingsOpt doubleRow">' . PHP_EOL;
		
		// Button set cookie
		$btnDefs	= array(	"class"		=> "setCCLogCookie",
								"value"		=> "{s_label:setsetcookie}",
								"icon"		=> "previewno",
								"attr"		=> 'onclick=\'if($.cookie("conciseLogging_off", "true", ' . (time()+60*60*24*365*10) . ', "/")){ $(this).next(".check").addClass("on cc-icon-ok").removeClass("off cc-icon-blocked"); jAlert(ln.settingsnolog, ln.alerttitle); }\''
							);
		
		$this->adminContent	.=	parent::getButton($btnDefs);
		
		$this->adminContent	.=	parent::getIcon(isset($GLOBALS['_COOKIE']['conciseLogging_off']) ? 'ok' : 'cancel', 'check ' . (isset($GLOBALS['_COOKIE']['conciseLogging_off']) ? 'on' : 'off')) .
								'</div>' . PHP_EOL;
		
		// Extern
		$this->adminContent .=	'<br class="clearfloat" />' . PHP_EOL .
								'<h4 class="cc-h4">{s_text:external}</h4>' . PHP_EOL;

		// Falls Admin
		if($this->adminLog) {		
							
			// Analytics
			$this->adminContent .=	'<label>Analytics &quot;{s_label:extern}&quot;<br />{s_label:setanalytics}</label>' . PHP_EOL .
								'<div class="settingsOpt doubleRow"><select name="setanalytics" id="setanalytics">' . PHP_EOL . 
								'<option value="false"' . (ANALYTICS === false ? ' selected="selected"' : '') . '>{s_option:inactive}</option>' . PHP_EOL .
								'<option value="true"' . (ANALYTICS === true ? ' selected="selected"' : '') . '>{s_option:active}</option>' . PHP_EOL .
								'</select>' . PHP_EOL .
								'</div>' . PHP_EOL;
								
			// Analytics-Codepos.
			$this->adminContent .=	'<label>{s_label:setanalyticspos}</label>' . PHP_EOL .
								'<div class="settingsOpt doubleRow"><select name="setanalyticspos" id="setanalyticspos">' . PHP_EOL . 
								'<option value="head"' . (ANALYTICS_POSITION == "head" ? ' selected="selected"' : '') . '>&lt;head&gt;</option>' . PHP_EOL .
								'<option value="body"' . (ANALYTICS_POSITION == "body" ? ' selected="selected"' : '') . '>&lt;body&gt;</option>' . PHP_EOL .
								'</select>' . PHP_EOL .
								'</div>' . PHP_EOL;

			// Falls analytics.js-Datei vorhanden
			if(file_exists(PROJECT_DOC_ROOT . '/access/js/analytics.js')) {

				$trackingCode	= file_get_contents(PROJECT_DOC_ROOT . '/access/js/analytics.js');
				$this->adminContent .=	'<label>Tracking-Code</label>' . PHP_EOL .
										'<div class="settingsOpt">' .
										'<textarea name="setanalyticscode" class="analyticsCode code" rows="10" accept-charset="utf8">' . $trackingCode . PHP_EOL .
										'</textarea>' . PHP_EOL .
										'<br class="clearfloat" />' . PHP_EOL .					
										'</div>' . PHP_EOL;
			}
		}
		
		// Filter-Tracking (extern)
		$this->adminContent .=	'<label>Filter Tracking (Google)</label>' . PHP_EOL .
								'<div class="settingsOpt">' . PHP_EOL;
		
		// Button filter tracking
		$btnDefs	= array(	"href"		=> PROJECT_HTTP_ROOT . '/_filter-tracking.html',
								"class"		=> "filterTracking",
								"text"		=> "{s_label:setsetcookie}",
								"icon"		=> 'previewno',
								"attr"		=> 'target="_blank" onclick=\'var filterbutton = $(this); jConfirm(ln.confirmfilter, ln.confirmtitle, function(result){ if(result === true){ filterbutton.next(".check").addClass("on cc-icon-ok").removeClass("off cc-icon-blocked"); window.open(filterbutton.attr("href"), "", "top:0,left=0"); }else{ return false; } }); return false; \''
							);
	
		$this->adminContent .=	parent::getButtonLink($btnDefs);
								
		$this->adminContent .=	parent::getIcon(isset($GLOBALS['_COOKIE']['conciseLogging_off']) ? 'ok' : 'cancel', 'check ' . (isset($GLOBALS['_COOKIE']['conciseLogging_off']) ? 'on' : 'off')) .
								'</div>' . PHP_EOL;
							
		$this->adminContent .= 	'<br class="clearfloat" />' . PHP_EOL .
								'</li>' .
								$submit .
								'</ul>' . PHP_EOL;


		// Falls Admin
		if($this->adminLog) {		
							
			// Benutzereinstellungen
			$this->adminContent .=	'<h2 id="setuser" class="toggleNext hideNext cc-section-heading cc-h2">{s_header:setuser}</h2>' . PHP_EOL . 
									'<ul class="framedItems"><li>' . PHP_EOL;
			
			
			// eigenen Benutzergruppen
			$this->adminContent .=	'<label>{s_label:setusergroup}</label>' . PHP_EOL .
									'<div class="settingsOpt">' .
									'<textarea name="setusergroups" id="setusergroups" class="ownUserGroups code" rows="5">' . implode("\n", $ownUserGroups) . PHP_EOL .
									'</textarea>' . PHP_EOL .
									'<br class="clearfloat" />' . PHP_EOL .					
									'</div>' . PHP_EOL;
		
			// Script
			$this->adminContent	.= $this->getScriptTag();
			
			
			// Registrierung
			$this->adminContent .=	'<label>{s_label:setreg}</label>' . PHP_EOL .
									'<div class="settingsOpt"><select name="setreg" id="setreg">' . PHP_EOL . 
									'<option value="none"' . (REGISTRATION_TYPE == "none" ? ' selected="selected"' : '') . '>{s_option:inactive}</option>' . PHP_EOL .
									'<option value="newsletter"' . (REGISTRATION_TYPE == "newsletter" ? ' selected="selected"' : '') . '>{s_option:newsl}</option>' . PHP_EOL .
									'<option value="account"' . (REGISTRATION_TYPE == "account" ? ' selected="selected"' : '') . '>{s_option:account}</option>' . PHP_EOL .
									'<option value="shopuser"' . (REGISTRATION_TYPE == "shopuser" ? ' selected="selected"' : '') . '>{s_option:shopuser}</option>' . PHP_EOL .
									'</select>' . PHP_EOL .
									'</div>' . PHP_EOL;
								
			
			// Logging
			$this->adminContent .=	'<label>{s_label:setlog}</label>' . PHP_EOL .
									'<div class="settingsOpt"><select name="setlog" id="setlog">' . PHP_EOL . 
									'<option value="default"' . (LOGFORM_TYPE == "default" ? ' selected="selected"' : '') . '>default</option>' . PHP_EOL .
									'<option value="forgotPass"' . (LOGFORM_TYPE == "forgotPass" ? ' selected="selected"' : '') . '>forgotPass</option>' . PHP_EOL .
									'</select>' . PHP_EOL .
									'</div>' . PHP_EOL;
								
			$this->adminContent .=	'<label>{s_label:setremuser}</label>' . PHP_EOL .
									'<div class="settingsOpt"><select name="setremuser" id="setremuser">' . PHP_EOL . 
									'<option value="false"' . (REMEMBER_USER === false ? ' selected="selected"' : '') . '>{s_option:inactive}</option>' . PHP_EOL .
									'<option value="true"' . (REMEMBER_USER === true ? ' selected="selected"' : '') . '>{s_option:active}</option>' . PHP_EOL .
									'</select>' . PHP_EOL .
									'</div>' . PHP_EOL;

			$this->adminContent .=	'<label>{s_label:setbadlog}</label>' . PHP_EOL .
									'<div class="settingsOpt"><select name="setbadlog" id="setbadlog">' . PHP_EOL;

			$options = 1;

			while($options <= 10) {
				
				$this->adminContent .=	'<option value="'.$options.'"' . (MAX_ALLOWED_BAD_LOGINS == $options ? ' selected="selected"' : '') . '>'.$options.'</option>' . PHP_EOL;
				
				$options++;
			}
			$this->adminContent .=	'<option value="50"' . (MAX_ALLOWED_BAD_LOGINS == 50 ? ' selected="selected"' : '') . '>50</option>' . PHP_EOL .
									'<option value="100"' . (MAX_ALLOWED_BAD_LOGINS == 100 ? ' selected="selected"' : '') . '>100</option>' . PHP_EOL .
									'<option value="500"' . (MAX_ALLOWED_BAD_LOGINS == 500 ? ' selected="selected"' : '') . '>500</option>' . PHP_EOL;

			$this->adminContent .=	'</select>' . PHP_EOL .
									'</div>' . PHP_EOL;
								
			$this->adminContent .=	'<label>{s_label:setbantime}</label>' . PHP_EOL .
									'<div class="settingsOpt doubleRow"><select name="setbantime" id="setbantime">' . PHP_EOL;

			$options = array(5, 15, 30, 60, 120, 240, 480, 1440);

			foreach($options as $opt) {
				
				$this->adminContent .=	'<option value="'.$opt.'"' . (LOGIN_BAN_TIME == ($opt * 60) ? ' selected="selected"' : '') . '>'.$opt.'</option>' . PHP_EOL;
				
			}

			$this->adminContent .=	'</select>' . PHP_EOL .
									'</div>' . PHP_EOL;
								
			$this->adminContent .=	'<label>{s_label:setses}</label>' . PHP_EOL .
									'<div class="settingsOpt"><select name="setsession" id="setsession">' . PHP_EOL;

			$options = array(15, 30, 60, 120, 240, 480);

			foreach($options as $opt) {
				
				$sutArr			 	 = explode(" ", SESSION_UPTIME);
				$this->adminContent .=	'<option value="'.$opt.'"' . (substr(reset($sutArr), 1) == $opt ? ' selected="selected"' : '') . '>'.$opt.'</option>' . PHP_EOL;
			}

			$this->adminContent .=	'</select>' . PHP_EOL .
									'</div>' . PHP_EOL;
								
			$this->adminContent .= 	'<br class="clearfloat" />' . PHP_EOL .
									'</li>' .
									$submit .
									'</ul>' . PHP_EOL;
		
		} // Ende if admin

		
		// Authoren/E-Mails
		$this->adminContent .=	'<h2 id="setauthors" class="toggleNext hideNext cc-section-heading cc-h2">{s_header:setmail}</h2>' . PHP_EOL . 
								'<ul class="framedItems">' . PHP_EOL;
							
		$this->adminContent .=	'<h3 class="cc-h3 toggle">{s_header:setmail}</h3>' . PHP_EOL;
							
		$this->adminContent .=	'<li><label>{s_label:setconmail}</label>' . PHP_EOL .
							'<div class="settingsOpt"><input type="text" name="setconmail" id="setconmail" class="email" maxlength="256" value="'.CONTACT_EMAIL.'" /></div>' . PHP_EOL;
							
		$this->adminContent .=	'<label>{s_label:setordermail}</label>' . PHP_EOL .
							'<div class="settingsOpt"><input type="text" name="setordermail" id="setordermail" class="email" maxlength="256" value="'.ORDER_EMAIL.'" /></div>' . PHP_EOL;
							
		$this->adminContent .=	'<label>{s_label:setautomail}</label>' . PHP_EOL .
							'<div class="settingsOpt doubleRow"><input type="text" name="setautomail" id="setautomail" class="email" maxlength="256" value="'.AUTO_MAIL_EMAIL.'" /></div>' . PHP_EOL;
							
		$this->adminContent .=	'<label>{s_label:setnewslmail}</label>' . PHP_EOL .
							'<div class="settingsOpt"><input type="text" name="setnewslmail" id="setnewslmail" class="email" maxlength="256" value="'.NEWSLETTER_EMAIL.'" /></div>' . PHP_EOL;
							
		$this->adminContent .=	'<label>{s_label:setgbmail}</label>' . PHP_EOL .
							'<div class="settingsOpt doubleRow"><input type="text" name="setgbmail" id="setgbmail" class="email" maxlength="256" value="'.GBOOK_NOTIFY_EMAIL.'" /></div>' . PHP_EOL;
							
		$this->adminContent .=	'<label>{s_label:setcommail}</label>' . PHP_EOL .
							'<div class="settingsOpt doubleRow"><input type="text" name="setcommail" id="setcommail" class="email" maxlength="256" value="'.COMMENTS_NOTIFY_EMAIL.'" /></div>' . PHP_EOL;
							
		$this->adminContent .=	'<label>{s_label:setautoauth}</label>' . PHP_EOL .
							'<div class="settingsOpt"><input type="text" name="setautoauth" id="setautoauth" maxlength="256" value="'.AUTO_MAIL_AUTHOR.'" /></div>' . PHP_EOL;
							
		$this->adminContent .=	'<label>{s_label:setnewslauth}</label>' . PHP_EOL .
							'<div class="settingsOpt"><input type="text" name="setnewslauth" id="setnewslauth" maxlength="256" value="'.NEWSLETTER_AUTHOR.'" /></div>' . PHP_EOL;
							
		$this->adminContent .=	'<br class="clearfloat" />' . PHP_EOL .

							'</li>' . PHP_EOL;


		// SMTP
		$this->adminContent .=	'<h3 class="cc-h3 toggle">SMTP {s_nav:adminsettings}</h3>' . PHP_EOL;

		$this->adminContent .=	'<li>' . PHP_EOL .
							'<label>{s_label:setsmtphost}</label>' . PHP_EOL .
							'<div class="settingsOpt"><input type="text" name="setsmtphost" id="setsmtphost" maxlength="256" value="'.SMTP_HOST.'" /></div>' . PHP_EOL .
							'<label>{s_label:setsmtpport}</label>' . PHP_EOL .
							'<div class="settingsOpt"><input type="text" name="setsmtpport" id="setsmtpport" maxlength="3" value="'.SMTP_PORT.'" /></div>' . PHP_EOL .
							'<label>{s_label:setsmtpmail}</label>' . PHP_EOL .
							'<div class="settingsOpt"><input type="text" name="setsmtpmail" id="setsmtpmail" maxlength="256" value="'.SMTP_MAIL.'" class="email" /></div>' . PHP_EOL .
							'<label>{s_label:setsmtpuser}</label>' . PHP_EOL .
							'<div class="settingsOpt"><input type="text" name="setsmtpuser" id="setsmtpuser" maxlength="256" value="'.SMTP_USER.'" /></div>' . PHP_EOL .
							'<label>{s_label:setsmtppass}</label>' . PHP_EOL .
							'<div class="settingsOpt"><input type="password" name="setsmtppass" id="setsmtppass" maxlength="256" value="'.SMTP_PASS.'" /></div>' . PHP_EOL;

		$this->adminContent .=	'<br class="clearfloat" />' . PHP_EOL .
								'</li>' .
								$submit .
								'</ul>' . PHP_EOL;


		// Modul-Einstellungen
		$this->adminContent .=	'<h2 id="setmod" class="toggleNext hideNext cc-section-heading cc-h2">{s_header:setmod}</h2>' . PHP_EOL . 
								'<ul class="framedItems">' . PHP_EOL;
							

		// Falls Admin
		if($this->adminLog) {		
							
			// Suche
			$this->adminContent .=	'<h3 class="cc-h3 toggle">{s_header:setsearch}</h3>' . PHP_EOL;

			$this->adminContent .=	'<li><label>{s_label:setsearch}</label>' . PHP_EOL .
								'<div class="settingsOpt"><select name="setsearch" id="setsearch">' . PHP_EOL . 
								'<option value="none"' . (SEARCH_TYPE == "none" ? ' selected="selected"' : '') . '>{s_option:inactive}</option>' . PHP_EOL .
								'<option value="LIKE"' . (SEARCH_TYPE == "LIKE" ? ' selected="selected"' : '') . '>LIKE</option>' . PHP_EOL .
								'<option value="MATCH"' . (SEARCH_TYPE == "MATCH" ? ' selected="selected"' : '') . '>MATCH</option>' . PHP_EOL .
								'</select>' . PHP_EOL .
								'</div>' . PHP_EOL;
								
			$this->adminContent .=	'<label>{s_label:setsearchres}</label>' . PHP_EOL .
								'<div class="settingsOpt"><select name="setsearchres" id="setsearchres">' . PHP_EOL;

			$options = array(1, 2, 5, 10, 15, 20, 50, 100);

			foreach($options as $opt) {
				
				$this->adminContent .=	'<option value="'.$opt.'"' . (SEARCH_MAX_ROWS == $opt ? ' selected="selected"' : '') . '>'.$opt.'</option>' . PHP_EOL;
			}

			$this->adminContent .=	'</select>' . PHP_EOL .
								'</div>' . PHP_EOL;
								
			$this->adminContent .=	'<label>{s_label:setsearchtables}</label>' . PHP_EOL .
								'<div class="settingsOpt doubleRow">' . PHP_EOL .
								'<label class="markBox"><input type="checkbox" id="searchtable1" name="setsearchtables[0]"' . (in_array("pages", $searchTables) ? ' checked="checked"' : '') . ' /></label>' .
								'<label class="inline-label" for="searchtable1">pages</label>' . PHP_EOL .
								'<label class="markBox"><input type="checkbox" id="searchtable2" name="setsearchtables[1]"' . (in_array("articles", $searchTables) ? ' checked="checked"' : '') . ' /></label>' .
								'<label class="inline-label" for="searchtable2">articles</label>' . PHP_EOL .
								'<label class="markBox"><input type="checkbox" id="searchtable3" name="setsearchtables[2]"' . (in_array("news", $searchTables) ? ' checked="checked"' : '') . ' /></label>' .
								'<label class="inline-label" for="searchtable3">news</label>' . PHP_EOL .
								'<label class="markBox"><input type="checkbox" id="searchtable4" name="setsearchtables[3]"' . (in_array("planner", $searchTables) ? ' checked="checked"' : '') . ' /></label>' .
								'<label class="inline-label" for="searchtable4">planner</label>' . PHP_EOL .
								'</div>' . PHP_EOL .
								'<br class="clearfloat" />' . PHP_EOL .					
								'</li>' . PHP_EOL;
		}
		
		
		// Module
		$this->adminContent .=	'<h3 class="cc-h3 toggle">{s_header:setmod}</h3>' . PHP_EOL .
								'<li>' . PHP_EOL;


		// Falls Admin
		if($this->adminLog) {		
							
			$this->adminContent .=	'<label>{s_label:setinstpub}</label>' . PHP_EOL .
								'<div class="settingsOpt doubleRow"><select name="setinstpub" id="setinstpub">' . PHP_EOL . 
								'<option value="true"' . (DATA_PUBLISH_DELAY === true ? ' selected="selected"' : '') . '>{s_option:inactive}</option>' . PHP_EOL .
								'<option value="false"' . (DATA_PUBLISH_DELAY === false ? ' selected="selected"' : '') . '>{s_option:active}</option>' . PHP_EOL .
								'</select>' . PHP_EOL .
								'</div>' . PHP_EOL;

			$this->adminContent .=	'<label>{s_label:setgbres}</label>' . PHP_EOL .
								'<div class="settingsOpt"><select name="setgbres" id="setgbres">' . PHP_EOL;

			$options = array(1, 2, 5, 10, 15, 20, 50, 100);

			foreach($options as $opt) {
				
				$this->adminContent .=	'<option value="'.$opt.'"' . (GBOOK_MAX_ROWS == $opt ? ' selected="selected"' : '') . '>'.$opt.'</option>' . PHP_EOL;
			}

			$this->adminContent .=	'</select>' . PHP_EOL .
								'</div>' . PHP_EOL;
								
			$this->adminContent .=	'<label>{s_label:setgbmod}</label>' . PHP_EOL .
								'<div class="settingsOpt"><select name="setgbmod" id="setgbmod">' . PHP_EOL . 
								'<option value="false"' . (GBOOK_MODERATE === false ? ' selected="selected"' : '') . '>{s_option:inactive}</option>' . PHP_EOL .
								'<option value="true"' . (GBOOK_MODERATE === true ? ' selected="selected"' : '') . '>{s_option:active}</option>' . PHP_EOL .
								'</select>' . PHP_EOL .
								'</div>' . PHP_EOL;
								

			$this->adminContent .=	'<label>{s_label:setcomres}</label>' . PHP_EOL .
								'<div class="settingsOpt"><select name="setcomres" id="setcomres">' . PHP_EOL;

			$options = array(1, 2, 5, 10, 15, 20, 50, 100);

			foreach($options as $opt) {
				
				if($opt <= MAX_COMMENTS)
					$this->adminContent .=	'<option value="'.$opt.'"' . (COMMENTS_MAX_ROWS == $opt ? ' selected="selected"' : '') . '>'.$opt.'</option>' . PHP_EOL;
			}

			$this->adminContent .=	'</select>' . PHP_EOL .
								'</div>' . PHP_EOL;
								
			$this->adminContent .=	'<label>{s_label:setcommod}</label>' . PHP_EOL .
								'<div class="settingsOpt"><select name="setcommod" id="setcommod">' . PHP_EOL . 
								'<option value="false"' . (COMMENTS_MODERATE === false ? ' selected="selected"' : '') . '>{s_option:inactive}</option>' . PHP_EOL .
								'<option value="true"' . (COMMENTS_MODERATE === true ? ' selected="selected"' : '') . '>{s_option:active}</option>' . PHP_EOL .
								'</select>' . PHP_EOL .
								'</div>' . PHP_EOL;

			$this->adminContent .=	'<label>{s_label:setmaxcom}</label>' . PHP_EOL .
								'<div class="settingsOpt"><select name="setmaxcom" id="setmaxcom">' . PHP_EOL;

			$options = array(5, 10, 15, 20, 50, 100);

			foreach($options as $opt) {
				
				$this->adminContent .='<option value="'.$opt.'"' . (MAX_COMMENTS == $opt ? ' selected="selected"' : '') . '>'.$opt.'</option>' . PHP_EOL;
			}

			$this->adminContent .=	'</select>' . PHP_EOL .
								'</div>' . PHP_EOL;
								
			$this->adminContent .=	'<label>{s_label:setcomtables}</label>' . PHP_EOL .
								'<div class="settingsOpt doubleRow">' . PHP_EOL .
								'<label class="markBox"><input type="checkbox" id="setcomtables1" name="setcomtables[0]"' . (in_array("gbook", $commentTables) ? ' checked="checked"' : '') . ' /></label>' .
								'<label class="inline-label" for="setcomtables1">gbook</label>' . PHP_EOL .
								'<label class="markBox"><input type="checkbox" id="setcomtables2" name="setcomtables[1]"' . (in_array("articles", $commentTables) ? ' checked="checked"' : '') . ' /></label>' .
								'<label class="inline-label" for="setcomtables2">articles</label>' . PHP_EOL .
								'<label class="markBox"><input type="checkbox" id="setcomtables3" name="setcomtables[2]"' . (in_array("news", $commentTables) ? ' checked="checked"' : '') . ' /></label>' .
								'<label class="inline-label" for="setcomtables3">news</label>' . PHP_EOL .
								'<label class="markBox"><input type="checkbox" id="setcomtables4" name="setcomtables[3]"' . (in_array("planner", $commentTables) ? ' checked="checked"' : '') . ' /></label>' .
								'<label class="inline-label" for="setcomtables4">planner</label>' . PHP_EOL .
								'</div>' . PHP_EOL;

			$this->adminContent .=	'<label>{s_label:setemotiforms}</label>' . PHP_EOL .
								'<div class="settingsOpt doubleRow">' . PHP_EOL .
								'<label class="markBox"><input type="checkbox" id="setemoforms1" name="setemoforms[0]"' . (in_array("gbook", $emoticonForms) ? ' checked="checked"' : '') . ' /></label>' .
								'<label class="inline-label" for="setemoforms1">gbook</label>' . PHP_EOL .
								'<label class="markBox"><input type="checkbox" id="setemoforms2" name="setemoforms[1]"' . (in_array("comments", $emoticonForms) ? ' checked="checked"' : '') . ' /></label>' .
								'<label class="inline-label" for="setemoforms2">comments</label>' . PHP_EOL .
								'</div>' . PHP_EOL;

			$this->adminContent .=	'<label>{s_label:setmenprefix}</label>' . PHP_EOL .
								'<div class="settingsOpt"><input type="text" name="setmenprefix" id="setmenprefix" maxlength="64" value="'.MOD_MENU_PREFIX.'" /></div>' . PHP_EOL;
		}
							
		$this->adminContent .=	'<br class="clearfloat" />' . PHP_EOL .					
								'</li>' . PHP_EOL;


		// Datei-Upload
		$this->adminContent .=	'<h3 class="cc-h3 toggle">{s_nav:adminfile}</h3>' . PHP_EOL .
								'<li>' . PHP_EOL;
		
		// File upload method
		$this->adminContent .=	'<label>{s_label:setfileupload}</label>' . PHP_EOL .
								'<div class="settingsOpt"><select name="setfileupload" id="setfileupload">' . PHP_EOL;
							
		foreach($this->uploadMethods as $upload) {
			
			$this->adminContent .='<option value="'.$upload.'"' . (FILE_UPLOAD_METHOD == $upload ? ' selected="selected"' : '') . '>'.$upload.'</option>' . PHP_EOL;
		}
							
		$this->adminContent .=	'</select></div>' . PHP_EOL;

		// Files-Ordner
		$this->adminContent .=	'<label>{s_label:setfiles}</label>' . PHP_EOL .
							'<div class="settingsOpt"><select name="setfiles" id="setfiles">' . PHP_EOL . 
							'<option value="false"' . (USE_FILES_FOLDER === false ? ' selected="selected"' : '') . '>{s_option:inactive}</option>' . PHP_EOL .
							'<option value="true"' . (USE_FILES_FOLDER === true ? ' selected="selected"' : '') . '>{s_option:active}</option>' . PHP_EOL .
							'</select>' . PHP_EOL .
							'</div>' . PHP_EOL;

		$this->adminContent .=	'<br class="clearfloat" />' . PHP_EOL .			
							'</li>' . PHP_EOL;

		// Breiten- und Höhenangaben
		$this->adminContent .=	'<h3 class="cc-h3 toggle">{s_header:setsize}</h3>' . PHP_EOL;

		$this->adminContent .=	'<li>' . PHP_EOL .
							'<label>{s_label:setimgwidth}</label>' . PHP_EOL .
							'<div class="settingsOpt"><input type="text" name="setimgwidth" id="setimgwidth" class="pixelSize" maxlength="4" value="'.IMG_WIDTH.'" /></div>' . PHP_EOL .
							'<label>{s_label:setimgheight}</label>' . PHP_EOL .
							'<div class="settingsOpt"><input type="text" name="setimgheight" id="setimgheight" class="pixelSize" maxlength="4" value="'.IMG_HEIGHT.'" /></div>' . PHP_EOL;
							
		$this->adminContent .=	'<label>{s_label:setthumbsize}</label>' . PHP_EOL .
							'<div class="settingsOpt"><input type="text" name="setthumbsize" id="setthumbsize" class="pixelSize" maxlength="4" value="'.THUMB_SIZE.'" /></div>' . PHP_EOL;
							
		$this->adminContent .=	'<label>{s_label:setplayerwidth}</label>' . PHP_EOL .
							'<div class="settingsOpt"><input type="text" name="setplayerwidth" id="setplayerwidth" class="pixelSize" maxlength="4" value="'.DEF_PLAYER_WIDTH.'" /></div>' . PHP_EOL;
							
		$this->adminContent .=	'<br class="clearfloat" />' . PHP_EOL .					
							'</li>' . PHP_EOL;


		// Falls Admin
		if($this->adminLog) {		
							
			// Themes
			$this->adminContent .=	'<h3 class="cc-h3 toggle">Themes</h3>' . PHP_EOL;

			$this->adminContent .=	'<li>' . PHP_EOL .
								'<label>{s_label:setseltheme}</label>' . PHP_EOL .
								'<div class="settingsOpt"><select name="setseltheme" id="setseltheme">' . PHP_EOL . 
								'<option value="false"' . (FE_THEME_SELECTION === false ? ' selected="selected"' : '') . '>{s_option:inactive}</option>' . PHP_EOL .
								'<option value="true"' . (FE_THEME_SELECTION === true ? ' selected="selected"' : '') . '>{s_option:active}</option>' . PHP_EOL .
								'</select>' . PHP_EOL .
								'</div>' . PHP_EOL;

			$this->adminContent .=	'<label>{s_label:setheadcode}</label>' . PHP_EOL .
								'<div class="settingsOpt">' . PHP_EOL .
								'<textarea class="code" name="setheadcode" rows="10">' . HTML_HEAD_EXT . PHP_EOL .
								'</textarea>' . PHP_EOL .
								'<br class="clearfloat" />' . PHP_EOL .					
								'</div>' . PHP_EOL .
								'<br class="clearfloat" />' . PHP_EOL .					
								'</li>' . PHP_EOL;
		}
		
		// jquery
		$this->adminContent .=	'<h3 class="cc-h3 toggle">{s_header:setjquery} & UI Themes</h3>' . PHP_EOL .
								'<li>' . PHP_EOL;


		// Falls Admin
		if($this->adminLog) {
							
			$this->adminContent .=	'<label>{s_label:setjqueryv}</label>' . PHP_EOL .
								'<div class="settingsOpt"><input type="text" name="setjqueryv" id="setjqueryv" maxlength="10" value="'.JQUERY_VERSION.'" /></div>' . PHP_EOL;
								
			$this->adminContent .=	'<label>{s_label:setjqueryuiv}</label>' . PHP_EOL .
								'<div class="settingsOpt"><input type="text" name="setjqueryuiv" id="setjqueryuiv" maxlength="10" value="'.JQUERY_UI_VERSION.'" /></div>' . PHP_EOL;
		}
		
		$this->adminContent .=	'<label>{s_label:setjqueryuit}</label>' . PHP_EOL .
								'<div class="settingsOpt"><select name="setjqueryuit" id="setjqueryuit">' . PHP_EOL;
							
		foreach($selUIThemes as $theme) {
			
			$this->adminContent .='<option value="'.$theme.'"' . (JQUERY_UI_THEME == $theme ? ' selected="selected"' : '') . '>'.$theme.'</option>' . PHP_EOL;
		}
							
		$this->adminContent .=	'</select></div>' . PHP_EOL .
								'<br class="clearfloat" />' . PHP_EOL .			
								'</li>' . PHP_EOL;


		// Falls Admin
		if($this->adminLog) {

			// Bestellformular
			$this->adminContent .=	'<h3 class="cc-h3 toggle">{s_header:setorder}</h3>' . PHP_EOL;
			
			$this->adminContent .=	'<li>' . PHP_EOL;			
							
			$this->adminContent .=	'<label>{s_label:setmaxorder}</label>' . PHP_EOL .
									'<div class="settingsOpt doubleRow"><select name="setmaxorder" id="setmaxorder">' . PHP_EOL;

			$options = array(5,10,15,20,25,50,100);

			foreach($options as $option) {
				
				$this->adminContent .=	'<option value="'.$option.'"' . (MAX_ENTRIES_ORDER_FORM == $option ? ' selected="selected"' : '') . '>'.$option.'</option>' . PHP_EOL;
			}

			$this->adminContent .=	'</select>' . PHP_EOL .
									'</div>' . PHP_EOL;
								
			$this->adminContent .=	'<label>{s_label:setshoworder}</label>' . PHP_EOL .
									'<div class="settingsOpt doubleRow"><select name="setshoworder" id="setshoworder">' . PHP_EOL;

			$options = 1;

			while($options <= 20) {
				
				$this->adminContent .=	'<option value="'.$options.'"' . (SHOW_ENTRIES_ORDER_FORM == $options ? ' selected="selected"' : '') . '>'.$options.'</option>' . PHP_EOL;
				
				$options++;
			}

			$this->adminContent .=	'</select>' . PHP_EOL .
									'</div>' . PHP_EOL;
			
			// Shipping
			$this->adminContent .=	'<label>{s_label:setshipping}</label>' . PHP_EOL .
									'<div class="settingsOpt"><input type="text" name="setshipping" id="setshipping" maxlength="6" value="'.SHIPPING_CHARGES.'" /></div>' . PHP_EOL;
								
			$this->adminContent .=	'<label>{s_label:setshippinglimit}</label>' . PHP_EOL .
									'<div class="settingsOpt doubleRow"><input type="text" name="setshippinglimit" id="setshippinglimit" maxlength="6" value="'.SHIPPING_CHARGES_LIMIT.'" /></div>' . PHP_EOL;
			
			$this->adminContent .= 	'<br class="clearfloat" />' . PHP_EOL .					
									'</li>';
		}
		
		$this->adminContent .= 	$submit .
								'</ul>' . PHP_EOL;


		// System-Einstellungen
		$this->adminContent .=	'<h2 id="setsystem" class="toggleNext hideNext cc-section-heading cc-h2">{s_header:setsystem}</h2>' . PHP_EOL . 
								'<ul class="framedItems">' . PHP_EOL .
								'<li>' . PHP_EOL;

		// Falls Admin
		if($this->adminLog) {							
			
			// Admin-Theme
			$this->adminContent .=	'<label>{s_label:setadmintheme}</label>' . PHP_EOL .
									'<div class="settingsOpt">' .
									'<select name="setadmintheme" id="setadmintheme" onchange="$(this).closest(\'form\').attr(\'data-ajax\',\'false\');">' . PHP_EOL;
							
			foreach($adminThemes as $theme) {
				
				$this->adminContent .=	'<option value="'.$theme.'"' . (ADMIN_THEME == $theme ? ' selected="selected"' : '') . '>'.$theme.'</option>' . PHP_EOL;
			}
			
			$this->adminContent .=	'</select></div>' . PHP_EOL;
		}
		
		
		// Admin-Skin
		$this->adminContent .=	'<label>{s_label:setadminskin}</label>' . PHP_EOL .
								'<div class="settingsOpt">' .
								'<select name="setadminskin" id="setadminskin" onchange="$(this).closest(\'form\').attr(\'data-ajax\',\'false\');">' . PHP_EOL;
						
		foreach($this->adminSkins as $skin) {
			
			$this->adminContent .=	'<option value="'.$skin.'"' . (ADMIN_SKIN == $skin ? ' selected="selected"' : '') . '>'.$skin.'</option>' . PHP_EOL;
		}
		
		$this->adminContent .=	'</select></div>' . PHP_EOL;


		// Falls Admin
		if($this->adminLog) {
								
			// Admin-Http-Protokol
			$this->adminContent .=	'<label>{s_label:sethttps}</label>' . PHP_EOL .
									'<div class="settingsOpt"><select name="sethttps" id="sethttps">' . PHP_EOL . 
									'<option value="false"' . (ADMIN_HTTPS_PROTOCOL === false ? ' selected="selected"' : '') . '>{s_option:inactive}</option>' . PHP_EOL .
									'<option value="true"' . (ADMIN_HTTPS_PROTOCOL === true ? ' selected="selected"' : '') . '>{s_option:active}</option>' . PHP_EOL .
									'</select></div>' . PHP_EOL;
								
			// Debug-Konsole
			$this->adminContent .=	'<label>{s_label:setdebug}</label>' . PHP_EOL .
									'<div class="settingsOpt"><select name="debug" id="debug">' . PHP_EOL . 
									'<option value="false"' . (DEBUG === false ? ' selected="selected"' : '') . '>{s_option:inactive}</option>' . PHP_EOL .
									'<option value="true"' . (DEBUG === true ? ' selected="selected"' : '') . '>{s_option:active}</option>' . PHP_EOL .
									'</select></div>' . PHP_EOL;
								
			$this->adminContent .=	'<label>{s_label:seterrornotify}</label>' . PHP_EOL .
									'<div class="settingsOpt"><select name="seterrornotify" id="seterrornotify">' . PHP_EOL . 
									'<option value="false"' . (EH_EMAIL_NOTIFICATION === false ? ' selected="selected"' : '') . '>{s_option:inactive}</option>' . PHP_EOL .
									'<option value="true"' . (EH_EMAIL_NOTIFICATION === true ? ' selected="selected"' : '') . '>{s_option:active}</option>' . PHP_EOL .
									'</select></div>' . PHP_EOL;
								
			$this->adminContent .=	'<label>{s_label:seterrormail}</label>' . PHP_EOL .
									'<div class="settingsOpt doubleRow"><input type="text" name="seterrormail" id="seterrormail" class="email" maxlength="256" value="'.EH_ADMIN_EMAIL.'" /></div>' . PHP_EOL;
		}
							
		$this->adminContent .= 	'<br class="clearfloat" />' . PHP_EOL .
								'</li>' .
								$submit .
								'</ul>' . PHP_EOL;



		$this->adminContent .=	'<input type="hidden" name="token" value="' . parent::$token . '" />' . PHP_EOL .
								'</form>' . PHP_EOL .
								'</div>' . PHP_EOL .
								'<p>&nbsp;</p>' . PHP_EOL;

		$this->adminContent .=	'<div class="adminArea">' . PHP_EOL . 
								'<ul class="framedItems">' . PHP_EOL .
								'<li class="submit back">' . PHP_EOL;
		
		// Button back
		$this->adminContent .=	$this->getButtonLinkBacktomain();
				
		$this->adminContent .=	'<br class="clearfloat" />' . PHP_EOL .
								'</li>' . PHP_EOL . 
								'</ul>' . PHP_EOL . 
								'</div>' . PHP_EOL;

		// #adminContent close
		$this->adminContent	.= $this->closeAdminContent();
		
		
		return $this->adminContent;

	}
	

	
	// jQuery-UI_Themes einlesen
	private function getUIThemes()
	{
	
		$selUIThemes	= array();
		$handle			= opendir(PROJECT_DOC_ROOT . '/extLibs/jquery/ui');
				
		while($content = readdir($handle)) {
			if( $content != ".." && 
				strpos($content, ".") !== 0 && 
				is_dir(PROJECT_DOC_ROOT . '/extLibs/jquery/ui/' . $content)
			) {
				$selUIThemes[] = $content;
			}
		}
		closedir($handle);
		
		return $selUIThemes;
		
	}
	

	
	// Admin-Themes einlesen
	private function getAdminThemes()
	{
	
		$aThemes	= array();
		$themeDir	= SYSTEM_DOC_ROOT.'/themes';
		$handle		= opendir($themeDir);
				
		while($content = readdir($handle)) {
			if( strpos($content, ".") !== 0 && 
				is_dir($themeDir . '/' . $content)
			) {
				$aThemes[] = $content;
			}
		}
		closedir($handle);
		
		return $aThemes;
		
	}
	

	// getScriptTag
	public function getScriptTag()
	{
	
		return	'<script>' . PHP_EOL .
				'head.ready(function(){' . PHP_EOL .
				'head.load({tagEditorcss: "' . PROJECT_HTTP_ROOT . '/extLibs/jquery/tagEditor/jquery.tag-editor.min.css"});' . PHP_EOL .
				'head.load({tagEditorcaret: "' . PROJECT_HTTP_ROOT . '/extLibs/jquery/tagEditor/jquery.caret.min.js"});' . PHP_EOL .
				'head.load({tagEditor: "' . PROJECT_HTTP_ROOT . '/extLibs/jquery/tagEditor/jquery.tag-editor.min.js"});' . PHP_EOL .
				'head.ready("tagEditor", function(){' . PHP_EOL .
				'$("document").ready(function(){' . PHP_EOL .
				'$("#setusergroups").tagEditor({' . PHP_EOL .
				'maxLength: 256,' . PHP_EOL .
				'delimiter: "\n",' . PHP_EOL .
				'onChange: function(field, editor, tags){' . PHP_EOL .
					'editor.next(".deleteAllTags-panel").remove();' . PHP_EOL .
					'if(tags.length > 0 && !editor.next(".deleteAllTags-panel").length){ editor.after(\'<span class="deleteAllTags-panel buttonPanel"><button class="deleteAllTags cc-button button button-small button-icon-only btn right" type="button" role="button" title="{s_javascript:removeall}"><span class="cc-admin-icons icons cc-icon-cancel-circle">&nbsp;</span></button><br class="clearfloat" /></span>\'); }' . PHP_EOL .
					'editor.next(".deleteAllTags-panel").children(".deleteAllTags").click(function(){' . PHP_EOL .
						'for (i = 0; i < tags.length; i++) { field.tagEditor("removeTag", tags[i]); }' . PHP_EOL .
					'});' . PHP_EOL .
				'}' . PHP_EOL .
				'});' . PHP_EOL .
				'});' . PHP_EOL .
				'});' . PHP_EOL .
				'});' . PHP_EOL .
				'</script>' . PHP_EOL;
	
	}
	
}
