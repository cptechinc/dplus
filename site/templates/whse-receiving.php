<?php
	if (file_exists(__DIR__."/whse-receiving-$config->company.php")) {
		$template = "whse-receiving-$config->company";
	} else {
		$template = 'whse-receiving-default';
	}

	$config->scripts->append(hash_templatefile('scripts/warehouse/receiving.js'));

	include __DIR__ . "/$template.php";
