<?php
namespace Concise;


##############################
#######  Script-Code   #######
##############################

/**
 * ScriptConfigElement class
 * 
 * content type => script
 */
class ScriptConfigElement extends ConfigElementFactory implements ConfigElements
{

	/**
	 * Gibt ein ScriptConfigElement zurück
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
		$this->params	= (array)json_decode($this->conValue);

		
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
			
			// Script-Code
			$this->params["code"] = $this->a_POST[$this->conPrefix];

			if(!empty($this->a_POST[$this->conPrefix . '_position']))
				$this->params["pos"] = 1;
			else
				$this->params["pos"] = 0;
			
			// DB-Updatestr generieren
			$this->makeUpdateStr();
		
		}
	
	}
	
	
	// makeUpdateStr
	public function makeUpdateStr()
	{

		// db-Updatestring
		$params		= json_encode($this->params, JSON_UNESCAPED_UNICODE);

		$this->dbUpdateStr = "'" . $this->DB->escapeString($params) . "',";

	}
	
	
	// setParams, Parameter (default) setzen
	public function setParams()
	{

		if(!isset($this->params["code"]))
			$this->params["code"] = "";

		// CodeMirror als Editor
		$this->cssFiles[]	 = "extLibs/codemirror/lib/codemirror.css";
		$this->cssFiles[]	 = "extLibs/codemirror/theme/concise.css";
		$this->cssFiles[]	 = "extLibs/codemirror/addon/display/fullscreen.css";
		$this->scriptFiles["codemirror"]		= "extLibs/codemirror/lib/codemirror.js";
		$this->scriptFiles["codemirrorjs"]		= "extLibs/codemirror/mode/javascript/javascript.js";
		$this->scriptFiles["codemirrorfs"]		= "extLibs/codemirror/addon/display/fullscreen.js";


		// CodeMirror init, falls Template
		$this->scriptCode["jsvars"]	 =	'var codeMirrorInstances = codeMirrorInstances || [];'."\r\n";
								
		$this->scriptCode[]	 =	'(function($){'."\r\n".
								  '$.myScriptCodeMirror = function(myTextArea){'."\r\n".
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
		$output	.=	'<label onclick="$.myScriptCodeMirror(this.nextElementSibling);">{s_label:script}<span class="toggle toggleCodeEditor">Code-Editor</span></label>' . "\r\n" .
					'<textarea name="' . $this->conPrefix . '" id="script-' . $this->conPrefix . '" class="noTinyMCE code">' . htmlspecialchars($this->params["code"]) . '</textarea>' . "\r\n";
		
		// Position
		$output	.=	'<br class="clearfloat" /><br />' . "\r\n" .
					'<div class="leftBox">' . "\r\n" .
					'<label class="markBox">' . "\r\n" .
					'<input type="checkbox" name="' . $this->conPrefix . '_position" id="' . $this->conPrefix . '-position"' . (!empty($this->params["pos"]) ? ' checked="checked"' : '') . ' />' . "\r\n" .
					'</label>' . "\r\n" .
					'<label for="' . $this->conPrefix . '-position" class="inline-label">{s_label:codepos}</label>' . "\r\n" .
					'</div>' . "\r\n" .
					'<br class="clearfloat" /><br />' . "\r\n";
	
		if($this->isNewElement)
			$output	.=	'<script>' . array_pop($this->scriptCode) . '</script>' . "\n";

		return $output;
	
	}

}
