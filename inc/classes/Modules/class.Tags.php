<?php
namespace Concise;



/**
 * Klasse für Schlagwortwolken
 *
 */

class Tags extends Modules {


	/**
	 * Tags Array
	 *
	 * @access public
     * @var    array
     */
	public $tags = array();

	/**
	 * Minimale Größe
	 *
	 * @access public
     * @var    string
     */
	public $minSize = "";

	/**
	 * Maximale Größe
	 *
	 * @access public
     * @var    string
     */
	public $maxSize = "";

	/**
	 * URL-Querystring
	 *
	 * @access public
     * @var    string
     */
	public $qs = "";

	
	/**
	 * Schlagwortklasse (Tags)
	 * 
     * @param	string	Sprache
     * @param	string	Benutzergruppe
     * @param	array	Eigene Benutzergruppe(n)
	 * @access	public
	 * @return	string
	 */
	public function __construct($DB, $lang, $group, $ownGroups)
	{	
	
		$this->DB			= $DB;
		$this->lang			= $lang;
		$this->group		= $group;
		$this->ownGroups	= $ownGroups;
	
	}
	
	
	/**
	 * Liefert ein Schlagwort-Array
	 * 
     * @param	string	Modultabelle(n)
     * @param	string	max. Anzahl an Tags (default = 0; unbegrenzt)
     * @param	int		max. Schrifgröße in px (default = 8)
     * @param	int		min. Schrifgröße in px (default = 30)
     * @param	string	Zielseite für Tag-Links (Datenliste), default Suchseite (default = -1004)
	 * @access	public
	 * @return	array
	 */
	public function getTags($tables, $totTags = 0, $minSize = 8, $maxSize = 30, $targetPage = -1004)
	{
		
		$tableArray	= explode(",", $tables);
		$lang		= $this->DB->escapeString($this->lang);
		$tabs		= "";
		$tags		= "";
		$queryTags	= array();
		$output		= "";
		$i 			= 1;
		// Benutzerberechtigung
		$group		= $this->group;
		$groupDB	= $this->DB->escapeString($group);
		
		$this->targetPage	= $targetPage;
		
		// ggf. Anzahl an Tags begrenzen
		if(is_int($totTags) && $totTags > 0)
			$totTagsDB	= " LIMIT " . $this->DB->escapeString($totTags);
		else
			$totTagsDB	= "";
		
		// Tags aus Tabellen auslesen
		foreach($tableArray as $tab) {
		
			$ownGroupsQueryStr = ContentsEngine::getOwnGroupsQueryStr($this->ownGroups, "dct.");
			
			$this->qs	.= $tab[0];
			
			$tab		= $this->DB->escapeString($tab);
			$tabs		= "SELECT `tags_" . $lang . "` AS tags 
								FROM `" . DB_TABLE_PREFIX . $tab . "_categories` AS dct
									LEFT JOIN `" . DB_TABLE_PREFIX . $tab . "` AS dt
									ON dt.`cat_id` = dct.`cat_id` 
								WHERE dt.`tags_" . $lang . "` != '' 
								AND `published` = 1 
								AND (dct.`group` = 'public' OR FIND_IN_SET('" . $groupDB . "', dct.`group`)" . $ownGroupsQueryStr . ")" .
								$totTagsDB .
								";";
			// db-Query nach allen Tags (nur veröffentlichte Daten)
			$query		= $this->DB->query($tabs, false);
			
			// Tags-Array erweitern
			if(count($query) > 0)
				$queryTags = array_merge($queryTags, $query);
			
			$i++;
		}
		
		foreach($queryTags as $key => $value) {
			if(trim($value['tags']) != "")
				$tags .= trim($value['tags']) . ",";
		}
		
		while(substr($tags, -1, 1) == ",") {
			$tags = substr($tags, 0, -1);
		}
		
		$tags = str_replace(", ", ",", $tags); // Leerzeichen entfernen
		$tags = str_replace(" ,", ",", $tags); // Leerzeichen entfernen
		$tagsArray = array_unique(explode(",", $tags));	// mehrfache Tags entfernen	
		$tagCloudArray = array();
		
		foreach($tagsArray as $tag) {
			if($tag != "")
				$tagCloudArray[$tag] = substr_count($tags . ",", $tag . ","); // Vorkommen des jeweiligen Tags zählen
		}
		
		arsort($tagCloudArray); // Array nach Werten (tag count) absteigend sortieren
		array_splice($tagCloudArray, $totTags, count($tagCloudArray) - $totTags); // Array auf max. angegebene Anzahl beschneiden
		ksort($tagCloudArray, SORT_LOCALE_STRING); // Sortierung der Schlüssel nach tag name
		
		
		$this->tags				= $tagCloudArray;
		$this->minSize			= $minSize;
		$this->maxSize			= $maxSize;
		
		
		return $this->tags;
	}
	 
	
	/**
	 * Liefert eine Schlagwortwolke
	 * 
	 * @access	public
	 * @return	string
	 */
	public function getTagCloud()
	{	
		
		$maxCount	= max($this->tags); // Tag mit häufigstem Vorkommen
		$minCount	= min($this->tags); // Tag mit geringstem Vorkommen
		$addSize	= $this->maxSize - $this->minSize; // Größenunterschied zwischen kleinster und größter Schriftgröße
		$output		= "";
		

		if($maxCount > 0) {
		
			$output		=	'<div class="tagCloudDiv clearfix">' . "\r\n" .
							'<ul class="tagCloud">' . "\r\n";
			
			
			// Zielseite, falls nicht Suchseite
			if($this->targetPage != "" && $this->targetPage != -1004)
				$qs	= 'tag';
			else
				$qs	= 'search';
			
			
			$this->targetPage = PROJECT_HTTP_ROOT . '/' . HTML::getLinkPath($this->targetPage) . '?' . $qs . '=';
			
			
			// Tags-Liste erstellen
			foreach($this->tags as $tagName => $tagCount) {
			
				$fontSize	= round(($this->minSize + ($tagCount / $maxCount * $addSize)), 0);
				$href		= $this->targetPage . urlencode($tagName) . '&amp;src=' . $this->qs;
				
				$output .= '<li><a href="' . $href . '" style="font-size:' . $fontSize . 'px;">' . $tagName . ' (' . $tagCount . ')</a></li>'; // Link zur Suchseite
			
			
				########################################
				### Optional einmalig aktivieren !!! ###
				########################################
				// Automatisches Ersetzten von Tag-Begriffen im Data-Text/-Teaser
				if(strpos($tagName, "#########") !== false) {
				#if($tagName != "Mesopotamien" && $tagName != "Israelis" && $tagName != "Israeliten") {
				
					$this->automaticTagLinks($tagName, $dataTable);
				}				
			
			}
			
			$output .=	'<br class="clearfloat" />' . "\r\n" .
						'</ul></div>' . "\r\n";
			
			// Falls HTML5
			if($this->html5)
				$output = '<nav>' . $output . '</nav>' . "\r\n";
		
		}

		return $output;
	}
	 
	
	/**
	 * Liefert eine Schlagwortliste
	 * 
     * @param	array	$excludeTags	Tags, die nicht gelistet werden sollen
	 * @access	public
	 * @return	string
	 */
	public function getTagList($excludeTags)
	{	
	
		if(count($this->tags) == 0)
			return false;
		
		
		$maxCount	= max($this->tags); // Tag mit häufigstem Vorkommen
		$minCount	= min($this->tags); // Tag mit geringstem Vorkommen
		$output		= "";
		

		if($maxCount > 0) {
		
			$output		=	'<div class="tagListDiv">' . "\r\n" .
							'<ol class="tagList">' . "\r\n";
			
			// Tags-Liste erstellen
			foreach($this->tags as $tagName => $tagCount) {
				
				if(!in_array($tagName, $excludeTags))
					$output .= '<li>' . $tagName . '</li>'; // Link zur Suchseite			
			}
			
			$output .=	'</ol>' . "\r\n" .
						'</div>' . "\r\n";
			
			// Falls HTML5
			if($this->html5)
				$output = '<nav>' . $output . '</nav>' . "\r\n";
		
		}

		return $output;
	}
	 
	
	/**
	 * Erstellt in Daten-Teasern/-Texten automatisch Links zu Schlagworten
	 * 
     * @param	array	$tagName	Tag der verlinkt werden soll
     * @param	array	$dataTarget	Daten-Zieltabelle (z.B. articles)
	 * @access	public
	 * @return	string
	 */
	public function automaticTagLinks($tagName, $dataTable)
	{

		$dataTable	= $this->DB->escapeString($dataTable);
		$lang		= $this->DB->escapeString($this->lang);
		$tagNameDB	= $this->DB->escapeString($tagName);
		$targetArt	= $this->DB->query("SELECT *, MATCH (`tags_" . $lang . "`, `last_name`, `header_" . $lang . "`) AGAINST ('$tagNameDB' IN BOOLEAN MODE) AS score, 
													MATCH (`header_" . $lang . "`, `teaser_" . $lang . "`, `text_" . $lang . "`) AGAINST ('$tagNameDB') AS score2 
											FROM `" . DB_TABLE_PREFIX . $dataTable . "` 
										WHERE MATCH (`tags_" . $lang . "`, `last_name`, `header_" . $lang . "`) AGAINST ('$tagNameDB' IN BOOLEAN MODE) 
										ORDER BY score + score2 DESC 
										LIMIT 1
										", false);
											
		#die(var_dump($targetArt));
		
		if(count($targetArt) > 0) {
		
			####################
			// Target-IDs
			$targID = $targetArt[0]['id'];
			
			// Ggf. Hauptzielseite (Basisseite) festlegen
			$mainPage = "Kulturgeschichte";
			if($targID >= 245 && $targID <= 259)
				$mainPage = "Erdgeschichte";
			if($targID > 259)
				$mainPage = "Evolution";
			
			$targCatID = $targetArt[0]['cat_id'];
			
			$targCatName = $this->DB->query("SELECT * 
												FROM `" . DB_TABLE_PREFIX . $dataTable . "_categories` 
											 WHERE `cat_id` = $targCatID 
											", false);

			if(count($targCatName) > 0) {

				$targCatName	= $targCatName[0]['category_'.$lang];
				$targCatAlias	= USE_CAT_NAME ? "/" . Modules::getAlias($targCatName) : "";
				$targName 		= Modules::getAlias($targetArt[0]['header_' . $lang]);
				$leftDel		= ">"; // ' ', '>'
				$rightDel		= "</strong>"; // ' ', '.', ',', '!', ')', '</i>', '</em>', '</strong>', '</p>', '<br />'
				$dataInitial	= $dataTable[0];
				
				// Schlagwörter in Artikeln (Teaser/Text) mit Links versehen
				$upd		= "UPDATE `" . DB_TABLE_PREFIX . $dataTable . "`
									SET `text_" . $lang . "` = REPLACE(`text_" . $lang . "`, '" . $leftDel . $tagName . $rightDel . "', '" . $leftDel . "<a class=\"link\" href=\"{#root}/" . $mainPage . $targCatAlias . "/" . $targName . "-" . $targCatID . $dataInitial . $targID . PAGE_EXT . "\">" . $tagName . "</a><span class=\"linkDetails\">&nbsp;</span>" . $rightDel . "')
									, `teaser_" . $lang . "` = REPLACE(`teaser_" . $lang . "`, '" . $leftDel . $tagName . $rightDel . "', '" . $leftDel . "<a class=\"link\" href=\"{#root}/" . $mainPage . $targCatAlias . "/" . $targName . "-" . $targCatID . $dataInitial . $targID . PAGE_EXT . "\">" . $tagName . "</a><span class=\"linkDetails\">&nbsp;</span>" . $rightDel . "')
									WHERE NOT FIND_IN_SET('" . $tagName . "', `tags_" . $lang . "`) 
									AND NOT FIND_IN_SET(' " . $tagName . "', `tags_" . $lang . "`) 
									AND NOT FIND_IN_SET('" . $tagName . " ', `tags_" . $lang . "`) 
									AND `id` != $targID
									";
									
				#die(var_dump($upd));							
				// db-Query
				$repl		= $this->DB->query($upd);
				#die(var_dump($repl));							
			}
		}

	}	

}
