<?php
	include($modules->get('Mvc')->controllersPath().'vendor/autoload.php');
	use Controllers\Mar\Spm;

	$routes = [
		['GET',  '', Spm::class, 'index'],
		['GET',  'page{d:\d+}', Spm::class, 'list'],
		['POST', '', Spm::class, 'handleCRUD'],
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
		$session->removeFor('response', 'spm');
		$page->show_breadcrumbs = false;
	}
	include __DIR__ . "/basic-page.php";
