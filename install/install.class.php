<?php
namespace Concise;

/**
 * Generiert die Installationsseite
 * 
 * @param object Sprachobjekt
 * @param object Contents-Objekt
 */

// Init-Objekt
class Install
{

	private $o_lng		= "";	
	private $sqlFile	= "install.sql";
	
	public function __construct($o_lng)
	{
	
		$this->o_lng = $o_lng;
	
	}
	
	public function getInstall($token)
	{
		
		// Testen ob Datenbankverbindung bereits erstellt wurde
		if(DB_SERVER != "dbhost"
		&& DB_NAME != "dbname"
		) {
			@$DbExist = new MySQL(DB_SERVER,DB_USER,DB_PASSWORD,DB_NAME,DB_PORT);
			#var_dump($DbExist->MySQLiObj);		
		
			if($DbExist->installStatus === true)
				$hint = true;
		}
		
		
		// Theme
		$theme		= THEME;

		
		// Falls das Installationsformular abgeschickt wurde
		if(isset($GLOBALS['_POST']['install'])) {
			
			$dbHost			= trim($GLOBALS['_POST']['host']);
			$db		 		= trim($GLOBALS['_POST']['db']);
			$dbUser			= trim($GLOBALS['_POST']['username']);
			$dbPass			= trim($GLOBALS['_POST']['password']);
			$adminMail		= trim($GLOBALS['_POST']['email']);
			$adminMail2		= trim($GLOBALS['_POST']['email2']);
			$this->sqlFile	= trim($GLOBALS['_POST']['sqlFile']);
			$theme			= trim($GLOBALS['_POST']['feTheme']);
			
			$mysqlError		= "";
			
			if($dbHost == "")
				$errorH = "{s_install:choosehost}";
			elseif(!preg_match("/[a-zA-Z0-9-_\/\.]+/", $dbHost))
				$errorH = "{s_error:check}";
			elseif(strlen($dbHost) > 100)
				$errorH = "{s_install:toolong}";


			if($db == "")
				$errorD = "{s_install:choosedb}";
			elseif(!preg_match("/[a-zA-Z0-9-_\.]+/", $db))
				$errorD = "{s_error:check}";
			elseif(strlen($db) > 100)
				$errorD = "{s_install:toolong}";


			if($dbUser == "")
				$errorU = "{s_install:chooseuser}";
			elseif(!preg_match("/[a-zA-Z0-9-_\/\.]+/", $dbUser))
				$errorU = "{s_error:check}";
			elseif(strlen($dbUser) > 100)
				$errorU = "{s_install:toolong}";


			if($dbPass == "")
				$errorP = "{s_install:choosepass}";
			elseif(strlen($dbPass) > 100)
				$errorP = sprintf(ContentsEngine::replaceStaText("{s_error:passlen}"), 100);


			if($adminMail == "")
				$errorM = "{s_error:mail1}";
			elseif(strlen($adminMail) > 256)
				$errorM = "{s_install:toolong}";
			elseif(filter_var($adminMail, FILTER_VALIDATE_EMAIL) === false)
				$errorM = "{s_error:mail2}";
			elseif($adminMail2 != $adminMail)
				$errorM2 = "{s_error:mail2}";
			
			if(empty($this->sqlFile)
			|| !file_exists(PROJECT_DOC_ROOT . '/install/' . $this->sqlFile)
			)
				$this->sqlFile = "install.sql";
			
			if($theme == ""
			|| !is_dir(PROJECT_DOC_ROOT . '/themes/' . $theme)
			)
				$theme = THEME;

			
			// Falls keine Fehler aufgetaucht sind			
			if(!isset($errorH)
			&& !isset($errorD)
			&& !isset($errorU)
			&& !isset($errorP)
			&& !isset($errorM)
			&& !isset($errorM2)
			) {
				
				// Server-Verbindung testen
				$dbConn = @mysqli_connect($dbHost, $dbUser, $dbPass);
				
				// Falls keine Verbindung möglich war oder ein Fehler bei 
				if(!$dbConn) {
					
					$error = '{s_error:dbconn}';
				
					$ec = mysqli_errno();
					
					if($ec == 2005)
						$errorH = '{s_error:dbhost} &quot;<i>'.htmlspecialchars($dbHost).'</i>&quot;';
					if($ec == 1045) {
						$errorU = '{s_error:dbuser}';
						$errorP = $errorU;
					}
					$mysqlError = $ec . ": " . mysqli_error();
				}
				
				// Falls Server-Verbindung erfolgreich
				else {
					
					// DB-Verbindung testen
					// Falls Fehler bei der DB-Verbindung
					if(!@mysqli_select_db($dbConn, $db)) {

						$error = '{s_error:dbconn}';
					
						// Fehler der DB-Verbindung
						$ec = mysqli_errno($dbConn);
						
						if($ec == 1049)
							$errorD = '{s_error:dbdb} &quot;<i>'.htmlspecialchars($db).'</i>&quot;)';
						
						$mysqlError = $ec . ": " . mysqli_error($dbConn);
					}
			
					else {
						// Datenbankobjekt erstellen
						$DB = @new MySQL($dbHost,$dbUser,$dbPass,$db);
								
						// Kollation der Datenbank auf utf8_general_ci setzen
						$setCollation = $DB->setDbCollation($db);
						
						// Datenbank-Tabellen/-Inhalte anlgenen
						system(BACKUP_ROOT . "/mysql -p".$dbPass." -u ".$dbUser." -h ".$dbHost." ".$db." < ".PROJECT_DOC_ROOT."/install/" . $this->sqlFile, $fp);
						#die(var_dump(BACKUP_ROOT . "/mysql -p".$dbPass." -u ".$dbUser." ".$db." < ".PROJECT_DOC_ROOT."/install/" . $this->sqlFile . $fp));
						
						if($fp == 0) {

							// Set admin email
							$setMail	= $DB->query("UPDATE `" . DB_TABLE_PREFIX . "user` 
														SET `email` = '" . $DB->escapeString($adminMail) . "' 
														WHERE `userid` = 1
														");
							
							if($setMail !== true) {
								$error		= "{s_install:errorsetmail}";
								$mysqlError = "";
							}
							
							
							if(isset($hint)) // Falls die Datenbank neu aufgesetzt wurde, auch die Settings-Datei zurücksetzen
								@copy(PROJECT_DOC_ROOT . '/inc/settings.bkp', PROJECT_DOC_ROOT . '/inc/settings.php');
							
							if(!$settings = @file_get_contents(PROJECT_DOC_ROOT . '/inc/settings.php')) die("settings file not found");
							else {
								
								@copy(PROJECT_DOC_ROOT . '/inc/settings.php', PROJECT_DOC_ROOT . '/inc/settings.bkp');
								
								$settings = preg_replace("/dbhost/", $dbHost, $settings);
								$settings = preg_replace("/dbname/", $db, $settings);
								$settings = preg_replace("/dbuser/", $dbUser, $settings);
								$settings = preg_replace("/dbpassword/", $dbPass, $settings);						
								$settings = preg_replace("/'DEF_ADMIN_LANG',\"".DEF_ADMIN_LANG."\"/", "'DEF_ADMIN_LANG',\"".$this->o_lng->adminLang."\"", $settings);
								$settings = preg_replace("/'CONTACT_EMAIL',\"".CONTACT_EMAIL."\"/", "'CONTACT_EMAIL',\"".$adminMail."\"", $settings);
								$settings = preg_replace("/'AUTO_MAIL_EMAIL',\"".AUTO_MAIL_EMAIL."\"/", "'AUTO_MAIL_EMAIL',\"".$adminMail."\"", $settings);
								$settings = preg_replace("/'NEWSLETTER_EMAIL',\"".NEWSLETTER_EMAIL."\"/", "'NEWSLETTER_EMAIL',\"".$adminMail."\"", $settings);
								$settings = preg_replace("/'GBOOK_NOTIFY_EMAIL',\"".GBOOK_NOTIFY_EMAIL."\"/", "'GBOOK_NOTIFY_EMAIL',\"".$adminMail."\"", $settings);
								$settings = preg_replace("/'COMMENTS_NOTIFY_EMAIL',\"".COMMENTS_NOTIFY_EMAIL."\"/", "'COMMENTS_NOTIFY_EMAIL',\"".$adminMail."\"", $settings);
								$settings = preg_replace("/'THEME',\"".THEME."\"/", "'THEME',\"".$theme."\"", $settings);
								
								if(!@file_put_contents(PROJECT_DOC_ROOT . '/inc/settings.php', $settings))
									die("Could not write settings file.<br />Please set database params manually in %root%/inc/settings.php.");
								else
									$notice = true;
							}
						}
						// Falls Dumping nicht erfolgreich, Fehler ausgeben
						else {
							$error = '{s_error:dbdump}';
							$mysqlError = "";
						}
					}
				}
				
			} // Ende Kein Eingabefehler
		
			
		} // Ende if submit
		
		
		// Theme selection
		$selSQL		= $this->getSQLFileSelectOptions($this->sqlFile);
		$selThemes	= $this->getThemeSelectOptions($theme);

		
		$installOutput =	'<h1 class="cc-h1">{s_header:welcome} - Concise WMS</h1>' . PHP_EOL .
							'<h2 class="cc-h2">' . $this->o_lng->getLangSelector("", "flag", "", false, true) . '{s_install:header}</h2>' . PHP_EOL;
		
		if(isset($notice)) { // Falls eine Erfolgsmeldung vorliegt, diese ausgeben

			return			$installOutput . '<p class="notice success">{s_install:installed}</p>' . PHP_EOL .
							(!$setCollation ? '<p class="notice error">{s_install:nocollate}</p>' . PHP_EOL : '') .
							'<p>{s_link:firstlogin}</p>' . PHP_EOL .
							'<p>&nbsp;</p>' . PHP_EOL .
							'<p>&nbsp;</p>' . PHP_EOL .
							'<p><a href="' . PROJECT_HTTP_ROOT . '/login' . PAGE_EXT . '?lang=' . $this->o_lng->adminLang . '" class="cc-button button {t_class:btn} {t_class:btnpri} formbutton login"><strong>{s_link:login}</strong></a></p>' . PHP_EOL;
		}
		
		if(isset($hint)) // Falls eine erreichbare Datenbank besteht, Hinweis ausgeben
			$installOutput .='<p class="error">{s_install:dbexist}<strong><a href="' . PROJECT_HTTP_ROOT . '/login' . PAGE_EXT . '"><u>hier</u></a></strong>{s_install:dbexist2}</p>' . PHP_EOL;
			
		if(isset($error)) { // Falls eine Fehlermeldung vorliegt, diese ausgeben
			$installOutput .='<p class="error">{s_error:error}<br /><br />'.$error.'<br /><br />'.$mysqlError.'</p>' . PHP_EOL;
		}
		
		$installOutput .=	'<div id="installForm" class="adminArea">' . PHP_EOL .
							'<form action="' . PROJECT_HTTP_ROOT . '/install.html" method="post" name="installForm" id="installForm" data-ajax="false" />' . PHP_EOL . 
							'<h3 class="cc-h3">' . PHP_EOL .
							ContentsEngine::getIcon("backup", "right") . PHP_EOL . 
							'{s_install:header2}<br class="clearfloat" /></h3>' . 
							'<ul class="adminSection">' . PHP_EOL . 
							'<li>' . PHP_EOL .
							'<label for="host">{s_install:host}</label>' . PHP_EOL . 
							(isset($errorH) ? '<p class="notice">' . $errorH . '</p>' : '') . 
							'<input type="text" name="host" id="host" maxlength="100" value="' . (isset($dbHost) ? htmlspecialchars($dbHost) : '') . '" />' . PHP_EOL .
							'<label for="db">{s_install:db}</label>' . PHP_EOL . 
							(isset($errorD) ? '<p class="notice">' . $errorD . '</p>' : '') . 
							'<input type="text" name="db" id="db" maxlength="100" value="' . (isset($db) ? htmlspecialchars($db) : '') . '" />' . PHP_EOL .
							'<label for="username">{s_label:userN}</label>' . PHP_EOL . 
							(isset($errorU) ? '<p class="notice">' . $errorU . '</p>' : '') . 
							'<input type="text" name="username" id="username" maxlength="100" value="' . (isset($dbUser) ? htmlspecialchars($dbUser) : '') . '" />' . PHP_EOL .
							'<label for="password">{s_label:userP1}</label>' . PHP_EOL . 
							(isset($errorP) ? '<p class="notice">' . $errorP . '</p>' : '') . 
							'<input type="password" name="password" id="password" maxlength="100" />' . PHP_EOL . 
							'</li>' . PHP_EOL .
							'<br class="separator" />' . PHP_EOL .
							'<li>' . PHP_EOL .
							'<label for="sqlFile">Install file</label>' . PHP_EOL .
							'<select name="sqlFile" id="sqlFile" class="select cc-select-sql">' . PHP_EOL .
							$selSQL.
							'</select>' . PHP_EOL .
							'</li>' . PHP_EOL .
							'</ul>' . PHP_EOL .
							'<h3 class="cc-h3">' . PHP_EOL .
							ContentsEngine::getIcon("admin", "right") . PHP_EOL . 
							'{s_option:account}<br class="clearfloat" /></h3>' . 
							'<ul class="adminSection">' . PHP_EOL . 
							'<li>' . PHP_EOL .
							'<label class="label">Admin {s_label:user}</label>' . PHP_EOL .
							(isset($errorM) ? '<p class="notice">' . $errorM . '</p>' : '') . 
							'<input type="text" name="email" id="email" maxlength="256" value="' . (isset($adminMail) ? htmlspecialchars($adminMail) : '') . '" />' . PHP_EOL .
							'<label class="label">Admin {s_install:repeatmail}</label>' . PHP_EOL .
							(isset($errorM2) ? '<p class="notice">' . $errorM2 . '</p>' : '') . 
							'<input type="text" name="email2" id="email2" maxlength="256" value="' . (isset($adminMail2) ? htmlspecialchars($adminMail2) : '') . '" />' . PHP_EOL .
							'</li>' . PHP_EOL .
							'</ul>' . PHP_EOL . 
							'<h3 class="cc-h3">' . PHP_EOL .
							ContentsEngine::getIcon("theme", "right") . PHP_EOL . 
							'{s_label:theme}<br class="clearfloat" /></h3>' . 
							'<ul class="adminSection">' . PHP_EOL . 
							'<li>' . PHP_EOL .
							'<div id="themeSelectionBox" class="choose imagePicker">' . PHP_EOL .
							'<div class="leftBox">' . PHP_EOL .
							'<label class="label" title="{s_title:choosetheme}">' . PHP_EOL .
							ContentsEngine::getIcon("theme") .
							'{s_label:theme} <span>&#9658;</span> <strong>' . $theme . '</strong> </label>' . PHP_EOL .
							'</div>' . PHP_EOL .
							'<div class="selTheme rightBox">' . PHP_EOL .
							'<div class="cc-select-theme-box">' . PHP_EOL .
							'<span class="singleInput-panel">' . PHP_EOL .
							'<select name="feTheme" id="feTheme" class="select cc-select-theme">' . PHP_EOL . 
							$selThemes .
							'</select>' . PHP_EOL;
		
		// Button toggle themes
		$btnDefs	= array(	"type"		=> "button",
								"name"		=> "toggleThemes",
								"class"		=> '{t_class:btndef} button-small',
								"value"		=> htmlspecialchars($theme),
								"text"		=> "{s_label:theme}",
								"attr"		=> 'data-toggle="image_picker_selector"',
								"title"		=> '{s_title:choosetheme}'
							);
		
		$installOutput .=	'<span class="right">' . PHP_EOL;
		
		$installOutput .=	ContentsEngine::getButton($btnDefs, "right");
		
		$installOutput .=	'</span>' . PHP_EOL;
		
		$installOutput .=	'</span>' . PHP_EOL .
							$this->getScriptTag() .
							'</div>' . PHP_EOL;
		
		$installOutput .=	'<br class="clearfloat" />' . PHP_EOL .
							'</div>' . PHP_EOL;
								
		$installOutput .=	'<br class="clearfloat" />' . PHP_EOL .
							'</div>' . PHP_EOL .
							'</li>' . PHP_EOL .
							'</ul>' . PHP_EOL . 
							'<ul>' . PHP_EOL . 
							'<li class="submit change">' . PHP_EOL;
			
		// Button submit
		$btnDefs	= array(	"type"		=> "submit",
								"name"		=> "install",
								"id"		=> "submitInstall",
								"class"		=> 'cc-button button {t_class:btnpri} formbutton ok',
								"value"		=> "{s_install:submit}",
								"text"		=> "{s_install:submit}",
								"icon"		=> "ok"
							);
			
		$installOutput .=	ContentsEngine::getButton($btnDefs);
		
		$installOutput .=	'<input type="hidden" name="install" value="{s_install:submit}" />' . PHP_EOL . 
							'<input type="hidden" name="token" id="token" value="' . $token . '" />' . PHP_EOL . 
							'</li>' . PHP_EOL . 
							'</ul>' . PHP_EOL . 
							'</form>' . PHP_EOL . 
							'</div>' . PHP_EOL . 
							'<p>&nbsp;</p>'  . PHP_EOL .
							'<p>&nbsp;</p>'  . PHP_EOL .
							'<p>&nbsp;</p>'  . PHP_EOL .
							'<p>&nbsp;</p>'  . PHP_EOL .
							'<p>&nbsp;</p>'  . PHP_EOL .
							'<script type="text/javascript">' . PHP_EOL . 
							'/* <![CDATA[ */' . PHP_EOL . 
							'head.ready(function() {' .  
							'$(document).ready(function() {' .  
							'$("#host").focus();' . 
							'});' . PHP_EOL . 
							'});' . PHP_EOL . 
							'/* ]]> */' . PHP_EOL . 
							'</script>' . PHP_EOL;
		
		
		return $installOutput;
		
	}
	

	// SQL files einlesen
	private function getSQLFileSelectOptions($selSQL)
	{
	
		// SQL file wählen
		$sqlFiles	= array();
		$output		= "";
		$i 			= 0;
		
		$sqlFiles	= glob(PROJECT_DOC_ROOT . '/install/install*.sql');
		
		sort($sqlFiles, SORT_NATURAL | SORT_FLAG_CASE); // sortieren

		// Option-Felder generieren
		foreach($sqlFiles as $sqlFile) {
			
			$fileName	= basename($sqlFile);
			
			$output .= '<option value="' . $fileName . '" class="selSQL"';
		
			if(!empty($selSQL)
			&& $fileName == $selSQL
			) {
				$output .= ' selected="selected"';
			}				

			$output .= '>' . $fileName . '</option>' . PHP_EOL;
			
			$i++;
		}
	
		return $output;
	
	}
	

	// Admin-Themes einlesen
	private function getThemeSelectOptions($selTheme)
	{
	
		// Theme wählen
		$instThemes = array();
		$output		= "";
		$i 			= 0;
		
		$handle = opendir(PROJECT_DOC_ROOT . '/themes');
		
		while($content = readdir($handle)) {
			if( strpos($content, ".") !== 0 && 
				is_dir(PROJECT_DOC_ROOT . '/themes/' . $content)) { // Falls index.tpl mit aufgelistet werden soll
				$instThemes[] = $content;
			}
		}
		closedir($handle);
		
		natsort($instThemes); // sortieren

		// Option-Felder generieren
		foreach($instThemes as $theme) {
			
			$output .= '<option value="' . $theme . '" class="selTheme' . (count($instThemes) > 10 ? ' longList' : '');
		
			if(!empty($selTheme)
			&& $theme == $selTheme
			) {
				$output .= ' currentTheme" selected="selected';
			}				
			$output .= '" style="background:url(' . PROJECT_HTTP_ROOT . '/themes/' . $theme . '/img/theme-preview.jpg) no-repeat right;"';
			$output .= ' data-img-src="' . PROJECT_HTTP_ROOT . '/themes/' . $theme . '/img/theme-preview.jpg"';
			$output .= ' data-img-label="' . ucfirst($theme) . '"';
			
			$output .= '>' . $theme . '</option>' . PHP_EOL;
			
			$i++;
		}
	
		return $output;
	
	}
	

	// getScriptTag
	public function getScriptTag()
	{

		return	'<script>' . PHP_EOL .
				'head.ready("jquery", function(){' . PHP_EOL .
				'head.load({imagepickercss: "' . PROJECT_HTTP_ROOT . '/extLibs/jquery/image-picker/image-picker.css"});' . PHP_EOL .
				'head.load({imagepicker: "' . PROJECT_HTTP_ROOT . '/extLibs/jquery/image-picker/image-picker.min.js"});' . PHP_EOL .
				'head.ready("imagepicker", function(){' . PHP_EOL .
					'$(document).ready(function(){' . PHP_EOL .
						'$("#feTheme").imagepicker({
							target_box: $("#themeSelectionBox"),
							hide_select: true,
							show_label: true,
							show_title: true,
							limit: undefined
						});' . PHP_EOL .
					'});' . PHP_EOL .
				'});' . PHP_EOL .
				'});' . PHP_EOL .
				'</script>' . PHP_EOL;
	
	}

}
