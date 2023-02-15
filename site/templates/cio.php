<?php
	include($modules->get('Mvc')->controllersPath().'vendor/autoload.php');

	use Controllers\Mci\Ciadmn;
	
	$routes = [
		['GET',  '', Ciadmn\Cio::class, 'index'],
		['GET',  'page{pagenbr:\d+}', Ciadmn\Cio::class, 'index'],
		['POST', '', Ciadmn\Cio::class, 'process'],
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
