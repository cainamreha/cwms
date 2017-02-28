<?php
namespace Concise;



/**
 * SearchpageElement
 * 
 */

class SearchpageElement extends ElementFactory implements Elements
{
	
	/**
	 * Gibt ein SearchpageElement zurück
	 * 
	 * @access	public
	 * @param	string	$options	Parameter-Array (Wert, Styles, Wrap)
	 */
	public function __construct($options, $DB, &$o_lng, &$o_page)
	{
	
		$this->DB				= $DB;
		$this->o_lng			= $o_lng;
		$this->o_page			= $o_page;

		$this->conType			= $options["conType"];
		$this->conValue			= $options["conValue"];
		$this->conAttributes	= $options["conAttributes"];
		$this->conNum			= $options["conNum"];
		$this->conTable			= $options["conTable"];

	}
	

	/**
	 * Element erstellen
	 * 
	 * @access	public
     * @return  string
	 */
	public function getElement()
	{

		##############################
		########  Searchpage  ########
		##############################
		
		SEARCH_TYPE == "none" ? $this->gotoErrorPage() : '';
		
		require_once PROJECT_DOC_ROOT . "/inc/classes/Modules/class.Search.php";
		
		// Zu durchsuchende Tabllen
		$params = (array)json_decode($this->conValue);
		
		$searchTabs = array();
		
		if(!empty($params)) {
			foreach($params as $key => $val) {
				if($key == "s" && $val)
					$searchTabs[] = "pages";
				if($key == "a" && $val)
					$searchTabs[] = "articles";
				if($key == "n" && $val)
					$searchTabs[] = "news";
				if($key == "p" && $val)
					$searchTabs[] = "planner";
			}
		}
		
		// SearchPageobjekt
		$o_search							= new Search($this->DB, $this->o_lng, SEARCH_TYPE, $searchTabs);
		$this->scriptFiles["ajaxsearch"]	= "access/js/ajaxSearch.js"; // js-Datei einbinden
		$this->cssFiles[]					= "access/css/ajaxSearch.css"; // css-Datei einbinden
		$output								= $o_search->getSearch("big");
		
		// Ggf. Attribute (Styles) Wrapper-div hinzufügen
		if($this->conAttributes['id'] != "" || $this->conAttributes['class'] != "" || $this->conAttributes['style'] != "")
			$output	= $this->getContentElementWrapper($this->conAttributes, $output);

		return $output;
	
	}	
	
}
