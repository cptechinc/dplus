<?php
	include($modules->get('Mvc')->controllersPath().'vendor/autoload.php');
	use Controllers\Dplus\Process as Dprocess;

	$routes = [
		['GET',  '', Dprocess::class, 'index'],
	];

	$router = new Mvc\Router();
	$router->setRoutes($routes);
	$router->setRoutePrefix($page->url);
	$response = $router->route();

	if ($router->hasError()) {
		$page->body = $response;
	}

	if (Dprocess::$permitted === false || Dprocess::$templateExists === false || $router->hasError()) {
		include __DIR__ . "/basic-page.php";
	}

	if (Dprocess::$permitted && Dprocess::$templateExists) {
		include Dprocess::templateFileName($page);
	}
