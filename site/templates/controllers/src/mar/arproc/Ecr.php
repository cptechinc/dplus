<?php namespace Controllers\Arproc;
// Purl Library
use Purl\Url as Purl;
// Propel ORM Library
use Propel\Runtime\Util\PropelModelPager;
// Dplus Model
use CustomerQuery, Customer;
use ArInvoiceQuery, ArInvoice;
use ArPaymentPendingQuery, ArPaymentPending;
use ArCashHeadQuery, ArCashHead;
// ProcessWire Classes, Modules
use ProcessWire\Page, ProcessWire\Module, ProcessWire\WireData;
// Dplus Filters
use Dplus\Filters;
// Dplus Arproc
use Dplus\Mar\Arproc;
// Mvc Controllers
use Controllers\Arproc\Base;

class Ecr extends Base {
	const DPLUSPERMISSION = '';
	const SHOWONPAGE      = 10;

/* =============================================================
	Indexes
============================================================= */
	public static function index($data) {
		self::sanitizeParametersShort($data, ['custID|text', 'invnbr|text']);
		if (self::validateUserPermission() === false) {
			return self::displayUserNotPermitted();
		}
		self::initHooks();
		self::pw('page')->headline = "Cash Receipts Entry";
		if (empty($data->invnbr) === false) {
			return self::payment($data);
		}
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
		$ecr = Arproc\Ecr::instance($data->custID);
		if ($ecr->headerIsLocked()) {
			return self::displayCustomerIsLocked($data);
		}
		$filter = new Filters\Mar\ArInvoice();
		$filter->custid($data->custID);
		$invoices = $filter->query->paginate(self::pw('input')->pageNum, self::SHOWONPAGE);
		$header = $ecr->header->header($data->custID);

		self::pw('page')->headline = "ECR: {$header->customer->name} Invoices";
		return self::displayCustomerInvoices($data, $header, $invoices);
	}

	private static function payment($data) {
		$ecr = Arproc\Ecr::instance();
		if ($ecr->payments->exists($data->invnbr) === false) {

		}
		$payment = $ecr->payments->payment($data->invnbr);
		$invoice = ArInvoiceQuery::create()->filterByInvoicenumber($data->invnbr)->findOne();
		self::pw('page')->js .= self::pw('config')->twig->render('mar/arproc/ecr/customer/payment/pay.js.twig');
		return self::displayPayment($data, $payment, $invoice);
	}

/* =============================================================
	URLs
============================================================= */
	public static function invoiceUrl($invnbr) {
		$url = new Purl(Menu::ecrUrl());
		$url->query->set('invnbr', $invnbr);
		return $url->getUrl();
	}

/* =============================================================
	Displays
============================================================= */
	private static function displaySelectCustomer($data) {
		$html = '';
		$html .= self::pw('config')->twig->render('mar/arproc/ecr/customer-form/display.twig');
		return $html;
	}

	private static function displayCustomerInvoices($data, ArCashHead $header, PropelModelPager $invoices) {
		$ecr = Arproc\Ecr::instance();

		$html = '';
		$html .= self::pw('config')->twig->render('mar/arproc/ecr/customer/display.twig', ['ecr' => $ecr, 'ecrSession' => $ecr->session, 'arCashHeader' => $header, 'customer' => $header->customer, 'invoices' => $invoices]);
		return $html;
	}

	private static function displayPayment($data, ArPaymentPending $payment, ArInvoice $invoice) {
		$ecr = Arproc\Ecr::instance();

		$html = '';
		$html .= self::pw('config')->twig->render('mar/arproc/ecr/customer/payment/display.twig', ['ecr' => $ecr, 'customer' => $invoice->customer, 'payment' => $payment, 'invoice' => $invoice]);
		return $html;
	}

	private static function displayCustomerIsLocked($data) {
		$ecr = Arproc\Ecr::instance();
		$ecrHeader = $ecr->header->header();

		$html = '';
		$html .= self::pw('config')->twig->render('util/alert.twig', ['type' => 'warning', 'title' => "Customer $data->custID is locked", 'iconclass' => 'fa fa-lock fa-2x', 'message' => "Customer $data->custID is locked by $ecrHeader->clerkid"]);
		return $html;
	}


/* =============================================================
	Init
============================================================= */
	public static function initHooks() {
		$m = self::pw('modules')->get('DpagesMar');

		$m->addHook('Page(pw_template=arproc)::invoiceUrl', function($event) {
			$event->return = self::invoiceUrl($event->arguments(0));
		});
	}

/* =============================================================
	Supplmental
============================================================= */
	private static function getCustomer($custID) {
		return CustomerQuery::create()->findOneById($custID);
	}
}
