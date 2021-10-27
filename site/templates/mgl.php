<?php
	include($modules->get('Mvc')->controllersPath().'vendor/autoload.php');
	use Controllers\Mgl;

	$routes = [
		['GET',  '', Mgl\Menu::class, 'index'],
		'glmain' => [
			['GET',  '', Mgl\Glmain\Menu::class, 'index'],
			'ttm' => [
				['GET',  '', Mgl\Glmain\Ttm::class, 'index'],
				['GET',  'page{pagenbr:\d+}', Mgl\Glmain\Ttm::class, 'index'],
				['POST', '', Mgl\Glmain\Ttm::class, 'handleCRUD'],
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
