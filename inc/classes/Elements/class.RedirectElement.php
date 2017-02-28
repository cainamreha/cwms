<?php
namespace Concise;



/**
 * RedirectElement
 * 
 */

class RedirectElement extends ElementFactory implements Elements
{
	
	/**
	 * Gibt ein RedirectElement zurück
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
		#########  Redirect  #########
		##############################
					
		$linkCon = explode("|", $this->conValue);
		
		if(!isset($linkCon[0]))
			$linkCon[0] = "";
		if(!isset($linkCon[1]))
			$linkCon[1] = 0;

		// Falls eine Seitenumleitung (intern)
		if(strpos($linkCon[0], "{root}/") !== false || strpos($linkCon[0], "{sitelink}/") !== false) {
			
			$linkCon[0] = str_replace(array("{root}", "{sitelink}"), PROJECT_HTTP_ROOT, $linkCon[0] . PAGE_EXT);
		}
		
		// Falls eine Umleitung auf ein Domument (e.g., pdf)
		elseif(strpos($linkCon[0], "{doc}/") !== false) {
			
			$linkCon[0]		= str_replace("{doc}/", "", $linkCon[0]);
		
			// Dokumentname ggf. verschlüsseln
			require_once(PROJECT_DOC_ROOT."/inc/classes/Media/class.FileOutput.php"); // Klasse FileOutput einbinden

			// Dokumentlinkname
			$docLink = FileOutput::getFileHash($linkCon[0], "doc");
			
			// Encrypt Name
			$linkCon[0] = PROJECT_HTTP_ROOT . "/redirect/" . $docLink;
		}
		
		// Falls dauerhafte PHP-Weiterleitung (Statuscode 301)
		if($linkCon[1])
			header("HTTP/1.1 301 Moved Permanently");
		
		// Umleitung
		header("Location: " . $linkCon[0]);
		exit;
	
	}	
	
}
