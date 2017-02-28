<?php
namespace Concise\Events\Modules;

use Symfony\Component\EventDispatcher\Event;


/**
 * Klasse OutputExtendDataEvent
 *
 * Erweitert Daten-Module
 * 
 * 
 */

class OutputExtendDataEvent extends Event
{

	public $DB				= null;
	public $o_lng			= null;
	public $lang			= "";
	public $tpl_data		= null;
	public $modType			= "";
	public $g_Session		= array();
	public $a_Post			= array();
	public $dataComments	= "";
	public $dataRating		= "";
	public $dataFeed		= "";
	public $dataOrder		= "";
	public $useCatImage		= "";
	public $dataCatImage	= "";
	public $catData			= "";
	public $catImg			= "";
	public $editData		= "";
	public $queryData		= array();
	public $dataEntry		= array();
	public $scriptFiles		= array();
	public $cssFiles		= array();
	public $scriptCode		= array();
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
	public $objectNumber	= 0;
	public $dbInsertStr1	= "";
	public $dbInsertStr2	= "";
	public $dbUpdateStr		= "";
	public $tokenInput		= "";
	public $hint			= "";
	public $wrongInput		= array();
	protected $output		= "";
	protected $result		= false;
	
	public function __construct($DB, $o_lng, $tpl_data, $modType)
    {
	
		$this->DB		= $DB;
		$this->o_lng	= $o_lng;
		$this->lang		= $this->o_lng->lang;
		$this->tpl_data	= $tpl_data;
		$this->modType	= $modType;
		
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
