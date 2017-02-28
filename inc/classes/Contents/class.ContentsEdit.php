<?php
namespace Concise;


require_once PROJECT_DOC_ROOT."/inc/classes/Contents/class.ContentsEngine.php"; // ContentsEngine einbinden
require_once PROJECT_DOC_ROOT."/inc/classes/Contents/class.Contents.php"; // Contents einbinden

/**
 * Datenbankinhalte
 * 
 */
class ContentsEdit extends Contents
{

	private $fetchCache = false;
	protected $editDetailsTpls = array();
	
	/**
	 * Liefert die Datenbankinhalte zur aktuellen Seite
	 * 
	 * @access	public
	 * @param	object	$DB			DB-Objekt
	 * @param	object	$o_lng		Sprachobjekt
	 * @param	boolean	$ajax		Aufruf via Ajax
	 * @param	string	$ajaxAction	Ajax Action Parameter
	 */
	public function __construct($DB, $o_lng, $ajax = false, $ajaxAction = "")
	{	
	
		// ContentsEngine Contructor
		parent::__construct($DB, $o_lng, $ajax, $ajaxAction);
		
		$this->fetchCache = $this->checkFetchCache();
	
	}


	/**
	 * FE-Status überprüfen
	 *
	 * @access	public
     * @return  string
	 */
	public function getFrontEndAccess()
	{

		// Falls die Theme-Auswahl für das Frontend eingebunden werden soll
		$this->checkFrontEndModes();


		// Falls die Theme-Auswahl für das Frontend eingebunden werden soll
		$themeSelection = $this->selectFrontEndTheme();


		// Falls ein Admin oder Editor eingeloggt ist, überprüfen, ob Seitenvorschau im FE-Editing-Modus
		// Fehlermeldung ausgeben
		if(isset($this->g_Session['error'])) {
			
			$feNot				= parent::replaceStaText(str_replace("<br>", "\\r\\n", $this->g_Session['error']));
			$this->scriptCode[]	= "$.doAlert('".$feNot."\\r\\n\\r\\n"."');";
			$this->unsetSessionKey('error');
		}


		// Falls Systemseite, FE-Editing-Modus aus
		if(!$this->pageId > 0)
			parent::$feMode = false;
			

		// FE-Editing-Modus an
		if(parent::$feMode === true) {
			
			$this->setFrontEndAccess();
			
		}


		// Falls die Theme-Auswahl für das Frontend eingebunden werden soll
		if($themeSelection && !$this->fetchCache) {
		
			// Theme-Galerie
			$this->scriptFiles["carousel"]	= "extLibs/jquery/owl-carousel/owl.carousel.min.js";
			$this->scriptFiles["themesel"]	= "system/access/js/themeSelection.js"; // themeSelection.js einbinden
			$this->cssFiles[]				= "extLibs/jquery/owl-carousel/owl.carousel.min.css";
			$this->cssFiles[]				= "extLibs/jquery/owl-carousel/owl.theme.css";
		}
		
		// editorlog js
		$this->scriptFiles["alerts"]		= "extLibs/jquery/alerts/jquery.alerts.min.js";

		// editorlog css
		$this->cssFiles[]					= "access/css/normalize_admin.css";
		$this->cssFiles[]					= "extLibs/jquery/alerts/jquery.alerts.css";
		$this->cssFiles[]					= "system/themes/" . ADMIN_THEME . "/css/editorlog.min.css";
		$this->cssFiles[]					= "system/themes/" . ADMIN_THEME . "/css/icons.min.css";
		
		// editorlog script code
		$this->scriptCode[]					= $this->getFETourScript();

	}
	

