<?php
namespace Concise\Events;

use Symfony\Component\EventDispatcher\Event;


/**
 * Klasse ExtendCampaignsEvent
 *
 * Erweitert Admin Campaigns
 * 
 * 
 */

class ExtendCampaignsEvent extends Event
{

	public $DB				= null;
	public $o_lng			= null;
	public $editorLog		= false;
	public $g_Session		= array();
	public $a_Post			= array();
	protected $output		= "";
	protected $result		= false;
	
	public function __construct($DB, $o_lng)
    {
	
		$this->DB		= $DB;
		$this->o_lng	= $o_lng;
		$this->editLang	= $this->o_lng->editLang;
		
    }
 
	public function __get($name)
    {
	
		if(!isset($name))
			return ""; // Überladung, falls neue Vars bei Aufruf durch Plugin
	
	}

    public function getStyles($reset = false)
    {

		$styles		= $this->styles;
		
		if($reset)
			$this->styles = "";
		
		return $styles;
	
	}
 
    public function setStyles($styles)
    {

		return $this->styles	= $styles;
	
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
