<?php namespace Controllers\Wm;
// Purl Library
use Purl\Url as Purl;
// Dplus Model
use WarehouseQuery, Warehouse;
// Dpluso Model
use BininfoQuery;
use WhsesessionQuery, Whsesession;
// ProcessWire Classes, Modules
use ProcessWire\Page;  
use Processwire\SearchInventory;
use Processwire\User;
use Processwire\WarehouseManagement;
// Dplus Classes
use Dplus\Session\UserMenuPermissions;
use Dplus\Wm\Binr as BinrCRUD;
// Mvc Controllers
use Controllers\AbstractController;
use Controllers\Mwm\Menu;

class Binr extends AbstractController {
	const PARENT_MENU_CODE = 'wm';
	const DPLUSPERMISSION = 'binr';

	/** @var SearchInventory **/
	private static $inventory;

	/** @var WarehouseManagement **/
	private static $whsem;

	/** @var Warehouse **/
	private static $warehouse;

	/** @var Whsesession **/
	private static $whsesession;

	public static function index($data) {
		$fields = ['scan|text', 'action|text', 'frombin|text', 'tobin|text'];
		$data = self::sanitizeParametersShort($data, $fields);

		if (self::validateUserPermission() === false) {
			return self::renderUserNotPermittedAlert();
		}

		if (empty($data->action) === false) {
			return self::handleCRUD($data);
		}

		if (empty($data->scan) === false) {
			return self::handleScan($data);
		}

		if (empty($data->serialnbr) === false || empty($data->lotnbr) === false || empty($data->itemID) === false) {
			return self::handleItem($data);
		}

		return self::searchItemForm($data);
	}

	public static function handleCRUD($data) {
		$data = self::sanitizeParametersShort($data, ['action|text', 'scan|text', 'frombin']);

		switch ($data->action) {
			case 'inventory-search':
				$whsem = self::getWarehouseManagement();
				$whsem->requestInventorySearch(strtoupper($data->scan));
				$url = new Purl(self::pw('page')->url);
				$url->query->set('scan', $data->scan);
				if ($data->frombin) {
					$url->query->set('frombin', $data->frombin);
				}
				if ($data->tobin) {
					$url->query->set('tobin', $data->tobin);
				}
				self::pw('session')->redirect($url->getUrl(), $http301 = false);
				break;
			case 'bin-reassign';
				$data = self::sanitizeParametersShort($data, ['itemID|text', 'frombin|text', 'tobin|text', 'serialnbr|text', 'lotnbr|text', 'qty|float']);
				$binr = new BinrCRUD();
				$binr->requestBinReassignment($data);
				$params = explode('&', self::pw('input')->queryString());
				$url = new Purl(self::pw('page')->url);
				if (array_key_exists('frombin', $params)) {
					$url->query->set('frombin', $params['frombin']);
				}
				if (array_key_exists('tobin', $params)) {
					$url->query->set('tobin', $params['tobin']);
				}
				self::pw('session')->redirect($url->getUrl(), $http301 = false);
				break;
		}
	}

	public static function handleScan($data) {
		$fields = ['scan|text', 'action|text', 'frombin|text', 'tobin|text', 'binID|text'];
		$data = self::sanitizeParametersShort($data, $fields);

		if (empty($data->action) === false) {
			return self::handleCRUD($data);
		}
		$html = '';
		$config = self::pw('config');
		$modules = self::pw('modules');
		$config->binr = $modules->get('ConfigsBinr');
		/** @var SearchInventory **/
		$inventory = self::getInventorySearch();
		$resultscount = $inventory->query()->count();

		if ($resultscount === 0) {
			return $config->twig->render('warehouse/binr/inventory-results.twig', ['items' => []]);
		}

		if ($resultscount === 1) {
			// $item = $inventory->query()->findOne();
			// $url = self::pw('page')->binr_itemURL($item);
			// self::pw('session')->redirect($url, $http301 = false);
			return self::handleScanInventorySingleItemid($data);
		}

		// Multiple Items - count the number Distinct Item IDs
		$countItemids = $inventory->count_itemids_distinct($data->binID);

		if ($countItemids === 1) {
			return self::handleScanInventorySingleItemid($data);
		}
		$items = $inventory->get_items_distinct($data->binID);
		return $config->twig->render('warehouse/binr/inventory-results.twig', ['resultscount' => $resultscount, 'items' => $items]);
	}

