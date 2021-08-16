<?php
	include($modules->get('Mvc')->controllersPath().'vendor/autoload.php');

	use Controllers\Min\Inmain\I2i;

	I2i::initHooks();

	$routes = [
		['GET',  '', I2i::class, 'index'],
		['GET',  'page{d:\d+}', I2i::class, 'list'],
		['POST', '', I2i::class, 'handleCRUD'],
	];
	$router = new Mvc\Router();
	$router->setRoutes($routes);
	$router->setRoutePrefix($page->url);
	$page->body = $router->route();

	if ($router->hasError() === false) {
		$config->scripts->append(hash_templatefile('scripts/lib/jquery-validate.js'));
		$session->removeFor('response', 'i2i');
		$page->show_breadcrumbs = false;
	}
	include __DIR__ . "/basic-page.php";
