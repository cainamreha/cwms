<?php
namespace Concise;



###################################################
##############  Sprachverwaltung  #################
###################################################

// Sprachen verwalten

class Admin_Langs extends Admin implements AdminTask
{

	private $countryArr		= array();
	private $nationalityArr	= array();
	private $tableLang		= "lang";
	
	public function __construct($DB, $o_lng, $task, $init = false)
	{

		// Admin-Elternklasse aufrufen
		parent::__construct($DB, $o_lng);
		
		parent::$task = $task;
		
		$this->tableLang		= DB_TABLE_PREFIX . $this->tableLang;

		// Scripte für Sortierung einbinden
		$this->headIncludeFiles['sortable']		= true;
		
		$this->countryArr		= require_once SYSTEM_DOC_ROOT . '/inc/admintasks/langs/countries_php_' . ($this->adminLang == "de" ? 'de' : 'en') . '.php'; // countries einbinden
		$nationalityArrInt		= require_once SYSTEM_DOC_ROOT . '/inc/admintasks/langs/nationalities.php'; // nationalities einbinden
		$nationalityArrEn		= require_once SYSTEM_DOC_ROOT . '/inc/admintasks/langs/nationalities_en.php'; // nationalities (en) einbinden
		$this->nationalityArr	= array_merge($nationalityArrEn, $nationalityArrInt);
		$this->scriptFiles[]	= 'system/inc/admintasks/langs/js/flags.js'; // flags js einbinden
		$this->cssFiles[]		= 'system/inc/admintasks/langs/css/flags16.css'; // flags css einbinden

	}
	
	
	public function getTaskContents($ajax = false)
	{
		
		// Enthält Headerbox
		$this->adminHeader		=	'{s_text:adminlangs}' . 
									'</div><!-- Ende headerBox -->' . PHP_EOL;
								
		// #adminContent
		$this->adminContent 	=	$this->openAdminContent();
		

		
		$formAction		= ADMIN_HTTP_ROOT . '?task=langs';

		$fixName		= "";
		$showBackButton	= true;
		$setLock		= array(true);
		$errorKeys		= array();

		// Locking checken
		if($this->checkLocking("all", "langs", $this->g_Session['username'])) {
			
			$this->adminContent .=	$this->getBackButtons("main");
			// #adminContent close
			$this->adminContent	.= $this->closeAdminContent();
			return $this->adminContent;
		}
		

		// Falls kein Lock
		$this->adminContent .=	'<div class="adminArea">' . PHP_EOL;

		
		#############  neue Sprache anlegen  ###############

		// Falls eine neue Sprache angelegt werden soll
		if(isset($GLOBALS['_POST']['new_ln'])
		|| isset($GLOBALS['_POST']['new_langExist'])
		) {
			
			
			// Falls eine vordefinierte Sprache installiert werden soll
			if(isset($GLOBALS['_POST']['new_langExist'])) {
				$GLOBALS['_POST']['new_langC'] = $GLOBALS['_POST']['new_langExist'];
				$GLOBALS['_POST']['new_langN'] = $GLOBALS['_POST']['new_langExistN_' . $GLOBALS['_POST']['new_langC']];
				$installExistLang = true;
			}
			else
				$installExistLang = false;
			
			
			// Länderkürzel überprüfen
			if(isset($GLOBALS['_POST']['new_langC'])) {
				
				$newC = strtolower(trim($GLOBALS['_POST']['new_langC']));
				 
				if($newC == "")
					$errorC = "{s_error:fill}";
				elseif(in_array($newC, $this->o_lng->installedLangs))
					$errorC = "{s_error:langexist}";
				elseif(!preg_match("/^[a-zA-Z]+$/", $newC))
					$errorC = htmlspecialchars($newC) . ": {s_error:wronglang}";
				elseif(strlen($newC) > 3)
					$errorC = "{s_error:langlenC}";
			}
			
			// Nationalität überprüfen
			if(isset($GLOBALS['_POST']['new_langN'])) {
				
				$newN = trim($GLOBALS['_POST']['new_langN']);
				 
				if($newN == "")
					$errorN = "{s_error:fill}";
				elseif(in_array($newN, $this->o_lng->existNation))
					$errorN = "{s_error:langexist}";
				elseif(!preg_match("/^[\pL ]+$/u", $newN))
					$errorN = "{s_error:wronglang}";
				elseif(strlen($newN) > 100)
					$errorN = "{s_error:langlenN}";
			}

			// Falls kein Fehler vorliegt
			if(isset($GLOBALS['_POST']['new_langC']) && !isset($errorC) && !isset($errorN)) {
				
				// Falls nicht eine vordefinierte Sprache installiert werden soll, Datei hochladen
				if(!$installExistLang) {

					$flagFile		= SYSTEM_DOC_ROOT . '/inc/admintasks/langs/img/flags/' . $newC . '.png';
					$fixName		= "flag_" . $newC . ".png";
					$folder			= PROJECT_DOC_ROOT . '/langs/' . $newC;
					
					if(file_exists($flagFile)) {
						if(!is_dir($folder)) mkdir($folder);
						$upload		= copy($flagFile, $folder . '/' . $fixName); // File-Upload
					}
					$nat			= file_put_contents(PROJECT_DOC_ROOT . '/langs/' . $newC . '/nationality.txt', $newN);
				}
				else {
					$upload			= true;
					$fixName		= "flag_" . $newC . ".png";
				}
				
				
				// Falls Upload nicht erfolgreich, Fehler
				if($upload !== true) {
					$errorF = $upload;
				}
				else { // Falls der Upload erfolgreich war, neue Sprache anlegen
						
					$natCode		= $this->DB->escapeString($newC);
					$nationality	= $this->DB->escapeString($newN);
					
					
					// db-Tabelle sperren
					$lock = $this->DB->query("LOCK TABLES `$this->tableLang`");
					
					
					// Zuletzt angelegte Sprache
					$lastLangQuery = $this->DB->query("SELECT `nat_code` 
															FROM `$this->tableLang` 
															WHERE id = (SELECT MAX(id) FROM `$this->tableLang`)
															");

					
					// Ermittlung der höchsten Sortierungs-ID
					$maxSortIdQuery = $this->DB->query("SELECT MAX(`sort_id`) 
															FROM `$this->tableLang` 
															");
				
					$newSortID = $this->DB->escapeString($maxSortIdQuery[0]['MAX(`sort_id`)']) +1;
					
					
					// Einfügen der neuen Sprachfelder
					$insertSQL = $this->DB->query("INSERT INTO `$this->tableLang` 
														(`sort_id`, `nat_code`, `nationality`, `flag_file`) 
														VALUES ($newSortID, '$natCode', '$nationality', '$fixName')
														");

					// db-Sperre aufheben
					$unLock = $this->DB->query("UNLOCK TABLES");
					
					
					if($insertSQL === true) {
						
						$lastNatCode	= $lastLangQuery[0]['nat_code'];
						$defLang		= $this->o_lng->defLang;
						$searchTitle	= ""; // Termini für geschützte Seiten
						$errorTitle		= ""; // Termini für geschützte Seiten
						$searchAlias	= "sitesearch"; // Termini für geschützte Seiten
						$errorAlias		= "error"; // Termini für geschützte Seiten
						$regAlias		= "registration";
						$tablePages		= DB_TABLE_PREFIX . parent::$tablePages;
						
						// Termini für geschützte Seiten
						switch($natCode) {
							
							case "de":
								$searchTitle	= "Suchergebnisse"; 
								$errorTitle		= "Fehlerseite";
								$regTitle		= "Registrierung";
								break;
							
							case "fr":
								$searchTitle	= "Recherche";
								$errorTitle		= "Erreur";
								$regTitle		= "Enregistrement";
								break;
							
							case "it":
								$searchTitle	= "Ricerca";
								$errorTitle		= "Errore";
								$regTitle		= "Registrazione";
								break;
							
							default:
								$searchTitle	= "Site search";
								$errorTitle		= "Error";
								$regTitle		= "Registration";
								break;
							
						}
						
						
						### pages ###
						// Tabelle pages updaten
						$pageField =	"ADD `title_" . $natCode . "` VARCHAR(100) NOT NULL," . 
										"ADD `alias_" . $natCode . "` VARCHAR(100) NOT NULL," . 
										"ADD `html_title_" . $natCode . "` VARCHAR(100) NOT NULL," . 
										"ADD `description_" . $natCode . "` VARCHAR(180) NOT NULL," . 
										"ADD `keywords_" . $natCode . "` VARCHAR(300) NOT NULL";

						// db-Tabelle sperren
						$lock = $this->DB->query("LOCK TABLES `" . $tablePages . "`");



						// Tabelle um neue Sprache erweitern
						$alterSQL2 = $this->DB->query("ALTER TABLE `" . $tablePages . "` " . $pageField . "
														   ");

					
						// Feldinhalte von Defaultsprache kopieren (Title)
						$updateSQL1 = $this->DB->query("UPDATE `" . $tablePages . "` 
															SET `title_" . $natCode . "` = CONCAT(`title_" . $defLang . "`, '-" . $natCode . "') 
															");

						// Feldinhalte von Defaultsprache kopieren (Alias)
						$updateSQL1a = $this->DB->query("UPDATE `" . $tablePages . "` 
															SET `alias_" . $natCode . "` = CONCAT(`alias_" . $defLang . "`, '-" . $natCode . "')
															WHERE `alias_" . $defLang . "` != ''
															");

						// Update der geschützten Seiten
						$updateSQL2 = $this->DB->query("UPDATE `" . $tablePages . "` 
															SET `title_" . $natCode . "` = 'Admin', 
															`alias_" . $natCode . "` = 'admin' 
															WHERE `page_id` = -1001
															");
						
						// Update der geschützten Seiten
						$updateSQL2a = $this->DB->query("UPDATE `" . $tablePages . "` 
															SET `title_" . $natCode . "` = 'Login', 
															`alias_" . $natCode . "` = 'login' 
															WHERE `page_id` = -1002
															");
						
						// Update der geschützten Seiten
						$updateSQL2b = $this->DB->query("UPDATE `" . $tablePages . "` 
															SET `title_" . $natCode . "` = '$errorTitle', 
															`alias_" . $natCode . "` = '$errorAlias' 
															WHERE `page_id` = -1003
															");
						
						// Update der geschützten Seiten
						$updateSQL2c = $this->DB->query("UPDATE `" . $tablePages . "` 
															SET `title_" . $natCode . "` = '$searchTitle', 
															`alias_" . $natCode . "` = '$searchAlias' 
															WHERE `page_id` = -1004
															");
												
						// Update der geschützten Seiten
						$updateSQL2d = $this->DB->query("UPDATE `" . $tablePages . "` 
															SET `title_" . $natCode . "` = 'Logout', 
															`alias_" . $natCode . "` = 'logout' 
															WHERE `page_id` = -1005
															");
						
						// Update der geschützten Seiten
						$updateSQL2e = $this->DB->query("UPDATE `" . $tablePages . "` 
															SET `title_" . $natCode . "` = '$regTitle', 
															`alias_" . $natCode . "` = '$regAlias' 
															WHERE `page_id` = -1006
															");
						
						
						// Ggf. Menü-Views löschen
						$this->DB->dropViews(array( DB_TABLE_PREFIX . "menuViewTable-1",
													DB_TABLE_PREFIX . "menuViewTable0",
													DB_TABLE_PREFIX . "menuViewTable1",
													DB_TABLE_PREFIX . "menuViewTable2",
													DB_TABLE_PREFIX . "menuViewTable3")
											);
						
						
						// db-Sperre aufheben
						$unLock = $this->DB->query("UNLOCK TABLES");




						### contents ###
						// Tabellen contents_xyz updaten
						foreach(parent::$contentAndPreviewTables as $table) {
							
							$table	= DB_TABLE_PREFIX . $table;
							
							$conNr	= parent::getConNumber($table);
							
							if($conNr == 0) $conNr = 1;
							$conCon = "";
							$conField = "";			
							$conType = "";
							$conUpd = "";
				
				
							for($i = 1; $i <= $conNr; $i++) {
								
								$conCon = "ADD `con" . $i . "_" . $natCode . "` MEDIUMTEXT NOT NULL AFTER `con" . $i . "_" . $lastNatCode . "`,";
								$conField .= $conCon;
								
								$conType = "`con" . $i . "_" . $natCode . "` = `con" . $i . "_" . $defLang . "`,";
								$conUpd .= $conType;
							}
							
							$conField = substr($conField, 0, -1);
							$conUpd = substr($conUpd, 0, -1);

							// db-Tabelle sperren
							$lock = $this->DB->query("LOCK TABLES `" . $table . "`");
					


							// Spalte für Sprache hinzufügen
							$alterSQL1 = $this->DB->query("ALTER TABLE `" . $table . "` " . $conField . "
															   ");
							
							// Suche nach Einträgen in gesetzter Sprache
							$updateSQL1 = $this->DB->query("UPDATE `" . $table . "` SET " . $conUpd . "
																");
							#var_dump($conNr);
							
						} // Ende foreach
						

						// db-Sperre aufheben
						$unLock = $this->DB->query("UNLOCK TABLES");



						### galleries ###
						// Tabelle galleries updaten
						$gallField =	"ADD `name_" . $natCode . "` TEXT NOT NULL";

						// db-Tabelle sperren
						$lock = $this->DB->query("LOCK TABLES `" . DB_TABLE_PREFIX . "galleries`, `" . DB_TABLE_PREFIX . "galleries_images`");


						// Tabelle um neue Sprache erweitern
						$alterSQL3 = $this->DB->query("ALTER TABLE `" . DB_TABLE_PREFIX . "galleries` " . $gallField . "
														   ");

						// Update des Galerienamens
						$updateSQL3a = $this->DB->query("UPDATE `" . DB_TABLE_PREFIX . "galleries` 
															SET `name_" . $natCode . "` = `name_" . $defLang . "`
															");
						

						
						// Tabelle galleries_images updaten
						$gallField =	"ADD `title_" . $natCode . "` VARCHAR(300) NOT NULL," . 
										"ADD `link_" . $natCode . "` VARCHAR(300) NOT NULL," . 
										"ADD `text_" . $natCode . "` TEXT NOT NULL";


						// Tabelle um neue Sprache erweitern
						$alterSQL3 = $this->DB->query("ALTER TABLE `" . DB_TABLE_PREFIX . "galleries_images` " . $gallField . "
														   ");

						// Update des Kategorienamens
						$updateSQL3a = $this->DB->query("UPDATE `" . DB_TABLE_PREFIX . "galleries_images` 
															SET 
															`title_" . $natCode . "` = `title_" . $defLang . "`,
															`link_" . $natCode . "` = `link_" . $defLang . "`,
															`text_" . $natCode . "` = `text_" . $defLang . "`
															");
						
						// db-Sperre aufheben
						$unLock = $this->DB->query("UNLOCK TABLES");
						
						
						
						### Module-data ###
						// Tabelle news updaten
						$newsField =	"ADD `header_" . $natCode . "` VARCHAR(300) NOT NULL," . 
										"ADD `teaser_" . $natCode . "` TEXT NOT NULL," . 
										"ADD `text_" . $natCode . "` MEDIUMTEXT NOT NULL," .
										"ADD `tags_" . $natCode . "` TEXT NOT NULL," .
										"ADD FULLTEXT `index_" . $natCode . "`(`header_" . $natCode . "`, `teaser_" . $natCode . "`, `text_" . $natCode . "`)," .
										"ADD FULLTEXT `tags_" . $natCode . "`(`tags_" . $natCode . "`)";

						// Datentext-Updates (Kopie der Hauptsprache)
						$dataUpdate =	"`header_" . $natCode . "` = `header_" . $defLang . "`," .
										"`teaser_" . $natCode . "` = `teaser_" . $defLang . "`," .
										"`text_" . $natCode . "` = `text_" . $defLang . "`," .
										"`tags_" . $natCode . "` = `tags_" . $defLang . "`";
						
						
						// db-Tabelle sperren
						$lock = $this->DB->query("LOCK TABLES	`" . DB_TABLE_PREFIX . "articles`,
																`" . DB_TABLE_PREFIX . "news`,
																`" . DB_TABLE_PREFIX . "planner`,
																`" . DB_TABLE_PREFIX . "search`
												");


						// Tabelle um neue Sprache erweitern
						$alterSQL3a = $this->DB->query("ALTER TABLE `" . DB_TABLE_PREFIX . "articles` " . $newsField . "
														   ");

						// Tabelle um neue Sprache erweitern
						$alterSQL3b = $this->DB->query("ALTER TABLE `" . DB_TABLE_PREFIX . "news` " . $newsField . "
														   ");

						// Tabelle um neue Sprache erweitern
						$alterSQL3c = $this->DB->query("ALTER TABLE `" . DB_TABLE_PREFIX . "planner` " . $newsField . "
														   ");
						

						// Update der Datentexte
						$updateSQL3a = $this->DB->query("UPDATE `" . DB_TABLE_PREFIX . "articles` 
															  SET " . $dataUpdate . "
															  ");
						
						// Update der Datentexte
						$updateSQL3b = $this->DB->query("UPDATE `" . DB_TABLE_PREFIX . "news` 
															  SET " . $dataUpdate . "
															  ");
						
						// Update der Datentexte
						$updateSQL3c = $this->DB->query("UPDATE `" . DB_TABLE_PREFIX . "planner` 
															  SET " . $dataUpdate . "
															  ");


						// Such-Tabelle um neue Sprache erweitern
						$alterSQL4 = $this->DB->query("ALTER TABLE `" . DB_TABLE_PREFIX . "search` ADD `con_" . $natCode . "` LONGTEXT NOT NULL, ADD FULLTEXT(`con_" . $natCode . "`)
														   ");
						
						// Update der Suchtabelle (Kopie der Hauptsprache)
						$updateSQL3c = $this->DB->query("UPDATE `" . DB_TABLE_PREFIX . "search` 
															  SET `con_" . $natCode . "` = `con_" . $defLang . "`
															  ");

						
						// db-Sperre aufheben
						$unLock = $this->DB->query("UNLOCK TABLES");
						
						
						
						// Tabelle news_categories updaten
						$catField =		"ADD `category_" . $natCode . "` VARCHAR(64) NOT NULL," .
										"ADD `cat_teaser_" . $natCode . "` TEXT NOT NULL," .
										"ADD FULLTEXT(`cat_teaser_" . $natCode . "`)";
						
						// Datentext-Updates (Kopie der Hauptsprache)
						$dataCatUpdate =	"`category_" . $natCode . "` = `category_" . $defLang . "`," .
											"`cat_teaser_" . $natCode . "` = `cat_teaser_" . $defLang . "`";
						
						
						// db-Tabelle sperren
						$lock = $this->DB->query("LOCK TABLES	`" . DB_TABLE_PREFIX . "articles_categories`,
																`" . DB_TABLE_PREFIX . "news_categories`,
																`" . DB_TABLE_PREFIX . "planner_categories`
												");


						// Tabelle um neue Sprache erweitern
						$alterSQL4a = $this->DB->query("ALTER TABLE `" . DB_TABLE_PREFIX . "articles_categories` " . $catField . "
														   ");
						
						// Tabelle um neue Sprache erweitern
						$alterSQL4b = $this->DB->query("ALTER TABLE `" . DB_TABLE_PREFIX . "news_categories` " . $catField . "
														   ");
						
						// Tabelle um neue Sprache erweitern
						$alterSQL4c = $this->DB->query("ALTER TABLE `" . DB_TABLE_PREFIX . "planner_categories` " . $catField . "
														   ");
						
						// Update des Kategorienamens
						$updateSQL4a = $this->DB->query("UPDATE `" . DB_TABLE_PREFIX . "articles_categories` 
															  SET " . $dataCatUpdate . "
															  ");
						
						// Update des Kategorienamens
						$updateSQL4b = $this->DB->query("UPDATE `" . DB_TABLE_PREFIX . "news_categories` 
															  SET " . $dataCatUpdate . "
															  ");
						
						// Update des Kategorienamens
						$updateSQL4c = $this->DB->query("UPDATE `" . DB_TABLE_PREFIX . "planner_categories` 
															  SET " . $dataCatUpdate . "
															  ");
						

						// db-Sperre aufheben
						$unLock = $this->DB->query("UNLOCK TABLES");
						
						
						
						
						// Tabelle forms und form_definitioins updaten
						$formsField =	"ADD `title_" . $natCode . "` VARCHAR(200) NOT NULL," . 
										"ADD `notice_success_" . $natCode . "` VARCHAR(300) NOT NULL," . 
										"ADD `notice_error_" . $natCode . "` VARCHAR(300) NOT NULL," . 
										"ADD `notice_field_" . $natCode . "` VARCHAR(200) NOT NULL," . 
										"ADD `add_labels_" . $natCode . "` VARCHAR(2048) NOT NULL";

						$formsDefField =	"ADD `label_" . $natCode . "` VARCHAR(300) NULL," . 
											"ADD `value_" . $natCode . "` TEXT NULL," . 
											"ADD `options_" . $natCode . "` VARCHAR(2048) NULL," .
											"ADD `notice_" . $natCode . "` VARCHAR(300) NULL," . 
											"ADD `linkval_" . $natCode . "` VARCHAR(256) NULL," . 
											"ADD `header_" . $natCode . "` VARCHAR(300) NULL," . 
											"ADD `remark_" . $natCode . "` TEXT NULL";

						$formsFieldData =	"`title_" . $natCode . "` = `title_" . $defLang . "`, " .
											"`notice_success_" . $natCode . "` = `notice_success_" . $defLang . "`, " .
											"`notice_error_" . $natCode . "` = `notice_error_" . $defLang . "`, " .
											"`notice_field_" . $natCode . "` = `notice_field_" . $defLang . "`, " .
											"`add_labels_" . $natCode . "` = `add_labels_" . $defLang . "`";

						$formsDefFieldData =	"`label_" . $natCode . "` = `label_" . $defLang . "`, " .
												"`value_" . $natCode . "` = `value_" . $defLang . "`, " .
												"`options_" . $natCode . "` = `options_" . $defLang . "`, " .
												"`notice_" . $natCode . "` = `notice_" . $defLang . "`, " .
												"`linkval_" . $natCode . "` = `linkval_" . $defLang . "`, " .
												"`header_" . $natCode . "` = `header_" . $defLang . "`, " .
												"`remark_" . $natCode . "` = `remark_" . $defLang . "`";

						// db-Tabelle sperren
						$lock = $this->DB->query("LOCK TABLES	`" . DB_TABLE_PREFIX . "forms`,
																`" . DB_TABLE_PREFIX . "forms_definitions`
												");


						// Tabelle um neue Sprache erweitern
						$alterSQL5a = $this->DB->query("ALTER TABLE `" . DB_TABLE_PREFIX . "forms` " . $formsField . "
														   ");

						// Tabelle um neue Sprache erweitern
						$alterSQL5b = $this->DB->query("ALTER TABLE `" . DB_TABLE_PREFIX . "forms_definitions` " . $formsDefField . "
														   ");
						
						// Update der Formsinhalte (forms)
						$updateSQL5a = $this->DB->query("UPDATE `" . DB_TABLE_PREFIX . "forms` 
															  SET " . $formsFieldData . "
															  ");
						
						// Update der Formsinhalte (forms)
						$updateSQL5b = $this->DB->query("UPDATE `" . DB_TABLE_PREFIX . "forms_definitions` 
															  SET " . $formsDefFieldData . "
															  ");
						

						// History löschen
						foreach(parent::$contentTables as $table) {
							
							$table		= DB_TABLE_PREFIX . $table . '_history';
							
							$dropSQL	= $this->DB->query("DROP TABLE IF EXISTS `" . $table . "`");
						}
						
						
						// db-Sperre aufheben
						$unLock = $this->DB->query("UNLOCK TABLES");
						

						// Sprachverzeichnis und staticText-File anlegen
						$langsPath		= PROJECT_DOC_ROOT . "/langs";
						
						if(!is_dir($langsPath . "/" . $newC))
							mkdir($langsPath . "/" . $newC, 0755);
						if(!file_exists($langsPath . "/" . $newC . "/staticText_" . $newC . ".ini")) {
							
							if(!$newIniFile = @file_get_contents($langsPath . "/".DEF_LANG."/staticText_".DEF_LANG.".ini")) die("Datei fuer Sprachbausteine nicht gefunden. Bitte den Administrator benachrichtigen.");
							else {
								
								$newIniFile = preg_replace("/; ini-file fuer statische Sprachbausteine_" . DEF_LANG . "\r\n\r\n/", "; ini-file fuer statische Sprachbausteine_" . $newC . "\r\n\r\n", $newIniFile);
															
								if(!@file_put_contents($langsPath . "/".$newC."/staticText_".$newC.".ini", $newIniFile)) die("Fehler beim Anlegen der Datei fuer Sprachbausteine. Bitte den Administrator benachrichtigen.");
								
							}
						}
						
						$this->setSessionVar('notice', "{s_notice:newlang}"); // Benachrichtigung in Session speichern

						// Ggf. edit_lang aus Session löschen
						$this->unsetSessionKey('edit_lang');
						
						header("Location: " . ADMIN_HTTP_ROOT . "?task=langs");
						exit;
					}
					else
						$errorC = "{s_error:error}";
				}
			}
			

			$this->adminContent .=	'<h2 class="toggle cc-section-heading cc-h2">{s_header:newlang}</h2>' . PHP_EOL;

			
			$this->adminContent .=	'<div class="adminBox">' . PHP_EOL .
									'<form action="' . $formAction . '" name="adminfm" method="post" enctype="multipart/form-data">' . PHP_EOL .
									'<h3 class="cc-h3 toggle">{s_label:existinglangs}</h3>' . PHP_EOL .
									'<ul class="adminSection">' . PHP_EOL .
									'<li class="predefinedLangs">' . PHP_EOL;
			
			
			// Vordefinierte Sprachen auflisten
			foreach($this->o_lng->getExistingLangs() as $existLang) {
			
				$langUrlPath	= PROJECT_HTTP_ROOT . '/langs';
				$nat			= htmlspecialchars(trim(file_get_contents(PROJECT_DOC_ROOT . '/langs/' . $existLang . '/nationality.txt')));
				
				
				// Falls noch nicht installiert
				if(!in_array($existLang, $this->o_lng->installedLangs)) {
				
					$this->adminContent .=	'<span class="predefinedLang">' . PHP_EOL;
			
					// Button submit (new)
					$btnDefs	= array(	"type"		=> "submit",
											"name"		=> "new_langExist",
											"value"		=> $existLang,
											"text"		=> "",
											"class"		=> "installLang installable button-icon-only",
											"title"		=> '{s_header:newlang} &#9658; <strong>' . $nat . '</strong>" data-lang="{s_header:newlang}?<br /><br /><img src=\'' . $langUrlPath . '/' . $existLang . '/flag_' . $existLang . '.png\' /> <strong>' . $nat . '</strong> (' . $existLang . ')',
											"icon"		=> 'lang-' . $existLang,
											"iconclass"	=> 'background-icon',
											"iconattr"	=> 'style="background:url(' . $langUrlPath . '/' . $existLang . '/flag_' . $existLang . '.png) no-repeat center center;"'
										);
					
					$this->adminContent	.=	parent::getButton($btnDefs);
					
					$this->adminContent	.=	'<input type="hidden" name="new_langExistN_' . $existLang . '" value="' . $nat . '" />' . PHP_EOL .
											'<br />' . PHP_EOL .
											$nat .
											'</span>' . PHP_EOL;
				}
				else {
					$this->adminContent .=	'<span class="predefinedLang installedLang">' . PHP_EOL;
			
					// Button submit (new)
					$btnDefs	= array(	"class"		=> "installLang opacity50",
											"title"		=> '<strong>' . $nat . '</strong> &#9658; {s_label:installed}',
											"text"		=> parent::getIcon("check", "active"),
											"icon"		=> 'lang-' . $existLang,
											"iconclass"	=> 'background-icon',
											"iconattr"	=> 'style="background:url(' . $langUrlPath . '/' . $existLang . '/flag_' . $existLang . '.png) no-repeat center center;"'
										);
					
					$this->adminContent	.=	parent::getButton($btnDefs);
				
					$this->adminContent	.=	$nat . '</span>' . PHP_EOL;
				}
			}
			
			$this->adminContent .=	'</li></ul>' . PHP_EOL;
			
			$this->adminContent .=	'<h3 class="cc-h3 toggle">{s_button:takelang}</h3>' . PHP_EOL .
									'<ul class="framedItems">' . PHP_EOL .
									'<li><label>{s_label:langC} ({s_label:langCC})</label>' . PHP_EOL;
			
			if(isset($GLOBALS['_POST']['new_langC']) && isset($errorC))
				$this->adminContent .= '<p class="notice">' . $errorC . '</p>' . PHP_EOL;
			
			
			$this->adminContent .=	'<select name="new_langC" id="countrySelect" class="countrySelect f16">' . PHP_EOL;
			
			foreach($this->countryArr as $key => $val) {
				$cCode	= strtolower($key);
				$this->adminContent .=	'<option value="' . $cCode . '" data-class="countryEntry flag ' . $cCode . '"' . (array_key_exists($key, $this->nationalityArr) ? ' data-nationality="' . $this->nationalityArr[$key] . '"' : '') . (isset($newC) && $newC == $cCode ? ' selected="selected"' : '') . (in_array($cCode, $this->o_lng->installedLangs) ? ' disabled="disabled"' : '') . '>' . htmlspecialchars($val . ' (' . $cCode . ')') . '</option>' . PHP_EOL;
			}
			
			$this->adminContent .=	'</select>' . PHP_EOL;
			
			$this->adminContent .=	'</li>' . PHP_EOL;
			
			$this->adminContent .=	'<li><label>{s_label:langN}</label>' . PHP_EOL;
			
			if(isset($GLOBALS['_POST']['new_langN']) && isset($errorN))
				$this->adminContent .= '<p class="notice">' . $errorN . '</p>';
			
			$this->adminContent .=	'<input type="text" name="new_langN" id="new_langN" maxlength="100"';
			
			isset($newN) ? $value = htmlspecialchars($newN) : $value = "";

			$this->adminContent .=	' value="' . $value . '" /></li>' . PHP_EOL;
			
			$this->adminContent .=	'<li class="submit change">' . PHP_EOL;
		
			// Button submit
			$btnDefs	= array(	"type"		=> "submit",
									"name"		=> "new_ln",
									"id"		=> "submit1",
									"class"		=> "change",
									"value"		=> "{s_button:takelang}",
									"icon"		=> "ok"
								);
			
			$this->adminContent .=	parent::getButton($btnDefs);
			
			$this->adminContent .=	'<input name="new_ln" type="hidden" value="{s_button:takelang}" />' . PHP_EOL . 
									'<input type="hidden" name="token" class="token" value="' . parent::$token . '" />' . PHP_EOL . 
									'</li>' .
									'</ul>' .
									'</form>' .
									'</div>';
			
			// Script
			$this->adminContent .=	$this->getSelectmenuScript();

		}


		#############  Sprache bearbeiten  ###############

		// Falls Details einer installierten Sprache bearbeitet werden sollen
		elseif(isset($GLOBALS['_POST']['edit_ln'])) {

			$elArr			= explode("#", $GLOBALS['_POST']['edit_ln']);
			$editC 			= strtolower(trim(reset($elArr)));
			$editN 			= next($elArr);
			$editF 			= end($elArr);
			$isUploadFile	= false;

			$natCodeOld		= $editC;
			$natNatOld		= $editN;
			$editFileOld	= $editF;


			// Falls das Formular abgeschickt wurde (Länderkürzel)
			if(isset($GLOBALS['_POST']['edit_langC'])) {

				$editC = strtolower(trim($GLOBALS['_POST']['edit_langC']));
				 
				if($editC == "")
					$errorC = "{s_error:fill}";
				elseif(in_array($editC, $this->o_lng->installedLangs) && $editC != strtolower(trim(reset($elArr))))
					$errorC = "{s_error:langexist}";
				elseif(!preg_match("/^[a-zA-Z]+$/", $editC))
					$errorC = htmlspecialchars($editC) . ": {s_error:wronglang}";
				elseif(strlen($editC) > 3)
					$errorC = "{s_error:langlenC}";
			
			
				// Falls das Formular abgeschickt wurde (Nationalität)
				if(isset($GLOBALS['_POST']['edit_langN'])) {
					
					$editN = trim($GLOBALS['_POST']['edit_langN']);
					 
					if($editN == "")
						$errorN = "{s_error:fill}";
					elseif(in_array($editN, $this->o_lng->existNation) && $editN != next($elArr))
						$errorN = "{s_error:langexist}";
					elseif(!preg_match("/^[\pL ]+$/u", $editN))
						$errorN = "{s_error:wronglang}";
					elseif(strlen($editN) > 100)
						$errorN = "{s_error:langlenN}";
				}
				
				
				// Falls keine Fehler vorliegen
				if(!isset($errorC) && !isset($errorN)) {

			
					// Falls das Formular abgeschickt wurde (Dateiupload)
					if(isset($GLOBALS['_FILES']['edit_langF'])) {
						
						$isUploadFile	= true;
						$editF			= $GLOBALS['_FILES']['edit_langF']['name'];
						$editFTmp		= $GLOBALS['_FILES']['edit_langF']['tmp_name'];
					}
						
					// Falls keine Änderungen vorgenommen wurden
					if($editC == strtolower(trim(reset($elArr))) && $editN == next($elArr) && $editF == "") {
						$notice = "{s_notice:nochange}";
						$editF	= $editFileOld; // Falls keine Datei zum Upload ausgewählt wurde, alten Namen übernehmen
					}
							
					elseif($isUploadFile) { // Falls eine Datei zum Upload ausgewählt wurde
					
						$upload_file	= $editF;
						$upload_tmpfile	= $editFTmp;
						$fileExt		= Files::getFileExt($upload_file); // Bestimmen der Dateinamenerweiterung
						$fixName		= "flag_" . $editC . "." . $fileExt;
						$folder			= 'langs/' . $editC;
							
						$upload = Files::uploadFile($upload_file, $upload_tmpfile, $folder, "image", 54, 54, true, $fixName); // File-Upload
							
						if($upload !== true) {
							$errorF	= $upload;
						}
					}
							
					if($editC != $natCodeOld) { // Falls das Länderkürzel geändert wurde
							
						$fileExt = Files::getFileExt($editFileOld); // Bestimmen der Dateinamenerweiterung
						$fixName = "flag_" . $editC . "." . $fileExt;
						
						$langPath	= PROJECT_DOC_ROOT . "/langs";
						
						if(mkdir($langPath . "/" . $editC))
							copy($langPath . "/" . $natCodeOld . "/" . $editFileOld, $langPath . "/" . $editC . "/" . $fixName);
						else
							$errorF = "{s_error:uploadfail}";
								
					}
					else {
						$editF = $editFileOld; // Falls keine Datei zum Upload ausgewählt wurde und das Länderkürzel gleich geblieben ist, alten Namen übernehmen
						$fixName = $editFileOld;
					}
					
						
					if(!isset($errorF)) { // Falls der Upload erfolgreich war, Sprache ändern
					
						$natCode		= $this->DB->escapeString($editC);
						$nationality	= $this->DB->escapeString($editN);
						
						
						// db-Tabelle sperren
						$lock = $this->DB->query("LOCK TABLES `$this->tableLang`");
						
					
					
						// Update der Sprachfelder
						$updateSQL = $this->DB->query("UPDATE `$this->tableLang` 
															SET `nat_code` = '$natCode', 
															`nationality` = '$nationality',
															`flag_file` = '$fixName' 
															WHERE `nat_code` = '$natCodeOld'
															");
			
						// db-Sperre aufheben
						$unLock = $this->DB->query("UNLOCK TABLES");
						
						
						if($updateSQL === true) {
							
							
							if($editC != $natCodeOld) { // Falls das Sprachkürzel geändert wurde, Änderungen übernehmen
							
								$tablePages		= DB_TABLE_PREFIX . parent::$tablePages;

								// Tabellen contents_xyz updaten
								foreach(parent::$contentAndPreviewTables as $table) {
								
									$table		= DB_TABLE_PREFIX . $table;
									$conNr		= parent::getConNumber($table);
									$conCon		= "";
									$conField	= "";
									$conType	= "";
									$conUpd		= "";
									$defLang	= $this->o_lng->defLang;
									
									
									for($i = 1; $i <= $conNr; $i++) {
										
										$conCon = "CHANGE `con" . $i . "_" . $natCodeOld . "` `con" . $i . "_" . $natCode . "` MEDIUMTEXT NOT NULL,";
										$conField .= $conCon;
										
									}
									
									$conField = substr($conField, 0, -1);
				
									// db-Tabelle sperren
									$lock = $this->DB->query("LOCK TABLES `" . $table . "`");
				
				
				
									// Sprache ändern
									$alterSQL1 = $this->DB->query("ALTER TABLE `" . $table . "` " . $conField . "
																	   ");
									
			
									// db-Sperre aufheben
									$unLock = $this->DB->query("UNLOCK TABLES");
			
								} // Ende foreach
								

								// Update der Tabelle Pages
								$pageField =	"CHANGE `title_" . $natCodeOld . "` `title_" . $natCode . "` VARCHAR(100) NOT NULL," . 
												"CHANGE `alias_" . $natCodeOld . "` `alias_" . $natCode . "` VARCHAR(100) NOT NULL," . 
												"CHANGE `html_title_" . $natCodeOld . "` `html_title_" . $natCode . "` VARCHAR(100) NOT NULL," . 
												"CHANGE `description_" . $natCodeOld . "` `description_" . $natCode . "` VARCHAR(180) NOT NULL," . 
												"CHANGE `keywords_" . $natCodeOld . "` `keywords_" . $natCode . "` VARCHAR(300) NOT NULL";
		
								// db-Tabelle sperren
								$lock = $this->DB->query("LOCK TABLES `" . $tablePages . "`");
			
			
			
								// Suche nach Einträgen in gesetzter Sprache
								$alterSQL2 = $this->DB->query("ALTER TABLE `" . $tablePages . "` " . $pageField . " 
																   ");
			
								
								// Ggf. Menü-Views löschen
								$this->DB->dropViews(array( DB_TABLE_PREFIX . "menuViewTable-1",
															DB_TABLE_PREFIX . "menuViewTable0",
															DB_TABLE_PREFIX . "menuViewTable1",
															DB_TABLE_PREFIX . "menuViewTable2",
															DB_TABLE_PREFIX . "menuViewTable3")
													);
								
								
								// db-Sperre aufheben
								$unLock = $this->DB->query("UNLOCK TABLES");
		
		
		
								// Update der Tabelle galleries
								// Update der Tabelle galleries_images
								$gallName =		"CHANGE `name_" . $natCodeOld . "` `name_" . $natCode . "` TEXT NOT NULL";
		
								$gallField =	"CHANGE `title_" . $natCodeOld . "` `title_" . $natCode . "` VARCHAR(300) NOT NULL," . 
												"CHANGE `link_" . $natCodeOld . "` `link_" . $natCode . "` VARCHAR(300) NOT NULL," . 
												"CHANGE `text_" . $natCodeOld . "` `text_" . $natCode . "` TEXT NOT NULL";
		
								// db-Tabelle sperren
								$lock = $this->DB->query("LOCK TABLES	`" . DB_TABLE_PREFIX . "galleries`,
																		`" . DB_TABLE_PREFIX . "galleries_images`
														");
			
			
			
								// Suche nach Einträgen in gesetzter Sprache
								$alterSQL	= $this->DB->query("ALTER TABLE `" . DB_TABLE_PREFIX . "galleries` " . $gallName . " 
																   ");
			
								// Suche nach Einträgen in gesetzter Sprache
								$alterSQL2a	= $this->DB->query("ALTER TABLE `" . DB_TABLE_PREFIX . "galleries_images` " . $gallField . " 
																   ");
			
								
								// db-Sperre aufheben
								$unLock = $this->DB->query("UNLOCK TABLES");
		
		
		
								// Update der Tabellen articles/news/planner
								$newsField =	"CHANGE `header_" . $natCodeOld . "` `header_" . $natCode . "` VARCHAR(300) NOT NULL," . 
												"CHANGE `teaser_" . $natCodeOld . "` `teaser_" . $natCode . "` TEXT NOT NULL," . 
												"CHANGE `text_" . $natCodeOld . "` `text_" . $natCode . "` MEDIUMTEXT NOT NULL," .
												"CHANGE `tags_" . $natCodeOld . "` `tags_" . $natCode . "` TEXT NOT NULL," .
												"DROP INDEX `index_" . $natCodeOld . "`," .
												"ADD FULLTEXT `index_" . $natCode . "` (`header_" . $natCode . "`, `teaser_" . $natCode . "`, `text_" . $natCode . "`)," .
												"DROP INDEX `tags_" . $natCodeOld . "`," .
												"ADD FULLTEXT `tags_" . $natCode . "` (`tags_" . $natCode . "`)";
		
								// db-Tabelle sperren
								$lock = $this->DB->query("LOCK TABLES	`" . DB_TABLE_PREFIX . "articles`,
																		`" . DB_TABLE_PREFIX . "news`,
																		`" . DB_TABLE_PREFIX . "planner`,
																		`" . DB_TABLE_PREFIX . "search`
														");
			
			
			
								// Suche nach Einträgen in gesetzter Sprache
								$alterSQL2a = $this->DB->query("ALTER TABLE `" . DB_TABLE_PREFIX . "articles` " . $newsField . " 
																   ");
			
								
								// Suche nach Einträgen in gesetzter Sprache
								$alterSQL2b = $this->DB->query("ALTER TABLE `" . DB_TABLE_PREFIX . "news` " . $newsField . " 
																   ");
			
								
								// Suche nach Einträgen in gesetzter Sprache
								$alterSQL2c = $this->DB->query("ALTER TABLE `" . DB_TABLE_PREFIX . "planner` " . $newsField . " 
																   ");
			
								
								// Suche nach Einträgen in gesetzter Sprache
								$alterSQL2d = $this->DB->query("ALTER TABLE `" . DB_TABLE_PREFIX . "search` CHANGE `con_" . $natCodeOld . "` `con_" . $natCode . "` LONGTEXT NOT NULL,
																	 DROP INDEX `con_" . $natCodeOld . "`,
																	 ADD FULLTEXT `con_" . $natCode . "` (`con_" . $natCode . "`)
																	");
			
								// db-Sperre aufheben
								$unLock = $this->DB->query("UNLOCK TABLES");
		
		
		
								// Update der Tabelle xyz_categories
								$catField =		"CHANGE `category_" . $natCodeOld . "` `category_" . $natCode . "` VARCHAR(64) NOT NULL," . 
												"CHANGE `cat_teaser_" . $natCodeOld . "` `cat_teaser_" . $natCode . "` TEXT NOT NULL," .
												"DROP INDEX `cat_teaser_" . $natCodeOld . "`," .
												"ADD FULLTEXT `cat_teaser_" . $natCode . "` (`cat_teaser_" . $natCode . "`)";
		
		
								// db-Tabelle sperren
								$lock = $this->DB->query("LOCK TABLES	`" . DB_TABLE_PREFIX . "articles_categories`,
																		`" . DB_TABLE_PREFIX . "news_categories`,
																		`" . DB_TABLE_PREFIX . "planner_categories`
														");
			
			
			
								// Suche nach Einträgen in gesetzter Sprache
								$alterSQL3a = $this->DB->query("ALTER TABLE `" . DB_TABLE_PREFIX . "articles_categories` " . $catField . " 
																   ");
			
								
								// Suche nach Einträgen in gesetzter Sprache
								$alterSQL3b = $this->DB->query("ALTER TABLE `" . DB_TABLE_PREFIX . "news_categories` " . $catField . " 
																   ");
			
								
								// Suche nach Einträgen in gesetzter Sprache
								$alterSQL3c = $this->DB->query("ALTER TABLE `" . DB_TABLE_PREFIX . "planner_categories` " . $catField . " 
																   ");
			
								
								// db-Sperre aufheben
								$unLock = $this->DB->query("UNLOCK TABLES");
		
		
							
								// Tabelle forms und form_definitioins updaten
								$formsField =	"CHANGE `title_" . $natCodeOld . "` `title_" . $natCode . "` VARCHAR(200) NOT NULL," . 
												"CHANGE `notice_success_" . $natCodeOld . "` `notice_success_" . $natCode . "` VARCHAR(300) NOT NULL," . 
												"CHANGE `notice_error_" . $natCodeOld . "` `notice_error_" . $natCode . "` VARCHAR(300) NOT NULL," . 
												"CHANGE `notice_field_" . $natCodeOld . "` `notice_field_" . $natCode . "` VARCHAR(200) NOT NULL," . 
												"CHANGE `add_labels_" . $natCodeOld . "` `add_labels_" . $natCode . "` VARCHAR(2048) NOT NULL";
		
								$formsDefField =	"CHANGE `label_" . $natCodeOld . "` `label_" . $natCode . "` VARCHAR(300) NULL," . 
													"CHANGE `value_" . $natCodeOld . "` `value_" . $natCode . "` TEXT NULL," . 
													"CHANGE `options_" . $natCodeOld . "` `options_" . $natCode . "` VARCHAR(2048) NULL," .
													"CHANGE `notice_" . $natCodeOld . "` `notice_" . $natCode . "` VARCHAR(300) NULL," . 
													"CHANGE `linkval_" . $natCodeOld . "` `linkval_" . $natCode . "` VARCHAR(256) NULL," . 
													"CHANGE `header_" . $natCodeOld . "` `header_" . $natCode . "` VARCHAR(300) NULL," .
													"CHANGE `remark_" . $natCodeOld . "` `remark_" . $natCode . "` TEXT NULL";
		
		
								// db-Tabelle sperren
								$lock = $this->DB->query("LOCK TABLES	`" . DB_TABLE_PREFIX . "forms`,
																		`" . DB_TABLE_PREFIX . "forms_definitions`
														");
			
			
								// Tabelle um neue Sprache erweitern
								$alterSQL4a = $this->DB->query("ALTER TABLE `" . DB_TABLE_PREFIX . "forms` " . $formsField . "
																   ");
		
								// Tabelle um neue Sprache erweitern
								$alterSQL4b = $this->DB->query("ALTER TABLE `" . DB_TABLE_PREFIX . "forms_definitions` " . $formsDefField . "
																   ");

					
								// db-Sperre aufheben
								$unLock = $this->DB->query("UNLOCK TABLES");
		

								// History Tabellen updaten
								foreach(parent::$contentTables as $table) {
									
									$table		= DB_TABLE_PREFIX . $table . '_history';
									
									if(!$this->DB->tableExists($table))
										continue;
									
									
									$conNr		= parent::getConNumber($table, "", 3);
									$conCon		= "";
									$conField	= "";
									
									
									for($i = 1; $i <= $conNr; $i++) {
										
										$conCon = "CHANGE `con" . $i . "_" . $natCodeOld . "` `con" . $i . "_" . $natCode . "` MEDIUMTEXT NOT NULL,";
										$conField .= $conCon;
										
									}
									
									$conField = substr($conField, 0, -1);
				
									// db-Tabelle sperren
									$lock = $this->DB->query("LOCK TABLES `" . $table . "`");
				
				
				
									// Sprache ändern
									$alterSQL5 = $this->DB->query("ALTER TABLE `" . $table . "` " . $conField . "
																	   ");
									
			
									// db-Sperre aufheben
									$unLock = $this->DB->query("UNLOCK TABLES");
								}								
		
							}

							if($editC != $natCodeOld) { // Falls das Sprachkürzel geändert wurde, Änderungen für Dateien übernehmen
							
								$langPath	= PROJECT_DOC_ROOT . "/langs";

								// Sprachverzeichnis und staticText-File kopieren
								@copy($langPath . "/" . $natCodeOld . "/staticText_" . $natCodeOld . ".ini", $langPath . "/" . $editC . "/staticText_" . $editC . ".ini");
								@copy($langPath . "/" . $natCodeOld . "/nationality.txt", $langPath . "/" . $editC . "/nationality.txt");
								
								@unlink($langPath . "/" . $natCodeOld . "/" . $editFileOld);
								@unlink($langPath . "/" . $natCodeOld . "/staticText_" . $natCodeOld . ".ini");
								@unlink($langPath . "/" . $natCodeOld . "/nationality.txt");
								@rmdir($langPath . "/" . $natCodeOld);
								
								// Cache-Ordner aktualisieren
								if(is_dir(HTML_CACHE_DIR . $natCodeOld))
									@rename(HTML_CACHE_DIR . $natCodeOld, HTML_CACHE_DIR . $editC);
								
							}
							
							$this->setSessionVar('notice', "{s_notice:editlang}"); // Benachrichtigung in Session speichern

							// Ggf. edit_lang aus Session löschen
							$this->unsetSessionKey('edit_lang');
							
							header("Location: " . ADMIN_HTTP_ROOT . "?task=langs");
							exit;
						}
						else
							$errorC = "{s_error:error}";
					
					} // Ende falls kein Fehler
					
					else {
						$editC	= $natCodeOld;
					}
				
				} // Ende falls kein Fehler bei Input
					
				else {
					$editC	= $natCodeOld;
				}
				
			} // Ende Post edit_ln
			
			

			// Falls das Formular abgeschickt wurde (StaticText)
			if(isset($GLOBALS['_POST']['langKeys-group'])) {
				
				$statFileComment	= "; ini-file fuer statische Sprachbausteine_" . $editC . PHP_EOL;
				$statText			= array();
				$langStrings		= $GLOBALS['_POST']['langStrings-group'];
				
				foreach($GLOBALS['_POST']['langKeys-group'] as $groupKey => $langGroup) {
						
					$arrayKeys	= array();
					
					foreach($langGroup as $key => $langKey) {
						
						// Falls ein Schlüsselname vorhanden
						if($langKey != "") {
							
							// Falls Schlüsselgruppenname
							// Zeile generieren
							if($key === 0)
								$statText[] = PHP_EOL . $langKey . PHP_EOL;
							// Falls Schlüssel
							elseif($key > 0) {
						
								$tabArr		= explode("\t", $langKey);
								$langKey	= trim(reset($tabArr));
								
								// Key checken
								if(strlen($langKey) > 32)
									$errorKeys[$groupKey][$key] = "{s_install:toolong} (max. 32)";
								elseif(!preg_match("/^[A-Za-z0-9]{2,32}$/", $langKey) || 
										$langKey == "no" || 
										$langKey == "not" || 
										$langKey == "none" || 
										$langKey == "yes" || 
										$langKey == "true" || 
										$langKey == "false")
									$errorKeys[$groupKey][$key] = "{s_error:wronglang}";
								elseif(in_array($langKey, $arrayKeys))
									$errorKeys[$groupKey][$key] = "{s_notice:fieldexists}";
								else {
									
									// Tabstopps generieren
									$keyLen		= strlen($langKey);
									$tabs		= "\t";
									
									while($keyLen < 12) {
										$tabs	.= "\t";
										$keyLen += 4;
									}
								}
								// Zeile generieren
								$statText[] = $langKey . $tabs . '= "' . str_replace(array("\n", "\r"), "", str_replace('"', "&quot;", nl2br($langStrings[$groupKey][$key]))) . '"' . PHP_EOL;
							}
							
							$arrayKeys[] = $langKey;
						}
					}
				}
				
				// Falls kein Fehler
				if(count($errorKeys) == 0) {
					// Falls Änderungen gespeichert wurden
					if(@file_put_contents(PROJECT_DOC_ROOT . '/langs/' . $natCodeOld . '/staticText_' . $natCodeOld . '.ini', $statFileComment . implode("", $statText)))
						$success = "{s_notice:takechange}";
					else
						$notice = "{s_error:failchange}";
				}
				else
					$notice = "{s_error:failchange} {s_error:check}";
				
			}
			// Andernfalls Sprachbausteine aus Datei zeilenweise in Array auslesen
			else {
				$statText = @file(PROJECT_DOC_ROOT . '/langs/' . $editC . '/staticText_' . $editC . '.ini', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
				if(strpos($statText[0], ";") === 0)
					array_shift($statText);
			}
			

			// Submitbutton
			$submitButton	= 	'<li class="submit change">' . PHP_EOL;
		
			// Button submit
			$btnDefs	= array(	"type"		=> "submit",
									"name"		=> "edit_lang",
									"class"		=> "change",
									"value"		=> "{s_button:takechange}",
									"icon"		=> "ok"
								);
			
			$submitButton	.=	parent::getButton($btnDefs);
			
			$submitButton	.=	'<input name="edit_lang" type="hidden" value="{s_button:takechange}" />' . PHP_EOL .
								'</li>{up}' . PHP_EOL;
			
			
			// HTML
			if(isset($success))
				$this->adminContent .='<p class="notice succes">' . $success . '</p>' . PHP_EOL;

			if(isset($notice))
				$this->adminContent .='<p class="notice error">' . $notice . '</p>' . PHP_EOL;

			$this->adminContent .=	'<h2 class="cc-section-heading cc-h2">{s_header:editlang}</h2>' . PHP_EOL .
									'<h3 class="cc-h3 toggle' . (isset($GLOBALS['_POST']['edit_statText']) || isset($errorC) || isset($errorN) || isset($errorF) ? '' : ' hideNext') . '">{s_header:editlangdetail}</h3>' . PHP_EOL;

			$this->adminContent .=	'<div class="adminBox">' . PHP_EOL .
									'<form action="' . $formAction . '" name="adminfm" id="adminfm1" method="post" enctype="multipart/form-data">' . PHP_EOL .
									'<input name="edit_ln" type="hidden" value="' . htmlspecialchars($GLOBALS['_POST']['edit_ln']) . '" />' . PHP_EOL .
									'<input type="hidden" name="token" class="token" value="' . parent::$token . '" />' . PHP_EOL .
									'<ul class="adminSection">' . PHP_EOL;
			
			$this->adminContent .=	'<li><label>{s_label:lang} / {s_label:langN}</label>' . PHP_EOL;
								
			if(isset($GLOBALS['_POST']['edit_langN']) && isset($errorN))
				$this->adminContent .= '<p class="notice">' . $errorN . '</p>';
			
			$this->adminContent .=	'<input type="text" name="edit_langN" maxlength="100"';
			
			isset($editN) ? $value = htmlentities($editN, ENT_QUOTES, "UTF-8") : $value = "";

			$this->adminContent .=	' value="' . $value . '" /></li>' . PHP_EOL;
			
			$this->adminContent .=	'<li><label>{s_label:langCC}</label>' . PHP_EOL;
			
			if(isset($GLOBALS['_POST']['edit_langC']) && isset($errorC))
				$this->adminContent .= '<p class="notice">' . $errorC . '</p>';
			
			$this->adminContent .=	'<input type="text" name="edit_langC" maxlength="3"';
			
			isset($editC) ? $value = htmlspecialchars($editC) : $value = "";
								
			$this->adminContent .=	' value="' . $value . '" /></li>' . PHP_EOL;
			
			$this->adminContent .=	'<li><label>{s_label:langF}</label>' . PHP_EOL;
								
			if(isset($errorF))
				$this->adminContent .= '<p class="notice">' . $errorF . '</p>';
			
			$this->adminContent .=	'<img src="' . PROJECT_HTTP_ROOT . "/langs/" . $editC . "/" . $editF . '" alt="' . $editF . '" title="' . $editF . '" class="lang" />' . PHP_EOL .
									'<input type="file" name="edit_langF" style="float:right"/><br class="clearfloat" />' . PHP_EOL;
								
			$this->adminContent .=	'</li>' . PHP_EOL .
									'</ul>' . PHP_EOL .
									'<ul>' . PHP_EOL .
									$submitButton .
									'</ul>' . PHP_EOL .
									'</form>' . PHP_EOL .
									'</div>' . PHP_EOL;
									
			// Sprachbausteine Edit
			$this->adminContent .=	'<h3 class="cc-h3 toggle' . (isset($GLOBALS['_POST']['edit_langC']) ? ' hideNext' : '') . '">{s_header:stattext}</h3>' . PHP_EOL . 
									'<div class="editStatText adminBox">' . PHP_EOL;
			
			
			// Reset search button
			$resetSearchBtn	= '<span class="editButtons-panel">' . PHP_EOL;

			// Button new
			$btnDefs	= array(	"type"		=> "button",
									"name"		=> "reset_search_val",
									"class"		=> 'resetSearchVal button-icon-only',
									"value"		=> "true",
									"text"		=> "",
									"title"		=> '{s_label:reset}',
									"icon"		=> "close"
								);
			
			$resetSearchBtn .=	parent::getButton($btnDefs);
									
			$resetSearchBtn .=	'</span>' . PHP_EOL;
			
			// Search-Box
			$this->adminContent .=	'<div class="controlBar">' . PHP_EOL .
									'<div class="langKeySearch-panel left">' . PHP_EOL .
									'<label>{s_label:searchfor} {s_label:langkey}</label>' . PHP_EOL .
									'<span class="singleInput-panel">' . PHP_EOL .
									'<input type="text" name="langKeySearch" class="langKeySearch listSearch input-button-right" value="" />' . PHP_EOL .
									$resetSearchBtn .
									'</span>' . PHP_EOL .
									'</div>' . PHP_EOL .
									'<div class="langStringSearch-panel left">' . PHP_EOL .
									'<label>{s_label:searchfor} {s_label:langstring}</label>' .
									'<span class="singleInput-panel">' . PHP_EOL .
									'<input type="text" name="langStringSearch" class="langStringSearch listSearch input-button-right" value="" />' . PHP_EOL .
									$resetSearchBtn .
									'</span>' . PHP_EOL .
									'</div>' . PHP_EOL .
									'<br class="clearfloat" />' . PHP_EOL;
							
		
			$this->adminContent	.=	'<script type="text/javascript">' . PHP_EOL .
									'head.ready("jquery",function(){' . PHP_EOL .
										'$(document).ready(function(){' .
											'$(".resetSearchVal").bind("click", function(){
												$(this).closest(".singleInput-panel").children(".listSearch").val("").focus().trigger("keyup").blur();
											});' .
										'});' . PHP_EOL .
									'});' . PHP_EOL .
									'</script>' . PHP_EOL;
				
			$this->adminContent .=	'</div>' . PHP_EOL;

		
			$this->adminContent .=	'<form action="' . $formAction . '" name="adminfm" id="adminfm2" method="post">' . PHP_EOL .
									'<input name="edit_ln" type="hidden" value="' . htmlspecialchars($GLOBALS['_POST']['edit_ln']) . '" />' . PHP_EOL;
									#'<input type="hidden" name="token" class="token" value="' . parent::$token . '" />' . PHP_EOL;
			
			$lineCount	= 0;
			$groupCount	= 1;
			$fieldNote	= '<p class="notice error">{error}</p>';
			$errorKey 	= false;


			// Sprachbausteine auslesen und auflisten
			foreach($statText as $line) {
				
				
				// Zusatzfeld für neue Sprachbausteine
				$addLangKey1	=	'<li class="statText addLangKey">' . PHP_EOL .
									'<label class="addLangKey">' . parent::getIcon("new") . '{s_label:addlangkey}</label>' . PHP_EOL .
									'<div class="addLangKey">' . PHP_EOL .
									'<label>{s_label:langkey}</label>' . PHP_EOL .
									'<label>{s_label:langstring}</label>' . PHP_EOL;
									
				// Feld für innerhalb der Liste
				$addLangKey2a	=	'<input name="langKeys-group[' . $groupCount . '][' . $lineCount . ']" type="text" value="" class="langKey" />' . PHP_EOL .
									'<textarea name="langStrings-group[' . $groupCount . '][' . $lineCount . ']" rows="1" class="langString singleLine"></textarea>' . PHP_EOL;
				
				// Feld für Ende der Liste		
				$addLangKey2b	=	'<input name="langKeys-group[' . $groupCount . '][' . ($lineCount + 1 ) . ']" type="text" value="" class="langKey" />' . PHP_EOL .
									'<textarea name="langStrings-group[' . $groupCount . '][' . ($lineCount + 1) . ']" rows="1" class="langString singleLine"></textarea>' . PHP_EOL;
									
				$addLangKey3	=	'</div>' . PHP_EOL .
									'</li>' . PHP_EOL;
								
				$line = trim($line);
				
				// Falls eine Sprachbausteingruppe beginnt
				if(strpos($line, "[") === 0) {
						
					// Falls nicht erste statText-Gruppe, leeres Input-Feld hinzufügen und Liste schließen
					if($lineCount > 0) {
										
						$this->adminContent .=	$addLangKey1 . $addLangKey2a . $addLangKey3 .
											'</ul>' . PHP_EOL .
											'<ul>' . PHP_EOL .
											$submitButton .
											'</ul>' . PHP_EOL .
											'</div>';
						$lineCount	= 0;
						$groupCount++;

					}
					
					$this->adminContent .=	'<h4 class="cc-h4 editLangHeader toggle">' . htmlspecialchars($line) . '<span class="editLangFlag"><img src="' . PROJECT_HTTP_ROOT . "/langs/" . $editC . "/" . $editF . '" alt="' . $editF . '" title="' . $editN . ' (' . $editC . ')" class="flag" /></span></h4>' . PHP_EOL .
											'<div class="adminSubBox">' . PHP_EOL .
											'<ul class="adminSection">' . PHP_EOL .
											'<li class="statText headLabel">' . PHP_EOL .
											'<label>{s_label:langkey}</label>' . PHP_EOL .
											'<label>{s_label:langstring}</label>' . PHP_EOL .
											'<br class="clearfloat" />' . PHP_EOL .
											'<input name="langKeys-group[' . $groupCount . '][' . $lineCount . ']" type="hidden" value="' . htmlspecialchars($line, ENT_QUOTES, "UTF-8") . '" />' . PHP_EOL .
											'<input name="langStrings-group[' . $groupCount . '][' . $lineCount . ']" type="hidden" value="" />' . PHP_EOL .
											'</li>' . PHP_EOL;
				}
				else {
					$line		= explode('= "', $line);
					$tabArr		= explode("\t", $line[0]);
					$langKey	= reset($tabArr);
					$langDef	= "";
					if(isset($line[1])) {
						$langDef	= str_replace("<br />", PHP_EOL, substr($line[1], 0, -1));
						$langDef	= str_replace("&quot;", '"', $langDef);
					}
					$defLen		= strlen($langDef);
					
				
					if(array_key_exists($groupCount, $errorKeys) && $errorKeys[$groupCount] == $lineCount)
						$this->adminContent .= $fieldNote;
										
					$this->adminContent .=	'<li class="statText">' . PHP_EOL;
					
					// Falls ein Fehler für diesen Schlüssel vorliegt
					if(array_key_exists($groupCount, $errorKeys) && array_key_exists($lineCount, $errorKeys[$groupCount])) {
						$this->adminContent .=	str_replace("{error}", $errorKeys[$groupCount][$lineCount], $fieldNote);
						$errorKey = true;
					}
						
					$this->adminContent .=	'<input name="langKeys-group[' . $groupCount . '][' . $lineCount . ']" type="text" value="' . htmlspecialchars($langKey, ENT_QUOTES, "UTF-8") . '" class="langKey"' . ($errorKey ? '' : ' readonly="readonly"') . ' />' . PHP_EOL .
											'<textarea name="langStrings-group[' . $groupCount . '][' . $lineCount . ']" rows="' . ($defLen > 180 ? 4 : ($defLen > 120 ? 3 : ($defLen > 60 ? 2 : 1))) . '" class="langString' . ($defLen <= 60 ? ' singleLine' : '') . '">' . htmlspecialchars($langDef, ENT_QUOTES, "UTF-8") . '</textarea>' . PHP_EOL . 
											'</li>' . PHP_EOL;
				}
				$lineCount++;
				$errorKey = false;
			}
								
			$this->adminContent .=	$addLangKey1 . $addLangKey2b . $addLangKey3 .
								'</ul>' . PHP_EOL .
								'<ul>' . PHP_EOL .
								$submitButton .
								'</ul>' .
								'</div>' .
								'</form>' .
								'</div>';
		}


		#############  Sprache löschen  ###############

		// Falls die Sprache gelöscht werden soll
		elseif(isset($GLOBALS['_POST']['del_ln'])) {

			$delLang = $GLOBALS['_POST']['del_ln'];	
			
			$deleted = false;
			
			// Datenbanksuche nach zu löschender Sprache
			$query = $this->DB->query("SELECT * 
											FROM `$this->tableLang` 
											WHERE `nat_code` = '$delLang' 
											", false);
			
			if(count($query) > 0) {
					
				#var_dump($query);
				
				$flagFile		= $query[0]['flag_file'];
				$defLang		= $query[0]['def_lang'];
				$nationality	= $query[0]['nationality'];
				

				if(isset($GLOBALS['_POST']['delete']) && $GLOBALS['_POST']['delete'] != "" && $defLang != 1) { // Falls das Löschen bestätigt wurde
					
					$tablePages		= DB_TABLE_PREFIX . parent::$tablePages;
					
					// db-Tabelle sperren
					$lock = $this->DB->query("LOCK TABLES	`" . $this->tableLang . "`,
															`" . $tablePages . "`,
															`" . DB_TABLE_PREFIX . "galleries`,
															`" . DB_TABLE_PREFIX . "galleries_images`,
															`" . DB_TABLE_PREFIX . "articles`,
															`" . DB_TABLE_PREFIX . "articles_categories`,
															`" . DB_TABLE_PREFIX . "news`,
															`" . DB_TABLE_PREFIX . "news_categories`,
															`" . DB_TABLE_PREFIX . "planner`,
															`" . DB_TABLE_PREFIX . "planner_categories`,
															`" . DB_TABLE_PREFIX . "search`,
															`" . DB_TABLE_PREFIX . "forms`,
															`" . DB_TABLE_PREFIX . "forms_definitions`
											");
			
					
					
					// Löschen der Sprache aus den contents-Tabellen
					$deleteSQL1 = $this->DB->query("DELETE 
														FROM `$this->tableLang` 
														WHERE `nat_code` = '$delLang'
														");
				
			
					$alterSQL1 = $this->DB->query("ALTER TABLE `" . $tablePages . "` 
														DROP `title_" . $delLang . "`,
														DROP `alias_" . $delLang . "`,
														DROP `html_title_" . $delLang . "`,
														DROP `description_" . $delLang . "`,
														DROP `keywords_" . $delLang . "`
														");
				
				
					// Löschen der Sprachinhalte aus den contents-Tabellen
					foreach(parent::$contentAndPreviewTables as $table) {
					
						$table		= DB_TABLE_PREFIX . $table;
						$conNr		= parent::getConNumber($table);
						$conCon		= "";
						$conField	= "";			
						
						for($i = 1; $i <= $conNr; $i++) {
							
							$conCon = "DROP `con" . $i . "_" . $delLang . "`,";
							$conField .= $conCon;
							
						}
						
						$conField = substr($conField, 0, -1);
						
						
						// db-Tabelle sperren
						$lock = $this->DB->query("LOCK TABLES `" . $table . "`");
				
						
						$alterSQL2 = $this->DB->query("ALTER TABLE `" . $table . "` " . $conField . "
														");
						
					}
					
				
					// Löschen der Sprachinhalte aus der galleries_images-Tabelle
					$alterSQL3 = $this->DB->query("ALTER TABLE `" . DB_TABLE_PREFIX . "galleries` 
														DROP `name_" . $delLang . "`
														");
				
					// Löschen der Sprachinhalte aus der galleries_images-Tabelle
					$alterSQL3a = $this->DB->query("ALTER TABLE `" . DB_TABLE_PREFIX . "galleries_images` 
														DROP `title_" . $delLang . "`,
														DROP `link_" . $delLang . "`,
														DROP `text_" . $delLang . "`
														");
				
				
					// Löschen der Sprachinhalte aus der articles-Tabelle
					$alterSQL4a = $this->DB->query("ALTER TABLE `" . DB_TABLE_PREFIX . "articles` 
														DROP `header_" . $delLang . "`,
														DROP `teaser_" . $delLang . "`,
														DROP `text_" . $delLang . "`,
														DROP `tags_" . $delLang . "`,
														DROP INDEX `index_" . $delLang . "`
														");
				
					
					// Löschen der Sprachinhalte aus der news-Tabelle
					$alterSQL4b = $this->DB->query("ALTER TABLE `" . DB_TABLE_PREFIX . "news` 
														DROP `header_" . $delLang . "`,
														DROP `teaser_" . $delLang . "`,
														DROP `text_" . $delLang . "`,
														DROP `tags_" . $delLang . "`,
														DROP INDEX `index_" . $delLang . "`
														");
				
					
					// Löschen der Sprachinhalte aus der planner-Tabelle
					$alterSQL4c = $this->DB->query("ALTER TABLE `" . DB_TABLE_PREFIX . "planner` 
														DROP `header_" . $delLang . "`,
														DROP `teaser_" . $delLang . "`,
														DROP `text_" . $delLang . "`,
														DROP `tags_" . $delLang . "`,
														DROP INDEX `index_" . $delLang . "`
														");
				
					
					// Löschen der Sprachinhalte aus der articles_categories-Tabelle
					$alterSQL5a = $this->DB->query("ALTER TABLE `" . DB_TABLE_PREFIX . "articles_categories` 
														DROP `category_" . $delLang . "`,
														DROP `cat_teaser_" . $delLang . "`,
														DROP INDEX `cat_teaser_" . $delLang . "`
														");
				
					
					// Löschen der Sprachinhalte aus der news_categories-Tabelle
					$alterSQL5b = $this->DB->query("ALTER TABLE `" . DB_TABLE_PREFIX . "news_categories` 
														DROP `category_" . $delLang . "`,
														DROP `cat_teaser_" . $delLang . "`,
														DROP INDEX `cat_teaser_" . $delLang . "`
														");
				
					
					// Löschen der Sprachinhalte aus der planner_categories-Tabelle
					$alterSQL5c = $this->DB->query("ALTER TABLE `" . DB_TABLE_PREFIX . "planner_categories` 
														DROP `category_" . $delLang . "`,
														DROP `cat_teaser_" . $delLang . "`,
														DROP INDEX `cat_teaser_" . $delLang . "`
														");
				
					
					// Löschen der Sprachinhalte aus der search-Tabelle
					$alterSQL6 = $this->DB->query("ALTER TABLE `" . DB_TABLE_PREFIX . "search` 
														DROP `con_" . $delLang . "`,
														DROP INDEX `con_" . $delLang . "`
														");
				
					
					// Löschen der Sprachinhalte aus der forms-Tabelle
					$alterSQL7 = $this->DB->query("ALTER TABLE `" . DB_TABLE_PREFIX . "forms` 
														DROP `title_" . $delLang . "`,
														DROP `notice_success_" . $delLang . "`,
														DROP `notice_error_" . $delLang . "`,
														DROP `notice_field_" . $delLang . "`,
														DROP `add_labels_" . $delLang . "`
														");
				
					
					// Löschen der Sprachinhalte aus der forms_definitions-Tabelle
					$alterSQL8 = $this->DB->query("ALTER TABLE `" . DB_TABLE_PREFIX . "forms_definitions` 
														DROP `label_" . $delLang . "`,
														DROP `value_" . $delLang . "`,
														DROP `options_" . $delLang . "`,
														DROP `notice_" . $delLang . "`,
														DROP `linkval_" . $delLang . "`,
														DROP `header_" . $delLang . "`,
														DROP `remark_" . $delLang . "`
														");
					

					// History löschen
					foreach(parent::$contentTables as $table) {
						
						$table		= DB_TABLE_PREFIX . $table . '_history';
						
						$dropSQL	= $this->DB->query("DROP TABLE IF EXISTS `" . $table . "`");
					}
					
					
					// db-Sperre aufheben
					$unLock = $this->DB->query("UNLOCK TABLES");



					if($deleteSQL1 == true && $alterSQL1 == true && $alterSQL2 == true) { // Falls die db-Updates erfolgreich waren, Sprachordner/-datei löschen
						
						// Cache-Ordner löschen
						if(is_dir(HTML_CACHE_DIR . $delLang))
							parent::unlinkRecursive(HTML_CACHE_DIR . $delLang, true);
						
						
						// Sprache aus Array installedLangs entfernen
						$key = array_search($delLang, $this->o_lng->installedLangs);
						$this->o_lng->installedLangs = array_splice($this->o_lng->installedLangs, $key, 1);

						if($this->g_Session['lang'] == $delLang)		$this->setSessionVar('lang', $this->o_lng->defLang);
						if($this->g_Session['edit_lang'] == $delLang)	$this->setSessionVar('edit_lang', $this->o_lng->defLang);
												
						$this->adminContent .=	'<h2 class="cc-section-heading cc-h2">{s_header:dellang}</h2>' . PHP_EOL .
												'<p class="notice success">{s_notice:dellang}</p>' . PHP_EOL;
											
						$deleted = true;
						
					}
					
					#var_dump($deleteSQL1.$deleteSQL2.$updateSQL1.$updateSQL2);
			
						#exit;
			
				} // Ende löschen bestätigt
			
			} // Ende falls zu löschender Eintrag existiert
			
			#var_dump($query);


			if($defLang == 1) // Falls die Sprache Hauptsprache ist, kein Löschen möglich
			
				$this->adminContent .=	'<p class="notice error">' . sprintf(ContentsEngine::replaceStaText("{s_notice:nodellang}"), '&quot;' . $delLang . '&quot;') . '</p>' . PHP_EOL;	
									
									
			elseif($deleted == false) {
									
				$this->adminContent .=	'<p class="notice error">{s_notice:dellangconf}</p>' . PHP_EOL . 
										'<ul class="framedItems">' . PHP_EOL . 
										'<li>' . PHP_EOL . 
										'<span class="delbox">' . PHP_EOL .
										'{s_text:dellang} ' .
										parent::getIcon("warning", "inline-icon", 'title="{s_title:dellang}"') .
										'<br /><br /><strong>' . $nationality . ' (' . strtoupper($delLang) . ')</strong> ' .
										'<img src="' . PROJECT_HTTP_ROOT . "/langs/" . $delLang . "/" . $flagFile . '" alt="' . $delLang . '" />' .
										'</span>' . PHP_EOL . 
										'</li>' . PHP_EOL . 
										'<li class="change submit">' . PHP_EOL .
										'<form action="' . $formAction . '" id="adminfm2" method="post">' . PHP_EOL;
		
				// Button submit (delete)
				$btnDefs	= array(	"type"		=> "submit",
										"name"		=> "cancel",
										"id"		=> "adminfm2",
										"class"		=> "cancel right",
										"value"		=> "{s_button:cancel}",
										"icon"		=> "cancel"
									);
				
				$this->adminContent .=	parent::getButton($btnDefs);
			
				$this->adminContent .=	'<input name="cancel" type="hidden" value="{s_button:cancel}" />' . PHP_EOL . 
										'<input type="hidden" name="token" class="token" value="' . parent::$token . '" />' . PHP_EOL . 
										'</form>' . PHP_EOL .
										'<form action="' . $formAction . '" id="adminfm" method="post">' . PHP_EOL;
		
				// Button submit (delete)
				$btnDefs	= array(	"type"		=> "submit",
										"name"		=> "submit",
										"class"		=> "delete",
										"value"		=> "{s_button:delete}",
										"icon"		=> "delete"
									);
				
				$this->adminContent .=	parent::getButton($btnDefs);
										
				$this->adminContent .=	'<input type="hidden" name="step" id="step" value="del" /> ' . PHP_EOL . 
										'<input type="hidden" name="delete" value="' . $delLang . '" />' . PHP_EOL . 
										'<input type="hidden" name="del_ln" value="' . $delLang . '" />' . PHP_EOL . 
										'<input type="hidden" name="token" class="token" value="' . parent::$token . '" />' . PHP_EOL . 
										'</form>' . PHP_EOL . 
										'</li>' . PHP_EOL . 
										'</ul>' . PHP_EOL;
			}
			
		}


		else {
				
			// Notifications
			if(isset($notice))
				$this->adminContent .= '<p class="notice success">' . $notice . '</p>';
			
			$this->adminContent		.= $this->getSessionNotifications("notice", true);
			
			$showBackButton = false;
			
			// Sprachverwaltung Html
			$this->adminContent .=	$this->getEditLangSection($this->lang);
			
		} // Ende else


		$this->adminContent .=	'</div>' . PHP_EOL;


		// Zurückbuttons
		$this->adminContent .=	'<p>&nbsp;</p>' . PHP_EOL . 
								'<div class="adminArea">' . PHP_EOL . 
								'<ul><li class="submit back">' . PHP_EOL;

		if($showBackButton) {
			$this->adminContent .=	'<form action="' . $formAction . '" id="adminfm3" method="post">' . PHP_EOL;
		
			// Button back
			$btnDefs	= array(	"type"		=> "submit",
									"name"		=> "backtolang",
									"id"		=> "backtolang",
									"class"		=> "back left",
									"value"		=> "{s_button:adminlang}",
									"icon"		=> "backtolist"
								);
			
			$this->adminContent .=	parent::getButton($btnDefs);
	
			$this->adminContent .=	'<input type="hidden" name="token" class="token" value="' . parent::$token . '" />' . PHP_EOL . 
									'</form>' . PHP_EOL;
		}
		
		
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
		
		
	
	/**
	 * Sprachverwaltung (Sprachen bearbeiten)
	 * 
	 * @access	public
     * @param	aktuelle Sprache
	 * @return	string
	 */
	public function getEditLangSection($lang)
	{
    		
		$langsList = 		'<h2 class="toggle cc-section-heading cc-h2">{s_header:newlang}</h2>' . PHP_EOL .
							'<ul class="editList cc-list cc-list-large">' . PHP_EOL . 
							'<li class="listItem"><span class="listName">{s_text:newlang}</span>' . PHP_EOL . 
							'<span class="editButtons-panel">' . PHP_EOL;
		
		$langsList .=		'<form action="" class="adminfm1" method="post">' . PHP_EOL;
		
		// Button new
		$btnDefs	= array(	"type"		=> "submit",
								"name"		=> "new_ln",
								"class"		=> 'button-icon-only',
								"value"		=> "new_ln",
								"text"		=> "",
								"title"		=> '{s_text:newlang}',
								"attr"		=> 'data-ajaxform="true"',
								"icon"		=> "new"
							);
			
		$langsList .=	parent::getButton($btnDefs);

		$langsList .=		'<input type="hidden" name="new_ln" value="new_ln" />' . PHP_EOL . 
							'<input type="hidden" name="token" value="' . parent::$token . '" />' . PHP_EOL . 
							'</form>' . PHP_EOL .
							'</span>' . PHP_EOL .
							'</li></ul>' . PHP_EOL .
							'<h2 class="toggle cc-section-heading cc-h2">{s_header:installedlangs}</h2>' . PHP_EOL .
							'<ul id="sortableLangs" class="langs editList list list-condensed sortable-container" data-url="' . SYSTEM_HTTP_ROOT . '/access/editLangs.php?page=admin&action=sort">' . PHP_EOL;
		
		$i = 0;
		$j = 1;
		
		foreach($this->installedLangs as $lang) {
		
		
			$langsList .= 		'<li id="'.$lang.'" class="listItem sortid-'.$j.'" data-sortid="'.$j.'" data-sortidold="'.$j.'" data-menu="context" data-target="contextmenu-' . $j . '">' . PHP_EOL .
								'<span class="listName">' . $this->o_lng->existNation[$i] . ' (' . strtoupper($lang) . ')</span>' . PHP_EOL . 
								'<span class="langFlag"><img src="' . PROJECT_HTTP_ROOT . '/langs/' . $lang . '/' . $this->o_lng->existFlag[$i] . '" title="' . $this->o_lng->existNation[$i] . '" class="lang" /></span>' . PHP_EOL . 
								'<span class="changeDefLang" data-url="' . SYSTEM_HTTP_ROOT . '/access/editLangs.php?defln=' . $lang . '" data-deflang="' . $this->o_lng->defLang . '">' . PHP_EOL .
								'<label class="radioBox markBox">' . PHP_EOL .
								'<input type="radio" name="def_lang" title="{s_title:mainlang} &quot;' . $this->o_lng->defLang . '&quot;" value="' . $lang . '" data-lang="' . $lang . '"';
			
			if($this->o_lng->defLang == $lang)
				$langsList .= 	' checked="checked" /></label><i class="defLang">{s_text:deflang}</i>' . PHP_EOL;
				
			else
				$langsList .= 	' /></label>' . PHP_EOL;
			
			$langsList .= 		'</span>' . PHP_EOL .
								'<span class="editButtons-panel" data-id="contextmenu-' . $j . '">' . PHP_EOL;
		
			$langsList .=		'<form action="' . ADMIN_HTTP_ROOT . '?task=langs" class="adminfm1" method="post">' . PHP_EOL;
			
			// Button edit
			$btnDefs	= array(	"type"		=> "submit",
									"name"		=> "edit_ln",
									"class"		=> 'button-icon-only',
									"value"		=> $lang.'#'.$this->o_lng->existNation[$i].'#'.$this->o_lng->existFlag[$i],
									"text"		=> "",
									"title"		=> '{s_header:editlang} &quot;' . $lang . '&quot;',
									"attr"		=> 'data-ajaxform="true" data-menuitem="true" data-id="item-id-' . $j . '"',
									"icon"		=> "edit"
								);
				
			$langsList .=	parent::getButton($btnDefs);
								
			$langsList .=		'<input type="hidden" name="edit_ln" value="' . $lang.'#'.$this->o_lng->existNation[$i].'#'.$this->o_lng->existFlag[$i] . '" />' . PHP_EOL . 
								'<input type="hidden" name="token" value="' . parent::$token . '" />' . PHP_EOL . 
								'</form>' . PHP_EOL; 
								
			if(count($this->o_lng->installedLangs) > 1) {			

				$langsList .= 	'<form action="' . ADMIN_HTTP_ROOT . '?task=langs" class="adminfm2" method="post">' . PHP_EOL;
				
				// Button delete
				$btnDefs	= array(	"type"		=> "submit",
										"name"		=> "del_ln",
										"class"		=> 'button-icon-only',
										"value"		=> $lang,
										"text"		=> "",
										"title"		=> '{s_header:dellang} &quot;' . $lang . '&quot;',
										"attr"		=> 'data-ajaxform="true" data-menuitem="true" data-id="item-id-' . $j . '"',
										"icon"		=> "delete"
									);
					
				$langsList .=	parent::getButton($btnDefs);
				
				$langsList .= 	'<input type="hidden" name="del_ln" value="' . $lang . '" />' . PHP_EOL . 
								'<input type="hidden" name="token" value="' . parent::$token . '" />' . PHP_EOL . 
								'</form>' . PHP_EOL;
			}
			
			$langsList .= 		'</span></li>' . PHP_EOL;
			
			$i++;
			$j++;
								
		}
		
		$langsList .= 			'</ul>' . PHP_EOL;

		
		// Contextmenü-Script
		$langsList .=	$this->getContextMenuScript();

				
		// Sortable
		$langsList .=	$this->getSortScript();

		
		return $langsList;
		
	}

	
	// getSortScript
	protected function getSortScript()
	{
	
		return	'<script>' . PHP_EOL .
				'head.ready(function(){' . PHP_EOL .
				'head.load({sort:"' . PROJECT_HTTP_ROOT . '/system/access/js/adminSort.min.js"}, function(){' . PHP_EOL .
					'$(document).ready(function(){' . PHP_EOL .
						'$.sortableLangs();' . PHP_EOL .
					'});' . PHP_EOL .
				'});' . PHP_EOL .
				'});' . PHP_EOL .
				'</script>' . PHP_EOL;
	
	}

	
	// getSelectmenuScript
	protected function getSelectmenuScript()
	{

		return	'<script>' . PHP_EOL .
				'head.ready(function(){' . PHP_EOL .
					'$(document).ready(function(){' . PHP_EOL .
						'$("#countrySelect").iconselectmenu({select: function( event, ui ) { var nat = $(this).children("option:selected").attr("data-nationality"); if(typeof(nat) == "undefined"){ nat = ""; } $("#new_langN").val(nat); }}).iconselectmenu( "menuWidget" ).addClass( "countrySelect f16" );' . PHP_EOL .
					'});' . PHP_EOL .
				'});' . PHP_EOL .
				'</script>' . PHP_EOL;
	
	}

}
