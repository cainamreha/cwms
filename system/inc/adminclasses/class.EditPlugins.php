<?php
namespace Concise;


###############################################
###############  Edit Plugins  ################
###############################################

// Plugins editieren

class EditPlugins extends Admin
{

	private $action				= "";
	private $editID				= "";
	private $isArray			= false;
	private $pluginName			= "";
	private $tablePlugins		= "plugins";

	
	public function __construct($DB, $o_lng)
	{

		// Admin-Elternklasse aufrufen
		parent::__construct($DB, $o_lng);

		$this->tablePlugins		= DB_TABLE_PREFIX . $this->tablePlugins;
	
	}
	
	public function conductAction()
	{
	
		if(!empty($GLOBALS['_GET']['action']))
			$this->action = $GLOBALS['_GET']['action'];

		if(!empty($GLOBALS['_GET']['array']))
			$this->isArray	= true;

		if(!empty($GLOBALS['_GET']['name']))
			$this->pluginName	= $this->DB->escapeString($GLOBALS['_GET']['name']);

		if(!empty($GLOBALS['_GET']['id']))
			$this->editID	= (int)$GLOBALS['_GET']['id'];
		


		// Plugins aktivieren
		if($this->action == "activate")
			return $this->activatePlugins();
		
		// Falls ein Plug-in gelöscht werden soll
		if($this->action == "delete")
			return $this->deletePlugins();
		
		// Falls ein Plug-in update aufgelistet oder vollzogen werden soll
		if($this->action == "update")
			return $this->updatePlugins();
	
	}
	
	
	#############  Plug-ins aktivieren  ###############
	// activatePlugins
	public function activatePlugins()
	{

		$deleteSQL1			= false;
		$notice 			= "{s_notice:puball}";
		$queryExt1			= "";
		$listCat			= "";
		$multiple			= false;
		$dataEntries		= 0;
		$pluginsDBInstall	= array();
		
		
		if(isset($GLOBALS['_GET']['active']) && $GLOBALS['_GET']['active'] == 1)
			$active = 1;
		else
			$active = 0;
			
			
		// Falls Array, mehrere Plugins aus Post auslesen
		if($this->isArray && isset($GLOBALS['_POST']['entryNr'])) {
			
			$multiple	= true;
			foreach($GLOBALS['_POST']['entryNr'] as $key => $IDvalue) {
				
				$pluginName 			= $this->DB->escapeString($GLOBALS['_POST']['entryID'][$key]);				
			
				// check if plugin width DB install
				if(file_exists(PLUGIN_DIR . $pluginName . '/install_' . $pluginName . '.inc.php'))
					$pluginsDBInstall[]	= $pluginName;
				
				if($dataEntries == 0)
					$this->pluginName	= $pluginName;
				
				$queryExt1 .= " OR `pl_name` = '".$pluginName."'";
				$dataEntries++;					
			}
			if($dataEntries > 0)
				--$dataEntries;
			
		}
		else {
			// check if plugin width DB install
			if(file_exists(PLUGIN_DIR . $this->pluginName . '/install_' . $this->pluginName . '.inc.php'))
				$pluginsDBInstall[]	= $this->pluginName;
		
			$queryExt1 = " OR `pl_name` = '".$this->pluginName."'";
		}
		
		// db-Tabelle sperren
		$lock = $this->DB->query("LOCK TABLES `$this->tablePlugins`");
		
		
		// log-Eintrag löschen
		$updateSQL1 = $this->DB->query("UPDATE `$this->tablePlugins` 
											 SET `active` = " . $active . " 
											 WHERE `pl_name` = '' 
											 $queryExt1
											");
			
						
		// db-Sperre aufheben
		$unLock = $this->DB->query("UNLOCK TABLES");
		
		
		// DB install, if active
		if($active)
			$this->installPluginDB($pluginsDBInstall);
	
		
		// Falls multiple Datensätze
		if($multiple) {
		
			$this->setSessionVar('notice', '<strong>' . $this->pluginName . '</strong>' . sprintf(ContentsEngine::replaceStaText("{s_notice:pluginsactivated}"), $dataEntries, "{s_common:" . ($active ? "" : "in") . "active}") . '.');
			header('location: ' . ADMIN_HTTP_ROOT . '?task=plugins') . exit;
			return false;
		}
		
			
		if($updateSQL1 === true)
			echo 1;
		else
			echo 0;
		
		exit;
	}
		
		
	#############  Plug-ins löschen  ###############
	// deletePlugins
	public function deletePlugins()
	{

		$delPlugins		= array();
		$deleted		= false;
		$multiple		= false;
		$dataEntries	= 0;
		
	
		// Falls Array, mehrere Plugins aus Post auslesen
		if($this->isArray && isset($GLOBALS['_POST']['entryNr'])) {
			
			$multiple	= true;
			foreach($GLOBALS['_POST']['entryNr'] as $key => $val) {
				
				$plName = $GLOBALS['_POST']['entryID'][$key];

				if($dataEntries == 0)
					$this->pluginName	= $plName;
				
				$deleted	= $this->deletePluginFolder($plName);
				
				if($deleted) {
					$delPlugins[]	= $plName;
					$dataEntries++;
				}
			}
			
			if($dataEntries > 0)
				--$dataEntries;
			
		}
		else {
			$delPlugins[]	= $this->pluginName;
			$deleted		= $this->deletePluginFolder($this->pluginName);
		}
		
		if($deleted) {
			$this->removePluginDbEntry($delPlugins);
			$this->setSessionVar('notice', sprintf(ContentsEngine::replaceStaText('{s_notice:delplugin}'), '<strong>' . $this->pluginName . '</strong>', $dataEntries));
		}
		else {
			$this->setSessionVar('error', '{s_error:bkpdel}'.$plName);
		}
		
		// Falls multiple Datensätze, Seite neu laden
		if($multiple) {
		
			header('location: ' . ADMIN_HTTP_ROOT . '?task=plugins') . exit;
			return false;
		}
		return true;
	}
		
		
	#############  Plug-ins update  ###############		
	// updatePlugins
	public function updatePlugins()
	{
	
		// Theme-Setup
		$this->getThemeDefaults("admin");
	
		$output	= "";
		
		// Update
		if(!CC_UPDATE_CHECK) {
			echo '<p class="notice error">No update permission</p>';
			exit;
		}
		
		require_once PROJECT_DOC_ROOT."/inc/classes/Update/class.LiveUpdate.php"; // Klasse LiveUpdate einbinden
		
		$o_update		= new LiveUpdate($this->DB, $this->installedPlugins);
		$o_update->initLiveUpdater(false, true, true);
		
		// Update output
		$output .=	$o_update->getUpdate(false, true);		
		
		$output	= parent::replaceStaText($output);
		$output	= parent::replaceStyleDefs($output);
		
		echo $output;
		exit;
		return true;
	
	}
	
	
	// installPluginDB
	public function installPluginDB($pluginsDBInstall)
	{

		foreach($pluginsDBInstall as $plugin) {
			if(file_exists(PLUGIN_DIR . $plugin . '/install_' . $plugin. '.inc.php')) {
			
				require_once PLUGIN_DIR . $plugin . '/install_' . $plugin. '.inc.php';
				
				verifyTableStructure($this->DB, $this->installedLangs);
			}
		}
		return true;

	}		
	
	
	// deletePluginFolder
	public function deletePluginFolder($delPlugin)
	{

		if(is_dir(PROJECT_DOC_ROOT . "/plugins/" . $delPlugin)) {
		
			// Ggf. Datenbanktabellen löschen bzw. uninstall-Datei ausführen
			if(file_exists(PROJECT_DOC_ROOT . "/plugins/" . $delPlugin . '/uninstall_' . $delPlugin . '.inc.php')) {
				$DB	= $this->DB;
				require_once(PROJECT_DOC_ROOT . "/plugins/" . $delPlugin . '/uninstall_' . $delPlugin . '.inc.php');
			}
			
			// Plugin-Ordner löschen
			$deleted = parent::unlinkRecursive(PROJECT_DOC_ROOT . "/plugins/" . $delPlugin, true);
			
			return $deleted;
		}
		return false;

	}		
	
	
	// removePluginDbEntry
	public function removePluginDbEntry($delPlugin)
	{
		
		$delStr	= "";
		
		// Falls Array mit Pluginnamen
		if(is_array($delPlugin)) {
		
			if(count($delPlugin) == 0)
				return false;
			
			foreach($delPlugin as $pl) {
				$delStr	.= " OR `pl_name` = '" . $this->DB->escapeString($pl) . "'";
			}
		}
		// Andernfalls String mit Pluginnamen
		else {
			
			if($delPlugin == "")
				return false;
					
			$delStr	= " OR `pl_name` = '" . $this->DB->escapeString($delPlugin) . "'";
		}
		
		// db-Tabelle sperren
		$lock = $this->DB->query("LOCK TABLES `$this->tablePlugins`");

		
		// log-Eintrag löschen
		$deleteSQL	= $this->DB->query( "DELETE FROM `$this->tablePlugins` 
											WHERE `pl_name` = ''
											$delStr
										");
			
		
		// db-Sperre aufheben
		$unLock = $this->DB->query("UNLOCK TABLES");
		
		return $deleteSQL;

	}

} // end class EditPlugins