	/**
	 * checkFrontEndModes
	 *
	 * @access	public
     * @return  string
	 */
	public function checkFrontEndModes()
	{

		// Falls frontend-Editing aktiviert ist
		if(isset($GLOBALS['_GET']['fe']) && $GLOBALS['_GET']['fe'] == "onlocked" && !$this->adminPage) {
			$feNot				= str_replace("<br />", "\\r\\n", parent::replaceStaText('{s_error:felock}<br /><br />{s_notice:felock}'));
			$this->scriptCode[] = "$.doAlert('" . $feNot . "\\r\\n\\r\\n"."');";
		}
		elseif(isset($GLOBALS['_GET']['fe']) && $GLOBALS['_GET']['fe'] == "off") {
			setcookie("feMode", "off", time()+60*60*24*180, "/");
		}
		elseif(!empty($GLOBALS['_GET']['fe']) && !$this->adminPage) {
			setcookie("feMode", "on", time()+60*60*24*180, "/");
			parent::$feMode = true;
		}
		elseif((isset($GLOBALS['_COOKIE']['feMode']) && $GLOBALS['_COOKIE']['feMode'] == "on") && !$this->adminPage) {
			parent::$feMode = true;
		}

		// Falls das Ersetzen der Platzhalter für Sprachbausteine ausgeschaltet werden soll
		if(isset($GLOBALS['_GET']['ph']) && isset($GLOBALS['_GET']['pageid'])) {

			if($GLOBALS['_GET']['ph'] == "off" && isset($this->g_Session['showStaTextKeys'])) {
				$this->unsetSessionKey('showStaTextKeys');
				header("Location:" . PROJECT_HTTP_ROOT . "/" . HTML::getLinkPath($GLOBALS['_GET']['pageid']));
				exit;
			}
			elseif(isset($GLOBALS['_GET']['ph']) && $GLOBALS['_GET']['ph'] == "on") {
				$this->setSessionVar('showStaTextKeys', true);
				header("Location:" . PROJECT_HTTP_ROOT . "/" . HTML::getLinkPath($GLOBALS['_GET']['pageid']));
				exit;
			}
		}
		if(isset($this->g_Session['showStaTextKeys']) &&  !$this->adminPage)
			parent::$phMode = true;
	}
	

	/**
	 * selectFrontEndTheme
	 *
	 * @access	public
     * @return  string
	 */
	public function selectFrontEndTheme()
	{

		// Falls die Theme-Auswahl für das Frontend eingebunden werden soll
		if(FE_THEME_SELECTION 
		&& !$this->adminPage
		) {
					
			// Falls ein Theme ausgewählt wurde
			if(isset($GLOBALS['_POST']['currTheme']) 
			&& $GLOBALS['_POST']['currTheme'] != ""
			) {

				$oldTheme	= THEME;
				$currTheme	= $GLOBALS['_POST']['currTheme'];
				$currURL	= $GLOBALS['_POST']['currURL'];
			
				if(!$settings = @file_get_contents(PROJECT_DOC_ROOT . '/inc/settings.php')) die("settings file not found");
				else {
					
					$replace = preg_replace("/'THEME',\"$oldTheme\"/", "'THEME',\"$currTheme\"", $settings);
												
					if(!@file_put_contents(PROJECT_DOC_ROOT . '/inc/settings.php', $replace)) {
						@file_put_contents(PROJECT_DOC_ROOT . '/inc/settings.php', $settings);
						die("could not write settings file");
					}
					else {
						$this->setSessionVar('fe-notice', parent::replaceStaText('{s_notice:themeactive}') . ' ' . $currTheme);
						header("Location:" . $currURL);
						exit;
					}
				}
			}
		
			return true;
		}

		return false;
	
	}
	

