<?php
namespace Concise\Events\Admindata;

use Symfony\Component\EventDispatcher\Event;
use Concise\ContentsEngine;
use Concise\Modules;
use Concise\Admin_ModulesData;



##############################
###  EventListener-Klasse  ###
##############################

// DataCoreEventsListener

class DataCoreEventsListener extends Admin_ModulesData
{	
	
	public function __construct()
	{
	
	}
	
	// onGetDataFieldsPre
	public function onGetDataFieldsPre(Event $event)
	{
	
		$output	= "";
		
		// Startdatum
		$output		 .=	'<li>' . "\r\n" .
						'<label>{s_label:datestart}</label>' . "\r\n";
								
		if(isset($event->wrongInput['date_start']))
			$output		 .=	'<p class="notice">' . $event->wrongInput['date_start'] . '</p>' . "\r\n";
								
		$output		 .=	'<input type="hidden" id="daynames" value="{s_date:daynames}" alt="{s_date:daynamesmin}" />' . "\r\n" .
						'<input type="hidden" id="monthnames" value="{s_date:monthnames}" alt="{s_date:monthnamesmin}" />' . "\r\n" .
						'<input type="hidden" id="minInterval" value="' . ($event->modType == "planner" ? 15 : 1) . '" />' . "\r\n" .
						'<input type="text" name="news_date" class="datepicker dataDate-input" value="' . (isset($event->dataDate) ? htmlspecialchars(Modules::getLocalDateString($event->dataDate, $event->o_lng->adminLang)) : '') . '" maxlength="10" />' . "\r\n" . 
						'<input type="hidden" name="news_date" class="altField" value="' . (isset($event->dataDate) ? htmlspecialchars($event->dataDate) : '') . '" maxlength="10" />' . "\r\n" . 
						'<input type="hidden" name="timePost" value="' . $event->planHour . '" data-min="' . $event->planMin . '" />' . "\r\n" .
						'<div class="timepicker start"><label>{s_label:time}</label></div>' . "\r\n";

		return $event->addOutput($output);

	}
	
	// onGetDataFieldsMid
	public function onGetDataFieldsMid(Event $event)
	{
		
		$output	= "";
		
		$output		 .=	'<br class="clearfloat" />' . "\r\n" .
						'</li>' . "\r\n";
		
		
		// if short calendar view
		if($event->showCalendar)
			return $event->addOutput($output);

		
		// Teaser
		$output		 .= '<li>' . "\r\n" .
						'<label>Teaser<span class="editLangFlag">' . $event->editLangFlag . '</span><span class="toggleEditor" data-target="teaser">Editor</span></label>' . "\r\n" .
						'<textarea name="news_teaser" id="teaser" class="teaser cc-editor-add disableEditor" rows="2">' . htmlspecialchars($event->dataTeaser) . '</textarea>' . "\r\n";
		
		$output		 .=	'<br class="clearfloat" />' . "\r\n" .
						'</li>' . "\r\n";
		
		
		// Text
		$output		 .= '<li>' . "\r\n" .
						'<label>{s_label:'.$event->modType.'text}<span class="editLangFlag">' . $event->editLangFlag . '</span></label>' . "\r\n";
							
		if(isset($event->wrongInput['text']))
			$output		 .=	'<p class="notice">' . $event->wrongInput['text'] . '</p>' . "\r\n";
						
		$output		 .=	'<textarea name="news_text" id="text" class="textEditor cc-editor-add" rows="5">' . htmlspecialchars($event->dataText) . '</textarea>' . "\r\n";
		
		$output		 .=	'<br class="clearfloat" />' . "\r\n" .
						'</li>' . "\r\n";
		
		
		// Falls mehrere Sprachen angelegt sind, Sprachübernahmecheckbox hinzufügen
		if(count($event->o_lng->installedLangs) > 1)
			$output		 .=	'<li class="changeAllLangs">' . "\r\n" .
							'<label class="markBox"><input type="checkbox" name="all_langs" id="all_langs" /></label>' .
							'<label for="all_langs" class="inline-label">{s_label:takechange}</label>' . "\r\n" .
							'</li>' . "\r\n";
		
		// Featured
		$output		 .=	'<li>' . "\r\n" .
						'<label class="markBox"><input type="checkbox" name="featured" id="featured"' . (!empty($event->dataFeatured) ? ' checked="checked"' : '') . ' /></label>' .
						'<label for="featured" class="inline-label">' . ContentsEngine::getIcon("pushpin") . ' {s_label:featured}</label>' . "\r\n" .
						'</li>' . "\r\n";

		return $event->addOutput($output);
	
	}
	
