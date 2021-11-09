<?php
include($modules->get('Mvc')->controllersPath().'vendor/autoload.php');
use Controllers\Mwm\Menu;

$routes = [
	['GET',  '', Menu::class, 'index'],
];

$router = new Mvc\Router();
$router->setRoutes($routes);
$router->setRoutePrefix($page->url);
$page->body = $router->route();

include __DIR__ . "/basic-page.php";
