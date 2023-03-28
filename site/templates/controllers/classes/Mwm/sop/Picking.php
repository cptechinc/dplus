<?php namespace Controllers\Wm\Sop\Picking;
// Purl Library
use Purl\Url as Purl;
// Dplus Model
use SalesOrderQuery;
// Dpluso Model
// ProcessWire Classes, Modules
use ProcessWire\Page, ProcessWire\Module;
use Processwire\User;
use Processwire\WarehouseManagement;
use  Dplus\Mso\So\SalesOrder as SalesOrders;
// Dplus 
use Dplus\CodeValidators\Mso as MsoValidator;
use Dplus\Session\UserMenuPermissions;
use Dplus\Wm\Sop\Picking\Picking as PickingCRUD;
use Dplus\Wm\Sop\Picking\Strategies\Inventory\Lookup\Lookup as InvLookup;
use Dplus\Wm as Wm;
// Mvc Controllers
use Controllers\Wm\Base;

class Picking extends Base {
	const DPLUSPERMISSION = 'porpk';

	/** @var PickingCRUD */
	private static $picking;
	/** @var MsoValidator */
	private static $validateMso;

/* =============================================================
	Indexes
============================================================= */
	public static function index($data) {
		$fields = ['scan|text', 'action|text', 'ordn|ordn'];
		self::sanitizeParametersShort($data, $fields);

		if (self::validateUserPermission() === false) {
			return static::renderUserNotPermittedAlert();
		}

		if (empty($data->action) === false) {
			return self::handleCRUD($data);
		}

		if (empty($data->ordn) === false) {
			return self::picking($data);
		}
		$picking  = self::getPicking();
		$wSession = self::getWhsesession();

		if ($wSession->is_pickingunguided() === false) {
			$picking->requestStartPicking();
		}
		$html = self::displayOrderForm();
		return $html;
	}

	public static function handleCRUD($data) {
		self::sanitizeParametersShort($data, ['action|text', 'ordn|ordn', 'scan|text']);
		$validate = self::getValidatorMso();

		if (empty($data->ordn) === false && $validate->order($data->ordn) === false) {
			self::redirect(self::pickingUrl($data->ordn), $http301 = false);
		}

		$m = self::getPicking($data->ordn);
		$m->processInput(self::pw('input'));


		// REDIRECT
		switch ($data->action) {
			case 'scan-pick-item':
				self::redirect(self::pickScanUrl($data->ordn, $data->scan), $http301 = false);
				break;
			case 'exit-order':
			case 'finish-order':
				self::redirect(self::pickingUrl(), $http301 = false);
				break;
			case 'add-lotserials':
				if (self::pw('session')->getFor('picking', 'verify-picked-items')) {
					self::redirect(self::pickScanUrl($data->ordn, $data->scan), $http301 = false);
				}
				self::redirect(self::pickingUrl($data->ordn), $http301 = false);
				break;
			default:
				self::redirect(self::pickingUrl($data->ordn), $http301 = false);
				break;
		}
	}

	private static function picking($data) {
		self::sanitizeParametersShort($data, ['action|text', 'ordn|text']);
		$validate = self::getValidatorMso();
		$wSession = self::getWhsesession();

		if ((empty($data->ordn) === false && $validate->order($data->ordn) === false) || $wSession->is_orderinvalid()) {
			return self::displayOrderNotFound();
		}

		self::pw('page')->headline = "Picking Order # $data->ordn";

		$picking = self::getPicking($data->ordn);
		$configInventory = $picking->getConfigInventory();

		if ($wSession->is_orderonhold() || $wSession->is_orderverified() || $wSession->is_orderinvoiced() || $wSession->is_ordernotfound() || (!$configInventory->allow_negativeinventory && $wSession->is_ordershortstocked())) {
			return self::displayErrorStatus($wSession->get_statusmessage());
		}
		self::pw('config')->scripts->append(self::pw('modules')->get('FileHasher')->getHashUrl('scripts/warehouse/pick-order.js'));
		return self::pickOrder($data);
	}