	// onGetDataFieldsPost
	public function onGetDataFieldsPost(Event $event)
	{

		$output	= "";
		
		return $event->addOutput($output);
		
	}
	
	// onGetNewdataFieldsPre
	public function onGetNewdataFieldsPre(Event $event)
	{

		$output	= "";
		// Startdatum
		$output		 .=	'<li>' . "\r\n" .
						'<div class="fullBox">' . "\r\n" .
						'<label>{s_label:datestart}' . (!$event->showCalendar && $event->modType != "planner" ? ' {s_label:dateleaveempty}' : '') . '</label>' . "\r\n";
						
		if(isset($event->wrongInput['date_start']))
			$output		 .=	'<p class="notice">' . $event->wrongInput['date_start'] . '</p>' . "\r\n";
								
		$output		 .=	'<input type="hidden" id="daynames" value="{s_date:daynames}" alt="{s_date:daynamesmin}" />' . "\r\n" .
						'<input type="hidden" id="monthnames" value="{s_date:monthnames}" alt="{s_date:monthnamesmin}" />' . "\r\n" .
						'<input type="hidden" id="mindate" value="" />' . "\r\n" .
						'<input type="hidden" id="minInterval" value="' . ($event->modType == "planner" ? 15 : 1) . '" />' . "\r\n" .
						'<input type="text" name="news_date" class="datepicker dataDate-input" value="' . (isset($event->dataDateForm) && isset($GLOBALS['_POST']['mod_submit']) ? htmlspecialchars(Modules::getLocalDateString($event->dataDateForm, $event->o_lng->adminLang)) : '') . '" maxlength="10" />' . "\r\n" . 
						'<input type="hidden" name="news_date" class="altField" value="' . (isset($event->dataDateForm) && isset($GLOBALS['_POST']['mod_submit']) ? htmlspecialchars($event->dataDateForm) : '') . '" maxlength="10" />' . "\r\n" . 
						'<input type="hidden" name="timePost" value="' . (isset($event->planHour) && isset($GLOBALS['_POST']['mod_submit']) ? htmlspecialchars($event->planHour) : '') . '" data-min="' . (isset($event->planMin) && isset($GLOBALS['_POST']['mod_submit']) ? htmlspecialchars($event->planMin) : '') . '" />' . "\r\n" .
						'<div class="timepicker start"><label>{s_label:time}</label></div>' . "\r\n";
						
		return $event->addOutput($output);
		
	}
	
	// onGetNewdataFieldsMid
	public function onGetNewdataFieldsMid(Event $event)
	{

		$output	= "";
		
		$output		 .=	'</div>' . "\r\n" .
						'<br class="clearfloat" />' . "\r\n" .
						'</li>' . "\r\n";
		
		
		// if short calendar view
		if($event->showCalendar)
			return $event->addOutput($output);
					

		// Teaser
		$output		 .=	'<li>' . "\r\n" .
						'<label>Teaser<span class="editLangFlag">' . $event->editLangFlag . '</span><span class="toggleEditor" data-target="teaser">Editor</span></label>' . "\r\n" .
						'<textarea name="news_teaser" id="teaser" class="teaser cc-editor-add disableEditor" rows="2">' . htmlspecialchars($event->dataTeaser) . '</textarea>' . "\r\n";

		$output		 .=	'<br class="clearfloat" />' . "\r\n" .
						'</li>' . "\r\n";
								
		
		// Text
		$output		 .=	'<li>' . "\r\n" .
						'<label>{s_label:'.$event->modType.'text}<span class="editLangFlag">' . $event->editLangFlag . '</span></label>' . "\r\n";
							
		if(isset($event->wrongInput['text']))
			$output		 .=	'<p class="notice">' . $event->wrongInput['text'] . '</p>' . "\r\n";
				
		$output		 .=	'<textarea name="news_text" id="text" class="textEditor cc-editor-add" rows="5">' . htmlspecialchars($event->dataText) . '</textarea>' . "\r\n";
		
		$output		 .=	'<br class="clearfloat" />' . "\r\n" .
						'</li>' . "\r\n";

		
		// Falls mehrere Sprachen angelegt sind, Sprachübernahmecheckbox hinzufügen
		if(count($event->o_lng->installedLangs) > 1)
			$output		 .=	'<li class="changeAllLangs">' . "\r\n" .
							'<label class="markBox"><input type="checkbox" name="all_langs" id="all_langs" checked="checked" /></label>' .
							'<label for="all_langs" class="inline-label">{s_label:takechange}</label>' . "\r\n" .
							'</li>' . "\r\n";
		
		// Featured
		$output		 .=	'<li>' . "\r\n" .
						'<label class="markBox"><input type="checkbox" name="featured" id="featured"' . (!empty($event->dataFeatured) ? ' checked="checked"' : '') . ' /></label>' .
						'<label for="featured" class="inline-label">' . ContentsEngine::getIcon("pushpin") . ' {s_label:featured}</label>' . "\r\n" .
						'</li>' . "\r\n";
		
		return $event->addOutput($output);
		
	}
	
