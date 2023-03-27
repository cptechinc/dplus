<?php
	include($modules->get('Mvc')->controllersPath().'vendor/autoload.php');

	use Controllers\Mpo\Poadmn;

	Poadmn\Menu::initHooks();

	$routes = [
		['GET',  '', Poadmn\Menu::class, 'index'],
		'cnfm' => [
			['GET',  '', Poadmn\Cnfm::class, 'index'],
			['GET',  'page{pagenbr:\d+}', Poadmn\Cnfm::class, 'index'],
			['POST',  '', Poadmn\Cnfm::class, 'handleCRUD'],
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
	
	if ($input->get->offsetExists('print')) {
		include __DIR__ . "/blank-page.php";
	} else {
		include __DIR__ . "/basic-page.php";
	}
	
