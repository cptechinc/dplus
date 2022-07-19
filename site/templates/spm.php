<?php
	include_once($modules->get('Mvc')->controllersPath().'vendor/autoload.php');
	use Controllers\Mar\Spm as Controller;

	Controller::init();

	$routes = [
		['GET',  '', Controller::class, 'index'],
		['GET',  'page{pagenbr:\d+}', Controller::class, 'list'],
		['POST', '', Controller::class, 'handleCRUD'],
	];
	$router = new Mvc\Routers\Router();
	$router->setRoutes($routes);
	$router->setRoutePrefix($page->url);
	$page->body = $router->route();

	if ($router->hasError() === false) {
		$config->scripts->append(Pauldro\ProcessWire\FileHasher::instance()->getHashUrl('scripts/lib/jquery-validate.js'));
		$session->removeFor('response', 'spm');
		$page->show_breadcrumbs = false;
	}
	include __DIR__ . "/basic-page.php";
