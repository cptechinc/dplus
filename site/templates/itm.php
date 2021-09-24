<?php
	include($modules->get('Mvc')->controllersPath().'vendor/autoload.php');

	use Controllers\Min\Itm\Itm as ItmController;
	use Controllers\Min\Itm\Item;
	use Controllers\Min\Itm\Xrefs\Controller as XrefsController;
	use Controllers\Min\Itm\Xrefs as ItmXrefs;

	use Controllers\Min\Itm as Mitm;

	ItmController::init();
	Item::initHooks();

	$routes = [
		['GET',  '', Mitm\Item::class, 'index'],
		['GET',  'page{d:\d+}', Mitm\Item::class, 'index'],
		['POST', '', Mitm\Item::class, 'handleCRUD'],
		'costing' => [
			['GET',  '', Mitm\Costing::class, 'index'],
			['POST', '', Mitm\Costing::class, 'handleCRUD'],
		],
		'pricing' => [
			['GET',  '', Mitm\Pricing::class, 'index'],
			['POST', '', Mitm\Pricing::class, 'handleCRUD'],
		],
		'warehouses' => [
			['GET',  '', Mitm\Warehouse::class, 'index'],
			['GET',  'page{d:\d+}', Mitm\Warehouse::class, 'index'],
			['POST', '', Mitm\Warehouse::class, 'handleCRUD'],
		],
		'misc' => [
			['GET',  '', Mitm\Misc::class, 'index'],
			['POST', '', Mitm\Misc::class, 'handleCRUD'],
		],
		// 'dimensions' => [
		// 	['GET',  '', Mitm\Dimensions::class, 'index'],
		// 	['POST', '', Mitm\Dimensions::class, 'handleCRUD'],
		// ],
		'xrefs' => [
			['GET',  '', ItmXrefs\Xrefs::class, 'index'],
			['POST', '', ItmXrefs\Xrefs::class, 'handleCRUD'],
			'cxm' => [
				['GET',  '', ItmXrefs\Cxm::class, 'index'],
				['POST', '', ItmXrefs\Cxm::class, 'handleCRUD'],
			],
			'vxm' => [
				['GET',  '', ItmXrefs\Vxm::class, 'index'],
				['POST', '', ItmXrefs\Vxm::class, 'handleCRUD'],
			],
			'mxrfe' => [
				['GET',  '', ItmXrefs\Mxrfe::class, 'index'],
				['POST', '', ItmXrefs\Mxrfe::class, 'handleCRUD'],
			],
			'upcx' => [
				['GET',  '', ItmXrefs\Upcx::class, 'index'],
				['POST', '', ItmXrefs\Upcx::class, 'hndleCRUD'],
			],
			'kim' => [
				['GET',  '', ItmXrefs\Kim::class, 'index'],
				['POST', '', ItmXrefs\Kim::class, 'kimhandleCRUD'],
			],
			'substitutes' => [
				['GET',  '', ItmXrefs\Substitutes::class, 'index'],
				['GET',  'page{d:\d+}', ItmXrefs\Substitutes::class, 'index'],
				['POST', '', ItmXrefs\Substitutes::class, 'handleCRUD'],
			],
		],
	];
	$router = new Mvc\Router();
	$router->setRoutes($routes);
	$router->setRoutePrefix($page->url);
	$page->body = $router->route();

	if ($router->hasError() === false) {
		$config->scripts->append(hash_templatefile('scripts/lib/jquery-validate.js'));
		if ($config->ajax === false) {
			$session->removeFor('response', 'itm');
		}
		$page->show_breadcrumbs = false;
	}
	include __DIR__ . "/basic-page.php";
