<?php
	include($modules->get('Mvc')->controllersPath().'vendor/autoload.php');
	use Controllers\Min\Itm\Itm as ItmController;
	use Controllers\Min\Itm\Costing as CostingController;
	use Controllers\Min\Itm\Pricing as PricingController;
	use Controllers\Min\Itm\Warehouse as WarehouseController;
	use Controllers\Min\Itm\Misc as MiscController;

	ItmController::initHooks();

	$routes = [
		['GET',  '', ItmController::class, 'index'],
		['GET',  'page{d:\d+}', ItmController::class, 'list'],
		['POST', '', ItmController::class, 'handleCRUD'],
		'costing' => [
			['GET',  '', CostingController::class, 'index'],
			['POST', '', CostingController::class, 'handleCRUD'],
		],
		'pricing' => [
			['GET',  '', PricingController::class, 'index'],
			['POST', '', PricingController::class, 'handleCRUD'],
		],
		'warehouses' => [
			['GET',  '', WarehouseController::class, 'index'],
			['GET',  'page{d:\d+}', WarehouseController::class, 'list'],
			['POST', '', WarehouseController::class, 'handleCRUD'],
		],
		'misc' => [
			['GET',  '', MiscController::class, 'index'],
			['POST', '', MiscController::class, 'handleCRUD'],
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
