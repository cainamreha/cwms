<?php
namespace Concise;



###################################################
##############  Templates-Bereich  ################
###################################################

// Templates verwalten 


class Admin_Main extends Admin implements AdminTask
{

	private $newTplAdded	= false;
	private $checkModTables = array("gbook",
									"comments",
									"articles",
									"news",
									"planner"
									);
	
	public function __construct($DB, $o_lng, $task, $init = false)
	{

		// Admin-Elternklasse aufrufen
		parent::__construct($DB, $o_lng);
		
		parent::$task = $task;

	}
	

	/**
	 * Hauptbereichsinhalte
	 * 
	 * @access public
	 * @return string
	 */
	public function getTaskContents($ajax = false)
	{

		// Falls ein Lock durch User besteht, Sperre löschen
		$this->LOCK->deleteAllUserLocks($this->loggedUser);
		
		
		// Ggf. EditID aus Session löschen
		$this->unsetSessionKey('edit_id');
		
		
		// Welcome Message
		$this->adminContent .=	$this->getBackendWelcomeMessage();
		
		
		// Falls der Installationsordner gelöscht werden soll
		if(isset($GLOBALS['_GET']['delinstalldir']) && $this->adminLog && is_dir(PROJECT_DOC_ROOT . '/install')) {
			if(self::unlinkRecursive(PROJECT_DOC_ROOT . '/install', true))
				$this->adminContent .= $this->getNotificationStr('{s_notice:installdel}', "notice");
			else
				$this->adminContent .= $this->getNotificationStr('{s_error:installdel}', "error");
		}
		
		
		// Ggf. Meldungen einbinden
		$this->adminContent .= $this->getSessionNotifications("all", true);
		
		
		// Dashboard
		$this->adminContent .= $this->getDashboard();
	
	
		// Menüboard
		$this->adminContent .=	$this->getAdminMenu(1); // Menü
	
	
		// Script code
		$this->adminContent .=	$this->getMainScript();
	
	
		// Admin Tour Script
		$this->adminContent .=	$this->getAdminTourScript();
		
	}
	

