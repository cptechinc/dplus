<?php namespace Controllers\Routers;
// Mvc Controllers
use Controllers\Min\Inproc;

class Min extends Base {
	const ROUTES = [
		'iarn' => ['', Inproc\Iarn::class, 'iarnUrl']
	];
}
