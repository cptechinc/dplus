<?php
	include($modules->get('Mvc')->controllersPath().'vendor/autoload.php');
	use Controllers\Mci\Ci;

	Ci\Ci::initHooks();

	$routes = [
		['GET',  '', Ci\Ci::class, 'index'],
		['GET',  'page{nbr:\d+}/', Ci\Ci::class, 'list'],
	];

	$router = new Mvc\Router();
	$router->setRoutes($routes);
	$router->setRoutePrefix($page->url);
	$page->body = $router->route();

	include __DIR__ . "/basic-page.php";
