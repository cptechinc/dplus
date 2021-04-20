<?php
	include_once($modules->get('Mvc')->controllersPath().'vendor/autoload.php');
	use Controllers\Wm\Receiving\Receiving as Controller;
	Controller::initHooks();
	
	$routes = [
		['GET',  '', Controller::class, 'index'],
		['POST', '', Controller::class, 'handleCRUD']
	];

	$router = new Mvc\Router();
	$router->setRoutes($routes);
	$router->setRoutePrefix($page->url);
	$page->body = $router->route();
	// $page->show_breadcrumbs = false;

	$config->scripts->append(hash_templatefile('scripts/lib/jquery-validate.js'));

	include __DIR__ . "/basic-page.php";
