<?php namespace Controllers\Wm\Receiving;

use stdClass;
// Purl Library
use Purl\Url as Purl;
// Propel Classes
use Propel\Runtime\ActiveQuery\Criteria;
// Dplus Model
use WarehouseQuery, Warehouse;
use PurchaseOrder;
// Dpluso Model
use BininfoQuery, Bininfo;
use WhsesessionQuery, Whsesession;
use WhseitemphysicalcountQuery, Whseitemphysicalcount;
// ProcessWire Classes, Modules
use ProcessWire\Page, ProcessWire\Module, ProcessWire\WireData;
use Processwire\SearchInventory, Processwire\WarehouseManagement,ProcessWire\HtmlWriter;
// Dplus Configs
use Dplus\Configs as Dconfigs;
// Dplus Validators
use Dplus\CodeValidators\Mpo as MpoValidator;
// Dplus CRUD
use Dplus\Wm\Receiving\Receiving as ReceivingCRUD;
use Dplus\Wm as Wm;
// Mvc Controllers
use Controllers\Wm\Base;

class Receiving extends Base {
	/** @var ReceivingCRUD */
	static private $receiving;

	/** @var MpoValidator */
	static private $validatempo;

/* =============================================================
	Indexes
============================================================= */
	static public function index($data) {
		$fields = ['scan|text', 'action|text', 'ponbr|ponbr'];
		self::sanitizeParametersShort($data, $fields);

		if (empty($data->action) === false) {
			return self::handleCRUD($data);
		}

		if (empty($data->ponbr) === false) {
			return self::receiving($data);
		}

		return self::poForm($data);
	}

	static public function handleCRUD($data) {
		self::sanitizeParametersShort($data, ['action|text', 'ponbr|ponbr', 'scan|text']);

		$validate = self::getValidatorMpo();
		if ($validate->po($data->ponbr) === false) {
			self::redirect(self::receivingUrl($data->ponbr), $http301 = false);
		}

		$m = self::getReceiving($data->ponbr);

		// REDIRECT
		switch ($data->action) {
			case 'search-inventory':
				self::redirect(self::receivingScanUrl($data->ponbr, $data->scan, $data->binID), $http301 = false);
				break;
			case 'verify-submit':
				$q = WhseitemphysicalcountQuery::create();
				$q->filterBySessionid(self::getSessionid());
				$q->filterByScan($scan);
				$q->findOne();
				$item = $q->findOne();
				$url = self::receivingUrl($data->ponbr);
				if ($item->has_error()) {
					$url = self::receivingScanUrl($data->ponbr, $data->scan);
				}
				self::redirect($url, $http301 = false);
				break;
			default:
				self::redirect(self::receivingUrl($data->ponbr), $http301 = false);
				break;
		}
	}

	static public function receiving($data) {
		$fields = ['action|text', 'ponbr|ponbr'];
		self::sanitizeParametersShort($data, $fields);

		if (empty($data->ponbr)) {
			self::redirect(self::receivingUrl(), $http301 = false);
		}

		if (empty($data->action) === false) {
			return self::handleCRUD($data);
		}

		$validate = self::getValidatorMpo();

		if ($validate->po($data->ponbr) === false) {
			return self::invalidPo($data);
		}
		$receiving = self::getReceiving();
		$receiving->setPonbr($data->ponbr);
		$po = $receiving->getPurchaseorder();

		if ($po->count_receivingitems() === 0) {
			$receiving->requestPoInit();
		}

		if ($po->count_receivingitems() === 0) {
			return self::noItemsToReceive($data);
		}

		if ($data->scan) {
			self::processScan($data);
		}
		$config = self::pw('config');
		$whsesession = self::getWhseSession();
		$warehouse   = self::getCurrentUserWarehouse();
		$receiving   = self::getReceiving($data->ponbr);

		$html = new WireData();
		$html->header    = self::purchaseOrderHeader($data, $po);
		$html->items     = self::purchaseOrderItems($data, $po);
		$html->scan      = self::scanForm($data);
		$html->modalbins = $config->twig->render('warehouse/inventory/bins-modal.twig', ['warehouse' => $warehouse]);
		$jsconfig = array('warehouse' => array('id' => $whsesession->whseid, 'binarrangement' => $warehouse->get_binarrangementdescription(), 'bins' => $warehouse->get_bins()->toArray()), 'items' => $receiving->get_purchaseorder_recevingdetails_js(), 'config_receive' => $receiving->get_jsconfig());
		$html->js = $config->twig->render('util/js-variables.twig', ['variables' => $jsconfig]);
		self::pw('page')->js .= $config->twig->render('warehouse/inventory/receiving/js.twig', ['ponbr' => $data->ponbr, 'linenbr' => self::pw('session')->getFor('receiving', 'removed-line')]);
		return $config->twig->render('warehouse/inventory/receiving/display.twig', ['html' => $html]);
	}

/* =============================================================
	Data Processing
============================================================= */
	static protected function processScan($data) {
		$fields = ['recno|int'];
		self::sanitizeParametersShort($data, $fields);
		$q = WhseitemphysicalcountQuery::create();
		$q->filterBySessionid(self::getSessionid());
		$q->filterByScan($data->scan);

		if ($q->count() == 1) {
			self::processScanSingle($data);
		}

		if ($data->recno) {
			$q->filterByRecno($recno, Criteria::ALT_NOT_EQUAL);
			$q->delete();

			self::redirect(self::receivingScanUrl($data->ponbr, $data->scan), $http301 = false);
		}

	}

