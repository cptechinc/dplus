<?php
	include_once($modules->get('Mvc')->controllersPath().'vendor/autoload.php');
	use Controllers\Min\Inmain\Ioptm;

	Ioptm::initHooks();

	$routes = [
		['GET',  '', Ioptm::class, 'index'],
		['GET',  'page{pagenbr:\d+}', Ioptm::class, 'index'],
		['POST', '', Ioptm::class, 'handleCRUD'],
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
