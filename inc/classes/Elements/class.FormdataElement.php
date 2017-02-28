<?php
namespace Concise;



/**
 * FormdataElement
 * 
 */

class FormdataElement extends ElementFactory implements Elements
{
	
	/**
	 * Gibt ein FormdataElement zurück
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
		######  Formulardaten  #######
		##############################
	
		// Zunächst das entsprechende Modul einbinden (FormEvaluation-Klasse)
		require_once PROJECT_DOC_ROOT."/inc/classes/Forms/class.FormEvaluation.php";
	
		$this->cssFiles[]		= STYLES_DIR . "forms.css"; // css-Datei einbinden
		$this->scriptFiles[]	= JS_DIR . "forms.js"; // js-Datei einbinden
	
		$formParam = explode("<>", $this->conValue);
		
		// Falls keine DB-Tabelle angegeben ist Meldung ausgeben
		if(!isset($formParam[0]) || $formParam[0] == "")
			return $this->getContentElementWrapper($this->conAttributes, "No form data table specified."); // kein db-Tabellenname
		
		// Falls Tabelle nicht vorhanden
		if(!$this->DB->tableExists(DB_TABLE_PREFIX . 'form_' . $formParam[0]))
			return $this->getContentElementWrapper($this->conAttributes, "Form table &quot;" . DB_TABLE_PREFIX . "form_" . $formParam[0] . "&quot; not found."); // kein db-Tabellenname
		
		
		if(!isset($formParam[1]))
			$formParam[1] = ""; // Formulartitel
		if(!isset($formParam[2]))
			$formParam[2] = array();
		else
			$formParam[2] = $this->trimArrayElements(explode(",", $formParam[2])); // Formularfelder, deren Inhalte nicht angezeigt werden sollen (kommaseparierte Liste)
		if(isset($formParam[3]) && $formParam[3] == 1)
			$formParam[3] = true; // Bei Dateien nur den Namen anzeigen (keine Anzeige als Bild oder Link)
		else
			$formParam[3] = false;
		if(!isset($formParam[4]))
			$formParam[4] = array("admin"); // Editieren von Formulardaten erlauben für Benutzergruppe(n)
		else
			$formParam[4] = $this->trimArrayElements(explode(",", $formParam[4]));
		
		
		$o_myForm				= new FormEvaluation($formParam[0], $formParam[1], $formParam[2], $formParam[3], $formParam[4]); // Bestell-Objekt
		
		$o_myForm->DB			= $this->DB;
		$o_myForm->lang			= $this->lang;
		$o_myForm->adminLang	= $this->adminLang;
		$o_myForm->editLang		= $this->editLang;
		$o_myForm->adminPage	= $this->adminPage;
		$o_myForm->themeConf	= $this->themeConf;
		
		// EventDispatcher
		$o_myForm->o_dispatcher	= $this->o_dispatcher;
				
		$form	= $o_myForm->getFormData(); // Formular generieren
		$output	= $form;
		
		$this->scriptFiles			= array_merge($this->scriptFiles, $o_myForm->scriptFiles);
		$this->cssFiles				= array_merge($this->cssFiles, $o_myForm->cssFiles);
		
		// Ggf. Attribute (Styles) Wrapper-div hinzufügen
		if($this->conAttributes['id'] != "" || $this->conAttributes['class'] != "" || $this->conAttributes['style'] != "")
			$output	= $this->getContentElementWrapper($this->conAttributes, $output);

		return $output;
	
	}	
	
}
