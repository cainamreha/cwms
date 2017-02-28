<?php
namespace Concise;


########################
########  Cards  ########
########################

/**
 * CardsConfigElement class
 * 
 * content type => cards
 */
class CardsConfigElement extends ConfigElementFactory implements ConfigElements
{

	private	$cardContent_h			= array(1 => "<h3>card-1</h3>");
	private	$cardContent_con		= array(1 => "");
	private	$cardContent_f			= array(1 => "");
	private	$cardContent_img		= array(1 => "");
	private	$cardContent_img_align	= array(1 => "");
	private	$cardContent_img_link	= array(1 => "");
	private	$cardContent_col		= array(1 => "third");
	private	$imgFile				= array(1 => "");
	private	$imgPath				= array(1 => "");
	private	$imgSrc					= array(1 => "");
	private	$img_Src				= array(1 => "");
	private	$phImage				= "";
	private	$errorImg				= array();
	private	$cardContent_align		= array(1 => "");
	private	$cardContent_id			= array(1 => "");
	private	$cardContent_class		= array(1 => "");
	private	$cardFormat				= array(1 => "");
	private	$cardStyle				= array(1 => "");
	private	$cardFormatAll			= "";
	private	$cardStyleAll			= "";
	private	$cardDisplayAll			= "";
	private	$cardHoverAll			= "";

