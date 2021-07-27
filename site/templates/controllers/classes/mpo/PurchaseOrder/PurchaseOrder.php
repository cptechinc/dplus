<?php namespace Controllers\Mpo\PurchaseOrder;
// Dplus Model
use PurchaseOrderQuery, PurchaseOrder as PoModel;
// Dplus Configs
use Dplus\Configs;
// ProcessWire Classes, Modules
use ProcessWire\Page, ProcessWire\WireData;
// Mvc Controllers
use Mvc\Controllers\AbstractController;
use Controllers\Mpo\PurchaseOrder\Base;

class PurchaseOrder extends Base {
/* =============================================================
	Indexes
============================================================= */
	public static function index($data) {
		$fields = ['ponbr|text', 'action|text'];
		self::sanitizeParametersShort($data, $fields);

		if (empty($data->action) === false) {
			return self::handleCRUD($data);
		}

		if (empty($data->ponbr) === false) {
			return self::po($data);
		}
		return self::lookupScreen($data);
	}

	public static function po($data) {
		self::sanitizeParametersShort($data, ['ponbr|ponbr']);
		$page = self::pw('page');
		$config   = self::pw('config');
		$validate = self::validator();

		if ($validate->po($data->ponbr) === false) {
			return self::invalidPo($data);
		}

		$page->headline = "Purchase Order #$data->ponbr";

		return self::purchaseorder($data);
	}




	public static function handleCRUD($data) {
		self::sanitizeParametersShort($data, ['ponbr|ponbr', 'action|text']);
		switch ($data->action) {
			case 'print-invoice':
				self::requestPrintInvoice($data);
				break;
		}
		self::pw('session')->redirect(self::orderUrl($data->ponbr));
	}

/* =============================================================
	Displays
============================================================= */
	private static function purchaseorder($data) {
		self::sanitizeParametersShort($data, ['ponbr|ponbr']);

		if (self::validator()->po($data->ponbr) === false) {
			return self::invalidPo($data);
		}
		$config = self::pw('config');
		$order = PurchaseOrderQuery::create()->findOneByPonbr($data->ponbr);
		$qnotes = self::getQnotes();
		$html = '';
		$html  .= $config->twig->render('purchase-orders/purchase-order/links-header.twig', ['purchaseorder' => $purchaseorder]);
		$html  .= $config->twig->render('purchase-orders/purchase-order/purchase-order.twig', ['config' => self::getConfigs(), 'user' => self::pw('user'), 'purchaseorder' => $purchaseorder, 'qnotes' => $qnotes]);
		$html  .= $config->twig->render('purchase-orders/purchase-order/documents.twig', ['ponbr' => $data->ponbr, 'documents' => self::docm()->getDocumentsPo($data->ponbr)]);
		$html  .= $config->twig->render('purchase-orders/purchase-order/qnotes.twig', ['ponbr' => $data->ponbr, 'qnotes' => $qnotes]);
		$html  .= $config->twig->render('purchase-orders/purchase-order/invoices.twig', ['purchaseorder' => $purchaseorder]);
		return $html;
	}

/* =============================================================
	Supplemental
============================================================= */
	public static function initHooks() { // TODO HOOKS for CI
		$m = self::pw('modules')->get('DpagesMso');

		$m->addHook('Page(pw_template=purchase-order-view|sales-order-edit)::poUrl', function($event) {
			$event->return = self::orderUrl($event->arguments(0));
		});

		$m->addHook('Page(pw_template=purchase-order-view)::poListUrl', function($event) {
			$event->return = self::poListUrl($event->arguments(0));
		});

		$m->addHook('Page(pw_template=purchase-order-view)::apInvoiceUrl', function($event) {
			$event->return = self::apInvoiceUrl($event->arguments(0));
		});




		$m->addHook('Page(pw_template=purchase-order-view|sales-order-edit)::poEditUrl', function($event) {
			$event->return = self::poEditUrl($event->arguments(0));
		});

		$m->addHook('Page(pw_template=purchase-order-view|sales-order-edit)::orderEditUnlockUrl', function($event) {
			$event->return = self::orderEditUnlockUrl($event->arguments(0));
		});

		$m->addHook('Page(pw_template=purchase-order-view|sales-order-edit)::orderNotesUrl', function($event) {
			$event->return = self::orderNotesUrl($event->arguments(0), $event->arguments(1));
		});

		$m->addHook('Page(pw_template=purchase-order-view|sales-order-edit)::orderDocumentsUrl', function($event) {
			$event->return = self::orderDocumentsUrl($event->arguments(0));
		});

		$m->addHook('Page(pw_template=purchase-order-view|sales-order-edit)::documentUrl', function($event) {
			$event->return = self::documentUrl($event->arguments(0), $event->arguments(1), $event->arguments(2));
		});

		$m->addHook('Page(pw_template=purchase-order-view|sales-order-edit)::iiUrl', function($event) {
			$event->return = Ii::iiUrl($event->arguments(0));
		});

		$m->addHook('Page(pw_template=purchase-order-view)::printInvoiceUrl', function($event) {
			$event->return = self::orderPrintInvoiceUrl($event->arguments(0));
		});

		$m->addHook('Page(pw_template=purchase-order-view|sales-order-edit)::ciUrl', function($event) {
			$event->return = Ci::ciUrl($event->arguments(0));
		});

		$m->addHook('Page(pw_template=purchase-order-view|sales-order-edit)::ciShiptoUrl', function($event) {
			$event->return = Ci::ciShiptoUrl($event->arguments(0), $event->arguments(1));
		});
	}

	public static function getQnotes() {
		return self::pw('modules')->get('QnotesPo');
	}

	public static function getConfigs() {
		$configs = new WireData();
		$configs->so = Configs\So::config();
		$configs->po = Configs\Po::config();
		return $configs;
	}
}
