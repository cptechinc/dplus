<?php

include($modules->get('Mvc')->controllersPath().'vendor/autoload.php');
use Controllers\Mwm\Menu;

if (Menu::sessionExists() === false) {
	Menu::requestWhseSessionLogin();
	$session->redirect($page->url, $http301 = false);
}

if (Menu::sessionWhseExists() === false) {
	$whsesession = Menu::getWhseSession();
	$page->body = $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => "Warehouse not Found", 'iconclass' => 'fa fa-warning fa-2x', 'message' => "Warehouse '$whsesession->whseid' not available "]);
	include('./basic-page.php');
}

if (Menu::sessionWhseExists()) {
	include('./dplus-function.php');
}
