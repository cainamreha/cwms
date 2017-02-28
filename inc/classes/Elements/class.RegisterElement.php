<?php
namespace Concise;



/**
 * RegisterElement
 * 
 */

class RegisterElement extends ElementFactory implements Elements
{
	
	/**
	 * Gibt ein RegisterElement zurück
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
		#########  Register  #########
		##############################
		
		// Formvalidator
		$this->scriptFiles["formvalidator"]	= "extLibs/jquery/form-validator/jquery.form-validator.min.js";		
		
		// Registrierungsformular ausgeben
		$o_user				= new User($this->DB, $this->o_lng);
		$o_user->formAction	= parent::$currentURL;
		$output				= $o_user->getRegPage($this->conValue);
		
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
							form : "#regform",
							lang : "' . $this->lang . '",
							validateOnBlur : false,
							errorMessagePosition : $("#regform .formErrorBox"),
							scrollToTopOnError : false,
							borderColorOnError : "",
							onError : function($form) {
								$("#regform .formErrorBox").addClass("' . ContentsEngine::replaceStyleDefs("{t_class:alert} {t_class:error}") . '").hide().fadeIn(800);
							}
						});' . "\r\n" .
					'});' . "\r\n" .
				'});' . "\r\n" .
				'});' . "\r\n" .
				'</script>' . "\r\n";
	
	}
	
}
