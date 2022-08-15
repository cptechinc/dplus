<?php
	include($modules->get('Mvc')->controllersPath().'vendor/autoload.php');

	use Controllers\Mar\Armain;

	Armain\Menu::initHooks();

	$routes = [
		['GET',  '', Armain\Menu::class, 'index'],
		'ccm' => [
			['GET',  '', Armain\Ccm::class, 'index'],
			['GET',  'page{pagenbr:\d+}', Armain\Ccm::class, 'index'],
			['POST',  '', Armain\Ccm::class, 'handleCRUD'],
		],
		'crtm' => [
			['GET',  '', Armain\Crtm::class, 'index'],
			['GET',  'page{pagenbr:\d+}', Armain\Crtm::class, 'index'],
			['POST',  '', Armain\Crtm::class, 'handleCRUD'],
		],
		'roptm' => [
			['GET',  '', Armain\Roptm::class, 'index'],
			['GET',  'page{pagenbr:\d+}', Armain\Roptm::class, 'index'],
			['POST',  '', Armain\Roptm::class, 'handleCRUD'],
		],
		'spgpm' => [
			['GET',  '', Armain\Spgpm::class, 'index'],
			['GET',  'page{pagenbr:\d+}', Armain\Spgpm::class, 'index'],
			['POST',  '', Armain\Spgpm::class, 'handleCRUD'],
		],
		'worm' => [
			['GET',  '', Armain\Worm::class, 'index'],
			['GET',  'page{pagenbr:\d+}', Armain\Worm::class, 'index'],
			['POST',  '', Armain\Worm::class, 'handleCRUD'],
		],
	];
	$router = new Mvc\Routers\Router();
	$router->setRoutes($routes);
	$router->setRoutePrefix($page->url);
	$page->body = $router->route();

	if ($router->hasError() === false) {
		$config->scripts->append(Pauldro\ProcessWire\FileHasher::instance()->getHashUrl('scripts/lib/jquery-validate.js'));
		$page->show_breadcrumbs = false;
	}
	include __DIR__ . "/basic-page.php";
