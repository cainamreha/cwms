<?php
namespace Concise;

use Concise\Events\GlobalAddHeadCodeEvent;
use \JSMin\JSMin;
	

// Event-Klassen einbinden
require_once PROJECT_DOC_ROOT."/inc/classes/HTML/events/event.GlobalAddHeadCodeEvent.php";

require_once PROJECT_DOC_ROOT."/inc/classes/JSMin/class.JSMin.php"; // Klasse JSMin

/**
 * Grundgerüst einer HTML-Seite.
 * 
 */

class HTML extends ContentsEngine
{
	
	/**
	 * HTML-Head-Code
	 *
	 * @access protected
     * @var    string
     */
	protected $headHtml = "";
	
	/**
	 * Head-Meta-Tags
	 *
	 * @access public
     * @var    string
     */
	public $metaTagsPre = array();
	public $metaTagsPost = array();
	
	/**
	 * CSS-Head-Code
	 *
	 * @access public
     * @var    string
     */
	public $headCSS = "";
	
	/**
	 * Script-Head-Code
	 *
	 * @access public
     * @var    string
     */
	public $headScripts = "";
	
	/**
	 * gesammelter JS-Code
	 *
	 * @access public
     * @var    array
     */
	public $globalScriptCode = array();
	
	/**
	 * gesammelter JS-Code zur Ausgabe an bestimmter Position
	 *
	 * @access public
     * @var    array
     */
	public $scriptCodePre = array();
	public $scriptCodePost = array();
	
	/**
	 * JS-Vars
	 *
	 * @access public
     * @var    array
     */
	public $jsVars = array();
	
	/**
	 * Script-Head-Code
	 *
	 * @access public
     * @var    string
     */
	public $headScriptCode = "";
	
	/**
	 * Script-Body-Code
	 *
	 * @access public
     * @var    string
     */
	public $bodyScriptCode = "";
	
	/**
	 * HTML-Body-Code
	 *
	 * @access public
     * @var    string
     */
	public $bodyHtml = "";
	
	/**
	 * HTML-Foot-Code
	 *
	 * @access public
     * @var    string
     */
	public $footHtml = "";
	
	/**
	 * Zusammenfassung und Komprimierung von CSS-Dateien
	 *
	 * @access public
     * @var    boolean
     */
	public $combineCSS = true;
	
	/**
	 * Preview theme
	 *
	 * @access private
     * @var    string
     */
	private $previewTheme = "";

	
	/**
	 * Konstruktor HTML-Klasse
	 * 
     * @param     object $parentObj	Elternobjekt
     * @access    public
	 */
	public function __construct(&$parentObj)
	{
		
		$this->parentObj		= $parentObj;		
		$this->DB				= $this->parentObj->DB;
		$this->backendLog		= $this->parentObj->backendLog;
		$this->adminPage		= $this->parentObj->adminPage;
		$this->html5			= $this->parentObj->html5;
		$this->scriptFiles		= $this->parentObj->scriptFiles;

		if(!COMBINE_CSS_FILES || empty($this->parentObj->themeConf["defaults"]["combinecss"]))
			$this->combineCSS = false;
		
	}
	
	
	
