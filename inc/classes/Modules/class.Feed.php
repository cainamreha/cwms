<?php
namespace Concise;


/**
 * Klasse für Newsfeeds
 *
 */

class Feed
{	
	
	/**
	 * Datenbank-Resource
	 *
	 * @access private
     * @var    object
     */
	private $DB = null;
	
	/**
	 * Eindeutige ID des anzuzeigenden Feeds
	 *
	 * @access private
     * @var    string
     */
	private $feedID = null;
	
	/**
	 * Typ
	 *
	 * @access private
     * @var    string
     */
	private $childCatIDs = array();

	/**
	 * Typ
	 *
	 * @access private
     * @var    string
     */
	private $feedType = "rss";
	
	/**
	 * Ziel-URL
	 *
	 * @access private
     * @var    string
     */
	private $targetUrl = "";
	
	/**
	 * Sprache
	 *
	 * @access private
     * @var    string
     */
	private $lang = DEF_LANG;
	
	/**
	 * Benutzergruppe
	 *
	 * @access private
     * @var    string
     */
	private $group = "";
	
	/**
	 * db ID
	 *
	 * @access private
     * @var    string
     */
	private $feedIDdb = null;
	
	/**
	 * Feed zur Ausgabe
	 *
	 * @access private
     * @var    string
     */
	private $feed = '';
	
	
	/**
	 * Konstruktor 
	 * 
	 * Bekommt die ID des darzustellenden Feeds übergeben und
	 * erstellt daraus den Feed.
	 * 
	 * @param	integer ID des Feeds
	 * @param	string	Art des Newsfeeds (default = 'rss')
	 * @param	string	Zielseite (default = '')
	 * @access	public
	 */
	public function __construct($DB, $lang, $feedID, $feedType = "rss", $targetUrl = "")
	{
		
		//Verbindung zur Feed-Datenbank aufbauen
		$this->DB 			= $DB;
		$this->lang			= $lang;

		// Session-Vars-Array
		$this->g_Session	= Security::getSessionVars();
		
		// Benutzerberechtigung
		if(isset($this->g_Session['group']) && $this->g_Session['group'] != "")
			$group = $this->g_Session['group'];
		else
			$group = "public";

		$this->group = $this->DB->escapeString($group);
		
		$this->feedID		= intval($feedID);
		$this->feedType		= $feedType;
		$this->targetUrl	= $targetUrl;
		
		$this->feedIDdb = $this->DB->escapeString($feedID);
		$this->feedTypeDb = $this->DB->escapeString($feedType);
		
			self::getAllChildCats($this->feedIDdb);
			$this->dbFilter = "`news_categories`.`cat_id` = $this->feedIDdb AND (`news`.`cat_id` = $this->feedTypeDb";
								
			foreach($this->childCatIDs as $childCat) {
				$this->dbFilter .= " OR `news`.`cat_id` = $childCat";
			}
		
		$this->printHeader();
		
		$this->printEntries();
		
		$this->printFooter();
		
	}
	
			
			
