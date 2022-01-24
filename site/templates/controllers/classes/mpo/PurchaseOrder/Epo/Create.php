<?php namespace Controllers\Mpo\PurchaseOrder\Epo;
// Purl URI Library
use Purl\Url as Purl;
// Dplus Model
use PurchaseOrder;
// ProcessWire Classes, Modules
use ProcessWire\Page, ProcessWire\PurchaseOrderEdit as EpoCRUD;
// Dplus Validators
use Dplus\CodeValidators\Mpo as MpoValidator;
// Mvc Controllers
use Mvc\Controllers\Controller;
use Controllers\Mpo\PurchaseOrder\Base;

class Create extends Base {
/* =============================================================
	Indexes
============================================================= */
	public static function index($data) {
		$fields = ['ponbr|text', 'action|text', 'vendorID|text'];
		self::sanitizeParametersShort($data, $fields);

		if (empty($data->action) === false) {
			return self::handleCRUD($data);
		}

		if (empty($data->ponbr) === false) {
			return self::loadPo($data);
		}
		return self::epo($data);
	}

	public static function handleCRUD($data) {
		self::sanitizeParametersShort($data, ['action|text', 'vendorID|text']);

		if (empty($data->action) === true) {
			self::pw('session')->redirect(self::pw('page')->url, $http301 = false);
		}

		if ($data->action) {
			$epo = self::pw('modules')->get('PurchaseOrderEdit');
			$url = self::pw('input')->url();

			switch ($data->action) {
				case 'create-po':
					$epo->process_input(self::pw('input'));
					$url = self::verifyCreatedVendorPoUrl($data->vendorID);
					break;
				case 'verify-po-created':
					if ($epo->verifyCreatedPo($data->vendorID)) {
						$url = self::poEditUrl(self::pw('user')->get_lockedID());
					}
					break;
			}
			self::pw('session')->redirect($url, $http301 = false);
		}
	}

	private static function epo($data) {
		self::pw('page')->headline = "EPO";
		self::pw('config')->scripts->append(self::getFileHasher()->getHashUrl('scripts/lib/jquery-validate.js'));
		self::pw('page')->js .= self::pw('config')->twig->render('purchase-orders/epo/.js.twig');
		return self::epoForms($data);
	}

	public static function loadPo($data) {
		self::sanitizeParametersShort($data, ['ponbr|text']);
		$data->ponbr = PurchaseOrder::get_paddedponumber($data->ponbr);
		$validate = new MpoValidator();
		if ($validate->po($data->ponbr)) {
			self::pw('session')->redirect(self::poEditUrl($data->ponbr), $http301 = false);
		}
		self::invalidPoDisplay($data);
	}

/* =============================================================
	URLs
============================================================= */
	public static function verifyCreatedVendorPoUrl($vendorID) {
		$url = new Purl(self::epoUrl());
		$url->query->set('action', 'verify-po-created');
		$url->query->set('vendorID', $vendorID);
		return $url->getUrl();
	}

	public static function createVendorPoUrl($vendorID) {
		$url = new Purl(self::epoUrl());
		$url->query->set('action', 'create-po');
		$url->query->set('vendorID', $vendorID);
		return $url->getUrl();
	}

/* =============================================================
	Displays
============================================================= */
	private static function epoForms($data) {
		return self::pw('config')->twig->render('purchase-orders/epo/form.twig');
	}

	private static function invalidPoDisplay($data) {
		$config = self::pw('config');
		$writer   = self::pw('modules')->get('HtmlWriter');
		self::pw('page')->headline = "PO #$data->ponbr not found";
		$html = '';
		$html .= $config->twig->render('util/alert.twig', ['type' => 'danger','iconclass' => 'fa fa-warning fa-2x', 'title' => "PO #$data->ponbr not found", 'message' => "Check if the Purchase Order Number is correct"]);
		$html .= $writer->div('class=mb-3');
		$html .= self::epoForms();
		return $html;
	}
}
