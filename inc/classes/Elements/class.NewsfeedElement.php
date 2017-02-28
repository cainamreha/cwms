<?php
namespace Concise;



/**
 * NewsfeedElement
 * 
 */

class NewsfeedElement extends ElementFactory implements Elements
{
	
	/**
	 * Gibt ein NewsfeedElement zurück
	 * 
	 * @access	public
	 * @param	string	$options	Parameter-Array (Wert, Styles, Wrap)
	 */
	public function __construct($options, $DB, &$o_lng, &$o_page)
	{
	
		$this->DB				= $DB;
		$this->o_lng			= $o_lng;
		$this->o_page			= $o_page;

		$this->conType			= $options["conType"];
		$this->conValue			= $options["conValue"];
		$this->conAttributes	= $options["conAttributes"];
		$this->conNum			= $options["conNum"];
		$this->conTable			= $options["conTable"];

	}
	

	/**
	 * Element erstellen
	 * 
	 * @access	public
     * @return  string
	 */
	public function getElement()
	{

		##############################
		#########  Newsfeed  #########
		##############################
								
		// Newsfeed ausgeben
		$feedCon	= explode("<>", $this->conValue);
		$output		= "";
		
		if(!isset($feedCon[0]))
			$feedCon[0] = ""; // Kategorie
		if(!isset($feedCon[1]))
			$feedCon[1] = ""; // Feed-Darstellung
		if(!isset($feedCon[2]))
			$feedCon[2] = ""; // TargetpageID

		if(!$this->isMainContent || !isset($GLOBALS['_GET']['mod'])) {
			
			require_once PROJECT_DOC_ROOT."/inc/classes/Modules/class.ModulesData.php";
		
			$o_module	= new ModulesData($this->DB, $this->o_lng, $this->o_dispatcher, $this->eventListeners, $this->o_page);
		
			$output = $o_module->getModule($this->conType, $feedCon[0], $feedCon[1], $feedCon[2]); // Datenausgabe generieren
			
			// Attribute (Styles) Wrapper-div hinzufügen
			$output	= $this->getContentElementWrapper($this->conAttributes, $output, 'cc-' . $this->conType . ' cc-module');
		}
		
		return $output;
	
	}	
	
}
