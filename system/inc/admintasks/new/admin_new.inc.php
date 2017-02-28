<?php
namespace Concise;



###################################################
################  Neu-Bereich  ####################
###################################################

// Step 1

class Admin_New extends Admin implements AdminTask
{

	public $newItem			= "";
	public $newItemName		= "";
	public $newPageDetails	= false;
	public $pageTemplate	= "standard.tpl";
	public $error			= "";
	private $tablePagesDB	= "";
	
	public function __construct($DB, $o_lng, $task, $init = false)
	{

		// Admin-Elternklasse aufrufen
		parent::__construct($DB, $o_lng);
		
		parent::$task = $task;
		
		$this->tablePagesDB	= DB_TABLE_PREFIX . parent::$tablePages;
		
		// Datenbank-Engine auf InnoDB setzen
		$this->setDbEngine($this->tablePagesDB, "InnoDB");

	}
	
	
	public function getTaskContents($ajax = false)
	{

		// Enthält Headerbox
		$this->adminHeader	=	'{s_text:adminnew}' . PHP_EOL . 
								$this->closeTag("#headerBox");
		
		// #adminContent
		$this->adminContent =	$this->openAdminContent();

		$this->formAction 	= ADMIN_HTTP_ROOT . '?task=new';
		
		$parentLft			= "";
		$parentRgt			= "";
		
		

		// Locking checken
		if($this->checkLocking("all", "editpages", $this->g_Session['username'], '{s_error:lockpages}')) {
			$this->adminContent .=	$this->getBackButtons("main");
			// #adminContent close
			$this->adminContent	.= $this->closeAdminContent();
			return $this->adminContent;
		}
		
			
		// Falls das Formular abgeschickt wurde, entsprechende Inhaltsdatei einbinden
		if(isset($GLOBALS['_POST']['newItem'])) {
			
			$this->newItem = trim($GLOBALS['_POST']['newItem']);

			if(isset($GLOBALS['_POST']['newPageDetails']) && $GLOBALS['_POST']['newPageDetails'] == "true") {
				$this->newItemName		= htmlspecialchars($this->newItem);
				$this->newPageDetails	= true;
				$this->adminContent	   .= $this->getAddNewPageForm();
				// #adminContent close
				$this->adminContent	   .= $this->closeAdminContent();
				return $this->adminContent;
			}

			// Falls das Formular abgeschickt wurde, entsprechende Inhaltsdatei einbinden
			if($this->newItem == "") { // Falls ein neuer Menuepunkt eingegeben werden sollte, aber das Feld leer ist oder sonst Fehler auftreten...
				$this->error = "{s_error:wrongname2}";
				$this->adminContent .= $this->getNewPageList();
				// #adminContent close
				$this->adminContent	.= $this->closeAdminContent();
				return $this->adminContent;
			}

			// Falls ein neuer Menuepunkt eingegeben werden sollte, aber das Feld leer ist oder sonst Fehler auftreten...
			if($this->validateTitle($this->newItem, true) === false) {
				$this->error		 = "{s_error:wrongname}";
				$this->newItemName	 = htmlspecialchars($this->newItem);
				$this->adminContent	.= $this->getNewPageList();
				// #adminContent close
				$this->adminContent	.= $this->closeAdminContent();
				return $this->adminContent;
			}
			
			// Falls die Entertaste gedrückt wurde und kein Zielmenü spezifiziert wurde, zur Seite 1 gehen
			if(!isset($GLOBALS['_POST']['new_first'])
			&& !isset($GLOBALS['_POST']['new_below'])
			&& !isset($GLOBALS['_POST']['new_child'])
			) {
				$this->error		 = "{s_error:choosemenu}";
				$this->newItemName	 = htmlspecialchars($this->newItem);
				$this->adminContent .= $this->getNewPageList();
				// #adminContent close
				$this->adminContent	.= $this->closeAdminContent();
				return $this->adminContent;
			}
			
			// Falls ein neuer Menuepunkt eingegeben wurde direkt zu Step2 gehen
			if($this->newItem != "" && $this->validateTitle($this->newItem, true) === true) {
				$this->newItemName = $this->newItem;

				$this->adminContent .= $this->getAddNewPageForm();
				// #adminContent close
				$this->adminContent	.= $this->closeAdminContent();
				return $this->adminContent;
			}
			
		}
		
		// Falls kein submit geklickt wurde (z.B. erster Seitenaufruf)
		// Ggf. Session edit_id löschen
		if(isset($this->g_Session['edit_id']) 
		&& $this->g_Session['edit_id'] != ""
		)
			$this->unsetSessionKey('edit_id');

		
		// Falls kein Lock, Liste anzeigen
		$this->adminContent .=	$this->getNewPageList();

		
		// #adminContent close
		$this->adminContent	.= $this->closeAdminContent();
		
		
		// Panel for rightbar
		$this->adminRightBarContents[]	= $this->getNewRightBarContents();
		
		
		return $this->adminContent;

	}

	
	// Html für Seitenliste
	protected function getNewPageList()
	{
	
		$output = 	'<div class="adminArea">' . PHP_EOL .
					'<h2 class="cc-section-heading cc-h2">{s_nav:adminnew}</h2>' . PHP_EOL;
					
				
		// Bei mehreren Sprachen Sprachauswahl einbinden
		$this->getLangSelection();

		
		$output .=	'<div class="adminSection">' . PHP_EOL .
					'<div class="adminBox">' . PHP_EOL .
					'<form id="addNewItem" action="' . $this->formAction . '" method="post">' . PHP_EOL . 
					'<label for="newItem">{s_label:newitem}</label>' . PHP_EOL;
							
		if(isset($this->error) && $this->error != "")
			$output .= '<p class="notice">' . $this->error . '</p>' . PHP_EOL;
			
		$output .=	'<input name="newItem" type="text" id="newItem" maxlength="100" value="' . $this->newItemName . '" />' . PHP_EOL .
					'<input type="hidden" id="new_item" />' . PHP_EOL .
					parent::getTokenInput() .
					'</form>' . PHP_EOL .
					'</div>' . PHP_EOL .
					'</div>' . PHP_EOL;
							
		$output .=	'<h3 class="cc-h3 toggle">{s_header:mainmenu}</h3>' . PHP_EOL;
		$output .=	$this->listPages("new");
		
		$output .=	'<h3 class="cc-h3 toggle">{s_header:topmenu}</h3>' . PHP_EOL;
		$output .=	$this->listPages("new", 2);
		
		$output .=	'<h3 class="cc-h3 toggle">{s_header:footmenu}</h3>' . PHP_EOL;
		$output .=	$this->listPages("new", 3);
		
		$output .=	'<h3 class="cc-h3 toggle">{s_header:nonmenu}</h3>' . PHP_EOL;
		$output .=	$this->listPages("new", 0);
			
		
		// Contextmenü-Script
		$output .=	$this->getContextMenuScript();

	
		$output .=	'</div>' . PHP_EOL;
		
		// Zurückbuttons
		$output .=	'<div class="adminArea">' . PHP_EOL . 
					'<p>&nbsp;</p>' . PHP_EOL . 
					'<p>&nbsp;</p>' . PHP_EOL . 
					'<ul>' . PHP_EOL . 
					'<li class="submit back">' . PHP_EOL;
		
		// Button back (edit)
		$btnDefs	= array(	"href"		=> ADMIN_HTTP_ROOT . '?task=edit',
								"class"		=> "edit",
								"text"		=> "{s_nav:adminedit}",
								"icon"		=> "edit"
							);
		
		$output	.=	parent::getButtonLink($btnDefs);
		
		// Button back (sort)
		$btnDefs	= array(	"href"		=> ADMIN_HTTP_ROOT . '?task=sort',
								"class"		=> "sort right",
								"text"		=> "{s_nav:adminsort}",
								"icon"		=> "sort"
							);
		
		$output	.=	parent::getButtonLink($btnDefs);
		
		$output	.=	'</li>' . PHP_EOL . 
					'<li class="submit back">' . PHP_EOL;
		
		// Button back
		$output .=	$this->getButtonLinkBacktomain();
				
		$output .=	'<br class="clearfloat" />' . PHP_EOL .
					'</li>' . PHP_EOL . 
					'</ul>' . PHP_EOL . 
					'<p>&nbsp;</p>' . PHP_EOL . 
					'<p>&nbsp;</p>' . PHP_EOL . 
					'</div>' . PHP_EOL;
		
		
		// Autofocus script
		$output .=	'<script type="text/javascript">' . "\n" . 
					'head.ready(function(){$(document).ready(function(){' .
					'$("#newItem").focus();' . 
					'});' . "\n" . 
					'});' . "\n" . 
					'</script>' . "\n";

		return $output;
	
	}
	
	
	// getAddNewPageForm
	public function getAddNewPageForm()
	{

		$output			= "";
		$success		= false;
		$online			= 1;
		$publicPage		= true;
		$setIndexPage	= false;
		$wrongTitle		= array();
		$duplicateTitle = array();
		$validTitle		= true;
		$allTitlesValid = false;
		$invalidTitles	= 1;
		$otherLangsTitle = "";
		$otherLangsAlias = "";
		$newPageId		= "";
		$updateSQL1b	= false;
		$options		= "";

		
		if(isset($GLOBALS['_POST']['newPageId'])) { // Postvariable für page_id auslesen, falls gesetzt
			$newPageId = $GLOBALS['_POST']['newPageId']; // 
			$this->newPageId = $newPageId;
		}
			
		if(isset($GLOBALS['_POST']['newItemName']))  // Postvariable für title auslesen, falls gesetzt
			$this->newItemName = $GLOBALS['_POST']['newItemName']; // 
			
			
		// Falls der Titel für weitere Sprachen eingegeben werden soll bzw. Online-Status, Seite als Startseite und Benutzergruppenbeschränkung
		if(isset($this->newPageDetails) && $this->newPageDetails == true) {
			
			$success		= true;
			$invalidTitles	= 0;
			$error1			= "{s_error:wrongname}";
			$error2			= "{s_error:nameexists}";
			$groupsRead		= array("public");
			$groupsWrite	= array();
			
			
			// Template
			if(!empty($GLOBALS['_POST']['template'])) { // template auslesen			
				$this->pageTemplate = $GLOBALS['_POST']['template'];
			}
			
			$templateDB			= $this->DB->escapeString($this->pageTemplate);
			
			
			// Benutzergruppen (read), falls nicht öffentliche Seite		
			if(!isset($GLOBALS['_POST']['public']) && isset($GLOBALS['_POST']['groups_read'])) { // Usergroup auslesen
				
				$groupsRead = $GLOBALS['_POST']['groups_read'];
				
				if(in_array("public", $groupsRead))
					$groupsRead = array("public");
				else
					$publicPage = false;
			}
			
			
			// Benutzergruppen (write)
			if(!empty($GLOBALS['_POST']['groups_write'])) { // Usergroup auslesen			
				$groupsWrite = $this->getPageEditGroups($GLOBALS['_POST']['groups_write']);
			}

			$userGroups		= $this->DB->escapeString(implode(",", $groupsRead));
			$userGroupsEdit	= $this->DB->escapeString(implode(",", $groupsWrite));
			
			
			// db-Tabelle sperren
			$lock = $this->DB->query("LOCK TABLES `" . $this->tablePagesDB . "`");


			// Update Benutzergruppen
			$updateSQL1 = $this->DB->query("UPDATE `" . $this->tablePagesDB . "` 
												  SET `group` = '" . $userGroups . "', 
													  `group_edit` = '" . $userGroupsEdit . "', 
													  `template` = '" . $templateDB . "' 
												  WHERE `page_id` = " . $newPageId . " 
												  ");
			
			// db-Sperre aufheben
			$unLock = $this->DB->query("UNLOCK TABLES");
		
		
			// Installierte Sprachen auflisten			
			foreach($this->installedLangs as $otherLang) {			
				
				if($otherLang != $this->editLang) {
					
					$checkTitle = false;
					$validTitle = true;
					
					$otherLangsTitle = trim($GLOBALS['_POST']['otherLang_' . $otherLang]);
					
					if($otherLangsTitle == "")
						$otherLangsTitle = $this->newItemName . "-" . $otherLang;
						
					$checkTitle = $this->validateTitle($otherLangsTitle, true); // Titel überprüfen
					#die($checkTitle);
					if($checkTitle === false) { // Falls ein falscher Begriff eingegeben wurde				
						$wrongTitle[] = $otherLang;
						$validTitle = false;
						$invalidTitles++;
					}
					
					if($validTitle == true && $otherLangsTitle != "") {
						
						$otherLangsAlias = $this->getAlias(strtolower($otherLangsTitle), $newPageId, $otherLang);
		
						// db-Tabelle sperren
						$lock = $this->DB->query("LOCK TABLES `" . $this->tablePagesDB . "`");
			
			
						// Update vorhandener Einträge (nach hinten verschieben)
						$updateSQL1 = $this->DB->query("UPDATE `" . $this->tablePagesDB . "` 
																SET `title_" . $otherLang . "` = '$otherLangsTitle', `alias_" . $otherLang . "` = '$otherLangsAlias' 
																WHERE `page_id` = " . $newPageId . " 
																");
						
						
						// db-Sperre aufheben
						$unLock = $this->DB->query("UNLOCK TABLES");
						
					}
						
					#var_dump($query);
					#exit;
				}
			
			}
			
			// Falls Seite als Startseite
			if(isset($GLOBALS['_POST']['index']) && $GLOBALS['_POST']['index'] == "on") {
				
				$setIndexPage = true;
				
				// db-Tabelle sperren
				$lock = $this->DB->query("LOCK TABLES `" . $this->tablePagesDB . "`");


				// Update vorhandener Einträge (nach hinten verschieben)
				$updateSQL2a = $this->DB->query("UPDATE `" . $this->tablePagesDB . "` 
													  SET `index_page` = 0    
													  WHERE `index_page` = 1 
													  ");
				
				// Update vorhandener Einträge (nach hinten verschieben)
				$updateSQL2b = $this->DB->query("UPDATE `" . $this->tablePagesDB . "` 
													  SET `index_page` = 1    
													  WHERE `page_id` = " . $newPageId . " 
													  ");
				
				
				// db-Sperre aufheben
				$unLock = $this->DB->query("UNLOCK TABLES");
			}
			
			// Seitenstatus (online)
			if(!isset($GLOBALS['_POST']['online']))			
				$online = 0;
			
			// db-Tabelle sperren
			$lock = $this->DB->query("LOCK TABLES `" . $this->tablePagesDB . "`");


			// Update vorhandener Einträge (nach hinten verschieben)
			$updateSQL3 = $this->DB->query("UPDATE `" . $this->tablePagesDB . "` 
												  SET published = " . $online . "    
												  WHERE `page_id` = " . $newPageId . " 
												  ");		
			
			// db-Sperre aufheben
			$unLock = $this->DB->query("UNLOCK TABLES");
		
			
		} // Ende if otherLang
		
		
		// Falls der gerade neu eingegebene Name gültig war, neue Seite eintragen
		else {
			
			// Datenbanksuche nach bereits vorhandenen Seiten zur Bestimmung der neuen page_id
			$lastPageId = $this->DB->query("SELECT MAX(`page_id`) as pageid 
													FROM `" . $this->tablePagesDB . "` 
													");
			
			#die(var_dump($lastPageId));

			
			$newPageId = $lastPageId[0]['pageid'] +1; // neue Item Id
			
			$authorID	= $this->DB->escapeString($this->loggedUserID);
			$title		= $this->DB->escapeString($this->newItem);
			$alias		= $this->getAlias(strtolower($this->newItem), $newPageId, $this->editLang);
			
			if($alias === false)
				$alias	= "{s_text:change}";

			$titleCol		= "";
			$aliasCol		= "";
			$htmlTitleCol	= "";
			$titleStr		= "";
			$aliasStr		= "";
			
			// Seitentitel und -alias für alle Sprachen übernehmen
			foreach($this->o_lng->installedLangs as $lang) {
			
				$titleCol		.= "`title_" . $lang . "`,";
				$aliasCol		.= "`alias_" . $lang . "`,";
				$htmlTitleCol	.= "`html_title_" . $lang . "`,";
				$titleStr		.= "'" . $title . "',";
				$aliasStr		.= "'" . $alias . "',";
				
			}
			
			$htmlTitleCol	= substr($htmlTitleCol, 0, -1);
			$htmlTitleStr	= substr($titleStr, 0, -1);
			
			$tableContentsDB		= DB_TABLE_PREFIX . parent::$tableContents;
			$tableContentsPrevDB	= $tableContentsDB . "_preview";
			
			// db-Tabelle sperren
			$lock = $this->DB->query("LOCK TABLES `" . $this->tablePagesDB . "`, `" . $tableContentsDB . "`, `" . $tableContentsPrevDB . "`");
		
		
			
			if(isset($GLOBALS['_POST']['new_first'])) { // Falls eine neue Seite an erster Position erstellt werden soll
				
				$menuId = $GLOBALS['_POST']['new_first'];
			

				// Update vorhandener Einträge (nach hinten verschieben)
				$updateSQL1a = $this->DB->query("UPDATE `" . $this->tablePagesDB . "` 
														SET rgt = rgt+2,
														lft = lft+2
														WHERE lft > 1 
														AND `menu_item` = $menuId 
														");


				// Update vorhandener Einträge (nach hinten verschieben)
				$updateSQL1b = $this->DB->query("UPDATE `" . $this->tablePagesDB . "` 
														SET rgt = rgt+2
														WHERE lft = 1
														AND `menu_item` = $menuId 
														");
				
		
				// Einfügen des neuen Seiteninhaltspunkts
				$insertSQL1a = $this->DB->query("INSERT INTO `" . $this->tablePagesDB . "` 
														(`page_id`, `create_date`, `author_id`, `menu_item`, lft, rgt, " . $titleCol . $aliasCol . $htmlTitleCol . ")
														VALUES (". $newPageId . ",  NOW(), " . $authorID . ", " . $menuId . ", 2, 3, " . $titleStr . $aliasStr . $htmlTitleStr .")
														");
		

				// Einfügen der pageId des neuen Seiteninhaltspunkts in Tabelle contents_preview
				$insertSQL1b = $this->DB->query("INSERT INTO `" . $tableContentsPrevDB . "` 
														(`page_id`)
														VALUES (". $newPageId . ")
														");
								
								
				// Einfügen der pageId des neuen Seiteninhaltspunkts in Tabelle contents
				$insertSQL1c = $this->DB->query("INSERT INTO `" . $tableContentsDB . "` 
														(`page_id`)
														VALUES (". $newPageId . ")
														");
				
			}
			
			
			elseif(isset($GLOBALS['_POST']['new_below'])) { // Falls eine Seite unterhalb einer anderen eingefügt werden soll
				
				
				$targetId = $GLOBALS['_POST']['new_below'];
			

				// Datenbanksuche nach bereits vorhandenen Seiten zur Bestimmung der neuen page_id
				$menuItem = $this->DB->query( "SELECT `group`,`menu_item`,lft,rgt 
													FROM `" . $this->tablePagesDB . "` 
													WHERE `page_id` = $targetId
													");
				
				$group = $menuItem[0]['group'];
				$menuId = $menuItem[0]['menu_item'];
				$lft = $menuItem[0]['lft'];
				$rgt = $menuItem[0]['rgt'];
				$newLft = $rgt +1;
				$newRgt = $rgt +2;
				
						
				// Update vorhandener Einträge (nach hinten verschieben)
				$updateSQL1a = $this->DB->query("UPDATE `" . $this->tablePagesDB . "` 
														SET rgt = rgt+2,
														lft = lft+2
														WHERE lft > $rgt 
														AND `menu_item` = $menuId 
														");


				// Update vorhandener Einträge (Wurzel rgt-Wert um 2 erhöhen)
				$updateSQL1b = $this->DB->query("UPDATE `" . $this->tablePagesDB . "` 
														SET rgt = rgt+2
														WHERE lft < $lft 
														AND rgt > $rgt
														AND `menu_item` = $menuId 
														");
					
		
				// Einfügen des neuen Seiteninhaltspunkts
				$insertSQL1a = $this->DB->query("INSERT INTO `" . $this->tablePagesDB . "` 
														(`page_id`, `create_date`, `author_id`, `group`, `menu_item`, lft, rgt, " . $titleCol . $aliasCol . $htmlTitleCol . ")
														VALUES (". $newPageId . ",  NOW(), " . $authorID . ", '$group', " . $menuId . ", " . $newLft . ", " . $newRgt . ", " . $titleStr . $aliasStr . $htmlTitleStr .")
														");


				// Einfügen der pageId des neuen Seiteninhaltspunkts in Tabelle contents
				$insertSQL1b = $this->DB->query("INSERT INTO `" . $tableContentsPrevDB . "` 
														(`page_id`)
														VALUES (". $newPageId . ")
														");
					
					
				// Einfügen der pageId des neuen Seiteninhaltspunkts in Tabelle contents
				$insertSQL1c = $this->DB->query("INSERT INTO `" . $tableContentsDB . "` 
														(`page_id`)
														VALUES (". $newPageId . ")
														");
					

			}

			elseif(isset($GLOBALS['_POST']['new_child'])) { // Falls eine Seite unterhalb einer andere eingefügt werden soll
				
				
				$targetId = $GLOBALS['_POST']['new_child'];
			

				// Datenbanksuche nach bereits vorhandenen Seiten zur Bestimmung der neuen page_id
				$menuItem = $this->DB->query( "SELECT `group`,`menu_item`,lft,rgt 
													FROM `" . $this->tablePagesDB . "` 
													WHERE `page_id` = $targetId
													");
				
				$group = $menuItem[0]['group'];
				$menuId = $menuItem[0]['menu_item'];
				$lft = $menuItem[0]['lft'];
				$rgt = $menuItem[0]['rgt'];
				$newLft = $lft +1;
				$newRgt = $lft +2;
						
			
				// Update vorhandener Einträge (nach hinten verschieben)
				$updateSQL1a = $this->DB->query("UPDATE `" . $this->tablePagesDB . "` 
														SET rgt = rgt+2,
														lft = lft+2 
														WHERE lft > $lft 
														AND `menu_item` = $menuId 
														");
		

				// Update vorhandener Einträge (nach hinten verschieben)
				$updateSQL1b = $this->DB->query("UPDATE `" . $this->tablePagesDB . "` 
														SET rgt = rgt+2 
														WHERE lft <= $lft 
														AND rgt >= $rgt
														AND `menu_item` = $menuId 
														");

				
				// Einfügen des neuen Seiteninhaltspunkts
				$insertSQL1a = $this->DB->query("INSERT INTO `" . $this->tablePagesDB . "` 
														(`page_id`, `create_date`, `author_id`, `group`, `menu_item`, lft, rgt, " . $titleCol . $aliasCol . $htmlTitleCol . ")
														VALUES (". $newPageId . ", NOW(), " . $authorID . ", '$group', " . $menuId . ", " . $newLft . ", " . $newRgt . ", " . $titleStr . $aliasStr . $htmlTitleStr .")
														");


				// Einfügen der pageId des neuen Seiteninhaltspunkts in Tabelle contents
				$insertSQL1b = $this->DB->query("INSERT INTO `" . $tableContentsPrevDB . "` 
														(`page_id`)
														VALUES (". $newPageId . ")
														");
					
				
				// Einfügen der pageId des neuen Seiteninhaltspunkts in Tabelle contents
				$insertSQL1c = $this->DB->query("INSERT INTO `" . $tableContentsDB . "` 
														(page_id)
														VALUES (". $newPageId . ")
														");
					
				
			}

			// db-Sperre aufheben
			$unLock = $this->DB->query("UNLOCK TABLES");
			
			
			if($updateSQL1b == true)
				$success = true;
			

				
		} // Ende Einarbeitung des default-Lang Titels
			
		#var_dump($updateSQL1a.$updateSQL1b.$insertSQL1a.$insertSQL1b);
		
		
		// Bei erfolgreichem db-Update
		if(($success == true || $this->newPageDetails == true) && $invalidTitles >= 1) {
			
			$this->setSessionVar('edit_id', $newPageId); // newPageId zur Session hinzufügen
			
			setcookie('sort_id', $newPageId); // Bewirkt aufklappen des Untermenüs bei der Seitenliste
			
			//Option anbieten, den Eintrag in anderer Sprache vorzunehmen
			if(count($this->installedLangs) > 1) {
				
				$i = 0;
					
				$options .= '<ul class="adminSection">' . PHP_EOL;
			
				foreach($this->installedLangs as $addLang) {
					
					if($addLang != $this->editLang) {
						
						$options .= '<li>' . PHP_EOL;
						
						$options .= '<label>{s_label:title}_' . $addLang . '<span class="editLangFlag"><img src="' . PROJECT_HTTP_ROOT . '/langs/' . $addLang . '/' . $this->o_lng->existFlag[$i] . '" title="' . $this->o_lng->existNation[$i] . '" class="flag" /></span></label>' . PHP_EOL;
						
						if(in_array($addLang, $wrongTitle)) // Falls im Fehlerarray der Titel der jeweiligen Sprache ist Meldung ausgeben
							$options .= '<p class="notice">' . $error1 . '</p>' . PHP_EOL;
						#var_dump( $duplicateTitle);
						
						if(in_array($addLang, $duplicateTitle)) // Falls im Fehlerarray der Titel der jeweiligen Sprache ist Meldung ausgeben
							$options .= '<p class="notice">' . $error2 . '</p>' . PHP_EOL;
						
						if(isset($GLOBALS['_POST']['otherLang_' . $addLang]))
							$otherLangsTitle = $GLOBALS['_POST']['otherLang_' . $addLang];
						
						$options .= '<input type="text" name="otherLang_' . $addLang . '" value="' . htmlspecialchars($otherLangsTitle != "" ? $otherLangsTitle : $this->newItemName."-".$addLang) . '" />' . PHP_EOL; 
						
						$options .= '</li>' . PHP_EOL;
						
					}
					
					$i++;
				}
				
				$options .= '</ul>' . PHP_EOL;
				
			} // Ende count Langs
			
			
			$output .=	'<div class="adminArea">' . PHP_EOL .
						'<p class="notice success">{s_header:adminnew}<strong>' . htmlspecialchars($this->newItemName) . '</strong>. {s_notice:newsuccess}</p>' . PHP_EOL .
						(isset($i) && $i > 1 ? '<p class="notice error">{s_text:newotherlang}</p>' . PHP_EOL : '') .
						'<h2 class="cc-section-heading cc-h2">{s_nav:adminnew}</h2>' . PHP_EOL .
						'<form action="' . ADMIN_HTTP_ROOT . '?task=new" id="adminfm" method="post">' . PHP_EOL .
						'<div class="newPage">' . PHP_EOL .
						$options . PHP_EOL;
		
			// Templates auflisten
			// Existing Tempates
			$this->existTemplates	= parent::readTemplateDir();
			
			$output .=	'<ul class="adminSection">' . PHP_EOL .
						'<li>' . PHP_EOL .
						'<label class="tplSelect-label">Template</label>' . PHP_EOL . 
						parent::listTemplates($this->pageTemplate, $this->defaultTemplates, $this->existTemplates, "select");
									
			if(!in_array($this->pageTemplate, $this->existTemplates))
				$output .=	parent::getIcon("warning", "hint", 'title="{s_title:tplnotexits}"');
			
			// tplSelectionBox
			$output .=	'<br class="clearfloat" />' . PHP_EOL . 
						'<div id="tplSelectionBox" class="choose imagePicker">' . PHP_EOL .
						'</div>' . PHP_EOL;							
			
			// Tpl image picker
			$output .=	$this->getTplScriptTag();
			
			$output .=	'</li>' . PHP_EOL;
			$output .=	'</ul>' . PHP_EOL;
						
			// Page details
			$output .=	'<ul class="adminSection">' . PHP_EOL .
						'<li>' . PHP_EOL .
						'<label class="markBox"><input type="checkbox" name="online" id="online"' . ($online ? ' checked="checked"' : '') . ' /></label>' . PHP_EOL .
						'<label for="online" class="inline-label">{s_label:pagepub}</label>' . PHP_EOL .
						'</li>' . PHP_EOL .
						'<li>' . PHP_EOL .
						'<span class="inline-box">' . PHP_EOL .
						'<label class="markBox"><input type="checkbox" name="public" id="public" class="toggleDetails" data-toggle="pageDetailsBox"' . ($publicPage && empty($groupsWrite) ? ' checked="checked"' : '') . ' /></label>' . PHP_EOL .
						'<label for="public" class="inline-label">{s_title:publicpage}' . (empty($groupsWrite) ? ' / {s_title:defaultrigths}' : '') . '</label>' . PHP_EOL;

			// Benutzergruppenauswahl
			$output .=	'<div id="pageDetailsBox" class="detailsDiv"' . ($publicPage && empty($groupsWrite) ? ' style="display:none;" ' : '') . '>' . PHP_EOL;
			
			// Benutzergruppen (read)
			$output .=	'<div class="leftBox">' . PHP_EOL;
			
			$output .=	'<label>{s_common:rightsread}</label>' . PHP_EOL .
						'<select multiple="multiple" size="' . count($this->userGroups) . '" name="groups_read[]">' . PHP_EOL;
			
			// Benutzergruppen
			foreach($this->userGroups as $group) {
				$output .='<option value="' . $group . '"' . (isset($groupsRead) && in_array($group, $groupsRead) ? ' selected="selected"' : '') . '>' . (in_array($group, $this->systemUserGroups) ? '{s_option:group' . $group . '}' : $group) . '</option>' . PHP_EOL;
			}
			
			$output .=	'</select>' . PHP_EOL;
			$output .=	'</div>' . PHP_EOL;
			
			// Benutzergruppen (write)
			$output .=	'<div class="rightBox">' . PHP_EOL;
			
			$output .=	'<label>{s_common:rightswrite}</label>' . PHP_EOL .
						'<select multiple="multiple" size="' . count($this->loggedUserEditGroups) . '" name="groups_write[]">' . PHP_EOL;
			
			// Benutzergruppen
			foreach($this->loggedUserEditGroups as $group) {
				$output .='<option value="' . $group . '"' . (isset($groupsWrite) && in_array($group, $groupsWrite) ? ' selected="selected"' : '') . '>' . (in_array($group, $this->systemUserGroups) ? '{s_option:group' . $group . '}' : $group) . '</option>' . PHP_EOL;
			}
			
			$output .=	'</select>' . PHP_EOL;
			$output .=	'</div>' . PHP_EOL;
			
			$output .=	'<br class="clearfloat" /><br /></div>' . PHP_EOL .
						'</span>' . PHP_EOL .
						'</li>' . PHP_EOL;
			
			// Neue Seite als Startseite
			$output .=	'<li>' . PHP_EOL .
						'<label class="markBox"><input type="checkbox" name="index" id="index"' . ($setIndexPage ? ' checked="checked"' : '') . ' /></label>' . PHP_EOL .
						'<label for="index" class="inline-label">{s_label:indexpage}</label>' . PHP_EOL .
						'</li>' . PHP_EOL .
						'</ul>' . PHP_EOL .
						'</div>' . PHP_EOL .
						'<ul>' . PHP_EOL .
						'<li class="submit change">' . PHP_EOL;
			
			// Button submit (new)
			$btnDefs	= array(	"type"		=> "submit",
									"name"		=> "newItem",
									"id"		=> "submit",
									"class"		=> "change",
									"value"		=> "{s_button:savechanges}",
									"icon"		=> "ok"
								);
			
			$output	.=	parent::getButton($btnDefs);
			
			$output	.=	'<input name="newItem" type="hidden" value="' . $newPageId . '" />' . PHP_EOL .
						'<input name="newPageId" type="hidden" value="' . $newPageId . '" />' . PHP_EOL .
						'<input name="newItemName" type="hidden" value="' . $this->newItemName . '" />' . PHP_EOL .
						'<input name="newPageDetails" type="hidden" value="true" />' . PHP_EOL .
						parent::getTokenInput() .
						'</li></ul>' . PHP_EOL .
						'</form>' . PHP_EOL;
		
								
								
		} // Ende if bei erfolgreicher Anlegung


		elseif($success == false) { // Bei Misserfolg Fehlermeldung ausgeben
										
			$output .=	'<div class="adminArea">' . PHP_EOL . 
						'<p class="notice error">{s_error:newfail}</p>' . PHP_EOL . 
						'<p>&nbsp;</p>' . PHP_EOL; 
		}
		
		else { // Bei Erfolgreicher Sprachtiteleingabe
		
			$this->setSessionVar('edit_id', $newPageId); // PageId in Sessionvar speichern
			
			
			$output .=	'<div class="adminArea">' . PHP_EOL . 
						'<p class="notice success">{s_notice:newsuccess}</p>' . PHP_EOL . 
						'<div class="controlBar">' . PHP_EOL .
						'<div class="editHeader">' . "\n" .
						parent::getIcon("page", "page") .
						'<span class"tableCell">{s_header:page} &#9658; <strong title="page ID #' . htmlspecialchars($this->newItemName) . '">' . htmlspecialchars($this->newItemName) . '</strong></span></div>' .
						'</div>' . PHP_EOL . 
						'<p class="notice">{s_notice:addcontent}</p>' . PHP_EOL . 
						'<p>&nbsp;</p>' . PHP_EOL; 
		}

		// Buttons zum zurückgehen
		$output .=	'<ul>' . PHP_EOL .
					'<li class="submit back">' . PHP_EOL;
		
		// Button back (editnew)
		$btnDefs	= array(	"href"		=> ADMIN_HTTP_ROOT . '?task=edit',
								"text"		=> "{s_button:admineditnew}",
								"icon"		=> "edit"
							);
		
		$output	.=	parent::getButtonLink($btnDefs);
		
		// Button back (sort)
		$btnDefs	= array(	"href"		=> ADMIN_HTTP_ROOT . '?task=sort',
								"class"		=> "right",
								"text"		=> "{s_nav:adminsort}",
								"icon"		=> "sort"
							);
		
		$output	.=	parent::getButtonLink($btnDefs);
					
		$output	.=	'</li>' . PHP_EOL .
					'<li class="submit back">' . PHP_EOL;
		
		// Button back (new)
		$btnDefs	= array(	"href"		=> ADMIN_HTTP_ROOT . '?task=new',
								"text"		=> "{s_button:adminnew}",
								"icon"		=> "new"
							);
		
		$output	.=	parent::getButtonLink($btnDefs);
		
		// Button back
		$output .=	$this->getButtonLinkBacktomain();
				
		$output .=	'</li>' . PHP_EOL . 
					'</ul>' . PHP_EOL . 
					'</div>' . PHP_EOL;
		
		$output .=	'</div>' . PHP_EOL;
		
		
		return $output;

	}

	
	// getNewRightBarContents
	private function getNewRightBarContents()
	{
	
		// Panel for rightbar
		$output	= "";
		
		// Back to list
		$output .=	'<div class="controlBar">' . PHP_EOL;
		
		// Button edit pages
		$btnDefs	= array(	"href"		=> ADMIN_HTTP_ROOT . '?task=edit',
								"class"		=> "{t_class:btnpri} {t_class:btnblock} {t_class:marginbs}",
								"text"		=> "{s_header:adminpages} & {s_header:contents}",
								"attr"		=> 'data-ajax="true"',
								"icon"		=> "edit"
							);
	
		$output		.=	parent::getButtonLink($btnDefs);
		
		// Button sort pages
		$btnDefs	= array(	"href"		=> ADMIN_HTTP_ROOT . '?task=sort',
								"class"		=> "{t_class:btnpri} {t_class:btnblock}",
								"text"		=> "{s_nav:adminsort}",
								"attr"		=> 'data-ajax="true"',
								"icon"		=> "sort"
							);
	
		$output		.=	parent::getButtonLink($btnDefs);
		
		$output .=	'</div>' . PHP_EOL;
		
		return $output;
		
	}


	// getTplScriptTag
	public function getTplScriptTag($hide = false)
	{

		return	'<script>' . PHP_EOL .
				'head.ready("jquery", function(){' . PHP_EOL .
				'head.load({imagepickercss: "' . PROJECT_HTTP_ROOT . '/extLibs/jquery/image-picker/image-picker.css"});' . PHP_EOL .
				'head.load({imagepicker: "' . PROJECT_HTTP_ROOT . '/extLibs/jquery/image-picker/image-picker.min.js"});' . PHP_EOL .
				'head.ready("imagepicker", function(){' . PHP_EOL .
					'$(document).ready(function(){' . PHP_EOL .
						'$("select.tplSelect").imagepicker({
							target_box: $("#tplSelectionBox"),
							hide_select: ' . ($hide ? 'true' : 'false') . ',
							show_label: false,
							limit: undefined,
							initialized: function(){
								$("#tplSelectionBox, #tplSelectionBox ul").show();
								$("#tplSelectionBox ul li").each(function(i,e){
									var title	= $("select.tplSelect").children(":nth-child(" + (i+1) + ")").attr("data-title");
									$(this).attr("title", title);
									$(this).append(\'<span class="label">\' + title + \'</span>\');
								});
							}
						});' . PHP_EOL .
					'});' . PHP_EOL .
				'});' . PHP_EOL .
				'});' . PHP_EOL .
				'</script>' . PHP_EOL;
	
	}

}
