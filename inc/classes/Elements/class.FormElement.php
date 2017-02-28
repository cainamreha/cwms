<?php
namespace Concise;



/**
 * FormElement
 * 
 */

class FormElement extends ElementFactory implements Elements
{
	
	/**
	 * Gibt ein FormElement zurück
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

		#####################################
		#####  Individuelles Formular  ######
		#####################################
	
		// Formularkonfiguration
		$formCon = explode("<>", $this->conValue);
		
		// Falls keine DB-Tabelle angegeben ist Meldung ausgeben
		if(!isset($formCon[0]) || $formCon[0] == "")
			return $this->getContentElementWrapper($this->conAttributes, "No form data table specified."); // kein db-Tabellenname
		
		// Prüft ob Tabelle vorhanden ist
		if(!$this->DB->tableExists(DB_TABLE_PREFIX . 'form_' . $formCon[0]))
			return $this->getContentElementWrapper($this->conAttributes, "Form table &quot;form_" . $formCon[0] . "&quot; not found."); // kein db-Tabellenname
		

		$this->cssFiles[] = STYLES_DIR . "forms.css"; // css-Datei einbinden

		// Ggf. Formvalidator
		if(!empty($formCon[14]))
			$this->scriptFiles["formvalidator"]	= "extLibs/jquery/form-validator/jquery.form-validator.min.js";
		
		
		// Präfix form_ für DB-Tabellennamen einfügen
		$formTable			= 'form_' . $formCon[0];
		$formTableDB		= $this->DB->escapeString($formCon[0]);
		$formTitle			= "";
		$queryFormFields	= array();
		$configArray		= array();
		$formPollAccess		= true;
		
		// db-Query nach Feldern des Formulars, falls im Backend angelegt
		$queryFormTable = $this->DB->query( "SELECT `id`, `title_".$this->lang."`, `poll` 
													FROM `" . DB_TABLE_PREFIX . "forms`
													WHERE `table` = '$formTableDB'
												  ");
			
		#var_dump($queryFormFields);
		
		if(is_array($queryFormTable)
		&& count($queryFormTable) > 0
		) {
		
			// Falls ein Poll-Formular angezeigt werden soll, überprüfen ob kein Cookie vorliegt
			if($queryFormTable[0]['poll'] && isset($GLOBALS['_COOKIE']['poll-' . $formTable]))
				$formPollAccess	= false;
			else
				// db-Query nach Feldern des Formulars, falls im Backend angelegt
				$queryFormFields = $this->DB->query( "SELECT * 
															FROM `" . DB_TABLE_PREFIX . "forms` AS n 
															LEFT JOIN `" . DB_TABLE_PREFIX . "forms_definitions` AS p 
															ON n.`id` = p.`table_id` 
															WHERE n.`table` = '$formTableDB' 
															ORDER BY p.`sort_id` ASC
														  ");
				#var_dump($queryFormFields);
		}										

		// Falls bereits abgestimmt wurde bei Poll
		if($formPollAccess	=== false)
			return $this->getContentElementWrapper($this->conAttributes, '<p class="notice hint pollVoted">{s_notice:pollvoted}</p>');
		
		// Falls keine Tabellendaten vorhanden sind und keine Konfigurationsdatei vorhanden ist
		if(is_array($queryFormFields)) {
			
			if(count($queryFormFields) == 0
			&& !file_exists(PROJECT_DOC_ROOT."/inc/classes/Forms/config.autoForm.".$formCon[0].".php")
			)
				return $this->getContentElementWrapper($this->conAttributes, '<p class="notice error">{s_error:noformconf}</p>'); // kein db-Tabellenname
		
			$formTitle = count($queryFormFields) > 0 ? $queryFormTable[0]["title_".$this->lang] : ''; // Formulartitel
		}
			
		if(!isset($formCon[2]) || $formCon[2] == "") // recipients/Quelle der Haupt-E-Mail
			$formCon[2] = array();
		else
			$formCon[2] = explode(",", $formCon[2]);
		if(!isset($formCon[3]) || $formCon[3] == "") // E-Mail nur an Besitzer
			$formCon[3] = 0;
		elseif($formCon[3] == 1)
			$formCon[1] = 1; // Formmailer setzen
		if(!isset($formCon[4]) || $formCon[4] == "") // recipients (nur Besitzer)
			$formCon[4] = array();
		else
			$formCon[2] = array_merge($formCon[2], explode(",", $formCon[4])); // E-Mails über [2] mitgeben
		if(!isset($formCon[5]) || $formCon[5] == "") // recipientsCC
			$formCon[5] = array();
		else
			$formCon[5] = explode(",", $formCon[5]);
		if(!isset($formCon[6]) || $formCon[6] == "") // recipientsBCC
			$formCon[6] = array();
		else
			$formCon[6] = explode(",", $formCon[6]);
		if(!isset($formCon[7]) || $formCon[7] == "") // subject
			$formCon[7] = "none";
		if(isset($formCon[8]) && $formCon[8] == 1) // pdf generieren
			$formCon[8] = true;
		else
			$formCon[8] = false;
		if(!isset($formCon[9])) // Ordner für pdf Dateien
			$formCon[9] = "";
		if(empty($formCon[10])) // pdf an Browser senden
			$formCon[10] = false;
		else
			$formCon[10] = true;
		if(empty($formCon[11])) // pdf an E-Mail anhängen
			$formCon[11] = false;
		else
			$formCon[11] = true;
		if(empty($formCon[12])) // pdf ist benutzerspezifisch
			$formCon[12] = false;
		else
			$formCon[12] = true;
		if(empty($formCon[13])) // keine Speicherung in DB
			$formCon[13] = false;
		else
			$formCon[13] = true;
		
		#var_dump($queryFormFields);	

		
		// Zunächst das entsprechende Modul einbinden (FormGenerator-Klasse)
		require_once PROJECT_DOC_ROOT."/inc/classes/Forms/class.FormGenerator.php";
		
									
		if(!isset($formCon[1]) || $formCon[1] == "" || $formCon[1] == 0) // Formmailer
			$formCon[1] = false;
		else { // Formular per E-Mail
		
			$formCon[1] = true;
			
			// FormMailerdatei einbinden
			require_once PROJECT_DOC_ROOT."/inc/classes/Forms/class.FormMailer.php";
		
		}


		// Formular generieren
		$o_myForm				= new FormGenerator(array(), $formTable); // Formular-Objekt	
		
		$o_myForm->DB			= $this->DB;
		$o_myForm->lang			= $this->lang;
		$o_myForm->adminPage	= $this->adminPage;
		
		// EventDispatcher
		$o_myForm->o_dispatcher	= $this->o_dispatcher;
	
		// Falls Formular im Backend angelegt, Configarray erstellen
		if(count($queryFormFields) > 0) {
			
			// Methode zum Erstellen eines Arrays mit Konfigurationsdaten für das Formular
			$configArray = $o_myForm->makeFormConfigArray($queryFormFields, $this->lang);
		}
		else
			// Formular-Konfigurationsdatei einbinden
			require(PROJECT_DOC_ROOT."/inc/classes/Forms/config.autoForm.".$formCon[0].".php");

		// Form parameter
		$o_myForm->formTitle		= ($formTitle != "" ? $formTitle : str_replace("form_", "", $formTable)); //Titel der Tabelle speichern
		$o_myForm->formMailer		= $formCon[1]; //FormMailer
		$o_myForm->recipients		= $formCon[2]; //E-Mailadresse bzw. Art der Bestimmung des E-Mailempfängers
		$o_myForm->recipientsCC		= $formCon[5]; //E-Mailadresse(n) Kopie-Empfänger
		$o_myForm->recipientsBCC	= $formCon[6]; //E-Mailadresse(n) Blindkopie-Empfänger
		$o_myForm->mailSubject		= $formCon[7]; //E-Mail-Betreff
		$o_myForm->makePDF			= $formCon[8]; //PDF erstellen
		$o_myForm->pdfFolder		= $formCon[9]; //PDF Ordner
		$o_myForm->browserPDF		= $formCon[10]; //PDF an Browser senden
		$o_myForm->mailPDF			= $formCon[11]; //PDF per E-Mail senden
		$o_myForm->userPDF			= $formCon[12]; //PDF in Ordner _user speichern
		$o_myForm->noDbStorage		= $formCon[13]; //Formulardaten nicht in DB speichern

		// Formular generieren
		$output	= $o_myForm->printForm($configArray);

		
		// Merge script files
		$this->scriptFiles			= array_merge($this->scriptFiles, $o_myForm->scriptFiles);
		$this->scriptCode			= array_merge($this->scriptCode, $o_myForm->scriptCode);
		$this->cssFiles				= array_merge($this->cssFiles, $o_myForm->cssFiles);
		
		
		// Falls die angegebene DB-Tabelle nicht existiert
		if($output === false)
			return $this->getContentElementWrapper($this->conAttributes, "Form data table does not exist."); // kein db-Tabellenname
		
		// Ggf. Attribute (Styles) Wrapper-div hinzufügen
		if($this->conAttributes['id'] != "" || $this->conAttributes['class'] != "" || $this->conAttributes['style'] != "")
			$output	= $this->getContentElementWrapper($this->conAttributes, $output);
		
		// Form validator script
		if(!empty($formCon[14]))
			$output	.= $this->getScriptTag($formTable);

		return $output;
	
	}	
	

	// getScriptTag
	public function getScriptTag($formTable)
	{

		$formID		= str_replace("_", "-", $formTable);

		return	'<script>' . "\r\n" .
				'head.ready("jquery", function(){' . "\r\n" .
				'head.load({formvalidator: "' . PROJECT_HTTP_ROOT . '/extLibs/jquery/form-validator/jquery.form-validator.min.js"});' . "\r\n" .
				'head.ready("formvalidator", function(){' . "\r\n" .
					'$(document).ready(function(){' . "\r\n" .
						'$.validate({
							form : "#' . $formID . ':not(.cc-form-multistep)",
							lang : "' . $this->lang . '",
							validateOnBlur : false,
							//errorMessagePosition : "top",
							//errorMessagePosition : $("#' . $formID . ' .formErrorBox"),
							scrollToTopOnError : false,
							borderColorOnError : "",
							onError : function($form) {
								/*
								if($form.closest(".form-minimal").length){
									$($form).find(".formErrorBox").addClass("' . ContentsEngine::replaceStyleDefs("{t_class:alert} {t_class:error}") . '").hide();
								}else{
									$($form).find(".formErrorBox").addClass("' . ContentsEngine::replaceStyleDefs("{t_class:alert} {t_class:error}") . '").hide().fadeIn(800);
								}
								*/
							}
						});' . "\r\n" .
					'});' . "\r\n" .
				'});' . "\r\n" .
				'});' . "\r\n" .
				'</script>' . "\r\n";
	
	}
	
}
