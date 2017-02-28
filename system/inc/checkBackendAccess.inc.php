<?php
namespace Concise;



// Falls geloggter Admin/Editor/Author, Adminklasse einbinden
if($o_security->get('backendLog')) {
	require_once(PROJECT_DOC_ROOT . "/inc/classes/Admin/class.Admin.php");
}
else {
	// Andernfalls zur Fehlerseite gehen
	header("Location:" . PROJECT_HTTP_ROOT . "/error" . PAGE_EXT) . exit;
}
