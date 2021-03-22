<?php namespace Controllers\Dplus\Dashboard;
// Mvc Controllers
use Controllers\Dplus\Dashboard\Whse;

class Whsmgr extends Whse {
	public static function dashboard($data) {
		$html = '';
		$html .= self::dashboardUserActions($data);
		$html .= self::dashboardSalesOrders($data);
		return $html;
	}
}
