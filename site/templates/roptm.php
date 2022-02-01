<?php
	include_once($modules->get('Mvc')->controllersPath().'vendor/autoload.php');
	use Controllers\Mar\Armain\Roptm;

	Roptm::initHooks();

	$routes = [
		['GET',  '', Roptm::class, 'index'],
		['GET',  'page{pagenbr:\d+}', Roptm::class, 'list'],
		['POST', '', Roptm::class, 'handleCRUD'],
	];
	$router = new Mvc\Routers\Router();
	$router->setRoutes($routes);
	$router->setRoutePrefix($page->url);
	$page->body = $router->route();

	if ($router->hasError() === false) {
		$config->scripts->append(hash_templatefile('scripts/lib/jquery-validate.js'));
		$session->removeFor('response', 'spm');
		$page->show_breadcrumbs = false;
	}
	include __DIR__ . "/basic-page.php";
