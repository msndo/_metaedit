<?php
if((! isset($_SERVER['HTTP_X_REQUESTED_WITH'])) || (strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest')) {
	// error_log('[' . $_SERVER['SCRIPT_NAME'] . ']: ' . 'Non-Ajax Access. Operation Stopeed');
	exit(1);
}

include('home/app/control-list-dir.php');
?>
