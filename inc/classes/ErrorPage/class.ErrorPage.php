<?php
namespace Concise;


require_once PROJECT_DOC_ROOT."/inc/classes/Contents/class.ContentsEngine.php"; // ContentsEngine einbinden

/**
 * Datenbankinhalte
 * 
 */

class ErrorPage extends ContentsEngine
{

	/**
	 * Fehler Code
	 *
	 * @access private
     * @var    int error code
     */
	private $sc				= 404;

	/**
	 * Fehler Meldungen
	 *
	 * @access public
     * @var    string
     */
	public $errorHeader		= "";
	public $noAccessHeader	= "";
	public $serverHeader	= "";
	public $errorSubHeader	= "";
	public $errorMes		= "";
	public $errorForbidden	= "";
	public $errorServer		= "";
	public $errorStatus		= "";
	public $errorNotfound	= "";
	public $errorAccess		= "";
	public $errorNoLogin	= "";
	public $errorTimeout	= "";
	public $errorNoFeed		= "";
	
	/**
	 * Konstruktor ErrorPage
	 * 
	 * @param	int error code
	 * @access	public
	 */
	public function __construct($sc = 404)
	{
	
		$this->sc				= $sc;
		$this->errorHeader		= "{s_header:error}";
		$this->noAccessHeader	= "{s_header:noaccess}";
		$this->serverHeader		= "{s_header:servererror}";
		$this->errorNotfound	= "{s_header:notfound}";
		$this->errorMes			= "{s_text:error}";
		$this->errorForbidden	= "{s_text:errorforbidden}";
		$this->errorServer		= "{s_text:errorserver}";
		$this->errorStatus		= "{s_text:errorstatus}";
		$this->errorAccess		= "{s_text:erroraccess}";
		$this->errorNoLogin		= "{s_text:erroraccess2}";
		$this->errorTimeout		= "{s_notice:errortimeout}";
		$this->errorNoFeed		= "{s_notice:nofeed}";
	
	}
	

	/**
	 * Erstellt eine Fehlerseite
	 * 
	 * @param	string group
	 * @access	public
     * @return  string
	 */
	public function getErrorPage($group)
	{
		
		// Def sub header
		$this->errorSubHeader	= $this->errorNotfound;
		
		// Serverumleitung
		if(isset($GLOBALS['_GET']['sc'])) {
			$this->sc			= $GLOBALS['_GET']['sc'];
			if($this->sc == 401 || $this->sc == 403) { $this->errorSubHeader = $this->noAccessHeader; $this->errorMes = $this->errorForbidden; }
			if($this->sc == 408 || $this->sc == 500) { $this->errorSubHeader = $this->serverHeader; $this->errorMes = $this->errorServer; }
		}
		// Fehlende Zugriffsberechtigung
		if(isset($GLOBALS['_GET']['status'])
		&& $GLOBALS['_GET']['status'] == 0
		) {
			$this->errorSubHeader	= $this->noAccessHeader;
			$this->errorMes			= $this->errorStatus;
		}
		// Fehlende Zugriffsberechtigung (eingeloggt)
		if(isset($GLOBALS['_GET']['access'])
		&& $GLOBALS['_GET']['access'] == 0
		&& (!empty($group) || $group != "public")
		) {
			$this->sc				= 403;
			$this->errorSubHeader	= $this->noAccessHeader;
			$this->errorMes			= $this->errorAccess;
		}
		// Fehlende Zugriffsberechtigung (ohne login)
		if(isset($GLOBALS['_GET']['access'])
		&& $GLOBALS['_GET']['access'] == 0
		&& (empty($group) || $group == "public")
		) {
			$this->sc				= 403;
			$this->errorSubHeader	= $this->noAccessHeader;
			$this->errorMes			= $this->errorNoLogin;
			$this->errorMes		   .= '<br /><br /><a href="' . PROJECT_HTTP_ROOT . '/' . HTML::getLinkPath(-1002) . '" class="cc-button button-login icon-login formbutton {t_class:btn} {t_class:btnpri}">{s_link:login}</a>' . (REGISTRATION_TYPE == "account" ? '<a href="' . PROJECT_HTTP_ROOT . '/' . HTML::getLinkPath(-1006) . '" class="cc-button button-register icon-user button {t_class:btn} {t_class:btnsec}">{s_link:register}</a>' : '');
		}
		// Session-Timeout
		if(isset($GLOBALS['_GET']['timeout']) && $GLOBALS['_GET']['timeout'] == 1) {
			$this->sc				= 408;
			$this->errorSubHeader	= $this->noAccessHeader;
			$this->errorMes			= $this->errorTimeout;
			$this->errorMes		   .= '<br /><br /><a href="' . PROJECT_HTTP_ROOT . '/' . HTML::getLinkPath(-1002) . '" class="cc-button button-login icon-login formbutton {t_class:btn} {t_class:btnpri}">{s_link:login}</a>';
		}
		// Kein Newsfeed
		if(isset($GLOBALS['_GET']['feed']) && $GLOBALS['_GET']['feed'] == 0) { 
			$this->errorMes			= $this->errorNoFeed;
			$this->errorMes		   .= '<br /><br /><a href="' . PROJECT_HTTP_ROOT . '/' . HTML::getLinkPath(-1002) . '" class="cc-button button-login icon-login formbutton {t_class:btn} {t_class:btnpri}">{s_link:login}</a>';
		}
		
		$this->errorSubHeader		= '<h2>' . $this->errorSubHeader . '</h2>' . PHP_EOL;
		
		if(strpos($this->errorMes, "<p") === false)
			$this->errorMes			= '<p>' . $this->errorMes . '</p>' . PHP_EOL;
		
		$systemContent				= '<p class="cc-statuscode">' . $this->sc . '</p>' . PHP_EOL .
									  '<h1>' . $this->errorHeader . '</h1>' . PHP_EOL .
									  $this->errorSubHeader .
									  $this->errorMes;

		$this->setStatusCodeHeader($this->sc);
		
		return $systemContent;
	
	}
	

	/**
	 * Erstellt einen Fehlerseiten Status code header
	 * 
	 * @access	public
     * @return  string
	 */
	public function setStatusCodeHeader($sc)
	{

		if(!is_numeric($sc))
			return false;
		
		// Statuscode Header setzen
		switch($sc) {
		
			case 301:
				header("HTTP/1.1 301 Moved Permanently");
				break;
			case 401:
				header("HTTP/1.1 401 Unauthorized");
				break;
			case 403:
				header("HTTP/1.1 403 Forbidden");
				break;
			case 408:
				header("HTTP/1.1 403 Request Timeout");
				break;
			case 500:
				header("HTTP/1.1 500 Internal Server Error");
				break;
			default:
				header("HTTP/1.1 404 Not Found");
				break;
		}
		
		return true;

	}

}
