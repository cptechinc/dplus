<?php
	include($modules->get('Mvc')->controllersPath().'vendor/autoload.php');
	use Controllers\Map\Vxm;

	Vxm::init();

	$routes = [
		['GET',  '', Vxm::class, 'index'],
		['GET',  'page{d:\d+}', Vxm::class, 'list'],
		['POST', '', Vxm::class, 'handleCRUD'],
	];
	$router = new Mvc\Router();
	$router->setRoutes($routes);
	$router->setRoutePrefix($page->url);
	$page->body = $router->route();

	if ($router->hasError() === false) {
		$config->scripts->append(hash_templatefile('scripts/lib/jquery-validate.js'));
		$session->removeFor('response', 'vxm');
		$page->show_breadcrumbs = false;
	}
	include __DIR__ . "/basic-page.php";
