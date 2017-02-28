<?php
namespace Concise\Events\Adminplanner;

use Symfony\Component\EventDispatcher\Event;
use Concise\Modules;



##############################
###  EventListener-Klasse  ###
##############################

// DataCoreEventsListener

class DataCoreEventsListener
{	
	
	// onGetDataFieldsPre
	public function onGetDataFieldsPre(Event $event)
	{
	
		$output	= "";
		
		// Falls ein Termin bearbeitet werden soll, Datum anzeigen
		// Enddatum
		$output		 .=	'<br class="clearfloat" />' . "\r\n" .
						'<label>{s_label:dateend}</label>' . "\r\n";

		if(isset($event->wrongInput['date_end']))
			$output		 .=	'<p class="notice">' . $event->wrongInput['date_end'] . '</p>' . "\r\n";
							
		$output		 .=	'<input type="text" name="news_date_end" class="datepicker dataDate-input" value="' . (isset($event->dataDateEnd) ? htmlspecialchars(Modules::getLocalDateString($event->dataDateEnd, $event->o_lng->adminLang)) : '') . '" maxlength="10" />' . "\r\n" . 
						'<input type="hidden" name="news_date_end" class="altField" value="' . (isset($event->dataDateEnd) ? htmlspecialchars($event->dataDateEnd) : '') . '" maxlength="10" />' . "\r\n" . 
						'<input type="hidden" name="timePost_end" value="' . $event->planHourEnd . '" data-min="' . $event->planMinEnd . '" />' . "\r\n" .
						'<div class="timepicker end"><label>{s_label:time}</label></div>' . "\r\n";

		return $event->addOutput($output);
	
	}
	
	// onGetNewdataFieldsPre
	public function onGetNewdataFieldsPre(Event $event)
	{

		$output	= "";
		

		// Enddatum
		$output		 .=	'<p class="clearfloat">&nbsp;</p>' . "\r\n" .
						'<label>{s_label:dateend}</label>' . "\r\n";
					
		if(isset($event->wrongInput['date_end']))
			$output		 .=	'<p class="notice">' . $event->wrongInput['date_end'] . '</p>' . "\r\n";
							
		$output		 .=	'<input type="text" name="news_date_end" class="datepicker dataDate-input" value="' . (isset($event->dataDateEnd) && isset($GLOBALS['_POST']['mod_submit']) ? htmlspecialchars(Modules::getLocalDateString($event->dataDateEnd, $event->o_lng->adminLang)) : '') . '" maxlength="10" />' . "\r\n" . 
						'<input type="hidden" name="news_date_end" class="altField" value="' . (isset($event->dataDateEnd) && isset($GLOBALS['_POST']['mod_submit']) ? htmlspecialchars($event->dataDateEnd) : '') . '" maxlength="10" />' . "\r\n" . 
						'<input type="hidden" name="timePost_end" value="' . (isset($event->planHourEnd) && isset($GLOBALS['_POST']['mod_submit']) ? htmlspecialchars($event->planHourEnd) : '') . '" data-min="' . (isset($event->planMinEnd) && isset($GLOBALS['_POST']['mod_submit']) ? htmlspecialchars($event->planMinEnd) : '') . '" />' . "\r\n" .
						'<div class="timepicker end"><label>{s_label:time}</label></div>' . "\r\n";
		
		return $event->addOutput($output);
		
	}
	
	// onGetDataAttributes
	public function onGetDataAttributes(Event $event)
	{
	
		// Datum
		$pdArr					= explode("-", $event->planDate);
		$ptArr					= explode(":", $event->editEntry[0]['time']);
		$event->dataDate		= implode(".", array_reverse($pdArr));
		$event->planHour		= reset($ptArr);
		$event->planMin			= next($ptArr);
		$event->dataTime		= $event->planHour.":".$event->planMin;
			
		$event->planDateEnd		= $event->editEntry[0]['date_end'];
		$pdeArr					= explode("-", $event->planDateEnd);
		$pteArr					= explode(":", $event->editEntry[0]['time_end']);
		$event->dataDateEnd		= implode(".", array_reverse($pdeArr));
		
		$event->planHourEnd		= reset($pteArr);
		$event->planMinEnd		= next($pteArr);
		$event->dataTimeEnd		= $event->planHourEnd.":".$event->planMinEnd;
	
		return true;
	
	}
	