	/**
	 * setFrontEndAccess
	 *
	 * @access	public
     * @return  string
	 */
	public function setFrontEndAccess()
	{

		// Falls das Https-Protokoll für Systemseiten aktiviert ist, überprüfen ob es aktuell verwedent wird.
		// Falls nicht, auf Https-Protokoll umstellen (u.a. damit listBox aus dem FE-Editing angezeigt wird
		if(ADMIN_HTTPS_PROTOCOL && strpos(PROJECT_HTTP_ROOT, "http://") !== false) {
			header("Location:" . str_replace("http://", "https://", parent::$currentURL));
			exit;
		}
		
		require_once PROJECT_DOC_ROOT."/inc/classes/Admin/class.Admin.php"; // Adminklasse einbinden
		
		
		// Admin-Theme-Defaults bereitstellen
		$this->adminThemeConf	= $this->getThemeDefaults("admin", false);
		
		
		// Locking checken (genPage)
		// Locking checken
		$LOCK	= new Locks(600, $this->DB);
		
		$locked = $this->checkContentTableLock($LOCK, $this->o_security->get('loggedUser'));
		

		// auf generelle/Seiten- Sperre prüfen
		$genLock	= $LOCK->readLock("langs", "all");
		$pageLock	= $LOCK->readLock("editpages", "all");

		// Falls Lang-Lock durch User besteht, aber keine Sprachverwaltung mehr stattfindet, Sperre löschen
		if($genLock[0] == true && $genLock[1]['lockedBy'] == $this->g_Session['username'])
			$LOCK->deleteAllUserLocks($this->g_Session['username'], "langs");
		
		elseif($genLock[0] == true)
			$locked[0] = true;
		
		// Falls Pages-Lock durch User besteht, aber keine Seitenbaumverwaltung mehr stattfindet, Sperre löschen
		if($pageLock[0] == true && $pageLock[1]['lockedBy'] == $this->g_Session['username'])
			$LOCK->deleteAllUserLocks($this->g_Session['username'], "editpages");
		
		elseif($pageLock[0] == true)
			$locked[0] = true;
		
		if($locked[0] == true || $locked[1] == false) {
			
			$this->feModeStatus	= "onlocked";
			parent::$feMode		= false; 
		}

		
		//Veraltete Einträge löschen
		$LOCK->deleteOldLocks();
	
		if(parent::$feMode) {
		
			$this->feModeStatus		= "off";
			parent::$loopTemplate	= "contents_edit.tpl";
			parent::$newElement		= $this->listContentTypes();
		
		}
		
		$this->contents[]			= '<noscript><p class="noScriptWarning error">{s_notice:noscript}</p></noscript>';
		#$this->scriptCode["jsvars"] = $this->setScriptVars(true, true, parent::$staText['javascript']);
		
		$jsFW		= isset($this->adminThemeConf["jsframework"]["framework"]) ? $this->adminThemeConf["jsframework"]["framework"] : "jquery";
		$jsFWV		= isset($this->adminThemeConf["jsframework"]["version"]) ? $this->adminThemeConf["jsframework"]["version"] : JQUERY_VERSION_ADMIN;
		$jsUIV		= isset($this->adminThemeConf["jsframework"]["uiversion"]) ? $this->adminThemeConf["jsframework"]["uiversion"] : JQUERY_UI_VERSION_ADMIN;
		$jsUIT		= isset($this->adminThemeConf["jsframework"]["uitheme"]) ? $this->adminThemeConf["jsframework"]["uitheme"] : JQUERY_UI_THEME_ADMIN;
	
		// Script-Dateien
		#$this->scriptFiles["modernizr"]	= "extLibs/modernizr/modernizr.min.js";
		#$this->scriptFiles["yepnope"]		= "extLibs/yepnope/yepnope.min.js";
		$this->scriptFiles["cookie"]		= "system/access/js/cookie.min.js";
		$this->scriptFiles["ui"]			= "extLibs/jquery/ui/jquery-ui-" . $jsUIV . ".custom.min.js";
		$this->scriptFiles["alerts"]		= "extLibs/jquery/alerts/jquery.alerts.min.js";
		$this->scriptFiles["tooltips"]		= "system/access/js/tooltips.min.js";
		$this->scriptFiles["customboxl"]	= "extLibs/jquery/custombox/legacy.min.js";
		$this->scriptFiles["custombox"]		= "extLibs/jquery/custombox/custombox.min.js";
		$this->scriptFiles["pimg"]			= "extLibs/jquery/pimg/pimg.min.js";

		
		if(EDITOR_VERSION == 3) {
			$this->scriptFiles[]		= "extLibs/tiny_mce3/tiny_mce.js";
			#$this->scriptFiles[]		= "extLibs/tiny_mce3/jquery.tinymce.js";
			$this->scriptFiles[]		= "system/access/js/myTinyMCE3.fe.js";
		}
		else {
			$this->scriptFiles["editor"]		= "extLibs/tinymce/tinymce.min.js";
			#$this->scriptFiles[]				= "extLibs/tinymce/jquery.tinymce.min.js";
			$this->scriptFiles["editorfe"]		= "system/access/js/myTinyMCE.fe.js";
			$this->scriptFiles["filebrowser"]	= "system/access/js/myFileBrowser.js";
		}

		$this->scriptFiles["sort"]			= "system/access/js/adminSort.min.js";
		
		$this->scriptFiles["history"]		= "extLibs/jquery/history/jquery.history.min.js";
		$this->scriptFiles["admin"]			= "system/access/js/admin.min.js";
		$this->scriptFiles["feedit"]		= "system/access/js/feEdit.min.js";
		$this->cssFiles[]					= "extLibs/jquery/alerts/jquery.alerts.css";
		$this->cssFiles[]					= "extLibs/jquery/custombox/custombox.min.css";
		$this->cssFiles[100]				= "extLibs/jquery/ui/" . $jsUIT . "/jquery-ui-" . $jsUIV . ".custom.min.css";
		$this->cssFiles[101]				= "system/themes/" . ADMIN_THEME . "/css/admin.min.css";
		$this->cssFiles[102]				= "system/themes/" . ADMIN_THEME . "/css/fe-edit.min.css";
		#$this->cssFiles[103]				= STYLES_DIR . "fe-edit.min.css"; // Ggf. Theme-spezifische Styles
	
		
		// FE-File-Uploader
		$this->getFrontEndFileUploader();
		
		
		return true;
	
	}
	

