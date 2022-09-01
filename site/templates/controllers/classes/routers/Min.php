<?php namespace Controllers\Routers;
// Mvc Controllers
use Controllers\Min\Inproc;
use Controllers\Min\Inmain;

class Min extends Base {
	const ROUTES = [
		'addm'   => ['', Inmain\Menu::class, 'addmUrl'],
		'csccm'  => ['', Inmain\Menu::class, 'csccmUrl'],
		'iarn'   => ['', Inproc\Iarn::class, 'iarnUrl'],
		'iasm'   => ['', Inmain\Menu::class, 'iasmUrl'],
		'igcm'   => ['', Inmain\Menu::class, 'igcmUrl'],
		'igm'    => ['', Inmain\Menu::class, 'igmUrl'],
		'igpm'   => ['', Inmain\Menu::class, 'igpmUrl'],
		'iplm'   => ['', Inmain\Menu::class, 'iplmUrl'],
		'itmp'   => ['', Inmain\Menu::class, 'itmpUrl'],
		'iwhm'   => ['', Inmain\Menu::class, 'iwhmUrl'],
		'msdsm'  => ['', Inmain\Menu::class, 'msdsmUrl'],
		'spit'   => ['', Inmain\Menu::class, 'spitUrl'],
		'stcm'   => ['', Inmain\Menu::class, 'stcmUrl'],
		'umm'    => ['', Inmain\Menu::class, 'ummUrl'],
	];
}
