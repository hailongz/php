<?php
$library = "..";

require_once "$library/org.hailong.service/service.php";
require_once "$library/org.hailong.ui/ui.php";
require_once "$library/org.hailong.account/account.php";

require_once "configs/config.php";

require_once "log.php";

session_start();
	

Shell::staticRun(config(), new UserViewStateAdapter("workspace/admin/log/index"),"views/log.html", "LogSearchController");


?>