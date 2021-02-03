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
	$router->route();
	$config->scripts->append(hash_templatefile('scripts/lib/jquery-validate.js'));
	$session->removeFor('response', 'kim');
	include __DIR__ . "/basic-page.php";
