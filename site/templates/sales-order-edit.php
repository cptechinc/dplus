<?php
	include_once($modules->get('Mvc')->controllersPath().'vendor/autoload.php');
	use Controllers\Mso\SalesOrder;
	SalesOrder\SalesOrder::initHooks();

	$routes = [
		['GET',  '', SalesOrder\Edit::class, 'index'],
		['POST', '', SalesOrder\Edit::class, 'handleCRUD'],
		['GET',  'line/', SalesOrder\Edit::class, 'editItem'],
		['GET',  'new/', SalesOrder\Edit::class, 'editNewOrder'],
	];
	$router = new Mvc\Routers\Router();
	$router->setRoutes($routes);
	$router->setRoutePrefix($page->url);
	$page->body = $router->route();

	if ($router->hasError() === false) {
		$config->scripts->append(Pauldro\ProcessWire\FileHasher::instance()->getHashUrl('scripts/lib/jquery-validate.js'));
		$session->removeFor('response', 'eso');
	}

	if ($config->ajax) {
		echo $page->body;
	} else {
		include __DIR__ . "/basic-page.php";
	}
