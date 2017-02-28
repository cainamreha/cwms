<?php
namespace Concise;

use Symfony\Component\EventDispatcher\Event;
use Concise\Events\ExtendEditEvent;


###################################################
################  Edit-Bereich  ###################
###################################################

// Event-Klassen einbinden
require_once SYSTEM_DOC_ROOT."/inc/admintasks/edit/events/event.ExtendEdit.php";

// Edit

class Admin_Edit extends Admin implements AdminTask
{

	const		MAX_CONELEM_NO		= 20;
	protected	$o_extendEditEvent	= null;
	private 	$tableContentsPrev	= array();
	private		$conElements		= array();
	private		$coreConTypes		= array();
	private		$conPrefix			= "";
	private		$fieldName			= "";
	private		$contentNumber		= 0;
	private		$busyContentNumber	= 0;
	public		$editElementNumber	= 0;
	public		$elementStartNumber	= 1;
	public		$elementPageNumber	= 1;
	private		$queryPages			= array();
	private		$pageDef			= array();
	protected	$queryContents		= array();
	private		$pageAccessGroups	= array();
	private		$pageEditGroups		= array();
	private		$pageTemplate		= "";
	private 	$redirect			= "";
	public 		$pasteCon			= array();
	public 		$showElements		= array();
	public		$conPreview			= "";
	private		$parentsAliasStr	= "";
	private		$elementExists		= true;
	private		$isStylable			= true;
	private		$isPlugin			= false;
	protected	$isNewElement		= false;
	private		$elementStatus		= 1;
	private		$statusButton		= "publish";
	private		$scriptTags			= array();
	private		$editTask			= "list";
	public		$activeTab			= 0;
	public		$showFieldset		= "";

	private		$dbUpdateStr		= "";
	private		$langSelection		= "";
	public		$editElements		= array();
	private		$countErrors		= 0;
	public		$wrongInput			= array();
	public		$success			= "{s_notice:takechange}";
	public		$errors				= "{s_error:failchange}";
	private		$textAreaCount		= 1;
	private		$invalidUserGroups	= array();
		
	private 	$uniLangElements	= array("sitemap",
											"search",
											"login",
											"cform",
											"oform",
											"articles",
											"news",
											"planner",
											"newsfeed",
											"cart",
											"tagcloud"
											);
		
	private 	$sectionTags		= array(1 => "section",
											2 => "div",
											3 => "header",
											4 => "footer",
											5 => "article",
											6 => "aside"
											);

	// edit_del
	private $delId				= "";
	private $query				= array();
	private $querychildPages	= array();
	private $delTitles			= array();
	private $delIDsArr			= array();
	private $childPageIDs		= array();
	private $deleted			= array();
	private $updated			= array();
	private $pageUserGroup		= "";
	private $noAccess			= array();
	private $pagesNotFound		= array();
	private $successTitles		= array();
	private $errorTitles		= array();
	private $errorPageDetails	= array();

