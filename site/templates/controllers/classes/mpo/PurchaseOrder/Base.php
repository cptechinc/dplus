<?php namespace Controllers\Mpo\PurchaseOrder;
// Purl URI Library
use Purl\Url as Purl;
// Dplus Model
use PurchaseOrder;
// ProcessWire Classes, Modules
use ProcessWire\Page;
// Dplus Document Management
use Dplus\DocManagement\Finders as DocFinders;
// Dplus Configs
use Dplus\Configs;
// Dplus Validators
use Dplus\CodeValidators\Mpo as MpoValidator;
// Mvc Controllers
use Mvc\Controllers\AbstractController;
use Controllers\Mpo\ApInvoice\Base as ApInvoice;

abstract class Base extends AbstractController {
	private static $validate;
	private static $docm;

/* =============================================================
	URLs
============================================================= */
	public static function poListUrl($ponbr = '') {
		$url = new Purl(self::pw('pages')->get('pw_template=purchase-orders')->url);
		if ($ponbr) {
			$url->query->set('focus', $ponbr);
		}
		return $url->getUrl();
	}

	public static function poListVendorUrl($vendorID, $ponbr = '') {
		$url = new Purl(self::poListUrl($ponbr));
		$url->path->add('vendor');
		$url->query->set('vendorID', $vendorID);
		return $url->getUrl();
	}

	public static function poUrl($ponbr = '') {
		$url = new Purl(self::pw('pages')->get('pw_template=purchase-order-view')->url);
		if ($ponbr) {
			$url->query->set('ponbr', $ponbr);
		}
		return $url->getUrl();
	}

	public static function poReceivedUrl($ponbr) {
		$url = new Purl(self::poUrl($ponbr));
		$url->path->add('received');
		return $url->getUrl();
	}

	public static function apInvoiceUrl($invnbr = '') {
		return ApInvoice::invoiceUrl($invnbr);
	}

	public static function poEditUrl($ponbr) {
		$url = new Purl(self::poUrl($ponbr));
		$url->path->add('edit');
		return $url->getUrl();
	}

	public static function poDocumentsUrl($ponbr) {
		$url = new Purl(self::poUrl($ponbr));
		$url->path->add('documents');
		return $url->getUrl();
	}

	public static function documentUrl($ponbr, $folder, $doc) {
		$url = new Purl(self::poDocumentsUrl($ponbr));
		$url->query->set('folder', $folder);
		$url->query->set('document', $doc);
		return $url->getUrl();
	}

	public static function epoUrl() {
		$url = new Purl(self::poListUrl());
		$url->path->add('epo');
		return $url->getUrl();
	}

	public static function poCreateUrl() {
		return self::epoUrl();
	}

/* =============================================================
	Displays
============================================================= */
	protected static function invalidPo($data) {
		$html = '';
		$html .= self::pw('config')->twig->render('util/alert.twig', ['type' => 'danger', 'title' => 'Purchase Order Not Found', 'iconclass' => 'fa fa-warning fa-2x', 'message' => "PO # $data->ponbr can not be found"]);
		$html .= '<div class="mb-3"></div>';
		$html .= self::lookupForm();
		return $html;
	}

	protected static function lookupForm() {
		return self::pw('config')->twig->render('purchase-orders/purchase-order/lookup-form.twig');
	}

	protected static function breadCrumbs() {
		return self::pw('config')->twig->render('purchase-orders/bread-crumbs.twig');
	}

/* =============================================================
	Supplemental
============================================================= */
	/**
	 * Return Mpo Validator
	 * @return MpoValidator
	 */
	protected static function validator() {
		if (empty(self::$validate)) {
			self::$validate = new MpoValidator();
		}
		return self::$validate;
	}

	/**
	 * Return Document Management
	 * @return DocFinders\PurchaseOrder
	 */
	public static function docm() {
		if (empty(self::$docm)) {
			self::$docm = new DocFinders\PurchaseOrder();
		}
		return self::$docm;
	}

	/**
	 * Return PO config
	 * @return ConfigPo
	 */
	public static function configPo() {
		return Configs\Po::config();
	}

	public static function qnotes() {
		return self::pw('modules')->get('QnotesPo');
	}
}
