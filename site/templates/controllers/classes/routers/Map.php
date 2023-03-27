<?php namespace Controllers\Routers;
// Mvc Controllers
use Controllers\Map as Ap;


class Map extends Base {
	const ROUTES = [
		'apmain' => ['', Ap\Apmain\Menu::class, 'apmainUrl'],
		'aoptm'  => ['', Ap\Apmain\Menu::class, 'aoptmUrl'],
		'bum'    => ['', Ap\Apmain\Menu::class, 'bumUrl'],
		'mxrfe'  => ['', Ap\Apmain\Menu::class, 'mxrfeUrl'],
		'ptm'    => ['', Ap\Apmain\Menu::class, 'ptmUrl'],
		'vtm'    => ['', Ap\Apmain\Menu::class, 'vtmUrl'],
		'vxm'    => ['', Ap\Apmain\Menu::class, 'vxmUrl'],
	];
}
