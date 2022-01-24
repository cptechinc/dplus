<?php
	use Controllers\Mso\SalesOrder\Lists\SalesOrder as Orders;
	use Controllers\Mso\SalesOrder\Lists\Customer;

	Orders::initHooks();

	$routes = [
		['GET', '', Orders::class, 'index'],
		['GET', 'page{pagenbr:\d+}', Orders::class, 'index'],
		'customer' => [
			['GET', '', Customer::class, 'index'],
			['GET', 'page{pagenbr:\d+}', Customer::class, 'index'],
		]
	];
	$router = new Mvc\Routers\Router();
	$router->setRoutes($routes);
	$router->setRoutePrefix($page->url);
	$page->body = $router->route();

	if ($config->ajax) {
		echo $page->body;
	} else {
		include __DIR__ . "/basic-page.php";
	}
