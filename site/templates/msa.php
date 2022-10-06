<?php
	include($modules->get('Mvc')->controllersPath().'vendor/autoload.php');
	use Controllers\Msa;

	$routes = [
		['GET',  '', Msa\Menu::class, 'index'],
		'lgrp' => [
			['GET',  '', Msa\Lgrp::class, 'index'],
			['GET',  'page{pagenbr:\d+}', Msa\Lgrp::class, 'index'],
			['POST', '', Msa\Lgrp::class, 'handleCRUD'],
		],
		'noce' => [
			['GET',  '', Msa\Noce::class, 'index'],
			['GET',  'page{pagenbr:\d+}', Msa\Noce::class, 'index'],
			['POST', '', Msa\Noce::class, 'handleCRUD'],
			'ajax' => [
				['GET',  '', Msa\Noce\Ajax::class, 'index'],
				['GET',  'page{pagenbr:\d+}', Msa\Noce\Ajax::class, 'index'],
			]
		],
		'logm' => [
			['GET',  '', Msa\Logm::class, 'index'],
			['GET',  'page{pagenbr:\d+}', Msa\Logm::class, 'index'],
			['POST', '', Msa\Logm::class, 'handleCRUD'],
			'contact' => [
				['GET',  '', Msa\Logm\Contact::class, 'index'],
				['POST',  '', Msa\Logm\Contact::class, 'handleCRUD'],
			],
			'password' => [
				['POST',  '', Msa\Logm\Password::class, 'handleCRUD'],
				['POST',  'web/', Msa\Logm\Password\Web::class, 'handleCRUD'],
			],
		],
		'sysop' => [
			['GET',  '', Msa\Sysop::class, 'index'],
			['GET',  'page{pagenbr:\d+}', Msa\Sysop::class, 'index'],
			['POST', '', Msa\Sysop::class, 'handleCRUD'],
		],
	];

	$router = new Mvc\Routers\Router();
	$router->setRoutes($routes);
	$router->setRoutePrefix($page->url);
	$page->body = $router->route();

	if ($router->hasError() === false) {
		$config->scripts->append($modules->get('FileHasher')->getHashUrl('scripts/lib/jquery-validate.js'));
		// $page->show_breadcrumbs = false;
	}

	if ($config->ajax) {
		echo $page->body;
	} else {
		include __DIR__ . "/basic-page.php";
	}
