<?php
	include_once($modules->get('Mvc')->controllersPath().'vendor/autoload.php');

	use Controllers\Min\Upcx;
	Upcx::initHooks();

	$routes = [
		['GET',  '', Upcx::class, 'index'],
		['GET',  'page{pagenbr:\d+}', Upcx::class, 'list'],
		['POST', '', Upcx::class, 'handleCRUD'],
	];
	$router = new Mvc\Routers\Router();
	$router->setRoutes($routes);
	$router->setRoutePrefix($page->url);
	$page->body = $router->route();

	if ($router->hasError() === false) {
		$config->scripts->append(hash_templatefile('scripts/lib/jquery-validate.js'));
		$session->removeFor('response', 'upcx');
		$page->show_breadcrumbs = false;
	}
	include __DIR__ . "/basic-page.php";
