<?php
	include_once($modules->get('Mvc')->controllersPath().'vendor/autoload.php');
	use Controllers\Wm\Sop\Picking\Picking as Controller;
	Controller::init();

	$routes = [
		['GET',  '', Controller::class, 'index'],
		['POST', '', Controller::class, 'handleCRUD'],
	];

	$router = new Mvc\Router();
	$router->setRoutes($routes);
	$router->setRoutePrefix($page->url);
	$page->body = $router->route();
	$page->show_breadcrumbs = false;

	$config->scripts->append($modules->get('FileHasher')->getHashUrl('scripts/lib/jquery-validate.js'));

	include __DIR__ . "/basic-page.php";
