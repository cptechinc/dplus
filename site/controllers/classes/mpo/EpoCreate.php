<?php namespace Controllers\Mpo;
// Dplus Model
use PurchaseOrder;
// ProcessWire Classes, Modules
use ProcessWire\Page, ProcessWire\PurchaseOrderEdit as EpoModel;
// Dplus Validators
use Dplus\CodeValidators\Mpo as MpoValidator;
// Mvc Controllers
use Mvc\Controllers\AbstractController;

class EpoCreate extends AbstractController {
	public static function index($data) {
		$fields = ['ponbr|text', 'action|text', 'vendorID|text'];
		$data = self::sanitizeParametersShort($data, $fields);

		if (empty($data->action) === false) {
			return self::handleCRUD($data);
		}

		if (empty($data->ponbr) === false) {
			return self::loadPo($data);
		}
		return self::epoForms();
	}

	public static function handleCRUD($data) {
		$data = self::sanitizeParametersShort($data, ['action|text', 'vendorID|text']);

		if (empty($data->action) === true) {
			self::pw('session')->redirect(self::pw('page')->url, $http301 = false);
		}

		if ($data->action) {
			$epo = self::pw('modules')->get('PurchaseOrderEdit');
			$epo->process_input(self::pw('input'));
			self::pw('session')->redirect(self::pw('page')->fullURL->getUrl(), $http301 = false);
		}
	}

	public static function loadPo($data) {
		$data = self::sanitizeParametersShort($data, ['ponbr|text']);
		$data->ponbr = PurchaseOrder::get_paddedponumber($data->ponbr);
		$page = self::pw('page');
		$validate = new MpoValidator();
		if ($validate->po($data->ponbr)) {
			self::pw('session')->redirect($page->po_editURL($data->ponbr), $http301 = false);
		}
		$config = self::pw('config');
		$html   = self::pw('modules')->get('HtmlWriter');
		$page->headline = "PO #$data->ponbr not found";
		$page->body .= $config->twig->render('util/alert.twig', ['type' => 'danger','iconclass' => 'fa fa-warning fa-2x', 'title' => "PO #$data->ponbr not found", 'message' => "Check if the Purchase Order Number is correct"]);
		$page->body .= $html->div('class=mb-3');
		return self::epoForms();
	}

	public static function epoForms() {
		$page = self::pw('page');
		$config = self::pw('config');
		$page->body .= $config->twig->render('purchase-orders/epo/form.twig');
		$page->js   .= $config->twig->render('purchase-orders/epo/.js.twig');
	}
}
