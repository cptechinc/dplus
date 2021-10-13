<?php
	include($modules->get('Mvc')->controllersPath().'vendor/autoload.php');
	use Controllers\Misc\Cart\Cart as Controller;

	Controller::initHooks();

	$routes = [
		['GET',  '', Controller::class, 'index'],
		['POST', '', Controller::class, 'handleCRUD'],
	];

	$router = new Mvc\Router();
	$router->setRoutes($routes);
	$router->setRoutePrefix($page->url);
	$page->body = $router->route();
	$page->show_breadcrumbs = false;

	include __DIR__ . "/basic-page.php";
