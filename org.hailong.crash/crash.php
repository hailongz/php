<?php

/**
 * Crash
 */

if($library){
	
	define("DB_CRASH","crash");
	
	require_once "$library/org.hailong.configs/error_code.php";
	require_once "$library/org.hailong.service/service.php";
	
	require_once "$library/org.hailong.crash/db/DBCrash.php";
	
	require_once "$library/org.hailong.crash/tasks/CrashService.php";
	
	
}
?>