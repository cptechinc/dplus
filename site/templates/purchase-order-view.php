<?php
	include_once($modules->get('Mvc')->controllersPath().'vendor/autoload.php');
	use Controllers\Mpo\PurchaseOrder\PurchaseOrder as Main;


	//use Controllers\Mpo\PurchaseOrder\Documents;
//	use Controllers\Mpo\PurchaseOrder\Notes;

	Main::initHooks();

	$routes = [
		['GET',  '', Main::class, 'index'],
	//	['GET',  'documents/', Documents::class, 'index'],
		// 'notes' => [
		// 	['GET',   '', Notes::class, 'index'],
		// 	['POST',  '', Notes::class, 'handleCRUD'],
		// ],
	];
	$router = new Mvc\Router();
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
		if ($page->print) {
			$page->show_title = true;

			if ($page->is_pdf()) {
				$page->show_title = false;
			}
			include __DIR__ . "/blank-page.php";
		} else {
			include __DIR__ . "/basic-page.php";
		}
	}
