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
		
		// Neues Reg-Objekt
		$o_user		= new User($this->DB, $this->o_lng);
		
		if(!empty($this->params["regsubject"]))	$o_user->regtextSubject		= $this->params["regsubject"];
		if(!empty($this->params["regthank"]))	$o_user->regtextThank		= $this->params["regthank"];
		if(!empty($this->params["regmessage"])) $o_user->regtextMessage		= $this->params["regmessage"];
		if(!empty($this->params["regtextnewsl"])) $o_user->regtextNewsl		= $this->params["regtextnewsl"];
		if(!empty($this->params["regtextguest"])) $o_user->regtextGuest		= $this->params["regtextguest"];
		if(!empty($this->params["regtextshop"])) $o_user->regtextShop		= $this->params["regtextshop"];
		if(!empty($this->params["reguser"])) $o_user->regtextUser			= $this->params["reguser"];
		if(!empty($this->params["regnewsl"])) $o_user->regtextRegNewsl		= $this->params["regnewsl"];
		if(!empty($this->params["unregnewsl"])) $o_user->regtextUnregNewsl	= $this->params["unregnewsl"];
		
		$output		= $o_user->getRegPage(REGISTRATION_TYPE);
		
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
							modules : "security",
							form : "#regForm",
							lang : "' . $this->lang . '",
							validateOnBlur : false,
							borderColorOnError : "",
							scrollToTopOnError : false
						});' . "\r\n" .
					'});' . "\r\n" .
				'});' . "\r\n" .
				'});' . "\r\n" .
				'</script>' . "\r\n";
	
	}
	
}
