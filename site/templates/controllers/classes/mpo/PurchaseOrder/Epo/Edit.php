<?php namespace Controllers\Mpo\PurchaseOrder\Epo;
// Purl URI Library
use Purl\Url as Purl;
// Dplus Model
use PurchaseOrder;
// ProcessWire Classes, Modules
use ProcessWire\Page, ProcessWire\PurchaseOrderEdit as EpoModel;
// Dplus Validators
use Dplus\CodeValidators\Mpo as MpoValidator;
// Mvc Controllers
use Mvc\Controllers\AbstractController;
use Controllers\Mpo\PurchaseOrder\Base;

class Edit extends Base {
/* =============================================================
	Indexes
============================================================= */
	public static function index($data) {
		$fields = ['ponbr|text', 'action|text'];
		$data = self::sanitizeParametersShort($data, $fields);

		if (empty($data->action) === false) {
			return self::handleCRUD($data);
		}

		if (empty($data->ponbr) === false) {
			return self::po($data);
		}
		return self::lookupForm();
	}

	public static function handleCRUD($data) {
		$data = self::sanitizeParametersShort($data, ['action|text', 'vendorID|text', 'ponbr|text']);
		$data->ponbr = PurchaseOrder::get_paddedponumber($data->ponbr);

		if (empty($data->action) === true) {
			self::pw('session')->redirect(self::pw('page')->url, $http301 = false);
		}

		if ($data->action) {
			$epo = self::pw('modules')->get('PurchaseOrderEdit');
			$epo->process_input(self::pw('input'));
			$url = $data->action == 'exit' ? self::poUrl($data->ponbr) : self::poEditUrl($data->ponbr);
			self::pw('session')->redirect($url, $http301 = false);
		}
	}

	private static function po($data) {
		$data = self::sanitizeParametersShort($data, ['ponbr|text', 'load|int']);
		$data->ponbr = PurchaseOrder::get_paddedponumber($data->ponbr);
		$page = self::pw('page');
		$config = self::pw('config');
		$validate = new MpoValidator();

		if ($validate->po($data->ponbr) === false) {
			return self::invalidPo($data);
		}
		$epo = self::pw('modules')->get('PurchaseOrderEdit');

		if ($epo->exists_editable($data->ponbr) === false) {
			if ($data->load > 0) {
				$page->body .= $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => $page->title, 'iconclass' => 'fa fa-warning fa-2x', 'message' => "PO # $data->ponbr can not be loaded for editing"]);
				return $page->body;
			}
			$epo->request_po_edit($data->ponbr);
			$page->fullURL->query->set('load', 1);
			self::pw('session')->redirect($page->fullURL->getUrl(), $http301 = false);
		}
		self::initHooks();
		return self::poEditForm($data, $epo, $page, $config);
	}

/* =============================================================
	Displays
============================================================= */
	private static function poEditForm($data, EpoModel $epo) {
		$epo->init_configs();
		$qnotes = self::pw('modules')->get('QnotesPo');
		$page = self::pw('page');
		$config = self::pw('config');
		$po_edit = $epo->get_editable_header($data->ponbr);
		$po_readonly = $epo->get_purchaseorder($data->ponbr);
		$page->headline = "Editing PO # $data->ponbr";
		$page->search_notesURL = self::pw('pages')->get('pw_template=msa-noce-ajax')->url;
		$page->body .= $config->twig->render('purchase-orders/purchase-order/edit/edit.twig', ['epo' => $epo, 'po' => $po_edit, 'po_readonly' => $po_readonly, 'qnotes' => $qnotes]);
		$page->js   .= $config->twig->render('purchase-orders/purchase-order/edit/js.twig', ['epo' => $epo]);
		$page->js   .= $config->twig->render('purchase-orders/purchase-order/edit/qnotes/js.twig');
		$page->js   .= $config->twig->render('purchase-orders/purchase-order/edit/lookup/js.twig');
		self::pw('session')->removeFor('epo', 'scrollto');
		return $page->body;
	}

/* =============================================================
	URLs
============================================================= */
	public static function poExitUrl($ponbr) {
		$url = new Purl(self::poEditUrl($ponbr));
		$url->query->set('action', 'exit');
		return $url->getUrl();
	}

/* =============================================================
	Hooks
============================================================= */
	public static function initHooks() {
		$m = self::pw('modules')->get('DpagesMpo');

		$m->addHook('Page(pw_template=purchase-order-view)::poExitUrl', function($event) {
			$event->return = self::poExitUrl($event->arguments(0));
		});
	}
}
