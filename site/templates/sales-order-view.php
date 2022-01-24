<?php
	include($modules->get('Mvc')->controllersPath().'vendor/autoload.php');
	use Controllers\Mso\SalesOrder\SalesOrder as SalesOrderController;
	use Controllers\Mso\SalesOrder\Documents;
	use Controllers\Mso\SalesOrder\Notes;
	use Controllers\Mso\SalesOrder\PrintSo;

	SalesOrderController::initHooks();

	$segments = $input->urlSegments();

	if (empty($segments) === false && $segments[sizeof($segments)] == 'print') {
		PrintSo::initHooks();
	}

	$routes = [
		['GET',  '', SalesOrderController::class, 'index'],
		['GET',  'documents/', Documents::class, 'index'],
		['GET',  'print/', PrintSo::class, 'index'],
		'notes' => [
			['GET',   '', Notes::class, 'index'],
			['POST',  '', Notes::class, 'handleCRUD'],
		],
	];
	$router = new Mvc\Routers\Router();
	$router->setRoutes($routes);
	$router->setRoutePrefix($page->url);
	$page->body = $router->route();

	$page->show_breadcrumbs = false;

	if ($config->ajax) {
		echo $page->body;
	} else {
		if ($page->print) {
			$page->show_title = true;

			if ($page->is_pdf()) {
				$page->show_title = false;
			}
			include __DIR__ . "/blank-page.php";
		} else {
			include __DIR__ . "/basic-page.php";
		}
	}
