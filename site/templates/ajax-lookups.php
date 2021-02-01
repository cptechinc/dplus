<?php
	include($modules->get('Mvc')->controllersPath().'vendor/autoload.php');
	use Controllers\Ajax\Lookup as AjaxLookup;
	$page->searchURL = $input->url();
	$routes = [
		['GET', '', AjaxLookup::class, 'test'],
		// Tariff Codes
		['GET', 'tariff-codes/', AjaxLookup::class, 'tariffCodes'],
		['GET', 'tariff-codes/page{d:\d+}/', AjaxLookup::class, 'tariffCodes'],
		// Itm
		['GET', 'items', AjaxLookup::class, 'itmItems'],
		['GET', 'items/page{nbr:\d+}/', AjaxLookup::class, 'itmItems'],
		// Msds Codes
		['GET', 'msds-codes/', AjaxLookup::class, 'msdsCodes'],
		['GET', 'msds-codes/page{nbr:\d+}/', AjaxLookup::class, 'msdsCodes'],
		// Freight Codes
		['GET', 'freight-codes/', AjaxLookup::class, 'freightCodes'],
		['GET', 'freight-codes/page{nbr:\d+}/', AjaxLookup::class, 'freightCodes'],
		// VXM
		['GET', 'vxm/', AjaxLookup::class, 'vxm'],
		['GET', 'vxm/page{nbr:\d+}/', AjaxLookup::class, 'vxm'],
		// Warehouse
		['GET', 'warehouses/', AjaxLookup::class, 'warehouses'],
		['GET', 'warehouses/page{nbr:\d+}/', AjaxLookup::class, 'warehouses'],
		// User
		['GET', 'users/', AjaxLookup::class, 'users'],
		['GET', 'users/page{nbr:\d+}/', AjaxLookup::class, 'users'],
	];

	$router = new Mvc\Router();
	$router->setRoutes($routes);
	$router->setRoutePrefix($page->url);
	$router->route();

	if ($config->ajax) {
		echo $page->body;
	} else {
		include __DIR__ . "/basic-page.php";
	}
