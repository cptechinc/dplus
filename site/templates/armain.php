<?php
	include($modules->get('Mvc')->controllersPath().'vendor/autoload.php');

	use Controllers\Mar\Armain;

	$routes = [
		['GET',  '', Armain\Menu::class, 'index'],
		'ccm' => [
			['GET',  '', Armain\Ccm::class, 'index'],
			['GET',  'page{pagenbr:\d+}', Armain\Ccm::class, 'index'],
			['POST', '', Armain\Ccm::class, 'handleCRUD'],
		],
		'cpm' => [
			['GET',  '', Armain\Cpm::class, 'index'],
			['GET',  'page{pagenbr:\d+}', Armain\Cpm::class, 'index'],
			['POST', '', Armain\Cpm::class, 'handleCRUD'],
		],
		'crcd' => [
			['GET',  '', Armain\Crcd::class, 'index'],
			['GET',  'page{pagenbr:\d+}', Armain\Crcd::class, 'index'],
			['POST', '', Armain\Crcd::class, 'handleCRUD'],
		],
		'crtm' => [
			['GET',  '', Armain\Crtm::class, 'index'],
			['GET',  'page{pagenbr:\d+}', Armain\Crtm::class, 'index'],
			['POST', '', Armain\Crtm::class, 'handleCRUD'],
		],
		'ctm' => [
			['GET',  '', Armain\Ctm::class, 'index'],
			['GET',  'page{pagenbr:\d+}', Armain\Ctm::class, 'index'],
			['POST', '', Armain\Ctm::class, 'handleCRUD'],
		],
		'spgpm' => [
			['GET',  '', Armain\Spgpm::class, 'index'],
			['GET',  'page{pagenbr:\d+}', Armain\Spgpm::class, 'index'],
			['POST', '', Armain\Spgpm::class, 'handleCRUD'],
		],
		'spm' => [
			['GET',  '', Armain\Spm::class, 'index'],
			['GET',  'page{pagenbr:\d+}', Armain\Spm::class, 'index'],
			['POST', '', Armain\Spm::class, 'handleCRUD'],
		],
		'sic' => [
			['GET',  '', Armain\Sic::class, 'index'],
			['GET',  'page{pagenbr:\d+}', Armain\Sic::class, 'index'],
			['POST', '', Armain\Sic::class, 'handleCRUD'],
		],
		'suc' => [
			['GET',  '', Armain\Suc::class, 'index'],
			['GET',  'page{pagenbr:\d+}', Armain\Suc::class, 'index'],
			['POST', '', Armain\Suc::class, 'handleCRUD'],
		],
		'worm' => [
			['GET',  '', Armain\Worm::class, 'index'],
			['GET',  'page{pagenbr:\d+}', Armain\Worm::class, 'index'],
			['POST', '', Armain\Worm::class, 'handleCRUD'],
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
