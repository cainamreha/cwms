<?php
namespace Concise;

use Symfony\Component\EventDispatcher\Event;
use Concise\Events\Modules\MakeMenuEvent;


// Event-Klassen einbinden
require_once PROJECT_DOC_ROOT."/inc/classes/Modules/events/event.MakeMenu.php";


/**
 * Menu-Generator
 * 
 */

class Menu extends ContentsEngine
{
	
	/**
	 * Menüsprache
	 *
	 * @access public
     * @var    string
     */
	public $menuLang = "";
	
	/**
	 * Sprache Url-Prefix
	 *
	 * @access public
     * @var    string
     */
	public $langUrlPrefix = "";
	
	/**
	 * Menütabelle (pages)
	 *
	 * @access public
     * @var    string
     */
	public $menuTable = "";
	
	/**
	 * Zusammenfassung und Komprimierung von CSS-Dateien
	 *
	 * @access public
     * @var    boolean
     */
	public $combineCSS = true;
	
	/**
	 * Nav/Navbar class string
	 *
	 * @access public
     * @var    string
     */
	public $navClass = "{t_class:nav}";
	public $navbarClass = "{t_class:navbar}";
	
	/**
	 * Menü style
	 *
	 * @access public
     * @var    string
     */
	public $menuStyle = "";
	
	/**
	 * Menü alignment
	 *
	 * @access public
     * @var    string
     */
	public $menuAlign = "";
	
	/**
	 * Menü item alignment
	 *
	 * @access public
     * @var    string
     */
	public $menuItemAlign = "";
	
	/**
	 * Menü fixed position
	 *
	 * @access public
     * @var    string
     */
	public $menuFixed = "";
	
	/**
	 * Sprachauswahl-Menü integrieren
	 *
	 * @access public
     * @var    boolean
     */
	public $langMenu = false;
	
	/**
	 * Collapsible menu
	 *
	 * @access public
     * @var    boolean
     */
	public $collapsibleMenu = true;
	
	/**
	 * Menu vars
	 *
	 * @access private
     */
	private $hasChildClass	= "";
	private $dropdownClass	= "";
	private $dropdownExt	= "";
	private $dropdownSub	= "";
	private $dropdownTag	= "div"; // Bei Safari macht Div im List-Tag Probpleme, daher hier <span>
	private $dropdownOpen	= "<ul>";
	private $dropdownClose	= "</ul>";
	private $navbarClose	= "</ul>";
	private $navClose		= "";

	/**
	 * Konstruktor Menu-Klasse
	 * 
	 * @param	object	$DB					DB-Objekt
	 * @param	object	$o_lng				Sprachobjekt
	 * @param	object	$o_dispatcher		Event Dispatcher-Object
	 * @param	boolean	$o_page				Parameter-Object der aktuellen Seite
	 * @param	boolean	$html5				Html5
     * @access  public
	 */

