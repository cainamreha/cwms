<?php
namespace Concise;

// checkSiteAccess
require_once "./inc/checkSiteAccess.inc.php";

// Falls die Installationsseite aufgerufen wird
if(checkInstallPage()) {
	require_once(__DIR__."/../install/install.php"); // Installationsdatei einbinden
	exit;
}


// common.php einbinden
require_once "./inc/common.php";


// Index Klasse
class Index
{

	public static function buildIndex($DB, $o_lng)
	{
	
		// Security-Objekt
		$o_security	= Security::getInstance();

		// Falls Adminseite
		if($o_security->get('adminPage')) {

			require_once PROJECT_DOC_ROOT."/inc/classes/Admin/class.AdminPage.php"; // Adminklasse einbinden
			$o_admin		= new AdminPage($DB, $o_lng);
			$contents		= $o_admin->initAdminPage(); // Adminseite ausgeben
			return true;
		}

		// Andernfalls Frontend
		//
		// Falls ein Admin oder Editor eingeloggt ist, -> FE-Status
		if($o_security->get('editorLog')) {
			
			require_once PROJECT_DOC_ROOT."/inc/classes/Contents/class.ContentsEdit.php"; // FE-Klasse einbinden
			$o_contents		= new ContentsEdit($DB, $o_lng); // Inhalte der aktuellen Seite (Edit-Modus)
			$contents		= $o_contents->getContents();
			return true;
		}

		// Falls kein Adminlog und kein Backend, -> Frontend-Inhalte
		require_once PROJECT_DOC_ROOT."/inc/classes/Contents/class.Contents.php"; // Contents-Klasse einbinden
		$o_contents			= new Contents($DB, $o_lng); // Datenbankinhalte zur aktuellen Seite (live)
		$contents			= $o_contents->getContents();
		return true;

	}
}
// Index-Seite aufbauen
Index::buildIndex($DB, $o_lng);


// Aufruf der Installationsseite prüfen und ggf. diese ausgeben
function checkInstallPage()
{

	if(isset($_GET['page']) 
	&& $_GET['page'] == "_install"
	) {
		
		if(is_file(__DIR__."/../install/install.php"))
			return true;
		else {
			header("Location: /error.html?sc=403");
			exit;
		}
	}
	return false;
}
