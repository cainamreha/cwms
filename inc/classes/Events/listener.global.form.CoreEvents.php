<?php
namespace Concise\Events;

use Symfony\Component\EventDispatcher\Event;
use Concise\Files;



##############################
###  EventListener-Klasse  ###
##############################

// FormCoreEventsListener

class FormCoreEventsListener
{

	// onMakePdf
	public function onEvent(Event $event)
	{
	
		return true;
	
	}
	
} // Ende class