	/**
	 * Erstellt den Kopf eines HTML-Dokuments.
	 * 
     * @access    public
     * @param     string $lang	Sprache (default = DEF_LANG)
     * @param     string $title	Seitentitel (default = '')
	 */
	public function printHead($lang = DEF_LANG, $title = "")
	{
	
		$htmlClass	= "";
		$pageDescr	= "";
		$pageKeyw	= "";
		$pageRobots	= 3;
		
		if($title == "")
			$title	=  htmlspecialchars($this->parentObj->pageName);

		$title		=  $this->parentObj->htmlTitlePrefix . $title . HTML_TITLE;
		
		if($this->adminPage
		|| $this->parentObj->currentPage == "_install")
			$htmlClass	= ' class="admin"';
		
		if(isset($this->parentObj->pageDescr))
			$pageDescr	= $this->parentObj->pageDescr;
		
		if(isset($this->parentObj->pageKeywords))
			$pageKeyw	= $this->parentObj->pageKeywords;

		if(isset($this->parentObj->pageRobots))
			$pageRobots	= $this->parentObj->pageRobots;

		
		
		// GlobalAddHeadCodeEvent
		$this->triggerGlobalAddHeadCodeEvent();
		
		
        
        // Head ausgeben
		$this->headHtml  = '<!DOCTYPE html' . ($this->html5 ? '' : ' PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"') . '>'.PHP_EOL;
		$this->headHtml	.= '<html ' . ($this->html5 ? '' : 'xmlns="http://www.w3.org/1999/xhtml" xml:lang="' . $lang . '" ') . 'lang="' . $lang . '"' . $htmlClass . '>'.PHP_EOL;
		$this->headHtml	.= '<head>'.PHP_EOL;
		$this->headHtml	.= '<meta ' . ($this->html5 ? 'charset="utf-8' : 'http-equiv="content-type" content="text/html; charset=utf-8') . '" />'.PHP_EOL;
		$this->headHtml	.= '<title>' . htmlspecialchars($title) . '</title>'.PHP_EOL;
		$this->headHtml	.= '<meta name="description" content="' . htmlspecialchars($pageDescr) . '" />'.PHP_EOL;
		$this->headHtml	.= '<meta name="keywords" content="' . htmlspecialchars($pageKeyw) . '" />'.PHP_EOL;
		$this->headHtml	.= SITE_AUTHOR != "" ? '<meta name="author" content="' . htmlspecialchars(SITE_AUTHOR) . '" />'.PHP_EOL : '';
		$this->headHtml	.= SITE_DESIGNER != "" ? '<meta name="designer" content="' . htmlspecialchars(SITE_DESIGNER) . '" />'.PHP_EOL : '';
		$this->headHtml	.= '<meta name="language" content="' . htmlspecialchars($lang) . '" />'.PHP_EOL;
		$this->headHtml	.= '<meta name="robots" content="' . ($pageRobots == 1 || $pageRobots == 3 ? 'index' : 'noindex') . ',' . ($pageRobots > 1 ? 'follow' : 'nofollow') . '" />'.PHP_EOL;
		$this->headHtml	.= '<meta name="viewport" content="width=device-width, initial-scale=1.0" />'.PHP_EOL;
		
		// Additional meta tags (pre)
		$this->headHtml	.= $this->getMetaTags($this->metaTagsPre);
		
		// Open graph meta tags
		$this->headHtml	.= '<meta property="og:title" content="' . htmlspecialchars($title) . '" />'.PHP_EOL;
		$this->headHtml	.= '<meta property="og:description" content="' . htmlspecialchars($pageDescr) . '" />'.PHP_EOL;
		
		if(!empty($this->parentObj->pageImage))
			$this->headHtml	.= '<meta property="og:image" content="' . htmlspecialchars($this->parentObj->pageImage) . '" />'.PHP_EOL;


		// Canonical Url
		// if specified
		if(!empty($this->parentObj->canonicalUrl)
		&& !is_numeric($this->parentObj->canonicalUrl)
		) {
			$this->headHtml	.= '<meta property="og:url" content="' . htmlspecialchars($this->parentObj->canonicalUrl) . '" />'.PHP_EOL;
			$this->headHtml	.= '<link rel="canonical" href="' . htmlspecialchars($this->parentObj->canonicalUrl) . '" />'.PHP_EOL;
		}
		
		elseif($this->parentObj->canonicalUrl > 0) {
			$pageUrl		= htmlspecialchars(PROJECT_HTTP_ROOT . '/' . self::getLinkPath($this->parentObj->canonicalUrl, $lang));
			$this->headHtml	.= '<meta property="og:url" content="' . $pageUrl . '" />'.PHP_EOL;
			$this->headHtml	.= '<link rel="canonical" href="' . $pageUrl . '" />'.PHP_EOL;
		}
		
		// in case domain root (index) or of sorting by tags
		elseif((!empty($GLOBALS['_GET']['page'])
			&& $GLOBALS['_GET']['page'] == "_index")
		||(!empty($GLOBALS['_SERVER']['REQUEST_URI'])
			&& strpos($GLOBALS['_SERVER']['REQUEST_URI'], "?") !== false)
		) {
			$this->headHtml	.= '<meta property="og:url" content="' . htmlspecialchars(parent::$currentURL) . '" />'.PHP_EOL;
			$this->headHtml	.= '<link rel="canonical" href="' . htmlspecialchars(parent::$currentURL) . '" />'.PHP_EOL;
		}
		
		// Additional meta tags (post)
		$this->headHtml	.= $this->getMetaTags($this->metaTagsPost);
		
		
		// CSS-Files aus Content-Bereich
		$this->headCSS	.= $this->printStyleLinks();

		
		// IE-Styles für Front-End einbinden
		if(!$this->adminPage) {
				
			// IE-Styles einbinden
			$this->headCSS	.= '<!--[if IE]><link rel="stylesheet" type="text/css" media="all" href="'.PROJECT_HTTP_ROOT.'/'.STYLES_DIR.'ie.css" /><![endif]-->'.PHP_EOL;
			#$this->headCSS	.= '<!--[if IE 6]><link rel="stylesheet" type="text/css" media="all" href="'.PROJECT_HTTP_ROOT.'/'.STYLES_DIR.'ie6.css" /><![endif]-->'.PHP_EOL;
			#$this->headCSS	.= '<!--[if IE 7]><link rel="stylesheet" type="text/css" media="all" href="'.PROJECT_HTTP_ROOT.'/'.STYLES_DIR.'ie7.css" /><![endif]-->'.PHP_EOL;
			#$this->headCSS	.= '<!--[if IE 8]><link rel="stylesheet" type="text/css" media="all" href="'.PROJECT_HTTP_ROOT.'/'.STYLES_DIR.'ie8.css" /><![endif]-->'.PHP_EOL;
			$this->headCSS	.= '<!--[if IE 9]><link rel="stylesheet" type="text/css" media="all" href="'.PROJECT_HTTP_ROOT.'/'.STYLES_DIR.'ie9.css" /><![endif]-->'.PHP_EOL;
			
			/*
			// Styles für Smartphones (handheld.css/iphone.css)
			$this->headCSS	.= '<link rel="stylesheet" type="text/css" media="only screen and (max-width:480px), only screen and (max-device-width: 480px), only screen and (orientation:portrait)" href="'.PROJECT_HTTP_ROOT.'/'.STYLES_DIR.'layout_handheld.css" />'.PHP_EOL;
			$this->headCSS	.= '<link rel="stylesheet" type="text/css" media="only screen and (max-width:960px), only screen and (max-device-width: 960px), only screen and (orientation:portrait)" href="'.PROJECT_HTTP_ROOT.'/'.STYLES_DIR.'layout_iphone.css" />'.PHP_EOL;
			$this->headCSS	.= '<link rel="stylesheet" type="text/css" media="only screen and (-webkit-min-device-pixel-ratio:1.5), only screen and (min-device-pixel-ratio:1.5), only screen and (orientation:portrait)" href="'.PROJECT_HTTP_ROOT.'/'.STYLES_DIR.'layout_iphone.css" />'.PHP_EOL;
			*/
		}
		
		$this->headHtml	.= $this->headCSS;
		
		
		// Favicon
		$this->headHtml	.= '<link rel="shortcut icon" href="'.PROJECT_HTTP_ROOT.'/favicon.ico" type="image/x-icon" />'.PHP_EOL;
		$this->headHtml	.= '<link rel="icon" href="'.PROJECT_HTTP_ROOT.'/favicon.ico" type="image/x-icon" />'.PHP_EOL;
		
		if(defined('APPLE_TOUCH_ICON')
		&& !empty(APPLE_TOUCH_ICON)
		)
			$this->headHtml	.= '<link rel="apple-touch-icon" href="'.PROJECT_HTTP_ROOT.'/'.APPLE_TOUCH_ICON.'" />'.PHP_EOL;
		
		
		// JavaScript
		// Javascript globale Vars
		if(isset($this->globalScriptCode["jsvars"])) {
			$this->jsVars		= array_filter(array_unique(explode("\n", str_replace("\r\n", "\n", $this->globalScriptCode["jsvars"]))));
			$this->jsVars		= array_merge($this->scriptCodePre, $this->jsVars, $this->scriptCodePost);
			$this->headScripts .= '<script' . ($this->html5 ? '' : ' type="text/javascript"') . ' data-scriptcon="jsvars">';
            $this->headScripts .= JSMin::minify(implode("\n", $this->jsVars));
            $this->headScripts .= '</script>'."\n";
			unset($this->globalScriptCode["jsvars"]);
		}

		
		// Javascript-Files aus Content-Bereich
		$this->headScripts .= $this->getScriptLinkTags(ContentsEngine::$feMode);
		
		
		// Javascript-Code aus Content-Bereich
		#$this->headScriptCode .= $this->getScriptCodeTags($this->globalScriptCode);


		$this->headHtml	.= $this->headScripts;
		$this->headHtml	.= $this->headScriptCode;
		
		
		// Seitenweite Feeds einbinden
		if($this->parentObj->feedHeadLinks != "")
			$this->headHtml	.= $this->parentObj->feedHeadLinks;
		
		// Ggf. HTML-Head-Code (z.B. Web-Fonts) einbinden
		if(HTML_HEAD_EXT && $this->adminPage == false)
			$this->headHtml	.= HTML_HEAD_EXT.PHP_EOL;
		
		
		// HTML-Head ausgeben
		echo $this->headHtml;
	
	}

