<?php
	include($modules->get('Mvc')->controllersPath().'vendor/autoload.php');
	use Controllers\Mii\Ii;

	$routes = [
		['GET',  '', Ii::class, 'index'],
		['GET',  'page{nbr:\d+}/', Ii::class, 'list'],
	];

	$router = new Mvc\Router();
	$router->setRoutes($routes);
	$router->setRoutePrefix($page->url);
	$response = $router->route();

	if ($router->hasError()) {
		$page->body = $response;
	}

	include __DIR__ . "/basic-page.php";
