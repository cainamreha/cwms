<?php
namespace Concise;


// Script- und css-Includes für den HTML-Headbereich
class HeadIncludes extends Admin
{

	// cssFiles / scriptFiles
	// In theme_config.ini definiert
	private $themeCSS = "";
	private $themeStyles = "";
	private $fromFE = false;
	
	
	/**
	 * HeadIncludes Constructor
	 * 
	 * @param	array	headIncludeFiles
	 * @param	string	adminLang
	 * @param	string	fromFE
	 * @access	public
	 * @return	string
	 */
	public function __construct($headIncludeFiles, $adminLang, $fromFE)
	{
	
		$this->headIncludeFiles	= $headIncludeFiles;
		$this->adminLang		= $adminLang;
		$this->fromFE			= $fromFE;
	
		if(!empty($this->headIncludeFiles['moduleeditor'])
		|| !empty($this->headIncludeFiles['editor'])
		) {
			$this->themeCSS		= $this->getContentStylesheets($fromFE);
			$this->themeStyles	= $this->getThemeDefaults("fe", false);
			$this->scriptCode[]	= $this->getThemeJSVars($this->themeStyles, "cc");
			$this->scriptFiles["ccthemestyles"]	= 'system/access/js/ccThemeStyles.min.js';
		}
	
	}

	
	public function getHeadIncludes($task, $type, $edSelector = "textarea.cc-editor-add")
	{

		// Sortierungsfiles einbinden, falls Gallerie, Artikel, News, Planner oder Forms
		if(!empty($this->headIncludeFiles['sortable'])) {
			$this->scriptFiles["sort"]		= "system/access/js/adminSort.min.js";
		}


		// Falls Edit-Bereich (TinyMCE für erweitertes Textfeld)
		if(!empty($this->headIncludeFiles['editor'])) {
			
			$this->scriptFiles["editor"]		= "extLibs/tinymce/tinymce.min.js";
			#$this->scriptFiles[] = "extLibs/tinymce/jquery.tinymce.min.js";
			$this->scriptFiles["filebrowser"]	= "system/access/js/myFileBrowser.js";
			$this->scriptFiles["sort"]			= "system/access/js/adminSort.min.js";
			#$this->scriptFiles["audio"]			= "extLibs/audio-player/audio-player.js";
			$this->scriptCode[]  = 'head.ready(\'ccInitScript\', function(){'."\r\n".
										'cc.getThemeStyles();'."\r\n".
										'cc.tinyMCESettings = {'."\r\n".
										  'encoding: "UTF-8",'."\r\n".
										  'entity_encoding : "named",'."\r\n".
										  'entities : "160,nbsp,38,amp,60,lt,62,gt",'."\r\n".
										  'selector : "textarea.cc-editor-add:not(.noTinyMCE, #message)",'."\r\n".
										  'relative_urls: false,'."\r\n".
										  'convert_urls: false,'."\r\n".
										  'remove_script_host : false,'."\r\n".
										  'forced_root_block : false,'."\r\n".
										  'force_p_newlines: true,'."\r\n".
										  'language : "' . $this->adminLang . '",'."\r\n".
										  'theme : "modern",'."\r\n".
										  'skin : "' . EDITOR_SKIN . '",'."\r\n".
										  $this->themeCSS .
										  'width : "calc(100% - 2px)",'."\r\n".
										  'height : "350",'."\r\n".
										  'schema: "html5",'."\r\n".
										  'visualblocks_default_state: ' . (HTML5 ? "true" : "false") . ','."\r\n".
										  'indentation : "18px",'."\r\n".
										  'plugins : ["advlist autolink link image lists charmap hr anchor pagebreak","searchreplace wordcount visualblocks visualchars codemagic fullscreen insertdatetime media nonbreaking","save table contextmenu directionality template paste textcolor","iconpicker colorpicker imagetools"],'."\r\n".
										  'toolbar: "undo redo | fontselect | fontsizeselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image media | forecolor | iconpicker | codemagic | ' . (!$this->fromFE ? 'fullscreen' : '') . '",'."\r\n".
										  'insertdatetime_formats: ["%d.%m.%Y", "%H:%M Uhr", "%H:%M:%S", "%m-%d-%Y", "%D", "%H:%M:%S %p"],'."\r\n".
										  $this->getEditorStyleFormats() . ','."\r\n".
										  'visualblocks_default_state: true,'."\r\n".
										  'end_container_on_empty_block: false,'."\r\n".
										  'extended_valid_elements: "span[id|class|style|title|aria-hidden|role|itemprop|itemscope|itemtype|data-*],div[id|class|style|title|aria-hidden|role|itemprop|itemscope|itemtype|data-*],meta[*],style[*],a[rel|rev|charset|hreflang|tabindex|accesskey|type|name|href|target|title|class|onfocus|onblur|onclick]",'."\r\n".
										  'valid_children : "span.cc-iconcontainer[span[role=\'icon\']]",'."\r\n".
										  'textcolor_map: cc.customPalette,'."\r\n".
										  'font_formats: cc.themeFonts,'."\r\n".
										  'fontsize_formats: "8px 10px 12px 14px 18px 24px 36px 48px 60px 72px 90px 128px 144px 180px",'."\r\n".
										  'iconpicker: cc.defaultIconpicker,'."\r\n".
										  'image_list : "' . SYSTEM_HTTP_ROOT . '/access/tmce4.imgList.php",'."\r\n".
										  'image_class_list: ['."\r\n".
												'{title: "None", value: ""},'."\r\n".
												'{title: "Image with frame", value: "' . (isset(parent::$styleDefs['imgf']) ? parent::$styleDefs['imgf'] : 'imgFrame img-framed') . '"},'."\r\n".
												'{title: "Image without frame", value: "' . (isset(parent::$styleDefs['imgnf']) ? parent::$styleDefs['imgnf'] : 'imgNoFrame img-default') . '"},'."\r\n".
												'{title: "Rounded image", value: "' . (isset(parent::$styleDefs['imgr']) ? parent::$styleDefs['imgr'] : 'imgNoFrame img-responsive img-circle') . '"},'."\r\n".
												'{title: "Rounded image with frame", value: "' . (isset(parent::$styleDefs['imgrf']) ? parent::$styleDefs['imgrf'] : 'imgFrame img-framed img-rounded') . '"},'."\r\n".
												'{title: "Circular image", value: "' . (isset(parent::$styleDefs['imgc']) ? parent::$styleDefs['imgc'] : 'imgNoFrame img-responsive img-circle') . '"},'."\r\n".
												'{title: "Circular image with frame", value: "' . (isset(parent::$styleDefs['imgcf']) ? parent::$styleDefs['imgcf'] : 'imgFrame img-framed img-circle') . '"}'."\r\n".
										  '],'."\r\n".
										  'icon_class_list: cc.iconEffects,'."\r\n".
										  'link_list : "' . SYSTEM_HTTP_ROOT . '/access/tmce4.linkList.php",'."\r\n".
										  'link_class_list: ['."\r\n".
												'{title: "None", value: ""},'."\r\n".
												'{title: "Link", value: "link"},'."\r\n".
												'{title: "External link", value: "extLink"},'."\r\n".
												'{title: "Button link", value: "' . parent::$styleDefs['btn'] . ' ' . parent::$styleDefs['btnlink'] . '"},'."\r\n".
												'{title: "Button primary", value: "' . parent::$styleDefs['btn'] . ' ' . parent::$styleDefs['btnpri'] . '"},'."\r\n".
												'{title: "Button default", value: "' . parent::$styleDefs['btn'] . ' ' . parent::$styleDefs['btndef'] . '"},'."\r\n".
												'{title: "Button info", value: "' . parent::$styleDefs['btn'] . ' ' . parent::$styleDefs['btninf'] . '"},'."\r\n".
												'{title: "Button success", value: "' . parent::$styleDefs['btn'] . ' ' . parent::$styleDefs['btnsuc'] . '"},'."\r\n".
												'{title: "Button warning", value: "' . parent::$styleDefs['btn'] . ' ' . parent::$styleDefs['btnwar'] . '"},'."\r\n".
												'{title: "Button danger", value: "' . parent::$styleDefs['btn'] . ' ' . parent::$styleDefs['btndan'] . '"}'."\r\n".
										  '],'."\r\n".
										  'init_instance_callback : function(edId){'."\r\n".
												'var inst = tinymce.get(edId.id);'."\r\n".
												'var repl = inst.getContent().replace(/\{#root\}/g, "' . PROJECT_HTTP_ROOT . '").replace(/\{#root_img\}/g, "' . IMAGE_DIR . '");'."\r\n".
												'var textar	= $("body textarea[id=\"" + edId.id + "\"]");'."\r\n".
												'inst.setContent(repl);'."\r\n".
												'if(textar.hasClass("teaser")){'."\r\n".
													'inst.theme.resizeTo("100%", 50);// resize editor'."\r\n".
												'}'."\r\n".
												'textar.keyup(function(){
													inst.setContent($(this).val());
												});'."\r\n".
												'if(textar.hasClass("disableEditor")){'."\r\n".
													'inst.hide();'."\r\n".
												'}else{'."\r\n".
												'cc.openEditors++;
												}'."\r\n".
										  '},'."\r\n".	
										  'setup: function(ed){'."\r\n".
												'ccEditorsSetup = true;'."\r\n".
												'$.toggleEditor(ed);'."\r\n".
												'ed.on("change",function(e){'."\r\n".
													'cc.conciseChanges = true;'."\r\n".
												'});'."\r\n".
										  '},'."\r\n".
										  'file_browser_callback : myFileBrowser'."\r\n".
									'},'."\r\n".
								'$.addInitFunction({name: "$.myTinyMCE", params: ""});'."\r\n".
								'});'."\r\n".
								'(function($){'."\r\n".
									'$.myTinyMCE = function(){'."\r\n".
										'if(typeof(tinymce) == "object"){'."\r\n".
											'try { tinymce.remove("textarea:not(.noTinyMCE)"); }'."\r\n".
											'catch(e) { console.log(e); }'."\r\n".
										'}'."\r\n".
										'cc.openEditors = 0;'."\r\n".
										'return tinymce.init( cc.tinyMCESettings );'."\r\n".
									'};'."\r\n".
								'})(jQuery);'."\r\n";
		}


		// Falls Gäsetebuch-/Kommentar-Modul, TinyMCE einbinden
		if(!empty($this->headIncludeFiles['commenteditor'])) {
			
			// Falls Planner, Date- und Timepicker einbinden
			$this->scriptFiles["editor"]		= "extLibs/tinymce/tinymce.min.js";
			#$this->scriptFiles[]	= "extLibs/tinymce/jquery.tinymce.min.js";
			$this->scriptFiles["filebrowser"]	= "system/access/js/myFileBrowser.js";
			$this->scriptFiles["editorcom"]		= "system/access/js/myTinyMCE.comments.js";

		}


		// Falls Daten-Module oder Galerietexte (TinyMCE für Textfelder)
		if(!empty($this->headIncludeFiles['moduleeditor'])) {
		
			// Falls Planner, Date- und Timepicker einbinden
			$this->scriptFiles["timepicker"]	= "extLibs/jquery/ui/jquery.jtimepicker.js";
			$this->scriptFiles["editor"]		= "extLibs/tinymce/tinymce.min.js";
			#$this->scriptFiles[]	= "extLibs/tinymce/jquery.tinymce.min.js";
			$this->scriptFiles["filebrowser"]	= "system/access/js/myFileBrowser.js";
			
			
			$ownButton		= "";
			$ownButtonFn	= "";
			$styleFormats	= "";
			
			// Objekt-Button, falls Daten-Module
			if($type == "articles" 
			|| $type == "news" 
			|| $type == "planner"
			) {
				$ownButton			=			" | insertObject";
				$ownButtonFn		= 				'var insertObject = function(){'."\r\n".
														'var con	= ed.getContent({format : "raw"});'."\r\n".
														'var regex	= /{#object_/g;'."\r\n".
														'var occ	= (con.match(regex) || []).length;'."\r\n".
														'var i		= 1;'."\r\n".
														'i += occ;'."\r\n".
														'str = "{#object_" + i + "}";'."\r\n".											
														'ed.execCommand("mceInsertContent", false, str);'."\r\n".
													'};'."\r\n".
													'ed.addButton("insertObject",{'."\r\n".
														'title : "'.parent::replaceStaText("{s_title:insertobject}").'",'."\r\n".
														'icon : "object",'."\r\n".
														'onclick : insertObject'."\r\n".
													'});'."\r\n".
													'ed.addMenuItem("insertObject",{'."\r\n".
														'text: "'.parent::replaceStaText("{s_title:insertobject}").'",'."\r\n".
														'icon : "object",'."\r\n".
														'context: "insert",'."\r\n".
														'onclick : insertObject'."\r\n".
													'});'."\r\n";
				
				$styleFormats		=		$this->getEditorStyleFormats() . ','."\r\n";
			}								  
			
			$edWidth	= "calc(100% - 2px)";
			$edHeight	= $type == "gallery" ? 50 : 350;
			
			$this->scriptCode[]  	= 'head.ready(\'ccInitScript\', function(){'."\r\n".
										'(function($){ $.myTinyMCEModules = function(editor){'."\r\n".
										'if(typeof(editor) == "undefined" || editor == ""){'."\r\n".
										'editor = "textarea:not(.noTinyMCE, .standardField, #message)";'."\r\n".
										'try { tinymce.remove(editor); }'."\r\n".
										'catch(e) { console.log(e); }'."\r\n".
										'cc.openEditors = 0;'."\r\n".
										'}'."\r\n".
										'cc.getThemeStyles();'."\r\n".
										'tinymce.init({'."\r\n".
										  'encoding: "UTF-8",'."\r\n".
										  'entity_encoding : "named",'."\r\n".
										  'entities : "160,nbsp,38,amp,60,lt,62,gt",'."\r\n".
										  'selector : "' . $edSelector . '",'."\r\n".
										  'relative_urls: false,'."\r\n".
										  'convert_urls: false,'."\r\n".
										  'remove_script_host: false,'."\r\n".
										  'convert_newlines_to_brs: true,'."\r\n".
										  'forced_root_block : false,'."\r\n".
										  'force_br_newlines: true,'."\r\n".
										  #'force_p_newlines: false,'."\r\n".
										  'language : "' . $this->adminLang . '",'."\r\n".
										  'theme : "modern",'."\r\n".
										  'skin : "' . EDITOR_SKIN . '",'."\r\n".
										  $this->themeCSS .
										  'width : "' . $edWidth . '",'."\r\n".
										  'height : "' . $edHeight . '",'."\r\n".
										  'schema: "html5",'."\r\n".
										  'visualblocks_default_state: ' . (HTML5 ? "true" : "false") . ','."\r\n".
										  'indentation : "18px",'."\r\n".
										  'plugins : ["advlist autolink link image lists charmap hr anchor pagebreak","searchreplace wordcount visualblocks visualchars codemagic fullscreen insertdatetime media nonbreaking","save table contextmenu directionality paste textcolor","iconpicker colorpicker imagetools"],'."\r\n".
										  'toolbar: "undo redo | fontselect | fontsizeselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image media' . $ownButton . ' | forecolor | iconpicker | codemagic | ' . (!$this->fromFE ? 'fullscreen' : '') . '",'."\r\n".
										  'insertdatetime_formats: ["%d.%m.%Y", "%H:%M Uhr", "%H:%M:%S", "%m-%d-%Y", "%D", "%H:%M:%S %p"],'."\r\n".
										  'end_container_on_empty_block: false,'."\r\n".
										  'extended_valid_elements: "span[id|class|style|title|aria-hidden|role|itemprop|itemscope|itemtype|data-*],div[id|class|style|title|aria-hidden|role|itemprop|itemscope|itemtype|data-*],meta[*],style[*],a[rel|rev|charset|hreflang|tabindex|accesskey|type|name|href|target|title|class|onfocus|onblur|onclick]",'."\r\n".
										  'valid_children : "span.cc-iconcontainer[span[role=\'icon\']]",'."\r\n".
										  'textcolor_map: cc.customPalette,'."\r\n".
										  'font_formats: cc.themeFonts,'."\r\n".
										  'fontsize_formats: "8px 10px 12px 14px 18px 24px 36px 48px 60px 72px 90px 128px 144px 180px",'."\r\n".
										  'iconpicker: cc.defaultIconpicker,'."\r\n".
										  $styleFormats .
										  'image_list : "' . SYSTEM_HTTP_ROOT . '/access/tmce4.imgList.php",'."\r\n".
										  'image_class_list: ['."\r\n".
												'{title: "None", value: ""},'."\r\n".
												'{title: "Image with frame", value: "' . (isset(parent::$styleDefs['imgf']) ? parent::$styleDefs['imgf'] : 'imgFrame img-framed') . '"},'."\r\n".
												'{title: "Image without frame", value: "' . (isset(parent::$styleDefs['imgnf']) ? parent::$styleDefs['imgnf'] : 'imgNoFrame img-default') . '"},'."\r\n".
												'{title: "Rounded image", value: "' . (isset(parent::$styleDefs['imgr']) ? parent::$styleDefs['imgr'] : 'imgNoFrame img-responsive img-circle') . '"},'."\r\n".
												'{title: "Rounded image with frame", value: "' . (isset(parent::$styleDefs['imgrf']) ? parent::$styleDefs['imgrf'] : 'imgFrame img-framed img-rounded') . '"},'."\r\n".
												'{title: "Circular image", value: "' . (isset(parent::$styleDefs['imgc']) ? parent::$styleDefs['imgc'] : 'imgNoFrame img-responsive img-circle') . '"},'."\r\n".
												'{title: "Circular image with frame", value: "' . (isset(parent::$styleDefs['imgcf']) ? parent::$styleDefs['imgcf'] : 'imgFrame img-framed img-circle') . '"}'."\r\n".
										  '],'."\r\n".
										  'icon_class_list: cc.iconEffects,'."\r\n".
										  'link_list : "' . SYSTEM_HTTP_ROOT . '/access/tmce4.linkList.php",'."\r\n".
										  'link_class_list: ['."\r\n".
												'{title: "None", value: ""},'."\r\n".
												'{title: "Link", value: "link"},'."\r\n".
												'{title: "External link", value: "extLink"},'."\r\n".
												'{title: "Button link", value: "' . parent::$styleDefs['btn'] . ' ' . parent::$styleDefs['btnlink'] . '"},'."\r\n".
												'{title: "Button primary", value: "' . parent::$styleDefs['btn'] . ' ' . parent::$styleDefs['btnpri'] . '"},'."\r\n".
												'{title: "Button default", value: "' . parent::$styleDefs['btn'] . ' ' . parent::$styleDefs['btndef'] . '"},'."\r\n".
												'{title: "Button info", value: "' . parent::$styleDefs['btn'] . ' ' . parent::$styleDefs['btninf'] . '"},'."\r\n".
												'{title: "Button success", value: "' . parent::$styleDefs['btn'] . ' ' . parent::$styleDefs['btnsuc'] . '"},'."\r\n".
												'{title: "Button warning", value: "' . parent::$styleDefs['btn'] . ' ' . parent::$styleDefs['btnwar'] . '"},'."\r\n".
												'{title: "Button danger", value: "' . parent::$styleDefs['btn'] . ' ' . parent::$styleDefs['btndan'] . '"}'."\r\n".
										  '],'."\r\n".
										  'init_instance_callback : function(edId){'."\r\n".
												'var inst = tinymce.get(edId.id);'."\r\n".
												'var textar	= $("body").find("#" + edId.id);'."\r\n".
												'var textval = textar.val();'."\r\n".
												'var optionalEditor = $("#" + edId.id).hasClass("disableEditor");'."\r\n".
												'var repl = textval.replace(/\{#root\}/g, "' . PROJECT_HTTP_ROOT . '").replace(/\{#root_img\}/g, "' . IMAGE_DIR . '");'."\r\n".
												'textar.keyup(function(){ // force html text
													textVal = $(this).val();
													if(typeof(textVal[0]) != "undefined" && textVal[0] != "<"){
														textVal = "<p>" + textVal.replace(/\n/g, "</p><p>") + "</p>";
													}
													inst.setContent(textVal);
												});'."\r\n".
												'inst.setContent(repl);'."\r\n".
												'if(optionalEditor){'."\r\n".
													'repl = repl.replace(/\n/g, "<br />");'."\r\n".
													'inst.theme.resizeTo("100%", 50);// resize editor'."\r\n".
													'if(typeof(repl[0]) != "undefined" && repl[0] != "<"){
														repl = "<p>" + repl + "</p>";
													}
													inst.hide();'."\r\n".
													'var rawVal = textval;'."\r\n".
													'textar.val(rawVal);'."\r\n".
													'if($("#" + edId.id).hasClass("galleryEditor")){
														inst.show();
													}'."\r\n".
													'cc.conciseChanges = false;'."\r\n".
												'}else{'."\r\n".
												'cc.openEditors++;}'."\r\n".
										  '},'."\r\n".	
										  'setup: function(ed){'."\r\n".
												$ownButtonFn .
												'ccEditorsSetup = true;'."\r\n".
												'$.toggleEditor(ed);'."\r\n".
												'ed.on("change",function(e){'."\r\n".
													'cc.conciseChanges = true;'."\r\n".
												'});'."\r\n".
										  '},'."\r\n".
										  'file_browser_callback : myFileBrowser'."\r\n".
									   '});'."\r\n".
									   '};'."\r\n".
									  '})(jQuery);'."\r\n".
									'});'."\r\n".
									'head.ready(\'ccInitScript\', function(){'."\r\n".
										'$.addInitFunction({name: "$.myTinyMCEModules", params: ""});'."\r\n".
									'});'."\r\n";
		}


		// Falls Newsletter (TinyMCE für Textfelder)
		if(!empty($this->headIncludeFiles['newsleditor'])) {
			$this->scriptFiles["editor"]		= "extLibs/tinymce/tinymce.min.js";
			$this->scriptFiles["filebrowser"]	= "system/access/js/myFileBrowser.js";
			
			$this->scriptCode[]		= '(function($){'."\r\n".
										'cc.tinyMCESettings = {'."\r\n".
										  'encoding: "UTF-8",'."\r\n".
										  'entity_encoding : "named",'."\r\n".
										  'entities : "160,nbsp,38,amp,60,lt,62,gt",'."\r\n".
										  'selector : "#newsl_text:not(.noTinyMCE)",'."\r\n".
										  'relative_urls: false,'."\r\n".
										  'convert_urls: false,'."\r\n".
										  'remove_script_host : false,'."\r\n".
										  'document_base_url : "' . PROJECT_HTTP_ROOT . '/",'."\r\n".
										  'token : "' . parent::$token . '",'."\r\n".
										  'template_replace_values:{'."\r\n".
											'root : "'.PROJECT_HTTP_ROOT.'",'."\r\n".
											'img_root : "'.PROJECT_HTTP_ROOT.'/'.IMAGE_DIR.'"'."\r\n".
										  '},'."\r\n".
										'templates : ['."\r\n".
											$this->getNewsletterTemplates() .
											'{'."\r\n".
											  'title : "Einfacher Newsletter",'."\r\n".
											  'url : "' . SYSTEM_TEMPLATE_DIR . '/tinymce_tpls/newsletter.htm?'.time().'",'."\r\n".
											  'description : "Einfacher Newsletter mit Empfängernamen und Unsubscribe-Link"'."\r\n".
											'},'."\r\n".
											'{'."\r\n".
											  'title : "Einspaltiger Newsletter",'."\r\n".
											  'url : "' . SYSTEM_TEMPLATE_DIR . '/tinymce_tpls/single-column.htm?'.time().'",'."\r\n".
											  'description : "Einspaltige Newsletter-Vorlage mit Empfängernamen und Unsubscribe-Link"'."\r\n".
											'},'."\r\n".
											'{'."\r\n".
											  'title : "Zweispaltiger Newsletter",'."\r\n".
											  'url : "' . SYSTEM_TEMPLATE_DIR . '/tinymce_tpls/two-cols-simple.htm?'.time().'",'."\r\n".
											  'description : "Zweispaltige Newsletter-Vorlage mit Empfängernamen und Unsubscribe-Link"'."\r\n".
											'},'."\r\n".
											'{'."\r\n".
											  'title : "Dreispaltiger Newsletter",'."\r\n".
											  'url : "' . SYSTEM_TEMPLATE_DIR . '/tinymce_tpls/three-cols-images.htm?'.time().'",'."\r\n".
											  'description : "Dreispaltige Newsletter-Vorlage mit Bildern"'."\r\n".
											'}'."\r\n".
										  '],'."\r\n".
										  'language : "' . $this->adminLang . '",'."\r\n".
										  'theme : "modern",'."\r\n".
										  'skin : "' . EDITOR_SKIN . '",'."\r\n".
										  'width : "calc(100% - 2px)",'."\r\n".
										  'height : "350",'."\r\n".
										  'schema: "html5",'."\r\n".
										  'visual: false,'."\r\n".
										  'visualblocks_default_state: false,'."\r\n".
										  'indentation : "18px",'."\r\n".
										  #'fullpage_default_doctype : \'html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd\','."\r\n".
										  'fullpage_default_encoding : "utf-8",'."\r\n".
										  'fullpage_default_title : "Newsletter - ' . str_replace(array("http://","https://"), "", PROJECT_HTTP_ROOT) . '",'."\r\n".
										  'fullpage_default_langcode : "de",'."\r\n".
										  'fullpage_default_xml_pi : false,'."\r\n".
										  'fullpage_hide_in_source_view : false,'."\r\n".
										  'fullpage_extended_meta : \'<meta name="viewport" content="width=device-width, initial-scale=1"> <!-- So that mobile will display zoomed in -->\n<meta http-equiv="X-UA-Compatible" content="IE=edge"> <!-- enable media queries for windows phone 8 -->\n<meta name="format-detection" content="telephone=no"> <!-- disable auto telephone linking in iOS -->\n\','."\r\n".
										  'fullpage_extended_link : "",'."\r\n".
										  'plugins : ["template advlist autolink link image lists charmap print preview hr anchor pagebreak","searchreplace wordcount visualblocks visualchars code fullscreen insertdatetime nonbreaking","save table contextmenu directionality template paste textcolor code","imagetools"],'."\r\n".
										  'extended_valid_elements: "span[id|class|style|title|aria-hidden|role|itemprop|itemscope|itemtype|data-*],div[id|class|style|title|aria-hidden|role|itemprop|itemscope|itemtype|data-*],meta[*],style[*],a[rel|rev|charset|hreflang|tabindex|accesskey|type|name|href|target|title|class|onfocus|onblur|onclick]",'."\r\n".
										  'valid_children: "+html[head],+body[meta|style],+head[meta|style],+style[*:*]",'."\r\n".
										  'toolbar: "template undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image | print preview fullscreen | forecolor | code | insertName",'."\r\n".
										  'insertdatetime_formats: ["%d.%m.%Y", "%H:%M Uhr", "%H:%M:%S", "%m-%d-%Y", "%D", "%H:%M:%S %p"],'."\r\n".
										  'image_list : "' . SYSTEM_HTTP_ROOT . '/access/tmce4.imgList.php",'."\r\n".
										  'image_class_list: ['."\r\n".
												'{title: "None", value: ""},'."\r\n".
												'{title: "Image with frame", value: "' . (isset(parent::$styleDefs['imgf']) ? parent::$styleDefs['imgf'] : 'imgFrame img-framed') . '"},'."\r\n".
												'{title: "Image without frame", value: "' . (isset(parent::$styleDefs['imgnf']) ? parent::$styleDefs['imgnf'] : 'imgNoFrame img-default') . '"},'."\r\n".
												'{title: "Rounded image", value: "' . (isset(parent::$styleDefs['imgr']) ? parent::$styleDefs['imgr'] : 'imgNoFrame img-responsive img-circle') . '"},'."\r\n".
												'{title: "Rounded image with frame", value: "' . (isset(parent::$styleDefs['imgrf']) ? parent::$styleDefs['imgrf'] : 'imgFrame img-framed img-rounded') . '"},'."\r\n".
												'{title: "Circular image", value: "' . (isset(parent::$styleDefs['imgc']) ? parent::$styleDefs['imgc'] : 'imgNoFrame img-responsive img-circle') . '"},'."\r\n".
												'{title: "Circular image with frame", value: "' . (isset(parent::$styleDefs['imgcf']) ? parent::$styleDefs['imgcf'] : 'imgFrame img-framed img-circle') . '"}'."\r\n".
										  '],'."\r\n".
										  'link_list : "' . SYSTEM_HTTP_ROOT . '/access/tmce4.linkList.php",'."\r\n".
										  'link_class_list: ['."\r\n".
												'{title: "None", value: ""},'."\r\n".
												'{title: "Link", value: "link"},'."\r\n".
												'{title: "External link", value: "extLink"},'."\r\n".
												'{title: "Button link", value: "' . parent::$styleDefs['btn'] . ' ' . parent::$styleDefs['btnlink'] . '"},'."\r\n".
												'{title: "Button primary", value: "' . parent::$styleDefs['btn'] . ' ' . parent::$styleDefs['btnpri'] . '"},'."\r\n".
												'{title: "Button default", value: "' . parent::$styleDefs['btn'] . ' ' . parent::$styleDefs['btndef'] . '"},'."\r\n".
												'{title: "Button info", value: "' . parent::$styleDefs['btn'] . ' ' . parent::$styleDefs['btninf'] . '"},'."\r\n".
												'{title: "Button success", value: "' . parent::$styleDefs['btn'] . ' ' . parent::$styleDefs['btnsuc'] . '"},'."\r\n".
												'{title: "Button warning", value: "' . parent::$styleDefs['btn'] . ' ' . parent::$styleDefs['btnwar'] . '"},'."\r\n".
												'{title: "Button danger", value: "' . parent::$styleDefs['btn'] . ' ' . parent::$styleDefs['btndan'] . '"}'."\r\n".
										  '],'."\r\n".
										  'init_instance_callback : function(edId){'."\r\n".
												'var inst = tinymce.get(edId.id);'."\r\n".
												'var textar	= $("body textarea[id=\"newsl_text\"]");'."\r\n".
												'var textval = textar.val();'."\r\n".
												#'var repl = textval.replace(/\{#root\}/g, "' . PROJECT_HTTP_ROOT . '").replace(/\{#root_img\}/g, "' . IMAGE_DIR . '").replace(/\n/g, "</p><p>");'."\r\n".
												'var repl = textval.replace(/\{#root\}/g, "' . PROJECT_HTTP_ROOT . '").replace(/\{#root_img\}/g, "' . IMAGE_DIR . '");'."\r\n".
												'inst.setContent(repl);'."\r\n".
												'if($("#" + edId.id).hasClass("disableEditor")){'."\r\n".
													'inst.hide();'."\r\n".
												'textar.val(textval.replace(/<br \/>/g, "\n").replace(/<\/?[^>]+>/gi, ""));'."\r\n".
												'}else{'."\r\n".
												'cc.openEditors++;}'."\r\n".
										  '},'."\r\n".
										  'setup: function(ed){'."\r\n".
												'ccEditorsSetup = true;'."\r\n".
												'ed.addButton("insertName",{'."\r\n".
													'title : "'.parent::replaceStaText("{s_title:newslrec}").'",'."\r\n".
													'icon : "user",'."\r\n".
													'onclick : function() {ed.selection.setContent("{%name%}")}'."\r\n".
												'});'."\r\n".
												'ed.addMenuItem("insertName",{'."\r\n".
													'text: "'.parent::replaceStaText("{s_title:newslrec}").'",'."\r\n".
													'icon : "user",'."\r\n".
													'context: "insert",'."\r\n".
													'onclick : function() {ed.selection.setContent("{%name%}")}'."\r\n".
												'});'."\r\n".
												'var textar	= $("body textarea[id=\"newsl_text\"]");'."\r\n".
												'textar.keyup(function(){ ed.setContent("<p>" + $(this).val().replace(/\n/g, "</p><p>") + "</p>"); });'."\r\n".
												'$("#newsl_format").click(function(){ setNewslFormat($(this)); });'."\r\n".
												'function setNewslFormat(formatToggle){'."\r\n".
													'var content = "";'."\r\n".
													'if(formatToggle.is(":checked")) {'."\r\n".
														'content = textar.val().replace(/\n/g, "</p><p>");'."\r\n".
														'textar.val("<p>" + content + "</p>");'."\r\n".
														'textar.removeClass("disableEditor");'."\r\n".
														'ed.show();'."\r\n".
													'}else{'."\r\n".
														'content = ed.getContent().replace(/<\/?[^>]+>/gi, "");'."\r\n".
														'textar.addClass("disableEditor");'."\r\n".
														'ed.hide();'."\r\n".
														'textar.val(content);'."\r\n".
													'}'."\r\n".
												'};'."\r\n".
												'ed.on("BeforeSetContent",function(e){'."\r\n".
													'cc.conciseChanges = true;'."\r\n".
												'});'."\r\n".
												'ed.on("change",function(e){'."\r\n".
													'ed.setContent(ed.getContent().replace(/\{%root%\}/gi, "' . PROJECT_HTTP_ROOT . '"));'."\r\n".
												'});'."\r\n".
										  '},'."\r\n".
										  'file_browser_callback : myFileBrowser'."\r\n".
										'}'."\r\n".
										'$.myTinyMCE = function(){ if(typeof(tinymce) == "object"){ tinymce.remove("#newsl_text:not(.noTinyMCE)"); cc.openEditors = 0; tinymce.init( cc.tinyMCESettings ); } };'."\r\n".
										'})(jQuery);'."\r\n".
										'head.ready(\'ccInitScript\', function(){'."\r\n".
											'$.addInitFunction({name: "$.myTinyMCE", params: ""});'."\r\n".
										'});'."\r\n";

		}


		// elFinder / CodeMirror
		if(!empty($this->headIncludeFiles['filemanager'])) {

			$this->cssFiles[]	 = "extLibs/jquery/elfinder/css/elfinder.min.css";
			$this->cssFiles[]	 = "extLibs/codemirror/lib/codemirror.css";
			$this->cssFiles[]	 = "extLibs/codemirror/theme/concise.css";
			$this->cssFiles[]	 = "extLibs/codemirror/addon/display/fullscreen.css";
			#$this->scriptFiles[] = "extLibs/jquery/ui/jquery-ui-custom-elfinder.min.js";
			$this->scriptFiles["elfinder"]			= "extLibs/jquery/elfinder/js/elfinder.min.js";
			if($this->adminLang != "en")
				$this->scriptFiles["elfinderln"]	= "extLibs/jquery/elfinder/js/i18n/elfinder." . $this->adminLang . ".js";
			$this->scriptFiles["codemirror"]		= "extLibs/codemirror/lib/codemirror.js";
			$this->scriptFiles["codemirrorhtml"]	= "extLibs/codemirror/mode/htmlmixed/htmlmixed.js";
			$this->scriptFiles["codemirrorxml"]		= "extLibs/codemirror/mode/xml/xml.js";
			$this->scriptFiles["codemirrorcss"]		= "extLibs/codemirror/mode/css/css.js";
			$this->scriptFiles["codemirrorjs"]		= "extLibs/codemirror/mode/javascript/javascript.js";
			#$this->scriptFiles["codemirrorvb"]		= "extLibs/codemirror/mode/vbscript/vbscript.js";
			$this->scriptFiles["codemirrorfs"]		= "extLibs/codemirror/addon/display/fullscreen.js";
			
			// CodeMirror vars
			$this->scriptCode["jsvars"]	 =	'var htmlCodeMirror;'."\r\n".
											'var htmlCodeMirrorContent;'."\r\n";
			
			// CodeMirror init, falls Template
			if($task == "tpl" 
			&& $type != "edit"
			)
				$this->scriptCode[]	 =	'(function($){'."\r\n".
										  '$.myCodeMirror = function(){'."\r\n".
										  'var htmlTextArea = $("#editTplCode");'."\r\n".
										  'if(typeof(htmlTextArea) == "undefined" || !htmlTextArea.length){ return false; }'."\r\n".
										  'htmlCodeMirror = CodeMirror.fromTextArea($(htmlTextArea)[0], {'."\r\n".
											'lineNumbers: true,'."\r\n".
											'lineWrapping: true,'."\r\n".
											'matchBrackets: true,'."\r\n".
											'extraKeys: {"F11": function(cm) {cm.setOption("fullScreen", !cm.getOption("fullScreen"));},"Esc": function(cm) {if(cm.getOption("fullScreen")) cm.setOption("fullScreen", false);return false;}},'."\r\n".
											'theme: "concise",'."\r\n".
											'mode: "htmlmixed"'."\r\n".
										  '});'."\r\n".
										  'htmlCodeMirrorContent = htmlCodeMirror.getValue();'."\r\n".
										  '};'."\r\n".
										'})(jQuery);'."\r\n".
										'head.ready("ccInitScript", function(){'."\r\n".
											'$.addInitFunction({name: "$.myCodeMirror", params: ""});'."\r\n".
										'});'."\r\n";
										
		}


		// Colorpicker einbinden bei Tpl
		if(!empty($this->headIncludeFiles['colorpicker'])) {
			
			$this->cssFiles[] 				= "extLibs/jquery/jpicker/jPicker.css";
			$this->cssFiles[]	 			= "extLibs/jquery/jpicker/css/jPicker-1.1.6.min.css";
			$this->scriptFiles["jpicker"]	= "extLibs/jquery/jpicker/jpicker-1.1.6.min.js";
			$this->scriptCode[]				= '(function($){'."\r\n".
											'$.myColorPicker = function(){'."\r\n".
												'$("input.color").jPicker({'."\r\n".
													'images:{'."\r\n".
														'clientPath: "' . PROJECT_HTTP_ROOT . '/extLibs/jquery/jpicker/images/"'."\r\n".
													'},'."\r\n".
													'window:{'."\r\n".
														'title: "Choose color", // any title for the jPicker window itself - displays, Drag Markers To Pick A Color" if left null'."\r\n".
														'effects:{'."\r\n".
															'type: "fade", // effect used to show/hide an expandable picker. Acceptable values "slide", "show", "fade"'."\r\n".
															'speed:{'."\r\n".
																'show: "300", // duration of "show" effect. Acceptable values are "fast", "slow", or time in ms'."\r\n".
																'hide: "fast" // duration of "hide" effect. Acceptable value are "fast", "slow", or time in ms'."\r\n".
															'}'."\r\n".
														'},'."\r\n".
														'position:{'."\r\n".
															'x: "screenCenter", // acceptable values "left", "center", "right", "screenCenter", or relative px value'."\r\n".
															'y: "300" // acceptable values "top", "bottom", "center", or relative px value'."\r\n".
														'},'."\r\n".
														'alphaSupport: false, // set to true to enable alpha picking'."\r\n".
														'alphaPrecision: 0, // set decimal precision for alpha percentage display - hex codes do not map directly to percentage integers - range 0-2'."\r\n".
														'updateInputColor: true // set to false to prevent binded input colors from changing'."\r\n".
													'}'."\r\n".
												'},'."\r\n".
												'function(color, context){'."\r\n".
												'var currCol = color.val("hex").toUpperCase();'."\r\n".
												'var thisID = context.parents("div.jPicker").attr("id").split("-")[1];'."\r\n".
												'var totColors	= $("li#totColors").attr("class").split("-")[1];'."\r\n".
												'var checkCol = 1;'."\r\n".
												'var i = 1;'."\r\n".
												'for(i = 1; i <= totColors; i++){'."\r\n".
												'checkCol = $("div#col-" + i).children("div.color").children("input.color").val().toUpperCase();'."\r\n".
												'if(checkCol == currCol && i != thisID){'."\r\n".
												'jAlert("#" + (currCol || "none") + ln.duplColor1 + i + "." + ln.duplColor2, ln.alerttitle);'."\r\n".
												'}'."\r\n".
												'}'."\r\n".
												'return false;'."\r\n".
												'}'."\r\n".
												');'."\r\n".
											'};'."\r\n".
										'})(jQuery);'."\r\n".
										'head.ready("ccInitScript", function(){'."\r\n".
											'$.addInitFunction({name: "$.myColorPicker", params: ""});'."\r\n".
										'});'."\r\n";
		}


		// admin.js einbinden
		#$this->scriptFiles["admin"] 	= "system/access/js/admin.min.js";
	}

	
	public function getEditorStyleFormats()
	{

		return 							'style_formats: ['."\r\n".
											'{title: "Headers", items: ['."\r\n".
												'{title: "Header 1", block: "h1"},'."\r\n".
												'{title: "Header 2", block: "h2"},'."\r\n".
												'{title: "Header 3", block: "h3"},'."\r\n".
												'{title: "Header 4", block: "h4"},'."\r\n".
												'{title: "Header 5", block: "h5"},'."\r\n".
												'{title: "Header 6", block: "h6"}'."\r\n".
											']},'."\r\n".
											'{title: "Blocks", items: ['."\r\n".
												'{title: "Div", format: "div", wrapper: true, merge_siblings: false},'."\r\n".
												'{title: "Paragraph", format: "p"},'."\r\n".
												'{title: "Pre", format: "pre"},'."\r\n".
												'{title: "Blockquote", format: "blockquote"},'."\r\n".
												'{title: "Figcaption", block: "figcaption"}'."\r\n".
											']},'."\r\n".
											'{title: "Containers", items: ['."\r\n".
												'{title: "Section", block: "section", wrapper: true, merge_siblings: false},'."\r\n".
												'{title: "Container", block: "div", classes: "container", wrapper: true, merge_siblings: false},'."\r\n".
												'{title: "Row", block: "div", classes: "row", wrapper: true, merge_siblings: false},'."\r\n".
												'{title: "Div", format: "div", wrapper: true, merge_siblings: false},'."\r\n".
												'{title: "Column", items: ['."\r\n".
													'{title: "Column 1", block: "div", selector: "div", classes: "{t_class:col-1}", wrapper: true, merge_siblings: false},'."\r\n".
													'{title: "Column 2", block: "div", selector: "div", classes: "{t_class:col-2}", wrapper: true, merge_siblings: false},'."\r\n".
													'{title: "Column 3", block: "div", selector: "div", classes: "{t_class:col-3}", wrapper: true, merge_siblings: false},'."\r\n".
													'{title: "Column 4", block: "div", selector: "div", classes: "{t_class:col-4}", wrapper: true, merge_siblings: false},'."\r\n".
													'{title: "Column 5", block: "div", selector: "div", classes: "{t_class:col-5}", wrapper: true, merge_siblings: false},'."\r\n".
													'{title: "Column 6", block: "div", selector: "div", classes: "{t_class:col-6}", wrapper: true, merge_siblings: false},'."\r\n".
													'{title: "Column 7", block: "div", selector: "div", classes: "{t_class:col-7}", wrapper: true, merge_siblings: false},'."\r\n".
													'{title: "Column 8", block: "div", selector: "div", classes: "{t_class:col-8}", wrapper: true, merge_siblings: false},'."\r\n".
													'{title: "Column 9", block: "div", selector: "div", classes: "{t_class:col-9}", wrapper: true, merge_siblings: false},'."\r\n".
													'{title: "Column 10", block: "div", selector: "div", classes: "{t_class:col-10}", wrapper: true, merge_siblings: false},'."\r\n".
													'{title: "Column 11", block: "div", selector: "div", classes: "{t_class:col-11}", wrapper: true, merge_siblings: false},'."\r\n".
													'{title: "Column 12", block: "div", selector: "div", classes: "{t_class:col-12}", wrapper: true, merge_siblings: false}'."\r\n".
												']},'."\r\n".
												'{title: "Article", block: "article", wrapper: true, merge_siblings: false},'."\r\n".
												'{title: "Hgroup", block: "hgroup", wrapper: true},'."\r\n".
												'{title: "Aside", block: "aside", wrapper: true},'."\r\n".
												'{title: "Figure", block: "figure", wrapper: true}'."\r\n".
											']}'."\r\n".
										 ']';
	
	}

	
	public function getContentStylesheets($fromFE)
	{
	
		$cCss	= "";
		$main	= '/themes/' . THEME . '/css/main.css';
		$icons	= '/themes/' . THEME . '/css/icons.css';
		$style	= '/themes/' . THEME . '/css/style.css';
	
		if(file_exists(PROJECT_DOC_ROOT . $main)
		&& file_exists(PROJECT_DOC_ROOT . $icons)
		&& file_exists(PROJECT_DOC_ROOT . $style)
		) {
		
			#$cCss	= 'content_css : ["' . PROJECT_HTTP_ROOT . $main . '","' . PROJECT_HTTP_ROOT . $icons . '","' . PROJECT_HTTP_ROOT . $style . '"],'."\r\n";
			$cCss	= 'content_css : ["' . PROJECT_HTTP_ROOT . $icons . '"],'."\r\n";
			
			if(!$fromFE)
				$this->cssFiles[]		= $icons; // Add icons css to make icons visible within iconpicker
		}
		
		return $cCss;
	
	}

	
	public function getNewsletterTemplates()
	{
	
		$output	= "";
	
		foreach (glob(str_replace(SYSTEM_HTTP_ROOT, SYSTEM_DOC_ROOT, SYSTEM_TEMPLATE_DIR . '/tinymce_tpls/newsletter-*.htm')) as $filename) {
		
			$date	= str_replace(array("newsletter-", ".htm"), "", basename($filename));
			$output	.=	'{'."\r\n".
						  'title : "Newsletter ' . $date . '",'."\r\n".
						  'url : "' . str_replace(SYSTEM_DOC_ROOT, SYSTEM_HTTP_ROOT, $filename) . '?'.time().'",'."\r\n".
						  'description : "Newsletter Template - ' . $date . '"'."\r\n".
						'},'."\r\n";

		}
		
		return $output;
	
	}

}
