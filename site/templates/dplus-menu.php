<?php
	include($modules->get('Mvc')->controllersPath().'vendor/autoload.php');
	use Controllers\Dplus\Menu;

	if (empty($page->pw_template) === false) {
		if (Menu::validateUserPermission() && Menu::templateExists($page)) {
			include Menu::templateFileName($page);
		}

		if (Menu::templateExists($page) === false) {
			$page->body .= $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => "Template Not Found", 'iconclass' => 'fa fa-warning fa-2x', 'message' => "Template $page->pw_template not found"]);
			include __DIR__ . "/basic-page.php";
		}

		if (Menu::validateUserPermission() === false) {
			$page->body = Menu::index(new WireData());
			include __DIR__ . "/basic-page.php";
		}
	} else {
		$page->body = Menu::index(new WireData());
		include __DIR__ . "/basic-page.php";
	}