	/**
	 * Gibt ein CardsConfigElement zurück
	 * 
	 * @access	public
	 * @param	string	$options	Parameter-Array (Wert, Styles)
	 * @param	string	$DB			DB-Objekt
	 * @param	string	$o_lng		Sprach-Objekt
	 */
	public function __construct($options, $DB, &$o_lng)
	{
	
		$this->DB				= $DB;
		$this->o_lng			= $o_lng;

		$this->conType			= $options["conType"];
		$this->conValue			= $options["conValue"];
		$this->conAttributes	= $options["conAttributes"];
		$this->conNum			= $options["conNum"];
		
		// Card image
		$this->phImage			= SYSTEM_IMAGE_DIR . '/noimage.png';
		$this->imgSrc[1]		= $this->phImage;
		$this->img_Src[1]		= $this->imgSrc[1];
		
	}

	
	public function getConfigElement($a_POST)
	{
	
		$this->scriptTag		= $this->getSortScript('sortableCards-' . $this->conPrefix);

		$this->a_POST	= $a_POST;
		$this->params	= (array)json_decode($this->conValue, true);
		
		
		// Parameter (default) setzen
		$this->setParams();

		
		// Ggf. POST auslesen
		if($this->a_POST != null)
			$this->evalElementPost();
		
		
		// DB-Updatestr generieren
		$this->makeUpdateStr();
		
		
		// Parameter (img) setzen
		$this->setImgParams();

		
		// Element-Formular generieren
		$this->output		= $this->getCreateElementHtml();
		
		
		// Ausgabe-Array erstellen und zurückgeben
		return $this->makeOutputArray();
		
	}
	
	
	// setParams, Parameter (default) setzen
	public function setParams()
	{

		$this->scriptFiles["concards"] = "system/access/js/cards.min.js";

		if(count($this->params) > 1) {
			
			$this->cardContent_h			= isset($this->params[0]) ? $this->params[0] : array();
			$this->cardContent_con			= isset($this->params[1]) ? $this->params[1] : array();
			$this->cardContent_f			= isset($this->params[2]) ? $this->params[2] : array();
			$this->cardContent_img			= isset($this->params[3]) ? $this->params[3] : array();
			$this->cardContent_img_align	= isset($this->params[4]) ? $this->params[4] : array();
			$this->cardContent_img_link		= isset($this->params[5]) ? $this->params[5] : array();
			$this->cardContent_col			= isset($this->params[6]) ? $this->params[6] : array();
			$this->cardStyle				= isset($this->params[7]) ? $this->params[7] : array();
			$this->cardFormat				= isset($this->params[8]) ? $this->params[8] : array();
			$this->cardContent_align		= isset($this->params[9]) ? $this->params[9] : array();
			$this->cardContent_id			= isset($this->params[10]) ? $this->params[10] : array();
			$this->cardContent_class		= isset($this->params[11]) ? $this->params[11] : array();

		}
		
		if(!isset($this->params[12]))
			$this->cardFormatAll	= "";
		else
			$this->cardFormatAll	= $this->params[12];
		if(!isset($this->params[13]))
			$this->cardStyleAll		= "";
		else
			$this->cardStyleAll		= $this->params[13];
		if(!isset($this->params[14]))
			$this->cardDisplayAll	= "";
		else
			$this->cardDisplayAll	= $this->params[14];
		if(!isset($this->params[15]))
			$this->cardHoverAll		= "";
		else
			$this->cardHoverAll		= $this->params[15];

	}
	
	
	// evalElementPost
	public function evalElementPost()
	{
	
		if(isset($this->a_POST[$this->conPrefix . '_h'])) { // Falls das Formular abgeschickt wurde
			
			$this->cardContent_h			= array();
			$this->cardContent_con			= array();
			$this->cardContent_f			= array();
			$this->cardContent_img			= array();
			$this->cardContent_img_align	= array();
			$this->cardContent_img_link		= array();
			$this->cardContent_col			= array();
			$this->cardStyle				= array();
			$this->cardFormat				= array();
			$this->cardContent_align		= array();
			$this->cardContent_id			= array();
			$this->cardContent_class		= array();

			$tc	= 1;
			
			// Reiterbeschriftungen / Inhalte
			foreach($this->a_POST[$this->conPrefix . '_h'] as $key => $val) {

				// Header
				$this->cardContent_h[$tc] 	= trim($val);
			
				// Body
				$this->cardContent_con[$tc]	= $this->a_POST[$this->conPrefix . '_con'][$key];
			
				// Footer
				$this->cardContent_f[$tc] 	= trim($this->a_POST[$this->conPrefix . '_f'][$key]);
			
				// Image
				$this->cardContent_img[$tc]	= $this->a_POST[$this->conPrefix . '_img'][$key];
			
				// Img align
				$this->cardContent_img_align[$tc]	= $this->a_POST[$this->conPrefix . '_img_align'][$key];
			
				// Img link
				$this->cardContent_img_link[$tc]	= $this->a_POST[$this->conPrefix . '_img_link'][$key];
			
				// Grid
				$this->cardContent_col[$tc]	= $this->a_POST[$this->conPrefix . '_cards_cols'][$key];
			
				// Card style
				$this->cardStyle[$tc]	= $this->a_POST[$this->conPrefix . '_cardStyle'][$key];
			
				// Card format
				$this->cardFormat[$tc]	= $this->a_POST[$this->conPrefix . '_cardFormat'][$key];
			
				// Img align
				$this->cardContent_align[$tc]	= $this->a_POST[$this->conPrefix . '_align'][$key];
			
				// Card id
				$this->cardContent_id[$tc]	= $this->a_POST[$this->conPrefix . '_card_id'][$key];
			
				// Card class
				$this->cardContent_class[$tc]	= $this->a_POST[$this->conPrefix . '_card_class'][$key];
				
				
				// Pfade durch Platzhalter ersetzen
				$rootPH		= "{#root}";
				$rootImgPH	= "{#root}/{#root_img}";
				
				$this->cardContent_con[$tc] = preg_replace("~".str_replace("/", "\/", PROJECT_HTTP_ROOT)."\/".str_replace("/", "\/", IMAGE_DIR)."~isU", $rootImgPH, $this->cardContent_con[$tc]);
				$this->cardContent_con[$tc] = preg_replace("~".str_replace("/", "\/", PROJECT_HTTP_ROOT)."~isU", $rootPH, $this->cardContent_con[$tc]);
			
				$tc++;
			}
			
			$this->cardFormatAll	= $this->a_POST[$this->conPrefix . '_cardFormatAll'];
			$this->cardStyleAll		= $this->a_POST[$this->conPrefix . '_cardStyleAll'];
			$this->cardDisplayAll	= $this->a_POST[$this->conPrefix . '_cardDisplayAll'];
			$this->cardHoverAll		= $this->a_POST[$this->conPrefix . '_cardHoverAll'];
		
			// Reassign vars
			$this->params[0]	= $this->cardContent_h;
			$this->params[1]	= $this->cardContent_con;
			$this->params[2]	= $this->cardContent_f;
			$this->params[3]	= $this->cardContent_img;
			$this->params[4]	= $this->cardContent_img_align;
			$this->params[5]	= $this->cardContent_img_link;
			$this->params[6]	= $this->cardContent_col;
			$this->params[7]	= $this->cardStyle;
			$this->params[8]	= $this->cardFormat;
			$this->params[9]	= $this->cardContent_align;
			$this->params[10]	= $this->cardContent_id;
			$this->params[11]	= $this->cardContent_class;
			$this->params[12]	= $this->cardFormatAll;
			$this->params[13]	= $this->cardStyleAll;
			$this->params[14]	= $this->cardDisplayAll;
			$this->params[15]	= $this->cardHoverAll;

		}
	
	}
	
	
	// setImgParams
	public function setImgParams()
	{
				
		// Pfade durch Platzhalter ersetzen
		$rootPH		= "{#root}";
		$rootImgPH	= "{#root}/{#root_img}";
		
		foreach($this->cardContent_con as $key => $cardCon) {
		
			$this->cardContent_con[$key] = str_replace($rootImgPH, PROJECT_HTTP_ROOT . '/' . IMAGE_DIR, $cardCon);
			$this->cardContent_con[$key] = str_replace($rootPH, PROJECT_HTTP_ROOT, $this->cardContent_con[$key]);
		
			// Pfad zur Bilddatei
			$this->imgFile[$key]	= Modules::getImagePath($this->cardContent_img[$key]);
			$basename				= basename($this->imgFile[$key]);

			$this->img_Src[$key]	= PROJECT_HTTP_ROOT . '/' . $this->imgFile[$key];
			$this->imgSrc[$key]		= str_replace($basename, 'thumbs/' . $basename, $this->img_Src[$key]);
			$this->imgPath[$key]	= PROJECT_HTTP_ROOT . '/' . CC_IMAGE_FOLDER . '/';
			
			// Falls noch kein Bild ausgewählt
			if($this->cardContent_img[$key] == "") {
				$this->imgSrc[$key]		= $this->phImage;
				$this->img_Src[$key]	= $this->imgSrc[$key];
			}

			// Falls Bild nicht vorhanden
			elseif(!file_exists(PROJECT_DOC_ROOT . '/' . $this->imgFile[$key])){
				$this->imgSrc[$key]		= $this->phImage;
				$this->img_Src[$key]	= $this->imgSrc[$key];
				
				$this->wrongInput[] 	= $this->conPrefix;
				$this->errorImg[$key]	= "{s_javascript:confirmreplace1}" . $this->imgFile[$key] . "&quot; {s_text:notexist}.";
			}
			else
				$this->imgPath[$key]	= pathinfo($this->img_Src[$key], PATHINFO_DIRNAME);
		}
	
	}
	
	
	// makeUpdateStr
	public function makeUpdateStr()
	{
		
		// db-Updatestring
		$this->dbUpdateStr  = "'";
		$this->dbUpdateStr .= $this->DB->escapeString(json_encode($this->params));
		$this->dbUpdateStr .= "',";

	}
	
	
	// makeUpdateStr
	public function getCreateElementHtml()
	{
	
		$output	 = '<h4 class="cc-contype-heading cc-h4">{s_option:' . $this->conType . '}</h4>' . PHP_EOL;

		if(in_array($this->conPrefix, $this->wrongInput)) // Falls im Fehlerarray vorhanden Meldung ausgeben
			$output	.= '<span class="notice">' . $this->error . '</span>' . PHP_EOL;

		$output .=	'<fieldset>' . PHP_EOL .
					'<legend>{s_option:' . $this->conType . '}</legend>' . PHP_EOL;

		$output	.= '<ul id="sortableCards-' . $this->conPrefix . '" class="setupCards sortableCards sortable">' . PHP_EOL;

		$i	= 1;
		
		foreach($this->cardContent_h as $key => $val) {
			
			$this->textAreaCount++; // TinyMCE-Zähler erhöhen
			
			$output	.= '<li class="cardEntry cc-groupitem-entry">' . PHP_EOL;
			
			// Registerüberschriften
			$output	.=	'<span class="listEntryHeader actionHeader has-panel-left toggleNext hideNext">' .
						'<span class="editButtons-panel panel-left">' . PHP_EOL;
			
			// Button move
			$btnDefs	= array(	"type"		=> "button",
									"class"		=> 'sortCard button-icon-only button-small move',
									"title"		=> '{s_title:move}',
									"icon"		=> "move"
								);
				
			$output .=	parent::getButton($btnDefs);
						
			$output .=	'</span>' . PHP_EOL;
			
			$output	.=	'Card ' . $key . PHP_EOL .
						'<span class="editButtons-panel">' . PHP_EOL;
			
			// Button remove card
			$btnDefs	= array(	"type"		=> "button",
									"class"		=> 'removeCard button-icon-only',
									"title"		=> '{s_title:delete}',
									"icon"		=> "delete"
								);
				
			$output .=	parent::getButton($btnDefs);
						
			$output .=	'</span>' . PHP_EOL .
						'</span>' . PHP_EOL;
			
			$output .=	'<ul class="">' . PHP_EOL;
			
			// Card header
			$output .=	'<li><label class="cardHeader-label cc-groupitem-content-label toggleEditor toggle' . ($this->cardContent_h[$key] != "" ? ' busy' : '') . '" data-target="' . $this->conPrefix . '-cardHeader' . $this->textAreaCount . '">Header<span class="editLangFlag">' . $this->editLangFlag . '</span></label>' . PHP_EOL .
						#'<input class="cardHeader" type="text" name="' . $this->conPrefix . '_h[]" value="' . htmlspecialchars($val) . '" />' . PHP_EOL;
						'<textarea name="' . $this->conPrefix . '_h[]" id="' . $this->conPrefix . '-cardHeader' . $this->textAreaCount . '" class="cardHeader disableEditor forceSave hide cc-editor-add cc-always-hide cc-editor-small teaser" data-index="' . $this->textAreaCount . '">' . $this->cardContent_h[$key] . '</textarea></li>' . PHP_EOL;
			
			$this->textAreaCount++; // TinyMCE-Zähler erhöhen

			// Card body
			$output	.=	'<label class="cardContent-label cc-groupitem-content-label toggleEditor toggle' . ($this->cardContent_con[$key] != "" ? ' busy' : '') . '" data-target="' . $this->conPrefix . '-cardCon' . $this->textAreaCount . '">Body {s_header:contents}<span class="editLangFlag">' . $this->editLangFlag . '</span></label>' . PHP_EOL .
						'<textarea name="' . $this->conPrefix . '_con[]" id="' . $this->conPrefix . '-cardCon' . $this->textAreaCount . '" class="cardContent disableEditor forceSave hide cc-editor-add cc-always-hide" data-index="' . $this->textAreaCount . '">' . $this->cardContent_con[$key] . '</textarea>' . PHP_EOL;
			
			$this->textAreaCount++; // TinyMCE-Zähler erhöhen
			
			// Card footer
			$output .=	'<label class="cardHeader-label cc-groupitem-content-label toggleEditor toggle' . ($this->cardContent_f[$key] != "" ? ' busy' : '') . '" data-target="' . $this->conPrefix . '-cardFooter' . $this->textAreaCount . '">Footer<span class="editLangFlag">' . $this->editLangFlag . '</span></label>' . PHP_EOL .
						#'<input class="cardHeader" type="text" name="' . $this->conPrefix . '_f[]" value="' . htmlspecialchars($this->cardContent_f[$key]) . '" />' . PHP_EOL;
						'<textarea name="' . $this->conPrefix . '_f[]" id="' . $this->conPrefix . '-cardFooter' . $this->textAreaCount . '" class="cardHeader disableEditor forceSave hide cc-editor-add cc-always-hide cc-editor-small teaser" data-index="' . $this->textAreaCount . '">' . $this->cardContent_f[$key] . '</textarea>' . PHP_EOL;
			
			// Card image
			$output	.=	'<label class="cardHeader-label cc-groupitem-content-label toggle hideNext' . ($this->cardContent_img[$key] != "" ? ' busy' : '') . '" data-toggle="' . $this->conPrefix . '-cardImg' . $i . '">{s_label:image}</label>' . PHP_EOL;
			$output .=	'<div id="' . $this->conPrefix . '-cardImg' . $i . '" class="cardImgBox">' . PHP_EOL;
			$output .=	'<div class="leftBox">' . PHP_EOL;
			$output	.=	'<div class="fileSelBox clearfix">' . PHP_EOL;
			$output .=	'<div class="existingFileBox">' . PHP_EOL;
						
						
			
			if(!empty($this->errorImg[$key]))
				$output	.=	'<span class="notice error">' . $this->errorImg[$key] . '</span>' . PHP_EOL;
			
			
			// Btn Reset bg
			$mediaListButtonDef		= array(	"type"		=> "button",
												"class"		=> "button-icon-only button-small right",
												"text"		=> "",
												"value"		=> "{s_button:reset}",
												"title"		=> "{s_button:reset}",
												"attr"		=> 'onclick="$(this).closest(\'.existingFileBox\').find(\'.existingFile\').val(\'\');$(this).closest(\'.existingFileBox\').find(\'.preview\').attr(\'src\',\'' . $this->phImage . '\').attr(\'data-img-src\',\'' . $this->phImage . '\'); $(this).siblings(\'.cardImage\').html(\'{s_label:choosefile}\');"',
												"icon"		=> "delete"
											);
			
			$btnResetImg 	=	$this->getButton($mediaListButtonDef);

			
			$output	.=	'<label>{s_label:image}</label>' . PHP_EOL;
			$output .=	'<label class="elementsFileName"><span class="cardImage">' . (!$this->cardContent_img[$key] ? "{s_label:choosefile}" : htmlspecialchars($this->cardContent_img[$key])) . '</span>' . $btnResetImg . '</label>' . PHP_EOL .
						'<div class="previewBox img">' . PHP_EOL .
						'<img src="' . $this->imgSrc[$key] . '" data-img-src="' . $this->img_Src[$key] . '" class="preview" alt="' . htmlspecialchars($this->cardContent_img[$key]) . '" title="' . htmlspecialchars($this->cardContent_img[$key]) . '" />' . PHP_EOL . 
						'</div>' . PHP_EOL;
			
			// Images MediaList-Button
			$mediaListButtonDef		= array(	"class"	 	=> "images",
												"type"		=> "images",
												"url"		=> SYSTEM_HTTP_ROOT . "/access/listMedia.php?page=admin&type=images",
												"path"		=> $this->imgPath[$key] . 'thumbs/',
												"value"		=> "{s_button:imgfolder}",
												"icon"		=> "images"
											);
			
			$output 	.=	$this->getButtonMediaList($mediaListButtonDef);

			$output 	.=	'<input class="existingFile cardHeader" type="text" name="' . $this->conPrefix . '_img[]" value="' . htmlspecialchars($this->cardContent_img[$key]) . '" readonly="readonly" />' . PHP_EOL .
							'</div>' . PHP_EOL;

			$output	.= '</div>' . PHP_EOL;
			$output	.= '</div>' . PHP_EOL;
			
			// Img align
			$output .=	'<div class="rightBox">' . PHP_EOL .
						'<label>{s_common:alignment}</label>' . PHP_EOL . 
						'<select name="' . $this->conPrefix . '_img_align[]" class="selImgAlign">' .
						'<option value="top"' . ($this->cardContent_img_align[$key] == "top" ? ' selected="selected"' : '') . '>{s_common:top}</option>' . PHP_EOL .
						'<option value="mid"' . ($this->cardContent_img_align[$key] == "mid" ? ' selected="selected"' : '') . '>{s_common:middle}</option>' . PHP_EOL .
						'<option value="bot"' . ($this->cardContent_img_align[$key] == "bot" ? ' selected="selected"' : '') . '>{s_common:bottom}</option>' . PHP_EOL .
						'<option value="ovl"' . ($this->cardContent_img_align[$key] == "ovl" ? ' selected="selected"' : '') . '>{s_label:background}</option>' . PHP_EOL .
						'</select>' . PHP_EOL;
		
			// Img link
			$output .=	'<label>Link</label>' . PHP_EOL;			
			$output .=	'<input type="text" name="' . $this->conPrefix . '_img_link[]" value="' . $this->cardContent_img_link[$key] . '" />' . PHP_EOL;

			$output	.=	'<div class="fieldBox cc-box-info clearfix"><label>{s_text:chooselink} {s_common:or} {s_text:chooselink2}</label></div>' . PHP_EOL;
			
			// Link MediaList-Button
			$mediaListButtonDef		= array(	"class"	 	=> "links",
												"type"		=> "links",
												"url"		=> SYSTEM_HTTP_ROOT . "/access/listPages.php?page=admin&type=link",
												"value"		=> "Links {s_label:intern}",
												"icon"		=> "page"
											);
			
			$output .=	$this->getButtonMediaList($mediaListButtonDef);
			
			$output .=	'</div>' . PHP_EOL;
			
			$output .=	'<br class="clearfloat" /><br />' . PHP_EOL;
			
			$output .=	'</div>' . PHP_EOL;
			
			// Grid
			$output .=	'<div class="leftBox">' . PHP_EOL .
						'<label>{s_label:colno}</label>' . PHP_EOL;
			
			$output .=	parent::getGridColumnSelect($this->conPrefix . '_cards_cols[]', $this->cardContent_col[$key]);
			
			$output	.= '</div>' . PHP_EOL;
			
			// Card style
			$output .=	'<div class="rightBox">' . PHP_EOL . 
						'<label>{s_common:style}</label>' . PHP_EOL . 
						'<select name="' . $this->conPrefix . '_cardStyle[]" class="selCardStyle">' .
						'<option value="">{s_common:non}</option>' . PHP_EOL .
						'<option value="def"' . ($this->cardStyle[$key] == "def" ? ' selected="selected"' : '') . '>Default</option>' . PHP_EOL .
						'<option value="pri"' . ($this->cardStyle[$key] == "pri" ? ' selected="selected"' : '') . '>Primary</option>' . PHP_EOL .
						'<option value="suc"' . ($this->cardStyle[$key] == "suc" ? ' selected="selected"' : '') . '>Success</option>' . PHP_EOL .
						'<option value="inf"' . ($this->cardStyle[$key] == "inf" ? ' selected="selected"' : '') . '>Info</option>' . PHP_EOL .
						'<option value="war"' . ($this->cardStyle[$key] == "war" ? ' selected="selected"' : '') . '>Warning</option>' . PHP_EOL .
						'<option value="dan"' . ($this->cardStyle[$key] == "dan" ? ' selected="selected"' : '') . '>Danger</option>' . PHP_EOL .
						'</select>' . PHP_EOL .
						'</div>' . PHP_EOL .
						'<br class="clearfloat" />' . PHP_EOL;
			
			// Text align
			$output .=	'<div class="leftBox">' . PHP_EOL .
						'<label>{s_common:alignment}</label>' . PHP_EOL . 
						'<select name="' . $this->conPrefix . '_align[]" class="selCardAlign">' .
						'<option value="center"' . ($this->cardContent_align[$key] == "center" ? ' selected="selected"' : '') . '>{s_common:centered}</option>' . PHP_EOL .
						'<option value="left"' . ($this->cardContent_align[$key] == "left" ? ' selected="selected"' : '') . '>{s_common:left}</option>' . PHP_EOL .
						'<option value="right"' . ($this->cardContent_align[$key] == "right" ? ' selected="selected"' : '') . '>{s_common:right}</option>' . PHP_EOL .
						'</select>' . PHP_EOL;
			$output .=	'</div>' . PHP_EOL;
			
			// Card format
			$output .=	'<div class="rightBox">' . PHP_EOL .
						'<label>{s_form:format}</label>' . PHP_EOL . 
						'<select name="' . $this->conPrefix . '_cardFormat[]" class="selCardFormat">' .
						'<option value="">{s_option:default}</option>' . PHP_EOL .
						'<option value="inv"' . ($this->cardFormat[$key] == "inv" ? ' selected="selected"' : '') . '>inverse</option>' . PHP_EOL .
						'<option value="outl"' . ($this->cardFormat[$key] == "outl" ? ' selected="selected"' : '') . '>outline</option>' . PHP_EOL .
						'</select>' . PHP_EOL;
			$output .=	'</div>' . PHP_EOL;
			
			$output .=	'<br class="clearfloat" />' . PHP_EOL;
			
			// Card ID
			$output .=	'<div class="leftBox">' . PHP_EOL .
						'<label>ID</label>' . PHP_EOL . 
						'<input type="text" name="' . $this->conPrefix . '_card_id[]" value="' . htmlspecialchars($this->cardContent_id[$key]) . '">';
			$output .=	'</div>' . PHP_EOL;
			
			// Card class
			$output .=	'<div class="rightBox">' . PHP_EOL .
						'<label>Class</label>' . PHP_EOL . 
						'<input type="text" name="' . $this->conPrefix . '_card_class[]" value="' . htmlspecialchars($this->cardContent_class[$key]) . '">';
			$output .=	'</div>' . PHP_EOL;
			
			$output .=	'<br class="clearfloat" />' . PHP_EOL;
			
			$output .=	'</ul>' . PHP_EOL;
			
			$output	.= '</li>' . PHP_EOL;
			
			$i++;
		
		}

		$output	.= '</ul>' . PHP_EOL;

		// Button für weitere Cards
		$output	.=	'<div class="addCards cc-groupitem-add buttonPanel">' . PHP_EOL;
		
		// Button new card
		$btnDefs	= array(	"type"		=> "button",
								"class"		=> 'newTab button-icon-only button-small',
								"title"		=> '{s_title:addtab}',
								"icon"		=> "new"
							);
			
		$output .=	parent::getButton($btnDefs);
		
		$output .=	'<br class="clearfloat" />' . PHP_EOL .
					'</div>' . PHP_EOL;
		$output .=	'</fieldset>' . PHP_EOL;
		

		$output .=	'<fieldset>' . PHP_EOL .
					'<legend>{s_form:format}</legend>' . PHP_EOL;
		
		// Format
		$output .=	'<div class="leftBox">' . PHP_EOL . 
					'<label>{s_form:format}</label>' . PHP_EOL . 
					'<select name="' . $this->conPrefix . '_cardFormatAll" id="' . $this->conPrefix . '_cardFormatAll" class="selListFormat">' .
					'<option value="">{s_option:default}</option>' . PHP_EOL .
					'<option value="group"' . ($this->cardFormatAll == "group" ? ' selected="selected"' : '') . '>Group</option>' . PHP_EOL .
					'<option value="deck"' . ($this->cardFormatAll == "deck" ? ' selected="selected"' : '') . '>Deck</option>' . PHP_EOL .
					'<option value="cols"' . ($this->cardFormatAll == "cols" ? ' selected="selected"' : '') . '>Columns</option>' . PHP_EOL .
					'</select>' . PHP_EOL .
					'</div>' . PHP_EOL;
		
		// Style
		$output .=	'<div class="rightBox">' . PHP_EOL . 
					'<label>{s_common:style}</label>' . PHP_EOL . 
					'<select name="' . $this->conPrefix . '_cardStyleAll" id="' . $this->conPrefix . '_cardStyleAll" class="selListStyle">' .
					'<option value="">{s_common:non}</option>' . PHP_EOL .
					'<option value="def"' . ($this->cardStyleAll == "def" ? ' selected="selected"' : '') . '>Default</option>' . PHP_EOL .
					'<option value="pri"' . ($this->cardStyleAll == "pri" ? ' selected="selected"' : '') . '>Primary</option>' . PHP_EOL .
					'<option value="suc"' . ($this->cardStyleAll == "suc" ? ' selected="selected"' : '') . '>Success</option>' . PHP_EOL .
					'<option value="inf"' . ($this->cardStyleAll == "inf" ? ' selected="selected"' : '') . '>Info</option>' . PHP_EOL .
					'<option value="war"' . ($this->cardStyleAll == "war" ? ' selected="selected"' : '') . '>Warning</option>' . PHP_EOL .
					'<option value="dan"' . ($this->cardStyleAll == "dan" ? ' selected="selected"' : '') . '>Danger</option>' . PHP_EOL .
					'</select>' . PHP_EOL .
					'</div>' . PHP_EOL;
		
		// Body format
		$output .=	'<div class="leftBox">' . PHP_EOL . 
					'<label>Body {s_form:format}</label>' . PHP_EOL . 
					'<select name="' . $this->conPrefix . '_cardDisplayAll" id="' . $this->conPrefix . '_cardDisplayAll" class="selDisplayFormat">' .
					'<option value="">{s_option:default}</option>' . PHP_EOL .
					'<option value="ovl"' . ($this->cardDisplayAll == "ovl" ? ' selected="selected"' : '') . '>Overlay</option>' . PHP_EOL .
					'</select>' . PHP_EOL .
					'</div>' . PHP_EOL;
		
		// Display type
		$output .=	'<div class="rightBox">' . PHP_EOL . 
					'<label>Body {s_form:format} (hover)</label>' . PHP_EOL . 
					'<select name="' . $this->conPrefix . '_cardHoverAll" id="' . $this->conPrefix . '_cardHoverAll" class="selHoverFormat">' .
					'<option value="">{s_option:default}</option>' . PHP_EOL .
					'<option value="hov"' . ($this->cardHoverAll == "hov" ? ' selected="selected"' : '') . '>Hover</option>' . PHP_EOL .
					'</select>' . PHP_EOL .
					'</div>' . PHP_EOL;

		$output .=	'<br class="clearfloat" />' . PHP_EOL;
		$output .=	'</fieldset>' . PHP_EOL;

		return $output;
	
	}

	
	// getSortScript
	protected function getSortScript($cardsID)
	{
	
		return	'<script>' . PHP_EOL .
				'head.ready("ui", function(){' . PHP_EOL .
				'head.load({sort:"' . SYSTEM_HTTP_ROOT . '/access/js/adminSort.min.js"}, function(){' . PHP_EOL .
				'$(document).ready(function(){' . PHP_EOL .
					'$.sortableCards("#' . $cardsID . '");' . PHP_EOL .
				'});' . PHP_EOL .
				'});' . PHP_EOL .
				'});' . PHP_EOL .
				'</script>' . PHP_EOL;
	
	}

}
