<?php
	include($modules->get('Mvc')->controllersPath().'vendor/autoload.php');

	use Controllers\Min\Itm\Itm as ItmController;
	use Controllers\Min\Itm\Item;

	ItmController::init();
	Item::initHooks();

	$routes = [
		['GET',  '', ItmController::class, 'item'],
		['GET',  'page{d:\d+}', ItmController::class, 'itemList'],
		['POST', '', ItmController::class, 'itemHandleCRUD'],
		'costing' => [
			['GET',  '', ItmController::class, 'costing'],
			['POST', '', ItmController::class, 'CostingHandleCRUD'],
		],
		'pricing' => [
			['GET',  '', ItmController::class, 'pricing'],
			['POST', '', ItmController::class, 'priciingHandleCRUD'],
		],
		'warehouses' => [
			['GET',  '', ItmController::class, 'warehouse'],
			['GET',  'page{d:\d+}', ItmController::class, 'warehouseList'],
			['POST', '', ItmController::class, 'warehouseHandleCRUD'],
		],
		'misc' => [
			['GET',  '', ItmController::class, 'misc'],
			['POST', '', ItmController::class, 'miscHandleCRUD'],
		],
		'xrefs' => [
			['GET',  '', ItmController::class, 'xrefs'],
			['POST', '', ItmController::class, 'xrefsHandleCRUD'],
		],
	];
	$router = new Mvc\Router();
	$router->setRoutes($routes);
	$router->setRoutePrefix($page->url);
	$page->body = $router->route();

	if ($router->hasError() === false) {
		$config->scripts->append(hash_templatefile('scripts/lib/jquery-validate.js'));
		$session->removeFor('response', 'itm');
		$page->show_breadcrumbs = false;
	}
	include __DIR__ . "/basic-page.php";
