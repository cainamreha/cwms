<?php

/**
 * Die Basisklassen werden eingefügt!
 *
 */
 
//Fehlerbehandlungsklasse
require_once PROJECT_DOC_ROOT."/inc/classes/ErrorHandling/class.ErrorHandling.php";

//"Debug-Logging"-Klasse
if(DEBUG === true)
	require_once PROJECT_DOC_ROOT."/inc/classes/Debugging/class.Logging.php";

//Sicherheitsklasse
require_once PROJECT_DOC_ROOT."/inc/classes/Security/class.Security.php";

//Datenbankklasse
require_once PROJECT_DOC_ROOT."/inc/classes/DB/class.MySQL.php";

//Sitzungsklasse
// Falls nicht ein Installtionsseite oder Cronjob
if((!isset($_GET['page']) || $_GET['page'] != "_install")
&& (!isset($cronTab) || $cronTab == false)
)
	require_once PROJECT_DOC_ROOT."/inc/classes/Session/class.SessionHandler.php"; // Datenbankanbindung erforderlich

//ContentEngine-Klasse
require_once PROJECT_DOC_ROOT."/inc/classes/Contents/class.ContentsEngine.php";

//EventDispatcher-Klassen
require_once PROJECT_DOC_ROOT."/vendor/Symfony/Component/EventDispatcher/EventDispatcherInterface.php";
require_once PROJECT_DOC_ROOT."/vendor/Symfony/Component/EventDispatcher/Event.php";
require_once PROJECT_DOC_ROOT."/vendor/Symfony/Component/EventDispatcher/EventDispatcher.php";

//Sprachenklasse
require_once PROJECT_DOC_ROOT."/inc/classes/Language/class.Language.php";

//Modulesklasse
require_once PROJECT_DOC_ROOT."/inc/classes/Modules/class.Modules.php";

//Template-Klasse
require_once PROJECT_DOC_ROOT."/inc/classes/Template/class.Template.php";

//Logklasse
require_once PROJECT_DOC_ROOT."/inc/classes/Logging/class.Log.php";

//Userklasse
require_once PROJECT_DOC_ROOT."/inc/classes/User/class.User.php";

//Loginklasse
require_once PROJECT_DOC_ROOT."/inc/classes/User/class.Login.php";

//Search-Klasse (extends Modules)
if(SEARCH_TYPE != "none")
	require_once PROJECT_DOC_ROOT."/inc/classes/Modules/class.Search.php";

//HTML-Klasse
require_once PROJECT_DOC_ROOT."/inc/classes/HTML/class.HTML.php";
