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
		$this->params	= explode("<>", $this->conValue);
		
		
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
			
			$this->params[0] = trim($this->a_POST[$this->conPrefix]);
			$this->formNewTab = trim($this->a_POST[$this->conPrefix.'_newtable']);
			$this->params[1] = (isset($this->a_POST[$this->conPrefix.'_mail']) ? 1 : 0);
			$this->params[2] = (isset($this->a_POST[$this->conPrefix.'_mailsource']) ? $this->a_POST[$this->conPrefix.'_mailsource'] : '');
			$this->params[3] = (isset($this->a_POST[$this->conPrefix.'_mailowner']) ? 1 : 0);
			$this->params[4] = trim($this->a_POST[$this->conPrefix.'_ownermail']);
			$this->params[5] = trim($this->a_POST[$this->conPrefix.'_mailcc']);
			$this->params[6] = trim($this->a_POST[$this->conPrefix.'_mailbcc']);
			$this->params[7] = trim($this->a_POST[$this->conPrefix.'_mailsubject']);
			$this->params[8] = (isset($this->a_POST[$this->conPrefix.'_pdf']) ? 1 : 0);
			$this->params[9] = trim($this->a_POST[$this->conPrefix.'_pdffolder']);
			$this->params[10] = (isset($this->a_POST[$this->conPrefix.'_pdfbrowser']) ? 1 : 0);
			$this->params[11] = (isset($this->a_POST[$this->conPrefix.'_pdfmail']) ? 1 : 0);
			$this->params[12] = (isset($this->a_POST[$this->conPrefix.'_userpdf']) ? 1 : 0);
			$this->params[13] = (isset($this->a_POST[$this->conPrefix.'_nodbstorage']) ? 1 : 0);
			$this->params[14] = (isset($this->a_POST[$this->conPrefix.'_validator']) ? 1 : 0);
			


			if($this->formNewTab != "") {
				$this->params[0] = "";
				$this->formTable = $this->formNewTab;
			}
			elseif($this->params[0] != "") {
				$this->formTable = $this->params[0];
			}
			elseif($this->params[0] == "" && $this->formNewTab == "") {
				$this->wrongInput[]	= $this->params[0];
				$this->formError[1]	= "{s_error:formtable}";
			}
				
				
			// Falls eine E-Mail an den Benutzer gehen soll, muss eine Angabe über die E-Mail-Quelle gemacht werden, sonst Fehler
			if($this->params[1] == 1 && $this->params[2] == "") {
				
				$this->wrongInput[]	= $this->params[0];
				$this->formError[2]	= "{s_error:mailsource}";
			}
			elseif($this->params[1] == 0 && $this->params[3] == 0) {
				$this->params[2]	= "";
			}
			
			// Falls eine E-Mail nur an den Betreiber gehen soll, Häckchen bei Benutzer entfernen
			if($this->params[3] == 1) {
				
				$this->params[1] = 0;
				$this->params[2] = "";
				
				// Falls in diesem Fall das Feld Betreiber-E-Mail leer ist, Fehlermeldung ausgeben
				if($this->params[4] == "") {
					$this->wrongInput[]	= $this->params[0];
					$this->formError[4]		= "{s_error:mail1}";
				}
			}
			
			// Falls eine pdf Datei an Browser oder E-Mail, muss pdf generieren gecheckt sein
			if($this->params[10] == 1 || $this->params[11] == 1) {
				
				$this->params[8]	= 1;
			}
			
			
		}

		if($this->formNewTab != "" && !$this->DB->tableExists(DB_TABLE_PREFIX . 'form_' . $this->formNewTab)) { // Prüft ob Tabelle vorhanden ist
			$this->wrongInput[]	= $this->formNewTab;
			$this->formError[1]	= "{s_error:formtable}";
		}

		if($this->params[4] != "" && (!filter_var($this->params[4], FILTER_VALIDATE_EMAIL) || strlen($this->params[4]) > 254)) {
			$this->wrongInput[]	= $this->params[0];
			$this->formError[4]	= "{s_error:mail2}";
		}
		if($this->params[5] != "" && (!filter_var($this->params[5], FILTER_VALIDATE_EMAIL) || strlen($this->params[5]) > 254)) {
			$this->wrongInput[]	= $this->params[0];
			$this->formError[5]	= "{s_error:mail2}";
		}
		if($this->params[6] != "" && (!filter_var($this->params[6], FILTER_VALIDATE_EMAIL) || strlen($this->params[6]) > 254)) {
			$this->wrongInput[]	= $this->params[0];
			$this->formError[6]	= "{s_error:mail2}";
		}
	
	}
	
	
	// makeUpdateStr
	public function makeUpdateStr()
	{

		// db-Updatestring
		$this->dbUpdateStr = "'";
		
		$this->dbUpdateStr .= $this->DB->escapeString($this->formTable) . "<>" . $this->DB->escapeString($this->params[1]) . "<>" . $this->DB->escapeString($this->params[2]) . "<>" . $this->DB->escapeString($this->params[3]) . "<>" . $this->DB->escapeString($this->params[4]) . "<>" . $this->DB->escapeString($this->params[5]) . "<>" . $this->DB->escapeString($this->params[6]) . "<>" . $this->DB->escapeString($this->params[7]) . "<>" . $this->DB->escapeString($this->params[8]) . "<>" . $this->DB->escapeString($this->params[9]) . "<>" . $this->DB->escapeString($this->params[10]) . "<>" . $this->DB->escapeString($this->params[11]) . "<>" . $this->DB->escapeString($this->params[12]) . "<>" . $this->DB->escapeString($this->params[13]) . "<>" . $this->DB->escapeString($this->params[14]);
		
		$this->dbUpdateStr .= "',";

	}
	
	
	// setParams, Parameter (default) setzen
	public function setParams()
	{

		// Nichtgesetzte Indizes setzen
		for($f = 0; $f <= 14; $f++) {
			
			if(!isset($this->params[$f])) {
				if($f == 1 || $f == 3 || $f == 8 || $f == 10 || $f == 12 || $f == 13 || $f == 14)
					$this->params[$f] = 0;
				else
					$this->params[$f] = "";
			}
					
			$this->formError[$f] = false;
		}

		// db-Query nach vorhandenen Formularen
		$this->queryFormName = $this->DB->query( "SELECT `table`,`title_" . $this->editLang . "` 
													FROM `" . DB_TABLE_PREFIX . "forms` 
												  ");
		#var_dump($this->queryFormName);
	
	}
	
	
	// makeUpdateStr
	public function getCreateElementHtml()
	{

		$output	 =	'<h4 class="cc-contype-heading cc-h4">{s_option:' . $this->conType . '}</h4>' . PHP_EOL;
							
		$output .=	'<fieldset>' . PHP_EOL;
		
		// Tabellenname (db)
		$output	.=	'<div class="leftBox">' . PHP_EOL .
					'<label>{s_label:tablename}</label>' . PHP_EOL;

		if($this->formError[1]) // Falls im Fehlerarray vorhanden Meldung ausgeben
			$output	.=	'<span class="notice">' . $this->formError[1] . '</span>' . PHP_EOL;

		// Tabellenauswahl
		$output	.=	'<select name="' . $this->conPrefix . '" class="inputLeft" onchange="$(this).parent(\'div\').next().children(\'input\').val(\'\');">' . PHP_EOL . 
					'<option value="">{s_option:choose}</option>' . PHP_EOL;
						
		foreach($this->queryFormName as $formTab) {
			
			$output	.=	'<option value="'.htmlspecialchars($formTab['table']).'"';
			
			if($this->params[0] == $formTab['table']) {
				$output	.= ' selected="selected"';
				$this->manualFormTab = false;
			}
			
			$output	.=	'>' . htmlspecialchars($formTab['table']).'</option>' . PHP_EOL;
		}

		$output	.=	'</select>' . PHP_EOL;
		$output	.=	'</div>' . PHP_EOL;

		// Falls kein angelegtes Formular ausgewählt ist, aber ein Tabellenname vorliegt, den Namen auf das Eingabefeld legen
		if($this->manualFormTab && $this->params[0] != "")
			$this->formNewTab = $this->params[0];

		$output	.=	'<div class="rightBox">' . PHP_EOL .
					'<label>' . ($this->manualFormTab && $this->params[0] != "" ? '<span class="notice">{s_option:choose}</span> ' : '') . '{s_common:or} ' .
					'<a href="' . ADMIN_HTTP_ROOT . '?task=forms&formname=" onclick="$(this).attr(\'href\', $(this).attr(\'href\') + $(this).parent().next().val());">&#9654; {s_label:newform}</a></label>' . PHP_EOL .
					'<input type="text" name="' . $this->conPrefix . '_newtable" maxlength="32" value="' . htmlspecialchars($this->formNewTab) . '" class="inputRight" />' . PHP_EOL;

		$output	.=	'</div>' . PHP_EOL .
					'<br class="clearfloat" />' . PHP_EOL; 

		$output .=	'</fieldset>' . PHP_EOL;
		$output .=	'<fieldset>' . PHP_EOL;
		
		// E-Mailversand des Formulars an Benutzer
		$output	.=	'<div class="leftBox">' . PHP_EOL .
					'<label class="markBox"><input type="checkbox" name="' . $this->conPrefix . '_mail" id="' . $this->conPrefix . '_mail" ' . ($this->params[1] == 1 ? ' checked="checked"' : '') . ' /></label>' . PHP_EOL .
					'<label for="' . $this->conPrefix . '_mail" class="inline-label">{s_label:formmail}</label>' . PHP_EOL .
					'</div>' . PHP_EOL;

		// Quelle für E-Mail des Empfängers (logged User oder aus Formular)
		$output	.=	'<div class="rightBox">' . PHP_EOL .
					'<label>{s_label:mailsource}' .
					parent::getIcon("info", "editInfo", 'title="{s_title:usermailsource}"') .
					'</label>' . PHP_EOL . 
					($this->formError[2] ? '<span class="notice">' . $this->formError[2] . '</span>' . PHP_EOL : '') . // Falls im Fehlerarray vorhanden Meldung ausgeben
					'<div class="fieldBox">' . PHP_EOL .
					'<label for="' . $this->conPrefix . '-mailsource">' . PHP_EOL .
					'<input type="radio" name="' . $this->conPrefix . '_mailsource" id="' . $this->conPrefix . '-mailsource" value="user"' . ($this->params[2] == "user" ? ' checked="checked"' : '') . '" /> {s_label:username}' .
					'</label>' . PHP_EOL .
					'<label>' . PHP_EOL .
					'<input type="radio" name="' . $this->conPrefix . '_mailsource" value="form"' . ($this->params[2] == "form" ? ' checked="checked"' : '') . '" style="margin-left:25px;" /> {s_label:formfield}' .
					'</label>' . PHP_EOL .
					'<br class="clearfloat" /></div>' . PHP_EOL .
					'</div><br class="clearfloat" />' . PHP_EOL; 

		// E-Mailversand des Formulars an Betreiber
		$output	.=	'<label class="markBox"><input type="checkbox" name="' . $this->conPrefix . '_mailowner" id="' . $this->conPrefix . '_mailowner" ' . ($this->params[3] == 1 ? ' checked="checked"' : '') . ' /></label>' . PHP_EOL .
					'<label for="' . $this->conPrefix . '_mailowner" class="inline-label">{s_label:formmail2}</label>' . PHP_EOL;

		// Betreiber-E-Mail
		$output	.=	'<label>{s_label:formowner}</label>' . PHP_EOL . 
					($this->formError[4] ? '<span class="notice">' . $this->formError[4] . '</span>' . PHP_EOL : '') . // Falls im Fehlerarray vorhanden Meldung ausgeben
					'<input type="text" name="' . $this->conPrefix . '_ownermail" maxlength="254" value="' . htmlspecialchars($this->params[4]) . '" />' . PHP_EOL .
					'<p>&nbsp;</p>' . PHP_EOL;
						
		// E-Mailempfänger Cc:
		$output	.= 	'<label>{s_label:formcc}</label>' . PHP_EOL . 
					($this->formError[5] ? '<span class="notice">' . $this->formError[5] . '</span>' . PHP_EOL : '') . // Falls im Fehlerarray vorhanden Meldung ausgeben
					'<input type="text" name="' . $this->conPrefix . '_mailcc" maxlength="254" value="' . htmlspecialchars($this->params[5]) . '" />' . PHP_EOL;
						
		// E-Mailempfänger Bcc:
		$output	.= '<label>{s_label:formbcc}</label>' . PHP_EOL . 
						($this->formError[6] ? '<span class="notice">' . $this->formError[6]. '</span>' . PHP_EOL : '') . // Falls im Fehlerarray vorhanden Meldung ausgeben
						'<input type="text" name="' . $this->conPrefix . '_mailbcc" maxlength="254" value="' . htmlspecialchars($this->params[6]) . '" />' . PHP_EOL;
						
		// E-Mail-Subject
		$output	.=	'<label>{s_label:subject}<span class="editLangFlag">' . $this->editLangFlag . '</span></label>' . PHP_EOL . 
					'<input type="text" name="' . $this->conPrefix . '_mailsubject" maxlength="300" value="' . htmlspecialchars($this->params[7]) . '" />' . PHP_EOL;

		$output .=	'</fieldset>' . PHP_EOL;
		$output .=	'<fieldset>' . PHP_EOL;

		// pdf-Datei mit Formulardaten generieren
		$output	.=	'<label class="markBox"><input type="checkbox" name="' . $this->conPrefix . '_pdf" id="' . $this->conPrefix . '_pdf"' . ($this->params[8] == 1 ? ' checked="checked"' : '') . ' /></label>' . PHP_EOL .
					'<label for="' . $this->conPrefix . '_pdf" class="inline-label">{s_label:makepdf}</label>' . PHP_EOL .
					'<script>head.ready(function(){ $(document).ready(function(){ $(\'*[id="' . $this->conPrefix . '_pdf"]\').click(function(){ $(\'*[id="' . $this->conPrefix . '_pdfDetails"]\').toggleClass(\'hide\');}); }); });</script>' . PHP_EOL;
															
		// Speicherort für pdf
		$output	.=	'<div id="' . $this->conPrefix . '_pdfDetails"' . ($this->params[8] == 0 ? ' class="hide"' : '') . '>' . PHP_EOL . 
					'<label for="' . $this->conPrefix . '_pdffolder">{s_label:pdffolder}</label>' . PHP_EOL . 
					'<input type="text" name="' . $this->conPrefix . '_pdffolder" id="' . $this->conPrefix . '_pdffolder" maxlength="64" value="' . htmlspecialchars($this->params[9]) . '" />' . PHP_EOL .
					'<br class="clearfloat" />' . PHP_EOL;

		// pdf-Datei ist benutzerspezifisch
		$output	.=	'<label class="markBox"><input type="checkbox" name="' . $this->conPrefix . '_userpdf" id="' . $this->conPrefix . '_userpdf" ' . ($this->params[12] == 1 ? ' checked="checked"' : '') . ' /></label>' . PHP_EOL .
					'<label for="' . $this->conPrefix . '_userpdf" class="inline-label">{s_label:userpdf}</label>' . PHP_EOL;

		// pdf-Datei an Browser
		$output	.=	'<label class="markBox"><input type="checkbox" name="' . $this->conPrefix . '_pdfbrowser" id="' . $this->conPrefix . '_pdfbrowser" ' . ($this->params[10] == 1 ? ' checked="checked"' : '') . ' /></label>' . PHP_EOL .
					'<label for="' . $this->conPrefix . '_pdfbrowser" class="inline-label">{s_label:pdfbrowser}</label>' . PHP_EOL;
						
		// E-Mailversand der pdf-Datei
		$output	.=	'<label class="markBox"><input type="checkbox" name="' . $this->conPrefix . '_pdfmail" id="' . $this->conPrefix . '_pdfmail" ' . ($this->params[11] == 1 ? ' checked="checked"' : '') . ' /></label>' . PHP_EOL .
					'<label for="' . $this->conPrefix . '_pdfmail" class="inline-label">{s_label:pdfmail}</label>' . PHP_EOL .
					'</div>' . PHP_EOL;

		$output .=	'</fieldset>' . PHP_EOL;
		$output .=	'<fieldset>' . PHP_EOL;

		// Keine Speicherung der Formulardaten in DB
		$output	.=	'<label class="markBox"><input type="checkbox" name="' . $this->conPrefix . '_nodbstorage" id="' . $this->conPrefix . '_nodbstorage" ' . ($this->params[13] == 1 ? ' checked="checked"' : '') . ' /></label>' . PHP_EOL .
					'<label for="' . $this->conPrefix . '_nodbstorage" class="inline-label">{s_label:nodbstorage}' .
					parent::getIcon("info", "editInfo form", 'title="{s_title:nodbstorage}"') .
					'</label>' . PHP_EOL;

		$output .=	'</fieldset>' . PHP_EOL;
		$output .=	'<fieldset>' . PHP_EOL;

		// Formvalidator
		$output	.=	'<label class="markBox"><input type="checkbox" name="' . $this->conPrefix . '_validator" id="' . $this->conPrefix . '_validator" ' . ($this->params[14] == 1 ? ' checked="checked"' : '') . ' /></label>' . PHP_EOL .
					'<label for="' . $this->conPrefix . '_validator" class="inline-label">Form-Validator (Javascript)' .
					'</label>' . PHP_EOL;
		
		$output .=	'</fieldset>' . PHP_EOL;

		return $output;
	
	}

}
