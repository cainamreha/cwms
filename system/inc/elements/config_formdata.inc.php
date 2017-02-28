<?php
namespace Concise;


###########################
#####  Formulardaten  #####
###########################

/**
 * FormdataConfigElement class
 * 
 * content type => formdata
 */
class FormdataConfigElement extends ConfigElementFactory implements ConfigElements
{

	private $queryFormName	= array();
	private $formError		= array();
	private $formTable		= "";
	private $formNewTab 	= "";
	private $manualFormTab	= true;
	private $formTitle		= "";
	private $formFields		= "";
	
	/**
	 * Gibt ein FormdataConfigElement zurück
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
			
			$this->params[0]	= trim($this->a_POST[$this->conPrefix]);
			$this->formNewTab	= trim($this->a_POST[$this->conPrefix.'_newtable']);
			$this->params[1]	= trim($this->a_POST[$this->conPrefix.'_formtitle']);
			$this->params[2]	= trim($this->a_POST[$this->conPrefix.'_formfields']);
			$this->params[3]	= isset($this->a_POST[$this->conPrefix.'_fileasname']) ? 1 : 0;
			$this->params[4]	= isset($this->a_POST[$this->conPrefix.'_editGroups']) ? $this->a_POST[$this->conPrefix.'_editGroups'] : array();


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
		
			// DB-Updatestr generieren
			$this->makeUpdateStr();
			
		}
	
	}
	
	
	// makeUpdateStr
	public function makeUpdateStr()
	{

		// db-Updatestring
		$this->dbUpdateStr = "'";
		
		$this->dbUpdateStr .= $this->DB->escapeString($this->formTable) . "<>" . $this->DB->escapeString($this->params[1]) . "<>" . $this->DB->escapeString($this->params[2]) . "<>" . $this->DB->escapeString($this->params[3]) . "<>" . $this->DB->escapeString(implode(",", $this->params[4]));
		
		$this->dbUpdateStr .= "',";

	}
	
	
	// setParams, Parameter (default) setzen
	public function setParams()
	{

		// Nichtgesetzte Indizes setzen
		for($f = 0; $f <= 4; $f++) {
			
			if(!isset($this->params[$f]) || $this->params[$f] == null) {
				
				if($f == 4)
					$this->params[$f] = array("public");
				else
					$this->params[$f] = "";
			}
			elseif($f == 4 && is_string($this->params[$f]))
				$this->params[$f] = explode(",", $this->params[$f]);
		
		}

		// db-Query nach vorhandenen Formularen
		$this->queryFormName = $this->DB->query( "SELECT `table`,`title_" . $this->editLang . "` 
												FROM `" . DB_TABLE_PREFIX . "forms` 
											");
		#var_dump($this->queryFormName);

		
		$this->formTitle	= $this->params[1];
		$this->formFields	= $this->params[2];

		if($this->formNewTab != "" && !$this->DB->tableExists(DB_TABLE_PREFIX . 'form_' . $this->formNewTab)) { // Prüft ob Tabelle vorhanden ist
			$this->wrongInput[]		= $this->formNewTab;
			$this->formError[1]		= "{s_error:formtable}";
		}
	
	}
	
	
	// makeUpdateStr
	public function getCreateElementHtml()
	{

		//Felder
		$output	 =	'<h4 class="cc-contype-heading cc-h4">{s_option:' . $this->conType . '}</h4>' . PHP_EOL;
							
		// Tabellenname (db)
		$output	.=	'<label>{s_label:tablename}</label>' . PHP_EOL;

		if(!empty($this->formError[1])) // Falls im Fehlerarray vorhanden Meldung ausgeben
			$output	.=	'<span class="notice">' . $this->formError[1] . '</span>' . PHP_EOL;

		$output	.=	'<div class="leftBox">' . PHP_EOL;
		// Tabellenauswahl
		$output	.=	'<select name="' . $this->conPrefix . '" class="inputLeft" onchange="$(this).next(\'input\').val(\'\');">' . PHP_EOL . 
					'<option value="">{s_option:choose}</option>' . PHP_EOL;
						
		foreach($this->queryFormName as $formTab) {
			
			$output	.=	'<option value="'.htmlspecialchars($formTab['table']).'"';
			
			if($this->params[0] == $formTab['table']) {
				$output	.= ' selected="selected"';
				$this->manualFormTab = false;
			}
			
			$output	.= '>' . htmlspecialchars($formTab['table']).'</option>' . PHP_EOL;
		}

		$output	.=	'</select>' . PHP_EOL;
		$output	.=	'</div>' . PHP_EOL;

		// Falls kein angelegtes Formular ausgewählt ist, aber ein Tabellenname vorliegt, den Namen auf das Eingabefeld legen
		$output	.=	'<div class="rightBox">' . PHP_EOL;
		if($this->manualFormTab && $this->params[0] != "")
			$this->formNewTab = $this->params[0];

		$output	.=	'<input type="text" name="' . $this->conPrefix . '_newtable" maxlength="32" value="' . htmlspecialchars($this->formNewTab) . '" class="inputRight" />' . PHP_EOL;

		$output	.=	'</div>' . PHP_EOL;
		$output	.=	'<br class="clearfloat" />' . PHP_EOL .
					'<p>&nbsp;</p>' . PHP_EOL; 

		// Formularüberschrift
		$output	.=	'<label>{s_label:formtitle}<span class="editLangFlag">' . $this->editLangFlag . '</span></label>' . PHP_EOL .
					'<input type="text" name="' . $this->conPrefix . '_formtitle" maxlength="255" value="' . htmlspecialchars($this->formTitle) . '" />' . PHP_EOL;

		// Formularfelder, die nicht angeziegt werden sollen
		$output	.=	'<label>{s_label:ommitformfields}</label>' . PHP_EOL .
					'<input type="text" name="' . $this->conPrefix . '_formfields" maxlength="255" value="' . htmlspecialchars($this->formFields) . '" />' . PHP_EOL .
					'<br class="clearfloat" />' . PHP_EOL;
						
		// pdf-Datei an Browser
		$output	.=	'<label class="markBox"><input type="checkbox" name="' . $this->conPrefix . 'fileasname" id="' . $this->conPrefix . '-fileasname"' . ($this->params[3] == 1 ? ' checked="checked"' : '') . ' /></label>' . PHP_EOL .
					'<label for="' . $this->conPrefix . '-fileasname">{s_label:fileasname}</label>' . PHP_EOL .
					'<br class="clearfloat" />' . PHP_EOL;
						
		// Benutzergruppen mit Berechtigung zum Editieren
		$output	.=	'<label>{s_label:editgroups}</label>' . PHP_EOL .
					'<select multiple="multiple" size="' . count($this->userGroups) . '" name="' . $this->conPrefix . '_editGroups[]" class="selgroup">' . PHP_EOL;

		// Benutzergruppe auflisten
		foreach($this->userGroups as $group) {
			$output	.=	'<option value="' . $group . '"' . (in_array($group, $this->params[4]) ? ' selected="selected"' : '') . '>' . (in_array($group, $this->systemUserGroups) ? '{s_option:group' . $group . '}' : $group) . '</option>' . PHP_EOL; // Benutzergruppe
		}
			
		$output	.=	'</select>' . PHP_EOL;
						 
		$output	.=	'<br class="clearfloat" />' . PHP_EOL;

		return $output;
	
	}

}
