<?php namespace Controllers\Mso\SalesOrder;
// Purl URI Library
use Purl\Url as Purl;

use Dplus\Mso\So\SalesOrder\Details as SalesOrderItems;
use Dplus\Qnotes\Sord as Qnotes;

class Item extends Base {
/* =============================================================
	Indexes
============================================================= */
	public static function index($data) {
		$fields = ['ordn|ordn', 'linenbr|int', 'action|text'];
		self::sanitizeParametersShort($data, $fields);

		if (empty($data->ordn)) {
			
		}

		if (empty($data->ordn) === false) {
			return self::item($data);
		}
		// return self::listItems($data);
	}

	private static function item($data) {
		self::sanitizeParametersShort($data, ['ordn|ordn', 'linenbr|int']);
		$validate = self::validator();

		self::pw('page')->headline = "Sales Order #$data->ordn Line #$data->linenbr";

		if ($validate->invoice($data->ordn)) {
			return self::salesHistory($data);
		}
		return self::salesOrder($data);
	}

	private static function salesOrder($data) {
		self::sanitizeParametersShort($data, ['ordn|ordn', 'linenbr|int']);
		$validate = self::validator();
		if ($validate->orderAccess($data->ordn, self::pw('user')) === false) {
			return self::soAccessDenied($data);
		}
		return self::displaySalesOrder($data);
	}

	private static function salesHistory($data) {
		self::sanitizeParametersShort($data, ['ordn|ordn', 'linenbr|int']);
		$validate = self::validator();

		if ($validate->orderAccess($data->ordn, self::pw('user')) === false) {
			return self::soAccessDenied($data);
		}
	}

/* =============================================================
	Displays
============================================================= */
	private static function displaySalesOrder($data) {
		$config = self::pw('config');

		$SALESORDERITEMS = SalesOrderItems::instance();
		$qnotes = Qnotes::instance();
		$item = $SALESORDERITEMS->detailLine($data->ordn, $data->linenbr, $createIfNotFound = true) ;
		return $config->twig->render('sales-orders/sales-order/item/page/display.twig', ['qnotes' => $qnotes, 'item' => $item]);
	}

/* =============================================================
	URLs
============================================================= */
	public static function url() {
		return self::pw('pages')->get('pw_template=sales-order-view')->url . 'item/';
	}

	public static function itemUrl($ordn = '', $linenbr = 0) {
		$url = new Purl(self::url());
		if ($ordn) {
			$url->query->set('ordn', $ordn);
			if ($linenbr) {
				$url->query->set('linenbr', $linenbr);
			}
		}
		return $url->getUrl();
	}

/* =============================================================
	Supplemental
============================================================= */
	public static function initHooks() {
		$m = self::pw('modules')->get('DpagesMso');

	}
}
