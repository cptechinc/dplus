<?php
	include($modules->get('Mvc')->controllersPath().'vendor/autoload.php');
	use Controllers\Min\Itm\Costing as CostingController;

	$routes = [
		['GET',  '', CostingController::class, 'index'],
		['POST', '', CostingController::class, 'handleCRUD'],
	];
	$router = new Mvc\Router();
	$router->setRoutes($routes);
	$router->setRoutePrefix($page->url);
	$page->body = $router->route();

	if ($router->hasError() === false) {
		$config->scripts->append(hash_templatefile('scripts/lib/jquery-validate.js'));
		$session->removeFor('response', 'itm');
		$page->show_breadcrumbs = false;
	}
	include __DIR__ . "/basic-page.php";
