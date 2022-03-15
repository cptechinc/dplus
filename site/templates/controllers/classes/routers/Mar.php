<?php namespace Controllers\Routers;
// Mvc Controllers
use Controllers\Mar as Ar;


class Mar extends Base {
	const ROUTES = [
		'armain' => ['', Ar\Armain\Menu::class, 'armainUrl'],
		'ccm'    => ['', Ar\Armain\Menu::class, 'ccmUrl'],
		'cpm'    => ['', Ar\Armain\Menu::class, 'cpmUrl'],
		'crcd'   => ['', Ar\Armain\Menu::class, 'crcdUrl'],
		'crtm'   => ['', Ar\Armain\Menu::class, 'crtmUrl'],
		'spgpm'  => ['', Ar\Armain\Menu::class, 'spgpmUrl'],
		'spm'    => ['', Ar\Armain\Menu::class, 'spmUrl'],
		'sic'    => ['', Ar\Armain\Menu::class, 'sicUrl'],
		'suc'    => ['', Ar\Armain\Menu::class, 'sucUrl'],
		'worm'   => ['', Ar\Armain\Menu::class, 'wormUrl'],
	];
}
