<?php
namespace Concise;


#####################################
#####  Individuelles Formular  ######
#####################################


/**
 * FormConfigElement class
 * 
 * content type => form
 */
class FormConfigElement extends ConfigElementFactory implements ConfigElements
{

	private $formTable		= "";
	private $formNewTab 	= "";
	private $manualFormTab	= true;
	private $queryFormName 	= array();
	private $formError		= array();
	
	/**
	 * Gibt ein FormConfigElement zurück
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
		
		if(substr_count($this->conValue, "<>") > 10)
			$this->params	= explode("<>", $this->conValue); // Legacy decode
		else
			$this->params	= (array)json_decode($this->conValue);

		
		// Parameter (default) setzen
		$this->setParams();

		
		// Ggf. POST auslesen
		if($this->a_POST != null)
			$this->evalElementPost();
		
		
		// DB-Updatestr generieren
		$this->makeUpdateStr();

		
		// Element-Formular generieren
		$this->output		= $this->getCreateElementHtml();
		
		
		// Ausgabe-Array erstellen und zurückgeben
		return $this->makeOutputArray();

	}
	
	
	// evalElementPost
	public function evalElementPost()
	{
	
		if(isset($this->a_POST[$this->conPrefix])) { // Falls das Formular abgeschickt wurde
			
			$this->params["formtab"] = trim($this->a_POST[$this->conPrefix]);
			$this->formNewTab = trim($this->a_POST[$this->conPrefix.'_newtable']);
			$this->params["mailform"] = (isset($this->a_POST[$this->conPrefix.'_mail']) ? 1 : 0);
			$this->params["mailsource"] = (isset($this->a_POST[$this->conPrefix.'_mailsource']) ? $this->a_POST[$this->conPrefix.'_mailsource'] : '');
			$this->params["mailowner"] = (isset($this->a_POST[$this->conPrefix.'_mailowner']) ? 1 : 0);
			$this->params["ownermail"] = trim($this->a_POST[$this->conPrefix.'_ownermail']);
			$this->params["cc"] = trim($this->a_POST[$this->conPrefix.'_mailcc']);
			$this->params["bcc"] = trim($this->a_POST[$this->conPrefix.'_mailbcc']);
			$this->params["subj"] = trim($this->a_POST[$this->conPrefix.'_mailsubject']);
			$this->params["pdf"] = (isset($this->a_POST[$this->conPrefix.'_pdf']) ? 1 : 0);
			$this->params["pdffolder"] = trim($this->a_POST[$this->conPrefix.'_pdffolder']);
			$this->params["browserpdf"] = (isset($this->a_POST[$this->conPrefix.'_pdfbrowser']) ? 1 : 0);
			$this->params["mailpdf"] = (isset($this->a_POST[$this->conPrefix.'_pdfmail']) ? 1 : 0);
			$this->params["userpdf"] = (isset($this->a_POST[$this->conPrefix.'_userpdf']) ? 1 : 0);
			$this->params["nodb"] = (isset($this->a_POST[$this->conPrefix.'_nodbstorage']) ? 1 : 0);
			$this->params["validator"] = (isset($this->a_POST[$this->conPrefix.'_formvalidator']) ? 1 : 0);
			$this->params["valonblur"] = (isset($this->a_POST[$this->conPrefix.'_validateonblur']) ? 1 : 0);
			$this->params["ajaxify"] = (isset($this->a_POST[$this->conPrefix.'_ajaxify']) ? 1 : 0);
			


			if($this->formNewTab != "") {
				$this->params["formtab"] = "";
				$this->formTable = $this->formNewTab;
			}
			elseif($this->params["formtab"] != "") {
				$this->formTable = $this->params["formtab"];
			}
			elseif($this->params["formtab"] == "" && $this->formNewTab == "") {
				$this->wrongInput[]	= $this->params["formtab"];
				$this->formError["mailform"]	= "{s_error:formtable}";
			}
				
				
			// Falls eine E-Mail an den Benutzer gehen soll, muss eine Angabe über die E-Mail-Quelle gemacht werden, sonst Fehler
			if($this->params["mailform"] == 1 && $this->params["mailsource"] == "") {
				
				$this->wrongInput[]	= $this->params["formtab"];
				$this->formError["mailsource"]	= "{s_error:mailsource}";
			}
			elseif($this->params["mailform"] == 0 && $this->params["mailowner"] == 0) {
				$this->params["mailsource"]	= "";
			}
			
			// Falls eine E-Mail nur an den Betreiber gehen soll, Häckchen bei Benutzer entfernen
			if($this->params["mailowner"] == 1) {
				
				$this->params["mailform"] = 0;
				$this->params["mailsource"] = "";
				
				// Falls in diesem Fall das Feld Betreiber-E-Mail leer ist, Fehlermeldung ausgeben
				if($this->params["ownermail"] == "") {
					$this->wrongInput[]	= $this->params["formtab"];
					$this->formError["ownermail"]		= "{s_error:mail1}";
				}
			}
			
			// Falls eine pdf Datei an Browser oder E-Mail, muss pdf generieren gecheckt sein
			if($this->params["browserpdf"] == 1 || $this->params["mailpdf"] == 1) {
				
				$this->params["pdf"]	= 1;
			}
			
			
		}

		if($this->formNewTab != "" && !$this->DB->tableExists(DB_TABLE_PREFIX . 'form_' . $this->formNewTab)) { // Prüft ob Tabelle vorhanden ist
			$this->wrongInput[]	= $this->formNewTab;
			$this->formError["mailform"]	= "{s_error:formtable}";
		}

		if($this->params["ownermail"] != "" && (!filter_var($this->params["ownermail"], FILTER_VALIDATE_EMAIL) || strlen($this->params["ownermail"]) > 254)) {
			$this->wrongInput[]	= $this->params["formtab"];
			$this->formError["ownermail"]	= "{s_error:mail2}";
		}
		if($this->params["cc"] != "" && (!filter_var($this->params["cc"], FILTER_VALIDATE_EMAIL) || strlen($this->params["cc"]) > 254)) {
			$this->wrongInput[]	= $this->params["formtab"];
			$this->formError["cc"]	= "{s_error:mail2}";
		}
		if($this->params["bcc"] != "" && (!filter_var($this->params["bcc"], FILTER_VALIDATE_EMAIL) || strlen($this->params["bcc"]) > 254)) {
			$this->wrongInput[]	= $this->params["formtab"];
			$this->formError["bcc"]	= "{s_error:mail2}";
		}
	
	}
	
	
	// makeUpdateStr
	public function makeUpdateStr()
	{

		// db-Updatestring
		$this->dbUpdateStr = "'" . $this->DB->escapeString(json_encode($this->params, JSON_UNESCAPED_UNICODE)) . "',";

	}
	
	
	// setParams, Parameter (default) setzen
	public function setParams()
	{

		// Nichtgesetzte Indizes setzen
		if(!isset($this->params["formtab"])) $this->params["formtab"] = "";
		if(!isset($this->params["mailform"])) $this->params["mailform"] = 0;
		if(!isset($this->params["mailsource"])) $this->params["mailsource"] = "";
		if(!isset($this->params["mailowner"])) $this->params["mailowner"] = 1;
		if(!isset($this->params["ownermail"])) $this->params["ownermail"] = "";
		if(!isset($this->params["cc"])) $this->params["cc"] = "";
		if(!isset($this->params["bcc"])) $this->params["bcc"] = "";
		if(!isset($this->params["subj"])) $this->params["subj"] = "";
		if(!isset($this->params["pdf"])) $this->params["pdf"] = 0;
		if(!isset($this->params["pdffolder"])) $this->params["pdffolder"] = "";
		if(!isset($this->params["browserpdf"])) $this->params["browserpdf"] = 0;
		if(!isset($this->params["mailpdf"])) $this->params["mailpdf"] = 0;
		if(!isset($this->params["userpdf"])) $this->params["userpdf"] = 0;
		if(!isset($this->params["nodb"])) $this->params["nodb"] = 0;
		if(!isset($this->params["validator"])) $this->params["validator"] = 0;
		if(!isset($this->params["valonblur"])) $this->params["valonblur"] = 0;
		if(!isset($this->params["ajaxify"])) $this->params["ajaxify"] = 0;

		
		// db-Query nach vorhandenen Formularen
		$this->queryFormName = $this->DB->query( "SELECT `id`,`table`,`title_" . $this->editLang . "` 
													FROM `" . DB_TABLE_PREFIX . "forms` 
												  ");
		#var_dump($this->queryFormName);
	
	}
	
	
	// makeUpdateStr
	public function getCreateElementHtml()
	{

		$output	 =	'<h4 class="cc-contype-heading cc-h4">{s_option:' . $this->conType . '}</h4>' . PHP_EOL;
							
		$output .=	'<fieldset>' . PHP_EOL;

		
		$urlExt	= "";
		
		if(!empty($this->params["formtab"])) {
			foreach($this->queryFormName as $key => $formTab) {
				if($this->params["formtab"] == $formTab['table']) {
					$urlExt	= '&list_fields=' . $this->queryFormName[$key]['id'];
				}
			}
		}
		
		// Button goto form
		$btnDefs	= array(	"href"		=> ADMIN_HTTP_ROOT . '?task=forms' . $urlExt,
								"class"		=> "modLink button-icon-only right",
								"text"		=> "{s_header:form} &raquo;",
								"title"		=> "{s_title:editform}",
								"icon"		=> "forms"
							);

		$output	.=	parent::getButtonLink($btnDefs);
		
		$output	.=	'<br class="clearfloat" />' . PHP_EOL; 

		
		// Tabellenname (db)
		$output	.=	'<div class="leftBox">' . PHP_EOL .
					'<label>{s_label:tablename}</label>' . PHP_EOL;

		if($this->formError["mailform"]) // Falls im Fehlerarray vorhanden Meldung ausgeben
			$output	.=	'<span class="notice">' . $this->formError["mailform"] . '</span>' . PHP_EOL;

		// Tabellenauswahl
		$output	.=	'<select name="' . $this->conPrefix . '" class="inputLeft" onchange="$(this).parent(\'div\').next().children(\'input\').val(\'\');">' . PHP_EOL . 
					'<option value="">{s_option:choose}</option>' . PHP_EOL;
						
		foreach($this->queryFormName as $formTab) {
			
			$output	.=	'<option value="'.htmlspecialchars($formTab['table']).'"';
			
			if($this->params["formtab"] == $formTab['table']) {
				$output	.= ' selected="selected"';
				$this->manualFormTab = false;
			}
			
			$output	.=	'>' . htmlspecialchars($formTab['table']).'</option>' . PHP_EOL;
		}

		$output	.=	'</select>' . PHP_EOL;
		$output	.=	'</div>' . PHP_EOL;

		// Falls kein angelegtes Formular ausgewählt ist, aber ein Tabellenname vorliegt, den Namen auf das Eingabefeld legen
		if($this->manualFormTab && $this->params["formtab"] != "")
			$this->formNewTab = $this->params["formtab"];

		$output	.=	'<div class="rightBox">' . PHP_EOL .
					'<label>' . ($this->manualFormTab && $this->params["formtab"] != "" ? '<span class="notice">{s_option:choose}</span> ' : '') . '{s_common:or} ' .
					'<a href="' . ADMIN_HTTP_ROOT . '?task=forms&formname=" onclick="$(this).attr(\'href\', $(this).attr(\'href\') + $(this).parent().next().val());">&#9654; {s_label:newform}</a></label>' . PHP_EOL .
					'<input type="text" name="' . $this->conPrefix . '_newtable" maxlength="32" value="' . htmlspecialchars($this->formNewTab) . '" class="inputRight" />' . PHP_EOL;

		$output	.=	'</div>' . PHP_EOL .
					'<br class="clearfloat" />' . PHP_EOL; 

		$output .=	'</fieldset>' . PHP_EOL;
		$output .=	'<fieldset>' . PHP_EOL;
		
		// E-Mailversand des Formulars an Benutzer
		$output	.=	'<div class="leftBox">' . PHP_EOL .
					'<label class="markBox"><input type="checkbox" name="' . $this->conPrefix . '_mail" id="' . $this->conPrefix . '_mail" ' . ($this->params["mailform"] == 1 ? ' checked="checked"' : '') . ' /></label>' . PHP_EOL .
					'<label for="' . $this->conPrefix . '_mail" class="inline-label">{s_label:formmail}</label>' . PHP_EOL .
					'</div>' . PHP_EOL;

		// Quelle für E-Mail des Empfängers (logged User oder aus Formular)
		$output	.=	'<div class="rightBox">' . PHP_EOL .
					'<label>{s_label:mailsource}' .
					parent::getIcon("info", "editInfo", 'title="{s_title:usermailsource}"') .
					'</label>' . PHP_EOL . 
					($this->formError["mailsource"] ? '<span class="notice">' . $this->formError["mailsource"] . '</span>' . PHP_EOL : '') . // Falls im Fehlerarray vorhanden Meldung ausgeben
					'<div class="fieldBox">' . PHP_EOL .
					'<label for="' . $this->conPrefix . '-mailsource">' . PHP_EOL .
					'<input type="radio" name="' . $this->conPrefix . '_mailsource" id="' . $this->conPrefix . '-mailsource" value="user"' . ($this->params["mailsource"] == "user" ? ' checked="checked"' : '') . '" /> {s_label:username}' .
					'</label>' . PHP_EOL .
					'<label>' . PHP_EOL .
					'<input type="radio" name="' . $this->conPrefix . '_mailsource" value="form"' . ($this->params["mailsource"] == "form" ? ' checked="checked"' : '') . '" /> {s_label:formfield}' .
					'</label>' . PHP_EOL .
					'<br class="clearfloat" /></div>' . PHP_EOL .
					'</div><br class="clearfloat" />' . PHP_EOL; 

		// E-Mailversand des Formulars an Betreiber
		$output	.=	'<label class="markBox"><input type="checkbox" name="' . $this->conPrefix . '_mailowner" id="' . $this->conPrefix . '_mailowner" ' . ($this->params["mailowner"] == 1 ? ' checked="checked"' : '') . ' /></label>' . PHP_EOL .
					'<label for="' . $this->conPrefix . '_mailowner" class="inline-label">{s_label:formmail2}</label>' . PHP_EOL;

		// Betreiber-E-Mail
		$output	.=	'<label>{s_label:formowner}</label>' . PHP_EOL . 
					($this->formError["ownermail"] ? '<span class="notice">' . $this->formError["ownermail"] . '</span>' . PHP_EOL : '') . // Falls im Fehlerarray vorhanden Meldung ausgeben
					'<input type="text" name="' . $this->conPrefix . '_ownermail" maxlength="254" value="' . htmlspecialchars($this->params["ownermail"]) . '" />' . PHP_EOL .
					'<p>&nbsp;</p>' . PHP_EOL;
						
		// E-Mailempfänger Cc:
		$output	.= 	'<label>{s_label:formcc}</label>' . PHP_EOL . 
					($this->formError["cc"] ? '<span class="notice">' . $this->formError["cc"] . '</span>' . PHP_EOL : '') . // Falls im Fehlerarray vorhanden Meldung ausgeben
					'<input type="text" name="' . $this->conPrefix . '_mailcc" maxlength="254" value="' . htmlspecialchars($this->params["cc"]) . '" />' . PHP_EOL;
						
		// E-Mailempfänger Bcc:
		$output	.= '<label>{s_label:formbcc}</label>' . PHP_EOL . 
						($this->formError["bcc"] ? '<span class="notice">' . $this->formError["bcc"]. '</span>' . PHP_EOL : '') . // Falls im Fehlerarray vorhanden Meldung ausgeben
						'<input type="text" name="' . $this->conPrefix . '_mailbcc" maxlength="254" value="' . htmlspecialchars($this->params["bcc"]) . '" />' . PHP_EOL;
						
		// E-Mail-Subject
		$output	.=	'<label>{s_label:subject}<span class="editLangFlag">' . $this->editLangFlag . '</span></label>' . PHP_EOL . 
					'<input type="text" name="' . $this->conPrefix . '_mailsubject" maxlength="300" value="' . htmlspecialchars($this->params["subj"]) . '" />' . PHP_EOL;

		$output .=	'</fieldset>' . PHP_EOL;
		$output .=	'<fieldset>' . PHP_EOL;

		// pdf-Datei mit Formulardaten generieren
		$output	.=	'<label class="markBox"><input type="checkbox" name="' . $this->conPrefix . '_pdf" id="' . $this->conPrefix . '_pdf"' . ($this->params["pdf"] == 1 ? ' checked="checked"' : '') . ' /></label>' . PHP_EOL .
					'<label for="' . $this->conPrefix . '_pdf" class="inline-label">{s_label:makepdf}</label>' . PHP_EOL .
					'<script>head.ready(function(){ $(document).ready(function(){ $(\'*[id="' . $this->conPrefix . '_pdf"]\').click(function(){ $(\'*[id="' . $this->conPrefix . '_pdfDetails"]\').toggleClass(\'hide\');}); }); });</script>' . PHP_EOL;
															
		// Speicherort für pdf
		$output	.=	'<div id="' . $this->conPrefix . '_pdfDetails"' . ($this->params["pdf"] == 0 ? ' class="hide"' : '') . '>' . PHP_EOL . 
					'<label for="' . $this->conPrefix . '_pdffolder">{s_label:pdffolder}</label>' . PHP_EOL . 
					'<input type="text" name="' . $this->conPrefix . '_pdffolder" id="' . $this->conPrefix . '_pdffolder" maxlength="64" value="' . htmlspecialchars($this->params["pdffolder"]) . '" />' . PHP_EOL .
					'<br class="clearfloat" />' . PHP_EOL;

		// pdf-Datei ist benutzerspezifisch
		$output	.=	'<label class="markBox"><input type="checkbox" name="' . $this->conPrefix . '_userpdf" id="' . $this->conPrefix . '_userpdf" ' . ($this->params["userpdf"] == 1 ? ' checked="checked"' : '') . ' /></label>' . PHP_EOL .
					'<label for="' . $this->conPrefix . '_userpdf" class="inline-label">{s_label:userpdf}</label>' . PHP_EOL;

		// pdf-Datei an Browser
		$output	.=	'<label class="markBox"><input type="checkbox" name="' . $this->conPrefix . '_pdfbrowser" id="' . $this->conPrefix . '_pdfbrowser" ' . ($this->params["browserpdf"] == 1 ? ' checked="checked"' : '') . ' /></label>' . PHP_EOL .
					'<label for="' . $this->conPrefix . '_pdfbrowser" class="inline-label">{s_label:pdfbrowser}</label>' . PHP_EOL;
						
		// E-Mailversand der pdf-Datei
		$output	.=	'<label class="markBox"><input type="checkbox" name="' . $this->conPrefix . '_pdfmail" id="' . $this->conPrefix . '_pdfmail" ' . ($this->params["mailpdf"] == 1 ? ' checked="checked"' : '') . ' /></label>' . PHP_EOL .
					'<label for="' . $this->conPrefix . '_pdfmail" class="inline-label">{s_label:pdfmail}</label>' . PHP_EOL .
					'</div>' . PHP_EOL;

		$output .=	'</fieldset>' . PHP_EOL;
		$output .=	'<fieldset>' . PHP_EOL;

		// Keine Speicherung der Formulardaten in DB
		$output	.=	'<label class="markBox"><input type="checkbox" name="' . $this->conPrefix . '_nodbstorage" id="' . $this->conPrefix . '_nodbstorage" ' . ($this->params["nodb"] == 1 ? ' checked="checked"' : '') . ' /></label>' . PHP_EOL .
					'<label for="' . $this->conPrefix . '_nodbstorage" class="inline-label">{s_label:nodbstorage}' .
					parent::getIcon("info", "editInfo form", 'title="{s_title:nodbstorage}"') .
					'</label>' . PHP_EOL;

		$output .=	'</fieldset>' . PHP_EOL;
		$output .=	'<fieldset>' . PHP_EOL;

		// Formvalidator
		$output	.=	'<div class="leftBox"><br />' . PHP_EOL .
					'<label class="markBox"><input type="checkbox" name="' . $this->conPrefix . '_formvalidator" id="' . $this->conPrefix . '_formvalidator" ' . ($this->params["validator"] == 1 ? ' checked="checked"' : '') . ' /></label>' . PHP_EOL .
					'<label for="' . $this->conPrefix . '_formvalidator" class="inline-label">Form-Validator (Javascript)' .
					'</label>' . PHP_EOL .
					'</div>' . PHP_EOL;

		// Validate on blur
		$output	.=	'<div class="rightBox">' . PHP_EOL .
					'<br /><label class="markBox"><input type="checkbox" name="' . $this->conPrefix . '_validateonblur" id="' . $this->conPrefix . '_validateonblur" ' . ($this->params["valonblur"] == 1 ? ' checked="checked"' : '') . ' /></label><label class="inline-label" for="' . $this->conPrefix . '_validateonblur">' . PHP_EOL .
					'Validate on blur</label>' . PHP_EOL .
					'</div>' . PHP_EOL;
		
		// Ajax submission
		$output .=	'<div class="leftBox"><br />' . PHP_EOL .
					'<label class="markBox"><input type="checkbox" name="' . $this->conPrefix . '_ajaxify" id="' . $this->conPrefix . '_ajaxify" ' . ($this->params["ajaxify"] == 1 ? ' checked="checked"' : '') . ' /></label><label class=" inline-label" for="' . $this->conPrefix . '_ajaxify">' . PHP_EOL .
					'Ajax form submission</label>' . PHP_EOL .
					'</div>' . PHP_EOL;
		
		$output .=	'</fieldset>' . PHP_EOL;

		return $output;
	
	}

}
