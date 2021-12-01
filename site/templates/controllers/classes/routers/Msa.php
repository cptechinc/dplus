<?php namespace Controllers\Routers;
// Mvc Controllers
use Controllers\Msa\Menu;

class Msa extends Base {
	const ROUTES = [
		'lgrp' => ['', Menu::class, 'lgrpUrl'],
		'noce' => ['', Menu::class, 'noceUrl'],
		'logm' => ['', Menu::class, 'logmUrl']
	];
}
