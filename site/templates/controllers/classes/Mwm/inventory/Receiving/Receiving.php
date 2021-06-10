<?php namespace Controllers\Wm\Receiving;

use stdClass;
// Purl Library
use Purl\Url as Purl;
// Propel Classes
use Propel\Runtime\ActiveQuery\Criteria;
// Dplus Model
use WarehouseQuery, Warehouse;
use PurchaseOrder;
use PurchaseOrderDetailLotReceiving;
use PurchaseOrderDetailReceiving;
// Dpluso Model
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
	const DPLUSPERMISSION = 'er';

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
		$html =  self::pw('config')->twig->render('warehouse/inventory/receiving/bread-crumbs.twig');
		$html .= self::poForm($data);
		$receiving = self::getReceiving();
		$createStrategy = $receiving->getCreatePoStrategy();
		if ($createStrategy->allowCreatePo()) {
			$url = self::receivingCreatePoUrl();
			$writer = self::pw('modules')->get('HtmlWriter');
			$html .= $writer->a("class=btn btn-primary|href=$url", $writer->icon('fa fa-plus') . " Create PO");
		}
		return $html;
	}

	static public function handleCRUD($data) {
		self::sanitizeParametersShort($data, ['action|text', 'ponbr|ponbr', 'scan|text']);

		$validate = self::getValidatorMpo();
		if (empty($data->ponbr) === false && $validate->po($data->ponbr) === false) {
			self::redirect(self::receivingUrl($data->ponbr), $http301 = false);
		}

		$m = self::getReceiving($data->ponbr);
		$m->processInput(self::pw('input'));

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
			case 'create-po':
				$url = self::receivingLoadPoUrl();
				self::redirect($url, $http301 = false);
				break;
			default:
				self::redirect(self::receivingUrl($data->ponbr), $http301 = false);
				break;
		}
	}

	static public function loadPo($data) {
		$user  = self::pw('user');
		$ponbr = $user->editing;
		if (intval($ponbr) === 0) {

		}
		self::redirect(self::receivingUrl($ponbr), $http301 = false);
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
		self::pw('page')->headline = "Receiving: PO # $data->ponbr";
		$receiving = self::getReceiving();
		$receiving->setPonbr($data->ponbr);
		$po = $receiving->getPurchaseorder();

		if ($po->count_receivingitems() === 0) {
			$receiving->requestPoInit();
		}

		if ($po->count_receivingitems() === 0) {
			$allowAdd = $receiving->getEnforceItemidsStrategy();

			if ($allowAdd->allowItemsNotListed() === false) {
				return self::noItemsToReceive($data);
			}
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
		$jsconfig = ['warehouse' => ['id' => $whsesession->whseid]];
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
		self::sanitizeParametersShort($data, ['binID|text']);
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

	static public function deleteReceivedLotserialUrl(PurchaseOrderDetailLotReceiving $lot) {
		$url = new Purl(self::receivingUrl($lot->ponbr));
		$url->query->set('action', 'delete-lotserial');
		$url->query->set('ponbr', $lot->ponbr);
		$url->query->set('linenbr', $lot->linenbr);
		$url->query->set('lotserial', $lot->lotserial);
		$url->query->set('binID', $lot->bin);
		return $url->getUrl();
	}

	static public function postReceivingUrl($ponbr) {
		$url = new Purl(self::receivingUrl($ponbr));
		$url->query->set('action', 'post-received');
		return $url->getUrl();
	}

	static public function printReceivingLineUrl(PurchaseOrderDetailReceiving $item) {
		$url = new Purl(self::receivingUrl($ponbr));
		$url->path = self::pw('pages')->get('pw_template=whse-print-received-item-label')->url;
		$url->query->set('ponbr', $item->ponbr);
		$url->query->set('linenbr', $item->linenbr);
		return $url->getUrl();
	}

	static public function receivingCreatePoUrl() {
		$url = new Purl(self::receivingUrl());
		$url->path->add('create');
		return $url->getUrl();
	}

	static public function receivingLoadPoUrl($ponbr = '') {
		$url = new Purl(self::receivingUrl());
		$url->path->add('load');
		if ($ponbr) {
			$url->query->set('ponbr', $ponbr);
		}
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
		$html =  $config->twig->render('warehouse/inventory/receiving/bread-crumbs.twig');
		$html .= $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => 'Purchase Order Not Found', 'iconclass' => 'fa fa-warning fa-2x', 'message' => "Order # $data->ponbr can not be found"]);
		$html .= '<div class="mb-3"></div>';
		$html .= self::poForm($data);

		return $html;
	}

	static public function noItemsToReceive($data) {
		$writer = self::getHtmlWriter();
		$html   = self::pw('config')->twig->render('util/alert.twig', ['type' => 'warning', 'title' => 'Warning!', 'iconclass' => 'fa fa-warning fa-2x', 'message' => 'No Items Left To Receive']);
		$html   .= $writer->div('class=mb-3');
		$html   .= self::poForm($data);
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

		$html = '<h3>Scan Item to Receive</h3>';
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
		if ($physicalitem->has_error() === false) {
			return self::poItemScanReceiveForm($data, $physicalitem);
		}

		$html = '';

		if ($physicalitem->has_error()) {
			$config = self::pw('config');
			$receiving = self::getReceiving($data->ponbr);

			if (trim($physicalitem->get_error()) == 'invalid item id' && $receiving->strategies->enforceItemids->allowItemsNotListed()) {
				$html .= $config->twig->render('warehouse/inventory/receiving/ugm/create-ilookup-item-form.twig', ['item' => $physicalitem, 'm_receiving' => $receiving]);
				self::pw('page')->js .= $config->twig->render('warehouse/inventory/receiving/ugm/ilookup.js.twig');
			}

			if ($receiving->strategies->enforceItemids->allowItemsNotListed() == false) {
				$html .= self::scannFormSingleItemError($data, $physicalitem);
				$html  .= self::poItemScanReceiveForm($data, $physicalitem);
			}
		}
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

	static public function createPo($data) {
		$fields = ['action|text'];
		self::sanitizeParametersShort($data, $fields);

		if (empty($data->action) === false) {
			return self::handleCRUD($data);
		}

		self::pw('page')->headline = "Create PO to Receive";
		self::pw('page')->js .= self::pw('config')->twig->render('warehouse/inventory/receiving/create-po/js.twig');
		return self::pw('config')->twig->render('warehouse/inventory/receiving/create-po/form.twig');
	}

/* =============================================================
	Validator, Module Getters
============================================================= */
	static public function validateUserPermission(user $user = nul) {
		if (empty($user)) {
			$user = self::pw('user');
		}
		return $user->has_function(self::DPLUSPERMISSION);
	}

	static public function getReceiving($ponbr = '') {
		self::pw('modules')->get('WarehouseManagement');

		if (empty(self::$receiving)) {
			self::$receiving = new ReceivingCRUD();
			self::$receiving->setSessionid(self::getSessionid());
		}
		if ($ponbr) {
			self::$receiving->setPonbr($ponbr);
		}
		self::$receiving->init();
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

		$m->addHook('Page(pw_template=whse-receiving)::deleteReceivedLotserialUrl', function($event) {
			$lot     = $event->arguments(0); // Instance of PurchaseOrderDetailLotReceiving
			$event->return = self::deleteReceivedLotserialUrl($lot);
		});

		$m->addHook('Page(pw_template=whse-receiving)::postReceivingUrl', function($event) {
			$lot     = $event->arguments(0); // Instance of PurchaseOrderDetailLotReceiving
			$event->return = self::postReceivingUrl($lot);
		});

		$m->addHook('Page(pw_template=whse-receiving)::printReceivingLineUrl', function($event) {
			$item     = $event->arguments(0); // Instance of PurchaseOrderDetailReceiving
			$event->return = self::printReceivingLineUrl($item);
		});
	}
}
