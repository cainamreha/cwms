<?php
namespace Concise;



###################################################
#################  Statistiken  ###################
###################################################

// Statistiken

class Admin_Stats extends Admin implements AdminTask
{

	/**
	 * Stats-Objekt
	 * 
	 * @access private
     * @var    object
	 */
	private $o_stats = null;

	/**
	 * Statistikjahr
	 * 
	 * @access public
     * @var    string
	 */
	public $statYear = "";

	/**
	 * Statistikjahre (Archiv)
	 * 
	 * @access public
     * @var    array
	 */
	public $statArchiveYears = array();

	/**
	 * Sortierungsschlüssel
	 * 
	 * @access public
     * @var    string
	 */
	public $orderBy = "name";

	/**
	 * Sortierungsrichtung
	 * 
	 * @access public
     * @var    string
	 */
	public $sort = "asc";

	
	public function __construct($DB, $o_lng, $task, $init = false)
	{

		// Admin-Elternklasse aufrufen
		parent::__construct($DB, $o_lng);
		
		parent::$task = $task;
		
/*
		// Script files werden in Klasse Chart eingebunden
		$this->scriptFiles[] = "extLibs/RGraph/libraries/RGraph.common.core.js";
		$this->scriptFiles[] = "extLibs/RGraph/libraries/RGraph.common.dynamic.js";
		$this->scriptFiles[] = "extLibs/RGraph/libraries/RGraph.common.tooltips.js";
		$this->scriptFiles[] = "extLibs/RGraph/libraries/RGraph.common.effects.js";
		$this->scriptFiles[] = "extLibs/RGraph/libraries/RGraph.common.key.js";
		$this->scriptFiles[] = "extLibs/RGraph/libraries/RGraph.bar.js";
		$this->scriptFiles[] = "extLibs/RGraph/libraries/RGraph.pie.js";
		$this->scriptFiles[] = "extLibs/RGraph/libraries/RGraph.line.js";
		$this->scriptFiles[] = "extLibs/RGraph/libraries/RGraph.drawing.rect.js";
		$this->scriptFiles[] = "system/access/js/getBarChart.js";
		$this->scriptFiles[] = "system/access/js/getPieChart.js";
*/
	}
	
	
	public function getTaskContents($ajax = false)
	{

		// Enthält Headerbox
		$this->adminHeader		=	'{s_text:adminstats}' . PHP_EOL . 
									$this->closeTag("#headerBox");
							
		
		// #adminContent
		$this->adminContent 	=	$this->openAdminContent();
	

		// Stats-Objekt
		require_once PROJECT_DOC_ROOT . "/inc/classes/Logging/class.Stats.php";
		$this->o_stats			=	new Stats($this->DB, $this->editLang);

		
		$this->formAction	= ADMIN_HTTP_ROOT . '?task=stats';

		// Notifications
		$this->notice 	= $this->getSessionNotifications("notice");
		$this->hint		= $this->getSessionNotifications("hint");

		
		// Statistik Cleanup
		if(!empty($GLOBALS['_POST']['cleanup_stats'])) {
			if(self::$LOG->cleanupLogTable())
				$this->notice 	= "{s_notice:cleanupstats}";
			else
				$this->error 	= "{s_error:error}";
		}
		
		// Art der Statistik
		if(isset($GLOBALS['_POST']['statview']) && is_numeric($GLOBALS['_POST']['statview'])) {
			$statView = $GLOBALS['_POST']['statview'];
			$this->setSessionVar('statview', $statView);
		}
		elseif(isset($this->g_Session['statview']) && is_numeric($this->g_Session['statview']))
			$statView = $this->g_Session['statview'];
		else
			$statView = 6; // Gesamtstat

		// Sortierung
		if(isset($GLOBALS['_POST']['statorder']) && is_numeric($GLOBALS['_POST']['statorder'])) {
			$statOrder = $GLOBALS['_POST']['statorder'];
			$this->setSessionVar('statorder', $statOrder);
		}
		elseif(isset($this->g_Session['statorder']) && is_numeric($this->g_Session['statorder']))
			$statOrder = $this->g_Session['statorder'];
		else
			$statOrder = 3;
		
		// Sortierungen (Listen)
		if(isset($GLOBALS['_GET']['by']) && isset($GLOBALS['_GET']['sort'])) {
			$this->orderBy	= $GLOBALS['_GET']['by'];
			$this->sort		= $GLOBALS['_GET']['sort'];
			$statOrder		= $this->getSortKey();
		}

		
		// Anzahl an Einträgen pro Seite
		$this->limit = $this->getLimit();

		
		// Statistikzeitraum
		// Jahr
		$this->getStatArchiveYears();
		$this->statYear	= $this->getStatYear();
		$currYear		= date("Y", time());
		$archiveYear	= $currYear != $this->statYear ? $this->statYear : "";
		
		// Falls ein Datum zur Begrenzung mitgegeben wurde, Statistikzeitraum
		// Mindestdatum (Beinn der Zählung bzw. Installation
		$minDate		= max($this->o_stats->getFirstStatDate($archiveYear), mktime(00,00,00,1,1, $this->statYear));
		$maxDate		= $archiveYear == "" ? time() : mktime(23,59,59,12,31, $this->statYear);
		$dateError		= false;
		$removeFilter 	= "";

		$statSince		= date("d.m.Y", $minDate); // Startdatum
		$statUntil		= date("d.m.Y", $maxDate); // Enddatum

		// Falls der Filter für den Datumsbereich entfernt werden soll
		if(isset($GLOBALS['_POST']['removeStatFilter']) && $GLOBALS['_POST']['removeStatFilter'] == 1) {
			// Ggf. Session Keys löschen
			$this->unsetSessionKey('statsince');
			$this->unsetSessionKey('statuntil');
		}
		// Andernfalls auf Datumsbereich überprüfen
		elseif(!isset($GLOBALS['_POST']['statyear'])) {
			
			// Startdatum
			if(isset($GLOBALS['_POST']['statsince']) && $GLOBALS['_POST']['statsince'] != "") {
				$statSince = $GLOBALS['_POST']['statsince'];
			}
			elseif(isset($GLOBALS['_GET']['statsince']) && $GLOBALS['_GET']['statsince'] != "") {
				$statSince = $GLOBALS['_GET']['statsince'];
			}
			elseif(isset($this->g_Session['statsince']) && $this->g_Session['statsince'] != "") {
				$statSince = $this->g_Session['statsince'];
			}
			
			// if given as timestamp
			if((string)(int)$statSince === (string)$statSince)
				$statSince = date("d.m.Y", $statSince);
			
			$this->setSessionVar('statsince', $statSince);
			
			// Enddatum
			if(isset($GLOBALS['_POST']['statuntil']) && $GLOBALS['_POST']['statuntil'] != "") {
				$statUntil = $GLOBALS['_POST']['statuntil'];
			}
			elseif(isset($GLOBALS['_GET']['statuntil']) && $GLOBALS['_GET']['statuntil'] != "") {
				$statUntil = $GLOBALS['_GET']['statuntil'];
			}
			elseif(isset($this->g_Session['statuntil']) && $this->g_Session['statuntil'] != "") {
				$statUntil = $this->g_Session['statuntil'];
			}
			
			// if given as timestamp
			if((string)(int)$statUntil === (string)$statUntil)
				$statUntil = date("d.m.Y", $statUntil);
			
			$this->setSessionVar('statuntil', $statUntil);
		}

		$statSinceTimestamp = Modules::getTimestamp($statSince, 0, 0, 0, "."); // Datum als Timestamp
		$statUntilTimestamp = Modules::getTimestamp($statUntil, 23, 59, 59, "."); // Datum als Timestamp

		// Falls das Enddatum vor dem Startdatum liegt, Fehlermeldung
		if($statUntilTimestamp < $statSinceTimestamp && $statView != 2 && $statView != 3 && $statView != 7) {
			$dateError	= true;
			$this->hint = "{s_notice:datepast}";
		}	
		else {
			// (Re)set archiveYear
			$periodYear		= date("Y", $statUntilTimestamp);
			if(in_array($periodYear, $this->statArchiveYears)) {
				$this->statYear	= $periodYear;
				$this->setSessionVar('statyear', $periodYear);
			}
		}

		
		// Adminarea
		$this->adminContent .=	'<div class="adminArea">' . PHP_EOL;
									
		if(!empty($this->notice))
			$this->adminContent .=	$this->getNotificationStr($this->notice);
		elseif(!empty($this->hint))
			$this->adminContent .=	$this->getNotificationStr($this->hint, "hint");
		if(!empty($this->error))
			$this->adminContent .=	$this->getNotificationStr($this->error, "error");


		// Header			
		$this->adminContent .=	'<h2 class="cc-section-heading cc-h2">{s_header:stats}</h2>' . PHP_EOL . 
								'<div class="controlBar stats">' . PHP_EOL;

		// Statistikauswahl
		$this->adminContent .=	'<div class="actionField">' .
								'<form action="' . $this->formAction . '" method="post">' . PHP_EOL . 
								'<label>{s_label:choosestat}</label>' . 	
								'<select name="statview" id="statview" class="statView" data-action="autosubmit" onchange="if(typeof(RGraph) == \'object\'){ RGraph.ObjectRegistry.Clear(); }">' . PHP_EOL . 
								'<option value="0"' . ($statView == 0 ? ' selected="selected"' : '') . '>{s_option:allstats}</option>' . PHP_EOL . 
								'<option value="6"' . ($statView == 6 ? ' selected="selected"' : '') . '>{s_option:allvisits}</option>' . PHP_EOL . 
								'<option value="7"' . ($statView == 7 ? ' selected="selected"' : '') . '>{s_option:visitsmonth}</option>' . PHP_EOL . 
								'<option value="1"' . ($statView == 1 ? ' selected="selected"' : '') . '>{s_option:pageimp}</option>' . PHP_EOL . 
								'<option value="2"' . ($statView == 2 ? ' selected="selected"' : '') . '>{s_option:currmon}</option>' . PHP_EOL . 
								'<option value="3"' . ($statView == 3 ? ' selected="selected"' : '') . '>{s_option:lastmon}</option>' . PHP_EOL . 
								#'<option value="4"' . ($statView == 4 ? ' selected="selected"' : '') . '>{s_option:clicks}</option>' . PHP_EOL . 
								'<option value="5"' . ($statView == 5 ? ' selected="selected"' : '') . '>{s_option:browser}</option>' . PHP_EOL . 
								'<option value="11"' . ($statView == 11 ? ' selected="selected"' : '') . '>{s_option:searchstats}</option>' . PHP_EOL . 
								'<option value="8"' . ($statView == 8 ? ' selected="selected"' : '') . '>{s_option:download}</option>' . PHP_EOL . 
								'<option value="9"' . ($statView == 9 ? ' selected="selected"' : '') . '>{s_option:referer}</option>' . PHP_EOL . 
								'<option value="10"' . ($statView == 10 ? ' selected="selected"' : '') . '>{s_option:botlogs}</option>' . PHP_EOL . 
								'</select></form>' . PHP_EOL .
								'</div>' . PHP_EOL;

		// Sortierung
		if(	$statView != 5 && 
			$statView != 6 && 
			$statView != 7
		) {
			$this->adminContent .=	'<div class="actionField">' .
									'<form action="' . $this->formAction . '" method="post">' .
									'<label>{s_label:orderby}</label>' . 	
									'<select name="statorder" id="statorder" class="sortFilter" data-action="autosubmit">' . PHP_EOL . 
									'<option value="3"' . ($statOrder == 3 || $statOrder == 5 ? ' selected="selected"' : '') . '>{s_option:valuesasc}</option>' . PHP_EOL . 
									'<option value="4"' . ($statOrder == 4 || $statOrder == 6 ? ' selected="selected"' : '') . '>{s_option:valuesdsc}</option>' . PHP_EOL . 
									'<option value="1"' . ($statOrder == 1 ? ' selected="selected"' : '') . '>{s_option:nameasc}</option>' . PHP_EOL . 
									'<option value="2"' . ($statOrder == 2 ? ' selected="selected"' : '') . '>{s_option:namedsc}</option>' . PHP_EOL;
								
			if(	$statView == 8 || 
				$statView == 9 || 
				$statView == 10 || 
				$statView == 11
			) {
				$this->adminContent .=	'<option value="5"' . ($statOrder == 9 ? ' selected="selected"' : '') . '>{s_option:dateasc}</option>' . PHP_EOL . 
										'<option value="6"' . ($statOrder == 10 ? ' selected="selected"' : '') . '>{s_option:datedsc}</option>' . PHP_EOL;
			}
			
			$this->adminContent .=	'</select></form>' . PHP_EOL .
									'</div>' . PHP_EOL;
		}
		
		
		// Zeitraum (Jahr)
		if(	$statView != 2 && 
			$statView != 3 && 
			$statView != 7 && 
			$statView != 8
		) {
			$this->adminContent .=	'<div class="actionField">' .
									'<form action="' . $this->formAction . '" method="post">' .
									'<label>{s_label:statyear}</label>' . 	
									'<select name="statyear" id="statyear" class="statYear tiny-select" data-action="autosubmit">' . PHP_EOL;
								
			foreach($this->statArchiveYears as $year) {
				$this->adminContent .=	'<option value="' . $year . '"' . ($this->statYear == $year ? ' selected="selected"' : '') . '>' . $year . '</option>' . PHP_EOL;
			}
			
			$this->adminContent .=	'</select></form>' . PHP_EOL .
									'</div>' . PHP_EOL;
			
			// Zeitraum
			if($statView != 2 && $statView != 3 && $statView != 7)
				$this->adminContent .=	'<div class="actionField statDate">' .
									'<form action="' . $this->formAction . '" method="post">' .
									'<div class="dateField">' .
									'<label>{s_label:statsince}</label>' . 	
									'<input type="text" name="statsince" value="' . $statSince . '" class="datepicker statPeriod" maxlength="10" />' . PHP_EOL . 
									'</div>' . PHP_EOL .
									'<div class="dateField">' .
									'<label>{s_label:statuntil}</label>' . 	
									'<input type="text" name="statuntil" value="' . $statUntil . '" class="datepicker statPeriod' . ($dateError	? ' invalid' : '') . '" maxlength="10" />' . PHP_EOL . 
									'</div>' . PHP_EOL .
									'<input type="hidden" id="daynames" value="{s_date:daynames}" alt="{s_date:daynamesmin}" />' . PHP_EOL .
									'<input type="hidden" id="monthnames" value="{s_date:monthnames}" alt="{s_date:monthnamesmin}" />' . PHP_EOL .
									'<input type="hidden" id="mindate" value="' . date("d.m.Y", $minDate) . '" />' . PHP_EOL .
									'<input type="hidden" id="maxdate" value="' . date("d.m.Y", $maxDate) . '" />' . PHP_EOL .
									'<input type="hidden" id="currentText" value="{s_date:today}" />' . PHP_EOL .
									'<input type="hidden" id="closeText" value="{s_label:done}" />' . PHP_EOL .
									'</form>' . PHP_EOL .
									'</div>' . PHP_EOL;
		}

		// Button tag
		$buttonO		= '<button type="submit" role="submit" name="statview" onclick="if(typeof(RGraph) == \'object\'){RGraph.ObjectRegistry.Clear();} var val = $(this).val(); var sbm = \'<input type=&quot;hidden&quot; name=&quot;statview&quot; value=&quot;\' + val +\'&quot; />\'; $(this).append(sbm);" class="button statview';
		$buttonC		= '></span></button>';
		$iconClass		= ' icon-statview background-icon';
		
		// Statistikauswahl (Icons)
		$this->adminContent .=	'<div class="actionField last left">'. PHP_EOL .
								'<form action="' . $this->formAction . '" method="post">' . PHP_EOL .
								$buttonO . ($statView == 0 ? ' button-active' : '') . '" title="{s_option:allstats}" value="0"><span class="icon-statview-all' . $iconClass . '"' . $buttonC . PHP_EOL . 
								$buttonO . ($statView == 6 ? ' button-active' : '') . '" title="{s_option:allvisits}" value="6"><span class="icon-statview-visitsall' . $iconClass . '"' . $buttonC . PHP_EOL . 
								$buttonO . ($statView == 7 ? ' button-active' : '') . '" title="{s_option:visitsmonth}" value="7"><span class="icon-statview-visits' . $iconClass . '"' . $buttonC . PHP_EOL . 
								$buttonO . ($statView == 1 ? ' button-active' : '') . '" title="{s_option:pageimp}" value="1"><span class="icon-statview-pageimp' . $iconClass . '"' . $buttonC . PHP_EOL . 
								$buttonO . ($statView == 2 ? ' button-active' : '') . '" title="{s_option:currmon}" value="2"><span class="icon-statview-currmon' . $iconClass . '"' . $buttonC . PHP_EOL . 
								$buttonO . ($statView == 3 ? ' button-active' : '') . '" title="{s_option:lastmon}" value="3"><span class="icon-statview-lastmon' . $iconClass . '"' . $buttonC . PHP_EOL . 
								#$buttonO . ($statView == 4 ? ' button-active' : '') . '" title="{s_option:clicks}" value="4"><span class="icon-statview-clicks' . $iconClass . '"' . $buttonC . PHP_EOL . 
								$buttonO . ($statView == 5 ? ' button-active' : '') . '" title="{s_option:browser}" value="5"><span class="icon-statview-browser' . $iconClass . '"' . $buttonC . PHP_EOL . 
								$buttonO . ($statView == 11 ? ' button-active' : '') . '" title="{s_option:searchstats}" value="11"><span class="icon-statview-search' . $iconClass . '"' . $buttonC . PHP_EOL . 
								$buttonO . ($statView == 8 ? ' button-active' : '') . '" title="{s_option:download}" value="8"><span class="icon-statview-download' . $iconClass . '"' . $buttonC . PHP_EOL . 
								$buttonO . ($statView == 9 ? ' button-active' : '') . '" title="{s_option:referer}" value="9"><span class="icon-statview-referer' . $iconClass . '"' . $buttonC . PHP_EOL . 
								$buttonO . ($statView == 10 ? ' button-active' : '') . '" title="{s_option:botlogs}" value="10"><span class="icon-statview-bots' . $iconClass . '"' . $buttonC . PHP_EOL . 
								'</form>' . PHP_EOL .
								'</div>' . PHP_EOL;


		$this->adminContent .=	'<p class="clearfloat">&nbsp;</p>' . PHP_EOL .
								'</div>' . PHP_EOL;


		// Statistik-Zeitraum
		$headerPeriod		= parent::getIcon("calendar", "listIcon inline-icon");
		$headerClass		= "";
		
		// Falls Gesamtzeitraum
		if($statSince == date("d.m.Y", $minDate) && $statUntil == date("d.m.Y", $maxDate)) {
			$headerPeriod .= '<span class="inlineParagraph">{s_text:statall} &nbsp;(' . $statSince . '&nbsp;&#9658;&nbsp;' . $statUntil . ')</span>';
		}
		// Andernfalls begrenzter Zeitraum
		else {
			
			$headerPeriod .= '<span class="inlineParagraph">{s_text:statperiod} <strong>' . $statSince . '&nbsp;&#9658;&nbsp;' . $statUntil . '</strong></span>';
			$headerClass		= " cc-hint";
			
			$removeFilter .=	'<form action="' . $this->formAction . '" method="post">' . PHP_EOL;
			$removeFilter .=	'<span class="editButtons-panel">' . PHP_EOL;
		
			// Button remove filter
			$btnDefs	= array(	"type"		=> "submit",
									"class"		=> 'removefilter ajaxSubmit button-icon-only',
									"text"		=> "",
									"title"		=> '{s_title:removefilter}',
									"icon"		=> "close"
								);
			
			$removeFilter .=	parent::getButton($btnDefs);
			
			$removeFilter .=	'<input type="hidden" name="removeStatFilter" value="1" />' . PHP_EOL .
								'</span>' . PHP_EOL .
								'</form>' . PHP_EOL;
		}


		// Falls nicht zeitspezifische Statistiken
		if($statView != 2 && $statView != 3 && $statView != 7)
			$this->adminContent .=	'<span class="showHiddenListEntries actionBox' . $headerClass . '">' .
									$headerPeriod .
									$removeFilter .
									'</span>' . PHP_EOL;

		// Falls Logging ausgeschaltet, zur Settingsseite verlinken
		if(!CONCISE_LOG)
			$this->adminContent .=	'<p class="error">{s_notice:loggingoff}<a href="admin?task=settings#setweb" class="link"><strong>{s_header:setpage}</strong></a>.</p>' . PHP_EOL;

		$this->adminContent .=	'<p class="stat-loading-hint notice hint">{s_notice:loadstats} <img src="' . SYSTEM_IMAGE_DIR . '/page-spinner.svg" class="loading" /></p>' . PHP_EOL;
		$this->adminContent .=	'<div class="cc-admin-panel-box">' . PHP_EOL;


		$this->adminContent .=	($statView == 0 || $statView == 6 ? $this->getStatPanel("{s_option:allvisits}",
								$this->o_stats->getStats(true, false, $statSinceTimestamp, $statUntilTimestamp)) : '') .
								($statView == 0 || $statView == 6 ? $this->getStatPanel("{s_option:visits}",
								$this->o_stats->showClickCountChart(1, $statOrder, "visits_period", $statSinceTimestamp, $statUntilTimestamp, false)) : '') .
								#($statView == 0 || $statView == 6 ? $this->getStatPanel("{s_option:allvisits}",
								#$this->o_stats->showClickCountChart(6, $statOrder, "overview")) : '') .
								($statView == 0 || $statView == 7 ? $this->getStatPanel("{s_option:visitsmonth}",
								$this->o_stats->showClickCountChart(7, $statOrder, "visits")) : '') .
								($statView == 0 || $statView == 1 ? $this->getStatPanel("{s_option:pageimp}",
								$this->o_stats->showClickCountChart(1, $statOrder, "pageimp_all", $statSinceTimestamp, $statUntilTimestamp)) : '') .
								($statView == 0 || $statView == 2 ? $this->getStatPanel("{s_option:currmon}",
								$this->o_stats->showClickCountChart(2, $statOrder, "pageimp_curmon")) : '') .
								($statView == 0 || $statView == 3 ? $this->getStatPanel("{s_option:lastmon}",
								$this->o_stats->showClickCountChart(3, $statOrder, "pageimp_lastmon")) : '') .
								#($statView == 0 || $statView == 4 ? $this->getStatPanel("{s_option:clicks}",
								#$this->o_stats->showClickCountChart(4, $statOrder, "pageimp_all", $statSinceTimestamp, $statUntilTimestamp)) : '') .
								($statView == 0 || $statView == 5 ? $this->getStatPanel("{s_option:browser}",
								$this->o_stats->showUsedBrowserChart(5, $statSinceTimestamp, $statUntilTimestamp)) : '') . 
								($statView == 0 || $statView == 11 ? $this->getStatPanel("{s_option:searchstats}",
								$this->o_stats->getSearchStats($statOrder, $statSinceTimestamp, $statUntilTimestamp)) : '') . 
								($statView == 0 || $statView == 8 ? $this->getStatPanel("{s_option:download}",
								$this->o_stats->getDownloadStats(8, $statOrder, $statSinceTimestamp, $statUntilTimestamp)) : '') . 
								($statView == 0 || $statView == 9 ? $this->getStatPanel("{s_option:referer} - {s_text:external}",
								$this->o_stats->getRefererStats("external", $statOrder, $statSinceTimestamp, $statUntilTimestamp, 0, $this->limit)) : '') . 
								($statView == 0 || $statView == 9 ? $this->getStatPanel("{s_option:referer} - {s_text:internal}",
								$this->o_stats->getRefererStats("internal", $statOrder, $statSinceTimestamp, $statUntilTimestamp, 0, $this->limit)) : '') . 
								($statView == 0 || $statView == 10 ? $this->getStatPanel("{s_option:botlogs}",
								$this->o_stats->getBotLogs(10, $statOrder, $statSinceTimestamp, $statUntilTimestamp, 0, $this->limit)) : '');
		
		$this->adminContent .=	'</div>' . PHP_EOL;

							
		$this->adminContent .=	'<br class="clearfloat" />' . PHP_EOL;
		$this->adminContent .=	'</div>' . PHP_EOL;
		$this->adminContent .=	'<div class="adminArea">' . PHP_EOL . 
								'<ul>' . PHP_EOL .
								'<li class="submit back">' . PHP_EOL;
		
		// Button back
		$this->adminContent .=	$this->getButtonLinkBacktomain();
							
		$this->adminContent .=	'<br class="clearfloat" />' . PHP_EOL .
								'</li>' . PHP_EOL . 
								'</ul>' . PHP_EOL . 
								'</div>' . PHP_EOL;


		$this->adminContent .=	$this->getStatsScript();

		// #adminContent close
		$this->adminContent	.= $this->closeAdminContent();
		
		
		// Panel for rightbar
		$this->adminRightBarContents[]	= $this->getStatsRightBarContents();

		
		return $this->adminContent;

	}

	
	// getStatPanel
	private function getStatPanel($header, $content)
	{
	
		$output	=	'<div class="cc-admin-panel">' . PHP_EOL .
					'<h3 class="cc-h3 toggleNext active">' . $header . '</h3>' . PHP_EOL .
					$content .
					'</div>' . PHP_EOL;
		
		return $output;
		
	}

	
	// getStatsRightBarContents
	private function getStatsRightBarContents()
	{
	
		// Panel for rightbar
		$output	= "";
		
		// Back to list
		$output .=	'<div class="controlBar">' . PHP_EOL;
		$output .=	'<form action="' . $this->formAction . '" method="post" data-history="false">' . PHP_EOL;
		
		// Button backtolist
		$btnDefs	= array(	"type"		=> 'submit',
								"name"		=> 'cleanup_stats',
								"class"		=> '{t_class:btnpri} {t_class:btnblock}',
								"text"		=> '{s_button:cleanupstats}',
								"value"		=> 'true',
								"title"		=> '{s_title:cleanupstats}',
								"icon"		=> "clean"
							);
			
		$output .=	parent::getButton($btnDefs) . PHP_EOL;
	
		$output .=	'<input type="hidden" name="cleanup_stats" value="true" />' . PHP_EOL;
		$output .=	'</form>' . PHP_EOL;
		$output .=	'</div>' . PHP_EOL;
		
		// Back to list
		$output .=	'<div class="controlBar">' . PHP_EOL;
		
		
		if(CONCISE_LOG) {
			// Button set cookie
			$btnDefs	= array(	"class"		=> "setCCLogCookie",
									"value"		=> "{s_label:setsetcookie}",
									"title"		=> "{s_label:setexcludelog}",
									"icon"		=> "previewno",
									"attr"		=> 'onclick=\'if($.cookie("conciseLogging_off", "true", ' . (time()+60*60*24*365*10) . ', "/")){ $(this).next(".check").addClass("on cc-icon-ok").removeClass("off cc-icon-blocked"); jAlert(ln.settingsnolog, ln.alerttitle); }\''
								);
			
			$output	.=	parent::getButton($btnDefs);
			
			$output	.=	parent::getIcon(isset($GLOBALS['_COOKIE']['conciseLogging_off']) ? 'ok' : 'cancel', 'check ' . (isset($GLOBALS['_COOKIE']['conciseLogging_off']) ? 'on' : 'off'));
			$output .=	'</div>' . PHP_EOL;
		}
		
		return $output;
		
	}

	
	// getStatYear
	public function getStatYear()
	{
	
		// Falls anderes als aktuelles Jahr
		if(	isset($GLOBALS['_POST']['statyear']) && 
			is_numeric($GLOBALS['_POST']['statyear']) && 
			in_array($GLOBALS['_POST']['statyear'], $this->statArchiveYears)
		) {
			$year	= $GLOBALS['_POST']['statyear'];
			$this->setSessionVar('statyear', $year);
		}
		elseif(isset($this->g_Session['statyear']) && 
			is_numeric($this->g_Session['statyear']) && 
			in_array($this->g_Session['statyear'], $this->statArchiveYears)
		)
			$year	= $this->g_Session['statyear'];
		else
			$year	= date("Y", time()); // aktuelles Jahr
		
		return $year;
		
	}

	
	// getStatArchiveYears
	public function getStatArchiveYears()
	{
	
		$year	= date("Y", time()); // aktuelles Jahr
		
		$this->statArchiveYears[]	= $year;
		
		// Archivjahre auslesen
		for($y = $year-1; $y >= 2010; $y--) {

			$sql	= "SHOW TABLES LIKE '" . DB_TABLE_PREFIX . "log_".$y."'";
			$result	= $this->DB->query($sql);
			
			if(count($result) > 0)
				$this->statArchiveYears[]	= $y;
		}
		
		return $this->statArchiveYears;
	
	}
	
	
	// getSortKey
	public function getSortKey()
	{
	
		if($this->orderBy == "name")
			return $this->sort == "asc" ? 1 : 2;
		if($this->orderBy == "count")
			return $this->sort == "asc" ? 3 : 4;
		if($this->orderBy == "size" || $this->orderBy == "referer")
			return $this->sort == "asc" ? 5 : 6;
		if($this->orderBy == "ip")
			return $this->sort == "asc" ? 7 : 8;
		if($this->orderBy == "date")
			return $this->sort == "asc" ? 9 : 10;
		return 1;
	}

	
	// getStatsScript
	protected function getStatsScript()
	{
	
		return	'<script>head.ready(function(){' . PHP_EOL .
				'$(document).ready(function(){' . PHP_EOL .
					'(function($){
						$.watchStatLoading = function() {						
						// Loading-Platzhalterbilder bei Bildern (z.B. Statistiken, Upload)
						// Falls IE Statistikbilder direkt anzeigen, da Fehler bei "load" (fired nur max. 1x, wenn überhaupt)
						if ($("body").hasClass("IE")) {
							$("img.stats").ready(function(){	
								$("img.stats:hidden").attr("style","display:block;");
								$("img.loading").remove();
								$(".stat-loading-hint").remove();
							});
						}else{
							$("img.stats").waitForImages(function(){
								$(this).attr("style","display:block;");
								$(this).prev("img.loading").remove();
								if(!($("div.stats > img").not("img[class=\'stats\']").length)) {
									$(".stat-loading-hint").remove();
								}
							});
						}
						// Falls gar kein Bild in Statistik vorkommt
						if(!($("div.stats > img").length)) {

							$(".stat-loading-hint").remove();
						}
					};
					})(jQuery);' . PHP_EOL .

					'$.watchStatLoading();' . PHP_EOL .
				
					'head.load(	{jspdf: "' . PROJECT_HTTP_ROOT . '/extLibs/jsPDF/jspdf.min.js"},
								{statstopdf: "' . SYSTEM_HTTP_ROOT . '/inc/admintasks/stats/js/statstopdf.js"}' . PHP_EOL .
					');' . PHP_EOL .
					'head.ready("jspdf", function(){' . PHP_EOL .
						'head.ready("statstopdf", function(){' . PHP_EOL .
							'cc.statsToPDF();' . PHP_EOL .
						'});' . PHP_EOL .
					'});' . PHP_EOL .
				'});' . PHP_EOL .
				'});' . PHP_EOL .
				'</script>' . PHP_EOL;
	
	}

}
