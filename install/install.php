<?php
namespace Concise;


if(!isset($_SESSION))
	session_start();

$DB	= null;

require_once(dirname(__FILE__)."/../inc/common.php"); // Settings einbinden
require_once(PROJECT_DOC_ROOT."/install/install.class.php"); // Install-Klasse einbinden

			
// Ggf. automatische Magic Quotes entfernen
Security::globalStripSlashes();

$o_security	= Security::getInstance(); // Security Objektinstanz

// Token auslesen/erstellen
$tokenOK	= $o_security->checkToken();
$token		= $o_security->getToken();


// Sprache festlegen
$o_lng->installedLangs	= array(); // vorhandene Sprachen für Seiten
$o_lng->existFlag		= array(); // vorhandene Sprachenflaggen für Seiten
$o_lng->existNation		= array(); // vorhandene Sprache (Nationalität) für Seiten

foreach($adminLangs as $key => $value) {
	
	$o_lng->installedLangs[]	= $key; // vorhandene Sprachen für Seiten
	$o_lng->existFlag[]			= "flag_".$key.".png"; // vorhandene Sprachenflaggen für Seiten
	$o_lng->existNation[] 		= $value; // vorhandene Sprache (Nationalität) für Seiten
}

if(isset($_GET['lang']) && in_array($_GET['lang'], $o_lng->installedLangs))
	$o_lng->adminLang = $_GET['lang'];	
elseif(isset($_SESSION['lang']) && in_array($_SESSION['lang'], $o_lng->installedLangs))
	$o_lng->adminLang = $_SESSION['lang'];
else
	$o_lng->adminLang = DEF_ADMIN_LANG;

$_SESSION['lang']			= $o_lng->adminLang;
$_SESSION['admin_lang'] 	= $o_lng->adminLang;

$lang						= $o_lng->getLang();

$adminPage					= true;		// AdminPage

// Datenbankinhalte zur aktuellen Seite (Main)
$contents_main				= new ContentsEngine(null, $o_lng);
$contents_main->pageName	= "Concise WMS Installation";
$contents_main->lang		= $lang;
$contents_main->initPage("install");


// Template
$incTemplates["CONTENTS"]	= "install.tpl";

$tpl_main = new Template(CC_MAIN_TEMPLATE, $incTemplates);
$tpl_main->loadTemplate($adminPage);


// Installation
$o_inst	= new Install($o_lng);
$installForm = $o_inst->getInstall($contents_main::$token);


$tpl_main->assign("root", PROJECT_HTTP_ROOT);
$tpl_main->assign("admin_theme", ADMIN_THEME);
$tpl_main->assign("MAIN", $installForm);
$tpl_main->assign("cwms_version", CWMS_VERSION);


// Das fertige Template in Variable speichern
$bodyContent = $tpl_main->getTemplate(true);

$o_html	= new HTML($contents_main);

// Zusammenfassen von CSS-Dateien abstellen
$o_html->combineCSS = false;

//HTML-Objekt erstellen
$o_html->printHead($lang);
$o_html->printBody("page-install", "admin install");

// Template ausgeben
echo $bodyContent;

//Inhalt der Seite:
$o_html->printFoot();

exit;
die();

?>