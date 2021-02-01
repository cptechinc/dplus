<?php
	include($modules->get('Mvc')->controllersPath().'vendor/autoload.php');
	use Controllers\Ajax\Lookup as AjaxLookup;
	$page->searchURL = $input->url();
	$routes = [
		['GET', $page->url, AjaxLookup::class, 'test'],
		['GET', $page->url.'tariff-codes/', AjaxLookup::class, 'tariffCodes'],
		['GET', $page->url.'items', AjaxLookup::class, 'itmItems'],
		['GET', $page->url.'items/page{id:\d+}/', AjaxLookup::class, 'itmItems'],
	];

	$router = new Mvc\Router();
	$router->setRoutes($routes);
	$router->route();

	if ($config->ajax) {
		echo $page->body;
	} else {
		include __DIR__ . "/basic-page.php";
	}