	/**
	 * Dashboard
	 * 
	 * @access public
	 * @return string
	 */
	public function getDashboard()
	{

		$changes		= "";
		
		$dashboard		=	'<h2 class="toggle cc-section-heading cc-h2">Dashboard</h2>' . PHP_EOL .
							'<div id="ccAdminDashboard" class="dashboard cc-admin-panel-box">' . PHP_EOL;
		
	
		// Falls der Installationsordner noch nicht gelöscht wurde, Meldung einbinden
		if($this->adminLog && is_dir(PROJECT_DOC_ROOT . '/install'))
		
			$changes		.=	'<p class="error">{s_notice:deleteinstall}</p>';
		
	
		// Falls der Live-Modus abgeschaltet ist, Meldung einbinden
		if(!WEBSITE_LIVE) {
		
			$changes		.=	'<p id="goLiveLink-dashboard" class="error">{s_common:hint}: {s_title:websitestage}' .
								'<br /><br />';
			
			// Button go-live
			$btnDefs	= array(	"href"		=> SYSTEM_HTTP_ROOT . "/access/editPages.php?page=admin&amp;sitemode=1",
									"class"		=> '{t_class:btnpri}',
									"text"		=> '{s_title:golive}',
									"title"		=> '{s_title:golive}',
									"attr"		=> 'onclick="$(\'#previewNav\').find(\'.siteStatusBox\').click(); return false;"',
									"icon"		=> "go-stage"
								);
			
			$changes		.=	parent::getButtonLink($btnDefs);
								
			$changes		.=	'</p>' . PHP_EOL;
		}
	
		// Falls Updates vorhanden sind, Meldung einbinden
		if($this->updateAvailable) {
		
			$changes		.=	'<p id="updateLink-dashboard" class="error">{s_common:hint}: {s_hint:newupdates}' .
								'<br /><br />';
			
			// Button go-live
			$btnDefs	= array(	"href"		=> ADMIN_HTTP_ROOT . "?task=update",
									"class"		=> '{t_class:btnpri}',
									"text"		=> '{s_button:downloadupdate}',
									"title"		=> '{s_button:downloadupdate}',
									"icon"		=> "update"
								);
			
			$changes		.=	parent::getButtonLink($btnDefs);
								
			$changes		.=	'</p>' . PHP_EOL;
		}
		
		// Falls noch nicht übernommene Änderungen vorliegen, Liste einbinden
		if($this->editorLog && count($this->diffConIDs) > 0)
		
			$changes		.=	$this->getChangesList();
		
		
		// Falls Meldungen vorliegen
		if($changes != "")
		
			$dashboard .=	'<div class="adminArea adminMain mainNotes cc-admin-panel">' . PHP_EOL .
							'<h3 class="cc-h3 toggle">{s_header:notes}</h3>' . PHP_EOL .
							'<div class="dashboard-notes">' . PHP_EOL .
							$changes .
							'</div>' . PHP_EOL .
							'</div>' . PHP_EOL;


		
		// Unveröffentlichte Daten
		if(!array_key_exists(self::$task, $this->adminPages)
		|| !in_array($this->loggedUserGroup, $this->adminPages[self::$task]['access'])
		) {
		
			$recentData	= array();

			// Daten-Module auslesen, fall Berechtigung
			foreach($this->checkModTables as $rdTable) {
				
				$data		= false;
				
				if(array_key_exists($rdTable, $this->adminPages)
				&& in_array($this->loggedUserGroup, $this->adminPages[$rdTable]['access'])
				)
					$data		= $this->getUnpublishedData($rdTable);
				
				if($data !== false)
					$recentData[]	= $this->getUnpublishedData($rdTable);
			}
			
			if(count($recentData) > 0) {
				
				$dashboard .=	'<div class="adminArea adminMain mainData cc-admin-panel">' . PHP_EOL .
								'<h3 class="cc-h3 toggle">{s_header:unpubdata}</h3>'. PHP_EOL .
								'<div class="unpublishedData">' . PHP_EOL .
								'<table class="stats overview shortStats adminTable">' . PHP_EOL .
								'<thead>' . PHP_EOL .
								'<tr>' . PHP_EOL .
								'<th>{s_header:adminmod}</th><th>{s_header:value}</th>' . PHP_EOL .
								'</tr>' . PHP_EOL .
								'</thead>' . PHP_EOL .
								implode(PHP_EOL, $recentData) .
								'</table>' . PHP_EOL .
								'</div>' . PHP_EOL .
								'</div>' . PHP_EOL;
			}
		}
		

		// Statistiken
		if(CONCISE_LOG
		&& array_key_exists("stats", $this->adminPages)
		&& in_array($this->loggedUserGroup, $this->adminPages["stats"]['access'])
		) {

			require_once PROJECT_DOC_ROOT . "/inc/classes/Logging/class.Stats.php";
			$o_stats		= new Stats($this->DB, $this->editLang);
			$tsEnd			= time();
			$tsEndD			= strtotime(date("Y-m-d", time()));
			$tsStart		= $tsEndD-29*24*3600;
			
			// Gesamtstatistik (letzte 30 Tage)
			$dashboard .=	'<div class="adminArea adminMain mainStats cc-admin-panel">' . PHP_EOL .
							'<h3 class="cc-h3 toggle">{s_header:allstats}</h3>' . PHP_EOL .
							'<div class="mainStats-panel">' . PHP_EOL .
							$o_stats->getStats(true, true, $tsStart, $tsEnd) .
							$o_stats->showClickCountChart(30, "", "visits_period", $tsStart, $tsEnd, false) .
							'</div>' . PHP_EOL .
							'</div>' . PHP_EOL;
		}
		
		
		$dashboard .=	'<br class="clearfloat" />' . PHP_EOL;
		$dashboard .=	'</div>' . PHP_EOL;
		
		return $dashboard;
	
	}
	

