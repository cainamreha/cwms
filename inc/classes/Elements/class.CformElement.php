<?php
namespace Concise;



/**
 * CformElement
 * 
 */

class CformElement extends ElementFactory implements Elements
{
	
	/**
	 * Gibt ein CformElement zurück
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
		#####  Kontaktformular  ######
		##############################

		$this->scriptFiles["formvalidator"]	= "extLibs/jquery/form-validator/jquery.form-validator.min.js";

		
		// Formularkonfiguration
		$formFields = (array)json_decode($this->conValue);
		
		if(count($formFields) == 0)
			$formFields = array("form" => "block");
		
		// Zunächst das entsprechende Modul einbinden (Contactfm-Klasse)
		require_once PROJECT_DOC_ROOT."/inc/classes/Modules/class.Contactfm.php";
	
		$cForm	= new Contactfm($formFields, Security::getSessionVars()); // Formular generieren
		$output = $cForm->getContactForm(); // Formular generieren	
		
		// Form validator script
		$output	.= $this->getScriptTag();

		// Ggf. Attribute (Styles) Wrapper-div hinzufügen
		if($this->conAttributes['id'] != "" || $this->conAttributes['class'] != "" || $this->conAttributes['style'] != "")
			$output	= $this->getContentElementWrapper($this->conAttributes, $output);
		
		return $output;
	
	}	
	

	// getScriptTag
	public function getScriptTag()
	{

		return	'<script>' . "\r\n" .
				'head.ready("jquery", function(){' . "\r\n" .
				'head.load({formvalidator: "' . PROJECT_HTTP_ROOT . '/extLibs/jquery/form-validator/jquery.form-validator.min.js"});' . "\r\n" .
				'head.ready("formvalidator", function(){' . "\r\n" .
					'$(document).ready(function(){' . "\r\n" .
						'$.validate({
							form : "#contactfm",
							lang : "' . $this->lang . '",
							validateOnBlur : false,
							//errorMessagePosition : "top",
							errorMessagePosition : $("#contactfm .formErrorBox"),
							scrollToTopOnError : false,
							borderColorOnError : "",
							onError : function($form) {
								if($form.closest(".form-minimal").length){
									$($form).find(".formErrorBox").addClass("' . ContentsEngine::replaceStyleDefs("{t_class:alert} {t_class:error}") . '").hide();
								}else{
									$($form).find(".formErrorBox").addClass("' . ContentsEngine::replaceStyleDefs("{t_class:alert} {t_class:error}") . '").hide().fadeIn(800);
								}
							}
						});' . "\r\n" .
					'});' . "\r\n" .
				'});' . "\r\n" .
				'});' . "\r\n" .
				'</script>' . "\r\n";
	
	}
	
}
