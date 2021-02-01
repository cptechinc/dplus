<?php
	include($modules->get('Mvc')->controllersPath().'vendor/autoload.php');
	use Controllers\Ajax\Lookup as AjaxLookup;
	$page->searchURL = $input->url();
	$routes = [
		['GET', $page->url, AjaxLookup::class, 'test'],
		// Tariff Codes
		['GET', $page->url.'tariff-codes/', AjaxLookup::class, 'tariffCodes'],
		['GET', $page->url.'tariff-codes/page{d:\d+}/', AjaxLookup::class, 'tariffCodes'],
		// Itm
		['GET', $page->url.'items', AjaxLookup::class, 'itmItems'],
		['GET', $page->url.'items/page{nbr:\d+}/', AjaxLookup::class, 'itmItems'],
		// Msds Codes
		['GET', $page->url.'msds-codes/', AjaxLookup::class, 'msdsCodes'],
		['GET', $page->url.'msds-codes/page{nbr:\d+}/', AjaxLookup::class, 'msdsCodes'],
		// Freight Codes
		['GET', $page->url.'freight-codes/', AjaxLookup::class, 'freightCodes'],
		['GET', $page->url.'freight-codes/page{nbr:\d+}/', AjaxLookup::class, 'freightCodes'],
		// VXM
		['GET', $page->url.'vxm/', AjaxLookup::class, 'vxm'],
		['GET', $page->url.'vxm/page{nbr:\d+}/', AjaxLookup::class, 'vxm'],
		// Warehouse
		['GET', $page->url.'warehouses/', AjaxLookup::class, 'warehouses'],
		['GET', $page->url.'warehouses/page{nbr:\d+}/', AjaxLookup::class, 'warehouses'],
		// User
		['GET', $page->url.'users/', AjaxLookup::class, 'users'],
		['GET', $page->url.'users/page{nbr:\d+}/', AjaxLookup::class, 'users'],
	];

	$router = new Mvc\Router();
	$router->setRoutes($routes);
	$router->route();

	if ($config->ajax) {
		echo $page->body;
	} else {
		include __DIR__ . "/basic-page.php";
	}