	/**
	 * getFrontEndFileUploader
	 *
	 * @access	public
     * @return  string
	 */
	public function getFrontEndFileUploader()
	{

		require_once PROJECT_DOC_ROOT . "/inc/classes/Media/class.FileUploaderFactory.php"; // FileUploader-Factory einbinden

		$this->uploadMethod			= Files::getUploadMethod();
		$this->allowedFiles			= Files::getAllowedFiles();
		$this->allowedFileSizeStr	= Files::getFileSizeStr(Files::getAllowedFileSize());

		// Element-Options
		$options	= array(	"allowedFiles"			=> $this->allowedFiles,
								"allowedFileSizeStr"	=> $this->allowedFileSizeStr,
							);


		// FileUploader
		try {
			
			// FileUploader-Instanz
			$o_uploader	= FileUploaderFactory::create($this->uploadMethod, $options, $this->DB, $this->o_lng);
			$o_uploader->assignHeadFiles("fe");
			$this->mergeHeadCodeArrays($o_uploader);
		}

		// Falls Element-Klasse nicht vorhanden
		catch(\Exception $e) {
			$this->adminContent = $this->backendLog ? $e->getMessage() : "";
			return $this->adminContent;
		}
	
	}
	

	/**
	 * checkContentTableLock
	 *
	 * @access	public
     * @return  string
	 */
	public function checkContentTableLock($LOCK, $user)
	{

		$locked		= false;
		$setLock	= array(true);
		$mainTable	= str_replace("_preview", "", parent::$tableContents);

		foreach(parent::$contentTables as $conTable) {
		
			if($conTable == $mainTable)
				$rowID = $this->pageId;
			else
				$rowID = $this->currentTemplate;
			
			$readLock = $LOCK->readLock($rowID, $conTable);

			if($readLock[0] == true && $readLock[1]['lockedBy'] != $user && $rowID == $this->pageId) {
				$setLock[0] = false;
				$locked = true;
			}
			
			$LOCK->deleteAllUserLocks($user, $conTable);
			
			if($locked == false)
				$setLock = $LOCK->setLock($rowID, $conTable, $user);
			
		}
		return array($locked, $setLock[0]);
	
	}
	

