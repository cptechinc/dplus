<?php namespace Controllers\Routers;
// Mvc Controllers
use Controllers\Mgl as Gl;


class Mgl extends Base {
	const ROUTES = [
		'mgl'    => ['', Gl\Menu::class, 'mglUrl'],
		'glmain' => ['', Gl\Menu::class, 'glmainUrl'],
		'ttm'    => ['', Gl\Glmain\Menu::class, 'ttmUrl'],
		'dtm'    => ['', Gl\Glmain\Menu::class, 'dtmUrl'],
	];
}
