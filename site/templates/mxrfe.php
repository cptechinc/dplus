<?php
	include($modules->get('Mvc')->controllersPath().'vendor/autoload.php');
	use Controllers\Map\Mxrfe;

	$routes = [
		['GET',  '', Mxrfe::class, 'index'],
		['GET',  'page{d:\d+}', Mxrfe::class, 'list'],
		['POST', '', Mxrfe::class, 'handleCRUD'],
	];
	$router = new Mvc\Router();
	$router->setRoutes($routes);
	$router->setRoutePrefix($page->url);
	$page->body = $router->route();
	
	if ($router->hasError() === false) {
		$config->scripts->append(hash_templatefile('scripts/lib/jquery-validate.js'));
		$session->removeFor('response', 'mxrfe');
		$page->show_breadcrumbs = false;
	}
	include __DIR__ . "/basic-page.php";