	/**
	 * checkFetchCache überprüfen
	 *
	 * @access	public
     * @return  string
	 */
	public function checkFetchCache()
	{
	
		return isset($GLOBALS['_GET']['fetchcache']);
	}
	

	/**
	 * getFileUploadBox
	 *
	 * @access	public
     * @return  string
	 */
	public function getFileUploadBox($type, $conArea, $conNr)
	{

		require_once PROJECT_DOC_ROOT . "/inc/classes/Media/class.FileUploaderFactory.php"; // FileUploader-Factory einbinden
		
		$uploadMethod			= $this->uploadMethod != "uploadify" ? "plupload" : $this->uploadMethod;
		$allowedFileSizeStr		= Files::getFileSizeStr(Files::getAllowedFileSize());
		$allowedFiles			= Files::getAllowedFiles();
		$uploadMethod			= $this->uploadMethod != "uploadify" ? "plupload" : $this->uploadMethod;
		$options				= array();
		$output					= "";
		
		$options["allowedFiles"]		= $allowedFiles;
		$options["allowedFileSizeStr"]	= $allowedFileSizeStr;
		
		if($type == "files"
		&& $folder != ""
		) {
			$options["folder"]			= $folder;
			$options["useFilesFolder"]	= true;
		}

		
		// FileUploader
		try {
			
			// FileUploader-Instanz
			$o_uploader		= FileUploaderFactory::create($uploadMethod, $options, $this->DB, $this->o_lng);
			//$o_uploader->assignHeadFiles();
			$uploadMask		= $o_uploader->getUploaderMask("fe", $type, $conArea.$conNr);		
			$uploadScript	= $o_uploader->getUploadScript('#myUploadBox-fe-' . $conArea.$conNr, $type);		
			$output			= $uploadMask.$uploadScript;
			
		}
		// Falls Element-Klasse nicht vorhanden
		catch(\Exception $e) {
			$output = $this->backendLog ? $e->getMessage() : "";
		}
		
		return $output;
	
	}
	

	// getFeEditPageMenu
	protected function getFeEditPageMenu()
	{
		
		// Theme select js code
		$this->scriptCode[]	= $this->getTplSelectScriptCode();

		// Dropdown menu
		$output =	'<div class="dropdown {t_class:btngroup} {t_class:btngroupxs}">' . PHP_EOL;
	
		// Button link edit page
		$btnDefs	= array(	"href"		=> ADMIN_HTTP_ROOT . '?task=edit&edit_id='.$this->pageId,
								"text"		=> '{s_link:editpage}',
								"class"		=> "account admin gotoEditPage {t_class:btndef}",
								"title"		=> "{s_title:editpage}",
								"attr"		=> $this->loadViaAjax,
								"icon"		=> "editpage"
							);
		
		$output .=	self::getButtonLink($btnDefs);
	
		// Button dropdown
		$btnDefs	= array(	"type"		=> "button",
								"class"		=> "{t_class:btndef} {t_class:ddtoggle}",
								"value"		=> "",
								"text"		=> '&nbsp;<span class="{t_class:caret}"></span>&nbsp;' ."\n",
								"title"		=> "{s_title:editpage}",
								"attr"		=> 'data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" data-context="navbar"',
								"icon"		=> "togglegrid"
							);
		
		$output .=	self::getButton($btnDefs) . "\n";
		
		// Dropdownmenu
		$output	.=	'<ul class="{t_class:ddmenu}">' . PHP_EOL;
		
		$output	.=	'<li><a class="{t_class:ddtoggle}" href="' . ADMIN_HTTP_ROOT . '?task=edit&edit_id=' . $this->pageId . '">{s_conareas:contents_main}</a></li>' . PHP_EOL;
		
		$output	.=	'<li class="{t_class:divider}" role="separator"></li>' . PHP_EOL;
		
		foreach(self::$areasTplContents as $area) {
			$output	.=	'<li><a href="' . ADMIN_HTTP_ROOT . '?task=tpl&type=edit&edit_id=' . $this->currentTemplate . '&area=contents_' . $area . '">{s_conareas:contents_' . $area . '}</a></li>' . PHP_EOL;
		}
		
		$output	.=	'<li class="{t_class:divider}" role="separator"></li>' . PHP_EOL;
		
		// Templates auflisten
		// Existing Tempates
		$this->existTemplates	= Admin::readTemplateDir();

		$output	.=	'<li class="{t_class:ddsubmenu}">' . PHP_EOL .
					'<a href="#" aria-expanded="false" role="button" data-toggle="cc-layoutSelectionBox" data-context="account">{s_label:layout}&nbsp;<span class="{t_class:caret}"></span>&nbsp;</a>' . PHP_EOL;
		
		$output	.=	'<form action="' . SYSTEM_HTTP_ROOT . '/access/editPages.php?page=admin&amp;action=changetpl&page_id=' . $this->pageId . '&amp;red=' . urlencode(parent::$currentURL) . '" method="post" data-ajax="false">' . PHP_EOL;
		
		$output	.=	Admin::listTemplates($this->currentTemplate, $this->defaultTemplates, $this->existTemplates, "select") . PHP_EOL;
		
		$output	.=	'<ul id="cc-layoutSelectionBox" class="imagePicker {t_class:ddmenu}">' . PHP_EOL .
					'</ul>' . PHP_EOL;							
		
		$output	.=	'</form>' . PHP_EOL;
		
		$output	.=	'</li>' . PHP_EOL;
		$output	.=	'</ul>' . PHP_EOL;
		$output .=	'</div>' . PHP_EOL;

		$output .=	'</div>' . PHP_EOL;
		$output .=	'<div class="{t_class:btngroup} {t_class:btngroupxs}">' . PHP_EOL;
		
		return $output;
	
	}
	

