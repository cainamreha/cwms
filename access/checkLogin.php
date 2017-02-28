<?php
// checkSiteAccess
require_once "../inc/checkSiteAccess.inc.php";
require_once "../inc/common.php";
require_once "../inc/classes/User/class.Login.php";

// Login-Daten checken
$o_Login	= new Concise\Login($DB, $o_lng, null);
$userLogin	= $o_Login->checkLoginData();

session_write_close();

// Seitenaufruf je nach Nutzer
$o_Login->redirectUser($userLogin);

exit;
?>