<?php
namespace Concise;

use Symfony\Component\EventDispatcher\Event;
use Concise\Events\Modules\MakeMenuEvent;



/**
 * ListmenuElement
 * 
 */

class ListmenuElement extends ElementFactory implements Elements
{
	
	public $langUrlPrefix	= "";
	public $menuLang		= "";
	
	/**
	 * Gibt ein ListmenuElement zurück
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
		#########  1D-Menu  ##########
		##############################
					
		$linkCon		= explode("<>", $this->conValue);
		$itemList		= "";
		
		if(!isset($linkCon[0]))
			$linkCon[0] = "";
		if(!isset($linkCon[1]))
			$linkCon[1] = "";
		if(!isset($linkCon[2]))
			$linkCon[2] = "";
		if(!isset($linkCon[3]))
			$linkCon[3] = "";
		if(!isset($linkCon[4]))	// Menü style
			$linkCon[4] = 1;
		if(!isset($linkCon[5])) // Menü fixed
			$linkCon[5] = 0;
		if(!isset($linkCon[6])) // Search bar
			$linkCon[6] = 0;
		if(!isset($linkCon[7])) // Logo
			$linkCon[7] = 0;
		if(!isset($linkCon[8])) // Align
			$linkCon[8] = 0;
		if(!isset($linkCon[9])) // Lang menu
			$linkCon[9] = 0;
		if(!isset($linkCon[10])) // Item align
			$linkCon[10] = 0;
		if(!isset($linkCon[11])) // collapsible
			$linkCon[11] = 1;

		// Link-Aliase
		$linkAlias	= array_filter(preg_replace("/\r/", "", explode("\n", $linkCon[1])));
		// Link-Namen
		$linkNames	= array_filter(preg_replace("/\r/", "", explode("\n", $linkCon[2])));
		
		$area		= ucfirst(str_replace("contents_", "", $this->conTable));
		
		$elementClass	= "linkList";
		
		if($linkCon[3] == "nav")
			$elementClass	= ' navi' . $area;
		
		// If navbar in sidebar, make vertical
		if($linkCon[10] == 1
		|| $area == "Left"
		|| $area == "Right"
		)
			$elementClass  .= ' verticalNav';
		else
			$elementClass  .= ' horizontalNav';
		
		// If fixed navbar
		if($linkCon[5]) {
			$elementClass  .= ' fixedNav';
			$this->bodyClassStrings[]	= "cc-with-fixed-navbar";
		}
		
		// If allways collapse
		if($linkCon[11] == 2) {
			$elementClass  .= ' navbar-always-collapse';
		}
	
		$wrapperClass	= "";
		$listClass		= "";
		$itemClass		= "cc-menuitem";		
		
		if($linkCon[3] == "nav") {
			$wrapperClass	= "{t_class:nav}" . ($linkCon[4] ? " {t_class:nav" . ($linkCon[4] == 2 ?  'inv' : 'def') . "}" : "") . ($linkCon[5] ? ($linkCon[5] == 2 ? ' {t_class:navaffix}' : ' {t_class:navfixed}') : '');
			$listClass		= "{t_class:navbar}";
			$itemClass	   .= " {t_class:navitem}";
		}
		if($linkCon[3] == "pills") {
			$listClass		= "{t_class:pills} {t_class:navstacked}";
		}
		if($linkCon[3] == "panel") {
			$wrapperClass	= "{t_class:panel}";
			$listClass		= "{t_class:listgroup}";
			$itemClass	   .= " {t_class:listitem}";
		}
		
		$l = 0;
		
		foreach($linkAlias as $item) {
		
			$classExt		= "";
			$linkClass		= "cc-menulink";
			
			if(strpos($item, "#") === 0
			|| strpos($item, "{root}/") !== false
			|| strpos($item, "{root}/") !== false
			|| strpos($item, "{sitelink}/") !== false
			) {
			
				if(strpos($item, "#") === 0) {
					$linkClass .= ' page-scroll';
					$intLink	= $item;
				}
				else {
					$intLink	= PROJECT_HTTP_ROOT . "/" . str_replace(array("{root}/","{sitelink}/"), "", $item) . PAGE_EXT;
				}

				if(parent::$currentURL == $intLink)
					$classExt = " active";
				
				$href		= htmlspecialchars($intLink);
			}
			else {
				if(strpos($linkCon[1], "https://") !== false)
					$href	= 'https://' . str_replace("https://", "", htmlspecialchars($item));
				else
					$href	= 'http://' . str_replace("http://", "", htmlspecialchars($item));
				
			}
			$itemList	.= '<li class="' . $itemClass . $classExt . '" role="presentation"><a href="' . $href . '" class="' . $linkClass . '">' . htmlspecialchars($linkNames[$l]) . '</a></li>' . PHP_EOL;
			
			$l++;
		}
		
		
		$output =	"";
		
		if($linkCon[0] != "") {
			
			$output	=	'<h2>' . htmlspecialchars($linkCon[0]) . '</h2>' . PHP_EOL;
			
			if($linkCon[3] == "panel")
				$output	=	'<div class="{t_class:panelhead}">' . $output . '</div>' . PHP_EOL;
		}
		
		
		// Falls nav
		if($linkCon[3] == "nav") {

			// Event-Klassen einbinden
			require_once PROJECT_DOC_ROOT."/inc/classes/Modules/events/event.MakeMenu.php";
			require_once PROJECT_DOC_ROOT."/inc/classes/Modules/class.Menu.php";
		
			$o_menu				= new Menu($this->DB, $this->o_lng, $this->o_dispatcher, $this->o_page, $this->html5);
	
			$this->langUrlPrefix				= HTML::getLangUrlPrefix($this->lang);
			$this->menuLang						= $this->lang;		
		
			// Ggf. Sprachauswahl-Menü anfügen
			$langDiv  	   = "";
			if($linkCon[9]) {
				$langDiv .= '<div class="{t_class:navrgt}">'.PHP_EOL;
				$langDiv .= $this->o_lng->getLangSelector($this->pageId, "flag", "");		
				$langDiv .= '</div>'.PHP_EOL;
			}
					
			// MakeMenu event
			$o_makeMenuEvent					= new MakeMenuEvent();
			
			$o_makeMenuEvent->menuType			= "list-" . $this->conTable . $this->conNum; // menu dom id
			$o_makeMenuEvent->menuAlign			= $linkCon[8];
			$o_makeMenuEvent->menuItemAlign		= $linkCon[10];
			$o_makeMenuEvent->menuFixed			= $linkCon[5];
			$o_makeMenuEvent->collapsibleMenu	= $linkCon[11];
			$o_makeMenuEvent->menuLogo			= $linkCon[7];
			$o_makeMenuEvent->langMenu			= $linkCon[9];
			$o_makeMenuEvent->langDiv			= $langDiv;
			$o_makeMenuEvent->framework			= parent::$styleDefs['framework'];
			$o_makeMenuEvent->html5				= $this->html5;
			$o_makeMenuEvent->dropdownTag		= 'hasChild';
			$o_makeMenuEvent->dropdownOpen		= '<ul class="' . $listClass . '">';
			$o_makeMenuEvent->dropdownClose		= '</ul>';
			$o_makeMenuEvent->navClass			= $wrapperClass;
			$o_makeMenuEvent->navbarClass		= $listClass;
			$o_makeMenuEvent->hasChildClass		= " hasChild";
			$o_makeMenuEvent->indexPageUrl		= $o_menu->getIndexPageUrl();
			
			
			// dispatch get menu head event
			$this->o_dispatcher->dispatch('listmenu.get_menu_head', $o_makeMenuEvent);
			

			// Div für Navi bei Verwendung von Frameworks
			$output .= $o_makeMenuEvent->getOutput();
			$output .= $itemList;
			$output .= $o_makeMenuEvent->navbarClose;
			$output .= $o_makeMenuEvent->navClose;
		
		}	
		else
			$output .= '<ul class="' . $listClass . '">' . $itemList . '</ul>' . PHP_EOL;
		
		
		// Ggf. Suchmaske anfügen
		if($linkCon[6]) {
			require_once PROJECT_DOC_ROOT."/inc/classes/Modules/class.Search.php";
			$o_search	= new Search($this->DB, $this->o_lng, SEARCH_TYPE);
			$output    .= $o_search->getSearchForm("navbar");
		}
		

		// Ggf. Wrapper
		if($wrapperClass != "")
			$output =	'<div class="' . $wrapperClass . '" role="navigation">' . PHP_EOL .
						$output .
						'</div>' . PHP_EOL;
		
		// Falls HTML5
		if($this->html5)
			$output = 	'<nav>' . PHP_EOL .
						$output .
						'</nav>' . PHP_EOL;
		
		// Attribute (Styles) Wrapper-div hinzufügen
		$output	= $this->getContentElementWrapper($this->conAttributes, $output, $elementClass);
		
		return $output;
	
	}	
	
}
