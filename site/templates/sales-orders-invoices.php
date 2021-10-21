<?php
	use Controllers\Mso\SalesOrder\Lists\Invoices;

	Invoices\Invoice::initHooks();

	$routes = [
		['GET', '', Invoices\Invoice::class, 'index'],
		['GET', 'page{pagenbr:\d+}', Invoices\Invoice::class, 'index'],
		'customer' => [
			['GET', '', Invoices\Customer::class, 'index'],
			['GET', 'page{pagenbr:\d+}', Invoices\Customer::class, 'index'],
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