	/**
	 * Erstellt den 'Körper' eines HTML-Dokuments.
	 * 
     * @access  public
	 * @param	varchar Zusätzliche Cascading Stylesheets
	 * @param	varchar nicht leer, falls Änderungen an Inhalten vorliegen
	 * @param	varchar Status Front-End-Editing
	 */
	public function printBody($bodyID = null, $bodyClass = null, $preview = "", $feModeStatus = "")
	{

		$this->bodyHtml = '<body';
		
		if ($bodyID != null) {
			$this->bodyHtml .= ' id="'.$bodyID.'"';
		}
		if ($bodyClass != null) {
			$this->bodyHtml .= ' class="'.$bodyClass.'"';
		}
		
		$this->bodyHtml .= '>' . PHP_EOL;
		
		
		// Previewinfo oder AdminNotice ausgeben
		if($this->parentObj->editorLog) {
		
			// FE-Buttons, Platzhalterschalter und Admin-Notice
			if($this->adminPage == false) {
				
				$feMode		= $feModeStatus;
				$phMode		= parent::$phMode ? 'off' : 'on';
				
				// FE-Buttons
				$this->bodyHtml .= $this->getFEPanel($feMode, $phMode, FE_THEME_SELECTION, $preview);
				

				// Temporäre Meldungen
				if(isset($this->parentObj->g_Session['fe-notice'])) {
					$this->bodyHtml .= ContentsEngine::replaceStyleDefs('<p class="tempHint notice success {t_class:alert} {t_class:success} {t_class:alertdismis}"><button type="button" class="{t_class:btnlink} {t_class:right} cc-close" data-dismiss="{t_class:alert}" aria-label="Close"><span aria-hidden="true">&times;</span></button>') . $this->parentObj->g_Session['fe-notice'] . '</p>';
					$this->parentObj->unsetSessionKey('fe-notice');
				}
			}
		}
		
		// HTML-Head schließen und Body ausgeben
		echo '</head>'.PHP_EOL . $this->bodyHtml;
	
	}
	
	
	/**
	 * Beendet ein HTML-Dokument.
	 * 
     * @access  public
	 * @param	array JS-Dateien zur Einbindung am Ende des Body-Tags
	 */
	public function printFoot($scriptFilesBody = array())
	{

		// Javascript-Files für Body-Bereich
		if(count($scriptFilesBody) > 0) {

			foreach($scriptFilesBody as $scriptFile) {
			
				$scriptUrl	= $this->getScriptUrl($scriptFile);
				
				if(!$scriptUrl)
					continue;

				$this->footHtml .= '<script' . ($this->html5 ? '' : ' type="text/javascript"') . ' src="' . $scriptUrl . '"></script>'.PHP_EOL;
			}
		}
		
		// Script-Code für Body-Bereich
		$this->bodyScriptCode .= $this->getScriptCodeTags($this->globalScriptCode);
		$this->footHtml .=	$this->bodyScriptCode;
		
		// Body und Html schließen
		$this->footHtml .=	PHP_EOL.'</body>'.PHP_EOL.
							'</html>';
		
		// HTML-Foot ausgeben
		echo $this->footHtml;
	
	}

	
	/**
	 * triggerGlobalAddHeadCodeEvent
	 * 
     * @access  public
	 * @return	string
	 */
	public function triggerGlobalAddHeadCodeEvent()
	{
	
		// Events
		// GlobalAddHeadCodeEvent
		$this->o_globalAddHeadCodeEvent	= new GlobalAddHeadCodeEvent($this->DB, $this->parentObj->o_lng);
		
		// dispatch event add_head_code
		$this->parentObj->o_dispatcher->dispatch('global.add_head_code', $this->o_globalAddHeadCodeEvent);
		
		// Meta tags
		$this->metaTagsPre			= $this->o_globalAddHeadCodeEvent->scriptCodePre;
		$this->metaTagsPost			= $this->o_globalAddHeadCodeEvent->metaTagsPost;
		
		// Script code
		$this->mergeHeadCodeArrays($this->o_globalAddHeadCodeEvent);
		$this->scriptCodePre		= $this->o_globalAddHeadCodeEvent->scriptCodePre;
		$this->scriptCodePost		= $this->o_globalAddHeadCodeEvent->scriptCodePost;

		// globalScriptCode
		$this->globalScriptCode		= array_merge($this->parentObj->scriptCode, $this->scriptCode);
		
		return true;
	
	}

	
	/**
	 * Erstellt einen Meta-Tag-String
	 * 
     * @access  public
	 * @param	array Array
	 * @return	string
	 */
	public static function getMetaTags($metaTags)
	{
		
		// Additional meta tags
		if(empty($metaTags))
			return "";
		
		$output	= "";
		
		foreach($metaTags as $mt) {
			if(!empty($mt)
			&& is_array($mt)
			) {
				foreach($mt as $key => $val) {
					$output	.= '<meta';
					$output	.= ' ' . htmlspecialchars($key) . '="' . htmlspecialchars($val) . '"';
					$output	.= ' />' . PHP_EOL;
				}
			}
		}
		
		return $output;
	
	}

	
	/**
	 * Erstellt lesbare Darstellungen von Arrays (für Debug-Konsole)
	 * 
     * @access  public
	 * @param	array Array
	 * @return	string
	 */
	public static function printArray($array = array())
	{
		return highlight_string(print_r($array,true),true);	
	}



