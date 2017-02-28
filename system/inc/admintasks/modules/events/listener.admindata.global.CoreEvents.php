<?php
namespace Concise\Events\Admindata;

use Symfony\Component\EventDispatcher\Event;
use Concise\ContentsEngine;



##############################
###  EventListener-Klasse  ###
##############################

// GlobalCoreEventsListener

class GlobalCoreEventsListener extends \Concise\Admin_ModulesData
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

		if(count($event->o_admin->existCats) == 0)
			return "";
		
		
		
		// New data entry
		if(!$event->o_admin->newData
		&& !$event->o_admin->showCalendar
		) {
		
			$this->formAction		= ADMIN_HTTP_ROOT . "?task=modules&type=" . parent::$type;
			
			// integrate add new button in rightbar
			$output		.=	'<div class="controlBar clearfix">' . PHP_EOL;				
			$output		.=	$this->getAddDataButton();
			$output		.=	'</div>' . PHP_EOL;		
		
		}
		
		
		// Data objects button
		if($event->o_admin->editData) {
		
			// Button goto objects
			$output		.=	'<div class="controlBar clearfix">' . PHP_EOL;				
			$btnDefs	= array(	"href"		=> '#sortableObjects',
									"class"		=> "objects {t_class:btnblock}",
									"text"		=> '{s_label:objects}',
									"title"		=> '{s_option:' . $event->adminType . '} - {s_label:objects}',
									"icon"		=> "attachment"
								);
			
			$output		.=	ContentsEngine::getButtonLink($btnDefs);
			$output		.=	'</div>' . PHP_EOL;		
		}
		
		
		// if file & media, integrate calendar button in rightbar
		if(($event->o_admin->listData
		&& ($event->o_admin->showCats || $event->o_admin->newData || $event->o_admin->editData))
		|| $event->o_admin->showCalendar
		) {

			$output		.=	'<div class="controlBar">' . PHP_EOL;
			
			// Button back to list
			$btnDefs	= array(	"href"		=> ADMIN_HTTP_ROOT . '?task=modules&type=' . $event->adminType. '&list_cat=all',
									"class"		=> "backtolist {t_class:btnblock}",
									"text"		=> '{s_option:' . $event->adminType. 'list} &raquo;',
									"title"		=> '{s_button:' . $event->adminType. 'list} &raquo;',
									"attr"		=> 'data-ajax="true"',
									"icon"		=> "list"
								);
		
			$output		.=	ContentsEngine::getButtonLink($btnDefs);
			
			$output		.=	'</div>' . PHP_EOL;		
		}
		
		
		// integrate calendar button in rightbar
		if(!$event->o_admin->showCalendar) {
		
			$output		.=	'<div class="controlBar">' . PHP_EOL;
		
			// Button calendar
			$btnDefs	= array(	"href"		=> ADMIN_HTTP_ROOT . '?task=modules&type=' . $event->adminType . '&calendar=1' . (!empty($event->o_admin->editEntry[0]['date']) ? '&date=' . urlencode($event->o_admin->editEntry[0]['date']) : ''),
									"class"		=> "showCalendar {t_class:btnblock}",
									"text"		=> '{s_link:calendar} &raquo;',
									"title"		=> '{s_option:calendar' . $event->adminType. '} &raquo;',
									"attr"		=> 'data-ajax="true"',
									"icon"		=> "calendar"
								);
			
			$output		.=	ContentsEngine::getButtonLink($btnDefs);
			
			$output		.=	'</div>' . PHP_EOL;		
		}
		else{
		
			// integrate calendar external events in rightbar			
			$output		.=	'<div class="controlBar">' . PHP_EOL;
			$output		.=	'<label>{s_header:list' . $event->adminType . '}</label>' . PHP_EOL;
			$output		.=	'<span class="inline-box">' . PHP_EOL;			
			$output		.=	'<label class="markAll markBox" data-mark="#external-events"><input type="checkbox" id="markAllEventCats" data-select="all" /></label>' . PHP_EOL;		
			$output		.=	'<label class="markAllLB inline-label" for="markAllEventCats">{s_label:mark}</label>' . PHP_EOL;		
			$output		.=	'</span>' . PHP_EOL;			
			$output		.=	'<div id="external-events">' . PHP_EOL;
			
			foreach($event->o_admin->existCats as $cat) {
				$output		.=	'<span class="inline-box">' . PHP_EOL;			
				$output		.=	'<label class="markBox"><input type="checkbox" id="fc_external_event_' . $cat['cat_id'] . '" class="toggleExternalEvents" name="fc_external_event_' . $cat['cat_id'] . '" data-eventcat="' . $cat['cat_id'] . '" checked="checked" /></label>' . PHP_EOL;			
				$output		.=	'<span>' . PHP_EOL;			
				$output		.=	'<div class="fc-external-event fc-event" data-event=\'{"id":"fc-ne-' . $cat['cat_id'] . '","catid":"' . $cat['cat_id'] . '","cat":"' . $cat['category_' . $event->lang] . '"}\'>' . $cat['category_' . $event->lang] . '</div>' . PHP_EOL;			
				$output		.=	'</span>' . PHP_EOL;			
				$output		.=	'</span>' . PHP_EOL;			
			}
			$output		.=	'</div>' . PHP_EOL;		
			$output		.=	'</div>' . PHP_EOL;		
			
			$output		.=	'<script>' . PHP_EOL;		
			$output		.=	'head.ready(function(){
								$("document").ready(function(){
									$("#external-events .fc-external-event").draggable({
										revert: true,      // immediately snap back to original position
										revertDuration: 0,  //
										zIndex: 9000,
										create: function(event, ui){
											var eventData = $.parseJSON($(this).attr("data-event"));
											$(this).css({"color": cc.eventColors[eventData.catid], "background-color": cc.eventBGColors[eventData.catid]});
										}
									});
									$("body").on("click", ".toggleExternalEvents", function() {									
										var catid = $(this).attr("data-eventcat");
										if($(this).is(":checked")){
											$("#calendar .fc-event-cat-" + catid).removeClass("hide");
										}else{
											$("#calendar .fc-event-cat-" + catid).addClass("hide");
										}
									});
									$("body").on("click", "#markAllEventCats", function() {									
										$(".toggleExternalEvents").each(function() {									
											var catid = $(this).attr("data-eventcat")
											if($(this).is(":checked")){
												$("#calendar .fc-event-cat-" + catid).removeClass("hide");
											}else{
												$("#calendar .fc-event-cat-" + catid).addClass("hide");
											}
										});
									});
								});	
							});' . PHP_EOL;		
			$output		.=	'</script>' . PHP_EOL;		
		}
		
		// add output
		$event->addOutput($output);
		
		return $output;
		
	}

} // Ende class
