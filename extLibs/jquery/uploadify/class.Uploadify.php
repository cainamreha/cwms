<?php
namespace Concise;


/**
 * Klasse Uploadify
 *
 */

class Uploadify extends FileUploaderFactory implements FileUploaderInterface
{

	private static $uploadMethod	= "uploadify";
	private $allowedFiles			= array();
	private $allowedFileSizeStr		= "";
	private $forcedFileCat			= "";
	private $overwrite				= false;
	private $scaleImg				= false;
	private $imgWidth				= 0;
	private $imgHeight				= 0;
	private $folder					= "";
	private $useFilesFolder			= false;
	
	/**
	 * Gibt ein FormdataConfigElement zurück
	 * 
	 * @access	public
	 * @param	string	$options	Parameter-Array (Wert, Styles)
	 * @param	string	$DB			DB-Objekt
	 * @param	string	$o_lng		Sprach-Objekt
	 */
	public function __construct($options, $DB, $o_lng)
	{
	
		$this->DB					= $DB;
		$this->o_lng				= $o_lng;

		$this->allowedFiles			= isset($options["allowedFiles"])		? $options["allowedFiles"] : array();
		$this->allowedFileSizeStr	= isset($options["allowedFileSizeStr"])	? $options["allowedFileSizeStr"] : "";
		$this->forcedFileCat		= isset($options["forcedFileCat"])		? $options["forcedFileCat"] : "";
		$this->overwrite			= isset($options["overwrite"])			? $options["overwrite"] : false;
		$this->scaleImg				= isset($options["scaleImg"])			? $options["scaleImg"] : false;
		$this->imgWidth				= isset($options["imgWidth"])			? $options["imgWidth"] : 0;
		$this->imgHeight			= isset($options["imgHeight"])			? $options["imgHeight"] : 0;
		$this->folder				= isset($options["folder"])				? $options["folder"] : "";
		$this->useFilesFolder		= isset($options["useFilesFolder"])		? $options["useFilesFolder"] : false;
		
	}


	/**
	 * Gibt Upload-Methode zurück
	 *
	 * @access	public
     * @return  string
	 */
	public function getUploadMethod()
	{
		
		return self::$uploadMethod;
	
	}


	/**
	 * Weist Upload Head Files zu
	 *
     * @param	string	$type	Variante des Upload-Sripts (default = '')
	 * @access	public
     * @return  string
	 */
	public function assignHeadFiles($type = "")
	{
		
		$this->cssFiles[]	 = "extLibs/jquery/uploadify/uploadify.css";
		$this->scriptFiles[] = "extLibs/jquery/uploadify/jquery.uploadify.v2.1.0.min.js";
		$this->scriptFiles[] = "extLibs/jquery/uploadify/swfobject.js";
		$this->scriptFiles[] = "extLibs/jquery/uploadify/uploadifyset" . ($type != "" ? "-" . $type : "") . ".js";
	
		return true;
	
	}


