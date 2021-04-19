<?php
	include_once($modules->get('Mvc')->controllersPath().'vendor/autoload.php');
	use Controllers\Mii\Ii;
	Ii::init();

	$routes = [
		['GET',  '', Ii::class, 'item'],
		['GET',  'page{nbr:\d+}/', Ii::class, 'item'],
		['GET',  'stock', Ii::class, 'stock'],
		['GET',  'requirements', Ii::class, 'requirements'],
		['GET',  'pricing', Ii::class, 'pricing'],
		['GET',  'usage', Ii::class, 'usage'],
		['GET',  'costing', Ii::class, 'costing'],
		['GET',  'activity', Ii::class, 'activity'],
		['GET',  'kit', Ii::class, 'kit'],
		['GET',  'bom', Ii::class, 'bom'],
		['GET',  'where-used', Ii::class, 'whereUsed'],
		['GET',  'lotserial', Ii::class, 'lotserial'],
		['GET',  'general', Ii::class, 'general'],
		['GET',  'substitutes', Ii::class, 'substitutes'],
		['GET',  'documents', Ii::class, 'documents'],
		['GET',  'sales-orders', Ii::class, 'salesOrders'],
		['GET',  'sales-history', Ii::class, 'salesHistory'],
		['GET',  'quotes', Ii::class, 'quotes'],
		['GET',  'purchase-orders', Ii::class, 'purchaseOrders'],
		['GET',  'purchase-history', Ii::class, 'purchaseHistory'],
	];

	$router = new Mvc\Router();
	$router->setRoutes($routes);
	$router->setRoutePrefix($page->url);
	$page->body = $router->route();
	$page->show_breadcrumbs = false;

	include __DIR__ . "/basic-page.php";
