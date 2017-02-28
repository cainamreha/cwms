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
		if(substr_count($this->conValue, "<>") > 10)
			$formCon	= explode("<>", $this->conValue); // Legacy decode
		else
			$formCon	= (array)json_decode($this->conValue);

		
		// Falls keine DB-Tabelle angegeben ist Meldung ausgeben
		if(!isset($formCon["formtab"]) || $formCon["formtab"] == "")
			return $this->getContentElementWrapper($this->conAttributes, "No form data table specified."); // kein db-Tabellenname
		
		// Prüft ob Tabelle vorhanden ist
		if(!$this->DB->tableExists(DB_TABLE_PREFIX . 'form_' . $formCon["formtab"]))
			return $this->getContentElementWrapper($this->conAttributes, "Form table &quot;form_" . $formCon["formtab"] . "&quot; not found."); // kein db-Tabellenname
		
		
		// Präfix form_ für DB-Tabellennamen einfügen
		$formTable			= 'form_' . $formCon["formtab"];
		$formTableDB		= $this->DB->escapeString($formCon["formtab"]);
		$formTitle			= "";
		$queryFormFields	= array();
		$configArray		= array();
		$formPollAccess		= true;

		// Css file
		$this->cssFiles[]	= STYLES_DIR . "forms.css";

		// Ggf. Formvalidator
		if(!empty($formCon["validator"])) {
			$this->scriptFiles["formvalidator"]	= "extLibs/jquery/form-validator/jquery.form-validator.min.js";
			$this->scriptCode[]					= $this->getFormValidatorScriptCode($formTable, !empty($formCon["valonblur"]), !empty($formCon["ajaxify"]));
		}
		elseif(!empty($formCon["ajaxify"])) {
			$this->scriptCode[]					= $this->getAjaxFormScriptCode($formTable);			
		}

		
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
			&& !file_exists(PROJECT_DOC_ROOT."/inc/classes/Forms/config.autoForm.".$formCon["formtab"].".php")
			)
				return $this->getContentElementWrapper($this->conAttributes, '<p class="notice error">{s_error:noformconf}</p>'); // kein db-Tabellenname
		
			$formTitle = count($queryFormFields) > 0 ? $queryFormTable[0]["title_".$this->lang] : ''; // Formulartitel
		}
			
		if(!isset($formCon["mailsource"]) || $formCon["mailsource"] == "") // recipients/Quelle der Haupt-E-Mail
			$formCon["mailsource"] = array();
		else
			$formCon["mailsource"] = explode(",", $formCon["mailsource"]);
		if(!isset($formCon["mailowner"]) || $formCon["mailowner"] == "") // E-Mail nur an Besitzer
			$formCon["mailowner"] = 0;
		elseif($formCon["mailowner"] == 1)
			$formCon["mailform"] = 1; // Formmailer setzen
		if(!isset($formCon["ownermail"]) || $formCon["ownermail"] == "") // recipients (nur Besitzer)
			$formCon["ownermail"] = array();
		else
			$formCon["mailsource"] = array_merge($formCon["mailsource"], explode(",", $formCon["ownermail"])); // E-Mails über ["mailsource"] mitgeben
		if(!isset($formCon["cc"]) || $formCon["cc"] == "") // recipientsCC
			$formCon["cc"] = array();
		else
			$formCon["cc"] = explode(",", $formCon["cc"]);
		if(!isset($formCon["bcc"]) || $formCon["bcc"] == "") // recipientsBCC
			$formCon["bcc"] = array();
		else
			$formCon["bcc"] = explode(",", $formCon["bcc"]);
		if(!isset($formCon["subj"]) || $formCon["subj"] == "") // subject
			$formCon["subj"] = "none";
		if(isset($formCon["pdf"]) && $formCon["pdf"] == 1) // pdf generieren
			$formCon["pdf"] = true;
		else
			$formCon["pdf"] = false;
		if(!isset($formCon["pdffolder"])) // Ordner für pdf Dateien
			$formCon["pdffolder"] = "";
		if(empty($formCon["browserpdf"])) // pdf an Browser senden
			$formCon["browserpdf"] = false;
		else
			$formCon["browserpdf"] = true;
		if(empty($formCon["mailpdf"])) // pdf an E-Mail anhängen
			$formCon["mailpdf"] = false;
		else
			$formCon["mailpdf"] = true;
		if(empty($formCon["userpdf"])) // pdf ist benutzerspezifisch
			$formCon["userpdf"] = false;
		else
			$formCon["userpdf"] = true;
		if(empty($formCon["nodb"])) // keine Speicherung in DB
			$formCon["nodb"] = false;
		else
			$formCon["nodb"] = true;
		
		#var_dump($queryFormFields);	

		
		// Zunächst das entsprechende Modul einbinden (FormGenerator-Klasse)
		require_once PROJECT_DOC_ROOT."/inc/classes/Forms/class.FormGenerator.php";
		
									
		if(!isset($formCon["mailform"]) || $formCon["mailform"] == "" || $formCon["mailform"] == 0) // Formmailer
			$formCon["mailform"] = false;
		else { // Formular per E-Mail
		
			$formCon["mailform"] = true;
			
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
			require(PROJECT_DOC_ROOT."/inc/classes/Forms/config.autoForm.".$formCon["formtab"].".php");

		// Form parameter
		$o_myForm->formTitle		= ($formTitle != "" ? $formTitle : str_replace("form_", "", $formTable)); //Titel der Tabelle speichern
		$o_myForm->formMailer		= $formCon["mailform"]; //FormMailer
		$o_myForm->recipients		= $formCon["mailsource"]; //E-Mailadresse bzw. Art der Bestimmung des E-Mailempfängers
		$o_myForm->recipientsCC		= $formCon["cc"]; //E-Mailadresse(n) Kopie-Empfänger
		$o_myForm->recipientsBCC	= $formCon["bcc"]; //E-Mailadresse(n) Blindkopie-Empfänger
		$o_myForm->mailSubject		= $formCon["subj"]; //E-Mail-Betreff
		$o_myForm->makePDF			= $formCon["pdf"]; //PDF erstellen
		$o_myForm->pdfFolder		= $formCon["pdffolder"]; //PDF Ordner
		$o_myForm->browserPDF		= $formCon["browserpdf"]; //PDF an Browser senden
		$o_myForm->mailPDF			= $formCon["mailpdf"]; //PDF per E-Mail senden
		$o_myForm->userPDF			= $formCon["userpdf"]; //PDF in Ordner _user speichern
		$o_myForm->noDbStorage		= $formCon["nodb"]; //Formulardaten nicht in DB speichern

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

		return $output;
	
	}	
	

	// getFormValidatorScriptCode
	public function getFormValidatorScriptCode($formTable, $validateOnBlur = false, $ajaxify = false)
	{

		$formID		= str_replace("_", "-", $formTable);

		$output	=	'head.ready("jquery", function(){' . PHP_EOL .
					'head.load({formvalidator: "' . PROJECT_HTTP_ROOT . '/extLibs/jquery/form-validator/jquery.form-validator.min.js"});' . PHP_EOL .
					'head.ready("formvalidator", function(){' . PHP_EOL .
						'$(document).ready(function(){' . PHP_EOL .
							'$.validate({
								form : "#' . $formID . ':not(.cc-form-multistep)",
								lang : "' . $this->lang . '",
								validateOnBlur : ' . ($validateOnBlur ? 'true' : 'false') . ',
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
								},
								onSuccess : function($form) {' . PHP_EOL;
									
		if($ajaxify) {
			$output	.=	$this->getAjaxFormScriptCode($formID, "true") .
						'return false;' . PHP_EOL;
		}
		else {
			$output	.=	'$form.find(\'button[type="submit"]\').not(".disabled").addClass("disabled").append(\'&nbsp;&nbsp;<span class="icons icon-refresh icon-spin"></span>\');' . PHP_EOL;
		}
		
		$output	.= 				'}
							});' . PHP_EOL .
						'});' . PHP_EOL .
					'});' . PHP_EOL .
					'});' . PHP_EOL;
	
		return $output;
	
	}
	

	// getAjaxFormScriptCode
	public function getAjaxFormScriptCode($formTable, $validation = "false")
	{

		$formID		= str_replace("_", "-", $formTable);

		$output	=	'$(document).ready(function(){
						head.ready("ccInitScript", function(){
							head.load({ajaxifyform: "' . PROJECT_HTTP_ROOT . '/access/js/ajaxifyForm.js"}, function(){		
								cc.ajaxifyForm(\'form[id="' . $formID . '"]\', "' . $validation . '", "' . ContentsEngine::replaceStyleDefs("{t_class:alert} {t_class:error}") . '");
							});		
						});
					});' . PHP_EOL;
	
		return $output;
	
	}
	
}