	/**
	 * CSS files für HTML-Head-Bereich ausgeben
	 * 
     * @access	private
	 * @param	string	ID der verlinkten Seite
	 */
	private function printStyleLinks()
	{
	
		$styleOutput	= "";
		
		if(is_array($this->parentObj->cssFiles) && count($this->parentObj->cssFiles) > 0) {
			
			$cssAll			= "";
			$cssPrint		= "";
			$i				= 0;
			
			foreach($this->parentObj->cssFiles as $cssFile) {
				
				// Falls externe CSS-Datei
				if(strpos($cssFile, "http") !== 0) {
					if(!(@file_exists(PROJECT_DOC_ROOT.'/'.$cssFile)))
						continue;
					
					#$cssFile = PROJECT_HTTP_ROOT.'/'.$cssFile;
					$cssFile = $cssFile;
				}	
				
				if(preg_match("/print/i", $cssFile)) {
					$media = "print";
					$cssPrint .= $cssFile . ',';
				}
				else {
					$media = "all";
					$cssAll .= $cssFile . ',';
				}
				
				// Falls Dateien nicht kombiniert werden sollen
				if(!$this->combineCSS)
					$styleOutput	.=  '<link rel="stylesheet" type="text/css" media="'.$media.'" href="' . PROJECT_HTTP_ROOT . '/' .$cssFile.'" />'.PHP_EOL;
				
				$i++;
			}
			
			// Falls Dateien kombiniert werden sollen
			if($this->combineCSS) {
				if($cssAll != "") {
					$cssAll = substr($cssAll, 0, -1);
					$cssAll = base64_encode($cssAll);
					$styleOutput	.= '<link rel="stylesheet" type="text/css" media="all" href="' . PROJECT_HTTP_ROOT .'/styles.css?type=css&files='.$cssAll.($this->adminPage ? '&page=admin' : '').'" data-styles="combined" />'.PHP_EOL;
				}
				if($cssPrint != "") {
					$cssPrint = substr($cssPrint, 0, -1);
					$cssPrint = base64_encode($cssPrint);
					$styleOutput .= '<link rel="stylesheet" type="text/css" media="print" href="' . PROJECT_HTTP_ROOT .'/styles.css?type=css&files='.$cssPrint.($this->adminPage ? '&page=admin' : '').'" />'.PHP_EOL;
				}
			}
		}
		
		return $styleOutput;
	}



	/**
	 * Script files für HTML-Head-Bereich ausgeben
	 * 
     * @access	private
	 */
	private function getScriptLinkTags($feMode)
	{

		// jQuery
		$scriptOutput	= "";
		$scriptFiles	= "";
		$scriptFilesObj	= "";
		$headJS			= "";
		$jsDefs			= "";
		$jsFramework	= "";
		$jsVersion		= "";
		$jsFwLink		= "";
		
		// js framgework (e.g., jquery)
		// fe page und feMode an
		if(!$this->adminPage && $feMode) {
			$headJS			= isset($this->parentObj->adminThemeConf["defaults"]["headjs"]) ? $this->parentObj->adminThemeConf["defaults"]["headjs"] : HEADJS;
			$jsDefs			= $this->parentObj->adminThemeConf["jsframework"];
			$jsFramework	= isset($jsDefs["framework"]) ? $jsDefs["framework"] : "jquery";
			$jsVersion		= isset($jsDefs["version"]) ? $jsDefs["version"] : JQUERY_VERSION_ADMIN;
		}
		// Falls admin page
		elseif($this->adminPage) {
			$headJS			= isset($this->parentObj->themeConf["defaults"]["headjs"]) ? $this->parentObj->themeConf["defaults"]["headjs"] : HEADJS;
			$jsDefs			= $this->parentObj->themeConf["jsframework"];
			$jsFramework	= isset($jsDefs["framework"]) ? $jsDefs["framework"] : "jquery";
			$jsVersion		= isset($jsDefs["version"]) ? $jsDefs["version"] : JQUERY_VERSION_ADMIN;
		}
		else {
			$headJS			= $this->parentObj->headJS;
			$jsDefs			= $this->parentObj->themeConf["jsframework"];
			$jsFramework	= isset($jsDefs["framework"]) ? $jsDefs["framework"] : "jquery";
			$jsVersion		= isset($jsDefs["version"]) ? $jsDefs["version"] : JQUERY_VERSION;
		}
		
		$jsFwLink		= PROJECT_HTTP_ROOT.'/extLibs/' . $jsFramework . '/' . $jsFramework . '-' . $jsVersion . '.min.js';

		
		// Falls das HeadJs-Plugin zum Laden von Script files verwendet werden soll
		if($headJS) {
			$scriptOutput .= '<script' . ($this->html5 ? '' : ' type="text/javascript"') . ' src="'.PROJECT_HTTP_ROOT.'/extLibs/headjs/head.min.js" charset="utf-8" data-script="headjs"></script>'.PHP_EOL;
		}
		else
			$this->array_unshift_assoc($this->scriptFiles, $jsFramework, $jsFwLink);
		
		
		// HTML5shiv
		if($this->html5 && HTML5SHIV)
			$scriptOutput .= '<!--[if lt IE 9]><script src="'.PROJECT_HTTP_ROOT.'/extLibs/html5shiv/html5shiv.js"></script><![endif]-->'.PHP_EOL;
		

		// Javascript-Files aus Content-Bereich
		if(is_array($this->scriptFiles) && count($this->scriptFiles) > 0) {

			foreach($this->scriptFiles as $key => $scriptFile) {
			
				$scriptUrl	= $this->getScriptUrl($scriptFile);
				
				if(!$scriptUrl)
					continue;
					
				// Falls HeadJs-Plugin
				if($headJS)
					$scriptFilesObj .= '{' . $key . ':"' . $scriptUrl . '"},';
				else
					$scriptOutput .= '<script' . ($this->html5 ? '' : ' type="text/javascript"') . ' src="' . $scriptUrl . '"></script>'.PHP_EOL;
			}
			
			// Falls HeadJs
			if($headJS && $scriptFilesObj != "") {
				
				$scriptFilesObj	 = substr($scriptFilesObj, 0, -1);
				
				$scriptOutput	.=	'<script' . ($this->html5 ? '' : ' type="text/javascript"') . ' charset="utf-8" data-script="headload" async>' .
									(!empty($jsFramework) ? 'head.load({' . $jsFramework . ':"' . $jsFwLink . '"});' : '') .
									'head.ready(' . (!empty($jsFramework) ? '"' . $jsFramework . '",' : '') . 'function(){head.load(' . $scriptFilesObj . ');});</script>'.PHP_EOL;
			}
		}
		
		return $scriptOutput;
	
	}



