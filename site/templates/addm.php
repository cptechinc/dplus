<?php
	include($modules->get('Mvc')->controllersPath().'vendor/autoload.php');

	use Controllers\Min\Inmain\Addm;

	Addm::initHooks();

	$routes = [
		['GET',  '', Addm::class, 'index'],
		['GET',  'page{pagenbr:\d+}', Addm::class, 'index'],
		['POST', '', Addm::class, 'handleCRUD'],
	];
	$router = new Mvc\Router();
	$router->setRoutes($routes);
	$router->setRoutePrefix($page->url);
	$page->body = $router->route();

	if ($router->hasError() === false) {
		$config->scripts->append($modules->get('FileHasher')->getHashUrl('scripts/lib/jquery-validate.js'));
		Addm::getAddm()->deleteResponse();
		$page->show_breadcrumbs = false;
	}
	include __DIR__ . "/basic-page.php";
