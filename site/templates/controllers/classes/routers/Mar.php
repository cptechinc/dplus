<?php namespace Controllers\Routers;
// Mvc Controllers
use Controllers\Mar as Ar;


class Mar extends Base {
	const ROUTES = [
		'armain' => ['', Ar\Armain\Menu::class, 'armainUrl'],
		'ccm'    => ['', Ar\Armain\Menu::class, 'ccmUrl'],
		'cocom'  => ['', Ar\Armain\Menu::class, 'cocomUrl'],
		'cpm'    => ['', Ar\Armain\Menu::class, 'cpmUrl'],
		'crcd'   => ['', Ar\Armain\Menu::class, 'crcdUrl'],
		'crtm'   => ['', Ar\Armain\Menu::class, 'crtmUrl'],
		'cuc'    => ['', Ar\Armain\Menu::class, 'cucUrl'],
		'roptm'  => ['', Ar\Armain\Menu::class, 'roptmUrl'],
		'spgpm'  => ['', Ar\Armain\Menu::class, 'spgpmUrl'],
		'worm'   => ['', Ar\Armain\Menu::class, 'wormUrl'],
	];
}
