<?php
namespace Concise\Events\Planner;

use Symfony\Component\EventDispatcher\Event;
use Concise\ContentsEngine;
use Concise\Modules;



##############################
###  EventListener-Klasse  ###
##############################

// DetailCoreEventsListener

class DetailCoreEventsListener
{	
	
	// onGetDataDetails
	public function onGetDataDetails(Event $event)
	{
	
		if($event->modType != "planner")
			return false;
		
	
		// Event vars
		$event->plannerPast			= "";
		$event->dataTime		= htmlspecialchars($event->queryData[0]['time']);
		
		if($event->modType == "planner" && $event->pastDate < Modules::getCurrentDate()) {
			$event->classExt	= ' dataPast';
			$event->plannerPast	= '{s_text:past}';
		}	

		return true;
	
	}
	
	// onAssignDataDetails
	public function onAssignDataDetails(Event $event)
	{	
	
		if($event->modType != "planner")
			return false;
		
		
		// Platzhalterersetzungen
		$event->tpl_data->assign("plannerPast", $event->plannerPast);

		return true;
	
	}

} // Ende class
