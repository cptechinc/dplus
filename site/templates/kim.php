<?php
	include_once($modules->get('Mvc')->controllersPath().'vendor/autoload.php');
	use Controllers\Mki\Kim as KimController;

	KimController::init();

	$routes = [
		['GET', '', KimController::class, 'index'],
		['GET', 'page{pagenbr:\d+}', KimController::class, 'listKits'],
		['POST', '', KimController::class, 'handleCRUD'],
	];
	$router = new Mvc\Router();
	$router->setRoutes($routes);
	$router->setRoutePrefix($page->url);
	$page->body = $router->route();

	if ($router->hasError() === false) {
		$config->scripts->append(hash_templatefile('scripts/lib/jquery-validate.js'));
		$session->removeFor('response', 'kim');
	}
	include __DIR__ . "/basic-page.php";
