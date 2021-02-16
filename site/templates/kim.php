<?php
	include($modules->get('Mvc')->controllersPath().'vendor/autoload.php');
	use Controllers\Mki\Kim;

	$routes = [
		['GET', '', Kim::class, 'index'],
		['GET', 'page{d:\d+}', Kim::class, 'listKits'],
		['POST', '', Kim::class, 'handleCRUD'],
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
		$session->removeFor('response', 'kim');
	}
	include __DIR__ . "/basic-page.php";
