<?php
namespace Concise\Events\Adminedit;

use Symfony\Component\EventDispatcher\Event;
use Concise\ContentsEngine;
use Concise\Admin_Edit;



##############################
###  EventListener-Klasse  ###
##############################

// EditCoreEventsListener

class EditCoreEventsListener extends Admin_Edit
{	
	
	public function __construct()
	{
		
	}
	
	// onEvalEditPost
	public function onEvalEditPost(Event $event)
	{
	
		$i	= $event->eleCnt;
		
		if(strpos($event->conElements[$i]['style'], "{") !== 0) {
			$hideEl = strpos($event->conElements[$i]['style'], "<hide>") !== false ? 1 : 0;
			$event->conElements[$i]['style'] = str_replace("<hide>", "", $event->conElements[$i]['style']);				
			$event->styles = explode("/", $event->conElements[$i]['style']);
			$event->styles = $this->changeArrayKey( $event->styles, 0, 'id');
			$event->styles = $this->changeArrayKey( $event->styles, 1, 'class');
			$event->styles = $this->changeArrayKey( $event->styles, 2, 'style');
			$event->styles = $this->changeArrayKey( $event->styles, 3, 'cols');
			$event->styles['hide']	= $hideEl;
		}
		else
			$event->styles = (array)json_decode($event->conElements[$i]['style']);
		
		if(!isset($event->styles['id']))
			$event->styles['id'] = "";
		if(!isset($event->styles['class']))
			$event->styles['class'] = "";
		if(!isset($event->styles['style']))
			$event->styles['style'] = "";
		if(!isset($event->styles['cols']))
			$event->styles['cols'] = "";
		if(!isset($event->styles['colssm']))
			$event->styles['colssm'] = "";
		if(!isset($event->styles['colsxs']))
			$event->styles['colsxs'] = "";
		if(!isset($event->styles['mt']))
			$event->styles['mt'] = "";
		if(!isset($event->styles['mb']))
			$event->styles['mb'] = "";
		if(!isset($event->styles['ml']))
			$event->styles['ml'] = "";
		if(!isset($event->styles['mr']))
			$event->styles['mr'] = "";
		if(!isset($event->styles['pt']))
			$event->styles['pt'] = "";
		if(!isset($event->styles['pb']))
			$event->styles['pb'] = "";
		if(!isset($event->styles['pl']))
			$event->styles['pl'] = "";
		if(!isset($event->styles['pr']))
			$event->styles['pr'] = "";
		if(!isset($event->styles['hide']))
			$event->styles['hide'] = 0;
		if(!isset($event->styles['sec']))
			$event->styles['sec'] = 0;
		if(!isset($event->styles['ctr']))
			$event->styles['ctr'] = 0;
		if(!isset($event->styles['row']))
			$event->styles['row'] = 0;
		if(!isset($event->styles['div']))
			$event->styles['div'] = 0;
		if(!isset($event->styles['secid']))
			$event->styles['secid'] = "";
		if(!isset($event->styles['ctrid']))
			$event->styles['ctrid'] = "";
		if(!isset($event->styles['rowid']))
			$event->styles['rowid'] = "";
		if(!isset($event->styles['divid']))
			$event->styles['divid'] = "";
		if(!isset($event->styles['secclass']))
			$event->styles['secclass'] = "";
		if(!isset($event->styles['ctrclass']))
			$event->styles['ctrclass'] = "";
		if(!isset($event->styles['rowclass']))
			$event->styles['rowclass'] = "";
		if(!isset($event->styles['divclass']))
			$event->styles['divclass'] = "";
		if(!isset($event->styles['secbgcol']))
			$event->styles['secbgcol'] = "";
		if(!isset($event->styles['secbgimg']))
			$event->styles['secbgimg'] = "";

		
		// Element Status
		if($event->styles['hide']) {
			$event->elementStatus		= 0;
			$event->statusButton		= "unpublish";
		}
		
		
		// Styleangaben
		// Falls das Formular abgeschickt wurde, Styles (id) auslesen
		if(isset($GLOBALS['_POST'][$event->conPrefix . '_id'])) {
		
			$event->stylesPost['id']		= trim($GLOBALS['_POST'][$event->conPrefix . '_id']);
			
			if(trim($GLOBALS['_POST'][$event->conPrefix . '_id']) != "" && !preg_match("/^[a-zA-Z0-9_-]+$/", $event->stylesPost['id'])) // Falls die id (styles) nicht den Regeln entspricht
				$event->wrongInput[]	= 'styles-con' . $i . '_id';
			else
				$event->styles['id']		= $event->stylesPost['id'];
		}
		
		// Styles (class) auslesen
		if(isset($GLOBALS['_POST'][$event->conPrefix . '_class'])) {

			$event->stylesPost['class']	= trim($GLOBALS['_POST'][$event->conPrefix . '_class']);

			if(trim($GLOBALS['_POST'][$event->conPrefix . '_class']) != "" && !preg_match("/^[a-zA-Z0-9 _-]+$/", $event->stylesPost['class'])) // Falls  die Classes (styles) nicht den Regeln entsprechen
				$event->wrongInput[] = 'styles-con' . $i . '_class';
			else
				$event->styles['class']	= $event->stylesPost['class'];
		}
		
		// Styles (styles) auslesen
		if(isset($GLOBALS['_POST'][$event->conPrefix . '_styles'])) {

			$event->stylesPost['style']	= trim($GLOBALS['_POST'][$event->conPrefix . '_styles']);
			$event->styles['style']		= $event->stylesPost['style'];
		}
		
		// Styles (columns) auslesen
		if(isset($GLOBALS['_POST'][$event->conPrefix . '_columns'])) {
		
			$event->stylesPost['cols']		= trim($GLOBALS['_POST'][$event->conPrefix . '_columns']);

			if($event->stylesPost['cols'] != "" && preg_match("/[\/]/", $event->stylesPost['cols'])) // Falls  die Styles (columns) nicht den Regeln entsprechen
				$event->wrongInput[] = 'styles-con' . $i . '_columns';
			else
				$event->styles['cols']	= $event->stylesPost['cols'];
		}
		
		// Styles (columns sm) auslesen
		if(isset($GLOBALS['_POST'][$event->conPrefix . '_columns_sm'])) {
		
			$event->stylesPost['cols']		= trim($GLOBALS['_POST'][$event->conPrefix . '_columns_sm']);

			if($event->stylesPost['cols'] != "" && preg_match("/[\/]/", $event->stylesPost['cols'])) // Falls  die Styles (columns) nicht den Regeln entsprechen
				$event->wrongInput[] = 'styles-con' . $i . '_columns_sm';
			else
				$event->styles['colssm']	= $event->stylesPost['cols'];
		}
		
		// Styles (columns xs) auslesen
		if(isset($GLOBALS['_POST'][$event->conPrefix . '_columns_xs'])) {
		
			$event->stylesPost['cols']		= trim($GLOBALS['_POST'][$event->conPrefix . '_columns_xs']);

			if($event->stylesPost['cols'] != "" && preg_match("/[\/]/", $event->stylesPost['cols'])) // Falls  die Styles (columns) nicht den Regeln entsprechen
				$event->wrongInput[] = 'styles-con' . $i . '_columns_xs';
			else
				$event->styles['colsxs']	= $event->stylesPost['cols'];
		}
		
		// Styles (margins) auslesen
		if(isset($GLOBALS['_POST'][$event->conPrefix . '_margint'])) {
		
			$event->stylesPost['mt']		= $GLOBALS['_POST'][$event->conPrefix . '_margint'];
			
			if($event->stylesPost['mt'] != "")
				(int)$event->stylesPost['mt'];

			if($event->stylesPost['mt'] != "" && !is_numeric($event->stylesPost['mt'])) // Falls  die Styles (margin) nicht den Regeln entsprechen
				$event->wrongInput[] = 'styles-con' . $i . '_margint';
			else
				$event->styles['mt']	= $event->stylesPost['mt'];
		}
		if(isset($GLOBALS['_POST'][$event->conPrefix . '_marginb'])) {
		
			$event->stylesPost['mb']		= $GLOBALS['_POST'][$event->conPrefix . '_marginb'];
			
			if($event->stylesPost['mb'] != "")
				(int)$event->stylesPost['mb'];

			if($event->stylesPost['mb'] != "" && !is_numeric($event->stylesPost['mb'])) // Falls  die Styles (margin) nicht den Regeln entsprechen
				$event->wrongInput[] = 'styles-con' . $i . '_marginb';
			else
				$event->styles['mb']	= $event->stylesPost['mb'];
		}
		if(isset($GLOBALS['_POST'][$event->conPrefix . '_marginl'])) {
		
			$event->stylesPost['ml']		= $GLOBALS['_POST'][$event->conPrefix . '_marginl'];
			
			if($event->stylesPost['ml'] != "")
				(int)$event->stylesPost['ml'];

			if($event->stylesPost['ml'] != "" && !is_numeric($event->stylesPost['ml'])) // Falls  die Styles (margin) nicht den Regeln entsprechen
				$event->wrongInput[] = 'styles-con' . $i . '_marginl';
			else
				$event->styles['ml']	= $event->stylesPost['ml'];
		}
		if(isset($GLOBALS['_POST'][$event->conPrefix . '_marginr'])) {
		
			$event->stylesPost['mr']		= $GLOBALS['_POST'][$event->conPrefix . '_marginr'];
			
			if($event->stylesPost['mr'] != "")
				(int)$event->stylesPost['mr'];

			if($event->stylesPost['mr'] != "" && !is_numeric($event->stylesPost['mr'])) // Falls  die Styles (margin) nicht den Regeln entsprechen
				$event->wrongInput[] = 'styles-con' . $i . '_marginr';
			else
				$event->styles['mr']	= $event->stylesPost['mr'];
		}
		
		// Styles (paddings) auslesen
		if(isset($GLOBALS['_POST'][$event->conPrefix . '_paddingt'])) {
		
			$event->stylesPost['pt']		= $GLOBALS['_POST'][$event->conPrefix . '_paddingt'];
			
			if($event->stylesPost['pt'] != "")
				(int)$event->stylesPost['pt'];

			if($event->stylesPost['pt'] != "" && !is_numeric($event->stylesPost['pt'])) // Falls  die Styles (padding) nicht den Regeln entsprechen
				$event->wrongInput[] = 'styles-con' . $i . '_paddingt';
			else
				$event->styles['pt']	= $event->stylesPost['pt'];
		}
		if(isset($GLOBALS['_POST'][$event->conPrefix . '_paddingb'])) {
		
			$event->stylesPost['pb']		= $GLOBALS['_POST'][$event->conPrefix . '_paddingb'];
			
			if($event->stylesPost['pb'] != "")
				(int)$event->stylesPost['pb'];

			if($event->stylesPost['pb'] != "" && !is_numeric($event->stylesPost['pb'])) // Falls  die Styles (padding) nicht den Regeln entsprechen
				$event->wrongInput[] = 'styles-con' . $i . '_paddingb';
			else
				$event->styles['pb']	= $event->stylesPost['pb'];
		}
		if(isset($GLOBALS['_POST'][$event->conPrefix . '_paddingl'])) {
		
			$event->stylesPost['pl']		= $GLOBALS['_POST'][$event->conPrefix . '_paddingl'];
			
			if($event->stylesPost['pl'] != "")
				(int)$event->stylesPost['pl'];

			if($event->stylesPost['pl'] != "" && !is_numeric($event->stylesPost['pl'])) // Falls  die Styles (padding) nicht den Regeln entsprechen
				$event->wrongInput[] = 'styles-con' . $i . '_paddingl';
			else
				$event->styles['pl']	= $event->stylesPost['pl'];
		}
		if(isset($GLOBALS['_POST'][$event->conPrefix . '_paddingr'])) {
		
			$event->stylesPost['pr']		= $GLOBALS['_POST'][$event->conPrefix . '_paddingr'];
			
			if($event->stylesPost['pr'] != "")
				(int)$event->stylesPost['pr'];

			if($event->stylesPost['pr'] != "" && !is_numeric($event->stylesPost['pr'])) // Falls  die Styles (padding) nicht den Regeln entsprechen
				$event->wrongInput[] = 'styles-con' . $i . '_paddingr';
			else
				$event->styles['pr']	= $event->stylesPost['pr'];
		}

		// Falls verstecktes Element
		if(isset($GLOBALS['_POST']["elementStatus_".$event->conPrefix]) && $GLOBALS['_POST']["elementStatus_".$event->conPrefix] == 0)
			$event->styles['hide'] = 1;
		else
			$event->styles['hide']	= 0;

		
		// Grid
		// Falls neue Section mit Element beginnen soll
		if(isset($GLOBALS['_POST'][$event->conPrefix . "_newSection"]))
			$event->styles['sec']		= $GLOBALS['_POST'][$event->conPrefix . "_newSection"];
		
		// Section id
		if(isset($GLOBALS['_POST'][$event->conPrefix . '_newSectionID'])) {
		
			$event->styles['secid']	= $GLOBALS['_POST'][$event->conPrefix . '_newSectionID'];
			
		}
		
		// Section class
		if(isset($GLOBALS['_POST'][$event->conPrefix . '_newSectionClass'])) {
		
			$event->styles['secclass']	= $GLOBALS['_POST'][$event->conPrefix . '_newSectionClass'];
			
		}

		// Falls neuer Container mit Element beginnen soll
		if(isset($GLOBALS['_POST'][$event->conPrefix . "_newCtr"]))
			$event->styles['ctr']		= $GLOBALS['_POST'][$event->conPrefix . "_newCtr"];
		
		// Container id
		if(isset($GLOBALS['_POST'][$event->conPrefix . '_newCtrID'])) {
		
			$event->styles['ctrid']	= $GLOBALS['_POST'][$event->conPrefix . '_newCtrID'];
			
		}
		
		// Container class
		if(isset($GLOBALS['_POST'][$event->conPrefix . '_newCtrClass'])) {
		
			$event->styles['ctrclass']	= $GLOBALS['_POST'][$event->conPrefix . '_newCtrClass'];
			
		}

		// Falls neue Row mit Element beginnen soll
		if(isset($GLOBALS['_POST'][$event->conPrefix . "_newRow"]))
			$event->styles['row']		= $GLOBALS['_POST'][$event->conPrefix . "_newRow"];
		
		// Row id
		if(isset($GLOBALS['_POST'][$event->conPrefix . '_newRowID'])) {
		
			$event->styles['rowid']	= $GLOBALS['_POST'][$event->conPrefix . '_newRowID'];
			
		}
		
		// Row class
		if(isset($GLOBALS['_POST'][$event->conPrefix . '_newRowClass'])) {
		
			$event->styles['rowclass']	= $GLOBALS['_POST'][$event->conPrefix . '_newRowClass'];
			
		}

		// Falls Div wrapper um Element
		if(isset($GLOBALS['_POST'][$event->conPrefix . "_newDiv"]))
			$event->styles['div']		= $GLOBALS['_POST'][$event->conPrefix . "_newDiv"];
		
		// Div id
		if(isset($GLOBALS['_POST'][$event->conPrefix . '_newDivID'])) {
		
			$event->styles['divid']	= $GLOBALS['_POST'][$event->conPrefix . '_newDivID'];
			
		}
		
		// Div class
		if(isset($GLOBALS['_POST'][$event->conPrefix . '_newDivClass'])) {
		
			$event->styles['divclass']	= $GLOBALS['_POST'][$event->conPrefix . '_newDivClass'];
			
		}
		
		// Section background color
		if(isset($GLOBALS['_POST'][$event->conPrefix . '_secbgcol'])) {
		
			$event->styles['secbgcol']	= $GLOBALS['_POST'][$event->conPrefix . '_secbgcol'];
			
		}
		
		// Section background image
		if(isset($GLOBALS['_POST'][$event->conPrefix . '_secbgimg'])) {
		
			$event->styles['secbgimg']	= $GLOBALS['_POST'][$event->conPrefix . '_secbgimg'];
			
		}
		
		return true;
	
	}
	
