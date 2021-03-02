<?php
include($modules->get('Mvc')->controllersPath().'vendor/autoload.php');
use Controllers\Mwm\Menu;

$routes = [
	['GET',  '', Menu::class, 'index'],
];

$router = new Mvc\Router();
$router->setRoutes($routes);
$router->setRoutePrefix($page->url);
$response = $router->route();

if ($router->hasError()) {
	$page->body = $response;
}

include __DIR__ . "/basic-page.php";
