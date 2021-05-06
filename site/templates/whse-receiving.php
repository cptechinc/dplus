<?php
	include_once($modules->get('Mvc')->controllersPath().'vendor/autoload.php');
	use Controllers\Wm\Receiving\Receiving as Controller;
	Controller::initHooks();

	$routes = [
		['GET',  '', Controller::class, 'index'],
		['POST', '', Controller::class, 'handleCRUD'],
		['GET',  'create/', Controller::class, 'createPo'],
		['GET',  'load/', Controller::class, 'loadPo'],
	];

	$router = new Mvc\Router();
	$router->setRoutes($routes);
	$router->setRoutePrefix($page->url);
	$page->body = $router->route();
	$page->show_breadcrumbs = false;

	$config->scripts->append($modules->get('FileHasher')->getHashUrl('scripts/lib/jquery-validate.js'));

	include __DIR__ . "/basic-page.php";
