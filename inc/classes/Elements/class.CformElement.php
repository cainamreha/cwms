<?php
namespace Concise;



/**
 * CformElement
 * 
 */

class CformElement extends ElementFactory implements Elements
{
	
	private $cFormID	= "contactfm";
	
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
		
		// Formularkonfiguration
		$formFields = (array)json_decode($this->conValue);
		
		if(count($formFields) == 0)
			$formFields = array("form" => "block");

		
		if(!empty($formFields["validator"])) {
			$this->scriptFiles["validator"]	= "extLibs/jquery/form-validator/jquery.form-validator.min.js";
			$this->scriptCode[]				= $this->getCfFormValidatorScriptCode(!empty($formFields["valonblur"]), !empty($formFields["ajaxify"]));
		}
		elseif(!empty($formFields["ajaxify"])) {
			$this->scriptCode[]				= $this->getAjaxCfFormScriptCode($this->cFormID);			
		}

		
		// Zunächst das entsprechende Modul einbinden (Contactfm-Klasse)
		require_once PROJECT_DOC_ROOT."/inc/classes/Modules/class.Contactfm.php";
		
		$cForm	= new Contactfm($formFields, Security::getSessionVars()); // Formular generieren
		$output = $cForm->getContactForm(); // Formular generieren	

		// Ggf. Attribute (Styles) Wrapper-div hinzufügen
		if($this->conAttributes['id'] != "" || $this->conAttributes['class'] != "" || $this->conAttributes['style'] != "")
			$output	= $this->getContentElementWrapper($this->conAttributes, $output);
		
		return $output;
	
	}	
	

	// getCfFormValidatorScriptCode
	public function getCfFormValidatorScriptCode($validateOnBlur, $ajaxify = false)
	{

		$output	=	'head.ready("jquery", function(){' . PHP_EOL .
					'head.load({formvalidator: "' . PROJECT_HTTP_ROOT . '/extLibs/jquery/form-validator/jquery.form-validator.min.js"});' . PHP_EOL .
					'head.ready("formvalidator", function(){' . PHP_EOL .
						'$(document).ready(function(){' . PHP_EOL .
							'$.validate({
								form : "#' . $this->cFormID . '",
								lang : "' . $this->lang . '",
								validateOnBlur : ' . ($validateOnBlur ? 'true' : 'false') . ',
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
								},
								onSuccess : function($form) {' . PHP_EOL;
									
		if($ajaxify) {
			$output	.=	$this->getAjaxCfFormScriptCode($this->cFormID, "true") .
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
	

	// getAjaxCfFormScriptCode
	public function getAjaxCfFormScriptCode($formID, $validation = "false")
	{

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
