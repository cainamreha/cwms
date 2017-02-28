<?php
namespace Concise;



/**
 * Klasse für Bildergalerie
 *
 */

class Gallery extends Modules
{

	public $galleryName		= "";
	public $galleryNameDB	= "";
	public $galleryType		= "";
	public $gallHeader		= "";
	public $showTitle		= 0;
	public $showText		= 0;
	public $useLink			= 0;
	public $maxImgNumber	= "";
	public $singleImg		= false;
	public $imgTitle		= "";
	public $imgAttr			= array();
	public $imgCount		= "";
	public $hasVideo		= false;
	public $galleryData		= array();
	private static $randID	= "";
	
	
	/**
	 * Bildergalerie Constructor
	 * 
	 * @param	object	DB-Objekt
	 * @param	string	Sprache
	 * @param	string	Galeriename
	 * @param	string	Galerietyp (default = fader)
	 * @param	string	Überschrift (default = '')
	 * @param	int		Bildüberschrift anzeigen (default = 0)
	 * @param	int		Bildtext anzeigen (default = 0)
	 * @param	int		Bildlink verwenden (default = 0)
	 * @param	string	max. Anzahl an Vorschaubildern (default = '')
	 * @param	boolean	Einzelbild (default = false)
	 * @param	string	Bildtitel (default = '')
	 * @access	public
	 * @return	string
	 */
	public function __construct($DB, $lang, $galleryName, $galleryType = "fader", $gallHeader = "", $showTitle = 0, $showText = 0, $useLink = 0, $maxImgNumber = "", $singleImg = false, $imgTitle = "")
	{
		
		$this->DB				= $DB;
		$this->lang				= $lang;
		
		$this->galleryName		= $galleryName;
		$this->galleryType		= $galleryType;
		$this->gallHeader		= $gallHeader;
		$this->showTitle		= $showTitle;
		$this->showText			= $showText;
		$this->useLink			= $useLink;
		$this->maxImgNumber		= $maxImgNumber;
		$this->singleImg		= $singleImg;
		$this->imgTitle			= $imgTitle;
		
		$this->galleryNameDB	= $this->DB->escapeString($galleryName);
  		$this->folder			= PROJECT_HTTP_ROOT . '/' . CC_GALLERY_FOLDER . '/' . $galleryName;
		$this->absPath			= PROJECT_DOC_ROOT . '/' . CC_GALLERY_FOLDER . '/' . $galleryName . '/';
		$this->userGroup		= "";
		$this->dbOrder			= "ORDER BY img.`sort_id` ASC";
		$this->imgCount			= 0;
		$this->hasVideo			= false;
		
		if($this->galleryType == "random")
			$this->dbOrder		= "ORDER BY RAND() LIMIT 1";

		#self::$randID			= uniqid();
		self::$randID			= self::sanitizeGalleryName($this->galleryName);
	
	}
	
	

	/**
	 * Bestimmt die einzubindenden JS-Dateien für die Bildergalerie
	 * 
	 * @param	string Galerietyp (default = fader)
	 * @access	public
	 * @return	string
	 */
	public function getGalleryCode($galleryType = "fader", $autoStart = 1, $continous = 1, $sliderSpeed = 600, $sliderPause = 3500, $controlNav = 1, $controlNavType = "num")
	{
	
		switch($galleryType) {
			
			case "fader":
				$this->cssFiles[]					= "extLibs/jquery/slideshow/slideshow.css";
				$this->scriptFiles["slideshow"]		= "extLibs/jquery/slideshow/slideshow.min.js";
				
				$this->scriptCode[]		=	'head.ready("jquery", function(){
												head.ready("slideshow", function(){
													$("document").ready(function(){
														setTimeout(function(){															
															$(".slideshow").simpleFaderSlideshow({"timeOut": 9000, "speed": 2000})
														}, 1);
													});
												});
											});' . PHP_EOL;
				break;
						
			case "thumbs":
				$this->cssFiles[]					= "extLibs/jquery/lightGallery/css/lightgallery.min.css";
				$this->scriptFiles["lightgallery"]	= "extLibs/jquery/lightGallery/js/lightgallery-all.min.js";
				
