<?php
namespace Concise;


/**
 * Klasse Plupload
 *
 */

class Plupload extends FileUploaderFactory implements FileUploaderInterface
{

	private static $uploadMethod	= "plupload";
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
	public function __construct($options, $DB, &$o_lng)
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
		
		$this->cssFiles["pluploadcss"]		= "extLibs/jquery/plupload/js/jquery.ui.plupload/css/jquery.ui.plupload.css";
		$this->scriptFiles["plupload"]		= "extLibs/jquery/plupload/js/plupload.full.min.js";
		#$this->scriptFiles["moxie"]		= "extLibs/jquery/plupload/js/moxie.js";
		#$this->scriptFiles["plupload"]		= "extLibs/jquery/plupload/js/plupload.dev.js";
		$this->scriptFiles["pluploadln"]	= "extLibs/jquery/plupload/js/i18n/" . $this->o_lng->adminLang . ".js";
		$this->scriptFiles["pluploadui"]	= "extLibs/jquery/plupload/js/jquery.ui.plupload/jquery.ui.plupload.min.js";
		$this->scriptFiles["pluploadset"]	= "extLibs/jquery/plupload/js/plupload.config.ui.js";
	
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
	
		$uploadScript	=	'<script type="text/javascript">' . "\r\n" . 
							#'head.ready(function(){'."\r\n".
							'head.ready("ui",function(){'. "\r\n" .
							'var headLinks = [];'. "\r\n" .
							'if(typeof($.fn.plupload) != "function"){'. "\r\n" .
								'head.load({pluploadcss:"' . PROJECT_HTTP_ROOT . '/extLibs/jquery/plupload/js/jquery.ui.plupload/css/jquery.ui.plupload.css"});' .
								'head.load({plupload:"' . PROJECT_HTTP_ROOT . '/extLibs/jquery/plupload/js/plupload.full.min.js"});' .
								'headLinks.push({pluploadln:"' . PROJECT_HTTP_ROOT . '/extLibs/jquery/plupload/js/i18n/' . $this->o_lng->adminLang . '.js"});' . 
								'headLinks.push({pluploadui:"' . PROJECT_HTTP_ROOT . '/extLibs/jquery/plupload/js/jquery.ui.plupload/jquery.ui.plupload.min.js"});' . 
								'headLinks.push({pluploadset:"' . PROJECT_HTTP_ROOT . '/extLibs/jquery/plupload/js/plupload.config.ui.js"});' . 
								'head.ready("plupload", function(){ head.load(headLinks); });' .
								'head.ready("ui", function(){ head.ready("plupload", function(){ head.ready("pluploadset", function(){ $(document).ready(function(){ setTimeout(function(){ $.ccPluploader("' . $targetElem . '");}, 100); }); }); }); });' .
							'}else{' .
								'head.ready("ui", function(){ head.ready("plupload", function(){ head.ready("pluploadset", function(){ $(document).ready(function(){ setTimeout(function(){ $.ccPluploader("' . $targetElem . '");}, 100); }); }); }); });' .
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
		
		$output	= "";
		
