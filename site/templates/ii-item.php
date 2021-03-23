<?php
	include_once($modules->get('Mvc')->controllersPath().'vendor/autoload.php');
	use Controllers\Mii\Ii;
	use Controllers\Mii\Stock;
	Ii::init();
	
	$routes = [
		['GET',  '', Ii::class, 'index'],
		['GET',  'page{nbr:\d+}/', Ii::class, 'list'],
		['GET',  'stock', Stock::class, 'index'],
	];

	$router = new Mvc\Router();
	$router->setRoutes($routes);
	$router->setRoutePrefix($page->url);
	$page->body = $router->route();

	include __DIR__ . "/basic-page.php";
