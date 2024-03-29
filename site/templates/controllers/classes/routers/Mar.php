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
		'csv'    => ['', Ar\Armain\Menu::class, 'csvUrl'],
		'ctm'    => ['', Ar\Armain\Menu::class, 'ctmUrl'],
		'cuc'    => ['', Ar\Armain\Menu::class, 'cucUrl'],
		'mtm'    => ['', Ar\Armain\Menu::class, 'MtmUrl'],
		'pty3'   => ['', Ar\Armain\Menu::class, 'Pty3Url'],
		'roptm'  => ['', Ar\Armain\Menu::class, 'roptmUrl'],
		'sic'    => ['', Ar\Armain\Menu::class, 'sicUrl'],
		'spgpm'  => ['', Ar\Armain\Menu::class, 'spgpmUrl'],
		'spm'    => ['', Ar\Armain\Menu::class, 'spmUrl'],
		'suc'    => ['', Ar\Armain\Menu::class, 'sucUrl'],
		'tm'     => ['', Ar\Armain\Menu::class, 'tmUrl'],
		'trm'    => ['', Ar\Armain\Menu::class, 'trmUrl'],
		'trmg'   => ['', Ar\Armain\Menu::class, 'trmgUrl'],
		'worm'   => ['', Ar\Armain\Menu::class, 'wormUrl'],
	];
}
