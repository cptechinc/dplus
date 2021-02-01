<?php
	include($modules->get('Mvc')->controllersPath().'vendor/autoload.php');
	use Controllers\Mki\Kim;

	$routes = [
		['GET', $page->url.'', Kim::class, 'index'],
		['GET', $page->url.'page{d:\d+}', Kim::class, 'listKits'],
		['POST', $page->url.'', Kim::class, 'handleCRUD'],
	];
	$router = new Mvc\Router();
	$router->setRoutes($routes);
	$router->route();
	$config->scripts->append(hash_templatefile('scripts/lib/jquery-validate.js'));
	$session->removeFor('response', 'kim');
	include __DIR__ . "/basic-page.php";
