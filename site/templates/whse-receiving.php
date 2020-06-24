<?php
	if (file_exists(__DIR__."/whse-receiving-$config->company.php")) {
		$template = "whse-receiving-$config->company";
	} else {
		$template = 'whse-receiving-default';
	}

	include __DIR__ . "/$template.php";
