<?php namespace Controllers\Routers;
// Mvc Controllers
use Controllers\Mso\Somain;

class Mso extends Base {
	const ROUTES = [
		'cxm'    => ['', Somain\Menu::class, 'cxmUrl'],
		'lsm'    => ['', Somain\Menu::class, 'lsmUrl'],
		'mfcm'   => ['', Somain\Menu::class, 'mfcmUrl'],
		'rgarc'  => ['', Somain\Menu::class, 'rgarcUrl'],
		'rgasc'  => ['', Somain\Menu::class, 'rgascUrl'],
		'soptm'  => ['', Somain\Menu::class, 'soptmUrl'],
	];
}
