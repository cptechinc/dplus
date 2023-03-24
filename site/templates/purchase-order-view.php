<?php
	include_once($modules->get('Mvc')->controllersPath().'vendor/autoload.php');
	use Controllers\Mpo\PurchaseOrder as App;
	use Controllers\Mpo\PurchaseOrder\PurchaseOrder as Main;
	use Controllers\Mpo\PurchaseOrder\Epo;

	Main::initHooks();

	$routes = [
		['GET',  '', Main::class, 'index'],
		['POST',  '', Main::class, 'handleCRUD'],
		['GET',  'documents/', App\Documents::class, 'index'],
		'notes' => [
			['GET',   '', App\Notes::class, 'index'],
			['POST',  '', App\Notes::class, 'handleCRUD'],
		],
		'edit' => [
			['GET',   '', Epo\Edit::class, 'index'],
			['POST',  '', Epo\Edit::class, 'handleCRUD'],
		],
		['GET',  'received/', App\Received::class, 'index'],
	];
	$router = new Mvc\Routers\Router();
	$router->setRoutes($routes);
	$router->setRoutePrefix($page->url);
	$page->body = $router->route();

	$page->show_breadcrumbs = false;

	if ($router->hasError() === false) {
		$config->scripts->append($modules->get('FileHasher')->getHashUrl('scripts/lib/jquery-validate.js'));
	}

	if ($config->ajax) {
		echo $page->body;
	} else {
		if ($input->lastSegment() == 'print' || $input->get->offsetExists('print')) {
			$page->show_title = true;

			if ($page->is_pdf()) {
				$page->show_title = false;
			}
			include __DIR__ . "/blank-page.php";
		} else {
			include __DIR__ . "/basic-page.php";
		}
	}
