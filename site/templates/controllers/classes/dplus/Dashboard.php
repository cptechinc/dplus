<?php namespace Controllers\Dplus;
// ProcessWire Classes, Modules
use ProcessWire\Page;
// Dplus Filters
use Dplus\Filters\Mso\SalesOrder   as SalesOrderFilter;
use Dplus\Filters\Mso\SalesHistory as SalesHistoryFilter;
// Mvc Controllers
use Mvc\Controllers\AbstractController;
use Controllers\Dplus\Dashboard\Common as DefaultDashboard;

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
}