	public function __construct($DB, $o_lng, $task, $init = false)
	{

		// Admin-Elternklasse aufrufen
		parent::__construct($DB, $o_lng);
		
		parent::$task = $task;
		
		// Datenbank-Engine auf InnoDB setzen
		$this->setDbEngine(DB_TABLE_PREFIX . parent::$tablePages, "InnoDB");
	
		$this->getEditTask();
		$this->evalEditRequest();
	
		//Falls Template, editor und filemanager erforderlich
		if($this->editTask	== "editentry") {
			$this->headIncludeFiles['editor']		= true;
			$this->headIncludeFiles['filemanager']	= true;
		}
		
		// Events listeners registrieren
		$this->addEventListeners("adminedit");
		
		// ExtendDataEvent
		$this->o_extendEditEvent					= new ExtendEditEvent($this->DB, $this->o_lng, $this->editTask);
		$this->o_extendEditEvent->editorLog			= $this->editorLog;
	
	}
	
	
	public function getTaskContents($ajax = false)
	{

		// Enthält Headerbox
		$this->adminHeader		=	'{s_text:admin' . self::$task . '}' . PHP_EOL .
									'</div><!-- Ende headerBox -->' . PHP_EOL;
		
		// #adminContent
		$this->adminContent 	=	$this->openAdminContent();
		
		$this->formAction		=	ADMIN_HTTP_ROOT . '?task=edit';
		
		
		// Bei mehreren Sprachen Sprachauswahl einbinden
		$this->getLangSelection();
		
		
		// Hauptseiten-/Tpl-Inhalte bearbeiten
		if($this->editTask == "editentry" 
		|| $this->isTemplateArea
		) {
		
			// Seiten-/Tpl-Details (step 2)
			$this->getEditSinglePage();
			return $this->adminContent;
		}

		
		// Delete page(s)
		if($this->editTask == "delete") {
			
			// Seiten löschen (edit_del)
			$this->getDeletePagesForm();			
			return $this->adminContent;
		}		
		

		// Sort up
		if($this->editTask == "sortup") {
			
			$this->sortPageUp($GLOBALS['_POST']['sortup_id']);
			
		}

		// Sort down
		if($this->editTask == "sortdown") {
			
			$this->sortPageDown($GLOBALS['_POST']['sortdown_id']);

		}


		// Andernfalls Seitenliste (step 1)
		$this->adminContent	.= $this->getEditPageList();
		
		
		// Panel for rightbar
		$this->adminRightBarContents[]	= $this->getEditListRightBarContents();
	
	
		// Admin Tour Script
		$this->adminContent .=	$this->getEditAdminTourScript();
		
		
		return $this->adminContent;

	}
		
	
	// Seitenliste
	protected function getEditPageList()
	{

		// Datenbanksuche nach bereits vorhandenen Seiten zur Überprüfung ob überhaupt Seiten vorhanden sind
		$maxPageId = $this->DB->query("SELECT MAX(page_id)
										FROM " . DB_TABLE_PREFIX . parent::$tablePages . " 
										");

		#var_dump($maxPageId);

		$output		= "";
		$pagesList	= "";

		if($maxPageId[0]['MAX(page_id)'] > 0) { // Falls zu bearbeitenden Seiten vorhanden sind

			$this->adminHeader		= 	'{s_text:adminedit}' .
										'{s_text:adminedit1}' .
										'</div><!-- Ende headerBox -->' . PHP_EOL;
										

			// Notifications
			$output .= $this->getSessionNotifications("notice", true);
			$output .= $this->getSessionNotifications("error", true);
			
			
			// Ggf. Meldung ausgeben
			if(!empty($this->notice))
				$output .= $this->getNotificationStr($this->notice);
		
		
			$output .=	'<div class="adminArea editPages">' . PHP_EOL .					
						'<h2 class="cc-section-heading cc-h2">{s_nav:adminedit}</h2>' . PHP_EOL;
		
			
			$output .=	$this->getControlBar("none"); // Filterfunktion/Seitensuche einbinden
			
			
			// Div für Markierungsgruppe
			$output .=	'<div class="pagesListDiv">' . PHP_EOL;
			
			
			// Aktion-Box
			$output .=	'<div class="actionBox">' . PHP_EOL;
			
			// Formular für Multi-Action
			$output .=	'<form action="' . $this->formAction . '" method="post">' . PHP_EOL;
			
			// Mark-All
			$output .=	'<label for="markAllLB" class="markAll markBox">' . PHP_EOL .
						'<input type="checkbox" id="markAllLB" data-select="all" />' . PHP_EOL .
						'</label>' . PHP_EOL .
						'<label for="markAllLB" class="markAllLB">{s_label:mark}</label>' . PHP_EOL;
			
			// Aktion-Box Buttons
			$output .=	'<span class="editButtons-panel">' . PHP_EOL;
			
			// Button publish
			$btnDefs	= array(	"type"		=> "submit",
									"class"		=> 'pubAll pubPages button-icon-only',
									"value"		=> "",
									"title"		=> '{s_title:pubmarked}',
									"icon"		=> "online"
								);
				
			$output .=	parent::getButton($btnDefs);
			
			// Button unpublish
			$btnDefs	= array(	"type"		=> "submit",
									"class"		=> 'pubAll pubPages unpublish button-icon-only',
									"value"		=> "",
									"title"		=> '{s_title:unpubmarked}',
									"icon"		=> "offline"
								);
				
			$output .=	parent::getButton($btnDefs);

			// Button delete
			$btnDefs	= array(	"type"		=> "submit",
									"class"		=> 'delAll delPages button-icon-only',
									"value"		=> "",
									"title"		=> '{s_title:delmarked}',
									"attr"		=> 'data-action="delmultiple"',
									"icon"		=> "delete"
								);
				
			$output .=	parent::getButton($btnDefs);

			// Alle Seitenstatus/Löschen Button
			$output .=	'<input type="hidden" name="del_id" value="array" />' . PHP_EOL .
						'<input type="hidden" class="multiAction" name="multiAction" value="' . SYSTEM_HTTP_ROOT . '/access/listPages.php?page=admin&array=1&action=" />' . PHP_EOL;
			
			$output .=	'</span>' . PHP_EOL .
						'</form>' . PHP_EOL .
						'</div>' . PHP_EOL;


			$pagesList1 = $this->listPages();
			$pagesList2 = $this->listPages("edit", 2); 
			$pagesList3 = $this->listPages("edit", 3); 
			$pagesList4 = $this->listPages("edit", 0); 
			
			
			// Mainmenüseiten
			if($pagesList1 != "")
				$output .=	'<h3 class="cc-h3 toggle">{s_header:mainmenu}</h3>' . PHP_EOL . 
								$pagesList1;
								
			// Topmenüseiten
			if($pagesList2 != "")
				$output .=	'<h3 class="cc-h3 toggle">{s_header:topmenu}</h3>' . PHP_EOL . 
								$pagesList2;
			
			// Footmenüseiten
			if($pagesList3 !="")
				$output .=	'<h3 class="cc-h3 toggle">{s_header:footmenu}</h3>' . PHP_EOL . 
								$pagesList3;
								
			// Nonmenüseiten
			if($pagesList4 != "")
				$output .=	'<h3 class="cc-h3 toggle">{s_header:nonmenu}</h3>' . PHP_EOL . 
								$pagesList4;
			
			// Geschützte Seiten
			if($this->loggedUserGroup == "admin") {
				
				$pagesList5 = $this->listPages("edit", -1, 1); 
				if($pagesList5 != "")
					$output .=	'<h3 class="cc-h3 toggle" style="border-style:dashed;">{s_header:protected}</h3>' . PHP_EOL . 
								$pagesList5;
			}
			
			
			$output .=	'</div>' . PHP_EOL;
			
			
			// Contextmenü-Script
			$output .=	$this->getContextMenuScript();

			
			$output .=	'</div>' . PHP_EOL;
		}

		else {
			
			// Falls noch keine Seiten angelegt wurden
			$output .=	'<div class="adminArea">' . PHP_EOL . 
						'<p class="notice error">{s_notice:nopages}</p>' . PHP_EOL . 
						'</div>' . PHP_EOL . 
						'<p>&nbsp;</p>' . PHP_EOL . 
						'<ul><li class="submit back">' . PHP_EOL . 
						'<form action="' . ADMIN_HTTP_ROOT . '?task=new" id="navform1" method="post">' . PHP_EOL; // Formular mit Buttons new
			
			// Button new
			$btnDefs	= array(	"type"		=> "submit",
									"name"		=> "addnew",
									"id"		=> "addnew",
									"class"		=> "add",
									"value"		=> "{s_button:adminnew}",
									"icon"		=> "new"
								);
			
			$output	.=	parent::getButton($btnDefs);
			
			$output	.=	'<input name="addnew" type="hidden"value="{s_button:adminnew}" />' . PHP_EOL .
						'<input type="hidden" name="token" value="' . parent::$token . '" />' . PHP_EOL . 
						'</form></li></ul>' . PHP_EOL;
		}

		// Zurückbuttons
		$output .=	'<div class="adminArea">' . PHP_EOL .
					'<ul>' . PHP_EOL .
					'<li class="submit back">' . PHP_EOL;
		
		// Button theme
		$btnDefs	= array(	"href"		=> ADMIN_HTTP_ROOT . '?task=tpl',
								"class"		=> "template",
								"text"		=> "{s_nav:admintpl}",
								"icon"		=> "theme"
							);
	
		$output		.=	parent::getButtonLink($btnDefs);
		
		$output .=	'</li>' . PHP_EOL .
					'<li class="submit back">' . PHP_EOL;
		
		// Button new page
		$btnDefs	= array(	"href"		=> ADMIN_HTTP_ROOT . '?task=new',
								"text"		=> "{s_button:adminnew}",
								"icon"		=> "new"
							);
	
		$output		.=	parent::getButtonLink($btnDefs);
		
		// Button sort pages
		$btnDefs	= array(	"href"		=> ADMIN_HTTP_ROOT . '?task=sort',
								"class"		=> "right",
								"text"		=> "{s_nav:adminsort}",
								"icon"		=> "sort"
							);
	
		$output		.=	parent::getButtonLink($btnDefs);
		
		$output .=	'<li class="submit back">' . PHP_EOL;
		
		// Button back
		$output .=	$this->getButtonLinkBacktomain();
				
		$output .=	'<br class="clearfloat" />' . PHP_EOL .
					'</li>' . PHP_EOL . 
					'</ul>' . PHP_EOL . 
					'<p>&nbsp;</p>' . PHP_EOL .
					'<p>&nbsp;</p>' . PHP_EOL .
					'</div>' . PHP_EOL .
					'</div>' . PHP_EOL;
		
		
		return $output;

	}
	
	
	// getEditSinglePage
	public function getEditSinglePage()
	{
		
		//Falls Template
		if($this->isTemplateArea)
			$this->getContentsArea();
		

		// Inhaltstabellenname für Preview festlegen
		$this->tableContentsPrev	= parent::$tableContents . "_preview";		

		// Anzahl an Inhaltsspalten
		$this->contentNumber		= parent::getConNumber(DB_TABLE_PREFIX . parent::$tableContents);
			
		// Anzahl an belegten Inhaltsspalten
		$this->busyContentNumber	= parent::getConNumber(DB_TABLE_PREFIX . parent::$tableContents, $this->editId);
		
		$tabExpl					= explode("_", parent::$tableContents);
		$this->editTplArea			= end($tabExpl);
		
		// Falls kein Template bearbeitet wird, Seiteninformationen auslesen
		if(!$this->isTemplateArea) {
			
			$this->queryPages		= $this->getPageQuery($this->editId, $this->editLang);
			$this->formAction		= ADMIN_HTTP_ROOT . '?task=edit';
		
		}

		// Falls Template area
		else {
			$this->formAction		= ADMIN_HTTP_ROOT . '?task=tpl&type=edit';
		}


		// Datenbanksuche nach zu bearbeitender Seite in Tabelle contents_xyz_preview
		$this->queryContents		= $this->getContentsQuery($this->tableContentsPrev, $this->editId, $this->editLang);

		
		
		// Bearbeitungsberechtigung prüfen
		if(!$this->checkEditAccess())
			return $this->adminContent;		
		
		
		// Locking checken
		if($this->checkLocking($this->editId, parent::$tableContents, $this->g_Session['username'])) {
			$this->adminContent .=	$this->getBackButtons("");
			// #adminContent close
			$this->adminContent	.= $this->closeAdminContent();
			return $this->adminContent;
		}
		
		
		// Redirect auslesen, falls von FE-Editing kommend
		if(!empty($GLOBALS['_GET']['red']))
			$this->redirect		= urldecode($GLOBALS['_GET']['red']);
		
		elseif(!empty($GLOBALS['_POST']['redirect']))
			$this->redirect		= $GLOBALS['_POST']['redirect'];
		
		

		// Ggf. zu große POST-Requests abfangen
		if($checkPostSize	= $this->checkPostRequestTooLarge())
			$this->error	= $this->getNotificationStr(sprintf(ContentsEngine::replaceStaText("{s_error:postrequest}"), $checkPostSize), "error");

		
		
		###################################
		##### Inhaltselemente auslesen ####
		###################################
		
		// Falls eine Seite und Berechtigung
		if($this->editId > -1001
		|| $this->adminLog
		)
			$this->getContentElements();
		
	
			
		// Falls das Formular abgeschickt wurde
		if(isset($GLOBALS['_POST']['submit'])) {
	
			###################################
			##### Seitendetails speichern #####
			###################################

			// Falls kein Template bearbeitet werden soll, Seitenformular auswerten und db-Update durchführen
			if(!$this->isTemplateArea
			&& empty($this->editElementNumber)
			&& isset($GLOBALS['_POST']['submit_page_details'])
			)
				$this->savePageDetails();
			
		
			#############################
			##### Inhalte speichern #####
			#############################
			if($this->dbUpdateStr != "")
				$updateSQL	= $this->updateContents($this->dbUpdateStr);

			
			// db-Sperre aufheben
			$unLock = $this->DB->query("UNLOCK TABLES");

		
			// Ggf. Zielseite für Datenmodule übernehmen
			$this->updateDataCatTargetPage();	
		

			// Falls erfolgreiche Änderung			
			if($this->countErrors == 0) {
				
				// Falls single element edit (fe)
				if(!empty($this->editElementNumber))
					return true;

				// Andernfalls Seite neu laden
				$this->redirectUpdatePage();
			}
			
		
			// Falls Fehler vorhanden sind, Meldung ausgeben
			$this->error	= '<p class="notice error">' . $this->errors . '</p>' . PHP_EOL;
		
		} // Ende if submit
		
	
	
		// Html
		$this->adminHeader		=	'{s_text:admin' . self::$task . '}' . PHP_EOL .
									'{s_text:adminedit2}' . PHP_EOL . 
									'</div><!-- Ende headerBox -->' . PHP_EOL;
		
		$this->adminContent .=		'<div class="adminArea">' . PHP_EOL;
		

		// Notifications
		$this->adminContent .= $this->error;
		$this->adminContent .= $this->notice;
		$this->adminContent .= $this->getSessionNotifications("notice", true);
		$this->adminContent .= $this->getSessionNotifications("error", true);

		
		$this->adminContent .=	'<h2 class="cc-section-heading cc-h2">{s_nav:admin' . self::$task . '}</h2>' . PHP_EOL .
								'<div class="controlBar">' . PHP_EOL;
							
		
		// Falls kein Template bearbeitet wird, Seitenformular anzeigen
		if(!$this->isTemplateArea)
			$this->getPageDetailsForm();

		
		// Falls jedoch ein Template bearbeitet wird, Templateformular anzeigen
		else
			$this->getTemplateDetailsForm();
		
		
		// Inhalte (falls editId > -1001, sprich geschützte Seiten nicht anzeigen)
		if($this->editId > -1001
		|| $this->adminLog
		)
			$this->getContentElementsForm();
		
		
		// History list
		$this->adminRightBarContents[]	=	$this->listContentHistory(DB_TABLE_PREFIX . parent::$tableContents . "_history", $this->editId);
		
		
		// Contextmenü-Script
		$this->adminContent .=	$this->getContextMenuScript();

		
		$this->adminContent .=	'</div> ' . PHP_EOL;


		// Zurückbuttons
		$this->adminContent .=	$this->getBackButtons($this->isTemplateArea);

		// #adminContent close
		$this->adminContent	.= $this->closeAdminContent();

		
		// Sortable und weitere Skripts
		$this->adminContent .=	$this->getSortScript();
		
		
		// Tabs script tag
		$this->adminContent .=	$this->getTabsScriptTag();
	
	
		// Admin Tour Script
		$this->adminContent .=	$this->getEditAdminTourScript();


		return $this->adminContent;

	}
	

	/**
	 * Seitendetails auslesen
	 *
	 * @param	string			$pageID		page-ID
	 * @param	string			$lang		Sprache
	 * @access	protected
     * @return  array | boolean
	 */
	protected function getPageQuery($pageID, $lang)
	{
	
		// Datenbanksuche nach zu bearbeitender Seite in Tabelle Pages (Überprüfung ob Seite vorhanden)
		$query = $this->DB->query( "SELECT *
										FROM " . DB_TABLE_PREFIX . parent::$tablePages . " 
									WHERE `page_id` = '$pageID'
									");
		#var_dump($query);
		
		
		if(count($query) > 0) {
			
			$this->pageDef['title']		= $query[0]['title_' . $lang];
			$this->pageDef['alias']		= $query[0]['alias_' . $lang];
			$this->pageDef['htmlTitle']	= $query[0]['html_title_' . $lang];
			$this->pageDef['descr']		= $query[0]['description_' . $lang];
			$this->pageDef['keyw']		= $query[0]['keywords_' . $lang];
			$this->pageDef['robots']	= $query[0]['robots'];
			$this->pageDef['canonical']	= $query[0]['canonical'];
			$this->pageDef['copy']		= $query[0]['copy'];
			$this->pageDef['template']	= $query[0]['template'];
			$this->pageDef['nosearch']	= $query[0]['nosearch'];
			$this->pageDef['groups_read']	= explode(",", $query[0]['group']);
			$this->pageDef['groups_write']	= array_filter(explode(",", $query[0]['group_edit']));
			$this->pageDef['publish']	= $query[0]['published'];
			$this->pageDef['index']		= $query[0]['index_page'];
			$this->pageDef['lft']		= $query[0]['lft'];
			$this->pageDef['rgt']		= $query[0]['rgt'];
			$this->pageDef['menuItem']	= $query[0]['menu_item'];
			
			$this->pageTemplate			= $this->pageDef['template'];
			$this->pageAccessGroups		= $this->pageDef['groups_read'];
			$this->pageEditGroups		= $this->getPageEditGroups($this->pageDef['groups_write']);
			
			
			// Seitenvorschaulink im Kopfbereich einbinden
			if(!empty($pageID))
				self::$statusNavArray['preview'] .=		$this->getPreviewNavItem($pageID, $lang);
			
			
			
			// Datenbanksuche nach Elternknoten des aktuellen Menuepunkts
			$this->parentsAliasStr = $this->getParentAliases($this->pageDef['menuItem'], $this->pageDef['lft'], $this->pageDef['rgt']);
		
			return $query[0];
			
		} // Ende count queryPages
		
		return false;
	
	}
	

	/**
	 * Inhalte auslesen
	 *
	 * @param	string			$conTable	Inhaltstabelle
	 * @param	string			$pageID		page-ID
	 * @param	string			$lang		Sprache
	 * @access	protected
     * @return  array | boolean
	 */
	protected function getContentsQuery($conTable, $pageID, $lang)
	{
		
		// Falls keine Inhalte
		if($this->contentNumber < 1)
			return array();
		
	
		$pageIDdb		= $this->DB->escapeString($pageID);
		$conTableDb		= $this->DB->escapeString(DB_TABLE_PREFIX . $conTable);
		$conQueryStr	= "";
	
		// Query string	
		for($c = 1; $c <= $this->contentNumber; $c++) {
		
			$conQueryStr .= "`con" . $c . "_" . $lang . "`,`type-con" . $c . "`,`styles-con" . $c . "`,";
			
		}
		
		$conQueryStr = substr($conQueryStr, 0, -1);
		
		// Inhalte auslesen
		$queryContents = $this->DB->query("SELECT $conQueryStr
												FROM `$conTableDb` 
											WHERE `page_id` = '$pageIDdb'
											");

		#var_dump($queryContents);
		
		if(count($queryContents) == 1)		
			return $queryContents[0];
		
		return false;

	}
	
	
	// getParentAliases
	protected function checkEditAccess()
	{

		// Falls kein entsprechender Eintrag gefunden wurde
		if((
			(!$this->isTemplateArea 
			&& ($this->queryPages === false 
				|| count($this->queryPages) == 0
			)
			) 
			|| $this->queryContents === false
		   ) 
		&& !empty($this->editId)
		&& $this->editId > -1001
		)
			$noAccess	=	'{s_error:noeditentry}'.$this->editId;		

		// Falls die Seite nur für Admin oder Editor mit Beschränkung zugänglich ist, Noaccess-Seite anzeigen
		if(!$this->isTemplateArea 
		&& count($this->queryPages) > 0 
		&& !$this->getWritePermission($this->pageDef['groups_write'])
		)
			$noAccess	=	'{s_error:noaccess}'; 
		
		if(isset($noAccess)) {
		
			// Ggf. EditID aus Session löschen
			$this->unsetSessionKey('edit_id');

			$this->adminContent .=	'<p class="notice error">' . $noAccess . '</p>' . PHP_EOL; 
			$this->adminContent .=	$this->getBackButtons($this->isTemplateArea);		
			// #adminContent close
			$this->adminContent	.= $this->closeAdminContent();
			
			return false;
		}
		
		return true;
	
	}
	
	
	// getParentAliases
	protected function getParentAliases($menuItem, $lft, $rgt)
	{

		$output			= "";
		$queryParents	= $this->DB->query("SELECT `alias_" . $this->editLang . "` 
												FROM " . DB_TABLE_PREFIX . parent::$tablePages . " 
											WHERE lft < " . "'$lft'" . "AND rgt > " . "'$rgt'" . " 
												AND `menu_item` = $menuItem 
											ORDER BY lft"
											);

		foreach($queryParents as $parentAlias) {
			$output .= $parentAlias['alias_' . $this->editLang] . "/";
		}
		
		return substr($output, 1); // Ersten Slash vom Wurzelverzeichnis entfernen
	
	}
	
	
	// getParentAliases
	protected function getContentElements()
	{

		// ID in Session speichern
		setcookie('edit_id', $this->editId); // Bewirkt aufklappen des Untermenüs bei der Seitenliste
		setcookie('sort_id', $this->editId);
		
		// if single element edit
		if(!empty($this->editElementNumber)
		&& is_int($this->editElementNumber)
		) {
			$this->elementStartNumber	= $this->editElementNumber;
			$this->elementPageNumber	= 1;
			$loopNum	= $this->elementStartNumber;
		}
		else {
			$this->elementPageNumber	= ($this->elementStartNumber-1) / self::MAX_CONELEM_NO +1;
			$loopNum	= min($this->busyContentNumber, (self::MAX_CONELEM_NO * $this->elementPageNumber));
		}
		
		// Inhaltstypen-Definitionen
		$this->coreConTypes = $this->getCoreContentTypes();		

		// Schleife zum Auslesen der relevanten sprich zur ausgewählten Sprache und zur page_id passenden bzw. vorhandenen Inhalte
		for($i = $this->elementStartNumber; $i <= $loopNum; $i++) {
		
			$this->editElements[$i]	= $this->getContentElement($i);
		
		} // Ende for
	}
	
	
	// Single Content element
	public function getContentElement($i, $showHeaderPanel = true)
	{
	
		$this->elementExists	= true;
		$this->isPlugin			= false;
		$this->elementStatus	= 1;
		$this->statusButton		= "publish";			
		$this->wrongInput		= array();
		$checkTitle				= "";
		$checkAlias				= "";
		$error 					= "";
		$allLangs				= 0;
		$output 				= "";
		
		foreach($this->queryContents as $fieldName => $content) {
		
			if($fieldName == "page_id")
				continue;
			
			if($fieldName == "con" . $i . "_" . $this->editLang) {
				$this->conElements[$i]['con']		= $content;
				$this->conElements[$i]['fieldname']	= $fieldName;
				$this->conElements[$i]['conprefix']	= $fieldName;
				$this->fieldName					= $fieldName;
			}
			if($fieldName == "type-con" . $i) {			
				$this->conElements[$i]['type']		= $content;
				$this->conElements[$i]['conprefix']	= $content . '-' . $this->conElements[$i]['conprefix'];
				$this->conPrefix					= $this->conElements[$i]['conprefix'];
			}
			
			if($fieldName == "styles-con" . $i) {			
				$this->conElements[$i]['style']		= $content;
				
				// Element-Status
				if(strpos($content, "<hide>") !== false) {
					$this->elementStatus			= 0;
					$this->statusButton				= "unpublish";
					$this->conElements[$i]['style'] = str_replace("<hide>", "", $this->conElements[$i]['style']);
				}
			}
		
		} // Ende foreach

		
		
		// Falls kein Inhaltstyp angegeben ist
		if(empty($this->conElements[$i]['type']))
			return false;
	

		// Falls der Inhaltstyp angegeben ist, Inhalte nach und nach auslesen
		// Überprüfen ob Elementtyp vorhanden
		if(false === parent::arraySearch_recursive($this->conElements[$i]['type'], $this->coreConTypes)
		&& false === in_array($this->conElements[$i]['type'], $this->systemContentElements)
		)
			if(file_exists(PLUGIN_DIR . $this->conElements[$i]['type'] . '/config_' . $this->conElements[$i]['type'] . '.inc.php'))
				$this->isPlugin			= true;
			else
				$this->elementExists	= false;
		
		 
		$this->usedFieldNames = $this->usedFieldNames + 1; // Anzahl Inhalte mitzählen
		
		// Falls ein Element kopiert oder ausgeschnitten wurde
		$copyConArr		= array("","","","");
		$cutClass		= "";
		$addPasteBtn	= true;
		$addCancelBtn	= false;
		$stylesPost		= array();
		
		if(isset($this->g_Session['copycon']))
			$this->pasteCon = explode(",", $this->g_Session['copycon']);
		
		#var_dump($this->pasteCon);
		if(count($this->pasteCon) > 0) {
		
			$copyConArr	= $this->pasteCon;
			
			// Falls ausgeschnittenes Element, Listentag markieren
			if( $copyConArr[0] == parent::$tableContents && 
				$copyConArr[1] == $i && 
				$copyConArr[2] == $this->editId
			) {
				$addCancelBtn		= true;
				
				if($copyConArr[3] == "true") {
					$cutClass		= "cutListEntry ";
					$addPasteBtn	= false;
				}
				else
					$cutClass		= "copiedListEntry ";
			}
		}

		
		// Styleangaben
		if($this->conElements[$i]['type'] != "script" 
		&& $this->conElements[$i]['type'] != "redirect"
		)
			$this->isStylable	= true;
		else
			$this->isStylable	= false;
		
		
	
		// Element styles
		// Edit Event
		$this->o_extendEditEvent->conElements	= $this->conElements;
		$this->o_extendEditEvent->conPrefix		= $this->conPrefix;
		$this->o_extendEditEvent->elementStatus	= $this->elementStatus;
		$this->o_extendEditEvent->statusButton	= $this->statusButton;
		$this->o_extendEditEvent->sectionTags	= $this->sectionTags;
		$this->o_extendEditEvent->eleCnt		= $i;
		$this->o_extendEditEvent->wrongInput	= $this->wrongInput;
		
		// dispatch event get_styles_fields 
		$this->o_dispatcher->dispatch('edit.eval_edit_post', $this->o_extendEditEvent);
		
		$styles					= $this->o_extendEditEvent->styles;
		$this->wrongInput		= $this->o_extendEditEvent->wrongInput;
		
		$this->elementStatus			= $this->o_extendEditEvent->elementStatus;
		$this->statusButton				= $this->o_extendEditEvent->statusButton;
		
		
	
		// Style containers hint
		$sectionHint	= "";
		
		if($this->isStylable) {

			$sectionHint	.= '<span class="{t_class:label' . ($styles['sec'] ? 'suc' : 'def') . '}">';
			$sectionHint	.= '&lt;' . ($styles['sec'] && $styles['sec'] != "x" ? $this->sectionTags[$styles['sec']] : 'section') . ($styles['secid'] != "" ? '#' . htmlspecialchars($styles['secid']) : '') . ($styles['secclass'] != "" ? '.' . htmlspecialchars($styles['secclass']) : '') . "&gt;";
			$sectionHint	.= '</span>';
			
			$sectionHint	.= $sectionHint != "" ? ' &#8627; ' : '';
			
			$sectionHint	.= '<span class="{t_class:label' . ($styles['ctr'] ? 'suc' : 'def') . '}">';
			$sectionHint	.= "&lt;container" . ($styles['ctrid'] != "" ? '#' . htmlspecialchars($styles['ctrid']) : '') . ($styles['ctrclass'] != "" ? '.' . htmlspecialchars($styles['ctrclass']) : '') . "&gt;";
			$sectionHint	.= '</span>';
			
			$sectionHint	.= $sectionHint != "" ? ' &#8627; ' : '';

			$sectionHint	.= '<span class="{t_class:label' . ($styles['row'] ? 'suc' : 'def') . '}">';
			$sectionHint	.= "&lt;row" . ($styles['rowid'] != "" ? '#' . htmlspecialchars($styles['rowid']) : '') . ($styles['rowclass'] != "" ? '.' . htmlspecialchars($styles['rowclass']) : '') . "&gt;";
			$sectionHint	.= '</span>';
			
			$sectionHint	.= $sectionHint != "" ? ' &#8627; ' : '';

			$sectionHint	.= '<span class="{t_class:label' . ($styles['div'] ? 'suc' : 'def') . '}">';
			$sectionHint	.= "&lt;div" . ($styles['divid'] != "" ? '#' . htmlspecialchars($styles['divid']) : '') . ($styles['divclass'] != "" ? '.' . htmlspecialchars($styles['divclass']) : '') . "&gt;";
			$sectionHint	.= '</span>';
		}
		
		
		// If more elements than max_conelem_no present
		if(empty($this->editElementNumber)
		&& $i == $this->elementStartNumber
		&& $this->elementStartNumber > self::MAX_CONELEM_NO
		) {
		
			// Button previous
			$btnDefs	= array(	"href"		=> $this->formAction . '&edit_id=' . $this->editId . '&start_con=' . ($this->elementStartNumber - self::MAX_CONELEM_NO),
									"class"		=> 'showPreviousElements showMoreElements',
									"text"		=> '{s_text:additional} ' . ($this->elementStartNumber -1),
									"title"		=> '{s_text:additional} ' . ($this->elementStartNumber -1),
									"attr"		=> 'data-ajax="true"',
									"icon"		=> "up"
								);
				
			$output .=	parent::getButtonLink($btnDefs) . PHP_EOL;
			#$output .=	'<input type="hidden" name="max_content_element' . $this->conPrefix . '" value="on" /></label>';
		}

		
		// Element header (edit panel)
		if($showHeaderPanel) {
		
		
			$output 	.=	'<li id="content-'.$i.'" class="cc-edit-element-box contentElement listItem ' . $cutClass . 'sortid-'.$i.'" data-sortid="'.$i.'" data-sortidold="'.$i.'" data-menu="context" data-target="contextmenu-elem-' . $i . '">' . PHP_EOL;
			
			if($this->editId > -1000)
				$output .=	'<label class="markBox">' . PHP_EOL .
							'<input type="checkbox" class="addVal" name="markConNr[' . $i . ']" />' . PHP_EOL .
							'</label>' . PHP_EOL;
			else
				$output .=	'<span class="markBox iconBox">' . PHP_EOL .
							parent::getIcon("lock") .
							'</span>' . PHP_EOL;
			
			
			$output .=	'<span class="type' . (!$this->elementExists ? ' unknownConType" title="{s_error:unknowncon}: ' . $this->conElements[$i]['type'] : '" title="{s_option:' . $this->conElements[$i]['type'] . '}') . '">' . $this->conElements[$i]['type'] .
						'</span>' . PHP_EOL;
			
			
			// EditButtons panel
			if(!in_array($this->conElements[$i]['type'], $this->systemContentElements))
				$output .=	$this->getEditButtonsPanel($styles, $i, $addCancelBtn, $addPasteBtn);
			
			
			// Preview panel
			$this->conPreview	=	$this->getPreviewPanel($styles, $i);
			
		
			// Content Type
			$output .=	'<span class="conType' . (!$this->elementExists ? ' unknownConType" title="{s_error:unknowncon}: ' . $this->conElements[$i]['type'] : '" title="{s_option:' . $this->conElements[$i]['type'] . '}') . '">' . PHP_EOL;
			
			$output .=	parent::getIcon($this->conElements[$i]['type'], ($this->isPlugin ? ' contype-plugins ' : '') . 'conicon-' . $this->conElements[$i]['type'], 'style="background-image:url(' . ($this->isPlugin ? PROJECT_HTTP_ROOT . '/plugins/' . $this->conElements[$i]['type'] .'/img/' : SYSTEM_IMAGE_DIR) . '/' . ($this->elementExists ? 'conicon_' . $this->conElements[$i]['type'] . '.png' : 'atten.png') . ');"');
			
			$output .=	'</span>';
			
			$output .=	'<div class="conNr" title="' . $this->fieldName . '">Element-' . $i .
						parent::getIcon("edit", "inline-icon {t_class:right}") .
						'<span><div class="conPreview cc-element-preview">' . $this->conPreview . '</div></span>' . PHP_EOL .
						'</div>' . PHP_EOL .
						'<br class="clearfloat" />' . PHP_EOL;
		}
		
		
		// Element config details
		$output .=	'<div class="elements cc-contype-' . $this->conElements[$i]['type'] . ' conType-' . $this->conElements[$i]['type'] . '" data-contype="' . $this->conElements[$i]['type'] . '">' . PHP_EOL;
			
		$output .=	'<h4 class="cc-contype-heading cc-h4">{s_option:' . $this->conElements[$i]['type'] . '}&nbsp;' .
					'<span class="cc-grid-info right">' . $sectionHint . '</span></h4>' . PHP_EOL;

		// Element settings tabs
		$output	.=	'<ul class="cc-tabs-tabheader" role="tablist">' . PHP_EOL .
					'<li class="cc-tab-details" role="tab"><a href="#cc-elementedit-tabcon-' . $i . '">' . parent::getIcon("settingsele", "inline-icon") . '&nbsp;{s_text:settings}</a></li>' . PHP_EOL;
		
		if($this->isStylable)					
			$output	.=	'<li class="cc-tab-styles" role="tab"><a href="#cc-stylesbox-' . $i . '">' . parent::getIcon("styles", "inline-icon") . '&nbsp;{s_header:styles}</a></li>' . PHP_EOL;
		
		$output	.=	'</ul>' . PHP_EOL;

		$output .=	'<div id="cc-elementedit-tabcon-' . $i . '" class="cc-tabcon">' . PHP_EOL;
		
		$this->isNewElement	= false;
		
		// Falls eine id über Get mitegegeben wurde, Vermerk zum neu angelegten (=letzten) Eintrag hinzufügen
		if((isset($GLOBALS['_GET']['new']) && 
			is_numeric($GLOBALS['_GET']['new']) && 
			$GLOBALS['_GET']['new'] == $i) || 
			in_array($i, $this->showElements)
		) {
			$this->isNewElement	= true;
			
			$output .= '<span class="newentry">';
			
			
			// Falls mehrere Sprachen installiert sind, Checkbox einbinden
			if(count($this->o_lng->installedLangs) > 1) {
				
				// Falls Elemente ohne Sprachunterscheidung angezeigt werden, die Checkbox "für alle Sprachen übernehmen" auf readonly stellen
				if(in_array($this->conElements[$i]['type'], $this->uniLangElements))
				
					$output .=	'<label class="right">{s_label:takechange} <input type="checkbox" checked="checked" disabled="true" />' . PHP_EOL .
								'<input type="hidden" name="all_langs_' . $this->conPrefix . '" value="on" /></label>';
				
				else // Falls Elemente mit Sprachunterscheidung angezeigt werden, die Checkbox "für alle Sprachen übernehmen" anzeigen
					$output .= '<label class="right">{s_label:takechange} <input type="checkbox" name="all_langs_' . $this->conPrefix . '" checked="checked" /></label>';
			
			} // Ende falls mehrere Sprachen					
			
			$output .= '{s_notice:newentry}</span>' . PHP_EOL;
			
		} // Ende if neuer Eintrag
		
		
		// Falls eine id über Get mitegegeben wurde, Anker zum neu angelegten Eintrag hinzufügen
		if((isset($GLOBALS['_GET']['connr']) && is_numeric($GLOBALS['_GET']['connr']) && $GLOBALS['_GET']['connr'] == $i) || 
			in_array($i, $this->showElements)
		)
			$output .= '<a name="con'.$i.'" id="con'.$i.'"></a><span class="editentry"></span>' . PHP_EOL;


		
		##########################################
		// Inhaltselement einbinden, falls bekannt
		##########################################
		if($this->elementExists)
			$output .=	$this->getConfigContentElement($i);
			
		else
			$output .=	$this->getErrorUnknowElement($this->conElements[$i]['type']);


		
		
		// Falls mehrere Sprachen angelegt sind und es sich nicht um einen neuen Eintrag handelt, Sprachübernahmecheckbox hinzufügen
		if(	count($this->o_lng->installedLangs) > 1 && 
			(!isset($GLOBALS['_GET']['new']) || $GLOBALS['_GET']['new'] != $i) && 
			!in_array($i, $this->showElements)
		)
			// Bei allen (alten) Elementen die Option zur Übernahme für alle Sprachen anzeigen, aber NICHT per default checken
			$output .= 	'<fieldset>' . PHP_EOL .
						'<span class="changeAllLangs">
						<label class="markBox"><input type="checkbox" name="all_langs_' . $this->conPrefix . '" id="all_langs_' . $this->conPrefix . '" /></label>
						<label for="all_langs_' . $this->conPrefix . '" class="inline-label">{s_label:takechange2}</label>
						</span>
						</fieldset>' . PHP_EOL;

		
		// elementedit-tabcon close
		$output .=	'</div>' . PHP_EOL;

		
		// Änderungen für alle Sprachen übernehmen
		if(isset($GLOBALS['_POST']['all_langs_' . $this->conPrefix]) && $GLOBALS['_POST']['all_langs_' . $this->conPrefix] = "on") { // Falls ein neues Inhaltselement für alle Sprachen übernommen werden soll
		
			$dbUpdateStrEdit	= $this->dbUpdateStr;
			$dbUpdateStrLangs	= array();
			
			foreach($this->installedLangs as $addLangs) {
				if($addLangs != $this->editLang)
					$dbUpdateStrLangs[] = preg_replace("/`con" . $i . "_" . $this->editLang . "` = /", "`con" . $i . "_" . $addLangs . "` = ", $dbUpdateStrEdit);
									
			}
			
			$dbUpdateStrEdit	= implode("", $dbUpdateStrLangs);
			$this->dbUpdateStr .= $dbUpdateStrEdit; // Updatestring um andere Sprachen update-Queries verlängern
		}
		
		
		// Styles-Array DB
		$stylesDB	= json_encode($styles);
		$stylesDB	= $this->DB->escapeString($stylesDB);
		
	
		// db-Updatestring
		$this->dbUpdateStr .= "`styles-con" . $i . "` = '" . $stylesDB . "',";
		
		
		// Styleangaben einbinden, falls kein script- oder redirect-Element (da hier sinnlos)
		if($this->isStylable)
			$output	.= $this->getStylesTabContent($i);
		
		
		$output .=	'</div>'. PHP_EOL; // element div close
		
		
		// Element close if header (edit panel)
		if($showHeaderPanel)
			$output .=	'</li>'. PHP_EOL;		
		
		
		// If more elements than max_conelem_no present
		if(empty($this->editElementNumber)
		&& $i == self::MAX_CONELEM_NO * $this->elementPageNumber
		&& $this->busyContentNumber > $i
		) {
		
			// Button back
			$btnDefs	= array(	"href"		=> $this->formAction . '&edit_id=' . $this->editId . '&start_con=' . ($i+1),
									"class"		=> 'showNextElements showMoreElements',
									"text"		=> '{s_text:additional} ' . ($this->busyContentNumber -$i),
									"title"		=> '{s_text:additional} ' . ($this->busyContentNumber -$i),
									"attr"		=> 'data-ajax="true"',
									"icon"		=> "toggle"
								);
				
			$output .=	parent::getButtonLink($btnDefs) . PHP_EOL;
			#$output .=	'<input type="hidden" name="max_content_element' . $this->conPrefix . '" value="on" /></label>';
		}
		
		// Fehler zählen
		$this->countErrors += count($this->wrongInput);
		
		return $output;
	
	}
	
	
	// getStylesTabContent
	protected function getStylesTabContent($i)
	{
	
		// Element styles
		// Edit Event
		$this->o_extendEditEvent->conPrefix		= $this->conPrefix;
		$this->o_extendEditEvent->sectionTags	= $this->sectionTags;
		$this->o_extendEditEvent->showFieldset	= $this->showFieldset;
		$this->o_extendEditEvent->eleCnt		= $i;
		$this->o_extendEditEvent->wrongInput	= $this->wrongInput;
		
		// dispatch event get_styles_fields 
		$this->o_dispatcher->dispatch('edit.get_styles_fields', $this->o_extendEditEvent);

		$this->wrongInput		= $this->o_extendEditEvent->wrongInput;
		
		$output =	'<div id="cc-stylesbox-' . $i . '" class="cc-stylesbox cc-tabcon">' . PHP_EOL;
		
		$output	.= $this->o_extendEditEvent->getOutput(true);
		
		$output	.=	'</div>' . PHP_EOL;
		
		return $output;
		
	}
	
	
	// Save page details
	protected function savePageDetails()
	{
	
		$error[1]	= "{s_error:wrongname}";
		$error[2]	= "{s_error:nameexists}";
		$error[3]	= "{s_notice:aliasrename}";
		$error[4]	= "{s_error:notempty}";
		$error[5]	= "{s_error:toolong}";
		
		if(trim($GLOBALS['_POST']['title']) != $this->pageDef['title'] 
		&& trim($GLOBALS['_POST']['title']) != ""
		) {
			$checkTitle = $this->validateTitle(trim($GLOBALS['_POST']['title']), true, true); // Titel überprüfen
		
			if($checkTitle === true) // Falls der Titel ok ist, diesen übernehmen
				$this->pageDef['title'] = trim($GLOBALS['_POST']['title']);
				
			elseif($checkTitle === false) // Falls der Titel unerlaubte Zeichen enthält
				$this->wrongInput['title'] = 1;
				
		}
		elseif(trim($GLOBALS['_POST']['title']) == "")
				$this->wrongInput['title'] = 4;
		
		if(trim($GLOBALS['_POST']['alias']) != $this->pageDef['alias'] && trim($GLOBALS['_POST']['alias']) != "") {
			$checkAlias = $this->getAlias(trim($GLOBALS['_POST']['alias']), $this->editId, $this->editLang); // Alias überprüfen

			if($checkAlias === false) // Falls der Titel unerlaubte Zeichen enthält
				$this->wrongInput['alias'] = 1;
				
			elseif($checkAlias != trim($GLOBALS['_POST']['alias'])) {// Falls der Alias bereits vorhanden war, Meldung auf automatische Umbenennung ausgeben
				$this->wrongInput['alias'] = 3;
				$this->pageDef['alias'] = $checkAlias;
			}

			else
				$this->pageDef['alias'] = $checkAlias;
			
		}
		elseif(trim($GLOBALS['_POST']['alias']) == "")
			$this->wrongInput['alias'] = 4;

		if(strlen(trim($GLOBALS['_POST']['htmltitle'])) > 100) // html-Titel auslesen
			$this->wrongInput['htmltitle'] = 5;
			
		elseif(trim($GLOBALS['_POST']['htmltitle']) != $this->pageDef['htmlTitle']) //  html-Titel auslesen
			$this->pageDef['htmlTitle'] = trim($GLOBALS['_POST']['htmltitle']);
			
		if(trim($GLOBALS['_POST']['descr']) != $this->pageDef['descr']) // Description auslesen
			$this->pageDef['descr'] = trim($GLOBALS['_POST']['descr']);
			
		if(trim($GLOBALS['_POST']['keyw']) != $this->pageDef['keyw']) // Keywords auslesen
			$this->pageDef['keyw'] = trim($GLOBALS['_POST']['keyw']);
			
		if($GLOBALS['_POST']['template'] != $this->pageDef['template'] && $GLOBALS['_POST']['template'] != "") // Template auslesen
			$this->pageDef['template'] = $GLOBALS['_POST']['template'];
		
		if(isset($GLOBALS['_POST']['nosearch']) && $GLOBALS['_POST']['nosearch'] == "on") // nosearch: Seite aus Suche ausschließen
			$this->pageDef['nosearch'] = 1;
		else
			$this->pageDef['nosearch'] = 0;
		
		if(isset($GLOBALS['_POST']['groups_read'])) // Usergroup (read) auslesen
			$this->pageDef['groups_read'] = $GLOBALS['_POST']['groups_read'];
		
		if(isset($GLOBALS['_POST']['groups_write'])) // Usergroup (write) auslesen
			$this->pageDef['groups_write'] = $GLOBALS['_POST']['groups_write'];
		
		if(isset($GLOBALS['_POST']['publish']) && $GLOBALS['_POST']['publish'] == "on") // Status veröffentlicht auslesen
			$this->pageDef['publish'] = 1;
		else
			$this->pageDef['publish'] = 0;
		
		if(isset($GLOBALS['_POST']['robotsIndex']) && $GLOBALS['_POST']['robotsIndex'] == "on") // Robots Index
			$this->pageDef['robots'] = 1;
		else
			$this->pageDef['robots'] = 0;
		
		if(isset($GLOBALS['_POST']['robotsFollow']) && $GLOBALS['_POST']['robotsFollow'] == "on") // Robots Follow
			$this->pageDef['robots'] += 2;
		else
			$this->pageDef['robots'] += 0;
		
		if(!empty($GLOBALS['_POST']['canonicalID']) && is_numeric($GLOBALS['_POST']['canonicalID'])) // canonical
			$this->pageDef['canonical'] = $GLOBALS['_POST']['canonicalID'];
		else
			$this->pageDef['canonical'] = 0;
		
		if(isset($GLOBALS['_POST']['copy']) && $GLOBALS['_POST']['copy'] == "on") { // copy (Seiten-Duplikat)
			$this->pageDef['copy'] = 1;
			if(!$this->pageDef['canonical'] > 0)
				$this->wrongInput['copy'] = true;
			}
		else
			$this->pageDef['copy'] = 0;
		
		
		$this->pageEditGroups		= $this->getPageEditGroups($this->pageDef['groups_write']);
		
		$dbTitle		= $this->DB->escapeString($this->pageDef['title']);
		$dbAlias		= $this->DB->escapeString($this->pageDef['alias']);
		$dbHtmlTitle	= $this->DB->escapeString($this->pageDef['htmlTitle']);
		$dbDescr		= $this->DB->escapeString($this->pageDef['descr']);
		$dbKeyw			= $this->DB->escapeString($this->pageDef['keyw']);
		$dbRobots		= $this->DB->escapeString($this->pageDef['robots']);
		$dbCanonical	= $this->DB->escapeString($this->pageDef['canonical']);
		$dbCopy			= $this->DB->escapeString($this->pageDef['copy']);
		$dbTemplate		= $this->DB->escapeString($this->pageDef['template']);
		$dbNosearch		= $this->DB->escapeString($this->pageDef['nosearch']);
		$dbGroupRead	= $this->DB->escapeString(implode(",", $this->pageDef['groups_read']));
		$dbGroupWrite	= $this->DB->escapeString(implode(",", $this->pageDef['groups_write']));
		$dbPublish		= $this->DB->escapeString($this->pageDef['publish']);
		
		
		// db-Tabelle sperren
		$lock = $this->DB->query("LOCK TABLES `" . DB_TABLE_PREFIX . parent::$tablePages . "`, `" . DB_TABLE_PREFIX . $this->tableContentsPrev . "`");
	
	
		// db-Update der Pages Tabelle
		$updateSQL1 = $this->DB->query("UPDATE " . DB_TABLE_PREFIX . parent::$tablePages . " 
											SET	`title_" . $this->editLang . "` = '$dbTitle',
												`alias_" . $this->editLang . "` = '$dbAlias', 
												`html_title_" . $this->editLang . "` = '$dbHtmlTitle', 
												`description_" . $this->editLang . "` = '$dbDescr', 
												`keywords_" . $this->editLang . "` = '$dbKeyw', 
												`robots` = $dbRobots, 
												`canonical` = $dbCanonical,
												`copy` = $dbCopy, 
												`template` = '$dbTemplate', 
												`nosearch` = $dbNosearch, 
												`group` = '$dbGroupRead', 
												`group_edit` = '$dbGroupWrite', 
												`published` = $dbPublish 
												WHERE `page_id` = '$this->editId'
											");
		
		

		// Falls die Seite als Startseite festgelegt werden soll
		if(isset($GLOBALS['_POST']['index']) && $GLOBALS['_POST']['index'] == "on") {
			$this->pageDef['index'] = 1;
			$this->setIndexPage();
		}
		
	}
	
	
	// Zielseite bei Datenmodulen übernehmen
	protected function updateDataCatTargetPage()
	{
	
		// Falls eine Zielseite angegeben wurde, diese für die Kategorie übernehmen
		if(isset($this->dbUpdateArticlesCat)) {
				 
			// db-Tabelle sperren
			$lock = $this->DB->query("LOCK TABLES `" . DB_TABLE_PREFIX . "articles_categories`");
		
			// db-Update der Inhaltstabelle
			$updateSQL2a = $this->DB->query("UPDATE `" . DB_TABLE_PREFIX . "articles_categories` 
												SET $this->dbUpdateArticlesCat 
												");
		}
		
		// Falls eine Zielseite angegeben wurde, diese für die Kategorie übernehmen
		if(isset($this->dbUpdateNewsCat)) {
				 
			// db-Tabelle sperren
			$lock = $this->DB->query("LOCK TABLES `" . DB_TABLE_PREFIX . "news_categories`");
		
			// db-Update der Inhaltstabelle
			$updateSQL2b = $this->DB->query("UPDATE `" . DB_TABLE_PREFIX . "news_categories` 
												SET $this->dbUpdateNewsCat 
												");
		
		}

		// Falls eine Zielseite angegeben wurde, diese für die Kategorie übernehmen
		if(isset($this->dbUpdatePlannerCat)) {
				 
			// db-Tabelle sperren
			$lock = $this->DB->query("LOCK TABLES `" . DB_TABLE_PREFIX . "planner_categories`");
		
			// db-Update der Inhaltstabelle
			$updateSQL2b = $this->DB->query("UPDATE `" . DB_TABLE_PREFIX . "planner_categories` 
												SET $this->dbUpdatePlannerCat 
												");
		
		}

		// db-Sperre aufheben
		$unLock = $this->DB->query("UNLOCK TABLES");
	}

	
	// Seite nach Speichern der Inhalte neu laden, bzw. zum Frontend gehen
	protected function redirectUpdatePage()
	{

		if($this->redirect == "_index")
			$updGoTo = "";
		elseif(!empty($this->redirect))
			$updGoTo = $this->redirect;
		else {
			$updGoTo = 'admin?task=' . self::$task . '&edit_id=' . $this->editId . ($this->isTemplateArea ? '&area=' . parent::$tableContents : '') . ($this->elementStartNumber > self::MAX_CONELEM_NO ? '&start_con=' . $this->elementStartNumber : '');
			$this->setSessionVar('notice', $this->success);
		}
		
		header("location: " . PROJECT_HTTP_ROOT . "/" . $updGoTo); // ...und Seite neu laden
		exit;
		return true;
	
	}

	
	// Formular bzw. Html für Seitendetails
	protected function getPageDetailsForm()
	{

		// Benutzergruppen überprüfen auf ggf. nicht mehr vorhandene Gruppen
		foreach($this->pageDef['groups_read'] as $group) {
			if(!in_array($group, $this->userGroups))
				$this->invalidUserGroups[] = $group;
		}
		
		
		// Gruppenzugehörigkeit
		$pageAccess	=	'<span class="iconBox">' .
						parent::getIcon((in_array("public", $this->pageDef['groups_read']) ? '' : 'admin') . 'user', '', 'title="{s_title:' . (in_array("public", $this->pageDef['groups_read']) ? 'publicpage}' : 'grouppage}' . implode(", ", $this->pageDef['groups_read'])) . '"') .
						'</span>' . PHP_EOL;

		
		$this->adminContent .=	'<div class="editHeader">' . PHP_EOL;
		
		$this->adminContent .=	'<span class="editTplArea">' . PHP_EOL .
								'<span class="buttonPanel-hover">' . PHP_EOL .
								'<span class="iconBox iconBox-page">' .
								parent::getIcon('page', (empty($this->pageDef['publish']) ? parent::$iconDefs['offline'] : '')) .
								'</span>' .
								'<span class="page" title="{s_common:uses} <strong>' . $this->pageTemplate . '</strong>" data-titlepos="top">' .
								'<span class="pageTitlePrefix">' . $pageAccess . ' {s_header:page} &#9658; </span>' .
								'<span class="pageTitle" title="page ID #' . $this->editId . '" data-titlepos="top">' . $this->parentsAliasStr . $this->pageDef['title'] . '</span>' . PHP_EOL .
								'</span>' . PHP_EOL;
		
		
		// EditTpl Panel
		$tplEditPanel		 =	$this->getTplEditPanel($this->pageTemplate, "main");

		$this->adminContent .=	$tplEditPanel .
								'</span>' . PHP_EOL .
								'</span>' . PHP_EOL;
		
		// Panel for rightbar
		$this->adminRightBarContents[]	= '<div class="controlBar">' . str_replace("buttonPanel popupBox", "", $tplEditPanel) . '</div>';
		
		// Back to list
		$this->adminContent .=	'<span class="editButtons-panel">' . PHP_EOL;
		
		// Button backtolist
		$btnDefs	= array(	"href"		=> $this->formAction,
								"class"		=> 'button-icon-only',
								"title"		=> '{s_button:adminedit}',
								"attr"		=> 'data-ajax="true"',
								"icon"		=> "backtolist"
							);
			
		$this->adminContent .=	parent::getButtonLink($btnDefs) . PHP_EOL;
	
		$this->adminContent .=	'</span>' . PHP_EOL;
		
		
		$changesButtons =	"";
		
		// Falls Änderungen bestehen, die übernommen werden können, Button einbinden
		if(in_array($this->pageDef['alias'], $this->diffConAlias)) {
		
			$changesButtons	= "";
			
			// Button apply
			$btnDefs	= array(	"href"		=> ADMIN_HTTP_ROOT . '?task=changes&affect=1&edit_id=' . $this->editId . '&edit=1',
									"class"		=> "applyChanges button-icon-only",
									"text"		=> "",
									"title"		=> "{s_link:changes}",
									"icon"		=> "apply",
									"attr"		=> 'data-action="applychanges"'
								);
		
			$changesButtons	.=	parent::getButtonLink($btnDefs) . PHP_EOL;
		
			// Button cancel
			$btnDefs	= array(	"href"		=> ADMIN_HTTP_ROOT . '?task=changes&affect=0&edit_id=' . $this->editId . '&edit=1',
									"class"		=> "discardChanges button-icon-only",
									"text"		=> "",
									"title"		=> "{s_link:nochanges}",
									"icon"		=> "cancel",
									"attr"		=> 'data-action="discardchanges"'
								);
		
			$changesButtons	.=	parent::getButtonLink($btnDefs) . PHP_EOL;
			
		}
		
		// Cache aktualisieren
		if(CACHE && $this->editId > 0) {
		
			// Button cache
			$btnDefs	= array(	"href"		=> SYSTEM_HTTP_ROOT . '/access/editElements.php?page=admin&action=cache&id=' . $this->editId,
									"class"		=> "refreshCache button-icon-only",
									"text"		=> "",
									"title"		=> "{s_notice:cache}",
									"icon"		=> 'cache' . (isset($GLOBALS['_GET']['cacheref']) && $GLOBALS['_GET']['cacheref'] == 1 ? '-ok' : ''),
									"attr"		=> 'data-action="refreshcache"'
								);
		
			$changesButtons	.=	parent::getButtonLink($btnDefs) . PHP_EOL;
		}
			
		$changesButtons =	'<span class="changesButtons-panel editButtons-panel panel-right">' . PHP_EOL .
							$changesButtons .
							'</span>' . PHP_EOL;
		
		
		$this->adminContent .=	$changesButtons;
		
		$this->adminContent .=	'</div>' . PHP_EOL .
								'<br class="clearfloat" />' . PHP_EOL .
								'</div>' . PHP_EOL;
		
		
		
		// Seiteneinstellungen
		$this->adminContent .=	'<div class="editPageDetailsDiv">' . PHP_EOL;
	
		$this->adminContent .=	'<form action="' . $this->formAction . '&edit_id=' . $this->editId . '" id="editPageDetails-form" method="post" enctype="multipart/form-data" accept-charset="UTF-8"' . (!empty($this->redirect) ? ' data-ajax="false"' : '') . ' data-history="false">' . PHP_EOL;
		
		$this->adminContent .=	'<h3 class="cc-h3 actionHeader toggle page' . (empty($this->errorPageDetails) ? ' hideNext' : '') . '">{s_header:editpage}' . PHP_EOL;						
		
		// Publish page
		$this->adminContent .=	'<span class="publishPage-panel editButtons-panel">' .
								'<label class="label-inline left">{s_label:pagestatus}&nbsp;&#9654;' . PHP_EOL .
								'<input class="togglePageStatus hide" type="checkbox" name="publish"';
		
		if(isset($this->pageDef['publish']) && $this->pageDef['publish'] == 1)
			$this->adminContent .=' checked="checked"';	

		$this->adminContent .=	' /></label>' . PHP_EOL .
								'<span class="publishPage toggle-cwms right" data-url="' . SYSTEM_HTTP_ROOT . '/access/listPages.php?page=admin&edit_id=' . $this->editId . '&online=' . (isset($this->pageDef['publish']) && $this->pageDef['publish'] ? 0 : 1) . '">' . PHP_EOL .
								'<label class="label-inline">online' . PHP_EOL .
								'<input class="togglePageStatus" type="checkbox" name="publish"';
		
		if(isset($this->pageDef['publish']) && $this->pageDef['publish'] == 1)
			$this->adminContent .=' checked="checked"';	

		$this->adminContent .=	' /></label>' . PHP_EOL .
								'</span>' . PHP_EOL .
								'</span>' . PHP_EOL;
		
		$this->adminContent .=	'</h3>' . PHP_EOL;						
		
		$this->adminContent .=	'<div class="adminBox">' . PHP_EOL;
		
		$this->adminContent .=	'<ul class="contents pageDetails framedItems">' . PHP_EOL . 
								'<li><label>{s_label:title}_' . $this->editLang . ' {s_label:title2}<span class="editLangFlag">' . $this->editLangFlag . '</span>' . PHP_EOL;
							
		if(isset($this->wrongInput['title'])) // Falls im Fehlerarray vorhanden Meldung ausgeben
			$this->adminContent .= '<span class="notice">&quot;' . htmlspecialchars(trim($GLOBALS['_POST']['title'])) . '&quot;: ' . $error[$this->wrongInput['title']] . '</span>' . PHP_EOL;
		
		// Seitenname
		$this->adminContent .=	'</label>' . PHP_EOL .
								'<input name="title" type="text" id="title" value="' . htmlspecialchars($this->pageDef['title']) . '" maxlength="100" />' . PHP_EOL .
								'</li>' . PHP_EOL . 
								'<li><label>{s_label:alias}_' . $this->editLang . ' {s_label:alias2}<span class="editLangFlag">' . $this->editLangFlag . '</span>' .PHP_EOL;
							
		if(isset($this->wrongInput['alias'])) // Falls im Fehlerarray vorhanden Meldung ausgeben
			$this->adminContent .= '<span class="notice">&quot;' .  htmlspecialchars(trim($GLOBALS['_POST']['alias'])) . '&quot;: ' . $error[$this->wrongInput['alias']] . '</span>' . PHP_EOL;
		
		// Seitenalias
		$this->adminContent .=	'</label>' . PHP_EOL .
								'<input name="alias" type="text" id="alias" value="' . htmlspecialchars($this->pageDef['alias']) . '" maxlength="100" />' . PHP_EOL .
								'</li>' . PHP_EOL .
								'<li><label>{s_label:htmltitle}_' . $this->editLang . ' {s_label:htmltitle2}<span class="editLangFlag">' . $this->editLangFlag . '</span>' . PHP_EOL;

		if(isset($this->wrongInput['htmltitle'])) // Falls im Fehlerarray vorhanden Meldung ausgeben
			$this->adminContent .= '<span class="notice">&quot;' . htmlspecialchars(trim($GLOBALS['_POST']['htmltitle'])) . '&quot;: ' . $error[$this->wrongInput['htmltitle']] . '</span>' . PHP_EOL;
		
		// Seitentitel
		$this->adminContent .=	'</label>' . PHP_EOL .
								'<input name="htmltitle" type="text" id="htmltitle" value="' . htmlspecialchars($this->pageDef['htmlTitle']) . '" maxlength="100" />' . PHP_EOL .
								'</li>' . PHP_EOL;
							
		// Description
		$this->adminContent .=	'<li><label>{s_label:descr}_' . $this->editLang . ' {s_label:descr2}<span class="editLangFlag">' . $this->editLangFlag . '</span>' . PHP_EOL . 
								'</label>' . PHP_EOL . 
								'<textarea name="descr" id="descr" maxlength="180" class="noTinyMCE" rows="3">' . htmlspecialchars($this->pageDef['descr']) . '</textarea>' . PHP_EOL .
								'</li>' . PHP_EOL;

		// Keywords
		$this->adminContent .=	'<li><label>{s_label:keyw}_' . $this->editLang . ' {s_label:keyw2}<span class="editLangFlag">' . $this->editLangFlag . '</span>' . PHP_EOL . 
								'</label>' . PHP_EOL . 
								'<input name="keyw" type="text" id="keyw" value="' . htmlspecialchars($this->pageDef['keyw']) . '" />' . PHP_EOL;

		
		$keywords =	"";
		
		if($this->editId > -1000) {
			
			// Ggf. Keywords vorschlagen
			require_once PROJECT_DOC_ROOT."/inc/classes/Modules/class.Keygen.php";
			
			// Datenbanksuche nach Inhalten des aktuellen Menuepunkts (aus Tabelle "search")
			$queryKeywords = $this->DB->query( "SELECT `con_" . $this->editLang . "` 
													FROM `" . DB_TABLE_PREFIX . "search` 
													WHERE `page_id` = " . $this->editId . ""
													);
			
			#var_dump($queryKeywords);
			
			
			if(count($queryKeywords) > 0)
				$keywords =		Keygen::getKeywords($queryKeywords[0]["con_" . $this->editLang], $this->editLang);
		}
		
		if($keywords != "") {
		
			$this->adminContent .=	'<label>{s_label:keywsuggest}<br />' . PHP_EOL .
									'<span class="keywordSuggestions">' . $keywords . '</span>' . PHP_EOL .
									'<span class="editButtons-panel">' . PHP_EOL;
			
			// Button fetch
			$btnDefs	= array(	"type"		=> "button",
									"class"		=> "fetchKeywords button-icon-only",
									"text"		=> "",
									"title"		=> "{s_label:keywsuggest} {s_common:fetch}",
									"icon"		=> 'fetch',
									"attr"		=> 'data-keywords="' . $keywords . '"'
								);
		
			$this->adminContent	.=	parent::getButtonLink($btnDefs);
									
			$this->adminContent	.=	'</span>' .  PHP_EOL .
									'</label>' . PHP_EOL;
		}
		
		$this->adminContent .=	'</li>' . PHP_EOL;

		
		// Templates auflisten
		// Existing Tempates
		$this->existTemplates	= parent::readTemplateDir();
		
		$this->adminContent .=	'<li>' . PHP_EOL .
								'<label class="tplSelect-label">Template</label>' . PHP_EOL . 
								parent::listTemplates($this->pageTemplate, $this->defaultTemplates, $this->existTemplates, "select");
		
		if(!in_array($this->pageTemplate, $this->existTemplates))
			$this->adminContent .=	parent::getIcon("warning", "hint", 'title="{s_title:tplnotexits}"');
		
		// tplSelectionBox
		$this->adminContent .=	'<br class="clearfloat" />' . PHP_EOL . 
								'<div id="tplSelectionBox" class="choose imagePicker">' . PHP_EOL .
								'</div>' . PHP_EOL;							
		
		// Tpl image picker
		$this->adminContent .=	$this->getTplScriptTag();
		
		$this->adminContent .=	'</li>' . PHP_EOL;
		
		
		if(count($this->invalidUserGroups) > 0 || isset($this->wrongInput['copy']))
			$showDetails	= true;
		else
			$showDetails	= false;			
			
		
		// Seitendetails
		$this->adminContent .=	'<li><label class="markBox"><input type="checkbox" name="showPageDetails" id="showPageDetails" class="toggleDetails" data-toggle="pageDetailsBox"' . ($showDetails ? ' checked="checked"' : '') . ' /></label>' . PHP_EOL .
								'<label for="showPageDetails" class="showPageDetails inline-label">{s_title:toggledetails}</label></li>' . PHP_EOL .
								'<div id="pageDetailsBox" class="pageDetails detailsDiv"' . (!$showDetails > 0 ? ' style="display:none;"' : '') . '>' . PHP_EOL;
		
		
		// Seite als Startseite
		$this->adminContent .=	'<li><label class="markBox"><input type="checkbox" name="index" id="index"';
		
		if(isset($this->pageDef['index']) && $this->pageDef['index'] == 1)
			$this->adminContent .=' checked="checked" disabled="disabled" />';	
		else
			$this->adminContent .=	' />';
		$this->adminContent .=	'</label>' .
								'<label for="index" class="inline-label">{s_label:indexpage}' . (isset($this->pageDef['index']) && $this->pageDef['index'] == 1 ? ' ({s_label:selected})' : '') . '</label>' .
								'</li>' . PHP_EOL;
		
		// Robots index,follow
		$this->adminContent .=	'<li><label class="markBox"><input type="checkbox" name="robotsIndex" id="robotsIndex"';
		
		if(isset($this->pageDef['robots']) && ($this->pageDef['robots'] == 1 || $this->pageDef['robots'] == 3))
			$this->adminContent .=' checked="checked"';
		
		$this->adminContent .=	' /></label>' .
								'<label for="robotsIndex" class="inline-label"> index (Meta-Tag &quot;robots&quot;)';
		$this->adminContent .=	'</label>' . PHP_EOL;
		
		$this->adminContent .=	'<label class="markBox"><input type="checkbox" name="robotsFollow" id="robotsFollow"';
		
		if(isset($this->pageDef['robots']) && $this->pageDef['robots'] > 1)
			$this->adminContent .=' checked="checked"';
		
		$this->adminContent .=	' /></label>' .
								'<label for="robotsFollow" class="inline-label"> follow (Meta-Tag &quot;robots&quot;)';
		$this->adminContent .=	'</label>' .
								'</li>' . PHP_EOL;
		
		
		// rel=canonical
		$this->adminContent .=	'<li>' . PHP_EOL .
								'<label for="canonical">Canonical url (Link-Tag &quot;rel=\'canonical\'&quot;)</label>' . PHP_EOL;
							
		if(isset($this->wrongInput['copy'])) // Falls im Fehlerarray vorhanden Meldung ausgeben
			$this->adminContent .= '<span class="notice">{s_error:fill}</span>' . PHP_EOL;


		$mediaListButtonDef		= array(	"class"	 	=> "targetPage",
											"type"		=> "targetPage",
											"url"		=> SYSTEM_HTTP_ROOT . "/access/listPages.php?page=admin&type=link",
											"value"		=> "{s_button:targetPage}",
											"title"		=> "canonical url {s_button:delete}",
											"icon"		=> "page"
										);
		
		$this->adminContent .=	$this->getButtonMediaList($mediaListButtonDef);
		
		// Button reset
		$btnDefs	= array(	"type"		=> "button",
								"class"		=> 'resetHiddenField button-icon-only',
								"title"		=> 'canonical url {s_button:delete}',
								"attr"		=> 'data-target="canonical"' . ($this->pageDef['canonical'] > 0 ? '' : ' style="display:none;"'),
								"icon"		=> "delete"
							);
			
		$this->adminContent .=	parent::getButtonLink($btnDefs);
	
		
		$this->adminContent .=	'<input type="text" class="hide-on-empty targetPage"' . ($this->pageDef['canonical'] > 0 ? '' : ' style="display:none;"') . ' data-reset="canonical" readonly="readonly" name="canonical" value="' . ($this->pageDef['canonical'] > 0 ? HTML::getLinkPath($this->pageDef['canonical'], "editLang", false) : '') . '" />' . PHP_EOL .
								'<input type="hidden" data-reset="canonical" name="canonicalID" class="targetPageID" max-length="11" value="' . htmlspecialchars($this->pageDef['canonical']) . '" />' . PHP_EOL .
								'</li>' . PHP_EOL;				
		
		
		// copy (Seiten-Duplikat)
		$this->adminContent .=	'<li><label class="markBox"><input type="checkbox" name="copy" id="copy"';
		
		if(isset($this->pageDef['copy']) && $this->pageDef['copy'] == 1)
			$this->adminContent .=' checked="checked"';
		
		$this->adminContent .=	' /></label>' .
								'<label for="copy" class="inline-label">{s_label:pagecopy}';
		$this->adminContent .=	'</label></li>' . PHP_EOL;
		
		
		// nosearch
		$this->adminContent .=	'<li><label class="markBox"><input type="checkbox" name="nosearch" id="nosearch"';
		
		if(isset($this->pageDef['nosearch']) && $this->pageDef['nosearch'] == 1)
			$this->adminContent .=' checked="checked"';
		
		$this->adminContent .=	' /></label>' .
								'<label for="nosearch" class="inline-label">{s_label:nosearch}</label>' .
								'</li>' . PHP_EOL;

		// Benutzergruppenauswahl
		$this->adminContent .=	'<li>';  // Auswahl der Benutzergruppe
		
		// Benutzergruppen (read)
		$this->adminContent .=	'<div class="leftBox">';
		$this->adminContent .=	'<label>{s_label:usergroup}';
		
		if(count($this->invalidUserGroups) > 0)
			$this->adminContent .='&nbsp;&nbsp;' . parent::getIcon("warning", "hint", 'title="' . implode(", ", $this->invalidUserGroups) . ': {s_title:groupnotexits}"');
			
		$this->adminContent .=	'</label>' . PHP_EOL . 
								'<select multiple="multiple" size="' . count($this->userGroups) . '" name="groups_read[]" class="selgroup">' . PHP_EOL;
		
		// Benutzergruppen
		foreach($this->userGroups as $group) {
			$this->adminContent .='<option value="' . $group . '"' . (isset($this->pageDef['groups_read']) && in_array($group, $this->pageDef['groups_read']) ? ' selected="selected"' : '') . '>' . (in_array($group, $this->systemUserGroups) ? '{s_option:group' . $group . '}' : $group) . '</option>' . PHP_EOL; // Benutzergruppe
		}
		
		$this->adminContent .=	'</select>' . PHP_EOL;
		$this->adminContent .=	'</div>' . PHP_EOL;
			
		// Benutzergruppen (write)
		$this->adminContent .=	'<div class="rightBox">' . PHP_EOL;
		
		$this->adminContent .=	'<label>{s_common:rightswrite}</label>' . PHP_EOL .
								'<select multiple="multiple" size="' . count($this->loggedUserEditGroups) . '" name="groups_write[]">' . PHP_EOL;
		
		// Benutzergruppen
		foreach($this->loggedUserEditGroups as $group) {
			$this->adminContent .='<option value="' . $group . '"' . (!empty($this->pageEditGroups) && in_array($group, $this->pageEditGroups) ? ' selected="selected"' : '') . '>' . (in_array($group, $this->systemUserGroups) ? '{s_option:group' . $group . '}' : $group) . '</option>' . PHP_EOL;
		}
		
		$this->adminContent .=	'</select>' . PHP_EOL;
		$this->adminContent .=	'</div>' . PHP_EOL;
		
		$this->adminContent .=	'<br class="clearfloat" />' . PHP_EOL;
		$this->adminContent .=	'</li>' . PHP_EOL;
		
		$this->adminContent .=	'</div>' . PHP_EOL;
		
		$this->adminContent .=	'<li class="submit change">' . PHP_EOL;
		
		// Button new
		$btnDefs	= array(	"type"		=> "submit",
								"name"		=> "submit",
								"id"		=> "submit1",
								"class"		=> "change",
								"value"		=> "{s_button:savechanges}",
								"icon"		=> "ok"
							);
		
		$this->adminContent	.=	parent::getButton($btnDefs);
		
		$this->adminContent	.=	'<input name="submit" type="hidden" value="{s_button:savechanges}" />' . PHP_EOL;
		$this->adminContent	.=	'<input name="submit_page_details" type="hidden" value="1" />' . PHP_EOL .
								'</li>' . PHP_EOL .
								'</ul>' . PHP_EOL .
								'</div>' . PHP_EOL;
		
		$this->adminContent .=	'</form>' . PHP_EOL;
		$this->adminContent .=	'</div> ' . PHP_EOL;
	
	}

	
	// getSortScript
	protected function getSortScript()
	{
	
		return	'<script>' . PHP_EOL .
				'head.ready("ui", function(){' . PHP_EOL .
				'head.load({sort:"' . SYSTEM_HTTP_ROOT . '/access/js/adminSort.min.js"}, function(){' . PHP_EOL .
				'head.ready("ccInitScript", function(){' . PHP_EOL .
				'$.addInitFunction({name: \'$.hideSortButtons\', params: \'\'});' . PHP_EOL .
				'$.addInitFunction({name: \'$.sortableContents\', params: \'\'});' . PHP_EOL .
				'$.addInitFunction({name: \'$.toggleContentElements\', params: \'\'});' . PHP_EOL .
				'});' . PHP_EOL .
				'});' . PHP_EOL .
				'});' . PHP_EOL .
				'</script>' . PHP_EOL;
	
	}

	
	// Formular bzw. Html für Templatedetails
	protected function getTemplateDetailsForm()
	{

		// Existing Tempates
		$this->existTemplates	= parent::readTemplateDir();

		// EditHeader
		// TplSelection
		$tplSelection	=	'<div class="editTplChange">' .
							'<form action="' . $this->formAction . '" method="post" data-getcontent="fullpage">' . PHP_EOL .
							'<label class="tplSelect-label">Template</label>' . PHP_EOL . 
							parent::listTemplates($this->editId, $this->defaultTemplates, $this->existTemplates, "select", true) .  // Select zum Zuordnen des Templates
							'<input type="hidden" name="edit_tpl_change" value="true" />' . PHP_EOL . 
							'<input type="hidden" name="edit_area" value="' . parent::$tableContents . '" />' . PHP_EOL .
							'</form>' .
							'</div>' . PHP_EOL;
		
		// Button toggle themes
		$btnDefs	= array(	"type"		=> "button",
								"name"		=> "tplSelectionBox",
								"class"		=> '{t_class:btndef} button-small',
								"value"		=> htmlspecialchars($this->editId),
								"text"		=> htmlspecialchars($this->editId),
								"attr"		=> 'data-toggle="tplSelectionBar"',
								"title"		=> 'Template',
								"icon"		=> 'tplfile'
							);
		
		$tplSelection .=	'<span class="selectButtons-panel panel-right">' . PHP_EOL;		
		$tplSelection .=	parent::getButton($btnDefs, "right");		
		$tplSelection .=	'</span>' . PHP_EOL;

		
		$this->adminContent .=	'<div class="editHeader">' . PHP_EOL;

		$this->adminContent .=	'<span class="editTplArea">' . PHP_EOL .
								'<span class="buttonPanel-hover">' . PHP_EOL .
								'<span class="iconBox">' .
								parent::getIcon('area-' . $this->editTplArea) .
								'</span>' .
								'<span class="page" title="{#' . strtoupper($this->editTplArea) . '}" data-titlepos="top">' . PHP_EOL .
								'Template &#9658; <strong title="{#'.strtoupper($this->editTplArea).'}" data-titlepos="top">{s_conareas:contents_' . $this->editTplArea . '}</strong>' . PHP_EOL .
								'</span>' . PHP_EOL;
		
		// EditTpl Panel
		$tplEditPanel		 =	$this->getTplEditPanel($this->editId, $this->editTplArea);
		
		$this->adminContent .=	$tplEditPanel .
								'</span>' . PHP_EOL .
								'</span>' . PHP_EOL;
		
		// Panel for rightbar
		$this->adminRightBarContents[]	= '<div class="controlBar">' . str_replace("buttonPanel popupBox", "", $tplEditPanel) . '</div>';
		
		// Back to list
		$this->adminContent .=	'<span class="editButtons-panel">' . PHP_EOL;
		
		// Button backtolist
		$btnDefs	= array(	"href"		=> ADMIN_HTTP_ROOT . '?task=tpl',
								"class"		=> 'button-icon-only',
								"title"		=> '{s_button:admintpl}',
								"attr"		=> 'data-ajax="true"',
								"icon"		=> "backtolist"
							);
			
		$this->adminContent .=	parent::getButtonLink($btnDefs) . PHP_EOL;
	
		$this->adminContent .=	'</span>' . PHP_EOL;
		

		$changesButtons =	"";
		
		// Falls Änderungen bestehen, die übernommen werden können, Button einbinden
		if(in_array($this->editId . " (" . $this->editTplArea . ")", $this->diffConAlias)) {
		
			$changesButtons	= "";
			
			// Button apply
			$btnDefs	= array(	"href"		=> ADMIN_HTTP_ROOT . '?task=changes&affect=1&edit_tpl=' . $this->editId . '&edit=1&edit_area=' . parent::$tableContents,
									"class"		=> "applyChanges button-icon-only",
									"text"		=> "",
									"title"		=> "{s_link:changes}",
									"icon"		=> "apply",
									"attr"		=> 'data-action="applychanges"'
								);
		
			$changesButtons	.=	parent::getButtonLink($btnDefs);
		
			// Button cancel
			$btnDefs	= array(	"href"		=> ADMIN_HTTP_ROOT . '?task=changes&affect=0&edit_tpl=' . $this->editId . '&edit=1&edit_area=' . parent::$tableContents,
									"class"		=> "discardChanges button-icon-only",
									"text"		=> "",
									"title"		=> "{s_link:nochanges}",
									"icon"		=> "cancel",
									"attr"		=> 'data-action="discardchanges"'
								);
		
			$changesButtons	.=	parent::getButtonLink($btnDefs);
			
		}
		
		// Cache aktualisieren
		if(CACHE) {
		
			// Button cache
			$btnDefs	= array(	"href"		=> SYSTEM_HTTP_ROOT . '/access/editElements.php?page=admin&action=cache&id=' . $this->editId,
									"class"		=> "refreshCache button-icon-only",
									"text"		=> "",
									"title"		=> "{s_notice:cache}",
									"icon"		=> 'cache' . (isset($GLOBALS['_GET']['cacheref']) && $GLOBALS['_GET']['cacheref'] == 1 ? '-ok' : ''),
									"attr"		=> 'data-action="refreshcache"'
								);
		
			$changesButtons	.=	parent::getButtonLink($btnDefs);
		}
		
		$changesButtons =	'<span class="changesButtons-panel editButtons-panel panel-right">' . PHP_EOL .
							$changesButtons .
							'</span>' . PHP_EOL;
		
		
		$this->adminContent .=	$changesButtons;
		
		$this->adminContent .=	$tplSelection . 
								'</div>' . PHP_EOL .
								'<br class="clearfloat" />' . PHP_EOL .
								'</div>' . PHP_EOL;
		
		// tplSelectionBox
		$this->adminContent .=	'<div id="tplSelectionBar" class="controlBar" style="display:none;">' . PHP_EOL . 
								'<div id="tplSelectionBox" class="choose imagePicker">' . PHP_EOL .
								'</div>' . PHP_EOL .
								'</div>' . PHP_EOL;
		
		// Tpl image picker
		$this->adminContent .=	$this->getTplScriptTag(true);		

	}

	
	// Formular bzw. Html für Inhaltselemente
	protected function getContentElementsForm()
	{
	
		$formAction	= $this->formAction . '&edit_id=' . $this->editId . ($this->elementStartNumber > self::MAX_CONELEM_NO ? '&start_con=' . $this->elementStartNumber : '');
		
		$this->adminContent .=	'<div class="editPageContentsDiv">' . PHP_EOL;
	
		$this->adminContent .=	'<form action="' . $formAction . '" id="editPageContents-form" method="post" enctype="multipart/form-data" accept-charset="UTF-8"' . (!empty($this->redirect) ? ' data-ajax="false"' : '') . ' data-history="false">' . PHP_EOL;

		$this->adminContent .=	'<h3 class="cc-h3 toggleCons">{s_header:contents} (' . $this->busyContentNumber . ')</h3>' . PHP_EOL;
							
		$this->adminContent .=	'<ul id="sortableContents" class="contents elements sortable-container" data-url="' . SYSTEM_HTTP_ROOT . '/access/editElements.php?page=admin&action=sort&conmax=' . $this->contentNumber . '&connr=' . $this->usedFieldNames . '&id=' . $this->editId . '&area=' . parent::$tableContents . '&red=' . urlencode($formAction) . '" data-sort-min="' . $this->elementStartNumber . '">' . PHP_EOL; 
	
	
		// Aktion-Box erstes Element
		if($this->editId > -1000)
			$this->adminContent .=	$this->getActionBox();
		
		
		// Inhaltselemente einfügen
		$this->adminContent .=	implode("", $this->editElements);
		
		
	
		// Liste schließen				
		$this->adminContent .=	'</ul>' . PHP_EOL;
		
		// Button speichern, falls Elemente vorhanden
		if($this->busyContentNumber > 0) {
		
			$this->adminContent .=	$this->getElementsSubmitButton();
		}
		$this->adminContent .=	'<input type="hidden" id="edit_id" name="edit_id" value="' . $this->editId . '" />' . PHP_EOL . 
								(!empty($this->redirect) ? '<input type="hidden" id="redirect" name="redirect" value="' . $this->redirect . '" />' . PHP_EOL : '');
								#'<input type="hidden" name="token" value="' . parent::$token . '" />' . PHP_EOL . 
		
		if($this->isTemplateArea)
			$this->adminContent .=	'<input type="hidden" name="edit_area" value="' . parent::$tableContents . '" />' . PHP_EOL .
									'<input type="hidden" name="edit_tpl" value="' . $this->editId . '" />' . PHP_EOL;
		
		$this->adminContent .=	'</form>' . PHP_EOL;
		$this->adminContent .=	'</div> ' . PHP_EOL;
		
		
		// Footer action box
		if($this->editId > -1000)
			$this->adminContent .=	$this->getFooterActionBox();

		
		// Script-Tags
		$this->adminContent .=	$this->getScriptTags();

		// Script-Code
		$this->adminContent .=	$this->getScriptCode();

	}

	
	// Element-Konfiguration Html auslesen
	public function getConfigContentElement($i)
	{
	
		$output	= "";
		
		// Falls Extension/Plug-in
		if($this->isPlugin) {
			$this->contentElementKind	= "plugin";
			$pluginDir	= PLUGIN_DIR . $this->conElements[$i]['type'] . '/';
			$this->setPlugin($this->conElements[$i]['type'], $this->adminLang); // Sprachbausteine des Plug-ins laden
		}
		else
			$this->contentElementKind	= "core";
		
		require_once(SYSTEM_DOC_ROOT . '/inc/elements/class.ConfigElementFactory.php');
		

		// Element-Options
		$options	= array(	"conType"		=> $this->conElements[$i]['type'],
								"conValue"		=> $this->conElements[$i]['con'],
								"conAttributes"	=> "",
								"conNum"		=> $i,
								"conCount"		=> $i,
								"textAreaCount"	=> $this->textAreaCount
							);
		
		// Elementinhalt
		try {				
			// Inhaltselement-Instanz
			$o_element	= ConfigElementFactory::create($this->conElements[$i]['type'], $options, $this->contentElementKind, $this->DB, $this->o_lng);
		}
			
		// Falls ConfigElement-Klasse nicht vorhanden
		catch(Exception $e) {
			$output				   .= $this->backendLog ? $e->getMessage() : "";
			return $output;
		}
		
		// Vars übergeben
		$o_element->o_security		= $this->o_security;
		$o_element->g_Session		= $this->g_Session;
		// Element-Objekt Eigenschaften
		$o_element->conPrefix		= $this->conPrefix;
		$o_element->editId			= $this->editId;
		$o_element->editLang		= $this->editLang;
		$o_element->editLangFlag	= $this->editLangFlag;
		$o_element->userGroups		= $this->userGroups;
		$o_element->isNewElement	= $this->isNewElement;

		
		// Inhaltselement generieren
		$elementConfigArr			= $o_element->getConfigElement($GLOBALS['_POST']);
		
		
		// Element settings
		// Edit Event		
		$this->o_extendEditEvent->conType			= $this->conElements[$i]['type'];
		$this->o_extendEditEvent->params			= $o_element->params;
		$this->o_extendEditEvent->options			= $options;
		$this->o_extendEditEvent->elementConfigArr	= $elementConfigArr;
		
		// dispatch event get_settings_fields 
		$this->o_dispatcher->dispatch('edit.get_settings_fields', $this->o_extendEditEvent);
		
		$elementConfigArr			= $this->o_extendEditEvent->elementConfigArr;
		
		
		// Update-Field
		$this->dbUpdateStr		   .= "`" . $this->fieldName . "` = ";
		
		// ConfigElement-Rückgabe zuweisen
		$this->dbUpdateStr		   .= $elementConfigArr['update'] != "" ? $elementConfigArr['update'] : "'',";
		$this->textAreaCount	   += $elementConfigArr['textareas'];
		$this->scriptTags[]		 	= $elementConfigArr['script'];
		$output						= $elementConfigArr['output'];
		$this->wrongInput			= array_merge($this->wrongInput, $elementConfigArr['error']);
			

		if(!empty($elementConfigArr['dbUpdateArticlesCat']))	$this->dbUpdateArticlesCat	= $elementConfigArr['dbUpdateArticlesCat'];
		if(!empty($elementConfigArr['dbUpdateNewsCat']))		$this->dbUpdateNewsCat		= $elementConfigArr['dbUpdateNewsCat'];
		if(!empty($elementConfigArr['dbUpdatePlannerCat']))		$this->dbUpdatePlannerCat	= $elementConfigArr['dbUpdatePlannerCat'];

		// Head code zusammenführen
		$this->mergeHeadCodeArrays($o_element);
		
		return $output;
	
	}

	
	// Seite als Startseite eintragen
	protected function setIndexPage()
	{
	
		// Ehemalige Index-Seite auf 0
		$updateSQL1 = $this->DB->query("UPDATE " . DB_TABLE_PREFIX . parent::$tablePages . " 
											  SET `index_page` = 0    
											  WHERE `index_page` = 1 
											  ");
		
		// Neue Index-Seite auf 1
		$updateSQL2 = $this->DB->query("UPDATE " . DB_TABLE_PREFIX . parent::$tablePages . " 
											  SET `index_page` = 1    
											  WHERE `page_id` = '$this->editId'
											  ");
		
		if($updateSQL1 === true && 
		   $updateSQL2 === true
		)
			return true;
		
		return false;
	
	}


	// Inhaltstabelle updaten
	protected function updateContents($dbUpdateStr)
	{
	
		if(empty($dbUpdateStr))
			return false;
		
		// letztes Komma entfernen
		if(strrpos($dbUpdateStr, ",") === strlen($dbUpdateStr) -1)
			$dbUpdateStr = substr($dbUpdateStr, 0, -1) . " ";

		
		// db-Update der Inhaltstabelle
		$updateSQL = $this->DB->query("UPDATE `" . DB_TABLE_PREFIX . $this->tableContentsPrev . "` 
											SET $dbUpdateStr 
											WHERE `page_id` = '$this->editId'
											");
							
		#die(var_dump($dbUpdateStr));
		
		return $updateSQL;
		
	}


	// Fehlermeldung Inhaltselement nicht bekannt
	protected function getErrorUnknowElement($elem)
	{

		$error	=	'<p class="error">{s_error:unknowncon}: <strong>' . $elem . '</strong></p>' . PHP_EOL;
		
		return $error;
	
	}
	

	// getActionBox
	protected function getActionBox()
	{

		$redirectConPage	=  urlencode('admin?task=' . self::$task . '&edit_id=' . $this->editId . ($this->isTemplateArea ? '&area=' . parent::$tableContents : '') . ($this->elementStartNumber > self::MAX_CONELEM_NO ? '&start_con=' . $this->elementStartNumber : ''));
		
		$output 	=	'<li class="firstNew actionBox" data-menu="context" data-target="contextmenu-elem-' . $this->contentNumber . '">' . PHP_EOL .
						'<label for="markAllLB" class="markAll markBox">' . PHP_EOL .
						'<input type="checkbox" id="markAllLB" data-select="all" />' . PHP_EOL .
						'</label>' . PHP_EOL .
						'<label for="markAllLB" class="markAllLB">{s_label:mark}</label>' . PHP_EOL;
		
		// EditButton-panel
		$output .=		'<span class="editButtons-panel" data-id="contextmenu-elem-' . $this->contentNumber . '">' . PHP_EOL;
		
		// Falls ein Element copiert oder ausgeschnitten wurde
		if(isset($this->g_Session['copycon'])) {
		
			// Button paste
			$btnDefs	= array(	"type"		=> "button",
									"class"		=> 'pastecon editcon button-icon-only',
									"title"		=> '{s_title:paste}',
									"attr"		=> 'data-action="editcon" data-actiontype="pastecon" data-url="' . SYSTEM_HTTP_ROOT . '/access/editElements.php?page=admin&action=paste&con=' . ($this->elementStartNumber -1) . '&conmax=' . $this->contentNumber . '&connr=' . $this->usedFieldNames . '&id=' . $this->editId . '&area=' . parent::$tableContents . '&red=' . $redirectConPage . '" data-menuitem="true" data-contextmenuitem="true" data-menutitle="{s_title:paste}" data-id="item-id-' . $this->contentNumber . '"',
									"icon"		=> "paste"
								);
				
			$output .=	parent::getButton($btnDefs);
		}
		
		// Neues Element an erster Stelle
		// Button new
		$btnDefs	= array(	"type"		=> "button",
								"class"		=> 'newcon button-icon-only',
								"title"		=> '{s_title:new}',
								"attr"		=> 'data-menuitem="true" data-contextmenuitem="true" data-menutitle="{s_title:new}" data-id="item-id-' . $this->contentNumber . '"',
								"icon"		=> "new"
							);
			
		$output .=	parent::getButton($btnDefs);
		
		$output .=	'<div class="addCon">' . PHP_EOL .
					'<input type="hidden" class="ajaxaction" value="' . SYSTEM_HTTP_ROOT . '/access/editElements.php?page=admin&action=new&con=0&conmax=' . $this->contentNumber . '&connr=' . $this->usedFieldNames . '&id=' . $this->editId . '&area=' . parent::$tableContents . '&red=' . $redirectConPage . '" />' . PHP_EOL;			
						
		// Auswahl für neues Element
		$output .=	$this->listContentTypes();
		
		$output .=	'</div>' . PHP_EOL;
		
		// Aktion-Box Buttons
		// Button publish
		$btnDefs	= array(	"type"		=> "button",
								"class"		=> 'pubAll pubElements button-icon-only',
								"value"		=> "",
								"title"		=> '{s_title:pubmarked}',
								"attr"		=> 'data-menuitem="true" data-contextmenuitem="true" data-menutitle="{s_title:pubmarked}" data-id="item-id-' . $this->contentNumber . '"',
								"icon"		=> "show"
							);
			
		$output .=	parent::getButton($btnDefs);
		
		// Button unpublish
		$btnDefs	= array(	"type"		=> "button",
								"class"		=> 'pubAll pubElements unpublish button-icon-only',
								"value"		=> "",
								"title"		=> '{s_title:unpubmarked}',
								"attr"		=> 'data-menuitem="true" data-contextmenuitem="true" data-menutitle="{s_title:unpubmarked}" data-id="item-id-' . $this->contentNumber . '"',
								"icon"		=> "hide"
							);
			
		$output	.=	parent::getButton($btnDefs);

		// Button delete
		$btnDefs	= array(	"type"		=> "button",
								"class"		=> 'delAll delElements button-icon-only',
								"value"		=> "",
								"title"		=> '{s_title:delmarked}',
								"attr"		=> 'data-action="delmultiple" data-menuitem="true" data-contextmenuitem="true" data-menutitle="{s_title:delmarked}" data-id="item-id-' . $this->contentNumber . '"',
								"icon"		=> "delete"
							);
			
		$output .=	parent::getButton($btnDefs);

		$output .=		'<input type="hidden" class="multiAction" name="multiAction" value="' . SYSTEM_HTTP_ROOT . '/access/editElements.php?page=admin&conmax=' . $this->contentNumber . '&connr=' . $this->usedFieldNames . '&id=' . $this->editId . '&area=' . parent::$tableContents . '&red=' . $redirectConPage . '&array=1" />' . PHP_EOL;			


		$output .=		'</span>' . PHP_EOL .
						'</li>' . PHP_EOL;
		
		return $output;

	}
	

	// getFooterActionBox
	protected function getFooterActionBox()
	{
	
		$output =	'<ul class="adminSection"><li>' . PHP_EOL . 
					'<form>' . PHP_EOL . 
					'<label>{s_label:new}<span class="right">{s_header:listfetchcon}</span></label>' . PHP_EOL .
					'<br class="clearfloat" />' . PHP_EOL;

		
		// Auswahl für neues Element
		$output .=	'<div class="left">' . PHP_EOL .
					'<input type="hidden" class="ajaxaction" value="' . SYSTEM_HTTP_ROOT . '/access/editElements.php?page=admin&action=new&con=' . $this->usedFieldNames . '&conmax=' . $this->contentNumber . '&connr=' . $this->usedFieldNames . '&id=' . $this->editId . '&area=' . parent::$tableContents . '" />' . PHP_EOL;
								
		$output .=	$this->listContentTypes() . 
					'</div>' . PHP_EOL;
		
		$mediaListButtonDef		= array(	"class"	 	=> "fetch right",
											"type"		=> "fetch",
											"url"		=> SYSTEM_HTTP_ROOT . '/access/listPages.php?page=admin&type=fetchcon' . ($this->isTemplateArea ? '&area=' . parent::$tableContents : '') . '&edit_id=' .  $this->editId,
											"value"		=> "{s_label:duplicatecon}",
											"title"		=> "{s_title:fetchcon}",
											"icon"		=> "fetch"
										);
		
		$output .=	$this->getButtonMediaList($mediaListButtonDef);
		
		// Button zum übernehmen von Seiteninhalten
		$output .=	parent::getIcon("warning", "inline-icon right", 'title="{s_title:fetchcon}"');
		
		$mediaListButtonDef		= array(	"class"	 	=> "fetch right clear-right",
											"type"		=> "fetch",
											"url"		=> SYSTEM_HTTP_ROOT . '/access/listPages.php?page=admin&type=fetchcon&tpl=1' . ($this->isTemplateArea ? '&area=' . parent::$tableContents : '') . '&edit_id=' .  $this->editId,
											"value"		=> "{s_label:duplicatetpl}",
											"title"		=> "{s_title:fetchcon}",
											"icon"		=> "fetch"
										);
		
		$output .=	$this->getButtonMediaList($mediaListButtonDef);
		
		// Button zum übernehmen von Templateinhalten
		$output .=	parent::getIcon("warning", "inline-icon right", 'title="{s_title:fetchcon}"');
		
		// Falls Seite, Buttons zu Templatebereichen
		if(!$this->isTemplateArea) {
		
			$output .=	'<span class="buttonPanel-tplareas buttonPanel left clear-left">' .PHP_EOL;		
			$output .=	$this->getTplAreasPanel($this->pageTemplate);						
			$output .=	'</span>' . PHP_EOL;
		}
		
		$output .=	'<br class="clearfloat" />' . PHP_EOL . 
					'</form>' . PHP_EOL . 
					'</li>' . PHP_EOL .
					'</ul>' . PHP_EOL;

		return $output;
	
	}
	
	

	// getEditButtonsPanel
	protected function getEditButtonsPanel($styles, $i, $addCancelBtn, $addPasteBtn)
	{
		
		$redirectConPage	=  urlencode('admin?task=' . self::$task . '&edit_id=' . $this->editId . ($this->isTemplateArea ? '&area=' . parent::$tableContents : '') . ($this->elementStartNumber > self::MAX_CONELEM_NO ? '&start_con=' . $this->elementStartNumber : ''));

		// EditButtons panel
		$output =	'<span class="editButtons-panel" data-id="contextmenu-elem-' . $i . '">' . PHP_EOL;
			
		// Button paste/cancel
		if(count($this->pasteCon) > 0) {
		
			// Falls ausgeschnittenes/kopiertes Element
			if($addCancelBtn) {

				// Button cancel
				$btnDefs	= array(	"type"		=> "button",
										"class"		=> 'cancelpaste editcon button-icon-only',
										"title"		=> '{s_javascript:cancel}',
										"attr"		=> 'data-action="editcon" data-actiontype="cancelpaste" data-url="' . SYSTEM_HTTP_ROOT . '/access/editElements.php?page=admin&action=cancelpaste&red=' . $redirectConPage . '" data-menuitem="true" data-contextmenuitem="true" data-menutitle="{s_javascript:cancel}" data-id="item-id-' . $i . '"',
										"icon"		=> "cancel"
									);
					
				$output .=	parent::getButton($btnDefs);

			}
			
			// Paste-Button
			if($addPasteBtn) {

				// Button paste
				$btnDefs	= array(	"type"		=> "button",
										"class"		=> 'pastecon editcon button-icon-only',
										"title"		=> '{s_title:paste}',
										"attr"		=> 'data-action="editcon" data-actiontype="pastecon" data-url="' . SYSTEM_HTTP_ROOT . '/access/editElements.php?page=admin&action=paste&con=' . $i . '&conmax=' . $this->contentNumber . '&connr=' . $this->busyContentNumber . '&id=' . $this->editId . '&area=' . parent::$tableContents . '&red=' . $redirectConPage . '" data-menuitem="true" data-contextmenuitem="true" data-menutitle="{s_title:paste}" data-id="item-id-' . $i . '"',
										"icon"		=> "paste"
									);
					
				$output .=	parent::getButton($btnDefs);
			}
		}
		
		// Button new
		$btnDefs	= array(	"type"		=> "button",
								"class"		=> 'newcon button-icon-only',
								"title"		=> '{s_title:new}',
								"attr"		=> 'data-menuitem="true" data-contextmenuitem="true" data-menutitle="{s_title:new}" data-id="item-id-' . $i . '"',
								"icon"		=> "new"
							);
			
		$output .=	parent::getButton($btnDefs);
		
		$output .=	'<div class="addCon">' . PHP_EOL .
					'<input type="hidden" class="ajaxaction" value="' . SYSTEM_HTTP_ROOT . '/access/editElements.php?page=admin&action=new&con=' . $i . '&conmax=' . $this->contentNumber . '&connr=' . $this->busyContentNumber . '&id=' . $this->editId . '&area=' . parent::$tableContents . '&red=' . $redirectConPage . '" />' . PHP_EOL;
						
		// Auswahl für neues Element
		$output .=	$this->listContentTypes();
		
		$output .=	'</div>' . PHP_EOL;
							
		// Button copy
		$btnDefs	= array(	"type"		=> "button",
								"class"		=> 'copycon editcon button-icon-only',
								"title"		=> '{s_title:copy}',
								"attr"		=> 'data-action="editcon" data-actiontype="copycon" data-url="' . SYSTEM_HTTP_ROOT . '/access/editElements.php?page=admin&action=copy&con=' . $i . '&id=' . $this->editId . '&area=' . parent::$tableContents . '&red=' . $redirectConPage . '" data-menuitem="true" data-contextmenuitem="true" data-menutitle="{s_title:copy}" data-id="item-id-' . $i . '"',
								"icon"		=> "copy"
							);
			
		$output .=	parent::getButton($btnDefs);
		
		// Button cut
		$btnDefs	= array(	"type"		=> "button",
								"class"		=> 'cutcon editcon button-icon-only',
								"title"		=> '{s_title:cut}',
								"attr"		=> 'data-action="editcon" data-actiontype="cutcon" data-url="' . SYSTEM_HTTP_ROOT . '/access/editElements.php?page=admin&action=cut&con=' . $i . '&id=' . $this->editId . '&area=' . parent::$tableContents . '&red=' . $redirectConPage . '" data-menuitem="true" data-contextmenuitem="true" data-menutitle="{s_title:cut}" data-id="item-id-' . $i . '"',
								"icon"		=> "cut"
							);
			
		$output .=	parent::getButton($btnDefs);
		
		// Button sortdown
		if($i < $this->busyContentNumber) {
		
			$btnDefs	= array(	"type"		=> "button",
									"class"		=> 'sortcon button-icon-only',
									"title"		=> '{s_title:movedown}',
									"attr"		=> 'data-ajax="true" data-check="changes" data-url="' . SYSTEM_HTTP_ROOT . '/access/editElements.php?page=admin&action=down&con=' . $i . '&id=' . $this->editId . '&area=' . parent::$tableContents . '&red=' . $redirectConPage . '" data-menuitem="true" data-contextmenuitem="true" data-menutitle="{s_title:movedown}" data-id="item-id-' . $i . '"',
									"icon"		=> "sortdown"
								);
				
			$output .=	parent::getButton($btnDefs);
		}
		
		// Button sortup
		if($i > 1) { // Falls nicht das oberste Element, nach oben Button einfügen
		
			$btnDefs	= array(	"type"		=> "button",
									"class"		=> 'sortcon button-icon-only',
									"title"		=> '{s_title:moveup}',
									"attr"		=> 'data-ajax="true" data-check="changes" data-url="' . SYSTEM_HTTP_ROOT . '/access/editElements.php?page=admin&action=up&con=' . $i . '&id=' . $this->editId . '&area=' . parent::$tableContents . '&red=' . $redirectConPage . '" data-menuitem="true" data-contextmenuitem="true" data-menutitle="{s_title:moveup}" data-id="item-id-' . $i . '"',
									"icon"		=> "sortup"
								);
				
			$output .=	parent::getButton($btnDefs);
		}
		
		// Button Status
		$output .=	'<span class="switchIcons">' . PHP_EOL;
		
		// Button publish
		$btnDefs	= array(	"type"		=> "button",
								"class"		=> 'pubElement button-icon-only',
								"value"		=> "",
								"title"		=> '{s_title:hideelement}',
								"attr"		=> 'data-publish="1" data-url="' . SYSTEM_HTTP_ROOT . '/access/editElements.php?page=admin&action=publish&con=' . $i . '&id=' . $this->editId . '&area=' . parent::$tableContents . '&red=' . $redirectConPage . '&status=1"' . (!$this->elementStatus ? ' style="display:none;"' : '') . ' data-menuitem="true" data-contextmenuitem="true" data-menutitle="{s_title:hideelement}" data-id="item-id-' . $i . '"',
								"icon"		=> "show"
							);
			
		$output .=	parent::getButton($btnDefs);

		// Button unpublish
		$btnDefs	= array(	"type"		=> "button",
								"class"		=> 'pubElement button-icon-only',
								"value"		=> "",
								"title"		=> '{s_title:publishelement}',
								"attr"		=> 'data-publish="0" data-url="' . SYSTEM_HTTP_ROOT . '/access/editElements.php?page=admin&action=publish&con=' . $i . '&id=' . $this->editId . '&area=' . parent::$tableContents . '&red=' . $redirectConPage . '&status=0"' . ($this->elementStatus ? ' style="display:none;"' : '') . ' data-menuitem="true" data-contextmenuitem="true" data-menutitle="{s_title:publishelement}" data-id="item-id-' . $i . '"',
								"icon"		=> "hide"
							);
			
		$output .=	parent::getButton($btnDefs);
		
		$output .=	'<input type="hidden" name="elementStatus_' . $this->conPrefix . '" class="elementStatus" value="' . $this->elementStatus . '" />' . PHP_EOL .
								'</span>' . PHP_EOL;
		
		// Button delete
		$btnDefs	= array(	"type"		=> "button",
								"class"		=> 'delcon button-icon-only',
								"title"		=> '{s_title:delete}',
								"attr"		=> 'data-action="delete" data-url="' . SYSTEM_HTTP_ROOT . '/access/editElements.php?page=admin&action=del&con=' . $i . '&id=' . $this->editId . '&area=' . parent::$tableContents . '&red=' . $redirectConPage . '" data-menuitem="true" data-contextmenuitem="true" data-menutitle="{s_title:delete}" data-id="item-id-' . $i . '"',
								"icon"		=> "delete"
							);
			
		$output .=	parent::getButton($btnDefs);
	
		$output .=	'</span>' . PHP_EOL;
		
		return $output;
	
	}
	
	

	// getPreviewPanel
	protected function getPreviewPanel($styles, $i)
	{
	
		$output	= "";
		
		if($this->conElements[$i]['type'] == "html" 
		|| $this->conElements[$i]['type'] == "script"
		|| $this->conElements[$i]['type'] == "tabs"
		)
			$isHtmlPreview	= true;
		else
			$isHtmlPreview	= false;
		
		
		// Preview-Box		
		// Falls Bild
		if($this->conElements[$i]['type'] == "text") {
			$output	= strip_tags($this->conElements[$i]['con'], "<h1><h2><h3>");
		}
		elseif($this->conElements[$i]['type'] == "img") {
			$output	= '<script>
						head.ready(function(){
							$("body").on("mouseenter", "#content-' . $i . ' .conNr", function(){
								var cele = $(this).closest(".contentElement");
								var imgPrev = cele.find(".previewBox").clone();
								cele.find(".conPreview").prepend(imgPrev).children(".previewBox").not(":first").remove();
							});
						});
						</script>';
		}
	
		$output	=	'<div class="cc-element-preview-params">' . PHP_EOL .
					$output .
					'</div>' . PHP_EOL;
		
		// Element-Attribute/-Wrapper
		$output	.= '<div class="cc-element-preview-styles">' . PHP_EOL;
		$output	.= '<table class="adminTable">' . PHP_EOL;
		$output	.= '<tr><td><strong>{s_label:newsec}</strong></td><td>' . parent::getIcon($styles['sec'] ? "ok" : "cancel") . ($styles['sec'] && $styles['secid'] != "" ? ' #' . $styles['secid'] : '') . ($styles['sec'] && $styles['secclass'] != "" ? ' .' . $styles['secclass'] : '') . '</td></tr>';
		$output	.= '<tr><td><strong>{s_label:newctr}</strong></td><td>' . parent::getIcon($styles['ctr'] ? "ok" : "cancel") . ($styles['ctr'] && $styles['ctrid'] != "" ? ' #' . $styles['ctrid'] : '') . ($styles['ctr'] && $styles['ctrclass'] != "" ? ' .' . $styles['ctrclass'] : '') . '</td></tr>';
		$output	.= '<tr><td><strong>{s_label:newrow}</strong></td><td>' . parent::getIcon($styles['row'] ? "ok" : "cancel") . ($styles['row'] && $styles['rowid'] != "" ? ' #' . $styles['rowid'] : '') . ($styles['row'] && $styles['rowclass'] != "" ? ' .' . $styles['rowclass'] : '') . '</td></tr>';
		$output	.= '<tr><td><strong>{s_label:newdiv}</strong></td><td>' . parent::getIcon($styles['div'] ? "ok" : "cancel") . ($styles['div'] && $styles['divid'] != "" ? ' #' . $styles['divid'] : '') . ($styles['div'] && $styles['divclass'] != "" ? ' .' . $styles['divclass'] : '') . '</td></tr>';
		$output	.= '<tr><td><strong>{s_label:colno}</strong></td><td>' . parent::getIcon($styles['cols'] ? "ok" : "cancel") . ($styles['cols'] ? ' ' . $styles['cols'] : '') . ($styles['id'] != "" ? ' #' . $styles['id'] : '') . ($styles['class'] != "" ? ' .' . $styles['class'] : '') . '</td></tr>';
		$output	.= '</table>' . PHP_EOL;
		$output	.= '</div>' . PHP_EOL;
		
		return $output;
	
	}
	

	// getElementsSubmitButton
	public function getElementsSubmitButton($fe = false)
	{		
		
		if($fe) {
			$output		= '<div class="feButtonPanel button-panel">' . PHP_EOL;
			$closeTag	= '</div>' . PHP_EOL;
			$btnClass	= "feEditButton submit cc-editelement-save";
		}
		else {
			$output		= '<li class="submit change">' . PHP_EOL;
			$closeTag	= '</li>' . PHP_EOL;
			$btnClass	= "change";
		}
		
		// Button new
		$btnDefs	= array(	"type"		=> "submit",
								"name"		=> "submit",
								"id"		=> "submit2",
								"class"		=> $btnClass,
								"value"		=> "{s_button:savechanges}",
								"icon"		=> "ok"
							);
		
		$output	.=	parent::getButton($btnDefs);
		
		$output	.=	'<input class="saveElementDetails" name="submit" type="hidden" value="{s_button:savechanges}" />' . PHP_EOL .
					$closeTag;
		
		return $output;
	
	}
	

	// getTplEditPanel
	protected function getTplEditPanel($template, $editTplArea, $cssClass = "buttonPanel-tplareas buttonPanel popupBox")
	{		
		
		// Buttons zum wechseln des Templatebereichs
		$panel  =	'<span class="' . htmlspecialchars($cssClass) . '">' .PHP_EOL;		
		$panel .=	$this->getTplAreasPanel($template, $editTplArea);
		$panel .=	'</span>' . PHP_EOL;
		
		return $panel;
	}


	// getTplAreasPanel
	protected function getTplAreasPanel($template, $active = "")
	{
	
		$panel	= "";
		$tc		= 0;
		
		// Templatebreiche
		foreach(parent::$tablesTplContents as $conTab) {
		
			$tplArea	= parent::$areasTplContents[$tc];

			$href	= "";
			$attr	= "";
			$title	= '<strong>{s_conareas:' . $conTab . '}</strong><br />{s_label:edittpl}';
			$icon 	= 'area-' . $tplArea;
			$class	= "chooseTplArea button-icon-only item-link" . ($active == $tplArea ? ' active' : '');
			
			if($active != $tplArea) {
			
				$href	= ADMIN_HTTP_ROOT . '?task=tpl&type=edit&edit_id=' . $template . '&area=contents_' . $tplArea;
				$attr	= 'data-ajax="true"';
			
				// Button link
				$btnDefs	= array(	"href"		=> $href,
										"text"		=> "",
										"title"		=> $title,
										"class"		=> $class,
										"icon"		=> $icon
									);
				
				$panel	.=	parent::getButtonLink($btnDefs);
			}				
			else {
				
				// Button link
				$btnDefs	= array(	"type"		=> "button",
										"class"		=> $class,
										"title"		=> $title,
										"icon"		=> $icon
									);
				
				$panel	.=	parent::getButton($btnDefs);
			}
			
			$tc++;
		}
		return $panel;
	
	}


	// listContentHistory
	protected function listContentHistory($table, $pageID)
	{
	
		$output	= "";
		
		if($this->DB->tableExists($table) !== true)
			return "";
		
		
		$query	= $this->getContentHistory($table, $pageID);
		
		if(!is_array($query)
		|| count($query) == 0
		)
			return "";
		
		
		$output	.= '<div class="controlBar">' . PHP_EOL;
		$output	.= '<h4 class="controlBar-header actionBox">' . PHP_EOL;
		$output	.= '<span class="editButtons-panel">' . PHP_EOL;
		
		$clearHistoryUrl	= SYSTEM_HTTP_ROOT . '/access/editElements.php?page=admin&action=clearhistory&id=' . $this->editId . '&area=' . parent::$tableContents . '&version=all';
			
		// Button clear
		$btnDefs	= array(	"type"		=> "button",
								"class"		=> 'clearHistory button-icon-only button-small right',
								"text"		=> '',
								"title"		=> '{s_button:delete}',
								"attr"		=> 'data-confirm="{s_promt:confdelhistory}" data-url="' . $clearHistoryUrl . '" data-ajax="true"',
								"icon"		=> "delete"
							);
			
		$output .= parent::getButton($btnDefs) . PHP_EOL;
		
		$output	.= '</span>' . PHP_EOL;
		
		$output	.= parent::getIcon("calendar", "inline-icon") . 'History</h4>' . PHP_EOL;

		$output	.= '<ul class="editList clear">' . PHP_EOL;
		
		$i	= 0;
		
		foreach($query as $version) {
		
			$vID			= $version['id'];
			$actionUrl		= SYSTEM_HTTP_ROOT . '/access/editElements.php?page=admin&action=restore&id=' . $this->editId . '&area=' . parent::$tableContents . '&version=' . $vID;
			$vDate			= Modules::getLocalDateString($version['v_timestamp'], $this->editLang, true);
			$vDateF			= Modules::getFormattedDateString(time($version['v_timestamp']), $this->editLang, true);
			$vElemCnt 		= 0;
			$aIcon	 		= '<span class="iconBox">' . parent::getIcon($this->isTemplateArea ? 'area-' . $this->editTplArea : 'page') . '</span>';
			$aStr			= $this->isTemplateArea ? $this->editId : $this->parentsAliasStr . $this->pageDef['title'] . ' (#' . $this->editId . ')';
			$areaStr 		= $aIcon . '{s_conareas:contents_' . $this->editTplArea . '} &raquo; ' . $aStr . '<br />';
			$confMes		= sprintf(ContentsEngine::replaceStaText("{s_promt:confreshistory}"), htmlspecialchars($areaStr) . $vDateF);
			
			foreach($version as $key => $val) {
				if(strpos($key, "type-") !== false
				&& $val != ""
				)
					$vElemCnt++;
			}
			
			// Falls mehr als fünf Einträge, Rest ausblenden und Button zum Einblenden einbinden
			if($i == 5) {
				
				// Button edit
				$btnDefs	= array(	"type"		=> "button",
										"class"		=> "toggleHistoryItems",
										"value"		=> "{s_common:showall}",
										"title"		=> "{s_common:showall}",
										"text"		=> '{s_text:additional} ' . (count($query) -5),
										"attr"		=> 'data-reveal=".listItem"',
										"icon"		=> "toggle"
									);
				
				$output	.=	parent::getButton($btnDefs);
				
			}
					
			$output	.=	'<li class="listItem' . ($i%2 ? ' alternate' : '') . '"' . ($i >= 5 ? ' style="display:none;"' : '') . '>' . PHP_EOL;
			$output	.=	parent::getIcon("stopwatch", "inline-icon");
			$output	.=	'<span class="cc-table-cell" title="version: ' . $vDate . '<br />{s_header:contents}: ' . $vElemCnt . '">' . PHP_EOL;
			$output	.=	$vDate;
			
			// Back to list
			$output	.=	'<span class="editButtons-panel">' . PHP_EOL;
			
			// Button restore
			$btnDefs	= array(	"type"		=> "button",
									"class"		=> 'restoreHistory button-icon-only',
									"text"		=> '',
									"title"		=> 'version: ' . $vDate . '<br />{s_button:redo}',
									"attr"		=> 'data-confirm="' . $confMes . '" data-url="' . $actionUrl . '" data-ajax="true"',
									"icon"		=> "fetch"
								);
				
			$output .=	parent::getButton($btnDefs) . PHP_EOL;
		
			$output .=	'</span>' . PHP_EOL;
			$output .=	'</span>' . PHP_EOL;
			$output	.=	'</li>' . PHP_EOL;
			
			$i++;
		}
		
		$output	.= '</ul>' . PHP_EOL;
		$output	.= '</div>' . PHP_EOL;
		
		return $output;
	
	}


	// listContentHistory
	protected function getContentHistory($table, $pageID)
	{
	
		// Datenbanksuche nach history
		$query = $this->DB->query( "SELECT * 
										FROM `" . $table . "` 
									WHERE `page_id` = '" . $pageID . "' 
									ORDER BY `v_timestamp` DESC
									");
		
		#var_dump($query);

		return $query;		
		
	}


	// getBackButtons
	public function getBackButtons($isTemplateArea = false)
	{
	
		$output		=	'<p>&nbsp;</p>' . PHP_EOL .
						'<p>&nbsp;</p>' . PHP_EOL .
						'<div class="adminArea">' . PHP_EOL .
						'<ul>' . PHP_EOL .
						'<li class="submit back">' . PHP_EOL;
		
		
		$output		.=	'<form action="' . ADMIN_HTTP_ROOT . '?task=' . self::$task . '" id="navform1" method="post">' . PHP_EOL; // Formular mit Buttons zum Zurückgehen
		
		// Falls ein Template bearbeitet wird
		if($isTemplateArea) {
			
			// Button zurück zu Templates
			$btnDefs	= array(	"type"		=> "submit",
									"name"		=> "back",
									"id"		=> "submit2",
									"class"		=> "template left",
									"value"		=> "{s_button:admintpl}",
									"icon"		=> "theme"
								);
			
			$output	.=	parent::getButton($btnDefs);
			
			$output	.=	'<input type="hidden" name="edit_tpl" value="' . $this->editId . '" />' . PHP_EOL;
		}

		else {
		
			// Button zurück zur Listenansicht (edit)
			$btnDefs	= array(	"type"		=> "submit",
									"name"		=> "back",
									"id"		=> "back",
									"class"		=> "left",
									"value"		=> "{s_button:adminedit}",
									"icon"		=> "backtolist"
								);
			
			$output	.=	parent::getButton($btnDefs);
			
			$output		.=	'</form>' . PHP_EOL .
							'<form action="' . ADMIN_HTTP_ROOT . '?task=tpl" method="post" data-getcontent="fullpage">' . PHP_EOL;
			
			// Button template
			$btnDefs	= array(	"type"		=> "submit",
									"name"		=> "edit_tpl",
									"class"		=> "template right",
									"value"		=> "{s_nav:admintpl}",
									"icon"		=> "theme"
								);
			
			$output	.=	parent::getButton($btnDefs);
			
			$output		.=	'<input type="hidden" name="edit_tpl" value="' . $this->pageTemplate . '" />' . PHP_EOL .
							'<input type="hidden" name="template" value="' . $this->pageTemplate . '" class="clearfloat" />' . PHP_EOL;
		}

		$output		.=	'</form>' . PHP_EOL . 
						'<br class="clearfloat" />' . PHP_EOL . 
						'</li>' . PHP_EOL;
						
		$output		.=	'<li class="submit back">' . PHP_EOL;
		
		// Button backtomain
		$output		.=	$this->getButtonLinkBacktomain();
		
		$output		.=	'<br class="clearfloat" />' . PHP_EOL .
						'</li></ul>' . PHP_EOL . 
						'<p>&nbsp;</p>' . PHP_EOL . 
						'</div>' . PHP_EOL;
						
		return $output;

	}

	
	// getContentsArea
	protected function getContentsArea()
	{

		if(!empty($GLOBALS['_POST']['edit_area']))
			parent::$tableContents		= $GLOBALS['_POST']['edit_area'];
		elseif(!empty($GLOBALS['_GET']['area']))
			parent::$tableContents		= $GLOBALS['_GET']['area'];
		elseif(!empty($GLOBALS['_GET']['edit_area']))
			parent::$tableContents		= $GLOBALS['_GET']['edit_area'];
	
	}
	
	
	// sortPageUp
	public function sortPageUp($sortId)
	{

		if(!is_numeric($sortId))
			return false;
		
		require_once SYSTEM_DOC_ROOT."/inc/adminclasses/class.SortPages.php"; // SortPages-Klasse
		
		$o_sortPages	= new SortPages($this->DB, parent::$tablePages);
		
		return $o_sortPages->sortPageUp($sortId);
	
	}
	
	
	// sortPageDown
	public function sortPageDown($sortId)
	{

		if(!is_numeric($sortId))
			return false;
		
		require_once SYSTEM_DOC_ROOT."/inc/adminclasses/class.SortPages.php"; // SortPages-Klasse
		
		$o_sortPages	= new SortPages($this->DB, parent::$tablePages);
		
		return $o_sortPages->sortPageDown($sortId);

	}
	
	
	// getEditTask
	public function getEditTask()
	{
	
		// Hauptseiteninhalte bearbeiten
		if(isset($GLOBALS['_GET']['edit_id']) 
		&& $GLOBALS['_GET']['edit_id'] != "" 
		&& is_numeric($GLOBALS['_GET']['edit_id'])
		) { // edit_id auslesen, falls über get gesetzt
			
			$this->editId	= $GLOBALS['_GET']['edit_id'];
			// Seitendetails (step 2)
			$this->editTask	= "editentry";			
			return $this->editTask;
		}

		if(isset($GLOBALS['_GET']['id']) 
		&& $GLOBALS['_GET']['id'] != "" 
		&& is_numeric($GLOBALS['_GET']['id'])
		) { // edit_id auslesen, falls über get gesetzt
			
			$this->editId = $GLOBALS['_GET']['id'];
			// Seitendetails (step 2)
			$this->editTask	= "editentry";			
			return $this->editTask;
		}

		if(isset($GLOBALS['_POST']['edit_id']) 
		&& $GLOBALS['_POST']['edit_id'] != "" 
		&& is_numeric($GLOBALS['_POST']['edit_id'])
		) { // edit_id auslesen, falls über post gesetzt
			
			$this->editId = $GLOBALS['_POST']['edit_id'];
			// Seitendetails (step 2)
			$this->editTask	= "editentry";			
			return $this->editTask;
		}
					
		if(isset($GLOBALS['_POST']['back'])) { // Falls der zurück-Button geklickt wurde Session-id löschen
			
			$this->editId = "";
			$this->unsetSessionKey('edit_id');
			// Seitenliste (step 1)
			$this->editTask	= "list";			
			return $this->editTask;
		}
		
		if(isset($GLOBALS['_POST']['del_id']) 
		&& $GLOBALS['_POST']['del_id'] != ""
		) { // del_id auslesen
			
			$this->editId = $GLOBALS['_POST']['del_id'];
			// Seiten löschen (edit_del)
			$this->editTask	= "delete";			
			return $this->editTask;
		}

		if(isset($GLOBALS['_POST']['delete']) 
		&& $GLOBALS['_POST']['delete'] != ""
		) { // del_id auslesen
			
			$this->editId = $GLOBALS['_POST']['delete'];
			// Seiten löschen (edit_del)
			$this->editTask	= "delete";			
			return $this->editTask;
		}

		if(isset($this->g_Session['edit_id']) 
		&& $this->g_Session['edit_id'] != "" 
		&& !isset($GLOBALS['_POST']['back'])
		) { // edit_id auslesen, falls in Session gesetzt und nicht der zurück-Button geklickt wurde
			
			$this->editId = $this->g_Session['edit_id'];
			$this->unsetSessionKey('edit_id');
			// Seitendetails (step 2)
			$this->editTask	= "editentry";			
			return $this->editTask;
		}
	
		// Falls Tpl
		if(!empty($GLOBALS['_GET']['edit_tpl'])
		|| !empty($GLOBALS['_GET']['area'])
		|| !empty($GLOBALS['_POST']['edit_tpl'])
		|| !empty($GLOBALS['_POST']['area'])
		) { // edit_id auslesen, falls in Session gesetzt und nicht der zurück-Button geklickt wurde
			// Seitendetails (step 2)
			$this->isTemplateArea	= true;
			$this->editTask	= "editentry";			
			return $this->editTask;
		}
		
		

		// Sort
		// up
		if(!empty($GLOBALS['_POST']['sortup_id'])) {
			
			$this->editTask	= "sortup";			
			return $this->editTask;
		}

		// down
		if(!empty($GLOBALS['_POST']['sortdown_id'])) {
			
			$this->editTask	= "sortdown";			
			return $this->editTask;
		}
		
		$this->editTask	= "list";			
		return $this->editTask;
	
	}
	
	
	// evalEditRequest
	public function evalEditRequest()
	{
	
		if(!empty($GLOBALS['_GET']['start_con'])
		&& $GLOBALS['_GET']['start_con'] > self::MAX_CONELEM_NO
		) {
			$this->elementStartNumber	=  $GLOBALS['_GET']['start_con'];
		}
	
	}
	
	
	// Delete pages
	// getDeletePagesForm
	public function getDeletePagesForm()
	{

		$this->adminContent	.=	'<div class="adminArea deletePages">' . PHP_EOL;
		
		
		$this->formAction	= ADMIN_HTTP_ROOT . '?task=edit';
		
		
		// Falls mehrere markierte Seiten gelöscht werden sollen
		if (isset($GLOBALS['_POST']['del_id']) && $GLOBALS['_POST']['del_id'] == "array" && 
			isset($GLOBALS['_POST']['pageIDs'])) {
		
			$this->delIDsArr	= $GLOBALS['_POST']['pageIDs'];			
		}
		else
			$this->delIDsArr[]	= $this->editId;

			
		$i = 1;
		
		
		// Zulöschenden Seiten suchen
		foreach($this->delIDsArr as $key => $delID) {
		
			
			$this->delId		= $delID;	
			$this->noAccess[$i]	= false;

		
			// Datenbanksuche nach zu löschender Seite, falls nicht array
			if($this->delId != "array")
				$this->query = $this->delQuery($this->delId);
			
			
			// Falls die zu löschende Seite gefunden wurde
			if(count($this->query) > 0) {
				
				$this->pageUserGroup	= explode(",", $this->query[0]['group']);
				$this->pageEditGroups	= array_filter(explode(",", $this->query[0]['group_edit']));
				$this->delTitles[$i]	= '<strong>' . $this->query[0]['title_' . $this->lang] . '</strong> [#' . $this->delId . ']';
				
				if($this->delId < -1000) {
					$this->errorTitles[$i] = $this->delTitles[$i];
					unset($this->delTitles[$i]);
					$this->noAccess[$i] = true;
					$i++;
					continue;
				}
					
				
				// Access checken
				if($this->getPermission($i)) {				
				
					// Locking checken
					$this->noAccess[$i]	= $this->checkLocking($this->delId, parent::$tableContents, $this->g_Session['username'], '<strong>' . $this->delTitles[$i] . '</strong><br />{s_error:lockededitentry}');
				}
				
				// Seitentitel
				if($this->noAccess[$i]) {
					unset($this->delIDsArr[$key]);					
					unset($this->delTitles[$i]);
					$i++;
					continue;
				}
				
				
				// Falls das Löschen der Seite(n) bestätigt wurde
				if (isset($GLOBALS['_POST']['delete']) && $GLOBALS['_POST']['delete'] != "" && 
					$this->noAccess[$i] == false
				)
				
					$this->deletePages($i); // Seite und, falls vorhanden, Unterseiten löschen
					$this->deleteCachePages(array($this->delId));
					$this->deleteCachePages($this->childPageIDs);
			
			
			} // Ende falls zu löschender Eintrag existiert
			else
				$this->pagesNotFound[] = $this->delId;

			$i++;
		
		} // Ende foreach

		
		// Löschenbestätigenseite
		if(in_array(false, $this->noAccess)) {

			// Falls erster Aufruf der Seite, Löschen bestätigen
			if(count($this->deleted) == 0 && count($this->updated) == 0) {
				
				// Falls keine Seiten zum Löschen gefunden
				if(count($this->delIDsArr) == 0 || count($this->delTitles) == 0) {
					$this->adminContent .=	'<p class="notice error">{s_error:delpage}</p>' . PHP_EOL;
				}
				
				else {
				
					$this->adminContent .=	'<p class="notice error">{s_text:delconf2}</p>' . PHP_EOL;
					
					if(count($this->errorTitles) > 0)
						$this->adminContent .=	'<p class="notice error">{s_notice:nodelpage}<br /><br /><strong>' . implode("<br />", $this->errorTitles) . '</strong></p>' . PHP_EOL;
					
					$this->adminContent .=	'<ul class="framedItems">' . PHP_EOL . 
											'<li>' . PHP_EOL . 
											'<span class="delbox">' . PHP_EOL . 
											'{s_text:delconf}' .
											parent::getIcon("info", "info inline-icon", 'title="{s_title:confdel}"') .
											'<br /><br />' . implode("<br />", $this->delTitles) .
											'</span>' . PHP_EOL . 
											'</li>' . PHP_EOL .
											'<li class="submit change">' . PHP_EOL . 
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
											'</form>' . PHP_EOL . 
											'<form action="' . $this->formAction . '" id="adminfm" method="post">' . PHP_EOL;
			
					// Button delete-ok
					$btnDefs	= array(	"type"		=> "submit",
											"name"		=> "submit",
											"class"		=> "delete",
											"value"		=> "{s_button:delete}",
											"icon"		=> "delete"
										);
					
					$this->adminContent	.=	parent::getButton($btnDefs);
					
					$this->adminContent	.=	'<input type="hidden" name="step" id="step" value="del" /> ' . PHP_EOL . 
											'<input type="hidden" name="delete" value="' . $this->editId . '" />' . PHP_EOL . 
											'<input type="hidden" name="del_id" value="array" />' . PHP_EOL;
					
					foreach($this->delIDsArr as $delId) {
						$this->adminContent .=	'<input type="hidden" name="pageIDs[]" value="' . $delId . '" />' . PHP_EOL;
					}
											
					$this->adminContent .=	'<input type="hidden" name="token" value="' . parent::$token . '" />' . PHP_EOL . 
											'</form>' . PHP_EOL . 
											'</li>' . PHP_EOL . 
											'</ul>' . PHP_EOL;
				}
			}
			else {
			
				// Seiten wurden gelöscht
				if(in_array(true, $this->deleted) && in_array(true, $this->updated)) {
				
					// Ggf. EditID aus Session löschen
					$this->unsetSessionKey('edit_id');

					$this->adminContent .=	'<p class="notice success">{s_notice:delpage}</p>' . PHP_EOL .
											'<ul class="framedItems">' . PHP_EOL . 
											'<li>' . PHP_EOL . 
											'<span class="delbox"><strong>' . implode("<br />", $this->successTitles) . '</strong></span>' . PHP_EOL . 
											'</li>' . PHP_EOL .
											'</ul>' . PHP_EOL;
				
				}
				// Seiten wurden nicht gelöscht
				if(in_array(false, $this->deleted) || in_array(false, $this->updated)) {

					$this->adminContent .=	'<p class="notice error">{s_error:delpageno}</p>' . PHP_EOL .
											'<ul class="framedItems">' . PHP_EOL . 
											'<li>' . PHP_EOL . 
											'<span class="delbox"><strong>' . implode("<br />", $this->errorTitles) . '</strong></span>' . PHP_EOL . 
											'</li>' . PHP_EOL .
											'</ul>' . PHP_EOL;
				
				}
			}
		}
		
		$this->adminContent .=		'</div>' . PHP_EOL; // div.deletePages schließen

		
		// Zurückbuttons
		$this->adminContent .=	$this->getBackButtons($this->isTemplateArea);

	}

	
	// delQuery
	private function delQuery($delId)
	{

		// Datenbanksuche nach zu löschender Seite
		$query = $this->DB->query( "SELECT * 
										FROM `" . DB_TABLE_PREFIX . parent::$tablePages . "` 
									WHERE `page_id` = " . $delId . " 
									");
		
		#var_dump($this->query);

		return $query;
	}

	
	// getChildPageIDs
	private function getChildPageIDs($menu_item, $lft, $rgt)
	{

		// Datenbanksuche nach zu löschender Seite
		$query = $this->DB->query( "SELECT `page_id` 
										FROM `" . DB_TABLE_PREFIX . parent::$tablePages . "` 
									WHERE lft > " . $lft . " AND lft < " . $rgt . " 
										AND `menu_item` = $menu_item
										AND protected != 1
									");
		
		#var_dump($this->query);

		return $query;
	}
	
	
	// getPermission
	private function getPermission($i)
	{

		// Falls die Seite nur für Admin zugänglich ist, Noaccess-Seite anzeigen
		if(!$this->getWritePermission($this->pageEditGroups)) {
				
			$this->noAccess[$i] 	= true;
			$this->adminContent .=	'<p class="notice error">{s_error:noaccess}</p>' . PHP_EOL;
			
			return false;
		}
		return true;
	}
	
	
	// deletePages
	private function deletePages($i)
	{

		// Seite(n) löschen
		$page_id		= $this->query[0]['page_id'];
		$menu_item		= $this->query[0]['menu_item'];
		$lft			= $this->query[0]['lft'];
		$rgt			= $this->query[0]['rgt'];
		$deleteSQL1 	= false;
		$deleteSQL2 	= false;
		$deleteSQL3 	= false;
		$updateSQL1		= false;
		$updateSQL2		= false;

		
		// db-Tabelle sperren
		$lock = $this->DB->query("LOCK TABLES	`" . DB_TABLE_PREFIX . parent::$tablePages . "`,
												`" . DB_TABLE_PREFIX . parent::$tableContents . "`,
												`" . DB_TABLE_PREFIX . parent::$tableContents . "_preview`,
												`" . DB_TABLE_PREFIX . "search`
								");


		// Transaktion starten
		$this->DB->query("SET AUTOCOMMIT=0");
		$this->DB->query("START TRANSACTION");
		
		
		// Kindseiten-IDs holen
		$this->querychildPages	= $this->getChildPageIDs($menu_item, $lft, $rgt);
		
		
		// Löschen der Seite
		$deleteSQL1 = $this->DB->query("DELETE 
												FROM `" . DB_TABLE_PREFIX . parent::$tablePages . "` 
												WHERE lft BETWEEN " . $lft . " AND " . $rgt . " 
												AND `menu_item` = $menu_item
												AND protected != 1
												");
			
		// Update vorhandener Einträge
		$updateSQL1 = $this->DB->query("UPDATE `" . DB_TABLE_PREFIX . parent::$tablePages . "` 
											SET lft=lft-ROUND(($rgt-$lft+1)) 
											WHERE lft>$rgt
											AND menu_item = $menu_item
											");


		// Update vorhandener Einträge
		$updateSQL2 = $this->DB->query("UPDATE `" . DB_TABLE_PREFIX . parent::$tablePages . "` 
											SET rgt=rgt-ROUND(($rgt-$lft+1))
											WHERE rgt>$rgt
											AND menu_item = $menu_item
											");

	

		// Transaktion ausführen/rückgängig
		if(	$deleteSQL1 === true && 
			$updateSQL1 === true && 
			$updateSQL2 === true
		) {
		
			$this->DB->query("COMMIT"); // Commit
		
			$this->deleted[$i]	= true;
			$this->updated[$i]	= true;
			$delSqlStr1			= "";
			$delSqlStr2			= "";
			$delSqlStr3			= "";
			
			// Seite und ggf. Kindseiten aus contents löschen
			if(count($this->querychildPages) > 0) {
			
				$this->childPageIDs	= array();
				
				foreach($this->querychildPages as $childPage) {
				
					$this->childPageIDs[] = $childPage['page_id'];
					$delSqlStr1	.= " OR `" . DB_TABLE_PREFIX . parent::$tableContents . "`.`page_id` = " . $childPage['page_id'];
					$delSqlStr2	.= " OR `" . DB_TABLE_PREFIX . parent::$tableContents . "_preview`.`page_id` = " . $childPage['page_id'];
					$delSqlStr3	.= " OR `page_id` = " . $childPage['page_id'];
				}
			}
			
			$deleteSQL2 = $this->DB->query("DELETE 
													FROM " . DB_TABLE_PREFIX . parent::$tableContents . ",
														`" . DB_TABLE_PREFIX . parent::$tableContents . "_preview` 
													USING " . DB_TABLE_PREFIX . parent::$tableContents . ",
														 `" . DB_TABLE_PREFIX . parent::$tableContents . "_preview` 
													WHERE (`" . DB_TABLE_PREFIX . parent::$tableContents . "`.`page_id` = " . $this->delId . $delSqlStr1 . ") 
													AND (`" . DB_TABLE_PREFIX . parent::$tableContents . "_preview`.`page_id` = " . $this->delId . $delSqlStr2 . ")
													");
		
		
			$deleteSQL3 = $this->DB->query("DELETE 
													FROM `" . DB_TABLE_PREFIX . "search` 
													WHERE `page_id` = " . $this->delId . $delSqlStr3 . "
													");
		
		
		}
		else {
			$this->DB->query("ROLLBACK"); // Rollback
			$dbError = '<script type="text/javascript">jAlert(ln.dberror, ln.alerttitle);</script>';
		}


		// Seitentitel speichern
		if(	$deleteSQL1 === true && 
			$deleteSQL2 === true && 
			$deleteSQL3 === true && 
			$updateSQL1 === true && 
			$updateSQL2 === true
		)
			$this->successTitles[]	= $this->delTitles[$i]; // kein Fehler
		else
			$this->errorTitles[] 	= $this->delTitles[$i]; // Fehler
		
		
		// db-Sperre aufheben
		$unLock = $this->DB->query("UNLOCK TABLES");
		
	}
	
	
	// deleteCachePages
	private function deleteCachePages($pageIDs)
	{

		if(!is_array($pageIDs) || count($pageIDs) == 0)
			return false;
		
		foreach($pageIDs as $pageID) {
		
			// Seite(n) aus Cache löschen
			foreach($this->installedLangs as $lang) {
				$cacheFile	= HTML_CACHE_DIR . $lang . '/' . $pageID . '.html';
				if(file_exists($cacheFile)) {
					unlink($cacheFile);
				}
			}
		}	
	}


	/**
	 * getPreviewNavItem
	 *
	 * @param	string			$pageID		page-ID
	 * @param	string			$lang		Sprache
	 * @access	protected
     * @return  array | boolean
	 */
	protected function getPreviewNavItem($pageID, $lang)
	{
	
		// Vorschaulink für Seite im Kopfbereich einbinden
		// Button preview
		$btnDefs	= array(	"href"		=> PROJECT_HTTP_ROOT . '/' . HTML::getLinkPath($pageID, "editLang") . '?lang='.$lang,
								"class"		=> 'pagePreview gotoPreviewPage button-icon-only button-small',
								"text"		=> "",
								"title"		=> '{s_title:pagepreview1a} &quot;' . $this->pageDef['title'] . '&quot; {s_title:pagepreview1b}',
								"icon"		=> "preview"
							);
			
		$output		=	parent::getButtonLink($btnDefs);

		return $output;

	}

	
	// getEditListRightBarContents
	private function getEditListRightBarContents()
	{
	
		// Panel for rightbar
		$output	= "";
		
		// Back to list
		$output .=	'<div class="controlBar">' . PHP_EOL;
		
		// Button new page
		$btnDefs	= array(	"href"		=> ADMIN_HTTP_ROOT . '?task=new',
								"class"		=> "{t_class:btnpri} {t_class:btnblock} {t_class:marginbs}",
								"text"		=> "{s_button:adminnew}",
								"attr"		=> 'data-ajax="true"',
								"icon"		=> "new"
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
		
		
		if(!$this->DB->tableExists(DB_TABLE_PREFIX . 'contents_%_history'))
			return $output;
		
		
		// Clear history
		$output .=	'<div class="controlBar">' . PHP_EOL;
		
		$clearHistoryUrl	= SYSTEM_HTTP_ROOT . '/access/editElements.php?page=admin&action=clearhistory&area=all&red=' . urlencode('admin?task=edit');
			
		// Button clear history
		$btnDefs	= array(	"type"		=> "button",
								"class"		=> 'clearHistory {t_class:btnpri} {t_class:btnblock}',
								"text"		=> 'History {s_button:delete}' . parent::getIcon('delete'),
								"title"		=> 'History {s_button:delete}: {s_common:all}',
								"attr"		=> 'data-confirm="{s_promt:confclearhistory}" data-url="' . $clearHistoryUrl . '" data-ajax="true"',
								"icon"		=> "calendar"
							);
			
		$output .= parent::getButton($btnDefs) . PHP_EOL;
		
		$output .=	'</div>' . PHP_EOL;
		
		return $output;
		
	}
	
	
	// getSectionImage
	public function getSectionImage($img, $conPrefix)
	{
	
		$output			= "";
		$imgSrc			= "";
		$img_Src		= "";
		$imgPath		= CC_IMAGE_FOLDER . '/';
		$thumbPath		= $imgPath . 'thumbs/';
		$imgFile		= "";
		$errorBG		= "";


		// Pfad zur Bilddatei
		// Falls files-Ordner, den Pfad ermitteln
		if(strpos($img, "/") !== false) {
			$filesImg	= explode("/", $img);
			$img		= array_pop($filesImg);					
			$imgPath	= CC_FILES_FOLDER . "/" . implode("/", $filesImg) . "/";
			$thumbPath	= $imgPath . 'thumbs/';
			$imgFile	= $img;
		}
		elseif(strpos($img, "{#img_root}") === 0) {
			$imgPath		= IMAGE_DIR;
			$thumbPath	= $imgPath;
			$imgFile	= str_replace("{#img_root}", "", $img);					
		}
		else
			$imgFile	= $img;
			

		$imgSrc			= PROJECT_HTTP_ROOT . '/' . $thumbPath . $imgFile;
		$img_Src		= PROJECT_HTTP_ROOT . '/' . $imgPath . $imgFile;
		
		// Falls noch kein Bild ausgewählt
		if($img == "") {
			$imgSrc		= SYSTEM_IMAGE_DIR . '/noimage.png';
			$img_Src	= $imgSrc;
		}

		// Falls Bild nicht vorhanden
		elseif(!file_exists(PROJECT_DOC_ROOT . '/' . $imgPath . $imgFile)){
			$imgSrc		= SYSTEM_IMAGE_DIR . '/noimage.png';
			$img_Src	= $imgSrc;
			
			$this->wrongInput[] = $conPrefix;
			$errorBG	= "{s_javascript:confirmreplace1}" . $img . "&quot; {s_text:notexist}.";
		}
	
		// bg image
		$output	.=	'<div class="fileSelBox clearfix">' . PHP_EOL;
		$output .=	'<div class="existingFileBox">' . PHP_EOL .
					'<label>{s_label:bgimg}</label>' . PHP_EOL;
					
		if(!empty($errorBG))
			$output	.=	'<span class="notice error">' . $errorBG . '</span>' . PHP_EOL;
		
		
		// Btn Reset bg
		$mediaListButtonDef		= array(	"type"		=> "button",
											"class"		=> "button-icon-only button-small right",
											"text"		=> "",
											"value"		=> "{s_plugin-daslider:removebg}",
											"title"		=> "{s_plugin-daslider:removebg}",
											"attr"		=> 'onclick="$(this).closest(\'.existingFileBox\').find(\'.existingFile\').val(\'\');$(this).closest(\'.existingFileBox\').find(\'.preview\').attr(\'src\',\'' . SYSTEM_IMAGE_DIR . '/noimage.png\').attr(\'data-img-src\',\'' . SYSTEM_IMAGE_DIR . '/noimage.png\'); $(this).parent(\'.elementsFileName\').html($(this).parent(\'.elementsFileName\').children(\'button\'));"',
											"icon"		=> "delete"
										);
		
		$btnResetImg 	=	$this->getButton($mediaListButtonDef);


		$output 	.=	'<label class="elementsFileName">' . (!$img ? "{s_label:choosefile}" : htmlspecialchars($img) . $btnResetImg) . '</label>' . PHP_EOL;
		
		$output .=	'<div class="previewBox img">' . PHP_EOL .
					'<img src="' . $imgSrc . '" data-img-src="' . $img_Src . '" class="preview" alt="' . htmlspecialchars($img) . '" title="' . htmlspecialchars($img) . '" />' . PHP_EOL . 
					'</div>' . PHP_EOL;
		
		// Images MediaList-Button
		$mediaListButtonDef		= array(	"class"	 	=> "images",
											"type"		=> "images",
											"url"		=> SYSTEM_HTTP_ROOT . "/access/listMedia.php?page=admin&type=images",
											"path"		=> PROJECT_HTTP_ROOT . '/' . $imgPath . 'thumbs/',
											"value"		=> "{s_button:imgfolder}",
											"icon"		=> "images"
										);
		
		$output 	.=	$this->getButtonMediaList($mediaListButtonDef);
					
		$output 	.=	'<input type="text" name="' . $conPrefix . '_secbgimg" class="existingFile" value="' . htmlspecialchars($img) . '" readonly="readonly" />' . PHP_EOL .
						'</div>' . PHP_EOL .
						'</div>' . PHP_EOL;
		
		return $output;
	
	}
	
	
	// getScriptTags
	public function getScriptTags()
	{
	
		$this->scriptTags	= array_filter(array_unique($this->scriptTags));
		$scriptTags			= "";
		
		foreach($this->scriptTags as $script) {
			$scriptTags		.= $script;
		}
		return $scriptTags;
	
	}
	
	
	// getScriptCode
	public function getScriptCode()
	{
	
		$output	=	'<script>' . PHP_EOL;
		
		$output	.=	'head.ready("ui", function(){' .
						'$(document).ready(function(){' .
							'$( ".numSpinner" ).spinner({min:-9999, max:9999});' .
						'});' .
					'});' . PHP_EOL;		
		
		// Toggles
		$output	.=	'head.ready("jquery", function(){' . PHP_EOL .
						'(function($){' . PHP_EOL .
							'head.load({togglescss: "' . PROJECT_HTTP_ROOT . '/extLibs/jquery/toggles/css/toggles.css"});' . PHP_EOL .
							'head.load({togglescss: "' . PROJECT_HTTP_ROOT . '/extLibs/jquery/toggles/css/themes/toggles-cwms.css"});' . PHP_EOL .
							'head.load({toggles: "' . PROJECT_HTTP_ROOT . '/extLibs/jquery/toggles/toggles.min.js"});' . PHP_EOL .
							'head.ready("toggles", function(){
								$(\'.publishPage\').toggles({
									checkbox: $(\'input.togglePageStatus\'),
									on: ' . (isset($this->pageDef['publish']) && $this->pageDef['publish'] ? "true" : "false") . ',
									text: {	on: "online",
											off: "offline"
									}
								});
								$(".publishPage").on("toggle", function(e, active) {
		
									e.preventDefault();
									e.stopPropagation();
									
									var element		= $(this);
									var statusInput	= $(this).find("input");
									var targetUrl	= $(this).attr("data-url");
									var status		= targetUrl.split("&online=");
									var newStatus;
									
									if(status[1] == 0){
										newStatus = 1;
									}else{
										newStatus = 0;
									}
									
									if(statusInput.is(":checked")) {
										statusInput.removeAttr("checked");
									} else {
										statusInput.prop("checked","checked");
									}
									
									$.ajax({
										url: targetUrl
									}).done(function(ajax){
										element.attr("data-url",status[0] + "&online=" + newStatus);
										$(".editHeader .iconBox-page").children(".cc-admin-icons").toggleClass("cc-icon-offline disabled");
										return false;
									});
									return false;
								});
								$(".publishPage").on("click", function(e, active) {
									e.stopImmediatePropagation();
								});
							});' . PHP_EOL .
						'})(jQuery);' . PHP_EOL .
					'});'.PHP_EOL;
		
		// Color picker
		$output	.=	'head.ready("jquery", function(){' . PHP_EOL .
						'(function($){' . PHP_EOL .
						'$.myColorPickerElem = function(){' . PHP_EOL .
							'head.load({cpcss: "' . PROJECT_HTTP_ROOT . '/extLibs/jquery/color-picker/color-picker.css"});' . PHP_EOL .
							'head.load({cpcolors: "' . PROJECT_HTTP_ROOT . '/extLibs/jquery/color-picker/colors.js"});' . PHP_EOL .
							'head.load({colorpicker: "' . PROJECT_HTTP_ROOT . '/extLibs/jquery/color-picker/jqColorPicker.min.js"});' . PHP_EOL .
							'head.ready("jquery", function(){' . PHP_EOL .
							'head.ready("cpcolors", function(){' . PHP_EOL .
							'head.ready("colorpicker", function(){' . PHP_EOL .
								'head.load({colorpickeridx: "' . PROJECT_HTTP_ROOT . '/extLibs/jquery/color-picker/index.js"}, function(){
									$("body").find(".color-picker-input").colorPicker();
								});' . PHP_EOL .
							'});' . PHP_EOL .
							'});' . PHP_EOL .
							'});' . PHP_EOL .
						'};' . PHP_EOL .
						'})(jQuery);' . PHP_EOL .
					'});' . PHP_EOL .
					'head.ready("ccInitScript", function(){' . PHP_EOL .
						'$.addInitFunction({name: "$.myColorPickerElem", params: ""});' . PHP_EOL .
					'});'.PHP_EOL;
	

		// Color picker
		$output	.=	'head.ready("jquery", function(){' . PHP_EOL .
					'head.ready("ui", function(){' . PHP_EOL .
					'head.load({tagEditorcss: "' . PROJECT_HTTP_ROOT . '/extLibs/jquery/tagEditor/jquery.tag-editor.min.css"});' . PHP_EOL .
					'head.load({tagEditorcaret: "' . PROJECT_HTTP_ROOT . '/extLibs/jquery/tagEditor/jquery.caret.min.js"});' . PHP_EOL .
					'head.load({tagEditor: "' . PROJECT_HTTP_ROOT . '/extLibs/jquery/tagEditor/jquery.tag-editor.min.js"});' . PHP_EOL .
					'head.ready("tagEditor", function(){' . PHP_EOL .
					// category autocomplete
					'$.widget( "tagEditor.autocomplete", $.ui.autocomplete, {
						  _create: function() {
							this._super();
							this.widget().menu( "option", "items", "> :not(.ui-autocomplete-category)" );
						  },
						  _renderMenu: function( ul, items ) {
							var that = this,
							  currentCategory = "";
							$.each( items, function( index, item ) {
							  var li;
							  if ( item.category != currentCategory ) {
								ul.append( "<li class=\'ui-autocomplete-category\'>" + item.category + "</li>" );
								currentCategory = item.category;
							  }
							  li = that._renderItemData( ul, item );
							  if ( item.category ) {
								li.attr( "aria-label", item.category + " : " + item.label );
							  }
							});
						  }
					});'.
					'$("document").ready(function(){' . PHP_EOL .
					// Class tag selector
						// Element class
						'$(".inputEleClass").each(function(){
							var elem	= $(this);
							var eleDiv	= elem.closest("[data-contype]");
							var conType	= eleDiv.attr("data-contype");
							elem.tagEditor({
								maxLength: 512,
								forceLowercase: false,
								delimiter: " ,;\n",
								autocomplete: {
									position: { collision: "flip" }, // automatic menu position up/down
									source: "' . SYSTEM_HTTP_ROOT . '/access/editElements.php?page=admin&fe-theme=1&action=gridclasses&tagtype=ele&contype=" + conType,
									delay: 0,
									minLength: 0
								}
							});
						});' . PHP_EOL .
						// Section class
						'$(".inputSecClass").each(function(){
							var elem	= $(this);
							var eleDiv	= elem.closest("[data-contype]");
							var conType	= eleDiv.attr("data-contype");
							elem.tagEditor({
								maxLength: 512,
								forceLowercase: false,
								delimiter: " ,;\n",
								autocomplete: {
									position: { collision: "flip" }, // automatic menu position up/down
									source: "' . SYSTEM_HTTP_ROOT . '/access/editElements.php?page=admin&fe-theme=1&action=gridclasses&tagtype=sec&contype=" + conType,
									delay: 0,
									minLength: 0
								}
							});
						});' . PHP_EOL .
						// Container class
						'$(".inputCtrClass").each(function(){
							var elem	= $(this);
							var eleDiv	= elem.closest("[data-contype]");
							var conType	= eleDiv.attr("data-contype");
							elem.tagEditor({
								maxLength: 512,
								forceLowercase: false,
								delimiter: " ,;\n",
								autocomplete: {
									position: { collision: "flip" }, // automatic menu position up/down
									source: "' . SYSTEM_HTTP_ROOT . '/access/editElements.php?page=admin&fe-theme=1&action=gridclasses&tagtype=ctr&contype=" + conType,
									delay: 0,
									minLength: 0
								}
							});
						});' . PHP_EOL .
						// Row class
						'$(".inputRowClass").each(function(){
							var elem	= $(this);
							var eleDiv	= elem.closest("[data-contype]");
							var conType	= eleDiv.attr("data-contype");
							elem.tagEditor({
								maxLength: 512,
								forceLowercase: false,
								delimiter: " ,;\n",
								autocomplete: {
									position: { collision: "flip" }, // automatic menu position up/down
									source: "' . SYSTEM_HTTP_ROOT . '/access/editElements.php?page=admin&fe-theme=1&action=gridclasses&tagtype=row&contype=" + conType,
									delay: 0,
									minLength: 0
								}
							});
						});' . PHP_EOL .
						// Wrapper div class
						'$(".inputDivClass").each(function(){
							var elem	= $(this);
							var eleDiv	= elem.closest("[data-contype]");
							var conType	= eleDiv.attr("data-contype");
							elem.tagEditor({
								maxLength: 512,
								forceLowercase: false,
								delimiter: " ,;\n",
								autocomplete: {
									position: { collision: "flip" }, // automatic menu position up/down
									source: "' . SYSTEM_HTTP_ROOT . '/access/editElements.php?page=admin&fe-theme=1&action=gridclasses&tagtype=div&contype=" + conType,
									delay: 0,
									minLength: 0
								}
							});
						});' . PHP_EOL .
					'});' . PHP_EOL .
					'});' . PHP_EOL .
					'});' . PHP_EOL .
					'});' . PHP_EOL;
		
		$output	.=	'</script>' . PHP_EOL;
		
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
						});
					});' . PHP_EOL .
				'});' . PHP_EOL .
				'});' . PHP_EOL .
				'</script>' . PHP_EOL;
	
	}
	

