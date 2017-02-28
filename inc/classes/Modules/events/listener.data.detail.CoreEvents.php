<?php
namespace Concise\Events\Data;

use Symfony\Component\EventDispatcher\Event;
use Concise\ContentsEngine;
use Concise\Modules;



##############################
###  EventListener-Klasse  ###
##############################

// DetailCoreEventsListener

class DetailCoreEventsListener extends Modules
{	

	// constructor
	public function __construct()
	{
	}

	// onGetDataDetails
	public function onGetDataDetails(Event $event)
	{
	
		// Event vars
		$event->dataID				= htmlspecialchars($event->queryData[0]['id']);
		$event->dataAlias			= Modules::getAlias($event->queryData[0]['header_'.$event->lang]);
		$event->dataSortID			= htmlspecialchars($event->queryData[0]['sort_id']);
		$event->dataCat				= $event->queryData[0]['category_' . $event->lang];
		$event->dataCatID			= htmlspecialchars($event->queryData[0]['cat_id']);
		$event->dataParentCat		= $event->queryData[0]['parent_cat'];
		$event->dataCatAlias		= Modules::getAlias($event->dataCat);
		$event->dataTags			= explode(",", $event->queryData[0]['tags_' . $event->lang]);
		$event->dataHeader			= htmlspecialchars($event->queryData[0]['header_' . $event->lang]);
		$event->dataAuthor			= htmlspecialchars($event->queryData[0]['author_name']);
		$event->dataText			= $event->queryData[0]['text_' . $event->lang];
		$event->dataCategory		= $event->dataCat;
		$event->dataRating			= $event->queryData[0]['rating'];
		$event->commentOutput		= "";
		$event->totComments			= "";
		$event->ratingOutput		= "";
		$event->nextDataHeader		= "";
		$event->prevDataHeader		= "";
		$event->nextDataLink		= "";
		$event->prevDataLink		= "";
		$event->backDataLink		= "";
		$event->nextData			= "";
		$event->prevData			= "";
		$event->backData			= "";
		$event->nextDataShort		= "";
		$event->prevDataShort		= "";
		$event->classExt			= "";
		$event->tagLinks			= array();
		$event->socialBar			= "";
		$event->adminIcons			= "";
		$event->date				= date("d.m.y", strtotime(htmlspecialchars($event->queryData[0]['date'])));
		$event->pastDate			= date("Y-m-d H:i:s", strtotime(htmlspecialchars($event->queryData[0]['date'])));
		$event->datetime			= date("Y-m-dTH:i:s", strtotime(htmlspecialchars($event->queryData[0]['date'])));
		$event->dataDay				= htmlspecialchars(date("d.", strtotime($event->queryData[0]['date'])));
		$event->dataYear			= htmlspecialchars(date("Y", strtotime($event->queryData[0]['date'])));
		$event->dataMon				= "{s_date:" . strtolower(date("M", strtotime(htmlspecialchars($event->queryData[0]['date'])))) . "}";
		#event->$dataMon			= htmlspecialchars(ucfirst(date("M", strtotime($event->queryData[0]['date']))));
		$event->dataDate			= Modules::getDateString($event->date, $event->dataDay, $event->dataYear, $event->dataMon); // Überprüfung ob heute, sonst Datumsausgabe
		$event->dataModDate			= htmlspecialchars($event->queryData[0]['mod_date']);
		
		
		
		// Teaser
		$event->dataTeaser			= $event->queryData[0]['teaser_' . $event->lang];
		
		// Falls der Teaser kein Html enthält, den Inhalt in einen p-tag packen
		if(strpos($event->dataTeaser, "<") !== 0)
			$event->dataTeaser		= '<p class="dataTeaser">' . nl2br($event->dataTeaser) . '</p>' . "\r\n";
		
		
		// Falls Author leer ist, unbekannt ausgeben
		if($event->dataAuthor == "")
			$event->dataAuthor		= "{s_common:unknown}";
		
		
		// Platzhalterschutz (#) entfernen
		$event->dataText			= str_replace("{#", "{", $event->dataText);
		
		
		// Tags auslesen
		if(count($event->dataTags) > 0
		&& $event->dataTags[0] != ""
		) {
			
			$filterPath = $event->targetUrl . PAGE_EXT;
			foreach($event->dataTags as $tag) {
			
				$tagName			= trim(htmlspecialchars($tag));

				// Button tag
				$btnDefs	= array(	"href"		=> $filterPath . '?tag=' . urlencode($tagName),
										"class"		=> 'tags {t_class:btnsec} {t_class:btnxs}',
										"text"		=> $tagName,
										"icon"		=> "tag"
									);
				
				$event->tagLinks[]	= ContentsEngine::getButtonLink($btnDefs);
				
				// Tags den Meta-Keywords hinzufügen
				$event->pageKeywords = $tagName . "," . $event->pageKeywords;
			}
		}
		if(count($event->dataTags) < 3) { // Falls keine oder wenig Tags vorhanden, Keywords aus Datenteaser und -text holen
								
			// Keywords vorschlagen
			require_once PROJECT_DOC_ROOT."/inc/classes/Modules/class.Keygen.php";
			
			$keywSource	= strip_tags($event->dataHeader . " " . $event->dataTeaser . " " . $event->dataText);
			$event->pageKeywords = \Concise\Keygen::getKeywords($keywSource, $event->lang, 5) . "," . $event->pageKeywords;	
		}
		
		$event->pageKeywords = explode(",", $event->pageKeywords);
		$event->pageKeywords = array_unique($event->pageKeywords);
		$event->pageKeywords = implode(", ", $event->pageKeywords);
		
		
		// Taglinks einbinden
		$event->tagLinks = implode(" | ", $event->tagLinks);					
		$event->tagLinks = '<p class="tags">{s_text:tags} <span class="tags">' . $event->tagLinks . '</span></p>' . "\r\n";
		
		// Falls Detailansicht, Meta-Description durch Datentexte ersetzen
		if(isset($GLOBALS['_GET']['id'])) {
		
			// Meta-Description durch Datentexte ersetzen
			$event->pageDescr = $event->dataHeader . " - ";
		
			// Falls Teaser vorhanden, diesen als Meta-Description verwenden
			if($event->dataTeaser != "")
				$event->pageDescr .= $event->dataTeaser;
			// Sonst Datentext an Meta-Description anfügen
			else
				$event->pageDescr .= substr($event->dataText, 0, 180);
			
			$event->pageDescr = strip_tags($event->pageDescr);
		}

		
		// Datenpfad
		$event->dataPath			= $event->targetUrl . '/' . $event->urlCatPath . $event->dataAlias . '-' . $event->dataCatID . $event->modType[0] . $event->dataID . PAGE_EXT;
		
		
		
		// DataNav
		// Prev
		if(count($event->queryPrevData) > 0) {
		
			$event->prevDataHeader	= $event->queryPrevData[0]['header_'.$event->lang];
			$prevDataAlias			= Modules::getAlias($event->prevDataHeader);
			$prevDataUrl			= $event->targetUrl . '/' . $event->urlCatPath . $prevDataAlias . '-' . $event->dataCatID . $event->modType[0] . $event->queryPrevData[0]['id'] . PAGE_EXT . ($event->queryString != "" ? '?' . $event->queryString : '');
			$prevTitle				= $event->prevDataHeader;

			// Button prevDataLink
			$btnDefs	= array(	"href"		=> $prevDataUrl,
									"class"		=> 'dataLinkFull {t_class:btndef}',
									"text"		=> '&nbsp;{s_link:prev'.$event->modType.'}',
									"icon"		=> "prev",
									"icontext"	=> ""
								);
			
			$event->prevDataLink	=	ContentsEngine::getButtonLink($btnDefs);
			
			$event->prevData		=	'<span class="prev {t_class:halfrow} {t_class:alpha} {t_class:alignlft} {t_class:margints} {t_class:marginbs}" title="' . $prevTitle . '">' .
										$event->prevDataLink .
										'<br class="{t_class:clearlft}" />' .
										'<a href="' . $prevDataUrl . '" class="prevDataLink">&laquo; ' . $prevTitle . '</a>' .
										'</span>' . "\r\n";

			// Button prevDataLinkShort
			$btnDefs	= array(	"href"		=> $prevDataUrl,
									"class"		=> 'dataLinkShort {t_class:btndef} {t_class:btnsm}',
									"text"		=> '{s_link:prevshort}',
									"attr"		=> 'rel="prev"',
									"icon"		=> "prev",
									"icontext"	=> ""
								);
			
			$prevDataLinkShort		=	ContentsEngine::getButtonLink($btnDefs);
		
			$event->prevDataShort	= '<span class="prev" title="{s_link:prev'.$event->modType.'} ' . $prevTitle . '">' . $prevDataLinkShort . '</span>' . "\r\n";
		
		}
		
		
		// Next
		if(count($event->queryNextData) > 0) {
		
			$event->nextDataHeader	= $event->queryNextData[0]['header_'.$event->lang];
			$nextDataAlias			= Modules::getAlias($event->nextDataHeader);
			$nextDataUrl			= $event->targetUrl . '/' . $event->urlCatPath . $nextDataAlias . '-' . $event->dataCatID . $event->modType[0] . $event->queryNextData[0]['id'] . PAGE_EXT . ($event->queryString != "" ? '?' . $event->queryString : '');
			$nextTitle				= $event->nextDataHeader;

			// Button nextDataLink
			$btnDefs	= array(	"href"		=> $nextDataUrl,
									"class"		=> 'dataLinkFull {t_class:btndef}',
									"text"		=> '{s_link:next'.$event->modType.'}&nbsp;',
									"icon"		=> "next"
								);
			
			$event->nextDataLink	=	ContentsEngine::getButtonLink($btnDefs, "right");
			
			$event->nextData		=	'<span class="next {t_class:halfrow} {t_class:omega} {t_class:right} {t_class:alignrgt} {t_class:margints} {t_class:marginbs}" title="' . $nextTitle . '">' .
										$event->nextDataLink .
										'<br class="{t_class:clearrgt}" />' .
										'<a href="' . $nextDataUrl . '" class="nextDataLink">' . $nextTitle . ' &raquo;</a>' .
										'</span>' . "\r\n";

			// Button nextDataLinkShort
			$btnDefs	= array(	"href"		=> $nextDataUrl,
									"class"		=> 'dataLinkShort {t_class:btndef} {t_class:btnsm}',
									"text"		=> '{s_link:nextshort}',
									"attr"		=> 'rel="next"',
									"icon"		=> "next",
									"icontext"	=> ""
								);
			
			$nextDataLinkShort		=	ContentsEngine::getButtonLink($btnDefs, "right");
		
			$event->nextDataShort	= '<span class="next" title="{s_link:next'.$event->modType.'} ' . $nextTitle . '">' . $nextDataLinkShort . '</span>' . "\r\n";
		
		}
		
		
		// Back to overview
		$event->linkBack			= $event->targetUrl;

		// Falls Hauptkategorie der Seite, Kategoriezusatz weglassen (unnötiger duplicate content)
		if($event->dataCatID != $event->catID) {
		
			$event->linkBack	   .= '/' . $event->urlCatPath;
			
			if(strrpos($event->linkBack, "/") === (strlen($event->linkBack) -1))
				$event->linkBack	= substr($event->linkBack, 0, -1);
			
			if($event->linkBack != $event->targetUrl)
				$event->linkBack	   .= '-' . $event->dataCatID . $event->modType[0];

		}
		$event->linkBack		   .= PAGE_EXT;
		
		if($event->queryString != "")
			$event->linkBack	   .= "?" . $event->queryString;
		
		// Button backDataLink
		$btnDefs	= array(	"href"		=> $event->linkBack,
								"class"		=> 'dataLinkFull {t_class:btndef}',
								"text"		=> '{s_link:back}&nbsp;',
								"icon"		=> "back"
							);
		
		$event->backDataLink	=	ContentsEngine::getButtonLink($btnDefs, "right");
			
		$event->backData		=	'<span class="backtolist {t_class:halfrow} {t_class:omega} {t_class:right} {t_class:clearrgt} {t_class:alignrgt} {t_class:margints} {t_class:marginbs}" title="{s_link:back}">' .
									$event->backDataLink;
		$event->backData	   .=	'</span>' . "\r\n";

		// Button backDataLinkShort
		$btnDefs	= array(	"href"		=> $event->linkBack,
								"class"		=> 'dataLinkShort {t_class:btndef} {t_class:btnsm}',
								"text"		=> '{s_link:backshort}',
								"attr"		=> 'rel="data-back"',
								"icon"		=> "back",
								"icontext"	=> ""
							);
		
		$backDataLinkShort		=	ContentsEngine::getButtonLink($btnDefs, "right");
	
		$event->backDataShort	= '<span class="backtolist" title="{s_link:back}">' . $backDataLinkShort . '</span>' . "\r\n";

		
		// Link zum aktuellen Datensatz
		$event->dataUrl		= $event->targetUrl . '/' . str_replace(PAGE_EXT, "", $event->urlCatPath);
		#$event->dataUrl	= implode("/", array_unique(explode("/", $event->dataUrl))) . '/' . $event->dataAlias . '-' . $event->dataCatID . $event->modType[0] . $event->dataID . PAGE_EXT;
		$event->dataUrl		= $event->dataUrl . $event->dataAlias . '-' . $event->dataCatID . $event->modType[0] . $event->dataID . PAGE_EXT;
		$event->dataLink	= '<a href="' . $event->dataUrl . ($event->queryString != "" ? '?' . $event->queryString : '') . '" class="gotoArticle dataLink">Permalink</a>' . "\r\n";

		return true;
	
	}
	
