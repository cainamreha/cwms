<?php
namespace Concise\Events\Admin;

use Symfony\Component\EventDispatcher\Event;
use Concise\ContentsEngine;



##############################
###  EventListener-Klasse  ###
##############################

// GlobalCoreEventsListener

class GlobalCoreEventsListener
{	

	// onRegisterHeadFiles
	public function onRegisterHeadFiles(Event $event)
	{
	
		$output	= "";
		return true;
	
	}

	// onGetRightbarContents
	public function onGetRightbarContents(Event $event)
	{
		
		$output = "";
		
		// if file & media, integrate gallery button in rightbar
		if($event->adminTask == "file") {
		
			$output		=	'<div class="controlBar">' . PHP_EOL;
		
			// Button gallery
			$btnDefs	= array(	"href"		=> ADMIN_HTTP_ROOT . '?task=modules&type=gallery&name=',
									"class"		=> "gallery {t_class:btnblock}",
									"text"		=> "{s_nav:admingallery} &raquo;",
									"title"		=> "{s_header:newgallery} &raquo;",
									"attr"		=> 'data-ajax="true"',
									"icon"		=> "gallery"
								);
		
			$output		.=	ContentsEngine::getButtonLink($btnDefs);
			
			$output		.=	'</div>' . PHP_EOL;

			$event->addOutput($output);
		}
		
		return $output;
		
	}

} // Ende class
