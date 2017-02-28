<?php
namespace Concise;



/**
 * FlashElement
 * 
 */

class FlashElement extends ElementFactory implements Elements
{
	
	/**
	 * Gibt ein FlashElement zurück
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
		#######  Flashinhalt  ########
		##############################
		
		$flashCon = explode("<>", $this->conValue);
		
		if(!isset($flashCon[0]))
			$flashCon[0] = "";
		if(!isset($flashCon[1]))
			$flashCon[1] = "";
		if(!isset($flashCon[2]))
			$flashCon[2] = DEF_FLASH_WIDTH;
		if(!isset($flashCon[3]))
			$flashCon[3] = DEF_FLASH_HEIGHT;
		if(!isset($flashCon[4]))
			$flashCon[4] = "false";
		if(!isset($flashCon[5]))
			$flashCon[5] = "false";
		if(!isset($flashCon[6]))
			$flashCon[6] = "false";
		

		if(Contents::isAppleDevice())
			$output = 	$flashCon[1] == "" ? "Flash video. No player available." : $flashCon[1];
		else {
			$this->scriptFiles[] = JS_DIR . "flash.js";
			require_once PROJECT_DOC_ROOT."/inc/classes/Media/class.FlashContent.php"; // FlashContent-Klasse einbinden
			$output =	FlashContent::getFlashObject($flashCon[0], $flashCon[1], $flashCon[2], $flashCon[3], $flashCon[4], $flashCon[5], $flashCon[6]);
		}		

		// Ggf. Attribute (Styles) Wrapper-div hinzufügen
		if($this->conAttributes['id'] != "" || $this->conAttributes['class'] != "" || $this->conAttributes['style'] != "")
			$output	= $this->getContentElementWrapper($this->conAttributes, $output);

		return $output;
	
	}	
	
}
