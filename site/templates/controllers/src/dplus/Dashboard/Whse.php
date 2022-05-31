<?php namespace Controllers\Dplus\Dashboard;
// Mvc Controllers
use Controllers\Dplus\Dashboard\Common;

class Whse extends Common {
	public static function dashboard($data) {
		$html = '';
		$html .= self::dashboardUserActions($data);
		$html .= self::dashboardSalesOrders($data);
		return $html;
	}
}
