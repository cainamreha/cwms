<?php
namespace Concise\Events\Admincampaigns;

use Symfony\Component\EventDispatcher\Event;
use Concise\ContentsEngine;
use Concise\Admin_Campaigns;



##############################
###  EventListener-Klasse  ###
##############################

// GlobalCoreEventsListener

class GlobalCoreEventsListener extends Admin_Campaigns
{	
	
	public function __construct()
	{
		
	}
	
	// onRegisterHeadFiles
	public function onRegisterHeadFiles(Event $event)
	{
		
	}
	
	// onGetRightbarContents
	public function onGetRightbarContents(Event $event)
	{
	
		$output = "";		
		
		// newsl buttons in rightbar
		
		if(!empty($GLOBALS['_GET']['add_new'])
		|| !empty($GLOBALS['_POST']['add_new'])
		|| !empty($GLOBALS['_SESSION']['newsl_id'])
		) {
		
			$output		.=	'<div class="controlBar">' . PHP_EOL;
			
			// list newsl button
			$btnDefs		= array(	"href"		=> ADMIN_HTTP_ROOT . "?task=campaigns&type=newsl&list_newsl=1",
										"class"	 	=> "{t_class:btnblock}",
										"text"		=> "{s_header:listnewsl}",
										"title"		=> "{s_button:newsllist}",
										"icon"		=> "list"
									);
			
			$output		.=	$this->getButtonLink($btnDefs);
			
			$output		.=	'</div>' . PHP_EOL;		
		}
		
		if(empty($GLOBALS['_GET']['add_new'])
		&& empty($GLOBALS['_POST']['add_new'])
		) {
		
			$output		.=	'<div class="controlBar">' . PHP_EOL;
		
			// new newsl button
			$btnDefs		= array(	"href"		=> ADMIN_HTTP_ROOT . "?task=campaigns&type=newsl&add_new=1",
										"class"	 	=> "{t_class:btnblock}",
										"text"		=> "{s_header:newnewsl}",
										"title"		=> "{s_button:addnewsl}",
										"icon"		=> "new"
									);
		
			
			$output		.=	$this->getButtonLink($btnDefs);
			
			$output		.=	'</div>' . PHP_EOL;		
		}
		
		// add output
		$event->addOutput($output);
		
		return $output;

	}

} // Ende class
