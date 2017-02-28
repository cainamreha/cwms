<?php
###### ist in common.php integriert ######

// checkSiteAccess
require_once "../inc/checkSiteAccess.inc.php";
require_once "../inc/common.php";
require_once PROJECT_DOC_ROOT . "/inc/classes/Security/class.Security.php";
require_once PROJECT_DOC_ROOT . "/inc/classes/User/class.Login.php";

// Falls auf logout geklickt wurde
if(isset($_GET['logout']) && $_GET['logout'] == "true")
	require_once PROJECT_DOC_ROOT . "/inc/logout.inc.php";

session_write_close();

// Loginstatus überprüfen
// Falls Session regenerate via ajax
if($o_security->get('loginStatus')
&& isset($GLOBALS['_GET']['session'])
&& $GLOBALS['_GET']['session'] == 0
) {
	$token	= $o_security->getToken();
	$o_security->setToken($token);
	echo "$token";
}
else
	echo "0";

exit;
