<?php
namespace Concise\Events\Adminarticles;

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
	
		// dataOrder
		$event->dataOrder	= (int)$event->a_Post['newsOrder'];
		
		return $event->setResult($event->dataOrder);
	
	}
	
	// onMakeCatDbstring
	public function onMakeCatDbstring(Event $event)
	{

		$dataOrderDb = $event->DB->escapeString($event->dataOrder);
		
		$event->dbInsertStr1 .= '`order_opt`,';
		$event->dbInsertStr2 .= $dataOrderDb . ",";
		
		$event->dbUpdateStr .= "`order_opt` = $dataOrderDb,";
		
		return true;
	
	}
	
	// onResetCatAttributes
	public function onResetCatAttributes(Event $event)
	{

		$event->dataOrder	= "";
		
		return $event->setResult($event->dataOrder);
	
	}
	
	// onGetCatFieldsMid
	public function onGetCatFieldsMid(Event $event)
	{

		// Option auf Bestellbarkeit hinzufügen
		$output		 =	'<li>' . "\r\n" .
						'<label>{s_label:orderopt}</label>' . "\r\n" .
						'<select name="newsOrder">' . "\r\n" .
						'<option value="0">{s_option:non}</option>' . "\r\n" .
						'<option value="1"' . (!empty($event->dataOrder) ? ' selected="selected"' : '') . '>{s_option:yesn}</option>' . "\r\n" .
						'</select>' . "\r\n" .
						'</li>' . "\r\n";
		
		return $event->addOutput($output);
	
	}
	
	// onGetCatFieldsPost
	public function onGetCatFieldsPost(Event $event)
	{
		
		$output		 =	"";
		
		return $event->addOutput($output);

	}
	
	// onGetGoeditcatFields
	public function onGetGoeditcatFields(Event $event)
	{

		$output	=	'<input type="hidden" name="newsOrder" value="' . $event->catData['order_opt'] . '" />' . "\n";

		return $event->addOutput($output);

	}

} // Ende class
