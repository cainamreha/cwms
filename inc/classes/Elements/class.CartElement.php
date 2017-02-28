<?php
namespace Concise;



/**
 * CartElement
 * 
 */

class CartElement extends ElementFactory implements Elements
{
	
	/**
	 * Gibt ein CartElement zurück
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
		########  Warenkorb  #########
		##############################
		
		// Zunächst das entsprechende Modul einbinden (Orderfm-Klasse)
		require_once PROJECT_DOC_ROOT."/inc/classes/Modules/class.Orderfm.php";
	
		$this->cssFiles[] 		= STYLES_DIR . "orderfm.css"; // css-Datei einbinden
		$this->scriptFiles[] 	= JS_DIR . "popup.js";
		
		$targetIDs		= explode("<>", $this->conValue);
		
		$targetPage 	= HTML::getLinkPath($targetIDs[0], "current", true, true);
		
		
		$o_oform	= new Orderfm($this->DB, $this->o_lng); // Bestell-Objekt
		$output		= $o_oform->getCart($targetPage); // Warenkorb generieren

		
		// Warenkorb-Buttons bei Artikeln hinzufügen, falls Bestelloption vorhanden
		parent::$o_mainTemplate->poolAssign["cart"]				= $output;
		
		
		// Links zu Popupseiten für AGBs und Versandinfo
		if(isset($targetIDs[1])) {
		
			$agbsLink		= HTML::getLinkPath($targetIDs[1], "current", true, true);
		
			// Button link
			$btnDefs	= array(	"href"		=> $agbsLink,
									"class"		=> 'link popup default {t_class:btnlink} {t_class:btnsm}',
									"text"		=> "{s_label:agb}",
									"attr"		=> 'rel="nofollow"',
									"icon"		=> ""
								);
				
			$btnAgb1	=	parent::getButtonLink($btnDefs);
		
			// Button link
			$btnDefs	= array(	"href"		=> $agbsLink,
									"class"		=> 'link popup formPopup {t_class:btninf} {t_class:btnsm}',
									"text"		=> "{s_label:agb}",
									"attr"		=> 'rel="nofollow"',
									"icon"		=> "info"
								);
				
			$btnAgb2	=	parent::getButtonLink($btnDefs);
			
			parent::$o_mainTemplate->poolAssign["agbsLink"]			= $btnAgb1;
			parent::$o_mainTemplate->poolAssign["agbsLinkOfm"]		= $btnAgb2;
		
		}
		if(isset($targetIDs[2])) {
		
			$shippingLink	= HTML::getLinkPath($targetIDs[2], "current", true, true);
		
			// Button link
			$btnDefs	= array(	"href"		=> $shippingLink,
									"class"		=> 'link popup default {t_class:btnlink} {t_class:btnsm}',
									"text"		=> "{s_link:shipping}",
									"attr"		=> 'rel="nofollow"',
									"icon"		=> ""
								);
				
			$btnShip1	=	parent::getButtonLink($btnDefs);
		
			// Button link
			$btnDefs	= array(	"href"		=> $shippingLink,
									"class"		=> 'link popup formPopup {t_class:btninf} {t_class:btnsm}',
									"text"		=> "{s_link:shipping}",
									"attr"		=> 'rel="nofollow"',
									"icon"		=> "send"
								);
				
			$btnShip2	=	parent::getButtonLink($btnDefs);
			
			parent::$o_mainTemplate->poolAssign["shippingLink"]		= $btnShip1;
			parent::$o_mainTemplate->poolAssign["shippingLinkOfm"]	= $btnShip2;
		
		}
		
		// Ggf. Attribute (Styles) Wrapper-div hinzufügen
		if($this->conAttributes['id'] != "" || $this->conAttributes['class'] != "" || $this->conAttributes['style'] != "")
			$output	= $this->getContentElementWrapper($this->conAttributes, $output);

		return $output;
	
	}	
	
}