	/**
	 * Script url prüfen und zurückgeben
	 * 
	 * @param	string	$scriptFile
     * @access	private
     * @return	string/boolean
	 */
	private function getScriptUrl($scriptFile)
	{
	
		$scriptUrl	= "";
		
		// Falls vollständige Url, Script-Tag direkt einbinden
		if(stripos($scriptFile, PROJECT_HTTP_ROOT) === 0) {
			if(!file_exists(str_replace(PROJECT_HTTP_ROOT, PROJECT_DOC_ROOT, $scriptFile)))
				return false;
			return $scriptFile;
		}
		if(stripos($scriptFile, "http") === 0)
			return $scriptFile;
		// Andernfalls, wenn Datei vorhanden
		if(file_exists(PROJECT_DOC_ROOT.'/'.$scriptFile))
			return PROJECT_HTTP_ROOT . '/' . $scriptFile;
		return false;

	}



	/**
	 * Script files für HTML-Head-Bereich ausgeben
	 * 
	 * @param	array	$globalScriptCode	globalScriptCode-Array
	 * @param	boolean	$jsvars				set true if not to remove jsvars (default = false)
     * @access	public
	 * @param	array	globalScriptCode Skript-Code für Head-Bereich
	 */
	public function getScriptCodeTags($globalScriptCode, $jsvars = false)
	{
		
		$scriptOutput	= "";
		$headReady		= "";
		$headReadyClose	= "";
			
		if(!$jsvars && isset($globalScriptCode["jsvars"]))
			unset($globalScriptCode["jsvars"]);
		
		// Falls das HeadJs-Plugin zum Laden von Script files verwendet werden soll
		if(HEADJS || $this->backendLog) {
			$headReady		= 'head.ready(function(){'.PHP_EOL;
			$headReadyClose	= '});'.PHP_EOL;
		}		
		
		

		if(count($globalScriptCode) > 0) {
		
			$scriptOutput .= '<script' . ($this->html5 ? '' : ' type="text/javascript"') . ' data-scriptcon="init">/* Concise init code */'.PHP_EOL;		
            $scriptOutput .= $headReady;
											 
			foreach($globalScriptCode as $scriptCode) {
           		$scriptOutput .=  $scriptCode.PHP_EOL;
			}
			
			$scriptOutput .= $headReadyClose;
			$scriptOutput .= '</script>'.PHP_EOL;
		}
		
		$scriptOutput	= JSMin::minify($scriptOutput);
		
		return $scriptOutput;
	
	}
	


