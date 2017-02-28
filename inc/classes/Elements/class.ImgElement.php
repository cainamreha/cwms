<?php
namespace Concise;



/**
 * ImgElement
 * 
 */

class ImgElement extends ElementFactory implements Elements
{
	
	public $enlarge			= false;
	public $useWrapper		= false;
	public $useTeaserLink	= false;
	public $teaserLink		= false;
	public $showCaption		= true;
	public $imgClass		= "";
	public $imgSrc			= "";
	
	/**
	 * Gibt ein ImgElement zurück
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
		$this->conCount			= $options["conCount"];

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
		########  Bildinhalt  ########
		##############################
		
		$imageCon		= explode("<>", $this->conValue);
		$image			= "";
		$caption		= "";
		$link			= "";
		$styles			= "";
		#$figStyles		= ' class="imageWrapper"';
		$this->conAttributes['class']	.= ($this->conAttributes['class'] != "" ? ' ' : '') . "imageWrapper";
		$linkClass		= "";
		$imgClass		= "";
		
		if(!isset($imageCon[0]))
			$imageCon[0] = "";
		if(!isset($imageCon[1]))
			$imageCon[1] = "";
		if(!isset($imageCon[2]))
			$imageCon[2] = "";
		if(!isset($imageCon[3]))
			$imageCon[3] = "";
		if(!isset($imageCon[4]))
			$imageCon[4] = "";
		if(!isset($imageCon[5]))
			$imageCon[5] = "";
		if(!isset($imageCon[6]))
			$imageCon[6] = 0; // enlargable = 1; zoomable = 2
		
	
		// Img Style class
		if($imageCon[5] != ""
		&& !empty(parent::$styleDefs['img' . $imageCon[5]])
		) {
			$imgClass = " " . parent::$styleDefs['img' . $imageCon[5]];
			$this->conAttributes['class']	.= ' cc-has-' . str_replace(" ", " cc-has-", parent::$styleDefs['img' . $imageCon[5]]);
		}
		
		// If enlargable
		if($imageCon[6] == 1) {
			$this->enlarge	= true;
		}
		
		// If zoomable
		if($imageCon[6] == 2) {
			$this->getZoomable();
		}
		
		// Styles zufügen, falls angegeben
		if($this->conAttributes['id'] != "")
			$styles = ' id="' . $this->conAttributes['id'] . '"';
		
		if($this->conAttributes['class'] != "")
			$styles .= ' class="' . $this->conAttributes['class'] . '"';
		
		$imgWidth	= preg_replace("/^(.*)width\:\s?([0-9]+)px;(.*)$/ium", "$2", $this->conAttributes['style']);
		$imgHeight	= preg_replace("/^(.*)height\:\s?([0-9]+)px;(.*)$/ium", "$2", $this->conAttributes['style']);
		
		$stylesRed	= preg_replace("/^(.*)(width\:\s?[0-9]+px;)(.*)$/", "$1$3", $this->conAttributes['style']);
		$stylesRed	= preg_replace("/^(.*)(height\:\s?[0-9]+px;)(.*)$/", "$1$3", $stylesRed);
	
		// Styles für Element um width und height reduziert
		if($this->conAttributes['style'] != "")
			$styles .= ' style="' . $stylesRed . '"';
		

		$imgAttrWidth	= is_numeric($imgWidth) ? ' width="' . $imgWidth . '"' : '';
		$imgAttrHeight	= is_numeric($imgHeight) ? ' height="' . $imgHeight . '"' : '';

		
		// Pfad zur Bilddatei
		// Falls files-Ordner, den Pfad ermitteln
		if(strpos($imageCon[0], "/") !== false) {
			$filesImg		= explode("/", $imageCon[0]);
			$imageCon[0]	= array_pop($filesImg);					
			$imgPath		= CC_FILES_FOLDER . "/" . implode("/", $filesImg) . "/";
		}
		elseif(strpos($imageCon[0], "{img_root}") === 0) {
			$imgPath		= IMAGE_DIR;
			$imageCon[0]	= str_replace("{img_root}", "", $imageCon[0]);					
		}
		else
			$imgPath		= CC_IMAGE_FOLDER . "/";

		
		// Falls Bilddatei vorhanden
		if($imageCon[0] != ""
		&& file_exists(PROJECT_DOC_ROOT . '/' . $imgPath . $imageCon[0])
		) {
			$this->imgSrc	= PROJECT_HTTP_ROOT . '/' . $imgPath . $imageCon[0];
			
			if(empty($imgWidth)
			|| empty($imgHeight)
			|| !is_numeric($imgWidth)
			|| !is_numeric($imgHeight)
			) {
				$size			= getimagesize(PROJECT_DOC_ROOT . '/' . $imgPath . $imageCon[0]);
				$imgAttrWidth	= ' width="' . (empty($imgWidth)|| !is_numeric($imgWidth) ? $size[0] : $imgWidth) . '"';
				$imgAttrHeight	= ' height="' . (empty($imgHeight)|| !is_numeric($imgHeight) ? $size[1] : $imgHeight) . '"';
			}
		}
		else
			$this->imgSrc	= SYSTEM_IMAGE_DIR . '/noimage.png';
		
		$imgAttr	= array	(	"class"	=> 'imageElement' . $imgClass,
								"data-imgextra"	=> $imageCon[6]
							);
		
		$imgDataStr	= "";
		if(parent::$feMode)
			$imgDataStr	=  ' data-imgclass="' . $imageCon[5] . '" data-imgextra="' . $imageCon[6] . '"';
			
		
		// data-attr
		foreach($this->conAttributes as $key => $val) {
			if(strpos($key, "data-") === 0) {
				$imgAttr[$key]	 = $val;
				$styles		.= ' ' . $key . '="' . $val . '"';
			}
		}
		
		// Ggf. Bild als Galerie
		if($this->enlarge)
			$image		= $this->getEnlargable($imageCon, $imgAttr);
		
		// Bild als Link
		elseif($this->teaserLink)
			$image		= $this->getTeaserLink($imageCon, $imgPath);

		// Image-Tag
		else {
			// Srcset
			$srcset		= Modules::getImageSrcset($imageCon[0], $imgPath);
			
			$image		= '<img src="' . $this->imgSrc . '"' . ($srcset != "" ? ' srcset="' . $srcset . '"' : '') . ' alt="' . $imageCon[1] . '" title="' . $imageCon[2] . '" class="imageElement' . $imgClass . ($this->conCount != "" ? ' object' . $this->conCount : '') . '"' . $imgAttrWidth . $imgAttrHeight . ($imageCon[6] == 2 ? ' data-imgid="' . $this->conTable . $this->conNum . '"' : '') . $imgDataStr .' />';
		}

		// If zoomable
		if($imageCon[6] == 2)
			$image		= '<div class="cc-jzoom-image">' . $image . '</div>' . PHP_EOL;

		
		// Caption
		if($this->showCaption && $imageCon[3] != "")
			$caption	= '<' . ($this->html5 ? 'figcaption' : 'p') . ' class="caption {t_class:block}">' . $imageCon[3] . '</' . ($this->html5 ? 'figcaption' : 'p') . '>';
			
		// Link
		if(!empty($imageCon[4]))
			$link	= $this->getImageLink($imageCon);

			
		
		// Link
		if($imageCon[4] != "")
			$output = $link . $image . '</a>' . PHP_EOL;
		
		else
			$output = $image . PHP_EOL;
		
		
		$output .= $caption;
		
		
		// Ggf. inner wrapper div
		if(!empty($this->conAttributes['div'])) {
			$output =	'<div' . (!empty($this->conAttributes['divid']) ? ' id="' . (htmlspecialchars($this->conAttributes['divid'])) . '"' : '') . (!empty($this->conAttributes['divclass']) ? ' class="' . htmlspecialchars($this->conAttributes['divclass']) . '"' : '') . '>' . PHP_EOL .
						$output .
						'</div>' . PHP_EOL;
		}

		
		// Figure tag
		$output =	'<' . ($this->html5 ? 'figure' : 'div') . $styles . '>' . PHP_EOL .
					$output .
					'</' . ($this->html5 ? 'figure' : 'div') . '>' . PHP_EOL;
		
		
		return $output;
	
	}
	
	
	// getImageLink
	private function getImageLink($imageCon)
	{

		// Link
		if(is_numeric($imageCon[4]) || strpos($imageCon[4], "{root}/") === 0 || strpos($imageCon[4], "{sitelink}/") === 0) {
			
			$intLink = explode("#", $imageCon[4]);
			
			if(is_numeric($intLink[0]))
				$intLink[0] = HTML::getLinkPath($intLink[0], "current", false);
			else
				$intLink[0] = str_replace(array("{sitelink}/","{root}/"), "", $intLink[0]);
			
			if(strrpos($intLink[0], '_doc-') === false
			&& strrpos($intLink[0], PAGE_EXT) !== strlen($intLink[0]) - strlen(PAGE_EXT)
			)
				$intLink[0] .= PAGE_EXT;
			
			$link = '<a href="' . PROJECT_HTTP_ROOT . "/" . $intLink[0] . (isset($intLink[1]) ? '#'.$intLink[1] : '') . '"';
			$linkClass = "siteLink-img ";
		}
		else {
			if(preg_match("/^https:\/\//", $imageCon[4]))
				$link = '<a href="https://' . str_replace("https://", "", $imageCon[4]) . '"';
			elseif(preg_match("/^http:\/\//", $imageCon[4]))
				$link = '<a href="http://' . str_replace("http://", "", $imageCon[4]) . '"';
			elseif(preg_match("/{root/", $imageCon[4]))
				$link = '<a href="' . $imageCon[4] . '"';
			else
				$link = '<a href="' . (strpos($imageCon[4], "#") !== false ? $imageCon[4] : PROJECT_HTTP_ROOT . '/' . $imageCon[4] . PAGE_EXT) . '"';
			
			$linkClass = "extLink-img ";
		}
		
		$link = $link . ' class="' . $linkClass . '">';
		
		return $link;
	
	}	
	
	
	// getEnlargable
	private function getEnlargable($imageCon, $imgAttr = array())
	{
	
		require_once PROJECT_DOC_ROOT."/inc/classes/Modules/class.Gallery.php";
		$o_gallery		= new Gallery($this->DB, $this->o_lng->lang, $imageCon[0], "slimbox", "", 0, 0, 0, "", true, $imageCon[2]);
		$o_gallery->getGalleryCode("slimbox");
		$o_gallery->imgAttr	= $imgAttr;
		
		$this->mergeHeadCodeArrays($o_gallery);
		
		return $o_gallery->getGallery(); // Bildergallerie einbinden
	
	}
	
	
	// getTeaserLink
	private function getTeaserLink($imageCon, $imgPath)
	{

		$output	= $this->teaserLink . '<img class="teaserImage ' . $this->conNum . '" src="' . PROJECT_HTTP_ROOT . '/' . $imgPath . 'thumbs/' . $imageCon[0] . '" alt="' . $imageCon[1] . '" title="' . $imageCon[1] . '" /></a>';
		
		return $output;
	}
	
	
	// getZoomable
	private function getZoomable()
	{
	
		$this->scriptFiles["jzoom"]	= "extLibs/jquery/jZoom/jzoom.min.js";
		
		$this->scriptCode[]			=	'head.ready("jzoom", function(){
											$(document).ready(function(){
												$(\'*[data-imgid="' . $this->conTable . $this->conNum . '"]\').closest(".cc-jzoom-image").jzoom({
													// width / height of the magnifying glass
													width: 400,
													height: 400,
													// where to position the zoomed image
													position: "right",
													// x/y offset in pixels.
													offsetX: 20,
													offsetY: 20,
													// opacity level
													opacity: 0.5,
													// background color
													bgColor: "#fff",
													// loading text
													loading: "Loading..."
												});	
											});
										});';
		
	}

}
