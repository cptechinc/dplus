<?php
	include_once($modules->get('Mvc')->controllersPath().'vendor/autoload.php');
	use Controllers\Mii\Ii;
	Ii\Ii::init();

	$routes = [
		['GET', '', Ii\Ii::class, 'index'],
		['GET', 'page{pagenbr:\d+}/', Ii\Ii::class, 'index'],
		['GET', 'stock', Ii\Stock::class, 'index'],
		['GET', 'requirements', Ii\Requirements::class, 'index'],
		['GET', 'pricing', Ii\Pricing::class, 'index'],
		['GET', 'usage', Ii\Usage::class, 'index'],
		['GET', 'costing', Ii\Costing::class, 'index'],
		['GET', 'activity', Ii\Activity::class, 'index'],
		['GET', 'kit', Ii\Kit::class, 'index'],
		['GET', 'bom', Ii\Bom::class, 'index'],
		['GET', 'where-used', Ii\WhereUsed::class, 'index'],
		['GET', 'lotserial', Ii\Lotserial::class, 'index'],
		['GET', 'general', Ii\General::class, 'index'],
		['GET', 'substitutes', Ii\Substitutes::class, 'index'],
		['GET', 'documents', Ii\Documents::class, 'index'],
		['GET', 'sales-orders', Ii\SalesOrders::class, 'index'],
		['GET', 'sales-history', Ii\SalesHistory::class, 'index'],
		['GET', 'quotes', Ii\Quotes::class, 'index'],
		['GET', 'purchase-orders', Ii\PurchaseOrders::class, 'index'],
		['GET', 'purchase-history', Ii\PurchaseHistory::class, 'index'],
	];

	$router = new Mvc\Routers\Router();
	$router->setRoutes($routes);
	$router->setRoutePrefix($page->url);
	$page->body = $router->route();
	$page->show_breadcrumbs = false;

	if ($input->get->offsetExists('print')) {
		include __DIR__ . "/blank-page.php";
	} else {
		include __DIR__ . "/basic-page.php";
	}

	