	// onAssignDataDetails
	public function onAssignDataDetails(Event $event)
	{	

		
		// Platzhalterersetzungen
		$event->tpl_data->assign("author", $event->dataAuthor);
		$event->tpl_data->assign("dataHeader", $event->dataHeader);
		$event->tpl_data->assign("dataEditButtons", $event->adminIcons);
		$event->tpl_data->assign("dataDate", $event->dataDate);
		$event->tpl_data->assign("datetime", $event->datetime);
		$event->tpl_data->assign("dataDay", $event->dataDay);
		$event->tpl_data->assign("dataMonth", $event->dataMon);
		$event->tpl_data->assign("dataYear", $event->dataYear);
		$event->tpl_data->assign("dataCat", $event->dataCategory);
		$event->tpl_data->assign("dataTags", $event->tagLinks);
		$event->tpl_data->assign("dataTeaser", $event->dataTeaser);
		$event->tpl_data->assign("dataText", $event->dataText);
		$event->tpl_data->assign("dataLink", $event->dataLink);
		$event->tpl_data->assign("nextDataHeader", $event->nextDataHeader);
		$event->tpl_data->assign("nextDataLink", $event->nextDataLink);
		$event->tpl_data->assign("nextData", $event->nextData);
		$event->tpl_data->assign("nextDataShort", $event->nextDataShort);
		$event->tpl_data->assign("linkBack", $event->linkBack);
		$event->tpl_data->assign("prevDataHeader", $event->prevDataHeader);
		$event->tpl_data->assign("prevDataLink", $event->prevDataLink);
		$event->tpl_data->assign("prevData", $event->prevData);
		$event->tpl_data->assign("prevDataShort", $event->prevDataShort);
		$event->tpl_data->assign("backData", $event->backData);
		$event->tpl_data->assign("backDataShort", $event->backDataShort);
		$event->tpl_data->assign("class", $event->classExt . (!empty($event->adminIcons) ? " cc-edit-access" : ''));
		$event->tpl_data->assign("rating", $event->ratingOutput);
		$event->tpl_data->assign("like", $event->socialBar);
		$event->tpl_data->assign("comments", $event->commentOutput);
		$event->tpl_data->assign("totComments", $event->totComments);
		$event->tpl_data->assign("#root", PROJECT_HTTP_ROOT);
		$event->tpl_data->assign("root", PROJECT_HTTP_ROOT);
		$event->tpl_data->assign("#root_img", IMAGE_DIR);
		$event->tpl_data->assign("root_img", IMAGE_DIR);

		return true;
	
	}

	
	// onAssignDataObjects
	public function onAssignDataObjects(Event $event)
	{	
	
		// Platzhalterersetzungen
		// Falls das erste Object ein Bild ist
		if(!empty($event->dataObjects) && $event->dataObjects[1]["type"] == "img") {
			$teaserImg	= $event->objOutput[1];
			$event->tpl_data->assign("object_img", $teaserImg);
			$event->tpl_data->assign("objOutput_img", $teaserImg);
			$event->objOutput[1] = ""; // Objekt aus Array löschen
		}
		else {
			$event->tpl_data->assign("object_img", "");
			$event->tpl_data->assign("objOutput_img", "");
		}
		
		// Daten-Objekte den einzelnen Platzhaltern zuweisen
		for($o = 1; $o <= $event->objectNumber; $o++) {
		
			$event->tpl_data->assign("object_".$o, $event->objOutput[$o]);
			
			if(!$o > 1)
				continue;
			
			if(!isset($tplCode))
				$tplCode	= file_get_contents($event->tpl_data->getTemplateFile());
			
			if(strpos($event->dataText, "object_" . $o) !== false
			|| strpos($event->dataText, "objOutput_" . $o) !== false
			|| strpos($tplCode, "object_" . $o) !== false
			)
				$event->objOutput[$o] = ""; // Objekt aus Array löschen
		}

		// Daten-Objekte, für die kein Einzelplatzhalter vorhanden ist
		$event->tpl_data->assign("object_all", implode("", $event->objOutput));
		$event->tpl_data->assign("objOutput", implode("", $event->objOutput));
		
		return true;
	
	}

} // Ende class
