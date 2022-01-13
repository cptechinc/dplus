<?php
	$modules->get('WarehouseManagement');

	include_once($modules->get('Mvc')->controllersPath().'vendor/autoload.php');

	use Controllers\Wm\Inventory\StockStatus;

	StockStatus::initHooks();

	$routes = [
		['GET',  '', StockStatus::class, 'index'],
	];

	$router = new Mvc\Router();
	$router->setRoutes($routes);
	$router->setRoutePrefix($page->url);
	$page->body = $router->route();
	$page->show_breadcrumbs = false;
	include __DIR__ . "/basic-page.php";
