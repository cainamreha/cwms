<?php
namespace Concise;



/**
 * TextElement
 * 
 */

class DataElement extends ElementFactory implements Elements
{
	
	/**
	 * Gibt ein TextElement zurück
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
		###  Artikel/News/Termine  ###
		##############################

		$modType		= $this->conType;
		$newsConValue	= $this->conValue;
		$newsCon		= explode("<>", $this->conValue);
		$dataDate		= "";
		
		if(!isset($newsCon[0]))
			$newsCon[0] = ""; // Kategorie
		if(!isset($newsCon[1]))
			$newsCon[1] = ""; // Darstellung
		if(!isset($newsCon[2]))
			$newsCon[2] = ""; // ZielseitenID
		if(!isset($newsCon[3]))
			$newsCon[3] = 1; // Limit
		if(!isset($newsCon[4]))
			$newsCon[4] = "datedsc"; // Sortierung
		if(!isset($newsCon[5]))
			$newsCon[5] = 0; // Kommentare
		if(!isset($newsCon[6]))
			$newsCon[6] = 0; // Rating
		if(!isset($newsCon[7]))
			$newsCon[7] = array("public"); // Benutzergruppen, die zum Rating berechtigt sind
		else
			$newsCon[7] = explode(",", $newsCon[7]);
		if(!isset($newsCon[8]))
			$newsCon[8] = array("public"); // Benutzergruppen, die zum Lesen von Kommentaren berechtigt sind
		else
			$newsCon[8] = explode(",", $newsCon[8]);
		if(!isset($newsCon[9]))
			$newsCon[9] = array("public"); // Benutzergruppen, die zum Schreiben von Kommentaren berechtigt sind
		else
			$newsCon[9] = explode(",", $newsCon[9]);
		if(!isset($newsCon[10]))
			$newsCon[10] = ""; // Alternatives Template
			
		
		$this->rootCatID	= $newsCon[0]; // ursprüngliche CatID für das Module speichern
		$this->displayMode	= $newsCon[1]; // Ansichtsmodus das Module speichern
		
		
		// Falls eine Kategorie-ID per GET übermittelt wurde
		if(isset($GLOBALS['_GET']['cid'])
		&& is_numeric($GLOBALS['_GET']['cid'])
		&& ($this->isMainContent
		 || $newsCon[1] == "catmenu"
		 || $newsCon[1] == "articlemenu")
		) {
			$newsCon[0]	= $GLOBALS['_GET']['cid'];
			
			// Falls eine Datensatz-ID per GET übermittelt wurde
			if(!empty($GLOBALS['_GET']['id'])
			&& is_numeric($GLOBALS['_GET']['id'])
			)
				$this->ID = $GLOBALS['_GET']['id'];
		}
		
		
		// Falls Hauptinhalt
		if($this->isMainContent) {
			
			// Falls eine Datensatz-ID per GET übermittelt wurde
			if($this->ID != "" && $newsCon[1] != "expired")
				$newsCon[1] = $this->ID; // Var newsCon[1] enthält jetzt die Daten-ID statt der Darstellungsform!
			
			// Evtl. POST-Parameter von Modulen auslesen (aus Kalender-Ansicht)
			if(isset($GLOBALS['_POST']['datepicker_mod'])
			&& $GLOBALS['_POST']['datepicker_mod'] != ""
			&& $newsCon[1] != "related"
			) {
				
				$modType	= $GLOBALS['_POST']['datepicker_mod'];
				$newsCon[0]	= $GLOBALS['_POST']['datepicker_cat'];
				$dataDate	= $GLOBALS['_POST']['datepicker_pickedDate'];
				$newsCon[1]	= "list"; // Listenansicht
				$this->displayMode	= $newsCon[1]; // Ansichtsmodus das Module speichern
			
			}
			// Evtl. GET-Parameter von Modulen auslesen
			elseif(isset($GLOBALS['_GET']['mod'])
			&& ($GLOBALS['_GET']['mod'] == "articles"
			 || $GLOBALS['_GET']['mod'] == "news"
			 || $GLOBALS['_GET']['mod'] == "planner")
			) {
				$modType = $GLOBALS['_GET']['mod'];
				
				// Falls ein Monat (Daten-Archiv) per GET übermittelt wurde
				if(isset($GLOBALS['_GET']['dam']) && is_numeric($GLOBALS['_GET']['dam']))
					$dataDate	= $GLOBALS['_GET']['dam'];				
			}
			
		} // Ende if pages
		
		
		require_once PROJECT_DOC_ROOT."/inc/classes/Modules/class.ModulesData.php";
		
		$o_module	= new ModulesData($this->DB, $this->o_lng, $this->o_dispatcher, $this->eventListeners, $this->o_page);
		
		$o_module->contentTable		= $this->conTable;
		$o_module->isMainContent	= $this->isMainContent;
		$o_module->backendLog		= $this->backendLog;
		$o_module->editorLog		= $this->editorLog;
		$o_module->adminLog			= $this->adminLog;
		$o_module->adminPage		= $this->adminPage;
		$o_module->pageId			= $this->pageId;
		$o_module->html5			= $this->html5;
		$o_module->themeConf		= $this->themeConf;
		
		$o_module->displayMode		= $this->displayMode;
		$o_module->ID				= $this->ID;
		$o_module->rootCatID		= $this->rootCatID;
		
		$output = $o_module->getModule($modType, $newsCon[0], $newsCon[1], $newsCon[2], $newsCon[3], $newsCon[4], $newsCon[5], $newsCon[6], $newsCon[7], $newsCon[8], $newsCon[9], $newsCon[10], $dataDate); // Datenausgabe generieren

		
		$this->scriptFiles			= array_merge($this->scriptFiles, $o_module->scriptFiles);
		$this->scriptCode			= array_merge($this->scriptCode, $o_module->scriptCode);
		$this->cssFiles				= array_merge($this->cssFiles, $o_module->cssFiles);
		
		// Ggf. Attribute (Styles) Wrapper-div hinzufügen
		$output	= $this->getContentElementWrapper($this->conAttributes, $output, 'cc-' . $this->conType . ' cc-module');
		
		return $output;
	
	}	
	
}