		switch($uploadKind) {
			
			case "file":
			return	'<div id="myUploadBox">' . "\r\n" .
					'<p>Your browser doesn\'t have Flash, Silverlight or HTML5 support.</p>' . "\r\n" .
					'</div>' . "\r\n" .
					'<br />' . "\r\n" .
					'<span class="inline-box">' . "\r\n" .
					'<label class="markBox"><input type="checkbox" name="overwrite" id="overwrite" class="overwrite"' . ($this->overwrite === true ? ' checked="checked"' : '') . ' /></label>' . "\r\n" .
					'<label for="overwrite" class="inline-label">{s_label:overwrite}</label>' . "\r\n" . 
					'</span>' . "\r\n" .
					'<span class="inline-box">' . "\r\n" .
					'<div class="scaleImgBox">' . "\r\n" .
					'<label class="markBox"><input type="checkbox" name="scaleimg" class="scaleimg" id="scaleimg" ' . ($this->scaleImg === true ? ' checked="checked"' : '') . '/></label>' . "\r\n" . 
					'<label for="scaleimg" class="inline-label">{s_label:scaleimg}</label>' . "\r\n" . 
					'<div class="scaleImgDiv" id="scaleImgDiv" style="' . ($this->scaleImg === false ? ' display:none;' : '') . '">' .
					'<input type="text" name="imgWidth" id="imgWidth" class="imgWidth" value="' . ($this->imgWidth == 0 ? IMG_WIDTH : $this->imgWidth) . '" />' . "\r\n" . 
					'<span class="imgSize"> x </span>' . "\r\n" . 
					'<input type="text" name="imgHeight" id="imgHeight" class="imgHeight" value="' . ($this->imgHeight == 0 ? IMG_HEIGHT : $this->imgHeight) . '" />' . "\r\n" . 
					'<label class="imgSizeLabel inline-label">' . sprintf(ContentsEngine::replaceStaText('{s_label:filesize3}'), MIN_IMG_SIZE, MAX_IMG_SIZE) . '</label>' . "\r\n" . 
					'<br class="clearfloat" /></div></div>' . "\r\n" . 
					'</span>' . "\r\n" .
					$this->getFilesUploadMask($this->folder, $this->useFilesFolder) .
					'<input type="hidden" name="maxFileSize" value="' . str_replace(" ", "", strtolower($this->allowedFileSizeStr)) . '" />' . "\r\n" .
					'<input type="hidden" name="allowedFileTypes" value="' . implode(",", $this->allowedFiles) . '" />' . "\r\n";
			break;
			
			case "gallery":
			return	'<div id="myUploadBox">' . "\r\n" .
					'<p>Your browser doesn\'t have Flash, Silverlight or HTML5 support.</p>' . "\r\n" .
					'</div>' . "\r\n" .
					'<br />' . "\r\n" .
					#'<input type="button" id="uploadGall" class="uploadButton cc-button button-icon-left button hide" value="{s_button:upload}" />'. "\r\n" .
					'<label class="markBox"><input type="checkbox" name="overwrite" id="overwrite" class="overwrite"' . ($this->overwrite === true ? ' checked="checked"' : '') . ' /></label>' . "\r\n" .
					'<label for="overwrite" class="inline-label">{s_label:overwrite}</label>' . "\r\n" . 
					'<div class="scaleImgBox">' . "\r\n" .
					'<label class="markBox"><input type="checkbox" name="scaleimg" class="scaleimg" id="scaleimg" ' . ($this->scaleImg === true ? ' checked="checked"' : '') . '/></label>' . "\r\n" . 
					'<label for="scaleimg" class="inline-label">{s_label:scaleimg}</label>' . "\r\n" . 
					'<div class="scaleImgDiv" id="scaleImgDiv" style="' . ($this->scaleImg === false ? ' display:none;' : '') . '">' .
					'<input type="text" name="imgWidth" id="imgWidth" class="imgWidth" value="' . ($this->imgWidth == 0 ? IMG_WIDTH : $this->imgWidth) . '" />' . "\r\n" . 
					'<span class="imgSize"> x </span>' . "\r\n" . 
					'<input type="text" name="imgHeight" id="imgHeight" class="imgHeight" value="' . ($this->imgHeight == 0 ? IMG_HEIGHT : $this->imgHeight) . '" />' . "\r\n" . 
					'<label class="imgSizeLabel inline-label">' . sprintf(ContentsEngine::replaceStaText('{s_label:filesize3}'), MIN_IMG_SIZE, MAX_IMG_SIZE) . '</label>' . "\r\n" . 
					'<br class="clearfloat" /></div></div>' . "\r\n" . 
					'<input type="hidden" name="maxFileSize" value="' . str_replace(" ", "", strtolower($this->allowedFileSizeStr)) . '" />' . "\r\n" .
					'<input type="hidden" name="allowedFileTypes" value="' . implode(",", $this->allowedFiles) . '" />' . "\r\n";
			break;
			
			case "listBox":
			$output	= 	'<div id="myUploadBox-lb">' . "\r\n" .
						'<p>Your browser doesn\'t have Flash, Silverlight or HTML5 support.</p>' . "\r\n" .
						'</div>' . "\r\n" .
						'<div class="overwriteBox actionBox">' . "\r\n" .
						'<label class="markBox"><input type="checkbox" name="overwrite" id="overwrite-lb" /></label>' . "\r\n" .
						'<label for="overwrite-lb" class="inline-label">{s_label:overwrite}</label>' . "\r\n" .
						'<br class="clearfloat" /></div>' . "\r\n" .
						'<div class="scaleImgBox actionBox">' . "\r\n" .
						'<label class="markBox"><input type="checkbox" name="scaleimg" class="scaleimg" id="scaleimg-lb" /></label>' . "\r\n" . 
						'<label for="scaleimg-lb" class="inline-label">{s_label:scaleimg}</label>' . "\r\n" . 
						'<div class="scaleImgDiv" id="scaleImgDiv" style="' . ($this->scaleImg === false ? ' display:none;' : '') . '">' .
						'<input type="text" name="imgWidth" id="imgWidth" class="imgWidth" value="' . ($this->imgWidth == 0 ? IMG_WIDTH : $this->imgWidth) . '" />' . "\r\n" . 
						'<span class="imgSize"> x </span>' . "\r\n" . 
						'<input type="text" name="imgHeight" id="imgHeight" class="imgHeight" value="' . ($this->imgHeight == 0 ? IMG_HEIGHT : $this->imgHeight) . '" />' . "\r\n" . 
						'<label class="imgSizeLabel inline-label">' . sprintf(ContentsEngine::replaceStaText('{s_label:filesize3}'), MIN_IMG_SIZE, MAX_IMG_SIZE) . '</label>' . "\r\n" . 
						'<br class="clearfloat" /></div>' . "\r\n" .
						'<input type="hidden" name="maxFileSize" value="' . str_replace(" ", "", strtolower($this->allowedFileSizeStr)) . '" />' . "\r\n" .
						'<input type="hidden" name="allowedFileTypes" value="' . implode(",", $this->allowedFiles) . '" />' . "\r\n" .
						'</div>' . "\r\n";
			break;
			
			case "fe":
			$output	= 	'<form action="" method="post" class="plupload-uploader" data-submit="false">' . "\r\n" .
						'<div id="myUploadBox-fe-' . $index . '" class="feFileUploader">' . "\r\n" .
						'<p>Your browser doesn\'t have Flash, Silverlight or HTML5 support.</p>' . "\r\n" .
						'</div>' . "\r\n" .
						'<input type="hidden" name="newfile" class="newfile" value="" />' . "\r\n" .
						'<input type="hidden" name="singleFile" value="true" />' . "\r\n" .
						'<div class="uploadFeaturesBox actionBox">' . "\r\n" .
						'<label class="markBox"><input type="checkbox" name="overwrite" id="overwrite-' . $index . '" /></label>' . "\r\n" .
						'<label for="overwrite-' . $index . '" class="inline-label">{s_label:overwrite}</label>' . "\r\n" .
						'<label class="markBox">' . "\r\n" .
						'<input type="checkbox" name="scaleimg" class="scaleimg" id="scaleImg-' . $index . '" />' . "\r\n" .
						'</label>' . "\r\n" .
						'<label for="scaleImg-' . $index . '" class="inline-label floatLabel">{s_label:scaleimg}</label>' . "\r\n" .
						'<div class="scaleImgDiv" style="display:none;">' . "\r\n" .
						'<input type="text" name="imgWidth" id="imgWidth-' . $index . '" class="imgWidth imgSize" value="" />' . "\r\n" .
						'<span class="imgSize"> x </span>' . "\r\n" . 
						'<input type="text" name="imgHeight" id="imgHeight-' . $index . '" class="imgHeight imgSize" value="" />&nbsp;&nbsp;' . "\r\n" .
						'<label class="imgSizeLabel inline-label">' . sprintf(ContentsEngine::replaceStaText('{s_label:filesize3}'), MIN_IMG_SIZE, MAX_IMG_SIZE) . '</label>' . "\r\n" .
						'</div>' . "\r\n" .
						'</div>' . "\r\n" .
						'<input type="hidden" name="maxFileSize" value="' . str_replace(" ", "", strtolower($this->allowedFileSizeStr)) . '" />' . "\r\n" .
						'<input type="hidden" name="allowedFileTypes" value="' . implode(",", $this->allowedFiles) . '" />' . "\r\n" .
						'</form>' . "\r\n";
			break;
		}

		return $output;
	
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

}