	protected static function pickOrder($data) {
		self::sanitizeParametersShort($data, ['action|text', 'ordn|ordn']);
		$config   = self::pw('config');
		$wSession = self::getWhsesession();
		$picking  = self::getPicking($data->ordn);

		if ($wSession->is_orderfinished()) {
			return $config->twig->render('warehouse/picking/order/finished.twig', ['ordn' => $data->ordn]);
		}

		$html = '';

		// Check if there are 0 items left to pick
		if ($picking->items->queryOrdn()->count() === 0) {
			$wSession->setStatus("There are no detail lines available to pick for Order # $ordn");

			if ($wSession->is_orderfinished() || $wSession->is_orderexited()) {
				// TODO
				// WhseItempickQuery::create()->filterByOrdn($ordn)->filterBySessionid(session_id())->delete();
			}

			$html = self::displayErrorStatus($wSession->get_statusmessage());
			$html .= '<div class="mb-3"></div>';
			$html .= self::displayOrderForm();
			return $html;
		}

		$html .= self::orderDisplay($data);
		return $html;
	}

/* =============================================================
	Data Processing
============================================================= */

/* =============================================================
	URLs
============================================================= */
	public static function pickingUrl($ordn = '') {
		$url = new Purl(self::pw('pages')->get('pw_template=whse-picking')->url);
		if ($ordn) {
			$url->query->set('ordn', $ordn);
		}
		return $url->getUrl();
	}

	public static function pickScanUrl($ordn, $scan) {
		$url = new Purl(self::pickingUrl($ordn));
		$url->query->set('scan', $scan);
		return $url->getUrl();
	}

	public static function pickingExitUrl($ordn) {
		$url = new Purl(self::pickingUrl($ordn));
		$url->query->set('action', 'exit-order');
		return $url->getUrl();
	}

	public static function pickingFinishUrl($ordn) {
		$url = new Purl(self::pickingUrl($ordn));
		$url->query->set('action', 'finish-order');
		return $url->getUrl();
	}

/* =============================================================
	Displays
============================================================= */
	private static function displayErrorStatus($msg) {
		return self::pw('config')->twig->render('util/alert.twig', ['type' => 'danger', 'title' => 'Error!', 'iconclass' => 'fa fa-warning fa-2x', 'message' => $msg]);
	}

	private static function displayOrderForm() {
		return self::pw('config')->twig->render('warehouse/picking/order/form.twig');
	}

	private static function displayOrderNotFound() {
		$html  = self::pw('config')->twig->render('util/alert.twig', ['type' => 'danger', 'title' => 'Order Not Found', 'iconclass' => 'fa fa-warning fa-2x', 'message' => "Order # $data->ordn can not be found"]);
		$html .= '<div class="mb-3"></div>';
		$html .= self::displayOrderForm();
		return $html;
	}

	private static function displaySessionErrors() {
		$session  = self::pw('session');
		$wSession = self::getWhsesession();
		$html = '';

		if ($session->pickingerror) {
			$writer = self::getHtmlWriter();
			$html = '<div class="mb-3">' . self::displayErrorStatus($session->pickingerror) . '</div>';
			$session->remove('pickingerror');
		}

		if ($wSession->has_warning()) {
			$html .= '<div class="mb-3">' . self::displayErrorStatus($wSession->status) . '</div>';
		} elseif ($wSession->has_message()) {
			$html .= '<div class="mb-3">' . self::displayErrorStatus($wSession->status) . '</div>';
		}
		return $html;
	}

	private static function orderDisplay($data) {
		$writer  = self::getHtmlWriter();

		$html  = '';
		$html .= self::displaySessionErrors();
		$html .= self::displayOrderHeader($data);

		if (empty($data->scan)) {
			$html .= self::displayScanForm($data);
		}

		if (empty($data->scan) === false) {
			self::pw('page')->scan = $data->scan;
			$html .= self::displayScanResults($data);
		}

		$html .= self::displayOrderItems($data);
		$html .= $writer->div('class=mb-3');
		$html .= self::displayOrderActions($data);
		return $html;
	}

