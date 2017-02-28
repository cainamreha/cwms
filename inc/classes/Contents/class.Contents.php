<?php
namespace Concise;

use Symfony\Component\EventDispatcher\Event;
use Concise\Events\ExtendFrontendPageEvent;


// Klassen einbinden
require_once PROJECT_DOC_ROOT."/inc/classes/Contents/class.ContentsEngine.php"; // ContentsEngine einbinden
require_once PROJECT_DOC_ROOT."/inc/classes/Modules/class.Modules.php"; // Modules einbinden

// Event-Klassen einbinden
require_once PROJECT_DOC_ROOT."/inc/classes/Contents/events/event.ExtendFrontendPageEvent.php";

/**
 * Datenbankinhalte
 * 
 */

class Contents extends Modules
{

	/**
	 * Inhalts-Tabelle
	 *
	 * @access public
     * @var    array
     */
	private $contentTable			= "";

	/**
	 * Inhalts-Elementart (e.g. core, plugin)
	 *
	 * @access public
     * @var    array
     */
	private $contentElementKind		= "";
	
	/**
	 * Core Inhalts-Elementtypen
	 *
	 * @access public
     * @var    array
     */
	private $coreContentElements 	= array(	"articles",
												"cards",
												"cart",
												"cform",
												"counter",
												"doc",
												"flash",
												"form",
												"formdata",
												"gallery",
												"gallerymenu",
												"gbook",
												"gmap",
												"html",
												"img",
												"lang",
												"link",
												"listmenu",
												"login",
												"mediaplayer",
												"menu",
												"video",
												"audio",
												"news",
												"newsfeed",
												"oform",
												"planner",
												"redirect",
												"register",
												"script",
												"search",
												"sitemap",
												"tabs",
												"tagcloud",
												"text"
											);
	
	/**
	 * Array mit Inhaltselement-Typen
	 *
	 * @access public
     * @var    array
     */
	public $contentTypes = array();
	
	/**
	 * Beinhaltet ein Array aus Sub-Typen von Plug-Ins
	 *
	 * @access protected
     * @var    array
     */
	protected $contentSubTypes = array();
	
	/**
	 * moduleTypes (task=modules&type=) Modultypen
	 *
	 * @access protected
     * @var    array
     */
	protected $moduleTypes = array(	"articles",
									"news",
									"planner",
									"gallery",
									"gbook",
									"comments"
								);
	
	/**
	 * Beinhaltet ein Array aus Inhaltselement-Definitionen
	 *
	 * @access protected
     * @var    array
     */
	protected $contentDefinitions = array();
	
	/**
	 * Beinhaltet ein Array aus Wrapper tags
	 *
	 * @access private
     * @var    array
     */		
	private $sectionTags		= array(1 => "section",
										2 => "div",
										3 => "header",
										4 => "footer",
										5 => "article",
										6 => "aside"
										);
	
	/**
	 * True falls Haupt-Inhaltsbereich
	 *
	 * @access public
     * @var    boolean
     */
	public $isMainContent = false;
		
	/**
	 * Beinhaltet ein Array mit der Anzahl an Inhaltselementen pro Sprache aus den Tabellen "contents_xyz"
	 *
	 * @access public
     * @var    array
     */
	public $totContentNum		= array();
	private $isContentPlugin 	= array();
	private $memoryUsage 		= array();
	
	/**
	 * Beinhaltet die ID der aktiven Seite in der Menüwurzel
	 *
	 * @access public
     * @var    string
     */
	public static $activeBasePageId = "";
	
	/**
	 * Beinhaltet den Namen der aktiven Seite in der Menüwurzel
	 *
	 * @access public
     * @var    string
     */
	public static $activeBasePageName = "";
	
	/**
	 * Beinhaltet den lft-Wert der aktiven Seite in der Menüwurzel
	 *
	 * @access public
     * @var    string
     */
	public static $activeBasePageLft = "";
	
	/**
	 * Beinhaltet den rgt-Wert der aktiven Seite in der Menüwurzel
	 *
	 * @access public
     * @var    string
     */
	public static $activeBasePageRgt = "";
	
	/**
	 * Liefert die Datenbankinhalte zur aktuellen Seite
	 * 
	 * @access	public
	 * @param	object	$DB			DB-Objekt
	 * @param	object	$o_lng		Sprachobjekt
	 * @param	boolean	$ajax		Aufruf via Ajax
	 * @param	string	$ajaxAction	Ajax Action Parameter
	 */
	public function __construct($DB, $o_lng, $ajax = false, $ajaxAction = "")
	{
	
		// ContentsEngine Contructor
		parent::__construct($DB, $o_lng, $ajax);
		
		// Seite Initialisieren
		$this->initPage();
		
		// Ggf. Ajax-Action auslesen
		$this->getAjaxAction($ajaxAction);

		// Theme-Setup
		$this->getThemeDefaults("fe");

		// Falls kein Backendlog und keine Adminseite oder Installationsseite, ggf. Logging und Analytics berücksichtigen
		if(!$this->backendLog 
		&& !$this->adminPage 
		) {
	
			// Ggf. Seitenaufruf loggen und/oder Analytics einbinden
			$this->setLogging();
		}
		
		
		// Bei nicht Systemseiten, auf Cache-Inhalte prüfen
		if($this->pageId > 0) {
		
			#####################################################################################
			######## Falls nicht Systemseiten, Seite wenn möglich aus HTML-Cache laden ##########
			#####################################################################################
			$this->checkCacheContent(); //  ggf. Cache-Inhalt laden (Script wird abgebrochen)
		}
		
		
		// Token auswerten
		$this->validateToken();
		
		
		// Neuen Token zur Session hinzufügen
		if(!isset($GLOBALS['_POST']['notoken']) && parent::$sessionTokenSet == false) {						
			$this->setToken();
		}
		
		
		// Aktive Plugins aus DB
		$this->activePlugins	= $this->getActivePlugins(true);

		// Aktive Plug-ins anmelden
		$this->registerPlugIns();
		
		// Globale Event Listener anmelden
		$this->addEventListeners("global");
		$this->addEventListeners("fe");
		
		// FE contents events
		// ExtendFrontendPageEvent
		$this->o_extendFrontendPageEvent	= new ExtendFrontendPageEvent($this->DB, $this->o_lng);

	}
	

	/**
	 * Liest ggf. vorhandene Ajax-Action (z.B. Inhaltselementkopie) aus
	 * 
     * @param	string	$pageId Id der aktuellen Seite
     * @param	string	$table Inhaltstabelle
	 * @access	public
     * @return  string
	 */
	public function getAjaxAction($ajaxAction)
	{

		if($ajaxAction == "pastecon")
			parent::$pasteElement = true;
	
	}
	

