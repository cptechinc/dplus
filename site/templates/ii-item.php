<?php
	include_once($modules->get('Mvc')->controllersPath().'vendor/autoload.php');
	use Controllers\Mii\Ii;
	use Controllers\Mii\Ii\Stock;
	use Controllers\Mii\Ii\Requirements;
	use Controllers\Mii\Ii\Pricing;
	use Controllers\Mii\Ii\Usage;

	Ii::init();

	$routes = [
		['GET',  '', Ii::class, 'index'],
		['GET',  'page{nbr:\d+}/', Ii::class, 'list'],
		['GET',  'stock', Stock::class, 'index'],
		['GET',  'requirements', Requirements::class, 'index'],
		['GET',  'pricing', Pricing::class, 'index'],
		['GET',  'usage', Usage::class, 'index'],
	];

	$router = new Mvc\Router();
	$router->setRoutes($routes);
	$router->setRoutePrefix($page->url);
	$page->body = $router->route();
	$page->show_breadcrumbs = false;

	include __DIR__ . "/basic-page.php";
