<?php
	include($modules->get('Mvc')->controllersPath().'vendor/autoload.php');

	use Controllers\Mvi\Vi\Admin;

	Admin\Vio::initHooks();

	$routes = [
		['GET',  '', Admin\Vio::class, 'index'],
		['GET',  'page{pagenbr:\d+}', Admin\Vio::class, 'index'],
		['POST', '', Admin\Vio::class, 'process'],
	];
	$router = new Mvc\Routers\Router();
	$router->setRoutes($routes);
	$router->setRoutePrefix($page->url);
	$page->body = $router->route();

	if ($router->hasError() === false) {
		$config->scripts->append(Pauldro\ProcessWire\FileHasher::instance()->getHashUrl('scripts/lib/jquery-validate.js'));
		$page->show_breadcrumbs = false;
	}
	include __DIR__ . "/basic-page.php";