	// onEvalDataPost
	public function onEvalDataPost(Event $event)
	{
		
		// Termin Datum und Uhrzeit auslesen
		$event->planDateEnd = $event->a_Post['news_date_end'];			
		if($event->planDateEnd == "") {
			$event->dataDateEnd	= $event->dataDate;
			$newsDateEndDB		= $event->newsDateDB;
			$event->planDateEnd	= $event->planDate;
			$event->planHourEnd	= $event->planHour;		
			$event->planMinEnd	= $event->planMin;
			$event->dataTimeEnd	= $event->dataTime;
		}
		else {
			$event->dataDateEnd	= $event->planDateEnd;
			$pdeArr				= explode(".", $event->planDateEnd);
			$newsDateEndDB		= implode("-", array_reverse($pdeArr));
			$event->planHourEnd	= $event->a_Post['hourcombo_end'];			
			$event->planMinEnd	= $event->a_Post['mincombo_end'];
			$event->dataTimeEnd	= $event->planHourEnd.":".$event->planMinEnd;				
		}
	
		$timestampEnd		= Modules::getTimestamp($event->planDateEnd, $event->planHourEnd, $event->planMinEnd, 0, ".");
		
		// Datum überprüfen
		if($event->planDate == "")
			$event->wrongInput['date_start'] = "{s_notice:nodate}";
		
		elseif($timestampEnd <= $event->timestampStart && $event->planDateEnd != "")
			$event->wrongInput['date_end'] = "{s_notice:datepast}";
		
		else
			$event->dbUpdateStr .=	"`date` = '" . $event->DB->escapeString($event->newsDateDB) . "'," .
									"`time` = '" . $event->DB->escapeString($event->dataTime) . "'," .
									"`date_end` = '" . $event->DB->escapeString($newsDateEndDB) . "'," .
									"`time_end` = '" . $event->DB->escapeString($event->dataTimeEnd) . "',";
		

		return true;
	
	}
	
	// onEvalNewdataPost
	public function onEvalNewdataPost(Event $event)
	{
	
		if(!empty($event->a_Post['news_date_end']))
			$event->planDateEnd	= $event->a_Post['news_date_end'];
		
		if($event->planDateEnd == "") {
			$event->dataDateEnd	= $event->planDate;
			$planDateEndDB		= $event->dataDate;
			$event->planHourEnd	= $event->planHour;		
			$event->planMinEnd	= $event->planMin;
			$event->dataTimeEnd	= $event->dataTime;
		}
		else {
			$event->dataDateEnd	= $event->planDateEnd;
			$planDateEndDB		= implode("-", array_reverse(explode(".", $event->planDateEnd)));
			$event->planHourEnd	= $event->a_Post['hourcombo_end'];			
			$event->planMinEnd	= $event->a_Post['mincombo_end'];
			$event->dataTimeEnd	= $event->planHourEnd.":".$event->planMinEnd;				
		}
		
		$timestampEnd		= Modules::getTimestamp($event->planDateEnd, $event->planHourEnd, $event->planMinEnd, 0, ".");
			
		
		// Datum überprüfen
		if($event->planDate == "")
			$event->wrongInput['date_start'] = "{s_notice:nodate}";
			
		elseif($timestampEnd <= $event->timestampStart && $event->planDateEnd != "")
			$event->wrongInput['date_end'] = "{s_notice:datepast}";
			
		else {
			$event->dbInsertStr1 .=	"`time`," .
									"`date_end`," .
									"`time_end`,";
									
			$event->dbInsertStr2 .=	"'" . $event->DB->escapeString($event->dataTime) . "'," .
									"'" . $event->DB->escapeString($planDateEndDB) . "'," .
									"'" . $event->DB->escapeString($event->dataTimeEnd) . "',";
		}

		return true;
	
	}

} // Ende class
