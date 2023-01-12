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
		'cocom' => [
			['GET',  '', Armain\Cocom::class, 'index'],
			['GET',  'page{pagenbr:\d+}', Armain\Cocom::class, 'index'],
			['POST',  '', Armain\Cocom::class, 'handleCRUD'],
		],
		'cpm' => [
			['GET',  '', Armain\Cpm::class, 'index'],
			['GET',  'page{pagenbr:\d+}', Armain\Cpm::class, 'index'],
			['POST',  '', Armain\Cpm::class, 'handleCRUD'],
		],
		'crcd' => [
			['GET',  '', Armain\Crcd::class, 'index'],
			['GET',  'page{pagenbr:\d+}', Armain\Crcd::class, 'index'],
			['POST',  '', Armain\Crcd::class, 'handleCRUD'],
		],
		'crtm' => [
			['GET',  '', Armain\Crtm::class, 'index'],
			['GET',  'page{pagenbr:\d+}', Armain\Crtm::class, 'index'],
			['POST',  '', Armain\Crtm::class, 'handleCRUD'],
		],
		'csv' => [
			['GET',  '', Armain\Csv::class, 'index'],
			['GET',  'page{pagenbr:\d+}', Armain\Csv::class, 'index'],
			['POST',  '', Armain\Csv::class, 'handleCRUD'],
		],
		'ctm' => [
			['GET',  '', Armain\Ctm::class, 'index'],
			['GET',  'page{pagenbr:\d+}', Armain\Ctm::class, 'index'],
			['POST',  '', Armain\Ctm::class, 'handleCRUD'],
		],
		'cuc' => [
			['GET',  '', Armain\Cuc::class, 'index'],
			['GET',  'page{pagenbr:\d+}', Armain\Cuc::class, 'index'],
			['POST',  '', Armain\Cuc::class, 'handleCRUD'],
		],
		'mtm' => [
			['GET',  '', Armain\Mtm::class, 'index'],
			['GET',  'page{pagenbr:\d+}', Armain\Mtm::class, 'index'],
			['POST',  '', Armain\Mtm::class, 'handleCRUD'],
		],
		'pty3' => [
			['GET',  '', Armain\Pty3::class, 'index'],
			['GET',  'page{pagenbr:\d+}', Armain\Pty3::class, 'index'],
			['POST',  '', Armain\Pty3::class, 'handleCRUD'],
		],
		'roptm' => [
			['GET',  '', Armain\Roptm::class, 'index'],
			['GET',  'page{pagenbr:\d+}', Armain\Roptm::class, 'index'],
			['POST',  '', Armain\Roptm::class, 'handleCRUD'],
		],
		'sic' => [
			['GET',  '', Armain\Sic::class, 'index'],
			['GET',  'page{pagenbr:\d+}', Armain\Sic::class, 'index'],
			['POST',  '', Armain\Sic::class, 'handleCRUD'],
		],
		'spgpm' => [
			['GET',  '', Armain\Spgpm::class, 'index'],
			['GET',  'page{pagenbr:\d+}', Armain\Spgpm::class, 'index'],
			['POST',  '', Armain\Spgpm::class, 'handleCRUD'],
		],
		'spm' => [
			['GET',  '', Armain\Spm::class, 'index'],
			['GET',  'page{pagenbr:\d+}', Armain\Spm::class, 'index'],
			['POST',  '', Armain\Spm::class, 'handleCRUD'],
		],
		'suc' => [
			['GET',  '', Armain\Suc::class, 'index'],
			['GET',  'page{pagenbr:\d+}', Armain\Suc::class, 'index'],
			['POST',  '', Armain\Suc::class, 'handleCRUD'],
		],
		'tm' => [
			['GET',  '', Armain\Tm::class, 'index'],
			['GET',  'page{pagenbr:\d+}', Armain\Tm::class, 'index'],
			['POST',  '', Armain\Tm::class, 'handleCRUD'],
		],
		'trm' => [
			['GET',  '', Armain\Trm::class, 'index'],
			['GET',  'page{pagenbr:\d+}', Armain\Trm::class, 'index'],
			['POST',  '', Armain\Trm::class, 'handleCRUD'],
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
	}

	if ($input->get->offsetExists('print')) {
		include __DIR__ . "/blank-page.php";
	} else {
		include __DIR__ . "/basic-page.php";
	}
	
