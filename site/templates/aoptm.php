<?php
	include_once($modules->get('Mvc')->controllersPath().'vendor/autoload.php');
	use Controllers\Map\Apmain\Aoptm;

	Aoptm::initHooks();

	$routes = [
		['GET',  '', Aoptm::class, 'index'],
		['GET',  'page{pagenbr:\d+}', Aoptm::class, 'index'],
		['POST', '', Aoptm::class, 'handleCRUD'],
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
