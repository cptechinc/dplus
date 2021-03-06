<?php
	include($modules->get('Mvc')->controllersPath().'vendor/autoload.php');
	use Controllers\Min\Itm\Xrefs\Upcx;

	$routes = [
		['GET',  '', Upcx::class, 'index'],
		['GET',  'page{d:\d+}', Upcx::class, 'list'],
		['POST', '', Upcx::class, 'handleCRUD'],
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
		$session->removeFor('response', 'upcx');
		$page->show_breadcrumbs = false;
	}

	include __DIR__ . "/basic-page.php";
