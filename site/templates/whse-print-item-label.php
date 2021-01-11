<?php
	$whsesession = WhsesessionQuery::create()->findOneBySessionid(session_id());
	$warehouse   = WarehouseQuery::create()->findOneByWhseid($whsesession->whseid);

	if (file_exists(__DIR__."/whse-print-item-label-$config->company.php")) {
		include(__DIR__."/whse-print-item-label-$config->company.php");
	} else {
		include(__DIR__."/whse-print-item-label-default.php");
	}
