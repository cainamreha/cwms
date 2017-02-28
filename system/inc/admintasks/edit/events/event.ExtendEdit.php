<?php
namespace Concise\Events;

use Symfony\Component\EventDispatcher\Event;


/**
 * Klasse ExtendEditEvent
 *
 * Erweitert Admin Edit
 * 
 * 
 */

class ExtendEditEvent extends Event
{

	public $DB				= null;
	public $o_lng			= null;
	public $editLang		= "";
	public $editTask		= "";
	public $editorLog		= false;
	public $g_Session		= array();
	public $a_Post			= array();
	public $options			= array();
	public $params			= array();
	public $conPrefix		= "";
	public $styles			= array();
	public $stylesPost		= array();
	public $elementConfigArr= array();
	public $showFieldset	= "";
	public $eleCnt			= "";
	public $dbInsertStr1	= "";
	public $dbInsertStr2	= "";
	public $dbUpdateStr		= "";
	public $wrongInput		= array();
	protected $output		= "";
	protected $result		= false;
	
	public function __construct($DB, $o_lng, $editTask)
    {
	
		$this->DB		= $DB;
		$this->o_lng	= $o_lng;
		$this->editLang	= $this->o_lng->editLang;
		$this->editTask	= $editTask;
		
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
