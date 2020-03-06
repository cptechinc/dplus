<?php
	include_once('./ci-include.php');

	if ($customerquery->count()) {
		
	}

	if ($page->print) {
		$page->show_title = true;
		include __DIR__ . "/blank-page.php";
	} else {
		include __DIR__ . "/basic-page.php";
	}
