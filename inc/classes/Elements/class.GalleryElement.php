<?php
namespace Concise;



/**
 * GalleryElement
 * 
 */

class GalleryElement extends ElementFactory implements Elements
{
	
	/**
	 * Gibt ein GalleryElement zurück
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
		######  Bildergalerie  #######
		##############################
		
		// Zunächst das entsprechende Modul einbinden (Gallery-Klasse)
		require_once PROJECT_DOC_ROOT."/inc/classes/Modules/class.Gallery.php";
		
		$gallCon = explode("/", $this->conValue);
		
		if(!isset($gallCon[0]))
			$gallCon[0] = ""; // gallery name
		if(!isset($gallCon[1]))
			$gallCon[1] = ""; // gallery type
		if(!isset($gallCon[2]))
			$gallCon[2] = ""; // gallery header
		if(!isset($gallCon[3]))
			$gallCon[3] = 0; // show header
		if(!isset($gallCon[4]))
			$gallCon[4] = 0; // show description
		if(!isset($gallCon[5]))
			$gallCon[5] = 0; // use link
		if(!isset($gallCon[6]))
			$gallCon[6] = ""; // thumb number
		if(!isset($gallCon[7]) || $gallCon[7] == "")
			$gallCon[7] = 1; // auto
		if(!isset($gallCon[8]))
			$gallCon[8] = 1; // continous
		if(!isset($gallCon[9]))
			$gallCon[9] = 1200; // speed
		if(!isset($gallCon[10]))
			$gallCon[10] = 3500; // pause
		if(!isset($gallCon[11]))
			$gallCon[11] = 1; // controls
		if(!isset($gallCon[12]))
			$gallCon[12] = "num"; // controls-type

		
		
		// Evtl. GET-Parameter für Gallerie auslesen
		if($this->isMainContent
		&& isset($GLOBALS['_GET']['gall'])
		&& $GLOBALS['_GET']['gall'] != "") {
			
			$gallCon[0] = $GLOBALS['_GET']['gall'];

			// Galerienamen entschlüsseln
			require_once(PROJECT_DOC_ROOT."/inc/classes/Modules/class.myCrypt.php"); // Klasse myCrypt einbinden
			
			// myCrypt Instanz
			$crypt = new myCrypt();
		
			// Decrypt String
			$gallCon[0] = Gallery::getValidGallName($crypt->decrypt($gallCon[0]));
		}
		
		
		// data-attr zum merken des Galerienamens
		$this->conAttributes["data-gallname"]	= $gallCon[0];

		
		// Galerie-Objekt
		$o_gallery	= new Gallery($this->DB, $this->o_lng->lang, $gallCon[0], $gallCon[1], $gallCon[2], $gallCon[3], $gallCon[4], $gallCon[5], $gallCon[6]);
		
		$galleryType = $gallCon[1];

		// Script-Code
		if($galleryType != "") {
		
			$o_gallery->getGalleryCode($galleryType, $gallCon[7], $gallCon[8], $gallCon[9], $gallCon[10], $gallCon[11], $gallCon[12]); // JS-Code holen
			
			$this->mergeHeadCodeArrays($o_gallery); // pass head files
		
		}
		
		// Gallerie generieren
		$output =	$o_gallery->getGallery();
		
		// Ggf. Attribute (Styles) Wrapper-div hinzufügen
		$output	= $this->getContentElementWrapper($this->conAttributes, $output, $gallCon[0] . ' cc-gallery cc-module');
		
		$this->contentDefinitions[$this->conTable][$this->conNum]	= $gallCon; // Inhaltstypen in Array speichern

		return $output;
	
	}	
	
}
