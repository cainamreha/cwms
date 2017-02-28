<?php
namespace Concise;



/**
 * HtmlElement
 * 
 */

class HtmlElement extends ElementFactory implements Elements
{
	
	/**
	 * Gibt ein HtmlElement zurück
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
		#######  HTML-Inhalt  ########
		##############################
		
		$code	= trim($this->conValue);
		$output	= html_entity_decode($code, ENT_QUOTES, 'UTF-8');
		
		// Ggf. Attribute (Styles) Wrapper-div hinzufügen
		if($this->conAttributes['id'] != "" || $this->conAttributes['class'] != "" || $this->conAttributes['style'] != "")
			$output	= $this->getContentElementWrapper($this->conAttributes, $output, 'htmlWrapper');
		
		$this->contentDefinitions[$this->conTable][$this->conNum]["html"] = $code;
		
		return $output;
	
	}	
	
}