	/**
	 * Links und Buttons für die Übernahme von Änderungen bei Seiten/Templates
	 * 
	 * @access public
	 * @return string
	 */
	public function getChangesList()
	{

		$return		=	'<p class="error">{s_notice:changes}';
		
		// Falls ein Lock besteht, Meldung auf vorraussichtliche Blockierdauer ausgeben
		if(($this->genLock[0] == true && $this->genLock[1]['lockedBy'] != $this->loggedUser) || 
		   ($this->pageLock[0] == true && $this->pageLock[1]['lockedBy'] != $this->loggedUser) || 
		   ($this->foreignPageLock[0] == true && $this->foreignPageLock[1]['lockedBy'] != $this->loggedUser)
		  ) {
		
			$lockStr	= sprintf(ContentsEngine::replaceStaText('{s_error:lockdata}'), '<strong>' . ($this->genLock[0] ? $this->genLock[1]['lockedBy'] : ($this->pageLock[0] ? $this->pageLock[1]['lockedBy'] : $this->foreignPageLock[1]['lockedBy'])) . '</strong>', '<strong>' . date("H:i:s", ($this->genLock[0] ? $this->genLock[1]['lockedUntil'] : ($this->pageLock[0] ? $this->pageLock[1]['lockedUntil'] : $this->foreignPageLock[1]['lockedUntil']))) . '</strong>');
			
			$return .=	'<br /><br />{s_notice:lockchanges}</p>' . PHP_EOL .
						'<p class="lockedBy framedParagraph">' .
						parent::getIcon("user", "inline-icon") .
						$lockStr;
			
			// Button refresh
			$btnDefs	= array(	"href"		=> ADMIN_HTTP_ROOT,
									"class"		=> 'button-icon-only inline-icon',
									"text"		=> "",
									"title"		=> '{s_title:refresh}',
									"attr"		=> 'onclick="$.doAjaxAction($(this).attr(\'href\'), true);"',
									"icon"		=> "refresh"
								);
			
			$return	.=	parent::getButtonLink($btnDefs);
						
			$return	.=	'</p>' . PHP_EOL .
						'<p>&nbsp;</p>' . PHP_EOL;
			
			return $return;
		}
		
		// Andernfalls Buttons zum Übernehmen der Änderungen anzeigen
		$return .=	'</p>' . PHP_EOL .
					'<ul class="conChangesList">' . PHP_EOL;
		
		$i = 0;
		
		// Seiten/Templates mit Änderungen auflisten
		foreach($this->diffConAlias as $ID => $diffPage) {
		
			$j 	= $i +1;
			
			// Falls mehr als fünf Einträge, Rest ausblenden und Button zum Einblenden einbinden
			if($i == 5) {
				
				// Button edit
				$btnDefs	= array(	"type"		=> "button",
										"class"		=> "toggleChangesItems",
										"value"		=> "{s_common:showall}",
										"title"		=> "{s_common:showall}",
										"text"		=> '{s_text:additional} ' . (count($this->diffConAlias) -5),
										"attr"		=> 'data-reveal=".listItem"',
										"icon"		=> "toggle"
									);
				
				$return	.=	parent::getButton($btnDefs);
				
			}
			
			$return .=	'<li class="changesItem listItem' . ($i%2 ? ' alternate' : '') . '"' . ($i >= 5 ? ' style="display:none;"' : '') . ' data-menu="context" data-target="contextmenu-changes-a' . $j . ',contextmenu-changes-b' . $j . '">' . PHP_EOL;
			
			// Button panel
			$return .=	'<span class="editButtons-panel panel-left" data-id="contextmenu-changes-a' . $j . '">' . PHP_EOL;
			
			// Falls Template
			if(strpos($diffPage, ".tpl (")) {
			
				$editTPL		=	substr($diffPage, 0, strrpos($diffPage, ".tpl (")+4);
				$areaContents	=	substr($diffPage, strrpos($diffPage, ".tpl (")+6,-1);
				$modDate		=	"";
				
				$editHref		=	ADMIN_HTTP_ROOT . '?task=tpl&type=edit&edit_tpl=' . $editTPL . '&edit_area=contents_' . $areaContents;
				
				// Button edit
				$btnDefs	= array(	"href"		=> $editHref,
										"class"		=> "editLinkIcon button-icon-only button-small",
										"text"		=> "",
										"title"		=> "{s_title:edit}",
										"attr"		=> 'data-menuitem="true" data-id="item-id-' . $j . '"',
										"icon"		=> "edit"
									);
				
				$editLink	=	parent::getButtonLink($btnDefs);
				
				// Button preview
				$btnDefs	= array(	"href"		=> $editHref,
										"class"		=> "previewLink button-icon-only button-small",
										"text"		=> "",
										"title"		=> "{s_title:edit}",
										"attr"		=> 'data-menuitem="true" data-id="item-id-' . $j . '"',
										"icon"		=> 'area-' . $areaContents
									);
				
				$previewLink	=	parent::getButtonLink($btnDefs);
						
				$changesButtons =	"";
				
				// Button apply
				$btnDefs	= array(	"href"		=> ADMIN_HTTP_ROOT . '?task=changes&affect=1&edit_tpl=' . $editTPL . '&edit_area=contents_' . $areaContents,
										"class"		=> "goLive change button-icon-only button-small",
										"text"		=> "",
										"title"		=> "{s_link:changes}",
										"icon"		=> "apply",
										"attr"		=> 'data-action="applychanges" data-contextmenuitem="true" data-itemclass="icon-apply" data-menuitem="true" data-menutitle="{s_link:changes}" data-id="item-id-' . $j . '"'
									);
				
				$changesButtons	.=	parent::getButtonLink($btnDefs);
				
				// Button cancel
				$btnDefs	= array(	"href"		=> ADMIN_HTTP_ROOT . '?task=changes&affect=0&edit_tpl=' . $editTPL . '&edit_area=contents_' . $areaContents,
										"class"		=> "cancel button-icon-only button-small",
										"text"		=> "",
										"title"		=> "{s_javascript:feeditcancel}",
										"icon"		=> "cancel",
										"attr"		=> 'data-action="discardchanges" data-contextmenuitem="true" data-itemclass="icon-cancel" data-menuitem="true" data-menutitle="{s_javascript:feeditcancel}" data-id="item-id-' . $j . '"'
									);
				
				$changesButtons	.=	parent::getButtonLink($btnDefs);
			
			}
			
			// Falls Page
			else {
			
				$editHref			=	ADMIN_HTTP_ROOT . '?task=edit&edit_id=' . $this->diffConIDs[$ID];
				$previewHref		=	HTML::getLinkPath($this->diffConIDs[$ID]);
				
				// Button edit
				$btnDefs	= array(	"href"		=> $editHref,
										"class"		=> "editLinkIcon button-icon-only button-small",
										"text"		=> "",
										"title"		=> "{s_title:edit}",
										"attr"		=> 'data-menuitem="true" data-id="item-id-' . $j . '"',
										"icon"		=> "edit"
									);
				
				$editLink	=	parent::getButtonLink($btnDefs);
				
				// Button preview
				$btnDefs	= array(	"href"		=> $previewHref,
										"class"		=> "previewLink button-icon-only button-small",
										"text"		=> "",
										"title"		=> '{s_title:pagepreview} &#x25BA; ' . $diffPage,
										"attr"		=> 'data-menutitle="{s_title:pagepreview} &#x25BA; ' . $diffPage . '" data-menuitem="true" data-id="item-id-' . $j . '"',
										"icon"		=> "preview"
									);
				
				$previewLink	=	parent::getButtonLink($btnDefs);
				
				$modDate		=	'<span class="modDate">{s_text:lastmodified} <strong>' . self::getDateString($this->diffConDate[$ID], $this->adminLang) . '</strong></span>';
				
				$changesButtons =	"";
				
				// Button apply
				$btnDefs	= array(	"href"		=> ADMIN_HTTP_ROOT . '?task=changes&affect=1&edit_id=' . $this->diffConIDs[$ID],
										"class"		=> "goLive change button-icon-only button-small",
										"text"		=> "",
										"title"		=> "{s_link:changes}",
										"icon"		=> "apply",
										"attr"		=> 'data-action="applychanges" data-contextmenuitem="true" data-itemclass="icon-apply" data-menutitle="{s_link:changes}" data-menuitem="true" data-id="item-id-' . $j . '"'
									);
				
				$changesButtons	.=	parent::getButtonLink($btnDefs);
				
				// Button cancel
				$btnDefs	= array(	"href"		=> ADMIN_HTTP_ROOT . '?task=changes&affect=0&edit_id=' . $this->diffConIDs[$ID],
										"class"		=> "cancel button-icon-only button-small",
										"text"		=> "",
										"title"		=> "{s_javascript:feeditcancel}",
										"icon"		=> "cancel",
										"attr"		=> 'data-action="discardchanges" data-contextmenuitem="true" data-itemclass="icon-cancel" data-menuitem="true" data-menutitle="{s_javascript:feeditcancel}" data-id="item-id-' . $j . '"'
									);
				
				$changesButtons	.=	parent::getButtonLink($btnDefs);

			}
			
			$return .=	$editLink;
						
			$return .=	$previewLink;
			
			$return .=	'</span>' . PHP_EOL;
						
			$return .=	'<a href="' . $editHref. '" class="editLink">' . PHP_EOL .
						'<span class="editPageTitle pageTitle" title="{s_title:edit}">' . $diffPage . '</span>' . PHP_EOL .
						'</a>' . PHP_EOL;
			
			$return .=	'<span class="changesButtons-panel editButtons-panel" data-id="contextmenu-changes-b' . $j . '">' . 
						$changesButtons .
						'</span>' . PHP_EOL;
			
			$return .=	$modDate .
						'<br class="clearfloat" />';
						
			$return .=	'</li>' . PHP_EOL;
			
			$i++;
		}
		
		$return .=	'<li class="change submit">' . "\n";
		
		// Button apply
		$btnDefs	= array(	"href"		=> ADMIN_HTTP_ROOT . '?task=changes&affect=all',
								"class"		=> "goLive change",
								"text"		=> "{s_common:all} {s_link:changes}",
								"title"		=> "{s_common:all} {s_link:changes}",
								"icon"		=> "apply",
								"attr"		=> 'data-action="applychanges"'
							);
		
		$return	.=	parent::getButtonLink($btnDefs);
		
		// Button cancel
		$btnDefs	= array(	"href"		=> ADMIN_HTTP_ROOT . '?task=changes&affect=none',
								"class"		=> "cancel right",
								"text"		=> "{s_link:nochanges}",
								"title"		=> "{s_javascript:feeditcancel}",
								"icon"		=> "cancel",
								"attr"		=> 'data-action="discardchanges"'
							);
		
		$return	.=	parent::getButtonLink($btnDefs);
	
		$return .=	'</li>' . PHP_EOL .
					'</ul>' . PHP_EOL;

		
		// Contextmenü-Script
		$return	.=	$this->getContextMenuScript();

		
		return $return;
		
	}	
	