	/**
	 * Liefert die Datenbankinhalte zur aktuellen Seite bzw. zu einem jeweiligen Inhaltsbereich
	 * 
     * @param	string	$pageId Id der aktuellen Seite
     * @param	string	$table Inhaltstabelle
	 * @access	public
     * @return  string
	 */
	public function readContents($pageId, $table = "contents_main")
	{

		$srcTable				= $table;
		$table					= str_replace("_preview", "", $table);
		$this->contentTable		= $table;
		
		// Hauptinhaltsbereich, falls pages angegeben
		$this->isMainContent	= $this->contentTable == str_replace("_preview", "", parent::$tableContents) ? true : false;

		// Inhalte des Inhaltsbereichts
		$this->contents[$this->contentTable]			= array(); // Inhalte
		$this->contentTypes[$this->contentTable]		= array(); // Inhaltstypen
		$this->contentDefinitions[$this->contentTable]	= array(); // Inhaltsdefinitionen
		$this->isContentPlugin[$this->contentTable]		= array(); // Plugin
		
		// Spaltenanzahl der Inhaltstabelle (db) ermitteln
		$this->totContentNum[$this->contentTable] 		= parent::getConNumber(DB_TABLE_PREFIX . $this->contentTable);
	
		// Query-String generieren zum Auswählen der Inhalte in der jeweiligen Sprache und von Typ- und Styleangaben
		$conQueryStr = "";
		
		
		// Falls Inhaltselemente vorhanden
		$this->queryContents = $this->getContentsQuery($srcTable, $pageId);
		
		
		// Falls keine Inhaltselemente vorhanden
		if(empty($this->queryContents) 
		|| !is_array($this->queryContents)
		|| count($this->queryContents) == 0
		)
			return $this->queryContents;
		
			
		require_once PROJECT_DOC_ROOT . "/inc/classes/Elements/class.ElementFactory.php"; // Element-Factory einbinden
		
		
		// Andernfalls Inhalte auslesen					
		$i				= 1;
		$sectionTag		= "section"; // Section tag (section or div)
		$sectionCnt		= 0; // Falls eine neue section begonnen werden soll
		$ctrCnt			= 0; // Falls eine neuer container begonnen werden soll
		$rowCnt			= 0; // Falls eine neue row begonnen werden soll
		$lastVisible	= "";

		// Grid breakpoint prefixes
		$colmd			= empty(parent::$styleDefs['colmd']) ? 'md' : parent::$styleDefs['colmd'];
		$colsm			= empty(parent::$styleDefs['colsm']) ? 'sm' : parent::$styleDefs['colsm'];
		$colxs			= empty(parent::$styleDefs['colxs']) ? 'xs' : parent::$styleDefs['colxs'];

		// Gap type
		$defGapType		= empty($this->themeConf['styledef']['gaptype']) ? "margin" : $this->themeConf['styledef']['gaptype'];
		
		
		// Schleife zum Auslesen der relevanten sprich zur ausgewählten Sprache und zur page_id passenden bzw. vorhandenen Inhalte
		foreach($this->queryContents[0] as $fieldName => $content) {

			// Falls kein Inhalt oder kein Inhaltstyp
			if($content == "" || $fieldName != "type-con" . $i)
				continue;

			
			$conNum			= "";
			$conType		= "";
			$conValue		= "";
			$conStyles		= "";
			$conLang		= "";
			$placeH			= "";
			$galleryType	= "";
			$conAttributes	= array (	"id"	=> "",
										"class" => "",
										"style" => "",
										"cols"	=> "",
										"hide"	=> 0,
										"sec"	=> 0,
										"ctr"	=> 0,
										"row"	=> 0,
										"div"	=> 0,
										"secid"	=> "",
										"ctrid"	=> "",
										"rowid"	=> "",
										"divid"	=> "",
										"secclass"	=> "",
										"ctrclass"	=> "",
										"rowclass"	=> "",
										"divclass"	=> "",
										"secbgcol"	=> "",
										"secbgimg"	=> ""
									);
	
			
			
			// Falls der Inhaltstyp angegeben ist, Inhalte nach und nach auslesen	
			$conType			= $content;
			$conNum				= "con" . $i;
			$conValue			= $this->queryContents[0]["con" . $i . "_" . $this->lang];
			$conStyles			= $this->queryContents[0]["styles-con" . $i];
			
			
			// Styles
			if(strpos($conStyles, "{") !== 0) {
				// Legacy styles
				$hideEl			= strpos($conStyles, "<hide>") !== false ? 1 : 0;
				$styles			= str_replace("<hide>", "", $conStyles);				
				$styles			= explode("/", $conStyles);
				$styles			= $this->changeArrayKey( $styles, 0, 'id');
				$styles			= $this->changeArrayKey( $styles, 1, 'class');
				$styles			= $this->changeArrayKey( $styles, 2, 'style');
				$styles			= $this->changeArrayKey( $styles, 3, 'cols');
				$styles['hide']	= $hideEl;
			}
			else
				$styles = (array)json_decode($conStyles);
			
			if(!isset($styles['id']))
				$styles['id'] = "";
			if(!isset($styles['class']))
				$styles['class'] = "";
			if(!isset($styles['style']))
				$styles['style'] = "";
			if(!isset($styles['cols']))
				$styles['cols'] = "";
			if(!isset($styles['colssm']))
				$styles['colssm'] = "";
			if(!isset($styles['colsxs']))
				$styles['colsxs'] = "";
			if(!isset($styles['mt']))
				$styles['mt'] = "";
			if(!isset($styles['mb']))
				$styles['mb'] = "";
			if(!isset($styles['ml']))
				$styles['ml'] = "";
			if(!isset($styles['mr']))
				$styles['mr'] = "";
			if(!isset($styles['pt']))
				$styles['pt'] = "";
			if(!isset($styles['pb']))
				$styles['pb'] = "";
			if(!isset($styles['pl']))
				$styles['pl'] = "";
			if(!isset($styles['pr']))
				$styles['pr'] = "";
			if(!isset($styles['hide']))
				$styles['hide'] = 0;
			if(!isset($styles['sec']))
				$styles['sec'] = 0;
			if(!isset($styles['ctr']))
				$styles['ctr'] = 0;
			if(!isset($styles['row']))
				$styles['row'] = 0;
			if(!isset($styles['div']))
				$styles['div'] = 0;
			if(!isset($styles['secid']))
				$styles['secid'] = "";
			if(!isset($styles['ctrid']))
				$styles['ctrid'] = "";
			if(!isset($styles['rowid']))
				$styles['rowid'] = "";
			if(!isset($styles['divid']))
				$styles['divid'] = "";
			if(!isset($styles['secclass']))
				$styles['secclass'] = "";
			if(!isset($styles['ctrclass']))
				$styles['ctrclass'] = "";
			if(!isset($styles['rowclass']))
				$styles['rowclass'] = "";
			if(!isset($styles['divclass']))
				$styles['divclass'] = "";
			if(!isset($styles['secbgcol']))
				$styles['secbgcol'] = "";
			if(!isset($styles['secbgimg']))
				$styles['secbgimg'] = "";

			$styles['style']	= trim($styles['style']);
			$styles['style']	= rtrim($styles['style'], ";");
			
			if($styles['style'] != ""
			)
				$styles['style']	.= ';';
			
			// Layout classes
			$styles['secclass']	.= (!empty($styles['secclass']) ? ' ' : '') . 'cc-layout-sec';
			$styles['ctrclass']	.= (!empty($styles['ctrclass']) ? ' ' : '') . 'cc-layout-ctr';
			$styles['rowclass']	.= (!empty($styles['rowclass']) ? ' ' : '') . 'cc-layout-row';
			$styles['divclass']	.= (!empty($styles['divclass']) ? ' ' : '') . 'cc-layout-div';
			
			// Margins setzen
			if($styles['mt'] != "")
				$styles['style'] .= ($styles['style'] == "" ? '' : ' ') . 'margin-top:' . $styles['mt']  . 'px;';
			if($styles['mb'] != "")
				$styles['style'] .= ($styles['style'] == "" ? '' : ' ') . 'margin-bottom:' . $styles['mb']  . 'px;';
			if($styles['ml'] != "")
				$styles['style'] .= ($styles['style'] == "" ? '' : ' ') . 'margin-left:' . $styles['ml']  . 'px;';
			if($styles['mr'] != "")
				$styles['style'] .= ($styles['style'] == "" ? '' : ' ') . 'margin-right:' . $styles['mr']  . 'px;';
			
			// Paddings setzen
			if($styles['pt'] != "")
				$styles['style'] .= ($styles['style'] == "" ? '' : ' ') . 'padding-top:' . $styles['pt']  . 'px;';
			if($styles['pb'] != "")
				$styles['style'] .= ($styles['style'] == "" ? '' : ' ') . 'padding-bottom:' . $styles['pb']  . 'px;';
			if($styles['pl'] != "")
				$styles['style'] .= ($styles['style'] == "" ? '' : ' ') . 'padding-left:' . $styles['pl']  . 'px;';
			if($styles['pr'] != "")
				$styles['style'] .= ($styles['style'] == "" ? '' : ' ') . 'padding-right:' . $styles['pr']  . 'px;';
			
			
			// Falls das Element versteckt werden soll, Schleife mit nächstem Element fortsetzen, falls nicht FE-Mode
			if(!empty($styles['hide'])) {
				if(!parent::$feMode) {
					$i++;
					continue;
				}
				else {
					$this->contentDefinitions[$this->contentTable][$conNum]["publish"] = "unpublish";
				}
			}
			else
				$this->contentDefinitions[$this->contentTable][$conNum]["publish"] = "publish";
			
			
			// ExtendFrontendPageEvent
			$this->o_extendFrontendPageEvent->styles		= $styles;
			
			// dispatch event get_styles_fe
			$this->o_dispatcher->dispatch('global.extend_styles_fe', $this->o_extendFrontendPageEvent);		
				
			$styles			= $this->o_extendFrontendPageEvent->styles;

			
			// Column count
			$this->contentDefinitions[$this->contentTable][$conNum]["cols"] = $this->getColumnGridCount($styles["cols"]);

			
			// Element attributes
			$conAttributes["type"]		= $conType;
			$conAttributes["id"]		= $styles["id"];
			$conAttributes["class"]		= $styles["class"];
			$conAttributes["style"]		= $styles["style"];
			$conAttributes["div"]		= $styles["div"];
			$conAttributes["divid"]		= $styles["divid"];
			$conAttributes["divclass"]	= $styles["divclass"];
			$conAttributes["cols"]		= $styles["cols"];
			$conAttributes["hide"]		= $styles["hide"];
			
		
			// data-attr
			foreach($styles as $key => $val) {
				if(strpos($key, "data-") === 0)
					$conAttributes[$key]	=	$val;
			}
			
			// Grid style class
			$gridClass				 = $this->getColumnGridClass($styles["cols"]);
			$gridClass				.= $this->getColumnGridClass($styles["colssm"], true, $colmd, $colsm);
			$gridClass				.= $this->getColumnGridClass($styles["colsxs"], true, $colmd, $colxs);
			
			// Columns als Style-Class
			if($gridClass != "")
				$conAttributes["class"]	= $gridClass . ($conAttributes["class"] != "" ? ' ' . $conAttributes["class"] : '');

			
			// Platzhalterschutz "#" entfernen
			$conValue		= str_replace("{#", "{", $conValue);


			// Inhaltselementenart
			$this->contentElementKind	= $this->getContentElementKind($conType);
			
			
			// Falls unbekannte Elementart
			if($this->contentElementKind == "unknown") {
				$this->contents[$this->contentTable][$conNum] = $this->backendLog ? '<p class="notice error {t_class:alert} {t_class:error}">{s_error:unknowncon}: ' . $conType . '.</p>' : "";
				$i++;
				continue;
			}
			
			// Falls Plug-in
			if($this->contentElementKind == "plugin") {			
				$this->isContentPlugin[$this->contentTable][$conNum]	= true;
				$this->setPlugin($conType, $this->lang); // Sprachbausteine des Plug-ins laden
			}
			
			// Element-Options
			$options	= array(	"conType"		=> $conType,
									"conValue"		=> $conValue,
									"conAttributes"	=> $conAttributes,
									"conNum"		=> $conNum,
									"conCount"		=> $i,
									"conTable"		=> $this->contentTable
								);
			
			// Elementinhalt
			try {				
				// Inhaltselement-Instanz
				$o_element	= ElementFactory::create($conType, $options, $this->contentElementKind, $this->DB, $this->o_lng, $this->o_page);
			}
				
			// Falls Element-Klasse nicht vorhanden
			catch(\Exception $e) {
				$this->contents[$this->contentTable][$conNum] = $this->backendLog ? $e->getMessage() : "";
				$i++;
				continue;
			}

			// Session übergeben
			$o_element->g_Session				= $this->g_Session;
		
			// Events listeners registrieren
			$hasEvents	= $this->addEventListeners($conType);

			// EventDispatcher mitgeben
			$o_element->setEventDispatcher($this->o_dispatcher);
			$o_element->setEventListeners($this->eventListeners);
			

			// Element-Objekt Eigenschaften
			$o_element->isMainContent			= $this->isMainContent;
			$o_element->lang					= $this->lang;
			$o_element->group					= $this->group;
			$o_element->backendLog				= $this->backendLog;
			$o_element->editorLog				= $this->editorLog;
			$o_element->adminLog				= $this->adminLog;
			$o_element->adminPage				= $this->adminPage;
			$o_element->pageId					= $this->pageId;
			$o_element->html5					= $this->html5;
			$o_element->themeConf				= $this->themeConf;

			
			// Inhaltselement generieren
			$contentElement						= $o_element->getElement();
			
			
			// ExtendFrontendPageEvent
			$this->o_extendFrontendPageEvent->conType		= $conType;
			$this->o_extendFrontendPageEvent->o_element		= $o_element;
			
			// dispatch event get_styles_fe
			$this->o_dispatcher->dispatch('global.extend_element_fe', $this->o_extendFrontendPageEvent);		
				
			$o_element			= $this->o_extendFrontendPageEvent->o_element;

			
			// Bei Plugins Wrapper-div hinzufügen
			if($this->contentElementKind == "plugin") {			
				$contentElement					= $o_element->getContentElementWrapper($conAttributes, $contentElement);
			}
			
			
			// Ggf. Content subtype
			$this->setContentSubType($o_element, $conNum);
			
			
			// Inhaltstypen in contentTypes-Array speichern
			$this->contentTypes[$this->contentTable][$conNum]	= $conType;
			
			
			// Content definitions
			$this->mergeContentDefinitions($o_element, $conNum);
			
			
			// Head code
			$this->mergeHeadCodeArrays($o_element);
			
			
			// Body class
			if(!empty($o_element->bodyClassStrings))
				$this->bodyClassStrings	= array_merge($this->bodyClassStrings, $o_element->bodyClassStrings);

			
			
			// Element-Inhalt in contents-Array speichern
			$this->contents[$this->contentTable][$conNum]	= $contentElement;

			$secOpen	= "";
			$ctrOpen	= "";
			$rowOpen	= "";			
			$secClose	= "";
			$ctrClose	= "";
			$rowClose	= "";
			
			// Ggf. Row schließen/starten
			if(!empty($styles['row'])) {
			
				// Ggf. vorherige Row schließen
				if($rowCnt > 0) {
					$rowClose = '</div><!-- close row -->' . "\n";
					$rowCnt--;
				}
				
				// Ggf. row starten
				if($styles['row'] == "1") {
					$rowCnt++;
					$rowOpen	= '<div' . (!empty($styles['rowid']) ? ' id="' . (htmlspecialchars($styles['rowid'])) . '"' : '') . ' class="{t_class:row}' . (!empty($styles['rowclass']) ? ' ' . (htmlspecialchars($styles['rowclass'])) : '') . '"' . (parent::$feMode ? ' data-cc-grid="row"' : '') . '><!-- open row -->' . "\n";
				}
			}
			
			// Ggf. Container schließen/starten
			if(!empty($styles['ctr'])) {

				// Ggf. vorherige Row schließen, falls nur container neu
				if($rowCnt > 0 && empty($styles['row'])) {
					$rowClose	= '</div><!-- close row -->' . "\n";
					$rowCnt--;
				}

				// Ggf. vorherigen container schließen
				if($ctrCnt > 0) {
					$ctrClose	= '</div><!-- close ctr -->' . "\n";
					$ctrCnt--;
				}
				
				// Ggf. container starten
				if($styles['ctr'] == "1") {
					$ctrCnt++;
					$ctrOpen	= '<div' . (!empty($styles['ctrid']) ? ' id="' . (htmlspecialchars($styles['ctrid'])) . '"' : '') . ' class="{t_class:container' . (stripos($this->currentTemplate, "fullwidth") !== false ? 'fl} {t_class:containerfw' : '') . '}' . (!empty($styles['ctrclass']) ? ' ' . (htmlspecialchars($styles['ctrclass'])) : '') . '"' . (parent::$feMode ? ' data-cc-grid="ctr"' : '') . '><!-- open ctr -->' . "\n";
				}
			}
			
			// Ggf. Section schließen/starten
			if(!empty($styles['sec'])) {
			
				// Ggf. vorherige Row schließen, falls nur section neu
				if($rowCnt > 0 && empty($styles['row']) && empty($styles['ctr'])) {
					$rowClose	= '</div><!-- close row -->' . "\n";
					$rowCnt--;
				}

				// Ggf. vorherigen container schließen, falls nur section neu
				if($ctrCnt > 0 && empty($styles['ctr'])) {
					$ctrClose	= '</div><!-- close ctr -->' . "\n";
					$ctrCnt--;
				}
				
				// Ggf. vorherige Section schließen
				if($sectionCnt > 0) {
					$secClose	= '</' . $sectionTag . '><!-- close sec -->' . "\n";
					$sectionCnt--;
				}
			
				// Ggf. Section starten
				if($styles['sec'] != "x") {
				
					$sectionCnt++;
					
					// Section background image
					$bgStyle	= "";
					if(!empty($styles['secbgcol']))
						$bgStyle	.= 'background-color:' . htmlspecialchars($styles['secbgcol']) . ';';
					if(!empty($styles['secbgimg'])) {
						$imgUrl		= PROJECT_HTTP_ROOT . '/' . Modules::getImagePath($styles['secbgimg'], true);
						$bgStyle	.= 'background-image:url(\'' . htmlspecialchars($imgUrl) . '\');';
					}
					
					$sectionTag	= $styles['sec'] ? $this->sectionTags[$styles['sec']] : 'section';
					$secOpen	= '<' . $sectionTag . (!empty($styles['secid']) ? ' id="' . (htmlspecialchars($styles['secid'])) . '"' : '') . (!empty($styles['secclass']) ? ' class="' . (htmlspecialchars($styles['secclass'])) . '"' : '') . (!empty($bgStyle) ? ' style="' . $bgStyle . '"' : '') . (parent::$feMode ? ' data-cc-grid="sec"' : '') . '><!-- open sec -->' . "\n";
				}
			}
			
			
			// Wrapper tags
			$this->contentDefinitions[$this->contentTable][$conNum]['open']		= $secOpen . $ctrOpen . $rowOpen;
			$this->contentDefinitions[$this->contentTable][$conNum]['close']	= "";
			
			if($i > 1){
				$this->contentDefinitions[$this->contentTable][$lastVisible]['close']	= $rowClose . $ctrClose . $secClose;
			}
			
			$lastVisible	= $conNum;
			$i++; // Zählerinkrement
			
		} // Ende foreach
		
		
		if(!empty($lastVisible)) {
			
			if(empty($this->contentDefinitions[$this->contentTable][$lastVisible]['close']))
				$this->contentDefinitions[$this->contentTable][$lastVisible]['close']	= "";
			
			// Ggf. vorherige Row schließen
			while($rowCnt > 0) {
				$this->contentDefinitions[$this->contentTable][$lastVisible]['close']	.= '</div><!-- close row -->' . "\n";
				$rowCnt--;
			}
			
			// Ggf. vorherigen Container schließen
			while($ctrCnt > 0) {
				$this->contentDefinitions[$this->contentTable][$lastVisible]['close']	.= '</div><!-- close ctr -->' . "\n";
				$ctrCnt--;
			}
			
			// Ggf. vorherige Section schließen
			while($sectionCnt > 0) {
				$this->contentDefinitions[$this->contentTable][$lastVisible]['close']	.= '</' . $sectionTag . '><!-- close sec -->' . "\n";
				$sectionCnt--;
			}
		}

	} // Ende Inhalte auslesen
	

