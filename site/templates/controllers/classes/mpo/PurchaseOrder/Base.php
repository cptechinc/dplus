<?php namespace Controllers\Mpo\PurchaseOrder;
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
	public static function poListUrl($ponbr = '') {
		$url = new Purl(self::pw('pages')->get('pw_template=purchase-orders')->url);
		if ($ponbr) {
			$url->query->set('focus', $ponbr);
		}
		return $url->getUrl();
	}

	public static function poUrl($ponbr = '') {
		$url = new Purl(self::pw('pages')->get('pw_template=purchase-order-view')->url);
		if ($ponbr) {
			$url->query->set('ponbr', $ponbr);
		}
		return $url->getUrl();
	}

	public static function receivedUrl($ponbr) {
		$url = new Purl(self::poUrl($ponbr));
		$url->path->add('received');
		return $url->getUrl();
	}

	public static function apInvoiceUrl($invnb = '') {
		$url = new Purl(self::poUrl());
		$url->path->add('invoice');
		if ($invnbr) {
			$url->query->set('focus', $invnbr);
		}
		return $url->getUrl();
	}

	public static function poEditUrl($ponbr) {
		$url = new Purl(self::poUrl($ponbr));
		$url->path->add('edit');
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

}
