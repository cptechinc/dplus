<?php namespace Controllers\Routers;
// Mvc Controllers
use Controllers\Mpm as Pm;


class Mpm extends Base {
	const ROUTES = [
		'mpm'    => ['', Pm\Menu::class, 'mpmUrl'],
		'pmmain' => ['', Pm\Menu::class, 'pmmainUrl'],
		'bmm'    => ['', Pm\Pmmain\Menu::class, 'bmmUrl'],
		'dcm'    => ['', Pm\Pmmain\Menu::class, 'dcmUrl'],
		'rcm'    => ['', Pm\Pmmain\Menu::class, 'rcmUrl'],
	];
}
