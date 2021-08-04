<?php
	use Controllers\Mpo\ApInvoice\Lists;


	$routes = [
		['GET', '', Lists\ApInvoice::class, 'index'],
		['GET', 'page{d:\d+}', Lists\ApInvoice::class, 'list'],
		'vendor' => [
			['GET', '', Lists\Vendor::class, 'index'],
			['GET', 'page{d:\d+}', Lists\Vendor::class, 'index'],
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
