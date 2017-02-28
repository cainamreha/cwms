<?php
namespace Concise\Events\Admindata;

use Symfony\Component\EventDispatcher\Event;
use Concise\Admin_ModulesData;
use Concise\HTML;



##############################
###  EventListener-Klasse  ###
##############################

// CatCoreEventsListener

class CatCoreEventsListener extends Admin_ModulesData
{	
	
	// constructor
	public function __construct()
	{
		
	}
	
	// onGetCatFieldsMid
	public function onGetCatFieldsMid(Event $event)
	{

		// Cat Output
		$output	= "";
		
		// Kommentarauswahl
		$output		 .=	'<li>' . "\r\n" .
						'<label>{s_label:comments}</label>' . "\r\n" .
						'<select name="newsComments">' . "\r\n" .
						'<option value="0">{s_option:non}</option>' . "\r\n" .
						'<option value="1"' . (!empty($event->dataComments) ? ' selected="selected"' : '') . '>{s_option:yesn}</option>' . "\r\n" .
						'</select>' . "\r\n" .
						'</li>' . "\r\n";
					
		// Ratingsystemauswahl
		$output		 .=	'<li>' . "\r\n" .
						'<label>{s_label:rating}</label>' . "\r\n" .
						'<select name="newsRating">' . "\r\n" .
						'<option value="0">{s_option:non}</option>' . "\r\n" .
						'<option value="1"' . (!empty($event->dataRating) ? ' selected="selected"' : '') . '>{s_option:yesn}</option>' . "\r\n" .
						'</select>' . "\r\n" .
						'</li>' . "\r\n";
		
		$output		 .=	'</ul>' . "\r\n" .
						'</li>' . "\r\n";

		// Zielseite
		
		$output .=		'<li>' . "\r\n" .
						'<label>{s_button:targetPage}</label>' . "\r\n" . 
						'<input type="text" name="dataTargetPage" class="targetPage" value="' . htmlspecialchars(HTML::getLinkPath($event->targetPage, "editLang", false)) . '" readonly="readonly" />' . "\r\n" .
						'<input type="hidden" name="dataTargetPageID" class="targetPageID" value="' . htmlspecialchars($event->targetPage) . '" />' . "\r\n";
		
		// targetPage MediaList-Button
		$mediaListButtonDef		= array(	"class"	 	=> "targetPage",
											"type"		=> "targetPage",
											"url"		=> SYSTEM_HTTP_ROOT . "/access/listPages.php?page=admin&type=link",
											"value"		=> "{s_button:targetPage}",
											"icon"		=> "page"
										);
		
		$output 	 .=	$this->getButtonMediaList($mediaListButtonDef);
		
		$output		 .=	'</li>' . "\r\n";

		// Kategoriebild
		$output		 .=	'<li>' . "\r\n" .
						'<div class="attach imgObject">' . "\r\n" .	
						'<label class="markBox">' . "\r\n" .
						'<input type="checkbox" name="add_catimg" id="add_catimg" class="toggleObjectType"' . ($event->useCatImage ? ' checked="checked"' : '') . ' />' . "\r\n" .
						'</label>'. "\r\n" .
						'<label for="add_catimg" class="dataObjectLabel inline-label">{s_label:catimage}</label>' . "\r\n";

		$output		 .=	'<div class="dataObjectBox" style="' . (!$event->useCatImage ? 'display:none;' : '') . '">' . "\r\n";
		
		
		// Kategorie-Bild
		$output		 .=	$event->dataCatImage;
		
		// Alte Bilddaten (weitere Sprachen mitgeben)
		$output		 .=	'<input type="hidden" name="old_catimg" value="' . htmlspecialchars(json_encode($event->catImg)) . '" />' . "\r\n";

		$output		 .=	'</div>' . "\r\n" .
						'</div>' . "\r\n" .
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

		$output	=	'<input type="hidden" name="edit_cat" value="' . htmlspecialchars($event->catData['cat_id']) . '" />' . "\r\n" .
					'<input type="hidden" name="go_edit_cat" value="' . htmlspecialchars($event->catData['cat_id']) . '" />' . "\r\n" .
					'<input type="hidden" name="dataCatName" value="' . htmlspecialchars($event->catData['category_'.$event->editLang]) . '" />' . "\r\n" .
					'<input type="hidden" name="dataParentCat" value="' . htmlspecialchars($event->catData['parent_cat']) . '" />' . "\r\n" .
					'<input type="hidden" name="oldParentCat" value="' . htmlspecialchars($event->catData['parent_cat']) . '" />' . "\r\n" .
					'<input type="hidden" name="sort_id" value="' . htmlspecialchars($event->catData['sort_id']) . '" />' . "\r\n" .
					'<input type="hidden" name="catTeaser" value="' . htmlspecialchars($event->catData['cat_teaser_'.$event->editLang]) . '" />' . "\r\n";
				
		$groupArray = explode(",", $event->catData['group']);
		
		foreach($groupArray as $targetGroup) {
			$output .= '<input type="hidden" name="newsGroupRead[]" value="' . htmlspecialchars($targetGroup) . '" />' . "\r\n";
		}
				
		$groupArray = explode(",", $event->catData['group_edit']);
		
		foreach($groupArray as $targetGroup) {
			$output .= '<input type="hidden" name="newsGroupWrite[]" value="' . htmlspecialchars($targetGroup) . '" />' . "\r\n";
		}
		
		$output .=	'<input type="hidden" name="newsComments" value="' . htmlspecialchars($event->catData['comments']) . '" />' . "\r\n" .
					'<input type="hidden" name="newsRating" value="' . htmlspecialchars($event->catData['rating']) . '" />' . "\r\n" .
					'<input type="hidden" name="dataTargetPageID" value="' . htmlspecialchars($event->catData['target_page']) . '" />' . "\r\n" .
					'<input type="hidden" name="catImg" value="' . htmlspecialchars($event->catData['image']) . '" />' . "\r\n" .
					'<input type="hidden" name="img_existFile" value="" />' . "\r\n" .
					'<input type="hidden" name="img_alttext" value="" />' . "\r\n" .
					'<input type="hidden" name="nochange" value="1" />' . "\r\n";

		return $event->addOutput($output);

	}
	
} // Ende class
