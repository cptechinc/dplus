<?php namespace Controllers\Mso\SalesOrder;
// Purl URI Library
use Purl\Url as Purl;
// ProcessWire
use ProcessWire\User;
// Dplus Model
use ConfigSalesOrderQuery, ConfigSalesOrder as ConfigSo;
// Dplus
use Dplus\CodeValidators\Mso as MsoValidator;
use Dplus\DocManagement\Finders as DocFinders;
use Dplus\Session\UserMenuPermissions;
// Controllers
use Controllers\AbstractController;

abstract class Base extends AbstractController {
	const PARENT_MENU_CODE = 'mso';
	private static $validate;
	private static $docm;
	private static $configSo;
	private static $filehasher;

/* =============================================================
	Displays
============================================================= */
	protected static function invalidSo($data) {
		$page   = self::pw('page');
		$config = self::pw('config');
		$page->headline = "Sales Order #$data->ordn not found";
		$html = $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => 'Sales Order Not Found', 'iconclass' => 'fa fa-warning fa-2x', 'message' => "Order # $data->ordn can not be found"]);
		$html .= '<div class="mb-3"></div>';
		$html .= self::lookupForm();
		return $html;
	}

	protected static function soAccessDenied($data) {
		$page   = self::pw('page');
		$config = self::pw('config');
		$page->headline = "Access Denied";
		$html = $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => 'Sales Order Access Denied', 'iconclass' => 'fa fa-warning fa-2x', 'message' => "You don't have access to Order # $data->ordn"]);
		$html .= '<div class="mb-3"></div>';
		$html .= self::lookupForm();
		return $html;
	}

	protected static function lookupForm() {
		return self::pw('config')->twig->render('sales-orders/sales-order/lookup-form.twig');
	}

	protected static function breadCrumbs() {
		return self::pw('config')->twig->render('sales-orders/bread-crumbs.twig');
	}

	protected static function lookupScreen($data) {
		$html  = self::breadCrumbs();
		$html .= self::lookupForm($data);
		return $html;
	}

/* =============================================================
	URLs
============================================================= */
	public static function orderUrl($ordn = '') {
		$url = new Purl(self::pw('pages')->get('pw_template=sales-order-view')->url);
		if ($ordn) {
			$url->query->set('ordn', $ordn);
		}
		return $url->getUrl();
	}

	public static function orderHistoryListUrl($ordn = '') {
		$url = new Purl(self::pw('pages')->get('pw_template=sales-orders-invoices')->url);
		if ($ordn) {
			$url->query->set('focus', $ordn);
		}
		return $url->getUrl();
	}

	public static function orderListUrl($ordn = '') {
		$url = new Purl(self::pw('pages')->get('pw_template=sales-orders')->url);
		if ($ordn) {
			$url->query->set('focus', $ordn);
		}
		return $url->getUrl();
	}

	public static function orderListCustomerUrl($custID, $ordn = '') {
		$url = new Purl(self::pw('pages')->get('pw_template=sales-orders')->url);
		$url->path->add('customer');
		$url->query->set('custID', $custID);
		if ($ordn) {
			$url->query->set('focus', $ordn);
		}
		return $url->getUrl();
	}

	public static function orderPrintUrl($ordn) {
		$url = new Purl(self::orderUrl($ordn));
		$url->path->add('print');
		return $url->getUrl();
	}

	public static function orderEditUrl($ordn) {
		$url = new Purl(self::orderUrl($ordn));
		$url->path->add('edit');
		return $url->getUrl();
	}

	public static function orderEditNewUrl() {
		$url = new Purl(self::orderUrl());
		$url->path->add('edit');
		$url->path->add('new');
		return $url->getUrl();
	}

	public static function orderEditUnlockUrl($ordn) {
		$url = new Purl(self::orderEditUrl($ordn));
		$url->query->set('action', 'unlock-order');
		return $url->getUrl();
	}

	public static function orderPrintInvoiceUrl($ordn) {
		$url = new Purl(self::orderUrl($ordn));
		$url->query->set('action', 'print-invoice');
		return $url->getUrl();
	}

	public static function orderNotesUrl($ordn, $linenbr = '') {
		$url = new Purl(self::orderUrl($ordn));
		$url->path->add('notes');
		$hash = $linenbr > 0 ? "#line-$linenbr" : '';
		return $url->getUrl().$hash;
	}

	public static function orderDocumentsUrl($ordn) {
		$url = new Purl(self::orderUrl($ordn));
		$url->path->add('documents');
		return $url->getUrl();
	}

	public static function documentUrl($ordn, $folder, $doc) {
		$url = new Purl(self::orderDocumentsUrl($ordn));
		$url->query->set('folder', $folder);
		$url->query->set('document', $doc);
		return $url->getUrl();
	}

/* =============================================================
	Supplemental
============================================================= */
	/**
	 * Return Mso Validator
	 * @return MsoValidator
	 */
	protected static function validator() {
		if (empty(self::$validate)) {
			self::$validate = new MsoValidator();
		}
		return self::$validate;
	}

	/**
	 * Return Document Management
	 * @return DocFinders\SalesOrder
	 */
	public static function docm() {
		if (empty(self::$docm)) {
			self::$docm = new DocFinders\SalesOrder();
		}
		return self::$docm;
	}

	/**
	 * Return Sales Order Config
	 * @return ConfigSo
	 */
	protected static function configSo() {
		if (empty(self::$configSo)) {
			self::$configSo = self::pw('modules')->get('ConfigureSo')->config();
		}
		return self::$configSo;
	}

/* =============================================================
	Validator, Module Getters
============================================================= */
	public static function validateUserPermission(User $user = null) {
		if (UserMenuPermissions::instance()->canAccess(self::PARENT_MENU_CODE) === false) {
			return false;
		}
		return parent::validateUserPermission($user);
	}
}
