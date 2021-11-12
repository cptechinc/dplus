<?php
	include_once($modules->get('Mvc')->controllersPath().'vendor/autoload.php');

	use Controllers\Mwm\Inventory\Mlot;

	$routes = [
		['GET',  '', Mlot\Menu::class, 'index'],
		'img' => [
			['GET',  '', Mlot\Img::class, 'index'],
		]
	];

	$router = new Mvc\Router();
	$router->setRoutes($routes);
	$router->setRoutePrefix($page->url);
	$page->body = $router->route();
	$page->show_breadcrumbs = false;
	include __DIR__ . "/basic-page.php";
