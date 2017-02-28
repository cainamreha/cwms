<?php
namespace Concise\Events\Adminarticles;

use Symfony\Component\EventDispatcher\Event;
use Concise\Modules;



##############################
###  EventListener-Klasse  ###
##############################

// DataCoreEventsListener

class DataCoreEventsListener
{	
	
	// onGetDataFieldsPost
	public function onGetDataFieldsPost(Event $event)
	{
	
		$output	= "";
		
		// Artikelpreise
		if($event->editData && $event->orderOpt == 1) {
		
			$output		 .=	'<li>' . "\r\n" .
							'<label>{s_label:price}</label>' . "\r\n";
		
			if(isset($event->wrongInput['price']))
				$output	 .=	'<p class="notice">' . $event->wrongInput['price'] . '</p>' . "\r\n";
					
			$output		 .=	'<input type="text" name="price" value="' . (isset($event->price) && $event->price!= '' ? htmlspecialchars(str_replace(".", ",", $event->price)) : '') . '" maxlength="9" />' . "\r\n";
	
			$output		 .=	'<br class="clearfloat" />' . "\r\n" .
							'</li>' . "\r\n";
		}
		
		return $event->addOutput($output);
	
	}
	
	// onGetNewdataFieldsPost
	public function onGetNewdataFieldsPost(Event $event)
	{
	
		// Artikelpreise
		$output		 =	'<li>' . "\r\n" .
						'<label>{s_label:price}</label>' . "\r\n";
		
		if(isset($event->wrongInput['price']))
			$output		 .=	'<p class="notice">' . $event->wrongInput['price'] . '</p>' . "\r\n";
			
		$output		 .=	'<input type="text" name="price" value="' . (isset($event->price) && $event->price!= '' ? htmlspecialchars(str_replace(".", ",", $event->price)) : '') . '" maxlength="9" />' . "\r\n";
	
		$output		 .=	'<br class="clearfloat" />' . "\r\n" .
						'</li>' . "\r\n";
	
		return $event->addOutput($output);
	
	}
	
	// onGetDataAttributes
	public function onGetDataAttributes(Event $event)
	{

		// Datum
		$event->dataDateOld		= $event->planDate;
		$pdArr					= explode(" ", $event->planDate);
		$ptArr					= explode(":", end($pdArr));
		$event->dataDate		= implode(".", array_reverse(explode("-", reset($pdArr))));
		$event->planHour		= reset($ptArr);
		$event->planMin			= next($ptArr);
		$event->dataTime		= $event->planHour.":".$event->planMin;

		
		// Artikelpreise
		$event->price		= trim($event->editEntry[0]['price']);				
		$event->orderOpt	= $event->editEntry[0]['order_opt'];
		
		if((2 * (float)$event->price) == 0 && $event->orderOpt == 1)
			$event->hint	= "{s_notice:noprice}";
	
		return true;
	
	}
	
	// onEvalDataPost
	public function onEvalDataPost(Event $event)
	{
	
		// Datum
		$event->newsDateDB = $event->newsDateDB . " " . $event->dataTime;
		
		// Falls das Datum bei Artikeln oder News geändert wurde, neues Datum in DB eintragen und mit 00 für Sekunden auffüllen
		if($event->newsDateDB != substr($event->dataDateOld, 0, -3))
			$event->dbUpdateStr .= "`date` = '" . $event->DB->escapeString($event->newsDateDB . ":00") . "',";

		
		// Artikelpreise
		if(isset($event->a_Post['price']))
			$event->price	= trim($event->a_Post['price']);
		
		// Preis überprüfen
		if(strlen($event->price) > 9)
			$event->wrongInput['price'] = "{s_error:check}";
		elseif($event->price != "" && !preg_match("/^[0-9]+[,\.]?[0-9]{0,2}$/", $event->price))
			$event->wrongInput['price'] = "{s_error:check}";
		else {
			$priceDb = number_format((float)str_replace(",", ".", $event->price), 2, '.', '');
			$event->dbUpdateStr .= "`price` = " . $event->DB->escapeString($priceDb) . ",";
		}

		return true;
	
	}
	
	// onEvalNewdataPost
	public function onEvalNewdataPost(Event $event)
	{
	
		// Artikel Preise
		if(isset($event->a_Post['price']))
			$event->price	= trim($event->a_Post['price']);
		
		// Preis überprüfen
		if(strlen($event->price) > 9)
			$event->wrongInput['price'] = "{s_error:check}";
		elseif($event->price != "" && !preg_match("/^[0-9]+[,\.]?[0-9]{0,2}$/", $event->price))
			$event->wrongInput['price'] = "{s_error:check}";
		else {
			$priceDb	= number_format((float)str_replace(",", ".", $event->price), 2, '.', '');
			$event->dbInsertStr1 .= "`price`,";
			$event->dbInsertStr2 .= $event->DB->escapeString($priceDb) . ",";
		}

		return true;
	
	}

	
	// onGetDataListattribute
	public function onGetDataListattribute(Event $event)
	{

		$output	= "";
		
		// Artikel Preise
		if($event->dataEntry['order_opt'] == 1)
			$output		 .=	'<div class="orderDetails">' . "\r\n" .
							'<p clss="right">{s_label:ordered}: <strong>' . $event->dataEntry['orders'] . ' x</strong></p>' . "\r\n" .
							'<p>{s_label:price}: <strong>' . Modules::getPrice($event->dataEntry['price'], $event->editLang) . '</strong></p>' . "\r\n". 
							'</div>' . "\r\n";
				
		return $event->addOutput($output);

	}

} // Ende class
