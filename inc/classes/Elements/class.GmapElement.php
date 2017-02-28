<?php
namespace Concise;



/**
 * GmapElement
 * 
 */

class GmapElement extends ElementFactory implements Elements
{
	
	/**
	 * Gibt ein GmapElement zurück
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
		########  GoogleMap  #########
		##############################
		
		// Zunächst das entsprechende Modul einbinden (Googleklasse)
		require_once PROJECT_DOC_ROOT."/inc/classes/Modules/class.Google.php";

		$mapCon = explode("<>", $this->conValue);
		
		if(!isset($mapCon[0]))
			$mapCon[0] = "";
		if(!isset($mapCon[1]))
			$mapCon[1] = "";
		
		if(!isset($mapCon[2])
		|| !isset($mapCon[3])
		|| !is_numeric($mapCon[2])
		|| !is_numeric($mapCon[3])
		) {
				
			if($this->isMainContent) {
				$width	= 600;
				$height	= 360;
			}
			else {
				$width	= 198;
				$height	= 198;
			}
		}
		else {
			$width	= $mapCon[2];
			$height	= $mapCon[3];
		}
		if(!isset($mapCon[4]))
			$code	= "";
		else							
			$code	= $mapCon[4];
		
		$eleClass	= 'googleMap module';
		
		if(!empty($mapCon[5]))
			$eleClass .= ' cc-has-frame';
		
		$output = Google::getMap($code, $width, $height, $mapCon[0], "", $mapCon[1]);
		
		// Ggf. Attribute (Styles) Wrapper-div hinzufügen
		$output	= $this->getContentElementWrapper($this->conAttributes, $output, $eleClass);
		
		return $output;
	
	}	
	
}
