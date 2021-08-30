<?php
	include($modules->get('Mvc')->controllersPath().'vendor/autoload.php');

	use Controllers\Mpo\Poadmn;

	Poadmn\Menu::initHooks();

	$routes = [
		['GET',  '', Poadmn\Menu::class, 'index'],
		// 'cnfm' => [
		// 	// ['GET',  '', Inproc\Iarn::class, 'index'],
		// 	// ['GET',  'page{d:\d+}', Inproc\Iarn::class, 'index'],
		// 	// ['POST',  '', Inproc\Iarn::class, 'handleCRUD'],
		// ],
	];
	$router = new Mvc\Router();
	$router->setRoutes($routes);
	$router->setRoutePrefix($page->url);
	$page->body = $router->route();

	if ($router->hasError() === false) {
		$config->scripts->append(hash_templatefile('scripts/lib/jquery-validate.js'));
		$session->removeFor('response', 'poadmin');
		$page->show_breadcrumbs = false;
	}
	include __DIR__ . "/basic-page.php";