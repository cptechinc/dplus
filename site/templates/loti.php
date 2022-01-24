<?php
	include_once($modules->get('Mvc')->controllersPath().'vendor/autoload.php');

	use Controllers\Mii\Loti;

	Loti\Base::initHooks();

	$routes = [
		['GET',  '', Loti\Loti::class, 'index'],
		['GET',  'activity/', Loti\Activity::class, 'index'],
	];


	$router = new Mvc\Routers\Router();
	$router->setRoutes($routes);
	$router->setRoutePrefix($page->url);
	$page->show_breadcrumbs = false;
	$page->body = $router->route();
	include __DIR__ . "/basic-page.php";
