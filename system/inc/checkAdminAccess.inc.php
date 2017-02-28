<?php
namespace Concise;



// Falls geloggter Admin, Adminklasse einbinden
if($o_security->get('adminLog') === true) {
	require_once(PROJECT_DOC_ROOT . "/inc/classes/Admin/class.Admin.php");
}
else {
	// Andernfalls zur Fehlerseite gehen
	header("Location:" . PROJECT_HTTP_ROOT . "/error" . PAGE_EXT) . exit;
}
