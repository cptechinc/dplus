<?php namespace Controllers\Dplus\Dashboard;
// ProcessWire Classes, Modules
use ProcessWire\Page;
// Dplus Filters
use Dplus\Filters\Mso\SalesOrder   as SalesOrderFilter;
use Dplus\Filters\Mso\SalesHistory as SalesHistoryFilter;
// Mvc Controllers
use Mvc\Controllers\AbstractController;
use Controllers\Dplus\Dashboard\Common;

class Slsrep extends Common {
	public static function dashboard($data) {
		$html = '';
		$html .= self::dashboardUserActions($data);
		$html .= self::dashboardBookings($data);
		$html .= self::dashboardTopSellingCustomers($data);
		$html .= self::dashboardSalesOrders($data);
		$html .= self::dashboardSalesHistory($data);
		$html .= self::dashboardQuotes($data);
		return $html;
	}

	protected static function dashboardBookings($data) {
		$config  = self::pw('config');
		$modules = self::pw('modules');
		$input   = self::pw('input');
		$filter  = $modules->get('FilterBookings');
		$modules->get('DpagesBookings')->init_bookingspage();
		$filter->set_user(self::pw('user'));
		$bookings = $filter->bookings_user->get_bookings($input)->find();
		$bookings_customer = $filter->bookings_customer->get_bookings_by_customer(self::pw('input'))->find();

		$html = '';
		$html .= $config->twig->render("dashboard/components/bookings.twig", ['bookings' => $bookings, 'customers' => $bookings_customer, 'interval' => $filter->bookings_user->interval, 'todaysbookings' => $filter->bookings_user->get_todaysbookingstotal()]);
		self::pw('page')->js .= $config->twig->render("bookings/user/js/bookings.js.twig", ['bookingsdata' => $filter->convert_bookings_for_js($bookings), 'interval' => $filter->bookings_user->interval]);
		return $html;
	}

	protected static function dashboardTopSellingCustomers($data) {
		$config     = self::pw('config');
		$modules    = self::pw('modules');
		$dashboardM = $modules->get('Dashboard');

		$customers = $dashboardM->get_top_x_customers(25);
		$piedata = array();

		foreach ($customers as $cust) {
			$piedata[] = array(
				'label'  => $cust->get_name(),
				'custid' => $cust->custid,
				'value'  => $cust->amountsold
			);
		}
		$html = '';
		$html .= $config->twig->render("dashboard/components/top-selling-customers.twig", ['customers' => $customers]);
		self::pw('page')->js .= $config->twig->render("dashboard/components/js/top-selling.js.twig", ['piedata' => $piedata, 'module_dashboard' => $dashboardM]);
		$config->styles->append(hash_templatefile('styles/lib/morris.css'));
		$config->scripts->append(hash_templatefile('scripts/lib/raphael.js'));
		$config->scripts->append(hash_templatefile('scripts/lib/morris.js'));
		return $html;
	}
}
