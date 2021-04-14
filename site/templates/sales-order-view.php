<?php
	include($modules->get('Mvc')->controllersPath().'vendor/autoload.php');
	use Controllers\Mso\SalesOrder\SalesOrder as SalesOrderController;
	use Controllers\Mso\SalesOrder\Documents;

	$routes = [
		['GET',  '', SalesOrderController::class, 'index'],
		['GET',  'documents/', Documents::class, 'index'],
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
