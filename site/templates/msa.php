<?php
	include($modules->get('Mvc')->controllersPath().'vendor/autoload.php');
	use Controllers\Mgl;

	$routes = [
		['GET',  '', Mgl\Menu::class, 'index'],
	];
	$router = new Mvc\Router();
	$router->setRoutes($routes);
	$router->setRoutePrefix($page->url);
	$page->body = $router->route();

	if ($router->hasError() === false) {
		$config->scripts->append($modules->get('FileHasher')->getHashUrl('scripts/lib/jquery-validate.js'));
		$page->show_breadcrumbs = false;
	}
	include __DIR__ . "/basic-page.php";