	/**
	 * Gibt Inhalte der aktuellen Seite aus
	 * 
	 * @access	public
     * @return  string
	 */
	public function getContents()
	{
	
		// Haupt-Inhalte auslesen
		$this->readContents($this->pageId, parent::$tableContents);
		
		
		// Template-Inhalte auslesen
		$this->getContentsTpl();
		
		
		// Frontend Menüs aufbauen (Menu pre-build sofern in theme_config.ini definiert)
		$this->buildMenus();
		
		
		// Script-Code für Headbereich
		$this->getScriptVars();
		
		
		// Head-Dateien zusammenführen
		$this->setHeadIncludes();
		
		
		// ExtendFrontendPageEvent
		// dispatch event register_head_files
		$this->o_dispatcher->dispatch('global.register_head_files', $this->o_extendFrontendPageEvent);		
		$this->mergeHeadCodeArrays($this->o_extendFrontendPageEvent);
		// dispatch event register_head_files_fe
		$this->o_dispatcher->dispatch('global.register_head_files_fe', $this->o_extendFrontendPageEvent);		
		$this->mergeHeadCodeArrays($this->o_extendFrontendPageEvent);
		// dispatch event add_head_code_fe
		$this->o_dispatcher->dispatch('global.add_head_code_fe', $this->o_extendFrontendPageEvent);		
		$this->mergeHeadCodeArrays($this->o_extendFrontendPageEvent);

		
		// Platzhalter zuweisen
		$this->assignReplaceContents();
		$this->assignReplaceCommon();
		
		
		// Inhalte zusammenführen
		$this->assembleContents(true);
		
		
		// Ggf. Memory usage für Debug-Konsole
		if(DEBUG && $this->adminLog)
			$this->getMemoryUsage();
		
		
		// Letzte Platzhalter zuweisen und alle Platzhalter ersetzen
		$this->assignReplace();

		
		// HTML-Seite ausgeben
		if(!$this->ajax)
			$this->printHtmlContent();
	
		return true;
	
	} // Ende Inhalte ausgeben




