<?php
	use Controllers\Mpo\PurchaseOrder\Lists;
	use Controllers\Mpo\PurchaseOrder\Epo;

	Lists\PurchaseOrder::initHooks();

	$routes = [
		['GET', '', Lists\PurchaseOrder::class, 'index'],
		['GET', 'page{pagenbr:\d+}', Lists\PurchaseOrder::class, 'list'],
		'vendor' => [
			['GET', '', Lists\Vendor::class, 'index'],
			['GET', 'page{pagenbr:\d+}', Lists\Vendor::class, 'index'],
		],
		'epo' => [
			['GET', '', Epo\Create::class, 'index'],
			['POST', '', Epo\Create::class, 'handleCRUD'],
		],
	];
	$router = new Mvc\Router();
	$router->setRoutes($routes);
	$router->setRoutePrefix($page->url);
	$page->body = $router->route();

	if ($config->ajax) {
		echo $page->body;
	} else {
		include __DIR__ . "/basic-page.php";
	}