	// onGetNewdataFieldsPost
	public function onGetNewdataFieldsPost(Event $event)
	{

		$output	= "";
		
		return $event->addOutput($output);
		
	}
	
	// onGetDataheaderFields
	public function onGetDataheaderFields(Event $event)
	{

		$output	= "";

		// Date
		$output		 .=	'<span class="dataDate" title="{s_date:created}">' . Modules::getLocalDateString($event->dataDate . ' ' . $event->dataTime, $event->o_lng->adminLang, true) . '</span>' . "\r\n";
	
		return $event->addOutput($output);
		
	}
	
	// onGetDataheaderListfields
	public function onGetDataheaderListfields(Event $event)
	{

		$output	= "";
		
		// Date
		if($event->editorLog
		&& $event->modType != "planner"
		)
			$output		 .=	'<span class="dataDate">' . $this->getEditableDate($event->dataEntry['date'], $event->dataEntry['id'], $event->o_lng->adminLang) . '</span>' . PHP_EOL;
		else
			$output		 .=	'<span class="dataDate" title="{s_date:created}">' . Modules::getLocalDateString($event->dataDate . ' ' . $event->dataTime, $event->o_lng->adminLang, true) . '</span>' . "\r\n";
	
		return $event->addOutput($output);
		
	}
	
	// onGetDataAttributes
	public function onGetDataAttributes(Event $event)
	{
	
		// Datum und Uhrzeit auslesen
		$event->planDate = $event->editEntry[0]['date'];
		
		return true;
	
	}
	
	// onEvalDataPost
	public function onEvalDataPost(Event $event)
	{
		
		// Datum und Uhrzeit auslesen
		$event->dataDate		= $event->a_Post['news_date'];
		$event->planDate		= $event->dataDate;			
		$event->planHour		= $event->a_Post['hourcombo_start'];			
		$event->planMin			= $event->a_Post['mincombo_start'];
		
		// Falls Datum leer gelassen wurde, aktuelles Datum nehmen
		if($event->planDate == "") {
			$timestamp			= time();
			$event->planDate	= date("d.m.Y", $timestamp);
			$event->planHour	= date("H", $timestamp);
			$event->planMin		= date("i", $timestamp);
			$event->planSec		= date("s", $timestamp);
		}
		
		$event->newsDateDB		= implode("-", array_reverse(explode(".", $event->planDate)));					
		$event->dataTime		= $event->planHour.":".$event->planMin;				
		$event->timestampStart 	= Modules::getTimestamp($event->planDate, $event->planHour, $event->planMin, 0, ".");

		// Featured
		$event->dataFeatured	= !empty($event->a_Post['featured']) ? 1 : 0;
		
		return true;
	
	}
	
	// onEvalNewdataPost
	public function onEvalNewdataPost(Event $event)
	{
	
		$event->dataDate				= "";
		$event->planDate				= "";
		$event->dataTime				= "";
		
		// Datum und Uhrzeit auslesen
		if(isset($event->a_Post['news_date']) && $event->a_Post['news_date'] != "") {
			$event->planDate	= $event->a_Post['news_date'];
			$event->planHour	= $event->a_Post['hourcombo_start'];
			$event->planMin		= $event->a_Post['mincombo_start'];
			$event->planSec		= "00";
		}
		else {
			$timestamp			= time();
			$event->planDate	= date("d.m.Y", $timestamp);
			$event->planHour	= date("H", $timestamp);
			$event->planMin		= date("i", $timestamp);
			$event->planSec		= date("s", $timestamp);
		}
		
		$event->dataDate		= implode("-", array_reverse(explode(".", $event->planDate)));
		$event->dataDateForm	= $event->planDate;
		$event->dataTime		= $event->planHour.":".$event->planMin.":".$event->planSec;
		$event->timestampStart	= Modules::getTimestamp($event->planDate, $event->planHour, $event->planMin, 0, ".");
	

		// Featured
		$event->dataFeatured	= !empty($event->a_Post['featured']) ? 1 : 0;

		
		// Datum und Uhrzeit für DB
		$event->dataDateDb		= $event->dataDate . " " . $event->dataTime;
		
		$event->dbInsertStr1   .= "`featured`,`date`,";		
		$event->dbInsertStr2   .= $event->dataFeatured . ",'" . $event->DB->escapeString($event->dataDateDb) . "',";

		return true;
	
	}

} // Ende class