	private static function displayOrderHeader($data) {
		$salesOrders = SalesOrders::instance();
		$wSession = self::getWhsesession();
		$order = $salesOrders->order($data->ordn);
		return self::pw('config')->twig->render('warehouse/picking/order/header-info.twig', ['order' => $order, 'whsesession' => $wSession]);
	}

	private static function displayOrderItems($data) {
		$wSession = self::getWhsesession();
		$picking  = self::getPicking($data->ordn);
		$config   = self::pw('config');
		$items    = $picking->items->getItems();

		if ($picking->items->hasSublines()) {
			return $config->twig->render('warehouse/picking/unguided/order/items/sublined/items.twig', ['lineitems' => $items, 'm_picking' => $picking]);
		}

		if ($config->twigloader->exists("warehouse/picking/unguided/$config->company/order/items.twig")) {
			return $config->twig->render("warehouse/picking/unguided/$config->company/order/items.twig", ['lineitems' => $items, 'm_picking' => $picking]);
		}
		return $config->twig->render('warehouse/picking/unguided/order/items.twig', ['lineitems' => $items, 'm_picking' => $picking]);
	}

	private static function displayOrderActions($data) {
		return self::pw('config')->twig->render('warehouse/picking/unguided/order/actions.twig', ['ordn' => $data->ordn]);
	}

	private static function displayScanResults($data) {
		$session = self::pw('session');
		$picking = self::getPicking($data->ordn);
		$inv     = $picking->inventory->lookup;
		$q       = $inv->getScanQuery($data->scan);

		if ($session->getFor('picking', 'verify-picked-items')) {
			return self::displayScanVerifyPicked($data, $picking);
		}

		if ($q->count() == 0) {
			$writer  = self::getHtmlWriter();
			$html .= $writer->div('class=mb-3', $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => '0 items found', 'iconclass' => 'fa fa-warning fa-2x', 'message' => "No items found for '$data->scan'"]));
			$html .= self::pw('config')->twig->render('warehouse/picking/unguided/scan/form.twig');
			return $html;
		}

		if ($q->count() == 1) {
			return self::displayScanResultsSingle($data, $picking);
		}
		return self::displayScanResultsMultiple($data, $picking);
	}

	private static function displayScanVerifyPicked($data, PickingCRUD $picking) {
		$session = self::pw('session');
		$query = $picking->getWhseitempickQuery(['barcode' => $data->scan, 'recordnumber' => $session->getFor('picking', 'verify-picked-items')]);

		if ($query->count()) {
			return self::pw('config')->twig->render('warehouse/picking/unguided/scan/verify-picked.twig', ['scan' => $data->scan, 'm_picking' => $picking, 'items' => $query->find()]);
		}
		$session->removeFor('picking', 'verify-picked-items');
		$writer  = self::getHtmlWriter();
		$html = $writer->div('class=mb-3');
		$html .= $config->twig->render('warehouse/picking/unguided/scan/form.twig');
		return $html;
	}

	private static function displayScanResultsMultiple($data, PickingCRUD $picking) {
		/** @var InvLookup */
		$lookup  = $picking->inventory->lookup;
		$q       = $lookup->getScanQuery($data->scan);
		$writer  = self::getHtmlWriter();
		$html    = '';

		$itemsDistinct = $q->groupBy('itemid')->find();
		self::pw('page')->js   .= self::pw('config')->twig->render('warehouse/picking/unguided/scan/scanned/select-multiple-list.js.twig');
		$html = $writer->h3('', 'Select Items to Pick');
		$html .= self::pw('config')->twig->render('warehouse/picking/unguided/scan/scanned/select-multiple-list.twig', ['items' => $itemsDistinct, 'm_picking' => $picking]);
		return $html;
	}

	private static function displayScanResultsSingle($data, PickingCRUD $picking) {
		/** @var InvLookup */
		$lookup  = $picking->inventory->lookup;
		$config  = self::pw('config');
		$q       = $lookup->getScanQuery($data->scan);
		$writer  = self::getHtmlWriter();
		$html    = '';

		if ($q->count() == 1) {
			$item = $q->findOne();

			if ($item->has_error()) {
				$html .= $writer->div('class=mb-3', $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => "Error searching '$data->scan'", 'iconclass' => 'fa fa-warning fa-2x', 'message' => $item->get_error()]));
				$html .= $writer->h3('', 'Scan item to add');
				$html .= $config->twig->render('warehouse/picking/unguided/scan/form.twig');
				return $html;
			}
			$picking = self::getPicking($data->ordn);

			if ($picking->items->hasItemid($item->itemid)) {
				$orderitem  = $picking->items->getItemByItemid($item->itemid);
				$html .= $writer->h3('', 'Enter Item Details');
				$html .= $config->twig->render('warehouse/picking/unguided/scan/scanned/add-single-form.twig', ['item' => $item, 'orderitem' => $orderitem, 'scan' => $data->scan]);
				self::pw('page')->js   .= $config->twig->render('warehouse/picking/unguided/scan/scan.js.twig');
				return $html;
			}

			$html .= $writer->div('class=mb-3', $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => 'Item Not on Order', 'iconclass' => 'fa fa-warning fa-2x', 'message' => "Item $item->itemid is not on this order"]));
			$html .= $config->twig->render('warehouse/picking/unguided/scan/form.twig');
			return $html;
		}
	}

	private static function displayScanForm($data) {
		$writer = self::getHtmlWriter();
		$html = $writer->h3('', 'Scan item to pick');
		$html .= self::pw('config')->twig->render('warehouse/picking/unguided/scan/form.twig');
		$html .= $writer->hr();
		return $html;
	}

