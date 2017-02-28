<?php
namespace Concise\Events;

use Symfony\Component\EventDispatcher\Event;


/**
 * Klasse GlobalAddHeadCodeEvent
 *
 * Erweitert Head code
 * 
 * 
 */

class GlobalAddHeadCodeEvent extends event
{

	public $DB				= null;
	public $metaTagsPre		= array();
	public $metaTagsPost	= array();
	public $scriptCodePre	= array();
	public $scriptCodePost	= array();
	protected $output		= "";
	
	public function __construct($DB)
    {
	
		$this->DB			= $DB;
		
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