	/**
	 * Inhalte der Template areas
	 *
	 * @access	public
     * @return  string
	 */
	public function getContentsTpl()
	{
	
		// Templatebereiche
		$ct				= 0;

		foreach(parent::$tablesTplContents as $conTab) {

			// Datenbankinhalte zur aktuellen Seite (Tpl-Areas)
			$this->readContents($this->currentTemplate, $conTab.$this->preview);
			
			$ct++;
		}

	}



	/**
	 * Inhaltsart ermitteln
	 *
	 * @access	public
	 * @param	string	$conType	Content Type
     * @return  string
	 */
	public function getContentElementKind($conType)
	{
		
		// Falls System-Element
		if(in_array($conType, $this->systemContentElements)) {
			return "system";
		}

		// Falls Core-Inhaltselement
		if(in_array($conType, $this->coreContentElements)) {

			// Falls Daten-Inhaltselement
			if($conType == "articles" 
			|| $conType == "news" 
			|| $conType == "planner" 
			)
				return "data";
			else
				return "core";
		}
		
		// Falls Plug-in
		if(in_array($conType, $this->activePlugins)) {
			return "plugin";
		}
		
		// Falls unbekanntes Element
		return "unknown";
	
	}



	/**
	 * Ggf. aktuellen Seitenaufruf loggen und/oder Analytics einbinden
	 * 
	 * @access	public
	 * @return	string
	 */
	public function setLogging() 
	{

		// Falls Seitenaufrufe geloggt werden sollen
		if(CONCISE_LOG && !isset($GLOBALS['_COOKIE']['conciseLogging_off']))
			parent::$LOG->doLog($this->pageId, $this->lang);

			
		// Ggf. Google-Analytics-Code einbinden
		if(ANALYTICS && !$this->backendLog && !$this->adminPage) {
			if(ANALYTICS_POSITION == "head")
				$this->scriptFiles[]		= "access/js/analytics.js";
			else
				$this->scriptFilesBody[]	= "access/js/analytics.js";
		}

	}



	/**
	 * Inhalte ggf. aus Cache laden
	 *
	 * @access	public
     * @return  string
	 */
	public function checkCacheContent()
	{
	
		// Falls Seite aus Cache geladen werden kann (nur bei öffentlichen Seiten, wenn kein Post oder Get-Parameter außer pageID)				
		if(	CACHE && 
			$this->group == "public" && 
			isset($GLOBALS['_GET']['page']) && count($GLOBALS['_GET']) == 1 && 
			(!is_array($GLOBALS['_POST']) || count($GLOBALS['_POST']) == 0)
		  ) {

			$htmlFile = HTML_CACHE_DIR . $this->lang . '/' . $this->pageId . '.html';
			
			// Cache-Html ausgeben, falls vorhanden und Script beenden
			if(file_exists($htmlFile)) {
			
				$cacheContent = file_get_contents($htmlFile);
				$cacheContent = str_replace("{#browser}", Log::$browser, $cacheContent);
				
				echo $cacheContent;
				
				exit;
				die();
			}
		}
	
	}
	
	

	/**
	 * Systemseite generieren
	 *
	 * @access	public
     * @return  string
	 */
	public function getSystemPageContent()
	{
		
		$systemContent 		= "";
		$contentType		= "";
		$baseTableContents	= str_replace("_preview", "", parent::$tableContents);
		
		// Zuweisungen für Hauptinhalte (contents_main) der aktuellen Seite, falls pageID < 0
		switch($this->pageId) {
		
			case "-1002": // Loginseite
				// Loginobjekt
				$o_Login			= new Login($this->DB, $this->o_lng, $this->o_dispatcher);
				$this->addEventListeners("user"); // User event listeners
				$systemContent		= $o_Login->printLoginForm();
				$contentType		= "login";
				break;
				
			case "-1003": // Fehlerseite
				require_once PROJECT_DOC_ROOT . "/inc/classes/ErrorPage/class.ErrorPage.php";
				// ErrorPageobjekt
				$o_ErrorPage		= new ErrorPage($this->statusCode);
				$systemContent		= $o_ErrorPage->getErrorPage($this->group);
				$contentType		= "error";
				break;

			case "-1004": // Suchseite
				SEARCH_TYPE == "none" ? $this->gotoErrorPage() : '';
				require_once PROJECT_DOC_ROOT . "/inc/classes/Modules/class.Search.php";
				// SearchPageobjekt
				$search								= new Search($this->DB, $this->o_lng, SEARCH_TYPE);
				$this->scriptFiles["ajaxsearch"]	= "access/js/ajaxSearch.js"; // js-Datei einbinden
				$this->cssFiles[]					= "access/css/ajaxSearch.css"; // css-Datei einbinden
				$systemContent						= $search->getSearch("big");
				$contentType						= "search";
				break;
				
			case "-1005": // Logoutseite
				$systemContent		=	'<div id="logoutForm" class="form {t_class:fullrow} {t_class:margintm} {t_class:marginbm}">' . "\n" .
										'<div class="top"></div>' . "\n" .
										'<div class="center">' . "\n" .
										'<form><fieldset><legend>{s_form:user}</legend>' . "\n" .
										'<h2 class="logout">Logout<span class="logout icons icon-logout">&nbsp;</span></h2>' . "\n" .
										'<p class="{t_class:alert} {t_class:success} notice success">{s_text:logout}</p>' . "\n" .
										'<p><a href="' . PROJECT_HTTP_ROOT . '/login' . PAGE_EXT . '">' . "\n" .
										'<span class="{t_class:btn} {t_class:btnpri} icon-login icon-left formbutton ok right">{s_text:relog}</a></span>' . "\n" .
										'</p>' . "\n" .
										'</form></fieldset>' . "\n" .
										'</div>' . "\n" .
										'<div class="bottom"></div>' . "\n" .
										'</div>' . "\n";
				$contentType		= "logout";
				break;
				
			case "-1006": // Registrierungsseite
				if(REGISTRATION_TYPE == "none")
					$this->gotoErrorPage();
				$o_user				= new User($this->DB, $this->o_lng);
				$systemContent		= $o_user->getRegPage(REGISTRATION_TYPE);
				$contentType		= "register";
				break;
				
			case "-1007": // Benutzer-Account-Seite
				if(!((REGISTRATION_TYPE == "account" || REGISTRATION_TYPE == "shopuser") && isset($this->g_Session['userid']) && isset($this->g_Session['username']) && isset($this->g_Session['group']) && $this->g_Session['group'] == "guest"))
					$this->gotoErrorPage();
				$o_user				= new User($this->DB, $this->o_lng);
				$systemContent		= $o_user->getPersonalPage($this->g_Session['userid'], $this->g_Session['username']);
				$contentType		= "guest";
				break;
		
		}
		$this->contents[$baseTableContents]			= array($systemContent);
		$this->contentTypes[$baseTableContents]		= array($contentType);
		
		// Content num auf 1 setzen
		$this->totContentNum[$baseTableContents]	= 1;
		
		return $this->contents[$baseTableContents];

	}
	
	

