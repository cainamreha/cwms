<?php
namespace Concise;


/**
 * Klasse LiveUpdate
 * 
 *
 */

class LiveUpdate extends Admin
{

	private $updateUrl				= "http://www.hermani-webrealisierung.de/media/files/cwms/update/";
	private $updateUrlPlugins		= "http://www.hermani-webrealisierung.de/media/files/cwms/plugins/";
	private $updateVersion			= "";
	private $updateVersionPlugins	= array();
	private $latestVersionFile		= "";
	private $nextMajorVersionFile	= "";
	private $latestVersionFilePlugins	= "pl_latest_version.txt";
	private $currentMajorVersion	= 0;
	private $nextMajorVersion		= 0;
	private $currVersionStr			= "";
	private $updateAvailableCore	= false;
	private $updateAvailablePlugins	= false;
	private $updateCoreChecked		= false;
	private $updatePluginsChecked	= false;
	private $updateDownload			= false;
	private $updateFile				= "";
	private $archive				= "";
	private $pluginArchive			= "";
	private $updError				= array();
	private $updSuccess				= array();
	private $successOpenTag			= '<p class="notice success">';
	private $errorOpenTag			= '<p class="error">';
	private $closeTag				= "</p>\r\n";
	private $updateFolder			= "";
	private $updateScript			= "";
	private $logDir					= "";
	
	/**
	 * Constructor
	 *
	 * @param	$installedPlugins array installed plug-ins
	 * @access	public
	 */
	public function __construct($DB, $o_lng, $installedPlugins)
	{

		// DB-Objekt
		$this->DB					= $DB;
		
		// Lng-Objekt
		$this->o_lng				= $o_lng;
		
		// Security-Objekt
		$this->o_security			= Security::getInstance();
		
		// Plug-ins
		$this->installedPlugins		= $installedPlugins;
		
		$versArr					= explode(".", CWMS_VERSION);
		$this->currentMajorVersion	= (int)reset($versArr);
		$this->nextMajorVersion		= $this->currentMajorVersion +1;
		$this->latestVersionFile	= 'cwms_latest_v' . $this->currentMajorVersion . '.txt';
		$this->nextMajorVersionFile	= 'cwms_latest_v' . $this->nextMajorVersion . '.txt';
		
		$this->updateFolder			= SYSTEM_DOC_ROOT . '/update';
		$this->updateScript			= $this->updateFolder . '/updateCore.inc.php';
		
		$this->logDir 				= $this->updateFolder . '/log';
		
	}
	


	/**
	 * Generate the module
	 * @access public
	 */
	public function initLiveUpdater($checkCoreUpd, $checkPluginUpd, $allPlugins = false)
	{
		
		// Überprüfen ob Updates vorhanden
		$this->updateAvailable		= $this->checkUpdatesAvailable($checkCoreUpd, $checkPluginUpd, $allPlugins);
		
		$this->updateFile			= 'cwms_update_v' . $this->updateVersion . '.zip';		
		
		$this->archive				= TEMP_DIR . $this->updateFile;
	
	}
	


	/**
	 * Generate the module
	 * @access public
	 */
	public function getUpdate($getCoreUpd, $getPluginUpd, $allPlugins = false)
	{

		$output					= "";
		
		// Core update
		if($getCoreUpd) {
			
			$this->currVersionStr 	=	'<p class="framedParagraph">' .
										parent::getIcon("concise", "inline-icon") .
										'<span class="versionHint padding-left"><strong>{s_hint:currentversion}: ' . CWMS_VERSION . '</strong></span></p>' . "\n";
			
			
			// Falls neueste Version bereits installiert
			if($this->updateAvailableCore)
				$output 	.= $this->getUpdateCore();
			else {
				$output 	.= $this->successOpenTag	. '{s_notice:systemuptodate}' . $this->closeTag;
				$output 	.= $this->currVersionStr;
			}
		}
		
		// Plugins update
		if($getPluginUpd) {
			
			if($this->updateAvailablePlugins)
				$output		.= $this->getUpdatePlugins();
			else
				$output		.= $this->getUpdatePluginsAjaxCheck();
		}
		
		return $output;
	
	}
	


