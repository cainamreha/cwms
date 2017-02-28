<?php
namespace Concise;



/**
 * TagcloudElement
 * 
 */

class TagcloudElement extends ElementFactory implements Elements
{
	
	/**
	 * Gibt ein TagcloudElement zurück
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
		########  Tag cloud  #########
		##############################
		
		$tagCon = explode("<>", $this->conValue);
		$output	= "";
		
		if(!isset($tagCon[0]))
			$tagCon[0] = 20;
		if(!isset($tagCon[2]))
			$tagCon[2] = 8;
		if(!isset($tagCon[3]))
			$tagCon[3] = 30;
		if(!isset($tagCon[4]))
			$tagCon[4] = -1004;
		if(!isset($tagCon[1]))
			$tagCon[1] = "";
		else {
			// Zunächst das entsprechende Modul einbinden (Tagcloud-Klasse)
			require_once PROJECT_DOC_ROOT."/inc/classes/Modules/class.Tags.php";

			$tags	= new Tags($this->DB, $this->lang, $this->group, $this->ownGroups);
			$tags->getTags($tagCon[1], $tagCon[0], $tagCon[2], $tagCon[3], $tagCon[4]);
			$output	= $tags->getTagCloud();
		}
		
		// Ggf. Attribute (Styles) Wrapper-div hinzufügen
		$output	= $this->getContentElementWrapper($this->conAttributes, $output, 'tagCloud module');

		return $output;
	
	}	
	
}
