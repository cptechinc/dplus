<?php
	$whsesession = WhsesessionQuery::create()->findOneBySessionid(session_id());
	$warehouse   = WarehouseQuery::create()->findOneByWhseid($whsesession->whseid);
	$page->print = false;

	if (file_exists(__DIR__."/whse-print-item-label-$config->company.php")) {
		include __DIR__."/whse-print-item-label-$config->company.php";
	} else {
		include __DIR__."/whse-print-item-label-default.php";
	}

	// Add JS
	$config->scripts->append(hash_templatefile('scripts/lib/jquery-validate.js'));
	$config->scripts->append(hash_templatefile('scripts/warehouse/print-item-label.js'));
