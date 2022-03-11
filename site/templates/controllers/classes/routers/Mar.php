<?php namespace Controllers\Routers;
// Mvc Controllers
use Controllers\Mar as Ar;


class Mar extends Base {
	const ROUTES = [
		'armain' => ['', Ar\Armain\Menu::class, 'armainUrl'],
		'ccm'    => ['', Ar\Armain\Menu::class, 'ccmUrl'],
		'crtm'   => ['', Ar\Armain\Menu::class, 'crtmUrl'],
		'spgpm'  => ['', Ar\Armain\Menu::class, 'spgpmUrl'],
		'suc'    => ['', Ar\Armain\Menu::class, 'sucUrl'],
	];
}
