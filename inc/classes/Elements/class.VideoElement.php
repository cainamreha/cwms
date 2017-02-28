<?php
namespace Concise;



/**
 * VideoElement
 * 
 */

class VideoElement extends ElementFactory implements Elements
{

	private $urlPath		= "";
	private $absPath		= "";
	private $videoFile		= "";
	private $baseFileName	= "";
	private $fileExt		= "";
	private $preload		= true;
	private $showControls	= true;
	private $muted			= true;
	private $autoplay		= false;
	private $loop			= false;
	private $border			= false;
	private $poster			= "";
	private $width			= 640;
	private $height			= 360;
	private $modal			= false;
	private $modalBtnTxt	= "";
	private $modalID		= "";
	
	/**
	 * Gibt ein VideoElement zurück
	 * 
	 * @access	public
	 * @param	string	$options	Parameter-Array (Wert, Styles, Wrap)
	 */
	public function __construct($options, $DB, &$o_lng, &$o_page)
	{
	
		$this->DB				= $DB;
		$this->o_lng			= $o_lng;
		$this->o_page			= $o_page;

		$this->conType			= $options["conType"];
		$this->conValue			= $options["conValue"];
		$this->conAttributes	= $options["conAttributes"];
		$this->conNum			= $options["conNum"];
		$this->conTable			= $options["conTable"];

	}
	

	/**
	 * Element erstellen
	 * 
	 * @access	public
     * @return  string
	 */
	public function getElement()
	{

		##############################
		##########  Video  ###########
		##############################

		if(strpos($this->conValue, "[") === 0)
			$this->conValue	= str_replace('\"', '"', $this->conValue);
		
		$videoCon = (array)json_decode($this->conValue);
		
		if(!isset($videoCon[0]))
			$videoCon[0] = ""; // video file
		if(!isset($videoCon[1]))
			$videoCon[1] = ""; // title
		if(!isset($videoCon[2]))
			$videoCon[2] = 1; // preload
		if(!isset($videoCon[3]))
			$videoCon[3] = 1; // controls
		if(!isset($videoCon[4]))
			$videoCon[4] = 1; // muted
		if(!isset($videoCon[5]))
			$videoCon[5] = 0; // autoplay
		if(!isset($videoCon[6]))
			$videoCon[6] = 0; // loop
		if(!isset($videoCon[7]))
			$videoCon[7] = 0; // border
		if(!isset($videoCon[8]))
			$videoCon[8] = 640; // width
		if(!isset($videoCon[9]))
			$videoCon[9] = 360; // height
		if(!isset($videoCon[10]))
			$videoCon[10] = ""; // poster
		if(!isset($videoCon[11]))
			$videoCon[11] = ""; // modal
		if(!isset($videoCon[12]))
			$videoCon[12] = ""; // modal btn

		$this->preload		= $videoCon[2];
		$this->showControls	= $videoCon[3];
		$this->muted		= $videoCon[4];
		$this->autoplay		= $videoCon[5];
		$this->loop			= $videoCon[6];
		$this->border		= $videoCon[7];
		$this->width		= $videoCon[8];
		$this->height		= $videoCon[9];
		$this->poster		= $videoCon[10] != "" ? Modules::getImagePath($videoCon[10], true) : "";
		$this->modal		= !empty($videoCon[11]);
		$this->modalBtnTxt	= $videoCon[12];

		$this->urlPath		= PROJECT_HTTP_ROOT . '/' . CC_VIDEO_FOLDER . '/';
		$this->absPath		= PROJECT_DOC_ROOT . '/' . CC_VIDEO_FOLDER . '/';
		$this->baseFileName	= pathinfo($videoCon[0], PATHINFO_FILENAME);
		$this->fileExt		= pathinfo($videoCon[0], PATHINFO_EXTENSION);
		$html5Formats		= array("mp4","webm","ogv");
		
		if($this->modal) {
			$this->modalID	= 'modal-' . $this->conTable . '-' . $this->conNum;
			$output			= $this->getVideoLink($videoCon, true);
			$output		   .= $this->getVideoModal($videoCon[1]);
		}
		elseif(in_array($this->fileExt, $html5Formats))
			$output			= $this->getHTML5Video();
		else
			$output			= $this->getVideoLink($videoCon);
		
		// Ggf. Attribute (Styles) Wrapper-div hinzufügen
		if($this->conAttributes['id'] != "" || $this->conAttributes['class'] != "" || $this->conAttributes['style'] != "")
			$output	= $this->getContentElementWrapper($this->conAttributes, $output);
		
	
		return $output;
	
	}	
	

