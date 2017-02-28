<?php
namespace Concise;


// Projektpfade und Syste-Einstellungen
require_once __DIR__."/settings.php";

// Header defaults
header("Content-Type: text/html; charset=utf-8");
header("Cache-Control: max-age=0,no-cache,no-store,post-check=0,pre-check=0");
header("Pragma: no-cache");
header_remove('x-powered-by');

// Alle Basis-Klassen einbinden
require_once PROJECT_DOC_ROOT."/inc/includeClasses.inc.php";


// Ggf. automatische Magic Quotes entfernen
Security::globalStripSlashes();


$isInstallPage	= isset($_GET['page']) && $_GET['page'] == "_install";


// Datenbankobjekt erstellen (wenn nicht bereits erstellt)
if(empty($DB) && !$isInstallPage)
	$DB = new MySQL(DB_SERVER,DB_USER,DB_PASSWORD,DB_NAME,DB_PORT);


// Falls nicht ein Installtionsseite oder ein Cronjob ausgeführt wird, Session aktivieren
if (!$isInstallPage
&& (!isset($cronTab) || $cronTab == false)
) {

	// global verfügbares Session-Objekt.
	new ConciseSessionHandler($DB);

	// Testcookie setzen, um für den Formulargebrauch zu überprüfen, ob coockies akzeptiert werden
	@setcookie("cookies_on", "cookies_on", time()+(abs((int)(SESSION_UPTIME))*60), "/");
}


// Loginstatus überprüfen
$o_security	= Security::getInstance(); // Security Objektinstanz
$o_security->getSessionVars(); // Session vars
$o_security->checkLogout($DB); // ggf. Logout
$o_security->checkLoginStatus($DB); // Loginstatus
$o_security->checkLiveMode(); // Website LiveMode


	// Sprachobjekt
$o_lng			= new Language($DB, $adminLangs);
$lang			= $o_lng->getLang();
