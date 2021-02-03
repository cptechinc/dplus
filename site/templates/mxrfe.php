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
	$router->route();
	$config->scripts->append(hash_templatefile('scripts/lib/jquery-validate.js'));
	$session->removeFor('response', 'mxrfe');
	include __DIR__ . "/basic-page.php";
