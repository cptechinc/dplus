<?php
	include($modules->get('Mvc')->controllersPath().'vendor/autoload.php');

	use Controllers\Min\Inproc\Menu;

	Menu::initHooks();

	$routes = [
		['GET',  '', Menu::class, 'index'],
	];
	$router = new Mvc\Router();
	$router->setRoutes($routes);
	$router->setRoutePrefix($page->url);
	$page->body = $router->route();

	if ($router->hasError() === false) {
		$config->scripts->append(hash_templatefile('scripts/lib/jquery-validate.js'));
		$session->removeFor('response', 'inproc');
		$page->show_breadcrumbs = false;
	}
	include __DIR__ . "/basic-page.php";
