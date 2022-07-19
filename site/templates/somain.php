<?php
	include($modules->get('Mvc')->controllersPath().'vendor/autoload.php');

	use Controllers\Mso\Somain;

	Somain\Menu::initHooks();

	$routes = [
		['GET',  '', Somain\Menu::class, 'index'],
		'cxm' => [
			['GET',  '', Somain\Cxm::class, 'index'],
			['GET',  'page{pagenbr:\d+}', Somain\Cxm::class, 'index'],
			['POST',  '', Somain\Cxm::class, 'handleCRUD'],
		],
		'lsm' => [
			['GET',  '', Somain\Lsm::class, 'index'],
			['GET',  'page{pagenbr:\d+}', Somain\Lsm::class, 'index'],
			['POST',  '', Somain\Lsm::class, 'handleCRUD'],
		],
		'mfcm' => [
			['GET',  '', Somain\Mfcm::class, 'index'],
			['GET',  'page{pagenbr:\d+}', Somain\Mfcm::class, 'index'],
			['POST',  '', Somain\Mfcm::class, 'handleCRUD'],
		],
		'rgarc' => [
			['GET',  '', Somain\Rgarc::class, 'index'],
			['GET',  'page{pagenbr:\d+}', Somain\Rgarc::class, 'index'],
			['POST',  '', Somain\Rgarc::class, 'handleCRUD'],
		],
		'rgasc' => [
			['GET',  '', Somain\Rgasc::class, 'index'],
			['GET',  'page{pagenbr:\d+}', Somain\Rgasc::class, 'index'],
			['POST',  '', Somain\Rgasc::class, 'handleCRUD'],
		],
		'soptm' => [
			['GET',  '', Somain\Soptm::class, 'index'],
			['GET',  'page{pagenbr:\d+}', Somain\Soptm::class, 'index'],
			['POST',  '', Somain\Soptm::class, 'handleCRUD'],
		],
	];
	$router = new Mvc\Routers\Router();
	$router->setRoutes($routes);
	$router->setRoutePrefix($page->url);
	$page->body = $router->route();

	if ($router->hasError() === false) {
		$config->scripts->append(Pauldro\ProcessWire\FileHasher::instance()->getHashUrl('scripts/lib/jquery-validate.js'));
		$session->removeFor('response', 'poadmin');
		$page->show_breadcrumbs = false;
	}
	include __DIR__ . "/basic-page.php";
