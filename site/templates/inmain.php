<?php
	include($modules->get('Mvc')->controllersPath().'vendor/autoload.php');

	use Controllers\Min\Inmain;

	Inmain\Menu::initHooks();

	$routes = [
		['GET',  '', Inmain\Menu::class, 'index'],
		'addm' => [
			['GET',  '', Inmain\Addm::class, 'index'],
			['GET',  'page{pagenbr:\d+}', Inmain\Addm::class, 'index'],
			['POST',  '', Inmain\Addm::class, 'handleCRUD'],
		],
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