	/**
	 * HTML5 video tag
	 * 
	 * @access	public
     * @return  string
	 */
	public function getHTML5Video()
	{
	
		$attrStr		= "";

		if(!$this->preload)
			$attrStr	.= ' preload="none"';
		if($this->showControls)
			$attrStr	.= ' controls="controls"';
		if($this->muted)
			$attrStr	.= ' muted="muted"';
		if($this->autoplay)
			$attrStr	.= ' autoplay="autoplay"';
		if($this->loop)
			$attrStr	.= ' loop="loop"';
		if($this->width)
			$attrStr	.= ' width="' . $this->width . '"';
		if($this->height)
			$attrStr	.= ' height="' . $this->height . '"';
		if(!empty($this->poster))
			$attrStr	.= ' poster="' . PROJECT_HTTP_ROOT . '/' . $this->poster . '"';
		if($this->border)
			$attrStr	.= ' class="cc-has-border"';
		
		// HTML5 Video (mp4 required)
		$output =	'<video' . $attrStr . '>';
		if(file_exists($this->absPath . $this->baseFileName . ".mp4"))
			$output .=	'<source src="' . $this->urlPath . $this->baseFileName . '.mp4" />';
		if(file_exists($this->absPath . $this->baseFileName . ".webm"))
			$output .=	'<source src="' . $this->urlPath . $this->baseFileName . '.webm" type="video/webm" />';
		if(file_exists($this->absPath . $this->baseFileName . ".ogv"))
			$output .=	'<source src="' . $this->urlPath . $this->baseFileName . '.ogv" type="video/ogg" />';
		$output .=		'No HTML5 videos.' . PHP_EOL;
		$output .=	'</video>' . PHP_EOL;
		
		return $output;
	
	}
	

	/**
	 * Video link
	 * 
	 * @access	public
     * @return  string
	 */
	public function getVideoLink($videoCon, $modal = false)
	{
	
		if($modal) {
			// Button open modal
			$btnDefs	= array(	"class"		=> "{t_class:btn} {t_class:btnpri}",
									"text"		=> htmlspecialchars($videoCon[12]),
									"title"		=> htmlspecialchars($videoCon[1]),
									"icon"		=> "video",
									"attr"		=> 'data-toggle="modal" data-context="modal" data-target="#' . $this->modalID . '"'
								);
			
			$output	=	parent::getButton($btnDefs);
		}
		else {
			// Button open video in new tab
			$btnDefs	= array(	"href"		=> PROJECT_HTTP_ROOT . '/' . CC_VIDEO_FOLDER . '/' . htmlspecialchars($videoCon[0]),
									"class"		=> "{t_class:btn} {t_class:btnpri}",
									"text"		=> htmlspecialchars($videoCon[1]),
									"title"		=> htmlspecialchars($videoCon[1]),
									"icon"		=> "video",
									"attr"		=> 'target="_blank"'
								);
			
			$output	=	parent::getButtonLink($btnDefs);
		}
		
		return $output;
	
	}
	

	/**
	 * Video Modal
	 * 
	 * @access	public
     * @return  string
	 */
	public function getVideoModal($videoTitle)
	{
			
		$output	= '<div id="' . $this->modalID . '" class="{t_class:modal} {t_class:centertxt}" tabindex="-1" role="dialog" aria-hidden="true" aria-labelledby="myModalLabel">
						<div class="{t_class:modaldial} {t_class:modallg}" role="document">
							<div class="{t_class:modalcon}">
								<div class="{t_class:modalhead}">
									<button type="button" class="close {t_class:btn} {t_class:btnlink}" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
									<div class="{t_class:modaltit}">' . htmlspecialchars($videoTitle) . '</div>
								</div>
								<div class="{t_class:modalbody}">' . 
									$this->getHTML5Video() . '
								</div>
							</div>
						</div>
					</div>';
		
		// Script stop on close modal
		$output	.= '<script>
						head.ready(function(){
						$(document).ready(function(){
							$("#' . $this->modalID . '").on("hide.bs.modal",function(e){
								var modEle = $("#' . $this->modalID . '").find("video");
								if(modEle.length){
									modEle[0].pause();
								}
							});
						});
						});
					</script>';
		return $output;
	
	}
	
}
