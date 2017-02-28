<?php
namespace Concise;



/**
 * Klasse Filemanager
 * 
 */

class Filemanager extends ContentsEngine
{

 	/**
	 * Filemanager Konstruktor
	 * 
	 * @param	string	$DB 	DB-Objekt
	 * @param	string	$o_lng	Sprach-Objekt
	 * @access	public
	 */
	 
	public function __construct($DB, $o_lng)
	{
	
		$this->DB		= $DB;
		$this->o_lng	= $o_lng;
	
	}


	/**
	 * Generiert Filemanager
	 * 
	 * @param	string	$root Root-Folder(s)
	 * @param	string	$lang Sprache
	 * @param	string	$queryExt Query-Extension
	 * @access	public
     * @return  string
	 */
	 
	public function getFilemanager($root = "", $lang = "en", $queryExt = "")
	{
	
		// Button close
		$btnDefs	= array(	"type"		=> "button",
								"class"		=> 'closeListBox close button-icon-only',
								"text"		=> "",
								"title"		=> '{s_title:close}',
								"icon"		=> "close"
							);
		
		$adminContent	=	parent::getButton($btnDefs);
	
		$adminContent	.=	'<h2 class="cc-section-heading cc-h2">{s_label:filemanager}' . ($root != "" && $root != "all" ? ' - ' . $root : '') . '</h2>' . "\r\n";
		
		$adminContent	.=	'<div id="elfinder">' . "\r\n" .
							'</div>' . "\r\n";
							
		$script			=	'<script type="text/javascript">' . "\r\n" . 
							'head.ready("ui", function(){'."\r\n".
							'head.ready("elfinderln", function(){'."\r\n".
							'head.ready("elfinderui", function(){'."\r\n".
							'head.ready("elfinder", function(){'."\r\n".
							'$(document).ready(function(){'."\r\n".
								/*#'yepnope(cc.httpRoot + "/extLibs/jquery/ui/jquery-ui-custom-elfinder.min.js");'."\r\n".
								'yepnope(cc.httpRoot + "/extLibs/jquery/elfinder/js/elfinder.min.js");'."\r\n".
								'yepnope(cc.httpRoot + "/extLibs/jquery/elfinder/js/i18n/elfinder.' . $lang . '.js");'."\r\n".
								'yepnope(cc.httpRoot + "/extLibs/codemirror/lib/codemirror.js");'."\r\n".
								'yepnope(cc.httpRoot + "/extLibs/codemirror/mode/htmlmixed/htmlmixed.js");'."\r\n".
								'yepnope(cc.httpRoot + "/extLibs/codemirror/mode/xml/xml.js");'."\r\n".
								'yepnope(cc.httpRoot + "/extLibs/codemirror/mode/css/css.js");'."\r\n".
								'yepnope(cc.httpRoot + "/extLibs/codemirror/mode/javascript/javascript.js");'."\r\n".
								#'yepnope(cc.httpRoot + "/extLibs/codemirror/mode/vbscript/vbscript.js");'."\r\n".
								'yepnope(cc.httpRoot + "/extLibs/codemirror/addon/display/fullscreen.js");'."\r\n".
								'yepnope(cc.httpRoot + "/extLibs/jquery/elfinder/css/elfinder.min.css");'."\r\n".
								'yepnope(cc.httpRoot + "/extLibs/codemirror/lib/codemirror.css");'."\r\n".
								'yepnope(cc.httpRoot + "/extLibs/codemirror/theme/cobalt.css");'."\r\n".
								'yepnope(cc.httpRoot + "/extLibs/codemirror/addon/display/fullscreen.css");'."\r\n".*/
								'var fmEditFile;'."\r\n".
								'var myElFinder = $("#elfinder").elfinder({'."\r\n".
									#'debug: true,'."\r\n".
									'resizable: true,'."\r\n".
									'lang: "' . $lang . '", // language (OPTIONAL)'."\r\n".
									'dateFormat: "d M Y H:i",'."\r\n".
									'fancyDateFormat: "$1 H:i:s",'."\r\n".
									'url: "' . PROJECT_HTTP_ROOT . '/extLibs/jquery/elfinder/php/connector.php?root=' . $root . $queryExt . '",  // connector URL (REQUIRED)'."\r\n".			
									'customData: {root : "' . $root . '"},  // root folder(s)'."\r\n".			
									'rememberLastDir: ' . ($queryExt != "" ? 'false' : 'true') . ',  // set to false if root folder is default'."\r\n".
									'validName: /^[^\s]$/, // disable names with spaces'."\r\n".
									'height: 540,  // frame height'."\r\n".
									'useBrowserHistory: false,  // browser history'."\r\n".
									'dialog: {'."\r\n".
										'create:function () {
        alert();$(this).closest(".ui-dialog")
            .find(".ui-button:first") // the first button
            .addClass("cc-button icon-ok");
    } // Dateiendung der zu bearbeiteten (markierten) Datei'."\r\n".
									'},'."\r\n".
									'handlers: {'."\r\n".
										'get: function(event, elfinderInstance) {'."\r\n".
											'fmEditFile = elfinderInstance.url(elfinderInstance.selected(this)); // zu bearbeitete (markierte) Datei'."\r\n".
											'elfinderInstance.options.dialogWidth = 1200; // Dateiendung der zu bearbeiteten (markierten) Datei'."\r\n".
										'}'."\r\n".
									'},'."\r\n".
									'commandsOptions : {'."\r\n".
										'mimes : ["text/html", "application/xhtml+xml", "text/css", "text/plain", "text/xml", "text/rtf", "text/javascript"],  // add here other mimes if required (e.g. "application/x-httpd-php")'."\r\n".
										'edit : {'."\r\n".
										  'dialogWidth : function(){ return Math.max(parseInt($(window).width() * 0.8), 1004); }, // Dialog'."\r\n".
										  'dialogHeight : function(){ return Math.max(parseInt($(window).height() * 0.8), 540); },'."\r\n".
										  'editors : ['."\r\n".
											'{'."\r\n".
											  'mimes : ["text/html", "application/xhtml+xml", "text/css", "text/plain", "text/xml", "text/rtf", "text/javascript"],  // add here other mimes if required (e.g. "application/x-httpd-php")'."\r\n".
											  /* tinyMCE als Editor
											  'load : function(textarea) {'."\r\n".
												'tinyMCE.execCommand("mceAddControl", true, textarea.id);'."\r\n".
											  '},'."\r\n".
											  'close : function(textarea, instance) {'."\r\n".
												'tinyMCE.execCommand("mceRemoveControl", false, textarea.id);'."\r\n".
											  '},'."\r\n".
											  'save : function(textarea, editor) {'."\r\n".
												'textarea.value = tinyMCE.get(textarea.id).selection.getContent({format : "html"});'."\r\n".
												'tinyMCE.execCommand("mceRemoveControl", false, textarea.id);'."\r\n".
											  '}'."\r\n".
											  */
											  /* CodeMirror als Editor */
											  'load : function(textarea) {'."\r\n".
												'// Dateiendung der zu bearbeiteten (markierten) Datei'."\r\n".
												'var extension = fmEditFile.split(".");'."\r\n".
												'extension = extension[extension.length - 1];'."\r\n".
												'var editMode = (extension == "css" ? "css" : (extension == "js" ? "javascript" : "htmlmixed"));'."\r\n".
												'if(editMode == "htmlmixed"){'."\r\n".
													'editMode = {name: "htmlmixed", scriptTypes: [{matches: /text\/html/i,mode: "html"},'."\r\n".
																			  '{matches: /\/x-handlebars-template|\/x-mustache/i,mode: null},'."\r\n".
																			  '{matches:/(text|application)\/(x-)?vb(a|script)/i,mode: "vbscript"}]'."\r\n".
															'};'."\r\n".
												'}'."\r\n".
												'this.myCodeMirror = CodeMirror.fromTextArea(textarea, {'."\r\n".
														'lineNumbers: true,'."\r\n".
														'matchBrackets: true,'."\r\n".
														'extraKeys: {"F11": function(cm) {cm.setOption("fullScreen", !cm.getOption("fullScreen"));},"Esc": function(cm) {if(cm.getOption("fullScreen")) cm.setOption("fullScreen", false);return false;}},'."\r\n".
														'theme: "concise",'."\r\n".
														'mode: editMode'."\r\n".
													'});'."\r\n".
											  '},'."\r\n".
											  'close : function(textarea, instance) {'."\r\n".
													'this.myCodeMirror = null;'."\r\n".
											  '},'."\r\n".
											  'save : function(textarea, editor) {'."\r\n".
													'textarea.value = this.myCodeMirror.getValue();'."\r\n".
													'this.myCodeMirror = null;'."\r\n".
											  '}'."\r\n".
											'}'."\r\n".
											']'."\r\n".
										'}'."\r\n".
									'}'."\r\n".
								'}).elfinder("instance");'."\r\n".
								#'console.log(myElFinder.storage("lastdir", "l3_XA"));'."\r\n".
								#'console.log(myElFinder);'."\r\n".
							'});'."\r\n".
							'});'."\r\n".
							'});'."\r\n".
							'});'."\r\n".
							'});'."\r\n".
							'</script>'."\r\n";
		
		
		return ContentsEngine::replaceStaText($adminContent . $script);

	} // Ende getFilemanager

}
