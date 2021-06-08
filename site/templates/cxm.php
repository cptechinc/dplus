<?php
	include($modules->get('Mvc')->controllersPath().'vendor/autoload.php');
	use Controllers\Mso\Cxm;

	Cxm::init();

	$routes = [
		['GET',  '', Cxm::class, 'index'],
		['GET',  'page{d:\d+}', Cxm::class, 'list'],
		['POST', '', Cxm::class, 'handleCRUD'],
	];
	$router = new Mvc\Router();
	$router->setRoutes($routes);
	$router->setRoutePrefix($page->url);
	$page->body = $router->route();

	if ($router->hasError() === false) {
		$config->scripts->append(hash_templatefile('scripts/lib/jquery-validate.js'));
		$session->removeFor('response', 'cxm');
		$page->show_breadcrumbs = false;
	}
	include __DIR__ . "/basic-page.php";