				$this->scriptCode[]		= '(function($){' . PHP_EOL .
											'$.myLightGallery' . self::$randID . ' = function(){' . PHP_EOL .
											'$("#lightGallery-' . self::$randID . '").lightGallery({' . PHP_EOL .
												'mode: "lg-slide", // Type of transition between images, e.g. "lg-slide" or "lg-fade".' . PHP_EOL .
												'cssEasing: "ease", // Value for CSS "transition-timing-function".' . PHP_EOL .
												'easing: "linear", // "for jquery animation"' . PHP_EOL .
												'speed: ' . ($sliderSpeed > 0 ? (int)$sliderSpeed : 600) . ', // Transition duration (in ms).' . PHP_EOL .
												'addClass: "", // Add custom class for gallery.' . PHP_EOL .
												'preload: 1,  // number of preload slides. will exicute only after the current slide is fully loaded. ex:// you clicked on 4th image and if preload = 1 then 3rd slide and 5th slide will be loaded in the background after the 4th slide is fully loaded.. if preload is 2 then 2nd 3rd 5th 6th slides will be preloaded.. ... ...' . PHP_EOL .
												'showAfterLoad: true, // Show Content once it is fully loaded.' . PHP_EOL .
												'selector: "", // Custom selector property insted of just child.' . PHP_EOL .
												'index: 0, // Allows to set which image/video should load when using dynamicEl.' . PHP_EOL .
												'dynamic: false, // Set to true to build a gallery based on the data from "dynamicEl" opt.' . PHP_EOL .
												'dynamicEl: [], // Array of objects (src, thumb, caption, desc, mobileSrc) for gallery els.' . PHP_EOL .
												'thumbnail: true, // Whether to animate thumbnails.' . PHP_EOL .
												'exThumbImage: false, // Name of a "data-" attribute containing the paths to thumbnails.' . PHP_EOL .
												'animateThumb: true, // Enable thumbnail animation.' . PHP_EOL .
												'showThumbByDefault: false, // Enable thumbnail animation.' . PHP_EOL .
												'currentPagerPosition: "middle", // Position of selected thumbnail.' . PHP_EOL .
												'thumbWidth: ' . THUMB_SIZE . ', // Width of each thumbnails' . PHP_EOL .
												'thumbMargin: 5, // Spacing between each thumbnails ' . PHP_EOL .
												'toogleThumb: true, // Spacing between each thumbnails ' . PHP_EOL .
												'controls: ' . ($controlNav ? 'true' : 'false') . ', // Whether to display prev/next buttons.' . PHP_EOL .
												'hideControlOnEnd: false, // If true, prev/next button will be hidden on first/last image.' . PHP_EOL .
												'loop: ' . ($continous ? 'true' : 'false') . ', // Allows to go to the other end of the gallery at first/last img.' . PHP_EOL .
												'auto: ' . ($autoStart ? 'false' : 'true') . ', // Enables slideshow mode.' . PHP_EOL .
												'pause: ' . ($sliderPause >= 0 ? (int)$sliderPause : 3500) . ', // Delay (in ms) between transitions in slideshow mode.' . PHP_EOL .
												'escKey: true, // Whether lightGallery should be closed when user presses "Esc".' . PHP_EOL .
												'closable: true, //allows clicks on dimmer to close gallery' . PHP_EOL .
												'counter: true, // Shows total number of images and index number of current image.' . PHP_EOL .
												'download: false, // Shows total number of images and index number of current image.' . PHP_EOL .
												'hash: false, // Shows total number of images and index number of current image.' . PHP_EOL .
												'swipeThreshold: 50, // How far user must swipe for the next/prev image (in px).' . PHP_EOL .
												'enableTouch: true, // Enables touch support' . PHP_EOL .
												'enableDrag: true // Enables desktop mouse drag support' . PHP_EOL .
												#'youtubePlayerParams: false, // See: https://developers.google.com/youtube/player_parameters' . PHP_EOL .
												#'videoMaxWidth: "855px", // Limits video maximal width (in px).' . PHP_EOL .
											'});' . PHP_EOL .
											'};' . PHP_EOL .
										  '})(jQuery);' . PHP_EOL .
										'head.ready("ccInitScript", function(){
											$.addInitFunction({name: "$.myLightGallery' . self::$randID . '", params: ""});
											if(typeof(cc.feMode) == "undefined" || !cc.feMode){
												$("document").ready(function(){
													$.myLightGallery' . self::$randID . '();
												});
											}
										});' . PHP_EOL;
				break;
						
			case "thumbs2":
				$this->cssFiles[]						= "extLibs/jquery/simpleLightbox/simplelightbox.min.css";
				$this->scriptFiles["simplelightbox"]	= "extLibs/jquery/simpleLightbox/simple-lightbox.min.js";
				
				$this->scriptCode[]		= '(function($){' . PHP_EOL .
											'$.mySimpleLightbox' . self::$randID . ' = function(){' . PHP_EOL .
											'$("#simpleLightbox-' . self::$randID . ' a").simpleLightbox({' . PHP_EOL .
												'overlay: true, // show an overlay or not' . PHP_EOL .
												'spinner: true, // show spinner or not' . PHP_EOL .
												'nav: true, // show arrow-navigation or not' . PHP_EOL .
												'navText: ["◄","►"], // text or html for the navigation arrows' . PHP_EOL .
												'captions: ' . ($this->showTitle || $this->showText ? 'true' : 'false') . ', // show captions if availabled or not' . PHP_EOL .
												'captionsData: "data-title", // get the caption from title or data-title attribute' . PHP_EOL .
												'close: true,  // show the close button or not' . PHP_EOL .
												'closeText: "✖", // Show Content once it is fully loaded.' . PHP_EOL .
												'fileExt: "png|jpg|jpeg|gif", // list of fileextensions the plugin works with' . PHP_EOL .
												'animationSpeed: 250, // how long takes the slide animation' . PHP_EOL .
												'preloading: true, // allows preloading next und previous images' . PHP_EOL .
												'enableKeyboard: true, // allow keyboard arrow navigation and close with ESC key' . PHP_EOL .
												'loop: ' . ($continous ? 'true' : 'false') . ', // Allows to go to the other end of the gallery at first/last img.' . PHP_EOL .
												'docClose: true, // closes the lightbox when clicking outside' . PHP_EOL .
												'swipeTolerance: 50, // how much pixel you have to swipe, until next or previous image' . PHP_EOL .
												'className: "simple-lightbox", // adds a class to the wrapper of the lightbox' . PHP_EOL .
												'widthRatio: 0.8, // Ratio of image width to screen width' . PHP_EOL .
												'heightRatio: 0.9 // Ratio of image height to screen height' . PHP_EOL .
											'});' . PHP_EOL .
											'$("#simpleLightbox-' . self::$randID . ' a").on("open.simplelightbox", function() {}); // this event fires before the lightbox opens' . PHP_EOL .
											'$("#simpleLightbox-' . self::$randID . ' a").on("opened.simplelightbox", function() {}); // this event fires after the lightbox was opened' . PHP_EOL .
											'$("#simpleLightbox-' . self::$randID . ' a").on("close.simplelightbox", function() {}); // this event fires before the lightbox closes' . PHP_EOL .
											'$("#simpleLightbox-' . self::$randID . ' a").on("closed.simplelightbox", function() {}); // this event fires after the lightbox was closed' . PHP_EOL .
											'};' . PHP_EOL .
										  '})(jQuery);' . PHP_EOL .
										'head.ready("ccInitScript", function(){
											$.addInitFunction({name: "$.mySimpleLightbox' . self::$randID . '", params: ""});
											if(typeof(cc.feMode) == "undefined" || !cc.feMode){
												head.ready("simplelightbox", function(){
													$("document").ready(function(){
														$.mySimpleLightbox' . self::$randID . '();
													});
												});
											}
										});' . PHP_EOL;
				break;
						
			case "slider":
				$this->cssFiles[]					= "extLibs/jquery/lightSlider/css/lightslider.min.css";
				#$this->cssFiles[]					= "extLibs/jquery/lightGallery/css/lightgallery.min.css";
				#$this->scriptFiles["lightgallery"]	= "extLibs/jquery/lightGallery/js/lightgallery.min.js";
				$this->scriptFiles["lightslider"]	= "extLibs/jquery/lightSlider/js/lightslider.min.js";
				
				$this->scriptCode[]		= '(function($){' . PHP_EOL .
											'$.myLightSlider' . self::$randID . ' = function(){' . PHP_EOL .
											'$("#lightSlider-' . self::$randID . ':not(.loaded)").lightSlider({' . PHP_EOL .
												'item: ' . (!empty($this->maxImgNumber) && is_numeric($this->maxImgNumber) ? $this->maxImgNumber : 1) . ',' . PHP_EOL .
												'autoWidth: false,' . PHP_EOL .
												'slideMove: ' . (!empty($this->maxImgNumber) && is_numeric($this->maxImgNumber) ? $this->maxImgNumber : 1) . ', // slidemove will be 1 if loop is true' . PHP_EOL .
												'slideMargin: 0,' . PHP_EOL .
												'addClass: "",' . PHP_EOL .
												'mode: "slide",// Type of transition "slide" and "fade"' . PHP_EOL .
												'useCSS: true,' . PHP_EOL .
												'cssEasing: "ease", // cubic-bezier(0.25, 0, 0.25, 1)' . PHP_EOL .
												'easing: "linear", // for jquery animation' . PHP_EOL .
												'speed: ' . ($sliderSpeed > 0 ? (int)$sliderSpeed : 600) . ',' . PHP_EOL .
												'auto: ' . ($autoStart ? 'true' : 'false') . ',' . PHP_EOL .
												'loop: ' . ($continous ? 'true' : 'false') . ',' . PHP_EOL .
												'pauseOnHover: false,' . PHP_EOL .
												'slideEndAnimation: false,' . PHP_EOL .
												'pause: ' . ($sliderPause >= 0 ? (int)$sliderPause : 3500) . ',' . PHP_EOL .
												'keyPress: false,' . PHP_EOL .
												'controls: true,' . PHP_EOL .
												'prevHtml: "",' . PHP_EOL .
												'nextHtml: "",' . PHP_EOL .
												'rtl: false,' . PHP_EOL .
												'adaptiveHeight: false,' . PHP_EOL .
												'vertical: false,' . PHP_EOL .
												'verticalHeight: "auto",' . PHP_EOL .
												'vThumbWidth: 100,' . PHP_EOL .
												'thumbItem: ' . (!empty($this->maxImgNumber) && is_numeric($this->maxImgNumber) ? $this->maxImgNumber : 10) . ',' . PHP_EOL .
												'pager: ' . ($controlNav ? 'true' : 'false') . ',' . PHP_EOL .
												'gallery: ' . ($controlNav && $controlNavType == "img" ? 'true' : 'false') . ',' . PHP_EOL .
												'galleryMargin: 5,' . PHP_EOL .
												'thumbMargin: 5,' . PHP_EOL .
												'currentPagerPosition: "middle",' . PHP_EOL .
												'enableTouch: true,' . PHP_EOL .
												'enableDrag: true,' . PHP_EOL .
												'freeMove: true,' . PHP_EOL .
												'swipeThreshold: 40,' . PHP_EOL .
												'responsive : [],' . PHP_EOL .
												'onBeforeStart: function(el) {}, // Executes immediately after the gallery is loaded.' . PHP_EOL .
												'onSliderLoad: function(el) {
													$(el).addClass("loaded");
													/* el.lightGallery({
														selector: "#lightSlider-' . self::$randID . ' .lslide"
													});
													*/
												},' . PHP_EOL .
												
												'onBeforeSlide  : function(el) {}, // Executes immediately after each transition.' . PHP_EOL .
												'onAfterSlide   : function(el) {}, // Executes immediately before each "Next" transition.' . PHP_EOL .
												'onBeforeNextSlide   : function(el) {}, // Executes immediately before each "Prev" transition.' . PHP_EOL .
												'onBeforePrevSlide : function(el) {} // Executes immediately before the start of the close process.' . PHP_EOL .
											'});' . PHP_EOL .
											'};' . PHP_EOL .
										  '})(jQuery);' . PHP_EOL .
										'head.ready("ccInitScript", function(){
											$.addInitFunction({name: "$.myLightSlider' . self::$randID . '", params: ""});
											if(typeof(cc.feMode) == "undefined" || !cc.feMode){
												$("document").ready(function(){
													$.myLightSlider' . self::$randID . '();
												});
											}
										});' . PHP_EOL;
				break;
				
			case "slider2":
			case "nivoSlider":
				$this->cssFiles[]					= "extLibs/jquery/nivoSlider/nivo-slider.css";
				$this->cssFiles[]					= "extLibs/jquery/nivoSlider/themes/default/default.css";
				$this->scriptFiles["nivoslider"]	= "extLibs/jquery/nivoSlider/jquery.nivo.slider.pack.js";
				
				$this->scriptCode[]		= '(function($){' . PHP_EOL .
											'$.myNivoSlider' . self::$randID . ' = function(){' . PHP_EOL .
											'$("#nivoSlider-' . self::$randID . '").nivoSlider({' . PHP_EOL .
												'effect: "random", // Specify sets like: "fold,fade,sliceDown,random"' . PHP_EOL .
												'slices: 9, // For slice animations' . PHP_EOL .
												'boxCols: 9, // For box animations' . PHP_EOL .
												'boxRows: 6, // For box animations' . PHP_EOL .
												'animSpeed: ' . ($sliderSpeed > 0 ? (int)$sliderSpeed : 600) . ', // Slide transition speed' . PHP_EOL .
												'pauseTime: ' . ($sliderPause >= 0 ? (int)$sliderPause : 3500) . ', // How long each slide will show' . PHP_EOL .
												'startSlide: 0, // Set starting Slide (0 index)' . PHP_EOL .
												'directionNav: true, // Next & Prev navigation' . PHP_EOL .
												'controlNav: ' . ($controlNav ? 'true' : 'false') . ', // 1,2,3... navigation' . PHP_EOL .
												'controlNavThumbs: ' . ($controlNavType == "img" ? 'true' : 'false') . ', // Use thumbnails for Control Nav' . PHP_EOL .
												'pauseOnHover: ' . ($continous ? 'true' : 'false') . ', // Stop animation while hovering' . PHP_EOL .
												'manualAdvance: ' . ($autoStart ? 'false' : 'true') . ', // Force manual transitions' . PHP_EOL .
												'prevText: "Prev", // Prev directionNav text' . PHP_EOL .
												'nextText: "Next", // Next directionNav text' . PHP_EOL .
												'randomStart: false, // Start on a random slide' . PHP_EOL .
												'beforeChange: function(){$("#nivoSlider-' . self::$randID . ' .nivo-caption").removeClass("nivo-current-caption");}, // Triggers before a slide transition' . PHP_EOL .
												'afterChange: function(){
													var nCap = $("#nivoSlider-' . self::$randID . ' .nivo-caption");
													var capClass = nCap.html() == "" ? " nivo-empty-caption" : "";
													nCap.addClass("nivo-current-caption" + capClass);
												}, // Triggers after a slide transition' . PHP_EOL .
												'slideshowEnd: function(){}, // Triggers after all slides have been shown' . PHP_EOL .
												'lastSlide: function(){}, // Triggers when last slide is shown' . PHP_EOL .
												'afterLoad: function(){$("#nivoSlider-' . self::$randID . ' .nivo-caption:first").addClass("nivo-current-caption");} // Triggers when slider has loaded' . PHP_EOL .
											'});' . PHP_EOL .
											'};' . PHP_EOL .
										  '})(jQuery);' . PHP_EOL .
										'head.ready("ccInitScript", function(){
											$.addInitFunction({name: "$.myNivoSlider' . self::$randID . '", params: ""});
											if(typeof(cc.feMode) == "undefined" || !cc.feMode){
												$("document").ready(function(){
													$.myNivoSlider' . self::$randID . '();
												});
											}
										});' . PHP_EOL;
				break;
			
			case "slimbox":
			case "lightbox":
			case "random":
				$this->cssFiles[]				= "extLibs/jquery/slimbox2/slimbox2.css";
				$this->scriptFiles["slimbox"]	= "extLibs/jquery/slimbox2/slimbox2.js";
				break;
			
			case "portfolio":
				$this->cssFiles[]						= "extLibs/jquery/justifiedGallery/css/justifiedGallery.min.css";
				$this->cssFiles[]						= "extLibs/jquery/colorbox/style1/colorbox.css";
				$this->scriptFiles["justifiedgallery"]	= "extLibs/jquery/justifiedGallery/js/jquery.justifiedGallery.min.js";
				$this->scriptFiles["jgcolorbox"]		= "extLibs/jquery/colorbox/jquery.colorbox-min.js";
				
				$this->scriptCode[]		= '(function($){' . PHP_EOL .
											'$.myJustifiedGallery' . self::$randID . ' = function(){' . PHP_EOL .
											'$("#justifiedGallery-' . self::$randID . '").justifiedGallery({' . PHP_EOL .
												'rowHeight: ' . (!empty($this->maxImgNumber) && is_numeric($this->maxImgNumber) ? 720 / $this->maxImgNumber : THUMB_SIZE) . ', // The preferred height of rows in pixel.' . PHP_EOL .
												'maxRowHeight: ' . THUMB_SIZE . ', // A number (e.g 200) which specifies the maximum row height in pixel. A negative value to dont have limits. Alternatively, a string which specifies a percentage (e.g. 200% means that the row height cant exceed 2 * rowHeight)' . PHP_EOL .
												'sizeRangeSuffixes: {}, // Describes the suffix for each size range. By default the plugin doesnt search for other thumbnails. To agree with the Flickrs suffixes you should change it in the following way:{"lt100":"_t","lt240":"_m","lt320":"_n","lt500":"","lt640":"_z","lt1024":"_b"}	The keys could be specified also as numbers (e.g. {512:"_small", 1024:"_big"} to specify the "_small" suffix for images which are less than 512px on the longest side, and "_big" for the bigger ones).' . PHP_EOL .
												'lastRow: "nojustify", // Decide if you want to justify the last row (i.e. "justify") or not (i.e. "nojustify"), or to hide the row if it can"t be justified (i.e. "hide")' . PHP_EOL .
												'fixedHeight: false, // Decide if you want to have a fixed height. This mean that all the rows will be exactly with the specified rowHeight.' . PHP_EOL .
												'captions: ' . ($this->showTitle || $this->showText ? 'true' : 'false') . ', // Decide if you want to show the caption or not, that appears when your mouse is over the image.' . PHP_EOL .
												'margins: 10, // Decide the margins between the images' . PHP_EOL .
												'border: -1, // Decide the border size of the gallery. With a negative value the border will be the same as the margins.' . PHP_EOL .
												'waitThumbnailsLoad: true, // In presence of width and height attributes in thumbnails, the layout is immediately built, and the thumbnails will appear randomly while they are loaded.' . PHP_EOL .
												'randomize: false, // Automatically randomize or not the order of photos.' . PHP_EOL .
												'filter: false, // Can be:' . PHP_EOL .
																'// false: for a disabled filter.' . PHP_EOL .
																'// a string: an entry is kept if entry.is(filter string) returns true (see jQuerys .is() function for further information).' . PHP_EOL .
																'// a function: invoked with arguments (entry, index, array). Return true to keep the entry, false otherwise. see Array.prototype.filter for further information.' . PHP_EOL .
												'sort: false, // Can be:' . PHP_EOL .
																'// false to do not sort.' . PHP_EOL .
																'// a function to sort them using the function as comparator (see Array.prototype.sort()).' . PHP_EOL .
												'selector: "> a, > div:not(.spinner)", // Used to determines which are the entries of the gallery.' . PHP_EOL .
												'extension: /.[^.]+$/, // Specify the extension for the images with a regex. Is used to reconstruct the filename of the images, change it if you need. For example /.jpg$/ is to detect only the ".jpg" extension and no more.' . PHP_EOL .
												'refreshTime: 250, // The time that the plugin waits before checking the page size, and if it is changed it recreates the gallery layout' . PHP_EOL .
												'rel: "colorbox", // To rewrite all the links rel attribute with the specified value. For example can be "gallery1", and is usually used to create gallery group for the lightbox (e.g. Colorbox)' . PHP_EOL .
												'target: "", // To rewrite all the links target attribute with the specified value. For example, if you dont use a lightbox, specifying "_blank", all the images will be opened to another page.' . PHP_EOL .
												'justifyThreshold: 0.65, // If "available space" / "row width" > 0.75 the last row is justified, even though the lastRow setting is "nojustify".' . PHP_EOL .
												'cssAnimation: false, // Use or not css animations. Using css animations you can change the behavior changing the justified gallery CSS file, or overriding that rules.' . PHP_EOL .
												'imagesAnimationDuration: 300, // Image fadeIn duration (in milliseconds).' . PHP_EOL .
												'captionSettings: { animationDuration: 500,' . PHP_EOL .
																	'visibleOpacity: 0.7,' . PHP_EOL .
																	'nonVisibleOpacity: 0.0 } // Caption settings. To configure the animation duration (in milliseconds), the caption opacity when the mouse is over (i.e. it should be visible), and the caption opacity when the mouse is not over (i.e. it should be not visible).' . PHP_EOL .
											'}).on("jg.complete", function(){' . PHP_EOL .
												'$(this).find("a").colorbox({' . PHP_EOL .
													'maxWidth : "80%",' . PHP_EOL .
													'maxHeight : "80%",' . PHP_EOL .
													'opacity : 0.8,' . PHP_EOL .
													'transition : "elastic",' . PHP_EOL .
													'rel:"colorbox",' . PHP_EOL .
													'current:"' . sprintf(ContentsEngine::replaceStaText("{s_text:picoftotal}"), "{current}", "{total}") . '"' . PHP_EOL .
												'});' . PHP_EOL .
											'});' . PHP_EOL .
											'};' . PHP_EOL .
										  '})(jQuery);' . PHP_EOL .
										'head.ready("ccInitScript", function(){
											$.addInitFunction({name: "$.myJustifiedGallery' . self::$randID . '", params: ""});
											if(typeof(cc.feMode) == "undefined" || !cc.feMode){
												$("document").ready(function(){
													$.myJustifiedGallery' . self::$randID . '();
												});
											}
										});' . PHP_EOL;
				break;
			
			case "portfolio2":
				$this->cssFiles[]						= "extLibs/jquery/portfolio/portfolio.jquery.min.css";
				$this->scriptFiles["portfoliogallery"]	= "extLibs/jquery/portfolio/portfolio.jquery.min.js";
				
				$this->scriptCode[]		= '(function($){' . PHP_EOL .
											'$.myPortfolioGallery' . self::$randID . '	= function(){' . PHP_EOL .
											'$("#portfolioGallery-' . self::$randID . '").portfolio({' . PHP_EOL .
												'cols: ' . (!empty($this->maxImgNumber) && is_numeric($this->maxImgNumber) ? $this->maxImgNumber : 7) . ', // Number of columns you want your thumbnails to take. Default is 3' . PHP_EOL .
												'transition: "slideDown" // What jQuery transition effect you want. Default is slideDown' . PHP_EOL .
											'});' . PHP_EOL .
											'};' . PHP_EOL .
										  '})(jQuery);' . PHP_EOL .
										'head.ready("ccInitScript", function(){
											$.addInitFunction({name: "$.myPortfolioGallery' . self::$randID . '", params: ""});
											if(typeof(cc.feMode) == "undefined" || !cc.feMode){
												$("document").ready(function(){
													$.myPortfolioGallery' . self::$randID . '();
												});
											}
										});' . PHP_EOL;
				break;
			
			case "portfolio3":
				$this->cssFiles[]					= "extLibs/jquery/filterizr/css/filterizr.min.css";
				$this->scriptFiles["filterizr"]		= "extLibs/jquery/filterizr/jquery.filterizr.min.js";

				// Include lity lightbox if use link
				if($this->useLink) {
					$this->cssFiles[]					= "extLibs/jquery/lity/lity.min.css";
					$this->scriptFiles["lity"]		= "extLibs/jquery/lity/lity.min.js";
				}
				
				$this->scriptCode[]		= '(function($){
											$.myFilterizrGallery' . self::$randID . '	= function(){' . PHP_EOL .
											'$("#filterizrGallery-' . self::$randID . '").filterizr({
												animationDuration: 0.3,
												callbacks: {
													onFilteringStart: function(obj) {
														var filterBtns = $("#filterizrGalleryFilters-' . self::$randID . '").find("[data-filter]");
														if(!filterBtns.length){
															return false;
														}
														filterBtns.removeClass("active");
														filterBtns.filter("[data-filter=\'" + obj.options.filter + "\']").addClass("active");
													},
													onFilteringEnd: function(obj) { },
													onShufflingStart: function(obj) { },
													onShufflingEnd: function(obj) { },
													onSortingStart: function(obj) { },
													onSortingEnd: function(obj) { }
												},
												delay: 0,
												delayMode: "progressive",
												easing: "ease-out",
												filter: "all",
												filterOutCss: {
													opacity: 0,
													transform: "scale(0.5)"
												},
												filterInCss: {
													opacity: 1,
													transform: "scale(1)"
												},
												layout: "sameWidth",
												selector: "#filterizrGallery-' . self::$randID . '",
												itemClass: "filterizr-item",
												setupControls: true
											});' . PHP_EOL .
											'};' . PHP_EOL .
										  '})(jQuery);' . PHP_EOL .
										'head.ready("ccInitScript", function(){
											$.addInitFunction({name: "$.myFilterizrGallery' . self::$randID . '", params: ""});
											if(typeof(cc.feMode) == "undefined" || !cc.feMode){
												$("document").ready(function(){
													$.myFilterizrGallery' . self::$randID . '();
												});
											}
										});' . PHP_EOL;
				break;
				
		}
		
	}



	/**
	 * Erstellt eine Bildergalerie
	 * 
	 * @access	public
	 * @return	string
	 */
	public function getGallery()
	{
	
		// Falls kein Galeriename angegeben
		if($this->galleryName == "")				
			return '<p class="error {t_class:alert} {t_class:warning}">{s_notice:nogall}</p>';
	
		// Falls Galerie nicht vorhanden
		if($this->singleImg == false
		&& $this->galleryType != "archive"
		&& !is_dir(PROJECT_DOC_ROOT . '/' . CC_GALLERY_FOLDER . '/' . $this->galleryName)
		)	
			return '<p class="error {t_class:alert} {t_class:warning}">{s_notice:gallnotfound} &quot;' . htmlspecialchars($this->galleryName) . '&quot;.</p>';
		
		
		$this->galleryData	= $this->getGalleryData(); // Galeriedaten-Array holen
	
		// Falls kein Galerieordner gefunden wurde
		if($this->galleryData === false)
			return '<p class="error {t_class:alert} {t_class:warning}">{s_notice:gallnotfound} &quot;' . htmlspecialchars($this->galleryName) . '&quot;.</p>';
		
		// Falls keine Galeriedaten gefunden wurden
		if(count($this->galleryData) == 0)
			return '<p class="error {t_class:alert} {t_class:warning}">{s_notice:nogall}</p>';
		
		
		// Galerie ausgeben
		$gallery	= $this->printGallery();
		
		
		return $gallery;

	}
	


	/**
	 * Erstellt eine Gallerieausgabe je nach Galerietyp
	 * 
	 * @access	public
	 * @return	string
	 */
	public function printGallery()
	{
		
		$gallery	= "";
		
		// Galerieüberschrift
		if($this->gallHeader != "")
			$gallery .=			'<h4 class="cc-h4 toggle">' . htmlspecialchars($this->gallHeader) . '</h4>';
		
		
		// Falls ein Galeriearchiv generiert werden soll
		if($this->galleryType == "archive") {
			
			$gallery .=			'<div class="gallArchive">' . PHP_EOL .
								'<ol>' . PHP_EOL;
			
			// Galeriearchiv generieren
			if(count($this->galleryData) > 0) {

				// Galerienamen verschlüsseln
				require_once(PROJECT_DOC_ROOT."/inc/classes/Modules/class.myCrypt.php"); // Klasse myCrypt einbinden
				
				// myCrypt Instanz
				$crypt = new myCrypt();

				foreach($this->galleryData as $gallEntry) {
					
					$gallName			= htmlspecialchars(str_replace("_", " ", $gallEntry['gallery_name']));
					$gallNameEncrypt	= htmlspecialchars($gallEntry['gallery_name']);

					// Encrypt string
					$gallNameEncrypt	= $crypt->encrypt($gallNameEncrypt);
					
					if(isset($GLOBALS['_GET']['gall']) && $GLOBALS['_GET']['gall'] == $gallNameEncrypt)
						$gallery .=		'<li class="gallLink active"><span>' . $gallName . '</span></li>' . PHP_EOL;
					else
						$gallery .=		'<li class="gallLink"><a href="?gall=' . $gallNameEncrypt . '">' . $gallName . '</a></li>' . PHP_EOL;
						
				}
			}
			
			$gallery .=			'</ol>' . PHP_EOL;
			$gallery .=			'</div>' . PHP_EOL;
		}
		

		// Falls eine einfache Slideshow mit überblendeffekt generiert werden soll
		if($this->galleryType == "fader") {
		
			$i			= 1;
			$gallery 	=		'<div class="slideshow simpleFaderSlideshow">' . PHP_EOL;
			
			foreach($this->galleryData as $gallEntry) {
				
				$imgFile	= $gallEntry['img_file'];
				$caption	= "";
				$gallTitle	= trim($gallEntry['title_' . $this->lang]);
				
				$gallText	= str_replace("{#root}", PROJECT_HTTP_ROOT, trim($gallEntry['text_' . $this->lang]));
				$link		= str_replace("{#root}", PROJECT_HTTP_ROOT, trim($gallEntry['link_' . $this->lang]));
				
				// Falls Gallerietext nicht aus HTML-Code besteht, als Paragraph deklarieren
				if($gallText != ""
				&& strpos($gallText, "<") !== 0
				)
					$gallText	= '<p>' . $gallText . '</p>';
				
				if($this->showTitle && $gallTitle != "")
					$caption .= '<h3 class="caption-title">' . $gallTitle . '</h3>';
				
				if($this->showText && $gallText != "")
					$caption .= '<div class="caption-text">' . $gallText . '</div>' .PHP_EOL;

				if($this->useLink
				&& $link != ""
				)
					$caption .=	'<a href="' . htmlspecialchars($link) . '" class="caption-link {t_class:btn} {t_class:btnpri}">{s_link:more}</a>' . PHP_EOL;
				
				if($caption != "")
					$caption		= '<div class="cc-gallery-caption cc-slider-caption {t_class:container}">' . $caption . '</div>' . PHP_EOL;
				
				if(trim($this->galleryData[0]['title_' . $this->lang]) != "")
					$altText	= $this->galleryData[0]['title_' . $this->lang];
				else
					$altText	= pathinfo($imgFile, PATHINFO_FILENAME);
				
				$gallery .=		'<div class="cc-gallery-item">' . PHP_EOL;
				$gallery .=		$this->getGalleryItemTag($imgFile, $this->folder . '/', array("alt" => $altText), false, $i > 1);
				$gallery .=		$caption;
				$gallery .=		'</div>' . PHP_EOL;
				
				$i++;
			
			}
			
			$gallery .=			'</div>' . PHP_EOL;
			
		}
		
		
		// Falls eine thumbnail-Galerie generiert werden soll
		if($this->galleryType == "thumbs") {
		
			$gallery	   .= '<ul id="lightGallery-' . self::$randID . '" class="lightGallery {t_class:row}">' . PHP_EOL;
			
   			$adjustVaules	= array('top', 'bottom');
			$colNo			= is_numeric($this->maxImgNumber) && $this->maxImgNumber > 0 && $this->maxImgNumber <= 12 ? ceil(12 / $this->maxImgNumber) : 3;
			
     		foreach($this->galleryData as $gallEntry) {
				
				$imgFile	= $gallEntry['img_file'];
				$imgSrc		= htmlspecialchars(Modules::getMobileImageSrc($imgFile, $this->folder . '/'));
				$title		= "";
				$gallTitle	= trim($gallEntry['title_' . $this->lang]);
				
				$gallText	= str_replace("{#root}", PROJECT_HTTP_ROOT, trim($gallEntry['text_' . $this->lang]));
				$link		= str_replace("{#root}", PROJECT_HTTP_ROOT, trim($gallEntry['link_' . $this->lang]));
				
				// Falls Gallerietext nicht aus HTML-Code besteht, als Paragraph deklarieren
				if($gallText != ""
				&& strpos($gallText, "<") !== 0
				)
					$gallText = '<p>' . $gallText . '</p>';
				
				if($this->showTitle && $gallTitle != "")
					$title .= '<h4 class="caption-title">' . $gallTitle . '</h4>';
				
				if($this->showText && $gallText != "")
					$title .= $gallText;
				
				/*				
				<li data-responsive-src="mobile1.jpg" > </li>
				<!-- the large version of your image/video -->
				<li data-src="img1.jpg" > </li>
				 
				<!-- Custom html5 video html (will be inserted same like youtube vimeo videos) -->
				<li data-html="video html" /> </li>
				<!-- id or class name of an object(div) which contain your html. -->
				<li data-html="#inlineHtml" > </li>
				 
				<!-- Custom html (Caption description comments ...) -->
				<li data-sub-html="<h3>My caption</h3><p>My description..</p>" /> </li>
				<!-- id or class name of an object(div) which contain your html. -->
				<li data-sub-html="#inlineSubHtml" > </li>
 				*/
				
				$gallery .=		'<li class="col-xs-6 col-md-' . $colNo . '" data-src="' . $imgSrc . '"' . ($title != "" ? ' data-sub-html="' . htmlspecialchars($title) . '"' : '') . '>' . PHP_EOL;
				$gallery .=		'<a href="' . $imgSrc . '" class="thumbnail">' . PHP_EOL;
				
				$attrArr	=	array(	"alt" => pathinfo($imgFile, PATHINFO_FILENAME));
				
				if(!empty($this->imgAttr))
					$attrArr	= array_merge($attrArr, $this->imgAttr);
				
				$gallery .=		$this->getGalleryItemTag($imgFile, $this->folder . '/thumbs/', $attrArr, true);
				
				$gallery .=		'</a>' . PHP_EOL;
				$gallery .=		'</li>' . PHP_EOL;
			
			}
			
			$gallery .=			'</ul>' . PHP_EOL;
		}
		
		
		// Falls eine simpleLightbox-Galerie generiert werden soll
		if($this->galleryType == "thumbs2"
		) {
			
			$gallery	   .= '<div id="simpleLightbox-' . self::$randID . '" class="simpleLightbox {t_class:row}">' . PHP_EOL;
			$colNo			= is_numeric($this->maxImgNumber) && $this->maxImgNumber > 0 && $this->maxImgNumber <= 12 ? ceil(12 / $this->maxImgNumber) : 3;
			
			foreach($this->galleryData as $gallEntry) {
				
				$imgFile	= $gallEntry['img_file'];
				$imgSrc		= Modules::getMobileImageSrc($imgFile, $this->folder . '/');
				$title		= "";
				$caption	= "";
				$gallTitle	= trim($gallEntry['title_' . $this->lang]);
				$gallText	= str_replace("{#root}", PROJECT_HTTP_ROOT, trim($gallEntry['text_' . $this->lang]));
				
				if($gallText != ""
				&& strpos($gallText, "<") !== 0
				)
					$gallText = '<p>' . $gallText . '</p>' . PHP_EOL;
				
				if($this->showTitle && $gallTitle != "") {
					$title		= ' title="' . htmlspecialchars($gallTitle) . '"';
					$caption	= '<h3 class="caption-title">' . htmlspecialchars($gallTitle) . '</h3>' . PHP_EOL;
				}
				
				if($this->showText && $gallText != "")
					$caption	.= $gallText;
				
				$imgAttr		= array("alt" => pathinfo($imgFile, PATHINFO_FILENAME));
				
				if(($this->showTitle && $gallTitle != "")
				|| ($this->showText && $gallText!= "")
				)
					$imgAttr["data-title"]	= $caption;
				
				$gallery .=		'<span class="col-xs-6 col-md-' . $colNo . '">' . PHP_EOL;
				
				$gallery .=		'<a href="' . htmlspecialchars($imgSrc) . '" class="thumbnail" rel="lightbox-' . htmlspecialchars($this->galleryName) . '"' . $title . '>' . PHP_EOL;
				
				$gallery .=		$this->getGalleryItemTag($imgFile, $this->folder . '/thumbs/', $imgAttr, true);
				
				$gallery .=		'</a>' . PHP_EOL;
				
				$gallery .=		'</span>' . PHP_EOL;
				
				$this->imgCount++;

			}
			
			$gallery .=			'</div>' . PHP_EOL;

		}
		
		
		// Falls eine slider-Galerie generiert werden soll
		if($this->galleryType == "slider") {
			
			$gallery	   .= '<ul id="lightSlider-' . self::$randID . '" class="lightSlider">' . PHP_EOL;
			
   			$adjustVaules	= array('top', 'bottom');
			$colNo			= is_numeric($this->maxImgNumber) && $this->maxImgNumber > 0 && $this->maxImgNumber <= 12 ? ceil(12 / $this->maxImgNumber) : 3;
			
     		foreach($this->galleryData as $gallEntry) {
				
				$imgFile	= htmlspecialchars($gallEntry['img_file']);
				$imgSrc		= Modules::getMobileImageSrc($imgFile, $this->folder . '/');
				$title		= "";
				$gallTitle	= trim($gallEntry['title_' . $this->lang]);
				
				$gallText	= str_replace("{#root}", PROJECT_HTTP_ROOT, trim($gallEntry['text_' . $this->lang]));
				$link		= str_replace("{#root}", PROJECT_HTTP_ROOT, trim($gallEntry['link_' . $this->lang]));
		
				$caption		= "";
				
				// Falls Gallerietext nicht aus HTML-Code besteht, als Paragraph deklarieren
				if($gallText != ""
				&& strpos($gallText, "<") !== 0
				)
					$gallText = '<p>' . htmlspecialchars($gallText) . '</p>';
				
				if($this->showTitle && $gallTitle != "")
					$title .= '<h2 class="caption-title">' . htmlspecialchars($gallTitle) . '</h2>';
				
				if($this->showText && $gallText != "")
					$title .= $gallText;

				if($this->useLink
				&& $link != ""
				)
					$title .=	'<a href="' . htmlspecialchars($link) . '" class="{t_class:btn} {t_class:btnpri}">{s_link:more}</a>' . PHP_EOL;
				
				if($title != "")
					$caption	.=	'<div class="lightSlider-caption cc-gallery-caption cc-slider-caption {t_class:container} {t_class:textpri}">' .
									$title .
									'</div>' . PHP_EOL;
				
				$gallery .=		'<li class="cc-gallery-item" data-thumb="' . $this->folder . '/thumbs/' . $imgFile . '" data-src="' . $imgSrc . '">' . PHP_EOL;
				
				$gallery .=		'<img src="' . $imgSrc . '" alt="' . pathinfo($imgFile, PATHINFO_FILENAME) . '" />' . PHP_EOL;
				
				$gallery .=		$caption;
				
				$gallery .=		'</li>' . PHP_EOL;
			
			}
			
			$gallery .=			'</ul>' . PHP_EOL;
		}
		
		
		// Falls eine Slimbox2-Galerie generiert werden soll
		if($this->galleryType == "slimbox"
		|| $this->galleryType == "lightbox"
		|| $this->galleryType == "random"
		) {
			
			$gallery .=			'<div class="slimbox">' . PHP_EOL;
			
			foreach($this->galleryData as $gallEntry) {

				$imgSrc		= Modules::getMobileImageSrc($gallEntry['img_file'], $this->folder . '/');
				$imgFile	= $gallEntry['img_file'];
				
				$attrArr	= array(	"alt" => pathinfo($imgFile, PATHINFO_FILENAME));
				
				if(!empty($this->imgAttr))
					$attrArr	= array_merge($attrArr, $this->imgAttr);
				
				$gallery .=		'<a href="' . $imgSrc . '" rel="lightbox-' . htmlspecialchars($this->galleryName) . '" title="' . htmlspecialchars($gallEntry['title_' . $this->lang]) . ($gallEntry['text_' . $this->lang] != "" ? " --- " . htmlspecialchars($gallEntry['text_' . $this->lang]) : '') . '">' . PHP_EOL;
				
				$gallery .=		$this->getGalleryItemTag($imgFile, $this->folder . '/thumbs/', $attrArr, true);

				$gallery .=		'</a>' . PHP_EOL;
				
				$this->imgCount++;
				
				if($this->maxImgNumber != "" && $this->imgCount >= $this->maxImgNumber) {
					$gallery .=	'<span class="morePics">...</span>' . PHP_EOL;
					break;
				}
			}
			
			$gallery .=			'</div>' . PHP_EOL;

		}
		
		
		// Falls eine Portfolio-Galerie generiert werden soll
		if($this->galleryType == "portfolio") {
		
			$gallery 			.= '<div id="justifiedGallery-' . self::$randID . '" class="justifiedGallery">' . PHP_EOL;
			$scrollItems	 	 = array();
			$showLoadItemsBtn	 = false;
			
			if(empty($this->maxImgNumber)) {
				$this->maxImgNumber	= 20;
			}
			
			$maxLoadItems	= $this->maxImgNumber * 4;
			$i = 1;
			
			foreach($this->galleryData as $gallEntry) {
				
				$imgFile	= htmlspecialchars($gallEntry['img_file']);
				$gallTitle	= trim($gallEntry['title_' . $this->lang]);
				
				if($gallTitle == "")
					$gallTitle	= pathinfo($imgFile, PATHINFO_FILENAME);
				
				$gallText	= str_replace("{#root}", PROJECT_HTTP_ROOT, trim($gallEntry['text_' . $this->lang]));
				#$link		= str_replace("{#root}", PROJECT_HTTP_ROOT, trim($gallEntry['link_' . $this->lang]));
				
				$imgSrc	= Modules::getMobileImageSrc($gallEntry['img_file'], $this->folder . '/');
				
				$gallItem	=	'<a href="' . htmlspecialchars($imgSrc) . '" rel="colorbox" title="' . htmlspecialchars($gallTitle) . ($gallText != "" ? " --- " . htmlspecialchars($gallText) : '') . '">' . 
								'<img src="' . $this->folder . '/thumbs/' . $imgFile . '" alt="' . htmlspecialchars($gallTitle) . '" />' .
								'</a>';
				
				// Falls mehr als maxImgNumber Bilder, Array für scrollItems erweitern
				if($i > $maxLoadItems) {
					$scrollItems[]		= $gallItem;
					$showLoadItemsBtn	= true;
				}
				else
					$gallery	   .= $gallItem;
				
				$i++;
			}
			
			$gallery .=		'</div>' . PHP_EOL;
			
			// Ggf. Load additional Items Button einbinden
			if($showLoadItemsBtn) {
				// Button changes
				$btnDefs	= array(	"type"		=> "button",
										"class"		=> "cc-button-loaditems loadItems {t_class:btnpri} {t_class:margintm}",
										"text"		=> "{s_link:more}",
										"title"		=> $maxLoadItems . '/' . count($this->galleryData),
										"attr"		=> 'data-target="#justifiedGallery-' . self::$randID . '"',
										"icon"		=> "image"
									);
				
				$gallery	   .= parent::getButton($btnDefs);
				
			}
		
			// Bilder bei Scrolling anhängen
			$gallery .=		'<script>
							head.ready("ccInitScript", function(){
								cc.justifiedGalleryCount	= 0;
								cc.justifiedGalleryItems	= [];' . "\n";

			foreach($scrollItems as $item){
				$gallery .=		'cc.justifiedGalleryItems.push(\'' . $item . '\');';
			}
				
			$gallery .=			'cc.fadeInGalleryItems = function(ccJGall, cnt) {
									var newItem	= $(cc.justifiedGalleryItems[cc.justifiedGalleryCount]);
									ccJGall.css("min-height",ccJGall.height() + "px").append(newItem);
									newItem.find("img").fadeTo(1,0).delay((cnt) * 100).fadeTo(800,1);
								};';
		
			$gallery .=			'$("document").ready(function(){
									$(".cc-button-loaditems").click(function(e){
										
										e.preventDefault();
										
										var loadBtn			= $(this);
										var ccJGall			= $(loadBtn.attr("data-target"));
										var ccJGallMaxImgNo	= ' . $maxLoadItems . ';
										
										loadBtn.fadeOut(100);
										
										$.getWaitBar();
										
										for (var i = 1; i <= ccJGallMaxImgNo; i++) {
											cc.fadeInGalleryItems(ccJGall, i);
											cc.justifiedGalleryCount++;
											if(i == ccJGallMaxImgNo){
												ccJGall.justifiedGallery("norewind");
												// if fe mode
												if(cc.feMode){
													setTimeout(function(){
														$.adjustEditElement(ccJGall.closest(".editDiv"));
													}, 100);
												}
												$.removeWaitBar();
											}
										}
										if(!(cc.justifiedGalleryCount >= cc.justifiedGalleryItems.length)){
											var loadedItems	= parseInt(ccJGallMaxImgNo + cc.justifiedGalleryCount);
											var totalItems	= parseInt(ccJGallMaxImgNo + cc.justifiedGalleryItems.length);
											loadBtn.attr("title", loadedItems + "/" + totalItems).delay(ccJGallMaxImgNo * 100 + 500).fadeIn(300);
										}
										return false;
									});
								});
							});
							</script>';
		}
		
		
		// Falls eine Portfolio-Galerie generiert werden soll
		if($this->galleryType == "portfolio2") {
		
			$gallery	.=		'<div id="portfolioGallery-' . self::$randID . '" class="portfolioGallery">' . PHP_EOL;
			
			$thumbs		= "";
			$content	= "";
			
			$i = 1;
			
			foreach($this->galleryData as $gallEntry) {
				
				$imgFile	= $gallEntry['img_file'];

				$gallTitle	= trim($gallEntry['title_' . $this->lang]);
				
				$gallText	= str_replace("{#root}", PROJECT_HTTP_ROOT, trim($gallEntry['text_' . $this->lang]));
				$link		= str_replace("{#root}", PROJECT_HTTP_ROOT, trim($gallEntry['link_' . $this->lang]));
				
				
				$thumbs .=		'<li class="cc-gallery-item">' . PHP_EOL .
								'<a href="#thumb-' . self::$randID . '-' . $i . '" class="thumbnail" style="background-image:url(' . htmlspecialchars($this->folder . '/thumbs/' . $imgFile) . ');">' . PHP_EOL . 
								#'<h4>' . $gallTitle . '</h4>' . PHP_EOL . 
								'<span class="description">' . $gallTitle . '</span>' . PHP_EOL . 
								'</a>' . PHP_EOL .
								'</li>' . PHP_EOL;
				
				$content .=		'<div id="thumb-' . self::$randID . '-' . $i . '">' . PHP_EOL .
								'<div class="media {t_class:halfrow}">' . PHP_EOL;

				$content .=		$this->getGalleryItemTag($imgFile, $this->folder . '/', array("alt" => pathinfo($imgFile, PATHINFO_FILENAME)));
				
				$content .=		'</div>' . PHP_EOL .
								'<div class="captionBox {t_class:halfrow}">' . PHP_EOL . 
								'<h3 class="captionHeader">' . htmlspecialchars($gallTitle) . '</h3>' . PHP_EOL . 
								'<p>' . $gallText . '</p>' . PHP_EOL;

				if($this->useLink
				&& $link != ""
				)
					$content .=	'<a href="' . htmlspecialchars($link) . '" class="{t_class:btn} {t_class:btnpri}">{s_link:more}</a>' . PHP_EOL;
				
				
				$content .=		'</div>' . PHP_EOL;
				$content .=		'</div>' . PHP_EOL;
				
							
				$i++;
			}
			
			$gallery	.=		'<ul class="thumbs">' . PHP_EOL .
								$thumbs .
								'</ul>' . PHP_EOL;
			
			$gallery	.=		'<div class="portfolio-content">' . PHP_EOL .
								$content .
								'</div>' . PHP_EOL;
			
			$gallery .=		'</div>' . PHP_EOL;
		
		}
		
		
		// Falls eine Filterizr Portfolio-Galerie generiert werden soll
		if($this->galleryType == "portfolio3") {
		
			$galleryTags	= $this->getGalleryTags($this->galleryData);
			
			$gallery .=		$this->getGalleryFilterBar($galleryTags, 'filterizrGalleryFilters-' . self::$randID, "filterizr");
			
			$gallery .=		'<div id="filterizrGallery-' . self::$randID . '" class="portfolioGallery filterizrGallery filterizr-container">' . PHP_EOL;
					
			$thumbs		= "";
			$content	= "";
			$colNum		= round($this->maxImgNumber != "" ? 12 / (int)$this->maxImgNumber : 3);
			$colNum		= min(12, $colNum);
			$colNum		= max(1, $colNum);
			
			$i = 1;
			
			foreach($this->galleryData as $gallEntry) {
				
				$imgFile	= $gallEntry['img_file'];
				$itemTags	= explode(",", $gallEntry['img_tags']);
				$tags		= array();
				$caption	= "";
				
				foreach($itemTags as $tag) {
					if(empty($tag)
					|| !in_array($tag, $galleryTags)
					)
						continue;
					
					$tagKey	= array_keys($galleryTags, $tag, true);
					
					if(isset($tagKey[0]))
						$tags[]	= $tagKey[0];
				}
				
				$gallTitle	= trim($gallEntry['title_' . $this->lang]);				
				$gallText	= str_replace("{#root}", PROJECT_HTTP_ROOT, trim($gallEntry['text_' . $this->lang]));
				$link		= str_replace("{#root}", PROJECT_HTTP_ROOT, trim($gallEntry['link_' . $this->lang]));

				if($this->showTitle
				&& $gallTitle != ""
				)
					$caption	.= '<h3 class="filterizr-caption-title">' . htmlspecialchars($gallTitle) . '</h3>' . PHP_EOL;
				
				if($this->showText
				&& $gallText != ""
				) {				
					// Falls Gallerietext nicht aus HTML-Code besteht, als Paragraph deklarieren
					if(strpos($gallText, "<") !== 0)
						$gallText = '<p>' . htmlspecialchars($gallText) . '</p>';
					
					$caption	.= '<div class="filterizr-caption-text">' . $gallText . '</div>' . PHP_EOL;
				}
				
				if($caption != "")
					$caption	= '<div class="filterizr-caption-wrapper">' . $caption . '</div>' . PHP_EOL;
				
				if($this->useLink) {
					if($link != "")
						$caption	= '<a href="' . htmlspecialchars($link) . '" class="filterizr-link">' . $caption . '</a>' . PHP_EOL;
					else {
					
						$imgSrc		= Modules::getMobileImageSrc($imgFile, $this->folder . '/');
						
						if(empty($caption)) {
							
							$btnDefs	= array(	"href"		=> $imgSrc,
													"class"		=> "cc-button-enlarge {t_class:btnpri} {t_class:btnlg} {t_class:vcenter}",
													"text"		=> "",
													"attr"		=> 'data-lity="true"',
													"icon"		=> "search",
													"icontext"	=> ""
												);
							
							$caption	= parent::getButtonLink($btnDefs);
						}
						else
							$caption	= '<a href="' . htmlspecialchars($imgSrc) . '" data-lity="true">' . $caption . '</a>' . PHP_EOL;
					}
				}
				
				if($caption != "")
					$caption	= '<div class="filterizr-caption cc-caption-wrapper">' . $caption . '</div>' . PHP_EOL;
				
				
				$content .=		'<div class="filterizr-item col-md-' . $colNum . ' cc-gallery-item" data-category="' . (!empty($tags) ? '' . implode(",", $tags) : '1000') . '">' . PHP_EOL;
				$content .=		'<div class="filterizr-item-inner">' . PHP_EOL;
				
				$content .=		$this->getGalleryItemTag($imgFile, $this->folder . '/', array("alt" => pathinfo($imgFile, PATHINFO_FILENAME)));
				
				$content .=		$caption;
				
				$content .=		'</div>' . PHP_EOL;
				$content .=		'</div>' . PHP_EOL;
							
				$i++;
			}
			
			$gallery	.=		$content;
			
			$gallery .=		'</div>' . PHP_EOL;
		
		}
		
		
		// Falls eine nivoSlider-Galerie generiert werden soll
		if($this->galleryType == "nivoSlider"
		|| $this->galleryType == "slider2"
		) {
		
			$gallery .=			'<div class="sliderWrapper slider-wrapper theme-default">' . PHP_EOL .
								'<div id="nivoSlider-' . self::$randID . '" class="nivoSlider">' . PHP_EOL;
			
			$caption		= "";
			
     		foreach($this->galleryData as $gallEntry) {
				
				$imgFile	= $gallEntry['img_file'];
				$imgSrc		= htmlspecialchars(Modules::getMobileImageSrc($imgFile, $this->folder . '/'));
				$fileName	= htmlspecialchars(pathinfo($imgFile, PATHINFO_FILENAME));
				$title		= "";
				$gallTitle	= trim($gallEntry['title_' . $this->lang]);

				$gallText	= str_replace("{#root}", PROJECT_HTTP_ROOT, trim($gallEntry['text_' . $this->lang]));
				$link		= str_replace("{#root}", PROJECT_HTTP_ROOT, trim($gallEntry['link_' . $this->lang]));
				
				// Falls Gallerietext nicht aus HTML-Code besteht, als Paragraph deklarieren
				if($gallText != ""
				&& strpos($gallText, "<") !== 0
				)
					$gallText = '<p>' . htmlspecialchars($gallText) . '</p>';
				
				if($this->showTitle && $gallTitle != "")
					$title .= '<h2 class="caption-title">' . htmlspecialchars($gallTitle) . '</h2>' . PHP_EOL ;
				
				if($this->showText && $gallText != "")
					$title .= $gallText;
				
				if($this->useLink
				&& $link != ""
				)
					$gallery .=	'<a href="' . htmlspecialchars($link) . '">' . PHP_EOL;
				
				$gallery .=		'<img src="' . $imgSrc . '" data-thumb="' . $this->folder . '/thumbs/' . $imgFile . '" alt="' . $fileName . '" title="#caption-' . $fileName . '" />' . PHP_EOL;
				
				if($this->useLink
				&& $link != ""
				)
					$gallery .=	'</a>' . PHP_EOL;

				$caption	.=	'<div id="caption-' . $fileName . '" class="nivo-html-caption' . ($title == "" ? ' nivo-empty-caption' : '') . ' cc-gallery-caption cc-slider-caption">' .
								$title .
								'</div>' . PHP_EOL;
			}
			
			$gallery .=			'</div>' . PHP_EOL;
			
			$gallery .=			$caption;
			
			$gallery .=			'</div>' . PHP_EOL;
		
		}
		
		return $gallery;

	}
	


	/**
	 * Gibt Galleriedaten zurück
	 * 
	 * @param	string	$queryExt	optionaler Zusatz für db-Query-String (default = '')
	 * @param	string	$folder		falls ein Ordner angegeben ist, Bilder aus diesem Einlesen (default = '')
	 * @access	public
	 * @return	string
	 */
	public function getGalleryData($queryExt = "", $folder = "")
	{
	
		// Falls ein Ordner angegeben ist, Bilder aus diesem einlesen
		if($folder != "")
		
			return getGalleryDataFromFolder($folder);
			
		
		// Session-Vars-Array
		$this->g_Session	= Security::getSessionVars();

		
		// Galeriedaten in Array speichern
		$queryGall	= array();
		
		// Falls Archive
		if($this->galleryType == "archive") {
		
			if(isset($this->g_Session['group']) && $this->g_Session['group'] != "")
				$this->userGroup = $this->DB->escapeString($this->g_Session['group']);
			
			$this->dbOrder = "ORDER BY `sort_id` DESC,`gallery_name`";
			

			// Datenbanksuche zum Auslesen von Galeriebildern und Bildtexten
			$queryGall = $this->DB->query( "SELECT `gallery_name` 
											FROM `" . DB_TABLE_PREFIX . "galleries` 
											WHERE (`group` = 'public'
												OR `group` = '" . $this->userGroup . "') " .
											$queryExt . " " .
											$this->dbOrder . "
											", false);
			
			#var_dump($queryGall);
		}
		
		// Falls Einzelbild
		elseif($this->singleImg === true) {
			
			// Falls files-Ordner, den Pfad ermitteln
			if(isset($this->galleryName) && strpos($this->galleryName, ">") !== false) {
				$filesImgArr		= explode(">", $this->galleryName);
				$galleryImg			= array_pop($filesImgArr);					
				$imgPath			= CC_FILES_FOLDER . "/" . implode("/", $filesImgArr);
			}
			else {
				$imgPath = CC_IMAGE_FOLDER;
				$galleryImg = $this->galleryName;
			}
			$this->folder = PROJECT_HTTP_ROOT . '/' . $imgPath;
			$queryGall[0]['img_file'] = $galleryImg;
			$queryGall[0]['title_' . $this->lang] = $this->imgTitle;
			$queryGall[0]['text_' . $this->lang] = "";
			$queryGall[0]['link_' . $this->lang] = "";
		}
		
		// Andernfalls Galeriebilder
		elseif($this->galleryNameDB != "") {
				
			// Datenbanksuche zum Auslesen von Galeriebildern und Bildtexten
			$queryGall = $this->DB->query( "SELECT * 
											FROM `" . DB_TABLE_PREFIX . "galleries_images` as img 
											LEFT JOIN `" . DB_TABLE_PREFIX . "galleries` as gall 
												ON gall.id = img.`gallery_id` 
											WHERE img.`show` = 1 
												AND gall.`gallery_name` = '" . $this->galleryNameDB . "' " .
											$queryExt . " " .
											$this->dbOrder . "
											", false);
			
	
			#var_dump($queryGall);			
		}
		
		return $queryGall;
		
	}
	


	/**
	 * Methode zum Auslesen eines Bilderordners
	 * 
	 * @param	string	Ordnername
	 * @access	public
	 * @return	string
	 */
	public function getGalleryDataFromFolder($folder)
	{
	
		if(!is_dir($folder))
		
			return false;
		
		
		// Galeriebilder aus Ordner lesen
		$gallData	= array();
		
		$handle = opendir($folder);
		
		while($content = readdir($handle)) {
			if($content != ".."
			&& strpos($content, ".") !== 0
			&& !is_dir($folder . '/' . $content)
			) {
				$gallData[] = $content;
			}
		}
		closedir($handle);
		
		return $gallData;
		
	}
	


	/**
	 * getGalleryTags
	 * 
	 * @param	array	galleryData
	 * @access	public
	 * @return	string
	 */
	public function getGalleryTags($galleryData)
	{
	
		$gallTags	= array();
		
		foreach($galleryData as $gallEntry) {
			
			$tags		= explode(",", $gallEntry['img_tags']);
			if(!empty($tags))
				$gallTags	= array_merge($gallTags, $tags);

		}
		
		$gallTags	= array_unique(array_filter($gallTags));		
		
		if(count($gallTags)) {
			$gallTags	= array_combine(range(1, count($gallTags)), array_values($gallTags));
		}
		
		return $gallTags;
		
	}
	


	/**
	 * getGalleryFilterBar
	 * 
	 * @param	array	galleryTags
	 * @access	public
	 * @return	string
	 */
	public function getGalleryFilterBar($galleryTags, $id, $classPrefix = "filter")
	{
	
		$output		= "";
		
		if(empty($galleryTags)
		|| !is_array($galleryTags)
		)
			return "";
		
		$output .=	'<div id="' . $id . '" class="' . $classPrefix . '-options {t_class:btngroup}">' . PHP_EOL;
		$output .=	'<button class="' . $classPrefix . '-button {t_class:btn} {t_class:btnpri} active" data-filter="all">{s_common:all}</button>' . PHP_EOL;
		
		foreach($galleryTags as $key => $tag) {
			
			$output .=	'<button class="' . $classPrefix . '-button {t_class:btn} {t_class:btnpri}" data-filter="' . $key . '">' . $tag . '</button>' . PHP_EOL;
		
		}
		
		$output .=	'</div>' . PHP_EOL;
		
		return $output;
		
	}
	


	/**
	 * Gallery item tag
	 * 
	 * @param	string	file
	 * @param	string	folder
	 * @param	array	attr
	 * @param	boolen	forceImg (default = false)
	 * @access	public
	 * @return	string
	 */
	public function getGalleryItemTag($file, $folder, $attr, $forceImg = false, $lazyLoad = false)
	{
		
		// If potemtial video
		if(!$forceImg) {
			
			$videoTag	= $this->getGalleryVideoTag($file, $folder, $attr);
			
			if(!empty($videoTag)) {
				$this->hasVideo	= true;
				return $videoTag;
			}
		
		}

		$attrStr		= "";
		$this->hasVideo	= false;
		
		// Else image
		// Get srcset if mobile and no thumbnail
		if(ContentsEngine::$device["isMobile"]
		&& strpos($folder, "/thumbs/") === false
		) {
			if(file_exists($this->absPath . 'small/' . $file))
				$attrStr	.= ' ' . $folder . 'small/' . $file . ' ' . SMALL_IMG_SIZE . 'w,';
			if(file_exists($this->absPath . 'medium/' . $file))
				$attrStr	.= ' ' . $folder . 'medium/' . $file . ' ' . MEDIUM_IMG_SIZE . 'w,';
			if($attrStr != "")
				$attrStr	= ' srcset="' . htmlspecialchars(substr($attrStr, 0, -1)) . '"';
		}
		if(!empty($attr)) {
			foreach($attr as $key => $val) {
				$attrStr	.= ' ' . $key . '="' . htmlspecialchars($val) . '"';
			}
		}
		
		$gallFile =		'<img ' . ($lazyLoad ? 'data-' : '') . 'src="' . htmlspecialchars($folder . $file) . '"' . $attrStr . ' />' . PHP_EOL;

		return $gallFile;
		
	}
	


	/**
	 * Gallery video item tag
	 * 
	 * @param	string	file
	 * @param	string	folder
	 * @param	array	attr
	 * @access	public
	 * @return	string
	 */
	public function getGalleryVideoTag($file, $folder, $attr)
	{
	
		$gallFile		= "";
		$attrStr		= "";
		$baseFileName	= pathinfo($file, PATHINFO_FILENAME);

		// If video (mp4 required)
		if(!file_exists($this->absPath . $baseFileName . ".mp4"))
			return false;
		
		if(!empty($attr)) {
			foreach($attr as $key => $val) {
				if($key != "alt")
					$attrStr	.= ' ' . $key . '="' . $val . '"';
			}
		}
		
		$gallFile =		'<video' . $attrStr . (ContentsEngine::$device["isMobile"] ? ' controls="controls" preload="none"' : ' autoplay="autoplay"') . ' loop="loop" muted="muted" poster="' . $folder . $file . '">';
		$gallFile .=		'<source src="' . $this->folder . '/' . $baseFileName . '.mp4" />';
		if(file_exists($this->absPath . $baseFileName . ".webm"))
			$gallFile .=	'<source src="' . $this->folder . '/' . $baseFileName . '.webm" type="video/webm" />';
		if(file_exists($this->absPath . $baseFileName . ".ogv"))
			$gallFile .=	'<source src="' . $this->folder . '/' . $baseFileName . '.ogv" type="video/ogg" />';
		#$gallFile .=		'Sorry, no videos' . PHP_EOL;
		$gallFile .=	'</video>' . PHP_EOL;

		return $gallFile;
		
	}
	


	/**
	 * Methode zur Bereinigung von Gallerienamen nach decrypt
	 * 
	 * @param	string	Galleriename
	 * @access	public
	 * @return	string
	 */
	public function getValidGallName($gallName)
	{
		
		$gallName = preg_replace('/[^0-9A-Za-z_-]/i','',$gallName); // Ersetzen evtl. verbliebender unsichtbarer Zeichen
		
		return $gallName;
		
	}
	


	/**
	 * Methode zur Bereinigung von Gallerienamen für Funktionsnamenerweiterung
	 * 
	 * @param	string	Galleriename
	 * @access	public
	 * @return	string
	 */
	public function sanitizeGalleryName($gallName)
	{
		
		$gallName = preg_replace('/[^0-9A-Za-z_]/i','',$gallName); // Ersetzen evtl. verbliebender unsichtbarer Zeichen
		
		return $gallName;
		
	}

}
