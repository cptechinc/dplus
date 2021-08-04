<?php
	use Controllers\Mpo\PurchaseOrder\Lists;

	$routes = [
		['GET', '', Lists\PurchaseOrder::class, 'index'],
		['GET', 'page{d:\d+}', Lists\PurchaseOrder::class, 'list'],
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
