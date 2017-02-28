<?php
namespace Concise;



/**
 * SitemapElement
 * 
 */

class SitemapElement extends ElementFactory implements Elements
{
	
	/**
	 * Gibt ein SitemapElement zurück
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
		#########  Sitemap  ##########
		##############################
		
		require_once PROJECT_DOC_ROOT."/inc/classes/Modules/class.Menu.php"; // Menu-Klasse einbinden
		
		$o_menu	= new Menu($this->DB, $this->o_lng, $this->o_dispatcher, $this->o_page, $this->html5);
		
		$output	= $o_menu->getSitemap(); // Sitemap generieren
		
		// Ggf. Attribute (Styles) Wrapper-div hinzufügen
		if($this->conAttributes['id'] != "" || $this->conAttributes['class'] != "" || $this->conAttributes['style'] != "")
			$output	= $this->getContentElementWrapper($this->conAttributes, $output);

		return $output;
	
	}	
	
}
