<?php
namespace Concise\Events\Adminnews;

use Symfony\Component\EventDispatcher\Event;
use Concise\Modules;



##############################
###  EventListener-Klasse  ###
##############################

// DataCoreEventsListener

class DataCoreEventsListener
{	
	
	// onGetDataAttributes
	public function onGetDataAttributes(Event $event)
	{

		// Datum
		$event->dataDateOld		= $event->planDate;
		$pdArr					= explode(" ", $event->planDate);
		$ptArr					= explode(":", end($pdArr));
		$event->dataDate		= implode(".", array_reverse(explode("-", reset($pdArr))));
		$event->planHour		= reset($ptArr);
		$event->planMin			= next($ptArr);
		$event->dataTime		= $event->planHour.":".$event->planMin;
	
		return true;
	
	}
	
	// onEvalDataPost
	public function onEvalDataPost(Event $event)
	{		
	
		// Datum
		$event->newsDateDB = $event->newsDateDB . " " . $event->dataTime;
			
		// Falls das Datum bei Artikeln oder News geändert wurde, neues Datum in DB eintragen und mit 00 für Sekunden auffüllen
		if($event->newsDateDB != substr($event->dataDateOld, 0, -3))
			$event->dbUpdateStr .= "`date` = '" . $event->DB->escapeString($event->newsDateDB . ":00") . "',";
		

		return true;
	
	}
	
	// onEvalNewdataPost
	public function onEvalNewdataPost(Event $event)
	{

		return true;
	
	}

} // Ende class
