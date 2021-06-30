<?php
	include($modules->get('Mvc')->controllersPath().'vendor/autoload.php');
	use Controllers\Mqo\Quote\Quote as QtController;
	use Controllers\Mqo\Quote\Documents;
	use Controllers\Mqo\Quote\Notes;
	use Controllers\Mqo\Quote\PrintQt;

	QtController::initHooks();

	$segments = $input->urlSegments();
	$segment  = $segments[sizeof($segments)];
	$values   = $input->get;

	if (empty($segments) === false && $segment == 'print') {
		PrintQt::initHooks();
	}

	$routes = [
		['GET',  '', QtController::class, 'index'],
		['GET',  'documents/', Documents::class, 'index'],
		'notes' => [
			['GET',   '', Notes::class, 'index'],
			['POST',  '', Notes::class, 'handleCRUD'],
		],
		['GET',  'print/', PrintQt::class, 'index'],
	];
	$router = new Mvc\Router();
	$router->setRoutes($routes);
	$router->setRoutePrefix($page->url);
	$page->body = $router->route();

	if ($config->ajax) {
		echo $page->body;
	} else {
		if ($segment == 'print') {
			$page->show_title = true;

			if ($values->offsetExists('pdf')) {
				$page->show_title = false;
			}
			include __DIR__ . "/blank-page.php";
		} else {
			include __DIR__ . "/basic-page.php";
		}
	}
