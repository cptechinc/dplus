<?php namespace Controllers\Routers;
// Mvc Controllers
use Controllers\Mpr as Pr;


class Mpr extends Base {
	const ROUTES = [
		'mpr'    => ['', Pr\Menu::class, 'mpmUrl'],
		'prman' => ['', Pr\Menu::class, 'prmanUrl'],
		'src'    => ['', Pr\Prman\Menu::class, 'srcUrl'],
	];
}
