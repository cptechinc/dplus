<?php
	include($modules->get('Mvc')->controllersPath().'vendor/autoload.php');

	use Controllers\Dplus\MainMenu as Menu;

	$routes = [
		['GET',  '', Menu::class, 'index'],
		['GET',  'page{pagenbr:\d+}', Menu::class, 'index'],
	];
	$router = new Mvc\Routers\Router();
	$router->setRoutes($routes);
	$router->setRoutePrefix($page->url);
	$page->body = $router->route();

	if ($router->hasError() === false) {
		// $page->show_breadcrumbs = false;
	}
	include __DIR__ . "/basic-page.php";
