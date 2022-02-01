<?php
	include_once($modules->get('Mvc')->controllersPath().'vendor/autoload.php');
	use Controllers\Mso\Somain\Soptm;

	Soptm::initHooks();

	$routes = [
		['GET',  '', Soptm::class, 'index'],
		['GET',  'page{pagenbr:\d+}', Soptm::class, 'index'],
		['POST', '', Soptm::class, 'handleCRUD'],
	];
	$router = new Mvc\Routers\Router();
	$router->setRoutes($routes);
	$router->setRoutePrefix($page->url);
	$page->body = $router->route();

	if ($router->hasError() === false) {
		$config->scripts->append(hash_templatefile('scripts/lib/jquery-validate.js'));
		$page->show_breadcrumbs = false;
	}
	include __DIR__ . "/basic-page.php";
