<?php
	include($modules->get('Mvc')->controllersPath().'vendor/autoload.php');
	use Controllers\Mso\SalesOrder\SalesOrder as SalesOrderController;
	use Controllers\Mso\Eso;
	SalesOrderController::initHooks();

	$routes = [
		['GET',  '', Eso::class, 'index'],
		['POST', '', Eso::class, 'handleCRUD'],
		['GET',  'line/', Eso::class, 'editItem'],
		['GET',  'new/', Eso::class, 'editNewOrder'],
	];
	$router = new Mvc\Router();
	$router->setRoutes($routes);
	$router->setRoutePrefix($page->url);
	$page->body = $router->route();

	if ($router->hasError() === false) {
		$config->scripts->append(hash_templatefile('scripts/lib/jquery-validate.js'));
		$session->removeFor('response', 'eso');
	}

	if ($config->ajax) {
		echo $page->body;
	} else {
		include __DIR__ . "/basic-page.php";
	}
