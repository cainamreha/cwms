<?php
namespace Concise;



/**
 * ScriptElement
 * 
 */

class ScriptElement extends ElementFactory implements Elements
{
	
	/**
	 * Gibt ein ScriptElement zurück
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
		#######  Script-Code   #######
		##############################
		
		$this->params	= (array)json_decode($this->conValue);
		$script			= "";
		
		if(isset($this->params["code"]))			
			$script = trim($this->params["code"]);

		
		if(!empty($this->params["pos"])) {
			$this->scriptCode[$this->conNum] = $script;
			return "";
		}
		
		if(strpos($script, '<script') === null
		|| strpos($script, '<script') === false
		)
			$script	= '<script' . ($this->html5 ? '' : ' type="text/javascript"') . '>' . $script . '</script>';

		return $script;
	
	}	
	
}
