<?php
namespace Concise\Events\Admingallery;

use Symfony\Component\EventDispatcher\Event;
use Concise\ContentsEngine;



##############################
###  EventListener-Klasse  ###
##############################

// GlobalCoreEventsListener

class GlobalCoreEventsListener extends \Concise\Admin_ModulesGallery
{	

	// onRegisterHeadFiles
	public function __construct()
	{
	}

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
		
		// integrate filemanager button in rightbar
		$output		.=	'<div class="controlBar">' . PHP_EOL;
		
		// Filemanager MediaList-Button
		$btnDefs		= array(	"type"		=> "filemanager",
									"url"		=> SYSTEM_HTTP_ROOT . "/access/listMedia.php?page=admin&action=elfinder&root=gallery,images,files",
									"class"	 	=> "filemanager",
									"text"		=> "{s_label:filemanager}",
									"value"		=> "{s_label:filemanager}",
									"icon"		=> "filemanager"
								);
		
		
		$output		.=	$this->getButtonMediaList($btnDefs);
		
		$output		.=	parent::getIcon("gallery");
		
		$output		.=	'</div>' . PHP_EOL;		
		
		// add output
		$event->addOutput($output);
		
		return $output;
		
	}

} // Ende class