	public function __construct($DB, $o_lng, $o_dispatcher, $o_page, $html5)
	{
		
		$this->DB				= $DB;
		$this->o_lng			= $o_lng;
		$this->o_dispatcher		= $o_dispatcher;
		$this->lang				= $this->o_lng->lang;
		$this->o_page			= $o_page;
	
		// Security-Objekt
		$this->o_security		= Security::getInstance();

		// Berechtigung
		$this->getAccessDetails();
	
		// Seiten-Details
		$this->queryCurrentPage	= $this->o_page->queryCurrentPage;
		$this->assignPageDetails($this->o_page);
		
		// Falls Änderungenvorschau und berechtigter Benutzer
		if($this->backendLog)		
			$this->editLang		= $this->o_lng->editLang; // aktuelle Sprache
		
		// Sprache
		if($this->backendLog)
			$this->menuLang = $this->editLang;
		else
			$this->menuLang = $this->lang;		

		// Ggf. Lang Url Prefix
		$this->langUrlPrefix	= HTML::getLangUrlPrefix($this->menuLang);

		$this->html5			= $html5;
	
	}

	
	/**
	 * Bestimmt DB-Tabelle bzw. View für die Menüerstellung
	 * 
     * @access  public
	 * @param	array Array
	 * @return	string
	 */
	public function getMenuTable($menuItem)
	{
	
		if(!is_int($menuItem))
			return DB_TABLE_PREFIX . parent::$tablePages;
		
		// Falls view existiert
		if(!$this->viewExists($menuItem)) {
			$this->menuTable	= DB_TABLE_PREFIX . parent::$tablePages;
			$this->makeView($menuItem);
		}
		else
			$this->menuTable	= DB_TABLE_PREFIX . "menuViewTable" . $menuItem;
		
		return $this->menuTable;
	
	}

	
	/**
	 * Überprüft ob DB-View vorhandenen
	 * 
     * @access  public
	 * @param	array Array
	 * @return	string
	 */
	public function viewExists($menuItem)
	{
	
		// Überprüpfen ob view existiert
		$view = $this->DB->query("CHECK TABLE `" . DB_TABLE_PREFIX . "menuViewTable" . $menuItem . "`");
		#var_dump($view[0]['Msg_type']);
		
		return strtolower($view[0]['Msg_type']) == "status" ? true : false;
	
	}

	
	/**
	 * Erstellt einen DB-View für Menütabellen
	 * 
     * @access  public
	 * @param	array Array
	 * @return	string
	 */
	public function makeView($menuItem)
	{
	
		$view = $this->DB->query("CREATE OR REPLACE VIEW `" . DB_TABLE_PREFIX . "menuViewTable" . $menuItem . "` 
										AS 
										SELECT * 
											FROM `" . DB_TABLE_PREFIX . parent::$tablePages . "` 
										WHERE `menu_item` = '$menuItem'
										");

		#var_dump($view);
		return $view;
	
	}
	
	
	/**
	 * Erstellt ein Menue
	 * 
     * @access	public
	 * @param	string aktuelle Seite (ID)
	 * @param	string Menüitem (default = 1 (Hauptmenü))
	 * @param	string Menütyp (default = main)
	 * @param	string aktiver Menüpunkt (default = span)
	 * @param	string Separator für bc Menue
	 * @param	string alternativer Menüpunkttitel
	 * @param	boolean Hauptmenüpunkt mit Unterpunkten gruppieren und Hauptmenüpunkt als ersten Gruppenpunkt einsetzen (default = false)
	 * @param	string lft-Wert des Basismenüpunkts
	 * @param	string rgt-Wert des Basismenüpunkts
	 * @param	string Alternativer Menüpunktname
	 * @param	boolean/int Search bar anzeigen
	 */
	public function getMenu($currentPageId, $menuItem = 1, $menuType = "main", $activeItem = "span", $separator = "", $groupSubmenuItems = true, $groupBaseItemExt = "", $baseItemLft = "", $baseItemRgt = "", $replaceTitle = "", $searchBar = false, $menuLogo = false)
	{
	
		$menuOutput			= "";
		
		// Tabelle bzw. View ermitteln
		$this->menuTable	= $this->getMenuTable($menuItem);
		
	
		// Falls optionaler Parameter $menuType = "bc" angegeben ist, Breadcrumb-Menü ausgeben
		if($menuType == "bc")			
			$menuOutput	.= $this->getBreadcrumbMenu($currentPageId, $activeItem, $separator);
		
		
		// Falls optionaler Parameter $menuType = "sub" oder "parroot" oder "parsub" angegeben ist, Untermenü des jeweiligen Punktes bzw. des Elternpunkts ausgeben
		elseif($menuType == "sub" || $menuType == "parroot" || $menuType == "parsub")			
			$menuOutput	.= $this->getSubMenu($currentPageId, $menuItem, $menuType, $activeItem, $separator, $groupSubmenuItems, $groupBaseItemExt, $baseItemLft, $baseItemRgt, $replaceTitle);
		
		
		// Andernfalls vollständiges normales Menü (main, top oder foot) ausgeben
		else
			$menuOutput	.= $this->getDefaultMenu($currentPageId, $menuItem, $menuType, $activeItem, $separator, $groupSubmenuItems, $groupBaseItemExt, $baseItemLft, $baseItemRgt, $replaceTitle, $searchBar, $menuLogo);


		if($menuType != "bc")
			$this->navClass	=	"{t_class:nav}" .
								($this->menuStyle == 2 ? " {t_class:navinv}" : ($this->menuStyle ? " {t_class:navdef}" : "")) .
								($this->menuFixed == 1 ? " {t_class:navfixed}" : "") .
								($this->menuFixed == 2 ? " {t_class:navaffix}" : "");

		
		// Falls collapsibleMenu
		if($this->collapsibleMenu)
			$this->navClass	.= ' cc-navbar-collapsible';
		else
			$this->navClass	.= ' cc-navbar-expanded';
		
		
		// Falls HTML5, nav-Tag einfügen
		if($this->html5)
			$menuOutput =	'<nav id="' . $menuType . 'Nav" class="' . $this->navClass . '">' . PHP_EOL .
							$menuOutput .
							'</nav>' . PHP_EOL;
		

		return $menuOutput;
	
	}


	/**
	 * Erstellt ein Unter-Menü
	 * 
     * @access	public
     * @return 	string
	 */
	public function getDefaultMenu($currentPageId, $menuItem, $menuType, $activeItem, $separator, $groupSubmenuItems, $groupBaseItemExt, $baseItemLft, $baseItemRgt, $replaceTitle, $searchBar = false, $menuLogo = false)
	{
	
		$menuOutput				= "";
		$base					= false;
		$this->dropdownTag		= Log::$browser == "safari" ? 'span' : 'div'; // Bei Safari macht Div im List-Tag Probpleme, daher hier <span>
		$this->hasChildClass	= " hasChild";
		$groupBaseItemExt		= explode(",", $groupBaseItemExt);
		$currGroupBaseItemExt	= reset($groupBaseItemExt);
		
		// Schließende Div Tag für Adminbereich weglassen
		if($this->adminPage)
			$this->navClose		= false;

		
		// Falls nur die Hauptpunkte ausgegeben werden sollen
		if($menuType == "base")
			$base = true;
		
		
		// Falls eine Sitemap ausgegeben werden soll
		if($menuType == "sitemap") {
			$menuOutput	.=	'<ul class="sitemap {t_class:listgroup}">' . PHP_EOL;  // Ausgabe des Menues mit Listen-id = Tabellenname
			$this->dropdownClass	= "{t_class:listgroup}";
		}
		
		else {
			
			switch($menuItem) {
				
				case "2":
					$menuType = "top";
					break;
				
				case "3":
					$menuType = "foot";
					break;
				
				default:
					$menuType = "main";
			}
		
		
			// Ggf. Sprachauswahl-Menü anfügen
			$langDiv  	   = "";
			if($this->langMenu) {
				$langDiv .= '<div class="{t_class:navrgt}">'.PHP_EOL;
				$langDiv .= $this->o_lng->getLangSelector($this->pageId, "flag", "");		
				$langDiv .= '</div>'.PHP_EOL;
			}
		
			
			// Div für Navi bei Verwendung von Frameworks
			if(!$this->adminPage) {
	
				// MakeMenu event
				$o_makeMenuEvent					= new MakeMenuEvent();
				
				$o_makeMenuEvent->menuType			= $menuType;
				$o_makeMenuEvent->menuAlign			= $this->menuAlign;
				$o_makeMenuEvent->menuItemAlign		= $this->menuItemAlign;
				$o_makeMenuEvent->menuFixed			= $this->menuFixed;
				$o_makeMenuEvent->collapsibleMenu	= $this->collapsibleMenu;
				$o_makeMenuEvent->menuLogo			= $menuLogo;
				$o_makeMenuEvent->langMenu			= $this->langMenu;
				$o_makeMenuEvent->langDiv			= $langDiv;
				$o_makeMenuEvent->framework			= parent::$styleDefs['framework'];
				$o_makeMenuEvent->html5				= $this->html5;
				$o_makeMenuEvent->groupSubmenuItems	= $groupSubmenuItems;
				$o_makeMenuEvent->dropdownTag		= $this->dropdownTag;
				$o_makeMenuEvent->dropdownOpen		= $this->dropdownOpen;
				$o_makeMenuEvent->dropdownClose		= $this->dropdownClose;
				$o_makeMenuEvent->navClass			= $this->navClass;
				$o_makeMenuEvent->navClose			= $this->navClose;
				$o_makeMenuEvent->navbarClass		= $this->navbarClass;
				$o_makeMenuEvent->hasChildClass		= $this->hasChildClass;
				$o_makeMenuEvent->indexPageUrl		= $this->getIndexPageUrl();
				
				
				// dispatch get menu head event
				$this->o_dispatcher->dispatch('menu.get_menu_head', $o_makeMenuEvent);
				
				$this->navClass			= $o_makeMenuEvent->navClass;
				$this->navbarClass		= $o_makeMenuEvent->navbarClass;
				$this->hasChildClass	= $o_makeMenuEvent->hasChildClass;
				$this->dropdownTag		= $o_makeMenuEvent->dropdownTag;
				$this->dropdownClass	= $o_makeMenuEvent->dropdownClass;
				$this->dropdownExt		= $o_makeMenuEvent->dropdownExt;
				$this->dropdownSub		= $o_makeMenuEvent->dropdownSub;
				$this->dropdownOpen		= $o_makeMenuEvent->dropdownOpen;
				$this->dropdownClose	= $o_makeMenuEvent->dropdownClose;
				$this->navbarClose		= $o_makeMenuEvent->navbarClose;
				$this->navClose			= $o_makeMenuEvent->navClose;

				// Menu output
				$menuOutput .=	$o_makeMenuEvent->getOutput();
			}
			else
				$menuOutput .=	'<ul id="' . $menuType . '_menu" class="' . $this->navbarClass . '">' . PHP_EOL;  // Ausgabe des Menues mit Listen-id = Tabellenname
		
		}
		
		
		// Parameter aller Menuepunkte auslesen
		$query	= $this->getMenuItemsFromDB($this->menuTable, $menuItem);

		
		if(is_array($query)
		&& count($query) > 0
		) {
			
			$rgt			= 1;
			$closeList		= ""; 
			
			$menuLevel		= 1; // Menuepunktlevel
			$level			= 0; // aktueller Level
			$mainLevel		= 1; // Hauptlevel
			$curLft			= 0; // aktueller lft-Wert
			$curRgt			= 0; // aktueller rgt-Wert
			$k				= 0; // Menuepunktzähler
			$editLang		= "";
			
			
			// Falls Adminseite, aktuelle Editiersprache hinzufügen
			if($this->adminPage && $this->backendLog)
				$editLang	=  '?lang=' . $this->menuLang;

				
			// Falls nur die Hauptpunkte ausgegeben werden sollen
			if($base === true) {
				// Schleife zum Bestimmern der lft und rgt Werte zur Markierung der Elternpunkte
				foreach($query as $row) { 
					if($row['page_id'] == $currentPageId) {
						$curLft = $row['lft']; // lft-Wert
						$curRgt = $row['rgt']; // rgt-Wert
					}
				}
			}
			
			
			foreach($query as $row) { // Schleife zum Ausgeben der Menupunktliste
				
				$i = $rgt; // speichert letzten rgt-Wert
				$k++;
				
				$menuLevel		= $row['menulevel']; // Level des Knotenpunkts
				$pageId			= $row['page_id']; // Menuepunktid
				$group			= explode(",", $row['group']);
				$menuTitle		= $row['title_' . $this->menuLang];
				$title			= $replaceTitle == "" ? $menuTitle : $replaceTitle; // Menuepunkttitel
				$alias			= $row['alias_' . $this->menuLang]; // Menuepunktalias
				$parentAliases	= "";
				$robots			= $row['robots'] ? '' : ' rel="nofollow"'; // Robots meta tag
				$lft			= $row['lft']; // lft-Wert
				$rgt			= $row['rgt']; // rgt-Wert
				$level			= $lft; // Level des Knotenpunkts
				$indexPage		= $row['index_page'];
				
				// Umbruch bei bestimmten Menuetiteln
				#if($title == "Beispieltitellang" && $menuType == "main") $title = "Beispieltitel-lang";

				
				// Falls nur die Hauptpunkte ausgegeben werden sollen
				if($base === true) {
									
					while($level >= $i+2) { // Solange der aktuelle lft-Wert größer als oder gleich ist wie der alte rgt-Wert +2...
	
						$menuOutput .= $closeList; // Schließen-Tags anhängen
						$level--; // Dekrement
					}
						
					if($menuLevel == 1 && (in_array("public", $group) || in_array($this->group, $group) || (count(array_intersect($this->ownGroups, $group)) > 0) || $this->editorLog)) {
							
						$liClass		= 'level-' . $menuLevel . ' item-' . $k . ' id-' . $pageId . ($indexPage ? ' indexPage' : '');
						$linkClass		= '{t_class:navlink}';

						if($currentPageId == $pageId || ($curLft > $lft && $curRgt < $rgt)) {
							
							parent::$activeBasePageId 		= $pageId;
							parent::$activeBasePageName		= $title;
							parent::$activeBasePageLft		= $lft;
							parent::$activeBasePageRgt		= $rgt;
							
							$menuOutput .= '<li class="active ' . $liClass . '">';
							
							if($activeItem == "span")
								$menuOutput .= '<span' . ($replaceTitle != "" ? ' title="' . $menuTitle . '"' : '') . '>' . $title . '</span>' . $separator . '</li>' . PHP_EOL; // Aktuellen Menuepunkt ohne Link ausgeben
							else
								$menuOutput .= '<a href="' . PROJECT_HTTP_ROOT . '/' . $this->langUrlPrefix . $alias . PAGE_EXT . $editLang . '"' . ($replaceTitle != "" ? ' class="' . $linkClass . '" title="' . $menuTitle . '"' : '') . $robots . '>' . $title . '</a>' . $separator . '</li>' . PHP_EOL; // Menuepunkte mit Links ausgeben
						}
						else
							$menuOutput .= '<li class="' . $liClass . '"><a href="' . PROJECT_HTTP_ROOT . '/' . $this->langUrlPrefix . $alias . PAGE_EXT . $editLang . '"' . ($replaceTitle != "" ? ' class="' . $linkClass . '" title="' . $menuTitle . '"' : '') . $robots . '>' . $title . '</a>' . $separator . '</li>' . PHP_EOL; // Menuepunkte mit Links ausgeben
					}
					
					while($menuLevel > $mainLevel) { // Solange der aktuelle lft-Wert größer als der alte rgt-Wert +2...
						$menuLevel--; // Dekrement
					}
				}
				
				else {
				
				
					// If full folder url depth
					if(CC_USE_FULL_PAGEURL) {
					
						// Datenbanksuche nach Elternknoten des aktuellen Menuepunkts
						$queryParents = $this->DB->query( "SELECT id, `page_id`, `alias_" . $this->menuLang . "` 
																FROM `" . $this->menuTable . "` 
																WHERE lft < $lft AND rgt > $rgt 
																AND lft > 1  
																AND `menu_item` = '$menuItem' 
																ORDER BY lft"
																);
						
						$qp = 0;
						
						
						// Elternaliase für Pfad speichern
						if(is_array($queryParents)
						&& count($queryParents) > 0
						) {
							foreach($queryParents as $parentAlias) {
								$parentAliases .= $parentAlias['alias_' . $this->menuLang] . "/";
								
								// Eltern-Root-ID speichern
								if($qp == 0 && $currentPageId == $pageId)
									parent::$rootPageId		= $parentAlias['page_id']; // Root-Elternseiten-ID an globale Variable übergeben (kann im Template über (rootpageid) eingebunden werden)
								$qp++;
							}
						}
						
						// Elternseiten-ID speichern
						if($qp > 0 && $currentPageId == $pageId)
							parent::$parentPageId	= $queryParents[--$qp]['page_id']; // Elternseiten-ID an globale Variable übergeben (kann im Template über (parpageid) eingebunden werden)
					}
					
					
					while($level >= $i+2) { // Solange der aktuelle lft-Wert größer als oder gleich ist wie der alte rgt-Wert +2...
						
						$trimmedMo	= trim($menuOutput);
	
						if(substr($trimmedMo, strlen($trimmedMo)-5, 5) == "</li>") { // Falls schon ein li-Tag am Ende des Menues ist,...
							$closeList = $this->dropdownClose . PHP_EOL . '</li>' . PHP_EOL; // ...Liste schließen-Tag einfügen
						}
						else					
							$closeList = '</li>' . PHP_EOL . $this->dropdownClose . PHP_EOL . '</li>' . PHP_EOL; // Sonst Listenpunkt und Liste schließen
							
						$menuOutput .= $closeList; // Schließen-Tags anhängen
						$level--; // Dekrement
					}
					
					
					// Falls eine Berechtigung für die Seite vorliegt
					if(in_array("public", $group) || in_array($this->group, $group) || (count(array_intersect($this->ownGroups, $group)) > 0) || $this->editorLog) {
						
						$menuListItem	= "";
						$itemLink		= "";
						$groupItemLink	= "";
						$hasChild		= $lft < $rgt-1 ? 1 : 0;
						$subMenuClass	= $hasChild ? ($menuLevel == 1 ? $this->hasChildClass : $this->dropdownSub) : '';
						
						$liClass		= '{t_class:navitem} level-' . $menuLevel . ' item-' . $k . ' id-' . $pageId . $subMenuClass . ($indexPage ? ' indexPage' : '');
						$linkClass		= '{t_class:navlink}';
						
						$itemLink	.= '<a href="' . PROJECT_HTTP_ROOT . '/' . $this->langUrlPrefix . $parentAliases . $alias . PAGE_EXT . $editLang . '" class="' . $linkClass . '"' . $robots . '>' . $title . '</a>';
						
						if($hasChild
						&& $groupSubmenuItems
						&& $this->dropdownExt != ""
						) {
							$groupItemLink	= $itemLink;
							$itemLink		= str_replace("{menutitle}", $title, $this->dropdownExt);
							$itemLink		= str_replace("{caret}", "{t_class:caret" . ($menuLevel > 1 ? "rgt" : "") . "}", $itemLink);
							$itemLink		= str_replace("{dropdowntarget}", '.id-' . $pageId, $itemLink);
						}
						
						if($currentPageId == $pageId) {
							$menuListItem .=	'<li class="active ' . $liClass . '">' . PHP_EOL;
							
							if($activeItem == "span")
								$menuListItem .= '<span>' . $title . '</span>' . ($k < count($query) ? $separator : ''); // Aktuellen Menuepunkt ohne Link ausgeben
							else
								$menuListItem .= $itemLink . ($k < count($query) ? $separator : ''); // Menuepunkte mit Links ausgeben
						}
						elseif(in_array($alias, parent::$parentAliases))
							$menuListItem .= '<li class="active ' . $liClass . '">' .PHP_EOL .
							$itemLink . ($k < count($query) ? $separator : ''); // Menuepunkte mit Links ausgeben
							
						else
							$menuListItem .= '<li class="' . $liClass . '">' .PHP_EOL .
							$itemLink . ($k < count($query) ? $separator : ''); // Menuepunkte mit Links ausgeben
					
					
						$menuOutput .= $menuListItem;
					
					
						// Wenn der lft-Wert kleiner ist als der rgt-Wert -1, beginnt eine neue Liste
						if($lft < $rgt-1) {
						
							// Untermenu beginnen
							$menuOutput .= $this->dropdownOpen;
							
							// Falls das Untermenü mit dem Hauptpunkt gruppiert werden soll, Hauptpunkt als ersten Punkt einfügen
							if($groupSubmenuItems){
							
								if($hasChild
								&& $this->dropdownExt != ""
								)
									$menuListItem 	= str_replace($itemLink, $groupItemLink, $menuListItem);
								
								$menuListItem 	= str_replace($subMenuClass . '"', '"', $menuListItem);
								$menuListItem 	= str_replace(' level-' . $menuLevel, ' level-' . ($menuLevel +1), $menuListItem);
								$menuListItem	= str_replace('>' . $title . '<', '>' . $title . $currGroupBaseItemExt . '<', $menuListItem);
								if($currentPageId != $pageId)
									$menuListItem 	= str_replace('class="active ', 'class="', $menuListItem);
							
								$menuOutput 			.= $menuListItem . '</li>' . PHP_EOL;
																
								$nextGroupBaseItemExt = next($groupBaseItemExt);
								
								if($nextGroupBaseItemExt !== false)
									$currGroupBaseItemExt = $nextGroupBaseItemExt;
							
							}
						}
						else {
							
							################  Einfügen von Artikelkategorien  ################
							/*
							if($pageId == 12) {
								$this->parentObj->rootCatID = 0;
								$this->parentObj->catTable = "articles_categories";
								$this->parentObj->urlCatPath = "";
								$menuOutput .= '<ul>' . str_replace(" class=\"dataMenu\"", "", ModulesData::getDataMenu("articles", "catmenu", "2", "", "", "", "ORDER BY `sort_id`",PROJECT_HTTP_ROOT."/Produkte"));
								$menuOutput .= '</li>' . PHP_EOL;
							}
							################  Einfügen von Artikelkategorien  ################
							*/
							
							$menuOutput .= '</li>' . PHP_EOL;
						}
						
					} // Ende if Berechtigung
					
				} // Ende else
			
			} // Ende foreach
			
			while($menuLevel > $mainLevel) { // Solange der aktuelle lft-Wert größer als oder gleich ist wie der alte rgt-Wert +2...

				$closeList = $this->dropdownClose . '</li>' . PHP_EOL; // ...Liste schließen-Tag einfügen
					
				$menuOutput .= $closeList; // Schließen-Tags anhängen
				$menuLevel--; // Dekrement
			}
			
			
			// Falls kein Listenpunkt vorhanden, leeren Punkt erzeugen
			if(strpos($menuOutput, "<li") === false)
				$menuOutput .= '<li>&nbsp;</li>' . PHP_EOL;
			
			
			$menuOutput .= $this->navbarClose;
		}
		else
			$menuOutput .= "&nbsp;" . $this->navbarClose;
		
		
		
		// Ggf. Suchmaske anfügen
		if($searchBar) {
			require_once PROJECT_DOC_ROOT."/inc/classes/Modules/class.Search.php";
			$o_search		= new Search($this->DB, $this->o_lng, SEARCH_TYPE);
			$menuOutput    .= $o_search->getSearchForm("navbar");
		}
		
		
		// Navi schließen
		$menuOutput .= $this->navClose;

		return $menuOutput;
	
	}


	/**
	 * Erstellt ein Unter-Menü
	 * 
     * @access	public
     * @return 	string
	 */
	public function getSubMenu($currentPageId, $menuItem, $menuType, $activeItem, $separator, $groupSubmenuItems, $groupBaseItemExt, $baseItemLft, $baseItemRgt, $replaceTitle)
	{
	
		$menuLevel				= 1; // Menuepunktlevel
		$i						= 1; // speichert letzten Menulevel-Wert
		$closeList				= ""; 
		$parentItems			= array();
		$menuOutput				= "";
		$this->hasChildClass	= " hasChild";
		$this->navClass			= "";
		$groupBaseItemExt		= explode(",", $groupBaseItemExt);
		$currGroupBaseItemExt	= reset($groupBaseItemExt);
		
		
		if(count($this->queryCurrentPage) > 0 || ($menuType == "sub" && $baseItemLft != "")) {
			
			// Submenü (definierter Unterpunkt)
			if($baseItemLft != "") {
				$lft = $baseItemLft;
				$rgt = $baseItemRgt;
				$currLft = $this->queryCurrentPage['lft'];
				$currRgt = $this->queryCurrentPage['rgt'];
				
				$queryParentItems = $this->DB->query( "SELECT `page_id` 
																FROM `" . $this->menuTable . "` 
																WHERE lft < $currLft AND rgt > $currRgt 
																AND `menu_item` = $menuItem 
																ORDER BY lft"
																);
				for($i=0; $i<count($queryParentItems); $i++) {
					$parentItems[] = ($queryParentItems[$i]['page_id']);
				}
				
			}
			
			// Root-Elternmenü
			elseif($menuType == "parroot") {
				
				$currLft = $this->queryCurrentPage['lft'];
				$currRgt = $this->queryCurrentPage['rgt'];
				
				$queryParentItems = $this->DB->query( "SELECT `page_id`, lft, rgt 
																FROM `" . $this->menuTable . "` 
																WHERE lft < $currLft AND rgt > $currRgt 
																AND lft > 1 
																AND `menu_item` = $menuItem 
																ORDER BY lft"
																);
				
				if(count($queryParentItems) > 0) {
					$lft = $queryParentItems[0]['lft'];
					$rgt = $queryParentItems[0]['rgt'];
					
					for($i=0; $i<count($queryParentItems); $i++) {
						$parentItems[] = ($queryParentItems[$i]['page_id']);
					}
				}
				else {
					$lft = $this->queryCurrentPage['lft'];
					$rgt = $this->queryCurrentPage['rgt'];
				}
				
			}
			
			// Elternknotenmenü
			elseif($menuType == "parsub") {
				
				$currLft = $this->queryCurrentPage['lft'];
				$currRgt = $this->queryCurrentPage['rgt'];
				
				$queryParentItem = $this->DB->query( "SELECT `page_id`, lft, rgt 
																FROM `" . $this->menuTable . "` 
																WHERE lft < $currLft AND rgt > $currRgt 
																AND `menu_item` = $menuItem 
																ORDER BY rgt-$currRgt ASC
																LIMIT 1"
																);
				
				if(count($queryParentItem) > 0) {
					$lft = $queryParentItem[0]['lft'];
					$rgt = $queryParentItem[0]['rgt'];
					
					$parentItems[] = ($queryParentItem[0]['page_id']);

				}
				else {
					$lft = $this->queryCurrentPage['lft'];
					$rgt = $this->queryCurrentPage['rgt'];
				}
				
			}
			
			// Submenü (aktuelle Seite)
			else {
				$lft = $this->queryCurrentPage['lft'];
				$rgt = $this->queryCurrentPage['rgt'];
			}
			
			
			// Datenbanksuche nach aktuellem Menuepunkt mit Kindelementen
			$query = $this->DB->query("SELECT n.*
											FROM `" . $this->menuTable . "` AS n, `" .
											$this->menuTable . "` AS p 
											WHERE n.lft BETWEEN p.lft+1 AND p.rgt-1  
											AND n.lft BETWEEN '$lft' +1 AND '$rgt' -1 
											AND n.`menu_item` = '$menuItem' 
											GROUP BY n.lft 
											ORDER BY n.lft 
											");
			#var_dump($query);
			
			if(is_array($query)
			&& count($query) > 0
			) {
				
				$lft = $query[0]['lft'];
				$rgt = $query[0]['rgt'];
				
	
				// MakeMenu event
				$o_makeMenuEvent					= new MakeMenuEvent();
				
				$o_makeMenuEvent->menuType			= $menuType;
				$o_makeMenuEvent->menuAlign			= $this->menuAlign;
				$o_makeMenuEvent->menuItemAlign		= $this->menuItemAlign;
				$o_makeMenuEvent->menuFixed			= $this->menuFixed;
				$o_makeMenuEvent->collapsibleMenu	= $this->collapsibleMenu;
				$o_makeMenuEvent->menuLogo			= false;
				$o_makeMenuEvent->langMenu			= $this->langMenu;
				$o_makeMenuEvent->framework			= parent::$styleDefs['framework'];
				$o_makeMenuEvent->html5				= $this->html5;
				$o_makeMenuEvent->groupSubmenuItems	= $groupSubmenuItems;
				$o_makeMenuEvent->dropdownTag		= $this->dropdownTag;
				$o_makeMenuEvent->dropdownOpen		= $this->dropdownOpen;
				$o_makeMenuEvent->dropdownClose		= $this->dropdownClose;
				$o_makeMenuEvent->navClass			= $this->navClass;
				$o_makeMenuEvent->navbarClass		= $this->navbarClass;
				$o_makeMenuEvent->hasChildClass		= $this->hasChildClass;
				
				
				// dispatch get menu head event
				$this->o_dispatcher->dispatch('menu.get_menu_head', $o_makeMenuEvent);
				
				$this->navClass			= $o_makeMenuEvent->navClass;
				$this->navbarClass		= $o_makeMenuEvent->navbarClass;
				$this->hasChildClass	= $o_makeMenuEvent->hasChildClass;
				$this->dropdownTag		= $o_makeMenuEvent->dropdownTag;
				$this->dropdownClass	= $o_makeMenuEvent->dropdownClass;
				$this->dropdownExt		= $o_makeMenuEvent->dropdownExt;
				$this->dropdownSub		= $o_makeMenuEvent->dropdownSub;
				$this->dropdownOpen		= $o_makeMenuEvent->dropdownOpen;
				$this->dropdownClose	= $o_makeMenuEvent->dropdownClose;
				$this->navbarClose		= $o_makeMenuEvent->navbarClose;
				$this->navClose			= $o_makeMenuEvent->navClose;

				
				// Div für Navi bei Verwendung von Frameworks
				$menuOutput .=	$o_makeMenuEvent->getOutput();
				
				
				foreach($query as $row) { // Schleife zum Ausgeben der Menupunktliste
					
					$oldLft			= $lft;
					$oldRgt			= $rgt;
					
					$lft			= $row['lft']; // lft-Wert
					$rgt			= $row['rgt']; // rgt-Wert
					$pageId			= $row['page_id']; // Menuepunktid
					$group			= explode(",", $row['group']); // Benutzergruppe
					$title			= $row['title_' . $this->menuLang]; // Menuepunkttitel
					$alias			= $row['alias_' . $this->menuLang]; // Menuepunktalias
					$parentAliases	= "";
					$robots			= $row['robots'] ? '' : ' rel="nofollow"'; // Robots meta tag
					$hasChild		= $lft < $rgt-1 ? 1 : 0;
					
					$liClass		= '{t_class:navitem}';
					$linkClass		= '{t_class:navlink}';
					
					
					// Falls eine Berechtigung für die Seite vorliegt
					if(in_array("public", $group) || in_array($this->group, $group) || (count(array_intersect($this->ownGroups, $group)) > 0) || in_array($this->ownGroups, $group) || $this->editorLog) {
					
				
						// If full folder url depth
						if(CC_USE_FULL_PAGEURL) {
						
							// Datenbanksuche nach Elternknoten des aktuellen Menuepunkts
							$queryParents = $this->DB->query( "SELECT `alias_" . $this->menuLang . "` 
																	FROM `" . $this->menuTable . "` 
																	WHERE lft < $lft AND rgt > $rgt 
																	AND `menu_item` = $menuItem 
																	ORDER BY lft"
																	);
							
							#var_dump($queryParents);
							
							foreach($queryParents as $parentAlias) {
								if($parentAlias['alias_' . $this->menuLang] != "")
									$parentAliases .= $parentAlias['alias_' . $this->menuLang] . "/";
							}
						}
						
						$j = $lft;
						
						while($j >= $oldRgt+2) { // Solange der aktuelle lft-Wert größer als oder gleich ist wie der alte rgt-Wert +2...
						
							$trimmedMo	= trim($menuOutput);
							
							if(substr($trimmedMo, strlen($trimmedMo)-5, 5) == "</li>") { // Falls schon ein li-Tag am Ende des Menues ist,...
								$closeList = $this->dropdownClose . PHP_EOL . '</li>' . PHP_EOL; // ...Liste schließen-Tag einfügen
							}
							else					
								$closeList = '</li>' . PHP_EOL . $this->dropdownClose . PHP_EOL . '</li>' . PHP_EOL; // Sonst Listenpunkt und Liste schließen
								
							$menuOutput .= $closeList; // Schließen-Tags anhängen
							$j--; // Dekrement
							$menuLevel--; // Dekrement
						}
						
						$menuListItem	= "";
						$itemLink		= "";
						$groupItemLink	= "";
						$subMenuClass	= $hasChild ? ($menuLevel == 1 ? $this->hasChildClass : $this->dropdownSub) : '';
						
						$itemLink	   .= '<a href="' . PROJECT_HTTP_ROOT . '/' . $this->langUrlPrefix . $parentAliases . $alias . PAGE_EXT . '" class="' . $linkClass . '"' . $robots . '>' . $title . '</a>';
						
						if($hasChild
						&& $groupSubmenuItems
						&& $this->dropdownExt != ""
						) {
							$groupItemLink	= $itemLink;
							$itemLink		= str_replace("{menutitle}", $title, $this->dropdownExt);
							$itemLink		= str_replace("{caret}", "{t_class:caret" . ($menuLevel > 1 ? "rgt" : "") . "}", $itemLink);
							$itemLink		= str_replace("{dropdowntarget}", '.id-' . $pageId, $itemLink);
						}

						
						if($currentPageId == $pageId || in_array($pageId, $parentItems)) {
						
							$menuListItem = '<li class="' . $liClass . ' active id-' . $pageId . $subMenuClass . '">';
							
							if($activeItem == "span")
								$menuListItem .= '<span>' . $title . "</span>".PHP_EOL; // Aktuellen Menuepunkt ohne Link ausgeben
							else
								$menuListItem .= $itemLink; // Menuepunkte mit Links ausgeben
						}
						else
							$menuListItem = '<li class="' . $liClass . ' id-' . $pageId . $subMenuClass . '">' . $itemLink; // Menuepunkte mit Links ausgeben
					
						if($lft < $rgt-1) {
							
							$menuOutput		.= $menuListItem;
						
							// Untermenu beginnen
							$menuOutput .= $this->dropdownOpen;
						
							if($groupSubmenuItems){
								
								if($hasChild
								&& $this->dropdownExt != ""
								)
									$menuListLead 	= str_replace($itemLink, $groupItemLink, $menuListItem);
							#die($groupItemLink);	
								$menuListLead 	= str_replace($subMenuClass . '"', '"', $menuListLead);
								$menuListLead 	= str_replace(' level-' . $menuLevel, ' level-' . ($menuLevel +1), $menuListLead);
								$menuListLead	= str_replace('>' . $title . '<', '>' . $title . $currGroupBaseItemExt . '<', $menuListLead);
								if($currentPageId != $pageId)
									$menuListLead 	= str_replace('class="active ', 'class="', $menuListLead);
								$menuOutput 		.= $menuListLead . '</li>' . PHP_EOL;
								
								$nextGroupBaseItemExt = next($groupBaseItemExt);
								
								if($nextGroupBaseItemExt !== false)
									$currGroupBaseItemExt = $nextGroupBaseItemExt;
							}

							$menuLevel++;
						
						}
						elseif(!$hasChild && substr(trim($menuOutput), strlen(trim($menuOutput))-5, 5) != "</li>") {
							$menuOutput .= $menuListItem;
							$menuOutput .= '</li>' . PHP_EOL;
						}
						else
							$menuOutput .= $menuListItem;
						
						
					} // Ende if Benutzergruppe
				
				} // Ende foreach
				
				if(substr(trim($menuOutput), strlen(trim($menuOutput))-5, 5) != "</li>") // Falls schon ein li-Tag am Ende des Menues ist,...
					$menuOutput .= '</li>' . PHP_EOL;
				
				while($menuLevel > 2) { // Solange der aktuelle lft-Wert größer als oder gleich ist wie der alte rgt-Wert +2...
	
					$closeList = $this->dropdownClose . '</li>' . PHP_EOL; // ...Liste schließen-Tag einfügen
						
					$menuOutput .= $closeList; // Schließen-Tags anhängen
					$menuLevel--; // Dekrement
				}

				$menuOutput .= $this->navbarClose;
				$menuOutput .= $this->navClose;
			
			} // Ende if Untermenupunkte
			
		} // Ende if Menupunkte
		
		
		// Ggf. Attribute (Styles) Wrapper-div hinzufügen
		if(!empty($this->conAttributes['id'])
		|| !empty($this->conAttributes['class'])
		|| !empty($this->conAttributes['style'])
		)
			$output	= $this->getContentElementWrapper($this->conAttributes, $output);
		

		return $menuOutput;
	
	}


	/**
	 * Erstellt ein Breadcrum-Menü
	 * 
     * @access	public
     * @return 	string
	 */
	public function getBreadcrumbMenu($currentPageId, $activeItem, $separator)
	{

		$curLft = 0;
		$curRgt = 0;
		
		// lft und rgt Werte auslesen
		if(count($this->queryCurrentPage) < 1)
			return "";
		
		
		$curLft		= $this->queryCurrentPage['lft'];
		$curRgt		= $this->queryCurrentPage['rgt'];
		$menuItem	= $this->queryCurrentPage['menu_item'];
	
		
		// Tabelle bzw. View ermitteln
		$this->menuTable	= $this->getMenuTable($menuItem);
		
		
		// Datenbanksuche nach aktuellem Menuepunkt mit Elternknoten
		$query = $this->DB->query("SELECT *
										FROM `" . $this->menuTable . "` 
										WHERE `page_id` = '$currentPageId' 
										OR (lft BETWEEN 2 AND '$curLft' 
										AND rgt > '$curRgt' 
										AND `menu_item` = $menuItem) 
										ORDER BY lft"
										);
		#die($currentPageId);
		#var_dump($query);

		$menuOutput =	'<div class="bc_nav">'.PHP_EOL .
						'<ul class="bc_nav {t_class:bcnav}">'.PHP_EOL; // Ausgabe des Menues mit Listen-id = bc_ + Tabellenname
		
		
		// Seperator
		if($separator != "")
			$separator = '<span class="menuSeparator">' . $separator . '</span>';
			
		
		// Make parent alias str
		$parentAliases	= "";
		
		foreach($query as $row) { // Schleife zum Ausgeben der Menupunktliste
		
			$pageId	= $row['page_id']; // Menuepunktid
			$title	= $row['title_' . $this->menuLang]; // Menuepunkttitel
			$alias	= $row['alias_' . $this->menuLang]; // Menuepunktalias
			$robots	= $row['robots'] ? '' : ' rel="nofollow"'; // Robots meta tag

			if($currentPageId == $pageId) {
				$menuOutput .= '<li class="active">';
				if($activeItem == "span" && count(parent::$dataBCPath) === 0)
					$menuOutput .= '<span>' . $title . '</span></li>' . PHP_EOL; // Aktuellen Menuepunkt ohne Link ausgeben
				else
					$menuOutput .= '<a href="' . PROJECT_HTTP_ROOT . '/' . $this->langUrlPrefix . $parentAliases . $alias . PAGE_EXT . '"' . $robots. '>' . $title . '</a>' . (count(parent::$dataBCPath) > 0 ? $separator . ' ' : '') . '</li>' . PHP_EOL; // Elternmenüpunkte mit Links ausgeben
			}
			else {
				$menuOutput .= '<li><a href="' . PROJECT_HTTP_ROOT . '/' . $this->langUrlPrefix . $parentAliases . $alias . PAGE_EXT . '"' . $robots. '>' . $title . '</a> ' . $separator . ' </li>' . PHP_EOL; // Elternmenüpunkte mit Links ausgeben
				
				// Append parent alias
				if(CC_USE_FULL_PAGEURL)
					$parentAliases	.= $alias . '/';
			}
							
		}
		
		// Falls Artikeldatenmenüpunkte im globalen Datenpfad-Array vorhanden sind
		if(count(parent::$dataBCPath) > 0) {
			
			// Daten-Menüpunkthierarchie zusammenfügen
			$menuOutput .= implode(" " . $separator . " ", parent::$dataBCPath);
			
			// Falls kein Link bei aktivem Menüpunkt erwünscht ist, a-tag mit span-tag ersetzen
			if($activeItem == "span")
				$menuOutput = preg_replace("/^((.*)(\R{0,2})([$]?)*)<a href((?!href).*)>((?!href)(.*)(\R{0,2})([$]?)*)<\/a>(\R{0,2})<\/li>((?!\w)\R{0,2})$/sim", "\\1"."<span>\\6</span></li>", $menuOutput);
				
			$menuOutput = preg_replace("/(<li>(.*)(\R{0,2}))*<\/li>(<li class=\"active\")/sim", "\\4", $menuOutput); // Entfernen anderer Produkte auf dieser Ebene
		}
		$menuOutput .= '</ul></div>'.PHP_EOL;
		
		return $menuOutput;
	
	}


	/**
	 * Liest Menüpunkte aus DB aus
	 * 
     * @param	menuTable
     * @param	menuItem
     * @access	public
     * @return 	string
	 */
	public function getMenuItemsFromDB($menuTable, $menuItem)
	{
	
		// Parameter aller Menuepunkte auslesen
		$query = $this->DB->query("SELECT n.*,
										COUNT(*)-1 AS menulevel  
										FROM `" . $menuTable . "` AS n,
										`" . $menuTable . "` AS p 
										WHERE n.lft BETWEEN p.lft AND p.rgt 
										AND n.lft > 1  
										AND n.`menu_item` = '$menuItem' 
										AND p.`menu_item` = '$menuItem' 
										GROUP BY n.lft 
										ORDER BY n.lft 
										");

		#var_dump($query);
		
		return $query;
	
	}
	

	/**
	 * Erstellt eine Sitemap mit Links aus den Menüstrukturen
	 * 
     * @access	public
     * @return 	string
	 */
	public function getSitemap()
	{
		
		$map = "";
		#$map = '<h1>Sitemap</h1>' . PHP_EOL;
		
		$menus = array(2 => "top", 1 => "main", 3 => "foot");
		$pageId = -1; // Page id = -1, verhindert, dass die aktuelle Seite gehighlightet werden kann/soll
		
		foreach($menus as $key => $menu) {
		
			$menu	= $this->getMenu($pageId, $key, "sitemap");
			
			if(strpos($menu, "</li>") !== false)
				$map .= $menu;
		}
		
		return $map;
		
	}


	/**
	 * Bestimmt die Index-Seiten-Url (Startseiten-Url)
	 * 
     * @access	public
	 */
	public function getIndexPageUrl()
	{
	
		// Parameter lft und rgt des aktuellen Menuepunkts auslesen
		$queryIndexPage = $this->getIndexPage();
		
		$indexPageUrl	= PROJECT_HTTP_ROOT . '/' . $this->langUrlPrefix . $queryIndexPage[0]['alias_' . $this->menuLang] . PAGE_EXT;
		
		return $indexPageUrl;
	
	}


	/**
	 * Bestimmt die Index-Seite (Startseite)
	 * 
     * @access	public
	 */
	public function getIndexPage()
	{
	
		// Parameter lft und rgt des aktuellen Menuepunkts auslesen
		$queryIndexPage = $this->DB->query( "SELECT * 
												FROM `" . DB_TABLE_PREFIX . parent::$tablePages . "` 
											WHERE `index_page` = 1
											");
		#var_dump($queryIndexPage);
	
		if(!is_array($queryIndexPage))
			$queryIndexPage = array(array("page_id" => 1));
		
		return $queryIndexPage;
	
	}

}