	/**
	 * getUpdateCore
	 * @access private
	 */
	private function getUpdateCore()
	{
		
		$output		= "";
		
		// Falls Update vorhanden
		// Falls Datei bereits heruntergeladen
		$this->checkDownloadExists();
		
		if(	isset($GLOBALS['_POST']['download_update']) || 
			isset($GLOBALS['_POST']['install_update'])
		)		
			$output .= $this->runUpdate();
		
		if(!isset($GLOBALS['_POST']['install_update']) || count($this->updError) > 0)
			$output .= $this->getUpdateForm();
	
		return $output;
	
	}
	


	/**
	 * getUpdatePlugins
	 * @access private
	 */
	private function getUpdatePlugins()
	{
		
		$output		= "";
		
		// Falls Update vorhanden
		// Falls Datei bereits heruntergeladen
		$this->checkDownloadExists();
		
		if(!empty($GLOBALS['_POST']['install_plugin_update']))		
			return $this->runPluginUpdate($GLOBALS['_POST']['install_plugin_update']);
	
		// Falls Update vorhanden
		$output		.= $this->getUpdatePluginsForm();
	
		return $output;
	
	}
	

	
	/**
	 * checkUpdatesAvailable
	 * @access public
	 */
	public function checkUpdatesAvailable($checkCoreUpd, $checkPluginUpd, $allPlugins = false)
	{
	
		$upd	= false;
		
		if($checkCoreUpd
		&& $this->updateAvailableCore = self::checkCoreUpdate()
		) {
			$upd = true;
			$this->setSessionVar('updateAvailable', true);
		}
		elseif($checkPluginUpd
		&& $this->updateAvailablePlugins = self::checkPluginsUpdate($this->installedPlugins, $allPlugins)
		) {
			$upd = true;
			$this->setSessionVar('updateAvailable', true);
		}
		
		if(!$upd
		&& $checkCoreUpd
		)
			$this->unsetSessionKey('updateAvailable');
		
		return $upd;

	}
	
	

	/**
	 * checkCoreUpdate
	 * @access protected
	 */
	protected function checkCoreUpdate()
	{
		
		$this->updateCoreChecked	= true;
	
		$updExists = false;
		
		// Auf neue Core-Version prüfen
		if($this->checkUrlExists($this->updateUrl . $this->latestVersionFile)
		&& $this->updateVersion = @file_get_contents($this->updateUrl . $this->latestVersionFile)
		)
			$updExists = version_compare(CWMS_VERSION, $this->updateVersion, '<');
		
		// Auf neue Core-Major-Version prüfen
		if(!$updExists
		&& !empty($this->updateVersion) // exclude server error
		&& $this->checkUrlExists($this->updateUrl . $this->nextMajorVersionFile)
		&& $this->updateVersion = @file_get_contents($this->updateUrl . $this->nextMajorVersionFile)
		)
			$updExists = version_compare(CWMS_VERSION, $this->updateVersion, '<');
		
		return $updExists;

	}
	
	