	// getTabsScriptTag
	public function getTabsScriptTag($sel = 0)
	{

		return	'<script>' . PHP_EOL .
				'head.ready("ui", function(){' . PHP_EOL .
					'$(document).ready(function(){' . PHP_EOL .
						'$(".cc-edit-element-box").each(function(){
							var conItem		= $(this);
							var tabs		= false;
							var opts		= {};
							var selected	= ' . $sel . ';
							var tabCon		= conItem.find("span.notice").filter(":first").closest(".cc-stylesbox");
							if(tabCon.length){
								selected	= 1;
							}
							if(selected){
								opts	= { active: selected };
							}
							tabs	= conItem.tabs(opts);
							return tabs;
						});' . PHP_EOL .
					'});' . PHP_EOL .
				'});' . PHP_EOL .
				'</script>' . PHP_EOL;
	
	}
	
	
	// getEditAdminTourScript
	protected function getEditAdminTourScript()
	{
	
		return	'<script>
				head.ready(function(){
					head.load({hopscotch: "extLibs/jquery/hopscotch/js/hopscotch.min.js"}, function(){
						head.load("extLibs/jquery/hopscotch/css/hopscotch.min.css");
						head.load({admintouredit: "system/inc/admintasks/edit/js/adminTour.edit.min.js"}, function(){
							$("document").ready(function(){
								// Start tour on desktop devices
								if(!cc.isPhone()){
									$.edit_AdminTour();
								}
							});
						});
					});
				});
				</script>' . PHP_EOL;
	
	}

}
