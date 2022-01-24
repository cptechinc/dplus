<?php
	include($modules->get('Mvc')->controllersPath().'vendor/autoload.php');
	use Controllers\Mci\Ci;

	Ci\Ci::initHooks();

	$routes = [
		['GET',  '', Ci\Ci::class, 'index'],
		['GET',  'page{pagenbr:\d+}/', Ci\Ci::class, 'index'],
		'pricing' => [
			['GET',  '', Ci\Pricing::class, 'index'],
			['GET',  'page{pagenbr:\d+}/', Ci\Pricing::class, 'index'],
		],
		'ship-tos' => [
			['GET',  '', Ci\Shipto::class, 'index'],
			['GET',  'page{pagenbr:\d+}/', Ci\Shipto::class, 'index'],
		],
		'contacts' => [
			['GET',  '', Ci\Contacts::class, 'index'],
			'contact' => [
				['GET',  '', Ci\Contacts\Contact::class, 'index'],
				'edit' => [
					['GET',  '', Ci\Contacts\Edit::class, 'index'],
					['POST',  '', Ci\Contacts\Edit::class, 'handleCRUD'],
				]
			]
		],
		['GET',  'documents/', Ci\Documents::class, 'index'],
		['GET',  'sales-orders/', Ci\SalesOrders::class, 'index'],
		['GET',  'sales-history/', Ci\SalesHistory::class, 'index'],
		['GET',  'purchase-orders/', Ci\PurchaseOrders::class, 'index'],
		['GET',  'quotes/', Ci\Quotes::class, 'index'],
		['GET',  'open-invoices/', Ci\OpenInvoices::class, 'index'],
		['GET',  'payments/', Ci\Payments::class, 'index'],
		['GET',  'credit/', Ci\Credit::class, 'index'],
		['GET',  'standing-orders/', Ci\StandingOrders::class, 'index'],
	];

	$router = new Mvc\Routers\Router();
	$router->setRoutes($routes);
	$router->setRoutePrefix($page->url);
	$page->body = $router->route();
	$page->show_breadcrumbs = false;

	include __DIR__ . "/basic-page.php";
