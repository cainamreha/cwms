<?php
namespace Concise;

##############################
######  Test-Extension  ######
##############################

// test
// Inhaltsausgabe

/**
 * TestPluginElement
 * 
 */
class TestPluginElement extends ElementFactory implements Elements
{
	
	/**
	 * Gibt ein TestPluginElement zurück
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

		$output = '<h4>{s_plugin-test:testtext}</h4>' . "\r\n";
		
		return $output;
		
	}
	
}
