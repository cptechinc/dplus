<?php namespace Controllers\Arproc;
// Purl Library
use Purl\Url as Purl;
// Propel ORM Library
use Propel\Runtime\Util\PropelModelPager;
// Dplus Model
use CustomerQuery, Customer;
// ProcessWire Classes, Modules
use ProcessWire\Page, ProcessWire\Module, ProcessWire\WireData;
// Dplus Filters
use Dplus\Filters;
// Mvc Controllers
use Controllers\Arproc\Base;

class Ecr extends Base {
	const DPLUSPERMISSION = '';
	const SHOWONPAGE      = 10;

/* =============================================================
	Indexes
============================================================= */
	public static function index($data) {
		self::sanitizeParametersShort($data, ['custID|text']);
		if (self::validateUserPermission() === false) {
			return self::displayUserNotPermitted();
		}
		self::initHooks();
		self::pw('page')->headline = "Cash Receipts Entry";
		if (empty($data->custID) === false) {
			return self::customerInvoices($data);
		}
		return self::selectCustomer($data);
	}

	private static function selectCustomer($data) {
		self::pw('page')->js .= self::pw('config')->twig->render('mar/arproc/ecr/customer-form/.js.twig');
		return self::displaySelectCustomer($data);
	}

	private static function customerInvoices($data) {
		$q = CustomerQuery::create();
		$q->filterByCustid($data->custID);
		if (boolval($q->count()) === false) {

		}
		$customer = $q->findOne();
		$filter = new Filters\Mar\ArInvoice();
		$filter->custid($data->custID);
		$invoices = $filter->query->paginate(self::pw('input')->pageNum, self::SHOWONPAGE);
		return self::displayCustomerInvoices($data, $customer, $invoices);
	}


/* =============================================================
	URLs
============================================================= */


/* =============================================================
	Displays
============================================================= */
	private static function displaySelectCustomer($data) {
		$html = '';
		$html .= self::pw('config')->twig->render('mar/arproc/ecr/customer-form/display.twig');
		return $html;
	}

	private static function displayCustomerInvoices($data, Customer $customer, PropelModelPager $invoices) {
		$html = '';
		$html .= self::pw('config')->twig->render('mar/arproc/ecr/customer/display.twig', ['customer' => $customer, 'invoices' => $invoices]);
		return $html;
	}


/* =============================================================
	Init
============================================================= */
	public static function initHooks() {
		$m = self::pw('modules')->get('DpagesMar');

	}
}
