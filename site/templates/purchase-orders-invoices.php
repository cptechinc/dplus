<?php
	use Controllers\Mpo\ApInvoice\Lists;
	use Controllers\Mpo\ApInvoice;

	ApInvoice\ApInvoice::initHooks();

	$routes = [
		['GET', '', Lists\ApInvoice::class, 'index'],
		['GET', 'page{pagenbr:\d+}', Lists\ApInvoice::class, 'list'],
		'invoice' => [
			['GET', '', ApInvoice\ApInvoice::class, 'index'],
			['GET', 'documents/', ApInvoice\Documents::class, 'index'],
		],
		'vendor' => [
			['GET', '', Lists\Vendor::class, 'index'],
			['GET', 'page{pagenbr:\d+}', Lists\Vendor::class, 'index'],
		]
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
