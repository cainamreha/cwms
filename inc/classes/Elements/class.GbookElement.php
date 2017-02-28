<?php
namespace Concise;



/**
 * GbookElement
 * 
 */

class GbookElement extends ElementFactory implements Elements
{
	
	/**
	 * Gibt ein GbookElement zurück
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
		########  Gästebuch  #########
		##############################
		
		// Formvalidator
		$this->scriptFiles["formvalidator"]	= "extLibs/jquery/form-validator/jquery.form-validator.min.js";
		$this->scriptCode[]					= $this->getGbScriptCode();
		
		// Zunächst das entsprechende Modul einbinden (Search-Klasse)
		require_once PROJECT_DOC_ROOT."/inc/classes/Modules/class.Guestbook.php";
		
		// Gästebuch-Instanz
		$o_gBook	= new Guestbook($this->DB, $this->o_lng, $this->group, parent::$currentURLPath); // Formular generieren
		$output		= $o_gBook->getGuestbook($this->group); // Gästebuch generieren

		$this->mergeHeadCodeArrays($o_gBook);
		
		// Ggf. Attribute (Styles) Wrapper-div hinzufügen
		$output	= $this->getContentElementWrapper($this->conAttributes, $output, 'cc-gbook cc-module');
		
		return $output;
	
	}	
	

	// getGbScriptCode
	public function getGbScriptCode()
	{

		return	'head.ready("jquery", function(){' . PHP_EOL .
				'head.load({formvalidator: "' . PROJECT_HTTP_ROOT . '/extLibs/jquery/form-validator/jquery.form-validator.min.js"});' . PHP_EOL .
				'head.ready("formvalidator", function(){' . PHP_EOL .
					'$(document).ready(function(){' . PHP_EOL .
						'$.validate({
							form : "#gbfm",
							lang : "' . $this->lang . '",
							validateOnBlur : false,
							borderColorOnError : "",
							scrollToTopOnError : false,
							onSuccess : function($form) {
								$form.find(\'button[type="submit"]\').not(".disabled").addClass("disabled").append(\'&nbsp;&nbsp;<span class="icons icon-refresh icon-spin"></span>\');
							}
						});' . PHP_EOL .
					'});' . PHP_EOL .
				'});' . PHP_EOL .
				'});' . PHP_EOL;
	
	}
	
}
