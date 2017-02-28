<?php
namespace Concise\Events;

use Symfony\Component\EventDispatcher\Event;


/**
 * Klasse ExtendGalleryEvent
 *
 * Erweitert Gallery-Modul
 * 
 * 
 */

class ExtendGalleryEvent extends Event
{

	public $DB				= null;
	public $o_lng			= null;
	public $editLang		= "";
	public $g_Session		= array();
	public $a_Post			= array();
	public $hint			= "";
	public $wrongInput		= array();
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