	/**
	 * Menüs generieren
	 *
	 * @param	array	$menuTypes	Array mit zu generierenden Menüs (default = array())
	 * @access	public
     * @return  string
	 */
	public function buildMenus($menuTypes = array())
	{
		
		// Falls die Menüs nur als Inhaltselemente erstellt werden
		if(!BUILD_MENUS || empty($this->themeConf['menus']))
			return false;
		
		
		require_once PROJECT_DOC_ROOT."/inc/classes/Modules/class.Menu.php"; // Menu-Klasse einbinden

		$o_menu				= new Menu($this->DB, $this->o_lng, $this->o_dispatcher, $this->o_page, $this->html5);
		
		// Variablen für Menueaufbau
		$this->mainMenu		= in_array("main", $menuTypes)		? $o_menu->getMenu($this->pageId, 1, "main", "link", "", false) : "";
		$this->topMenu		= in_array("top", $menuTypes)		? $o_menu->getMenu($this->pageId, 2, "top", "link", "", false) : "";
		$this->footMenu		= in_array("foot", $menuTypes)		? $o_menu->getMenu($this->pageId, 3, "foot", "link", "", false) : "";
		$this->bcNav		= in_array("bc", $menuTypes)		? $o_menu->getMenu($this->pageId, 1, "bc", "span", "&raquo;") : ""; // e.g. &gt;
		$this->subMenu		= in_array("sub", $menuTypes)		? $o_menu->getMenu($this->pageId, 1, "sub", "link", "", false, "", self::$activeBasePageLft, self::$activeBasePageRgt) : "";
		$this->parRootMenu	= in_array("parroot", $menuTypes)	? $o_menu->getMenu($this->pageId, 1, "parroot", "link", "", false) : "";
		$this->parSubMenu	= in_array("parsub", $menuTypes)	? $o_menu->getMenu($this->pageId, 1, "parsub", "link", "", false) : "";
		
		return true;

	}
	

