<?php
	include($modules->get('Mvc')->controllersPath().'vendor/autoload.php');
	use Controllers\Mpr;

	$routes = [
		['GET',  '', Mpr\Menu::class, 'index'],
		'prman' => [
			['GET',  '', Mpr\Prman\Menu::class, 'index'],
			'src' => [
				['GET',  '', Mpr\Prman\Src::class, 'index'],
				['GET',  'page{pagenbr:\d+}', Mpr\Prman\Src::class, 'index'],
				['POST', '', Mpr\Prman\Src::class, 'handleCRUD'],
			]
		]
	];
	$router = new Mvc\Routers\Router();
	$router->setRoutes($routes);
	$router->setRoutePrefix($page->url);
	$page->body = $router->route();

	if ($router->hasError() === false) {
		$config->scripts->append($modules->get('FileHasher')->getHashUrl('scripts/lib/jquery-validate.js'));
		$page->show_breadcrumbs = false;
	}

	if ($input->get->offsetExists('print')) {
		include __DIR__ . "/blank-page.php";
	} else {
		include __DIR__ . "/basic-page.php";
	}
