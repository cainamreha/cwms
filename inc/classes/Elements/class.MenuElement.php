<?php
namespace Concise;


/**
 * MenuElement
 * 
 */

class MenuElement extends ElementFactory implements Elements
{
	
	/**
	 * Gibt ein MenuElement zurück
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
		###########  Menu  ###########
		##############################

		require_once PROJECT_DOC_ROOT."/inc/classes/Modules/class.Menu.php"; // Menu-Klasse einbinden
					
		$linkCon		= explode("<>", $this->conValue);
		$menu			= "";
		$menuOpenDiv	= "";
		$menuCloseDiv	= "";
		
		$o_menu			= new Menu($this->DB, $this->o_lng, $this->o_dispatcher, $this->o_page, $this->html5);

		if(!isset($linkCon[0])) // Header
			$linkCon[0] = "";
		if(!isset($linkCon[1])) // Menütyp
			$linkCon[1] = "";
		if(!isset($linkCon[2])) // Menüactive
			$linkCon[2] = 1;
		$linkCon[2] = $linkCon[2] == 1 ? "link" : "span";
		if(!isset($linkCon[3])) // Menüseparator
			$linkCon[3] = "";
		if(!isset($linkCon[4])) // Menügroup
			$linkCon[4] = false;
		if(!isset($linkCon[5])) // Menügroupext
			$linkCon[5] = "";
		if(!isset($linkCon[6])) // Menü style
			$linkCon[6] = 1;
		if(!isset($linkCon[7])) // Menü fixed
			$linkCon[7] = 0;
		if(!isset($linkCon[8])) // Search bar
			$linkCon[8] = false;
		if(!isset($linkCon[9])) // Logo
			$linkCon[9] = false;
		if(!isset($linkCon[10])) // Alignment
			$linkCon[10] = 0;
		if(!isset($linkCon[11])) // Lang menu
			$linkCon[11] = 0;
		if(!isset($linkCon[12])) // Menu item align
			$linkCon[12] = 0;
		if(!isset($linkCon[13])) // Collapsible
			$linkCon[13] = $linkCon[1] == "main" ? 1 : 0;
		
		if($linkCon[1] == "sub") {
			$linkCon[14] = parent::$activeBasePageLft;
			$linkCon[15] = parent::$activeBasePageRgt;
			$linkCon[16] = "";
		}
		else {
			$linkCon[14] = "";
			$linkCon[15] = "";
			$linkCon[16] = "";
		}

		$menuItem		= 1; // Menü-Item (Menüart)
		
		if($linkCon[1] == "top")
			$menuItem	= 2;
		if($linkCon[1] == "foot")
			$menuItem	= 3;
		
		
		// Ggf. Search bar Script
		if($linkCon[8]) {
			$this->scriptFiles["ajaxsearch"]	= "access/js/ajaxSearch.js"; // js-Datei einbinden
			$this->cssFiles[]					= "access/css/ajaxSearch.css"; // css-Datei einbinden
		}
		
		$classExt		= 'navigation ' . ($linkCon[1] == "sub" || $linkCon[1] == "parroot" || $linkCon[1] == "parsub" ? 'subNavi' : $linkCon[1] . 'Navi');
		
		$area		= ucfirst(str_replace("contents_", "", $this->conTable));
		$classExt  .= ' navi' . $area;
		
		// If navbar in sidebar, make vertical
		if($linkCon[12] == 1
		|| $area == "Left"
		|| $area == "Right"
		)
			$classExt  .= ' verticalNav';
		else
			$classExt  .= ' horizontalNav';
			
		// If fixed navbar
		if($linkCon[7]) {
			$classExt  .= ' fixedNav';
			$this->bodyClassStrings[]	= "cc-with-fixed-navbar";
		}
		
		// If allways collapse
		if($linkCon[13] == 2) {
			$classExt  .= ' navbar-always-collapse';
		}
		
		
		$output			= '<div class="' . $classExt . '">' . "\r\n";						
		
		if($linkCon[0] != "")
			$output	.= '<h2>' . htmlspecialchars($linkCon[0]) . '</h2>' . "\r\n";
		
		// Menü generieren
		$o_menu->menuStyle			= $linkCon[6];
		$o_menu->menuFixed			= $linkCon[7];
		$o_menu->menuAlign			= $linkCon[10];
		$o_menu->langMenu			= $linkCon[11];
		$o_menu->menuItemAlign		= $linkCon[12];
		$o_menu->collapsibleMenu	= $linkCon[13];


		$output			.= $o_menu->getMenu($this->pageId, $menuItem, $linkCon[1], $linkCon[2], $linkCon[3], $linkCon[4], $linkCon[5], $linkCon[14], $linkCon[15], $linkCon[16], $linkCon[8], $linkCon[9]);

		$output			.= '</div>';
		
		// Ggf. Attribute (Styles) Wrapper-div hinzufügen
		if($this->conAttributes['id'] != "" || $this->conAttributes['class'] != "" || $this->conAttributes['style'] != "")
			$output	= $this->getContentElementWrapper($this->conAttributes, $output);
		
		return $output;
	
	}	
	
}
