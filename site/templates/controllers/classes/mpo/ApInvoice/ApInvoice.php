<?php namespace Controllers\Mpo\ApInvoice;
// Dplus Model
use ApInvoiceQuery, ApInvoice as PoModel;
// Dplus Configs
use Dplus\Configs;
// ProcessWire Classes, Modules
use ProcessWire\Page, ProcessWire\WireData;
// Mvc Controllers
use Controllers\Mpo\ApInvoice\Base;
use Controllers\Mpo\PurchaseOrder\Lists\PurchaseOrder as PoLists;
use Controllers\Mii\Ii\Ii;

class ApInvoice extends Base {
/* =============================================================
	Indexes
============================================================= */
	public static function index($data) {
		$fields = ['invnbr|text', 'action|text'];
		self::sanitizeParametersShort($data, $fields);
		if (empty($data->invnbr) === false) {
			return self::po($data);
		}
		return self::lookupScreen($data);
	}

	public static function po($data) {
		self::sanitizeParametersShort($data, ['invnbr|invnbr']);
		$page = self::pw('page');
		$config   = self::pw('config');
		$validate = self::validator();

		if ($validate->invoice($data->invnbr) === false) {
			return self::invalidInvoice($data);
		}

		$page->headline = "Invoice #$data->invnbr";

		return self::invoice($data);
	}

/* =============================================================
	Displays
============================================================= */
	private static function lookupScreen($data) {
		self::pw('page')->js .= self::pw('config')->twig->render('purchase-orders/purchase-order/lookup-form.js.twig');
		return self::lookupForm();
	}

	private static function invoice($data) {
		self::sanitizeParametersShort($data, ['invnbr|invnbr']);

		if (self::validator()->invoice($data->invnbr) === false) {
			return self::invalidInvoice($data);
		}
		$config  = self::pw('config');
		$invoice = ApInvoiceQuery::create()->findOneByInvnbr($data->invnbr);
		$docm    = self::docm();

		$html = '';
		// $html .= $config->twig->render('purchase-orders/purchase-order/links-header.twig', ['purchaseorder' => $po, 'docm' => $docm]);
		$html .= $config->twig->render('purchase-orders/invoices/invoice/invoice.twig', ['config' => self::getConfigs(), 'invoice' => $invoice, 'documents' => $docm->getDocuments($data->invnbr)]);
		return $html;
	}

/* =============================================================
	Supplemental
============================================================= */
	public static function initHooks() {
		$m = self::pw('modules')->get('DpagesMpo');

		$m->addHook('Page(pw_template=purchase-orders-invoices)::poUrl', function($event) {
			$event->return = PoLists::poUrl($event->arguments(0));
		});

		$m->addHook('Page(pw_template=purchase-orders-invoices)::iiUrl', function($event) {
			$event->return = Ii::iiUrl($event->arguments(0));
		});

		$m->addHook('Page(pw_template=purchase-orders-invoices)::documentUrl', function($event) {
			$event->return = self::documentUrl($event->arguments(0), $event->arguments(1), $event->arguments(2));
		});

		// $m->addHook('Page(pw_template=purchase-order-view)::poReceivedUrl', function($event) {
		// 	$event->return = self::poReceivedUrl($event->arguments(0));
		// });
		//
		// $m->addHook('Page(pw_template=purchase-order-view)::poListUrl', function($event) {
		// 	$event->return = self::poListUrl($event->arguments(0));
		// });
		//
		// $m->addHook('Page(pw_template=purchase-order-view)::apInvoiceUrl', function($event) {
		// 	$event->return = self::apInvoiceUrl($event->arguments(0));
		// });
		//
		// $m->addHook('Page(pw_template=purchase-order-view)::poDocumentsUrl', function($event) {
		// 	$event->return = self::poDocumentsUrl($event->arguments(0));
		// });
		//
		//
		// $m->addHook('Page(pw_template=purchase-order-view)::poEditUrl', function($event) {
		// 	$event->return = self::poEditUrl($event->arguments(0));
		// });
		//
		// $m->addHook('Page(pw_template=purchase-order-view)::orderEditUnlockUrl', function($event) {
		// 	$event->return = self::orderEditUnlockUrl($event->arguments(0));
		// });
		//
		// $m->addHook('Page(pw_template=purchase-order-view)::orderNotesUrl', function($event) {
		// 	$event->return = self::orderNotesUrl($event->arguments(0), $event->arguments(1));
		// });
		//
		// $m->addHook('Page(pw_template=purchase-order-view)::iiUrl', function($event) {
		// 	$event->return = Ii::iiUrl($event->arguments(0));
		// });
		//
		// $m->addHook('Page(pw_template=purchase-order-view)::printInvoiceUrl', function($event) {
		// 	$event->return = self::orderPrintInvoiceUrl($event->arguments(0));
		// });
		//
		// $m->addHook('Page(pw_template=purchase-order-view)::ciUrl', function($event) {
		// 	$event->return = Ci::ciUrl($event->arguments(0));
		// });
		//
		// $m->addHook('Page(pw_template=purchase-order-view)::ciShiptoUrl', function($event) {
		// 	$event->return = Ci::ciShiptoUrl($event->arguments(0), $event->arguments(1));
		// });
	}

	public static function getConfigs() {
		$configs = new WireData();
		$configs->so = Configs\So::config();
		$configs->po = Configs\Po::config();
		return $configs;
	}
}