	private static function handleScanInventorySingleItemid($data) {
		$fields    = ['scan|text', 'action|text', 'frombin|text', 'tobin|text', 'binID|text'];
		self::sanitizeParametersShort($data, $fields);
		$inventory = self::getInventorySearch();
		$config    = self::pw('config');

		$q = $inventory->query();
		if ($data->binID) {
			$q->filterByBin($data->binID);
		}
		$item = $q->findOne();

		if ($item->is_lotted() === false && $item->is_serialized() === false) {
			$whsem = self::getWarehouseManagement();
			$whsem->requestItemBins($item->itemid);
			$url = new Purl(self::pw('page')->url);
			$url->query->set('itemID', $item->itemid);
			if ($data->frombin) {
				$url->query->set('frombin', $data->frombin);
			}
			if ($data->tobin) {
				$url->query->set('tobin', $data->tobin);
			}
 			self::pw('session')->redirect($url->getUrl(), $http301 = false);
		}

		// If Item is Lotted / Serialized show results to choose which lot or serial to move
		$countLotserial = $inventory->count_itemid_records($item->itemid, $data->binID);

		if ($countLotserial == 1) {
			$whsem = self::getWarehouseManagement();
			$whsem->requestItemBins($item->itemid);
			$url = new Purl(self::pw('page')->url);
			if ($item->is_lotted()) {
				$url->query->set('lotnbr', $item->lotserial);
			}
			if ($item->is_serialized()) {
				$url->query->set('serialnbr', $item->lotserial);
			}
			if ($data->frombin) {
				$url->query->set('frombin', $data->frombin);
			}
			if ($data->tobin) {
				$url->query->set('tobin', $data->tobin);
			}
			self::pw('session')->redirect($url->getUrl(), $http301 = false);
		}
		$items = $inventory->get_items_distinct($data->binID);
		$warehouse = self::getCurrentUserWarehouse();

		if ($config->twigloader->exists("warehouse/binr/$config->company/inventory-results.twig")) {
			return $config->twig->render("warehouse/binr/$config->company/inventory-results.twig", ['config' => $config->binr, 'resultscount' => $countLotserial, 'items' => $items, 'warehouse' => $warehouse, 'inventory' => $inventory]);
		}
		return $config->twig->render('warehouse/binr/inventory-results.twig', ['config' => $config->binr, 'resultscount' => $countLotserial, 'items' => $items, 'warehouse' => $warehouse, 'inventory' => $inventory]);
	}