	static protected function processScanSingle($data) {
		$q = WhseitemphysicalcountQuery::create();
		$q->filterBySessionid(self::getSessionid());
		$q->filterByScan($data->scan);

		if ($q->count() != 1) {
			return false;
		}

		$physicalitem = $q->findOne();

		if ($physicalitem->has_error() === false) {
			$session = self::pw('session');

			if ($session->getfor('receiving', 'received')) {
				$received = $session->getfor('receiving', 'received');

				if ($received->itemid == $physicalitem->itemid) {
					$physicalitem->setBin($received->binid);
				}
			}

			if ($data->binID) {
				$physicalitem->setBin($data->binID);
			}
			$receiving = self::getReceiving();
			$receiving->setPonbr($data->ponbr);
			// AUTO SUBMIT
			if ($receiving->canAutoSubmit($physicalitem)) {
				$physicalitem->save();
				$receiving->autoSubmitScan($data->scan);
				self::redirect(self::receivingSubmitVerifyUrl($data->ponbr, $data->scan), $http301 = false);
			}
		}
	}
/* =============================================================
	URLs
============================================================= */
	static public function receivingUrl($ponbr = '') {
		$url = new Purl(self::pw('pages')->get('pw_template=whse-receiving')->url);
		if ($ponbr) {
			$url->query->set('ponbr', $ponbr);
		}
		return $url->getUrl();
	}

	static public function receivingInitUrl($ponbr) {
		$url = new Purl(self::receivingUrl($ponbr));
		$url->query->set('action', 'init-receiving');
		return $url->getUrl();
	}

	static public function receivingScanUrl($ponbr, $scan, $binID = '') {
		$url = new Purl(self::receivingUrl($ponbr));
		$url->query->set('scan', $scan);
		if ($binID) {
			$url->query->set('binID', $binID);
		}
		return $url->getUrl();
	}

	static public function receivingAutoSubmitUrl($ponbr, $scan) {
		$url = new Purl(self::receivingScanUrl($ponbr, $scan));
		$url->query->set('action', 'autosubmit-scan');
		return $url->getUrl();
	}

	static public function receivingSubmitVerifyUrl($ponbr, $scan) {
		$url = new Purl(self::receivingScanUrl($ponbr, $scan));
		$url->query->set('action', 'verify-submit');
		return $url->getUrl();
	}

/* =============================================================
	Displays
============================================================= */
	static public function poForm($data) {
		return self::pw('config')->twig->render('warehouse/inventory/receiving/po-form.twig');
	}

