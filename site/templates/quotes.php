<?php
	use Controllers\Mqo\Quote\Lists\Quote as Base;
	use Controllers\Mqo\Quote\Lists\Customer;

	$routes = [
		['GET',  '', Base::class, 'index'],
		['GET',  'customer', Customer::class, 'index'],
	];
	$router = new Mvc\Routers\Router();
	$router->setRoutes($routes);
	$router->setRoutePrefix($page->url);
	$page->body = $router->route();

	$config->scripts->append(Base::getFileHasher()->getHashUrl('scripts/lib/jquery-validate.js'));

	if ($config->ajax) {
		echo $page->body;
	} else {
		include __DIR__ . "/basic-page.php";
	}
