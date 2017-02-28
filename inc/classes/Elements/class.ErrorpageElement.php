<?php
namespace Concise;



/**
 * ErrorpageElement
 * 
 */

class ErrorpageElement extends ElementFactory implements Elements
{
	
	/**
	 * Gibt ein ErrorpageElement zurück
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
		########  Errorpage  #########
		##############################
		
		require_once PROJECT_DOC_ROOT . "/inc/classes/ErrorPage/class.ErrorPage.php";
		
		$this->params	= json_decode($this->conValue, true);

		// Neues ErrorPageobjekt
		$o_ErrorPage	= new ErrorPage($this->statusCode);
		
		if(!empty($this->params["er"]))		$o_ErrorPage->errorMes			= $this->params["er"];
		if(!empty($this->params["nf"]))		$o_ErrorPage->errorNotfound		= $this->params["nf"];
		if(!empty($this->params["fb"]))		$o_ErrorPage->errorForbidden	= $this->params["fb"];
		if(!empty($this->params["sv"]))	 	$o_ErrorPage->errorServer		= $this->params["sv"];
		if(!empty($this->params["st"])) 	$o_ErrorPage->errorStatus		= $this->params["st"];
		if(!empty($this->params["ac"]))		$o_ErrorPage->errorAccess		= $this->params["ac"];
		if(!empty($this->params["nl"]))		$o_ErrorPage->errorNoLogin		= $this->params["nl"];
		if(!empty($this->params["to"]))		$o_ErrorPage->errorTimeout		= $this->params["to"];
		if(!empty($this->params["nn"]))		$o_ErrorPage->errorNoFeed		= $this->params["nn"];
		
		$output			= $o_ErrorPage->getErrorPage($this->group);
		
		// Ggf. Attribute (Styles) Wrapper-div hinzufügen
		if($this->conAttributes['id'] != "" || $this->conAttributes['class'] != "" || $this->conAttributes['style'] != "")
			$output	= $this->getContentElementWrapper($this->conAttributes, $output);

		return $output;
	
	}	
	
}
