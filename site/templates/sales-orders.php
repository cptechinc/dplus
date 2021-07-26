<?php
	use Controllers\Mso\SalesOrder\Lists\SalesOrder as Orders;

	$routes = [
		['GET', '', Orders::class, 'index'],
		['GET', 'page{d:\d+}', Orders::class, 'index'],
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