	/**
	 * Übernahme/Verwerfen von Änderungen bei Seiten/Templates
	 * 
	 * @access public
	 * @return string
	 */
	public function conductChanges()
	{
	
		$affect			= "none";
		$changesTarget	= "";
		$changesTPL		= "";

		// Prüfen on alle bzw. einzelne Änderungen übernehmen oder verworfen werden sollen
		if($GLOBALS['_GET']['affect'] == "all" 
		|| $GLOBALS['_GET']['affect'] == "none" 
		|| $GLOBALS['_GET']['affect'] == 1 
		|| $GLOBALS['_GET']['affect'] == 0
		)
			$affect = $GLOBALS['_GET']['affect'];
			
		// Änderungen für ein Template übernehmen/verwerfen
		if(isset($GLOBALS['_GET']['edit_tpl']) 
		&& !is_numeric($GLOBALS['_GET']['edit_tpl']) 
		&& isset($GLOBALS['_GET']['edit_area']) 
		&& $GLOBALS['_GET']['edit_area'] != ""
		) {
			$changesTarget	= $GLOBALS['_GET']['edit_tpl'];
			$changesTPL		= $GLOBALS['_GET']['edit_area'];
		}
		
		// Änderungen für eine Seite übernehmen
		elseif(isset($GLOBALS['_GET']['edit_id']) 
		&& is_numeric($GLOBALS['_GET']['edit_id'])
		)
			$changesTarget	= $GLOBALS['_GET']['edit_id'];
			
		// Änderungen an Seiten- bzw. Templateinhalten übernehmen/veröffentlichen
		$applyChanges = $this->applyConChanges($affect, $changesTarget, $changesTPL);
		
		if($applyChanges == "all" || $applyChanges > 0)
			$this->setSessionVar('notice', "{s_notice:changecon}");
		elseif($applyChanges == "none" || $applyChanges == 0)
			$this->setSessionVar('notice', "{s_notice:cancelcon}");
		else {
			if(count($this->diffConIDs) <= 0)
				$this->setSessionVar('error', "{s_error:nochangecon}");
		}
		
		$taskExt = "";
		
		// Falls zum Editbereich einer Seite gegangen werden soll
		if(isset($GLOBALS['_GET']['edit'])) {
			
			$taskExt = '?task=' . (isset($GLOBALS['_GET']['edit_area']) ? 'tpl&type=edit' : 'edit');
			
			if($GLOBALS['_GET']['edit'] == 1)
				$taskExt .= "&edit_id=" . $changesTarget;
				
			if(isset($GLOBALS['_GET']['edit_area']))
				$taskExt .= "&area=" . $GLOBALS['_GET']['edit_area'];
			
			if(CACHE && $applyChanges == "1")
				$taskExt .= "&cacheref=1";
		}
		
		header("Location: " . ADMIN_HTTP_ROOT . $taskExt);		
		exit;
	}

	
	
