<?php
// Potentiellen Bot in DB eintragen

// checkSiteAccess
require_once "../inc/checkSiteAccess.inc.php";

// common.php einbinden
require_once "../inc/common.php";

$log_o = new Concise\Log($DB, $_SESSION);
$log_o->logPotentialBot(Concise\User::getRealIP(), $log_o->userAgent, $log_o->referer, time());

header("HTTP/1.1 403 Forbidden");

echo "Potential bot identified.";
exit;
?>