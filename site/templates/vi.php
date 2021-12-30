<?php
	include($modules->get('Mvc')->controllersPath().'vendor/autoload.php');
	use Controllers\Mvi\Vi;

	Vi\Vi::initHooks();

	$routes = [
		['GET', '', Vi\Vi::class, 'index'],
		['GET', 'page{pagenbr:\d+}/', Vi\Vi::class, 'index'],
		['GET', 'contacts', Vi\Contacts::class, 'index'],
		'costing' => [
			['GET', '', Vi\Costing::class, 'index'],
			['GET', 'page{pagenbr:\d+}/', Vi\Costing::class, 'index'],
		],
		['GET', 'documents', Vi\Documents::class, 'index'],
		['GET', 'notes', Vi\Notes::class, 'index'],
		['GET', 'open-invoices', Vi\OpenInvoices::class, 'index'],
		['GET', 'payments', Vi\Payments::class, 'index'],
		'purchase-history' => [
			['GET', '', Vi\PurchaseHistory::class, 'index'],
		],
		'purchase-orders' => [
			['GET', '', Vi\PurchaseOrders::class, 'index'],
			['GET', 'uninvoiced', Vi\PurchaseOrders\Uninvoiced::class, 'index'],
			['GET', 'unreleased', Vi\PurchaseOrders\Unreleased::class, 'index'],
		],
		'ship-froms' => [
			['GET', '', Vi\Shipfrom::class, 'index'],
			['GET', 'page{pagenbr:\d+}/', Vi\Shipfrom::class, 'index'],
		],
		['GET', 'summary', Vi\Summary::class, 'index'],
	];

	$router = new Mvc\Router();
	$router->setRoutes($routes);
	$router->setRoutePrefix($page->url);
	$page->body = $router->route();
	$page->show_breadcrumbs = false;

	include __DIR__ . "/basic-page.php";
