<?php
	include($modules->get('Mvc')->controllersPath().'vendor/autoload.php');

	use Controllers\Map\Apmain;

	$routes = [
		['GET',  '', Apmain\Menu::class, 'index'],
		'aoptm' => [
			['GET',  '', Apmain\Aoptm::class, 'index'],
			['GET',  'page{pagenbr:\d+}', Apmain\Aoptm::class, 'index'],
			['POST', '', Apmain\Aoptm::class, 'handleCRUD'],
		],
		'bum' => [
			['GET',  '', Apmain\Bum::class, 'index'],
			['GET',  'page{pagenbr:\d+}', Apmain\Bum::class, 'index'],
			['POST', '', Apmain\Bum::class, 'handleCRUD'],
		],
		'cocom' => [
			['GET',  '', Apmain\Cocom::class, 'index'],
			['GET',  'page{pagenbr:\d+}', Apmain\Cocom::class, 'index'],
			['POST',  '', Apmain\Cocom::class, 'handleCRUD'],
		],
		'mxrfe' => [
			['GET',  '', Apmain\Mxrfe::class, 'index'],
			['GET',  'page{pagenbr:\d+}', Apmain\Mxrfe::class, 'index'],
			['POST', '', Apmain\Mxrfe::class, 'handleCRUD'],
		],
		'vtm' => [
			['GET',  '', Apmain\Vtm::class, 'index'],
			['GET',  'page{pagenbr:\d+}', Apmain\Vtm::class, 'index'],
			['POST', '', Apmain\Vtm::class, 'handleCRUD'],
		],
		'vxm' => [
			['GET',  '', Apmain\Vxm::class, 'index'],
			['GET',  'page{pagenbr:\d+}', Apmain\Vxm::class, 'index'],
			['POST', '', Apmain\Vxm::class, 'handleCRUD'],
		],
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