/* =============================================================
	Validator, Module Getters
============================================================= */
	public static function getPicking($ordn = '') {
		self::pw('modules')->get('WarehouseManagement');

		if (empty(self::$picking)) {
			self::$picking = new PickingCRUD();
			self::$picking->setSessionid(session_id());
		}
		if ($ordn) {
			self::$picking->setOrdn($ordn);
		}
		self::$picking->init();
		return self::$picking;
	}

	public static function getValidatorMso() {
		if (empty(self::$validateMso)) {
			self::$validateMso = new MsoValidator();
		}
		return self::$validateMso;
	}

/* =============================================================
	Supplemental
============================================================= */
	public static function validateUserPermission(User $user = null) {
		if (static::validateMenuPermissions($user) === false) {
			return false;
		}
		return parent::validateUserPermission($user);
	}

	public static function validateMenuPermissions(User $user = null) {
		$page   = self::pw('page');

		foreach ($page->parents('template=dplus-menu|warehouse-menu') as $parent) {
			$code = $parent->dplus_function ? $parent->dplus_function : $parent->dplus_permission;

			if (empty($code) === false && UserMenuPermissions::instance()->canAccess($code) === false) {
				return false;
			}
		}
	}

/* =============================================================
	Init
============================================================= */
	public static function init() {
		$m = self::pw('modules')->get('WarehouseManagement');

		$m->addHook('Page::removeScanUrl', function($event) {
			$p   = $event->object;
			$url = $p->fullURL;
			$event->return = self::pickingUrl($url->query->get('ordn'));
		});

		$m->addHook('Page::exitOrderUrl', function($event) {
			$p   = $event->object;
			$url = $p->fullURL;
			$event->return = self::pickingExitUrl($url->query->get('ordn'));
		});

		$m->addHook('Page::finishOrderUrl', function($event) {
			$p   = $event->object;
			$url = $p->fullURL;
			$event->return = self::pickingFinishUrl($url->query->get('ordn'));
		});
	}
}