	/**
	 * FE-Buttons zurückgeben
	 * 
     * @access	public
	 * @param	string	$feMode			feMode
	 * @param	string	$phMode			phMode
	 * @param	boolean	$themeSelection	themeSelection
	 * @param	string	$preview		pewview
	 */
	public function getFEPanel($feMode, $phMode, $themeSelection, $preview)
	{
	
		// AdminThemeStyles laden, falls noch nicht geschehen (-> Class ContentsEdit)
		if(empty($this->parentObj->adminThemeConf)) {
			$this->adminThemeStyles	= parse_ini_file(SYSTEM_DOC_ROOT.'/themes/' . ADMIN_THEME . '/theme_styles.ini', true);
			$themeIcons				= !empty($this->adminThemeStyles["icons"]) ? $this->adminThemeStyles["icons"] : null;
		}
		else
			$themeIcons				= !empty($this->parentObj->adminThemeConf["icons"]) ? $this->parentObj->adminThemeConf["icons"] : null;
	
		
		if(!empty($themeIcons))
			parent::$iconDefs	= $themeIcons;
		
		$this->previewTheme	= Security::getCookie('previewTheme');
		
		
		$output  =	'<div id="cc-fePanel" class="cc-button-panel">' . PHP_EOL;
		
		$output .=	'<div class="{t_class:btngroup' . (self::$device["isMobile"] ? 'v' : '') . '}" role="group">' . PHP_EOL;
		
		// Button link feMode
		$btnDefs	= array(	"href"		=> '?fe=' . $feMode,
								"text"		=> '',
								"class"		=> 'feMode turn' . $feMode . ' {t_class:btndef}',
								"title"		=> str_replace("<br />", "", ContentsEngine::replaceStaText('{s_' . ($feMode == "onlocked" ? 'error:felock' : 'title:fetoggle') . '}')),
								"icon"		=> 'turn' . $feMode,
								"iconclass"	=> 'fe-mode-icon'
							);
		
		$output .=	parent::getButtonLink($btnDefs);
		
		
		// Adminnotice
		$output .=	$this->getAdminNotifications(WEBSITE_LIVE, $preview, $this->parentObj->adminNotice);
		
		// Falls Editor
		if($this->parentObj->editorLog
		&& parent::$phMode
		) {
		
			// Button link phMode
			$btnDefs	= array(	"href"		=> '?ph=' . $phMode . '&pageid=' . $this->parentObj->pageId,
									"text"		=> '',
									"class"		=> 'feMode turn' . $phMode . ' {t_class:btndef}',
									"title"		=> ContentsEngine::replaceStaText('{s_title:phtoggle}<br />{s_button:turnoff}') . '" class="turn' . $phMode,
									"icon"		=> 'phmode' . $phMode,
									"iconclass"	=> 'ph-mode-icon'
								);
			
			$output .=	parent::getButtonLink($btnDefs);
		
		}
		
		// Falls Editor
		if($this->parentObj->editorLog
		&& $themeSelection
		&& Template::checkThemePreview()
		) {

			$output .=	'<div class="{t_class:btngroup}" role="group">' . PHP_EOL;
			
			// Button link themeSelection
			$btnDefs	= array(	"href"		=> "#",
									"text"		=> '',
									"class"		=> 'themeSelection themePreviewNote feNote {t_class:btndef}',
									"title"		=> '{s_label:theme}',
									"attr"		=> 'data-toggle="dropdown" onclick="$(\'#cc-fePanelDropdown\').dropdown(\'toggle\'); $(\'#cc-fePanel\').toggleClass(\'active\'); return false;"',
									"icon"		=> 'leaf'
								);
			
			$output .=	parent::getButtonLink($btnDefs);
			
			$output .=	'<span class="previewHint {t_class:alert} {t_class:warning}">' . PHP_EOL .
						'<h2 class="{t_class:alert} {t_class:info}">{s_label:theme}</h2>' . PHP_EOL .
						'{s_title:themepreview}<br /><br />
						{s_title:previewtheme}: <strong>' . $this->previewTheme . '</strong>
						<br />
						{s_label:activetheme}: <strong>' . THEME . '</strong>
						</span>';
		
			$output .=	'</div>' . PHP_EOL;
			
		}
		
		
		// Button refresh
		$btnDefs	= array(	"href"		=> parent::$currentURL,
								"class"		=> "{t_class:btndef}",
								"text"		=> '',
								"title"		=> "{s_title:refreshpage}",
								"icon"		=> "refresh"
							);
		
		$output .=	parent::getButtonLink($btnDefs) . PHP_EOL;
		
		
		// Button dropdown
		$btnDefs	= array(	"type"		=> "button",
								"class"		=> "{t_class:btndef} {t_class:ddtoggle}",
								"id"		=> "cc-fePanelDropdown",
								"value"		=> "",
								"text"		=> '&nbsp;<span class="{t_class:caret}"></span>&nbsp;' .PHP_EOL,
								"title"		=> "{s_title:editpage}",
								"attr"		=> 'data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" onclick="$(this).dropdown(); $(\'#cc-fePanel\').toggleClass(\'active\');"',
								"icon"		=> "settings"
							);
		
		$output .=	parent::getButton($btnDefs) . PHP_EOL;
					
		// Dropdownmenu
		$output	.=	'<ul class="{t_class:ddmenu} {t_class:row}" aria-labelledby="cc-fePanelDropdown">' . PHP_EOL;
		
		$output	.=	'<li title="' . str_replace("<br />", "", ContentsEngine::replaceStaText('{s_' . ($feMode == "onlocked" ? 'error:felock' : 'title:fetoggle') . '}')) . '" class="turn' . $feMode . '">' . PHP_EOL .
					'<a href="?fe=' . $feMode . '" title="' . str_replace("<br />", "", ContentsEngine::replaceStaText('{s_' . ($feMode == "onlocked" ? 'error:felock' : 'title:fetoggle') . '}')) . '" class="turn' . $feMode . '">&nbsp;' . PHP_EOL .
					 ContentsEngine::getIcon('turn' . $feMode, "inline-icon") .
					 'FE-Mode&#09;&#9658; <u>' . ContentsEngine::replaceStaText('{s_button:turn' . $feMode . '}') . '</u>' . PHP_EOL .
					'</a>' . PHP_EOL .
					'</li>' . PHP_EOL;
		
		// Falls Editor
		if($this->parentObj->editorLog) {
			$output	.=	'<li class="{t_class:divider}" role="separator"></li>' . PHP_EOL;
		
			$output	.=	'<li title="' . ContentsEngine::replaceStaText('{s_title:phtoggle}') . '" class="turn' . $phMode . '">' . PHP_EOL .
						'<a href="?ph=' . $phMode . '&pageid=' . $this->parentObj->pageId . '">&nbsp;' . PHP_EOL .
						ContentsEngine::getIcon('phmode' . $phMode, "inline-icon") .
						'PH-Keys&#09;&#9658; <u>' . ContentsEngine::replaceStaText('{s_button:turn' . $phMode . '}') . '</u>' . PHP_EOL .
						'</a>' . PHP_EOL .
						'</li>' . PHP_EOL;
		}
		
		// Falls die Theme-Auswahl für das Frontend eingebunden werden soll
		if($themeSelection) {
		
			$output	.=	'<li class="{t_class:divider}" role="separator"></li>' . PHP_EOL;
		
			$output	.=	'<li class="selTheme" title="' . ContentsEngine::replaceStaText('{s_label:theme}') . '">' . PHP_EOL .
						'<a href="#">&nbsp;' . PHP_EOL .
						ContentsEngine::getIcon("leaf") .
						'Layout&#09;&#9658; <u>' . ContentsEngine::replaceStaText('{s_label:theme}') . '</u></a>';
				
			$output .=	'</li>' . PHP_EOL;
		
		
			// Falls Theme-Preview-Cookie und Preview-Theme nicht gleich mit aktuellem Theme
			if(Template::checkThemePreview()) {
			
				$output .=	'<li class="themeSelection-panel {t_class:row}">' . PHP_EOL;
				$output .=	'<span id="themeSelection-panel" class="themeSelection-panel {t_class:fullrow}">' . PHP_EOL;
				$output	.=	'<span class="{t_class:fullrow} {t_class:margints} {t_class:marginbs}">' . PHP_EOL;
				
				$output .=	'<span class="{t_class:alert} {t_class:info} {t_class:fullrow}">' . PHP_EOL;				
				$output .=	parent::getIcon("info", "themeSelection-info {t_class:center}", 'title="{s_title:themepreview}"') . PHP_EOL;				
				$output .=	'{s_title:previewtheme}</span>' . PHP_EOL;
		
				// Button confirmTheme
				$btnDefs	= array(	"type"		=> "button",
										"name"		=> "confirmTheme",
										"class"		=> "{t_class:btnsuc} {t_class:btnblock} {t_class:fullrow} confirmTheme",
										"value"		=> "",
										"text"		=> ' {s_title:activatetheme} &#9658; ' . $this->previewTheme,
										"title"		=> ContentsEngine::replaceStaText('<strong>{s_button:takechange}</strong><br />{s_label:theme}') . ' &#9658; ' . $this->previewTheme,
										"icon"		=> "apply"
									);
				
				$output .=	parent::getButton($btnDefs) . PHP_EOL;
		
				// Button confirmTheme
				$btnDefs	= array(	"type"		=> "button",
										"name"		=> "cancelTheme",
										"class"		=> "{t_class:btnwar} {t_class:btnblock} {t_class:fullrow} cancelTheme",
										"value"		=> "",
										"text"		=> " {s_title:canceltheme}",
										"title"		=> ContentsEngine::replaceStaText('<strong>{s_link:nochanges}</strong>') . ' &#9658; ' . $this->previewTheme,
										"icon"		=> "cancel"
									);
				
				$output .=	parent::getButton($btnDefs) . PHP_EOL;
			
				$output	.=	'</span>' . PHP_EOL;
				$output	.=	'</span>' . PHP_EOL;
				
				$output .=	'</li>' . PHP_EOL;
			
			}
		
		}
		
		$output	.=	'</ul>' . PHP_EOL;
		
		$output .=	'</div>';
		
		if($themeSelection) {
			
			$output .=	'<div id="themeSelection" class="{t_class:panel}">' . Admin::getThemeSelection(THEME, "all", "gallery") . 
						'<form id="themeSelectionForm" action="' . parent::$currentURL . '" method="post" data-ajax="false">
						<input type="hidden" name="currURL" value="' . parent::$currentURL . '" />
						<input type="hidden" name="currTheme" id="currTheme" value="' . THEME . '" />
						</form>' .
						'</div>';
		}
		
		$output .=	'</div>';
		
		$output	= ContentsEngine::replaceStaText($output);
		$output	= ContentsEngine::replaceStyleDefs($output);
		
		return $output;
	
	}
	
	


