<?php
namespace Concise\Events\Adminnews;

use Symfony\Component\EventDispatcher\Event;



##############################
###  EventListener-Klasse  ###
##############################

// CatCoreEventsListener

class CatCoreEventsListener
{	
	
	// onEvalCatPost
	public function onEvalCatPost(Event $event)
	{
	
		// dataFeed
		$event->dataFeed	= (int)$event->a_Post['newsFeed'];
		
		return $event->setResult($event->dataFeed);
	
	}
	
	// onMakeCatDbstring
	public function onMakeCatDbstring(Event $event)
	{

		// Newsfeedoption bei News			
		$dataFeedDb = $event->DB->escapeString($event->dataFeed);
			
		$event->dbInsertStr1 .= '`newsfeed`,';
		$event->dbInsertStr2 .= $dataFeedDb . ",";
		
		$event->dbUpdateStr  .= "`newsfeed` = $dataFeedDb,";
		
		return $event->setResult(true);
	
	}
	
	// onResetCatAttributes
	public function onResetCatAttributes(Event $event)
	{

		$event->dataFeed	= "";
		
		return $event->setResult($event->dataFeed);
	
	}
	
	// onGetCatFieldsMid
	public function onGetCatFieldsMid(Event $event)
	{

		// Option auf Bestellbarkeit hinzufügen
		$output		 =	'<li>' . "\r\n" .
						'<label>Newsfeed</label>' . "\r\n" .
						'<select name="newsFeed">' . "\r\n" .
						'<option value="0">{s_option:non}</option>' . "\r\n" .
						'<option value="1"' . (!empty($event->dataFeed) ? ' selected="selected"' : '') . '>{s_option:yesn}</option>' . "\r\n" .
						'</select>' . "\r\n" .
						'</li>' . "\r\n";
	
		return $event->addOutput($output);
	
	}
	
	// onGetCatFieldsPost
	public function onGetCatFieldsPost(Event $event)
	{
	}
	
	// onGetGoeditcatFields
	public function onGetGoeditcatFields(Event $event)
	{

		$output	=	'<input type="hidden" name="newsFeed" value="' . $event->catData['newsfeed'] . '" />' . "\n";

		return $event->addOutput($output);

	}
	
} // Ende class
