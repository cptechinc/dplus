<?php namespace Controllers\Routers;
// Mvc Controllers
// use Controllers\Min\Inproc;
use Controllers\Min\Inmain;

class Min extends Base {
	const ROUTES = [
		'addm'   => ['', Inmain\Menu::class, 'addmUrl'],
		'csccm'  => ['', Inmain\Menu::class, 'csccmUrl'],
		'i2i'    => ['', Inmain\Menu::class, 'i2iUrl'],
		'iarn'   => ['', Inmain\Iarn::class, 'iarnUrl'],
		'iasm'   => ['', Inmain\Menu::class, 'iasmUrl'],
		'igcm'   => ['', Inmain\Menu::class, 'igcmUrl'],
		'igm'    => ['', Inmain\Menu::class, 'igmUrl'],
		'igpm'   => ['', Inmain\Menu::class, 'igpmUrl'],
		'iplm'   => ['', Inmain\Menu::class, 'iplmUrl'],
		'ioptm'  => ['', Inmain\Menu::class, 'ioptmUrl'],
		'itmp'   => ['', Inmain\Menu::class, 'itmpUrl'],
		'iwhm'   => ['', Inmain\Menu::class, 'iwhmUrl'],
		'msdsm'  => ['', Inmain\Menu::class, 'msdsmUrl'],
		'spit'   => ['', Inmain\Menu::class, 'spitUrl'],
		'stcm'   => ['', Inmain\Menu::class, 'stcmUrl'],
		'tarm'   => ['', Inmain\Menu::class, 'tarmUrl'],
		'umm'    => ['', Inmain\Menu::class, 'ummUrl'],
		'upcx'   => ['', Inmain\Menu::class, 'upcxUrl'],
	];
}