	/**
	 * Admin notifications
	 * 
     * @access	public
	 * @param	string	liveMode
	 * @param	string	preview
	 * @param	string	adminNotice
	 */
	public function getAdminNotifications($liveMode, $preview, $adminNotice)
	{	
	
		$output  =	"";
		
		// Previewinfo einblenden
		if(!empty($preview)) {
		
			// Button preview
			$btnDefs	= array(	"href"		=> ADMIN_HTTP_ROOT,
									"text"		=> '',
									"class"		=> 'websitePreviewNote feNote {t_class:btndef} button-icon-only',
									"title"		=> '{s_notice:preview} ' . $this->parentObj->currentPage,
									"icon"		=> 'preview',
								);
			
			$output .=	parent::getButtonLink($btnDefs);
			
			$output .=	'<span class="previewHint {t_class:alert} {t_class:warning}">' . PHP_EOL .
						'<h2 class="{t_class:alert} {t_class:info}">{s_notice:preview} ' . $this->parentObj->currentPage . '</h2>' . PHP_EOL .
						'{s_notice:preview2}</span>';
		}
		
		// Website Live-Status
		if(empty($liveMode)) {
		
			// Button goLive
			$btnDefs	= array(	"href"		=> SYSTEM_HTTP_ROOT . '/access/editPages.php?page=admin&sitemode=1',
									"text"		=> '',
									"class"		=> 'goLive live-off websiteOfflineNote feNote {t_class:btndef} button-icon-only',
									"title"		=> '{s_title:websitestage}',
									"attr"		=> 'data-confirm="' . str_replace('\r\n', "<br />", ContentsEngine::$staText["javascript"]["confirmgolive"]) . '"',
									"icon"		=> 'globe',
								);
			
			$output .=	parent::getButtonLink($btnDefs);
			
			$output .=	'<span class="previewHint {t_class:alert} {t_class:warning}">' . PHP_EOL .
						'<h2 class="{t_class:alert} {t_class:info}">{s_title:websitestage}</h2>' . PHP_EOL .
						'{s_title:golive}</span>' . PHP_EOL;
		}
		
		// Falls eine Meldung für den Admin oder Editor vorliegt, diese im Seitenkopf ausgeben
		if(!empty($adminNotice)) {
			$output .= ContentsEngine::replaceStaText('<a href="'.ADMIN_HTTP_ROOT . '?task=edit&edit_id=' . $this->parentObj->pageId . '" class="webpageHint"><p>' . $adminNotice . '</p></a>'.PHP_EOL);
		}
		
		if($output != "") {
		
			$output  =	'<div class="{t_class:btngroup}" role="group">' . PHP_EOL .
						$output .
						'</div>';
			
			$output	= ContentsEngine::replaceStaText($output);
			$output	= ContentsEngine::replaceStyleDefs($output);
		
		}
		
		return $output;
	
	}	
	