	// onGetSettingsFields
	public function onGetSettingsFields(Event $event)
	{	
	
		return true;
	
	}
	
	// onGetStylesFieldsPre
	public function onGetStylesFieldsPre(Event $event)
	{
	}
	
	// onGetStylesFieldsMid
	public function onGetStylesFieldsMid(Event $event)
	{

		// Element attributes
		$output =	"";
							
		// ID
		$output .=	'<fieldset' . (!empty($event->showFieldset) && $event->showFieldset != "ele" ? ' class="collapsed"' : '') . '>' . PHP_EOL .
					'<legend>Element-Style</legend>' . PHP_EOL .
					'<div class="leftBox">' . PHP_EOL .
					'<label class="cc-group-label">{s_header:attr}</label>' . PHP_EOL .
					'<label>id';
		
		if(in_array('styles-con' . $event->eleCnt . '_id', $event->wrongInput))
			$output .=	'<span class="notice">{s_error:wrongstyle}</span>';
			
		$output .=	'</label>' . PHP_EOL . 
					'<input class="inputEleID" type="text" name="' . $event->conPrefix . '_id" value="' . htmlspecialchars(isset($event->stylesPost['id']) ? $event->stylesPost['id'] : $event->styles['id']) . '" maxlength="255" />' . PHP_EOL;
		$output .=	'</div>' . PHP_EOL;			
		
		// Columns md
		$output .=	'<div class="rightBox">' . PHP_EOL .
					'<label class="cc-group-label">{s_label:colno}</label>' . PHP_EOL .
					'<label>{s_label:colno} (screen)</label>' . PHP_EOL;
		
		$output .=	parent::getGridColumnSelect($event->conPrefix . '_columns', $event->styles['cols']);
		
		$output .=	'</div>' . PHP_EOL;			
		
		// Class
		$output .=	'<div class="leftBox clear">' . PHP_EOL;
		$output .=	'<span class="inline-tags">';
		$output .=	'<label>class';
							
		if(in_array('styles-con' . $event->eleCnt . '_class', $event->wrongInput))
			$output .=	'<span class="notice">{s_error:wrongstyle}</span>';
							
		$output .=	'</label>' . PHP_EOL . 
					'<input class="inputEleClass" type="text" name="' . $event->conPrefix . '_class" value="' . htmlspecialchars(isset($event->stylesPost['class']) ? $event->stylesPost['class'] : $event->styles['class']) . '" maxlength="255" />' . PHP_EOL;

		$output .=	'</span>';
		$output .=	'</div>' . PHP_EOL;			
		
		// Columns sm
		$output .=	'<div class="rightBox">' . PHP_EOL;
		$output .=	'<label>{s_label:colno} (tablet)</label>' . PHP_EOL .
					'<select name="' . $event->conPrefix . '_columns_sm">' .
					'<option value="">auto</option>' .
					'<optgroup label="{s_label:colno}">';

		
		$maxCols	= empty(parent::$styleDefs['fullrowcnt']) ? 12 : parent::$styleDefs['fullrowcnt'];
		
		for($colCount = 1; $colCount <= $maxCols; $colCount++) {
			$output .=	'<option value="' . $colCount . '"' . (is_numeric($event->styles['colssm']) && $event->styles['colssm'] == $colCount ? ' selected="selected"' : '') . '>' . $colCount . ' {s_option:columns}</option>';
		}
							
		$output .=	'</optgroup>' .
					'</select>' . PHP_EOL;
		
		$output .=	'</div>' . PHP_EOL;
		
		// Style
		$output .=	'<div class="leftBox clear">' . PHP_EOL;
		$output .=	'<label>style';
		
		if(in_array('styles-con' . $event->eleCnt . '_styles', $event->wrongInput))
			$output .=	'<span class="notice">{s_error:wrongstyle}</span>';
							
		$output .=	'</label>' . PHP_EOL . 
					'<input class="inputEleStyle" type="text" name="' . $event->conPrefix . '_styles" value="' . htmlspecialchars(isset($event->stylesPost['style']) ? $event->stylesPost['style'] : $event->styles['style']) . '" maxlength="512" />' . PHP_EOL .
					'</div>' . PHP_EOL;			
		
		// Columns xs
		$output .=	'<div class="rightBox">' . PHP_EOL;
		$output .=	'<label>{s_label:colno} (phone)</label>' . PHP_EOL .
					'<select name="' . $event->conPrefix . '_columns_xs">' .
					'<option value="">auto</option>' .
					'<optgroup label="{s_label:colno}">';

		
		$maxCols	= empty(parent::$styleDefs['fullrowcnt']) ? 12 : parent::$styleDefs['fullrowcnt'];
		
		for($colCount = 1; $colCount <= $maxCols; $colCount++) {
			$output .=	'<option value="' . $colCount . '"' . (is_numeric($event->styles['colsxs']) && $event->styles['colsxs'] == $colCount ? ' selected="selected"' : '') . '>' . $colCount . ' {s_option:columns}</option>';
		}
							
		$output .=	'</optgroup>' .
					'</select>' . PHP_EOL .
					'</div>' . PHP_EOL;
		
		$output .=	'<br class="clearfloat" />' . PHP_EOL;
		$output .=	'</fieldset>' . PHP_EOL;												

		
		// margins
		$output .=	'<fieldset' . (!empty($event->showFieldset) && $event->showFieldset != "ele" ? ' class="collapsed"' : '') . '>' . PHP_EOL .
					'<legend>{s_option:margins}</legend>' . PHP_EOL .
					'<div class="leftBox">' . PHP_EOL .
					'<label class="cc-group-label">{s_option:margins} {s_common:outside}</label>' . PHP_EOL .
					'<div>' . PHP_EOL;
		
		// margin l
		$output .=	'<div class="halfBox">' . PHP_EOL .
					'<label>{s_option:margint}';
		
		if(in_array('styles-con' . $event->eleCnt . '_margint', $event->wrongInput))
			$output .=	'<span class="notice">{s_error:wrongstyle}</span>';
			
		$output .=	'</label>' . PHP_EOL . 
					'<input type="text" name="' . $event->conPrefix . '_margint" value="' . htmlspecialchars($event->styles['mt']) . '" class="numSpinner" maxlength="5" />' . PHP_EOL .
					'</div>' . PHP_EOL;
					
		// margin r
		$output .=	'<div class="halfBox">' . PHP_EOL .
					'<label>{s_option:marginr}';
		
		if(in_array('styles-con' . $event->eleCnt . '_marginr', $event->wrongInput))
			$output .=	'<span class="notice">{s_error:wrongstyle}</span>';
			
		$output .=	'</label>' ."\n" .
					'<input type="text" name="' . $event->conPrefix . '_marginr" value="' . htmlspecialchars($event->styles['mr']) . '" class="numSpinner" maxlength="5" />' . PHP_EOL .
					'</div>' . PHP_EOL .
					'<br class="clearL" />' . PHP_EOL;
		
		// margin l
		$output .=	'<div class="halfBox">' . PHP_EOL .
					'<label>{s_option:marginl}';
		
		if(in_array('styles-con' . $event->eleCnt . '_marginl', $event->wrongInput))
			$output .=	'<span class="notice">{s_error:wrongstyle}</span>';
			
		$output .=	'</label>' . PHP_EOL . 
					'<input type="text" name="' . $event->conPrefix . '_marginl" value="' . htmlspecialchars($event->styles['ml']) . '" class="numSpinner" maxlength="5" />' . PHP_EOL .
					'</div>' . PHP_EOL;

		// margin b
		$output .=	'<div class="halfBox">' . PHP_EOL .
					'<label>{s_option:marginb}';
		
		if(in_array('styles-con' . $event->eleCnt . '_marginb', $event->wrongInput))
			$output .=	'<span class="notice">{s_error:wrongstyle}</span>';
			
		$output .=	'</label>' ."\n" .
					'<input type="text" name="' . $event->conPrefix . '_marginb" value="' . htmlspecialchars($event->styles['mb']) . '" class="numSpinner" maxlength="5" />' . PHP_EOL .
					'</div>' . PHP_EOL .
					'</div>' . PHP_EOL .
					'</div>' . PHP_EOL;
		
		// paddings
		$output .=	'<div class="rightBox">' . PHP_EOL .
					'<label class="cc-group-label">{s_option:margins} {s_common:inside}</label>' . PHP_EOL .
					'<div>' . PHP_EOL;
		
		// padding l
		$output .=	'<div class="halfBox">' . PHP_EOL .
					'<label>{s_option:margint}';
		
		if(in_array('styles-con' . $event->eleCnt . '_paddingt', $event->wrongInput))
			$output .=	'<span class="notice">{s_error:wrongstyle}</span>';
			
		$output .=	'</label>' . PHP_EOL . 
					'<input type="text" name="' . $event->conPrefix . '_paddingt" value="' . htmlspecialchars($event->styles['pt']) . '" class="numSpinner" maxlength="5" />' . PHP_EOL .
					'</div>' . PHP_EOL;
					
		// padding r
		$output .=	'<div class="halfBox">' . PHP_EOL .
					'<label>{s_option:marginr}';
		
		if(in_array('styles-con' . $event->eleCnt . '_paddingr', $event->wrongInput))
			$output .=	'<span class="notice">{s_error:wrongstyle}</span>';
			
		$output .=	'</label>' ."\n" .
					'<input type="text" name="' . $event->conPrefix . '_paddingr" value="' . htmlspecialchars($event->styles['pr']) . '" class="numSpinner" maxlength="5" />' . PHP_EOL .
					'</div>' . PHP_EOL .
					'<br class="clearL" />' . PHP_EOL;
		
		// padding l
		$output .=	'<div class="halfBox">' . PHP_EOL .
					'<label>{s_option:marginl}';
		
		if(in_array('styles-con' . $event->eleCnt . '_paddingl', $event->wrongInput))
			$output .=	'<span class="notice">{s_error:wrongstyle}</span>';
			
		$output .=	'</label>' . PHP_EOL . 
					'<input type="text" name="' . $event->conPrefix . '_paddingl" value="' . htmlspecialchars($event->styles['pl']) . '" class="numSpinner" maxlength="5" />' . PHP_EOL .
					'</div>' . PHP_EOL;

		// padding b
		$output .=	'<div class="halfBox">' . PHP_EOL .
					'<label>{s_option:marginb}';
		
		if(in_array('styles-con' . $event->eleCnt . '_paddingb', $event->wrongInput))
			$output .=	'<span class="notice">{s_error:wrongstyle}</span>';
			
		$output .=	'</label>' ."\n" .
					'<input type="text" name="' . $event->conPrefix . '_paddingb" value="' . htmlspecialchars($event->styles['pb']) . '" class="numSpinner" maxlength="5" />' . PHP_EOL .
					'</div>' . PHP_EOL .
					'</div>' . PHP_EOL .
					'</div>' . PHP_EOL;
		
		$output .=	'<br class="clearfloat" />' . PHP_EOL;
		$output .=	'</fieldset>' . PHP_EOL;

		// Grid
		$output .=	'<fieldset' . (!empty($event->showFieldset) && $event->showFieldset != "grid" ? ' class="collapsed"' : '') . '>' . PHP_EOL .
					'<legend>{s_label:grid}</legend>' . PHP_EOL;

		// New Section
		$output .=	'<div class="leftBox">' . PHP_EOL;
		
		$output .=	'<label>' . parent::getIcon("gridsec", "inline-icon") . ' {s_label:newsec}</label>' . PHP_EOL . 
					'<select class="iconSelect" name="' . $event->conPrefix . '_newSection">' . PHP_EOL .
					'<option value="0">{s_option:non}</option>' . PHP_EOL;
					
		foreach($event->sectionTags as $key => $tag) {
			$output .=	'<option value="' . $key . '"' . ($event->styles['sec'] == $key ? ' selected="selected"' : '') . '>{s_option:yesn} &lt;' . $tag . '&gt;</option>' . PHP_EOL;
		}
		$output .=	'<option value="x"' . ($event->styles['sec'] === "x" ? ' selected="selected"' : '') . '>{s_option:closeprev}</option>' . PHP_EOL;
		
		$output .=	'</select>' . PHP_EOL .
					'</div>' . PHP_EOL .
					'<div class="rightBox">' . PHP_EOL .
					'<label>Section id</label>' . PHP_EOL . 
					'<input class="inputSecID" type="text" name="' . $event->conPrefix . '_newSectionID" value="' . htmlspecialchars($event->styles['secid']) . '" />' . PHP_EOL .
					'</div>' . PHP_EOL;
		$output .=	'<div class="rightBox">' . PHP_EOL;
		$output .=	'<span class="inline-tags">';
		$output .=	'<label>class</label>' . PHP_EOL . 
					'<input class="inputSecClass" type="text" name="' . $event->conPrefix . '_newSectionClass" value="' . htmlspecialchars($event->styles['secclass']) . '" />' . PHP_EOL .
					'</span>' . PHP_EOL .
					'</div>' . PHP_EOL;
		
		// New Container
		$output .=	'<div class="leftBox clear">' . PHP_EOL;
		
		$output .=	'<label>' . parent::getIcon("gridctr", "inline-icon") . ' {s_label:newctr}</label>' . PHP_EOL . 
					'<select class="iconSelect" name="' . $event->conPrefix . '_newCtr">' . PHP_EOL .
					'<option value="0">{s_option:non}</option>' . PHP_EOL .
					'<option value="1"' . ($event->styles['ctr'] == "1" ? ' selected="selected"' : '') . '>{s_option:yesn}</option>' . PHP_EOL .
					'<option value="2"' . ($event->styles['ctr'] == "2" ? ' selected="selected"' : '') . '>{s_option:closeprev}</option>' . PHP_EOL .
					'</select>' . PHP_EOL .
					'</div>' . PHP_EOL .
					'<div class="rightBox">' . PHP_EOL .
					'<label>Container id</label>' . PHP_EOL . 
					'<input class="inputCtrID" type="text" name="' . $event->conPrefix . '_newCtrID" value="' . htmlspecialchars($event->styles['ctrid']) . '" />' . PHP_EOL .
					'</div>' . PHP_EOL;
		$output .=	'<div class="rightBox">' . PHP_EOL;
		$output .=	'<span class="inline-tags">';
		$output .=	'<label>class</label>' . PHP_EOL . 
					'<input class="inputCtrClass" type="text" name="' . $event->conPrefix . '_newCtrClass" value="' . htmlspecialchars($event->styles['ctrclass']) . '" />' . PHP_EOL .
					'</span>' . PHP_EOL .
					'</div>' . PHP_EOL;
		
		// New Row
		$output .=	'<div class="leftBox clear">' . PHP_EOL;
		$output .=	'<label>' . parent::getIcon("gridrow", "inline-icon") . ' {s_label:newrow}</label>' . PHP_EOL . 
					'<select class="iconSelect" name="' . $event->conPrefix . '_newRow">' . PHP_EOL .
					'<option value="0">{s_option:non}</option>' . PHP_EOL .
					'<option value="1"' . ($event->styles['row'] == "1" ? ' selected="selected"' : '') . '>{s_option:yesn}</option>' . PHP_EOL .
					'<option value="2"' . ($event->styles['row'] == "2" ? ' selected="selected"' : '') . '>{s_option:closeprev}</option>' . PHP_EOL .
					'</select>' . PHP_EOL .
					'</div>' . PHP_EOL .
					'<div class="rightBox">' . PHP_EOL .
					'<label>Row id</label>' . PHP_EOL . 
					'<input class="inputRowID" type="text" name="' . $event->conPrefix . '_newRowID" value="' . htmlspecialchars($event->styles['rowid']) . '" />' . PHP_EOL .
					'</div>' . PHP_EOL;
		$output .=	'<div class="rightBox">' . PHP_EOL;
		$output .=	'<span class="inline-tags">';
		$output .=	'<label>class</label>' . PHP_EOL . 
					'<input class="inputRowClass" type="text" name="' . $event->conPrefix . '_newRowClass" value="' . htmlspecialchars($event->styles['rowclass']) . '" />' . PHP_EOL .
					'</span>' . PHP_EOL .
					'</div>' . PHP_EOL;
		
		// Div wrapper
		$output .=	'<div class="leftBox clear">' . PHP_EOL;
		$output .=	'<label>' . parent::getIcon("griddiv", "inline-icon") . ' {s_label:newdiv}</label>' . PHP_EOL . 
					'<select class="iconSelect" name="' . $event->conPrefix . '_newDiv">' . PHP_EOL .
					'<option value="0">{s_option:non}</option>' . PHP_EOL .
					'<option value="1"' . ($event->styles['div'] ? ' selected="selected"' : '') . '>{s_option:yesn}</option>' . PHP_EOL .
					'</select>' . PHP_EOL .
					'</div>' . PHP_EOL .
					'<div class="rightBox">' . PHP_EOL .
					'<label>Div id</label>' . PHP_EOL . 
					'<input class="inputDivID" type="text" name="' . $event->conPrefix . '_newDivID" value="' . htmlspecialchars($event->styles['divid']) . '" />' . PHP_EOL .
					'</div>' . PHP_EOL;
		$output .=	'<div class="rightBox">' . PHP_EOL;
		$output .=	'<label>class</label>' . PHP_EOL; 
		$output .=	'<span class="inline-tags">';
		$output .=	'<input class="inputDivClass" type="text" name="' . $event->conPrefix . '_newDivClass" value="' . htmlspecialchars($event->styles['divclass']) . '" />' . PHP_EOL .
					'</span>' . PHP_EOL .
					'</div>' . PHP_EOL;
					
		$output .=	'<br class="clearfloat" />' . PHP_EOL;
		$output .=	'</fieldset>' . PHP_EOL;

		// Section background
		$output .=	'<fieldset' . ($event->showFieldset != "secbg" || (empty($event->styles['sec']) || $event->styles['sec'] === "x") ? ' class="collapsed"' : '')  . '>' . PHP_EOL .
					'<legend>{s_label:section} {s_label:background}</legend>' . PHP_EOL .
					'<div class="leftBox">' . PHP_EOL;
		
		$output .=	'<label>{s_label:bgcol}</label>' . PHP_EOL . 
					'<input type="text" class="color-picker-input no-alpha" name="' . $event->conPrefix . '_secbgcol" value="' . htmlspecialchars($event->styles['secbgcol']) . '" placeholder="transparent" />' . PHP_EOL .
					'</div>' . PHP_EOL;
		$output .=	'<div class="rightBox">' . PHP_EOL;
		
							
		// bg image
		$output	.=	$this->getSectionImage($event->styles['secbgimg'], $event->conPrefix);
					
		$output .=	'</div>' . PHP_EOL;
		
		$output .=	'<br class="clearfloat" />' . PHP_EOL;
		$output .=	'</fieldset>' . PHP_EOL;
		
		// Reassing pot error
		if(in_array($event->conPrefix, $this->wrongInput))
			$event->wrongInput[]	= $event->conPrefix;
		
		return $event->addOutput($output);
		
	}
	
	// onGetStylesFieldsPost
	public function onGetStylesFieldsPost(Event $event)
	{
	}

} // Ende class
