<?php
	include($modules->get('Mvc')->controllersPath().'vendor/autoload.php');
	use Controllers\Dplus\Process as Dprocess;

	Dprocess::index(new WireData());

	if (Dprocess::$permitted === false || Dprocess::$templateExists === false) {
		include __DIR__ . "/basic-page.php";
	}

	if (Dprocess::$permitted && Dprocess::$templateExists) {
		include Dprocess::templateFileName($page);
	}
