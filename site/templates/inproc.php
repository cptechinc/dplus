<?php
	include($modules->get('Mvc')->controllersPath().'vendor/autoload.php');

	use Controllers\Min\Inproc;

	Inproc\Menu::initHooks();

	$routes = [
		['GET',  '', Inproc\Menu::class, 'index'],
	];
	$router = new Mvc\Routers\Router();
	$router->setRoutes($routes);
	$router->setRoutePrefix($page->url);
	$page->body = $router->route();

	if ($router->hasError() === false) {
		$config->scripts->append(Pauldro\ProcessWire\FileHasher::instance()->getHashUrl('scripts/lib/jquery-validate.js'));
		$session->removeFor('response', 'inproc');
		$page->show_breadcrumbs = false;
	}
	include __DIR__ . "/basic-page.php";
