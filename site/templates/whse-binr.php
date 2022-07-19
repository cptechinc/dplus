<?php
	include_once($modules->get('Mvc')->controllersPath().'vendor/autoload.php');
	use Controllers\Wm\Binr;
	Binr::init();

	$routes = [
		['GET',  '', Binr::class, 'index'],
		['POST', '', Binr::class, 'handleCRUD']
	];

	$router = new Mvc\Routers\Router();
	$router->setRoutes($routes);
	$router->setRoutePrefix($page->url);
	$page->body = $router->route();
	// $page->show_breadcrumbs = false;

	$config->scripts->append(Pauldro\ProcessWire\FileHasher::instance()->getHashUrl('scripts/lib/jquery-validate.js'));
	$config->scripts->append(Pauldro\ProcessWire\FileHasher::instance()->getHashUrl('scripts/warehouse/shared.js'));
	$config->scripts->append(Pauldro\ProcessWire\FileHasher::instance()->getHashUrl('scripts/warehouse/binr.js'));

	include __DIR__ . "/basic-page.php";
