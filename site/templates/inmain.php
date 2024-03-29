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
		'csccm' => [
			['GET',  '', Inmain\Csccm::class, 'index'],
			['GET',  'page{pagenbr:\d+}', Inmain\Csccm::class, 'index'],
			['POST',  '', Inmain\Csccm::class, 'handleCRUD'],
		],
		'i2i' => [
			['GET',  '', Inmain\I2i::class, 'index'],
			['GET',  'page{pagenbr:\d+}', Inmain\I2i::class, 'index'],
			['POST',  '', Inmain\I2i::class, 'handleCRUD'],
		],
		'iarn' => [
			['GET',  '', Inmain\Iarn::class, 'index'],
			['GET',  'page{pagenbr:\d+}', Inmain\Iarn::class, 'index'],
			['POST',  '', Inmain\Iarn::class, 'handleCRUD'],
		],
		'iasm' => [
			['GET',  '', Inmain\Iasm::class, 'index'],
			['GET',  'page{pagenbr:\d+}', Inmain\Iasm::class, 'index'],
			['POST',  '', Inmain\Iasm::class, 'handleCRUD'],
		],
		'igcm' => [
			['GET',  '', Inmain\Igcm::class, 'index'],
			['GET',  'page{pagenbr:\d+}', Inmain\Igcm::class, 'index'],
			['POST',  '', Inmain\Igcm::class, 'handleCRUD'],
		],
		'igm' => [
			['GET',  '', Inmain\Igm::class, 'index'],
			['GET',  'page{pagenbr:\d+}', Inmain\Igm::class, 'index'],
			['POST',  '', Inmain\Igm::class, 'handleCRUD'],
		],
		'igpm' => [
			['GET',  '', Inmain\Igpm::class, 'index'],
			['GET',  'page{pagenbr:\d+}', Inmain\Igpm::class, 'index'],
			['POST',  '', Inmain\Igpm::class, 'handleCRUD'],
		],
		'iplm' => [
			['GET',  '', Inmain\Iplm::class, 'index'],
			['GET',  'page{pagenbr:\d+}', Inmain\Iplm::class, 'index'],
			['POST',  '', Inmain\Iplm::class, 'handleCRUD'],
		],
		'ioptm' => [
			['GET',  '', Inmain\Ioptm::class, 'index'],
			['GET',  'page{pagenbr:\d+}', Inmain\Ioptm::class, 'index'],
			['POST',  '', Inmain\Ioptm::class, 'handleCRUD'],
		],
		'itmimg' => [
			['GET',  '', Inmain\Itmimg::class, 'index'],
			['GET',  'page{pagenbr:\d+}', Inmain\Itmimg::class, 'index'],
			['POST',  '', Inmain\Itmimg::class, 'handleCRUD'],
		],
		'itmp' => [
			['GET',  '', Inmain\Itmp::class, 'index'],
			['GET',  'page{pagenbr:\d+}', Inmain\Itmp::class, 'index'],
			['POST',  '', Inmain\Itmp::class, 'handleCRUD'],
		],
		'iwhm' => [
			['GET',  '', Inmain\Iwhm::class, 'index'],
			['GET',  'page{pagenbr:\d+}', Inmain\Iwhm::class, 'index'],
			['POST',  '', Inmain\Iwhm::class, 'handleCRUD'],
		],
		'msdsm' => [
			['GET',  '', Inmain\Msdsm::class, 'index'],
			['GET',  'page{pagenbr:\d+}', Inmain\Msdsm::class, 'index'],
			['POST',  '', Inmain\Msdsm::class, 'handleCRUD'],
		],
		'spit' => [
			['GET',  '', Inmain\Spit::class, 'index'],
			['GET',  'page{pagenbr:\d+}', Inmain\Spit::class, 'index'],
			['POST',  '', Inmain\Spit::class, 'handleCRUD'],
		],
		'stcm' => [
			['GET',  '', Inmain\Stcm::class, 'index'],
			['GET',  'page{pagenbr:\d+}', Inmain\Stcm::class, 'index'],
			['POST',  '', Inmain\Stcm::class, 'handleCRUD'],
		],
		'tarm' => [
			['GET',  '', Inmain\Tarm::class, 'index'],
			['GET',  'page{pagenbr:\d+}', Inmain\Tarm::class, 'index'],
			['POST',  '', Inmain\Tarm::class, 'handleCRUD'],
		],
		'umm' => [
			['GET',  '', Inmain\Umm::class, 'index'],
			['GET',  'page{pagenbr:\d+}', Inmain\Umm::class, 'index'],
			['POST',  '', Inmain\Umm::class, 'handleCRUD'],
		],
		'upcx' => [
			['GET',  '', Inmain\Upcx::class, 'index'],
			['GET',  'page{pagenbr:\d+}', Inmain\Upcx::class, 'index'],
			['POST',  '', Inmain\Upcx::class, 'handleCRUD'],
		],
	];
	$router = new Mvc\Routers\Router();
	$router->setRoutes($routes);
	$router->setRoutePrefix($page->url);
	$page->body = $router->route();

	if ($router->hasError() === false) {
		$config->scripts->append(Pauldro\ProcessWire\FileHasher::instance()->getHashUrl('scripts/lib/jquery-validate.js'));
		// $page->show_breadcrumbs = false;
	}
	
	if ($input->get->offsetExists('print')) {
		include __DIR__ . "/blank-page.php";
	} else {
		include __DIR__ . "/basic-page.php";
	}
	