	/**
	 * Inhalte aus DB laden
	 *
	 * @param	object			$conTable	Inhaltstabelle
	 * @param	array			$pageID		page-ID
	 * @access	protected
     * @return  string
	 */
	protected function getContentsQuery($conTable, $pageID)
	{
		
		// Falls keine Inhalte
		if($this->totContentNum[$this->contentTable] < 1)
			return false;
		
	
		$pageIDdb		= $this->DB->escapeString($pageID);
		$conTableDb		= $this->DB->escapeString(DB_TABLE_PREFIX . $conTable);
		$conQueryStr	= "";
	
		// Query string	
		for($c = 1; $c <= $this->totContentNum[$this->contentTable]; $c++) {
		
			$conQueryStr .= "`con" . $c . "_" . $this->lang . "`,`type-con" . $c . "`,`styles-con" . $c . "`,";
			
		}
		
		$conQueryStr = substr($conQueryStr, 0, -1);
		
		// Inhalte auslesen
		$queryContents = $this->DB->query("SELECT $conQueryStr
												FROM `$conTableDb` 
											WHERE `page_id` = '$pageIDdb'
											");

		#var_dump($this->queryContents);
		
		return $queryContents;

	}
	

	/**
	 * Inhalts-Definitionen zusammenführen
	 *
	 * @param	object			$o_element	Element-Objekt
	 * @param	array			$conNum		content number
	 * @access	protected
     * @return  string
	 */
	protected function mergeContentDefinitions($o_element, $conNum)
	{
	
		if(isset($o_element->contentDefinitions[$this->contentTable][$conNum]))
			$this->contentDefinitions[$this->contentTable][$conNum]	= array_merge($this->contentDefinitions[$this->contentTable][$conNum], $o_element->contentDefinitions[$this->contentTable][$conNum]);
	}


	/**
	 * Inhaltstypen in Array speichern
	 *
	 * @param	array			$arr1	Array 1
	 * @param	array			$arr2	Array 2
	 * @access	protected
     * @return  string
	 */
	protected function setContentSubType($o_element, $conNum)
	{
	
		$this->contentSubTypes[$this->contentTable][$conNum]	= $o_element->getContentSubType();	
	
	}
	
	
	/**
	 * Inhaltsbereiche zusammensetzen
	 *
	 * @access	public
     * @return  string
	 */
	public function assembleContents($includeTplContents)
	{
		
		// Loop-Template Objekt
		$tpl_loop			= new Template(parent::$loopTemplate);
		$tpl_loop->loadTemplate();

		$o_lng				= $this->o_lng;

		$feMode				= parent::$feMode;
		$hasDbContent		= false;
		$sessionCopy		= false;
		$conIconPath		= 'system/themes/' . ADMIN_THEME . '/img';

		$tc	= 0;

		// Template-Areas auslesen, falls angegeben
		if($includeTplContents !== false) {
		
		
		// Falls ein Inhalt ausgeschnitten wurde
		if(parent::$pasteElement || isset($this->g_Session['copycon'])) {
			$sessionCopy	= true;
		}
		
		// Content-Elemente ersetzen bzw. zuweisen (head, left, right, foot)
		foreach(parent::$areasTplContents as $conArea) {
			
			$conTab			= parent::$tablesTplContents[$tc];
			$loopContent	= "";
			$i				= 1;
			$area			= $conArea;
			$dbContents		= $this->contents[$conTab];
			
			foreach($dbContents as $key => $list) {
			
				$loop_tpl	= clone $tpl_loop; // Objekt-Instanz klonieren
				
				$loop_tpl->assign("dbcontents", $list);

				
				// Falls kein FE-Mode, Inhalte direkt ausgeben
				if(!$this->adminPage
				&& !$feMode
				) {
					$loopContent .= $this->getContentDef("open", $conTab, $key);
					$loopContent .= $loop_tpl->getTemplate();
					$loopContent .= $this->getContentDef("close", $conTab, $key);
					$i++;
					continue;
				}
				
				$conMax 		= $this->totContentNum[$conTab];
				$busyCon 		= parent::getConNumber(DB_TABLE_PREFIX . $conTab, $this->currentTemplate);
				$conType		= "";
				$subType		= "";
				$conDef			= array();
				$conIconPath	= SYSTEM_HTTP_ROOT . '/themes/' . ADMIN_THEME . '/img';
				$isPlugin		= isset($this->isContentPlugin[$conTab]["con".$i]);
				
				
				// Ggf. Inhaltssubtyp ermitteln (Plug-Ins)
				if(!empty($this->contentSubTypes[$conTab]["con".$i])) {
					$subType	= " [" . $this->contentSubTypes[$conTab]["con".$i] . "]";
					$conType	= $this->contentTypes[$conTab]["con".$i];
				}
				
				// Inhaltstyp ermitteln
				elseif(isset($this->contentTypes[$conTab]["con".$i]))
					$conType	= $this->contentTypes[$conTab]["con".$i];
				
				// Inhaltselement-Definitionen ermitteln
				if(isset($this->contentDefinitions[$conTab]["con".$i]))
					$conDef	= $this->contentDefinitions[$conTab]["con".$i];
					
				// Falls Plugin
				if($isPlugin)
					$conIconPath	= PLUGIN_URL . '/' . $this->contentTypes[$conTab]["con".$i] . '/img';
			
			
				if($conType == "text") {
					$loop_tpl->assign("e_textbegin", '<div class="' . $area . ' editableText" title="{s_title:edittext}">');			
					$loop_tpl->assign("e_textend", '</div>');
				}
				else {
					$loop_tpl->assign("e_display-text", "display:none;");
				}
				
				// EditDetails Tpl
				$editDetails	= $this->getEditDetails($conType, $isPlugin);
				$loop_tpl->assign("e_editdetails", $editDetails);

				
				$loop_tpl->assign("e_path", PROJECT_HTTP_ROOT);
				$loop_tpl->assign("e_coniconpath", $conIconPath);
				$loop_tpl->assign("e_connr", $i);
				$loop_tpl->assign("e_edittask", "tpl&type=edit");
				$loop_tpl->assign("e_contype", $conType);
				$loop_tpl->assign("e_subtype", $subType);
				$loop_tpl->assign("e_id", $this->currentTemplate);
				$loop_tpl->assign("e_page", $this->currentTemplate);
				$loop_tpl->assign("e_conarea", $conTab);
				$loop_tpl->assign("e_area", $conArea);
				$loop_tpl->assign("e_arealang", "{s_conareas:contents_".$conArea."}");
				$loop_tpl->assign("e_red", urlencode(parent::$currentURLPath));
				$loop_tpl->assign("e_conmax", $conMax);
				$loop_tpl->assign("e_busycon", $busyCon);
				$loop_tpl->assign("e_chooseNewElement", parent::$newElement);
				$conType != "menu" ? $loop_tpl->assign("e_display0", "display:none;") : '';
				
				// Falls ausgeschnittenes Element
				if($sessionCopy) {
					
					// Button: cancel
					$btnDefs	= array(	"type"		=> "button",
											"class"		=> "cc-button button cancelpaste editcon button-icon-only",
											"title"		=> "{s_javascript:cancel}",
											"attr"		=> 'data-action="editcon" data-actiontype="cancelpaste" data-url="' . SYSTEM_HTTP_ROOT . '/access/editElements.php?page=admin&action=cancelpaste" data-menuitem="true" data-id="item-id-' . $i . '" data-menutitle="{s_javascript:cancel}"',
											"icon"		=> "cancel",
											"iconclass"	=> "cc-admin-icons cc-icons cc-icon-cancel"
										);
					
					$cancelBtn	=	parent::getButton($btnDefs);
		
					$loop_tpl->assign("e_cancelpaste", $cancelBtn);
				}
				else
					$loop_tpl->assign("e_display1", "display:none;");
				if(!$sessionCopy) $loop_tpl->assign("e_display1", "display:none;");
				$busyCon == $i ? $loop_tpl->assign("e_display4", "display:none;") : '';
				$i == 1 ? $loop_tpl->assign("e_display5", "display:none;") : '';
				if($conType == "gallery" || !in_array($conType, $this->moduleTypes)) {
					$loop_tpl->assign("e_display7", "display:none;");
				}
				
				in_array($conType, $this->moduleTypes) ? $loop_tpl->assign("e_datatype", self::replaceStaText("{s_nav:admin".$conType."}")) : '';
				if($conType == "img") {
					$hArr	= explode(">", strip_tags($list, '<img>'));
					$loop_tpl->assign("e_previewImage", reset($hArr) . '>');
					$loop_tpl->assign("e_uploader", $this->getFileUploadBox("images", $conArea, $i));
				}
				else {
					$loop_tpl->assign("e_display-img", "display:none;");
				}
				$conType != "gallery" && !array_key_exists("isGallery", $conDef) ? $loop_tpl->assign("e_display8", "display:none;") . $loop_tpl->assign("e_display-gall", "display:none;") : '';
				$conType == "html" ? $loop_tpl->assign("e_htmlContent", htmlentities($conDef["html"], ENT_QUOTES, "UTF-8")) : '';
				$conType != "form" && $conType != "formdata" ? $loop_tpl->assign("e_display9", "display:none;") : '';

				if(!in_array($this->currentTemplate, $this->diffConIDs)
				|| count($this->diffConTables[$conTab]) == 0
				|| !in_array($this->currentTemplate, $this->diffConTables[$conTab])
				)
					$loop_tpl->assign("e_display-directchange", "display:none;");
				count($o_lng->installedLangs) < 2	? $loop_tpl->assign("e_display-alllangs", "display:none!important;") : '';
				$list == "" || (strpos($list, '<div class="textWrapper">') === 0 && strlen($list) == 35) ? $loop_tpl->assign("e_empty", " empty") : '';
				$conType == "gallery" && empty($subType) ? $loop_tpl->assign("e_galltypes", Admin::getGalleryTypes("edit", $conDef[1])) : '';
				$conType == "gallery" && empty($conDef[0]) ? $loop_tpl->assign("e_hideempty", ' style="display:none;"') : '';
				
				// Falls Galerie, FE-Edit Editor
				if($conType == "gallery") {
					$this->headIncludeFiles['moduleeditor']		= true;
				}
				
				// Columns
				$maxCols	= empty(parent::$styleDefs['fullrowcnt']) ? 12 : parent::$styleDefs['fullrowcnt'];
				$loop_tpl->assign("e_maxcols", $maxCols);
				$loop_tpl->assign("e_row", parent::$styleDefs['row']);

				// Content-Definitionen
				if(count($conDef) > 0) {
				
					foreach($conDef as $key => $def) {
						
						$loop_tpl->assign("e_conDef-" . $key, $conDef[$key]);
						
						if($key == "publish") {
							$loop_tpl->assign("e_hidden", ($def == "publish" ? "" : " hiddenElement"));
							$loop_tpl->assign("e_status", ($def == "publish" ? "1" : "0"));
							$loop_tpl->assign("e_display-pub1", ($def == "publish" ? "display:none;" : ""));
							$loop_tpl->assign("e_display-pub2", ($def != "publish" ? "display:none;" : ""));
						}
						if($key == "cols") {
							$loop_tpl->assign("e_cols", (empty($def) ? $maxCols : (int)$def));
						}
					}
				}
				
				$loopContent .= $loop_tpl->getTemplate();
				
				$i++;
			}

			// Falls keine Template-(Datenbank)-Inhalte vorhanden sind und der FE_Mode aktiviert ist
			if($loopContent == "" && $feMode === true) {
				
				$loop_tpl	= clone $tpl_loop; // Objekt-Instanz klonieren
				
				$loop_tpl->assign("dbcontents", "");
				
				if($this->adminPage || $feMode === true) {
					
					$conMax 	= $this->totContentNum[$conTab];
					$taskStr	= "tpl&type=edit";
					$diffCon	= !in_array($this->currentTemplate, $this->diffConIDs) || count($this->diffConTables[$conTab]) == 0;
					
					$this->assignEmptyFEElement($loop_tpl, $this->currentTemplate, $this->currentTemplate, $conTab, $conArea, "contents_", $conMax, $taskStr, $sessionCopy, $diffCon, $conIconPath);
					
				}
				
				$loopContent .= $loop_tpl->getTemplate();
			}
			
			self::$o_mainTemplate->assign(strtoupper($conArea), $loopContent);
			self::$o_mainTemplate->poolAssign[strtoupper($conArea)] = $loopContent;
			
			
			$tc++;
		
		} // Ende foreach areasTplContents
		
		} // Ende falls includeTplContents
		

		// Hauptcontent-Elemente ersetzen bzw. zuweisen (contents_main)
		$baseTableContents	= str_replace("_preview", "", parent::$tableContents);
		$dbContents_main	= $this->contents[$baseTableContents];
		$loopContent		= "";
		$loopOutput			= "";
		$i					= 1;

		// Ersetzen von Platzhaltern in wiederholten Bereichen
		foreach($dbContents_main as $key => $list) {
		
			// Falls das Hauptinhaltselement kein Datenbankeintrag ist, sprich keine "Typ"-Bezeichnung hat, nicht das fe-Edit tpl nehmen
			if(strpos($key, "con") === false) {
				$loopContent .= $list;
				continue;
			}
				
			$loop_tpl	= clone $tpl_loop; // Objekt-Instanz klonieren
		
			$loop_tpl->assign("dbcontents", $list);
			
			// Falls nicht FE-Mode
			if(!$this->adminPage
			&& !$feMode
			) {
				$loopContent .= $this->getContentDef("open", $baseTableContents, $key);
				$loopContent .= $loop_tpl->getTemplate();
				$loopContent .= $this->getContentDef("close", $baseTableContents, $key);
				$i++;
				continue;
			}
			
			$hasDbContent	= true;
			
			$conMax 		= $this->totContentNum[$baseTableContents];
			$busyCon 		= self::getConNumber(DB_TABLE_PREFIX . $baseTableContents, $this->pageId);
			$conType		= "";
			$subType		= "";
			$conDef			= array();
			$conIconPath	= SYSTEM_HTTP_ROOT . '/themes/' . ADMIN_THEME . '/img';
			$isPlugin		= isset($this->isContentPlugin[$baseTableContents]["con".$i]);
			

			// Ggf. Inhaltssubtyp ermitteln (Plug-Ins)
			if(!empty($this->contentSubTypes[$baseTableContents]["con".$i])) {
				$subType	= " [" . $this->contentSubTypes[$baseTableContents]["con".$i] . "]";
				$conType	= $this->contentTypes[$baseTableContents]["con".$i];
			}
			
			// Inhaltstyp ermitteln
			elseif(isset($this->contentTypes[$baseTableContents]["con".$i]))
				$conType	= $this->contentTypes[$baseTableContents]["con".$i];
			
			if($conType == "text") {
				$loop_tpl->assign("e_textbegin", '<div class="contents_main editableText" title="{s_title:edittext}">');			
				$loop_tpl->assign("e_textend", '</div>');
			}
			else {
				$loop_tpl->assign("e_display-text", "display:none;");
			}
		
			// Inhaltselement-Definitionen ermitteln
			if(isset($this->contentDefinitions[$baseTableContents]["con".$i]))
				$conDef	= $this->contentDefinitions[$baseTableContents]["con".$i];
			
			// Falls Plugin
			if($isPlugin)
				$conIconPath	= PLUGIN_URL . '/' . $this->contentTypes[$baseTableContents]["con".$i] . '/img';
			
			// EditDetails Tpl
			$editDetails	= $this->getEditDetails($conType, $isPlugin);
			$loop_tpl->assign("e_editdetails", $editDetails);
		
		
			$loop_tpl->assign("e_path", PROJECT_HTTP_ROOT);
			$loop_tpl->assign("e_coniconpath", $conIconPath);
			$loop_tpl->assign("e_connr", $i);
			$loop_tpl->assign("e_edittask", "edit");
			$loop_tpl->assign("e_contype", $conType);
			$loop_tpl->assign("e_subtype", $subType);
			$loop_tpl->assign("e_id", $this->pageId);
			$loop_tpl->assign("e_page", $this->currentPage);
			$loop_tpl->assign("e_conarea", $baseTableContents);
			$loop_tpl->assign("e_area", "main");
			$loop_tpl->assign("e_arealang", "{s_conareas:" . $baseTableContents . "}");
			$loop_tpl->assign("e_red", urlencode(parent::$currentURLPath));
			$loop_tpl->assign("e_conmax", $conMax);
			$loop_tpl->assign("e_busycon", $busyCon);
			$loop_tpl->assign("e_chooseNewElement", parent::$newElement);
			
			$conType != "menu" ? $loop_tpl->assign("e_display0", "display:none;") : '';
			
			// Falls ausgeschnittenes Element
			if($sessionCopy) {
				
				// Button: cancel
				$btnDefs	= array(	"type"		=> "button",
										"class"		=> "cc-button button cancelpaste editcon button-icon-only",
										"title"		=> "{s_javascript:cancel}",
										"attr"		=> 'data-action="editcon" data-actiontype="cancelpaste" data-url="' . SYSTEM_HTTP_ROOT . '/access/editElements.php?page=admin&action=cancelpaste" data-menuitem="true" data-id="item-id-' . $i . '" data-menutitle="{s_javascript:cancel}"',
										"icon"		=> "cancel",
										"iconclass"	=> "cc-admin-icons cc-icons cc-icon-cancel"
									);
				
				$cancelBtn	=	parent::getButton($btnDefs);
				
				$loop_tpl->assign("e_cancelpaste", $cancelBtn);
			}
			else
				$loop_tpl->assign("e_display1", "display:none;");
			
			$busyCon == $i ? $loop_tpl->assign("e_display4", "display:none;") : '';
			$i == 1 ? $loop_tpl->assign("e_display5", "display:none;") : '';
			
			if($conType == "gallery" || !in_array($conType, $this->moduleTypes)) {
				$loop_tpl->assign("e_display7", "display:none;");
			}
			
			in_array($conType, $this->moduleTypes) ? $loop_tpl->assign("e_datatype", self::replaceStaText("{s_nav:admin".$conType."}")) : '';
			
			if($conType == "img") {
				$hArr	= explode(">", strip_tags($list, '<img>'));
				$loop_tpl->assign("e_previewImage", reset($hArr) . '>');
				$loop_tpl->assign("e_uploader", $this->getFileUploadBox("images", $baseTableContents, $i));
			}
			else {
				$loop_tpl->assign("e_display-img", "display:none;");
			}
			
			$conType != "gallery" && !array_key_exists("isGallery", $conDef) ? $loop_tpl->assign("e_display8", "display:none;") . $loop_tpl->assign("e_display-gall", "display:none;") : '';
			$conType == "html" ? $loop_tpl->assign("e_htmlContent", htmlentities($conDef["html"], ENT_QUOTES, "UTF-8")) : '';
			$conType != "form" && $conType != "formdata" ? $loop_tpl->assign("e_display9", "display:none;") : '';
			!in_array($this->pageId, $this->diffConIDs)	? $loop_tpl->assign("e_display-directchange", "display:none;") : '';
			count($o_lng->installedLangs) < 2	? $loop_tpl->assign("e_display-alllangs", "display:none!important;") : '';
			$list == "" || (strpos($list, '<div class="textWrapper">') === 0 && strlen($list) == 35) ? $loop_tpl->assign("e_empty", " empty") : '';
			$conType == "gallery" && empty($subType) ? $loop_tpl->assign("e_galltypes", Admin::getGalleryTypes("edit", $conDef[1])) : '';
			$conType == "gallery" && empty($conDef[0]) ? $loop_tpl->assign("e_hideempty", ' style="display:none;"') : '';
				
			// Falls Galerie, FE-Edit Editor
			if($conType == "gallery") {
				$this->headIncludeFiles['moduleeditor']		= true;
			}
			
			// Columns
			$maxCols	= empty(parent::$styleDefs['fullrowcnt']) ? 12 : parent::$styleDefs['fullrowcnt'];					
			$loop_tpl->assign("e_maxcols", $maxCols);
			$loop_tpl->assign("e_row", parent::$styleDefs['row']);
			
			// Content-Definitionen
			if(count($conDef) > 0) {						
				foreach($conDef as $key => $def) {
					$loop_tpl->assign("e_conDef-" . $key, $conDef[$key]);
					if($key == "publish") {
						$loop_tpl->assign("e_hidden", ($def == "publish" ? "" : " hiddenElement"));
						$loop_tpl->assign("e_status", ($def == "publish" ? "1" : "0"));
						$loop_tpl->assign("e_display-pub1", ($def == "publish" ? "display:none;" : ""));
						$loop_tpl->assign("e_display-pub2", ($def != "publish" ? "display:none;" : ""));
					}
					if($key == "cols") {
						$loop_tpl->assign("e_cols", (empty($def) ? $maxCols : (int)$def));
					}
				}
			}
			
			$i++;
			
			$loopContent .= $loop_tpl->getTemplate();
		
		}

		// Falls keine Haupt-(Datenbank)-Inhalte vorhanden sind und der FE_Mode aktiviert ist
		if(($loopContent == "" || !$hasDbContent) && $feMode === true) {
			
			$loop_tpl	= clone $tpl_loop; // Objekt-Instanz klonieren
			
			$loop_tpl->assign("dbcontents", "");

			if($this->adminPage || $feMode === true) {
				
				$conMax 	= $this->totContentNum[$baseTableContents];
				$taskStr	= "edit";
				$diffCon	= !in_array($this->pageId, $this->diffConIDs) || count($this->diffConTables[$baseTableContents]) == 0;
				
				$this->assignEmptyFEElement($loop_tpl, $this->pageId, $this->currentPage, $baseTableContents, "main", "contents_", $conMax, $taskStr, $sessionCopy, $diffCon, $conIconPath);
				
			}
						
			$loopContent .= $loop_tpl->getTemplate();
		}
		

		// Hauptinhalte zuweisen, falls nicht schon geschehen
		self::$o_mainTemplate->assign("MAIN", $loopContent);
		

		// Falls eine Bildergalerie vorhanden, FE-Edit Editor einbinden
		if(!empty($this->headIncludeFiles['moduleeditor'])) {
		
			// Head-Definitionen (headExt)
			// Script- und css-Includes für den HTML-Headbereich (headIncludes)
			require_once PROJECT_DOC_ROOT."/inc/classes/Admin/class.HeadIncludes.php"; // TinyMCE 4
			
			$o_headInc	= new HeadIncludes($this->headIncludeFiles, $this->adminLang, true);
			$o_headInc->getHeadIncludes("modules", "gallery", ".galleryEditor", parent::$styleDefs['btn'] . ' ' . parent::$styleDefs['btnpri']);
			
			$this->mergeHeadCodeArrays($o_headInc);
		}
	
	}



	/**
	 * Ersetzt Inhaltsplatzhalter mit Inhalten
	 *
	 * @access	public
     * @return  string
	 */
	public function assignReplaceContents()
	{

		// Accountmenu
		if(!$this->adminPage) {
			$accountMenu = $this->getAccountMenu(); // Benutzerkontomenü generieren
			self::$o_mainTemplate->poolAssign["account"]	= $accountMenu;
		}

		// Menues
		self::$o_mainTemplate->poolAssign["main_menu"]		= $this->mainMenu;
		self::$o_mainTemplate->poolAssign["sub_menu"]		= $this->subMenu;
		self::$o_mainTemplate->poolAssign["parroot_menu"]	= $this->parRootMenu;
		self::$o_mainTemplate->poolAssign["parsub_menu"]	= $this->parSubMenu;
		self::$o_mainTemplate->poolAssign["top_menu"]		= $this->topMenu;
		self::$o_mainTemplate->poolAssign["bc_nav"]			= $this->bcNav;
		self::$o_mainTemplate->poolAssign["foot_menu"]		= $this->footMenu;


		// Aktuelle Seite
		self::$o_mainTemplate->poolAssign["currpage"]		= $this->currentPage;

		// Aktuelle Seiten-ID
		self::$o_mainTemplate->poolAssign["currpageid"]		= $this->pageId;

		// Root-Eltern-Seite
		if(parent::$rootPageId != "" && parent::$rootPageId > 0)
			self::$o_mainTemplate->poolAssign["rootpageid"]	= " rootPage-" . parent::$rootPageId;

		// Eltern-Seite
		if(parent::$parentPageId != "" && parent::$parentPageId > 0)
			self::$o_mainTemplate->poolAssign["parpageid"]	= " parentPage-" . parent::$parentPageId;

		// Sprachauswahl
		if(count($this->o_lng->installedLangs) > 1)
			#self::$o_mainTemplate->poolAssign["lang"] 		= $this->o_lng->getLangSelector($this->pageId, "text", '<img src="' .PROJECT_HTTP_ROOT.'/'.IMAGE_DIR.'dot.png" alt="separator" />', false);
			self::$o_mainTemplate->poolAssign["lang"]		= $this->o_lng->getLangSelector($this->pageId, "flag", "");

	}



	/**
	 * Ersetzt leere FE-Edit Elemente Inhalten
	 *
	 * @access	public
     * @return  string
	 */
	public function assignEmptyFEElement($loop_tpl, $id, $page, $conTab, $conArea, $tabPrefix, $conMax, $taskStr, $sessionCopy, $diffCon, $conIconPath)
	{

		$loop_tpl->assign("e_path", PROJECT_HTTP_ROOT);
		$loop_tpl->assign("e_coniconpath", $conIconPath);
		$loop_tpl->assign("e_edittask", $taskStr);
		$loop_tpl->assign("e_connr", 0);
		$loop_tpl->assign("e_id", $id);
		$loop_tpl->assign("e_page", $page);
		$loop_tpl->assign("e_conarea", $conTab);
		$loop_tpl->assign("e_area", $conArea);
		$loop_tpl->assign("e_arealang", "{s_conareas:".$tabPrefix.$conArea."}");
		$loop_tpl->assign("e_red", urlencode(parent::$currentURLPath));
		$loop_tpl->assign("e_conmax", $conMax);
		$loop_tpl->assign("e_busycon", 0);
		$loop_tpl->assign("e_contype", "empty");
		$loop_tpl->assign("e_chooseNewElement", parent::$newElement);
		$loop_tpl->assign("e_display0", "display:none;");
		if(!$sessionCopy) $loop_tpl->assign("e_display1", "display:none;");
		$loop_tpl->assign("e_display2", "display:none;");
		$loop_tpl->assign("e_display3", "display:none;");
		$loop_tpl->assign("e_display4", "display:none;");
		$loop_tpl->assign("e_display5", "display:none;");
		$loop_tpl->assign("e_display6", "display:none;");
		$loop_tpl->assign("e_display7", "display:none;");
		$loop_tpl->assign("e_display8", "display:none;");
		$loop_tpl->assign("e_display9", "display:none;");
		$loop_tpl->assign("e_display-pub1", "display:none;");
		$loop_tpl->assign("e_display-pub2", "display:none;");
		$loop_tpl->assign("e_display-text", "display:none;");
		$loop_tpl->assign("e_display-img", "display:none;");
		$loop_tpl->assign("e_display-gall", "display:none;");
		$loop_tpl->assign("e_display-directedit", "display:none;");
		$loop_tpl->assign("e_moduleicon", "blind.gif");
		$loop_tpl->assign("e_empty", " empty firstEmpty");
		!$diffCon ? $loop_tpl->assign("e_display-directchange", "display:none;") : '';
		
		return true;
	
	}
	

	/**
	 * Gibt bestimmte content definitions zurück
	 *
	 * @param	conType		content type
	 * @param	isPlugin	isPlugin
	 * @access	public
	 */
	public function getEditDetails($conType, $isPlugin)
	{
			
		// Falls Plugin
		if($isPlugin)
			$tplDir	= PLUGIN_DIR . $conType . '/templates/edit_tpls/';
		else
			$tplDir	= 'system/themes/' . ADMIN_THEME . '/templates/edit_tpls/';
	
		$tplName= 'edit_' . $conType . '.tpl';
		
		// If no Tpl for this type yet
		if(empty($this->editDetailsTpls[$conType])) {
			
			// If no edit tpl file exists return empty string
			if(isset($this->editDetailsTpls[$conType])
			|| !file_exists(PROJECT_DOC_ROOT . '/' . $tplDir . $tplName)
			) {
				$this->editDetailsTpls[$conType]	= false;
				return "";
			}
			
			$this->editDetailsTpls[$conType]	= file_get_contents(PROJECT_DOC_ROOT . '/' . $tplDir . $tplName);
		}
		
		return $this->editDetailsTpls[$conType];
	
	}
	

	/**
	 * Gibt bestimmte content definitions zurück
	 *
	 * @param	conDef	content definition
	 * @param	conTab	content table
	 * @param	key		content index key
	 * @access	public
	 */
	public function getContentDef($conDef, $conTab, $key)
	{
	
		$output	= "";
		
		if(isset($this->contentDefinitions[$conTab][$key][$conDef]))
			$output	= $this->contentDefinitions[$conTab][$key][$conDef];
		
		return $output;
	
	}
	

	/**
	 * Gibt eine Anzahl an Column-grid-Einheiten zurück
	 *
	 * @param	colDef	falls false, wird ein Ergebnis-Array mit Script-Datei und -Code zurückgegeben
	 * @access	public
	 */
	public function getColumnGridCount($colDef)
	{
		
		if(is_numeric($colDef)
		&& isset(parent::$styleDefs['col' . $colDef])
		)
			return $colDef;
		
		if(strpos($colDef, "full") !== false)
			return parent::$styleDefs['fullrowcnt'];
		
		if(strpos($colDef, "half") !== false)
			return floor(parent::$styleDefs['fullrowcnt'] / 2);
		
		if(strpos($colDef, "2thirds") !== false)
			return floor(parent::$styleDefs['fullrowcnt'] / 3 * 2);
		
		if(strpos($colDef, "3quaters") !== false)
			return floor(parent::$styleDefs['fullrowcnt'] / 4 * 3);
		
		if(strpos($colDef, "third") !== false)
			return floor(parent::$styleDefs['fullrowcnt'] / 3);
		
		if(strpos($colDef, "quater") !== false)
			return floor(parent::$styleDefs['fullrowcnt'] / 4);
		
		if(empty(parent::$styleDefs['fullrowcnt']))
			return 12;
	
		return parent::$styleDefs['fullrowcnt'];
	
	}
	

	/**
	 * Gibt eine Column-grid-class zurück
	 *
	 * @param	colDef	falls false, wird ein Ergebnis-Array mit Script-Datei und -Code zurückgegeben
	 * @param	replace	falls true, wird der Präfix für "medium" mit dem von kleineren Anzeigen ersetzt (default = false)
	 * @param	colmd	Präfix für "medium" (default = '')
	 * @param	colrepl	Präfix für "small/s-small" (default = '')
	 * @access	public
	 */
	public function getColumnGridClass($colDef, $replace = false, $colmd = "", $colrepl = "")
	{
		
		if(is_numeric($colDef)
		&& isset(parent::$styleDefs['col' . $colDef])
		) {
			if($replace) {
				if(!empty($colmd)
				&& !empty($colrepl)
				)
					return " " . str_replace($colmd, $colrepl, parent::$styleDefs['col' . $colDef]);
				else
					return "";
			}
			else
				return parent::$styleDefs['col' . $colDef];
		}
		
		if(strpos($colDef, "full") !== false)
			return parent::$styleDefs['fullrow'];
		
		if(strpos($colDef, "half") !== false)
			return parent::$styleDefs['halfrow'];
		
		if(strpos($colDef, "2thirds") !== false)
			return parent::$styleDefs['2thirds'];
		
		if(strpos($colDef, "3quaters") !== false)
			return parent::$styleDefs['3quaters'];
		
		if(strpos($colDef, "third") !== false)
			return parent::$styleDefs['thirdrow'];
		
		if(strpos($colDef, "quater") !== false)
			return parent::$styleDefs['quaterrow'];
	
		return "";
	
	}
	
}
