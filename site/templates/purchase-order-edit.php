<?php
	include($modules->get('Mvc')->controllersPath().'vendor/autoload.php');
	use Controllers\Mpo\Epo;

	$routes = [
		['GET',  '', Epo::class, 'index'],
		['POST', '', Epo::class, 'handleCRUD'],
	];
	$router = new Mvc\Router();
	$router->setRoutes($routes);
	$router->setRoutePrefix($page->url);
	$response = $router->route();

	if ($router->hasError()) {
		$page->body = $response;
	}

	if ($router->hasError() === false) {
		$config->scripts->append(hash_templatefile('scripts/lib/jquery-validate.js'));
		$session->removeFor('response', 'epo');
	}
	include __DIR__ . "/basic-page.php";
