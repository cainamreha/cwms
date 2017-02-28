<?php
namespace Concise;


require_once PROJECT_DOC_ROOT . "/inc/classes/Admin/class.Admin.php"; // Admin-Klasse einbinden
require_once PROJECT_DOC_ROOT . "/inc/classes/Admin/interface.AdminTask.php"; // AdminTask-Interface einbinden

/**
 * Klasse AdminTaskFactory
 * 
 */

class AdminTaskFactory
{

	private static $isAdminPlugin	= false;
	private static $classNamePrefix	= 'Admin_';
	
	/**
	 * Instanziert ein Element-Objekt
	 * 
     * @param	string	$task			AdminTask
     * @param	array	$options		Options
     * @param	object	$DB				DB-Objekt
     * @param	object	$o_lng			Lng-Objekt
     * @param	booean	$init			Init page
	 * @access	public
     * @return  object
	 */
	public static function create($task, $options, $DB, $o_lng, $init)
	{
	
		$classPath	= self::getClassPath($task, $options);
		
		// Falls Datei nicht vorhanden
		if(file_exists($classPath)) {
			
			require_once $classPath; // Element-Klasse einbinden
			$className	= self::getClassName($task, $options);
			
			$obj 	= new $className($DB, $o_lng, $task, $init); // Objekt instanzieren und zurückgeben			
			$obj	= self::setOptions($obj, $options);
			
			return $obj;
		}
		else
			throw new \Exception('<p class="notice error">unknown admin task: ' . $task . '.</p>');
	
	}	
	
	/**
	 * Instanziert ein Element-Objekt
	 * 
     * @param	string	$task			AdminTask
     * @param	array	$options		Options
	 * @access	public
     * @return  object
	 */
	public static function getClassPath($task, $options)
	{
	
		// First check for (overwrite by) plugin
		// Falls Plug-in
		if(file_exists(PLUGIN_DIR . $task . '/admin_' . $task . '.inc.php')) {
			self::$isAdminPlugin	= true;
			return PLUGIN_DIR . $task . '/admin_' . $task . '.inc.php'; // Pfad zur Plug-in-Klasse
		}
		
		// Falls bestimmter Task-Typ forciert ist (e.g. Edit-Tpl)
		if(!empty($options['admintype'])) {
		
			// Edit-Tpl
			if($options['admintype'] == "edit") {
				self::$isAdminPlugin	= false;
				return SYSTEM_DOC_ROOT . '/inc/admintasks/edit/admin_edit.inc.php'; // Pfad zur Edit-Taks-Klasse
			}
		
			// Changes
			if($options['admintype'] == "changes") {
				self::$isAdminPlugin	= false;
				return SYSTEM_DOC_ROOT . '/inc/admintasks/main/admin_main.inc.php'; // Pfad zur Edit-Taks-Klasse
			}
		
			// Modules
			if($options['admintype'] == "data"
			|| $options['admintype'] == "gallery"
			|| $options['admintype'] == "comments"
			|| $options['admintype'] == "gbook"
			) {
				self::$isAdminPlugin	= false;
				return SYSTEM_DOC_ROOT . '/inc/admintasks/modules/admin_modules.' . $options['admintype'] . '.inc.php'; // Pfad zur Modules-Type-Klasse
			}
		
			// Campaigns
			if($options['admintype'] == "newsl") {
				self::$isAdminPlugin	= false;
				return SYSTEM_DOC_ROOT . '/inc/admintasks/campaigns/admin_campaigns.' . $options['admintype'] . '.inc.php'; // Pfad zur Campaigns-Type-Klasse
			}
		}
		
		// Andernfalls Core-Adminseite
		self::$isAdminPlugin	= false;
		return SYSTEM_DOC_ROOT . '/inc/admintasks/' . $task . '/admin_' . $task . '.inc.php'; // Pfad zur Core-Taks-Klasse
	
	}
	
	/**
	 * Instanziert ein Element-Objekt
	 * 
     * @param	string	$task			AdminTask
     * @param	array	$options		Options
	 * @access	public
     * @return  object
	 */
	public static function getClassName($task, $options)
	{

		// Falls bestimmter Task-Typ forciert ist (e.g. Edit-Tpl)
		if(!empty($options['admintype'])) {
			
			// Edit-Tpl
			if($options['admintype'] == "edit")
				return 'Concise\\' . self::$classNamePrefix . 'Edit';
				
			// Changes
			if($options['admintype'] == "changes")
				return 'Concise\\' . self::$classNamePrefix . 'Main';
				
			// Modules
			if($options['admintype'] == "data"
			|| $options['admintype'] == "gallery"
			|| $options['admintype'] == "comments"
			|| $options['admintype'] == "gbook"
			)
				return 'Concise\\' . self::$classNamePrefix . 'Modules' . ucfirst($options['admintype']);
				
			// Modules
			if($options['admintype'] == "newsl")
				return 'Concise\\' . self::$classNamePrefix . 'Campaigns' . ucfirst($options['admintype']);
		}
		
		// Class name
		return 'Concise\\' . self::$classNamePrefix . ucfirst(str_replace("-", "", $task));
	
	}
	
	/**
	 * Instanziert ein Element-Objekt
	 * 
     * @param	object	$obj			AdminTask-objekt
     * @param	array	$options		Options
	 * @access	public
     * @return  object
	 */
	public static function setOptions($obj, $options)
	{
	
		$obj->isAdminPlugin	= self::$isAdminPlugin;		
		
		// Falls bestimmter Task-Typ forciert ist (e.g. Edit-Tpl)
		if(!empty($options['admintype'])) {
			
			// Edit-Tpl
			if($options['admintype'] == "edit") {
				$obj->editId			= $options['editId'];		
				$obj->isTemplateArea	= $options['isTemplateArea'];		
			}
		}
		
		return $obj;
	
	}

}