	// getTplSelectScriptCode
	protected function getTplSelectScriptCode()
	{

		return	'head.ready("jquery", function(){' . PHP_EOL .
				'head.load({imagepickercss: "' . PROJECT_HTTP_ROOT . '/extLibs/jquery/image-picker/image-picker.css"});' . PHP_EOL .
				'head.load({imagepicker: "' . PROJECT_HTTP_ROOT . '/extLibs/jquery/image-picker/image-picker.min.js"});' . PHP_EOL .
				'head.ready("imagepicker", function(){' . PHP_EOL .
					'$(document).ready(function(){' . PHP_EOL .
						'$("#accountMenu select.tplSelect").imagepicker({
							target_box: $("#cc-layoutSelectionBox"),
							hide_select: true,
							show_label: false,
							limit: undefined,
							initialized: function(){
								$("#cc-layoutSelectionBox ul li").each(function(i,e){
									var title	= $("#accountMenu select.tplSelect").children(":nth-child(" + (i+1) + ")").attr("data-title");
									$(this).attr("title", title);
								});
							},
							changed: function(oldVal, newVal){
								// autosubmit
								var elem	= $(this);
								if(cc.conciseChanges){
									e.preventDefault();
									jConfirm(ln.savefirst, ln.confirmtitle, function(result){													
										if(result === true){										
											$.submitViaAjax(elem);
										}else{
											elem.val(newVal);
										}
									});
									return false;
								}else{
									elem.closest("form").submit();
								}
							}
						});' . PHP_EOL .
					'});' . PHP_EOL .
				'});' . PHP_EOL .
				'});' . PHP_EOL;
	
	}
	
	
	// getFETourScript
	protected function getFETourScript()
	{
	
		return	'head.ready(function(){
					head.load({hopscotch: "' . PROJECT_HTTP_ROOT . '/extLibs/jquery/hopscotch/js/hopscotch.min.js"}, function(){
						head.load("' . PROJECT_HTTP_ROOT . '/extLibs/jquery/hopscotch/css/hopscotch.min.css");
						head.load({admintour: "' . SYSTEM_HTTP_ROOT . '/access/js/feTour.min.js"}, function(){
							$("document").ready(function(){
								// Start tour on desktop devices
								if(!cc.isPhone()){
									$.fe_AdminTour();
								}
							});
						});
					});
				});' . PHP_EOL;
	
	}
}
