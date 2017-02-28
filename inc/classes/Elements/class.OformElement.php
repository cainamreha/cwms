<?php
namespace Concise;



/**
 * OformElement
 * 
 */

class OformElement extends ElementFactory implements Elements
{
	
	/**
	 * Gibt ein OformElement zurück
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
		#####  Bestellformular  ######
		##############################
		
		// Zunächst das entsprechende Modul einbinden (Orderfm-Klasse)
		require_once PROJECT_DOC_ROOT."/inc/classes/Modules/class.Orderfm.php";
	
		$this->cssFiles[]		= STYLES_DIR . "orderfm.css"; // css-Datei einbinden
		$this->scriptFiles[] 	= JS_DIR . "popup.js";
		
		$orderParam = explode("<>", $this->conValue);
		
		if(!isset($orderParam[1]))
			$orderParam[1] = "";
			
		$o_oform			= new Orderfm($this->DB, $this->o_lng); // Bestell-Objekt
		$o_oform->themeConf	= $this->themeConf;
		$output				= $o_oform->getOrderForm($orderParam[0], $orderParam[1]); // Formular generieren

		$this->mergeHeadCodeArrays($o_oform);
		
		// Ggf. Attribute (Styles) Wrapper-div hinzufügen
		if($this->conAttributes['id'] != "" || $this->conAttributes['class'] != "" || $this->conAttributes['style'] != "")
			$output	= $this->getContentElementWrapper($this->conAttributes, $output);
		
		return $output;
	
	}	
	
}
