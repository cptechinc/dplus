<?php
	include($modules->get('Mvc')->controllersPath().'vendor/autoload.php');

	use Controllers\Min\Itm\Itm as ItmController;
	use Controllers\Min\Itm\Item;
	use Controllers\Min\Itm\Xrefs\Controller as XrefsController;

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
			['GET',  '', XrefsController::class, 'xrefs'],
			['POST', '', XrefsController::class, 'xrefsHandleCRUD'],
			'cxm' => [
				['GET',  '', XrefsController::class, 'cxm'],
				['POST', '', XrefsController::class, 'cxmHandleCRUD'],
			],
			'vxm' => [
				['GET',  '', XrefsController::class, 'vxm'],
				['POST', '', XrefsController::class, 'vxmHandleCRUD'],
			],
			'mxrfe' => [
				['GET',  '', XrefsController::class, 'mxrfe'],
				['POST', '', XrefsController::class, 'mxrfeHandleCRUD'],
			],
			'upcx' => [
				['GET',  '', XrefsController::class, 'upcx'],
				['POST', '', XrefsController::class, 'upcxHandleCRUD'],
			],
			'kim' => [
				['GET',  '', XrefsController::class, 'kim'],
				['POST', '', XrefsController::class, 'kimHandleCRUD'],
			],
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