	/**
	 * Gibt die direkten Kindkategorien einer Elternkategorie zurück
	 * 
     * @param	string Kategorie-ID der Elternkategorie
     * @param	string Limitierung der Datensätze (default = '')
	 * @access	public
	 * @return	string
	 */
	public function getChildCats($catID, $queryLimit = "")
	{
		
		$catID		= (int)$this->DB->escapeString($catID);
		
		$dbOrder	= "ORDER BY dct.`sort_id`";
		
		// db-Query nach Kindkategorien
		$queryChildCats = $this->DB->query("SELECT * 
												FROM `" . DB_TABLE_PREFIX . "news_categories` AS dct 
											WHERE `parent_cat` = $catID 
												AND (`group` = 'public' OR FIND_IN_SET('" . $this->group . "', `group`)) 
												$dbOrder 
												$queryLimit
											", false);
		#var_dump($queryChildCats);

		return $queryChildCats;

	}
	


	/**
	 * Gibt Kategorie-IDs aller Kindkategorien einer Elternkategorie zurück
	 * 
     * @param	string Kategorie-ID der Elternkategorie
	 * @access	public
	 */
	public function getAllChildCats($parentCatID)
	{
		
		$childCats = self::getChildCats($parentCatID);
		
		if(count($childCats) > 0) {
			
			foreach($childCats as $parentCat) {
				
				$this->childCatIDs[] = $parentCat['cat_id'];
				self::getAllChildCats($parentCat['cat_id']);
			}
		}
	}
	

	
	/**
	 * Erstellt einen Newsfeed-Header 
	 * 
	 * @access	private
	 */
	private function printHeader()
	{
		
		//XML-Datei beginnen
		$this->feed .= '<?xml version="1.0" encoding="UTF-8"?>';
		
		//Daten des Feed auslesen:
		$headerData = $this->DB->query("SELECT * 
											FROM `" . DB_TABLE_PREFIX . "news_categories` 
									    WHERE `cat_id` = $this->feedIDdb
											AND (`group` = 'public' OR FIND_IN_SET('" . $this->group . "', `group`)) 
											AND `newsfeed` > 0 
										");
		
		if(count($headerData) == 0) {
			header("Location: error" . PAGE_EXT . "?feed=0");
			exit;
		}
				
		$feed = $headerData[0];

		if($this->feedType == "atom") {
		
			//Atom-Feed Version
			$this->feed .=  '<feed xmlns="http://www.w3.org/2005/Atom">';			
			$this->feed .=  '<title>'.$feed['category_' . $this->lang].' - '.htmlspecialchars(HTML_TITLE != "" ? HTML_TITLE : str_replace("http://", "", PROJECT_HTTP_ROOT)).'</title>';
			$this->feed .=  '<subtitle>'.$feed['category_' . $this->lang].'</subtitle>';
			$this->feed .=  '<logo>'.PROJECT_HTTP_ROOT.'/'.IMAGE_DIR.'logo_feed.png</logo>';
		}

		if($this->feedType == "rss") {
		
			//RSS-Feed Version
			$this->feed .=  '<rss version="2.0">';			
			$this->feed .=  '<channel>';
			$this->feed .=  '<title>'.$feed['category_' . $this->lang].' - '.htmlspecialchars(HTML_TITLE != "" ? HTML_TITLE : str_replace("http://", "", PROJECT_HTTP_ROOT)).'</title>';
			$this->feed .=  '<image><url>'.PROJECT_HTTP_ROOT.'/'.IMAGE_DIR.'logo_feed.png</url></image>';
			$this->feed .=  '<link></link>';
			$this->feed .=  '<description><![CDATA['.$feed['category_' . $this->lang].']]></description>';
			$this->feed .=  '<language>'.$this->lang.'-'.$this->lang.'</language>';
		}
	}



	/**
	 * Erstellt Newsfeed-Einträge
	 * 
	 * @access	private
	 */
	private function printEntries()
	{
		
		//Einträge des Feeds auslesen:		
		$entries = $this->DB->query("SELECT * 
									 FROM `" . DB_TABLE_PREFIX . "news` as dt
										LEFT JOIN `" . DB_TABLE_PREFIX . "news_categories` AS dct
										ON dt.`cat_id` = dct.`cat_id` 
									 WHERE (dct.`cat_id` = $this->feedIDdb OR dct.`parent_cat` = $this->feedIDdb)
										AND published = 1 
										AND (dct.`group` = 'public' OR FIND_IN_SET('" . $this->group . "', dct.`group`)) 
									 ORDER BY `date` DESC
									");
		
		#var_dump($entries);
		
		if(count($entries)==0)die('Es konnte kein Feed mit der Nummer '.$this->feedID.' gefunden werden, bzw. der Feed ist leer.');
		
		$feed = $entries[0];
		
		foreach($entries as $entry)
		{
		
			$catAlias	= USE_CAT_NAME ? Modules::getAlias($entry['category_' . $this->lang]) . '/' : '';
			$dataHeader	= $entry['header_' . $this->lang];
			$dataAlias	= Modules::getAlias($entry['header_' . $this->lang]);
			$dataPath	= $catAlias . $dataAlias . '-' . $entry['cat_id'] . 'n' . $entry['id'] . PAGE_EXT;
			$dataTeaser	= $entry['teaser_' . $this->lang];
			$dataDate	= strtotime($entry['date']);
			#die($this->targetUrl);
		
			if($this->feedType == "atom") {
				$this->feed .=  '<entry>';
				$this->feed .=  '<title>'.$dataHeader.'</title>';
				$this->feed .=	'<link href="' . $this->targetUrl . '/' . $dataPath . '" />';
				$this->feed .=  '<updated>'.date(DATE_ATOM, $dataDate).'</updated>';
				$this->feed .=  '<summary>'.strip_tags($dataTeaser).'</summary>';
				$this->feed .=  '</entry>';
			}
		
			if($this->feedType == "rss") {			
				$this->feed .=  '<item>';
				$this->feed .=  '<title>'.$dataHeader.'</title>';
				$this->feed .=  '<link>' . $this->targetUrl . '/' . $dataPath . '</link>';
				#$this->feed .=  '<description><![CDATA[' . $dataTeaser . ']]></description>';
				$this->feed .=  '<description>' . strip_tags($dataTeaser) . '</description>'; // works in keosu app rss feed
				$this->feed .=  '<pubDate>'.date(DATE_RSS, $dataDate).'</pubDate>';
				$this->feed .=  '</item>';
			}
		}
	}
	
	
	
	/**
	 * Erstellt einen Newsfeed-Header 
	 * 
	 * @access	private
	 */
	private function printFooter()
	{
		if($this->feedType == "atom") {
			
			$this->feed .=  '</feed>';
		
		}
		
		if($this->feedType == "rss") {
			
			$this->feed .=  '</channel>';
			$this->feed .=  '</rss>';
		}
	}
	
	
	
	/**
	 * Erstellt einen Newsfeed-Header 
	 * 
	 * @access	public
	 * @return	string
	 */
	public function outputFeed()
	{
		//Feedtype ausgeben
		header("Content-Type: application/rss+xml");
		
		echo $this->feed;
	}
	
	
	public function getFeedContent()
	{
		return $this->feed;	
	}
	
}
