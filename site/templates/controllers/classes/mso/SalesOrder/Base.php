<?php namespace Controllers\Mso\SalesOrder;
// Purl URI Library
use Purl\Url as Purl;
// Dplus Model
use ConfigSalesOrderQuery, ConfigSalesOrder as ConfigSo;
// Alias Document Finders
use Dplus\DocManagement\Finders as DocFinders;
// Dplus Classes
use Dplus\CodeValidators\Mso as MsoValidator;
// Mvc Controllers
use Mvc\Controllers\AbstractController;
use Controllers\Mii\Ii;

abstract class Base extends AbstractController {
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
		$config = self::pw('config');
		$html = $config->twig->render('sales-orders/sales-order/lookup-form.twig');
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

	public static function orderEditUnlockUrl($ordn) {
		$url = new Purl(self::orderEditUrl($ordn));
		$url->query->set('action', 'unlock-order');
		return $url->getUrl();
	}

	public static function orderNotesUrl($ordn, $linenbr = '') {
		$url = new Purl(self::orderUrl($ordn));
		$url->path->add('notes');
		$hash = $linenbr > 0 ? "#line-$linenbr" : '';
		return $url->getUrl().$hash;
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

	/**
	 * Return Sales Order Config
	 * @return ProcessWire\FileHasher
	 */
	protected static function getFileHasher() {
		if (empty(self::$filehasher)) {
			self::$filehasher = self::pw('modules')->get('FileHasher');
		}
		return self::$filehasher;
	}
}
