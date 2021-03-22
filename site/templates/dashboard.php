<?php
	include($modules->get('Mvc')->controllersPath().'vendor/autoload.php');
	use Controllers\Dplus\Dashboard as DashboardController;

	$routes = [
		['GET',  '', DashboardController::class, 'dashboard'],
	];

	$router = new Mvc\Router();
	$router->setRoutes($routes);
	$router->setRoutePrefix($page->url);
	$page->body = $router->route();

	include __DIR__ . "/basic-page.php";
