<?php
header('HTTP/1.1 301 Moved Permanently');
header("Location: ../install.html"); 
header("Connection: close");
exit;