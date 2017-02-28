<?php
namespace Concise;



/**
 * RegpageElement
 * 
 */

class RegpageElement extends ElementFactory implements Elements
{
	
	/**
	 * Gibt ein RegpageElement zurück
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
		#########  Regpage  ##########
		##############################
		
		if(REGISTRATION_TYPE == "none")
			$this->gotoErrorPage();
		
		$this->params	= json_decode($this->conValue, true);
		
		// Formvalidator
		$this->scriptFiles["formvalidator"]	= "extLibs/jquery/form-validator/jquery.form-validator.min.js";		
		$this->scriptCode[]					= $this->getRegScriptCode();
		
		// Neues Reg-Objekt
		$o_user		= new User($this->DB, $this->o_lng);
		
		if(!empty($this->params["regsubject"]))	$o_user->regtextSubject		= $this->params["regsubject"];
		if(!empty($this->params["regthank"]))	$o_user->regtextThank		= $this->params["regthank"];
		if(!empty($this->params["regmessage"])) $o_user->regtextMessage		= $this->params["regmessage"];
		if(!empty($this->params["regtextoptin"])) $o_user->regtextOptIn		= $this->params["regtextoptin"];
		if(!empty($this->params["regtextnewsl"])) $o_user->regtextNewsl		= $this->params["regtextnewsl"];
		if(!empty($this->params["regtextguest"])) $o_user->regtextGuest		= $this->params["regtextguest"];
		if(!empty($this->params["regtextshop"])) $o_user->regtextShop		= $this->params["regtextshop"];
		if(!empty($this->params["reguser"])) $o_user->regtextUser			= $this->params["reguser"];
		if(!empty($this->params["regnewsl"])) $o_user->regtextRegNewsl		= $this->params["regnewsl"];
		if(!empty($this->params["unregnewsl"])) $o_user->regtextUnregNewsl	= $this->params["unregnewsl"];
		
		$output		= $o_user->getRegPage(REGISTRATION_TYPE);
		
		// Ggf. Attribute (Styles) Wrapper-div hinzufügen
		if($this->conAttributes['id'] != "" || $this->conAttributes['class'] != "" || $this->conAttributes['style'] != "")
			$output	= $this->getContentElementWrapper($this->conAttributes, $output);

		return $output;
	
	}	
	

	// getRegScriptCode
	public function getRegScriptCode()
	{

		return	'head.ready("jquery", function(){' . PHP_EOL .
				'head.load({formvalidator: "' . PROJECT_HTTP_ROOT . '/extLibs/jquery/form-validator/jquery.form-validator.min.js"});' . PHP_EOL .
				'head.ready("formvalidator", function(){' . PHP_EOL .
					'$(document).ready(function(){' . PHP_EOL .
						'$.validate({
							modules : "security",
							form : "#regForm",
							lang : "' . $this->lang . '",
							validateOnBlur : false,
							borderColorOnError : "",
							scrollToTopOnError : false,
							onSuccess : function($form) {
								$form.find(\'button[type="submit"]\').not(".disabled").not( $(\'button[type="submit"]:focus\').siblings(\'button\')).addClass("disabled").append(\'&nbsp;&nbsp;<span class="icons icon-refresh icon-spin"></span>\');
							}
						});' . PHP_EOL .
					'});' . PHP_EOL .
				'});' . PHP_EOL .
				'});' . PHP_EOL;
	
	}
	
}
