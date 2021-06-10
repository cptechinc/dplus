<?php namespace Controllers\Dplus\Dashboard;
// ProcessWire Classes, Modules
use ProcessWire\Page;
// Dplus Filters
use Dplus\Filters\Mso\SalesOrder   as SalesOrderFilter;
use Dplus\Filters\Mso\SalesHistory as SalesHistoryFilter;
// Mvc Controllers
use Mvc\Controllers\AbstractController;

class Common extends AbstractController {
	public static function dashboard($data) {
		$html = '';
		$html .= self::dashboardUserActions($data);
		$html .= self::dashboardSalesOrders($data);
		$html .= self::dashboardSalesHistory($data);
		$html .= self::dashboardQuotes($data);
		return $html;
	}

	protected static function dashboardUserActions($data) {
		$config = self::pw('config');
		$html = '';
		$filter = self::pw('modules')->get('FilterUserActions');

		if ($filter->data['use_useractions']) {
			$params['module_useractions'] = $filter;
			$params['user']  = self::pw('user');
			$params['input'] = self::pw('input');
			$html .= $config->twig->render("dashboard/components/user-actions.twig", $params);
		}
		return $html;
	}

	protected static function dashboardSalesOrders($data) {
		$pages  = self::pw('pages');
		$input  = self::pw('input');
		$config = self::pw('config');
		$filter = new SalesOrderFilter();
		$filter->user(self::pw('user'));
		$filter->filterInput($input);
		$filter->query->orderByDate_ordered('DESC');
		$orders = $filter->query->paginate($input->pageNum, 10);

		$params = [
			'orders'         => $orders,
			'count'          => $orders->getNbResults(),
			'url_order'      => $pages->get('pw_template=sales-order-view')->url,
			'url_order_list' => $pages->get('pw_template=sales-orders')->url
		];
		$html = '';
		$html .= $config->twig->render("dashboard/components/sales-orders.twig", $params);
		return $html;
	}

	protected static function dashboardSalesHistory($data) {
		$pages  = self::pw('pages');
		$input  = self::pw('input');
		$config = self::pw('config');
		$filter = new SalesHistoryFilter();
		$filter->user(self::pw('user'));
		//$filter->filterInput($input);
		$filter->query->orderByDate_ordered('DESC');
		$orders = $filter->query->paginate($input->pageNum, 10);

		$params = [
			'invoices'       => $orders,
			'count'          => $orders->getNbResults(),
			'url_invoice'      => $pages->get('pw_template=sales-order-view')->url,
			'url_invoice_list' => $pages->get('pw_template=sales-history-orders')->url
		];
		$html = '';
		$html .= $config->twig->render("dashboard/components/sales-history.twig", $params);
		return $html;
	}

	protected static function dashboardQuotes($data) {
		$pages  = self::pw('pages');
		$input  = self::pw('input');
		$config = self::pw('config');
		$filter = self::pw('modules')->get('FilterQuotes');
		$filter->init_query(self::pw('user'));
		$filter->filter_query($input);
		$query = $filter->get_query();
		$query->orderByDate_quoted('DESC');
		$quotes = $query->paginate($input->pageNum, 10);

		$params = [
			'quotes'         => $quotes,
			'count'          => $quotes->getNbResults(),
			'url_quote'      => $pages->get('pw_template=quote-view')->url,
			'url_quote_list' => $pages->get('pw_template=quotes')->url
		];
		$html = '';
		$html .= $config->twig->render("dashboard/components/quotes.twig", $params);
		return $html;
	}
}
