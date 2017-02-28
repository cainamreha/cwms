<?php
namespace Concise;



/**
 * TextElement
 * 
 */

class TextElement extends ElementFactory implements Elements
{
	
	/**
	 * Gibt ein TextElement zurück
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
		########  Textinhalt  ########
		##############################
	
		// Ggf. Attribute (Styles) Wrapper-div hinzufügen
		$output	= $this->getContentElementWrapper($this->conAttributes, $this->conValue, 'textWrapper');

		return $output;
	
	}	
	
}