	/**
	 * Bestimmt den Pfad (mit Elternknoten) für einen Link
	 * 
     * @access	public
	 * @param	string	ID der verlinkten Seite
	 * @param	string	Sprache (default = current)
	 * @param	boolean	Hinzufügen von Url-Extension, falls true (default = true)
	 * @param	boolean	Hinzufügen von HTTP-Root mit http://domainname.de, falls true (default = false)
	 */
	public static function getLinkPath($targetId, $lang = "current", $addExt = true, $addRoot = false)
	{
		
		$DB				= $GLOBALS['DB'];
		$o_globalLang	= $GLOBALS['o_lng'];
		$targetId		= $DB->escapeString($targetId);
		
		if($lang == "current")
			$lang		= $o_globalLang->lang;
		elseif($lang == "editLang" && class_exists("Concise\Admin"))
			$lang		= $o_globalLang->editLang;
		
		if($lang == "" || (is_object($o_globalLang) && !in_array($lang, $o_globalLang->installedLangs)))
			$lang		= $o_globalLang->defLang;
		if($lang === false || $lang == "" || !is_object($o_globalLang) || $o_globalLang == null)
			$lang		= DEF_LANG;
		
		$linkPath		= "";
		$queryExt		= "";
			
		$curLft			= 0;
		$curRgt			= 0;

		// If full folder url depth
		if(CC_USE_FULL_PAGEURL)
			$queryExt	= "OR (p.`lft` BETWEEN 2 AND n.`lft` 
							AND p.`rgt` > n.`rgt` 
							AND p.`lft` > 1
							AND p.`menu_item` = n.`menu_item`)";
		
		
		// Parameter lft und rgt des aktuellen Menuepunkts auslesen
		$queryTargetPage = $DB->query( "SELECT DISTINCT p.* 
											FROM `" . DB_TABLE_PREFIX . parent::$tablePages . "` AS n 
											,`" . DB_TABLE_PREFIX . parent::$tablePages . "` AS p
											
											WHERE n.`page_id` = '$targetId'
												AND (p.`page_id` = '$targetId'
												$queryExt
												)
											ORDER BY p.`lft` 
											");
		#var_dump($queryTargetPage);
		
		// Falls die db-Query erfolgreich war, linken und rechten Wert auslesen
		if(is_array($queryTargetPage)
		&& count($queryTargetPage) > 0
		) {
		
			foreach($queryTargetPage as $row) { // Schleife zum Ausgeben der Menupunktliste
			
				$pageId	= $row['page_id']; // Menuepunktid
				$title	= $row['title_' . $lang]; // Menuepunkttitel
				$alias	= $row['alias_' . $lang]; // Menuepunktalias
				
				$linkPath .= $alias . '/'; // Alias der Elternmenuepunkte ausgeben
			}	
		
			$linkPath = substr($linkPath, 0, -1);
			$linkPath = self::getLangUrlPrefix($lang) . $linkPath;

			
			// Ggf. Url-Erweiterung anhängen
			if($addExt === true)
				$linkPath .= PAGE_EXT;

			// Ggf. inkl. Http-Root (vorne anhängen)
			if($addRoot === true) {
			
				// Falls LANG_URL_TYPE = subdomain 
				if(LANG_URL_TYPE == "subdomain") {
					
					if(strpos(PROJECT_HTTP_ROOT, "http://localhost") === 0
					|| strpos(PROJECT_HTTP_ROOT, "https://localhost") === 0
					|| strpos(PROJECT_HTTP_ROOT, ".localhost") !== false
					)
						$subdomain	= preg_replace("/(http[s]?:\/\/)([a-zA-Z_-]{2,5}\.)?(localhost\/)/", "$1" . ($lang != DEF_LANG ? $lang . '.' : '') . "localhost/", PROJECT_HTTP_ROOT);
					else
						$subdomain	= preg_replace("/(http[s]?:\/\/)([a-zA-Z_-]{2,5}\.)/", "$1" . ($lang != DEF_LANG ? $lang . '.' : 'www.'), PROJECT_HTTP_ROOT);
					
					$linkPath = $subdomain . '/' . $linkPath;
				}
				// Sonst LANG_URL_TYPE = default 
				else
					$linkPath = PROJECT_HTTP_ROOT . '/' . $linkPath;
			}
		}
		
		return $linkPath;
	
	}



	/**
	 * Lang Url Prefix (falls erforderlich) zurückgeben
	 * 
     * @access	public
	 * @param	string	Sprache (default = current)
	 */
	public static function getLangUrlPrefix($lang)
	{
	
		$prefix	= "";
		
		// Ggf. Lang Url Prefix
		if(LANG_URL_TYPE == "default"
		&& $lang != DEF_LANG
		&& $lang != ""
		)
			$prefix	= $lang . '/';

		return $prefix;
	}



	/**
	 * Ermittelt die Geräteform (mobile/desktop)
	 * 
     * @access	public
	 * @return	boolean
	 */
	public function isMobile()
	{  
	
		if(isset($GLOBALS['_SERVER']['HTTP_USER_AGENT']) && preg_match('/(alcatel|amoi|android|avantgo|blackberry|benq|cell|cricket|docomo|elaine|htc|iemobile|iphone|ipad|ipaq|ipod|j2me|java|midp|mini|mmp|mobi|motorola|nec-|nokia|palm|panasonic|philips|phone|sagem|sharp|sie-|smartphone|sony|symbian|t-mobile|telus|up\.browser|up\.link|vodafone|wap|webos|wireless|xda|xoom|zte)/i', $GLOBALS['_SERVER']['HTTP_USER_AGENT']))
			return true;
		else
			return false;
	}	

}
