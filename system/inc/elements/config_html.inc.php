<?php
namespace Concise;


##############################
#######  HTML-Inhalt  ########
##############################

/**
 * HtmlConfigElement class
 * 
 * content type => html
 */
class HtmlConfigElement extends ConfigElementFactory implements ConfigElements
{

	/**
	 * Gibt ein HtmlConfigElement zurück
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
	
	}

	
	public function getConfigElement($a_POST)
	{

		$this->a_POST	= $a_POST;
		$this->params	= $this->conValue; // Html-Code

		
		// Ggf. POST auslesen
		if($this->a_POST != null)
			$this->evalElementPost();
				
		
		// Parameter (default) setzen
		$this->setParams();

		
		// Element-Formular generieren
		$this->output		= $this->getCreateElementHtml();
		
		
		// Ausgabe-Array erstellen und zurückgeben
		return $this->makeOutputArray();
		
	}
	
	
	// evalElementPost
	public function evalElementPost()
	{
	
		if(isset($this->a_POST[$this->conPrefix])) { // Falls das Formular abgeschickt wurde
			
			// Html-Code
			$this->conValue = $this->a_POST[$this->conPrefix];
		
			// DB-Updatestr generieren
			$this->makeUpdateStr();
							
		}
	
	}
	
	
	// makeUpdateStr
	public function makeUpdateStr()
	{

		// db-Updatestring
		$this->dbUpdateStr = "'" . $this->DB->escapeString($this->conValue) . "',";

	}
	
	
	// setParams, Parameter (default) setzen
	public function setParams()
	{

		// CodeMirror als Editor
		$this->cssFiles[]	 = "extLibs/codemirror/lib/codemirror.css";
		$this->cssFiles[]	 = "extLibs/codemirror/theme/concise.css";
		$this->cssFiles[]	 = "extLibs/codemirror/addon/display/fullscreen.css";
		$this->scriptFiles["codemirror"]		= "extLibs/codemirror/lib/codemirror.js";
		$this->scriptFiles["codemirrorhtml"]	= "extLibs/codemirror/mode/htmlmixed/htmlmixed.js";
		$this->scriptFiles["codemirrorxml"]		= "extLibs/codemirror/mode/xml/xml.js";
		$this->scriptFiles["codemirrorcss"]		= "extLibs/codemirror/mode/css/css.js";
		$this->scriptFiles["codemirrorjs"]		= "extLibs/codemirror/mode/javascript/javascript.js";
		$this->scriptFiles["codemirrorfs"]		= "extLibs/codemirror/addon/display/fullscreen.js";


		// CodeMirror init, falls Template
		$this->scriptCode["jsvars"]	 =	'var codeMirrorInstances = codeMirrorInstances || [];'."\r\n";
								
		$this->scriptCode[]	 =	'(function($){'."\r\n".
								  '$.myCodeMirror = function(myTextArea){'."\r\n".
								  'if(typeof(myTextArea) != "object"){ return false; }'."\r\n".
								  'var cm = $("#" + myTextArea.id).data("CodeMirrorInstance");'."\r\n".
								  'if(typeof(cm) != "undefined" && cm != null){'."\r\n".
								  'cm.toTextArea();'."\r\n".
								  '$("#" + myTextArea.id).data("CodeMirrorInstance", null);'."\r\n".								  
								  '$(".CodeMirror-fullscreen-hint").remove();'."\r\n".
								  'return false;'."\r\n".
								  '}'."\r\n".
								  '$.getCodeMirrorInstance(myTextArea);'."\r\n".
								  '},'."\r\n".
								  '$.getCodeMirrorInstance = function(myTextArea){'."\r\n".
								  'var cmEditor	= CodeMirror.fromTextArea(myTextArea, {'."\r\n".
									'lineNumbers: true,'."\r\n".
									'matchBrackets: true,'."\r\n".
									'lineWrapping: true,'."\r\n".
									'extraKeys: {"F11": function(cm) {cm.setOption("fullScreen", !cm.getOption("fullScreen"));},"Esc": function(cm) {if(cm.getOption("fullScreen")) cm.setOption("fullScreen", false);return false;}},'."\r\n".
									'theme: "concise",'."\r\n".
									'mode: "javascript"'."\r\n".
								  '});'."\r\n".
								  'codeMirrorInstances.push(cmEditor);'."\r\n".
								  '$("#" + myTextArea.id).data("CodeMirrorInstance", cmEditor);'."\r\n".
								  '};'."\r\n".
								'})(jQuery);'."\r\n";
	
	}
	
	
	// makeUpdateStr
	public function getCreateElementHtml()
	{

		$output	 = '<h4 class="cc-contype-heading cc-h4">{s_option:' . $this->conType . '}</h4>' . "\r\n";

		// Textfeld anlegen
		$output	.=	'<label onclick="$.myCodeMirror(this.nextElementSibling);">{s_label:html}<span class="toggle toggleCodeEditor">Code-Editor</span></label>' . "\r\n" .
					'<textarea name="' . $this->conPrefix . '" id="html-' . $this->conPrefix . '" class="noTinyMCE code">' . htmlspecialchars($this->conValue) . '</textarea>' . "\r\n";
		
		if($this->isNewElement)
			$output	.=	'<script>' . array_pop($this->scriptCode) . '</script>' . "\n";

		return $output;
	
	}

}
