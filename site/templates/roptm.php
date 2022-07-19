<?php
	include_once($modules->get('Mvc')->controllersPath().'vendor/autoload.php');
	use Controllers\Mar\Armain\Roptm;

	Roptm::initHooks();

	$routes = [
		['GET',  '', Roptm::class, 'index'],
		['GET',  'page{pagenbr:\d+}', Roptm::class, 'index'],
		['POST', '', Roptm::class, 'handleCRUD'],
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
