<?php
namespace Concise;


/**
 * Klasse für ein Bewertungs-Modul
 *
 */

class Rating extends Modules
{

	private static $minVote = 0;
	private static $maxVote = 5;
	private static $stepSize = 1;
	private static $size = "xxs";

	// constructor
	public function __construct($DB, $ajax = false)
	{
	
		$this->DB	= $DB;
		$this->ajax	= $ajax;
		
		if(empty($this->ajax)) {
		
			// Head files
			$this->cssFiles[]					= "extLibs/jquery/starrating/css/star-rating.min.css";
			$this->scriptFiles["starrating"]	= "extLibs/jquery/starrating/js/star-rating.min.js";
			$this->scriptFiles["starratingmod"]	= "access/js/starrating.js";
		}
	
	}

	/**
	 * Erstellt einen Starrater
	 * 
     * @param	string Modultyp (Artikel/Nachrichten/Termine), für die Bewertung
     * @param	string Kategorie-ID
     * @param	string ID des Datensatzes
     * @param	string Entscheidung ob Skriptdatei eingebunden und Bewertung ermöglicht werden soll (default = true)
     * @param	string captions (default = true)
     * @param	string glyphicons (default = false)
	 * @access	public
	 * @return	string
	 */
	public function getStarRater($module, $catId, $id, $enable = true, $caption = true, $glyphicon = false)
	{
		
		$votes		= 0;
		$rate		= 0;
		$rateVal	= 0;
		$url		= PROJECT_HTTP_ROOT;
		$output 	= "";
		
		if($id == ""
		|| $catId == ""
		)
			return "";
		
		$module	= $this->DB->escapeString($module);
		$catId	= (int)$catId;
		$id		= (int)$id;
		
		
		self::setThemeConfigArrays();

		
		// db-Query nach neuerem Datensatz
		$rating = $this->DB->query("SELECT * 
											FROM `" . DB_TABLE_PREFIX . "rating` 
											WHERE 
											`module` = '$module' 
											AND `id` = $id
										");

		if(count($rating) > 0) {
			
			$votes		= $rating[0]['votes'];
			$rate		= round($rating[0]['rate'], 2);
			$rateVal	= str_replace(",", ".", $rate);
		}
		
        
		// Falls HTML output (kein Ajax)
		if(empty($this->ajax)) {			
		
			$captions	= array("0" => ContentsEngine::replaceStaText("{s_title:totvotes}"));
			
			for($i = 1; $i <= self::$maxVote; $i++) {
				
				$captions[$i]	= ContentsEngine::replaceStaText("{s_title:star" . $i . "}");
			}
			$captions	= json_encode($captions, JSON_FORCE_OBJECT);
			
			$ratingInput	= '<input value="' . $rateVal . '" type="number" class="cc-rating hide" min="' . self::$minVote . '" max="' . self::$maxVote . '" step="' . self::$stepSize . '" data-size="' . self::$size . '" data-show-clear="false" data-default-caption="&Oslash; '.$rate.' ({s_title:totvotes}: '.$votes.')" data-clear-caption="{s_title:totvotes}: '.$votes.'" data-star-captions=\'' . $captions . '\' data-disabled="' . ($enable ? 'false' : 'true') . '" rel="'.$module.','.$catId.','.$id.','.$url.'" data-show-caption="' . $caption . '" data-glyphicon="' . $glyphicon . '" />' . "\r\n";
		
			$output =	'<div class="starrater '.$module.$catId.'_'.$id . ($enable ? '' : ' locked') . ' {t_class:halfrow}" title="&Oslash; '.$rate.' ('.$votes.' {s_title:totvotes})">' . "\r\n" .
						$ratingInput .
						'</div>' . "\r\n";

			$output = self::replacePlaceholders($output);
			return $output;
		}

		// Falls Ajax		
		$voteStr = '<p class="voted {t_class:badge} {t_class:success}">{s_text:voted}</p>' . "\r\n";
		$voteCap = '&Oslash; '.$rate.' ({s_title:totvotes}: '.$votes.')';
		
		$voteStr = self::replacePlaceholders($voteStr);
		$voteCap = self::replacePlaceholders($voteCap);
	
		return json_encode(array("votes" => $votes, "rate" => $rateVal, "notice" => $voteStr, "caption" => $voteCap));
	
	}
	


	/**
	 * Führt eine Bewertung aus
	 * 
	 * @access	public
	 * @return	string
	 */
	public function executeRating()
	{
		
		self::setThemeConfigArrays();
	
		$lockStr	= self::replacePlaceholders('<p class="novote {t_class:badge} {t_class:danger}">{s_notice:pollvoted}</p>') . "\r\n";
		
		if(!isset($GLOBALS['_GET']['mod'])
		|| !isset($GLOBALS['_GET']['cat'])
		|| !isset($GLOBALS['_GET']['id'])
		|| !isset($GLOBALS['_GET']['vote'])
		|| !is_numeric($GLOBALS['_GET']['vote'])
		|| $GLOBALS['_GET']['vote'] < self::$minVote
		|| $GLOBALS['_GET']['vote'] > self::$maxVote
		)
			return json_encode(array("notice" => $lockStr));
		
		$module	= $GLOBALS['_GET']['mod'];
		$catId	= $GLOBALS['_GET']['cat'];
		$id		= $GLOBALS['_GET']['id'];
		$vote	= $GLOBALS['_GET']['vote'];
		
		if(isset($GLOBALS['_COOKIE']["starrater_" . $module . $catId . "_" . $id]))
			return json_encode(array("notice" => $lockStr));
		
		// Vote
		$result	= $this->vote($module, $catId, $id, $vote);

		// set cookie
		@setcookie("starrater_" . $module . $catId . "_" . $id, "1", strtotime("tomorrow"), '/');

		if($result)
			return self::getStarRater($module, $catId, $id, false);
		
		return "0";
	
	}
	


	/**
	 * Setzt Bewertungen zurück
	 * 
	 * @access	public
	 * @return	string
	 */
	public function resetRating()
	{
	
		if( !isset($GLOBALS['_GET']['mod']) || 
			!isset($GLOBALS['_GET']['cat']) || 
			!isset($GLOBALS['_GET']['id'])
		)
			return false;		

		$module	= $this->DB->escapeString($GLOBALS['_GET']['mod']);
		$catId	= (int)$GLOBALS['_GET']['cat'];
		$id		= (int)$GLOBALS['_GET']['id'];
	
		if($catId == "all") {
	
			// bestimmen der CatId
			$queryCat = $this->DB->query("SELECT `cat_id` 
												FROM `" . DB_TABLE_PREFIX . $module . "` 
												WHERE id = $id 
												");
			
			$catId = $queryCat[0]['cat_id'];
		}		

		$vote	= $this->resetVotes($module, $id);
		$output = $this->getStarRater($module, $catId, $id, false);

		return $output;	
	
	}
	


	/**
	 * Führt eine Bewertung aus
	 * 
     * @param	string Modultyp (Artikel/Nachrichten/Termine), für die Bewertung
     * @param	string Kategorie-ID
     * @param	string ID des Datensatzes
     * @param	string Bewertungszahl
	 * @access	public
	 * @return	string
	 */
	public function vote($module, $catId, $id, $vote)
	{
	
		$module	= $this->DB->escapeString($module);
		$catId	= (int)$catId;
		$id		= (int)$id;
		$vote	= (int)$vote;
		
		
		// Einträge in DB löschen
		$lock = $this->DB->query("LOCK TABLES `" . DB_TABLE_PREFIX . "rating`");
			
		// db-Query nach neuerem Datensatz
		$queryExist = $this->DB->query("SELECT * 
											FROM `" . DB_TABLE_PREFIX . "rating` 
											WHERE 
												`module` = '$module' 
											AND `cat_id` = $catId
											AND `id` = $id
										");
		#var_dump($queryExist);
		
		if(!is_array($queryExist)
		|| count($queryExist) == 0
		)
		
			// db-Query nach neuerem Datensatz
			$rate = $this->DB->query("INSERT INTO `" . DB_TABLE_PREFIX . "rating` 
													(`module`,
													`cat_id`,
													`id`,
													`votes`,
													`rate`)
											VALUES ('$module',
													 $catId,
													 $id,
													 1,
													 $vote)
											");
		
		else {
			
			$oldRate 	= $queryExist[0]['rate'];
			$votes		= $queryExist[0]['votes'];
			$newRate	= ($votes * $oldRate + $vote) / ($votes + 1);
			$newRate	= str_replace(",", ".", $newRate);
			
			// db-Query nach neuerem Datensatz
			$rate = $this->DB->query("UPDATE `" . DB_TABLE_PREFIX . "rating` 
										SET `module` = '$module',
											`cat_id` = $catId,
											`id` = $id,
											`votes` = `votes`+1,
											`rate` = $newRate
										WHERE
											`module` = '$module' 
										AND `cat_id` = $catId
										AND `id` = $id
										
									");
		}
		
		// db-Sperre aufheben
		$unLock = $this->DB->query("UNLOCK TABLES");
	
		return $rate;
		
	}
	


	/**
	 * Setzt Bewertungen zurück
	 * 
     * @param	string Modultyp (Artikel/Nachrichten/Termine), für die Bewertung
     * @param	string ID des Datensatzes
	 * @access	public
	 * @return	boolean
	 */
	public function resetVotes($module, $id)
	{
		
		// Einträge in DB löschen
		$lock = $this->DB->query("LOCK TABLES `" . DB_TABLE_PREFIX . "rating`");
		
		// db-Query nach neuerem Datensatz
		$resVotes = $this->DB->query("DELETE FROM `" . DB_TABLE_PREFIX . "rating` 
										WHERE 
											`module` = '$module' 
										AND `id` = $id
										");
		
		
		// db-Sperre aufheben
		$unLock = $this->DB->query("UNLOCK TABLES");
		
		return $resVotes;
		
	}	
	


	/**
	 * setThemeConfigArrays
	 * 
	 * @access	public
	 * @return	boolean
	 */
	public function setThemeConfigArrays()
	{
		
		if(empty(ContentsEngine::$staText)) {
			ContentsEngine::$staText	= $GLOBALS['o_lng']->staText;
			$themeConf					= parse_ini_file(PROJECT_DOC_ROOT.'/themes/' . THEME . '/theme_styles.ini', true);
			ContentsEngine::$styleDefs	= $themeConf["class"];
		}		
		
	}	
	


	/**
	 * replacePlaceholders
	 * 
     * @param	string output
	 * @access	public
	 * @return	boolean
	 */
	public function replacePlaceholders($output)
	{
		
		$output = ContentsEngine::replaceStaText($output);
		$output = ContentsEngine::replaceStyleDefs($output);
	
		return $output;
		
	}	

}
