<?php
namespace Concise\Events\Articles;

use Symfony\Component\EventDispatcher\Event;
use Concise\ContentsEngine;
use Concise\Modules;
use Concise\Orderfm;



##############################
###  EventListener-Klasse  ###
##############################

// DetailCoreEventsListener

class DetailCoreEventsListener
{	
	
	// onGetDataDetails
	public function onGetDataDetails(Event $event)
	{
	
		if($event->modType != "articles")
			return false;
		
		
		// Event vars
		$orderOpt			= $event->queryData[0]['order_opt'];
		$event->dataOrder	= "";
		$event->cart		= "";
		
		if($orderOpt == 1) {
			
			// Zunächst das entsprechende Modul einbinden (Orderfm-Klasse)
			require_once PROJECT_DOC_ROOT."/inc/classes/Modules/class.Orderfm.php";
			
			$price			= Modules::getPrice($event->queryData[0]['price'], $event->lang);
			$shippingLink	= "{shippingLink}";
			
			$event->scriptFiles[] 	= JS_DIR . "popup.js";


			$event->classExt	= ' dataShop';
			$event->cart		= "{cart}"; // Buttons zum Warenkorb einbinden
			$event->dataDate	= '<span class="articleDate">{s_text:availablesince} </span>' . $event->dataDate;

			// Button addToCart
			$btnDefs	= array(	"type"		=> "submit",
									"class"		=> 'addToCart button-icon-only button-small right',
									"text"		=> "",
									"title"		=> '{s_title:addtocart}',
									"icon"		=> "cart"
								);
			
			$addToCartBtn	=	ContentsEngine::getButton($btnDefs);
			
			$event->dataOrder	=	'<div class="addToCart">' . "\r\n" .
									'<form method="post" action="" class="{t_class:forminl}">' . "\r\n" .
									'<fieldset>' . "\r\n" .
									'<span class="{t_class:fieldrow}">' . "\r\n" .
									'<input type="hidden" name="cat_id" value="' . $event->dataCatID . '" />' . "\r\n" .
									'<input type="hidden" name="data_id" value="' . $event->dataID . '" />' . "\r\n" .
									'<input type="hidden" name="addToCart" value="true" />' . "\r\n" .
									$event->tokenInput .
									$addToCartBtn .
									'<input type="text" name="amount" value="1" class="inputAmount {t_class:input} {t_class:fieldinl}" />' . "\r\n" .
									'<label class="{t_class:labelinl}"><span class="price">' . $price . ' EUR</span><span class="mwst"><br />{s_label:price} {s_text:ustr}</span> <span class="shipping">{s_common:plus} ' . $shippingLink . '</span></label>' .
									'</span>' . "\r\n" .
									'</fieldset>' . "\r\n" .
									'</form>' . "\r\n" .
									'</div>' . "\r\n";
			
			$dataCategory	=	$event->dataOrder;
		
		}
		else{
		
			$event->dataDate		= '<span class="articleDate">' . $event->dataDate . '</span>' . "\r\n";
		
		}
		
		return true;
	
	}
	
	// onAssignDataDetails
	public function onAssignDataDetails(Event $event)
	{
	
		if($event->modType != "articles")
			return false;
		
				
		// Platzhalterersetzungen
		$event->tpl_data->assign("dataDate", $event->dataDate);
		$event->tpl_data->assign("cart", $event->cart);
		$event->tpl_data->assign("dataOrder", $event->dataOrder);

		return true;
	
	}

} // Ende class
