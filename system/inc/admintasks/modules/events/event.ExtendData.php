<?php
namespace Concise\Events;

use Symfony\Component\EventDispatcher\Event;


/**
 * Klasse ExtendDataEvent
 *
 * Erweitert Daten-Module
 * 
 * 
 */

class ExtendDataEvent extends Event
{

	public $DB				= null;
	public $o_lng			= null;
	public $editLang		= "";
	public $editorLog		= false;
	public $modType			= "";
	public $g_Session		= array();
	public $a_Post			= array();
	public $dataComments	= "";
	public $dataRating		= "";
	public $targetPage		= "";
	public $dataFeed		= "";
	public $dataOrder		= "";
	public $useCatImage		= "";
	public $dataCatImage	= "";
	public $catData			= "";
	public $catImg			= "";
	public $editData		= "";
	public $editEntry		= array();
	public $dataEntry		= array();
	public $dataFeatured	= 0;
	public $dataDate		= "";
	public $dataDateDb		= "";
	public $timestampStart	= "";
	public $dataDateEnd		= "";
	public $dataDateForm	= "";
	public $dataDateOld		= "";
	public $dataTime		= "";
	public $dataTimeEnd		= "";
	public $planDate		= "";
	public $planDateEnd		= "";
	public $planHour		= "";
	public $planHourEnd		= "";
	public $planMin			= "";
	public $planMinEnd		= "";
	public $planSec			= "";
	public $orderOpt		= "";
	public $price			= "";
	public $showCalendar	= false;
	public $dbInsertStr1	= "";
	public $dbInsertStr2	= "";
	public $dbUpdateStr		= "";
	public $hint			= "";
	public $wrongInput		= array();
	protected $output		= "";
	protected $result		= false;
	
	public function __construct($DB, $o_lng, $type)
    {
	
		$this->DB		= $DB;
		$this->o_lng	= $o_lng;
		$this->editLang	= $this->o_lng->editLang;
		$this->modType	= $type;
		
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
