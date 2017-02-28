<?php
namespace Concise;



###################################################
##############  Pluginverwaltung  #################
###################################################

// Plug-Ins verwalten

class Admin_Plugins extends Admin implements AdminTask
{

	private $pluginDownloadSite = 'http://www.hermani-webrealisierung.de/';
	private $pluginDownloadPage = 'concise-wms-plugins.html';
	private $pluginDownloadDir	= 'media/files/';
	private $pluginUrl			= "";
	private $pluginDir			= "";
	private $pluginFile			= "";
	private $pluginName			= "";
	private $archive			= "";
	private $pluginExists		= false;
	private $overwritePlugin	= true;
	private $pluginError		= array();
	private $pluginSuccess		= array();
	private $pluginInstalled	= false;
	private $instPluginCnt		= 0;
	private $sortList			= "nameasc";
	private $pubFilter			= "all";
	private $sortRes			= "";
	private $sortBy				= "name";
	private $sortOrder			= "asc";
	private $pluginSearch		= "";

	
	public function __construct($DB, $o_lng, $task, $init = false)
	{

		// Admin-Elternklasse aufrufen
		parent::__construct($DB, $o_lng);
		
		parent::$task = $task;

	}
	
	
	public function getTaskContents($ajax = false)
	{

		// Enthält Headerbox
		$this->adminHeader		=	'{s_text:adminplugins}' . PHP_EOL . 
									$this->closeTag("#headerBox");
		
		// #adminContent
		$this->adminContent 	=	$this->openAdminContent();
		

		$formAction		= ADMIN_HTTP_ROOT . '?task=plugins';
		$showBackButton = true;
		$setLock 		= array(true);
		

		// Parameter auslesen
		// Weitere Listenparameter
		$this->evalListParams();


		// Bereich: Plugins
		$this->adminContent .=	'<div class="adminArea">' . PHP_EOL;


		#############  Plug-in installieren  ###############

		// Falls ein Plug-in installiert werden soll
		if(isset($GLOBALS['_POST']['pluginUrl'])) {
		
			$this->pluginUrl	= trim($GLOBALS['_POST']['pluginUrl']);
			
			if($this->verifyPluginUrl($this->pluginUrl)){
			
				$this->pluginFile		= str_replace("_doc-", "", pathinfo($this->pluginUrl, PATHINFO_BASENAME));
				
				// Galerienamen entschlüsseln
				require_once(PROJECT_DOC_ROOT."/inc/classes/Modules/class.myCrypt.php"); // Klasse myCrypt einbinden
				require_once(PROJECT_DOC_ROOT."/inc/classes/Media/class.Files.php"); // Klasse Files einbinden
				
				// myCrypt Instanz
				$crypt = new myCrypt();
				
				// Decrypt String
				$this->pluginFile		= trim($crypt->decrypt($this->pluginFile));
				#$this->pluginFile		= Files::getValidFileName($this->pluginFile); // Sanitize
				$this->pluginFile		= pathinfo($this->pluginFile, PATHINFO_BASENAME);
				$this->pluginName		= Files::getFileBasename($this->pluginFile);
				
				$this->pluginDir		= PLUGIN_DIR . $this->pluginName;
				$this->archive			= TEMP_DIR . $this->pluginFile;
			
				// Falls Datei bereits heruntergeladen
				$this->checkDownloadExists($this->pluginDir);
			
				if(!$this->pluginExists || isset($GLOBALS['_POST']['overwritePlugin'])) {
					$this->installPluginFile($this->pluginUrl);
				}
				else
					$this->pluginError[]	= str_replace("$1", $this->pluginName, parent::replaceStaText("{s_notice:pluginexists}")); // Plugin exists
			}
		
		}



		#############  Plug-ins auflisten  ###############
		
		// Plug-ins auslesen
		$pluginPath			= PROJECT_DOC_ROOT . '/plugins/';
		$plugins			= "";
		$pluginsListStr		= "";
		$installedPlugin	= "";
		$k					= 1;
		
		$pluginsArr			= $this->scanPluginDir($pluginPath, $this->sortRes, $this->sortBy, $this->sortOrder);
	
	
		foreach($pluginsArr as $plugin) {
			
			// Falls Plugin noch nicht installiert
			if(!$this->checkInstallPlugin($plugin))
				$this->installPlugin($plugin);
			
			$isActive		= in_array($plugin, $this->activePlugins);
			$plVersion		= !empty($this->installedPlugins[$plugin]["version"]["pluginversion"]) ? $this->installedPlugins[$plugin]["version"]["pluginversion"] : '{s_common:unknown}';
			$installDate	= isset($this->installedPlugins[$plugin]) ? Modules::getFormattedDateString(strtotime($this->installedPlugins[$plugin]['date']), $this->adminLang) : '{s_common:unknown}';
			$statusNote		= ' (<span class="pluginDescr-status notice ' . ($isActive ? 'success' : 'error') . '">{s_common:' . ($isActive ? '' : 'in') . 'active}</span>)' . PHP_EOL;
			$dependencies	= "";
			$dependencyWarn	= false;
			$pluginIconKey	= "";
			
			ContentsEngine::setPlugin($plugin, $this->adminLang); // Sprachbausteine des Plug-ins laden
			
			// Falls Filter
			if(($this->pubFilter == "active" && !$isActive)
			|| ($this->pubFilter == "inactive" && $isActive)
			)
				continue;					
			
			// Dependencies
			if(!empty($this->installedPlugins[$plugin]['dependency'])) {
				
				$dependencies	.=	'<br />';
				$dependencies	.=	'<table class="adminTable">';
				$dependencies	.=	'<tbody>';
				$dependencies	.=	'<tr><th colspan="3">Dependencies</th></tr>' . PHP_EOL;
				
				foreach($this->installedPlugins[$plugin]['dependency'] as $dep) {
				
					if(!empty($dep)) {
						$depIsInstalled	= array_key_exists($dep, $this->installedPlugins) ? true : false;
						$depIsActive	= in_array($dep, $this->activePlugins) ? true : false;
						$depInstalled	= $depIsInstalled ? 'success">installed' : 'error">not installed';
						$depActive		= ($depIsActive ? 'success">{s_common:active' . '' : 'error">{s_common:inactive') . '}';
						$dependencies   .= '<tr><td>' . $dep . '</td><td><span class="notice ' . $depInstalled . '</span></td><td><span class="notice ' . $depActive . '</span></td></tr>';
						
						if(!$depIsInstalled
						|| !$depIsActive
						)
							$dependencyWarn	= true;
					}
				}
				$dependencies .=	'</tbody>';
				$dependencies .=	'</table>';
			}
			
			// Plugin-Icon
			if(!empty($this->installedPlugins[$plugin]['features']['conicon']))
				$pluginIconKey	= $this->installedPlugins[$plugin]['features']['conicon'];
			else
				$pluginIconKey	= $plugin;
			
			
			// List entry
			$pluginEntry =	'<li class="listItem pluginEntry' . ($this->pluginName == $plugin ? ' newPlugin' : '') . ' ' . (!$isActive ? 'in' : '') . 'active' . ($k%2 ? '' : ' alternate') . '" data-name="' . $plugin . '" data-toggleclass="active" data-menu="context" data-target="contextmenu-' . $k . '">' . PHP_EOL;
			
			// Markbox
			$pluginEntry .=	'<label class="markBox">' . 
							'<input type="checkbox" name="entryNr[' . $k . ']" class="addVal" />' .
							'<input type="hidden" name="entryID[' . $k . ']" value="' . $plugin . '" class="getVal" />' .
							'</label>';			
			
			// Plugin-Icon
			$pluginEntry .=	'<span class="pluginIcon listIcon">' .
							parent::getIcon($pluginIconKey, "contype-plugins inline-icon") .
							'</span>' . PHP_EOL;
			
			// Plug-In details
			$pluginEntry .=	'<span class="pluginName">' . $plugin . '</span>' . PHP_EOL .
							'<span class="pluginDescr toggleNext" title="{s_description:' . $plugin . '-long}"> {s_description:' . $plugin . '-short}' .
							($dependencyWarn ? parent::getIcon("warning") : '') .
							'</span>' . PHP_EOL .
							'<span class="pluginDescr-detail">{s_description:' . $plugin . '-long}' .
							'<br /><br /><label>Version: ' . $plVersion . '</label>' . PHP_EOL .
							'<label>{s_date:installdate}: ' . $installDate . $statusNote . '</label>' . PHP_EOL;
			
			// Dependencies
			$pluginEntry .=	$dependencies;
			
			// If admin plugin
			if($isActive && in_array($plugin, $this->adminPlugins))
				$pluginEntry .=	'<br />' . $this->getEditPluginButton($plugin);
			
			$pluginEntry .=	'</span>' . PHP_EOL;
			
			// Button panel
			$pluginEntry .=	'<span class="editButtons-panel" data-id="contextmenu-' . $k . '">' . PHP_EOL;
			
			$pluginEntry .=	'<span class="switchIcons">' . PHP_EOL;
			
			// Button publish
			$btnDefs	= array(	"type"		=> "button",
									"class"		=> 'activatePlugin button-icon-only',
									"value"		=> "",
									"title"		=> 'Plug-in <strong>' . $plugin . '</strong> {s_title:deactivate}',
									"attr"		=> 'data-url="' . SYSTEM_HTTP_ROOT . '/access/editPlugins.php?page=admin&action=activate&name=' . $plugin . '&active=0" data-title="Plug-in <strong>' . $plugin . '</strong> {s_title:deactivate}" data-publish="0" data-menuitem="true" data-id="item-id-' . $k . '"' . (!$isActive ? ' style="display:none;"' : ''),
									"icon"		=> "publish"
								);
				
			$pluginEntry .=	parent::getButton($btnDefs);

			// Button unpublish
			$btnDefs	= array(	"type"		=> "button",
									"class"		=> 'activatePlugin button-icon-only',
									"text"		=> "",
									"title"		=> 'Plug-in <strong>' . $plugin . '</strong> {s_title:activate}',
									"attr"		=> 'data-url="' . SYSTEM_HTTP_ROOT . '/access/editPlugins.php?page=admin&action=activate&name=' . $plugin . '&active=1" data-title="Plug-in <strong>' . $plugin . '</strong> {s_title:activate}" data-publish="1" data-menuitem="true" data-id="item-id-' . $k . '"' . ($isActive ? ' style="display:none;"' : ''),
									"icon"		=> "unpublish"
								);
				
			$pluginEntry .=	parent::getButton($btnDefs);
			
			$pluginEntry .=	'</span>' . PHP_EOL;
			
			// Button delete
			$btnDefs	= array(	"type"		=> "button",
									"class"		=> 'deletePlugin button-icon-only',
									"text"		=> "",
									"title"		=> 'Plug-in <strong>' . $plugin . '</strong> {s_title:del}',
									"attr"		=> 'data-action="del_plugin" data-url="' . SYSTEM_HTTP_ROOT . '/access/editPlugins.php?page=admin&action=delete&name=' . $plugin . '" data-name="' . $plugin . '" data-delstr="Plug-in <strong>' . $plugin . '</strong> {s_title:del}?" data-menuitem="true" data-id="item-id-' . $k . '" data-menutitle="Plug-in <strong>' . $plugin . '</strong> {s_title:del}"',
									"icon"		=> "delete"
								);
			
			$pluginEntry .=	parent::getButton($btnDefs);
			
			$pluginEntry .= '</span>' . PHP_EOL;
			
			$pluginEntry .=	'</li>' . PHP_EOL;
			
			$pluginsListStr	.= $pluginEntry;
			
			// Falls neues Plugin installiert
			if($this->pluginName == $plugin)
				$installedPlugin	= $pluginEntry;
			
			$k++;
		}

		// Anzahl Plugins
		$this->instPluginCnt	= --$k;
		
		
		$plugins   		.= 	self::getPluginsControlBar();
		
		
		if($pluginsListStr == "")
			$plugins   .=	'<p class="notice error empty">{s_notice:noplugins}</p><li>&nbsp;</li>';
		
		else {
		
			$plugins   .=	'<form action="' . SYSTEM_HTTP_ROOT . '/access/editPlugins.php?page=admin&array=1&action=" method="post" data-history="false">' . PHP_EOL;
			
			$plugins   .= 	self::getPluginsActionBar();
			
			$plugins   .= 	$pluginsListStr;
			
			$plugins   .= 	'</form>' . PHP_EOL;
		}
		
		

		// Notifications
		if($this->notice != "") {
			$this->adminContent .= '<p class="notice success">' . $this->notice . '</p>' . PHP_EOL;
		}
		$this->adminContent 	.= $this->getSessionNotifications("notice", true);
		$this->adminContent		.= $this->getSessionNotifications("error", true);

		
		// Falls neu installiert
		if($this->pluginInstalled) {
			if(!in_array($this->pluginName, $this->activePlugins))
				$this->adminContent .=	'<p class="hint">Plug-in <strong>' . $this->pluginName . '</strong> &#9658; <a href="" class="link" onclick="$(\'li[data-name=' . $this->pluginName . ']\').find(\'.activatePlugin\').click(); $(this).after(ln.setactive); $(this).parent().attr(\'class\',\'notice success\'); $(this).remove(); return false;"><u>{s_title:activate}</u></a></p>' . PHP_EOL;
			else
				$this->adminContent .=	'<p class="notice success">Plug-in <strong>' . $this->pluginName . '</strong> &#9658; {s_common:active}</p>' . PHP_EOL;
		}
		
		
		$showBackButton = false;
		
		
		// Plugin-Installieren/herunterladen
		$this->adminContent .=	'<h2 class="toggle cc-section-heading cc-h2">{s_header:newplugin}</h2>' . PHP_EOL;
		
		$this->adminContent .=	'<div class="adminBox">' . PHP_EOL .
								'<div class="controlBar">' . PHP_EOL .
								'<label>' . PHP_EOL .
								'<span class="searchPlugin">{s_text:getplugins}</span> &#9658;' . PHP_EOL;
		
		// Button plugin page
		$btnDefs	= array(	"href"		=> $this->pluginDownloadSite . $this->pluginDownloadPage,
								"class"		=> "gotoPluginPage",
								"text"		=> "Plugin-Website",
								"icon"		=> 'plugins',
								"attr"		=> 'target="_blank"'
							);
	
		$this->adminContent .=	parent::getButtonLink($btnDefs);

		$this->adminContent .=	'</label>' . PHP_EOL .
								'</div>' . PHP_EOL;
								
		$this->adminContent .=	'<div class="adminSection">' . PHP_EOL .
								'<form action="' . $formAction . '" method="post">' . PHP_EOL .
								'<label>Plug-in-URL</label>' . PHP_EOL;
		
		if(count($this->pluginError) > 0)
			$this->adminContent .= '<p class="notice error">' . implode("<br />", $this->pluginError) . '</p>' . PHP_EOL;
		
		
		if($this->pluginExists)
			$this->adminContent .= '<input type="hidden" name="overwritePlugin" value="true" />' . PHP_EOL;
		
		$this->adminContent .=	'<span class="singleInput-panel">' . PHP_EOL .
								'<input type="text" class="input-button-right" name="pluginUrl" value="' . htmlspecialchars($this->pluginUrl) . '" />' . PHP_EOL .
								'<input type="hidden" name="install_plugin" value="" />' . PHP_EOL .
								parent::getTokenInput() .
								'<span class="editButtons-panel">' . PHP_EOL;
		
		// Button new
		$btnDefs	= array(	"type"		=> "submit",
								"name"		=> "install_plugin",
								"class"		=> 'installPlugin ajaxSubmit button-icon-only',
								"value"		=> "",
								"text"		=> "",
								"title"		=> '{s_header:newplugin}',
								"icon"		=> "new"
							);
		
		$this->adminContent .=	parent::getButton($btnDefs);
								
		$this->adminContent .=	'</span>' . PHP_EOL .
								'</span>' . PHP_EOL .
								'</form>' . PHP_EOL .
								'</div>' . PHP_EOL;
		
		$this->adminContent .=	'</div>' . PHP_EOL;

		
		// Plugins auflisten
		$this->adminContent .=	'<h2 class="toggle cc-section-heading cc-h2">{s_header:instplugins} (' . $this->instPluginCnt . ' / ' . count($this->activePlugins) . ' {s_common:active})</h2>' . PHP_EOL .
								'<ul class="editList list list-condensed">' . $plugins . '</ul>' . PHP_EOL;

		
		// Contextmenü-Script
		$this->adminContent .=	$this->getContextMenuScript();


		$this->adminContent .=	'</div>' . PHP_EOL;



		// Zurückbuttons
		$this->adminContent .=	'<p>&nbsp;</p>' . PHP_EOL . 
								'<div class="adminArea">' . PHP_EOL . 
								'<ul><li class="submit back">' . PHP_EOL;

		if($showBackButton) {
		
			// Button back
			$btnDefs	= array(	"href"		=> $formAction,
									"class"		=> "left",
									"text"		=> "{s_button:back}",
									"icon"		=> "backtolist"
								);
			
			$this->adminContent	.=	parent::getButtonLink($btnDefs);
		}

		// Button back to main
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
	 * Liest Listen Sortierungs- und Filterparameter aus
	 * 
	 * @access public
	 */
	public function evalListParams()
	{
		
		if(!empty($GLOBALS['_GET']['sort_entries']))
		
			$this->sortList = $GLOBALS['_GET']['sort_entries'];

		
		
		if(!empty($GLOBALS['_POST']['sort_entries'])) {
			
			$this->sortList = $GLOBALS['_POST']['sort_entries'];
		
		}
		
		
		switch($this->sortList) {
				
			case "nameasc":
				$this->sortRes	 	= defined('SCANDIR_SORT_ASCENDING') ? SCANDIR_SORT_ASCENDING : 2;
				$this->sortOrder	= "asc";
				$this->sortBy		= "name";
				break;
				
			case "namedsc":
				$this->sortRes 		= defined('SCANDIR_SORT_DESCENDING') ? SCANDIR_SORT_DESCENDING : 2;
				$this->sortOrder	= "dsc";
				$this->sortBy		= "name";
				break;
			
			case "dateasc":
				$this->sortRes 		= defined('SCANDIR_SORT_ASCENDING') ? SCANDIR_SORT_ASCENDING : 2;
				$this->sortOrder 	= "asc";
				$this->sortBy		= "date";
				break;
				
			case "datedsc":
				$this->sortRes 		= defined('SCANDIR_SORT_DESCENDING') ? SCANDIR_SORT_DESCENDING : 2;
				$this->sortOrder	= "dsc";
				$this->sortBy		= "date";
				break;
		}
		
		
		if(!empty($GLOBALS['_POST']['filter_active'])) {
			
			$this->pubFilter = $GLOBALS['_POST']['filter_active'];
			
		}		
		elseif(!empty($GLOBALS['_GET']['filter_active'])) {
		
			$this->pubFilter = $GLOBALS['_GET']['filter_active'];
		
		}
		elseif(!empty($this->g_Session['filter_active'])) {
			
			$this->pubFilter = $this->g_Session['filter_active'];
		
		}

		$this->setSessionVar('filter_active', $this->pubFilter);
		
		
		if($this->pubFilter != "all") {
			
			$this->filter = "WHERE `active` = " . ($this->pubFilter == "active" ? "1" : "0");
		
		}
	
	}
	
	

	/**
	 * getNewslControlBar
	 * @access protected
	 */
	protected function getPluginsControlBar()
	{
	
		$output		 =	'<div class="controlBar">' . PHP_EOL .
						'<form name="sort" action="' . $this->formAction . '" method="post">' . PHP_EOL . 
						'<div class="sortOption small left"><label>{s_label:sort}</label>' . PHP_EOL .
						'<select name="sort_entries" class="listSort" data-action="autosubmit">' . PHP_EOL;
					
		$sortOptions = array("nameasc" => "{s_option:nameasc}",
							 "namedsc" => "{s_option:namedsc}",
							 "datedsc" => "{s_option:datedsc}",
							 "dateasc" => "{s_option:dateasc}"
							 );
		
		foreach($sortOptions as $key => $value) { // Sortierungsoptionen auflisten
			
			$output		 .='<option value="' . $key . '"';
			
			if($key == $this->sortList)
				$output		 .=' selected="selected"';
				
			$output		 .= '>' . $value . '</option>' . PHP_EOL;
		
		}
							
		$output		 .= 	'</select></div>' . PHP_EOL;
		
							
		$output		 .= '<div class="filterOption left"><label for="all">{s_label:all}</label>' . PHP_EOL .
						'<label class="radioBox markBox">' . PHP_EOL .
						'<input type="radio" name="filter_active" id="all" value="all"' . ($this->pubFilter == "all" ? ' checked="checked"' : '') . ' data-action="filterlist" />' . PHP_EOL .
						'</label>' . PHP_EOL .
						'</div>' . PHP_EOL .
						'<div class="filterOptions cc-table-cell">' . PHP_EOL .
						'<div class="filterOption left"><label for="filter_active">{s_common:active}</label>' . PHP_EOL .
						'<label class="radioBox markBox">' . PHP_EOL .
						'<input type="radio" name="filter_active" id="filter_active" value="active"' . ($this->pubFilter == "active" ? ' checked="checked"' : '') . ' data-action="filterlist" />' . PHP_EOL .
						'</label>' . PHP_EOL .
						'</div>' . PHP_EOL .
						'<div class="filterOption left"><label for="filter_inactive">{s_common:inactive}</label>' . PHP_EOL .
						'<label class="radioBox markBox">' . PHP_EOL .
						'<input type="radio" name="filter_active" id="filter_inactive" value="inactive"' . ($this->pubFilter == "inactive" ? ' checked="checked"' : '') . ' data-action="filterlist" />' . PHP_EOL .
						'</label>' . PHP_EOL .
						'</div>' . PHP_EOL;
		
		// Suchfunktion
		$output 	.=	'<div class="labelBox left"><label>{s_label:searchfor}</label>' .
						'<span class="singleInput-panel">' . PHP_EOL .
						'<input type="text" name="listSearch" class="listSearch input-button-right" value="' . htmlspecialchars($this->pluginSearch) . '" />' . PHP_EOL .
						'<span class="editButtons-panel">' . PHP_EOL;

		// Button new
		$btnDefs	= array(	"type"		=> "button",
								"name"		=> "reset_search_val",
								"class"		=> 'resetSearchVal button-icon-only',
								"value"		=> "true",
								"text"		=> "",
								"title"		=> '{s_label:reset}',
								"icon"		=> "close"
							);
		
		$output 	.=	parent::getButton($btnDefs);
								
		$output 	.=	'</span>' . PHP_EOL .
						'</span>' . PHP_EOL .
						'</div>' . PHP_EOL;
		
		$output 	.=	'</div>' . PHP_EOL;
					
		$output		 .=	parent::getTokenInput() .
						'</form>' . PHP_EOL .
						'<br class="clearfloat" /></div>' . PHP_EOL;

		
		
		// Falls Gruppenfilter oder Abofilter, Filter löschen Button einfügen
		if((!empty($this->pubFilter) && $this->pubFilter != "all")) {
			
			$filter		= '<strong>{s_common:' . ($this->pubFilter == "active" ? '' : 'in') . 'active}' . '</strong>';
			
			
			$output .=	'<span class="showHiddenListEntries actionBox cc-hint" onclick="$(\'.showHiddenListEntries\').each(function(){ $(this).hide(); $(this).siblings(\'.controlBar\').find(\'input.listSearch\').val(\'\').focus().trigger({ type :\'keyup\', which : 8 }).val(\'\'); });">{s_label:filter}: ' . $filter;
			
			
			// Filter icon
			$output .=	'<span class="listIcon">' . PHP_EOL .
						parent::getIcon("filter", "inline-icon") .
						'</span>' . "\n";
			
			$output .=	'<span class="editButtons-panel">' . PHP_EOL;
			
			$output .=	'<form action="'.$this->formAction.'" method="post">' . PHP_EOL;
			
			// Button remove filter
			$btnDefs	= array(	"type"		=> "submit",
									"class"		=> 'removefilter ajaxSubmit button-icon-only',
									"title"		=> '{s_title:removefilter}',
									"icon"		=> "close"
								);
			
			$output .=	parent::getButton($btnDefs);
						
			$output .=	'<input type="hidden" value="all" name="filter_active">' . PHP_EOL .
						'</form>' . PHP_EOL .
						'</span>' . PHP_EOL .
						'</span>' . PHP_EOL;
		}

		return $output;
	
	}
	
	

	/**
	 * getPluginsActionBar
	 * @access protected
	 */
	protected function getPluginsActionBar()
	{

		// Checkbox zur Mehrfachauswahl zum Löschen und Publizieren
		$output =	'<div class="actionBox">' .
					'<label class="markAll markBox"><input type="checkbox" id="markAllLB" data-select="all" /></label>' .
					'<label for="markAllLB" class="markAllLB"> {s_label:mark}</label>' . PHP_EOL .
					'<span class="editButtons-panel">' . PHP_EOL;
		
		// Button publish
		$btnDefs	= array(	"type"		=> "submit",
								"class"		=> 'pubAll pubPlugins button-icon-only',
								"text"		=> "",
								"title"		=> '{s_title:pubplugins}',
								"icon"		=> "publish"
							);
		
		$output .=	parent::getButton($btnDefs);
		
		// Button unpublish
		$btnDefs	= array(	"type"		=> "submit",
								"class"		=> 'pubAll pubPlugins unpublish button-icon-only',
								"text"		=> "",
								"title"		=> '{s_title:unpubplugins}',
								"icon"		=> "unpublish"
							);
		
		$output .=	parent::getButton($btnDefs);
		
		// Button delete
		$btnDefs	= array(	"type"		=> "submit",
								"class"		=> 'delAll delPlugins button-icon-only',
								"text"		=> "",
								"title"		=> '{s_title:delmarked}',
								"attr"		=> 'data-action="delmultiple"',
								"icon"		=> "delete"
							);
		
		$output .=	parent::getButton($btnDefs);
						
		$output .=	'</span>' .
					'</div>' . PHP_EOL;
		$output .=	'<script type="text/javascript">' . "\n" .
					'head.ready("jquery",function(){
						$(document).ready(function(){' .
							'$(".resetSearchVal").bind("click", function(){
								$(this).closest(".singleInput-panel").children(".listSearch").val("").focus().trigger("keyup").blur();
							});' .
						'});' . "\n" .
					'});' . "\n" .
					'</script>' . "\n";
		
		return $output;
	
	}
	
	

	/**
	 * scanPluginDir
	 * @access protected
	 */
	protected function scanPluginDir($dir, $sortKey, $sortBy, $sortOrder) {
	
		$files	= array();
				
		$dirCon		= scandir($dir, $sortKey);
		$i			= 0;
		
		foreach ($dirCon as $file) {
		
			if(strpos($file, ".") === 0
			|| !is_dir($dir . '/' . $file)
			)
				continue;
			
			if($sortBy == "date")
				$files[$file]	= filemtime($dir . '/' . $file);
			else
				$files[$i] 		= $file;
			
			$i++;			
		}

		if($sortOrder == "dsc")
			arsort($files);
		else
			asort($files);
		
		if($sortBy == "date")
			$files = array_keys($files);

		return $files;
	
	}	
	

	/**
	 * verifyPluginUrl
	 * @access protected
	 */
	protected function verifyPluginUrl($url)
	{
		if($url == "") {
			$this->pluginError[] = "{s_error:fill}";
			return false;
		}

		if(strpos($url, $this->pluginDownloadSite . '_doc-') === false) {
			$this->pluginError[] = "{s_error:check}";
			return false;
		}
		
		// Url prüfen
		if(!$this->checkUrlExists($url)) {
			$this->pluginError[] = "{s_error:check}";
			return false;
		}
		return true;
	}
	
	

	/**
	 * checkInstallPlugin
	 * @access protected
	 */
	protected function checkInstallPlugin($checkPl)
	{
	
		foreach($this->installedPlugins as $plugin => $pluginDetails) {
		
			if($plugin == $checkPl)
				return true;
		}
		
		return false;
	}
	
	

	/**
	 * installPluginFile
	 * @access protected
	 */
	protected function installPluginFile($url)
	{
		
		$dnlOK	= false;
		
		// Datei herunterladen
		if($this->downloadPluginFile())
			$dnlOK	= true;
		
		// Wartungsmodus einschalten
		if(!$this->activateMaintenanceMode()) {
			$this->pluginError[]	= '{s_error:mtmodeon}';
			return false;
		}
		
		if($dnlOK)
			$this->unzipPluginFile();
		
		if(count($this->pluginError) === 0) {
			$this->pluginInstalled	= true;
			$this->notice			= "{s_notice:plugininst}<strong>" . $this->pluginName . "</strong>";
			$this->pluginUrl		= "";
			$maintenanceOff			= $this->inactivateMaintenanceMode(); // Wartungsmodus deaktivieren
			parent::deleteTempDir();			
								
			// Falls Fehler beim Abschalten des Wartungsmodus
			if(is_array($maintenanceOff))
				$this->pluginError	= array_merge($this->updError, $maintenanceOff);

			return true;
		}
		return false;
	}
	
	

	/**
	 * checkUrlExists
	 * @access protected
	 */
	protected function checkUrlExists($url)
	{
		
		// Set to head method for faster stream
		$context = stream_context_create(
			array(
				'http' => array(
					'method' => 'HEAD',
					'timeout' => 5
				)
			)
		);
		
		// Suppress error if update server or file not available
		$errorlevel	= error_reporting();
		error_reporting(0);
		
		$headers	= @file_get_contents($url, false, $context);
		
		error_reporting($errorlevel);
		
		// Update-Datei herunterladen
		if($headers !== false
		&& !empty($http_response_header)
		&& isset($http_response_header[0])
		&& stripos($http_response_header[0], "OK") !== false
		)
			return true;
		
		return false;

	}
	
	

	/**
	 * checkDownloadExists
	 * @access protected
	 */
	protected function checkDownloadExists($plDir)
	{
		
		// Plugin-Datei herunterladen
		if(is_dir($plDir)) {
			$this->pluginExists = true;
			return true;
		}		
		return false;

	}
	
	

	/**
	 * downloadPluginFile
	 * @access protected
	 */
	protected function downloadPluginFile()
	{
		
		// Temp-Verzeichnis erstellen
		parent::makeTempDir();

		// Plugin-Datei herunterladen
		if($pluginFile = @file_get_contents($this->pluginUrl)) {
			if(@file_put_contents($this->archive, $pluginFile)) {
				$this->pluginSuccess[]	=	'{s_notice:downloadupdok}';			
				return true;
			}
			$this->pluginError[]	=	'Error putting contents: ' . $this->archive;			
			return false;
		}		
		$this->pluginError[]	=	'{s_error:downloadupdfile}<br />' . $this->pluginUrl;			
		return false;

	}
	


	/**
	 * unzipPluginFile
	 * @access private
	 */
	private function unzipPluginFile()
	{
		
		// Verzeichnis entpacken
		require_once PROJECT_DOC_ROOT."/inc/classes/ZipArchive/class.UnZip.php"; // Klasse UnZip einbinden
		
		$o_unzip	= new UnZip();
		
		$result		= $o_unzip->unZip($this->pluginFile);
		
		if(count($result['success']) > 0) {
			$this->pluginSuccess[]	=	implode("<br />", $result['success']);
		}
		if(count($result['error']) > 0) {
			$this->pluginError[]	=	implode("<br />", $result['error']);
			return false;
		}
		else
			return true;
	
	}
	


	/**
	 * getEditPluginButton
	 * @access private
	 */
	private function getEditPluginButton($plugin)
	{
		
		$output	= "";
			
		// Button remove filter
		$btnDefs	= array(	"href"		=> ADMIN_HTTP_ROOT . '?task=' . $plugin,
								"class"		=> 'editPlugin',
								"text"		=> "{s_header:editplugin}",
								"icon"		=> "edit"
							);
		
		$output .=	parent::getButtonLink($btnDefs);
		
		return $output;
	
	}

}
