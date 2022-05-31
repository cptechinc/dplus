<?php namespace Controllers\Mpr\Prman;
// Mvc Controllers
use Mvc\Controllers\Controller;

use Controllers\Mpr\Base as BaseMpr;

abstract class Base extends BaseMpr {
	const DPLUSPERMISSION = 'prman';
	const TITLE_MENU = 'Maintenance';
}
