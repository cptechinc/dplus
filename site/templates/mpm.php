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
				['GET',  'page{pagenbr:\d+}', Mpm\Pmmain\Bmm::class, 'index'],
				['POST', '', Mpm\Pmmain\Bmm::class, 'handleCRUD'],
			],
			'dcm' => [
				['GET',  '', Mpm\Pmmain\Dcm::class, 'index'],
				['GET',  'page{pagenbr:\d+}', Mpm\Pmmain\Dcm::class, 'index'],
				['POST', '', Mpm\Pmmain\Dcm::class, 'handleCRUD'],
			],
			'rcm' => [
				['GET',  '', Mpm\Pmmain\Rcm::class, 'index'],
				['GET',  'page{pagenbr:\d+}', Mpm\Pmmain\Rcm::class, 'index'],
				['POST', '', Mpm\Pmmain\Rcm::class, 'handleCRUD'],
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
