<?php namespace Controllers\Dplus;
// ProcessWire Classes, Modules
use ProcessWire\Page;
// Dplus Filters
use Dplus\Filters\Mso\SalesOrder   as SalesOrderFilter;
use Dplus\Filters\Mso\SalesHistory as SalesHistoryFilter;
// Mvc Controllers
use Mvc\Controllers\AbstractController;
use Controllers\Dplus\Dashboard\Common as DefaultDashboard;
use Controllers\Mso\SalesOrder\SalesOrder as SoController;

class Dashboard extends AbstractController {
	const DEFAULT = 'common';

	public static function dashboard($data) {
		$role = self::pw('user')->dplusrole;
		if (self::dashboardExists($role) === false) {
			$role = self::DEFAULT;
		}
		$class = self::getClassNameNamespaced($role);
		return $class::dashboard($data);
	}

	public static function dashboardExists($role) {
		return class_exists(self::getClassNameNamespaced($role));
	}

	public static function getClassNameNamespaced($role) {
		$ns    = self::getDashboardNamespace();
		$class = self::getClassName($role);
		return "$ns\\$class";
	}

	public static function getClassName($role) {
		return ucfirst($role);
	}

	public static function getDashboardNamespace() {
		return __NAMESPACE__.'\Dashboard';
	}

	public static function initHooks() {
		$m = self::pw('modules')->get('DpagesMso');

		$m->addHook('Page(template=dashboard)::orderUrl', function($event) {
			$event->return = SoController::orderUrl($event->arguments(0));
		});

		$m->addHook('Page(template=dashboard)::orderListUrl', function($event) {
			$event->return = SoController::orderListUrl($event->arguments(0));
		});

		$m->addHook('Page(template=dashboard)::orderHistoryListUrl', function($event) {
			$event->return = SoController::orderHistoryListUrl($event->arguments(0));
		});
	}
}
