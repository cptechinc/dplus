<?php namespace Controllers\Routers;
// Mvc Controllers
use Controllers\Mpo\Poadmn;

class Mpo extends Base {
	const ROUTES = [
		'cnfm' => ['', Poadmn\Cnfm::class, 'cnfmUrl']
	];
}