	/**
	 * checkPluginsUpdate
	 * @access protected
	 */
	protected function checkPluginsUpdate($installedPlugins, $checkAll = false)
	{
	
		$this->updatePluginsChecked	= true;
		
		if(empty($installedPlugins))
			return false;
				
		$updExists = false;
		
		// Auf neue Plugin-Versionen prüfen
		foreach($installedPlugins as $plugin => $plDetails) {
		
			$url	= $this->updateUrlPlugins . $plugin . '/' . $this->latestVersionFilePlugins;
			
			if($this->checkUrlExists($url)
			&& $updateVersionPl = @file_get_contents($url)
			) {
				if((empty($plDetails["version"]["pluginversion"])
				|| version_compare($plDetails["version"]["pluginversion"], $updateVersionPl, '<'))
				&& empty($plDetails["version"]["ignoreupdates"])
				) {
					$this->updateVersionPlugins[$plugin] = $updateVersionPl;
					$updExists = true;
					
					if(!$checkAll)
						return true;
				}
			}
		}
		
		return $updExists;

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
	protected function checkDownloadExists()
	{
		
		// Update-Datei herunterladen
		if(file_exists($this->archive)) {
			$this->updateDownload = true;
			return true;
		}		
		return false;

	}
	
	

	/**
	 * downloadUpdateFile
	 * @access protected
	 */
	protected function downloadUpdateFile()
	{
	
		// Temp-Verzeichnis erstellen
		parent::makeTempDir();
		
		// Update-Datei herunterladen
		if($updateFile = @file_get_contents($this->updateUrl . $this->updateFile)) {
			if(@file_put_contents($this->archive, $updateFile)) {
				$this->updateDownload = true;
				$this->updSuccess[]	=	'{s_notice:downloadupdok}';			
				return true;
			}
		}		
		$this->updError[]	=	'{s_error:downloadupdfile}';			
		return false;

	}
	
	

	/**
	 * downloadPluginUpdateFile
	 * @access protected
	 */
	protected function downloadPluginUpdateFile($plugin)
	{
	
		if(empty($plugin))
			return false;
		
		
		// Temp-Verzeichnis erstellen
		parent::makeTempDir();
		
		// Archive definieren
		$this->updateFile	= $plugin . '.zip';
		$this->archive		= TEMP_DIR . $this->updateFile;
		
		// Update-Datei herunterladen
		if($updateFile = @file_get_contents($this->updateUrlPlugins . $plugin . '/' . $this->updateFile)) {
			if(@file_put_contents($this->archive, $updateFile)) {
				$this->updateDownload = true;
				$this->updSuccess[]	=	'{s_notice:downloadupdok}';			
				return true;
			}
		}		
		$this->updError[]	=	'{s_error:downloadupdfile}';			
		return false;

	}
	
	

	/**
	 * Run the Live Update
	 * @access protected
	 */
	protected function runLiveUpdate()
	{
		
		// Download the archive
		if(!file_exists($this->archive)) {
			$this->updError[]	=	'{s_error:nofiles}';
			return false;
		}
		
		// Wartungsmodus einschalten
		if(!$this->activateMaintenanceMode()) {
			$this->updError[]	=	'{s_error:mtmodeon}';
			return false;
		}
		
		// Verzeichnis entpacken
		if($this->unzipUpdateFile())
			return true;
		else
			return false;		
	}
	


	/**
	 * unzipUpdateFile
	 * @access protected
	 */
	private function unzipUpdateFile()
	{
		
		// Verzeichnis entpacken
		require_once PROJECT_DOC_ROOT."/inc/classes/ZipArchive/class.UnZip.php"; // Klasse UnZip einbinden
		
		$o_unzip	= new UnZip();
		
		$result		= $o_unzip->unZip($this->updateFile);
		
		if(count($result['success']) > 0) {
			$this->updSuccess[]	=	implode("<br />", $result['success']);
		}
		if(count($result['error']) > 0) {
			$this->updError[]	=	implode("<br />", $result['error']);
			return false;
		}
		else
			return true;
	
	}
	
	
	
	/**
	 * runUpdate
	 * @return boolean
	 */
	private function runUpdate()
	{
	
		$output		= "";
		$install	= false;
		
		// Falls Update download
		if(isset($GLOBALS['_POST']['download_update'])) {
			$this->downloadUpdateFile();		
		}
		
		// Falls Update-Installation
		if(isset($GLOBALS['_POST']['install_update']) && $this->updateDownload) {
			
			$install = $this->runLiveUpdate();
			
			// Falls Update erfolgreich
			if($install) {
			
				// Update-Script ausführen
				if($this->runUpdateScript()) {
					$this->removeUpdateHint(); // Session aktualisieren
					$this->updateVersionConst(); // Versionskonstante aktualisieren
					$maintenanceOff = $this->inactivateMaintenanceMode(); // Wartungsmodus deaktivieren
					parent::deleteTempDir(); // Temp-Verzeichnis löschen
					
					// Falls Fehler beim Abschalten des Wartungsmodus
					if(is_array($maintenanceOff))
						$this->updError	= array_merge($this->updError, $maintenanceOff);
				}
			}		
		}
		
		// Fehlermeldungen
		if(count($this->updError) > 0) {
			$output .= $this->errorOpenTag . '{s_error:error}' . $this->closeTag;
			$output .= $this->errorOpenTag . implode($this->closeTag . $this->errorOpenTag, $this->updError) . $this->closeTag;
		}
		elseif($install)
			$this->updSuccess[]	= '{s_notice:updateok}';
		
		// Erfolgsmeldungen
		if(count($this->updSuccess) > 0)
			$output .= $this->successOpenTag . implode($this->closeTag . $this->successOpenTag, $this->updSuccess) . $this->closeTag;

		// Updatevorgang loggen
		$this->logUpdateResult();
		
		return $output;
	}
	
	
	
	/**
	 * runUpdateScript
	 * @return boolean
	 */
	private function runUpdateScript()
	{
	
		// Falls ein Update-Script vorliegt, dieses ausführen
		if(!file_exists($this->updateScript))
			return false;
		
		require_once $this->updateScript; // Update-Script einbinden

		// ConciseCoreUpdater instance
		$o_CoreUpdater = new ConciseCoreUpdater($this->DB, $this->o_lng, $this->updateVersion);
		$o_CoreUpdater->runCWMSUpdater("all");

		// Objektinstanz aus updateScript
		$this->updError		= array_merge($this->updError, $o_CoreUpdater->errorUpdScript);
		$this->updSuccess	= array_merge($this->updSuccess, $o_CoreUpdater->successUpdScript);
		
		if(count($o_CoreUpdater->errorUpdScript) > 0)
			return false;
		else
			return true;
	}
	
	
	
	/**
	 * runPluginUpdate
	 * @return boolean
	 */
	private function runPluginUpdate($plugin)
	{
	
		$output		= "";
		$install	= false;
		$success	= "true";
		
		// Falls Update download
		if(empty($plugin))
			return false;
		
		
		// Download plugin update file
		$this->downloadPluginUpdateFile($plugin);		
		
		
		// Update-Installation
		if($this->updateDownload) {
			
			$install = $this->runLiveUpdate();
			
			// Falls Update erfolgreich
			if($install) {
			
				// Update-Script ausführen
				if($this->runPluginUpdateScript($plugin)) {
					$this->removeUpdateHint(); // Session aktualisieren
					$maintenanceOff = $this->inactivateMaintenanceMode(); // Wartungsmodus deaktivieren
					parent::deleteTempDir(); // Temp-Verzeichnis löschen
					
					// Falls Fehler beim Abschalten des Wartungsmodus
					if(is_array($maintenanceOff))
						$this->updError	= array_merge($this->updError, $maintenanceOff);
				}
			}		
		}
		
		// Fehlermeldungen
		if(count($this->updError) > 0) {
			$success	= "false";
			$output .= $this->errorOpenTag . '{s_error:error}' . $this->closeTag;
			$output .= $this->errorOpenTag . implode($this->closeTag . $this->errorOpenTag, $this->updError) . $this->closeTag;
		}
		elseif($install)
			$this->updSuccess[]	= '{s_notice:updateok}';
		
		// Erfolgsmeldungen
		if(count($this->updSuccess) > 0)
			$output .= $this->successOpenTag . implode($this->closeTag . $this->successOpenTag, $this->updSuccess) . $this->closeTag;

		// Updatevorgang loggen
		#$this->logUpdateResult();
		$output	=	json_encode(
						array(
							"success"	=> $success,
							"html"		=> $output
						)
					);
		
		return $output;
	
	}
	
	
	
	/**
	 * runPluginUpdateScript
	 * @return boolean
	 */
	private function runPluginUpdateScript($plugin)
	{
	
		return true;
	
	}
	
	
	
	/**
	 * removeUpdateHint
	 * @return boolean
	 */
	private function removeUpdateHint()
	{
	
		$this->updateAvailable				= false;
		parent::$statusNavArray['update']	= "";
		
		// Falls ein Update-Script vorliegt, dieses ausführen
		$this->unsetSessionKey('updateAvailable');

	}
	
	
	
	/**
	 * updateVersionConst
	 * @return boolean
	 */
	private function updateVersionConst()
	{
	
		// Inhalte der Settings-Datei einlesen
		if(!$settings = @file_get_contents(PROJECT_DOC_ROOT . '/inc/settings.php')) {
			$this->updError[]	= "settings file not found";
			return false;
		}
			
		// Sicherungskopie von settings.php anlegen
		copy(PROJECT_DOC_ROOT . '/inc/settings.php', PROJECT_DOC_ROOT . '/inc/settings.php.old');
		
		$settings = preg_replace("/'CWMS_VERSION',\"".CWMS_VERSION."\"/", "'CWMS_VERSION',\"".$this->updateVersion."\"", $settings);
									
		// settings.php speichern
		if(!@file_put_contents(PROJECT_DOC_ROOT . '/inc/settings.php', $settings)) {
			$this->updError[]	= "could not write settings file";
			return false;
		}
		return true;
	}
	
	
	
	/**
	 * getUpdateForm
	 * @return boolean
	 */
	private function getUpdateForm()
	{

		$formAction		= ADMIN_HTTP_ROOT . '?task=update';
		$token			= parent::getTokenInput();
		
		$output 		=	'<p class="hint">{s_hint:newupdates} &nbsp; ' .
							'<strong>({s_hint:latestversion}: ' .
							parent::getIcon("concise", "inline-icon") .
							$this->updateVersion . ')' .
							'</strong>' .
							'</p>' . "\r\n";
		
		$output 		.= $this->currVersionStr;
		
		
		// Falls neuere Version bereits heruntergeladen
		if($this->updateDownload && count($this->updError) == 0) {
		
			$output 	.=	'<form action="' . $formAction . '" method="post">' . "\r\n";
			$output 	.=	'<li class="submit change">' . "\r\n";
			
			// Button install
			$btnDefs	= array(	"type"		=> "submit",
									"name"		=> "install_update",
									"class"		=> 'button-install button-wait',
									"value"		=> "{s_button:installupdate}",
									"icon"		=> "install"
								);
				
			$output		.=	parent::getButton($btnDefs);
			
			$output		.=	'<input type="hidden" name="install_update" value="1" />' . "\r\n" .
							$token .
							'</li>' . "\r\n";
			$output 	.=	'</form>' . "\r\n";
		}
		else {
		
			$output 	.=	'<li class="submit change">' . "\r\n";
			
			$output 	.=	'<form action="' . $formAction . '" method="post">' . "\r\n";
			
			// Button download
			$btnDefs	= array(	"type"		=> "submit",
									"name"		=> "download_update",
									"class"		=> 'button-download button-wait',
									"value"		=> "{s_button:downloadupdate}",
									"icon"		=> "download"
								);
				
			$output		.=	parent::getButton($btnDefs);
			
			$output		.=	'<input type="hidden" name="download_update" value="1" />' . "\r\n" .
							$token .
							'</form>' . "\r\n";
		
			$output 	.=	'<p>&nbsp;</p>' . "\r\n";
			
			$output 	.=	'<form action="' . $formAction . '" method="post">' . "\r\n";
			
			// Button install
			$btnDefs	= array(	"type"		=> "submit",
									"name"		=> "downinst_update",
									"class"		=> 'button-install button-wait',
									"value"		=> "{s_button:downinstupdate}",
									"icon"		=> "install"
								);
				
			$output		.=	parent::getButton($btnDefs);
			
			$output		.=	'<input type="hidden" name="downinst_update" value="1" />' . "\r\n" .
							'<input type="hidden" name="download_update" value="1" />' . "\r\n" .
							'<input type="hidden" name="install_update" value="1" />' . "\r\n" .
							$token .
							'</form>' . "\r\n";
							
			$output 	.=	'</li>' . "\r\n";
		}
		
		$output 	.= '</ul>' . "\r\n";
	
		return $output;
	
	}
	
	
	
	/**
	 * getUpdatePluginsForm
	 * @return boolean
	 */
	private function getUpdatePluginsForm()
	{

		$formAction		= SYSTEM_HTTP_ROOT . '/access/editPlugins.php?page=admin&action=update';
		$token			= parent::getTokenInput();
		
		$output 		=	'<p class="hint">{s_hint:newupdates} {s_hint:forplugins}:</p>' . "\r\n";
	
		$output 		.=	'<table class="adminTable">' . "\r\n";
		$output 		.=	'<tbody>' . "\r\n";
		
		$i	= 1;
		
		// Plugin updates
		foreach($this->updateVersionPlugins as $plugin => $updateVersion) {

			$iconKey	 =	!empty($this->installedPlugins[$plugin]["features"]["conicon"]) ? $this->installedPlugins[$plugin]["features"]["conicon"] : "plugin";
			$output 	.=	'<tr>' . PHP_EOL;
			$output 	.=	'<td class="markBox-cell">' . PHP_EOL . 
							'<label class="markBox">' . 
							'<input type="checkbox" name="pluginNo[' . $i . ']" class="addVal" />' .
							'<input type="hidden" name="pluginName[' . $i . ']" value="' . $plugin . '" class="getVal" />' .
							'</label>' .
							'</td>' . PHP_EOL;
			$output		.=	'<td class="cc-table-cell">' . PHP_EOL .
							parent::getIcon($iconKey, "inline-icon") .
							'<strong>' . $plugin . '</strong></td>' . PHP_EOL;
			$output		.=	'<td class="cc-table-cell">' .
							' ({s_hint:latestversion}: ' .
							$updateVersion . ')</td>' . PHP_EOL;
			$output 	.=	'<td class="editButtons-cell">' . PHP_EOL;
			$output 	.=	'<span class="editButtons-panel">' . PHP_EOL;
		
			$output 	.=	'<form action="' . $formAction . '" method="post" data-history="false">' . "\r\n";
			
			// Button install
			$btnDefs	= array(	"type"		=> "submit",
									"name"		=> "install_plugin_update",
									"class"		=> 'cc-submit-install-plugin button-icon-only',
									"value"		=> $plugin,
									"text"		=> "",
									"title"		=> "{s_button:downinstupdate}",
									"icon"		=> "install"
								);
				
			$output		.=	parent::getButton($btnDefs);
			
			$output		.=	'<input type="hidden" name="install_plugin_update" value="' . $plugin . '" />' . "\r\n" .
							$token;
			
			$output 	.=	'</form>' . "\r\n";
			$output 	.=	'</span>' . "\r\n";
			$output 	.=	'</td>' . "\r\n";
			$output 	.=	'</tr>' . "\r\n";
			
			$i++;
		
		}
		
		$output 	.=	'</tbody>' . "\r\n";
		$output 	.=	'</table>' . "\r\n";
	
		return $output;
	
	}
	
	
	
	/**
	 * getUpdatePluginsAjaxCheck
	 * @return boolean
	 */
	private function getUpdatePluginsAjaxCheck()
	{

		if($this->updatePluginsChecked
		&& $this->updateAvailablePlugins == false
		) {
			return $this->getNotificationStr('{s_notice:pluginsuptodate}');
		}
		
		$checkUrl		= SYSTEM_HTTP_ROOT . '/access/editPlugins.php?page=admin&action=update';
		$token			= parent::getTokenInput();
		
		$output 		=	'<div id="updatePluginsList">' . "\r\n";
		
		$output 		.=	'<p class="updateSearchNote framedParagraph">' . parent::getIcon("plugin", "inline-icon") . '{s_header:updatesearch}...&nbsp;</p>' . "\r\n";
		
		$output 		.=	'</div>' . "\r\n";
		
		$output 		.=	'<script>
								head.ready(function() {
									$(document).ready(function() {
										var loadingImg	= $(\'<span class="cc-admin-icons inline-icon cc-icon-loading">&nbsp;</span>\');
										$("#updatePluginsList").children(".updateSearchNote").append(loadingImg);
										$("#updatePluginsList").load("' . $checkUrl . '", function(){loadingImg.parent(".updateSearchNote").remove();});
										$("body").on("click", "#updatePluginsList .cc-submit-install-plugin", function(e){
											e.preventDefault();
											e.stopImmediatePropagation();
											var updBtn		= $(this);
											var formelem	= updBtn.closest("form");
											$.submitViaAjax(formelem, false, "json", false, function(ajax){
												var resIcon	 = "";
												if(ajax.success == "true"){
													resIcon	= \'<span class="cc-admin-icons cc-icon-ok inline-icon" title="Update ok">&nbsp;</span>\';
												}else{
													resIcon	= \'<span class="cc-admin-icons cc-icon-error inline-icon" title="Update error">&nbsp;</span>\';
												}
												updBtn.closest(".editButtons-panel").append(resIcon);
												formelem.remove();
												jAlert(ajax.html.replace(/\\n/g, ""), ln.alerttitle);
												$.removeWaitBar();
											});
										});
									});
								});
							</script>' . PHP_EOL;
	
		return $output;
	
	}
	
	
	// Log Updatevorgang
	private function logUpdateResult()
	{
	
		$resultArray	= array_merge($this->updError, $this->updSuccess);
		
		if(!is_dir($this->logDir))
			mkdir($this->logDir);
		
		$logFile	= $this->logDir . '/updateLog.txt';
		
		$handle = @fopen($logFile, "a");
		
		// Updatevorgang eintragen
		$error = date("d.m.Y h:i:s", time()) . " => version " . $this->updateVersion . ': ' . ContentsEngine::replaceStaText(str_replace(array("<br>","<br />"), " ", implode(" | ", $resultArray))) . PHP_EOL;

		fwrite($handle, $error);

		fclose($handle);
	}
}
