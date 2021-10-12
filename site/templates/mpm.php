<?php
	include($modules->get('Mvc')->controllersPath().'vendor/autoload.php');
	use Controllers\Mpm;

	Mpm\Menu::initHooks();

	$routes = [
		['GET',  '', Mpm\Menu::class, 'index'],
		'pmmain' => [
			['GET',  '', Mpm\Pmmain\Menu::class, 'index'],
			'bmm' => [
				['GET',  '', Mpm\Pmmain\Bmm::class, 'index'],
				['POST', '', Mpm\Pmmain\Bmm::class, 'handleCRUD'],
			]
		]
	];
	$router = new Mvc\Router();
	$router->setRoutes($routes);
	$router->setRoutePrefix($page->url);
	$page->body = $router->route();

	if ($router->hasError() === false) {
		$config->scripts->append($modules->get('FileHasher')->getHashUrl('scripts/lib/jquery-validate.js'));
		$page->show_breadcrumbs = false;
	}
	include __DIR__ . "/basic-page.php";