	/**
	 * Methode zum Anzeigen von unveröffentlichten Artikeln/Daten/Kommentaren
	 * 
	 * @param	string $search String
	 * @param	array $array zu durchlaufendes Array (default = pages)
	 * @access	public
     * @return  boolean
	 */
	public function getUnpublishedData($table)
	{
	
		$restrict	= "";
		$join		= "";
		
		// Falls Author, Daten nach selbst verfassten Filtern
		if($this->loggedUserGroup == "author" && ($table == "articles" || $table == "news" || $table == "planner")) {
			
			$restrict	= "AND `author_id` = '" . $this->DB->escapeString($this->loggedUserID) . "'";
			
			$join		= "  LEFT JOIN `" . DB_TABLE_PREFIX . "user`
									ON `" . DB_TABLE_PREFIX . $table . "`.`author_id` = `" . DB_TABLE_PREFIX . "user`.`userid`";
		}
		
		// Suche nach unterschiedichen Inhaltsspalten
		$queryUnpub = $this->DB->query( "SELECT COUNT(*) 
											FROM `" . DB_TABLE_PREFIX . $table . "` " .
											$join .	" 
											WHERE `published` = 0 
											$restrict
										 ");
	  
		#var_dump($queryUnpub);
		
		// Falls unveröffentlichte Daten vorhanden
		if($queryUnpub[0]['COUNT(*)'] > 0) {
		
			$link	= '<a href="admin?task=modules&type=' . $table . '&pub=unpub&list_cat=all" title="{s_title:showunpup} {s_option:' . $table . '}">';
			$output = '<tr><td>' . parent::getIcon("volume-mute2", "inline-icon left") . '<span class="tableCell">'. $link . '{s_option:' . $table . '}</a></span></td><td>' . $link . $queryUnpub[0]['COUNT(*)'] . '</a></td></tr>' . PHP_EOL;
			
			return $output;
		}
		
		return false;
	
	}

	
	// getBackendWelcomeMessage
	protected function getBackendWelcomeMessage()
	{
	
		$output		=	"";
		$userImg	= Login::getUserImage($this->loggedUserID);
		
		$output		.=	'<div class="cc-welcome-user">' . $userImg .
						'<span class="loggedUser">{s_text:adminmain} <strong>' . (!empty($this->g_Session['author_name']) ? $this->g_Session['author_name'] : $this->loggedUser) . '</strong></span> ' . PHP_EOL .
						'<span class="loggedSince">' . parent::getIcon("user", "inline-icon") . '{s_text:loggedsince}<strong> ' . (!empty($this->g_Session['loggedInSince']) ? $this->g_Session['loggedInSince'] : '?') . '</strong></span>' . PHP_EOL;
		
		// Button preview
		$btnDefs	= array(	"href"		=> '#',
								"id"		=> "toggleMenu",
								"class"		=> "button-icon-only inline-icon right",
								"text"		=> "",
								"title"		=> "{s_title:menu}",
								"icon"		=> 'togglegrid'
							);
		
		$output	.=	parent::getButtonLink($btnDefs);
		
		$output	.=	'</div>' . PHP_EOL . 
					'</div><!-- Ende headerBox -->' . PHP_EOL;
		
		return $output;
		
	}
	
	
	// getMainScript
	protected function getMainScript()
	{
	
		return	'<script>
				head.ready("ccInitScript", function(){
					$.addInitFunction({name: "$.toggleDashboard", params: ""});
				});
				head.ready("jquery", function(){
					head.load({jknob: "extLibs/jquery/knob/jquery.knob.min.js"}, function(){
						head.load({adminknobs: "system/inc/admintasks/main/js/adminKnobs.min.js"}, function(){
							$("document").ready(function(){
								// Create dashboard knobs
								$.createDashboardKnobs();
							});
						});
					});
				});
				</script>' . PHP_EOL;
	
	}
	
	
	// getAdminTourScript
	protected function getAdminTourScript()
	{
	
		return	'<script>
				head.ready(function(){
					head.load({hopscotch: "extLibs/jquery/hopscotch/js/hopscotch.min.js"}, function(){
						head.load("extLibs/jquery/hopscotch/css/hopscotch.min.css");
						head.load({admintour: "system/inc/admintasks/main/js/adminTour.min.js"}, function(){
							$("document").ready(function(){
								// Start tour on desktop devices
								if(!cc.isPhone()){
									$.main_AdminTour();
								}
							});
						});
					});
				});
				</script>' . PHP_EOL;
	
	}

}
