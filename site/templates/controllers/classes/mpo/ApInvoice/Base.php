<?php namespace Controllers\Mpo\ApInvoice;
// Purl URI Library
use Purl\Url as Purl;
// Dplus Model
use PurchaseOrder;
// ProcessWire Classes, Modules
use ProcessWire\Page;
// Dplus Document Management
use Dplus\DocManagement\Finders as DocFinders;
// Dplus Validators
use Dplus\CodeValidators\Mpo as MpoValidator;
// Mvc Controllers
use Mvc\Controllers\AbstractController;

abstract class Base extends AbstractController {
	private static $validate;
	private static $docm;

/* =============================================================
	URLs
============================================================= */
	public static function apInvoiceListUrl($invnbr = '') {
		$url = new Purl(self::pw('pages')->get('pw_template=purchase-orders-invoices')->url);
		if ($invnbr) {
			$url->query->set('focus', $invnbr);
		}
		return $url->getUrl();
	}

	public static function apInvoiceUrl($invnbr = '') {
		$url = new Purl(self::apInvoiceListUrl());
		$url->path->add('invoice');
		if ($invnbr) {
			$url->query->set('invnbr', $invnbr);
		}
		return $url->getUrl();
	}


	public static function apInvoiceDocumentsUrl($invnbr) {
		$url = new Purl(self::apInvoiceUrl($invnbr));
		$url->path->add('documents');
		return $url->getUrl();
	}

	public static function documentUrl($invnbr, $folder, $doc) {
		$url = new Purl(self::poDocumentsUrl($invnbr));
		$url->query->set('folder', $folder);
		$url->query->set('document', $doc);
		return $url->getUrl();
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
	 * @return DocFinders\ApInvoice
	 */
	public static function docm() {
		if (empty(self::$docm)) {
			self::$docm = new DocFinders\ApInvoice();
		}
		return self::$docm;
	}

	public static function qnotes() {
		return self::pw('modules')->get('QnotesPo');
	}
}
