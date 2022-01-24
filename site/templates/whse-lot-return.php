<?php
	include_once($modules->get('Mvc')->controllersPath().'vendor/autoload.php');

	use Controllers\Wm\Inventory\LotReturn;

	//LotReturn::initHooks();

	$routes = [
		['GET',  '', LotReturn::class, 'index'],
	];


	$router = new Mvc\Routers\Router();
	$router->setRoutes($routes);
	$router->setRoutePrefix($page->url);
	$page->body = $router->route();
	$page->show_breadcrumbs = false;
	include __DIR__ . "/basic-page.php";
