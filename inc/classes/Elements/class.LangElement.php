<?php
namespace Concise;



/**
 * LangElement
 * 
 */

class LangElement extends ElementFactory implements Elements
{
	
	/**
	 * Gibt ein LangElement zurück
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
		###########  Lang  ###########
		##############################
					
		$linkCon		= explode("<>", $this->conValue);
		$menu			= "";
		$output			= "";
		$menuOpenDiv	= "";
		$menuCloseDiv	= "";
		
		if(!isset($linkCon[0])) // Menütyp
			$linkCon[0] = "flag";
		else
			$menuType	= $linkCon[0]; // Menütyp
		
		if(!isset($linkCon[1])) // Separator
			$linkCon[1] = "";
		
		if(!isset($linkCon[2])) // Show active Lang
			$linkCon[2] = false;
		else
			$linkCon[2] = !$linkCon[2];
		
		// Ggf. Attribute (Styles) Wrapper-div hinzufügen
		if($menuType != "") {
			
			// Menütyp auswählen
			if($menuType == "text")
				$output = $this->o_lng->getLangSelector($this->pageId, "text", $linkCon[1], $linkCon[2]);
			else
				$output = $this->o_lng->getLangSelector($this->pageId, "flag", $linkCon[1], $linkCon[2]);		
		}
		
		
		// Ggf. Attribute (Styles) Wrapper-div hinzufügen
		if($this->conAttributes['id'] != "" || $this->conAttributes['class'] != "" || $this->conAttributes['style'] != "")
			$output	= $this->getContentElementWrapper($this->conAttributes, $output);
		
		return $output;
	
	}	
	
}