	/**
	 * Gibt Upload-Script zurück
	 *
     * @param	string	$targetElem	Zielelement (dom)
     * @param	string	$type		Medien-Typ (default = 'default')
     * @param	string	$noCache	falls true, hängt timestamp an src Attribut (default = true)
	 * @access	public
     * @return  string
	 */
	public function getUploadScript($targetElem, $type = "default", $noCache = true)
	{
		
		$elem				= "";
		$ext				= "";
		$scriptFileExt		= "";
		$functionNameExt	= "";
		$isListBox			= false;
		
		if($noCache)
			$ext	= "?" . time();
		
		if(strpos($targetElem, "-lb") !== false) { // Falls listBox
			$isListBox		 = true;
			$scriptFileExt	 = '-lb';
			$functionNameExt = 'LB';
		}
		
		if(strpos($targetElem, "-fe") !== false) { // Falls FE
			$scriptFileExt	 = '-fe';
			$functionNameExt = 'FE';
			$elem			 = '"' . $targetElem . '"';
		}
		elseif($type == "" 
		|| $type == "images" 
		|| $type == "theme" 
		|| $type == "systemimg"
		) {
			$scriptFileExt	.=	'-image';
			$functionNameExt.=	'Image';
		}
		elseif($type == "docs") {
			$scriptFileExt	.=	"-doc";
			$functionNameExt.=	'Doc';
		}
		elseif($type == "video") {
			$scriptFileExt	.=	"-video";
			$functionNameExt.=	'Video';
		}
		elseif($type == "audio") {
			$scriptFileExt	.=	"-audio";
			$functionNameExt.=	'Audio';
		}
		elseif($type == "gallery") {
			$scriptFileExt	 .=	"-" . $type;
			$functionNameExt .=	ucfirst($type);
		}
		elseif($type != "default") {
			$scriptFileExt	.=	"-" . $type;
			$functionNameExt.=	ucfirst($type);
		}
		else {
			$scriptFileExt	 =	"";
			$functionNameExt =	"";
		}
		
		$uploadScript	=	'<script type="text/javascript">' . "\r\n" . 
							'head.ready("jquery",function(){'."\r\n".
							'var headLinks = [];'. "\r\n" .
							'if(typeof($.fn.uploadify) != "function"){'. "\r\n" .
								'headLinks.push({uploadifycss:"' . PROJECT_HTTP_ROOT . '/extLibs/jquery/uploadify/uploadify.css"});' .
								'headLinks.push({uploadify:"' . PROJECT_HTTP_ROOT . '/extLibs/jquery/uploadify/jquery.uploadify.v2.1.0.min.js"});' .
								'headLinks.push({uploadifyswf:"' . PROJECT_HTTP_ROOT . '/extLibs/jquery/uploadify/swfobject.js"});' . 
								'head.load(headLinks);' .
								'head.ready("uploadify", function(){ head.load({uploadifyset:"' . PROJECT_HTTP_ROOT . '/extLibs/jquery/uploadify/uploadifyset' . $scriptFileExt . '.js"}, function(){ $.setUploadify' . $functionNameExt . '(' . $elem . '); }); });' .
							'}else{' .
								'head.load({uploadifyset:"' . PROJECT_HTTP_ROOT . '/extLibs/jquery/uploadify/uploadifyset' . $scriptFileExt . '.js"}, function(){ $.setUploadify' . $functionNameExt . '(' . $elem . '); } );' .
							'}' .
							'});' .
							'</script>';
							
		return $uploadScript;
	
	}
	
	
	/**
	 * Gibt eine Upload Maske eines bestimmten Typs zurück
	 *
     * @param	string	$uploadKind	Upload-Typ
     * @param	string	$type		Mediengruppen-Typ
     * @param	string	$index		Index
	 * @access	public
     * @return  string
	 */
	public function getUploaderMask($uploadKind, $type = "", $index = "")
	{
	
		switch($uploadKind) {
			
			case "file":
			return	'<button type="button" id="uploadFiles" class="uploadButton cc-button button hide" value="{s_button:upload}">' .
					'<span class="icons icon-upload">&nbsp;</span>' .
					'{s_button:upload}' .
					'</button>'. "\r\n" .
					'<div class="scaleImgBox right">' . "\r\n" .
					'<a href="?task=modules&type=gallery&name=" class="gallery cc-button button right" title="{s_header:gallerytext}">' .
					'<span class="icons icon-gallery">&nbsp;</span>' .
					'{s_header:newgallery} &raquo;' .
					'</a>' . "\r\n" . 
					'<label class="markBox"><input type="checkbox" name="scaleimg" class="scaleimg" id="scaleimg" ' . ($this->scaleImg === true ? ' checked="checked"' : '') . '/></label>' . "\r\n" . 
					'<label for="scaleimg" class="inline-label">{s_label:scaleimg}</label>' . "\r\n" . 
					'<div class="scaleImgDiv" id="scaleImgDiv" style="' . ($this->scaleImg === false ? ' display:none;' : '') . '">' .
					'<input type="text" name="imgWidth" id="imgWidth" class="imgWidth" value="' . ($this->imgWidth == 0 ? IMG_WIDTH : $this->imgWidth) . '" />' . "\r\n" . 
					'<span class="imgSize"> x </span>' . "\r\n" . 
					'<input type="text" name="imgHeight" id="imgHeight" class="imgHeight" value="' . ($this->imgHeight == 0 ? IMG_HEIGHT : $this->imgHeight) . '" />' . "\r\n" . 
					'<label class="imgSizeLabel inline-label">' . sprintf(ContentsEngine::replaceStaText('{s_label:filesize3}'), MIN_IMG_SIZE, MAX_IMG_SIZE) . '</label><br class="clearfloat" /></div></div>' . "\r\n" . 
					'<input id="imageFile" class="hide" name="imageFile" type="file" />' . "\r\n" .
					'<input id="docFile" class="hide" name="docFile" type="file" />' . "\r\n" .
					'<input id="videoFile" class="hide" name="videoFile" type="file" />' . "\r\n" .
					'<input id="audioFile" class="hide" name="audioFile" type="file" />' . "\r\n" .
					'<br class="clearfloat" />' . "\r\n" .
					$this->getFilesUploadMask($this->folder, $this->useFilesFolder);
			break;
			
			case "gallery":
			return	'<button type="button" id="uploadGall" class="uploadButton cc-button button hide" value="{s_button:upload}">' .
					'<span class="icons icon-upload">&nbsp;</span>' .
					'{s_button:upload}' .
					'</button>'. "\r\n" .
					'<input type="hidden" name="duplicateFiles" id="duplicateFiles" value="" />' . "\r\n" .
					'<input id="gallFiles" class="hide" name="gallFiles" type="file" />' . "\r\n" .
					'<div class="scaleImgBox">' . "\r\n" .
					'<label class="markBox"><input type="checkbox" name="scaleimg" class="scaleimg" id="scaleimg"' . ($this->scaleImg === true ? ' checked="checked"' : '') . '/></label>' . "\r\n" . 
					'<label for="scaleimg" class="inline-label">{s_label:scaleimg}</label>' . "\r\n" . 
					'<div class="scaleImgDiv" id="scaleImgDiv" style="clear:left;' . ($this->scaleImg === false ? ' display:none;' : '') . '">' . "\r\n" .
					'<input type="text" name="imgWidth" id="imgWidth" class="imgWidth" value="' . ($this->imgWidth == 0 ? IMG_WIDTH : $this->imgWidth) . '" />' . "\r\n" . 
					'<span class="imgSize"> x </span>' . "\r\n" . 
					'<input type="text" name="imgHeight" id="imgHeight" class="imgHeight" value="' . ($this->imgHeight == 0 ? IMG_HEIGHT : $this->imgHeight) . '" />' . "\r\n" . 
					'<label class="imgSizeLabel inline-label">' . sprintf(ContentsEngine::replaceStaText('{s_label:filesize2}'), MIN_IMG_SIZE, MAX_IMG_SIZE) . '</label>' . "\r\n" . 
					'</div>' . "\r\n" .
					'</div>' . "\r\n";
			break;
			
			case "listBox":
			return	'<button type="button" id="uploadGall" class="' . ($type == "gallery" ? 'uploadGalleryFiles' : 'uploadSingleFile') . ' uploadButton cc-button button hide" value="{s_button:upload}">' .
					'<span class="icons icon-upload">&nbsp;</span>' .
					'{s_button:upload}' .
					'</button>'. "\r\n" .
					'<input id="' . ($type == "gallery" ? 'uploadifyGallery' : 'uploadFile') . '" name="imageFile" type="file" style="float:left;" />' . "\r\n" .
					'<div class="scaleImgBox actionBox">' . "\r\n" .
					'<label class="markBox"><input type="checkbox" name="scaleimg" class="scaleimg" id="scaleimg-lb" /></label>' . "\r\n" . 
					'<label for="scaleimg-lb" class="inline-label">{s_label:scaleimg}</label>' . "\r\n" . 
					'<div class="scaleImgDiv" id="scaleImgDiv" style="display:none;">' .
					'<input type="text" name="imgWidth" id="imgWidth" class="imgWidth" value="' . IMG_WIDTH . '" />' . "\r\n" .
					'<span class="imgSize"> x </span>' . "\r\n" .
					'<input type="text" name="imgHeight" id="imgHeight" class="imgHeight" value="' . IMG_HEIGHT . '" />' . "\r\n" .
					'<label class="imgSizeLabel inline-label">px</label>' . "\r\n" .
					'</div>' . "\r\n" .
                    '<br class="clearfloat">' . "\r\n" .
                    '</div>' . "\r\n" .
                    '<br class="clearfloat">' . "\r\n";
			break;
			
			case "fe":
			return	'<input type="file" class="imageFile" name="imageFile" id="myUploadBox-fe-' . $index . '" />' . "\r\n" .
					'<input type="hidden" name="newfile" class="newfile" value="" />' . "\r\n" .
                    '<label class="markBox">' . "\r\n" .
                    '<input type="checkbox" name="scaleimg" class="scaleimg" id="scaleImg-' . $index . '">' . "\r\n" .
                    '</label>' . "\r\n" .
                    '<label for="scaleImg-' . $index . '" class="inline-label">{s_label:scaleimg}</label>' . "\r\n" .
                    '<div class="scaleImgDiv" style="display:none;">' . "\r\n" .
                    '<input type="text" name="imgWidth" id="imgWidth-' . $index . '" class="imgWidth imgSize" value="" />' . "\r\n" .
                    '<span class="imgSize"> x </span>' . "\r\n" .
                    '<input type="text" name="imgHeight" id="imgHeight-' . $index . '" class="imgHeight imgSize" value="" />&nbsp;&nbsp;' . "\r\n" .
                    '<label class="imgSizeLabel inline-label">' . sprintf(ContentsEngine::replaceStaText('{s_label:filesize3}'), MIN_IMG_SIZE, MAX_IMG_SIZE) . '</label>' . "\r\n" .
                    '<br class="clearfloat">' . "\r\n" .
                    '</div>' . "\r\n" .
                    '<br class="clearfloat">' . "\r\n";
			break;
		}
	
	}


	/**
	 * Upload-Verzeichnis setzen
	 *
	 * @access	public
     * @return  string
	 */
	public function setUploadFolder($folder)
	{
		
		return $this->folder = $folder;
	
	}


	/**
	 * Getter
	 *
	 * @access	public
     * @return  string
	 */
	public function __get($property)
	{

		if (property_exists($this, $property)) {
			return $this->$property;
		}
	}


	/**
	 * Setter
	 *
	 * @access	public
     * @return  string
	 */
	public function __set($property, $value)
	{
	
		if (property_exists($this, $property)) {
			$this->$property = $value;
		}
		return $this;
	}	
	
}