	static public function invalidPo($data) {
		$page   = self::pw('page');
		$config = self::pw('config');
		$page->headline = "Sales Order #$data->ponbr not found";
		$html = $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => 'Purchase Order Not Found', 'iconclass' => 'fa fa-warning fa-2x', 'message' => "Order # $data->ponbr can not be found"]);
		$html .= '<div class="mb-3"></div>';
		$html .= self::poForm($data);
		return $html;
	}

	static public function noItemsToReceive($data) {
		$writer = self::getHtmlWriter();
		$html   = self::pw('config')->twig->render('util/alert.twig', ['type' => 'warning', 'title' => 'Error!', 'iconclass' => 'fa fa-warning fa-2x', 'message' => 'There are no Items to Receive']);
		$html   .= $writer->div('class=mb-3');
		$html   .= $writer->a("href=". self::receivingUrl() ."|class=btn btn-primary", "Exit");
		return $html;
	}

	static protected function purchaseOrderHeader($data, PurchaseOrder $po) {
		return self::pw('config')->twig->render('warehouse/inventory/receiving/po-header.twig', ['purchaseorder' => $po]);
	}

	static protected function purchaseOrderItems($data, PurchaseOrder $po) {
		$receiving = self::getReceiving();
		$receiving->setPonbr($data->ponbr);
		$config = self::pw('config');
		$html = '';

		if (file_exists($config->paths->templates."twig/warehouse/inventory/receiving/$config->company/po-items.twig")) {
			$html .= $config->twig->render("warehouse/inventory/receiving/$config->company/po-items.twig", ['m_receiving' => $receiving, 'ponbr' => $data->ponbr, 'items' => $po->get_receivingitems()]);
		} else {
			$html .= $config->twig->render('warehouse/inventory/receiving/po-items.twig', ['m_receiving' => $receiving,  'ponbr' => $data->ponbr, 'items' => $po->get_receivingitems()]);
		}
		return $html;
	}

	static protected function scanForm($data) {
		$html = '';

		if (empty($data->scan) === false) {
			return self::scannedForm($data);
		}

		$html = '<h3>Scan item to add</h3>';
		$html .= '<div class="mb-3">';
			$html .= self::poItemScanForm($data);
		$html .= '</div>';
		return $html;
	}

	static protected function scannedForm($data) {
		$q = WhseitemphysicalcountQuery::create();
		$q->filterBySessionid(self::getSessionid());
		$q->filterByScan($data->scan);

		if ($q->count() > 1) {
			$physicalitems = $q->find();
			return self::pw('config')->twig->render('warehouse/inventory/physical-count/item-list.twig', ['items' => $physicalitems]);
		}

		if ($q->count() == 1) {
			$physicalitem = $q->findOne();
			return self::scannedFormSingleItem($data, $physicalitem);
		}

		// $q->count() == 0
		$html = '<h3>'. "No results found for '$data->scan'" .'</h3>';
		$html .= '<div class="mb-3">';
			$html .= self::poItemScanForm($data);
		$html .= '</div>';
		return $html;
	}

	static protected function scannedFormSingleItem($data, Whseitemphysicalcount $physicalitem) {
		$html = '';

		if ($physicalitem->has_error()) {
			$html .= self::scannFormSingleItemError($data, $physicalitem);
		}

		$config = self::pw('config');
		$html  .= self::poItemScanReceiveForm($data, $physicalitem);
		return $html;
	}

	static protected function scannedFormSingleItemError($data, Whseitemphysicalcount $physicalitem) {
		$html = '';

		if ($physicalitem->has_error()) {
			if (!$physicalitem->is_on_po()) {
				$physicalitem->setItemid('');
				$physicalitem->setLotserial('');
				$physicalitem->setLotserialref('');
			}

			$html .= '<div class="mb-3">';
				$html .= self::pw('config')->twig->render('util/alert.twig', ['type' => 'danger', 'title' => 'Error!', 'iconclass' => 'fa fa-warning fa-2x', 'message' => $physicalitem->get_error()]);
			$html .= '</div>';
		}
		return $html;
	}

	static protected function poItemScanForm($data) {
		$configEnv = self::pw('modules')->get('ConfigsWarehouseInventory');
		$settings = new WireData();
		$settings->forceItemLookupBin = $configEnv->receive_force_bin_itemlookup;
		$settings->skipBin            = $configEnv->receive_disregard_bin;
		$settings->binid = '';
		$receiving = self::getReceiving($data->ponbr);
		$received = $receiving->getSessionLastReceived();

		if ($received->binid && $configEnv->physicalcount_savebin) {
			$settings->binid = $received->binid;
		}
		return self::pw('config')->twig->render('warehouse/inventory/receiving/po-item-form.twig', ['ponbr' => $data->ponbr, 'settings' => $settings]);
	}

	static protected function poItemScanReceiveForm($data, Whseitemphysicalcount $physicalitem) {
		$configEnv = self::pw('modules')->get('ConfigsWarehouseInventory');
		$settings = new WireData();
		$settings->forceItemLookupBin = $configEnv->receive_force_bin_itemlookup;
		$settings->skipBin            = $configEnv->receive_disregard_bin;
		$settings->binid = '';
		$receiving = self::getReceiving($data->ponbr);
		$received = $receiving->getSessionLastReceived();

		if ($received->binid && $configEnv->physicalcount_savebin) {
			$settings->binid = $received->binid;
		}

		return self::pw('config')->twig->render('warehouse/inventory/receiving/po-item-receive-form.twig', ['item' => $physicalitem, 'm_receiving' => self::getReceiving($data->ponbr)]);
	}

/* =============================================================
	Validator, Module Getters
============================================================= */
	static public function getReceiving($ponbr = '') {
		self::pw('modules')->get('WarehouseManagement');

		if (empty(self::$receiving)) {
			self::$receiving = new ReceivingCRUD();
			self::$receiving->setSessionid(self::getSessionid());
		}
		if ($ponbr) {
			self::$receiving->setPonbr($ponbr);
		}
		return self::$receiving;
	}

	static public function getValidatorMpo() {
		if (empty(self::$validatempo)) {
			self::$validatempo = new MpoValidator();
		}
		return self::$validatempo;
	}

/* =============================================================
	Init
============================================================= */
	public static function initHooks() {
		$m = self::pw('modules')->get('WarehouseManagement');

		$m->addHook('Page(pw_template=whse-receiving)::receivingUrl', function($event) {
			$event->return = self::receivingUrl($event->arguments(0));
		});

	}
}