	private static function handleItem($data) {
		$fields = ['lotnbr|text', 'serialnbr|text', 'itemID|text', 'binID|text', 'frombin|string', 'tobin|string'];
		self::sanitizeParametersShort($data, $fields);
		if (empty($data->binID) && empty($data->frombin) === false) {
			$data->binID = $data->frombin;
		}
		$page   = self::pw('page');
		$config = self::pw('config');
		$warehouse   = self::getCurrentUserWarehouse();
		$inventory   = self::getInventorySearch();
		$whsesession = self::getWhseSession();
		$page->tobin = $data->tobin;

		if ($data->itemID) {
			$page->scan   = $data->itemID;
			$resultscount = $inventory->count_lotserial_records($data->itemID, $data->binID);
		}

		if ($data->lotnbr) {
			$page->scan   = $data->lotnbr;
			$resultscount = $inventory->count_lotserial_records($data->lotnbr, $data->binID);
		}
		if ($data->serialnbr) {
			$page->scan   = $data->serialnbr;
			$resultscount = $inventory->count_lotserial_records($data->serialnbr, $data->binID);
		}

		if ($resultscount > 1) {
			$items = $inventory->get_items_distinct();
			return $config->twig->render('warehouse/binr/inventory-results.twig', ['resultscount' => $resultscount, 'items' => $items, 'inventory' => $inventory]);
		}

		$html = '';

		if (empty(self::pw('session')->get('binr')) === false) {
			$html .= $config->twig->render('warehouse/binr/binr-result.twig', ['whsesession' => $whsesession, 'nexturl' => $page->url]);
			self::pw('session')->remove('binr');
			return $html;
		}

		// $resultscount == 1

		// Prepare Binr Form
		$item = $data->lotnbr || $data->serialnbr ? $inventory->get_lotserial($page->scan, $data->binID) : $inventory->get_invsearch_by_itemid($page->scan, $data->binID);
		$whsem = self::getWarehouseManagement();
		$whsem->requestItemBins($item->itemid);
		$currentbins = BininfoQuery::create()->filterByItem(session_id(), $item)->select_bin_qty()->find();
		// 1. Binr form
		$page->formurl = $page->parent('template=warehouse-menu')->child('template=redir')->url;
		$html .= $config->twig->render('warehouse/binr/binr-form.twig', ['config' => $config->binr, 'whsesession' => $whsesession, 'item' => $item, 'inventory' => $inventory]);
		// 2. Choose From Bin Modal
		$html .= $config->twig->render('warehouse/binr/from-bins-modal.twig', ['config' => $config->binr, 'item' => $item, 'bins' => $currentbins]);
		// 3. Choose To Bin Modals
		$html .= $config->twig->render('warehouse/binr/to-bins-modal.twig', ['config' => $config->binr, 'currentbins' => $currentbins, 'warehouse' => $warehouse, 'item' => $item, 'inventory' => $inventory]);
		// 4. Warehouse Config JS
		$bins = $warehouse->get_bins();
		
		$availablebins = BininfoQuery::create()->filterBySessionItemid(session_id(), $item->itemID)->find()->toArray('Bin');
		
		$jsconfig = array('warehouse' => array('id' => $whsesession->whseid, 'binarrangement' => $warehouse->get_binarrangementdescription(), 'bins' => $bins));
		$html .= $config->twig->render('util/js-variables.twig', ['variables' => array('warehouse' => $jsconfig, 'validfrombins' => $availablebins)]);
		return $html;
	}

	private static function searchItemForm($data) {
		$page   = self::pw('page');
		$config = self::pw('config');
		$page->formurl = $page->url;
		return $config->twig->render('warehouse/item-form.twig');
	}

	public static function getWarehouseManagement() {
		if (empty(self::$whsem)) {
			self::$whsem = self::pw('modules')->get('WarehouseManagement');
		}
		return self::$whsem;
	}

	public static function getInventorySearch() {
		if (empty(self::$inventory)) {
			self::$inventory = self::pw('modules')->get('SearchInventory');
		}
		return self::$inventory;
	}

	public static function getCurrentUserWarehouse() {
		if (empty(self::$warehouse)) {
			self::$warehouse = WarehouseQuery::create()->findOneById(self::pw('user')->whseid);
		}
		return self::$warehouse;
	}

	public static function getWhseSession() {
		if (empty(self::$whsesession)) {
			self::$whsesession = WhsesessionQuery::create()->findOneBySessionid(session_id());
		}
		return self::$whsesession;
	}

	/**
	 * Validate User's Permission to this Function
	 * @param  User|null $user
	 * @return bool
	 */
	public static function validateUserPermission(User $user = null) {
		if (Menu::validateMenuPermission() === false) {
			return false;
		}
		return parent::validateUserPermission($user);
	}

	public static function init() {
		$wm = self::pw('modules')->get('WarehouseManagement');

		$wm->addHook('Page::binr_itemURL', function($event) {
			$p = $event->object;
			$item = $event->arguments(0);
			$url = new Purl($p->url);
			$url->query->set('itemID', $item->itemid);
			$url->query->set($item->get_itemtypeproperty(), $item->get_itemidentifier());
			$url->query->set('binID', $item->bin);
			$event->return = $url->getUrl();
		});

		$wm->addHookProperty('Page::scan', function($event) {
			$p = $event->object;
			$event->return = !empty($p->scan) ? $p->scan : false;
		});

		$wm->addHookProperty('Page::tobin', function($event) {
			$p = $event->object;
			$event->return = !empty($p->tobin) ? $p->tobin : '';
		});

		$wm->addHookProperty('Page::frombin', function($event) {
			$p = $event->object;
			$event->return = !empty($p->frombin) ? $p->frombin : '';
		});
	}
}
