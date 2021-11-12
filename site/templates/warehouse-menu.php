<?php
include($modules->get('Mvc')->controllersPath().'vendor/autoload.php');
use Controllers\Dplus\Menu as Dmenu;
use Controllers\Mwm\Menu;

if (empty($page->pw_template) === false) {
	if (Dmenu::validateUserPermission() && Dmenu::templateExists($page)) {
		include Dmenu::templateFileName($page);
	}

	if (Dmenu::templateExists($page) === false) {
		$page->body .= $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => "Template Not Found", 'iconclass' => 'fa fa-warning fa-2x', 'message' => "Template $page->pw_template not found"]);
		include __DIR__ . "/basic-page.php";
	}

	if (Dmenu::validateUserPermission() === false) {
		$page->body = Menu::index(new WireData());
		include __DIR__ . "/basic-page.php";
	}
} else {
	$page->body = Menu::index(new WireData());
	include __DIR__ . "/basic-page.php";
}
