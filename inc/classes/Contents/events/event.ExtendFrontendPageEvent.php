<?php
namespace Concise\Events;

use Symfony\Component\EventDispatcher\Event;


/**
 * Klasse ExtendFrontendPageEvent
 *
 * Erweitert FE content page
 * 
 * 
 */

class ExtendFrontendPageEvent extends event
{

	public $DB				= null;
	public $o_lng			= null;
	public $o_element		= null;
	public $cssFiles		= array();
	public $scriptFiles		= array();
	public $scriptCode		= array();
	public $styles			= array();
	public $elementID		= "";
	public $html5			= false;
	protected $output		= "";
	
	public function __construct($DB, $o_lng)
    {
	
		$this->DB			= $DB;
		$this->o_lng		= $o_lng;
		
    }
 
	public function __get($name)
    {
	
		if(!isset($name))
			return ""; // Überladung, falls neue Vars bei Aufruf durch Plugin
	
	}

    public function getOutput($reset = false)
    {

		$output		= $this->output;
		
		if($reset)
			$this->output = "";
		
		return $output;
	
	}
 
    public function setOutput($out)
    {

		return $this->output	= $out;
	
	}
 
    public function addOutput($out)
    {

		return $this->output	.= $out;
	
	}
 
    public function getResult()
    {

		return $this->result;
	
	}
 
    public function setResult($res)
    {

		return $this->result	= $res;
	
	}

}
