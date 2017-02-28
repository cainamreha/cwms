<?php
namespace Concise\Events\Modules;

use Symfony\Component\EventDispatcher\Event;


/**
 * Klasse MakeMenuEvent
 *
 * Erweitert Menu-Modul
 * 
 * 
 */

class MakeMenuEvent extends Event
{

	public $framework			= "";
	public $html5				= false;
	public $menuFixed			= false;
	public $menuLogo			= false;
	public $langMenu			= false;
	public $langDiv				= "";
	public $indexPageUrl		= "";
	public $navbarClass			= "";
	public $hasChildClass		= "";
	public $dropdownTag			= "";
	public $dropdownClass		= "";
	public $dropdownOpen		= "";
	public $dropdownClose		= "";
	public $dropdownSub			= "";
	public $dropdownExt			= "";
	public $navClose			= "";
	protected $output			= "";
	
	public function __construct()
    {
		
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
